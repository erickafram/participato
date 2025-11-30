/**
 * Rotas do Site Público
 */
const express = require('express');
const router = express.Router();
const SiteController = require('../controllers/SiteController');
const { SitemapStream, streamToPromise } = require('sitemap');
const { Post, Category, Page } = require('../models');

// Página inicial
router.get('/', SiteController.home);

// Listagem de notícias
router.get('/noticias', SiteController.posts);

// Post individual
router.get('/noticia/:slug', SiteController.post);

// Listagem de categorias
router.get('/categorias', SiteController.categories);

// Categoria individual
router.get('/categoria/:slug', SiteController.category);

// Tag
router.get('/tag/:tag', SiteController.tag);

// Busca
router.get('/busca', SiteController.search);

// Sitemap XML
router.get('/sitemap.xml', async (req, res) => {
  try {
    const siteUrl = process.env.SITE_URL || `http://${req.headers.host}`;
    const smStream = new SitemapStream({ hostname: siteUrl });
    
    // Página inicial
    smStream.write({ url: '/', changefreq: 'daily', priority: 1.0 });
    
    // Páginas estáticas
    const pages = await Page.findAll({ where: { status: 'published' } });
    pages.forEach(page => {
      smStream.write({ 
        url: `/pagina/${page.slug}`, 
        changefreq: 'monthly', 
        priority: 0.7,
        lastmod: page.updated_at
      });
    });
    
    // Categorias
    const categories = await Category.findAll({ where: { active: true } });
    categories.forEach(cat => {
      smStream.write({ 
        url: `/categoria/${cat.slug}`, 
        changefreq: 'weekly', 
        priority: 0.8 
      });
    });
    
    // Posts
    const posts = await Post.findAll({ 
      where: { status: 'published' },
      order: [['published_at', 'DESC']],
      limit: 1000
    });
    posts.forEach(post => {
      smStream.write({ 
        url: `/noticia/${post.slug}`, 
        changefreq: 'weekly', 
        priority: 0.9,
        lastmod: post.updated_at
      });
    });
    
    smStream.end();
    
    const sitemap = await streamToPromise(smStream);
    res.header('Content-Type', 'application/xml');
    res.send(sitemap.toString());
  } catch (error) {
    console.error('Erro ao gerar sitemap:', error);
    res.status(500).send('Erro ao gerar sitemap');
  }
});

// Robots.txt
router.get('/robots.txt', (req, res) => {
  const siteUrl = process.env.SITE_URL || `http://${req.headers.host}`;
  res.type('text/plain');
  res.send(`User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/

Sitemap: ${siteUrl}/sitemap.xml`);
});

// Página estática (deve ser a última rota com parâmetro)
router.get('/pagina/:slug', SiteController.page);

module.exports = router;
