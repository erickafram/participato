/**
 * Rotas da API
 * Endpoints para requisições AJAX e integrações
 */
const express = require('express');
const router = express.Router();
const { Post, Category, Page } = require('../models');
const { isAuthenticated } = require('../middlewares/auth');
const { uploadSingle } = require('../middlewares/upload');
const MediaController = require('../controllers/MediaController');

// ==========================================
// ROTAS PÚBLICAS
// ==========================================

// Busca de posts (autocomplete)
router.get('/posts/search', async (req, res) => {
  try {
    const { q, limit = 5 } = req.query;
    
    if (!q || q.length < 2) {
      return res.json({ posts: [] });
    }

    const posts = await Post.findAll({
      where: {
        status: 'published',
        title: { [require('sequelize').Op.like]: `%${q}%` }
      },
      attributes: ['id', 'title', 'slug', 'featured_image'],
      limit: parseInt(limit),
      order: [['published_at', 'DESC']]
    });

    res.json({ posts });
  } catch (error) {
    console.error('Erro na busca:', error);
    res.status(500).json({ error: 'Erro ao buscar posts' });
  }
});

// Listar categorias
router.get('/categories', async (req, res) => {
  try {
    const categories = await Category.findAll({
      where: { active: true },
      attributes: ['id', 'name', 'slug', 'color'],
      order: [['order', 'ASC'], ['name', 'ASC']]
    });

    res.json({ categories });
  } catch (error) {
    console.error('Erro ao listar categorias:', error);
    res.status(500).json({ error: 'Erro ao carregar categorias' });
  }
});

// ==========================================
// ROTAS PROTEGIDAS (requer autenticação)
// ==========================================
router.use(isAuthenticated);

// Upload de imagem (para editor)
router.post('/upload/image', ...uploadSingle('upload'), async (req, res) => {
  try {
    if (!req.processedFile) {
      return res.status(400).json({ 
        uploaded: false,
        error: { message: 'Nenhum arquivo enviado.' }
      });
    }

    // Formato esperado pelo CKEditor
    res.json({
      uploaded: true,
      url: req.processedFile.url
    });
  } catch (error) {
    console.error('Erro no upload:', error);
    res.status(500).json({ 
      uploaded: false,
      error: { message: 'Erro ao fazer upload.' }
    });
  }
});

// Verificar slug único (posts)
router.get('/posts/check-slug', async (req, res) => {
  try {
    const { slug, id } = req.query;
    
    const where = { slug };
    if (id) {
      where.id = { [require('sequelize').Op.ne]: id };
    }

    const existing = await Post.findOne({ where });
    
    res.json({ available: !existing });
  } catch (error) {
    console.error('Erro ao verificar slug:', error);
    res.status(500).json({ error: 'Erro ao verificar slug' });
  }
});

// Verificar slug único (categorias)
router.get('/categories/check-slug', async (req, res) => {
  try {
    const { slug, id } = req.query;
    
    const where = { slug };
    if (id) {
      where.id = { [require('sequelize').Op.ne]: id };
    }

    const existing = await Category.findOne({ where });
    
    res.json({ available: !existing });
  } catch (error) {
    console.error('Erro ao verificar slug:', error);
    res.status(500).json({ error: 'Erro ao verificar slug' });
  }
});

// Verificar slug único (páginas)
router.get('/pages/check-slug', async (req, res) => {
  try {
    const { slug, id } = req.query;
    
    const where = { slug };
    if (id) {
      where.id = { [require('sequelize').Op.ne]: id };
    }

    const existing = await Page.findOne({ where });
    
    res.json({ available: !existing });
  } catch (error) {
    console.error('Erro ao verificar slug:', error);
    res.status(500).json({ error: 'Erro ao verificar slug' });
  }
});

// Listar mídias para seleção
router.get('/media/browse', MediaController.browse);

module.exports = router;
