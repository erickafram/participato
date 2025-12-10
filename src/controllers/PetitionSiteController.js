/**
 * Controller de Petições (Site Público)
 * Exibição e assinatura de petições
 */
const { Petition, PetitionSignature, User, Setting } = require('../models');
const { Op } = require('sequelize');
const crypto = require('crypto');

class PetitionSiteController {
  // Listar petições públicas
  async index(req, res) {
    try {
      const { category, search, page = 1 } = req.query;
      const limit = 12;
      const offset = (page - 1) * limit;

      const where = { status: 'active' };
      if (category) where.category = category;
      if (search) {
        where[Op.or] = [
          { title: { [Op.like]: `%${search}%` } },
          { description: { [Op.like]: `%${search}%` } }
        ];
      }

      const { count, rows: petitions } = await Petition.findAndCountAll({
        where,
        order: [['featured', 'DESC'], ['created_at', 'DESC']],
        limit,
        offset
      });

      // Contar assinaturas
      for (let petition of petitions) {
        petition.signatureCount = await PetitionSignature.count({
          where: { petition_id: petition.id }
        });
      }

      const totalPages = Math.ceil(count / limit);
      const petitionCategories = ['Meio Ambiente', 'Direitos Humanos', 'Educação', 'Saúde', 'Política', 'Economia', 'Outros'];

      res.render('site/petitions/index', {
        title: 'Petições',
        petitions,
        currentPage: parseInt(page),
        totalPages,
        total: count,
        category,
        search,
        petitionCategories
      });
    } catch (error) {
      console.error('Erro ao listar petições:', error);
      res.status(500).render('site/error', {
        title: 'Erro',
        message: 'Erro ao carregar petições.'
      });
    }
  }

  // Exibir petição individual
  async show(req, res) {
    try {
      const petition = await Petition.findOne({
        where: { slug: req.params.slug, status: { [Op.in]: ['active', 'victory', 'closed'] } },
        include: [{ model: User, as: 'author', attributes: ['id', 'name'] }]
      });

      if (!petition) {
        return res.status(404).render('site/404', { title: 'Petição não encontrada' });
      }

      // Incrementar views
      await petition.increment('views');

      // Contar assinaturas
      const signatureCount = await PetitionSignature.count({
        where: { petition_id: petition.id }
      });

      // Últimas assinaturas públicas
      let recentSignatures = [];
      if (petition.show_signatures) {
        recentSignatures = await PetitionSignature.findAll({
          where: { petition_id: petition.id, is_public: true },
          order: [['created_at', 'DESC']],
          limit: 20
        });
      }

      // Verificar se usuário logado já assinou
      let alreadySigned = false;
      if (req.session.user) {
        const existing = await PetitionSignature.findOne({
          where: { petition_id: petition.id, user_id: req.session.user.id }
        });
        alreadySigned = !!existing;
      }

      const progress = Math.min(100, Math.round((signatureCount / petition.goal) * 100));

      // Função helper para calcular tempo relativo
      const timeAgo = (date) => {
        const seconds = Math.floor((new Date() - new Date(date)) / 1000);
        const intervals = { ano: 31536000, mês: 2592000, semana: 604800, dia: 86400, hora: 3600, minuto: 60 };
        for (const [unit, value] of Object.entries(intervals)) {
          const count = Math.floor(seconds / value);
          if (count >= 1) return `há ${count} ${unit}${count > 1 ? (unit === 'mês' ? 'es' : 's') : ''}`;
        }
        return 'agora';
      };

      res.render('site/petitions/show', {
        title: petition.title,
        petition,
        signatureCount,
        recentSignatures,
        alreadySigned,
        progress,
        timeAgo
      });
    } catch (error) {
      console.error('Erro ao exibir petição:', error);
      res.status(500).render('site/error', {
        title: 'Erro',
        message: 'Erro ao carregar petição.'
      });
    }
  }

  // Assinar petição
  async sign(req, res) {
    try {
      const petition = await Petition.findOne({
        where: { slug: req.params.slug, status: 'active' }
      });

      if (!petition) {
        return res.status(404).json({ success: false, message: 'Petição não encontrada ou encerrada.' });
      }

      const { name, email, city, state, comment, is_public } = req.body;

      // Validações
      if (!name || !email) {
        return res.status(400).json({ success: false, message: 'Nome e email são obrigatórios.' });
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        return res.status(400).json({ success: false, message: 'Email inválido.' });
      }

      // Verificar se já assinou
      const existing = await PetitionSignature.findOne({
        where: { petition_id: petition.id, email: email.toLowerCase() }
      });

      if (existing) {
        return res.status(400).json({ success: false, message: 'Este email já assinou esta petição.' });
      }

      // Criar assinatura
      const signature = await PetitionSignature.create({
        petition_id: petition.id,
        user_id: req.session.user?.id || null,
        name: name.trim(),
        email: email.toLowerCase().trim(),
        city: city?.trim() || null,
        state: state?.trim() || null,
        comment: comment?.trim() || null,
        is_public: is_public !== 'false',
        verified: !!req.session.user,
        verification_token: !req.session.user ? crypto.randomBytes(32).toString('hex') : null,
        ip_address: req.ip
      });

      // Contar total de assinaturas
      const signatureCount = await PetitionSignature.count({
        where: { petition_id: petition.id }
      });

      res.json({
        success: true,
        message: 'Obrigado por assinar! Sua voz faz a diferença.',
        signatureCount,
        progress: Math.min(100, Math.round((signatureCount / petition.goal) * 100))
      });
    } catch (error) {
      console.error('Erro ao assinar petição:', error);
      res.status(500).json({ success: false, message: 'Erro ao processar assinatura.' });
    }
  }

  // Carregar mais assinaturas (AJAX)
  async loadSignatures(req, res) {
    try {
      const petition = await Petition.findOne({ where: { slug: req.params.slug } });
      if (!petition || !petition.show_signatures) {
        return res.json({ signatures: [] });
      }

      const { page = 1 } = req.query;
      const limit = 20;
      const offset = (page - 1) * limit;

      const signatures = await PetitionSignature.findAll({
        where: { petition_id: petition.id, is_public: true },
        order: [['created_at', 'DESC']],
        limit,
        offset,
        attributes: ['name', 'city', 'state', 'comment', 'created_at']
      });

      res.json({ signatures });
    } catch (error) {
      res.status(500).json({ signatures: [], error: 'Erro ao carregar assinaturas' });
    }
  }
}

module.exports = new PetitionSiteController();
