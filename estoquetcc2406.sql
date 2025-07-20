-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: estoquetcc
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cadastro_user`
--

DROP TABLE IF EXISTS `cadastro_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cadastro_user` (
  `cod_usuario` int NOT NULL AUTO_INCREMENT,
  `login` varchar(20) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `data_criacao` date DEFAULT NULL,
  `setor` varchar(30) DEFAULT NULL,
  `perfil` varchar(30) DEFAULT NULL,
  `fotos` varchar(255) DEFAULT NULL,
  `status_user` varchar(10) DEFAULT NULL,
  `alt_senha` int DEFAULT NULL,
  `user_criador` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`cod_usuario`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cadastro_user`
--

LOCK TABLES `cadastro_user` WRITE;
/*!40000 ALTER TABLE `cadastro_user` DISABLE KEYS */;
INSERT INTO `cadastro_user` VALUES (1,'admin','Administrador','admin@infostock.com.br','$2y$10$/eoFMtRrskm1swyr9qgmZu5RNE0P89fXzZKNkaGcDbk89PeUXqJiG','2025-02-23','Sistema','administrador','/estoque/public/html/perfil/vivualizacao_perfil/config/fotos/foto_684618afb7c57.png','Ativo',0,'Sistema'),(9,'almoxarifado','Almoxarifado','almoxarifado@infostock.com.br','$2y$10$SeKb0xocDn12F4gCvu2AAeTsFLG/s46Ah5IZLIOYyeuV0OTRTX0jS','2025-06-14','Almoxarifado','almoxarifado','/estoque/public/assets/img/avatar/usuario_padrao.png','Ativo',0,'Administrador'),(11,'operador','Operador de almoxarifado','operador@infostock.com.br','$2y$10$WV3kkZVReNRzBNguHszDYOKFKNzz2Z64Z/yuDeubidObYcX4Iku2q','2025-06-21','Almoxarifado','operador','/estoque/public/html/perfil/vivualizacao_perfil/config/fotos/foto_685897744481e.png','Ativo',0,'Administrador');
/*!40000 ALTER TABLE `cadastro_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES (1,'0001','Informática ','Categoria para Informática ','Ativo'),(2,'0002','Eletrônico ','Eletrônico ','Ativo'),(3,'0003','Livros ','Categoria para Livros','Ativo'),(4,'0033','Pet','Pet','Ativo');
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custo`
--

DROP TABLE IF EXISTS `custo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custo` (
  `id_custo` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  PRIMARY KEY (`id_custo`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custo`
--

LOCK TABLES `custo` WRITE;
/*!40000 ALTER TABLE `custo` DISABLE KEYS */;
INSERT INTO `custo` VALUES (1,'2400','Tecnologia da Informação','Centro de custo para Tecnologia da Informação','Ativo'),(2,'0002','Saída de Material','Saída de Material','Ativo'),(3,'2300','Financeiro','Centro de Custo para Financeiro','Ativo'),(4,'2200','RH','RH','Ativo');
/*!40000 ALTER TABLE `custo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `depositos`
--

DROP TABLE IF EXISTS `depositos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `depositos` (
  `id_deposito` varchar(100) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `status` varchar(7) DEFAULT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_criardor` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_deposito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `depositos`
--

LOCK TABLES `depositos` WRITE;
/*!40000 ALTER TABLE `depositos` DISABLE KEYS */;
INSERT INTO `depositos` VALUES ('0002','Marcos Cleverson Rodrigues','Marcos Cleverson Rodrigues','Ativo','2025-06-20 23:06:40','Marcos Rodrigues'),('0003','Pet','Deposito de material de pet','Ativo','2025-06-23 03:38:51','Almoxarifado'),('001','Informática ','Deposito destinado para Informática ','Ativo','2025-06-20 03:51:12','Administrador'),('002','Livros ','Depósitos para livros ','Ativo','2025-06-23 00:35:11','Almoxarifado');
/*!40000 ALTER TABLE `depositos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entradas`
--

DROP TABLE IF EXISTS `entradas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entradas` (
  `nota_fiscal` varchar(50) NOT NULL,
  `id_produtos` varchar(50) NOT NULL,
  `produto` varchar(255) NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unit` decimal(10,2) NOT NULL,
  `fornecedor` varchar(255) NOT NULL,
  `data_cadastro` date NOT NULL,
  `id_fornecedor` varchar(50) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entradas`
--

LOCK TABLES `entradas` WRITE;
/*!40000 ALTER TABLE `entradas` DISABLE KEYS */;
INSERT INTO `entradas` VALUES ('NF-1234','000001','Teclados sem Fio',10,100.00,'Dell Computadores Do Brasil Ltda','2025-06-20','000002','Dell','M900'),('NF-10','000001','Teclados sem Fio',21,120.00,'Dell Computadores Do Brasil Ltda','2025-06-20','000002','dell','m9'),('NF-23456','000001','Teclados sem Fio',10,150.00,'Dell Computadores Do Brasil Ltda','2025-06-23','000002','Dell','M400'),('NF-159753','000002','Tv Led Samsung',10,5000.00,'Nexon Tech','2025-06-23','000003','Samsung','K900'),('NF-1597532','000001','Teclados sem Fio',2,120.00,'Dell Computadores Do Brasil Ltda','2025-06-23','000002','Dell','M900');
/*!40000 ALTER TABLE `entradas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estoque`
--

DROP TABLE IF EXISTS `estoque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nota_fiscal` varchar(50) NOT NULL,
  `id_produtos` varchar(50) NOT NULL,
  `produto` varchar(255) NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unit` decimal(10,2) NOT NULL,
  `fornecedor` varchar(255) NOT NULL,
  `data_cadastro` date NOT NULL,
  `id_fornecedor` varchar(50) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque`
--

LOCK TABLES `estoque` WRITE;
/*!40000 ALTER TABLE `estoque` DISABLE KEYS */;
INSERT INTO `estoque` VALUES (1,'NF-1234','000001','Teclados sem Fio',0,100.00,'Dell Computadores Do Brasil Ltda','2025-06-20','000002','Dell','M900'),(2,'NF-10','000001','Teclados sem Fio',8,120.00,'Dell Computadores Do Brasil Ltda','2025-06-20','000002','dell','m9'),(3,'NF-23456','000001','Teclados sem Fio',10,150.00,'Dell Computadores Do Brasil Ltda','2025-06-23','000002','Dell','M400'),(4,'NF-159753','000002','Tv Led Samsung',10,5000.00,'Nexon Tech','2025-06-23','000003','Samsung','K900'),(5,'NF-1597532','000001','Teclados sem Fio',2,120.00,'Dell Computadores Do Brasil Ltda','2025-06-23','000002','Dell','M900');
/*!40000 ALTER TABLE `estoque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fornecedores`
--

DROP TABLE IF EXISTS `fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fornecedores` (
  `id_fornecedor` varchar(50) NOT NULL,
  `razao_social` varchar(255) NOT NULL,
  `nome_fantasia` varchar(255) NOT NULL,
  `cnpj` varchar(18) NOT NULL,
  `inscricao_estadual` varchar(50) DEFAULT NULL,
  `telefone` varchar(20) NOT NULL,
  `telefone_seg` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `nome_contado` varchar(255) NOT NULL,
  `cargo` varchar(255) NOT NULL,
  `cep` varchar(10) NOT NULL,
  `rua` varchar(255) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro` varchar(255) NOT NULL,
  `cidade` varchar(255) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `user_criador` varchar(50) NOT NULL,
  `data_cadastro` date NOT NULL,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`id_fornecedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fornecedores`
--

LOCK TABLES `fornecedores` WRITE;
/*!40000 ALTER TABLE `fornecedores` DISABLE KEYS */;
INSERT INTO `fornecedores` VALUES ('000001','Petrofisa do Brasil','Petrofisa do Brasil','02.240.839/0001-22','ISENTO','(41) 9884-6177','(41) 3626-1531','felipe.santos@infostock.com.br','https://petrofisa.com.br/','BRUNO DA SILVEIRA RODRIGUES','Gerente Comercial','83833-212','Rua Rio Oiapoque','1503','Casa 7','Iguaçu','Fazenda Rio Grande','PR','Bruno da Silveira Rodrigues','2025-06-22','Ativo'),('000002','Dell Computadores Do Brasil Ltda','Dell Computadores Do Brasil Ltda','72.381.189/0010-01','ISENTO','(08) 0072-2330','(00) 4004-0108','dell@dell.com.br','https://www.dell.com/pt-br/lp/contact-us','Bruno da Silveira Rodrigues','Assistente de TI','13184-654','Avenida da Emancipação','5000','','Parque dos Pinheiros','Hortolândia','SP','Bruno da Silveira Rodrigues','2025-06-13','Ativo'),('000003','Nexon Tech','Nexon Tech','23.456.780/0010-1','123.456.789.000','(11) 4001-1001','(41) 9884-6177','contato@nexontech.com.br','','Rafael Martins','Gerente Ti','20040-031','Rua do Ouvidor','123','Sala 2','Centro','Rio de Janeiro','RJ','Bruno da Silveira Rodrigues','2025-04-26','Ativo'),('000004','Comercial Lima LTDA','BRUNO DA SILVEIRA RODRIGUES','32.132.132/1321-32','ISENTO','(41) 9884-6177','(41) 9884-6177','fernanda.rocha@email.com','https://petrofisa.com.br/','BRUNO DA SILVEIRA RODRIGUES','Assistente de TI','48654-213','Rua Rio Oiapoque','1503','Casa 7','Iguaçu','Fazenda Rio Grande','PR','Bruno da Silveira Rodrigues','2025-06-14','Ativo'),('000005','Ecofibra do Brasil','Ecofibra do Brasil','45.165.489/5165-16','ISENTO','(41) 9884-6177','(41) 9884-6177','carolina.mendes@email.com','https://petrofisa.com.br/','BRUNO DA SILVEIRA RODRIGUES','Gerente Ti','83833-212','Rua Rio Oiapoque','1503','Casa 7','Iguaçu','Fazenda Rio Grande','PR','Administrador','2025-06-22','Ativo'),('000006','Metalúrgica Alfa Ltda','Alfa Metais','45.165.489/5165-15','123.456.789.000','(11) 3456-7890','(11) 9123-4567','contato@alfametais.com.br','https://www.alfametais.com.br',' João Silva','Gerente Comercial','01001-000','Praça da Sé','100',' Galpão 2','Sé','São Paulo','SP','Administrador','2025-06-22','Ativo');
/*!40000 ALTER TABLE `fornecedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itens`
--

DROP TABLE IF EXISTS `itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itens` (
  `id_produtos` varchar(50) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `quantidade_minima` int NOT NULL,
  `preco_unit` decimal(10,2) NOT NULL,
  `status_item` varchar(255) DEFAULT NULL,
  `id_deposito` varchar(50) DEFAULT NULL,
  `codigo_completo` varchar(255) DEFAULT NULL,
  `usuario_cadastro` varchar(255) DEFAULT NULL,
  `data_criacao` date DEFAULT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itens`
--

LOCK TABLES `itens` WRITE;
/*!40000 ALTER TABLE `itens` DISABLE KEYS */;
INSERT INTO `itens` VALUES ('000001','Teclados sem Fio','Informática ',10,120.00,'Ativo','001','A1-A1-A1-A1-A1','Administrador','2025-06-22',NULL,NULL),('000002','Tv Led Samsung','Eletrônico ',5,4000.00,'Ativo','001','A1-A1-A1-A1-A2','Almoxarifado','2025-06-23',NULL,NULL),('000003','Arduino','Informática ',5,120.00,'Ativo','001','A1-A1-A1-A2-A1','Almoxarifado','2025-06-23',NULL,NULL);
/*!40000 ALTER TABLE `itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `localizacoes`
--

DROP TABLE IF EXISTS `localizacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `localizacoes` (
  `id_deposito` varchar(50) NOT NULL,
  `predio` varchar(50) NOT NULL,
  `andar_setor` varchar(50) NOT NULL,
  `corredor` varchar(50) NOT NULL,
  `prateleira` varchar(50) NOT NULL,
  `compartimento` varchar(50) NOT NULL,
  `codigo_completo` varchar(255) GENERATED ALWAYS AS (concat(`predio`,_utf8mb4'-',`andar_setor`,_utf8mb4'-',`corredor`,_utf8mb4'-',`prateleira`,_utf8mb4'-',`compartimento`)) STORED,
  `descricao` text,
  `id_produtos` varchar(50) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `status` varchar(7) DEFAULT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_criador` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_deposito`,`predio`,`andar_setor`,`corredor`,`prateleira`,`compartimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `localizacoes`
--

LOCK TABLES `localizacoes` WRITE;
/*!40000 ALTER TABLE `localizacoes` DISABLE KEYS */;
INSERT INTO `localizacoes` (`id_deposito`, `predio`, `andar_setor`, `corredor`, `prateleira`, `compartimento`, `descricao`, `id_produtos`, `nome`, `status`, `data_criacao`, `user_criador`) VALUES ('0003','A1','A1','A1','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A1','A1','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A1','A1','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A1','A1','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A1','A2','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A1','A2','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A1','A2','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A1','A2','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A2','A1','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A2','A1','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A2','A1','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A2','A1','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A2','A2','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A2','A2','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A2','A2','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A1','A2','A2','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A1','A1','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A1','A1','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A1','A1','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A1','A1','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A1','A2','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A1','A2','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A1','A2','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A1','A2','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A2','A1','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A2','A1','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A2','A1','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A2','A1','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A2','A2','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A2','A2','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A2','A2','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('0003','A2','A2','A2','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-23 03:39:16','Almoxarifado'),('001','A1','A1','A1','A1','A1',NULL,'000001','Teclados sem Fio','Inativo','2025-06-20 03:51:59','Administrador'),('001','A1','A1','A1','A1','A2',NULL,'000002','Tv Led Samsung','Inativo','2025-06-20 03:51:59','Administrador'),('001','A1','A1','A1','A2','A1',NULL,'000003','Arduino','Inativo','2025-06-20 03:51:59','Administrador'),('001','A1','A1','A1','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:51:59','Administrador'),('001','A1','A1','A2','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A1','A2','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A1','A2','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A1','A2','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A2','A1','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A2','A1','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A2','A1','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A2','A1','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A2','A2','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A2','A2','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A2','A2','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A1','A2','A2','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A1','A1','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A1','A1','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A1','A1','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A1','A1','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A1','A2','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A1','A2','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A1','A2','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A1','A2','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A2','A1','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A2','A1','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A2','A1','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A2','A1','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A2','A2','A1','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A2','A2','A1','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A2','A2','A2','A1',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador'),('001','A2','A2','A2','A2','A2',NULL,NULL,'Disponivel','Ativo','2025-06-20 03:52:00','Administrador');
/*!40000 ALTER TABLE `localizacoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nf_entrada`
--

DROP TABLE IF EXISTS `nf_entrada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nf_entrada` (
  `nota_fiscal` varchar(20) DEFAULT NULL,
  `fornecedor` varchar(60) DEFAULT NULL,
  `valor_unit` decimal(10,2) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `data_entrada` date DEFAULT NULL,
  `usuario_cadastro` varchar(255) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nf_entrada`
--

LOCK TABLES `nf_entrada` WRITE;
/*!40000 ALTER TABLE `nf_entrada` DISABLE KEYS */;
INSERT INTO `nf_entrada` VALUES ('NF-1234','Dell Computadores Do Brasil Ltda',100.00,1000.00,'2025-06-20','sistema','Dell','M900'),('NF-10','Dell Computadores Do Brasil Ltda',120.00,2520.00,'2025-06-20','sistema','dell','m9'),('NF-23456','Dell Computadores Do Brasil Ltda',150.00,1500.00,'2025-06-23','sistema','Dell','M400'),('NF-159753','Nexon Tech',5000.00,50000.00,'2025-06-23','sistema','Samsung','K900'),('NF-1597532','Dell Computadores Do Brasil Ltda',120.00,240.00,'2025-06-23','sistema','Dell','M900');
/*!40000 ALTER TABLE `nf_entrada` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nf_saida`
--

DROP TABLE IF EXISTS `nf_saida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nf_saida` (
  `nota_fiscal_saida` varchar(20) DEFAULT NULL,
  `valor_unit` decimal(10,2) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `cetro_custo` varchar(50) DEFAULT NULL,
  `motivo_saida` varchar(50) DEFAULT NULL,
  `observacao` varchar(254) DEFAULT NULL,
  `data_saida` date DEFAULT NULL,
  `usuario_cadastro` varchar(255) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nf_saida`
--

LOCK TABLES `nf_saida` WRITE;
/*!40000 ALTER TABLE `nf_saida` DISABLE KEYS */;
INSERT INTO `nf_saida` VALUES ('NF-000001',100.00,1000.00,'2400','Uso Interno','Cliente pagou com pix ','2025-06-20','Administrador','Dell','M900'),('NF-000002',120.00,1200.00,'2400','Troca','venda ','2025-06-20','Marcos Rodrigues','dell','m9'),('NF-000003',120.00,240.00,'2400','Uso Interno','','2025-06-23','Operador de almoxarifado','dell','m9'),('NF-000004',120.00,120.00,'2400','Outro','Pegou para utilizar no computador ','2025-06-23','Operador de almoxarifado','dell','m9');
/*!40000 ALTER TABLE `nf_saida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saida`
--

DROP TABLE IF EXISTS `saida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saida` (
  `nota_fiscal_saida` varchar(20) DEFAULT NULL,
  `id_produtos` varchar(50) NOT NULL,
  `produto` varchar(255) NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unit` decimal(10,2) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `data_cadastro` date NOT NULL,
  `data_saida` date DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saida`
--

LOCK TABLES `saida` WRITE;
/*!40000 ALTER TABLE `saida` DISABLE KEYS */;
INSERT INTO `saida` VALUES ('NF-000001','000001','Teclados sem Fio',10,100.00,1000.00,'2025-06-20','2025-06-20','Dell','M900'),('NF-000002','000001','Teclados sem Fio',10,120.00,1200.00,'2025-06-20','2025-06-20','dell','m9'),('NF-000003','000001','Teclados sem Fio',2,120.00,240.00,'2025-06-23','2025-06-23','dell','m9'),('NF-000004','000001','Teclados sem Fio',1,120.00,120.00,'2025-06-23','2025-06-23','dell','m9');
/*!40000 ALTER TABLE `saida` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-24 23:34:02
