/**
 * Model Page - Páginas estáticas do site
 */
const { DataTypes } = require('sequelize');
const slugify = require('slugify');

module.exports = (sequelize) => {
  const Page = sequelize.define('Page', {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    title: {
      type: DataTypes.STRING(255),
      allowNull: false,
      validate: {
        notEmpty: { msg: 'O título é obrigatório' },
        len: { args: [2, 255], msg: 'O título deve ter entre 2 e 255 caracteres' }
      }
    },
    slug: {
      type: DataTypes.STRING(280),
      allowNull: false,
      unique: { msg: 'Este slug já existe' }
    },
    content: {
      type: DataTypes.TEXT('long'),
      allowNull: true
    },
    featured_image: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    status: {
      type: DataTypes.ENUM('draft', 'published'),
      defaultValue: 'draft',
      allowNull: false
    },
    template: {
      type: DataTypes.STRING(50),
      defaultValue: 'default',
      allowNull: false
    },
    order: {
      type: DataTypes.INTEGER,
      defaultValue: 0
    },
    show_in_menu: {
      type: DataTypes.BOOLEAN,
      defaultValue: false
    },
    meta_title: {
      type: DataTypes.STRING(70),
      allowNull: true
    },
    meta_description: {
      type: DataTypes.STRING(160),
      allowNull: true
    },
    author_id: {
      type: DataTypes.INTEGER,
      allowNull: false,
      references: {
        model: 'users',
        key: 'id'
      }
    }
  }, {
    tableName: 'pages',
    timestamps: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at',
    hooks: {
      // Gerar slug automaticamente antes de criar
      beforeCreate: async (page) => {
        if (!page.slug && page.title) {
          let baseSlug = slugify(page.title, { lower: true, strict: true });
          let slug = baseSlug;
          let counter = 1;
          
          while (await Page.findOne({ where: { slug } })) {
            slug = `${baseSlug}-${counter}`;
            counter++;
          }
          page.slug = slug;
        }
      }
    }
  });

  // Escopo para páginas publicadas
  Page.addScope('published', {
    where: { status: 'published' }
  });

  // Escopo para páginas no menu
  Page.addScope('inMenu', {
    where: { show_in_menu: true, status: 'published' },
    order: [['order', 'ASC']]
  });

  return Page;
};
