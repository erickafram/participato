/**
 * Controller de Categorias
 * CRUD completo de categorias
 */
const { Category, Post } = require('../models');
const { Op } = require('sequelize');
const slugify = require('slugify');

class CategoryController {
  // Listar todas as categorias (admin)
  async index(req, res) {
    try {
      // Buscar categorias principais (sem parent_id)
      const mainCategories = await Category.findAll({
        where: { parent_id: null },
        order: [['order', 'ASC'], ['name', 'ASC']],
        include: [
          {
            model: Post,
            as: 'posts',
            attributes: ['id']
          },
          {
            model: Category,
            as: 'children',
            include: [{ model: Post, as: 'posts', attributes: ['id'] }],
            order: [['order', 'ASC'], ['name', 'ASC']]
          }
        ]
      });

      // Organizar categorias com subcategorias logo abaixo
      const organizedCategories = [];
      mainCategories.forEach(cat => {
        const catData = {
          ...cat.toJSON(),
          postCount: cat.posts ? cat.posts.length : 0
        };
        organizedCategories.push(catData);
        
        // Adicionar subcategorias logo após a categoria pai
        if (cat.children && cat.children.length > 0) {
          const sortedChildren = cat.children.sort((a, b) => {
            if (a.order !== b.order) return a.order - b.order;
            return a.name.localeCompare(b.name);
          });
          sortedChildren.forEach(child => {
            organizedCategories.push({
              ...child.toJSON(),
              postCount: child.posts ? child.posts.length : 0,
              parent: { id: cat.id, name: cat.name, color: cat.color }
            });
          });
        }
      });

      res.render('admin/categories/index', {
        title: 'Categorias',
        categories: organizedCategories
      });
    } catch (error) {
      console.error('Erro ao listar categorias:', error);
      req.flash('error', 'Erro ao carregar categorias.');
      res.redirect('/admin');
    }
  }

  // Reordenar categorias via drag-and-drop
  async reorder(req, res) {
    try {
      const { items, subItems } = req.body;
      
      // Atualizar ordem das categorias principais
      if (items && Array.isArray(items)) {
        for (let i = 0; i < items.length; i++) {
          await Category.update(
            { order: i },
            { where: { id: items[i] } }
          );
        }
      }

      // Atualizar ordem das subcategorias
      if (subItems && typeof subItems === 'object') {
        for (const parentId in subItems) {
          const subs = subItems[parentId];
          if (Array.isArray(subs)) {
            for (let i = 0; i < subs.length; i++) {
              await Category.update(
                { order: i },
                { where: { id: subs[i] } }
              );
            }
          }
        }
      }

      return res.json({ success: true, message: 'Ordem atualizada!' });
    } catch (error) {
      console.error('Erro ao reordenar:', error);
      return res.json({ success: false, message: 'Erro ao reordenar.' });
    }
  }

  // Exibir formulário de criação
  async create(req, res) {
    try {
      // Buscar apenas categorias principais (sem parent_id) para serem opções de pai
      const parentCategories = await Category.findAll({
        where: { parent_id: null, active: true },
        order: [['order', 'ASC'], ['name', 'ASC']]
      });

      res.render('admin/categories/form', {
        title: 'Nova Categoria',
        category: null,
        parentCategories,
        isEdit: false
      });
    } catch (error) {
      console.error('Erro ao carregar formulário:', error);
      req.flash('error', 'Erro ao carregar formulário.');
      res.redirect('/admin/categories');
    }
  }

  // Salvar nova categoria
  async store(req, res) {
    try {
      const { name, slug, description, color, icon, order, active, parent_id } = req.body;

      // Gerar slug se não fornecido
      let categorySlug = slug || slugify(name, { lower: true, strict: true });

      // Verificar se slug já existe
      const existingCategory = await Category.findOne({ where: { slug: categorySlug } });
      if (existingCategory) {
        req.flash('error', 'Este slug já está em uso.');
        return res.redirect('/admin/categories/create');
      }

      await Category.create({
        name,
        slug: categorySlug,
        description,
        color: color || '#3ba4ff',
        icon,
        order: order || 0,
        active: active === 'on' || active === true,
        parent_id: parent_id || null
      });

      req.flash('success', 'Categoria criada com sucesso!');
      res.redirect('/admin/categories');
    } catch (error) {
      console.error('Erro ao criar categoria:', error);
      req.flash('error', 'Erro ao criar categoria: ' + error.message);
      res.redirect('/admin/categories/create');
    }
  }

  // Exibir formulário de edição
  async edit(req, res) {
    try {
      const category = await Category.findByPk(req.params.id);

      if (!category) {
        req.flash('error', 'Categoria não encontrada.');
        return res.redirect('/admin/categories');
      }

      // Buscar categorias principais que não sejam a própria categoria
      // (para evitar que uma categoria seja pai dela mesma)
      const parentCategories = await Category.findAll({
        where: {
          parent_id: null,
          active: true,
          id: { [Op.ne]: category.id }
        },
        order: [['order', 'ASC'], ['name', 'ASC']]
      });

      res.render('admin/categories/form', {
        title: 'Editar Categoria',
        category,
        parentCategories,
        isEdit: true
      });
    } catch (error) {
      console.error('Erro ao carregar categoria:', error);
      req.flash('error', 'Erro ao carregar categoria.');
      res.redirect('/admin/categories');
    }
  }

  // Atualizar categoria
  async update(req, res) {
    try {
      const category = await Category.findByPk(req.params.id);

      if (!category) {
        req.flash('error', 'Categoria não encontrada.');
        return res.redirect('/admin/categories');
      }

      const { name, slug, description, color, icon, order, active, parent_id } = req.body;

      // Verificar slug único (se alterado)
      if (slug && slug !== category.slug) {
        const existingCategory = await Category.findOne({
          where: { slug, id: { [Op.ne]: category.id } }
        });
        if (existingCategory) {
          req.flash('error', 'Este slug já está em uso.');
          return res.redirect(`/admin/categories/${category.id}/edit`);
        }
      }

      // Atualizar campos
      category.name = name;
      category.slug = slug || category.slug;
      category.description = description;
      category.color = color || '#3ba4ff';
      category.icon = icon;
      category.order = order || 0;
      category.active = active === 'on' || active === true;
      category.parent_id = parent_id || null;

      await category.save();

      req.flash('success', 'Categoria atualizada com sucesso!');
      res.redirect('/admin/categories');
    } catch (error) {
      console.error('Erro ao atualizar categoria:', error);
      req.flash('error', 'Erro ao atualizar categoria: ' + error.message);
      res.redirect(`/admin/categories/${req.params.id}/edit`);
    }
  }

  // Excluir categoria
  async destroy(req, res) {
    try {
      const category = await Category.findByPk(req.params.id, {
        include: [{ model: Post, as: 'posts' }]
      });

      if (!category) {
        req.flash('error', 'Categoria não encontrada.');
        return res.redirect('/admin/categories');
      }

      // Verificar se há posts vinculados
      if (category.posts && category.posts.length > 0) {
        req.flash('error', `Não é possível excluir. Existem ${category.posts.length} posts vinculados a esta categoria.`);
        return res.redirect('/admin/categories');
      }

      await category.destroy();

      req.flash('success', 'Categoria excluída com sucesso!');
      res.redirect('/admin/categories');
    } catch (error) {
      console.error('Erro ao excluir categoria:', error);
      req.flash('error', 'Erro ao excluir categoria.');
      res.redirect('/admin/categories');
    }
  }

  // Alternar status ativo
  async toggleActive(req, res) {
    try {
      const category = await Category.findByPk(req.params.id);

      if (!category) {
        return res.json({ success: false, message: 'Categoria não encontrada.' });
      }

      category.active = !category.active;
      await category.save();

      return res.json({
        success: true,
        active: category.active,
        message: category.active ? 'Categoria ativada!' : 'Categoria desativada!'
      });
    } catch (error) {
      console.error('Erro ao alternar status:', error);
      return res.json({ success: false, message: 'Erro ao alternar status.' });
    }
  }
}

module.exports = new CategoryController();
