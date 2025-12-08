/**
 * Seeder para configurações do Assistente de IA
 * Execute: node src/database/seeders/ai-settings.js
 */
const { Setting } = require('../../models');

const aiSettings = [
  {
    key: 'ai_enabled',
    value: 'false',
    type: 'boolean',
    group: 'ai',
    label: 'Assistente de IA',
    description: 'Ativa ou desativa o assistente de IA'
  },
  {
    key: 'ai_api_key',
    value: '',
    type: 'text',
    group: 'ai',
    label: 'Chave da API Together AI',
    description: 'Obtenha sua chave em: https://api.together.xyz'
  },
  {
    key: 'ai_api_url',
    value: 'https://api.together.xyz/v1/chat/completions',
    type: 'text',
    group: 'ai',
    label: 'URL da API',
    description: 'URL do endpoint da API'
  },
  {
    key: 'ai_model',
    value: 'meta-llama/Llama-3-70b-chat-hf',
    type: 'text',
    group: 'ai',
    label: 'Modelo de IA',
    description: 'Modelo a ser utilizado (ex: meta-llama/Llama-3-70b-chat-hf)'
  }
];

async function seed() {
  try {
    for (const setting of aiSettings) {
      const [record, created] = await Setting.findOrCreate({
        where: { key: setting.key },
        defaults: setting
      });
      console.log(`${setting.key}: ${created ? 'criado' : 'já existe'}`);
    }
    console.log('\nConfigurações de IA adicionadas com sucesso!');
    process.exit(0);
  } catch (error) {
    console.error('Erro:', error);
    process.exit(1);
  }
}

seed();
