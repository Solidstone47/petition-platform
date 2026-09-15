-- MariaDB dump 10.19  Distrib 10.4.21-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: petition_platform
-- ------------------------------------------------------
-- Server version	10.4.21-MariaDB

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
-- Current Database: `petition_platform`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `petition_platform` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `petition_platform`;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (5,'Administrator','orderxpeter102@gmail.com','$2y$10$DK/ad/G0c9jqAc6DUrMJU.fL5PTl3y8DctpwDwuEFZoWQ7rGbaAb2','2026-08-28 08:17:54');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_messages_status` (`status`),
  KEY `idx_messages_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_type` enum('user','admin') NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_password_reset_token` (`token`),
  KEY `idx_password_reset_account` (`account_type`,`account_id`),
  KEY `idx_password_reset_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,'user',1,'orderxpeter102@gmail.com','6c8242af82874d0be87f6d8ee86642218f550474cfcf1e959634e160c1ec6572','2026-08-28 16:10:03',0,'2026-08-28 13:10:03'),(2,'user',2,'orderxpeter102@gmail.com','6c5c16909ecf7e357c0d6209a8a89a4459675c0302be654d6f63ebe54e31fbe5','2026-08-28 18:55:06',1,'2026-08-28 15:55:06'),(4,'user',2,'orderxpeter102@gmail.com','c0553d457ddfbe7c91a0b8a5ee5012555fb741c8514bf81b8d40c36e1a43f115','2026-08-29 11:07:24',0,'2026-08-29 08:07:24');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `petitions`
--

DROP TABLE IF EXISTS `petitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `petitions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Other',
  `goal` int(10) unsigned NOT NULL DEFAULT 1000,
  `status` enum('draft','active','closed') NOT NULL DEFAULT 'draft',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_petitions_status` (`status`),
  KEY `idx_petitions_created_at` (`created_at`),
  KEY `fk_petitions_admin` (`created_by`),
  CONSTRAINT `fk_petitions_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `petitions`
--

LOCK TABLES `petitions` WRITE;
/*!40000 ALTER TABLE `petitions` DISABLE KEYS */;
INSERT INTO `petitions` VALUES (1,'Improve the Quality of Public Education','We call for continued investment in Tanzania\'s public education system, with particular attention to teacher availability, classroom conditions, learning materials and student retention.\r\n\r\nEvery child should have access to quality education regardless of where they live or their family\'s economic circumstances.\r\n\r\nWe urge decision-makers to prioritize education financing, equitable distribution of teachers and resources, and measurable improvements in learning outcomes.\r\n\r\nEducation quality, teacher distribution, financing and student retention remain documented challenges.','Other',20000,'draft',NULL,'2026-08-26 14:10:03','2026-08-26 14:10:03'),(2,'Strengthen Public Accountability and Transparency','We call for stronger transparency in the management of public funds and government projects. Citizens should have accessible information about major public expenditures, procurement decisions, project costs, implementation progress and outcomes.\r\n\r\nWe urge relevant institutions to strengthen public reporting, independent oversight and mechanisms that allow citizens to raise concerns and receive meaningful responses.\r\n\r\nPublic resources belong to the people. Greater transparency can strengthen trust between citizens and public institutions.','Other',10000,'active',NULL,'2026-08-26 14:11:15','2026-08-26 14:25:40'),(3,'Protect the Right to Peaceful Civic Participation','We call for the protection of citizens\' lawful rights to peaceful civic participation, public discussion and association.\n\nTanzania\'s democratic institutions should provide safe and predictable channels through which citizens, civil society organizations and political actors can express concerns and participate in national affairs peacefully and within the law.\n\nWe urge authorities to ensure that restrictions on peaceful civic activity are lawful, proportionate and consistent with Tanzania\'s constitutional and human-rights obligations.','Other',15000,'active',NULL,'2026-08-26 14:11:15','2026-08-26 14:11:15'),(4,'Advance Constitutional Reform Through Inclusive Public Consultation','We call for an inclusive, transparent and citizen-centered process for constitutional reform in Tanzania.\n\nAny major constitutional changes should involve meaningful consultation with citizens, civil society, legal experts, political parties and other relevant stakeholders.\n\nThe process should provide clear opportunities for public input and should strengthen institutions, accountability, representation and protection of fundamental rights.','Other',20000,'active',NULL,'2026-08-26 14:11:15','2026-08-26 14:28:11'),(5,'Improve the Quality of Public Education','We call for continued investment in Tanzania\'s public education system, with particular attention to teacher availability, classroom conditions, learning materials and student retention.\n\nEvery child should have access to quality education regardless of where they live or their family\'s economic circumstances.\n\nWe urge decision-makers to prioritize education financing, equitable distribution of teachers and resources, and measurable improvements in learning outcomes.','Other',25000,'active',NULL,'2026-08-26 14:11:15','2026-08-26 14:11:15'),(6,'Strengthen Flood and Drought Preparedness','We call for stronger national and local preparedness for floods, droughts and other climate-related risks.\n\nTanzania should strengthen early-warning systems, drainage infrastructure, emergency response, water management and climate-resilient public infrastructure.\n\nWe also call for climate-risk considerations to be integrated into public budgeting and development planning so communities are better protected before disasters occur.','Other',12000,'active',NULL,'2026-08-26 14:11:15','2026-08-26 14:11:15'),(7,'Improve Transparency in the National Budget','We call for greater public transparency regarding Tanzania\'s national budget, public borrowing and government expenditure.\n\nCitizens should be able to easily understand how public revenue is collected, how borrowed funds are used, how major projects are financed and how government spending contributes to public services.\n\nWe urge the government and relevant oversight institutions to publish accessible, timely and understandable financial information.','Other',10000,'draft',NULL,'2026-08-26 14:11:15','2026-08-26 14:11:15'),(8,'Improve Public Transport and Urban Mobility in Dar es Salaam','We call for continued improvement of public transportation in Dar es Salaam and other rapidly growing urban areas.\n\nPriorities should include reliable public transport, safer roads, pedestrian infrastructure, traffic management, better integration between transport services and improved accessibility for people with disabilities.\n\nEfficient transportation is essential for workers, students, businesses and the wider urban economy.','Other',15000,'active',NULL,'2026-08-26 14:11:15','2026-08-26 14:11:15'),(9,'Expand Access to Clean and Reliable Water','We call for accelerated investment in reliable and affordable access to clean water for communities across Tanzania.\n\nWater infrastructure should receive appropriate maintenance and expansion, particularly in rapidly growing urban areas and communities facing recurring shortages.\n\nWe urge authorities to improve transparency around water projects and prioritize communities with the greatest unmet needs.','Other',20000,'active',NULL,'2026-08-26 14:11:15','2026-08-26 14:11:15'),(10,'Increase Youth Employment and Entrepreneurship Opportunities','We call for stronger programs supporting employment, entrepreneurship and skills development among Tanzania\'s young people.\n\nYoung Tanzanians should have greater access to practical training, entrepreneurship support, affordable financing, technology and opportunities to participate in the formal economy.\n\nWe urge public and private institutions to develop measurable programs that help young people transition from education and training into sustainable employment.','Other',25000,'active',NULL,'2026-08-26 14:11:15','2026-08-26 14:11:15'),(11,'Protect Tanzania\'s Environment and Natural Resources','We call for stronger protection of Tanzania\'s forests, wetlands, wildlife habitats, water sources and coastal ecosystems.\n\nDevelopment projects should follow environmental laws and undergo appropriate assessment, while communities affected by environmental decisions should have meaningful opportunities to participate.\n\nTanzania\'s economic development and environmental protection should advance together so that natural resources remain available for future generations.','Other',20000,'active',NULL,'2026-08-26 14:11:15','2026-08-26 14:11:15');
/*!40000 ALTER TABLE `petitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','Petition Platform','2026-08-28 10:14:49'),(2,'site_description','A platform for creating and supporting petitions.','2026-08-28 10:14:49'),(3,'contact_email','','2026-08-28 10:14:49'),(4,'default_signature_goal','1000','2026-08-28 10:14:49'),(5,'allow_registration','1','2026-08-28 10:14:49'),(6,'allow_petitions','1','2026-08-28 10:14:49'),(7,'maintenance_mode','0','2026-08-28 10:23:16');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `signatures`
--

DROP TABLE IF EXISTS `signatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signatures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `petition_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_petition_user` (`petition_id`,`user_id`),
  KEY `idx_signatures_user` (`user_id`),
  CONSTRAINT `fk_signatures_petition` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_signatures_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `signatures`
--

LOCK TABLES `signatures` WRITE;
/*!40000 ALTER TABLE `signatures` DISABLE KEYS */;
INSERT INTO `signatures` VALUES (3,2,2,'2026-08-29 16:45:50');
/*!40000 ALTER TABLE `signatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `password_reset_required` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Jeff Peter','orderxpeter102@gmail.com','108963124877558387983','+255687614346','Tanzania','$2y$10$r063bE/pGlHfjkhZTSw2p.KJLLUwYw9VJ71oH6eqAgBOTR29d2/eC',0,'2026-08-28 15:38:11');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-15 14:00:40
