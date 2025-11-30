/**
 * Middleware de Configurações
 * Carrega configurações do site para todas as views
 */
const { Setting, Category, Page } = require('../models');

// Cache de configurações
let settingsCache = null;
let cacheTimestamp = null;
const CACHE_DURATION = 5 * 60 * 1000; // 5 minutos

// Carregar configurações do banco de dados
const loadSettings = async () => {
  const now = Date.now();
  
  // Verificar se o cache ainda é válido
  if (settingsCache && cacheTimestamp && (now - cacheTimestamp) < CACHE_DURATION) {
    return settingsCache;
  }
  
  try {
    const settings = await Setting.findAll();
    const settingsObj = {};
    
    settings.forEach(setting => {
      settingsObj[setting.key] = setting.value;
    });
    
    // Atualizar cache
    settingsCache = settingsObj;
    cacheTimestamp = now;
    
    return settingsObj;
  } catch (error) {
    console.error('Erro ao carregar configurações:', error);
    return settingsCache || {};
  }
};

// Limpar cache de configurações
const clearSettingsCache = () => {
  settingsCache = null;
  cacheTimestamp = null;
};

// Middleware para adicionar configurações às views
const addSettingsToLocals = async (req, res, next) => {
  try {
    // Carregar configurações
    const settings = await loadSettings();
    res.locals.settings = settings;
    
    // Carregar categorias ativas para o menu
    const categories = await Category.findAll({
      where: { active: true },
      order: [['order', 'ASC'], ['name', 'ASC']]
    });
    res.locals.categories = categories;
    
    // Carregar páginas do menu
    const menuPages = await Page.scope('inMenu').findAll();
    res.locals.menuPages = menuPages;
    
    // URL base do site
    res.locals.siteUrl = process.env.SITE_URL || `http://${req.headers.host}`;
    
    // Ano atual para copyright
    res.locals.currentYear = new Date().getFullYear();
    
    // URL atual
    res.locals.currentUrl = req.originalUrl;
    res.locals.currentPath = req.path;
    
    next();
  } catch (error) {
    console.error('Erro no middleware de configurações:', error);
    res.locals.settings = {};
    res.locals.categories = [];
    res.locals.menuPages = [];
    next();
  }
};

module.exports = {
  loadSettings,
  clearSettingsCache,
  addSettingsToLocals
};
