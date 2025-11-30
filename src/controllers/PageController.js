/**
 * Controller de Páginas
 * CRUD completo de páginas estáticas
 */
const { Page, User } = require('../models');
const { Op } = require('sequelize');
const slugify = require('slugify');

class PageController {
  // Listar todas as páginas (admin)
  async index(req, res) {
    try {
      const pages = await Page.findAll({
        include: [{ model: User, as: 'author', attributes: ['id', 'name'] }],
        order: [['order', 'ASC'], ['title', 'ASC']]
      });

      res.render('admin/pages/index', {
        title: 'Páginas',
        pages
      });
    } catch (error) {
      console.error('Erro ao listar páginas:', error);
      req.flash('error', 'Erro ao carregar páginas.');
      res.redirect('/admin');
    }
  }

  // Exibir formulário de criação
  async create(req, res) {
    res.render('admin/pages/form', {
      title: 'Nova Página',
      page: null,
      isEdit: false
    });
  }

  // Salvar nova página
  async store(req, res) {
    try {
      const {
        title,
        slug,
        content,
        status,
        template,
        order,
        show_in_menu,
        meta_title,
        meta_description
      } = req.body;

      // Gerar slug se não fornecido
      let pageSlug = slug || slugify(title, { lower: true, strict: true });
      
      // Verificar se slug já existe
      const existingPage = await Page.findOne({ where: { slug: pageSlug } });
      if (existingPage) {
        pageSlug = `${pageSlug}-${Date.now()}`;
      }

      await Page.create({
        title,
        slug: pageSlug,
        content,
        status: status || 'draft',
        template: template || 'default',
        order: order || 0,
        show_in_menu: show_in_menu === 'on' || show_in_menu === true,
        meta_title,
        meta_description,
        featured_image: req.processedFile ? req.processedFile.url : null,
        author_id: req.session.user.id
      });

      req.flash('success', 'Página criada com sucesso!');
      res.redirect('/admin/pages');
    } catch (error) {
      console.error('Erro ao criar página:', error);
      req.flash('error', 'Erro ao criar página: ' + error.message);
      res.redirect('/admin/pages/create');
    }
  }

  // Exibir formulário de edição
  async edit(req, res) {
    try {
      const page = await Page.findByPk(req.params.id, {
        include: [{ model: User, as: 'author' }]
      });

      if (!page) {
        req.flash('error', 'Página não encontrada.');
        return res.redirect('/admin/pages');
      }

      res.render('admin/pages/form', {
        title: 'Editar Página',
        page,
        isEdit: true
      });
    } catch (error) {
      console.error('Erro ao carregar página:', error);
      req.flash('error', 'Erro ao carregar página.');
      res.redirect('/admin/pages');
    }
  }

  // Atualizar página
  async update(req, res) {
    try {
      const page = await Page.findByPk(req.params.id);

      if (!page) {
        req.flash('error', 'Página não encontrada.');
        return res.redirect('/admin/pages');
      }

      const {
        title,
        slug,
        content,
        status,
        template,
        order,
        show_in_menu,
        meta_title,
        meta_description
      } = req.body;

      // Verificar slug único (se alterado)
      if (slug && slug !== page.slug) {
        const existingPage = await Page.findOne({ 
          where: { slug, id: { [Op.ne]: page.id } } 
        });
        if (existingPage) {
          req.flash('error', 'Este slug já está em uso.');
          return res.redirect(`/admin/pages/${page.id}/edit`);
        }
      }

      // Atualizar campos
      page.title = title;
      page.slug = slug || page.slug;
      page.content = content;
      page.status = status || 'draft';
      page.template = template || 'default';
      page.order = order || 0;
      page.show_in_menu = show_in_menu === 'on' || show_in_menu === true;
      page.meta_title = meta_title;
      page.meta_description = meta_description;

      // Atualizar imagem se enviada
      if (req.processedFile) {
        page.featured_image = req.processedFile.url;
      }

      await page.save();

      req.flash('success', 'Página atualizada com sucesso!');
      res.redirect('/admin/pages');
    } catch (error) {
      console.error('Erro ao atualizar página:', error);
      req.flash('error', 'Erro ao atualizar página: ' + error.message);
      res.redirect(`/admin/pages/${req.params.id}/edit`);
    }
  }

  // Excluir página
  async destroy(req, res) {
    try {
      const page = await Page.findByPk(req.params.id);

      if (!page) {
        req.flash('error', 'Página não encontrada.');
        return res.redirect('/admin/pages');
      }

      await page.destroy();

      req.flash('success', 'Página excluída com sucesso!');
      res.redirect('/admin/pages');
    } catch (error) {
      console.error('Erro ao excluir página:', error);
      req.flash('error', 'Erro ao excluir página.');
      res.redirect('/admin/pages');
    }
  }

  // Alternar status de publicação
  async toggleStatus(req, res) {
    try {
      const page = await Page.findByPk(req.params.id);

      if (!page) {
        return res.json({ success: false, message: 'Página não encontrada.' });
      }

      page.status = page.status === 'published' ? 'draft' : 'published';
      await page.save();

      return res.json({ 
        success: true, 
        status: page.status,
        message: page.status === 'published' ? 'Página publicada!' : 'Página despublicada!'
      });
    } catch (error) {
      console.error('Erro ao alternar status:', error);
      return res.json({ success: false, message: 'Erro ao alternar status.' });
    }
  }
}

module.exports = new PageController();
