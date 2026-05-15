/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: smartlocker
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `historial_paquetes`
--

DROP TABLE IF EXISTS `historial_paquetes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_paquetes` (
  `id` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `paquete_id` int(11) NOT NULL,
  `pin` int(11) NOT NULL,
  `taquilla` int(11) NOT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_paquetes`
--

LOCK TABLES `historial_paquetes` WRITE;
/*!40000 ALTER TABLE `historial_paquetes` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial_paquetes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lockers`
--

DROP TABLE IF EXISTS `lockers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lockers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codi` varchar(10) NOT NULL,
  `estat` enum('disponible','ocupat','no_preparat') DEFAULT 'disponible',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lockers`
--

LOCK TABLES `lockers` WRITE;
/*!40000 ALTER TABLE `lockers` DISABLE KEYS */;
INSERT INTO `lockers` VALUES
(1,'A','disponible'),
(2,'B','disponible');
/*!40000 ALTER TABLE `lockers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paquetes`
--

DROP TABLE IF EXISTS `paquetes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `paquetes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) NOT NULL,
  `paquete_id` varchar(20) NOT NULL,
  `taquilla` enum('A','B') NOT NULL,
  `pin` char(4) NOT NULL,
  `pin_repartidor` char(4) DEFAULT NULL,
  `pin_retirada` char(4) DEFAULT NULL,
  `estado` enum('pendiente','en_taquilla','recogido','caducado') NOT NULL DEFAULT 'pendiente',
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_recogida` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paquete_id` (`paquete_id`)
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paquetes`
--

LOCK TABLES `paquetes` WRITE;
/*!40000 ALTER TABLE `paquetes` DISABLE KEYS */;
INSERT INTO `paquetes` VALUES
(1,'alumne','PAQ-1001','A','3847',NULL,NULL,'recogido','2026-04-28 07:21:56','2026-05-08 08:46:02'),
(2,'alumne','PAQ-1002','B','9123',NULL,NULL,'recogido','2026-04-28 07:21:56','2026-05-08 07:55:26'),
(3,'alumne','PAQ-E28539','A','1885',NULL,NULL,'recogido','2026-04-28 07:37:05','2026-04-29 07:43:41'),
(4,'test','PAQ-408FFC','A','7893',NULL,NULL,'recogido','2026-04-28 08:44:19','2026-04-28 08:44:26'),
(5,'test','PAQ-5401B4','A','4300',NULL,NULL,'recogido','2026-04-28 08:48:52','2026-05-04 06:30:07'),
(6,'test','PAQ-C18E21','B','6900',NULL,NULL,'recogido','2026-04-28 08:50:34','2026-05-04 06:30:09'),
(7,'alexlopez','PAQ-50B0D8','A','3700',NULL,NULL,'recogido','2026-04-29 07:31:46','2026-04-29 08:03:11'),
(8,'alumne','PAQ-6E9333','A','7255',NULL,NULL,'recogido','2026-04-29 07:44:03','2026-05-04 06:30:09'),
(9,'nose','PAQ-46C124','B','7956',NULL,NULL,'recogido','2026-04-29 07:48:24','2026-05-04 06:30:10'),
(10,'alumne','PAQ-08F11D','A','6323',NULL,NULL,'recogido','2026-04-29 07:58:57','2026-05-04 06:30:30'),
(11,'nose','PAQ-E09980','B','1598',NULL,NULL,'recogido','2026-04-29 08:02:49','2026-05-04 06:30:49'),
(12,'alex','PAQ-4315FD','B','8434',NULL,NULL,'recogido','2026-04-29 08:18:47','2026-05-04 06:29:23'),
(13,'alex','PAQ-220E5C','A','8750',NULL,NULL,'recogido','2026-04-29 08:18:47','2026-05-04 06:29:22'),
(16,'nose','PAQ-1630EF','A','8939',NULL,NULL,'recogido','2026-04-29 08:18:48','2026-05-05 06:49:50'),
(17,'nose','PAQ-8DC506','A','6260',NULL,NULL,'recogido','2026-04-29 08:18:48','2026-05-08 08:46:03'),
(20,'alex','PAQ-471FFE','B','6852',NULL,NULL,'recogido','2026-04-29 08:18:48','2026-05-08 08:46:07'),
(21,'nose','PAQ-1C588E','B','2244',NULL,NULL,'recogido','2026-04-29 08:18:48','2026-05-08 07:56:45'),
(23,'nose','PAQ-E8BEB0','B','4663',NULL,NULL,'recogido','2026-04-29 08:18:56','2026-05-08 08:46:16'),
(24,'alex','PAQ-EA1BE7','B','5619',NULL,NULL,'recogido','2026-04-29 08:18:56','2026-05-07 10:47:44'),
(25,'alex','PAQ-1706DA','A','8800',NULL,NULL,'recogido','2026-04-29 08:18:56','2026-05-08 08:46:15'),
(26,'alex','PAQ-42BAD5','B','8063',NULL,NULL,'recogido','2026-04-29 08:18:56','2026-05-08 08:46:09'),
(27,'nose','PAQ-F3E8D1','B','2462',NULL,NULL,'recogido','2026-04-29 08:18:56','2026-05-08 08:46:14'),
(28,'alex','PAQ-A4CE93','A','1222',NULL,NULL,'recogido','2026-04-29 08:18:56','2026-05-08 08:46:08'),
(29,'nose','PAQ-632E52','A','3734',NULL,NULL,'recogido','2026-04-29 08:18:56','2026-05-05 08:30:17'),
(30,'nose','PAQ-1A246A','A','6674',NULL,NULL,'recogido','2026-04-29 08:18:56','2026-05-08 08:46:10'),
(31,'alex','PAQ-264CB4','A','5349',NULL,NULL,'recogido','2026-04-29 08:18:56','2026-05-08 08:46:07'),
(32,'alex','PAQ-43712E','B','3920',NULL,NULL,'recogido','2026-04-29 10:25:30','2026-05-08 08:46:21'),
(33,'nose','PAQ-54B035','A','5886',NULL,NULL,'recogido','2026-04-29 10:25:30','2026-05-08 08:46:22'),
(34,'alex','PAQ-17EAD2','A','3440',NULL,NULL,'recogido','2026-04-29 10:25:30','2026-05-08 08:46:30'),
(35,'alex','PAQ-85ADC3','A','6383',NULL,NULL,'recogido','2026-04-29 10:25:30','2026-05-11 06:26:40'),
(36,'alex','PAQ-D48AEE','B','3355',NULL,NULL,'recogido','2026-04-29 10:25:30','2026-05-12 10:40:53'),
(37,'alex','PAQ-E95DE6','A','6001',NULL,NULL,'recogido','2026-04-29 10:25:30','2026-05-08 08:46:17'),
(39,'nose','PAQ-900F32','B','9559',NULL,NULL,'recogido','2026-04-29 10:25:30','2026-05-12 10:40:53'),
(40,'nose','PAQ-7192D5','A','5740',NULL,NULL,'recogido','2026-04-29 10:25:30','2026-05-08 08:46:16'),
(41,'nose','PAQ-AE503F','A','7253',NULL,NULL,'recogido','2026-04-29 10:25:30','2026-05-08 08:46:25'),
(42,'nose','PAQ-276ED8','A','6365',NULL,NULL,'recogido','2026-04-29 10:30:01','2026-05-08 08:46:21'),
(44,'alex','PAQ-0909FE','A','9943',NULL,NULL,'recogido','2026-05-04 06:20:35','2026-05-08 08:41:34'),
(47,'alex','PAQ-4F804C','B','1534',NULL,NULL,'recogido','2026-05-04 06:20:35','2026-05-05 06:36:33'),
(48,'nose','PAQ-B1BA2E','A','2278',NULL,NULL,'recogido','2026-05-04 06:20:35','2026-05-05 06:36:32'),
(49,'nose','PAQ-3D7823','A','9265',NULL,NULL,'recogido','2026-05-04 06:20:35','2026-05-08 07:55:41'),
(50,'nose','PAQ-FCB83F','A','5391',NULL,NULL,'recogido','2026-05-04 06:20:35','2026-05-08 07:56:27'),
(51,'nose','PAQ-736E13','B','6152',NULL,NULL,'recogido','2026-05-04 06:20:35','2026-05-08 08:02:42'),
(52,'alex','PAQ-17FE5D','B','2992',NULL,NULL,'recogido','2026-05-04 06:20:35','2026-05-05 06:12:48'),
(53,'alex','PAQ-F8820F','B','9471',NULL,NULL,'recogido','2026-05-04 06:20:44','2026-05-08 08:25:29'),
(54,'nose','PAQ-C83904','B','8299',NULL,NULL,'recogido','2026-05-04 06:20:44','2026-05-05 06:43:16'),
(55,'nose','PAQ-5A7E33','A','1578',NULL,NULL,'recogido','2026-05-04 06:20:44','2026-05-05 06:43:12'),
(56,'alex','PAQ-8E37B6','A','5031',NULL,NULL,'recogido','2026-05-04 06:20:44','2026-05-05 06:43:10'),
(57,'alex','PAQ-753B79','A','7638',NULL,NULL,'recogido','2026-05-04 06:20:44','2026-05-05 06:43:10'),
(58,'nose','PAQ-3AD8D5','B','8080',NULL,NULL,'recogido','2026-05-04 06:20:44','2026-05-05 06:42:09'),
(59,'nose','PAQ-007BBB','B','8598',NULL,NULL,'recogido','2026-05-04 06:20:44','2026-05-05 06:42:09'),
(60,'nose','PAQ-BBEF9A','A','5056',NULL,NULL,'recogido','2026-05-04 06:20:44','2026-05-05 06:42:30'),
(61,'alex','PAQ-DA007A','B','1031',NULL,NULL,'recogido','2026-05-04 06:20:44','2026-05-05 06:43:09'),
(63,'nose','PAQ-EA996C','A','9622',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:47:37'),
(64,'alex','PAQ-97CE1F','A','7053',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:47:37'),
(65,'alex','PAQ-2D94E1','A','3679',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:47:38'),
(66,'alex','PAQ-FFC7DC','A','3259',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:47:41'),
(67,'alex','PAQ-5ED33B','A','9508',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:43:17'),
(68,'alex','PAQ-89C9AE','A','5847',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:47:42'),
(69,'alex','PAQ-87D8BA','A','1548',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:43:14'),
(70,'alex','PAQ-67CD1A','A','7270',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:43:14'),
(71,'alex','PAQ-69A91E','A','8633',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:43:15'),
(72,'alex','PAQ-8998C7','A','1115',NULL,NULL,'recogido','2026-05-04 06:20:47','2026-05-05 06:43:12'),
(73,'nose','PAQ-94E73A','A','9100',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:51'),
(74,'alex','PAQ-559075','A','4526',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:51'),
(75,'alex','PAQ-18FA2A','B','5984',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:50'),
(76,'nose','PAQ-28D1A4','B','3407',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:49'),
(77,'nose','PAQ-5FC42D','B','9477',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:47'),
(78,'alex','PAQ-C917B3','A','4594',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:47'),
(79,'alex','PAQ-48A7CB','B','2469',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:45'),
(80,'nose','PAQ-DE48E2','B','2401',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:43'),
(81,'nose','PAQ-7A3C2F','B','4246',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:44'),
(82,'alex','PAQ-FFAF00','B','3349',NULL,NULL,'recogido','2026-05-04 06:20:48','2026-05-05 06:47:45'),
(83,'alex','PAQ-C4E63D','B','7534',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:48:06'),
(84,'alex','PAQ-9FA633','B','4358',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:48:04'),
(85,'nose','PAQ-A34C59','A','8817',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:48:02'),
(86,'nose','PAQ-C03A80','A','3953',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:48:00'),
(87,'alex','PAQ-2AE07E','B','1599',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:47:59'),
(88,'alex','PAQ-E8A39A','B','2845',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:47:57'),
(89,'alex','PAQ-92E3D1','B','6695',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:47:53'),
(90,'alex','PAQ-EACCD8','B','6659',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:47:53'),
(91,'alex','PAQ-049BE6','B','1997',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:48:54'),
(92,'alex','PAQ-D69EA7','A','5307',NULL,NULL,'recogido','2026-05-04 06:23:09','2026-05-05 06:47:55'),
(93,'nose','PAQ-26F43F','B','1290',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:23'),
(94,'nose','PAQ-3DE248','A','9279',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:22'),
(95,'nose','PAQ-754F15','A','6138',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:20'),
(96,'nose','PAQ-E62D63','B','7790',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:18'),
(97,'alex','PAQ-E859BC','A','6292',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:16'),
(98,'nose','PAQ-1266F0','B','1087',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:15'),
(99,'alex','PAQ-BF5FB2','B','2364',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:08'),
(100,'nose','PAQ-205B13','B','1684',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:09'),
(101,'alex','PAQ-6EB7BA','B','8869',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:11'),
(102,'nose','PAQ-BB2253','A','4952',NULL,NULL,'recogido','2026-05-04 06:27:12','2026-05-05 06:48:13'),
(103,'alex','PAQ-ADCE89','A','6926',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:41'),
(104,'nose','PAQ-93DF26','A','9367',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:40'),
(105,'alex','PAQ-513DAE','B','1074',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:38'),
(106,'alex','PAQ-758F25','B','1207',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:36'),
(107,'nose','PAQ-9AE713','B','7057',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:34'),
(108,'nose','PAQ-55B649','B','2695',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:32'),
(109,'alex','PAQ-775ADC','A','1189',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:25'),
(110,'alex','PAQ-B07AD3','B','7780',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:27'),
(111,'alex','PAQ-92740D','B','8353',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:29'),
(112,'alex','PAQ-C658AE','B','9783',NULL,NULL,'recogido','2026-05-04 06:29:07','2026-05-05 06:48:31'),
(113,'nose','PAQ-C2B389','A','8558',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-05 06:48:56'),
(114,'alex','PAQ-74E785','A','4873',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-05 06:48:57'),
(115,'nose','PAQ-D014D3','A','3176',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-05 06:48:59'),
(116,'alex','PAQ-3422A9','A','1999',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-05 06:49:01'),
(117,'nose','PAQ-84A66B','A','6453',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-05 06:49:03'),
(118,'nose','PAQ-ACB3DA','A','7019',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-05 06:48:49'),
(119,'alex','PAQ-5182CC','B','9389',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-05 06:48:47'),
(120,'nose','PAQ-BED720','A','4906',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-05 06:48:45'),
(121,'alex','PAQ-017AAD','B','2163',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-05 06:48:43'),
(122,'nose','PAQ-9315F0','B','3845',NULL,NULL,'recogido','2026-05-04 06:29:10','2026-05-04 06:31:58'),
(123,'alumne','PAQ-BAA011','A','2424',NULL,NULL,'recogido','2026-05-04 06:30:43','2026-05-05 06:49:05'),
(124,'nose','PAQ-6C3E0C','B','2475',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:21'),
(125,'alex','PAQ-6079D4','B','7332',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:18'),
(126,'nose','PAQ-EA2E70','A','6313',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:19'),
(127,'alex','PAQ-2D73EC','B','6420',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:15'),
(128,'alex','PAQ-AFCE9A','A','4813',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:14'),
(129,'nose','PAQ-E283DC','B','1029',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:17'),
(130,'nose','PAQ-621A0D','A','9292',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:12'),
(131,'alex','PAQ-3F9829','B','3002',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:10'),
(132,'alex','PAQ-103974','B','2772',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:08'),
(133,'nose','PAQ-53E5F8','B','9871',NULL,NULL,'recogido','2026-05-04 07:54:25','2026-05-05 06:49:06'),
(134,'alex','PAQ-F2217D','B','1268',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:40'),
(135,'nose','PAQ-B2FBE9','A','9623',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:38'),
(136,'nose','PAQ-BE9CF8','A','9191',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:36'),
(137,'nose','PAQ-1D28DB','B','7330',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:35'),
(138,'nose','PAQ-9B8F1E','B','4075',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:33'),
(139,'alex','PAQ-039C6E','A','5978',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:31'),
(140,'alex','PAQ-B904CB','B','8841',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:23'),
(141,'alex','PAQ-4EC44A','A','8749',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:25'),
(142,'nose','PAQ-5AD670','A','6235',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:26'),
(143,'alex','PAQ-666997','B','1210',NULL,NULL,'recogido','2026-05-04 08:35:36','2026-05-05 06:49:29'),
(144,'cliente','PAQ-B626BD','A','4300',NULL,NULL,'recogido','2026-05-04 10:03:10','2026-05-04 10:03:33'),
(145,'cliente','PAQ-387BE7','A','6458',NULL,NULL,'recogido','2026-05-04 10:11:25','2026-05-04 10:11:43'),
(146,'cliente','PAQ-D376A0','A','3305',NULL,NULL,'recogido','2026-05-04 10:12:43','2026-05-04 10:19:44'),
(147,'cliente','PAQ-B8201E','B','1166',NULL,NULL,'recogido','2026-05-04 10:12:47','2026-05-04 10:19:38'),
(148,'cliente','PAQ-C10449','A','5210',NULL,NULL,'recogido','2026-05-04 10:20:05','2026-05-04 10:24:29'),
(149,'cliente','PAQ-0C1D23','A','6737',NULL,NULL,'recogido','2026-05-04 10:24:51','2026-05-05 06:49:41'),
(150,'nose','PAQ-18D227','A','4665',NULL,NULL,'recogido','2026-05-05 06:42:20','2026-05-05 06:49:43'),
(151,'nose','PAQ-2EC606','B','2595',NULL,NULL,'recogido','2026-05-05 06:49:59','2026-05-05 06:50:10'),
(152,'nose','PAQ-7171F9','B','2871',NULL,NULL,'recogido','2026-05-05 06:49:59','2026-05-05 07:03:16'),
(153,'alex','PAQ-FF404F','B','3122',NULL,NULL,'recogido','2026-05-05 06:49:59','2026-05-05 07:32:04'),
(154,'nose','PAQ-AC26F4','A','3142',NULL,NULL,'recogido','2026-05-05 06:49:59','2026-05-05 07:32:05'),
(155,'nose','PAQ-7284ED','A','4178',NULL,NULL,'recogido','2026-05-05 06:49:59','2026-05-05 07:32:08'),
(156,'cliente','PAQ-72E196','A','2448',NULL,NULL,'recogido','2026-05-05 06:49:59','2026-05-05 06:50:55'),
(157,'nose','PAQ-355A50','B','9216',NULL,NULL,'recogido','2026-05-05 06:49:59','2026-05-05 07:32:10'),
(158,'nose','PAQ-207461','A','4578',NULL,NULL,'recogido','2026-05-05 06:49:59','2026-05-05 07:32:11'),
(159,'cliente','PAQ-0A7385','B','6360',NULL,NULL,'recogido','2026-05-05 06:49:59','2026-05-05 07:32:16'),
(161,'cliente','PAQ-73D83A','A','4036',NULL,NULL,'recogido','2026-05-05 06:50:39','2026-05-05 07:32:17'),
(162,'cliente','PAQ-515914','B','6328',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:33:07'),
(163,'alex','PAQ-0F6640','A','3983',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:33:04'),
(164,'alex','PAQ-3FE533','A','4298',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:33:02'),
(165,'nose','PAQ-E5E09C','A','8862',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:33:00'),
(166,'cliente','PAQ-3DAB9B','B','1102',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:32:58'),
(167,'nose','PAQ-23FA61','B','1534',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:32:56'),
(168,'cliente','PAQ-FA836A','B','1375',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:32:22'),
(169,'alex','PAQ-3FFA50','B','6490',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:32:21'),
(170,'alex','PAQ-075042','A','2587',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:32:21'),
(171,'cliente','PAQ-EDCA03','A','4700',NULL,NULL,'recogido','2026-05-05 07:02:17','2026-05-05 07:32:17'),
(172,'nose','PAQ-17B221','B','9140',NULL,NULL,'recogido','2026-05-05 07:03:14','2026-05-05 07:33:09'),
(173,'alex','PAQ-E4C7E7','A','1760',NULL,NULL,'recogido','2026-05-05 07:33:14','2026-05-05 07:34:53'),
(174,'nose','PAQ-F387C3','B','7769',NULL,NULL,'recogido','2026-05-05 07:33:14','2026-05-05 07:34:57'),
(175,'cliente','PAQ-6BD90D','A','2572',NULL,NULL,'recogido','2026-05-05 07:35:01','2026-05-06 07:19:41'),
(176,'alex','PAQ-FECEFE','B','8839',NULL,NULL,'recogido','2026-05-05 07:35:01','2026-05-06 07:19:42'),
(177,'alex','PAQ-B57495','A','3062',NULL,NULL,'recogido','2026-05-06 07:19:46','2026-05-06 07:30:43'),
(178,'cliente','PAQ-AD3670','B','9347',NULL,NULL,'recogido','2026-05-06 07:19:46','2026-05-06 07:30:44'),
(179,'alex','PAQ-43608C','A','2443',NULL,NULL,'recogido','2026-05-06 07:30:50','2026-05-06 07:37:19'),
(180,'cliente','PAQ-A74FF9','B','5302',NULL,NULL,'recogido','2026-05-06 07:30:50','2026-05-06 07:37:21'),
(181,'nose','PAQ-4E9C4C','A','2725',NULL,NULL,'recogido','2026-05-06 07:37:36','2026-05-06 07:45:37'),
(182,'alex','PAQ-9511B0','B','4716',NULL,NULL,'recogido','2026-05-06 07:37:36','2026-05-06 07:45:13'),
(183,'nose','PAQ-8C9126','A','3835',NULL,NULL,'recogido','2026-05-06 07:43:56','2026-05-06 07:45:30'),
(184,'alex','PAQ-1C6ED3','B','9908',NULL,NULL,'recogido','2026-05-06 07:44:19','2026-05-06 07:45:09'),
(186,'nose','PAQ-E02FE5','B','1021',NULL,NULL,'recogido','2026-05-06 07:45:54','2026-05-06 07:54:43'),
(187,'alex','PAQ-E5674C','A','2690',NULL,NULL,'recogido','2026-05-06 07:53:20','2026-05-06 08:16:11'),
(188,'alex','PAQ-943B10','B','3221',NULL,NULL,'recogido','2026-05-06 07:53:20','2026-05-06 08:16:15'),
(189,'alex','PAQ-E38F28','A','2378',NULL,NULL,'recogido','2026-05-06 08:20:06','2026-05-06 08:44:16'),
(190,'alex','PAQ-45ED93','B','6087',NULL,NULL,'recogido','2026-05-06 08:20:06','2026-05-06 08:44:19'),
(191,'alex','PAQ-D42E63','A','4056',NULL,NULL,'recogido','2026-05-06 08:45:20','2026-05-06 08:49:57'),
(192,'alex','PAQ-342133','B','2612',NULL,NULL,'recogido','2026-05-06 08:45:20','2026-05-06 08:50:01'),
(197,'nose','PAQ-1F8F38','B','4192',NULL,NULL,'recogido','2026-05-06 09:36:18','2026-05-06 09:37:02'),
(198,'nose','PAQ-101D65','A','7689',NULL,NULL,'recogido','2026-05-06 09:36:18','2026-05-06 11:09:04'),
(199,'cliente','PAQ-6E9F02','A','3129',NULL,NULL,'recogido','2026-05-07 07:19:44','2026-05-07 07:23:02'),
(200,'nose','PAQ-1CD6D6','B','8175',NULL,NULL,'recogido','2026-05-07 07:19:44','2026-05-07 07:22:26'),
(201,'nose','PAQ-DB77B9','A','2408','7656',NULL,'recogido','2026-05-07 07:27:01','2026-05-07 08:00:33'),
(202,'cliente','PAQ-AF0467','B','9582','9932',NULL,'recogido','2026-05-07 07:27:01','2026-05-07 07:52:50'),
(203,'cliente','PAQ-0B02DF','B','4837','5654',NULL,'recogido','2026-05-07 09:41:30','2026-05-07 09:42:19'),
(204,'alex','PAQ-925C5A','A','5306','8113',NULL,'recogido','2026-05-07 09:41:30','2026-05-07 09:42:03'),
(206,'nose','PAQ-3AF092','A','2068','5253',NULL,'recogido','2026-05-07 09:57:44','2026-05-07 10:03:53'),
(207,'nose','PAQ-3FF49F','A','7929','1082',NULL,'recogido','2026-05-07 10:22:25','2026-05-07 10:24:43'),
(208,'nose','PAQ-3332DA','B','1188','3586',NULL,'recogido','2026-05-07 10:22:25','2026-05-07 10:24:16'),
(209,'alex','PAQ-17CB03','A','4940','7736',NULL,'recogido','2026-05-07 10:48:40','2026-05-07 10:50:52'),
(210,'nose','PAQ-D5513F','A','7320','1046',NULL,'recogido','2026-05-07 10:48:40','2026-05-07 11:36:34'),
(211,'nose','PAQ-99CC28','B','9062','7374',NULL,'recogido','2026-05-07 10:51:12','2026-05-07 10:55:01'),
(212,'alex','PAQ-33F0A1','A','2157','1110',NULL,'recogido','2026-05-07 10:51:33','2026-05-07 10:54:51'),
(214,'nose','PAQ-6EE25D','A','2058','9038',NULL,'recogido','2026-05-08 07:43:30','2026-05-08 07:49:29'),
(215,'cliente','PAQ-625D4E','B','9250','1730',NULL,'recogido','2026-05-08 07:43:30','2026-05-08 07:50:01'),
(216,'alex','PAQ-B7D227','A','8695','5054',NULL,'recogido','2026-05-08 07:52:25','2026-05-08 08:00:38'),
(217,'cliente','PAQ-54B7C7','B','3887','9039',NULL,'recogido','2026-05-08 07:52:25','2026-05-08 08:00:14'),
(218,'alex','PAQ-374052','A','3383','3284',NULL,'recogido','2026-05-08 08:04:16','2026-05-08 08:12:05'),
(219,'cliente','PAQ-16C865','B','9211','3954',NULL,'recogido','2026-05-08 08:04:16','2026-05-08 08:12:21'),
(220,'alex','PAQ-A3822F','A','2257','3284',NULL,'recogido','2026-05-08 08:05:09','2026-05-08 08:12:40'),
(221,'alex','PAQ-BD2EE0','A','9499','5639',NULL,'recogido','2026-05-08 08:16:55','2026-05-08 08:18:00'),
(222,'cliente','PAQ-7B5669','B','8071','8147',NULL,'recogido','2026-05-08 08:16:55','2026-05-08 08:17:35'),
(223,'cliente','PAQ-97C3B5','B','5833','6088',NULL,'recogido','2026-05-08 08:18:36','2026-05-08 08:20:58'),
(224,'alex','PAQ-EC11F9','A','1895','6724',NULL,'recogido','2026-05-08 08:18:37','2026-05-08 08:21:12'),
(225,'cliente','PAQ-AC3A41','B','1605','6088',NULL,'recogido','2026-05-08 08:18:54','2026-05-08 08:19:55'),
(226,'nose','PAQ-85632C','B','7038','4977',NULL,'recogido','2026-05-08 08:24:20','2026-05-08 10:37:59'),
(228,'nose','PAQ-2A13DA','A','9264','3702',NULL,'recogido','2026-05-08 08:46:55','2026-05-08 08:51:39'),
(229,'cliente','PAQ-91F6CA','A','2400','6034',NULL,'recogido','2026-05-08 10:39:38','2026-05-08 10:46:58'),
(230,'nose','PAQ-4B94E1','B','8603','9014',NULL,'recogido','2026-05-08 10:39:38','2026-05-08 10:46:03'),
(231,'cliente','PAQ-9BD74C','A','7976','4957',NULL,'recogido','2026-05-08 10:46:27','2026-05-08 10:56:22'),
(232,'cliente','PAQ-CFFE18','A','1659','2389',NULL,'recogido','2026-05-08 10:46:27','2026-05-08 11:15:18'),
(233,'cliente','PAQ-867688','B','8345','2929',NULL,'recogido','2026-05-08 10:47:43','2026-05-08 11:15:09'),
(238,'probaregistre','PAQ-6BA120','A','3863','3805',NULL,'recogido','2026-05-11 06:26:54','2026-05-11 06:32:56'),
(239,'probaregistre','PAQ-70AF02','B','9642','5672',NULL,'recogido','2026-05-11 06:26:54','2026-05-11 06:33:31'),
(240,'probaregistre','PAQ-0066CE','A','4887','3805',NULL,'recogido','2026-05-11 06:27:32','2026-05-11 06:32:50'),
(243,'alex','PAQ-D4F151','A','1584','5673',NULL,'recogido','2026-05-11 06:35:56','2026-05-11 06:37:00'),
(244,'alex','PAQ-7544F5','B','6050','1129',NULL,'recogido','2026-05-11 06:36:39','2026-05-11 06:37:08'),
(246,'alex','PAQ-02819E','B','2239','1129',NULL,'recogido','2026-05-11 07:01:15','2026-05-11 07:02:10'),
(247,'alex','PAQ-B53944','A','3510','8956',NULL,'recogido','2026-05-11 07:01:38','2026-05-11 07:02:06'),
(250,'cliente','PAQ-79F374','A','9348','9870',NULL,'recogido','2026-05-11 07:02:48','2026-05-11 07:06:30'),
(257,'alex','PAQ-E21CC2','B','4925','4231',NULL,'recogido','2026-05-11 07:48:23','2026-05-11 07:49:43'),
(263,'nose','PAQ-537ADC','A','8307','7799',NULL,'recogido','2026-05-11 08:26:31','2026-05-11 09:42:38'),
(265,'cliente','PAQ-CF1A4C','B','5284','4396',NULL,'recogido','2026-05-11 09:43:38','2026-05-11 09:48:04'),
(266,'nose','PAQ-E02798','A','7531','3913',NULL,'recogido','2026-05-11 09:44:02','2026-05-11 09:45:44'),
(267,'nose','PAQ-0C9299','A','6662','3913',NULL,'recogido','2026-05-11 09:59:13','2026-05-11 10:14:56'),
(269,'nose','PAQ-60F199','A','4703','3913',NULL,'recogido','2026-05-12 07:48:43','2026-05-12 10:26:33'),
(280,'alex','PAQ-875569','A','9876','2178',NULL,'recogido','2026-05-12 10:34:48','2026-05-12 10:35:51'),
(281,'probaregistre','PAQ-B8608E','B','4249','3890',NULL,'recogido','2026-05-12 10:35:15','2026-05-12 10:37:25'),
(284,'probaregistre','PAQ-807447','A','1938','9561',NULL,'recogido','2026-05-12 10:42:17','2026-05-12 10:53:28'),
(285,'cliente','PAQ-451CB5','B','3014','3506',NULL,'recogido','2026-05-12 10:42:17','2026-05-12 10:54:23'),
(286,'cliente','PAQ-B71981','B','6503','3506',NULL,'recogido','2026-05-12 10:42:50','2026-05-12 10:48:05'),
(287,'probaregistre','PAQ-B250A6','A','9888','9561',NULL,'recogido','2026-05-12 10:43:31','2026-05-12 10:49:59'),
(290,'nose','PAQ-F24ACE','A','2721','3326',NULL,'recogido','2026-05-12 10:56:05','2026-05-12 10:57:30'),
(291,'nose','PAQ-A2CE6F','B','6405','2433',NULL,'recogido','2026-05-12 10:56:17','2026-05-12 10:56:47'),
(292,'nose','PAQ-F261AA','A','8607','3326',NULL,'recogido','2026-05-13 07:18:16','2026-05-13 07:24:42'),
(293,'nose','PAQ-53B913','B','8000','2433',NULL,'recogido','2026-05-13 07:47:06','2026-05-13 09:45:58'),
(294,'cliente','PAQ-4D7B09','A','8164','7638','1329','recogido','2026-05-01 09:53:40','2026-05-15 08:23:07'),
(295,'alex','PAQ-50FF17','B','5890','1834',NULL,'recogido','2026-05-13 09:53:40','2026-05-15 08:17:45'),
(297,'alex','PAQ-2C1D64','B','8793','1119',NULL,'recogido','2026-05-15 08:23:15','2026-05-15 08:27:56'),
(298,'alex','PAQ-537F34','B','8718','9544',NULL,'recogido','2026-05-15 08:28:38','2026-05-15 08:32:04'),
(299,'cliente','PAQ-E6136D','B','3510','1894',NULL,'recogido','2026-05-15 08:36:16','2026-05-15 08:40:22'),
(300,'cliente','PAQ-5D4A1B','B','9108','2342',NULL,'recogido','2026-05-15 08:41:33','2026-05-15 08:46:26'),
(304,'cliente','PAQ-BB40D4','A','3795','3498','4471','recogido','2026-04-15 10:02:35','2026-05-15 10:38:03'),
(305,'alex','PAQ-F2B38F','B','9894','5462',NULL,'pendiente','2026-05-15 10:02:35',NULL);
/*!40000 ALTER TABLE `paquetes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('user','consumidor','repartidor') NOT NULL DEFAULT 'consumidor',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'danilalegre','$2y$10$NpROBf6pdCFa2xq7/9i4Uu7TyaEgNlKqgOgZjO7HNoOhX2rk1M71a','user','activo','2026-04-28 08:26:12'),
(2,'alumne','$2y$10$SsmgjWc8pHTp9.yTW0zeGOdgEBmcYsdWQBIdL4ierbgOUd6g4IrQe','user','activo','2026-04-28 08:27:22'),
(3,'dalegrei','$2y$10$hxT31ZzLOEBsiKoiBYu71.6Mv7EHeanrNmnk25oolVuUBGkePKUhC','user','activo','2026-04-28 08:28:24'),
(6,'alex','$2y$10$QC/CGZSk0DdLShUVLWMcsevOOCFoqpHDMB0gRSxn.G9KXYzGzz.HK','consumidor','activo','2026-04-29 07:28:00'),
(7,'alexlopez','$2y$10$AytDOrEttEFvXb6JkwRR2eOt3ATOX.ohRJJbHr4rH9jQy4dqzINIG','repartidor','activo','2026-04-29 07:31:23'),
(8,'nose','$2y$10$QxId0QiJgrBNfn0VFG8Juu1a6uFAuvvodlv5fEUrRiKJmwFHhGlWu','consumidor','activo','2026-04-29 07:44:44'),
(9,'cuenta','$2y$10$YvdBSumJxWZaatyKXCrvI.v4sjF4.IJ0qH1.gdpMk./z0u8ogvMUm','repartidor','activo','2026-04-29 10:25:08'),
(10,'prueba','$2y$10$XXEu8wSI/q3wHwRgYdlJr.rWjPbDAFB/35fBBvbXMMNA4I5hpY1ti','repartidor','activo','2026-05-04 07:39:51'),
(11,'cliente','$2y$10$SHGTj0mWpNLiVjtxfmF4ke9.f7mL9CmS6WMTF52pnS05WMkFxkWm6','consumidor','activo','2026-05-04 10:02:12'),
(13,'repartidor','$2y$10$jMaeTd0KA.Me7cQZmfhCy.4Nj6huah5heFQ4KXm8NQ4tPpvL1XeK6','repartidor','activo','2026-05-06 07:59:00'),
(14,'probaregistre','$2y$10$SkbIoeatX9xa4eBPQoD4Gu91PwJrcXMawYJ28jv2qLNeBFTRw4wWu','consumidor','activo','2026-05-11 06:07:12'),
(15,'dalegr','$2y$10$kjbt4k5vG29WygiVoaZZbeatpN6muKdmotRKlWfBdiNymbA/Il/MW','repartidor','activo','2026-05-12 07:47:28'),
(16,'andreu','$2y$10$LyC6pWCOXHs77kAGIiWbmenk3Q/3bn3yirtaNoJcqIAA7RDulz6He','repartidor','activo','2026-05-15 09:58:46');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-15 11:32:19
