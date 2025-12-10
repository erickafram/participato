'use strict';

/** @type {import('sequelize-cli').Migration} */
module.exports = {
  async up(queryInterface, Sequelize) {
    // Buscar o primeiro admin
    const [users] = await queryInterface.sequelize.query(
      "SELECT id FROM users WHERE role = 'admin' LIMIT 1"
    );
    const authorId = users.length > 0 ? users[0].id : 1;

    await queryInterface.bulkInsert('pages', [
      {
        title: 'Sobre Nós',
        slug: 'sobre',
        content: `
          <h2>Quem Somos</h2>
          <p>O Portal Convictos é um veículo de comunicação digital dedicado a trazer as melhores notícias e conteúdos de entretenimento para você.</p>
          
          <h3>Nossa Missão</h3>
          <p>Informar com qualidade, responsabilidade e agilidade, mantendo sempre o compromisso com a verdade e a ética jornalística.</p>
          
          <h3>Nossa Visão</h3>
          <p>Ser referência em jornalismo digital, reconhecido pela credibilidade e pela capacidade de conectar pessoas através da informação.</p>
          
          <h3>Nossos Valores</h3>
          <ul>
            <li><strong>Verdade:</strong> Compromisso inabalável com os fatos</li>
            <li><strong>Ética:</strong> Respeito às normas do bom jornalismo</li>
            <li><strong>Inovação:</strong> Busca constante por novas formas de comunicar</li>
            <li><strong>Respeito:</strong> Valorização da diversidade e das diferentes opiniões</li>
          </ul>
        `,
        status: 'published',
        template: 'default',
        order: 1,
        show_in_menu: true,
        meta_title: 'Sobre Nós - Portal Convictos',
        meta_description: 'Conheça o Portal Convictos, sua fonte de notícias e entretenimento de qualidade.',
        author_id: authorId,
        created_at: new Date(),
        updated_at: new Date()
      },
      {
        title: 'Contato',
        slug: 'contato',
        content: `
          <h2>Entre em Contato</h2>
          <p>Ficamos felizes em ouvir você! Entre em contato conosco através dos canais abaixo:</p>
          
          <h3>Informações de Contato</h3>
          <ul>
            <li><strong>Email:</strong> contato@portalconvictos.com</li>
            <li><strong>Telefone:</strong> (11) 99999-9999</li>
            <li><strong>Endereço:</strong> Rua Exemplo, 123 - São Paulo, SP</li>
          </ul>
          
          <h3>Horário de Atendimento</h3>
          <p>Segunda a Sexta: 9h às 18h<br>Sábados: 9h às 13h</p>
          
          <h3>Redes Sociais</h3>
          <p>Siga-nos nas redes sociais para ficar por dentro de todas as novidades!</p>
        `,
        status: 'published',
        template: 'contact',
        order: 2,
        show_in_menu: true,
        meta_title: 'Contato - Portal Convictos',
        meta_description: 'Entre em contato com o Portal Convictos. Estamos prontos para atendê-lo.',
        author_id: authorId,
        created_at: new Date(),
        updated_at: new Date()
      },
      {
        title: 'Política de Privacidade',
        slug: 'politica-de-privacidade',
        content: `
          <h2>Política de Privacidade</h2>
          <p>Esta Política de Privacidade descreve como o Portal Convictos coleta, usa e protege suas informações pessoais.</p>
          
          <h3>1. Informações Coletadas</h3>
          <p>Coletamos informações que você nos fornece diretamente, como nome e email ao se cadastrar em nossa newsletter.</p>
          
          <h3>2. Uso das Informações</h3>
          <p>Utilizamos suas informações para:</p>
          <ul>
            <li>Enviar newsletters e atualizações</li>
            <li>Melhorar nossos serviços</li>
            <li>Responder suas solicitações</li>
          </ul>
          
          <h3>3. Proteção de Dados</h3>
          <p>Implementamos medidas de segurança para proteger suas informações contra acesso não autorizado.</p>
          
          <h3>4. Cookies</h3>
          <p>Utilizamos cookies para melhorar sua experiência de navegação. Você pode desativá-los nas configurações do seu navegador.</p>
          
          <h3>5. Contato</h3>
          <p>Para dúvidas sobre esta política, entre em contato: privacidade@portalconvictos.com</p>
        `,
        status: 'published',
        template: 'default',
        order: 3,
        show_in_menu: false,
        meta_title: 'Política de Privacidade - Portal Convictos',
        meta_description: 'Conheça nossa política de privacidade e como protegemos seus dados.',
        author_id: authorId,
        created_at: new Date(),
        updated_at: new Date()
      },
      {
        title: 'Termos de Uso',
        slug: 'termos-de-uso',
        content: `
          <h2>Termos de Uso</h2>
          <p>Ao acessar o Portal Convictos, você concorda com os seguintes termos:</p>
          
          <h3>1. Aceitação dos Termos</h3>
          <p>O uso deste site implica na aceitação integral destes termos de uso.</p>
          
          <h3>2. Propriedade Intelectual</h3>
          <p>Todo o conteúdo publicado é de propriedade do Portal Convictos ou de seus parceiros, protegido por leis de direitos autorais.</p>
          
          <h3>3. Uso do Conteúdo</h3>
          <p>É permitido compartilhar nosso conteúdo desde que seja dado o devido crédito e link para a fonte original.</p>
          
          <h3>4. Responsabilidades</h3>
          <p>O Portal Convictos não se responsabiliza por conteúdos de sites externos linkados em nossas páginas.</p>
          
          <h3>5. Modificações</h3>
          <p>Reservamo-nos o direito de modificar estes termos a qualquer momento.</p>
        `,
        status: 'published',
        template: 'default',
        order: 4,
        show_in_menu: false,
        meta_title: 'Termos de Uso - Portal Convictos',
        meta_description: 'Leia os termos de uso do Portal Convictos.',
        author_id: authorId,
        created_at: new Date(),
        updated_at: new Date()
      }
    ]);
  },

  async down(queryInterface, Sequelize) {
    await queryInterface.bulkDelete('pages', null, {});
  }
};
