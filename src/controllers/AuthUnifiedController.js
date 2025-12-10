/**
 * Controller de Autenticação Unificada
 * Login único para admin/editor e cidadãos
 */
const { User, Petitioner } = require('../models');

class AuthUnifiedController {
  // Página de login unificado
  showLogin(req, res) {
    // Se já está logado, redirecionar
    if (req.session.user) {
      return res.redirect('/admin');
    }
    if (req.session.petitioner) {
      return res.redirect('/minha-conta');
    }

    res.render('site/auth/login', {
      title: 'Entrar'
    });
  }

  // Processar login unificado
  async login(req, res) {
    try {
      const { email, password } = req.body;
      const emailLower = email.toLowerCase().trim();

      // Primeiro, tentar como admin/editor
      const user = await User.findOne({ where: { email: emailLower } });
      
      if (user) {
        if (!user.active) {
          req.flash('error', 'Sua conta está desativada.');
          return res.redirect('/login');
        }

        const validPassword = await user.checkPassword(password);
        if (validPassword) {
          // Atualizar último login
          await user.update({ last_login: new Date() });

          // Criar sessão de admin/editor
          req.session.user = {
            id: user.id,
            name: user.name,
            email: user.email,
            role: user.role,
            avatar: user.avatar
          };

          req.flash('success', `Bem-vindo(a), ${user.name}!`);
          return res.redirect('/admin');
        }
      }

      // Se não encontrou como admin, tentar como cidadão
      const petitioner = await Petitioner.findOne({ where: { email: emailLower } });

      if (petitioner) {
        if (!petitioner.active) {
          req.flash('error', 'Sua conta está desativada.');
          return res.redirect('/login');
        }

        const validPassword = await petitioner.checkPassword(password);
        if (validPassword) {
          // Atualizar último login
          await petitioner.update({ last_login: new Date() });

          // Criar sessão de cidadão
          req.session.petitioner = {
            id: petitioner.id,
            name: petitioner.name,
            email: petitioner.email
          };

          req.flash('success', `Bem-vindo(a), ${petitioner.name}!`);
          return res.redirect('/minha-conta');
        }
      }

      // Nenhum usuário encontrado ou senha incorreta
      req.flash('error', 'Email ou senha incorretos.');
      return res.redirect('/login');

    } catch (error) {
      console.error('Erro no login:', error);
      req.flash('error', 'Erro ao fazer login. Tente novamente.');
      return res.redirect('/login');
    }
  }

  // Logout unificado
  logout(req, res) {
    req.session.user = null;
    req.session.petitioner = null;
    req.flash('success', 'Você saiu da sua conta.');
    res.redirect('/');
  }
}

module.exports = new AuthUnifiedController();
