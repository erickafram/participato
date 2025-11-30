/**
 * Model Post - Posts/Notícias do portal
 */
const { DataTypes } = require('sequelize');
const slugify = require('slugify');

module.exports = (sequelize) => {
  const Post = sequelize.define('Post', {
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
        len: { args: [5, 255], msg: 'O título deve ter entre 5 e 255 caracteres' }
      }
    },
    subtitle: {
      type: DataTypes.STRING(500),
      allowNull: true
    },
    slug: {
      type: DataTypes.STRING(280),
      allowNull: false,
      unique: { msg: 'Este slug já existe' }
    },
    content: {
      type: DataTypes.TEXT('long'),
      allowNull: false,
      validate: {
        notEmpty: { msg: 'O conteúdo é obrigatório' }
      }
    },
    excerpt: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    featured_image: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    featured_image_alt: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    tags: {
      type: DataTypes.TEXT,
      allowNull: true,
      get() {
        const rawValue = this.getDataValue('tags');
        return rawValue ? rawValue.split(',').map(tag => tag.trim()) : [];
      },
      set(value) {
        if (Array.isArray(value)) {
          this.setDataValue('tags', value.join(','));
        } else {
          this.setDataValue('tags', value);
        }
      }
    },
    status: {
      type: DataTypes.ENUM('draft', 'published', 'scheduled'),
      defaultValue: 'draft',
      allowNull: false
    },
    featured: {
      type: DataTypes.BOOLEAN,
      defaultValue: false
    },
    views: {
      type: DataTypes.INTEGER,
      defaultValue: 0
    },
    meta_title: {
      type: DataTypes.STRING(70),
      allowNull: true
    },
    meta_description: {
      type: DataTypes.STRING(160),
      allowNull: true
    },
    published_at: {
      type: DataTypes.DATE,
      allowNull: true
    },
    scheduled_at: {
      type: DataTypes.DATE,
      allowNull: true
    },
    category_id: {
      type: DataTypes.INTEGER,
      allowNull: true,
      references: {
        model: 'categories',
        key: 'id'
      }
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
    tableName: 'posts',
    timestamps: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at',
    hooks: {
      // Gerar slug automaticamente antes de criar
      beforeCreate: async (post) => {
        if (!post.slug && post.title) {
          let baseSlug = slugify(post.title, { lower: true, strict: true });
          let slug = baseSlug;
          let counter = 1;
          
          // Verificar se slug já existe
          while (await Post.findOne({ where: { slug } })) {
            slug = `${baseSlug}-${counter}`;
            counter++;
          }
          post.slug = slug;
        }
        
        // Gerar excerpt automaticamente se não fornecido
        if (!post.excerpt && post.content) {
          const plainText = post.content.replace(/<[^>]+>/g, '');
          post.excerpt = plainText.substring(0, 200) + (plainText.length > 200 ? '...' : '');
        }
        
        // Definir data de publicação
        if (post.status === 'published' && !post.published_at) {
          post.published_at = new Date();
        }
      },
      beforeUpdate: async (post) => {
        // Atualizar data de publicação quando status mudar para publicado
        if (post.changed('status') && post.status === 'published' && !post.published_at) {
          post.published_at = new Date();
        }
      }
    }
  });

  // Método para incrementar visualizações
  Post.prototype.incrementViews = async function() {
    this.views += 1;
    await this.save({ fields: ['views'] });
  };

  // Escopo para posts publicados
  Post.addScope('published', {
    where: { status: 'published' }
  });

  // Escopo para posts em destaque
  Post.addScope('featured', {
    where: { featured: true, status: 'published' }
  });

  return Post;
};
