'use strict';

/** @type {import('sequelize-cli').Migration} */
module.exports = {
    async up(queryInterface, Sequelize) {
        // Adicionar coluna parent_id para criar hierarquia de categorias
        await queryInterface.addColumn('categories', 'parent_id', {
            type: Sequelize.INTEGER,
            allowNull: true,
            references: {
                model: 'categories',
                key: 'id'
            },
            onUpdate: 'CASCADE',
            onDelete: 'SET NULL'
        });

        // Adicionar índice para melhor performance
        await queryInterface.addIndex('categories', ['parent_id']);
    },

    async down(queryInterface, Sequelize) {
        await queryInterface.removeColumn('categories', 'parent_id');
    }
};
