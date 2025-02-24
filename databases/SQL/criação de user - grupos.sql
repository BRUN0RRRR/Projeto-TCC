use estoquetcc;



CREATE TABLE grupos (
    id_grupo INT PRIMARY KEY AUTO_INCREMENT,
    nome_grupo VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE usuario_grupo (
    id_usuario INT,
    id_grupo INT,
    PRIMARY KEY (id_usuario, id_grupo),
    FOREIGN KEY (id_usuario) REFERENCES cadastro_user(cod_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE CASCADE
);

drop table cadastro_user;
drop table grupos;
drop table usuario_grupo;
CREATE TABLE cadastro_user (
    cod_usuario INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(20) UNIQUE NOT NULL,
    nome VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(50) NOT NULL,
    data_criacao DATE DEFAULT NULL,
    id_grupo INT, 
    grupo varchar(20),
    setor VARCHAR(30),
    perfil VARCHAR(30),
    status_user VARCHAR(10), 
    user_criador VARCHAR(30),
    FOREIGN KEY (id_grupo) REFERENCES grupos(id_grupo) ON DELETE SET NULL
);



show tables;
SELECT VERSION();

INSERT INTO cadastro_user (cod_usuario, login, nome, email, senha, data_criacao, id_grupo,grupo,setor,perfil,status_user,user_criador)
VALUES (null ,'bruno', 'Bruno da Silveira Rodrigues', 'bruno.rodrigues@infostock.com.br', md5('1234'),CURDATE(), 1,"administrador",'TI','super-usuario', 'ativo', 'banco de dados');

SELECT * FROM cadastro_user WHERE login = 'admin';
SELECT * FROM cadastro_user WHERE  like "%Bruno%";
select * from grupos;
select * from cadastro_user;

insert into grupos (id_grupo,nome_grupo) value(null,"administrador"); 

select * from cadastro_user T0 inner join grupos T1 on T0.id_grupo = T1.id_grupo;

select * from usuario_grupo;

ALTER TABLE cadastro_user MODIFY senha VARCHAR(255);
