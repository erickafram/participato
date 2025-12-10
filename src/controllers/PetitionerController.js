/**
 * Controller do Painel do Peticionário (Cidadão)
 * Área do usuário que cria petições
 */
const { Petition, PetitionSignature, Petitioner } = require('../models');
const { Op } = require('sequelize');
const bcrypt = require('bcryptjs');
const crypto = require('crypto');

class PetitionerController {
  // ==========================================
  // AUTENTICAÇÃO
  // ==========================================

  // Página de login - redireciona para login unificado
  showLogin(req, res) {
    res.redirect('/login');
  }

  // Processar login - redireciona para login unificado
  async login(req, res) {
    res.redirect('/login');
  }

  // Logout - redireciona para logout unificado
  logout(req, res) {
    res.redirect('/logout');
  }

  // ==========================================
  // CADASTRO (durante criação de petição)
  // ==========================================

  // Página de criar petição (com cadastro)
  showCreatePetition(req, res) {
    res.render('site/petitioner/create-petition', {
      title: 'Criar Petição',
      petitioner: req.session.petitioner || null
    });
  }

  // Processar criação de petição + cadastro
  async createPetition(req, res) {
    try {
      const { 
        name, email, phone, password,
        title, description, content, category, goal 
      } = req.body;

      let petitioner = null;

      // Se não está logado, criar conta
      if (!req.session.petitioner) {
        // Validações
        if (!name || !email || !phone || !password) {
          req.flash('error', 'Preencha todos os campos de cadastro.');
          return res.redirect('/criar-peticao');
        }

        if (password.length < 6) {
          req.flash('error', 'A senha deve ter pelo menos 6 caracteres.');
          return res.redirect('/criar-peticao');
        }

        // Verificar se email já existe
        const existing = await Petitioner.findOne({ 
          where: { email: email.toLowerCase().trim() } 
        });

        if (existing) {
          req.flash('error', 'Este email já está cadastrado. Faça login para continuar.');
          return res.redirect('/login');
        }

        // Criar peticionário
        petitioner = await Petitioner.create({
          name: name.trim(),
          email: email.toLowerCase().trim(),
          phone: phone.trim(),
          password_hash: password,
          verification_token: crypto.randomBytes(32).toString('hex')
        });

        // Criar sessão
        req.session.petitioner = {
          id: petitioner.id,
          name: petitioner.name,
          email: petitioner.email
        };
      } else {
        petitioner = await Petitioner.findByPk(req.session.petitioner.id);
      }

      // Validar dados da petição
      if (!title || !description) {
        req.flash('error', 'Título e descrição são obrigatórios.');
        return res.redirect('/criar-peticao');
      }

      // Gerar slug
      let slug = title.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');

      const existingSlug = await Petition.findOne({ where: { slug } });
      if (existingSlug) {
        slug = `${slug}-${Date.now()}`;
      }

      // Criar petição com status pendente
      const petition = await Petition.create({
        title: title.trim(),
        slug,
        description: description.trim(),
        content: content?.trim() || null,
        category: category || null,
        goal: parseInt(goal) || 1000,
        petitioner_id: petitioner.id,
        status: 'pending',
        image: req.file ? `/uploads/${req.file.filename}` : null
      });

      req.flash('success', 'Petição enviada com sucesso! Aguarde a aprovação do administrador.');
      res.redirect('/minha-conta');
    } catch (error) {
      console.error('Erro ao criar petição:', error);
      req.flash('error', 'Erro ao criar petição: ' + error.message);
      res.redirect('/criar-peticao');
    }
  }

  // ==========================================
  // PAINEL DO USUÁRIO
  // ==========================================

  // Dashboard
  async dashboard(req, res) {
    try {
      const petitioner = await Petitioner.findByPk(req.session.petitioner.id);
      
      const petitions = await Petition.findAll({
        where: { petitioner_id: petitioner.id },
        order: [['created_at', 'DESC']]
      });

      // Contar assinaturas de cada petição
      for (let petition of petitions) {
        petition.signatureCount = await PetitionSignature.count({
          where: { petition_id: petition.id }
        });
      }

      // Estatísticas
      const stats = {
        total: petitions.length,
        pending: petitions.filter(p => p.status === 'pending').length,
        active: petitions.filter(p => p.status === 'active').length,
        rejected: petitions.filter(p => p.status === 'rejected').length,
        totalSignatures: petitions.reduce((sum, p) => sum + p.signatureCount, 0)
      };

      res.render('site/petitioner/dashboard', {
        title: 'Minha Conta',
        petitioner,
        petitions,
        stats
      });
    } catch (error) {
      console.error('Erro no dashboard:', error);
      req.flash('error', 'Erro ao carregar dados.');
      res.redirect('/');
    }
  }

  // Ver detalhes de uma petição
  async viewPetition(req, res) {
    try {
      const petition = await Petition.findOne({
        where: { 
          id: req.params.id,
          petitioner_id: req.session.petitioner.id
        }
      });

      if (!petition) {
        req.flash('error', 'Petição não encontrada.');
        return res.redirect('/minha-conta');
      }

      const signatureCount = await PetitionSignature.count({
        where: { petition_id: petition.id }
      });

      const recentSignatures = await PetitionSignature.findAll({
        where: { petition_id: petition.id },
        order: [['created_at', 'DESC']],
        limit: 20
      });

      res.render('site/petitioner/petition-detail', {
        title: petition.title,
        petition,
        signatureCount,
        recentSignatures,
        progress: Math.min(100, Math.round((signatureCount / petition.goal) * 100))
      });
    } catch (error) {
      console.error('Erro ao ver petição:', error);
      req.flash('error', 'Erro ao carregar petição.');
      res.redirect('/minha-conta');
    }
  }

  // Editar perfil
  async showProfile(req, res) {
    const petitioner = await Petitioner.findByPk(req.session.petitioner.id);
    res.render('site/petitioner/profile', {
      title: 'Meu Perfil',
      petitioner
    });
  }

  async updateProfile(req, res) {
    try {
      const petitioner = await Petitioner.findByPk(req.session.petitioner.id);
      const { name, phone, current_password, new_password } = req.body;

      // Atualizar dados básicos
      await petitioner.update({
        name: name.trim(),
        phone: phone.trim()
      });

      // Atualizar senha se fornecida
      if (new_password) {
        if (!current_password) {
          req.flash('error', 'Informe a senha atual para alterar.');
          return res.redirect('/minha-conta/perfil');
        }

        const validPassword = await petitioner.checkPassword(current_password);
        if (!validPassword) {
          req.flash('error', 'Senha atual incorreta.');
          return res.redirect('/minha-conta/perfil');
        }

        if (new_password.length < 6) {
          req.flash('error', 'A nova senha deve ter pelo menos 6 caracteres.');
          return res.redirect('/minha-conta/perfil');
        }

        await petitioner.update({ password_hash: new_password });
      }

      // Atualizar sessão
      req.session.petitioner.name = petitioner.name;

      req.flash('success', 'Perfil atualizado com sucesso!');
      res.redirect('/minha-conta/perfil');
    } catch (error) {
      console.error('Erro ao atualizar perfil:', error);
      req.flash('error', 'Erro ao atualizar perfil.');
      res.redirect('/minha-conta/perfil');
    }
  }
}

module.exports = new PetitionerController();
