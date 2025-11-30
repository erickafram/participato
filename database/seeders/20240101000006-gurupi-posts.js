'use strict';

/** @type {import('sequelize-cli').Migration} */
module.exports = {
  async up (queryInterface, Sequelize) {
    const now = new Date();
    
    const posts = [
      {
        title: 'Prefeitura de Gurupi anuncia grande pacote de obras para infraestrutura urbana',
        subtitle: 'Investimento milionário prevê recapeamento asfáltico em diversos bairros e construção de novas praças.',
        slug: 'prefeitura-gurupi-anuncia-pacote-obras-infraestrutura',
        content: '<p>A Prefeitura de Gurupi anunciou nesta manhã um ambicioso pacote de obras voltado para a melhoria da infraestrutura urbana da cidade. O projeto inclui o recapeamento de mais de 50km de vias, além da revitalização de espaços públicos.</p><p>"É um momento histórico para Gurupi. Estamos investindo na qualidade de vida da nossa população", afirmou a prefeita durante a coletiva.</p><p>As obras devem começar já no próximo mês, priorizando os bairros mais afastados do centro.</p>',
        excerpt: 'Investimento milionário prevê recapeamento asfáltico em diversos bairros e construção de novas praças na capital da amizade.',
        featured_image: 'https://picsum.photos/seed/obras/800/600',
        status: 'published',
        featured: true,
        views: 1540,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 1), // 1 dia atrás
        updated_at: new Date(now.getTime() - 86400000 * 1)
      },
      {
        title: 'UnirG abre inscrições para vestibular de Medicina e outros cursos',
        subtitle: 'Universidade de Gurupi oferece centenas de vagas para o próximo semestre letivo.',
        slug: 'unirg-abre-inscricoes-vestibular-medicina',
        content: '<p>A Universidade de Gurupi (UnirG) abriu hoje o período de inscrições para o vestibular do próximo semestre. O destaque continua sendo o curso de Medicina, um dos mais concorridos da região norte do país.</p><p>Os interessados podem se inscrever através do site oficial da instituição. As provas serão realizadas em duas etapas.</p>',
        excerpt: 'Universidade de Gurupi oferece centenas de vagas para o próximo semestre letivo com destaque para Medicina.',
        featured_image: 'https://picsum.photos/seed/unirg/800/600',
        status: 'published',
        featured: false,
        views: 2300,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 2),
        updated_at: new Date(now.getTime() - 86400000 * 2)
      },
      {
        title: 'Gurupi se prepara para receber a maior feira agrotecnológica do sul do estado',
        subtitle: 'Evento promete movimentar milhões em negócios e trazer inovações para o campo.',
        slug: 'gurupi-feira-agrotecnologica-sul-tocantins',
        content: '<p>O Parque de Exposições de Gurupi já está sendo preparado para receber a Agrotec, maior feira de tecnologia agrícola do sul do Tocantins. O evento contará com expositores de todo o Brasil.</p><p>Palestras, leilões e demonstrações de maquinário de última geração fazem parte da programação.</p>',
        excerpt: 'Evento promete movimentar milhões em negócios e trazer inovações para o campo tocantinense.',
        featured_image: 'https://picsum.photos/seed/agro/800/600',
        status: 'published',
        featured: true,
        views: 890,
        category_id: 3, // Tecnologia (adaptado para agro)
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 3),
        updated_at: new Date(now.getTime() - 86400000 * 3)
      },
      {
        title: 'Gurupi Esporte Clube apresenta reforços para o Campeonato Tocantinense',
        subtitle: 'Camaleão do Sul busca o título estadual com elenco renovado.',
        slug: 'gurupi-esporte-clube-reforcos-tocantinense',
        content: '<p>O Gurupi Esporte Clube apresentou na tarde de ontem o elenco que disputará a primeira divisão do Campeonato Tocantinense. O time, conhecido como Camaleão do Sul, aposta na mescla de jovens talentos da base com jogadores experientes.</p>',
        excerpt: 'Camaleão do Sul busca o título estadual com elenco renovado e aposta na base.',
        featured_image: 'https://picsum.photos/seed/camaleao/800/600',
        status: 'published',
        featured: false,
        views: 1120,
        category_id: 4, // Esportes
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 4),
        updated_at: new Date(now.getTime() - 86400000 * 4)
      },
      {
        title: 'Festival Cultural de Gurupi reúne artistas locais na Praça do Centro Cultural',
        subtitle: 'Música, dança e gastronomia marcaram o fim de semana na cidade.',
        slug: 'festival-cultural-gurupi-praca-centro',
        content: '<p>A cultura gurupiense esteve em evidência neste fim de semana. A Praça do Centro Cultural Mauro Cunha foi palco de diversas apresentações artísticas, reunindo famílias e jovens.</p><p>Além dos shows, barracas de comida típica do Tocantins fizeram sucesso entre os visitantes.</p>',
        excerpt: 'Música, dança e gastronomia marcaram o fim de semana na cidade com artistas locais.',
        featured_image: 'https://picsum.photos/seed/culturagurupi/800/600',
        status: 'published',
        featured: false,
        views: 650,
        category_id: 5, // Cultura
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 5),
        updated_at: new Date(now.getTime() - 86400000 * 5)
      },
      {
        title: 'Novas empresas se instalam no Parque Industrial de Gurupi',
        subtitle: 'Expectativa é gerar mais de 500 empregos diretos nos próximos meses.',
        slug: 'novas-empresas-parque-industrial-gurupi',
        content: '<p>O desenvolvimento econômico de Gurupi segue em ritmo acelerado. Duas novas indústrias assinaram contrato para instalação no PIG (Parque Industrial de Gurupi).</p><p>O secretário de desenvolvimento destacou a posição estratégica da cidade como fator decisivo para a atração de investimentos.</p>',
        excerpt: 'Expectativa é gerar mais de 500 empregos diretos nos próximos meses com novas indústrias.',
        featured_image: 'https://picsum.photos/seed/industria/800/600',
        status: 'published',
        featured: true,
        views: 1800,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 6),
        updated_at: new Date(now.getTime() - 86400000 * 6)
      },
      {
        title: 'Previsão do tempo: Calor intenso deve continuar em Gurupi esta semana',
        subtitle: 'Termômetros podem chegar aos 38ºC; Defesa Civil alerta para baixa umidade.',
        slug: 'previsao-tempo-calor-intenso-gurupi',
        content: '<p>Preparem-se para dias quentes. A previsão meteorológica indica que a massa de ar quente sobre o Tocantins continuará atuando, elevando as temperaturas em Gurupi.</p><p>A recomendação é beber muita água e evitar exposição ao sol nos horários de pico.</p>',
        excerpt: 'Termômetros podem chegar aos 38ºC; Defesa Civil alerta para baixa umidade do ar.',
        featured_image: 'https://picsum.photos/seed/calor/800/600',
        status: 'published',
        featured: false,
        views: 3200,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 7),
        updated_at: new Date(now.getTime() - 86400000 * 7)
      },
      {
        title: 'Campanha de vacinação atinge meta em Gurupi',
        subtitle: 'Secretaria de Saúde comemora adesão da população.',
        slug: 'campanha-vacinacao-meta-gurupi',
        content: '<p>Gurupi é exemplo mais uma vez. A campanha de multivacinação atingiu 95% do público-alvo antes mesmo do prazo final.</p><p>As unidades de saúde continuarão atendendo para manter a caderneta em dia.</p>',
        excerpt: 'Secretaria de Saúde comemora adesão da população e cobertura vacinal de 95%.',
        featured_image: 'https://picsum.photos/seed/vacina/800/600',
        status: 'published',
        featured: false,
        views: 450,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 8),
        updated_at: new Date(now.getTime() - 86400000 * 8)
      },
      {
        title: 'BR-153: Fluxo intenso de veículos no trecho de Gurupi devido ao feriado',
        subtitle: 'Polícia Rodoviária Federal intensifica fiscalização na rodovia Belém-Brasília.',
        slug: 'br-153-fluxo-intenso-gurupi-feriado',
        content: '<p>Quem vai pegar a estrada deve ter paciência. O fluxo de veículos na BR-153, que corta Gurupi, aumentou consideravelmente nas últimas horas.</p>',
        excerpt: 'Polícia Rodoviária Federal intensifica fiscalização na rodovia Belém-Brasília para garantir segurança.',
        featured_image: 'https://picsum.photos/seed/transito/800/600',
        status: 'published',
        featured: false,
        views: 980,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 9),
        updated_at: new Date(now.getTime() - 86400000 * 9)
      },
      {
        title: 'Startup de Gurupi vence prêmio estadual de inovação',
        subtitle: 'Jovens empreendedores desenvolveram aplicativo para gestão rural.',
        slug: 'startup-gurupi-premio-inovacao',
        content: '<p>Uma startup nascida na incubadora da UnirG conquistou o primeiro lugar no Prêmio Tocantins de Inovação. O aplicativo desenvolvido facilita a vida do pequeno produtor rural.</p>',
        excerpt: 'Jovens empreendedores desenvolveram aplicativo para gestão rural e ganham destaque.',
        featured_image: 'https://picsum.photos/seed/startup/800/600',
        status: 'published',
        featured: true,
        views: 1200,
        category_id: 3, // Tecnologia
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 10),
        updated_at: new Date(now.getTime() - 86400000 * 10)
      },
      {
        title: 'Mercado Municipal de Gurupi passa por reforma e modernização',
        subtitle: 'Feirantes e consumidores terão mais conforto e higiene.',
        slug: 'mercado-municipal-gurupi-reforma',
        content: '<p>O tradicional Mercado Municipal de Gurupi está de cara nova. A reforma incluiu troca do telhado, novo piso e padronização das bancas.</p>',
        excerpt: 'Feirantes e consumidores terão mais conforto e higiene com as novas instalações.',
        featured_image: 'https://picsum.photos/seed/mercado/800/600',
        status: 'published',
        featured: false,
        views: 700,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 11),
        updated_at: new Date(now.getTime() - 86400000 * 11)
      },
      {
        title: 'Corrida de Rua de Gurupi reúne mais de mil atletas',
        subtitle: 'Evento esportivo movimentou a avenida Goiás na manhã de domingo.',
        slug: 'corrida-rua-gurupi-atletas',
        content: '<p>Saúde e superação. A Corrida de Rua de Gurupi foi um sucesso absoluto, reunindo atletas profissionais e amadores de toda a região.</p>',
        excerpt: 'Evento esportivo movimentou a avenida Goiás e incentivou a prática de atividades físicas.',
        featured_image: 'https://picsum.photos/seed/run/800/600',
        status: 'published',
        featured: false,
        views: 850,
        category_id: 4, // Esportes
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 12),
        updated_at: new Date(now.getTime() - 86400000 * 12)
      },
      {
        title: 'Cinema de Gurupi exibe mostra de filmes regionais',
        subtitle: 'Cineastas tocantinenses têm suas obras prestigiadas na tela grande.',
        slug: 'cinema-gurupi-mostra-filmes-regionais',
        content: '<p>Valorizando a arte local, o cinema de Gurupi promove nesta semana uma mostra especial com produções 100% tocantinenses. A entrada é gratuita.</p>',
        excerpt: 'Cineastas tocantinenses têm suas obras prestigiadas na tela grande em evento gratuito.',
        featured_image: 'https://picsum.photos/seed/cinema/800/600',
        status: 'published',
        featured: false,
        views: 400,
        category_id: 2, // Entretenimento
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 13),
        updated_at: new Date(now.getTime() - 86400000 * 13)
      },
      {
        title: 'Coleta Seletiva é ampliada para novos bairros em Gurupi',
        subtitle: 'Sustentabilidade: Caminhões passarão três vezes por semana.',
        slug: 'coleta-seletiva-ampliada-bairros-gurupi',
        content: '<p>A Prefeitura de Gurupi expandiu o programa de coleta seletiva. Agora, os bairros da região sul também serão atendidos, reforçando o compromisso ambiental da cidade.</p>',
        excerpt: 'Sustentabilidade: Caminhões da coleta seletiva passarão três vezes por semana em novas rotas.',
        featured_image: 'https://picsum.photos/seed/lixo/800/600',
        status: 'published',
        featured: false,
        views: 550,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 14),
        updated_at: new Date(now.getTime() - 86400000 * 14)
      },
      {
        title: 'Show Gospel atrai multidão para o Centro de Convenções',
        subtitle: 'Cantor nacional emocionou o público presente em Gurupi.',
        slug: 'show-gospel-multidao-centro-convencoes',
        content: '<p>Uma noite de louvor e adoração. O show gospel realizado ontem lotou o pátio do Centro de Convenções de Gurupi, reunindo fiéis de diversas denominações.</p>',
        excerpt: 'Cantor nacional emocionou o público presente em uma noite de louvor e adoração.',
        featured_image: 'https://picsum.photos/seed/gospel/800/600',
        status: 'published',
        featured: true,
        views: 2100,
        category_id: 2, // Entretenimento
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 15),
        updated_at: new Date(now.getTime() - 86400000 * 15)
      },
      {
        title: 'Iluminação de LED chega a 100% das ruas de Gurupi',
        subtitle: 'Projeto "Gurupi Mais Iluminada" conclui instalação em tempo recorde.',
        slug: 'iluminacao-led-100-porcento-ruas-gurupi',
        content: '<p>Gurupi agora é 100% LED. A prefeitura concluiu a substituição das antigas lâmpadas de vapor de sódio, garantindo mais segurança e economia para o município.</p>',
        excerpt: 'Projeto conclui instalação em tempo recorde, garantindo mais economia e segurança.',
        featured_image: 'https://picsum.photos/seed/led/800/600',
        status: 'published',
        featured: false,
        views: 1300,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 16),
        updated_at: new Date(now.getTime() - 86400000 * 16)
      },
      {
        title: 'Exposição fotográfica retrata a história de Gurupi',
        subtitle: 'Imagens antigas mostram a evolução da "Capital da Amizade".',
        slug: 'exposicao-fotografica-historia-gurupi',
        content: '<p>Uma viagem no tempo. Assim pode ser definida a exposição "Memórias de Gurupi", em cartaz na Casa de Cultura. O acervo conta com fotos inéditas da fundação da cidade.</p>',
        excerpt: 'Imagens antigas mostram a evolução e o crescimento da Capital da Amizade ao longo das décadas.',
        featured_image: 'https://picsum.photos/seed/oldphoto/800/600',
        status: 'published',
        featured: false,
        views: 600,
        category_id: 5, // Cultura
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 17),
        updated_at: new Date(now.getTime() - 86400000 * 17)
      },
      {
        title: 'Câmara Municipal aprova projeto de incentivo ao esporte amador',
        subtitle: 'Verba será destinada para compra de materiais e organização de torneios.',
        slug: 'camara-aprova-incentivo-esporte-amador',
        content: '<p>Os vereadores de Gurupi aprovaram por unanimidade o projeto que cria o Fundo Municipal de Apoio ao Esporte Amador. A medida visa fortalecer as ligas de bairro.</p>',
        excerpt: 'Verba será destinada para compra de materiais e organização de torneios nos bairros.',
        featured_image: 'https://picsum.photos/seed/camara/800/600',
        status: 'published',
        featured: false,
        views: 400,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 18),
        updated_at: new Date(now.getTime() - 86400000 * 18)
      },
      {
        title: 'Mutirão de limpeza combate a dengue em Gurupi',
        subtitle: 'Agentes de endemias visitaram mais de 2 mil residências no fim de semana.',
        slug: 'mutirao-limpeza-combate-dengue-gurupi',
        content: '<p>A guerra contra o mosquito Aedes aegypti continua. Um grande mutirão de limpeza retirou toneladas de lixo e entulho de terrenos baldios em Gurupi.</p>',
        excerpt: 'Agentes de endemias visitaram residências e retiraram entulhos para prevenir focos do mosquito.',
        featured_image: 'https://picsum.photos/seed/dengue/800/600',
        status: 'published',
        featured: false,
        views: 750,
        category_id: 1, // Notícias
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 19),
        updated_at: new Date(now.getTime() - 86400000 * 19)
      },
      {
        title: 'Carnaval de Gurupi promete ser o maior da história',
        subtitle: 'Programação oficial foi lançada e conta com atrações nacionais.',
        slug: 'carnaval-gurupi-maior-historia',
        content: '<p>O tradicional Carnaval de Gurupi promete agitar o estado. A prefeitura lançou a programação oficial, que inclui trios elétricos e shows nacionais na Avenida Goiás.</p>',
        excerpt: 'Programação oficial foi lançada e conta com atrações nacionais e trios elétricos.',
        featured_image: 'https://picsum.photos/seed/carnaval/800/600',
        status: 'published',
        featured: true,
        views: 5000,
        category_id: 2, // Entretenimento
        author_id: 1,
        created_at: new Date(now.getTime() - 86400000 * 20),
        updated_at: new Date(now.getTime() - 86400000 * 20)
      }
    ];

    await queryInterface.bulkInsert('posts', posts, {});
  },

  async down (queryInterface, Sequelize) {
    await queryInterface.bulkDelete('posts', null, {});
  }
};
