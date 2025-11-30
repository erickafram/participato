/**
 * Validadores de Formulários
 * Usando express-validator
 */
const { body, validationResult } = require('express-validator');

// Helper para verificar erros de validação
const validate = (req, res, next) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    req.flash('error', errors.array().map(e => e.msg).join('<br>'));
    return res.redirect('back');
  }
  next();
};

// Validação de Login
const loginValidation = [
  body('email')
    .trim()
    .notEmpty().withMessage('O email é obrigatório')
    .isEmail().withMessage('Email inválido')
    .normalizeEmail(),
  body('password')
    .notEmpty().withMessage('A senha é obrigatória')
];

// Validação de Usuário
const userValidation = [
  body('name')
    .trim()
    .notEmpty().withMessage('O nome é obrigatório')
    .isLength({ min: 2, max: 100 }).withMessage('O nome deve ter entre 2 e 100 caracteres'),
  body('email')
    .trim()
    .notEmpty().withMessage('O email é obrigatório')
    .isEmail().withMessage('Email inválido')
    .normalizeEmail(),
  body('password')
    .optional({ checkFalsy: true })
    .isLength({ min: 6 }).withMessage('A senha deve ter no mínimo 6 caracteres'),
  body('role')
    .optional()
    .isIn(['admin', 'editor']).withMessage('Função inválida')
];

// Validação de Post
const postValidation = [
  body('title')
    .trim()
    .notEmpty().withMessage('O título é obrigatório')
    .isLength({ min: 5, max: 255 }).withMessage('O título deve ter entre 5 e 255 caracteres'),
  body('subtitle')
    .optional()
    .trim()
    .isLength({ max: 500 }).withMessage('O subtítulo deve ter no máximo 500 caracteres'),
  body('content')
    .notEmpty().withMessage('O conteúdo é obrigatório'),
  body('category_id')
    .optional({ checkFalsy: true })
    .isInt().withMessage('Categoria inválida'),
  body('status')
    .optional()
    .isIn(['draft', 'published', 'scheduled']).withMessage('Status inválido'),
  body('meta_title')
    .optional()
    .trim()
    .isLength({ max: 70 }).withMessage('O meta título deve ter no máximo 70 caracteres'),
  body('meta_description')
    .optional()
    .trim()
    .isLength({ max: 160 }).withMessage('A meta descrição deve ter no máximo 160 caracteres')
];

// Validação de Categoria
const categoryValidation = [
  body('name')
    .trim()
    .notEmpty().withMessage('O nome é obrigatório')
    .isLength({ min: 2, max: 100 }).withMessage('O nome deve ter entre 2 e 100 caracteres'),
  body('slug')
    .optional()
    .trim()
    .isLength({ max: 120 }).withMessage('O slug deve ter no máximo 120 caracteres'),
  body('description')
    .optional()
    .trim(),
  body('color')
    .optional()
    .matches(/^#[0-9A-Fa-f]{6}$/).withMessage('Cor inválida (use formato hexadecimal)')
];

// Validação de Página
const pageValidation = [
  body('title')
    .trim()
    .notEmpty().withMessage('O título é obrigatório')
    .isLength({ min: 2, max: 255 }).withMessage('O título deve ter entre 2 e 255 caracteres'),
  body('slug')
    .optional()
    .trim()
    .isLength({ max: 280 }).withMessage('O slug deve ter no máximo 280 caracteres'),
  body('content')
    .optional(),
  body('status')
    .optional()
    .isIn(['draft', 'published']).withMessage('Status inválido'),
  body('meta_title')
    .optional()
    .trim()
    .isLength({ max: 70 }).withMessage('O meta título deve ter no máximo 70 caracteres'),
  body('meta_description')
    .optional()
    .trim()
    .isLength({ max: 160 }).withMessage('A meta descrição deve ter no máximo 160 caracteres')
];

// Validação de Configurações
const settingsValidation = [
  body('site_name')
    .optional()
    .trim()
    .isLength({ max: 100 }).withMessage('O nome do site deve ter no máximo 100 caracteres'),
  body('site_description')
    .optional()
    .trim()
    .isLength({ max: 255 }).withMessage('A descrição deve ter no máximo 255 caracteres'),
  body('contact_email')
    .optional()
    .trim()
    .isEmail().withMessage('Email de contato inválido')
];

module.exports = {
  validate,
  loginValidation,
  userValidation,
  postValidation,
  categoryValidation,
  pageValidation,
  settingsValidation
};
