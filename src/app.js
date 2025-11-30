/**
 * Portal Convictos - Aplicação Principal
 * Portal de entretenimento com painel administrativo
 */
require('dotenv').config();

const express = require('express');
const path = require('path');
const session = require('express-session');
const flash = require('connect-flash');

// Importar banco de dados
const { sequelize } = require('./models');

// Importar middlewares
const { addUserToLocals, addFlashMessages } = require('./middlewares/auth');
const { addSettingsToLocals } = require('./middlewares/settings');

// Importar rotas
const routes = require('./routes');

// Criar aplicação Express
const app = express();

// ==========================================
// CONFIGURAÇÕES
// ==========================================

// View engine (EJS)
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Parser de body
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Arquivos estáticos
app.use(express.static(path.join(__dirname, 'public')));
app.use('/uploads', express.static(path.join(__dirname, '../uploads')));

// Sessão
app.use(session({
  secret: process.env.SESSION_SECRET || 'portal_convictos_secret',
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: process.env.NODE_ENV === 'production',
    maxAge: 24 * 60 * 60 * 1000 // 24 horas
  }
}));

// Flash messages
app.use(flash());

// ==========================================
// MIDDLEWARES GLOBAIS
// ==========================================

// Adicionar usuário e mensagens flash às views
app.use(addUserToLocals);
app.use(addFlashMessages);

// Adicionar configurações do site às views (apenas para rotas do site)
app.use(async (req, res, next) => {
  // Não carregar configurações para rotas de API ou arquivos estáticos
  if (req.path.startsWith('/api/') || req.path.startsWith('/uploads/')) {
    return next();
  }
  await addSettingsToLocals(req, res, next);
});

// ==========================================
// ROTAS
// ==========================================
app.use(routes);

// ==========================================
// TRATAMENTO DE ERROS
// ==========================================

// Erro 404
app.use((req, res, next) => {
  res.status(404).render('site/404', {
    title: 'Página não encontrada',
    settings: res.locals.settings || {},
    categories: res.locals.categories || [],
    menuPages: res.locals.menuPages || [],
    siteUrl: process.env.SITE_URL || `http://${req.headers.host}`,
    currentUrl: req.originalUrl,
    currentYear: new Date().getFullYear()
  });
});

// Erro 500
app.use((err, req, res, next) => {
  console.error('Erro:', err);
  
  // Se for requisição de API, retornar JSON
  if (req.path.startsWith('/api/')) {
    return res.status(500).json({ 
      error: 'Erro interno do servidor',
      message: process.env.NODE_ENV === 'development' ? err.message : undefined
    });
  }
  
  res.status(500).render('site/error', {
    title: 'Erro',
    message: 'Ocorreu um erro interno. Por favor, tente novamente.',
    error: process.env.NODE_ENV === 'development' ? err : null,
    settings: res.locals.settings || {},
    categories: res.locals.categories || [],
    menuPages: res.locals.menuPages || [],
    siteUrl: process.env.SITE_URL || `http://${req.headers.host}`,
    currentUrl: req.originalUrl,
    currentYear: new Date().getFullYear()
  });
});

// ==========================================
// INICIALIZAÇÃO
// ==========================================

const PORT = process.env.PORT || 3000;

// Sincronizar banco de dados e iniciar servidor
async function startServer() {
  try {
    // Testar conexão com o banco
    await sequelize.authenticate();
    console.log('✅ Conexão com o banco de dados estabelecida.');

    // Sincronizar models (em desenvolvimento)
    if (process.env.NODE_ENV === 'development') {
      await sequelize.sync({ alter: false });
      console.log('✅ Models sincronizados.');
    }

    // Iniciar servidor
    app.listen(PORT, () => {
      console.log(`
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║   🚀 Portal Convictos iniciado com sucesso!                ║
║                                                            ║
║   📍 Site:   http://localhost:${PORT}                        ║
║   🔐 Admin:  http://localhost:${PORT}/admin                  ║
║                                                            ║
║   📧 Login padrão:                                         ║
║      Email: admin@portal.com                               ║
║      Senha: admin123                                       ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
      `);
    });
  } catch (error) {
    console.error('❌ Erro ao iniciar servidor:', error);
    process.exit(1);
  }
}

startServer();

module.exports = app;
