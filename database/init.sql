-- =====================================================
-- Portal Convictos - Script de Inicialização do Banco
-- =====================================================
-- Execute este script para criar o banco de dados
-- Ou use: npm run db:migrate && npm run db:seed
-- =====================================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS portal_convictos 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE portal_convictos;

-- =====================================================
-- TABELA: users
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'editor') DEFAULT 'editor',
  avatar VARCHAR(255),
  bio TEXT,
  active BOOLEAN DEFAULT TRUE,
  last_login DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: categories
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  color VARCHAR(7) DEFAULT '#3ba4ff',
  icon VARCHAR(50),
  `order` INT DEFAULT 0,
  active BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug (slug),
  INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: posts
-- =====================================================
CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  subtitle VARCHAR(500),
  slug VARCHAR(280) NOT NULL UNIQUE,
  content LONGTEXT NOT NULL,
  excerpt TEXT,
  featured_image VARCHAR(255),
  featured_image_alt VARCHAR(255),
  tags TEXT,
  status ENUM('draft', 'published', 'scheduled') DEFAULT 'draft',
  featured BOOLEAN DEFAULT FALSE,
  views INT DEFAULT 0,
  meta_title VARCHAR(70),
  meta_description VARCHAR(160),
  published_at DATETIME,
  scheduled_at DATETIME,
  category_id INT,
  author_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_slug (slug),
  INDEX idx_status (status),
  INDEX idx_featured (featured),
  INDEX idx_category (category_id),
  INDEX idx_author (author_id),
  INDEX idx_published (published_at),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: pages
-- =====================================================
CREATE TABLE IF NOT EXISTS pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(280) NOT NULL UNIQUE,
  content LONGTEXT,
  featured_image VARCHAR(255),
  status ENUM('draft', 'published') DEFAULT 'draft',
  template VARCHAR(50) DEFAULT 'default',
  `order` INT DEFAULT 0,
  show_in_menu BOOLEAN DEFAULT FALSE,
  meta_title VARCHAR(70),
  meta_description VARCHAR(160),
  author_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_slug (slug),
  INDEX idx_status (status),
  INDEX idx_menu (show_in_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: media
-- =====================================================
CREATE TABLE IF NOT EXISTS media (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  path VARCHAR(500) NOT NULL,
  url VARCHAR(500) NOT NULL,
  mimetype VARCHAR(100) NOT NULL,
  size INT NOT NULL,
  width INT,
  height INT,
  alt_text VARCHAR(255),
  caption TEXT,
  user_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_mimetype (mimetype)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: settings
-- =====================================================
CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  value TEXT,
  type ENUM('text', 'textarea', 'number', 'boolean', 'json', 'image') DEFAULT 'text',
  `group` VARCHAR(50) DEFAULT 'general',
  label VARCHAR(100),
  description VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_key (`key`),
  INDEX idx_group (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DADOS INICIAIS
-- =====================================================

-- Usuário admin (senha: admin123)
INSERT INTO users (name, email, password_hash, role, active) VALUES
('Administrador', 'admin@portal.com', '$2a$10$8K1p/a0dL1LXMIgoEDFrwOfMQHLVXFwJGnKsqjG5U5PkOq5AA5BOi', 'admin', TRUE),
('Editor', 'editor@portal.com', '$2a$10$8K1p/a0dL1LXMIgoEDFrwOfMQHLVXFwJGnKsqjG5U5PkOq5AA5BOi', 'editor', TRUE);

-- Categorias
INSERT INTO categories (name, slug, description, color, icon, `order`, active) VALUES
('Notícias', 'noticias', 'Últimas notícias e acontecimentos', '#3ba4ff', 'bi-newspaper', 1, TRUE),
('Entretenimento', 'entretenimento', 'Novidades do mundo do entretenimento', '#ff6b6b', 'bi-film', 2, TRUE),
('Tecnologia', 'tecnologia', 'Inovações e tendências tecnológicas', '#4ecdc4', 'bi-cpu', 3, TRUE),
('Esportes', 'esportes', 'Cobertura esportiva completa', '#45b7d1', 'bi-trophy', 4, TRUE),
('Cultura', 'cultura', 'Arte, música e cultura em geral', '#96ceb4', 'bi-palette', 5, TRUE);

-- Configurações
INSERT INTO settings (`key`, value, type, `group`, label, description) VALUES
('site_name', 'Portal Convictos', 'text', 'general', 'Nome do Site', 'Nome principal do site'),
('site_description', 'Seu portal de notícias e entretenimento', 'textarea', 'general', 'Descrição do Site', 'Descrição curta para SEO'),
('site_keywords', 'notícias, entretenimento, tecnologia, esportes, cultura', 'textarea', 'general', 'Palavras-chave', 'Palavras-chave para SEO'),
('contact_email', 'contato@portalconvictos.com', 'text', 'contact', 'Email de Contato', 'Email principal'),
('contact_phone', '(11) 99999-9999', 'text', 'contact', 'Telefone', 'Telefone de contato'),
('contact_address', 'Rua Exemplo, 123 - São Paulo, SP', 'textarea', 'contact', 'Endereço', 'Endereço físico'),
('social_facebook', 'https://facebook.com/portalconvictos', 'text', 'social', 'Facebook', 'URL do Facebook'),
('social_instagram', 'https://instagram.com/portalconvictos', 'text', 'social', 'Instagram', 'URL do Instagram'),
('social_twitter', 'https://twitter.com/portalconvictos', 'text', 'social', 'Twitter/X', 'URL do Twitter'),
('social_youtube', 'https://youtube.com/portalconvictos', 'text', 'social', 'YouTube', 'URL do YouTube'),
('posts_per_page', '12', 'number', 'posts', 'Posts por Página', 'Quantidade de posts por página'),
('featured_posts_count', '5', 'number', 'posts', 'Posts em Destaque', 'Quantidade de posts em destaque'),
('footer_text', '© 2024 Portal Convictos. Todos os direitos reservados.', 'textarea', 'footer', 'Texto do Rodapé', 'Copyright');

-- Páginas
INSERT INTO pages (title, slug, content, status, template, `order`, show_in_menu, author_id) VALUES
('Sobre Nós', 'sobre', '<h2>Quem Somos</h2><p>O Portal Convictos é um veículo de comunicação digital dedicado a trazer as melhores notícias e conteúdos de entretenimento para você.</p>', 'published', 'default', 1, TRUE, 1),
('Contato', 'contato', '<h2>Entre em Contato</h2><p>Ficamos felizes em ouvir você!</p>', 'published', 'contact', 2, TRUE, 1),
('Política de Privacidade', 'politica-de-privacidade', '<h2>Política de Privacidade</h2><p>Esta política descreve como coletamos e usamos suas informações.</p>', 'published', 'default', 3, FALSE, 1),
('Termos de Uso', 'termos-de-uso', '<h2>Termos de Uso</h2><p>Ao acessar o site, você concorda com estes termos.</p>', 'published', 'default', 4, FALSE, 1);

-- Post de exemplo
INSERT INTO posts (title, subtitle, slug, content, excerpt, tags, status, featured, views, category_id, author_id, published_at) VALUES
('Bem-vindo ao Portal Convictos', 'Seu novo destino para notícias e entretenimento de qualidade', 'bem-vindo-ao-portal-convictos', '<p>É com grande satisfação que apresentamos o <strong>Portal Convictos</strong>, sua nova fonte de informação e entretenimento!</p><p>Nosso portal foi desenvolvido com o objetivo de trazer conteúdo de qualidade, com uma experiência de leitura agradável e moderna.</p><h2>O que você encontrará aqui</h2><ul><li><strong>Notícias:</strong> Fique por dentro dos principais acontecimentos</li><li><strong>Entretenimento:</strong> Cinema, música, séries e muito mais</li><li><strong>Tecnologia:</strong> As últimas novidades do mundo tech</li><li><strong>Esportes:</strong> Cobertura completa do mundo esportivo</li><li><strong>Cultura:</strong> Arte, literatura e manifestações culturais</li></ul>', 'É com grande satisfação que apresentamos o Portal Convictos, sua nova fonte de informação e entretenimento!', 'portal,lançamento,novidades', 'published', TRUE, 100, 1, 1, NOW());

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
