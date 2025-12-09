'use strict';

/** @type {import('sequelize-cli').Migration} */
module.exports = {
  async up(queryInterface, Sequelize) {
    // As configurações de IA serão armazenadas na tabela settings existente
    // Esta migration é apenas para documentação, os valores serão inseridos via seeder
  },

  async down(queryInterface, Sequelize) {
    // Remove as configurações de IA
    await queryInterface.bulkDelete('settings', {
      key: {
        [Sequelize.Op.in]: [
          'ai_api_key',
          'ai_api_url',
          'ai_enabled',
          'ai_model'
        ]
      }
    });
  }
};
