-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: test_demo_db
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `username` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_data` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FD06F647A76ED395` (`user_id`),
  CONSTRAINT `FK_FD06F647A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=317 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,NULL,'create','User',1,'New user registered: user (Role: ROLE_USER)','127.0.0.1','2026-03-11 18:20:33',NULL,NULL,NULL),(4,1,'logout','User',1,'User user logged out','127.0.0.1','2026-03-11 19:03:29',NULL,NULL,NULL),(5,1,'login','User',1,'User user logged in','127.0.0.1','2026-03-11 19:03:57',NULL,NULL,NULL),(6,1,'create','User',2,'Admin created user: Admin with role: ROLE_ADMIN','127.0.0.1','2026-03-11 19:05:04',NULL,NULL,NULL),(7,1,'update','User',2,'Admin updated user: Admin (role: ROLE_ADMIN → ROLE_STAFF)','127.0.0.1','2026-03-11 19:05:28',NULL,NULL,NULL),(8,1,'update','User',2,'Admin updated user: Admin (role: ROLE_STAFF → ROLE_ADMIN)','127.0.0.1','2026-03-11 19:05:48',NULL,NULL,NULL),(9,1,'logout','User',1,'User user logged out','127.0.0.1','2026-03-11 19:06:31',NULL,NULL,NULL),(10,2,'login','User',2,'User Admin logged in','127.0.0.1','2026-03-11 19:06:46',NULL,NULL,NULL),(11,2,'update','User',1,'Admin updated user: user (role: ROLE_ADMIN → ROLE_STAFF)','127.0.0.1','2026-03-11 19:07:07',NULL,NULL,NULL),(12,2,'create','Category',1,'Admin created Category: #1 - Coffee','127.0.0.1','2026-03-11 19:09:09',NULL,NULL,NULL),(13,2,'login','User',2,'User Admin logged in','127.0.0.1','2026-03-11 19:15:34',NULL,NULL,NULL),(14,1,'login','User',1,'User user logged in','127.0.0.1','2026-03-12 07:49:58',NULL,NULL,NULL),(15,1,'create','Product',1,'Staff created product: Black Coffee (Price: ₱70.00)','127.0.0.1','2026-03-12 07:51:41',NULL,NULL,NULL),(16,1,'create','Stock',1,'Staff created Stock: #1 - Product: Black Coffee, Qty: 6','127.0.0.1','2026-03-12 07:52:11',NULL,NULL,NULL),(17,1,'create','Customer',1,'Staff created Customer: Ann - Email: Ann@gmail.com, Phone: 0001','127.0.0.1','2026-03-12 07:53:17',NULL,NULL,NULL),(18,1,'logout','User',1,'User user logged out','127.0.0.1','2026-03-12 07:53:49',NULL,NULL,NULL),(19,2,'login','User',2,'User Admin logged in','127.0.0.1','2026-03-12 07:54:07',NULL,NULL,NULL),(20,2,'update','User',1,'Admin updated user: staff1','127.0.0.1','2026-03-12 07:54:41',NULL,NULL,NULL),(21,9,'login','User',9,'User admin0 logged in','127.0.0.1','2026-03-19 21:17:20',NULL,NULL,NULL),(22,2,'login','User',2,'User Admin logged in','127.0.0.1','2026-03-19 21:17:39',NULL,NULL,NULL),(23,2,'logout','User',2,'User Admin logged out','127.0.0.1','2026-03-19 21:32:15',NULL,NULL,NULL),(24,1,'login','User',1,'User staff1 logged in','127.0.0.1','2026-03-19 21:33:41',NULL,NULL,NULL),(25,1,'logout','User',1,'User staff1 logged out','127.0.0.1','2026-03-19 21:35:03',NULL,NULL,NULL),(26,2,'login','User',2,'User Admin logged in','127.0.0.1','2026-03-19 21:35:17',NULL,NULL,NULL),(27,2,'update','User',3,'Admin updated user: admin2','127.0.0.1','2026-03-19 21:36:28',NULL,NULL,NULL),(28,2,'update','User',1,'Admin reset password for user: staff1','127.0.0.1','2026-03-19 21:36:52',NULL,NULL,NULL),(29,2,'logout','User',2,'User Admin logged out','127.0.0.1','2026-03-19 21:38:22',NULL,NULL,NULL),(30,13,'login','User',13,'User user13 logged in','127.0.0.1','2026-03-30 15:59:03',NULL,NULL,NULL),(31,13,'login','User',13,'User user13 logged in','127.0.0.1','2026-03-30 16:02:44',NULL,NULL,NULL),(32,2,'login','User',2,'User Admin logged in','127.0.0.1','2026-03-30 16:03:06',NULL,NULL,NULL),(33,2,'logout','User',2,'User Admin logged out','127.0.0.1','2026-03-30 16:03:21',NULL,NULL,NULL),(34,9,'login','User',9,'User admin0 logged in','127.0.0.1','2026-03-30 16:15:38',NULL,NULL,NULL),(35,9,'login','User',9,'User admin0 logged in','127.0.0.1','2026-03-30 16:16:18',NULL,NULL,NULL),(36,1,'login','User',1,'User staff1 logged in','127.0.0.1','2026-03-30 16:16:58',NULL,NULL,NULL),(37,1,'logout','User',1,'User staff1 logged out','127.0.0.1','2026-03-30 16:17:11',NULL,NULL,NULL),(38,2,'login','User',2,'User Admin logged in','127.0.0.1','2026-03-30 16:17:29',NULL,NULL,NULL),(39,2,'update','User',9,'Admin updated user: admin0 (role: ROLE_USER → ROLE_ADMIN)','127.0.0.1','2026-03-30 16:18:19',NULL,NULL,NULL),(40,2,'logout','User',2,'User Admin logged out','127.0.0.1','2026-03-30 16:18:55',NULL,NULL,NULL),(41,9,'login','User',9,'User admin0 logged in','127.0.0.1','2026-03-30 19:07:06',NULL,NULL,NULL),(42,9,'DELETE','User',12,'User: User: llloll (ID: 12) (ID: 12)',NULL,'2026-03-30 20:08:06','admin0','ROLE_ADMIN',NULL),(43,9,'CREATE','Category',2,'Category: Category: Smoothie (ID: 2) (ID: 2)',NULL,'2026-03-30 20:10:03','admin0','ROLE_ADMIN',NULL),(44,9,'LOGOUT','User',9,'User: admin0 (ID: 9)',NULL,'2026-03-30 20:17:14','admin0','ROLE_ADMIN',NULL),(45,9,'LOGIN','User',9,'User: admin0 (ID: 9)',NULL,'2026-03-30 20:21:16','admin0','ROLE_ADMIN',NULL),(46,9,'LOGOUT','User',9,'User: admin0 (ID: 9)',NULL,'2026-03-30 20:22:28','admin0','ROLE_ADMIN',NULL),(48,9,'LOGIN','User',9,'User: admin0 (ID: 9)',NULL,'2026-04-04 21:38:20','admin0','ROLE_ADMIN',NULL),(49,9,'DELETE','User',17,'User: User: EstrabelaHyzlann123@outlook.com (ID: 17) (ID: 17)',NULL,'2026-04-04 21:39:22','admin0','ROLE_ADMIN',NULL),(50,9,'LOGOUT','User',9,'User: admin0 (ID: 9)',NULL,'2026-04-04 21:40:06','admin0','ROLE_ADMIN',NULL),(51,9,'DELETE','User',17,'User: User: EstrabelaHyzlann123@outlook.com (ID: 17) (ID: 17)',NULL,'2026-04-04 21:57:36','admin0','ROLE_ADMIN',NULL),(52,9,'LOGOUT','User',9,'User: admin0 (ID: 9)',NULL,'2026-04-04 22:04:21','admin0','ROLE_ADMIN',NULL),(53,9,'LOGOUT','User',9,'User: admin0 (ID: 9)',NULL,'2026-04-04 22:05:13','admin0','ROLE_ADMIN',NULL),(54,32,'LOGIN','User',32,'User: hyzl (ID: 32)',NULL,'2026-04-05 22:30:18','hyzl','ROLE_USER',NULL),(55,9,'UPDATE','User',1,'User: User: staff1 (ID: 1) (ID: 1)',NULL,'2026-04-06 15:44:59','admin0','ROLE_ADMIN',NULL),(56,9,'UPDATE','User',1,'User: User: staff1 (ID: 1) (ID: 1)',NULL,'2026-04-06 15:45:18','admin0','ROLE_ADMIN',NULL),(57,9,'CREATE','Stock',2,'Stock: Stock: Black Coffee (ID: 2) (ID: 2)',NULL,'2026-04-06 15:49:15','admin0','ROLE_ADMIN',NULL),(58,9,'LOGOUT','User',9,'User: admin0 (ID: 9)',NULL,'2026-04-06 16:16:12','admin0','ROLE_ADMIN',NULL),(59,9,'UPDATE','User',32,'User: User: hyzl (ID: 32) (ID: 32)',NULL,'2026-04-06 16:22:44','admin0','ROLE_ADMIN',NULL),(60,9,'LOGOUT','User',9,'User: admin0 (ID: 9)',NULL,'2026-04-06 16:28:21','admin0','ROLE_ADMIN',NULL),(61,32,'LOGOUT','User',32,'User: hyzl (ID: 32)',NULL,'2026-04-06 16:31:55','hyzl','ROLE_STAFF',NULL),(62,9,'CREATE','Order',1,'Order: Order #1 - Customer: Ann, Product: Black Coffee (ID: 1) (ID: 1)',NULL,'2026-04-06 16:50:28','admin0','ROLE_ADMIN',NULL),(63,9,'CREATE','Customer',2,'Customer: Customer: Hyzl (ID: 2) (ID: 2)',NULL,'2026-04-06 16:57:40','admin0','ROLE_ADMIN',NULL),(64,9,'UPDATE','User',9,'User: User: Hyzl-Ann (ID: 9) (ID: 9)',NULL,'2026-04-06 19:09:49','Hyzl-Ann','ROLE_ADMIN',NULL),(65,9,'LOGOUT','User',9,'User: Hyzl-Ann (ID: 9)',NULL,'2026-04-06 19:56:58','Hyzl-Ann','ROLE_ADMIN',NULL),(66,9,'LOGIN','User',9,'User: Hyzl-Ann (ID: 9)',NULL,'2026-04-06 20:07:22','Hyzl-Ann','ROLE_ADMIN',NULL),(67,32,'LOGIN','User',32,'User: hyzl (ID: 32)',NULL,'2026-04-06 20:08:21','hyzl','ROLE_STAFF',NULL),(68,32,'LOGOUT','User',32,'User: hyzl (ID: 32)',NULL,'2026-04-06 20:39:23','hyzl','ROLE_STAFF',NULL),(69,9,'UPDATE','Product',1,'Admin \"Hyzl-Ann\" updated Product \"Expresso (ID: 1)\" (ID: 1).','127.0.0.1','2026-04-07 08:23:10','Hyzl-Ann','ROLE_ADMIN',NULL),(70,9,'UPDATE','Product',1,'Admin \"Hyzl-Ann\" updated Product \"Expresso (ID: 1)\" (ID: 1).','127.0.0.1','2026-04-07 08:37:14','Hyzl-Ann','ROLE_ADMIN',NULL),(71,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-04-07 15:18:22','Hyzl-Ann','ROLE_ADMIN',NULL),(72,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-04-07 15:18:22','Hyzl-Ann','ROLE_ADMIN',NULL),(73,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-04-07 15:18:37','Hyzl-Ann','ROLE_ADMIN',NULL),(74,9,'UPDATE','Category',1,'Admin \"Hyzl-Ann\" updated Category \"Signature Eco Drinks (ID: 1)\" (ID: 1).','127.0.0.1','2026-04-07 15:21:11','Hyzl-Ann','ROLE_ADMIN',NULL),(75,9,'UPDATE','Category',1,'Admin \"Hyzl-Ann\" updated Category \"Signature Eco Drinks 🌿 (ID: 1)\" (ID: 1).','127.0.0.1','2026-04-07 15:22:46','Hyzl-Ann','ROLE_ADMIN',NULL),(76,9,'UPDATE','Category',1,'Admin \"Hyzl-Ann\" updated Category \"Signature Eco Drinks 🌿 (ID: 1)\" (ID: 1).','127.0.0.1','2026-04-07 15:22:49','Hyzl-Ann','ROLE_ADMIN',NULL),(77,9,'UPDATE','Category',2,'Admin \"Hyzl-Ann\" updated Category \"Cold Brews & Iced Coffee ❄️ (ID: 2)\" (ID: 2).','127.0.0.1','2026-04-07 15:23:23','Hyzl-Ann','ROLE_ADMIN',NULL),(78,9,'CREATE','Category',3,'Admin \"Hyzl-Ann\" created a new Category: \"Hot Beverages ☕ (ID: 3)\" (ID: 3).','127.0.0.1','2026-04-07 15:23:47','Hyzl-Ann','ROLE_ADMIN',NULL),(79,9,'CREATE','Category',4,'Admin \"Hyzl-Ann\" created a new Category: \"Fruit-Based Refreshers 🍓 (ID: 4)\" (ID: 4).','127.0.0.1','2026-04-07 15:24:13','Hyzl-Ann','ROLE_ADMIN',NULL),(80,9,'CREATE','Category',5,'Admin \"Hyzl-Ann\" created a new Category: \"Healthy Snacks 🥗 (ID: 5)\" (ID: 5).','127.0.0.1','2026-04-07 15:24:32','Hyzl-Ann','ROLE_ADMIN',NULL),(81,9,'CREATE','Category',6,'Admin \"Hyzl-Ann\" created a new Category: \"Plant-Based Specials 🌱 (ID: 6)\" (ID: 6).','127.0.0.1','2026-04-07 15:24:51','Hyzl-Ann','ROLE_ADMIN',NULL),(82,9,'UPDATE','Product',1,'Admin \"Hyzl-Ann\" updated Product \"Green Bliss Latte (ID: 1)\" (ID: 1).','127.0.0.1','2026-04-07 15:26:51','Hyzl-Ann','ROLE_ADMIN',NULL),(83,9,'CREATE','Product',2,'Admin \"Hyzl-Ann\" created a new Product: \"Eco Herbal Cooler (ID: 2)\" (ID: 2).','127.0.0.1','2026-04-07 15:28:34','Hyzl-Ann','ROLE_ADMIN',NULL),(84,9,'CREATE','Product',3,'Admin \"Hyzl-Ann\" created a new Product: \"Classic Cold Brew (ID: 3)\" (ID: 3).','127.0.0.1','2026-04-07 15:29:39','Hyzl-Ann','ROLE_ADMIN',NULL),(85,9,'CREATE','Product',4,'Admin \"Hyzl-Ann\" created a new Product: \"Vanilla Oat Iced Coffee (ID: 4)\" (ID: 4).','127.0.0.1','2026-04-07 15:30:37','Hyzl-Ann','ROLE_ADMIN',NULL),(86,9,'CREATE','Product',5,'Admin \"Hyzl-Ann\" created a new Product: \"Organic Brewed Coffee (ID: 5)\" (ID: 5).','127.0.0.1','2026-04-07 15:32:47','Hyzl-Ann','ROLE_ADMIN',NULL),(87,9,'CREATE','Product',6,'Admin \"Hyzl-Ann\" created a new Product: \"Chamomile Calm Tea (ID: 6)\" (ID: 6).','127.0.0.1','2026-04-07 15:33:22','Hyzl-Ann','ROLE_ADMIN',NULL),(88,9,'CREATE','Product',7,'Admin \"Hyzl-Ann\" created a new Product: \"Strawberry Fresh Splash (ID: 7)\" (ID: 7).','127.0.0.1','2026-04-07 15:35:24','Hyzl-Ann','ROLE_ADMIN',NULL),(89,9,'CREATE','Product',8,'Admin \"Hyzl-Ann\" created a new Product: \"Mango Citrus Cooler (ID: 8)\" (ID: 8).','127.0.0.1','2026-04-07 15:36:19','Hyzl-Ann','ROLE_ADMIN',NULL),(90,9,'CREATE','Product',9,'Admin \"Hyzl-Ann\" created a new Product: \"Banana Oat Muffin (ID: 9)\" (ID: 9).','127.0.0.1','2026-04-07 15:37:18','Hyzl-Ann','ROLE_ADMIN',NULL),(91,9,'CREATE','Product',10,'Admin \"Hyzl-Ann\" created a new Product: \"Veggie Wrap (ID: 10)\" (ID: 10).','127.0.0.1','2026-04-07 15:39:30','Hyzl-Ann','ROLE_ADMIN',NULL),(92,9,'CREATE','Product',11,'Admin \"Hyzl-Ann\" created a new Product: \"Veggie Wrap (ID: 11)\" (ID: 11).','127.0.0.1','2026-04-07 15:40:18','Hyzl-Ann','ROLE_ADMIN',NULL),(93,9,'CREATE','Product',12,'Admin \"Hyzl-Ann\" created a new Product: \"Vegan Chocolate Shake (ID: 12)\" (ID: 12).','127.0.0.1','2026-04-07 15:40:54','Hyzl-Ann','ROLE_ADMIN',NULL),(94,9,'CREATE','Product',13,'Admin \"Hyzl-Ann\" created a new Product: \"Plant Protein Smoothie (ID: 13)\" (ID: 13).','127.0.0.1','2026-04-07 15:41:41','Hyzl-Ann','ROLE_ADMIN',NULL),(95,9,'UPDATE','Stock',1,'Admin \"Hyzl-Ann\" updated Stock \"Green Bliss Latte (ID: 1)\" (ID: 1).','127.0.0.1','2026-04-07 15:42:41','Hyzl-Ann','ROLE_ADMIN',NULL),(96,9,'UPDATE','Stock',2,'Admin \"Hyzl-Ann\" updated Stock \"Eco Herbal Cooler (ID: 2)\" (ID: 2).','127.0.0.1','2026-04-07 15:42:58','Hyzl-Ann','ROLE_ADMIN',NULL),(97,9,'CREATE','Stock',3,'Admin \"Hyzl-Ann\" created a new Stock: \"Classic Cold Brew (ID: 3)\" (ID: 3).','127.0.0.1','2026-04-07 15:43:19','Hyzl-Ann','ROLE_ADMIN',NULL),(98,9,'CREATE','Stock',4,'Admin \"Hyzl-Ann\" created a new Stock: \"Vanilla Oat Iced Coffee (ID: 4)\" (ID: 4).','127.0.0.1','2026-04-07 15:43:39','Hyzl-Ann','ROLE_ADMIN',NULL),(99,9,'LOGIN','User',9,'Admin \"Hyzl-Ann\" logged in successfully.','127.0.0.1','2026-04-07 17:44:59','Hyzl-Ann','ROLE_ADMIN',NULL),(100,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-04-07 17:44:59','Hyzl-Ann','ROLE_ADMIN',NULL),(101,9,'CREATE','User',35,'Admin \"Hyzl-Ann\" created a new User: \"Desree (ID: 35)\" (ID: 35).','127.0.0.1','2026-04-07 17:46:47','Hyzl-Ann','ROLE_ADMIN',NULL),(102,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-04-07 17:48:59','Hyzl-Ann','ROLE_ADMIN',NULL),(103,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-04-07 17:48:59','Hyzl-Ann','ROLE_ADMIN',NULL),(104,9,'LOGIN','User',9,'Admin \"Hyzl-Ann\" logged in successfully.','127.0.0.1','2026-04-07 18:01:35','Hyzl-Ann','ROLE_ADMIN',NULL),(105,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-04-07 18:01:35','Hyzl-Ann','ROLE_ADMIN',NULL),(106,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-04-07 18:02:46','Hyzl-Ann','ROLE_ADMIN',NULL),(107,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-04-07 18:02:46','Hyzl-Ann','ROLE_ADMIN',NULL),(108,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-04-07 18:03:21','Hyzl-Ann','ROLE_ADMIN',NULL),(109,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-04-07 18:03:44','Hyzl-Ann','ROLE_ADMIN',NULL),(110,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-04-07 18:03:44','Hyzl-Ann','ROLE_ADMIN',NULL),(111,32,'login','User',32,'hyzl logged in successfully.','127.0.0.1','2026-04-07 18:03:59','hyzl','ROLE_STAFF',NULL),(112,32,'logout','User',32,'hyzl logged out.','127.0.0.1','2026-04-07 18:04:36','hyzl','ROLE_STAFF',NULL),(113,32,'LOGOUT','User',32,'Staff \"hyzl\" logged out.','127.0.0.1','2026-04-07 18:04:36','hyzl','ROLE_STAFF',NULL),(114,9,'LOGIN','User',9,'Admin \"Hyzl-Ann\" logged in successfully.','127.0.0.1','2026-04-07 18:21:43','Hyzl-Ann','ROLE_ADMIN',NULL),(115,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-04-07 18:21:43','Hyzl-Ann','ROLE_ADMIN',NULL),(116,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-04-07 18:24:39','Hyzl-Ann','ROLE_ADMIN',NULL),(117,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-04-07 18:24:39','Hyzl-Ann','ROLE_ADMIN',NULL),(118,36,'login','User',36,'llloll123pll logged in successfully.','127.0.0.1','2026-04-19 21:18:29','llloll123pll','ROLE_USER',NULL),(119,36,'login','User',36,'llloll123pll logged in successfully.','127.0.0.1','2026-04-19 21:19:14','llloll123pll','ROLE_USER',NULL),(120,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-04-19 21:22:49','Hyzl-Ann','ROLE_ADMIN',NULL),(121,9,'CREATE','Stock',5,'Admin \"Hyzl-Ann\" created a new Stock: \"Vanilla Oat Iced Coffee (ID: 5)\" (ID: 5).','127.0.0.1','2026-04-19 21:29:41','Hyzl-Ann','ROLE_ADMIN',NULL),(122,9,'DELETE','Stock',5,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Vanilla Oat Iced Coffee (ID: 5)\" (ID: 5).','127.0.0.1','2026-04-19 21:36:10','Hyzl-Ann','ROLE_ADMIN',NULL),(123,9,'CREATE','Stock',4,'Admin \"Hyzl-Ann\" created a new Stock: \"Stock restocked: Vanilla Oat Iced Coffee +5 units\" (ID: 4).','127.0.0.1','2026-04-19 21:37:09','Hyzl-Ann','ROLE_ADMIN',NULL),(124,9,'DELETE','Stock',4,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Vanilla Oat Iced Coffee (ID: 4)\" (ID: 4).','127.0.0.1','2026-04-19 21:37:53','Hyzl-Ann','ROLE_ADMIN',NULL),(125,9,'DELETE','Stock',6,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Vanilla Oat Iced Coffee (ID: 6)\" (ID: 6).','127.0.0.1','2026-04-19 21:38:04','Hyzl-Ann','ROLE_ADMIN',NULL),(126,9,'CREATE','Stock',3,'Admin \"Hyzl-Ann\" created a new Stock: \"Stock restocked: Classic Cold Brew +10 units\" (ID: 3).','127.0.0.1','2026-04-19 21:38:32','Hyzl-Ann','ROLE_ADMIN',NULL),(127,9,'DELETE','Stock',7,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Classic Cold Brew (ID: 7)\" (ID: 7).','127.0.0.1','2026-04-19 21:38:51','Hyzl-Ann','ROLE_ADMIN',NULL),(128,9,'DELETE','Stock',3,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Classic Cold Brew (ID: 3)\" (ID: 3).','127.0.0.1','2026-04-19 21:39:02','Hyzl-Ann','ROLE_ADMIN',NULL),(129,9,'DELETE','Stock',2,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Eco Herbal Cooler (ID: 2)\" (ID: 2).','127.0.0.1','2026-04-19 21:39:09','Hyzl-Ann','ROLE_ADMIN',NULL),(130,9,'DELETE','Stock',1,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Green Bliss Latte (ID: 1)\" (ID: 1).','127.0.0.1','2026-04-19 21:39:11','Hyzl-Ann','ROLE_ADMIN',NULL),(131,9,'CREATE','Stock',8,'Admin \"Hyzl-Ann\" created a new Stock: \"Green Bliss Latte (ID: 8)\" (ID: 8).','127.0.0.1','2026-04-19 21:39:36','Hyzl-Ann','ROLE_ADMIN',NULL),(132,9,'CREATE','Stock',8,'Admin \"Hyzl-Ann\" created a new Stock: \"Stock restocked: Green Bliss Latte +6 units\" (ID: 8).','127.0.0.1','2026-04-19 21:39:57','Hyzl-Ann','ROLE_ADMIN',NULL),(133,9,'DELETE','Stock',9,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Green Bliss Latte (ID: 9)\" (ID: 9).','127.0.0.1','2026-04-19 21:44:13','Hyzl-Ann','ROLE_ADMIN',NULL),(134,9,'DELETE','Stock',8,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Green Bliss Latte (ID: 8)\" (ID: 8).','127.0.0.1','2026-04-19 21:44:15','Hyzl-Ann','ROLE_ADMIN',NULL),(135,9,'CREATE','Stock',10,'Admin \"Hyzl-Ann\" created a new Stock: \"Eco Herbal Cooler (ID: 10)\" (ID: 10).','127.0.0.1','2026-04-19 21:44:37','Hyzl-Ann','ROLE_ADMIN',NULL),(136,9,'CREATE','Stock',10,'Admin \"Hyzl-Ann\" created a new Stock: \"Stock restocked: Eco Herbal Cooler +4 units\" (ID: 10).','127.0.0.1','2026-04-19 21:44:58','Hyzl-Ann','ROLE_ADMIN',NULL),(137,9,'CREATE','Stock',10,'Admin \"Hyzl-Ann\" created a new Stock: \"Stock restocked: Eco Herbal Cooler +6 units\" (ID: 10).','127.0.0.1','2026-04-19 21:45:22','Hyzl-Ann','ROLE_ADMIN',NULL),(138,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-04-19 21:46:50','Hyzl-Ann','ROLE_ADMIN',NULL),(139,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-04-19 21:46:50','Hyzl-Ann','ROLE_ADMIN',NULL),(140,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-04-22 12:50:57','Hyzl-Ann','ROLE_ADMIN',NULL),(141,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-04-22 13:24:08','Hyzl-Ann','ROLE_ADMIN',NULL),(142,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-04-22 13:24:59','Hyzl-Ann','ROLE_ADMIN',NULL),(143,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-04-22 13:24:59','Hyzl-Ann','ROLE_ADMIN',NULL),(144,32,'login','User',32,'hyzl logged in successfully.','127.0.0.1','2026-05-02 13:47:06','hyzl','ROLE_STAFF',NULL),(145,32,'CREATE','Stock',10,'Staff \"hyzl\" created a new Stock: \"Stock restocked: Eco Herbal Cooler +6 units\" (ID: 10).','127.0.0.1','2026-05-02 13:51:44','hyzl','ROLE_STAFF',NULL),(146,32,'CREATE','Stock',15,'Staff \"hyzl\" created a new Stock: \"Vegan Chocolate Shake (ID: 15)\" (ID: 15).','127.0.0.1','2026-05-02 13:52:41','hyzl','ROLE_STAFF',NULL),(147,32,'CREATE','Stock',10,'Staff \"hyzl\" created a new Stock: \"Stock restocked: Eco Herbal Cooler +1 units\" (ID: 10).','127.0.0.1','2026-05-02 13:53:54','hyzl','ROLE_STAFF',NULL),(148,32,'CREATE','Stock',18,'Staff \"hyzl\" created a new Stock: \"Banana Oat Muffin (ID: 18)\" (ID: 18).','127.0.0.1','2026-05-02 13:55:26','hyzl','ROLE_STAFF',NULL),(149,32,'CREATE','Stock',20,'Staff \"hyzl\" created a new Stock: \"Strawberry Fresh Splash (ID: 20)\" (ID: 20).','127.0.0.1','2026-05-02 13:56:26','hyzl','ROLE_STAFF',NULL),(150,9,'LOGIN','User',9,'Admin \"Hyzl-Ann\" logged in successfully.','127.0.0.1','2026-05-15 11:01:42','Hyzl-Ann','ROLE_ADMIN',NULL),(151,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-15 11:01:42','Hyzl-Ann','ROLE_ADMIN',NULL),(152,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-15 11:03:48','Hyzl-Ann','ROLE_ADMIN',NULL),(153,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-15 11:04:02','Hyzl-Ann','ROLE_ADMIN',NULL),(154,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-15 11:04:16','Hyzl-Ann','ROLE_ADMIN',NULL),(155,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-15 11:04:55','Hyzl-Ann','ROLE_ADMIN',NULL),(156,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-15 11:05:07','Hyzl-Ann','ROLE_ADMIN',NULL),(157,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-15 11:05:17','Hyzl-Ann','ROLE_ADMIN',NULL),(158,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-15 11:13:46','Hyzl-Ann','ROLE_ADMIN',NULL),(159,32,'login','User',32,'hyzl logged in successfully.','127.0.0.1','2026-05-17 21:03:06','hyzl','ROLE_STAFF',NULL),(160,32,'UPDATE','Product',9,'Staff \"hyzl\" updated Product \"Banana Oat Muffin (ID: 9)\" (ID: 9).','127.0.0.1','2026-05-17 21:29:27','hyzl','ROLE_STAFF',NULL),(161,32,'UPDATE','Product',9,'Staff \"hyzl\" updated Product \"Banana Oat Muffin (ID: 9)\" (ID: 9).','127.0.0.1','2026-05-17 21:43:06','hyzl','ROLE_STAFF',NULL),(162,32,'UPDATE','Product',8,'Staff \"hyzl\" updated Product \"Mango Citrus Cooler (ID: 8)\" (ID: 8).','127.0.0.1','2026-05-17 21:44:00','hyzl','ROLE_STAFF',NULL),(163,32,'UPDATE','Product',6,'Staff \"hyzl\" updated Product \"Chamomile Calm Tea (ID: 6)\" (ID: 6).','127.0.0.1','2026-05-17 21:47:12','hyzl','ROLE_STAFF',NULL),(164,32,'UPDATE','Product',3,'Staff \"hyzl\" updated Product \"Classic Cold Brew (ID: 3)\" (ID: 3).','127.0.0.1','2026-05-17 21:48:23','hyzl','ROLE_STAFF',NULL),(165,32,'UPDATE','Product',4,'Staff \"hyzl\" updated Product \"Vanilla Oat Iced Coffee (ID: 4)\" (ID: 4).','127.0.0.1','2026-05-17 21:49:29','hyzl','ROLE_STAFF',NULL),(166,32,'UPDATE','Product',5,'Staff \"hyzl\" updated Product \"Organic Brewed Coffee (ID: 5)\" (ID: 5).','127.0.0.1','2026-05-17 21:50:31','hyzl','ROLE_STAFF',NULL),(167,32,'UPDATE','Product',13,'Staff \"hyzl\" updated Product \"Plant Protein Smoothie (ID: 13)\" (ID: 13).','127.0.0.1','2026-05-17 21:51:21','hyzl','ROLE_STAFF',NULL),(168,32,'UPDATE','Product',12,'Staff \"hyzl\" updated Product \"Vegan Chocolate Shake (ID: 12)\" (ID: 12).','127.0.0.1','2026-05-17 21:52:17','hyzl','ROLE_STAFF',NULL),(169,32,'UPDATE','Product',12,'Staff \"hyzl\" updated Product \"Vegan Chocolate Shake (ID: 12)\" (ID: 12).','127.0.0.1','2026-05-17 21:52:53','hyzl','ROLE_STAFF',NULL),(170,32,'UPDATE','Product',2,'Staff \"hyzl\" updated Product \"Eco Herbal Cooler (ID: 2)\" (ID: 2).','127.0.0.1','2026-05-17 21:54:43','hyzl','ROLE_STAFF',NULL),(171,32,'UPDATE','Product',7,'Staff \"hyzl\" updated Product \"Strawberry Fresh Splash (ID: 7)\" (ID: 7).','127.0.0.1','2026-05-17 21:55:31','hyzl','ROLE_STAFF',NULL),(172,32,'UPDATE','Product',11,'Staff \"hyzl\" updated Product \"Veggie Wrap (ID: 11)\" (ID: 11).','127.0.0.1','2026-05-17 21:56:25','hyzl','ROLE_STAFF',NULL),(173,32,'UPDATE','Product',1,'Staff \"hyzl\" updated Product \"Green Bliss Latte (ID: 1)\" (ID: 1).','127.0.0.1','2026-05-17 21:57:45','hyzl','ROLE_STAFF',NULL),(174,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-18 06:05:49','Hyzl-Ann','ROLE_ADMIN',NULL),(175,9,'CREATE','Stock',22,'Admin \"Hyzl-Ann\" created a new Stock: \"Green Bliss Latte (ID: 22)\" (ID: 22).','127.0.0.1','2026-05-18 06:06:33','Hyzl-Ann','ROLE_ADMIN',NULL),(176,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 06:36:46','hyzl','ROLE_STAFF',NULL),(177,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 06:43:27','hyzl','ROLE_STAFF',NULL),(178,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 06:53:48','hyzl','ROLE_STAFF',NULL),(179,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 06:56:50','hyzl','ROLE_STAFF',NULL),(180,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 06:56:51','hyzl','ROLE_STAFF',NULL),(181,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 06:58:10','hyzl','ROLE_STAFF',NULL),(182,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 06:58:12','hyzl','ROLE_STAFF',NULL),(183,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:01:47','hyzl','ROLE_STAFF',NULL),(184,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:01:50','hyzl','ROLE_STAFF',NULL),(185,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:07:51','hyzl','ROLE_STAFF',NULL),(186,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:07:53','hyzl','ROLE_STAFF',NULL),(187,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:07:55','hyzl','ROLE_STAFF',NULL),(188,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:07:57','hyzl','ROLE_STAFF',NULL),(189,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:11:47','hyzl','ROLE_STAFF',NULL),(190,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:11:49','hyzl','ROLE_STAFF',NULL),(191,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:16:07','hyzl','ROLE_STAFF',NULL),(192,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:16:09','hyzl','ROLE_STAFF',NULL),(193,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:17:57','hyzl','ROLE_STAFF',NULL),(194,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:18:00','hyzl','ROLE_STAFF',NULL),(195,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:18:12','hyzl','ROLE_STAFF',NULL),(196,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:18:14','hyzl','ROLE_STAFF',NULL),(197,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:28:05','hyzl','ROLE_STAFF',NULL),(198,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:28:10','hyzl','ROLE_STAFF',NULL),(199,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:28:33','hyzl','ROLE_STAFF',NULL),(200,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:28:54','hyzl','ROLE_STAFF',NULL),(201,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:29:06','hyzl','ROLE_STAFF',NULL),(202,9,'UPDATE','Order',3,'Admin \"Hyzl-Ann\" updated Order \"Order #3 - Customer: ESTRABELA HYZL-ANN (ID: 3)\" (ID: 3).','127.0.0.1','2026-05-18 07:34:51','Hyzl-Ann','ROLE_ADMIN',NULL),(203,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:35:05','hyzl','ROLE_STAFF',NULL),(204,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 07:58:22','hyzl','ROLE_STAFF',NULL),(205,9,'CREATE','Stock',24,'Admin \"Hyzl-Ann\" created a new Stock: \"Classic Cold Brew (ID: 24)\" (ID: 24).','127.0.0.1','2026-05-18 08:02:07','Hyzl-Ann','ROLE_ADMIN',NULL),(206,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 10:30:04','hyzl','ROLE_STAFF',NULL),(207,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 10:30:56','hyzl','ROLE_STAFF',NULL),(208,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 10:38:08','hyzl','ROLE_STAFF',NULL),(209,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 10:38:19','hyzl','ROLE_STAFF',NULL),(210,9,'UPDATE','Order',5,'Admin \"Hyzl-Ann\" updated Order \"Order #5 - Customer: ESTRABELA HYZL-ANN (ID: 5)\" (ID: 5).','127.0.0.1','2026-05-18 10:39:18','Hyzl-Ann','ROLE_ADMIN',NULL),(211,9,'UPDATE','Order',4,'Admin \"Hyzl-Ann\" updated Order \"Order #4 - Customer: ESTRABELA HYZL-ANN (ID: 4)\" (ID: 4).','127.0.0.1','2026-05-18 10:48:23','Hyzl-Ann','ROLE_ADMIN',NULL),(212,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-05-18 10:53:01','Hyzl-Ann','ROLE_ADMIN',NULL),(213,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-05-18 10:53:01','Hyzl-Ann','ROLE_ADMIN',NULL),(214,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-18 10:59:50','Hyzl-Ann','ROLE_ADMIN',NULL),(215,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-05-18 11:04:30','Hyzl-Ann','ROLE_ADMIN',NULL),(216,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-05-18 11:04:30','Hyzl-Ann','ROLE_ADMIN',NULL),(217,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-18 11:04:50','Hyzl-Ann','ROLE_ADMIN',NULL),(218,9,'UPDATE','User',32,'Admin \"Hyzl-Ann\" updated User \"hyzl\" (ID: 32). Previous role was \"ROLE_STAFF\".','127.0.0.1','2026-05-18 11:09:40','Hyzl-Ann','ROLE_ADMIN',NULL),(219,9,'DELETE','Customer',2,'Admin \"Hyzl-Ann\" permanently deleted Customer: \"Hyzl (ID: 2)\" (ID: 2).','127.0.0.1','2026-05-18 11:10:41','Hyzl-Ann','ROLE_ADMIN',NULL),(220,9,'UPDATE','Order',2,'Admin \"Hyzl-Ann\" updated Order \"Order #2 - Customer: Juan dela Cruz (ID: 2)\" (ID: 2).','127.0.0.1','2026-05-18 11:14:37','Hyzl-Ann','ROLE_ADMIN',NULL),(221,9,'DELETE','Order',1,'Admin \"Hyzl-Ann\" permanently deleted Order: \"Order #1 - Customer: Ann, Product: Green Bliss Latte (ID: 1)\" (ID: 1).','127.0.0.1','2026-05-18 11:14:56','Hyzl-Ann','ROLE_ADMIN',NULL),(222,9,'DELETE','Order',2,'Admin \"Hyzl-Ann\" permanently deleted Order: \"Order #2 - Customer: Juan dela Cruz, Product: Eco Herbal Cooler (ID: 2)\" (ID: 2).','127.0.0.1','2026-05-18 11:15:07','Hyzl-Ann','ROLE_ADMIN',NULL),(223,9,'DELETE','Customer',3,'Admin \"Hyzl-Ann\" permanently deleted Customer: \"Juan dela Cruz (ID: 3)\" (ID: 3).','127.0.0.1','2026-05-18 11:15:33','Hyzl-Ann','ROLE_ADMIN',NULL),(224,9,'DELETE','Customer',1,'Admin \"Hyzl-Ann\" permanently deleted Customer: \"Ann (ID: 1)\" (ID: 1).','127.0.0.1','2026-05-18 11:15:44','Hyzl-Ann','ROLE_ADMIN',NULL),(225,9,'UPDATE','User',32,'Admin \"Hyzl-Ann\" updated User \"hyzl\" (ID: 32). Previous role was \"ROLE_USER\".','127.0.0.1','2026-05-18 11:52:28','Hyzl-Ann','ROLE_ADMIN',NULL),(226,9,'UPDATE','User',32,'Admin \"Hyzl-Ann\" updated User \"hyzl\" (ID: 32). Previous role was \"ROLE_USER\".','127.0.0.1','2026-05-18 11:59:51','Hyzl-Ann','ROLE_ADMIN',NULL),(227,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 12:37:06','hyzl','ROLE_USER',NULL),(228,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 12:37:09','hyzl','ROLE_USER',NULL),(229,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-18 12:37:28','hyzl','ROLE_USER',NULL),(230,9,'UPDATE','User',39,'Admin \"Hyzl-Ann\" updated User \"hyzl5678\" (ID: 39). Previous role was \"ROLE_USER\".','127.0.0.1','2026-05-18 12:39:06','Hyzl-Ann','ROLE_ADMIN',NULL),(231,39,'login','User',39,'hyzl5678 logged in successfully.','192.168.101.27','2026-05-18 12:40:04','hyzl5678','ROLE_STAFF',NULL),(232,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 20:43:24','hyzl','ROLE_USER',NULL),(233,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-19 20:47:59','Hyzl-Ann','ROLE_ADMIN',NULL),(234,9,'CREATE','Stock',26,'Admin \"Hyzl-Ann\" created a new Stock: \"Vanilla Oat Iced Coffee (ID: 26)\" (ID: 26).','127.0.0.1','2026-05-19 20:49:30','Hyzl-Ann','ROLE_ADMIN',NULL),(235,9,'CREATE','Stock',26,'Admin \"Hyzl-Ann\" created a new Stock: \"Stock restocked: Vanilla Oat Iced Coffee +11 units\" (ID: 26).','127.0.0.1','2026-05-19 20:49:34','Hyzl-Ann','ROLE_ADMIN',NULL),(236,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 20:51:19','hyzl','ROLE_USER',NULL),(237,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 20:54:30','hyzl','ROLE_USER',NULL),(238,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 20:54:41','hyzl','ROLE_USER',NULL),(239,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 21:38:54','hyzl','ROLE_USER',NULL),(240,9,'UPDATE','Order',7,'Admin \"Hyzl-Ann\" changed status of Order \"Order #7 status changed: Pending → Processing\" (ID: 7) from \"Pending\" to \"Processing\".','127.0.0.1','2026-05-19 21:39:07','Hyzl-Ann','ROLE_ADMIN',NULL),(241,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 21:39:59','hyzl','ROLE_USER',NULL),(242,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 21:45:22','hyzl','ROLE_USER',NULL),(243,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 21:46:01','hyzl','ROLE_USER',NULL),(244,9,'UPDATE','Order',7,'Admin \"Hyzl-Ann\" changed status of Order \"Order #7 status changed: Processing → Cancelled\" (ID: 7) from \"Processing\" to \"Cancelled\".','127.0.0.1','2026-05-19 21:46:45','Hyzl-Ann','ROLE_ADMIN',NULL),(245,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 21:47:53','hyzl','ROLE_USER',NULL),(246,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 21:53:00','hyzl','ROLE_USER',NULL),(247,9,'UPDATE','Order',9,'Admin \"Hyzl-Ann\" changed status of Order \"Order #9 status changed: Pending → Completed\" (ID: 9) from \"Pending\" to \"Completed\".','127.0.0.1','2026-05-19 21:53:58','Hyzl-Ann','ROLE_ADMIN',NULL),(248,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-19 21:54:07','hyzl','ROLE_USER',NULL),(249,9,'UPDATE','User',3,'Admin \"Hyzl-Ann\" updated User \"admin2\" (ID: 3). Previous role was \"ROLE_USER\".','127.0.0.1','2026-05-19 21:57:46','Hyzl-Ann','ROLE_ADMIN',NULL),(250,9,'UPDATE','User',13,'Admin \"Hyzl-Ann\" updated User \"user13\" (ID: 13). Previous role was \"ROLE_USER\".','127.0.0.1','2026-05-19 21:58:00','Hyzl-Ann','ROLE_ADMIN',NULL),(251,9,'DELETE','User',16,'Admin \"Hyzl-Ann\" permanently deleted User: \"dfgdfgfg (ID: 16)\" (ID: 16).','127.0.0.1','2026-05-19 21:58:13','Hyzl-Ann','ROLE_ADMIN',NULL),(252,9,'DELETE','User',33,'Admin \"Hyzl-Ann\" permanently deleted User: \"fghfg (ID: 33)\" (ID: 33).','127.0.0.1','2026-05-19 21:58:23','Hyzl-Ann','ROLE_ADMIN',NULL),(253,9,'DELETE','User',18,'Admin \"Hyzl-Ann\" permanently deleted User: \"user17 (ID: 18)\" (ID: 18).','127.0.0.1','2026-05-19 21:58:31','Hyzl-Ann','ROLE_ADMIN',NULL),(254,9,'UPDATE','User',14,'Admin \"Hyzl-Ann\" updated User \"user14\" (ID: 14). Previous role was \"ROLE_USER\".','127.0.0.1','2026-05-19 21:58:44','Hyzl-Ann','ROLE_ADMIN',NULL),(255,9,'UPDATE','User',15,'Admin \"Hyzl-Ann\" updated User \"user15\" (ID: 15). Previous role was \"ROLE_USER\".','127.0.0.1','2026-05-19 21:58:55','Hyzl-Ann','ROLE_ADMIN',NULL),(256,9,'DELETE','User',34,'Admin \"Hyzl-Ann\" permanently deleted User: \"seriusly (ID: 34)\" (ID: 34).','127.0.0.1','2026-05-19 21:59:04','Hyzl-Ann','ROLE_ADMIN',NULL),(257,9,'UPDATE','User',36,'Admin \"Hyzl-Ann\" updated User \"llloll123pll\" (ID: 36). Previous role was \"ROLE_USER\".','127.0.0.1','2026-05-19 21:59:21','Hyzl-Ann','ROLE_ADMIN',NULL),(258,9,'UPDATE','User',40,'Admin \"Hyzl-Ann\" updated User \"anniestrabela\" (ID: 40). Previous role was \"ROLE_USER\".','127.0.0.1','2026-05-19 21:59:34','Hyzl-Ann','ROLE_ADMIN',NULL),(259,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-05-19 22:00:39','Hyzl-Ann','ROLE_ADMIN',NULL),(260,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-05-19 22:00:39','Hyzl-Ann','ROLE_ADMIN',NULL),(261,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-19 23:23:43','Hyzl-Ann','ROLE_ADMIN',NULL),(262,9,'CREATE','User',49,'Admin \"Hyzl-Ann\" created a new User: \"Maine (ID: 49)\" (ID: 49).','127.0.0.1','2026-05-19 23:25:31','Hyzl-Ann','ROLE_ADMIN',NULL),(263,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-05-19 23:26:15','Hyzl-Ann','ROLE_ADMIN',NULL),(264,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-05-19 23:26:15','Hyzl-Ann','ROLE_ADMIN',NULL),(265,9,'LOGIN','User',9,'Admin \"Hyzl-Ann\" logged in successfully.','127.0.0.1','2026-05-20 06:50:09','Hyzl-Ann','ROLE_ADMIN',NULL),(266,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-20 06:50:09','Hyzl-Ann','ROLE_ADMIN',NULL),(267,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-05-20 06:50:44','Hyzl-Ann','ROLE_ADMIN',NULL),(268,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-05-20 06:50:44','Hyzl-Ann','ROLE_ADMIN',NULL),(269,9,'LOGIN','User',9,'Admin \"Hyzl-Ann\" logged in successfully.','127.0.0.1','2026-05-20 07:08:52','Hyzl-Ann','ROLE_ADMIN',NULL),(270,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-20 07:08:52','Hyzl-Ann','ROLE_ADMIN',NULL),(271,9,'logout','User',9,'Hyzl-Ann logged out.','127.0.0.1','2026-05-20 07:09:29','Hyzl-Ann','ROLE_ADMIN',NULL),(272,9,'LOGOUT','User',9,'Admin \"Hyzl-Ann\" logged out.','127.0.0.1','2026-05-20 07:09:29','Hyzl-Ann','ROLE_ADMIN',NULL),(273,39,'login','User',39,'hyzl5678 logged in successfully.','127.0.0.1','2026-05-20 07:10:58','hyzl5678','ROLE_STAFF',NULL),(274,39,'logout','User',39,'hyzl5678 logged out.','127.0.0.1','2026-05-20 07:11:22','hyzl5678','ROLE_STAFF',NULL),(275,39,'LOGOUT','User',39,'Staff \"hyzl5678\" logged out.','127.0.0.1','2026-05-20 07:11:22','hyzl5678','ROLE_STAFF',NULL),(276,32,'login','User',32,'hyzl logged in successfully.','127.0.0.1','2026-05-20 07:11:37','hyzl','ROLE_USER',NULL),(277,36,'login','User',36,'llloll123pll logged in successfully.','127.0.0.1','2026-05-20 07:13:18','llloll123pll','ROLE_USER',NULL),(278,9,'LOGIN','User',9,'Admin \"Hyzl-Ann\" logged in successfully.','127.0.0.1','2026-05-20 07:16:18','Hyzl-Ann','ROLE_ADMIN',NULL),(279,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-20 07:16:18','Hyzl-Ann','ROLE_ADMIN',NULL),(280,9,'DELETE','Stock',22,'Admin \"Hyzl-Ann\" permanently deleted Stock: \"Green Bliss Latte (ID: 22)\" (ID: 22).','127.0.0.1','2026-05-20 07:40:58','Hyzl-Ann','ROLE_ADMIN',NULL),(281,9,'CREATE','Stock',31,'Admin \"Hyzl-Ann\" created a new Stock: \"Green Bliss Latte (ID: 31)\" (ID: 31).','127.0.0.1','2026-05-20 07:42:31','Hyzl-Ann','ROLE_ADMIN',NULL),(282,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-20 07:43:49','hyzl','ROLE_USER',NULL),(283,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-20 07:53:43','hyzl','ROLE_USER',NULL),(284,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-20 07:54:20','hyzl','ROLE_USER',NULL),(285,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:12:31','hyzl','ROLE_USER',NULL),(286,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:12:53','hyzl','ROLE_USER',NULL),(287,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:13:50','hyzl','ROLE_USER',NULL),(288,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:14:17','hyzl','ROLE_USER',NULL),(289,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:34:52','hyzl','ROLE_USER',NULL),(290,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:35:01','hyzl','ROLE_USER',NULL),(291,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:35:29','hyzl','ROLE_USER',NULL),(292,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:43:26','hyzl','ROLE_USER',NULL),(293,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:43:46','hyzl','ROLE_USER',NULL),(294,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:49:13','hyzl','ROLE_USER',NULL),(295,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:49:27','hyzl','ROLE_USER',NULL),(296,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:58:44','hyzl','ROLE_USER',NULL),(297,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 14:59:19','hyzl','ROLE_USER',NULL),(298,9,'LOGIN','User',9,'Admin \"Hyzl-Ann\" logged in successfully.','127.0.0.1','2026-05-21 15:00:19','Hyzl-Ann','ROLE_ADMIN',NULL),(299,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-21 15:00:19','Hyzl-Ann','ROLE_ADMIN',NULL),(300,9,'UPDATE','Order',18,'Admin \"Hyzl-Ann\" changed status of Order \"Order #18 status changed: Completed → Completed\" (ID: 18) from \"Completed\" to \"Completed\".','127.0.0.1','2026-05-21 15:09:37','Hyzl-Ann','ROLE_ADMIN',NULL),(301,9,'UPDATE','Order',17,'Admin \"Hyzl-Ann\" changed status of Order \"Order #17 status changed: Pending → Cancelled\" (ID: 17) from \"Pending\" to \"Cancelled\".','127.0.0.1','2026-05-21 15:10:18','Hyzl-Ann','ROLE_ADMIN',NULL),(302,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 15:10:29','hyzl','ROLE_USER',NULL),(303,9,'UPDATE','Order',16,'Admin \"Hyzl-Ann\" changed status of Order \"Order #16 status changed: Pending → Processing\" (ID: 16) from \"Pending\" to \"Processing\".','127.0.0.1','2026-05-21 15:10:42','Hyzl-Ann','ROLE_ADMIN',NULL),(304,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 15:11:15','hyzl','ROLE_USER',NULL),(305,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 15:14:40','hyzl','ROLE_USER',NULL),(306,9,'UPDATE','Order',15,'Admin \"Hyzl-Ann\" changed status of Order \"Order #15 status changed: Pending → Cancelled\" (ID: 15) from \"Pending\" to \"Cancelled\".','127.0.0.1','2026-05-21 15:15:01','Hyzl-Ann','ROLE_ADMIN',NULL),(307,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 15:15:06','hyzl','ROLE_USER',NULL),(308,9,'UPDATE','Order',16,'Admin \"Hyzl-Ann\" changed status of Order \"Order #16 status changed: Processing → Completed\" (ID: 16) from \"Processing\" to \"Completed\".','127.0.0.1','2026-05-21 15:15:30','Hyzl-Ann','ROLE_ADMIN',NULL),(309,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 15:15:32','hyzl','ROLE_USER',NULL),(310,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 15:15:48','hyzl','ROLE_USER',NULL),(311,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 15:16:43','hyzl','ROLE_USER',NULL),(312,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 15:17:07','hyzl','ROLE_USER',NULL),(313,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 16:30:21','hyzl','ROLE_USER',NULL),(314,32,'login','User',32,'hyzl logged in successfully.','192.168.101.27','2026-05-21 16:30:36','hyzl','ROLE_USER',NULL),(315,9,'LOGIN','User',9,'Admin \"Hyzl-Ann\" logged in successfully.','127.0.0.1','2026-05-21 16:31:39','Hyzl-Ann','ROLE_ADMIN',NULL),(316,9,'login','User',9,'Hyzl-Ann logged in successfully.','127.0.0.1','2026-05-21 16:31:39','Hyzl-Ann','ROLE_ADMIN',NULL);
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_by_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_64C19C1B03A8386` (`created_by_id`),
  CONSTRAINT `FK_64C19C1B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (1,2,'Signature Eco Drinks 🌿','House-crafted beverages made with natural, plant-based ingredients for a refreshing and healthy experience.','2026-03-11 19:09:08'),(2,9,'Cold Brews & Iced Coffee ❄️','Smooth, chilled coffee options perfect for a cool and energizing pick-me-up.','2026-03-30 20:10:03'),(3,9,'Hot Beverages ☕','Comforting warm drinks made from sustainably sourced coffee, tea, and natural blends','2026-04-07 15:23:47'),(4,9,'Fruit-Based Refreshers 🍓','Light and vibrant drinks made with real fruits for a naturally sweet boost','2026-04-07 15:24:12'),(5,9,'Healthy Snacks 🥗','Nutritious bites that pair perfectly with your drink, made with wholesome ingredients','2026-04-07 15:24:31'),(6,9,'Plant-Based Specials 🌱','Unique menu items that highlight creative, eco-friendly, and plant-based alternatives','2026-04-07 15:24:51');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_by_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_81398E09B03A8386` (`created_by_id`),
  CONSTRAINT `FK_81398E09B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (4,32,'ESTRABELA HYZL-ANN','estrabelahyzlann123@outlook.com','099','street','2026-05-18 07:28:06');
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20260317144323','2026-03-17 14:43:41',840),('DoctrineMigrations\\Version20260330111236','2026-03-30 11:13:06',484),('DoctrineMigrations\\Version20260330115020','2026-03-30 11:50:36',295),('DoctrineMigrations\\Version20260404142937','2026-04-04 14:29:47',454),('DoctrineMigrations\\Version20260512025456','2026-05-12 02:56:24',1129),('DoctrineMigrations\\Version20260519131329','2026-05-19 13:13:54',672);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
INSERT INTO `messenger_messages` VALUES (20,'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;N;i:1;N;i:2;s:1150:\\\"\r\n                <div style=\\\"font-family:sans-serif;max-width:520px;margin:auto;padding:32px;\r\n                            border:1px solid #e0e0e0;border-radius:12px;\\\">\r\n                    <h2 style=\\\"color:#1D4A23;\\\">☕ Welcome to EcoBrew Café!</h2>\r\n                    <p>Hi <strong>hyzl ann</strong>,</p>\r\n                    <p>Thanks for registering! Please verify your email address by clicking the button below.</p>\r\n                    <a href=\\\"http://127.0.0.1:8000/verify-email?token=621d9dc23c2b09f3a9edf436d0e23d3a29dada8bef7018bc6537ca36030a9a98\\\"\r\n                       style=\\\"display:inline-block;margin:24px 0;padding:12px 28px;\r\n                              background:#1D4A23;color:white;border-radius:8px;\r\n                              text-decoration:none;font-weight:600;\\\">\r\n                        Verify My Account\r\n                    </a>\r\n                    <p style=\\\"color:#888;font-size:13px;\\\">\r\n                        This link expires in 24 hours.<br>\r\n                        If you did not register with EcoBrew, you can safely ignore this email.\r\n                    </p>\r\n                </div>\r\n            \\\";i:3;s:5:\\\"utf-8\\\";i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:20:\\\"hello@ecobrewcafe.ph\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:31:\\\"estrabelahyzlann123@outlook.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:33:\\\"Verify your EcoBrew Café account\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}','[]','default','2026-04-05 09:13:50','2026-04-05 09:13:50','2026-04-05 09:21:43');
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `created_by_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `total_price` double NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_F52993984584665A` (`product_id`),
  KEY `IDX_F52993989395C3F3` (`customer_id`),
  KEY `IDX_F5299398B03A8386` (`created_by_id`),
  CONSTRAINT `FK_F52993984584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `FK_F52993989395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  CONSTRAINT `FK_F5299398B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order`
--

LOCK TABLES `order` WRITE;
/*!40000 ALTER TABLE `order` DISABLE KEYS */;
INSERT INTO `order` VALUES (3,1,4,32,2,190,'Completed','2026-05-18 07:28:06'),(4,2,4,32,1,85,'Completed','2026-05-18 07:28:10'),(5,3,4,32,1,90,'Completed','2026-05-18 10:38:08'),(6,2,4,32,1,85,'Cancelled','2026-05-18 12:37:07'),(7,7,4,32,1,85,'Cancelled','2026-05-18 12:37:10'),(8,4,4,32,2,200,'Cancelled','2026-05-19 20:51:20'),(9,7,4,32,1,85,'Completed','2026-05-19 21:53:01'),(10,1,4,32,1,95,'Cancelled','2026-05-21 14:12:32'),(11,1,4,32,1,95,'Pending','2026-05-21 14:12:53'),(12,1,4,32,1,95,'Pending','2026-05-21 14:13:51'),(13,1,4,32,1,95,'Pending','2026-05-21 14:14:17'),(14,4,4,32,1,100,'Pending','2026-05-21 14:35:29'),(15,4,4,32,1,100,'Cancelled','2026-05-21 14:43:47'),(16,4,4,32,1,100,'Completed','2026-05-21 14:49:13'),(17,4,4,32,1,100,'Cancelled','2026-05-21 14:49:28'),(18,4,4,32,1,100,'Completed','2026-05-21 14:58:45'),(19,9,4,32,1,60,'Pending','2026-05-21 15:16:44'),(20,12,4,32,1,110,'Pending','2026-05-21 16:30:22');
/*!40000 ALTER TABLE `order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `created_by_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `created_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_D34A04AD12469DE2` (`category_id`),
  KEY `IDX_D34A04ADB03A8386` (`created_by_id`),
  CONSTRAINT `FK_D34A04AD12469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  CONSTRAINT `FK_D34A04ADB03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (1,1,1,'Green Bliss Latte','A creamy matcha-based drink made with plant milk for a smooth, earthy flavor',95,'Iced-Matcha-Latte-Munching-with-Mariyah-6a09c959cfa82.jpg',NULL,2,'2026-03-12 07:51:40'),(2,1,9,'Eco Herbal Cooler','A refreshing blend of herbal tea and natural sweeteners, served chilled',85,'DIY-Herbal-Tea-for-Better-Digestion-Simple-Ayurvedic-Recipe-6a09c8a3862e7.jpg',NULL,21,'2026-04-07 15:28:33'),(3,2,9,'Classic Cold Brew','Slow-steeped coffee with a bold yet smooth taste',90,'How-to-Make-Cold-Brew-Coffee-at-Home-1-6a09c727234c7.jpg',NULL,2,'2026-04-07 15:29:38'),(4,2,9,'Vanilla Oat Iced Coffee','Chilled coffee with a hint of vanilla and creamy oat milk',100,'Easy-Iced-Oat-Milk-Honey-Vanilla-Latte-Creamy-6a09c7695e7fc.jpg',NULL,17,'2026-04-07 15:30:36'),(5,3,9,'Organic Brewed Coffee','Freshly brewed coffee made from sustainably sourced beans',80,'French-roast-is-a-very-dark-coffee-known-for-its-6a09c7a732076.jpg',NULL,0,'2026-04-07 15:32:46'),(6,3,9,'Chamomile Calm Tea','A soothing hot tea perfect for relaxation and stress relief',75,'CHA-DE-CAMOMILA-calmante-natural-em-forma-de-6a09c6e0e036e.jpg',NULL,0,'2026-04-07 15:33:21'),(7,4,9,'Strawberry Fresh Splash','A sweet and tangy drink made with real strawberries',85,'Easy-and-Healthy-Smoothie-King-Recipe-How-To-Make-In-Easiest-Ways-6a09c8d362b94.jpg',NULL,6,'2026-04-07 15:35:24'),(8,4,9,'Mango Citrus Cooler','A light wrap filled with fresh vegetables and a healthy dressing',95,'Mango-Chili-Citrus-Sparkler-Bright-tropical-6a09c620c38b3.jpg',NULL,0,'2026-04-07 15:36:18'),(9,5,9,'Banana Oat Muffin','A soft, naturally sweet muffin made with oats and ripe bananas',60,'Moist-Banana-Bread-Recipe-Homemade-Baking-Guide-Digital-Download-6a09c5ea20432.jpg',NULL,5,'2026-04-07 15:37:17'),(11,5,9,'Veggie Wrap','A light wrap filled with fresh vegetables and a healthy dressing',95,'Greek-Veggie-Cottage-Cheese-Wrap-Averyrecipes-6a09c909a8402.jpg',NULL,0,'2026-04-07 15:40:17'),(12,6,9,'Vegan Chocolate Shake','A rich chocolate drink made with dairy-free ingredients',110,'Alexandra-Banana-Chocolate-Smoothie-dessert-vibes-breakfast-energy-Rich-sweet-and-full-of-potassium-feel-good-fuel-Ingredients-1-banana-1-tbsp-cocoa-or-cacao-powder-1-2-cup-almond-or-oat-mil-6a09c835813f6.jpg',NULL,6,'2026-04-07 15:40:53'),(13,6,9,'Plant Protein Smoothie','A filling smoothie packed with plant-based protein and natural flavors',120,'12-Delicious-Plant-Based-Post-Workout-Smoothies-6a09c7d95e967.jpg',NULL,0,'2026-04-07 15:41:41');
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `total_amount` double NOT NULL,
  `sale_date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_6B8170444584665A` (`product_id`),
  CONSTRAINT `FK_6B8170444584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock`
--

DROP TABLE IF EXISTS `stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `created_by_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `last_updated` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `created_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `is_history_entry` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'restock',
  PRIMARY KEY (`id`),
  KEY `IDX_4B3656604584665A` (`product_id`),
  KEY `IDX_4B365660B03A8386` (`created_by_id`),
  CONSTRAINT `FK_4B3656604584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4B365660B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock`
--

LOCK TABLES `stock` WRITE;
/*!40000 ALTER TABLE `stock` DISABLE KEYS */;
INSERT INTO `stock` VALUES (10,2,9,22,'2026-05-19 21:46:02','2026-04-19 21:44:37',0,'restock'),(15,12,32,6,'2026-05-21 16:30:23','2026-05-02 13:52:41',0,'restock'),(16,12,32,7,'2026-05-02 13:52:41','2026-05-02 13:52:41',1,'restock'),(17,2,32,1,'2026-05-02 13:53:54','2026-05-02 13:53:54',1,'restock'),(18,9,32,5,'2026-05-21 15:16:44','2026-05-02 13:55:26',0,'restock'),(19,9,32,6,'2026-05-02 13:55:26','2026-05-02 13:55:26',1,'restock'),(20,7,32,6,'2026-05-19 21:53:01','2026-05-02 13:56:26',0,'restock'),(21,7,32,8,'2026-05-02 13:56:26','2026-05-02 13:56:26',1,'restock'),(23,1,9,5,'2026-05-18 06:06:33','2026-05-18 06:06:33',1,'restock'),(24,3,9,2,'2026-05-18 10:38:08','2026-05-18 08:02:07',0,'restock'),(25,3,9,3,'2026-05-18 08:02:07','2026-05-18 08:02:07',1,'restock'),(26,4,9,17,'2026-05-21 14:58:45','2026-05-19 20:49:30',0,'restock'),(27,4,9,11,'2026-05-19 20:49:30','2026-05-19 20:49:30',1,'restock'),(28,4,9,11,'2026-05-19 20:49:33','2026-05-19 20:49:33',1,'restock'),(29,2,NULL,1,'2026-05-19 21:46:02','2026-05-19 21:46:02',1,'order_cancelled'),(30,7,NULL,1,'2026-05-19 21:53:01','2026-05-19 21:53:01',1,'order_deduction'),(31,1,9,2,'2026-05-21 14:14:17','2026-05-20 07:42:30',0,'restock'),(32,1,9,6,'2026-05-20 07:42:30','2026-05-20 07:42:30',1,'restock'),(33,1,NULL,1,'2026-05-21 14:12:32','2026-05-21 14:12:32',1,'order_deduction'),(34,1,NULL,1,'2026-05-21 14:12:53','2026-05-21 14:12:53',1,'order_deduction'),(35,1,NULL,1,'2026-05-21 14:13:51','2026-05-21 14:13:51',1,'order_deduction'),(36,1,NULL,1,'2026-05-21 14:14:17','2026-05-21 14:14:17',1,'order_deduction'),(37,4,NULL,1,'2026-05-21 14:35:29','2026-05-21 14:35:29',1,'order_deduction'),(38,4,NULL,1,'2026-05-21 14:43:47','2026-05-21 14:43:47',1,'order_deduction'),(39,4,NULL,1,'2026-05-21 14:49:13','2026-05-21 14:49:13',1,'order_deduction'),(40,4,NULL,1,'2026-05-21 14:49:28','2026-05-21 14:49:28',1,'order_deduction'),(41,4,NULL,1,'2026-05-21 14:58:45','2026-05-21 14:58:45',1,'order_deduction'),(42,9,NULL,1,'2026-05-21 15:16:44','2026-05-21 15:16:44',1,'order_deduction'),(43,12,NULL,1,'2026-05-21 16:30:23','2026-05-21 16:30:23',1,'order_deduction');
/*!40000 ALTER TABLE `stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firebase_uid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  UNIQUE KEY `UNIQ_8D93D6492FB49151` (`firebase_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'staff1','[\"ROLE_STAFF\"]','$2y$13$RxXy.kZdcAOBKWNLyk.ede5TvJ6ZyKZjElhQt/mXTYJeT0E0n1Cci',0,'staff1@gmail.com','Staff Member1','2026-03-11 18:20:31','active',NULL,NULL,NULL,NULL),(2,'Admin','[\"ROLE_ADMIN\"]','$2y$13$MuIW2aFG5esEAN03lnhC4.CnvIGGBRQJx/6R8iy5YEuv8OLeSS.86',0,'admin@gmail.com','admin','2026-03-11 19:05:03','active',NULL,NULL,NULL,NULL),(3,'admin2','[\"ROLE_ADMIN\"]','$2y$13$KH5St9.tZzNg5.fMIFZ7T.I6MnAsnMleVUFa5cL15Q5rleC5PN832',0,'admin2@gmail.com','Default Name','2026-03-18 11:41:47','active','4f028e18f3994f346c548f1a6fb2ceb03e82acaba54e5613825c55be6267f99a',NULL,NULL,NULL),(9,'Hyzl-Ann','[\"ROLE_ADMIN\"]','$2y$13$eLvo35pmEEk9wbJYuIyjxu/ghel7CmbLUx6uKMQBViZSUmt1e/Heu',1,'estrabelahyzlann0@gmail.com','Hyzl-Ann Estrabela','2026-03-18 18:54:39','active',NULL,'https://lh3.googleusercontent.com/a/ACg8ocIrqYgnqnAIYfOqUzOiylyKVpqWgKVdRqAAOzrCoR2LsJx-2A=s96-c','B5rJf4qHyJNHw5iZAtHNR4RRjfH2','Hyzl-Ann Estrabela'),(13,'user13','[\"ROLE_STAFF\"]','$2y$13$XQFpKrQyLo62McvEseSbG.thH1QIZ73qpIyai5mzhE0bY9rEgPWHy',0,'user13@gmail.com','Default Name','2026-03-30 15:54:39','active','58027d00a8ac9d50f9fd279315417b0c2abec29b6cb7d80a163513bdea3390f5',NULL,NULL,NULL),(14,'user14','[\"ROLE_STAFF\"]','$2y$13$7uuI5POBq6juTq6d3h21Te8Mvi4HyFKKhBl9pFO3kziSC4Z8opUaS',0,'user14@gmail.com','Default Name','2026-03-30 20:23:10','active','665eafe86560101a02ae0434aab2addd902910c379e6dd906e29dcaf6b9d103c',NULL,NULL,NULL),(15,'user15','[\"ROLE_STAFF\"]','$2y$13$TmxIbVRh4xV5JX4IatIoLO9Kell5nfzYihivhkqY/MVs238uExJ0W',0,'user15@gmail.com','Default Name','2026-04-04 18:59:55','active','af03092df205f73b2d68acef916cdf9efc5a09ed81ea49c24e17c3e5d5f04934',NULL,NULL,NULL),(32,'hyzl','[\"ROLE_CUSTOMER\"]','$2y$13$srqmmxH8w9smIN1kLBqvjuh4zDHGgKr3UFa5kZk3d.VjYOHL5Buv6',1,'estrabelahyzlann123@outlook.com','ESTRABELA HYZL-ANN','2026-04-05 20:54:43','active',NULL,'https://lh3.googleusercontent.com/a/ACg8ocLUKruNTfuYDbXJ45qaUX6qqrgdAV1fLc63ToNka7WUAQ5P-w=s96-c','sAEVDkQrf5cmW8WsxiklEqGyjJV2','ESTRABELA HYZL-ANN'),(36,'llloll123pll','[\"ROLE_CUSTOMER\"]','',1,'llloll123pll@gmail.com','000 123','2026-04-19 21:18:29','active',NULL,NULL,NULL,NULL),(39,'hyzl5678','[\"ROLE_STAFF\"]','',1,'hyzl5678@gmail.com','Hyzl','2026-05-13 20:13:48','active',NULL,'https://lh3.googleusercontent.com/a/ACg8ocIIQPKbWnZGUhjRBU0bS2vI9tjPyDYm5mydiL1CKfWWjHjs4g=s96-c','ugqlxW8ZBcVbxqfWCIC0QnAUZ7X2','Hyzl'),(40,'anniestrabela','[\"ROLE_CUSTOMER\"]','',1,'anniestrabela@gmail.com','Annie Estrabela','2026-05-13 20:15:37','active',NULL,'https://lh3.googleusercontent.com/a/ACg8ocKQPivEOcusBfyijHnLs2CP-i2taWHVRl6D4ojZMtzRVPKCLg=s96-c','ziHlUkiCN3NcWHv8wyI0Wzo8Gif2','Annie Estrabela');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `verification_token`
--

DROP TABLE IF EXISTS `verification_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `verification_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C1CC006B5F37A13B` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `verification_token`
--

LOCK TABLES `verification_token` WRITE;
/*!40000 ALTER TABLE `verification_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `verification_token` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-21 16:17:57
