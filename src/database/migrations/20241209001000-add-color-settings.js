'use strict';

module.exports = {
  async up(queryInterface, Sequelize) {
    const settings = [
      // Cores principais
      { key: 'color_primary', value: '#3b82f6', group: 'colors', label: 'Cor Principal', type: 'color', description: 'Cor principal do site (links, botões, destaques)' },
      { key: 'color_primary_dark', value: '#2563eb', group: 'colors', label: 'Cor Principal Escura', type: 'color', description: 'Versão escura da cor principal (hover)' },
      { key: 'color_secondary', value: '#0f172a', group: 'colors', label: 'Cor Secundária', type: 'color', description: 'Cor secundária (textos, header)' },
      
      // Header
      { key: 'color_header_bg', value: '#ffffff', group: 'colors', label: 'Fundo do Header', type: 'color', description: 'Cor de fundo do cabeçalho' },
      { key: 'color_header_text', value: '#0f172a', group: 'colors', label: 'Texto do Header', type: 'color', description: 'Cor do texto no cabeçalho' },
      { key: 'color_topbar_bg', value: '#0f172a', group: 'colors', label: 'Fundo da Barra Superior', type: 'color', description: 'Cor de fundo da barra superior' },
      { key: 'color_topbar_text', value: '#ffffff', group: 'colors', label: 'Texto da Barra Superior', type: 'color', description: 'Cor do texto na barra superior' },
      
      // Menu
      { key: 'color_menu_bg', value: '#ffffff', group: 'colors', label: 'Fundo do Menu', type: 'color', description: 'Cor de fundo do menu de navegação' },
      { key: 'color_menu_text', value: '#0f172a', group: 'colors', label: 'Texto do Menu', type: 'color', description: 'Cor do texto do menu' },
      { key: 'color_menu_hover', value: '#3b82f6', group: 'colors', label: 'Menu Hover', type: 'color', description: 'Cor do texto ao passar o mouse' },
      
      // Footer
      { key: 'color_footer_bg', value: '#0f172a', group: 'colors', label: 'Fundo do Rodapé', type: 'color', description: 'Cor de fundo do rodapé' },
      { key: 'color_footer_text', value: '#94a3b8', group: 'colors', label: 'Texto do Rodapé', type: 'color', description: 'Cor do texto no rodapé' },
      
      // Corpo
      { key: 'color_body_bg', value: '#f8fafc', group: 'colors', label: 'Fundo do Site', type: 'color', description: 'Cor de fundo geral do site' },
      { key: 'color_card_bg', value: '#ffffff', group: 'colors', label: 'Fundo dos Cards', type: 'color', description: 'Cor de fundo dos cards de notícias' },
      { key: 'color_text', value: '#0f172a', group: 'colors', label: 'Texto Principal', type: 'color', description: 'Cor do texto principal' },
      { key: 'color_text_muted', value: '#64748b', group: 'colors', label: 'Texto Secundário', type: 'color', description: 'Cor do texto secundário' },
    ];

    for (const setting of settings) {
      const exists = await queryInterface.rawSelect('settings', {
        where: { key: setting.key }
      }, ['id']);
      
      if (!exists) {
        await queryInterface.bulkInsert('settings', [{
          ...setting,
          created_at: new Date(),
          updated_at: new Date()
        }]);
      }
    }
  },

  async down(queryInterface, Sequelize) {
    await queryInterface.bulkDelete('settings', {
      key: {
        [Sequelize.Op.like]: 'color_%'
      }
    });
  }
};
