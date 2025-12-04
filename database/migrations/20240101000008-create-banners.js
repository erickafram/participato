'use strict';

/** @type {import('sequelize-cli').Migration} */
module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('banners', {
      id: {
        type: Sequelize.INTEGER,
        primaryKey: true,
        autoIncrement: true
      },
      title: {
        type: Sequelize.STRING(255),
        allowNull: false,
        comment: 'Título/nome do banner para identificação'
      },
      image: {
        type: Sequelize.STRING(500),
        allowNull: false,
        comment: 'URL da imagem do banner'
      },
      link: {
        type: Sequelize.STRING(500),
        allowNull: true,
        comment: 'URL de destino ao clicar no banner'
      },
      position: {
        type: Sequelize.ENUM(
          'home_top',
          'home_middle',
          'home_bottom',
          'home_sidebar',
          'post_top',
          'post_middle',
          'post_bottom',
          'post_sidebar',
          'category_top',
          'category_bottom',
          'category_sidebar'
        ),
        allowNull: false,
        comment: 'Posição onde o banner será exibido'
      },
      size: {
        type: Sequelize.ENUM(
          '728x90',
          '300x250',
          '336x280',
          '300x600',
          '320x100',
          '970x90',
          '970x250',
          '160x600',
          '300x50',
          'responsive'
        ),
        allowNull: false,
        defaultValue: '728x90',
        comment: 'Tamanho do banner'
      },
      alt_text: {
        type: Sequelize.STRING(255),
        allowNull: true,
        comment: 'Texto alternativo para acessibilidade'
      },
      target: {
        type: Sequelize.ENUM('_self', '_blank'),
        defaultValue: '_blank',
        comment: 'Abrir link na mesma aba ou nova aba'
      },
      order: {
        type: Sequelize.INTEGER,
        defaultValue: 0,
        comment: 'Ordem de exibição'
      },
      views: {
        type: Sequelize.INTEGER,
        defaultValue: 0,
        comment: 'Contador de visualizações'
      },
      clicks: {
        type: Sequelize.INTEGER,
        defaultValue: 0,
        comment: 'Contador de cliques'
      },
      start_date: {
        type: Sequelize.DATE,
        allowNull: true,
        comment: 'Data de início da exibição'
      },
      end_date: {
        type: Sequelize.DATE,
        allowNull: true,
        comment: 'Data de fim da exibição'
      },
      active: {
        type: Sequelize.BOOLEAN,
        defaultValue: true,
        comment: 'Banner ativo ou inativo'
      },
      created_at: {
        type: Sequelize.DATE,
        allowNull: false,
        defaultValue: Sequelize.literal('CURRENT_TIMESTAMP')
      },
      updated_at: {
        type: Sequelize.DATE,
        allowNull: false,
        defaultValue: Sequelize.literal('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')
      }
    });

    // Índices
    await queryInterface.addIndex('banners', ['position']);
    await queryInterface.addIndex('banners', ['active']);
    await queryInterface.addIndex('banners', ['start_date', 'end_date']);
  },

  async down(queryInterface, Sequelize) {
    await queryInterface.dropTable('banners');
  }
};
