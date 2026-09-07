-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS tcg_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tcg_db;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('cliente', 'admin') DEFAULT 'cliente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Cartas (Produtos)
CREATE TABLE IF NOT EXISTS cartas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    imagem VARCHAR(500) NOT NULL,
    descricao TEXT,
    edicao VARCHAR(255),
    estado VARCHAR(100),
    raridade VARCHAR(100),
    data_lancamento DATE,
    tipo_carta VARCHAR(100),
    tipo_energia VARCHAR(100),
    nacionalidade VARCHAR(50),
    estoque INT DEFAULT 1,
    promocao_porcentagem INT DEFAULT 0,
    vezes_no_carrinho INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela do Carrinho (Itens do Carrinho de Compras)
CREATE TABLE IF NOT EXISTS carrinho_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    carta_id INT NOT NULL,
    quantidade INT DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (carta_id) REFERENCES cartas(id) ON DELETE CASCADE
);

-- Tabela de Favoritos (Wishlist)
CREATE TABLE IF NOT EXISTS favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    carta_id INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (carta_id) REFERENCES cartas(id) ON DELETE CASCADE,
    UNIQUE(usuario_id, carta_id) -- Não permite a mesma carta duas vezes para o mesmo usuário
);

-- Inserir um administrador padrão (senha: admin123)
-- hash bcrypt para "admin123"
INSERT IGNORE INTO usuarios (nome, email, senha, tipo) VALUES 
('Administrador', 'admin@rockettcg.com', '$2y$10$tZ1X/LIt4a6mI.eK5Q4oQeJ0oA.J9cZ0jX2pUuR0PzE.pXy9T0.o2', 'admin');

-- Inserir algumas cartas de exemplo para testar o catálogo
INSERT IGNORE INTO cartas (nome, preco, categoria, imagem, descricao, edicao, estado, raridade, data_lancamento, tipo_carta, tipo_energia, nacionalidade) VALUES
('Dragão Flamejante', 150.00, 'pokemon', 'assets/card1.jpg', 'Carta poderosa.', 'Coleção Base 1ª Edição', 'Mint', 'Holográfica', '1999-01-09', 'Pokémon Estágio 2', 'Fogo', 'PT-BR'),
('Arqueira Élfica', 89.90, 'magic', 'assets/card2.jpg', 'Ótima defesa.', 'Floresta Sombria', 'Near Mint', 'Rara', '2010-05-15', 'Criatura Elfo', 'Floresta', 'EN'),
('Cavaleiro Cibernético', 210.00, 'yugioh', 'assets/card3.jpg', 'Ataque avassalador.', 'Futuro Neon', 'Mint', 'Secreta', '2015-11-20', 'Monstro Efeito', 'Trevas', 'JP');
