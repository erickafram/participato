'use strict';

/** @type {import('sequelize-cli').Migration} */
module.exports = {
  async up(queryInterface, Sequelize) {
    const now = new Date();
    
    // Verifica se as configurações já existem
    const existingSettings = await queryInterface.sequelize.query(
      `SELECT \`key\` FROM settings WHERE \`key\` IN ('ai_api_key', 'ai_api_url', 'ai_enabled', 'ai_model')`,
      { type: Sequelize.QueryTypes.SELECT }
    );
    
    const existingKeys = existingSettings.map(s => s.key);
    
    const settings = [
      {
        key: 'ai_api_key',
        value: '',
        type: 'text',
        group: 'ai',
        label: 'Chave da API Together AI',
        description: 'Obtenha sua chave em: https://api.together.xyz',
        created_at: now,
        updated_at: now
      },
      {
        key: 'ai_api_url',
        value: 'https://api.together.xyz/v1/chat/completions',
        type: 'text',
        group: 'ai',
        label: 'URL da API Together AI',
        description: null,
        created_at: now,
        updated_at: now
      },
      {
        key: 'ai_enabled',
        value: 'false',
        type: 'boolean',
        group: 'ai',
        label: 'Status do Assistente',
        description: null,
        created_at: now,
        updated_at: now
      },
      {
        key: 'ai_model',
        value: 'meta-llama/Llama-3-70b-chat-hf',
        type: 'text',
        group: 'ai',
        label: 'Modelo de IA',
        description: null,
        created_at: now,
        updated_at: now
      }
    ].filter(s => !existingKeys.includes(s.key));
    
    if (settings.length > 0) {
      await queryInterface.bulkInsert('settings', settings);
    }
  },

  async down(queryInterface, Sequelize) {
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
