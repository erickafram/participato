/**
 * Model Category - Categorias de posts
 */
const { DataTypes } = require('sequelize');
const slugify = require('slugify');

module.exports = (sequelize) => {
  const Category = sequelize.define('Category', {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    name: {
      type: DataTypes.STRING(100),
      allowNull: false,
      validate: {
        notEmpty: { msg: 'O nome da categoria é obrigatório' },
        len: { args: [2, 100], msg: 'O nome deve ter entre 2 e 100 caracteres' }
      }
    },
    slug: {
      type: DataTypes.STRING(120),
      allowNull: false,
      unique: { msg: 'Este slug já existe' }
    },
    description: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    color: {
      type: DataTypes.STRING(7),
      allowNull: true,
      defaultValue: '#3ba4ff',
      validate: {
        is: /^#[0-9A-Fa-f]{6}$/i
      }
    },
    icon: {
      type: DataTypes.STRING(50),
      allowNull: true
    },
    order: {
      type: DataTypes.INTEGER,
      defaultValue: 0
    },
    active: {
      type: DataTypes.BOOLEAN,
      defaultValue: true
    }
  }, {
    tableName: 'categories',
    timestamps: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at',
    hooks: {
      // Gerar slug automaticamente antes de criar
      beforeCreate: async (category) => {
        if (!category.slug && category.name) {
          category.slug = slugify(category.name, { lower: true, strict: true });
        }
      },
      // Atualizar slug se o nome mudar
      beforeUpdate: async (category) => {
        if (category.changed('name') && !category.changed('slug')) {
          category.slug = slugify(category.name, { lower: true, strict: true });
        }
      }
    }
  });

  return Category;
};
