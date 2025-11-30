# Portal Convictos

Portal de entretenimento com painel administrativo estilo WordPress, desenvolvido com Node.js, Express, EJS e MySQL.

## 🚀 Tecnologias

- **Backend:** Node.js + Express
- **Frontend:** HTML5, CSS3, JavaScript moderno
- **Template Engine:** EJS
- **Banco de Dados:** MySQL
- **ORM:** Sequelize
- **CSS Framework:** Bootstrap 5.3
- **Ícones:** Bootstrap Icons
- **Upload:** Multer + Sharp

## 📋 Funcionalidades

### Site Público
- ✅ Página inicial com slider/banner
- ✅ Listagem de notícias com paginação
- ✅ Página individual de notícias
- ✅ Categorias e tags
- ✅ Sistema de busca
- ✅ Páginas estáticas (Sobre, Contato, etc.)
- ✅ SEO otimizado (meta tags, sitemap, robots.txt)
- ✅ Design responsivo
- ✅ Página 404 personalizada

### Painel Administrativo
- ✅ Dashboard com estatísticas
- ✅ CRUD completo de Posts
- ✅ CRUD completo de Categorias
- ✅ CRUD completo de Páginas
- ✅ Gerenciamento de Usuários
- ✅ Biblioteca de Mídia (upload de imagens)
- ✅ Configurações do site
- ✅ Editor de texto rico (CKEditor)
- ✅ Sistema de autenticação
- ✅ Permissões (Admin/Editor)

## 🛠️ Instalação

### Pré-requisitos
- Node.js 18+
- MySQL 5.7+ ou 8.0+
- npm ou yarn

### Passo a passo

1. **Clone ou copie o projeto**
```bash
cd c:\wamp64\www\PortalConvictos
```

2. **Instale as dependências**
```bash
npm install
```

3. **Configure o banco de dados**

Crie um banco de dados MySQL:
```sql
CREATE DATABASE portal_convictos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. **Configure as variáveis de ambiente**

Edite o arquivo `.env` com suas configurações:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=portal_convictos
DB_USER=root
DB_PASSWORD=sua_senha
SESSION_SECRET=sua_chave_secreta
```

5. **Execute as migrations**
```bash
npm run db:migrate
```

6. **Execute os seeders (dados iniciais)**
```bash
npm run db:seed
```

7. **Inicie o servidor**
```bash
# Desenvolvimento (com hot reload)
npm run dev

# Produção
npm start
```

8. **Acesse o sistema**
- Site: http://localhost:3000
- Painel Admin: http://localhost:3000/admin

### Login padrão
- **Email:** admin@portal.com
- **Senha:** admin123

## 📁 Estrutura do Projeto

```
PortalConvictos/
├── config/
│   └── database.js          # Configuração do Sequelize
├── database/
│   ├── migrations/          # Migrations do banco
│   └── seeders/             # Dados iniciais
├── src/
│   ├── app.js               # Arquivo principal
│   ├── controllers/         # Controllers
│   ├── middlewares/         # Middlewares
│   ├── models/              # Models Sequelize
│   ├── routes/              # Rotas
│   ├── views/               # Views EJS
│   │   ├── admin/           # Views do painel
│   │   └── site/            # Views do site
│   └── public/              # Arquivos estáticos
├── uploads/                 # Uploads de mídia
├── .env                     # Variáveis de ambiente
├── .env.example             # Exemplo de variáveis
├── package.json
└── README.md
```

## 🎨 Personalização

### Cores
As cores principais podem ser alteradas no CSS:
```css
:root {
  --primary-color: #3ba4ff;    /* Azul claro */
  --primary-dark: #2b8ce6;     /* Azul escuro */
  --primary-light: #e8f4ff;    /* Azul muito claro */
}
```

### Logo
Substitua os arquivos em `src/public/images/`:
- `logo.png` - Logo principal
- `favicon.ico` - Ícone do site

## 📝 Scripts Disponíveis

```bash
npm start          # Inicia em produção
npm run dev        # Inicia em desenvolvimento (nodemon)
npm run db:migrate # Executa migrations
npm run db:seed    # Executa seeders
npm run db:reset   # Reset completo do banco
```

## 🔒 Segurança

- Senhas hasheadas com bcrypt
- Sessões seguras
- Validação de formulários
- Proteção contra uploads maliciosos
- Sanitização de inputs

## 🌐 SEO

- Meta tags dinâmicas
- Open Graph tags
- Sitemap XML automático
- Robots.txt configurado
- URLs amigáveis (slugs)
- Breadcrumbs

## 📱 Responsividade

O site é totalmente responsivo, adaptando-se a:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (< 768px)

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👤 Autor

Portal Convictos - Desenvolvido com ❤️

---

**Dúvidas?** Entre em contato através do painel de administração ou abra uma issue no repositório.
