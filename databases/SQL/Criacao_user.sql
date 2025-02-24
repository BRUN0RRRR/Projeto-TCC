CREATE TABLE cadastro_user (
    cod_usuario INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(20) UNIQUE NOT NULL,
    nome VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    data_criacao DATE DEFAULT NULL,
    id_grupo INT, 
    grupo varchar(20),
    setor VARCHAR(30),
    perfil VARCHAR(30),
    status_user VARCHAR(10), 
    user_criador VARCHAR(30),
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE SET NULL
); 

INSERT INTO cadastro_user (cod_usuario, login, nome, email, senha, data_criacao, id_grupo,grupo,setor,perfil,status_user,user_criador)
VALUES (null ,'bruno', 'Bruno da Silveira Rodrigues', 'bruno.rodrigues@infostock.com.br', md5('1234'),CURDATE(), 1,"administrador",'TI','super-usuario', 'ativo', 'banco de dados');

ALTER TABLE cadastro_user MODIFY senha VARCHAR(255);