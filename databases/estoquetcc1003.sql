-- MySQL dump 10.13  Distrib 8.0.41, for Linux (x86_64)
--
-- Host: localhost    Database: estoquetcc
-- ------------------------------------------------------
-- Server version	8.0.41-0ubuntu0.24.04.1

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
  `id_grupo` int DEFAULT NULL,
  `grupo` varchar(20) DEFAULT NULL,
  `setor` varchar(30) DEFAULT NULL,
  `perfil` varchar(30) DEFAULT NULL,
  `status_user` varchar(10) DEFAULT NULL,
  `user_criador` varchar(30) DEFAULT NULL,
  `alt_senha` int DEFAULT NULL,
  PRIMARY KEY (`cod_usuario`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `email` (`email`),
  KEY `id_grupo` (`id_grupo`),
  CONSTRAINT `cadastro_user_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cadastro_user`
--

LOCK TABLES `cadastro_user` WRITE;
/*!40000 ALTER TABLE `cadastro_user` DISABLE KEYS */;
INSERT INTO `cadastro_user` VALUES (2,'admin','Administrador','admin@infostock.com.br','$2y$10$iHDACpHTFuDKF8NbMWLSWeUq/C8sZCRpGAjKrR8RLN4JE.m64TEuy','2025-02-23',1,'Administrador','Sistema','Administrador','Ativo','Bruno da Silveira Rodrigues',0),(6,'beatriz.ferreira','Beatriz Ferreira','beatriz.ferreira@email.com','$2y$10$l1wh2CcGf3qZt8vOR52aGeGvEMx0r9J28sq.fX/vV0nmV78CLK2We','2025-03-02',1,'Administrador','compras','Operador','Inativo','Administrador',0),(7,'bruno.rodrigues','Bruno da Silveira Rodrigues','brunorodriguesbsr@gmail.com','$2y$10$Zzozf7JYnJkJP9Dcf.71TeOpwaMWrebgpp125zW8YMiBgL4lRfBlG','2025-03-02',1,'Administrador','TI','Administrador','Ativo','Administrador',0),(8,'felipe.santos','Felipe Santos','felipe.santos@infostock.com.br','$2y$10$I1m/DiehIpzItf6cnKhVROjmIgO.CPK.JK8J8mLtYrU2gKs4EUycW','2025-03-02',1,'Administrador','operador','Administrador','Ativo','Bruno da Silveira Rodrigues',0),(9,'caio.rodrigues','Caio da Silveira Rodrigues','caio.rodrigues@infostock.com.br','$2y$10$9CUctAO.ntBvxwYNM3sSsOi41euHAKajZs183/.DVfVxR1yQNLYbi','2025-03-03',1,'Administrador','TI','Administrador','Ativo','Administrador',1),(10,'fernanda.rocha','Fernanda Rocha','fernanda.rocha@infostock.com.br','$2y$10$FoAcGAr0y0pTGR8b0vuWwuMlw69WWT/2/3Vd.nxvY55vT2400qVP6','2025-03-03',1,'Administrador','Almoxarifado','Gerente','Ativo','Administrador',1),(11,'carlos.souza','Carlos Almeida de Souza','carlos.souza@infostock.com.br','$2y$10$sgn.WYAjRfUDwTYRTToo8u.LRnU0s90JU3CzbNX8MUA1L0N7AphsC','2025-03-03',1,'Administrador','Compras','Coordenador','Ativo','Administrador',1),(12,'ana.cardoso','Ana Cardoso','ana.cardoso@infostock.com.br','$2y$10$xITxHYMBs4.8vC0hJhHsz..yZT44dpeYMfdp4lsGd8hMWn/yCq6YK','2025-03-03',1,'Administrador','Almoxarifado','Operador','Ativo','Administrador',1),(13,'lua.silva','Lua Fernanda da Silva','lua.silva@infostock.com.br','$2y$10$scdsEvbOGA5zoTgRGjHr1.lh52NYHlfHyB7EjpH55kw/1N4HqazdO','2025-03-08',1,'Administrador','Compras','Analista','Ativo','Felipe Santos',0);
/*!40000 ALTER TABLE `cadastro_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupos`
--

DROP TABLE IF EXISTS `grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupos` (
  `id_grupo` int NOT NULL AUTO_INCREMENT,
  `nome_grupo` varchar(50) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_grupo`),
  UNIQUE KEY `nome_grupo` (`nome_grupo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos`
--

LOCK TABLES `grupos` WRITE;
/*!40000 ALTER TABLE `grupos` DISABLE KEYS */;
INSERT INTO `grupos` VALUES (1,'Administrador','Acesso total no sistema');
/*!40000 ALTER TABLE `grupos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itens`
--

DROP TABLE IF EXISTS `itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `quantidade_minima` int NOT NULL,
  `predio` varchar(50) NOT NULL,
  `andar_setor` varchar(50) DEFAULT NULL,
  `corredor` varchar(50) NOT NULL,
  `prateleira` varchar(50) DEFAULT NULL,
  `compartimento` varchar(50) DEFAULT NULL,
  `status_item` varchar(255) DEFAULT NULL,
  `usuario_cadastro` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itens`
--

LOCK TABLES `itens` WRITE;
/*!40000 ALTER TABLE `itens` DISABLE KEYS */;
INSERT INTO `itens` VALUES (3,'0001','Teclado','Informática',10,'A1','A1','A1','A1','A1','Ativo','Sistema'),(4,'0002','Mouse com Fio','Informática',5,'A1','A1','A1','A1','A2','Ativo','Sistema'),(5,'0003','Cabo de Alimentação','Informática',10,'A1','A1','A1','A1','A3','Ativo','Sistema'),(6,'0004','Cabo VGA','Informática',10,'A1','A1','A1','A1','A4','Ativo','Sistema'),(7,'0005','Cabo HDMI','Informática',10,'A1','A1','A1','A1','A5','Ativo','Sistema'),(8,'0006','Teclado sem Fio','Informática',5,'A1','A1','A1','A1','A6','Ativo','Bruno da Silveira Rodrigues'),(9,'0007','Teclado com Fio - Dell','Informática',5,'A1','A1','A1','A1','A7','Ativo','Bruno da Silveira Rodrigues'),(10,'0008','Mouse sem Fio','Informática',5,'A1','A1','A1','A1','A8','Ativo','Bruno da Silveira Rodrigues');
/*!40000 ALTER TABLE `itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_grupo`
--

DROP TABLE IF EXISTS `usuario_grupo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_grupo` (
  `id_usuario` int NOT NULL,
  `id_grupo` int NOT NULL,
  PRIMARY KEY (`id_usuario`,`id_grupo`),
  KEY `id_grupo` (`id_grupo`),
  CONSTRAINT `usuario_grupo_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `cadastro_user` (`cod_usuario`) ON DELETE CASCADE,
  CONSTRAINT `usuario_grupo_ibfk_2` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_grupo`
--

LOCK TABLES `usuario_grupo` WRITE;
/*!40000 ALTER TABLE `usuario_grupo` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario_grupo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-10 10:05:57
