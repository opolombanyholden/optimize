-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: finance
-- ------------------------------------------------------
-- Server version	8.0.30

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
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,'default','created','App\\Models\\User',1,NULL,NULL,'{\"attributes\": {\"id\": 1, \"nom\": null, \"name\": \"Super Admin\", \"sexe\": null, \"email\": \"super@optimiz.test\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$N5.oJiJhMpsFN6.rCMKxYurkiK0FAz5CzhteMvMPXzvNo7dS8n.GW\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:53:59.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:53:59.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": null, \"nom_jeune_fille\": null, \"email_verified_at\": null}}','2022-10-18 10:53:59','2022-10-18 10:53:59'),(2,'default','created','App\\Models\\User',2,NULL,NULL,'{\"attributes\": {\"id\": 2, \"nom\": null, \"name\": \"Admin\", \"sexe\": null, \"email\": \"admin@optimiz.test\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$oNCiH1u0uE5pT2vVTdzJt.FYJb9QGxDu7Rj/iCfWHnnUSRAzGVmwK\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:53:59.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:53:59.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": null, \"nom_jeune_fille\": null, \"email_verified_at\": null}}','2022-10-18 10:53:59','2022-10-18 10:53:59'),(3,'default','created','App\\Models\\User',3,NULL,NULL,'{\"attributes\": {\"id\": 3, \"nom\": null, \"name\": \"Andrée Leroux\", \"sexe\": null, \"email\": \"victor.maillot@example.com\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"MnpSHFvHxK\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:53:59.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(4,'default','created','App\\Models\\User',4,NULL,NULL,'{\"attributes\": {\"id\": 4, \"nom\": null, \"name\": \"Étienne Louis\", \"sexe\": null, \"email\": \"lcoste@example.org\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"CH7og32nRS\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:53:59.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(5,'default','created','App\\Models\\User',5,NULL,NULL,'{\"attributes\": {\"id\": 5, \"nom\": null, \"name\": \"Renée Vaillant-Chauveau\", \"sexe\": null, \"email\": \"fdupuy@example.net\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"A6INByaLN7\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:53:59.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(6,'default','created','App\\Models\\User',6,NULL,NULL,'{\"attributes\": {\"id\": 6, \"nom\": null, \"name\": \"Capucine Pires\", \"sexe\": null, \"email\": \"roger68@example.net\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"ithBuQ9euv\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:53:59.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(7,'default','created','App\\Models\\User',7,NULL,NULL,'{\"attributes\": {\"id\": 7, \"nom\": null, \"name\": \"Thomas-Noël Delattre\", \"sexe\": null, \"email\": \"maurice36@example.net\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"H94qCUaViN\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:53:59.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(8,'default','created','App\\Models\\User',8,NULL,NULL,'{\"attributes\": {\"id\": 8, \"nom\": null, \"name\": \"Lucy Le Goff\", \"sexe\": null, \"email\": \"denis.launay@example.net\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"g8ywFWHWY5\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:53:59.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(9,'default','created','App\\Models\\User',9,NULL,NULL,'{\"attributes\": {\"id\": 9, \"nom\": null, \"name\": \"Thomas Hebert\", \"sexe\": null, \"email\": \"aime.barthelemy@example.com\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"8GwMofHQgv\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:54:00.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(10,'default','created','App\\Models\\User',10,NULL,NULL,'{\"attributes\": {\"id\": 10, \"nom\": null, \"name\": \"Théodore Vallet\", \"sexe\": null, \"email\": \"rousseau.richard@example.org\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"EF5WiRT8hd\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:54:00.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(11,'default','created','App\\Models\\User',11,NULL,NULL,'{\"attributes\": {\"id\": 11, \"nom\": null, \"name\": \"Hortense Dias\", \"sexe\": null, \"email\": \"orodriguez@example.org\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"mT3uQwLftI\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:54:00.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(12,'default','created','App\\Models\\User',12,NULL,NULL,'{\"attributes\": {\"id\": 12, \"nom\": null, \"name\": \"Noël Gallet-Picard\", \"sexe\": null, \"email\": \"laurence91@example.com\", \"prenom\": null, \"adresse\": null, \"id_pays\": null, \"isadmin\": 0, \"id_ville\": null, \"password\": \"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi\", \"matricule\": null, \"code_agent\": null, \"created_at\": \"2022-10-18T11:54:00.000000Z\", \"deleted_at\": null, \"updated_at\": \"2022-10-18T11:54:00.000000Z\", \"id_quartier\": null, \"contact_perso\": null, \"date_naissance\": null, \"remember_token\": \"bILD0Pk0as\", \"nom_jeune_fille\": null, \"email_verified_at\": \"2022-10-18T11:54:00.000000Z\"}}','2022-10-18 10:54:00','2022-10-18 10:54:00'),(13,'default','created','App\\Models\\Entite',1,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(14,'default','created','App\\Models\\ModeReglement',1,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(15,'default','created','App\\Models\\ModeReglement',2,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(16,'default','created','App\\Models\\ModeReglement',3,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(17,'default','created','App\\Models\\Compte',1,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(18,'default','created','App\\Models\\Compte',2,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(19,'default','created','App\\Models\\Source',1,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(20,'default','created','App\\Models\\Source',2,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(21,'default','created','App\\Models\\Source',3,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(22,'default','created','App\\Models\\Titre',1,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(23,'default','created','App\\Models\\Titre',2,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(24,'default','created','App\\Models\\Titre',3,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(25,'default','created','App\\Models\\Titre',4,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(26,'default','created','App\\Models\\Titre',5,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(27,'default','created','App\\Models\\Titre',6,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(28,'default','created','App\\Models\\Titre',7,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(29,'default','created','App\\Models\\Titre',8,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(30,'default','created','App\\Models\\Ligne',1,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(31,'default','created','App\\Models\\Ligne',2,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(32,'default','created','App\\Models\\Ligne',3,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(33,'default','created','App\\Models\\Ligne',4,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(34,'default','created','App\\Models\\Ligne',5,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(35,'default','created','App\\Models\\Ligne',6,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(36,'default','created','App\\Models\\Ligne',7,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(37,'default','created','App\\Models\\Ligne',8,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(38,'default','created','App\\Models\\Ligne',9,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(39,'default','created','App\\Models\\Ligne',10,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(40,'default','created','App\\Models\\Ligne',11,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(41,'default','created','App\\Models\\Ligne',12,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(42,'default','created','App\\Models\\Ligne',13,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(43,'default','created','App\\Models\\Ligne',14,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(44,'default','created','App\\Models\\Ligne',15,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(45,'default','created','App\\Models\\Ligne',16,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(46,'default','created','App\\Models\\Ligne',17,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(47,'default','created','App\\Models\\Ligne',18,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(48,'default','created','App\\Models\\Ligne',19,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(49,'default','created','App\\Models\\Ligne',20,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(50,'default','created','App\\Models\\Ligne',21,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(51,'default','created','App\\Models\\Ligne',22,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(52,'default','created','App\\Models\\Ligne',23,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(53,'default','created','App\\Models\\Ligne',24,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(54,'default','created','App\\Models\\Ligne',25,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(55,'default','created','App\\Models\\Ligne',26,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(56,'default','created','App\\Models\\Ligne',27,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(57,'default','created','App\\Models\\Ligne',28,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(58,'default','created','App\\Models\\Ligne',29,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(59,'default','created','App\\Models\\Ligne',30,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(60,'default','created','App\\Models\\Ligne',31,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(61,'default','created','App\\Models\\Ligne',32,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(62,'default','created','App\\Models\\Ligne',33,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(63,'default','created','App\\Models\\Ligne',34,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(64,'default','created','App\\Models\\Ligne',35,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(65,'default','created','App\\Models\\Ligne',36,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(66,'default','created','App\\Models\\Ligne',37,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(67,'default','created','App\\Models\\Ligne',38,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(68,'default','created','App\\Models\\Ligne',39,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(69,'default','created','App\\Models\\Ligne',40,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(70,'default','created','App\\Models\\Ligne',41,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(71,'default','created','App\\Models\\Ligne',42,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(72,'default','created','App\\Models\\Ligne',43,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(73,'default','created','App\\Models\\Exercice',1,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(74,'default','created','App\\Models\\Exercice',2,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(75,'default','created','App\\Models\\BudgetSource',1,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(76,'default','created','App\\Models\\BudgetSource',2,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(77,'default','created','App\\Models\\Config',1,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(78,'default','created','App\\Models\\Config',2,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(79,'default','created','App\\Models\\Config',3,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(80,'default','created','App\\Models\\Config',4,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(81,'default','created','App\\Models\\Config',5,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(82,'default','created','App\\Models\\Config',6,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(83,'default','created','App\\Models\\Config',7,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(84,'default','created','App\\Models\\Config',8,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(85,'default','created','App\\Models\\Config',9,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(86,'default','created','App\\Models\\Config',10,NULL,NULL,'[]','2022-10-18 10:54:00','2022-10-18 10:54:00'),(87,'default','created','App\\Models\\Config',11,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(88,'default','created','App\\Models\\Config',12,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(89,'default','created','App\\Models\\Config',13,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(90,'default','created','App\\Models\\Config',14,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(91,'default','created','App\\Models\\Config',15,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(92,'default','created','App\\Models\\Config',16,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(93,'default','created','App\\Models\\Config',17,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(94,'default','created','App\\Models\\Config',18,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(95,'default','created','App\\Models\\Config',19,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(96,'default','created','App\\Models\\Config',20,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(97,'default','created','App\\Models\\Config',21,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(98,'default','created','App\\Models\\Config',22,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(99,'default','created','App\\Models\\Config',23,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(100,'default','created','App\\Models\\Config',24,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(101,'default','created','App\\Models\\Config',25,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(102,'default','created','App\\Models\\Config',26,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(103,'default','created','App\\Models\\Config',27,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(104,'default','created','App\\Models\\Config',28,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(105,'default','created','App\\Models\\Config',29,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(106,'default','created','App\\Models\\Config',30,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(107,'default','created','App\\Models\\Config',31,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(108,'default','created','App\\Models\\Config',32,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(109,'default','created','App\\Models\\Config',33,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(110,'default','created','App\\Models\\Config',34,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(111,'default','created','App\\Models\\Config',35,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(112,'default','created','App\\Models\\Config',36,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(113,'default','created','App\\Models\\Config',37,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(114,'default','created','App\\Models\\Config',38,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(115,'default','created','App\\Models\\Config',39,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(116,'default','created','App\\Models\\Config',40,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(117,'default','created','App\\Models\\Config',41,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(118,'default','created','App\\Models\\Config',42,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(119,'default','created','App\\Models\\Config',43,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(120,'default','created','App\\Models\\Config',44,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(121,'default','created','App\\Models\\Config',45,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(122,'default','created','App\\Models\\Config',46,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(123,'default','created','App\\Models\\Config',47,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(124,'default','created','App\\Models\\Config',48,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(125,'default','created','App\\Models\\Config',49,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(126,'default','created','App\\Models\\Config',50,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(127,'default','created','App\\Models\\Config',51,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(128,'default','created','App\\Models\\Config',52,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(129,'default','created','App\\Models\\Config',53,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(130,'default','created','App\\Models\\Config',54,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(131,'default','created','App\\Models\\Config',55,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(132,'default','created','App\\Models\\Config',56,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(133,'default','created','App\\Models\\Config',57,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(134,'default','created','App\\Models\\Config',58,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(135,'default','created','App\\Models\\Transaction',1,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(136,'default','created','App\\Models\\Transaction',2,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(137,'default','created','App\\Models\\Transaction',3,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(138,'default','created','App\\Models\\Transaction',4,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01'),(139,'default','created','App\\Models\\Transaction',5,NULL,NULL,'[]','2022-10-18 10:54:01','2022-10-18 10:54:01');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_sources`
--

DROP TABLE IF EXISTS `budget_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `montant` decimal(12,2) NOT NULL,
  `source_id` bigint unsigned NOT NULL,
  `ligne_id` bigint unsigned NOT NULL,
  `exercice_id` bigint unsigned NOT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_sources`
--

LOCK TABLES `budget_sources` WRITE;
/*!40000 ALTER TABLE `budget_sources` DISABLE KEYS */;
INSERT INTO `budget_sources` VALUES (1,NULL,NULL,637500000.00,3,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(2,NULL,NULL,1021002372.00,3,2,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL);
/*!40000 ALTER TABLE `budget_sources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budgets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `seuil` double(8,2) DEFAULT NULL,
  `status` smallint NOT NULL DEFAULT '0',
  `ligne_id` bigint unsigned NOT NULL,
  `exercice_id` bigint unsigned NOT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budgets`
--

LOCK TABLES `budgets` WRITE;
/*!40000 ALTER TABLE `budgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `budgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comptes`
--

DROP TABLE IF EXISTS `comptes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comptes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rib` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domiciliation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `solde` double(8,2) NOT NULL DEFAULT '0.00',
  `type` int NOT NULL DEFAULT '0',
  `entite_id` bigint unsigned DEFAULT NULL,
  `extra` json DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filepath` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comptes`
--

LOCK TABLES `comptes` WRITE;
/*!40000 ALTER TABLE `comptes` DISABLE KEYS */;
INSERT INTO `comptes` VALUES (1,'C1','Caisse principale',NULL,NULL,NULL,18000.00,0,1,NULL,NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(2,'C2','Caisse secondaire',NULL,NULL,NULL,30000.00,0,1,NULL,NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL);
/*!40000 ALTER TABLE `comptes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configs`
--

DROP TABLE IF EXISTS `configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `config_object` json DEFAULT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configs`
--

LOCK TABLES `configs` WRITE;
/*!40000 ALTER TABLE `configs` DISABLE KEYS */;
INSERT INTO `configs` VALUES (1,'software_version','3.0.0',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(2,'default_theme','light',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(3,'subdomain','',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(4,'plan_income','false',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(5,'label_transaction','Pièce de caisse',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(6,'menu_header_dashboard','Accueil',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(7,'menu_header_budget','Budget',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(8,'menu_header_treso','Opérations',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(9,'menu_header_ref','Référentiel',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(10,'menu_header_admin','Administration',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(11,'menu_header_config','Configuration',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(12,'menu_exercices','Exercices',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(13,'menu_plannification','Plannification',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(14,'menu_suivi','Suivi',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(15,'menu_modification','Modification',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(16,'menu_transactions','Dépenses/Recettes',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(17,'menu_comptes','Comptes',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(18,'menu_journal','Journal',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(19,'menu_lignes','Lignes',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(20,'menu_titres','Titres',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(21,'menu_sources','Sources',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(22,'menu_entites','Entités',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(23,'menu_users','Utilisateurs',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(24,'menu_activity','Logs d\'activité',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(25,'menu_permissions','Permissions',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(26,'menu_roles','Rôles',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(27,'menu_modes_reglements','Modes de règlement',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(28,'menu_parametres','Paramètres',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(29,'menu_guide','Guide',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(30,'devise','XAF',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(31,'page_title_exercices','Exercices Budgétaires',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(32,'page_title_plannification','Plannification Budgétaire',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(33,'page_title_suivi','Suivi Budgétaire',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(34,'page_title_modification','Modification Budgétaire',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(35,'page_title_transactions','Dépenses/Recettes',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(36,'page_title_depenses','Dépenses',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(37,'page_title_recettes','Recettes',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(38,'page_title_comptes','Comptes de trésorerie',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(39,'page_title_journal','Journal de Caisse',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(40,'page_title_lignes','Lignes Budgétaires',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(41,'page_title_titres','Titres Budgétaires',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(42,'page_title_sources','Sources',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(43,'page_title_entites','Entités',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(44,'page_title_users','Utilisateurs',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(45,'page_title_permissions','Permissions',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(46,'page_title_roles','Rôles',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(47,'page_title_parametres','Paramètres',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(48,'page_title_guide','Guide',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(49,'page_title_modif','Modifications Budgétaires',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(50,'page_title_modes_reglement','Modes de Règlement',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(51,'org_name','Yubile Technologie',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(52,'org_logo','org-logo.png',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(53,'org_po','',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(54,'org_tel','',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(55,'org_email','',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(56,'org_nif','',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(57,'org_rccm','',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(58,'org_country_id','80',NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL);
/*!40000 ALTER TABLE `configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entites`
--

DROP TABLE IF EXISTS `entites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entites`
--

LOCK TABLES `entites` WRITE;
/*!40000 ALTER TABLE `entites` DISABLE KEYS */;
INSERT INTO `entites` VALUES (1,'E1',NULL,'Entité par défault',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL);
/*!40000 ALTER TABLE `entites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exercices`
--

DROP TABLE IF EXISTS `exercices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exercices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `budgetglobal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `budgetglobalinitial` decimal(12,2) DEFAULT NULL,
  `budgetglobalrestant` decimal(12,2) DEFAULT NULL,
  `debut` date DEFAULT NULL,
  `fin` date DEFAULT NULL,
  `global` tinyint(1) NOT NULL DEFAULT '0',
  `isvalide` tinyint(1) DEFAULT NULL,
  `status` smallint NOT NULL DEFAULT '0',
  `entite_id` bigint unsigned DEFAULT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exercices`
--

LOCK TABLES `exercices` WRITE;
/*!40000 ALTER TABLE `exercices` DISABLE KEYS */;
INSERT INTO `exercices` VALUES (1,'EX-09-2022','Exercice Budgétaire Septembre 2022','',0.00,10000000.00,10000000.00,'2022-09-01','2022-09-30',0,NULL,0,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(2,'EX-10-2022','Exercice Budgétaire Octobre 2022','',0.00,10000000.00,10000000.00,'2022-10-01','2022-10-31',0,NULL,0,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL);
/*!40000 ALTER TABLE `exercices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extra_fields`
--

DROP TABLE IF EXISTS `extra_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `extra_fields` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extra_fields`
--

LOCK TABLES `extra_fields` WRITE;
/*!40000 ALTER TABLE `extra_fields` DISABLE KEYS */;
INSERT INTO `extra_fields` VALUES (1,'no_quittance','No Quittance','string','Transaction','2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(2,'date_quittance','Date Quittance','date','Transaction','2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(3,'date_reglement_gni','Date de Reglement GNI','date','Transaction','2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(4,'bon_commande','Bon de Commande','file','Transaction','2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(5,'bon_livairson','Bon de Livraison','file','Transaction','2022-10-18 10:54:01','2022-10-18 10:54:01',NULL);
/*!40000 ALTER TABLE `extra_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lignes`
--

DROP TABLE IF EXISTS `lignes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lignes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nature',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `seuil` double(8,2) DEFAULT NULL,
  `status` smallint NOT NULL DEFAULT '0',
  `type` int NOT NULL DEFAULT '0',
  `titre_id` bigint unsigned NOT NULL DEFAULT '1',
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lignes`
--

LOCK TABLES `lignes` WRITE;
/*!40000 ALTER TABLE `lignes` DISABLE KEYS */;
INSERT INTO `lignes` VALUES (1,'7313','Subvention de fonctionnement Etat',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(2,'7391','Subvention des Emplois Etat',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(3,'702','Recettes propres Anpi-Gabon',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(4,'7021','Produits DFDE',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(5,'7022','Produits DEAE',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(6,'7023','Produits DMC',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(7,'7024','Produits DAF',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(8,'7025','Produits SI',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(9,'7089','Autres produits Loyers',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(10,'7546','Produits divers',NULL,NULL,0,1,1,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(11,'601101','Fournitures',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(12,'601101','Documents périodiques',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(13,'601102','Fournitures informatiques',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(14,'601103','Papeterie',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(15,'601104','Fournitures audio-visuelles',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(16,'601105','Imprimés spéciaux',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(17,'601106','Fournitures d\'imprimerie',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(18,'601107','Fournitures de bureaux diverses',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(19,'601108','Produits et fournitures d\'entrerien de bureaux',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(20,'601201','Fournitures et entrerien - véhicules de fonctions',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(21,'601202','Fournitures et entrerien - autres véhicules',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(22,'602402','Patisserie',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(23,'602502','Boissons',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(24,'602509','Produits alimentaires divers',NULL,NULL,0,0,2,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(25,'611101','Frais de mission au Gabon',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(26,'611102','Frais de mission hors du Gabon',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(27,'611201','Frais de deplacement au Gabon',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(28,'611202','Frais de deplacement hors du Gabon',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(29,'611311','Transport terrestre',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(30,'611401','Accueil et reception hotes de marque',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(31,'611402','Autres receptions et restaurant',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(32,'611403','Fournitures accueil et réception',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(33,'611405','Autres frais d\'hôtellerie',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(34,'611407','Location salle de conférence',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(35,'612102','Location bureaux',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(36,'613305','Service gardiennage-Bâtiments divers',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(37,'613405','Convention nettoyage-Bâtiments divers',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(38,'613521','Honoraires',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(39,'613532','Jetons de présence',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(40,'614101','Entretien et réparation - véhicules divers',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(41,'614309','Entretien et réparation - équipements divers',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(42,'615102','Assurances véhicules divers',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(43,'615109','Assurances Bâtiments et équipements divers',NULL,NULL,0,0,3,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL);
/*!40000 ALTER TABLE `lignes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2022_03_17_201824_create_database_finance',1),(6,'2022_07_11_110312_create_permission_tables',1),(7,'2022_07_16_062105_create_media_table',1),(8,'2022_09_30_173351_create_activity_log_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mode_reglements`
--

DROP TABLE IF EXISTS `mode_reglements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mode_reglements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mode_reglements`
--

LOCK TABLES `mode_reglements` WRITE;
/*!40000 ALTER TABLE `mode_reglements` DISABLE KEYS */;
INSERT INTO `mode_reglements` VALUES (1,'CHQ','Chèque',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(2,'NUM','Numéraire',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(3,'VB','Virement Bancaire',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL);
/*!40000 ALTER TABLE `mode_reglements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(4,'App\\Models\\User',3),(4,'App\\Models\\User',4),(4,'App\\Models\\User',5),(4,'App\\Models\\User',6),(4,'App\\Models\\User',7),(4,'App\\Models\\User',8),(4,'App\\Models\\User',9),(4,'App\\Models\\User',10),(4,'App\\Models\\User',11),(4,'App\\Models\\User',12);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modifs`
--

DROP TABLE IF EXISTS `modifs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modifs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `montant` decimal(12,2) NOT NULL DEFAULT '0.00',
  `budget_emission_id` bigint unsigned DEFAULT NULL,
  `budget_reception_id` bigint unsigned DEFAULT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `modifs_budget_emission_id_foreign` (`budget_emission_id`),
  KEY `modifs_budget_reception_id_foreign` (`budget_reception_id`),
  CONSTRAINT `modifs_budget_emission_id_foreign` FOREIGN KEY (`budget_emission_id`) REFERENCES `budget_sources` (`id`),
  CONSTRAINT `modifs_budget_reception_id_foreign` FOREIGN KEY (`budget_reception_id`) REFERENCES `budget_sources` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modifs`
--

LOCK TABLES `modifs` WRITE;
/*!40000 ALTER TABLE `modifs` DISABLE KEYS */;
/*!40000 ALTER TABLE `modifs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'création user','create:user','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(2,'read user','read:user','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(3,'update user','update:user','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(4,'delete User','delete:user','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(5,'validate User','validate:user','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(6,'création role','create:role','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(7,'read role','read:role','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(8,'update role','update:role','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(9,'delete role','delete:role','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(10,'assign role','assign:role','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(11,'assign super-admin role','assign:super-admin-role','web','2022-10-18 10:53:55','2022-10-18 10:53:55'),(12,'création permission','create:permission','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(13,'read permission','read:permission','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(14,'update Permission','update:permission','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(15,'delete permission','delete:permission','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(16,'read activity-log','read:activity-log','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(17,'création titre','create:titre','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(18,'read titre','read:titre','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(19,'update titre','update:titre','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(20,'delete titre','delete:titre','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(21,'validate titre','validate:titre','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(22,'création ligne','create:ligne','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(23,'read ligne','read:ligne','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(24,'update ligne','update:ligne','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(25,'delete ligne','delete:ligne','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(26,'validate ligne','validate:ligne','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(27,'création entite','create:entite','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(28,'read entite','read:entite','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(29,'update entite','update:entite','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(30,'delete entite','delete:entite','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(31,'création modif','create:modif','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(32,'read modif','read:modif','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(33,'update modif','update:modif','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(34,'delete modif','delete:modif','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(35,'création compte','create:compte','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(36,'read compte','read:compte','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(37,'update compte','update:compte','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(38,'delete compte','delete:compte','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(39,'création source','create:source','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(40,'read source','read:source','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(41,'update source','update:source','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(42,'delete source','delete:source','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(43,'création exercice','create:exercice','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(44,'read exercice','read:exercice','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(45,'update exercice','update:exercice','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(46,'delete exercice','delete:exercice','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(47,'validate exercice','validate:exercice','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(48,'création budget','create:budget','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(49,'read budget','read:budget','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(50,'update budget','update:budget','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(51,'delete budget','delete:budget','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(52,'création extrafield','create:extrafield','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(53,'read extrafield','read:extrafield','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(54,'update extrafield','update:extrafield','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(55,'delete extrafield','delete:extrafield','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(56,'c mode','create:mode-reglement','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(57,'read mode ','read:mode-reglement','web','2022-10-18 10:53:56','2022-10-18 10:53:56'),(58,'update mode','update:mode-reglement','web','2022-10-18 10:53:57','2022-10-18 10:53:57'),(59,'delete mode','delete:mode-reglement','web','2022-10-18 10:53:57','2022-10-18 10:53:57'),(60,'création transaction','create:transaction','web','2022-10-18 10:53:57','2022-10-18 10:53:57'),(61,'read transaction','read:transaction','web','2022-10-18 10:53:57','2022-10-18 10:53:57'),(62,'update transaction','update:transaction','web','2022-10-18 10:53:57','2022-10-18 10:53:57'),(63,'delete transaction','delete:transaction','web','2022-10-18 10:53:57','2022-10-18 10:53:57'),(64,'validate transaction','validate:transaction','web','2022-10-18 10:53:57','2022-10-18 10:53:57'),(65,'read admin','read:admin','web','2022-10-18 10:53:58','2022-10-18 10:53:58'),(66,'update admin','update:admin','web','2022-10-18 10:53:58','2022-10-18 10:53:58'),(67,'N/A','N/A','web','2022-10-18 10:53:58','2022-10-18 10:53:58');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(1,2),(2,2),(3,2),(4,2),(10,2),(17,2),(18,2),(19,2),(20,2),(22,2),(23,2),(24,2),(25,2),(27,2),(28,2),(29,2),(30,2),(31,2),(32,2),(33,2),(34,2),(35,2),(36,2),(37,2),(38,2),(39,2),(40,2),(41,2),(42,2),(43,2),(44,2),(45,2),(46,2),(48,2),(49,2),(50,2),(51,2),(56,2),(57,2),(58,2),(59,2),(60,2),(61,2),(62,2),(63,2),(17,3),(18,3),(19,3),(20,3),(22,3),(23,3),(24,3),(25,3),(27,3),(28,3),(29,3),(30,3),(31,3),(32,3),(33,3),(34,3),(35,3),(36,3),(37,3),(38,3),(39,3),(40,3),(41,3),(42,3),(43,3),(44,3),(45,3),(46,3),(47,3),(48,3),(49,3),(50,3),(51,3),(56,3),(57,3),(58,3),(59,3),(60,3),(61,3),(62,3),(63,3),(67,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super-admin','web','2022-10-18 10:53:58','2022-10-18 10:53:58'),(2,'admin','web','2022-10-18 10:53:58','2022-10-18 10:53:58'),(3,'manager','web','2022-10-18 10:53:58','2022-10-18 10:53:58'),(4,'user','web','2022-10-18 10:53:58','2022-10-18 10:53:58');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sources`
--

DROP TABLE IF EXISTS `sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sources`
--

LOCK TABLES `sources` WRITE;
/*!40000 ALTER TABLE `sources` DISABLE KEYS */;
INSERT INTO `sources` VALUES (1,'FP','Fonds propres',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(2,'RB','Reports budgétaires',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(3,'ETAT','Dotation de l\'État',NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL);
/*!40000 ALTER TABLE `sources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `titres`
--

DROP TABLE IF EXISTS `titres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `titres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Imputation',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `seuil` double(8,2) DEFAULT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `titres`
--

LOCK TABLES `titres` WRITE;
/*!40000 ALTER TABLE `titres` DISABLE KEYS */;
INSERT INTO `titres` VALUES (1,'','SECTION 1 : RECETTES',NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(2,'60','Dépenses : Achats de biens et de produits la a consommation',NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(3,'61','Dépenses : Achats de services',NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(4,'62','Dépenses : Services banquiers',NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(5,'64','Dépenses : Transferts courants',NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(6,'65','Dépenses : Autres droits',NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(7,'66','Dépenses : Charges de personnel',NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL),(8,'','SECTION D\'INVESTISSEMENT : RECETTES',NULL,NULL,NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',NULL);
/*!40000 ALTER TABLE `titres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_details`
--

DROP TABLE IF EXISTS `transaction_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `montant` double(8,2) NOT NULL,
  `montant_lettres` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `transaction_id` bigint unsigned DEFAULT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_details`
--

LOCK TABLES `transaction_details` WRITE;
/*!40000 ALTER TABLE `transaction_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` smallint NOT NULL DEFAULT '0',
  `status` smallint DEFAULT '2',
  `montant` double(8,2) NOT NULL,
  `montant_restant` double(8,2) NOT NULL DEFAULT '0.00',
  `montant_lettres` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_restant_lettres` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL DEFAULT '2022-10-18',
  `devise` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiaire_externe` tinyint(1) NOT NULL DEFAULT '1',
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `isvalide` tinyint(1) DEFAULT NULL,
  `entite_id` bigint unsigned DEFAULT NULL,
  `compte_id` bigint unsigned DEFAULT NULL,
  `ligne_id` bigint unsigned DEFAULT NULL,
  `exercice_id` bigint unsigned DEFAULT NULL,
  `mode_reglement_id` bigint unsigned NOT NULL DEFAULT '2',
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filepath` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,2,2,1000.00,0.00,NULL,NULL,'2022-10-18',NULL,NULL,1,'CA-22-04-001',NULL,NULL,NULL,NULL,1,NULL,NULL,2,NULL,NULL,NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(2,2,2,1000.00,0.00,NULL,NULL,'2022-10-18',NULL,NULL,1,'CA-22-04-002',NULL,NULL,NULL,NULL,1,NULL,NULL,2,NULL,NULL,NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(3,2,2,300.00,0.00,NULL,NULL,'2022-10-18',NULL,NULL,1,'CA-22-04-003',NULL,NULL,NULL,NULL,1,NULL,NULL,2,NULL,NULL,NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(4,0,2,500.00,0.00,NULL,NULL,'2022-10-18',NULL,NULL,1,'CD-22-04-001',NULL,NULL,NULL,NULL,1,NULL,NULL,2,NULL,NULL,NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL),(5,1,2,3000.00,0.00,NULL,NULL,'2022-10-18',NULL,NULL,1,'CR-22-04-001',NULL,NULL,NULL,NULL,2,NULL,NULL,2,NULL,NULL,NULL,NULL,'2022-10-18 10:54:01','2022-10-18 10:54:01',NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `isadmin` tinyint(1) NOT NULL DEFAULT '0',
  `code_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricule` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_jeune_fille` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexe` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `contact_perso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_quartier` bigint unsigned DEFAULT NULL,
  `id_ville` bigint unsigned DEFAULT NULL,
  `id_pays` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Admin','super@optimiz.test',NULL,'$2y$10$N5.oJiJhMpsFN6.rCMKxYurkiK0FAz5CzhteMvMPXzvNo7dS8n.GW',NULL,NULL,'2022-10-18 10:53:59','2022-10-18 10:53:59',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'Admin','admin@optimiz.test',NULL,'$2y$10$oNCiH1u0uE5pT2vVTdzJt.FYJb9QGxDu7Rj/iCfWHnnUSRAzGVmwK',NULL,NULL,'2022-10-18 10:53:59','2022-10-18 10:53:59',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'Andrée Leroux','victor.maillot@example.com','2022-10-18 10:53:59','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','MnpSHFvHxK',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,'Étienne Louis','lcoste@example.org','2022-10-18 10:53:59','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','CH7og32nRS',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,'Renée Vaillant-Chauveau','fdupuy@example.net','2022-10-18 10:53:59','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','A6INByaLN7',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,'Capucine Pires','roger68@example.net','2022-10-18 10:53:59','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','ithBuQ9euv',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,'Thomas-Noël Delattre','maurice36@example.net','2022-10-18 10:53:59','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','H94qCUaViN',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,'Lucy Le Goff','denis.launay@example.net','2022-10-18 10:53:59','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','g8ywFWHWY5',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,'Thomas Hebert','aime.barthelemy@example.com','2022-10-18 10:54:00','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','8GwMofHQgv',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,'Théodore Vallet','rousseau.richard@example.org','2022-10-18 10:54:00','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','EF5WiRT8hd',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,'Hortense Dias','orodriguez@example.org','2022-10-18 10:54:00','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','mT3uQwLftI',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(12,'Noël Gallet-Picard','laurence91@example.com','2022-10-18 10:54:00','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','bILD0Pk0as',NULL,'2022-10-18 10:54:00','2022-10-18 10:54:00',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
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

-- Dump completed on 2022-10-18 21:09:49
