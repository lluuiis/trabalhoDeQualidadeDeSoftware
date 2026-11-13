CREATE DATABASE  IF NOT EXISTS `banco_artes` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `banco_artes`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: banco_artes
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Luis Henrique Chagas Lauxen','luishenriquechagaslauxen@gmail.com','51998208111','Av. São Paulo, 795'),(3,'gabrielle','123@gmail.com','119999999','rua de tal');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contas_a_pagar`
--

DROP TABLE IF EXISTS `contas_a_pagar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contas_a_pagar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `status` enum('paga','pendente') NOT NULL DEFAULT 'pendente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contas_a_pagar`
--

LOCK TABLES `contas_a_pagar` WRITE;
/*!40000 ALTER TABLE `contas_a_pagar` DISABLE KEYS */;
INSERT INTO `contas_a_pagar` VALUES (1,'Aluguel',2300.00,'2025-07-01','paga'),(3,'Conta de Luz',200.00,'2025-06-30','pendente'),(4,'conta de agua',250.00,'2025-07-05','pendente');
/*!40000 ALTER TABLE `contas_a_pagar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materiais`
--

DROP TABLE IF EXISTS `materiais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `materiais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `unidade_medida` varchar(50) NOT NULL,
  `preco_custo` decimal(10,2) NOT NULL,
  `estoque_atual` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materiais`
--

LOCK TABLES `materiais` WRITE;
/*!40000 ALTER TABLE `materiais` DISABLE KEYS */;
INSERT INTO `materiais` VALUES (1,'linha azul - mzb','rolo',5.00,46.00),(2,'teste de materialhdfhdu','rolo',5.00,59.00),(3,'linha rosa','m',3.00,50.00);
/*!40000 ALTER TABLE `materiais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimentacao_estoque`
--

DROP TABLE IF EXISTS `movimentacao_estoque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimentacao_estoque` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) NOT NULL,
  `tipo_movimentacao` enum('entrada','saida_producao','ajuste_positivo','ajuste_negativo') NOT NULL,
  `quantidade` decimal(10,2) NOT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `movimentacao_estoque_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiais` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimentacao_estoque`
--

LOCK TABLES `movimentacao_estoque` WRITE;
/*!40000 ALTER TABLE `movimentacao_estoque` DISABLE KEYS */;
INSERT INTO `movimentacao_estoque` VALUES (1,1,'entrada',5.00,'2025-06-29 22:42:47','teste'),(2,1,'saida_producao',3.00,'2025-06-29 23:03:26','Produção de 3 unidade(s) do produto #1'),(3,1,'entrada',13.00,'2025-06-29 23:04:42',''),(4,1,'saida_producao',15.00,'2025-06-29 23:05:39','Produção de 3 unidade(s) do produto #2'),(5,2,'entrada',5.00,'2025-06-30 03:29:12',''),(6,1,'entrada',50.00,'2025-06-30 22:44:47',''),(7,3,'entrada',50.00,'2025-06-30 22:44:55',''),(8,2,'entrada',50.00,'2025-06-30 22:45:03',''),(9,1,'saida_producao',4.00,'2025-06-30 22:45:16','Produção de 2 unidade(s) do produto #3'),(10,2,'saida_producao',6.00,'2025-06-30 22:45:16','Produção de 2 unidade(s) do produto #3'),(11,2,'entrada',10.00,'2025-06-30 23:10:07','entrada do fornecedor');
/*!40000 ALTER TABLE `movimentacao_estoque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produto_materiais`
--

DROP TABLE IF EXISTS `produto_materiais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto_materiais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produto_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantidade_utilizada` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `produto_id` (`produto_id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `produto_materiais_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `produto_materiais_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materiais` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produto_materiais`
--

LOCK TABLES `produto_materiais` WRITE;
/*!40000 ALTER TABLE `produto_materiais` DISABLE KEYS */;
INSERT INTO `produto_materiais` VALUES (1,1,1,1.00),(2,2,1,5.00),(3,3,1,2.00),(4,3,2,3.00),(5,4,1,100.00);
/*!40000 ALTER TABLE `produto_materiais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produtos`
--

DROP TABLE IF EXISTS `produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `preco_venda` decimal(10,2) NOT NULL,
  `estoque_atual` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produtos`
--

LOCK TABLES `produtos` WRITE;
/*!40000 ALTER TABLE `produtos` DISABLE KEYS */;
INSERT INTO `produtos` VALUES (1,'vamos ver',22.00,2),(2,'outro teste',10.00,1),(3,'teste2',50.00,2),(4,'testeaula',80.00,0);
/*!40000 ALTER TABLE `produtos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_usuario` enum('administrador','funcionario') NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'1','1','administrador'),(2,'luis','testedesenha','administrador'),(4,'gabrielle','2022','administrador'),(6,'funcionario','funcionario','funcionario'),(7,'gabriellerj','Vicentina@123','administrador');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `venda_pagamentos`
--

DROP TABLE IF EXISTS `venda_pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `venda_pagamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `forma_pagamento` enum('Dinheiro','Cartão Débito','Cartão Crédito','Pix','Boleto','Crediário') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `status` enum('pago','pendente','cancelado') NOT NULL DEFAULT 'pendente',
  `data_pagamento` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `venda_id` (`venda_id`),
  CONSTRAINT `venda_pagamentos_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `venda_pagamentos`
--

LOCK TABLES `venda_pagamentos` WRITE;
/*!40000 ALTER TABLE `venda_pagamentos` DISABLE KEYS */;
INSERT INTO `venda_pagamentos` VALUES (1,1,'Dinheiro',10.00,'2025-06-29','','2025-06-29 21:42:47'),(2,2,'Crediário',32.00,'2025-07-29','pendente','2025-06-29 21:52:47'),(3,3,'Crediário',20.00,'2025-07-30','cancelado',NULL),(4,4,'Dinheiro',50.00,'2025-07-01','cancelado',NULL),(5,5,'Dinheiro',5.00,'2025-07-01','pago',NULL),(6,5,'Crediário',5.00,'2025-07-03','pendente',NULL),(7,6,'Dinheiro',50.00,'2025-07-01','cancelado',NULL),(8,6,'Cartão Débito',25.00,'2025-07-01','cancelado',NULL),(9,6,'Crediário',25.00,'2025-07-01','cancelado',NULL);
/*!40000 ALTER TABLE `venda_pagamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `venda_produtos`
--

DROP TABLE IF EXISTS `venda_produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `venda_produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario_venda` decimal(10,2) NOT NULL,
  `custo_unitario_producao` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `venda_id` (`venda_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `venda_produtos_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `venda_produtos_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `venda_produtos`
--

LOCK TABLES `venda_produtos` WRITE;
/*!40000 ALTER TABLE `venda_produtos` DISABLE KEYS */;
INSERT INTO `venda_produtos` VALUES (1,1,2,1,10.00,25.00),(2,2,2,1,10.00,25.00),(3,2,1,1,22.00,5.00),(4,3,2,1,10.00,25.00),(5,3,2,1,10.00,25.00),(6,4,3,1,50.00,25.00),(7,5,2,1,10.00,25.00),(8,6,3,2,50.00,25.00);
/*!40000 ALTER TABLE `venda_produtos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendas`
--

DROP TABLE IF EXISTS `vendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `data_venda` timestamp NOT NULL DEFAULT current_timestamp(),
  `valor_total` decimal(10,2) NOT NULL,
  `status_venda` enum('ativa','estornada') NOT NULL DEFAULT 'ativa',
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendas`
--

LOCK TABLES `vendas` WRITE;
/*!40000 ALTER TABLE `vendas` DISABLE KEYS */;
INSERT INTO `vendas` VALUES (1,1,'2025-06-29 23:24:15',10.00,'estornada'),(2,3,'2025-06-29 23:58:30',32.00,'ativa'),(3,3,'2025-06-30 02:40:12',20.00,'estornada'),(4,3,'2025-06-30 22:46:21',50.00,'estornada'),(5,3,'2025-06-30 22:49:38',10.00,'ativa'),(6,1,'2025-06-30 23:14:06',100.00,'estornada');
/*!40000 ALTER TABLE `vendas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-30 20:26:17
