/**
 * Controller de Autenticação
 * Gerencia login, logout e sessões
 */
const { User } = require('../models');

class AuthController {
  // Exibir página de login
  async showLogin(req, res) {
    res.render('admin/layouts/auth', {
      title: 'Login - Painel Administrativo',
      error: req.flash('error'),
      success: req.flash('success')
    });
  }

  // Processar login
  async login(req, res) {
    try {
      const { email, password } = req.body;

      // Buscar usuário pelo email
      const user = await User.findOne({ where: { email } });

      if (!user) {
        req.flash('error', 'Email ou senha incorretos.');
        return res.redirect('/admin/login');
      }

      // Verificar se o usuário está ativo
      if (!user.active) {
        req.flash('error', 'Sua conta está desativada. Entre em contato com o administrador.');
        return res.redirect('/admin/login');
      }

      // Verificar senha
      const isValidPassword = await user.checkPassword(password);
      if (!isValidPassword) {
        req.flash('error', 'Email ou senha incorretos.');
        return res.redirect('/admin/login');
      }

      // Atualizar último login
      user.last_login = new Date();
      await user.save();

      // Criar sessão
      req.session.user = {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role,
        avatar: user.avatar
      };

      req.flash('success', `Bem-vindo(a), ${user.name}!`);
      return res.redirect('/admin');
    } catch (error) {
      console.error('Erro no login:', error);
      req.flash('error', 'Ocorreu um erro ao fazer login. Tente novamente.');
      return res.redirect('/admin/login');
    }
  }

  // Processar logout
  async logout(req, res) {
    req.session.destroy((err) => {
      if (err) {
        console.error('Erro ao fazer logout:', err);
      }
      res.redirect('/admin/login');
    });
  }

  // Exibir perfil do usuário
  async showProfile(req, res) {
    try {
      const user = await User.findByPk(req.session.user.id);
      
      res.render('admin/auth/profile', {
        title: 'Meu Perfil',
        profile: user
      });
    } catch (error) {
      console.error('Erro ao carregar perfil:', error);
      req.flash('error', 'Erro ao carregar perfil.');
      res.redirect('/admin');
    }
  }

  // Atualizar perfil
  async updateProfile(req, res) {
    try {
      const { name, email, password, bio } = req.body;
      const user = await User.findByPk(req.session.user.id);

      // Verificar se email já existe (se foi alterado)
      if (email !== user.email) {
        const existingUser = await User.findOne({ where: { email } });
        if (existingUser) {
          req.flash('error', 'Este email já está em uso.');
          return res.redirect('/admin/profile');
        }
      }

      // Atualizar dados
      user.name = name;
      user.email = email;
      user.bio = bio;

      // Atualizar senha se fornecida
      if (password && password.length >= 6) {
        user.password_hash = password;
      }

      // Atualizar avatar se enviado
      if (req.processedFile) {
        user.avatar = req.processedFile.url;
      }

      await user.save();

      // Atualizar sessão
      req.session.user.name = user.name;
      req.session.user.email = user.email;
      req.session.user.avatar = user.avatar;

      req.flash('success', 'Perfil atualizado com sucesso!');
      res.redirect('/admin/profile');
    } catch (error) {
      console.error('Erro ao atualizar perfil:', error);
      req.flash('error', 'Erro ao atualizar perfil.');
      res.redirect('/admin/profile');
    }
  }
}

module.exports = new AuthController();
