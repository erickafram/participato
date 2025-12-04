/**
 * Model Banner
 * Gerenciamento de banners publicitários
 */
const { DataTypes } = require('sequelize');

module.exports = (sequelize) => {
  const Banner = sequelize.define('Banner', {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true
    },
    title: {
      type: DataTypes.STRING(255),
      allowNull: false,
      comment: 'Título/nome do banner para identificação'
    },
    image: {
      type: DataTypes.STRING(500),
      allowNull: false,
      comment: 'URL da imagem do banner'
    },
    link: {
      type: DataTypes.STRING(500),
      allowNull: true,
      comment: 'URL de destino ao clicar no banner'
    },
    position: {
      type: DataTypes.ENUM(
        'home_top',           // Topo da home
        'home_middle',        // Meio da home
        'home_bottom',        // Rodapé da home
        'home_sidebar',       // Sidebar da home
        'post_top',           // Topo do post (antes do conteúdo)
        'post_middle',        // Meio do post (dentro do conteúdo)
        'post_bottom',        // Fim do post (após conteúdo)
        'post_sidebar',       // Sidebar do post
        'category_top',       // Topo da categoria
        'category_bottom',    // Fim da categoria
        'category_sidebar'    // Sidebar da categoria
      ),
      allowNull: false,
      comment: 'Posição onde o banner será exibido'
    },
    size: {
      type: DataTypes.ENUM(
        '728x90',    // Leaderboard
        '300x250',   // Medium Rectangle
        '336x280',   // Large Rectangle
        '300x600',   // Half Page
        '320x100',   // Large Mobile Banner
        '970x90',    // Large Leaderboard
        '970x250',   // Billboard
        '160x600',   // Wide Skyscraper
        '300x50',    // Mobile Banner
        'responsive' // Responsivo (100% largura)
      ),
      allowNull: false,
      defaultValue: '728x90',
      comment: 'Tamanho do banner'
    },
    alt_text: {
      type: DataTypes.STRING(255),
      allowNull: true,
      comment: 'Texto alternativo para acessibilidade'
    },
    target: {
      type: DataTypes.ENUM('_self', '_blank'),
      defaultValue: '_blank',
      comment: 'Abrir link na mesma aba ou nova aba'
    },
    order: {
      type: DataTypes.INTEGER,
      defaultValue: 0,
      comment: 'Ordem de exibição (menor = primeiro)'
    },
    views: {
      type: DataTypes.INTEGER,
      defaultValue: 0,
      comment: 'Contador de visualizações'
    },
    clicks: {
      type: DataTypes.INTEGER,
      defaultValue: 0,
      comment: 'Contador de cliques'
    },
    start_date: {
      type: DataTypes.DATE,
      allowNull: true,
      comment: 'Data de início da exibição'
    },
    end_date: {
      type: DataTypes.DATE,
      allowNull: true,
      comment: 'Data de fim da exibição'
    },
    active: {
      type: DataTypes.BOOLEAN,
      defaultValue: true,
      comment: 'Banner ativo ou inativo'
    }
  }, {
    tableName: 'banners',
    timestamps: true,
    underscored: true
  });

  // Método para incrementar visualizações
  Banner.prototype.incrementViews = async function() {
    this.views += 1;
    await this.save();
  };

  // Método para incrementar cliques
  Banner.prototype.incrementClicks = async function() {
    this.clicks += 1;
    await this.save();
  };

  // Método estático para buscar banners ativos por posição
  Banner.getByPosition = async function(position) {
    const now = new Date();
    return await this.findAll({
      where: {
        position,
        active: true,
        [sequelize.Sequelize.Op.or]: [
          { start_date: null },
          { start_date: { [sequelize.Sequelize.Op.lte]: now } }
        ],
        [sequelize.Sequelize.Op.or]: [
          { end_date: null },
          { end_date: { [sequelize.Sequelize.Op.gte]: now } }
        ]
      },
      order: [['order', 'ASC']]
    });
  };

  return Banner;
};
