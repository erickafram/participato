/**
 * Helper para buscar banners ativos
 */
const { Banner } = require('../models');
const { Op } = require('sequelize');

/**
 * Busca banners ativos por posição
 * @param {string|string[]} positions - Posição ou array de posições
 * @returns {Object} Objeto com banners agrupados por posição
 */
async function getBanners(positions) {
  try {
    const now = new Date();
    
    // Converter para array se for string única
    const positionArray = Array.isArray(positions) ? positions : [positions];
    
    const banners = await Banner.findAll({
      where: {
        position: { [Op.in]: positionArray },
        active: true,
        [Op.or]: [
          { start_date: null },
          { start_date: { [Op.lte]: now } }
        ]
      },
      order: [['position', 'ASC'], ['order', 'ASC']]
    });

    // Filtrar por data de término (precisa ser feito em JS por causa do OR com null)
    const activeBanners = banners.filter(banner => {
      if (!banner.end_date) return true;
      return new Date(banner.end_date) >= now;
    });

    // Agrupar por posição
    const grouped = {};
    for (const pos of positionArray) {
      grouped[pos] = activeBanners.filter(b => b.position === pos);
    }

    // Incrementar views dos banners retornados
    for (const banner of activeBanners) {
      banner.views += 1;
      await banner.save({ silent: true });
    }

    return grouped;
  } catch (error) {
    console.error('Erro ao buscar banners:', error);
    // Retornar objeto vazio em caso de erro para não quebrar a página
    const result = {};
    const positionArray = Array.isArray(positions) ? positions : [positions];
    for (const pos of positionArray) {
      result[pos] = [];
    }
    return result;
  }
}

/**
 * Gera o HTML de um banner
 * @param {Object} banner - Objeto do banner
 * @returns {string} HTML do banner
 */
function renderBanner(banner) {
  if (!banner) return '';
  
  const sizeStyles = {
    '728x90': 'width: 728px; height: 90px;',
    '300x250': 'width: 300px; height: 250px;',
    '336x280': 'width: 336px; height: 280px;',
    '300x600': 'width: 300px; height: 600px;',
    '320x100': 'width: 320px; height: 100px;',
    '970x90': 'width: 970px; height: 90px;',
    '970x250': 'width: 970px; height: 250px;',
    '160x600': 'width: 160px; height: 600px;',
    '300x50': 'width: 300px; height: 50px;',
    'responsive': 'width: 100%; height: auto;'
  };

  const style = sizeStyles[banner.size] || sizeStyles['responsive'];
  const maxWidth = banner.size !== 'responsive' ? `max-width: ${banner.size.split('x')[0]}px;` : '';

  let html = `<div class="banner-container" style="${maxWidth}">`;
  
  if (banner.link) {
    html += `<a href="${banner.link}" target="${banner.target}" rel="noopener" onclick="trackBannerClick(${banner.id})">`;
  }
  
  html += `<img src="${banner.image}" alt="${banner.alt_text || banner.title}" style="${style} max-width: 100%; height: auto;" loading="lazy">`;
  
  if (banner.link) {
    html += `</a>`;
  }
  
  html += `</div>`;
  
  return html;
}

/**
 * Gera o HTML de múltiplos banners
 * @param {Array} banners - Array de banners
 * @returns {string} HTML dos banners
 */
function renderBanners(banners) {
  if (!banners || banners.length === 0) return '';
  return banners.map(b => renderBanner(b)).join('');
}

module.exports = {
  getBanners,
  renderBanner,
  renderBanners
};
