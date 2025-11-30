'use strict';

/** @type {import('sequelize-cli').Migration} */
module.exports = {
  async up(queryInterface, Sequelize) {
    const now = new Date();
    
    await queryInterface.bulkInsert('posts', [
      {
        title: 'Bem-vindo ao Portal Convictos',
        subtitle: 'Seu novo destino para notícias e entretenimento de qualidade',
        slug: 'bem-vindo-ao-portal-convictos',
        content: `
          <p>É com grande satisfação que apresentamos o <strong>Portal Convictos</strong>, sua nova fonte de informação e entretenimento!</p>
          <p>Nosso portal foi desenvolvido com o objetivo de trazer conteúdo de qualidade, com uma experiência de leitura agradável e moderna.</p>
        `,
        excerpt: 'É com grande satisfação que apresentamos o Portal Convictos, sua nova fonte de informação e entretenimento de qualidade!',
        tags: 'portal,lançamento,novidades',
        status: 'published',
        featured: true,
        views: 150,
        meta_title: 'Bem-vindo ao Portal Convictos',
        meta_description: 'Conheça o Portal Convictos, seu novo destino para notícias e entretenimento de qualidade.',
        published_at: now,
        featured_image: 'https://picsum.photos/seed/welcome/800/600',
        category_id: 1,
        author_id: 1,
        created_at: now,
        updated_at: now
      },
      {
        title: 'As 10 Séries Mais Aguardadas de 2024',
        subtitle: 'Confira as produções que prometem conquistar o público este ano',
        slug: 'as-10-series-mais-aguardadas-de-2024',
        content: `
          <p>O ano de 2024 promete ser repleto de grandes lançamentos no mundo das séries. Preparamos uma lista com as produções mais aguardadas!</p>
        `,
        excerpt: 'O ano de 2024 promete ser repleto de grandes lançamentos no mundo das séries. Confira nossa lista das mais aguardadas!',
        tags: 'séries,streaming,entretenimento,2024',
        status: 'published',
        featured: true,
        views: 320,
        meta_title: 'As 10 Séries Mais Aguardadas de 2024',
        meta_description: 'Confira as séries mais aguardadas de 2024.',
        published_at: new Date(now.getTime() - 86400000),
        featured_image: 'https://picsum.photos/seed/series2024/800/600',
        category_id: 2,
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000),
        updated_at: new Date(now.getTime() - 86400000)
      },
      {
        title: 'Inteligência Artificial: O Futuro Chegou',
        subtitle: 'Como a IA está transformando nossa vida cotidiana',
        slug: 'inteligencia-artificial-o-futuro-chegou',
        content: `
          <p>A Inteligência Artificial deixou de ser ficção científica e se tornou parte integral do nosso dia a dia.</p>
        `,
        excerpt: 'A Inteligência Artificial deixou de ser ficção científica e se tornou parte integral do nosso dia a dia.',
        tags: 'inteligência artificial,tecnologia,inovação,futuro',
        status: 'published',
        featured: true,
        views: 450,
        meta_title: 'Inteligência Artificial: O Futuro Chegou',
        meta_description: 'Descubra como a Inteligência Artificial está transformando nossa vida cotidiana.',
        published_at: new Date(now.getTime() - 172800000),
        featured_image: 'https://picsum.photos/seed/ai/800/600',
        category_id: 3,
        author_id: 1,
        created_at: new Date(now.getTime() - 172800000),
        updated_at: new Date(now.getTime() - 172800000)
      },
      {
        title: 'Copa do Mundo 2026: O Que Esperar',
        subtitle: 'Primeira edição com 48 seleções promete ser histórica',
        slug: 'copa-do-mundo-2026-o-que-esperar',
        content: `
          <p>A Copa do Mundo de 2026, que será realizada nos Estados Unidos, Canadá e México, promete ser a maior da história.</p>
        `,
        excerpt: 'A Copa do Mundo de 2026 promete ser a maior da história, com 48 seleções e jogos em três países.',
        tags: 'copa do mundo,futebol,esportes,2026',
        status: 'published',
        featured: false,
        views: 280,
        meta_title: 'Copa do Mundo 2026: O Que Esperar',
        meta_description: 'Saiba tudo sobre a Copa do Mundo 2026.',
        published_at: new Date(now.getTime() - 259200000),
        featured_image: 'https://picsum.photos/seed/worldcup/800/600',
        category_id: 4,
        author_id: 1,
        created_at: new Date(now.getTime() - 259200000),
        updated_at: new Date(now.getTime() - 259200000)
      },
      {
        title: 'Exposição Imersiva de Van Gogh Chega ao Brasil',
        subtitle: 'Experiência única permite "entrar" nas obras do mestre holandês',
        slug: 'exposicao-imersiva-van-gogh-brasil',
        content: `
          <p>Uma das exposições mais aclamadas do mundo chega ao Brasil, oferecendo uma experiência única com as obras de Vincent van Gogh.</p>
        `,
        excerpt: 'Uma das exposições mais aclamadas do mundo chega ao Brasil com uma experiência imersiva nas obras de Van Gogh.',
        tags: 'arte,exposição,van gogh,cultura',
        status: 'published',
        featured: true,
        views: 190,
        meta_title: 'Exposição Imersiva de Van Gogh Chega ao Brasil',
        meta_description: 'Conheça a exposição imersiva de Van Gogh.',
        published_at: new Date(now.getTime() - 345600000),
        featured_image: 'https://picsum.photos/seed/vangogh/800/600',
        category_id: 5,
        author_id: 1,
        created_at: new Date(now.getTime() - 345600000),
        updated_at: new Date(now.getTime() - 345600000)
      },
      {
        title: 'Dicas para Economizar nas Compras Online',
        subtitle: 'Aprenda a encontrar as melhores ofertas na internet',
        slug: 'dicas-economizar-compras-online',
        content: `
          <p>Comprar online pode ser muito vantajoso, mas é preciso saber onde e como procurar as melhores ofertas.</p>
        `,
        excerpt: 'Aprenda estratégias para economizar nas compras online e encontrar as melhores ofertas.',
        tags: 'economia,compras,dicas,online',
        status: 'published',
        featured: false,
        views: 520,
        meta_title: 'Dicas para Economizar nas Compras Online',
        meta_description: 'Confira dicas práticas para economizar nas suas compras online.',
        published_at: new Date(now.getTime() - 432000000),
        featured_image: 'https://picsum.photos/seed/shopping/800/600',
        category_id: 1,
        author_id: 1,
        created_at: new Date(now.getTime() - 432000000),
        updated_at: new Date(now.getTime() - 432000000)
      }
    ]);
  },

  async down(queryInterface, Sequelize) {
    await queryInterface.bulkDelete('posts', null, {});
  }
};
