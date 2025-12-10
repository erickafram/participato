'use strict';

module.exports = {
  async up(queryInterface, Sequelize) {
    // Criar tabela de petições
    await queryInterface.createTable('petitions', {
      id: {
        type: Sequelize.INTEGER,
        primaryKey: true,
        autoIncrement: true
      },
      title: {
        type: Sequelize.STRING(255),
        allowNull: false
      },
      slug: {
        type: Sequelize.STRING(255),
        allowNull: false,
        unique: true
      },
      description: {
        type: Sequelize.TEXT,
        allowNull: false
      },
      content: {
        type: Sequelize.TEXT,
        allowNull: true
      },
      image: {
        type: Sequelize.STRING(255),
        allowNull: true
      },
      goal: {
        type: Sequelize.INTEGER,
        allowNull: false,
        defaultValue: 1000
      },
      author_id: {
        type: Sequelize.INTEGER,
        allowNull: false,
        references: {
          model: 'users',
          key: 'id'
        },
        onUpdate: 'CASCADE',
        onDelete: 'CASCADE'
      },
      category: {
        type: Sequelize.STRING(100),
        allowNull: true
      },
      status: {
        type: Sequelize.ENUM('draft', 'active', 'closed', 'victory'),
        defaultValue: 'draft'
      },
      featured: {
        type: Sequelize.BOOLEAN,
        defaultValue: false
      },
      allow_anonymous: {
        type: Sequelize.BOOLEAN,
        defaultValue: true
      },
      show_signatures: {
        type: Sequelize.BOOLEAN,
        defaultValue: true
      },
      end_date: {
        type: Sequelize.DATE,
        allowNull: true
      },
      views: {
        type: Sequelize.INTEGER,
        defaultValue: 0
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

    // Criar tabela de assinaturas
    await queryInterface.createTable('petition_signatures', {
      id: {
        type: Sequelize.INTEGER,
        primaryKey: true,
        autoIncrement: true
      },
      petition_id: {
        type: Sequelize.INTEGER,
        allowNull: false,
        references: {
          model: 'petitions',
          key: 'id'
        },
        onUpdate: 'CASCADE',
        onDelete: 'CASCADE'
      },
      user_id: {
        type: Sequelize.INTEGER,
        allowNull: true,
        references: {
          model: 'users',
          key: 'id'
        },
        onUpdate: 'CASCADE',
        onDelete: 'SET NULL'
      },
      name: {
        type: Sequelize.STRING(100),
        allowNull: false
      },
      email: {
        type: Sequelize.STRING(255),
        allowNull: false
      },
      city: {
        type: Sequelize.STRING(100),
        allowNull: true
      },
      state: {
        type: Sequelize.STRING(50),
        allowNull: true
      },
      comment: {
        type: Sequelize.TEXT,
        allowNull: true
      },
      is_public: {
        type: Sequelize.BOOLEAN,
        defaultValue: true
      },
      verified: {
        type: Sequelize.BOOLEAN,
        defaultValue: false
      },
      verification_token: {
        type: Sequelize.STRING(100),
        allowNull: true
      },
      ip_address: {
        type: Sequelize.STRING(45),
        allowNull: true
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

    // Índice único para evitar assinaturas duplicadas
    await queryInterface.addIndex('petition_signatures', ['petition_id', 'email'], {
      unique: true,
      name: 'petition_signatures_unique_email'
    });

    // Adicionar configuração do módulo
    await queryInterface.bulkInsert('settings', [{
      key: 'module_petitions',
      value: 'true',
      type: 'boolean',
      group: 'modules',
      label: 'Módulo de Petições',
      description: 'Habilitar ou desabilitar o módulo de petições',
      created_at: new Date(),
      updated_at: new Date()
    }]);
  },

  async down(queryInterface) {
    await queryInterface.dropTable('petition_signatures');
    await queryInterface.dropTable('petitions');
    await queryInterface.bulkDelete('settings', { key: 'module_petitions' });
  }
};
