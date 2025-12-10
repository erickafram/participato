'use strict';

module.exports = {
  async up(queryInterface, Sequelize) {
    // Criar tabela de peticionários (cidadãos)
    await queryInterface.createTable('petitioners', {
      id: {
        type: Sequelize.INTEGER,
        primaryKey: true,
        autoIncrement: true
      },
      name: {
        type: Sequelize.STRING(100),
        allowNull: false
      },
      email: {
        type: Sequelize.STRING(150),
        allowNull: false,
        unique: true
      },
      phone: {
        type: Sequelize.STRING(20),
        allowNull: false
      },
      password_hash: {
        type: Sequelize.STRING(255),
        allowNull: false
      },
      active: {
        type: Sequelize.BOOLEAN,
        defaultValue: true
      },
      email_verified: {
        type: Sequelize.BOOLEAN,
        defaultValue: false
      },
      verification_token: {
        type: Sequelize.STRING(100),
        allowNull: true
      },
      last_login: {
        type: Sequelize.DATE,
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

    // Adicionar novos campos na tabela petitions
    await queryInterface.addColumn('petitions', 'petitioner_id', {
      type: Sequelize.INTEGER,
      allowNull: true,
      references: {
        model: 'petitioners',
        key: 'id'
      },
      onUpdate: 'CASCADE',
      onDelete: 'SET NULL'
    });

    await queryInterface.addColumn('petitions', 'admin_notes', {
      type: Sequelize.TEXT,
      allowNull: true
    });

    await queryInterface.addColumn('petitions', 'rejection_reason', {
      type: Sequelize.TEXT,
      allowNull: true
    });

    await queryInterface.addColumn('petitions', 'approved_at', {
      type: Sequelize.DATE,
      allowNull: true
    });

    await queryInterface.addColumn('petitions', 'approved_by', {
      type: Sequelize.INTEGER,
      allowNull: true,
      references: {
        model: 'users',
        key: 'id'
      },
      onUpdate: 'CASCADE',
      onDelete: 'SET NULL'
    });

    // Alterar ENUM de status para incluir pending e rejected
    await queryInterface.changeColumn('petitions', 'status', {
      type: Sequelize.ENUM('pending', 'draft', 'active', 'closed', 'victory', 'rejected'),
      defaultValue: 'pending'
    });

    // Tornar author_id opcional
    await queryInterface.changeColumn('petitions', 'author_id', {
      type: Sequelize.INTEGER,
      allowNull: true
    });

    // Adicionar petitioner_id na tabela de assinaturas
    await queryInterface.addColumn('petition_signatures', 'petitioner_id', {
      type: Sequelize.INTEGER,
      allowNull: true,
      references: {
        model: 'petitioners',
        key: 'id'
      },
      onUpdate: 'CASCADE',
      onDelete: 'SET NULL'
    });
  },

  async down(queryInterface, Sequelize) {
    await queryInterface.removeColumn('petition_signatures', 'petitioner_id');
    await queryInterface.removeColumn('petitions', 'approved_by');
    await queryInterface.removeColumn('petitions', 'approved_at');
    await queryInterface.removeColumn('petitions', 'rejection_reason');
    await queryInterface.removeColumn('petitions', 'admin_notes');
    await queryInterface.removeColumn('petitions', 'petitioner_id');
    await queryInterface.dropTable('petitioners');
    
    await queryInterface.changeColumn('petitions', 'status', {
      type: Sequelize.ENUM('draft', 'active', 'closed', 'victory'),
      defaultValue: 'draft'
    });
  }
};
