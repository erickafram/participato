/**
 * Controller de Banners
 * CRUD completo de banners publicitários
 */
const { Banner } = require('../models');
const { Op } = require('sequelize');

class BannerController {
  // Listar todos os banners (admin)
  async index(req, res) {
    try {
      const banners = await Banner.findAll({
        order: [['position', 'ASC'], ['order', 'ASC']]
      });

      // Agrupar por posição para melhor visualização
      const positions = {
        'home_top': 'Home - Topo',
        'home_middle': 'Home - Meio',
        'home_bottom': 'Home - Rodapé',
        'home_sidebar': 'Home - Sidebar',
        'post_top': 'Post - Topo',
        'post_middle': 'Post - Meio',
        'post_bottom': 'Post - Rodapé',
        'post_sidebar': 'Post - Sidebar',
        'category_top': 'Categoria - Topo',
        'category_bottom': 'Categoria - Rodapé',
        'category_sidebar': 'Categoria - Sidebar'
      };

      const sizes = {
        '728x90': 'Leaderboard (728x90)',
        '300x250': 'Medium Rectangle (300x250)',
        '336x280': 'Large Rectangle (336x280)',
        '300x600': 'Half Page (300x600)',
        '320x100': 'Large Mobile (320x100)',
        '970x90': 'Large Leaderboard (970x90)',
        '970x250': 'Billboard (970x250)',
        '160x600': 'Wide Skyscraper (160x600)',
        '300x50': 'Mobile Banner (300x50)',
        'responsive': 'Responsivo (100%)'
      };

      res.render('admin/banners/index', {
        title: 'Banners',
        banners,
        positions,
        sizes
      });
    } catch (error) {
      console.error('Erro ao listar banners:', error);
      req.flash('error', 'Erro ao carregar banners.');
      res.redirect('/admin');
    }
  }

  // Exibir formulário de criação
  async create(req, res) {
    const positions = {
      'home_top': 'Home - Topo',
      'home_middle': 'Home - Meio',
      'home_bottom': 'Home - Rodapé',
      'home_sidebar': 'Home - Sidebar',
      'post_top': 'Post - Topo',
      'post_middle': 'Post - Meio',
      'post_bottom': 'Post - Rodapé',
      'post_sidebar': 'Post - Sidebar',
      'category_top': 'Categoria - Topo',
      'category_bottom': 'Categoria - Rodapé',
      'category_sidebar': 'Categoria - Sidebar'
    };

    const sizes = {
      '728x90': 'Leaderboard (728x90)',
      '300x250': 'Medium Rectangle (300x250)',
      '336x280': 'Large Rectangle (336x280)',
      '300x600': 'Half Page (300x600)',
      '320x100': 'Large Mobile (320x100)',
      '970x90': 'Large Leaderboard (970x90)',
      '970x250': 'Billboard (970x250)',
      '160x600': 'Wide Skyscraper (160x600)',
      '300x50': 'Mobile Banner (300x50)',
      'responsive': 'Responsivo (100%)'
    };

    res.render('admin/banners/form', {
      title: 'Novo Banner',
      banner: null,
      positions,
      sizes,
      isEdit: false
    });
  }

  // Salvar novo banner
  async store(req, res) {
    try {
      const { 
        title, 
        link, 
        position, 
        size, 
        alt_text, 
        target, 
        order, 
        start_date, 
        end_date, 
        active 
      } = req.body;

      // Verificar se tem imagem
      if (!req.processedFile) {
        req.flash('error', 'A imagem do banner é obrigatória.');
        return res.redirect('/admin/banners/create');
      }

      await Banner.create({
        title,
        image: req.processedFile.url,
        link: link || null,
        position,
        size,
        alt_text: alt_text || title,
        target: target || '_blank',
        order: order || 0,
        start_date: start_date || null,
        end_date: end_date || null,
        active: active === 'on' || active === true
      });

      req.flash('success', 'Banner criado com sucesso!');
      res.redirect('/admin/banners');
    } catch (error) {
      console.error('Erro ao criar banner:', error);
      req.flash('error', 'Erro ao criar banner: ' + error.message);
      res.redirect('/admin/banners/create');
    }
  }

  // Exibir formulário de edição
  async edit(req, res) {
    try {
      const banner = await Banner.findByPk(req.params.id);

      if (!banner) {
        req.flash('error', 'Banner não encontrado.');
        return res.redirect('/admin/banners');
      }

      const positions = {
        'home_top': 'Home - Topo',
        'home_middle': 'Home - Meio',
        'home_bottom': 'Home - Rodapé',
        'home_sidebar': 'Home - Sidebar',
        'post_top': 'Post - Topo',
        'post_middle': 'Post - Meio',
        'post_bottom': 'Post - Rodapé',
        'post_sidebar': 'Post - Sidebar',
        'category_top': 'Categoria - Topo',
        'category_bottom': 'Categoria - Rodapé',
        'category_sidebar': 'Categoria - Sidebar'
      };

      const sizes = {
        '728x90': 'Leaderboard (728x90)',
        '300x250': 'Medium Rectangle (300x250)',
        '336x280': 'Large Rectangle (336x280)',
        '300x600': 'Half Page (300x600)',
        '320x100': 'Large Mobile (320x100)',
        '970x90': 'Large Leaderboard (970x90)',
        '970x250': 'Billboard (970x250)',
        '160x600': 'Wide Skyscraper (160x600)',
        '300x50': 'Mobile Banner (300x50)',
        'responsive': 'Responsivo (100%)'
      };

      res.render('admin/banners/form', {
        title: 'Editar Banner',
        banner,
        positions,
        sizes,
        isEdit: true
      });
    } catch (error) {
      console.error('Erro ao carregar banner:', error);
      req.flash('error', 'Erro ao carregar banner.');
      res.redirect('/admin/banners');
    }
  }

  // Atualizar banner
  async update(req, res) {
    try {
      const banner = await Banner.findByPk(req.params.id);

      if (!banner) {
        req.flash('error', 'Banner não encontrado.');
        return res.redirect('/admin/banners');
      }

      const { 
        title, 
        link, 
        position, 
        size, 
        alt_text, 
        target, 
        order, 
        start_date, 
        end_date, 
        active 
      } = req.body;

      // Atualizar campos
      banner.title = title;
      banner.link = link || null;
      banner.position = position;
      banner.size = size;
      banner.alt_text = alt_text || title;
      banner.target = target || '_blank';
      banner.order = order || 0;
      banner.start_date = start_date || null;
      banner.end_date = end_date || null;
      banner.active = active === 'on' || active === true;

      // Atualizar imagem se foi enviada nova
      if (req.processedFile) {
        banner.image = req.processedFile.url;
      }

      await banner.save();

      req.flash('success', 'Banner atualizado com sucesso!');
      res.redirect('/admin/banners');
    } catch (error) {
      console.error('Erro ao atualizar banner:', error);
      req.flash('error', 'Erro ao atualizar banner: ' + error.message);
      res.redirect(`/admin/banners/${req.params.id}/edit`);
    }
  }

  // Excluir banner
  async destroy(req, res) {
    try {
      const banner = await Banner.findByPk(req.params.id);

      if (!banner) {
        req.flash('error', 'Banner não encontrado.');
        return res.redirect('/admin/banners');
      }

      await banner.destroy();

      req.flash('success', 'Banner excluído com sucesso!');
      res.redirect('/admin/banners');
    } catch (error) {
      console.error('Erro ao excluir banner:', error);
      req.flash('error', 'Erro ao excluir banner.');
      res.redirect('/admin/banners');
    }
  }

  // Alternar status ativo
  async toggleActive(req, res) {
    try {
      const banner = await Banner.findByPk(req.params.id);

      if (!banner) {
        return res.json({ success: false, message: 'Banner não encontrado.' });
      }

      banner.active = !banner.active;
      await banner.save();

      return res.json({ 
        success: true, 
        active: banner.active,
        message: banner.active ? 'Banner ativado!' : 'Banner desativado!'
      });
    } catch (error) {
      console.error('Erro ao alternar status:', error);
      return res.json({ success: false, message: 'Erro ao alternar status.' });
    }
  }

  // Registrar clique no banner (API pública)
  async registerClick(req, res) {
    try {
      const banner = await Banner.findByPk(req.params.id);

      if (!banner) {
        return res.status(404).json({ success: false });
      }

      await banner.incrementClicks();
      
      return res.json({ success: true });
    } catch (error) {
      console.error('Erro ao registrar clique:', error);
      return res.status(500).json({ success: false });
    }
  }
}

module.exports = new BannerController();
