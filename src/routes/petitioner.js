/**
 * Rotas do Painel do Peticionário (Cidadão)
 */
const express = require('express');
const router = express.Router();
const PetitionerController = require('../controllers/PetitionerController');
const { uploadSingle } = require('../middlewares/upload');

// Middleware para verificar se está logado como peticionário
const isPetitioner = (req, res, next) => {
  if (req.session && req.session.petitioner) {
    return next();
  }
  req.flash('error', 'Faça login para acessar esta área.');
  return res.redirect('/cidadao/login');
};

// Middleware para verificar se NÃO está logado
const isNotPetitioner = (req, res, next) => {
  if (req.session && req.session.petitioner) {
    return res.redirect('/minha-conta');
  }
  return next();
};

// ==========================================
// AUTENTICAÇÃO
// ==========================================
router.get('/cidadao/login', isNotPetitioner, PetitionerController.showLogin);
router.post('/cidadao/login', isNotPetitioner, PetitionerController.login);
router.get('/cidadao/logout', PetitionerController.logout);

// ==========================================
// CRIAR PETIÇÃO (público ou logado)
// ==========================================
router.get('/criar-peticao', PetitionerController.showCreatePetition);
router.post('/criar-peticao', ...uploadSingle('image'), PetitionerController.createPetition);

// ==========================================
// PAINEL DO USUÁRIO (requer login)
// ==========================================
router.get('/minha-conta', isPetitioner, PetitionerController.dashboard);
router.get('/minha-conta/peticao/:id', isPetitioner, PetitionerController.viewPetition);
router.get('/minha-conta/perfil', isPetitioner, PetitionerController.showProfile);
router.post('/minha-conta/perfil', isPetitioner, PetitionerController.updateProfile);

module.exports = router;
