/**
 * Índice de Rotas
 * Agrupa todas as rotas da aplicação
 */
const express = require('express');
const router = express.Router();

// Importar rotas
const siteRoutes = require('./site');
const adminRoutes = require('./admin');
const apiRoutes = require('./api');
const petitionerRoutes = require('./petitioner');

// Rotas do painel administrativo (deve vir ANTES das rotas do site)
router.use('/admin', adminRoutes);

// Rotas da API
router.use('/api', apiRoutes);

// Rotas do painel do peticionário (cidadão)
router.use('/', petitionerRoutes);

// Rotas do site público (deve ser a última, pois tem rotas genéricas)
router.use('/', siteRoutes);

module.exports = router;
