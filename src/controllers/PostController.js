/**
 * Controller de Posts
 * CRUD completo de posts/notícias
 */
const { Post, Category, User } = require('../models');
const { Op } = require('sequelize');
const slugify = require('slugify');

class PostController {
  // Listar todos os posts (admin)
  async index(req, res) {
    try {
      const page = parseInt(req.query.page) || 1;
      const limit = 15;
      const offset = (page - 1) * limit;
      
      // Filtros
      const where = {};
      if (req.query.status) {
        where.status = req.query.status;
      }
      if (req.query.category) {
        where.category_id = req.query.category;
      }
      if (req.query.search) {
        where[Op.or] = [
          { title: { [Op.like]: `%${req.query.search}%` } },
          { content: { [Op.like]: `%${req.query.search}%` } }
        ];
      }

      const { count, rows: posts } = await Post.findAndCountAll({
        where,
        include: [
          { model: User, as: 'author', attributes: ['id', 'name'] },
          { model: Category, as: 'category', attributes: ['id', 'name', 'color'] }
        ],
        order: [['created_at', 'DESC']],
        limit,
        offset
      });

      const totalPages = Math.ceil(count / limit);
      const categories = await Category.findAll({ where: { active: true } });

      res.render('admin/posts/index', {
        title: 'Posts',
        posts,
        categories,
        pagination: {
          page,
          totalPages,
          total: count,
          hasNext: page < totalPages,
          hasPrev: page > 1
        },
        filters: req.query
      });
    } catch (error) {
      console.error('Erro ao listar posts:', error);
      req.flash('error', 'Erro ao carregar posts.');
      res.redirect('/admin');
    }
  }

  // Exibir formulário de criação
  async create(req, res) {
    try {
      const categories = await Category.findAll({ 
        where: { active: true },
        order: [['name', 'ASC']]
      });

      res.render('admin/posts/form', {
        title: 'Novo Post',
        post: null,
        categories,
        isEdit: false
      });
    } catch (error) {
      console.error('Erro ao carregar formulário:', error);
      req.flash('error', 'Erro ao carregar formulário.');
      res.redirect('/admin/posts');
    }
  }

  // Salvar novo post
  async store(req, res) {
    try {
      const {
        title,
        subtitle,
        slug,
        content,
        embed_code,
        excerpt,
        category_id,
        tags,
        status,
        featured,
        meta_title,
        meta_description,
        scheduled_at,
        featured_image_url
      } = req.body;

      // Gerar slug se não fornecido
      let postSlug = slug || slugify(title, { lower: true, strict: true });
      
      // Verificar se slug já existe
      const existingPost = await Post.findOne({ where: { slug: postSlug } });
      if (existingPost) {
        postSlug = `${postSlug}-${Date.now()}`;
      }

      // Determinar imagem destacada: upload tem prioridade, depois URL
      let featuredImage = null;
      if (req.processedFile) {
        featuredImage = req.processedFile.url;
      } else if (featured_image_url) {
        featuredImage = featured_image_url;
      }

      const post = await Post.create({
        title,
        subtitle,
        slug: postSlug,
        content,
        embed_code: embed_code || null,
        excerpt,
        category_id: category_id || null,
        tags: tags || '',
        status: status || 'draft',
        featured: featured === 'on' || featured === true,
        meta_title,
        meta_description,
        scheduled_at: scheduled_at || null,
        featured_image: featuredImage,
        author_id: req.session.user.id
      });

      req.flash('success', 'Post criado com sucesso!');
      res.redirect('/admin/posts');
    } catch (error) {
      console.error('Erro ao criar post:', error);
      req.flash('error', 'Erro ao criar post: ' + error.message);
      res.redirect('/admin/posts/create');
    }
  }

  // Exibir formulário de edição
  async edit(req, res) {
    try {
      const post = await Post.findByPk(req.params.id, {
        include: [
          { model: Category, as: 'category' },
          { model: User, as: 'author' }
        ]
      });

      if (!post) {
        req.flash('error', 'Post não encontrado.');
        return res.redirect('/admin/posts');
      }

      const categories = await Category.findAll({ 
        where: { active: true },
        order: [['name', 'ASC']]
      });

      res.render('admin/posts/form', {
        title: 'Editar Post',
        post,
        categories,
        isEdit: true
      });
    } catch (error) {
      console.error('Erro ao carregar post:', error);
      req.flash('error', 'Erro ao carregar post.');
      res.redirect('/admin/posts');
    }
  }

  // Atualizar post
  async update(req, res) {
    try {
      const post = await Post.findByPk(req.params.id);

      if (!post) {
        req.flash('error', 'Post não encontrado.');
        return res.redirect('/admin/posts');
      }

      const {
        title,
        subtitle,
        slug,
        content,
        embed_code,
        excerpt,
        category_id,
        tags,
        status,
        featured,
        meta_title,
        meta_description,
        scheduled_at,
        featured_image_url
      } = req.body;

      // Verificar slug único (se alterado)
      if (slug && slug !== post.slug) {
        const existingPost = await Post.findOne({ 
          where: { slug, id: { [Op.ne]: post.id } } 
        });
        if (existingPost) {
          req.flash('error', 'Este slug já está em uso.');
          return res.redirect(`/admin/posts/${post.id}/edit`);
        }
      }

      // Atualizar campos
      post.title = title;
      post.subtitle = subtitle;
      post.slug = slug || post.slug;
      post.content = content;
      post.embed_code = embed_code || null;
      post.excerpt = excerpt;
      post.category_id = category_id || null;
      post.tags = tags || '';
      post.status = status || 'draft';
      post.featured = featured === 'on' || featured === true;
      post.meta_title = meta_title;
      post.meta_description = meta_description;
      post.scheduled_at = scheduled_at || null;

      // Atualizar imagem: upload tem prioridade, depois URL da galeria
      if (req.processedFile) {
        post.featured_image = req.processedFile.url;
      } else if (featured_image_url) {
        post.featured_image = featured_image_url;
      }

      await post.save();

      req.flash('success', 'Post atualizado com sucesso!');
      res.redirect('/admin/posts');
    } catch (error) {
      console.error('Erro ao atualizar post:', error);
      req.flash('error', 'Erro ao atualizar post: ' + error.message);
      res.redirect(`/admin/posts/${req.params.id}/edit`);
    }
  }

  // Excluir post
  async destroy(req, res) {
    try {
      const post = await Post.findByPk(req.params.id);

      if (!post) {
        req.flash('error', 'Post não encontrado.');
        return res.redirect('/admin/posts');
      }

      await post.destroy();

      req.flash('success', 'Post excluído com sucesso!');
      res.redirect('/admin/posts');
    } catch (error) {
      console.error('Erro ao excluir post:', error);
      req.flash('error', 'Erro ao excluir post.');
      res.redirect('/admin/posts');
    }
  }

  // Alternar status de destaque
  async toggleFeatured(req, res) {
    try {
      const post = await Post.findByPk(req.params.id);

      if (!post) {
        return res.json({ success: false, message: 'Post não encontrado.' });
      }

      post.featured = !post.featured;
      await post.save();

      return res.json({ 
        success: true, 
        featured: post.featured,
        message: post.featured ? 'Post destacado!' : 'Destaque removido!'
      });
    } catch (error) {
      console.error('Erro ao alternar destaque:', error);
      return res.json({ success: false, message: 'Erro ao alternar destaque.' });
    }
  }

  // Alternar status de publicação
  async toggleStatus(req, res) {
    try {
      const post = await Post.findByPk(req.params.id);

      if (!post) {
        return res.json({ success: false, message: 'Post não encontrado.' });
      }

      post.status = post.status === 'published' ? 'draft' : 'published';
      if (post.status === 'published' && !post.published_at) {
        post.published_at = new Date();
      }
      await post.save();

      return res.json({ 
        success: true, 
        status: post.status,
        message: post.status === 'published' ? 'Post publicado!' : 'Post despublicado!'
      });
    } catch (error) {
      console.error('Erro ao alternar status:', error);
      return res.json({ success: false, message: 'Erro ao alternar status.' });
    }
  }

  // Excluir múltiplos posts
  async destroyMultiple(req, res) {
    try {
      const { ids } = req.body;

      if (!ids || !Array.isArray(ids) || ids.length === 0) {
        req.flash('error', 'Nenhum post selecionado.');
        return res.redirect('/admin/posts');
      }

      const deleted = await Post.destroy({
        where: { id: { [Op.in]: ids } }
      });

      req.flash('success', `${deleted} post(s) excluído(s) com sucesso!`);
      res.redirect('/admin/posts');
    } catch (error) {
      console.error('Erro ao excluir posts:', error);
      req.flash('error', 'Erro ao excluir posts.');
      res.redirect('/admin/posts');
    }
  }
}

module.exports = new PostController();
