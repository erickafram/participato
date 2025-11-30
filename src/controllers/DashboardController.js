/**
 * Controller do Dashboard
 * Exibe estatísticas e informações gerais do painel
 */
const { Post, Category, Page, User, Media, sequelize } = require('../models');
const { Op } = require('sequelize');

class DashboardController {
  // Exibir dashboard principal
  async index(req, res) {
    try {
      // Estatísticas gerais
      const [
        totalPosts,
        publishedPosts,
        draftPosts,
        totalCategories,
        totalPages,
        totalUsers,
        totalMedia
      ] = await Promise.all([
        Post.count(),
        Post.count({ where: { status: 'published' } }),
        Post.count({ where: { status: 'draft' } }),
        Category.count(),
        Page.count(),
        User.count(),
        Media.count()
      ]);

      // Posts recentes
      const recentPosts = await Post.findAll({
        include: [
          { model: User, as: 'author', attributes: ['name'] },
          { model: Category, as: 'category', attributes: ['name'] }
        ],
        order: [['created_at', 'DESC']],
        limit: 5
      });

      // Posts mais visualizados
      const popularPosts = await Post.findAll({
        where: { status: 'published' },
        order: [['views', 'DESC']],
        limit: 5
      });

      // Total de visualizações
      const totalViews = await Post.sum('views') || 0;

      // Posts por categoria
      const postsByCategory = await Category.findAll({
        attributes: [
          'id',
          'name',
          'color',
          [sequelize.fn('COUNT', sequelize.col('posts.id')), 'post_count']
        ],
        include: [{
          model: Post,
          as: 'posts',
          attributes: [],
          where: { status: 'published' },
          required: false
        }],
        group: ['Category.id'],
        order: [[sequelize.literal('post_count'), 'DESC']]
      });

      // Posts dos últimos 7 dias
      const sevenDaysAgo = new Date();
      sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
      
      const recentPostsCount = await Post.count({
        where: {
          created_at: { [Op.gte]: sevenDaysAgo }
        }
      });

      res.render('admin/dashboard/index', {
        title: 'Dashboard',
        stats: {
          totalPosts,
          publishedPosts,
          draftPosts,
          totalCategories,
          totalPages,
          totalUsers,
          totalMedia,
          totalViews,
          recentPostsCount
        },
        recentPosts,
        popularPosts,
        postsByCategory
      });
    } catch (error) {
      console.error('Erro ao carregar dashboard:', error);
      req.flash('error', 'Erro ao carregar dashboard.');
      res.render('admin/dashboard/index', {
        title: 'Dashboard',
        stats: {},
        recentPosts: [],
        popularPosts: [],
        postsByCategory: []
      });
    }
  }
}

module.exports = new DashboardController();
