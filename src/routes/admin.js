/**
 * Rotas do Painel Administrativo
 */
const express = require('express');
const router = express.Router();

// Controllers
const AuthController = require('../controllers/AuthController');
const DashboardController = require('../controllers/DashboardController');
const PostController = require('../controllers/PostController');
const CategoryController = require('../controllers/CategoryController');
const PageController = require('../controllers/PageController');
const UserController = require('../controllers/UserController');
const MediaController = require('../controllers/MediaController');
const SettingController = require('../controllers/SettingController');

// Middlewares
const { isAuthenticated, isNotAuthenticated, isAdmin } = require('../middlewares/auth');
const { uploadSingle, uploadMultiple } = require('../middlewares/upload');
const { 
  validate, 
  loginValidation, 
  postValidation, 
  categoryValidation, 
  pageValidation,
  userValidation 
} = require('../middlewares/validators');

// ==========================================
// ROTAS DE AUTENTICAÇÃO
// ==========================================
router.get('/login', isNotAuthenticated, AuthController.showLogin);
router.post('/login', isNotAuthenticated, loginValidation, validate, AuthController.login);
router.get('/logout', AuthController.logout);

// ==========================================
// ROTAS PROTEGIDAS (requer autenticação)
// ==========================================
router.use(isAuthenticated);

// Dashboard
router.get('/', DashboardController.index);

// Perfil do usuário
router.get('/profile', AuthController.showProfile);
router.post('/profile', ...uploadSingle('avatar'), AuthController.updateProfile);

// ==========================================
// POSTS
// ==========================================
router.get('/posts', PostController.index);
router.get('/posts/create', PostController.create);
router.post('/posts', ...uploadSingle('featured_image'), PostController.store);
router.post('/posts/delete-multiple', PostController.destroyMultiple);
router.get('/posts/:id/edit', PostController.edit);
router.post('/posts/:id', ...uploadSingle('featured_image'), PostController.update);
router.post('/posts/:id/delete', PostController.destroy);
router.post('/posts/:id/toggle-featured', PostController.toggleFeatured);
router.post('/posts/:id/toggle-status', PostController.toggleStatus);

// ==========================================
// CATEGORIAS
// ==========================================
router.get('/categories', CategoryController.index);
router.get('/categories/create', CategoryController.create);
router.post('/categories', CategoryController.store);
router.get('/categories/:id/edit', CategoryController.edit);
router.post('/categories/:id', CategoryController.update);
router.post('/categories/:id/delete', CategoryController.destroy);
router.post('/categories/:id/toggle-active', CategoryController.toggleActive);

// ==========================================
// PÁGINAS
// ==========================================
router.get('/pages', PageController.index);
router.get('/pages/create', PageController.create);
router.post('/pages', ...uploadSingle('featured_image'), PageController.store);
router.get('/pages/:id/edit', PageController.edit);
router.post('/pages/:id', ...uploadSingle('featured_image'), PageController.update);
router.post('/pages/:id/delete', PageController.destroy);
router.post('/pages/:id/toggle-status', PageController.toggleStatus);

// ==========================================
// MÍDIA
// ==========================================
router.get('/media', MediaController.index);
router.post('/media/upload', ...uploadSingle('file'), MediaController.upload);
router.post('/media/upload-multiple', ...uploadMultiple('files', 10), MediaController.uploadMultiple);
router.get('/media/browse', MediaController.browse);
router.get('/media/:id', MediaController.show);
router.post('/media/:id', MediaController.update);
router.post('/media/:id/delete', MediaController.destroy);

// ==========================================
// ROTAS APENAS PARA ADMIN
// ==========================================
router.use('/users', isAdmin);
router.use('/settings', isAdmin);

// ==========================================
// USUÁRIOS (apenas admin)
// ==========================================
router.get('/users', UserController.index);
router.get('/users/create', UserController.create);
router.post('/users', ...uploadSingle('avatar'), UserController.store);
router.get('/users/:id/edit', UserController.edit);
router.post('/users/:id', ...uploadSingle('avatar'), UserController.update);
router.post('/users/:id/delete', UserController.destroy);
router.post('/users/:id/toggle-active', UserController.toggleActive);

// ==========================================
// CONFIGURAÇÕES (apenas admin)
// ==========================================
router.get('/settings', SettingController.index);
router.post('/settings', SettingController.update);

module.exports = router;
