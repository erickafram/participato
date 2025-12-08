/**
 * Model Subcategory - Subcategorias de posts vinculadas a categorias
 */
const { DataTypes } = require('sequelize');
const slugify = require('slugify');

module.exports = (sequelize) => {
    const Subcategory = sequelize.define('Subcategory', {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true
        },
        category_id: {
            type: DataTypes.INTEGER,
            allowNull: false,
            references: {
                model: 'categories',
                key: 'id'
            }
        },
        name: {
            type: DataTypes.STRING(100),
            allowNull: false,
            validate: {
                notEmpty: { msg: 'O nome da subcategoria é obrigatório' },
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
        tableName: 'subcategories',
        timestamps: true,
        createdAt: 'created_at',
        updatedAt: 'updated_at',
        hooks: {
            // Gerar slug automaticamente antes de criar
            beforeCreate: async (subcategory) => {
                if (!subcategory.slug && subcategory.name) {
                    subcategory.slug = slugify(subcategory.name, { lower: true, strict: true });
                }
            },
            // Atualizar slug se o nome mudar
            beforeUpdate: async (subcategory) => {
                if (subcategory.changed('name') && !subcategory.changed('slug')) {
                    subcategory.slug = slugify(subcategory.name, { lower: true, strict: true });
                }
            }
        }
    });

    return Subcategory;
};
