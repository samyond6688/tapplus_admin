-- MySQL dump 10.13  Distrib 5.7.27, for Linux (x86_64)
--
-- Host: localhost    Database: publish_admin
-- ------------------------------------------------------
-- Server version	5.7.27-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_menu`
--

DROP TABLE IF EXISTS `admin_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uri` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `show` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_menu`
--

LOCK TABLES `admin_menu` WRITE;
/*!40000 ALTER TABLE `admin_menu` DISABLE KEYS */;
INSERT INTO `admin_menu` VALUES (1,0,1,'Index','feather icon-bar-chart-2','/','',1,'2021-06-04 12:36:36',NULL),(2,0,2,'Admin','feather icon-settings','','',1,'2021-06-04 12:36:36',NULL),(3,2,3,'Users','','auth/users','',1,'2021-06-04 12:36:36',NULL),(4,2,4,'Roles','','auth/roles','',1,'2021-06-04 12:36:36',NULL),(5,2,5,'Permission','','auth/permissions','',1,'2021-06-04 12:36:36',NULL),(6,2,6,'Menu','','auth/menu','',1,'2021-06-04 12:36:36',NULL),(7,2,7,'Extensions','','auth/extensions','',1,'2021-06-04 12:36:36',NULL),(8,0,8,'融合管理','fa-bars',NULL,'',1,'2021-06-05 11:45:11','2021-06-24 10:54:14'),(9,8,9,'基础管理',NULL,NULL,'',1,'2021-06-05 11:45:57','2021-06-24 10:54:36'),(10,8,10,'出包管理',NULL,NULL,'',1,'2021-06-05 11:46:31','2021-06-24 10:54:45'),(11,9,11,'合作商主体',NULL,NULL,'',1,'2021-06-05 11:47:03','2021-06-05 11:47:03'),(13,9,13,'插件账号',NULL,'/plugins','',1,'2021-06-05 11:48:22','2021-06-07 11:00:21'),(14,9,14,'插件参数',NULL,'/plugin_params','',1,'2021-06-05 11:48:34','2021-06-07 16:33:05'),(15,10,15,'游戏组',NULL,'/cates','',1,'2021-06-05 11:50:04','2021-06-16 14:12:46'),(16,10,16,'游戏',NULL,'/games','',1,'2021-06-05 11:50:26','2021-06-15 11:38:08'),(17,10,17,'游戏包',NULL,'/packages','',1,'2021-06-05 11:50:39','2021-06-24 18:53:36'),(18,0,18,'投放推广','fa-shopping-bag',NULL,'',1,'2021-06-24 10:47:10','2021-06-24 10:53:23'),(19,0,19,'支付结算','fa-dollar',NULL,'',1,'2021-06-24 10:48:11','2021-06-24 10:48:11'),(20,0,20,'统计报表','fa-bar-chart-o',NULL,'',1,'2021-06-24 10:50:40','2021-06-24 10:50:40'),(21,0,21,'系统管理','fa-th-large',NULL,'',1,'2021-06-24 10:57:19','2021-06-24 10:57:19'),(22,18,22,'基础管理',NULL,NULL,'',1,'2021-06-24 10:58:39','2021-06-24 10:58:39'),(23,22,23,'媒体管理',NULL,'/media','',1,'2021-06-24 10:59:31','2021-06-24 10:59:31'),(24,22,24,'媒体账号',NULL,'/medium_accounts','',1,'2021-06-24 11:00:43','2021-06-24 11:21:27'),(25,18,25,'投放管理',NULL,NULL,'',1,'2021-06-28 18:55:14','2021-06-28 18:55:14'),(26,25,26,'投放计划',NULL,'/serving_plans','',1,'2021-06-28 18:55:46','2021-06-28 18:55:46'),(27,21,27,'用户管理',NULL,NULL,'',1,'2021-06-29 11:25:17','2021-06-29 11:25:17'),(28,27,28,'用户列表',NULL,'/sdk/users','',1,'2021-06-29 11:26:03','2021-06-29 11:26:03'),(29,19,29,'订单管理',NULL,NULL,'',1,'2021-06-29 14:33:47','2021-06-29 14:33:47'),(30,29,30,'订单查看',NULL,'/sdk/orders','',1,'2021-06-29 14:34:22','2021-06-29 14:34:22');
/*!40000 ALTER TABLE `admin_menu` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-06-29  6:43:42
