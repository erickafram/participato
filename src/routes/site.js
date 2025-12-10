/**
 * Rotas do Site Público
 */
const express = require('express');
const router = express.Router();
const SiteController = require('../controllers/SiteController');
const BannerController = require('../controllers/BannerController');
const PetitionSiteController = require('../controllers/PetitionSiteController');
const PetitionerController = require('../controllers/PetitionerController');
const AuthUnifiedController = require('../controllers/AuthUnifiedController');
const { uploadSingle } = require('../middlewares/upload');
const { SitemapStream, streamToPromise } = require('sitemap');
const { Post, Category, Page } = require('../models');

// Middleware para verificar se peticionário está logado
const isPetitionerAuthenticated = (req, res, next) => {
  if (req.session && req.session.petitioner) {
    return next();
  }
  req.flash('error', 'Faça login para acessar esta página.');
  return res.redirect('/login');
};

// ==========================================
// LOGIN UNIFICADO
// ==========================================
router.get('/login', AuthUnifiedController.showLogin);
router.post('/login', AuthUnifiedController.login);
router.get('/logout', AuthUnifiedController.logout);

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

// Subcategoria individual
router.get('/subcategoria/:slug', SiteController.subcategory);

// Tag
router.get('/tag/:tag', SiteController.tag);

// Busca
router.get('/busca', SiteController.search);

// ==========================================
// PETIÇÕES PÚBLICAS
// ==========================================
router.get('/peticoes', PetitionSiteController.index);
router.get('/peticao/:slug', PetitionSiteController.show);
router.post('/peticao/:slug/assinar', PetitionSiteController.sign);
router.get('/peticao/:slug/assinaturas', PetitionSiteController.loadSignatures);

// ==========================================
// ÁREA DO CIDADÃO (PETICIONÁRIO)
// ==========================================
// Autenticação
router.get('/cidadao/login', PetitionerController.showLogin);
router.post('/cidadao/login', PetitionerController.login);
router.get('/cidadao/logout', PetitionerController.logout);

// Criar petição (com cadastro)
router.get('/criar-peticao', PetitionerController.showCreatePetition);
router.post('/criar-peticao', ...uploadSingle('image'), PetitionerController.createPetition);

// Painel do cidadão (requer login)
router.get('/minha-conta', isPetitionerAuthenticated, PetitionerController.dashboard);
router.get('/minha-conta/peticao/:id', isPetitionerAuthenticated, PetitionerController.viewPetition);
router.get('/minha-conta/perfil', isPetitionerAuthenticated, PetitionerController.showProfile);
router.post('/minha-conta/perfil', isPetitionerAuthenticated, PetitionerController.updateProfile);

// Registrar clique em banner (API)
router.post('/api/banner/:id/click', BannerController.registerClick);

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
