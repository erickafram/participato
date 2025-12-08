'use strict';

/** @type {import('sequelize-cli').Migration} */
module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('home_blocks', {
      id: {
        type: Sequelize.INTEGER,
        primaryKey: true,
        autoIncrement: true
      },
      type: {
        type: Sequelize.STRING(30),
        allowNull: false
      },
      title: {
        type: Sequelize.STRING(100),
        allowNull: true
      },
      category_id: {
        type: Sequelize.INTEGER,
        allowNull: true,
        references: {
          model: 'categories',
          key: 'id'
        },
        onUpdate: 'CASCADE',
        onDelete: 'SET NULL'
      },
      banner_id: {
        type: Sequelize.INTEGER,
        allowNull: true,
        references: {
          model: 'banners',
          key: 'id'
        },
        onUpdate: 'CASCADE',
        onDelete: 'SET NULL'
      },
      posts_count: {
        type: Sequelize.INTEGER,
        defaultValue: 4
      },
      show_title: {
        type: Sequelize.BOOLEAN,
        defaultValue: true
      },
      show_excerpt: {
        type: Sequelize.BOOLEAN,
        defaultValue: true
      },
      show_date: {
        type: Sequelize.BOOLEAN,
        defaultValue: true
      },
      show_category: {
        type: Sequelize.BOOLEAN,
        defaultValue: true
      },
      background_color: {
        type: Sequelize.STRING(20),
        defaultValue: '#ffffff'
      },
      order: {
        type: Sequelize.INTEGER,
        defaultValue: 0
      },
      active: {
        type: Sequelize.BOOLEAN,
        defaultValue: true
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

    await queryInterface.addIndex('home_blocks', ['category_id']);
    await queryInterface.addIndex('home_blocks', ['banner_id']);
    await queryInterface.addIndex('home_blocks', ['order']);
    await queryInterface.addIndex('home_blocks', ['active']);
  },

  async down(queryInterface) {
    await queryInterface.dropTable('home_blocks');
  }
};
