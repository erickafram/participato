/**
 * Model HomeBlock
 * Blocos configuráveis da página inicial
 */
const { DataTypes } = require('sequelize');

module.exports = (sequelize) => {
  const HomeBlock = sequelize.define('HomeBlock', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  type: {
    type: DataTypes.STRING(30),
    allowNull: false
    // Tipos suportados:
    // 'featured'      - Destaque principal (1 grande + 2 pequenos)
    // 'highlight-2'   - 2 destaques lado a lado
    // 'grid-2'        - 2 cards em grid
    // 'grid-3'        - 3 cards em grid
    // 'grid-4'        - 4 cards em grid
    // 'list-vertical' - Lista vertical
    // 'big-left'      - 1 grande à esquerda + lista à direita
    // 'big-right'     - Lista à esquerda + 1 grande à direita
    // 'carousel'      - Carrossel de notícias
    // 'banner'        - Banner publicitário
  },
  title: {
    type: DataTypes.STRING(100),
    allowNull: true
  },
  category_id: {
    type: DataTypes.INTEGER,
    allowNull: true // null = todas as categorias
  },
  banner_id: {
    type: DataTypes.INTEGER,
    allowNull: true // Para blocos do tipo banner
  },
  posts_count: {
    type: DataTypes.INTEGER,
    defaultValue: 4
  },
  show_title: {
    type: DataTypes.BOOLEAN,
    defaultValue: true
  },
  show_excerpt: {
    type: DataTypes.BOOLEAN,
    defaultValue: true
  },
  show_date: {
    type: DataTypes.BOOLEAN,
    defaultValue: true
  },
  show_category: {
    type: DataTypes.BOOLEAN,
    defaultValue: true
  },
  background_color: {
    type: DataTypes.STRING(20),
    defaultValue: '#ffffff'
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
    tableName: 'home_blocks',
    timestamps: true,
    underscored: true
  });

  return HomeBlock;
};
