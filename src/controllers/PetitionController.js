/**
 * Controller de Petições (Admin)
 * Gerenciamento de petições no painel administrativo
 */
const { Petition, PetitionSignature, User, Petitioner, sequelize } = require('../models');
const { Op } = require('sequelize');

class PetitionController {
  // Listar petições
  async index(req, res) {
    try {
      const { status, search, page = 1 } = req.query;
      const limit = 20;
      const offset = (page - 1) * limit;

      const where = {};
      if (status) where.status = status;
      if (search) {
        where[Op.or] = [
          { title: { [Op.like]: `%${search}%` } },
          { description: { [Op.like]: `%${search}%` } }
        ];
      }

      const { count, rows: petitions } = await Petition.findAndCountAll({
        where,
        include: [
          { model: User, as: 'author', attributes: ['id', 'name'] },
          { model: Petitioner, as: 'petitioner', attributes: ['id', 'name', 'email', 'phone'] }
        ],
        order: [['created_at', 'DESC']],
        limit,
        offset
      });

      // Contar pendentes para badge
      const pendingCount = await Petition.count({ where: { status: 'pending' } });

      // Contar assinaturas para cada petição
      for (let petition of petitions) {
        petition.signatureCount = await PetitionSignature.count({
          where: { petition_id: petition.id }
        });
      }

      const totalPages = Math.ceil(count / limit);

      res.render('admin/petitions/index', {
        title: 'Petições',
        petitions,
        currentPage: parseInt(page),
        totalPages,
        total: count,
        status,
        search,
        pendingCount
      });
    } catch (error) {
      console.error('Erro ao listar petições:', error);
      req.flash('error', 'Erro ao carregar petições.');
      res.redirect('/admin');
    }
  }

  // Formulário de criação
  async create(req, res) {
    try {
      res.render('admin/petitions/form', {
        title: 'Nova Petição',
        petition: null,
        categories: ['Meio Ambiente', 'Direitos Humanos', 'Educação', 'Saúde', 'Política', 'Economia', 'Outros']
      });
    } catch (error) {
      console.error('Erro ao carregar formulário:', error);
      req.flash('error', 'Erro ao carregar formulário.');
      res.redirect('/admin/petitions');
    }
  }

  // Salvar nova petição
  async store(req, res) {
    try {
      const { title, description, content, goal, category, status, allow_anonymous, show_signatures, end_date, featured } = req.body;

      // Gerar slug
      let slug = title.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');

      // Verificar slug único
      const existing = await Petition.findOne({ where: { slug } });
      if (existing) {
        slug = `${slug}-${Date.now()}`;
      }

      const petition = await Petition.create({
        title,
        slug,
        description,
        content,
        goal: parseInt(goal) || 1000,
        category,
        status: status || 'draft',
        allow_anonymous: allow_anonymous === 'on',
        show_signatures: show_signatures === 'on',
        end_date: end_date || null,
        featured: featured === 'on',
        author_id: req.session.user.id,
        image: req.file ? `/uploads/${req.file.filename}` : null
      });

      req.flash('success', 'Petição criada com sucesso!');
      res.redirect('/admin/petitions');
    } catch (error) {
      console.error('Erro ao criar petição:', error);
      req.flash('error', 'Erro ao criar petição: ' + error.message);
      res.redirect('/admin/petitions/create');
    }
  }

  // Formulário de edição
  async edit(req, res) {
    try {
      const petition = await Petition.findByPk(req.params.id, {
        include: [{ model: User, as: 'author' }]
      });

      if (!petition) {
        req.flash('error', 'Petição não encontrada.');
        return res.redirect('/admin/petitions');
      }

      const signatureCount = await PetitionSignature.count({
        where: { petition_id: petition.id }
      });

      res.render('admin/petitions/form', {
        title: 'Editar Petição',
        petition,
        signatureCount,
        categories: ['Meio Ambiente', 'Direitos Humanos', 'Educação', 'Saúde', 'Política', 'Economia', 'Outros']
      });
    } catch (error) {
      console.error('Erro ao carregar petição:', error);
      req.flash('error', 'Erro ao carregar petição.');
      res.redirect('/admin/petitions');
    }
  }

  // Atualizar petição
  async update(req, res) {
    try {
      const petition = await Petition.findByPk(req.params.id);
      if (!petition) {
        req.flash('error', 'Petição não encontrada.');
        return res.redirect('/admin/petitions');
      }

      const { title, description, content, goal, category, status, allow_anonymous, show_signatures, end_date, featured } = req.body;

      await petition.update({
        title,
        description,
        content,
        goal: parseInt(goal) || 1000,
        category,
        status,
        allow_anonymous: allow_anonymous === 'on',
        show_signatures: show_signatures === 'on',
        end_date: end_date || null,
        featured: featured === 'on',
        image: req.file ? `/uploads/${req.file.filename}` : petition.image
      });

      req.flash('success', 'Petição atualizada com sucesso!');
      res.redirect('/admin/petitions');
    } catch (error) {
      console.error('Erro ao atualizar petição:', error);
      req.flash('error', 'Erro ao atualizar petição: ' + error.message);
      res.redirect(`/admin/petitions/${req.params.id}/edit`);
    }
  }

  // Excluir petição
  async destroy(req, res) {
    try {
      const petition = await Petition.findByPk(req.params.id);
      if (!petition) {
        req.flash('error', 'Petição não encontrada.');
        return res.redirect('/admin/petitions');
      }

      // Excluir assinaturas primeiro
      await PetitionSignature.destroy({ where: { petition_id: petition.id } });
      await petition.destroy();

      req.flash('success', 'Petição excluída com sucesso!');
      res.redirect('/admin/petitions');
    } catch (error) {
      console.error('Erro ao excluir petição:', error);
      req.flash('error', 'Erro ao excluir petição.');
      res.redirect('/admin/petitions');
    }
  }

  // Ver assinaturas
  async signatures(req, res) {
    try {
      const petition = await Petition.findByPk(req.params.id);
      if (!petition) {
        req.flash('error', 'Petição não encontrada.');
        return res.redirect('/admin/petitions');
      }

      const { page = 1 } = req.query;
      const limit = 50;
      const offset = (page - 1) * limit;

      const { count, rows: signatures } = await PetitionSignature.findAndCountAll({
        where: { petition_id: petition.id },
        order: [['created_at', 'DESC']],
        limit,
        offset
      });

      const totalPages = Math.ceil(count / limit);

      res.render('admin/petitions/signatures', {
        title: `Assinaturas - ${petition.title}`,
        petition,
        signatures,
        currentPage: parseInt(page),
        totalPages,
        total: count
      });
    } catch (error) {
      console.error('Erro ao carregar assinaturas:', error);
      req.flash('error', 'Erro ao carregar assinaturas.');
      res.redirect('/admin/petitions');
    }
  }

  // Exportar assinaturas (CSV)
  async exportSignatures(req, res) {
    try {
      const petition = await Petition.findByPk(req.params.id);
      if (!petition) {
        return res.status(404).json({ error: 'Petição não encontrada' });
      }

      const signatures = await PetitionSignature.findAll({
        where: { petition_id: petition.id },
        order: [['created_at', 'DESC']]
      });

      let csv = 'Nome,Email,Cidade,Estado,Data,Verificado\n';
      signatures.forEach(s => {
        csv += `"${s.name}","${s.email}","${s.city || ''}","${s.state || ''}","${s.created_at}","${s.verified ? 'Sim' : 'Não'}"\n`;
      });

      res.setHeader('Content-Type', 'text/csv');
      res.setHeader('Content-Disposition', `attachment; filename=assinaturas-${petition.slug}.csv`);
      res.send(csv);
    } catch (error) {
      console.error('Erro ao exportar:', error);
      res.status(500).json({ error: 'Erro ao exportar assinaturas' });
    }
  }

  // Toggle status
  async toggleStatus(req, res) {
    try {
      const petition = await Petition.findByPk(req.params.id);
      if (!petition) {
        return res.status(404).json({ success: false, message: 'Petição não encontrada' });
      }

      const newStatus = petition.status === 'active' ? 'closed' : 'active';
      await petition.update({ status: newStatus });

      res.json({ success: true, status: newStatus });
    } catch (error) {
      res.status(500).json({ success: false, message: error.message });
    }
  }

  // Aprovar petição
  async approve(req, res) {
    try {
      const petition = await Petition.findByPk(req.params.id);
      if (!petition) {
        req.flash('error', 'Petição não encontrada.');
        return res.redirect('/admin/petitions');
      }

      const { admin_notes } = req.body;

      await petition.update({
        status: 'active',
        approved_at: new Date(),
        approved_by: req.session.user.id,
        admin_notes: admin_notes || null
      });

      req.flash('success', 'Petição aprovada e publicada com sucesso!');
      res.redirect('/admin/petitions');
    } catch (error) {
      console.error('Erro ao aprovar petição:', error);
      req.flash('error', 'Erro ao aprovar petição.');
      res.redirect('/admin/petitions');
    }
  }

  // Rejeitar petição
  async reject(req, res) {
    try {
      const petition = await Petition.findByPk(req.params.id);
      if (!petition) {
        req.flash('error', 'Petição não encontrada.');
        return res.redirect('/admin/petitions');
      }

      const { rejection_reason } = req.body;

      if (!rejection_reason) {
        req.flash('error', 'Informe o motivo da rejeição.');
        return res.redirect(`/admin/petitions/${req.params.id}/edit`);
      }

      await petition.update({
        status: 'rejected',
        rejection_reason
      });

      req.flash('success', 'Petição rejeitada.');
      res.redirect('/admin/petitions');
    } catch (error) {
      console.error('Erro ao rejeitar petição:', error);
      req.flash('error', 'Erro ao rejeitar petição.');
      res.redirect('/admin/petitions');
    }
  }

  // Ver petição pendente para revisão
  async review(req, res) {
    try {
      const petition = await Petition.findByPk(req.params.id, {
        include: [
          { model: User, as: 'author' },
          { model: Petitioner, as: 'petitioner' }
        ]
      });

      if (!petition) {
        req.flash('error', 'Petição não encontrada.');
        return res.redirect('/admin/petitions');
      }

      res.render('admin/petitions/review', {
        title: 'Revisar Petição',
        petition,
        categories: ['Meio Ambiente', 'Direitos Humanos', 'Educação', 'Saúde', 'Política', 'Economia', 'Segurança', 'Transporte', 'Cultura', 'Outros']
      });
    } catch (error) {
      console.error('Erro ao carregar petição:', error);
      req.flash('error', 'Erro ao carregar petição.');
      res.redirect('/admin/petitions');
    }
  }
}

module.exports = new PetitionController();
