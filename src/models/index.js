/**
 * Índice de Models - Inicialização do Sequelize
 */
const { Sequelize } = require('sequelize');
const config = require('../../config/database');

const env = process.env.NODE_ENV || 'development';
const dbConfig = config[env];

// Criar instância do Sequelize
const sequelize = new Sequelize(
  dbConfig.database,
  dbConfig.username,
  dbConfig.password,
  {
    host: dbConfig.host,
    port: dbConfig.port,
    dialect: dbConfig.dialect,
    logging: dbConfig.logging,
    define: dbConfig.define,
    pool: dbConfig.pool
  }
);

// Importar models
const User = require('./User')(sequelize);
const Category = require('./Category')(sequelize);
const Subcategory = require('./Subcategory')(sequelize);
const Post = require('./Post')(sequelize);
const Page = require('./Page')(sequelize);
const Media = require('./Media')(sequelize);
const Setting = require('./Setting')(sequelize);
const Banner = require('./Banner')(sequelize);

// Definir associações
// User -> Posts (um usuário pode ter muitos posts)
User.hasMany(Post, { foreignKey: 'author_id', as: 'posts' });
Post.belongsTo(User, { foreignKey: 'author_id', as: 'author' });

// Category -> Posts (uma categoria pode ter muitos posts)
Category.hasMany(Post, { foreignKey: 'category_id', as: 'posts' });
Post.belongsTo(Category, { foreignKey: 'category_id', as: 'category' });

// Category -> Subcategories (uma categoria pode ter muitas subcategorias)
Category.hasMany(Subcategory, { foreignKey: 'category_id', as: 'subcategories' });
Subcategory.belongsTo(Category, { foreignKey: 'category_id', as: 'category' });

// Category -> Category (hierarquia: categoria pai -> subcategorias)
Category.hasMany(Category, { foreignKey: 'parent_id', as: 'children' });
Category.belongsTo(Category, { foreignKey: 'parent_id', as: 'parent' });

// User -> Pages (um usuário pode criar muitas páginas)
User.hasMany(Page, { foreignKey: 'author_id', as: 'pages' });
Page.belongsTo(User, { foreignKey: 'author_id', as: 'author' });

// User -> Media (um usuário pode fazer upload de muitas mídias)
User.hasMany(Media, { foreignKey: 'user_id', as: 'medias' });
Media.belongsTo(User, { foreignKey: 'user_id', as: 'user' });

module.exports = {
  sequelize,
  Sequelize,
  User,
  Category,
  Subcategory,
  Post,
  Page,
  Media,
  Setting,
  Banner
};
