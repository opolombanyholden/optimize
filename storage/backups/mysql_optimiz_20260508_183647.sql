-- MySQL dump 10.13  Distrib 8.0.44, for macos12.7 (arm64)
--
-- Host: 127.0.0.1    Database: optimiz
-- ------------------------------------------------------
-- Server version	8.0.44

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
-- Table structure for table `absences`
--

DROP TABLE IF EXISTS `absences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `absences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_abscence` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'conge_paye, maladie, mission, etc.',
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'justificatifs',
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=brouillon, 1=soumis, 2=valide, 3=rejete',
  `valide_par` bigint unsigned DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `absences_employee_id_foreign` (`employee_id`),
  KEY `absences_valide_par_foreign` (`valide_par`),
  CONSTRAINT `absences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `absences_valide_par_foreign` FOREIGN KEY (`valide_par`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absences`
--

LOCK TABLES `absences` WRITE;
/*!40000 ALTER TABLE `absences` DISABLE KEYS */;
/*!40000 ALTER TABLE `absences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `affilies`
--

DROP TABLE IF EXISTS `affilies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `affilies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `liens` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `noms` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenoms` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `contact1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` tinyint NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affilies_employee_id_foreign` (`employee_id`),
  CONSTRAINT `affilies_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `affilies`
--

LOCK TABLES `affilies` WRITE;
/*!40000 ALTER TABLE `affilies` DISABLE KEYS */;
/*!40000 ALTER TABLE `affilies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approvisionnements`
--

DROP TABLE IF EXISTS `approvisionnements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approvisionnements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fournisseur_id` bigint unsigned DEFAULT NULL,
  `demandeur_id` bigint unsigned DEFAULT NULL,
  `date_demande` date DEFAULT NULL,
  `date_livraison_souhaitee` date DEFAULT NULL,
  `montant_total` double NOT NULL DEFAULT '0',
  `priorite` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'basse, normale, haute, urgente',
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=brouillon, 1=soumis, 2=valide, 3=commande, 4=livre, 5=annule',
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approvisionnements_fournisseur_id_foreign` (`fournisseur_id`),
  KEY `approvisionnements_demandeur_id_foreign` (`demandeur_id`),
  CONSTRAINT `approvisionnements_demandeur_id_foreign` FOREIGN KEY (`demandeur_id`) REFERENCES `users` (`id`),
  CONSTRAINT `approvisionnements_fournisseur_id_foreign` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approvisionnements`
--

LOCK TABLES `approvisionnements` WRITE;
/*!40000 ALTER TABLE `approvisionnements` DISABLE KEYS */;
/*!40000 ALTER TABLE `approvisionnements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approvisionnementsproduits`
--

DROP TABLE IF EXISTS `approvisionnementsproduits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approvisionnementsproduits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `approvisionnement_id` bigint unsigned NOT NULL,
  `produit_id` bigint unsigned NOT NULL,
  `quantite_demandee` int NOT NULL DEFAULT '1',
  `quantite_recue` int NOT NULL DEFAULT '0',
  `prix_unitaire` double NOT NULL DEFAULT '0',
  `montant_ht` double NOT NULL DEFAULT '0',
  `montant_tva` double NOT NULL DEFAULT '0',
  `montant_ttc` double NOT NULL DEFAULT '0',
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approvisionnementsproduits_approvisionnement_id_foreign` (`approvisionnement_id`),
  KEY `approvisionnementsproduits_produit_id_foreign` (`produit_id`),
  CONSTRAINT `approvisionnementsproduits_approvisionnement_id_foreign` FOREIGN KEY (`approvisionnement_id`) REFERENCES `approvisionnements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approvisionnementsproduits_produit_id_foreign` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approvisionnementsproduits`
--

LOCK TABLES `approvisionnementsproduits` WRITE;
/*!40000 ALTER TABLE `approvisionnementsproduits` DISABLE KEYS */;
/*!40000 ALTER TABLE `approvisionnementsproduits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `arrondissements`
--

DROP TABLE IF EXISTS `arrondissements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `arrondissements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` int NOT NULL DEFAULT '1',
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int NOT NULL DEFAULT '1',
  `id_user` int NOT NULL DEFAULT '1',
  `effacer` int NOT NULL DEFAULT '0',
  `id_localite` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `arrondissements_id_localite_foreign` (`id_localite`),
  CONSTRAINT `arrondissements_id_localite_foreign` FOREIGN KEY (`id_localite`) REFERENCES `localites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `arrondissements`
--

LOCK TABLES `arrondissements` WRITE;
/*!40000 ALTER TABLE `arrondissements` DISABLE KEYS */;
/*!40000 ALTER TABLE `arrondissements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authentifications`
--

DROP TABLE IF EXISTS `authentifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `authentifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `operations` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'login, logout, failed',
  `lieu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authentifications`
--

LOCK TABLES `authentifications` WRITE;
/*!40000 ALTER TABLE `authentifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `authentifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_lignes`
--

DROP TABLE IF EXISTS `budget_lignes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_lignes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_budgetligne` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateeffect` timestamp NULL DEFAULT NULL,
  `id_exercicebudgetaire` bigint unsigned NOT NULL,
  `exercice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_famillecodeanalytique` bigint unsigned DEFAULT NULL COMMENT 'id titre',
  `id_codeanalytique` bigint unsigned DEFAULT NULL COMMENT 'id ligne',
  `codecompte` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `budgetligne` double NOT NULL DEFAULT '0',
  `commentaire` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attribut1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attribut2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attribut3` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isvalide` tinyint NOT NULL DEFAULT '0',
  `id_user` bigint unsigned NOT NULL,
  `effacer` tinyint NOT NULL DEFAULT '0',
  `dotation_etat` double DEFAULT NULL,
  `fonds_propres` double DEFAULT NULL,
  `reports_budgetaire` double DEFAULT NULL,
  `reports_tresorerie` double DEFAULT NULL,
  `transfert` double NOT NULL DEFAULT '0',
  `engagement` double NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `budget_lignes_id_exercicebudgetaire_foreign` (`id_exercicebudgetaire`),
  KEY `budget_lignes_id_user_foreign` (`id_user`),
  CONSTRAINT `budget_lignes_id_exercicebudgetaire_foreign` FOREIGN KEY (`id_exercicebudgetaire`) REFERENCES `exercices` (`id`),
  CONSTRAINT `budget_lignes_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_lignes`
--

LOCK TABLES `budget_lignes` WRITE;
/*!40000 ALTER TABLE `budget_lignes` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_lignes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('optimiz-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:468:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:15:\"create:exercice\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:13:\"read:exercice\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"update:exercice\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"delete:exercice\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:17:\"validate:exercice\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:15:\"export:exercice\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:13:\"create:budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:11:\"read:budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:13:\"update:budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:13:\"delete:budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:15:\"validate:budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:13:\"export:budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:17:\"create:grandlivre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:15:\"read:grandlivre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:17:\"update:grandlivre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:17:\"delete:grandlivre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:19:\"validate:grandlivre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:17:\"export:grandlivre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:13:\"create:compte\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:11:\"read:compte\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:13:\"update:compte\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:13:\"delete:compte\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:15:\"validate:compte\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:13:\"export:compte\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:12:\"create:titre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:10:\"read:titre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:12:\"update:titre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:12:\"delete:titre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:14:\"validate:titre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:12:\"export:titre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:12:\"create:ligne\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:10:\"read:ligne\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:12:\"update:ligne\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:12:\"delete:ligne\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:14:\"validate:ligne\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:12:\"export:ligne\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:30:\"create:modification_budgetaire\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:28:\"read:modification_budgetaire\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:30:\"update:modification_budgetaire\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:30:\"delete:modification_budgetaire\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:32:\"validate:modification_budgetaire\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:30:\"export:modification_budgetaire\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:18:\"create:transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:16:\"read:transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:18:\"update:transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:18:\"delete:transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:20:\"validate:transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:18:\"export:transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:15:\"create:employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:13:\"read:employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:15:\"update:employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:15:\"delete:employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:17:\"validate:employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:15:\"export:employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:14:\"create:absence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:12:\"read:absence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:14:\"update:absence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:14:\"delete:absence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:16:\"validate:absence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:14:\"export:absence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:11:\"create:paie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:9:\"read:paie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:11:\"update:paie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:11:\"delete:paie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:13:\"validate:paie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:11:\"export:paie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:17:\"create:competence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:15:\"read:competence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:17:\"update:competence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:17:\"delete:competence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:19:\"validate:competence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:17:\"export:competence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:20:\"create:qualification\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:18:\"read:qualification\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:20:\"update:qualification\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:20:\"delete:qualification\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:22:\"validate:qualification\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:20:\"export:qualification\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:16:\"create:formation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:14:\"read:formation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:16:\"update:formation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:16:\"delete:formation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:18:\"validate:formation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:16:\"export:formation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:18:\"create:recrutement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:16:\"read:recrutement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:18:\"update:recrutement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:87;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:18:\"delete:recrutement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:20:\"validate:recrutement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:89;a:4:{s:1:\"a\";i:90;s:1:\"b\";s:18:\"export:recrutement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:16:\"create:postulant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:91;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:14:\"read:postulant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:16:\"update:postulant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:93;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:16:\"delete:postulant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:18:\"validate:postulant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:95;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:16:\"export:postulant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:14:\"create:mission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:97;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:12:\"read:mission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:14:\"update:mission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:99;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:14:\"delete:mission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:16:\"validate:mission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:14:\"export:mission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:14:\"create:affilie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:12:\"read:affilie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:14:\"update:affilie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:105;a:4:{s:1:\"a\";i:106;s:1:\"b\";s:14:\"delete:affilie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:16:\"validate:affilie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:107;a:4:{s:1:\"a\";i:108;s:1:\"b\";s:14:\"export:affilie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:15:\"create:embauche\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:109;a:4:{s:1:\"a\";i:110;s:1:\"b\";s:13:\"read:embauche\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:15:\"update:embauche\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:111;a:4:{s:1:\"a\";i:112;s:1:\"b\";s:15:\"delete:embauche\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:112;a:4:{s:1:\"a\";i:113;s:1:\"b\";s:17:\"validate:embauche\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:113;a:4:{s:1:\"a\";i:114;s:1:\"b\";s:15:\"export:embauche\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:114;a:4:{s:1:\"a\";i:115;s:1:\"b\";s:18:\"create:fournisseur\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:115;a:4:{s:1:\"a\";i:116;s:1:\"b\";s:16:\"read:fournisseur\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:116;a:4:{s:1:\"a\";i:117;s:1:\"b\";s:18:\"update:fournisseur\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:117;a:4:{s:1:\"a\";i:118;s:1:\"b\";s:18:\"delete:fournisseur\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:118;a:4:{s:1:\"a\";i:119;s:1:\"b\";s:20:\"validate:fournisseur\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:119;a:4:{s:1:\"a\";i:120;s:1:\"b\";s:18:\"export:fournisseur\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:120;a:4:{s:1:\"a\";i:121;s:1:\"b\";s:14:\"create:produit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:121;a:4:{s:1:\"a\";i:122;s:1:\"b\";s:12:\"read:produit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:122;a:4:{s:1:\"a\";i:123;s:1:\"b\";s:14:\"update:produit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:123;a:4:{s:1:\"a\";i:124;s:1:\"b\";s:14:\"delete:produit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:124;a:4:{s:1:\"a\";i:125;s:1:\"b\";s:16:\"validate:produit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:125;a:4:{s:1:\"a\";i:126;s:1:\"b\";s:14:\"export:produit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:126;a:4:{s:1:\"a\";i:127;s:1:\"b\";s:15:\"create:commande\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:127;a:4:{s:1:\"a\";i:128;s:1:\"b\";s:13:\"read:commande\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:128;a:4:{s:1:\"a\";i:129;s:1:\"b\";s:15:\"update:commande\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:129;a:4:{s:1:\"a\";i:130;s:1:\"b\";s:15:\"delete:commande\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:130;a:4:{s:1:\"a\";i:131;s:1:\"b\";s:17:\"validate:commande\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:131;a:4:{s:1:\"a\";i:132;s:1:\"b\";s:15:\"export:commande\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:132;a:4:{s:1:\"a\";i:133;s:1:\"b\";s:24:\"create:approvisionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:133;a:4:{s:1:\"a\";i:134;s:1:\"b\";s:22:\"read:approvisionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:134;a:4:{s:1:\"a\";i:135;s:1:\"b\";s:24:\"update:approvisionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:135;a:4:{s:1:\"a\";i:136;s:1:\"b\";s:24:\"delete:approvisionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:136;a:4:{s:1:\"a\";i:137;s:1:\"b\";s:26:\"validate:approvisionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:137;a:4:{s:1:\"a\";i:138;s:1:\"b\";s:24:\"export:approvisionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:138;a:4:{s:1:\"a\";i:139;s:1:\"b\";s:21:\"create:immobilisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:139;a:4:{s:1:\"a\";i:140;s:1:\"b\";s:19:\"read:immobilisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:140;a:4:{s:1:\"a\";i:141;s:1:\"b\";s:21:\"update:immobilisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:141;a:4:{s:1:\"a\";i:142;s:1:\"b\";s:21:\"delete:immobilisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:142;a:4:{s:1:\"a\";i:143;s:1:\"b\";s:23:\"validate:immobilisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:143;a:4:{s:1:\"a\";i:144;s:1:\"b\";s:21:\"export:immobilisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:144;a:4:{s:1:\"a\";i:145;s:1:\"b\";s:24:\"create:dysfonctionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:145;a:4:{s:1:\"a\";i:146;s:1:\"b\";s:22:\"read:dysfonctionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:146;a:4:{s:1:\"a\";i:147;s:1:\"b\";s:24:\"update:dysfonctionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:147;a:4:{s:1:\"a\";i:148;s:1:\"b\";s:24:\"delete:dysfonctionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:148;a:4:{s:1:\"a\";i:149;s:1:\"b\";s:26:\"validate:dysfonctionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:149;a:4:{s:1:\"a\";i:150;s:1:\"b\";s:24:\"export:dysfonctionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:150;a:4:{s:1:\"a\";i:151;s:1:\"b\";s:19:\"create:intervention\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:151;a:4:{s:1:\"a\";i:152;s:1:\"b\";s:17:\"read:intervention\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:152;a:4:{s:1:\"a\";i:153;s:1:\"b\";s:19:\"update:intervention\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:153;a:4:{s:1:\"a\";i:154;s:1:\"b\";s:19:\"delete:intervention\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:154;a:4:{s:1:\"a\";i:155;s:1:\"b\";s:21:\"validate:intervention\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:155;a:4:{s:1:\"a\";i:156;s:1:\"b\";s:19:\"export:intervention\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:156;a:4:{s:1:\"a\";i:157;s:1:\"b\";s:11:\"create:user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:157;a:4:{s:1:\"a\";i:158;s:1:\"b\";s:9:\"read:user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:158;a:4:{s:1:\"a\";i:159;s:1:\"b\";s:11:\"update:user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:159;a:4:{s:1:\"a\";i:160;s:1:\"b\";s:11:\"delete:user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:160;a:4:{s:1:\"a\";i:161;s:1:\"b\";s:13:\"validate:user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:161;a:4:{s:1:\"a\";i:162;s:1:\"b\";s:11:\"export:user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:162;a:4:{s:1:\"a\";i:163;s:1:\"b\";s:11:\"create:role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:163;a:4:{s:1:\"a\";i:164;s:1:\"b\";s:9:\"read:role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:164;a:4:{s:1:\"a\";i:165;s:1:\"b\";s:11:\"update:role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:165;a:4:{s:1:\"a\";i:166;s:1:\"b\";s:11:\"delete:role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:166;a:4:{s:1:\"a\";i:167;s:1:\"b\";s:13:\"validate:role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:167;a:4:{s:1:\"a\";i:168;s:1:\"b\";s:11:\"export:role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:168;a:4:{s:1:\"a\";i:169;s:1:\"b\";s:17:\"create:permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:169;a:4:{s:1:\"a\";i:170;s:1:\"b\";s:15:\"read:permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:170;a:4:{s:1:\"a\";i:171;s:1:\"b\";s:17:\"update:permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:171;a:4:{s:1:\"a\";i:172;s:1:\"b\";s:17:\"delete:permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:172;a:4:{s:1:\"a\";i:173;s:1:\"b\";s:19:\"validate:permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:173;a:4:{s:1:\"a\";i:174;s:1:\"b\";s:17:\"export:permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:174;a:4:{s:1:\"a\";i:175;s:1:\"b\";s:19:\"create:organisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:175;a:4:{s:1:\"a\";i:176;s:1:\"b\";s:17:\"read:organisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:176;a:4:{s:1:\"a\";i:177;s:1:\"b\";s:19:\"update:organisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:177;a:4:{s:1:\"a\";i:178;s:1:\"b\";s:19:\"delete:organisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:178;a:4:{s:1:\"a\";i:179;s:1:\"b\";s:21:\"validate:organisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:179;a:4:{s:1:\"a\";i:180;s:1:\"b\";s:19:\"export:organisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:180;a:4:{s:1:\"a\";i:181;s:1:\"b\";s:13:\"create:entite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:181;a:4:{s:1:\"a\";i:182;s:1:\"b\";s:11:\"read:entite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:182;a:4:{s:1:\"a\";i:183;s:1:\"b\";s:13:\"update:entite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:183;a:4:{s:1:\"a\";i:184;s:1:\"b\";s:13:\"delete:entite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:184;a:4:{s:1:\"a\";i:185;s:1:\"b\";s:15:\"validate:entite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:185;a:4:{s:1:\"a\";i:186;s:1:\"b\";s:13:\"export:entite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:186;a:4:{s:1:\"a\";i:187;s:1:\"b\";s:13:\"create:config\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:187;a:4:{s:1:\"a\";i:188;s:1:\"b\";s:11:\"read:config\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:188;a:4:{s:1:\"a\";i:189;s:1:\"b\";s:13:\"update:config\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:189;a:4:{s:1:\"a\";i:190;s:1:\"b\";s:13:\"delete:config\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:190;a:4:{s:1:\"a\";i:191;s:1:\"b\";s:15:\"validate:config\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:191;a:4:{s:1:\"a\";i:192;s:1:\"b\";s:13:\"export:config\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:192;a:4:{s:1:\"a\";i:193;s:1:\"b\";s:16:\"publish:exercice\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:193;a:4:{s:1:\"a\";i:194;s:1:\"b\";s:14:\"publish:budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:194;a:4:{s:1:\"a\";i:195;s:1:\"b\";s:18:\"publish:grandlivre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:195;a:4:{s:1:\"a\";i:196;s:1:\"b\";s:14:\"publish:compte\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:196;a:4:{s:1:\"a\";i:197;s:1:\"b\";s:13:\"publish:titre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:197;a:4:{s:1:\"a\";i:198;s:1:\"b\";s:13:\"publish:ligne\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:198;a:4:{s:1:\"a\";i:199;s:1:\"b\";s:31:\"publish:modification_budgetaire\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:199;a:4:{s:1:\"a\";i:200;s:1:\"b\";s:19:\"publish:transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:200;a:4:{s:1:\"a\";i:201;s:1:\"b\";s:16:\"publish:employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:201;a:4:{s:1:\"a\";i:202;s:1:\"b\";s:15:\"publish:absence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:202;a:4:{s:1:\"a\";i:203;s:1:\"b\";s:12:\"publish:paie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:203;a:4:{s:1:\"a\";i:204;s:1:\"b\";s:18:\"publish:competence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:204;a:4:{s:1:\"a\";i:205;s:1:\"b\";s:21:\"publish:qualification\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:205;a:4:{s:1:\"a\";i:206;s:1:\"b\";s:17:\"publish:formation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:206;a:4:{s:1:\"a\";i:207;s:1:\"b\";s:19:\"publish:recrutement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:207;a:4:{s:1:\"a\";i:208;s:1:\"b\";s:17:\"publish:postulant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:208;a:4:{s:1:\"a\";i:209;s:1:\"b\";s:15:\"publish:mission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:209;a:4:{s:1:\"a\";i:210;s:1:\"b\";s:15:\"publish:affilie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:210;a:4:{s:1:\"a\";i:211;s:1:\"b\";s:16:\"publish:embauche\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:211;a:4:{s:1:\"a\";i:212;s:1:\"b\";s:19:\"publish:fournisseur\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:212;a:4:{s:1:\"a\";i:213;s:1:\"b\";s:15:\"publish:produit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:213;a:4:{s:1:\"a\";i:214;s:1:\"b\";s:16:\"publish:commande\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:214;a:4:{s:1:\"a\";i:215;s:1:\"b\";s:25:\"publish:approvisionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:215;a:4:{s:1:\"a\";i:216;s:1:\"b\";s:22:\"publish:immobilisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:216;a:4:{s:1:\"a\";i:217;s:1:\"b\";s:25:\"publish:dysfonctionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:217;a:4:{s:1:\"a\";i:218;s:1:\"b\";s:20:\"publish:intervention\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:218;a:4:{s:1:\"a\";i:219;s:1:\"b\";s:12:\"publish:user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:219;a:4:{s:1:\"a\";i:220;s:1:\"b\";s:12:\"publish:role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:220;a:4:{s:1:\"a\";i:221;s:1:\"b\";s:18:\"publish:permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:221;a:4:{s:1:\"a\";i:222;s:1:\"b\";s:20:\"publish:organisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:222;a:4:{s:1:\"a\";i:223;s:1:\"b\";s:14:\"publish:entite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:223;a:4:{s:1:\"a\";i:224;s:1:\"b\";s:14:\"publish:config\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:224;a:4:{s:1:\"a\";i:225;s:1:\"b\";s:14:\"create:annonce\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:225;a:4:{s:1:\"a\";i:226;s:1:\"b\";s:12:\"read:annonce\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:226;a:4:{s:1:\"a\";i:227;s:1:\"b\";s:14:\"update:annonce\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:227;a:4:{s:1:\"a\";i:228;s:1:\"b\";s:14:\"delete:annonce\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:228;a:4:{s:1:\"a\";i:229;s:1:\"b\";s:16:\"validate:annonce\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:229;a:4:{s:1:\"a\";i:230;s:1:\"b\";s:14:\"export:annonce\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:230;a:4:{s:1:\"a\";i:231;s:1:\"b\";s:15:\"publish:annonce\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:231;a:4:{s:1:\"a\";i:232;s:1:\"b\";s:11:\"create:news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:232;a:4:{s:1:\"a\";i:233;s:1:\"b\";s:9:\"read:news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:233;a:4:{s:1:\"a\";i:234;s:1:\"b\";s:11:\"update:news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:234;a:4:{s:1:\"a\";i:235;s:1:\"b\";s:11:\"delete:news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:235;a:4:{s:1:\"a\";i:236;s:1:\"b\";s:13:\"validate:news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:236;a:4:{s:1:\"a\";i:237;s:1:\"b\";s:11:\"export:news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:237;a:4:{s:1:\"a\";i:238;s:1:\"b\";s:12:\"publish:news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:238;a:4:{s:1:\"a\";i:239;s:1:\"b\";s:16:\"create:evenement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:239;a:4:{s:1:\"a\";i:240;s:1:\"b\";s:14:\"read:evenement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:240;a:4:{s:1:\"a\";i:241;s:1:\"b\";s:16:\"update:evenement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:241;a:4:{s:1:\"a\";i:242;s:1:\"b\";s:16:\"delete:evenement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:242;a:4:{s:1:\"a\";i:243;s:1:\"b\";s:18:\"validate:evenement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:243;a:4:{s:1:\"a\";i:244;s:1:\"b\";s:16:\"export:evenement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:244;a:4:{s:1:\"a\";i:245;s:1:\"b\";s:17:\"publish:evenement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:245;a:4:{s:1:\"a\";i:246;s:1:\"b\";s:15:\"create:courrier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:246;a:4:{s:1:\"a\";i:247;s:1:\"b\";s:13:\"read:courrier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:247;a:4:{s:1:\"a\";i:248;s:1:\"b\";s:15:\"update:courrier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:248;a:4:{s:1:\"a\";i:249;s:1:\"b\";s:15:\"delete:courrier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:249;a:4:{s:1:\"a\";i:250;s:1:\"b\";s:17:\"validate:courrier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:250;a:4:{s:1:\"a\";i:251;s:1:\"b\";s:15:\"export:courrier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:251;a:4:{s:1:\"a\";i:252;s:1:\"b\";s:16:\"publish:courrier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:252;a:4:{s:1:\"a\";i:253;s:1:\"b\";s:16:\"create:ressource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:253;a:4:{s:1:\"a\";i:254;s:1:\"b\";s:14:\"read:ressource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:254;a:4:{s:1:\"a\";i:255;s:1:\"b\";s:16:\"update:ressource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:255;a:4:{s:1:\"a\";i:256;s:1:\"b\";s:16:\"delete:ressource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:256;a:4:{s:1:\"a\";i:257;s:1:\"b\";s:18:\"validate:ressource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:257;a:4:{s:1:\"a\";i:258;s:1:\"b\";s:16:\"export:ressource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:258;a:4:{s:1:\"a\";i:259;s:1:\"b\";s:17:\"publish:ressource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:259;a:4:{s:1:\"a\";i:260;s:1:\"b\";s:12:\"create:media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:260;a:4:{s:1:\"a\";i:261;s:1:\"b\";s:10:\"read:media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:261;a:4:{s:1:\"a\";i:262;s:1:\"b\";s:12:\"update:media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:262;a:4:{s:1:\"a\";i:263;s:1:\"b\";s:12:\"delete:media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:263;a:4:{s:1:\"a\";i:264;s:1:\"b\";s:14:\"validate:media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:264;a:4:{s:1:\"a\";i:265;s:1:\"b\";s:12:\"export:media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:265;a:4:{s:1:\"a\";i:266;s:1:\"b\";s:13:\"publish:media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:266;a:4:{s:1:\"a\";i:267;s:1:\"b\";s:14:\"create:archive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:267;a:4:{s:1:\"a\";i:268;s:1:\"b\";s:12:\"read:archive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:268;a:4:{s:1:\"a\";i:269;s:1:\"b\";s:14:\"update:archive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:269;a:4:{s:1:\"a\";i:270;s:1:\"b\";s:14:\"delete:archive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:270;a:4:{s:1:\"a\";i:271;s:1:\"b\";s:16:\"validate:archive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:271;a:4:{s:1:\"a\";i:272;s:1:\"b\";s:14:\"export:archive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:272;a:4:{s:1:\"a\";i:273;s:1:\"b\";s:15:\"publish:archive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:273;a:4:{s:1:\"a\";i:274;s:1:\"b\";s:15:\"create:template\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:274;a:4:{s:1:\"a\";i:275;s:1:\"b\";s:13:\"read:template\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:275;a:4:{s:1:\"a\";i:276;s:1:\"b\";s:15:\"update:template\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:276;a:4:{s:1:\"a\";i:277;s:1:\"b\";s:15:\"delete:template\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:277;a:4:{s:1:\"a\";i:278;s:1:\"b\";s:17:\"validate:template\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:278;a:4:{s:1:\"a\";i:279;s:1:\"b\";s:15:\"export:template\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:279;a:4:{s:1:\"a\";i:280;s:1:\"b\";s:16:\"publish:template\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:280;a:4:{s:1:\"a\";i:281;s:1:\"b\";s:22:\"create:projet_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:281;a:4:{s:1:\"a\";i:282;s:1:\"b\";s:20:\"read:projet_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:282;a:4:{s:1:\"a\";i:283;s:1:\"b\";s:22:\"update:projet_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:283;a:4:{s:1:\"a\";i:284;s:1:\"b\";s:22:\"delete:projet_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:284;a:4:{s:1:\"a\";i:285;s:1:\"b\";s:24:\"validate:projet_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:285;a:4:{s:1:\"a\";i:286;s:1:\"b\";s:22:\"export:projet_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:286;a:4:{s:1:\"a\";i:287;s:1:\"b\";s:23:\"publish:projet_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:287;a:4:{s:1:\"a\";i:288;s:1:\"b\";s:21:\"create:tache_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:288;a:4:{s:1:\"a\";i:289;s:1:\"b\";s:19:\"read:tache_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:289;a:4:{s:1:\"a\";i:290;s:1:\"b\";s:21:\"update:tache_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:290;a:4:{s:1:\"a\";i:291;s:1:\"b\";s:21:\"delete:tache_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:291;a:4:{s:1:\"a\";i:292;s:1:\"b\";s:23:\"validate:tache_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:292;a:4:{s:1:\"a\";i:293;s:1:\"b\";s:21:\"export:tache_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:293;a:4:{s:1:\"a\";i:294;s:1:\"b\";s:22:\"publish:tache_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:294;a:4:{s:1:\"a\";i:295;s:1:\"b\";s:14:\"create:rapport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:295;a:4:{s:1:\"a\";i:296;s:1:\"b\";s:12:\"read:rapport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:296;a:4:{s:1:\"a\";i:297;s:1:\"b\";s:14:\"update:rapport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:297;a:4:{s:1:\"a\";i:298;s:1:\"b\";s:14:\"delete:rapport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:298;a:4:{s:1:\"a\";i:299;s:1:\"b\";s:16:\"validate:rapport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:299;a:4:{s:1:\"a\";i:300;s:1:\"b\";s:14:\"export:rapport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:300;a:4:{s:1:\"a\";i:301;s:1:\"b\";s:15:\"publish:rapport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:301;a:4:{s:1:\"a\";i:302;s:1:\"b\";s:15:\"create:objectif\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:302;a:4:{s:1:\"a\";i:303;s:1:\"b\";s:13:\"read:objectif\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:303;a:4:{s:1:\"a\";i:304;s:1:\"b\";s:15:\"update:objectif\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:304;a:4:{s:1:\"a\";i:305;s:1:\"b\";s:15:\"delete:objectif\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:305;a:4:{s:1:\"a\";i:306;s:1:\"b\";s:17:\"validate:objectif\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:306;a:4:{s:1:\"a\";i:307;s:1:\"b\";s:15:\"export:objectif\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:307;a:4:{s:1:\"a\";i:308;s:1:\"b\";s:16:\"publish:objectif\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:308;a:4:{s:1:\"a\";i:309;s:1:\"b\";s:10:\"create:kpi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:309;a:4:{s:1:\"a\";i:310;s:1:\"b\";s:8:\"read:kpi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:310;a:4:{s:1:\"a\";i:311;s:1:\"b\";s:10:\"update:kpi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:311;a:4:{s:1:\"a\";i:312;s:1:\"b\";s:10:\"delete:kpi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:312;a:4:{s:1:\"a\";i:313;s:1:\"b\";s:12:\"validate:kpi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:313;a:4:{s:1:\"a\";i:314;s:1:\"b\";s:10:\"export:kpi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:314;a:4:{s:1:\"a\";i:315;s:1:\"b\";s:11:\"publish:kpi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:315;a:4:{s:1:\"a\";i:316;s:1:\"b\";s:17:\"create:evaluation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:316;a:4:{s:1:\"a\";i:317;s:1:\"b\";s:15:\"read:evaluation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:317;a:4:{s:1:\"a\";i:318;s:1:\"b\";s:17:\"update:evaluation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:318;a:4:{s:1:\"a\";i:319;s:1:\"b\";s:17:\"delete:evaluation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:319;a:4:{s:1:\"a\";i:320;s:1:\"b\";s:19:\"validate:evaluation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:320;a:4:{s:1:\"a\";i:321;s:1:\"b\";s:17:\"export:evaluation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:321;a:4:{s:1:\"a\";i:322;s:1:\"b\";s:18:\"publish:evaluation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:322;a:4:{s:1:\"a\";i:323;s:1:\"b\";s:11:\"create:wiki\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:323;a:4:{s:1:\"a\";i:324;s:1:\"b\";s:9:\"read:wiki\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:324;a:4:{s:1:\"a\";i:325;s:1:\"b\";s:11:\"update:wiki\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:325;a:4:{s:1:\"a\";i:326;s:1:\"b\";s:11:\"delete:wiki\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:326;a:4:{s:1:\"a\";i:327;s:1:\"b\";s:13:\"validate:wiki\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:327;a:4:{s:1:\"a\";i:328;s:1:\"b\";s:11:\"export:wiki\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:328;a:4:{s:1:\"a\";i:329;s:1:\"b\";s:12:\"publish:wiki\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:329;a:4:{s:1:\"a\";i:330;s:1:\"b\";s:14:\"create:contact\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:330;a:4:{s:1:\"a\";i:331;s:1:\"b\";s:12:\"read:contact\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:331;a:4:{s:1:\"a\";i:332;s:1:\"b\";s:14:\"update:contact\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:332;a:4:{s:1:\"a\";i:333;s:1:\"b\";s:14:\"delete:contact\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:333;a:4:{s:1:\"a\";i:334;s:1:\"b\";s:16:\"validate:contact\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:334;a:4:{s:1:\"a\";i:335;s:1:\"b\";s:14:\"export:contact\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:335;a:4:{s:1:\"a\";i:336;s:1:\"b\";s:15:\"publish:contact\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:336;a:4:{s:1:\"a\";i:337;s:1:\"b\";s:23:\"create:organisation_crm\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:337;a:4:{s:1:\"a\";i:338;s:1:\"b\";s:21:\"read:organisation_crm\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:338;a:4:{s:1:\"a\";i:339;s:1:\"b\";s:23:\"update:organisation_crm\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:339;a:4:{s:1:\"a\";i:340;s:1:\"b\";s:23:\"delete:organisation_crm\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:340;a:4:{s:1:\"a\";i:341;s:1:\"b\";s:25:\"validate:organisation_crm\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:341;a:4:{s:1:\"a\";i:342;s:1:\"b\";s:23:\"export:organisation_crm\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:342;a:4:{s:1:\"a\";i:343;s:1:\"b\";s:24:\"publish:organisation_crm\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:343;a:4:{s:1:\"a\";i:344;s:1:\"b\";s:18:\"create:opportunite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:344;a:4:{s:1:\"a\";i:345;s:1:\"b\";s:16:\"read:opportunite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:345;a:4:{s:1:\"a\";i:346;s:1:\"b\";s:18:\"update:opportunite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:346;a:4:{s:1:\"a\";i:347;s:1:\"b\";s:18:\"delete:opportunite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:347;a:4:{s:1:\"a\";i:348;s:1:\"b\";s:20:\"validate:opportunite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:348;a:4:{s:1:\"a\";i:349;s:1:\"b\";s:18:\"export:opportunite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:349;a:4:{s:1:\"a\";i:350;s:1:\"b\";s:19:\"publish:opportunite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:350;a:4:{s:1:\"a\";i:351;s:1:\"b\";s:13:\"create:groupe\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:351;a:4:{s:1:\"a\";i:352;s:1:\"b\";s:11:\"read:groupe\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:352;a:4:{s:1:\"a\";i:353;s:1:\"b\";s:13:\"update:groupe\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:353;a:4:{s:1:\"a\";i:354;s:1:\"b\";s:13:\"delete:groupe\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:354;a:4:{s:1:\"a\";i:355;s:1:\"b\";s:15:\"validate:groupe\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:355;a:4:{s:1:\"a\";i:356;s:1:\"b\";s:13:\"export:groupe\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:356;a:4:{s:1:\"a\";i:357;s:1:\"b\";s:14:\"publish:groupe\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:357;a:4:{s:1:\"a\";i:358;s:1:\"b\";s:30:\"create:evenement_participation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:358;a:4:{s:1:\"a\";i:359;s:1:\"b\";s:28:\"read:evenement_participation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:359;a:4:{s:1:\"a\";i:360;s:1:\"b\";s:30:\"update:evenement_participation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:360;a:4:{s:1:\"a\";i:361;s:1:\"b\";s:30:\"delete:evenement_participation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:361;a:4:{s:1:\"a\";i:362;s:1:\"b\";s:32:\"validate:evenement_participation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:362;a:4:{s:1:\"a\";i:363;s:1:\"b\";s:30:\"export:evenement_participation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:363;a:4:{s:1:\"a\";i:364;s:1:\"b\";s:31:\"publish:evenement_participation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:364;a:4:{s:1:\"a\";i:365;s:1:\"b\";s:15:\"assign:exercice\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:365;a:4:{s:1:\"a\";i:366;s:1:\"b\";s:16:\"process:exercice\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:366;a:4:{s:1:\"a\";i:367;s:1:\"b\";s:13:\"assign:budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:367;a:4:{s:1:\"a\";i:368;s:1:\"b\";s:14:\"process:budget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:368;a:4:{s:1:\"a\";i:369;s:1:\"b\";s:17:\"assign:grandlivre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:369;a:4:{s:1:\"a\";i:370;s:1:\"b\";s:18:\"process:grandlivre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:370;a:4:{s:1:\"a\";i:371;s:1:\"b\";s:13:\"assign:compte\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:371;a:4:{s:1:\"a\";i:372;s:1:\"b\";s:14:\"process:compte\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:372;a:4:{s:1:\"a\";i:373;s:1:\"b\";s:12:\"assign:titre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:373;a:4:{s:1:\"a\";i:374;s:1:\"b\";s:13:\"process:titre\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:374;a:4:{s:1:\"a\";i:375;s:1:\"b\";s:12:\"assign:ligne\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:375;a:4:{s:1:\"a\";i:376;s:1:\"b\";s:13:\"process:ligne\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:376;a:4:{s:1:\"a\";i:377;s:1:\"b\";s:30:\"assign:modification_budgetaire\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:377;a:4:{s:1:\"a\";i:378;s:1:\"b\";s:31:\"process:modification_budgetaire\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:378;a:4:{s:1:\"a\";i:379;s:1:\"b\";s:18:\"assign:transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:379;a:4:{s:1:\"a\";i:380;s:1:\"b\";s:19:\"process:transaction\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:380;a:4:{s:1:\"a\";i:381;s:1:\"b\";s:15:\"assign:employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:381;a:4:{s:1:\"a\";i:382;s:1:\"b\";s:16:\"process:employee\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:382;a:4:{s:1:\"a\";i:383;s:1:\"b\";s:14:\"assign:absence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:383;a:4:{s:1:\"a\";i:384;s:1:\"b\";s:15:\"process:absence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:384;a:4:{s:1:\"a\";i:385;s:1:\"b\";s:11:\"assign:paie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:385;a:4:{s:1:\"a\";i:386;s:1:\"b\";s:12:\"process:paie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:386;a:4:{s:1:\"a\";i:387;s:1:\"b\";s:17:\"assign:competence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:387;a:4:{s:1:\"a\";i:388;s:1:\"b\";s:18:\"process:competence\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:388;a:4:{s:1:\"a\";i:389;s:1:\"b\";s:20:\"assign:qualification\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:389;a:4:{s:1:\"a\";i:390;s:1:\"b\";s:21:\"process:qualification\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:390;a:4:{s:1:\"a\";i:391;s:1:\"b\";s:16:\"assign:formation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:391;a:4:{s:1:\"a\";i:392;s:1:\"b\";s:17:\"process:formation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:392;a:4:{s:1:\"a\";i:393;s:1:\"b\";s:18:\"assign:recrutement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:393;a:4:{s:1:\"a\";i:394;s:1:\"b\";s:19:\"process:recrutement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:394;a:4:{s:1:\"a\";i:395;s:1:\"b\";s:16:\"assign:postulant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:395;a:4:{s:1:\"a\";i:396;s:1:\"b\";s:17:\"process:postulant\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:396;a:4:{s:1:\"a\";i:397;s:1:\"b\";s:14:\"assign:mission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:397;a:4:{s:1:\"a\";i:398;s:1:\"b\";s:15:\"process:mission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:398;a:4:{s:1:\"a\";i:399;s:1:\"b\";s:14:\"assign:affilie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:399;a:4:{s:1:\"a\";i:400;s:1:\"b\";s:15:\"process:affilie\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:400;a:4:{s:1:\"a\";i:401;s:1:\"b\";s:15:\"assign:embauche\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:401;a:4:{s:1:\"a\";i:402;s:1:\"b\";s:16:\"process:embauche\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:402;a:4:{s:1:\"a\";i:403;s:1:\"b\";s:18:\"assign:fournisseur\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:403;a:4:{s:1:\"a\";i:404;s:1:\"b\";s:19:\"process:fournisseur\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:404;a:4:{s:1:\"a\";i:405;s:1:\"b\";s:14:\"assign:produit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:405;a:4:{s:1:\"a\";i:406;s:1:\"b\";s:15:\"process:produit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:406;a:4:{s:1:\"a\";i:407;s:1:\"b\";s:15:\"assign:commande\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:407;a:4:{s:1:\"a\";i:408;s:1:\"b\";s:16:\"process:commande\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:408;a:4:{s:1:\"a\";i:409;s:1:\"b\";s:24:\"assign:approvisionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:409;a:4:{s:1:\"a\";i:410;s:1:\"b\";s:25:\"process:approvisionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:410;a:4:{s:1:\"a\";i:411;s:1:\"b\";s:21:\"assign:immobilisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:411;a:4:{s:1:\"a\";i:412;s:1:\"b\";s:22:\"process:immobilisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:412;a:4:{s:1:\"a\";i:413;s:1:\"b\";s:24:\"assign:dysfonctionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:413;a:4:{s:1:\"a\";i:414;s:1:\"b\";s:25:\"process:dysfonctionnement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:414;a:4:{s:1:\"a\";i:415;s:1:\"b\";s:19:\"assign:intervention\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:415;a:4:{s:1:\"a\";i:416;s:1:\"b\";s:20:\"process:intervention\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:416;a:4:{s:1:\"a\";i:417;s:1:\"b\";s:11:\"assign:user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:417;a:4:{s:1:\"a\";i:418;s:1:\"b\";s:12:\"process:user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:418;a:4:{s:1:\"a\";i:419;s:1:\"b\";s:11:\"assign:role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:419;a:4:{s:1:\"a\";i:420;s:1:\"b\";s:12:\"process:role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:420;a:4:{s:1:\"a\";i:421;s:1:\"b\";s:17:\"assign:permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:421;a:4:{s:1:\"a\";i:422;s:1:\"b\";s:18:\"process:permission\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:422;a:4:{s:1:\"a\";i:423;s:1:\"b\";s:19:\"assign:organisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:423;a:4:{s:1:\"a\";i:424;s:1:\"b\";s:20:\"process:organisation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:424;a:4:{s:1:\"a\";i:425;s:1:\"b\";s:13:\"assign:entite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:425;a:4:{s:1:\"a\";i:426;s:1:\"b\";s:14:\"process:entite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:426;a:4:{s:1:\"a\";i:427;s:1:\"b\";s:13:\"assign:config\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:427;a:4:{s:1:\"a\";i:428;s:1:\"b\";s:14:\"process:config\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:428;a:4:{s:1:\"a\";i:429;s:1:\"b\";s:14:\"assign:annonce\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:429;a:4:{s:1:\"a\";i:430;s:1:\"b\";s:15:\"process:annonce\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:430;a:4:{s:1:\"a\";i:431;s:1:\"b\";s:11:\"assign:news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:431;a:4:{s:1:\"a\";i:432;s:1:\"b\";s:12:\"process:news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:432;a:4:{s:1:\"a\";i:433;s:1:\"b\";s:16:\"assign:evenement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:433;a:4:{s:1:\"a\";i:434;s:1:\"b\";s:17:\"process:evenement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:434;a:4:{s:1:\"a\";i:435;s:1:\"b\";s:30:\"assign:evenement_participation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:435;a:4:{s:1:\"a\";i:436;s:1:\"b\";s:31:\"process:evenement_participation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:436;a:4:{s:1:\"a\";i:437;s:1:\"b\";s:15:\"assign:courrier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:437;a:4:{s:1:\"a\";i:438;s:1:\"b\";s:16:\"process:courrier\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:438;a:4:{s:1:\"a\";i:439;s:1:\"b\";s:16:\"assign:ressource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:439;a:4:{s:1:\"a\";i:440;s:1:\"b\";s:17:\"process:ressource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:440;a:4:{s:1:\"a\";i:441;s:1:\"b\";s:12:\"assign:media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:441;a:4:{s:1:\"a\";i:442;s:1:\"b\";s:13:\"process:media\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:442;a:4:{s:1:\"a\";i:443;s:1:\"b\";s:14:\"assign:archive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:443;a:4:{s:1:\"a\";i:444;s:1:\"b\";s:15:\"process:archive\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:444;a:4:{s:1:\"a\";i:445;s:1:\"b\";s:15:\"assign:template\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:445;a:4:{s:1:\"a\";i:446;s:1:\"b\";s:16:\"process:template\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:446;a:4:{s:1:\"a\";i:447;s:1:\"b\";s:22:\"assign:projet_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:447;a:4:{s:1:\"a\";i:448;s:1:\"b\";s:23:\"process:projet_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:448;a:4:{s:1:\"a\";i:449;s:1:\"b\";s:21:\"assign:tache_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:449;a:4:{s:1:\"a\";i:450;s:1:\"b\";s:22:\"process:tache_intranet\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:450;a:4:{s:1:\"a\";i:451;s:1:\"b\";s:14:\"assign:rapport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:451;a:4:{s:1:\"a\";i:452;s:1:\"b\";s:15:\"process:rapport\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:452;a:4:{s:1:\"a\";i:453;s:1:\"b\";s:15:\"assign:objectif\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:453;a:4:{s:1:\"a\";i:454;s:1:\"b\";s:16:\"process:objectif\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:454;a:4:{s:1:\"a\";i:455;s:1:\"b\";s:10:\"assign:kpi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:455;a:4:{s:1:\"a\";i:456;s:1:\"b\";s:11:\"process:kpi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:456;a:4:{s:1:\"a\";i:457;s:1:\"b\";s:17:\"assign:evaluation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:457;a:4:{s:1:\"a\";i:458;s:1:\"b\";s:18:\"process:evaluation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:458;a:4:{s:1:\"a\";i:459;s:1:\"b\";s:11:\"assign:wiki\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:459;a:4:{s:1:\"a\";i:460;s:1:\"b\";s:12:\"process:wiki\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:460;a:4:{s:1:\"a\";i:461;s:1:\"b\";s:14:\"assign:contact\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:461;a:4:{s:1:\"a\";i:462;s:1:\"b\";s:15:\"process:contact\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:462;a:4:{s:1:\"a\";i:463;s:1:\"b\";s:23:\"assign:organisation_crm\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:463;a:4:{s:1:\"a\";i:464;s:1:\"b\";s:24:\"process:organisation_crm\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:464;a:4:{s:1:\"a\";i:465;s:1:\"b\";s:18:\"assign:opportunite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:465;a:4:{s:1:\"a\";i:466;s:1:\"b\";s:19:\"process:opportunite\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:466;a:4:{s:1:\"a\";i:467;s:1:\"b\";s:13:\"assign:groupe\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:467;a:4:{s:1:\"a\";i:468;s:1:\"b\";s:14:\"process:groupe\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}}s:5:\"roles\";a:4:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super-admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:7:\"manager\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:4:\"user\";s:1:\"c\";s:3:\"web\";}}}',1778266162);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `changespasswords`
--

DROP TABLE IF EXISTS `changespasswords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `changespasswords` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `users` bigint unsigned DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `changespasswords_users_foreign` (`users`),
  CONSTRAINT `changespasswords_users_foreign` FOREIGN KEY (`users`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `changespasswords`
--

LOCK TABLES `changespasswords` WRITE;
/*!40000 ALTER TABLE `changespasswords` DISABLE KEYS */;
/*!40000 ALTER TABLE `changespasswords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commande_fournisseurs`
--

DROP TABLE IF EXISTS `commande_fournisseurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commande_fournisseurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero_commande` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fournisseur_id` bigint unsigned NOT NULL,
  `approvisionnement_id` bigint unsigned DEFAULT NULL,
  `date_commande` date DEFAULT NULL,
  `date_livraison_prevue` date DEFAULT NULL,
  `date_livraison_effective` date DEFAULT NULL,
  `montant_ht` double NOT NULL DEFAULT '0',
  `montant_tva` double NOT NULL DEFAULT '0',
  `montant_ttc` double NOT NULL DEFAULT '0',
  `mode_reglement` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valideur_id` bigint unsigned DEFAULT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=brouillon, 1=soumis, 2=valide, 3=envoye, 4=livre_partiellement, 5=livre, 6=annule',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `commande_fournisseurs_numero_commande_unique` (`numero_commande`),
  KEY `commande_fournisseurs_fournisseur_id_foreign` (`fournisseur_id`),
  KEY `commande_fournisseurs_approvisionnement_id_foreign` (`approvisionnement_id`),
  KEY `commande_fournisseurs_valideur_id_foreign` (`valideur_id`),
  CONSTRAINT `commande_fournisseurs_approvisionnement_id_foreign` FOREIGN KEY (`approvisionnement_id`) REFERENCES `approvisionnements` (`id`),
  CONSTRAINT `commande_fournisseurs_fournisseur_id_foreign` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`),
  CONSTRAINT `commande_fournisseurs_valideur_id_foreign` FOREIGN KEY (`valideur_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commande_fournisseurs`
--

LOCK TABLES `commande_fournisseurs` WRITE;
/*!40000 ALTER TABLE `commande_fournisseurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `commande_fournisseurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `competences`
--

DROP TABLE IF EXISTS `competences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `competences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `niveau` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'debutant, intermediaire, expert',
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'certificats, diplomes',
  `statut` int NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `competences_employee_id_foreign` (`employee_id`),
  CONSTRAINT `competences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `competences`
--

LOCK TABLES `competences` WRITE;
/*!40000 ALTER TABLE `competences` DISABLE KEYS */;
/*!40000 ALTER TABLE `competences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comptes`
--

DROP TABLE IF EXISTS `comptes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comptes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint DEFAULT NULL COMMENT '1=banque, 2=caisse, 3=tiers',
  `entite_id` int DEFAULT NULL,
  `solde` double NOT NULL DEFAULT '0',
  `rib` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsable` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestionnaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_gestionnaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domiciliation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attribut2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attribut3` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `effacer` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comptes`
--

LOCK TABLES `comptes` WRITE;
/*!40000 ALTER TABLE `comptes` DISABLE KEYS */;
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
  `config_object` json NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configs`
--

LOCK TABLES `configs` WRITE;
/*!40000 ALTER TABLE `configs` DISABLE KEYS */;
/*!40000 ALTER TABLE `configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dysfonctionnements`
--

DROP TABLE IF EXISTS `dysfonctionnements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dysfonctionnements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type_id` bigint unsigned DEFAULT NULL,
  `declarant_id` bigint unsigned DEFAULT NULL,
  `immobilisation_id` bigint unsigned DEFAULT NULL,
  `localisation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priorite` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'basse, normale, haute, critique',
  `date_signalement` datetime DEFAULT NULL,
  `date_resolution` datetime DEFAULT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=signale, 1=pris_en_charge, 2=resolu, 3=clos',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dysfonctionnements_type_id_foreign` (`type_id`),
  KEY `dysfonctionnements_declarant_id_foreign` (`declarant_id`),
  KEY `dysfonctionnements_immobilisation_id_foreign` (`immobilisation_id`),
  CONSTRAINT `dysfonctionnements_declarant_id_foreign` FOREIGN KEY (`declarant_id`) REFERENCES `users` (`id`),
  CONSTRAINT `dysfonctionnements_immobilisation_id_foreign` FOREIGN KEY (`immobilisation_id`) REFERENCES `immobilisations` (`id`),
  CONSTRAINT `dysfonctionnements_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `typesdysfonctionnements` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dysfonctionnements`
--

LOCK TABLES `dysfonctionnements` WRITE;
/*!40000 ALTER TABLE `dysfonctionnements` DISABLE KEYS */;
/*!40000 ALTER TABLE `dysfonctionnements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `embauches`
--

DROP TABLE IF EXISTS `embauches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `embauches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valider` tinyint(1) NOT NULL DEFAULT '0',
  `postulant_id` bigint unsigned DEFAULT NULL,
  `employee_id` bigint unsigned DEFAULT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` tinyint NOT NULL DEFAULT '0',
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `embauches_postulant_id_foreign` (`postulant_id`),
  KEY `embauches_employee_id_foreign` (`employee_id`),
  CONSTRAINT `embauches_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `embauches_postulant_id_foreign` FOREIGN KEY (`postulant_id`) REFERENCES `postulants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `embauches`
--

LOCK TABLES `embauches` WRITE;
/*!40000 ALTER TABLE `embauches` DISABLE KEYS */;
/*!40000 ALTER TABLE `embauches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `noms` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenoms` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `matricule` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalite` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexe` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `situation_matrimoniale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_enfants` int NOT NULL DEFAULT '0',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_quartier` bigint unsigned DEFAULT NULL,
  `id_ville` bigint unsigned DEFAULT NULL,
  `id_pays` bigint unsigned DEFAULT NULL,
  `date_embauche` date DEFAULT NULL,
  `type_contrat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_fin_contrat` date DEFAULT NULL,
  `poste` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departement` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `superieur_hierarchique` bigint unsigned DEFAULT NULL,
  `salaire_base` double NOT NULL DEFAULT '0',
  `iban` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_secu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` int NOT NULL DEFAULT '1' COMMENT '1=actif, 2=inactif, 3=parti',
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'CV, pieces jointes',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_matricule_unique` (`matricule`),
  KEY `employees_user_id_foreign` (`user_id`),
  KEY `employees_superieur_hierarchique_foreign` (`superieur_hierarchique`),
  CONSTRAINT `employees_superieur_hierarchique_foreign` FOREIGN KEY (`superieur_hierarchique`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entites`
--

DROP TABLE IF EXISTS `entites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_localite` bigint unsigned DEFAULT NULL,
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `effacer` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entites_id_localite_foreign` (`id_localite`),
  CONSTRAINT `entites_id_localite_foreign` FOREIGN KEY (`id_localite`) REFERENCES `localites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entites`
--

LOCK TABLES `entites` WRITE;
/*!40000 ALTER TABLE `entites` DISABLE KEYS */;
/*!40000 ALTER TABLE `entites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evenementscarrieres`
--

DROP TABLE IF EXISTS `evenementscarrieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evenementscarrieres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `typesevenementscarriere_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `date_effet` date DEFAULT NULL,
  `ancien_poste` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nouveau_poste` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evenementscarrieres_typesevenementscarriere_id_foreign` (`typesevenementscarriere_id`),
  KEY `evenementscarrieres_employee_id_foreign` (`employee_id`),
  CONSTRAINT `evenementscarrieres_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evenementscarrieres_typesevenementscarriere_id_foreign` FOREIGN KEY (`typesevenementscarriere_id`) REFERENCES `typesevenementscarrieres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evenementscarrieres`
--

LOCK TABLES `evenementscarrieres` WRITE;
/*!40000 ALTER TABLE `evenementscarrieres` DISABLE KEYS */;
/*!40000 ALTER TABLE `evenementscarrieres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exercices`
--

DROP TABLE IF EXISTS `exercices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exercices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exercice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_regroupement` bigint unsigned DEFAULT NULL,
  `dateeffect` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datedebut` datetime DEFAULT NULL,
  `datefin` datetime DEFAULT NULL,
  `entite` datetime DEFAULT NULL,
  `budgetglobalinitial` double DEFAULT NULL,
  `commentaire` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attribut1` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attribut2` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attribut3` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isvalide` int NOT NULL DEFAULT '0',
  `id_user` bigint unsigned DEFAULT NULL,
  `effacer` int NOT NULL DEFAULT '0',
  `version_exercice` int NOT NULL DEFAULT '1',
  `statut` int NOT NULL DEFAULT '3' COMMENT '1=planifie, 2=en cours, 3=cloture',
  `dotationglobale` double NOT NULL DEFAULT '0',
  `fondpropreglobal` double NOT NULL DEFAULT '0',
  `reportbudgetare` double NOT NULL DEFAULT '0',
  `reporttresorerieglobal` double NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exercices_id_user_foreign` (`id_user`),
  CONSTRAINT `exercices_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exercices`
--

LOCK TABLES `exercices` WRITE;
/*!40000 ALTER TABLE `exercices` DISABLE KEYS */;
/*!40000 ALTER TABLE `exercices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `formations`
--

DROP TABLE IF EXISTS `formations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `organisme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `formations_employee_id_foreign` (`employee_id`),
  CONSTRAINT `formations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formations`
--

LOCK TABLES `formations` WRITE;
/*!40000 ALTER TABLE `formations` DISABLE KEYS */;
/*!40000 ALTER TABLE `formations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fournisseurs`
--

DROP TABLE IF EXISTS `fournisseurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fournisseurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `raison_sociale` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sigle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rccm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pays` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_web` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_telephone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rib` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banque` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domiciliation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note_evaluation` double DEFAULT NULL,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '1' COMMENT '1=actif, 0=inactif',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fournisseurs`
--

LOCK TABLES `fournisseurs` WRITE;
/*!40000 ALTER TABLE `fournisseurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `fournisseurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gpaies`
--

DROP TABLE IF EXISTS `gpaies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gpaies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `salaire_base` double NOT NULL DEFAULT '0',
  `primes` double NOT NULL DEFAULT '0',
  `indemnites` double NOT NULL DEFAULT '0',
  `heures_sup` double NOT NULL DEFAULT '0',
  `brut` double NOT NULL DEFAULT '0',
  `cotisations_salariales` double NOT NULL DEFAULT '0',
  `cotisations_patronales` double NOT NULL DEFAULT '0',
  `irpp` double NOT NULL DEFAULT '0',
  `net_imposable` double NOT NULL DEFAULT '0',
  `net_a_payer` double NOT NULL DEFAULT '0',
  `avances` double NOT NULL DEFAULT '0',
  `retenues` double NOT NULL DEFAULT '0',
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=brouillon, 1=valide, 2=paye',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gpaies_employee_id_foreign` (`employee_id`),
  CONSTRAINT `gpaies_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gpaies`
--

LOCK TABLES `gpaies` WRITE;
/*!40000 ALTER TABLE `gpaies` DISABLE KEYS */;
/*!40000 ALTER TABLE `gpaies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grand_livre_details`
--

DROP TABLE IF EXISTS `grand_livre_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grand_livre_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_facture` bigint unsigned DEFAULT NULL,
  `id_produit` bigint unsigned DEFAULT NULL,
  `prix` double DEFAULT NULL,
  `quantite` double DEFAULT NULL,
  `total_ht` double DEFAULT NULL,
  `total_taxe` double DEFAULT NULL,
  `total_reduction` double DEFAULT NULL,
  `total_ttc` double DEFAULT NULL,
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `effacer` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grand_livre_details`
--

LOCK TABLES `grand_livre_details` WRITE;
/*!40000 ALTER TABLE `grand_livre_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `grand_livre_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grand_livres`
--

DROP TABLE IF EXISTS `grand_livres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grand_livres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date_ecriture` date DEFAULT NULL,
  `id_entite` bigint unsigned NOT NULL,
  `dateeffect` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `montant_tc` double DEFAULT NULL,
  `sens` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode_reglement` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exercice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_exercicebudgetaire` bigint unsigned DEFAULT NULL,
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_user` bigint unsigned NOT NULL,
  `rib` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banque` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_beneficiaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiaire_interne` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_beneficiaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_beneficiaire_interne` bigint unsigned DEFAULT NULL,
  `libelle_piece` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_piece` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_ligne` bigint unsigned DEFAULT NULL,
  `id_titre` bigint unsigned DEFAULT NULL,
  `imputation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nature` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_piece` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_piece` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nature_piece` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_signe_tc` double DEFAULT NULL,
  `compte_id` bigint unsigned NOT NULL,
  `compte_general` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `compte_auxiliaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_tiers` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `journal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `devise` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_tr` double DEFAULT NULL,
  `code_lettrage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_marquage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_lettrage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_pointage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lettre_rappro` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_rappro` date DEFAULT NULL,
  `type_ecriture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_lot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_ecriture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_tiers` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_signe_tr` double DEFAULT NULL,
  `id_famillecodeanalytique` bigint unsigned DEFAULT NULL,
  `id_codeanalytique` bigint unsigned DEFAULT NULL,
  `id_organisation` bigint unsigned DEFAULT NULL,
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int NOT NULL DEFAULT '0',
  `effacer` int NOT NULL DEFAULT '0',
  `num_stat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_facture` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grand_livres_id_entite_foreign` (`id_entite`),
  KEY `grand_livres_id_exercicebudgetaire_foreign` (`id_exercicebudgetaire`),
  KEY `grand_livres_id_user_foreign` (`id_user`),
  KEY `grand_livres_compte_id_foreign` (`compte_id`),
  CONSTRAINT `grand_livres_compte_id_foreign` FOREIGN KEY (`compte_id`) REFERENCES `comptes` (`id`),
  CONSTRAINT `grand_livres_id_entite_foreign` FOREIGN KEY (`id_entite`) REFERENCES `entites` (`id`),
  CONSTRAINT `grand_livres_id_exercicebudgetaire_foreign` FOREIGN KEY (`id_exercicebudgetaire`) REFERENCES `exercices` (`id`),
  CONSTRAINT `grand_livres_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grand_livres`
--

LOCK TABLES `grand_livres` WRITE;
/*!40000 ALTER TABLE `grand_livres` DISABLE KEYS */;
/*!40000 ALTER TABLE `grand_livres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `immobilisations`
--

DROP TABLE IF EXISTS `immobilisations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `immobilisations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `categorie` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `localisation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_acquisition` date DEFAULT NULL,
  `valeur_acquisition` double NOT NULL DEFAULT '0',
  `valeur_nette_comptable` double NOT NULL DEFAULT '0',
  `duree_amortissement` int DEFAULT NULL COMMENT 'en mois',
  `methode_amortissement` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'lineaire, degressif',
  `etat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'neuf, bon, usage, hors_service',
  `affecte_a` bigint unsigned DEFAULT NULL,
  `entite_id` bigint unsigned DEFAULT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `immobilisations_code_unique` (`code`),
  KEY `immobilisations_affecte_a_foreign` (`affecte_a`),
  KEY `immobilisations_entite_id_foreign` (`entite_id`),
  CONSTRAINT `immobilisations_affecte_a_foreign` FOREIGN KEY (`affecte_a`) REFERENCES `employees` (`id`),
  CONSTRAINT `immobilisations_entite_id_foreign` FOREIGN KEY (`entite_id`) REFERENCES `entites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `immobilisations`
--

LOCK TABLES `immobilisations` WRITE;
/*!40000 ALTER TABLE `immobilisations` DISABLE KEYS */;
/*!40000 ALTER TABLE `immobilisations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interventions`
--

DROP TABLE IF EXISTS `interventions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interventions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dysfonctionnement_id` bigint unsigned DEFAULT NULL,
  `immobilisation_id` bigint unsigned DEFAULT NULL,
  `technicien_id` bigint unsigned DEFAULT NULL,
  `type_intervention` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'preventive, corrective, ameliorative',
  `date_planifiee` datetime DEFAULT NULL,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `cout` double DEFAULT NULL,
  `rapport` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=planifie, 1=en_cours, 2=termine, 3=annule',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `interventions_dysfonctionnement_id_foreign` (`dysfonctionnement_id`),
  KEY `interventions_immobilisation_id_foreign` (`immobilisation_id`),
  KEY `interventions_technicien_id_foreign` (`technicien_id`),
  CONSTRAINT `interventions_dysfonctionnement_id_foreign` FOREIGN KEY (`dysfonctionnement_id`) REFERENCES `dysfonctionnements` (`id`),
  CONSTRAINT `interventions_immobilisation_id_foreign` FOREIGN KEY (`immobilisation_id`) REFERENCES `immobilisations` (`id`),
  CONSTRAINT `interventions_technicien_id_foreign` FOREIGN KEY (`technicien_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interventions`
--

LOCK TABLES `interventions` WRITE;
/*!40000 ALTER TABLE `interventions` DISABLE KEYS */;
/*!40000 ALTER TABLE `interventions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_activite_checklist`
--

DROP TABLE IF EXISTS `intranet_activite_checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_activite_checklist` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `activite_id` bigint unsigned NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complete` tinyint(1) NOT NULL DEFAULT '0',
  `ordre` int NOT NULL DEFAULT '0',
  `complete_par` bigint unsigned DEFAULT NULL,
  `complete_le` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_activite_checklist_activite_id_foreign` (`activite_id`),
  KEY `intranet_activite_checklist_complete_par_foreign` (`complete_par`),
  KEY `intranet_activite_checklist_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_activite_checklist_activite_id_foreign` FOREIGN KEY (`activite_id`) REFERENCES `intranet_activites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_activite_checklist_complete_par_foreign` FOREIGN KEY (`complete_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_activite_checklist_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_activite_checklist`
--

LOCK TABLES `intranet_activite_checklist` WRITE;
/*!40000 ALTER TABLE `intranet_activite_checklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_activite_checklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_activites`
--

DROP TABLE IF EXISTS `intranet_activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_activites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tache_id` bigint unsigned NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `statut_id` bigint unsigned DEFAULT NULL,
  `priorite_id` bigint unsigned DEFAULT NULL,
  `responsable_id` bigint unsigned DEFAULT NULL,
  `duree_estimee` decimal(6,2) DEFAULT NULL,
  `duree_reelle` decimal(6,2) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `avancement` tinyint unsigned NOT NULL DEFAULT '0',
  `ordre` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_activites_tache_id_foreign` (`tache_id`),
  KEY `intranet_activites_statut_id_foreign` (`statut_id`),
  KEY `intranet_activites_priorite_id_foreign` (`priorite_id`),
  KEY `intranet_activites_responsable_id_foreign` (`responsable_id`),
  KEY `intranet_activites_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_activites_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_activites_priorite_id_foreign` FOREIGN KEY (`priorite_id`) REFERENCES `intranet_priorites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_activites_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_activites_statut_id_foreign` FOREIGN KEY (`statut_id`) REFERENCES `intranet_statuts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_activites_tache_id_foreign` FOREIGN KEY (`tache_id`) REFERENCES `intranet_taches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_activites`
--

LOCK TABLES `intranet_activites` WRITE;
/*!40000 ALTER TABLE `intranet_activites` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_activites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_annonces`
--

DROP TABLE IF EXISTS `intranet_annonces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_annonces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extrait` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `is_urgent` tinyint(1) NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `epingle` tinyint(1) NOT NULL DEFAULT '0',
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#7C3AED',
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_annonces_slug_unique` (`slug`),
  KEY `intranet_annonces_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_annonces_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_annonces`
--

LOCK TABLES `intranet_annonces` WRITE;
/*!40000 ALTER TABLE `intranet_annonces` DISABLE KEYS */;
INSERT INTO `intranet_annonces` VALUES (1,'Bienvenue sur le portail OptimiZe Intranet',NULL,NULL,NULL,'L\'intranet OptimiZe est désormais opérationnel. Retrouvez toutes les informations de l\'organisation en un seul endroit.',1,1,0,NULL,NULL,NULL,NULL,NULL,0,'#7C3AED',0,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(2,'Réunion de direction — 15 Avril 2026',NULL,NULL,NULL,'La réunion mensuelle de direction aura lieu le 15 avril 2026 à 9h00 en salle de conférence.',1,1,0,NULL,NULL,NULL,NULL,NULL,0,'#7C3AED',0,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(3,'Mise à jour de la politique de congés',NULL,NULL,NULL,'Veuillez prendre connaissance de la nouvelle politique de gestion des congés en vigueur à partir du 1er mai.',1,1,1,NULL,NULL,NULL,NULL,NULL,0,'#7C3AED',0,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(4,'Le passage de Lorem Ipsum standard, utilisé depuis 1500','le-passage-de-lorem-ipsum-standard-utilise-depuis-1500','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum','RH','<p class=\"ql-align-justify\">Le <strong>Lorem Ipsum</strong> est simplement du faux texte employé dans la composition et la mise en page avant impression. Le Lorem Ipsum est le faux texte standard de l\'imprimerie depuis les années 1500, quand un imprimeur anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte. Il n\'a pas fait que survivre cinq siècles, mais s\'est aussi adapté à la bureautique informatique, sans que son contenu n\'en soit modifié. Il a été popularisé dans les années 1960 grâce à la vente de feuilles Letraset contenant des passages du Lorem Ipsum, et, plus récemment, par son inclusion dans des applications de mise en page de texte, comme Aldus PageMaker.</p>',1,0,1,NULL,'intranet/annonces/Bf7VgSsABZKs0qVmfQpjVssxQGISEDdttY7xT5hS.png','image','2026-04-09','2026-04-10',1,'#7c3aed',10,'2026-04-09 17:31:14','2026-05-07 17:49:34',NULL);
/*!40000 ALTER TABLE `intranet_annonces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_archive_dossiers`
--

DROP TABLE IF EXISTS `intranet_archive_dossiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_archive_dossiers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` bigint unsigned DEFAULT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#4F46E5',
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couverture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_archive_dossiers_slug_unique` (`slug`),
  KEY `intranet_archive_dossiers_created_by_foreign` (`created_by`),
  KEY `intranet_archive_dossiers_parent_id_index` (`parent_id`),
  CONSTRAINT `intranet_archive_dossiers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_archive_dossiers_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `intranet_archive_dossiers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_archive_dossiers`
--

LOCK TABLES `intranet_archive_dossiers` WRITE;
/*!40000 ALTER TABLE `intranet_archive_dossiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_archive_dossiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_archives`
--

DROP TABLE IF EXISTS `intranet_archives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_archives` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille` bigint unsigned DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nature` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `lieu_physique` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_barre` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duree_conservation_mois` smallint unsigned DEFAULT NULL,
  `date_destruction_prevue` date DEFAULT NULL,
  `date_archivage` date DEFAULT NULL,
  `statut` enum('actif','semi_actif','inactif','a_detruire') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'actif',
  `telechargements_count` int unsigned NOT NULL DEFAULT '0',
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `date_document` date DEFAULT NULL,
  `dossier_id` bigint unsigned DEFAULT NULL,
  `is_confidentiel` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_archives_slug_unique` (`slug`),
  KEY `intranet_archives_dossier_id_foreign` (`dossier_id`),
  KEY `intranet_archives_created_by_foreign` (`created_by`),
  KEY `intranet_archives_statut_index` (`statut`),
  KEY `intranet_archives_nature_index` (`nature`),
  KEY `intranet_archives_date_destruction_prevue_index` (`date_destruction_prevue`),
  CONSTRAINT `intranet_archives_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_archives_dossier_id_foreign` FOREIGN KEY (`dossier_id`) REFERENCES `intranet_archive_dossiers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_archives`
--

LOCK TABLES `intranet_archives` WRITE;
/*!40000 ALTER TABLE `intranet_archives` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_archives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_commentaires`
--

DROP TABLE IF EXISTS `intranet_commentaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_commentaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `commentable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentable_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `est_epingle` tinyint(1) NOT NULL DEFAULT '0',
  `modifie_le` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_commentaires_commentable_type_commentable_id_index` (`commentable_type`,`commentable_id`),
  KEY `intranet_commentaires_user_id_foreign` (`user_id`),
  KEY `intranet_commentaires_parent_id_foreign` (`parent_id`),
  CONSTRAINT `intranet_commentaires_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `intranet_commentaires` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_commentaires_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_commentaires`
--

LOCK TABLES `intranet_commentaires` WRITE;
/*!40000 ALTER TABLE `intranet_commentaires` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_commentaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_contact_organisations`
--

DROP TABLE IF EXISTS `intranet_contact_organisations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_contact_organisations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `secteur_id` bigint unsigned DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text COLLATE utf8mb4_unicode_ci,
  `pays_id` bigint unsigned DEFAULT NULL,
  `ville` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_postal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille` enum('TPE','PME','ETI','GE') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chiffre_affaires` decimal(18,2) DEFAULT NULL,
  `effectif` int unsigned DEFAULT NULL,
  `siret` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_tva` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `etiquette` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_client` tinyint(1) NOT NULL DEFAULT '0',
  `est_prospect` tinyint(1) NOT NULL DEFAULT '1',
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `site_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_contact_organisations_slug_unique` (`slug`),
  KEY `intranet_contact_organisations_created_by_foreign` (`created_by`),
  KEY `intranet_contact_organisations_secteur_id_foreign` (`secteur_id`),
  KEY `intranet_contact_organisations_pays_id_foreign` (`pays_id`),
  CONSTRAINT `intranet_contact_organisations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_contact_organisations_pays_id_foreign` FOREIGN KEY (`pays_id`) REFERENCES `intranet_pays` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_contact_organisations_secteur_id_foreign` FOREIGN KEY (`secteur_id`) REFERENCES `intranet_secteurs_activite` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_contact_organisations`
--

LOCK TABLES `intranet_contact_organisations` WRITE;
/*!40000 ALTER TABLE `intranet_contact_organisations` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_contact_organisations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_contacts`
--

DROP TABLE IF EXISTS `intranet_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `civilite` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenoms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poste` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text COLLATE utf8mb4_unicode_ci,
  `pays_id` bigint unsigned DEFAULT NULL,
  `ville` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `langue` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `etiquette` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `derniere_interaction_le` timestamp NULL DEFAULT NULL,
  `est_favori` tinyint(1) NOT NULL DEFAULT '0',
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `organisation_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_contacts_slug_unique` (`slug`),
  KEY `intranet_contacts_organisation_id_foreign` (`organisation_id`),
  KEY `intranet_contacts_created_by_foreign` (`created_by`),
  KEY `intranet_contacts_pays_id_foreign` (`pays_id`),
  CONSTRAINT `intranet_contacts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_contacts_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `intranet_contact_organisations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_contacts_pays_id_foreign` FOREIGN KEY (`pays_id`) REFERENCES `intranet_pays` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_contacts`
--

LOCK TABLES `intranet_contacts` WRITE;
/*!40000 ALTER TABLE `intranet_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_courrier_traitements`
--

DROP TABLE IF EXISTS `intranet_courrier_traitements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_courrier_traitements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `courrier_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `action` enum('cree','assigne','reassigne','commente','accuse_reception','traite','archive','rouvert') COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_courrier_traitements_user_id_foreign` (`user_id`),
  KEY `intranet_courrier_traitements_courrier_id_index` (`courrier_id`),
  KEY `intranet_courrier_traitements_action_index` (`action`),
  CONSTRAINT `intranet_courrier_traitements_courrier_id_foreign` FOREIGN KEY (`courrier_id`) REFERENCES `intranet_courriers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_courrier_traitements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_courrier_traitements`
--

LOCK TABLES `intranet_courrier_traitements` WRITE;
/*!40000 ALTER TABLE `intranet_courrier_traitements` DISABLE KEYS */;
INSERT INTO `intranet_courrier_traitements` VALUES (1,1,1,'cree',NULL,'[]','2026-04-09 22:07:42','2026-04-09 22:07:42'),(2,1,1,'assigne',NULL,'{\"nouveau_assigne\": \"2\"}','2026-04-09 22:07:42','2026-04-09 22:07:42'),(3,1,1,'reassigne','pour avis tech','{\"ancien_assigne\": 2, \"nouveau_assigne\": 1}','2026-04-09 22:08:46','2026-04-09 22:08:46');
/*!40000 ALTER TABLE `intranet_courrier_traitements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_courriers`
--

DROP TABLE IF EXISTS `intranet_courriers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_courriers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('entrant','sortant','interne') COLLATE utf8mb4_unicode_ci NOT NULL,
  `objet` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extrait` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci,
  `expediteur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destinataire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destinataire_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expediteur_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expediteur_organisation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_destinataire_id` bigint unsigned DEFAULT NULL,
  `service_expediteur_id` bigint unsigned DEFAULT NULL,
  `assigne_a` bigint unsigned DEFAULT NULL,
  `assigne_le` timestamp NULL DEFAULT NULL,
  `assigne_par` bigint unsigned DEFAULT NULL,
  `traite_le` timestamp NULL DEFAULT NULL,
  `traite_par` bigint unsigned DEFAULT NULL,
  `echeance_traitement` date DEFAULT NULL,
  `accuse_reception` tinyint(1) NOT NULL DEFAULT '0',
  `accuse_reception_le` timestamp NULL DEFAULT NULL,
  `accuse_reception_methode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accuse_reception_scan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confidentiel` tinyint(1) NOT NULL DEFAULT '0',
  `urgent` tinyint(1) NOT NULL DEFAULT '0',
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `date_reception` date DEFAULT NULL,
  `date_expedition` date DEFAULT NULL,
  `priorite` enum('normale','urgente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normale',
  `statut` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recu',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_courriers_reference_unique` (`reference`),
  UNIQUE KEY `intranet_courriers_slug_unique` (`slug`),
  KEY `intranet_courriers_created_by_foreign` (`created_by`),
  KEY `intranet_courriers_service_destinataire_id_foreign` (`service_destinataire_id`),
  KEY `intranet_courriers_service_expediteur_id_foreign` (`service_expediteur_id`),
  KEY `intranet_courriers_assigne_par_foreign` (`assigne_par`),
  KEY `intranet_courriers_traite_par_foreign` (`traite_par`),
  KEY `intranet_courriers_assigne_a_index` (`assigne_a`),
  KEY `intranet_courriers_statut_index` (`statut`),
  KEY `intranet_courriers_type_statut_index` (`type`,`statut`),
  CONSTRAINT `intranet_courriers_assigne_a_foreign` FOREIGN KEY (`assigne_a`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_courriers_assigne_par_foreign` FOREIGN KEY (`assigne_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_courriers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_courriers_service_destinataire_id_foreign` FOREIGN KEY (`service_destinataire_id`) REFERENCES `intranet_services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_courriers_service_expediteur_id_foreign` FOREIGN KEY (`service_expediteur_id`) REFERENCES `intranet_services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_courriers_traite_par_foreign` FOREIGN KEY (`traite_par`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_courriers`
--

LOCK TABLES `intranet_courriers` WRITE;
/*!40000 ALTER TABLE `intranet_courriers` DISABLE KEYS */;
INSERT INTO `intranet_courriers` VALUES (1,'test','entrant','test','Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit,','COU-2026-00001','<p>\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"</p><p><br></p><p><strong>Section 1.10.32 du \"De Finibus Bonorum et Malorum\" de Ciceron (45 av. J.-C.)</strong></p><p>\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\"</p><p><br></p><p><strong>Traduction de H. Rackham (1914)</strong></p><p>\"But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure. To take a trivial example, which of us ever undertakes laborious physical exercise, except to obtain some advantage from it? But who has any right to find fault with a man who chooses to enjoy a pleasure that has no annoying consequences, or one who avoids a pain that produces no resultant pleasure?\"</p>','Holden','Rayleigh','rayleigh@gmail.com','holden@gmail.com','YUbile',NULL,NULL,1,'2026-04-09 22:08:46',1,NULL,NULL,NULL,0,NULL,NULL,NULL,1,1,'intranet/courriers/LFcY5sjhHvPEgAH5kuUasOOX3JZVbk0tTHYOwcg5.png','image',5,'2026-04-09','2026-04-10','normale','en_traitement',1,'2026-04-09 22:07:42','2026-04-10 19:51:00',NULL);
/*!40000 ALTER TABLE `intranet_courriers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_crm_etapes`
--

DROP TABLE IF EXISTS `intranet_crm_etapes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_crm_etapes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordre` int NOT NULL DEFAULT '0',
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#4F46E5',
  `probabilite_defaut` tinyint unsigned NOT NULL DEFAULT '0',
  `est_gagnee` tinyint(1) NOT NULL DEFAULT '0',
  `est_perdue` tinyint(1) NOT NULL DEFAULT '0',
  `est_finale` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_crm_etapes`
--

LOCK TABLES `intranet_crm_etapes` WRITE;
/*!40000 ALTER TABLE `intranet_crm_etapes` DISABLE KEYS */;
INSERT INTO `intranet_crm_etapes` VALUES (1,'Prospection',1,'#94A3B8',10,0,0,0,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(2,'Qualification',2,'#06B6D4',25,0,0,0,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(3,'Proposition',3,'#7C3AED',50,0,0,0,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(4,'Négociation',4,'#F59E0B',75,0,0,0,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(5,'Gagnée',5,'#16A34A',100,1,0,1,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(6,'Perdue',6,'#DC2626',0,0,1,1,'2026-04-09 19:44:52','2026-04-09 19:44:52');
/*!40000 ALTER TABLE `intranet_crm_etapes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_crm_interactions`
--

DROP TABLE IF EXISTS `intranet_crm_interactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_crm_interactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `interactable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `interactable_id` bigint unsigned NOT NULL,
  `type` enum('appel','reunion','email','note','tache','autre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'note',
  `objet` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `date_interaction` datetime NOT NULL,
  `duree_minutes` smallint unsigned DEFAULT NULL,
  `est_terminee` tinyint(1) NOT NULL DEFAULT '1',
  `realisee_par` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_crm_interactions`
--

LOCK TABLES `intranet_crm_interactions` WRITE;
/*!40000 ALTER TABLE `intranet_crm_interactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_crm_interactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_demandes_modification`
--

DROP TABLE IF EXISTS `intranet_demandes_modification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_demandes_modification` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `modifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modifiable_id` bigint unsigned NOT NULL,
  `type_demande` enum('modification','suppression') COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('en_attente','approuve','rejete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `motif` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `champs_modifies` json DEFAULT NULL,
  `demandeur_id` bigint unsigned NOT NULL,
  `decideur_id` bigint unsigned DEFAULT NULL,
  `decide_le` timestamp NULL DEFAULT NULL,
  `commentaire_decision` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_demandes_modification_demandeur_id_foreign` (`demandeur_id`),
  KEY `intranet_demandes_modification_decideur_id_foreign` (`decideur_id`),
  KEY `dm_modifiable_idx` (`modifiable_type`,`modifiable_id`),
  KEY `intranet_demandes_modification_statut_index` (`statut`),
  CONSTRAINT `intranet_demandes_modification_decideur_id_foreign` FOREIGN KEY (`decideur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_demandes_modification_demandeur_id_foreign` FOREIGN KEY (`demandeur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_demandes_modification`
--

LOCK TABLES `intranet_demandes_modification` WRITE;
/*!40000 ALTER TABLE `intranet_demandes_modification` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_demandes_modification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_dependances`
--

DROP TABLE IF EXISTS `intranet_dependances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_dependances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` bigint unsigned NOT NULL,
  `cible_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cible_id` bigint unsigned NOT NULL,
  `type` enum('FS','SS','FF','SF') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FS',
  `lag_jours` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_dependances_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `intranet_dependances_cible_type_cible_id_index` (`cible_type`,`cible_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_dependances`
--

LOCK TABLES `intranet_dependances` WRITE;
/*!40000 ALTER TABLE `intranet_dependances` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_dependances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_evaluations`
--

DROP TABLE IF EXISTS `intranet_evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_evaluations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `evaluateur_id` bigint unsigned NOT NULL,
  `objectif_id` bigint unsigned DEFAULT NULL,
  `score` tinyint unsigned DEFAULT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `points_forts` text COLLATE utf8mb4_unicode_ci,
  `axes_amelioration` text COLLATE utf8mb4_unicode_ci,
  `date_evaluation` date NOT NULL,
  `statut` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_evaluations_user_id_foreign` (`user_id`),
  KEY `intranet_evaluations_evaluateur_id_foreign` (`evaluateur_id`),
  KEY `intranet_evaluations_objectif_id_foreign` (`objectif_id`),
  CONSTRAINT `intranet_evaluations_evaluateur_id_foreign` FOREIGN KEY (`evaluateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_evaluations_objectif_id_foreign` FOREIGN KEY (`objectif_id`) REFERENCES `intranet_objectifs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_evaluations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_evaluations`
--

LOCK TABLES `intranet_evaluations` WRITE;
/*!40000 ALTER TABLE `intranet_evaluations` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_evaluations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_evenement_invites_externes`
--

DROP TABLE IF EXISTS `intranet_evenement_invites_externes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_evenement_invites_externes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `evenement_id` bigint unsigned NOT NULL,
  `contact_id` bigint unsigned DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('invite','confirme','decline','peut_etre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'invite',
  `token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repondu_le` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_evenement_invites_externes_evenement_id_email_unique` (`evenement_id`,`email`),
  UNIQUE KEY `intranet_evenement_invites_externes_token_unique` (`token`),
  KEY `intranet_evenement_invites_externes_contact_id_foreign` (`contact_id`),
  KEY `intranet_evenement_invites_externes_email_index` (`email`),
  CONSTRAINT `intranet_evenement_invites_externes_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `intranet_contacts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_evenement_invites_externes_evenement_id_foreign` FOREIGN KEY (`evenement_id`) REFERENCES `intranet_evenements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_evenement_invites_externes`
--

LOCK TABLES `intranet_evenement_invites_externes` WRITE;
/*!40000 ALTER TABLE `intranet_evenement_invites_externes` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_evenement_invites_externes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_evenement_participants`
--

DROP TABLE IF EXISTS `intranet_evenement_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_evenement_participants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `evenement_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `statut` enum('invite','confirme','decline','peut_etre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'invite',
  `reponse` text COLLATE utf8mb4_unicode_ci,
  `repondu_le` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_evenement_participants_evenement_id_user_id_unique` (`evenement_id`,`user_id`),
  KEY `intranet_evenement_participants_user_id_foreign` (`user_id`),
  CONSTRAINT `intranet_evenement_participants_evenement_id_foreign` FOREIGN KEY (`evenement_id`) REFERENCES `intranet_evenements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_evenement_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_evenement_participants`
--

LOCK TABLES `intranet_evenement_participants` WRITE;
/*!40000 ALTER TABLE `intranet_evenement_participants` DISABLE KEYS */;
INSERT INTO `intranet_evenement_participants` VALUES (1,1,1,'confirme',NULL,'2026-04-09 18:41:45','2026-04-09 18:41:45','2026-04-09 18:41:45');
/*!40000 ALTER TABLE `intranet_evenement_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_evenements`
--

DROP TABLE IF EXISTS `intranet_evenements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_evenements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extrait` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `journee_entiere` tinyint(1) NOT NULL DEFAULT '0',
  `recurrence` enum('aucune','quotidienne','hebdomadaire','mensuelle','annuelle') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aucune',
  `recurrence_jusqu_au` date DEFAULT NULL,
  `parent_recurrence_id` bigint unsigned DEFAULT NULL,
  `lieu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lieu_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `est_visio` tinyint(1) NOT NULL DEFAULT '0',
  `lien_visio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacite_max` smallint unsigned DEFAULT NULL,
  `inscription_requise` tinyint(1) NOT NULL DEFAULT '0',
  `rappel_minutes` smallint unsigned DEFAULT NULL,
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `statut` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'prevu',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_evenement_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_evenements_slug_unique` (`slug`),
  KEY `intranet_evenements_type_evenement_id_foreign` (`type_evenement_id`),
  KEY `intranet_evenements_created_by_foreign` (`created_by`),
  KEY `intranet_evenements_parent_recurrence_id_index` (`parent_recurrence_id`),
  KEY `intranet_evenements_date_debut_index` (`date_debut`),
  CONSTRAINT `intranet_evenements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_evenements_type_evenement_id_foreign` FOREIGN KEY (`type_evenement_id`) REFERENCES `intranet_type_evenements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_evenements`
--

LOCK TABLES `intranet_evenements` WRITE;
/*!40000 ALTER TABLE `intranet_evenements` DISABLE KEYS */;
INSERT INTO `intranet_evenements` VALUES (1,'Réunion mensuelle — Avril',NULL,NULL,'2026-04-14 09:00:00','2026-04-14 11:00:00',0,'aucune',NULL,NULL,'Salle de conférence A',NULL,0,NULL,NULL,0,NULL,4,'prevu',1,NULL,NULL,NULL,NULL,2,1,'2026-04-07 17:16:57','2026-04-09 20:03:11',NULL),(2,'Formation : Prise en main OptimiZe',NULL,NULL,'2026-04-21 14:00:00','2026-04-21 17:00:00',0,'aucune',NULL,NULL,'Salle informatique',NULL,0,NULL,NULL,0,NULL,0,'prevu',1,NULL,NULL,NULL,NULL,3,1,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL);
/*!40000 ALTER TABLE `intranet_evenements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_feuilles_temps`
--

DROP TABLE IF EXISTS `intranet_feuilles_temps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_feuilles_temps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `projet_id` bigint unsigned NOT NULL,
  `tache_id` bigint unsigned DEFAULT NULL,
  `activite_id` bigint unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `heures` decimal(5,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `statut` enum('brouillon','soumis','approuve','rejete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `approuve_par` bigint unsigned DEFAULT NULL,
  `approuve_le` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_feuilles_temps_user_id_foreign` (`user_id`),
  KEY `intranet_feuilles_temps_projet_id_foreign` (`projet_id`),
  KEY `intranet_feuilles_temps_tache_id_foreign` (`tache_id`),
  KEY `intranet_feuilles_temps_activite_id_foreign` (`activite_id`),
  KEY `intranet_feuilles_temps_approuve_par_foreign` (`approuve_par`),
  CONSTRAINT `intranet_feuilles_temps_activite_id_foreign` FOREIGN KEY (`activite_id`) REFERENCES `intranet_activites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_feuilles_temps_approuve_par_foreign` FOREIGN KEY (`approuve_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_feuilles_temps_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_feuilles_temps_tache_id_foreign` FOREIGN KEY (`tache_id`) REFERENCES `intranet_taches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_feuilles_temps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_feuilles_temps`
--

LOCK TABLES `intranet_feuilles_temps` WRITE;
/*!40000 ALTER TABLE `intranet_feuilles_temps` DISABLE KEYS */;
INSERT INTO `intranet_feuilles_temps` VALUES (1,2,2,5,NULL,'2026-04-17',8.00,'Développement','soumis',NULL,NULL,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(2,2,2,8,NULL,'2026-04-16',8.00,'Documentation','soumis',NULL,NULL,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(3,1,2,7,NULL,'2026-04-15',2.00,'Tests','soumis',NULL,NULL,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(4,1,2,9,NULL,'2026-04-14',5.00,'Réunion','soumis',NULL,NULL,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(5,1,2,10,NULL,'2026-04-13',3.00,'Conception','soumis',NULL,NULL,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(6,2,2,11,NULL,'2026-04-12',7.00,'Analyse','soumis',NULL,NULL,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(7,2,2,10,NULL,'2026-04-11',6.00,'Analyse','approuve',1,'2026-04-12 12:44:41','2026-04-17 12:44:41','2026-04-17 12:44:41'),(8,1,2,10,NULL,'2026-04-10',2.00,'Revue de code','approuve',1,'2026-04-11 12:44:41','2026-04-17 12:44:41','2026-04-17 12:44:41'),(9,1,2,12,NULL,'2026-04-09',3.00,'Conception','approuve',1,'2026-04-10 12:44:41','2026-04-17 12:44:41','2026-04-17 12:44:41'),(10,2,2,10,NULL,'2026-04-08',2.00,'Analyse','approuve',1,'2026-04-09 12:44:41','2026-04-17 12:44:41','2026-04-17 12:44:41'),(11,2,2,7,NULL,'2026-04-07',4.00,'Développement','approuve',1,'2026-04-08 12:44:41','2026-04-17 12:44:41','2026-04-17 12:44:41'),(12,1,2,3,NULL,'2026-04-06',8.00,'Documentation','approuve',1,'2026-04-07 12:44:41','2026-04-17 12:44:41','2026-04-17 12:44:41'),(13,2,2,8,NULL,'2026-04-05',3.00,'Documentation','approuve',1,'2026-04-06 12:44:41','2026-04-17 12:44:41','2026-04-17 12:44:41'),(14,1,2,11,NULL,'2026-04-04',6.00,'Développement','approuve',1,'2026-04-05 12:44:41','2026-04-17 12:44:41','2026-04-17 12:44:41'),(15,1,2,4,NULL,'2026-04-03',4.00,'Documentation','approuve',1,'2026-04-04 12:44:41','2026-04-17 12:44:41','2026-04-17 12:44:41'),(16,1,2,12,NULL,'2026-04-17',3.00,'Revue de code','soumis',NULL,NULL,'2026-04-17 12:44:49','2026-04-17 12:44:49'),(17,2,2,3,NULL,'2026-04-16',7.00,'Documentation','soumis',NULL,NULL,'2026-04-17 12:44:49','2026-04-17 12:44:49'),(18,1,2,9,NULL,'2026-04-15',2.00,'Réunion','soumis',NULL,NULL,'2026-04-17 12:44:49','2026-04-17 12:44:49'),(19,2,2,8,NULL,'2026-04-14',8.00,'Tests','soumis',NULL,NULL,'2026-04-17 12:44:49','2026-04-17 12:44:49'),(20,1,2,6,NULL,'2026-04-13',8.00,'Conception','soumis',NULL,NULL,'2026-04-17 12:44:49','2026-04-17 12:44:49'),(21,1,2,9,NULL,'2026-04-12',4.00,'Analyse','soumis',NULL,NULL,'2026-04-17 12:44:49','2026-04-17 12:44:49'),(22,1,2,11,NULL,'2026-04-11',5.00,'Documentation','approuve',1,'2026-04-12 12:44:49','2026-04-17 12:44:49','2026-04-17 12:44:49'),(23,1,2,7,NULL,'2026-04-10',5.00,'Réunion','approuve',1,'2026-04-11 12:44:49','2026-04-17 12:44:49','2026-04-17 12:44:49'),(24,2,2,11,NULL,'2026-04-09',8.00,'Tests','approuve',1,'2026-04-10 12:44:49','2026-04-17 12:44:49','2026-04-17 12:44:49'),(25,1,2,8,NULL,'2026-04-08',6.00,'Analyse','approuve',1,'2026-04-09 12:44:49','2026-04-17 12:44:49','2026-04-17 12:44:49'),(26,1,2,7,NULL,'2026-04-07',5.00,'Revue de code','approuve',1,'2026-04-08 12:44:49','2026-04-17 12:44:49','2026-04-17 12:44:49'),(27,1,2,6,NULL,'2026-04-06',2.00,'Conception','approuve',1,'2026-04-07 12:44:49','2026-04-17 12:44:49','2026-04-17 12:44:49'),(28,1,2,11,NULL,'2026-04-05',8.00,'Revue de code','approuve',1,'2026-04-06 12:44:49','2026-04-17 12:44:49','2026-04-17 12:44:49'),(29,2,2,6,NULL,'2026-04-04',5.00,'Analyse','approuve',1,'2026-04-05 12:44:49','2026-04-17 12:44:49','2026-04-17 12:44:49'),(30,2,2,11,NULL,'2026-04-03',8.00,'Réunion','approuve',1,'2026-04-04 12:44:49','2026-04-17 12:44:49','2026-04-17 12:44:49');
/*!40000 ALTER TABLE `intranet_feuilles_temps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_groupe_user`
--

DROP TABLE IF EXISTS `intranet_groupe_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_groupe_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `groupe_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'membre',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_groupe_user_groupe_id_user_id_unique` (`groupe_id`,`user_id`),
  KEY `intranet_groupe_user_user_id_foreign` (`user_id`),
  CONSTRAINT `intranet_groupe_user_groupe_id_foreign` FOREIGN KEY (`groupe_id`) REFERENCES `intranet_groupes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_groupe_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_groupe_user`
--

LOCK TABLES `intranet_groupe_user` WRITE;
/*!40000 ALTER TABLE `intranet_groupe_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_groupe_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_groupes`
--

DROP TABLE IF EXISTS `intranet_groupes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_groupes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#4F46E5',
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_groupes_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_groupes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_groupes`
--

LOCK TABLES `intranet_groupes` WRITE;
/*!40000 ALTER TABLE `intranet_groupes` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_groupes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_historique_validations`
--

DROP TABLE IF EXISTS `intranet_historique_validations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_historique_validations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `validable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validable_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `action` enum('cree','modifie','soumis','resoumis','approuve','rejete','revisions_demandees','cloture','reouvert') COLLATE utf8mb4_unicode_ci NOT NULL,
  `justification` text COLLATE utf8mb4_unicode_ci,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_historique_validations_user_id_foreign` (`user_id`),
  KEY `hist_valid_validable_idx` (`validable_type`,`validable_id`),
  KEY `intranet_historique_validations_action_index` (`action`),
  CONSTRAINT `intranet_historique_validations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_historique_validations`
--

LOCK TABLES `intranet_historique_validations` WRITE;
/*!40000 ALTER TABLE `intranet_historique_validations` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_historique_validations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_jalons`
--

DROP TABLE IF EXISTS `intranet_jalons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_jalons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `phase_id` bigint unsigned DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `date_prevue` date NOT NULL,
  `date_reelle` date DEFAULT NULL,
  `statut` enum('prevu','atteint','manque','reporte') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'prevu',
  `statut_cloture` enum('ouvert','soumis','approuve','rejete','revisions') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ouvert',
  `justification_cloture` text COLLATE utf8mb4_unicode_ci,
  `soumis_le` timestamp NULL DEFAULT NULL,
  `valide_le` timestamp NULL DEFAULT NULL,
  `motif_rejet` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_jalons_projet_id_foreign` (`projet_id`),
  KEY `intranet_jalons_phase_id_foreign` (`phase_id`),
  KEY `intranet_jalons_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_jalons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_jalons_phase_id_foreign` FOREIGN KEY (`phase_id`) REFERENCES `intranet_projet_phases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_jalons_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_jalons`
--

LOCK TABLES `intranet_jalons` WRITE;
/*!40000 ALTER TABLE `intranet_jalons` DISABLE KEYS */;
INSERT INTO `intranet_jalons` VALUES (1,2,1,'Validation du cahier des charges',NULL,'2026-03-17','2026-03-19','atteint','ouvert',NULL,NULL,NULL,NULL,1,'2026-04-17 12:24:53','2026-04-17 12:44:49'),(2,2,2,'Revue d\'architecture',NULL,'2026-05-01',NULL,'prevu','ouvert',NULL,NULL,NULL,NULL,1,'2026-04-17 12:24:53','2026-04-17 12:44:49'),(3,2,2,'Go/No-Go développement',NULL,'2026-05-17',NULL,'prevu','ouvert',NULL,NULL,NULL,NULL,1,'2026-04-17 12:24:53','2026-04-17 12:44:49'),(4,2,3,'Livraison module RH',NULL,'2026-08-17',NULL,'prevu','ouvert',NULL,NULL,NULL,NULL,1,'2026-04-17 12:24:53','2026-04-17 12:44:49'),(5,2,3,'Livraison module Finance',NULL,'2026-09-17',NULL,'prevu','ouvert',NULL,NULL,NULL,NULL,1,'2026-04-17 12:24:53','2026-04-17 12:44:49'),(6,2,4,'Mise en production',NULL,'2026-12-17',NULL,'prevu','ouvert',NULL,NULL,NULL,NULL,1,'2026-04-17 12:24:53','2026-04-17 12:44:49'),(7,2,4,'Réception définitive',NULL,'2026-04-14',NULL,'prevu','ouvert',NULL,NULL,NULL,NULL,1,'2026-04-17 12:24:53','2026-04-17 12:44:49'),(8,5,6,'test jalon','test jalon','2026-04-23',NULL,'prevu','ouvert',NULL,NULL,NULL,NULL,1,'2026-04-20 21:05:35','2026-04-20 21:05:35');
/*!40000 ALTER TABLE `intranet_jalons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_kpi`
--

DROP TABLE IF EXISTS `intranet_kpi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_kpi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `objectif_id` bigint unsigned DEFAULT NULL,
  `valeur_cible` decimal(15,4) DEFAULT NULL,
  `valeur_actuelle` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `unite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tendance` enum('hausse','baisse','stable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'stable',
  `periodicite` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mensuel',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_kpi_objectif_id_foreign` (`objectif_id`),
  KEY `intranet_kpi_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_kpi_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_kpi_objectif_id_foreign` FOREIGN KEY (`objectif_id`) REFERENCES `intranet_objectifs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_kpi`
--

LOCK TABLES `intranet_kpi` WRITE;
/*!40000 ALTER TABLE `intranet_kpi` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_kpi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_kpi_valeurs`
--

DROP TABLE IF EXISTS `intranet_kpi_valeurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_kpi_valeurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kpi_id` bigint unsigned NOT NULL,
  `valeur` decimal(15,4) NOT NULL,
  `date_mesure` date NOT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_kpi_valeurs_kpi_id_foreign` (`kpi_id`),
  KEY `intranet_kpi_valeurs_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_kpi_valeurs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_kpi_valeurs_kpi_id_foreign` FOREIGN KEY (`kpi_id`) REFERENCES `intranet_kpi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_kpi_valeurs`
--

LOCK TABLES `intranet_kpi_valeurs` WRITE;
/*!40000 ALTER TABLE `intranet_kpi_valeurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_kpi_valeurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_likes`
--

DROP TABLE IF EXISTS `intranet_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_likes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `likeable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `likeable_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `like_unique` (`likeable_type`,`likeable_id`,`user_id`),
  KEY `intranet_likes_likeable_type_likeable_id_index` (`likeable_type`,`likeable_id`),
  KEY `intranet_likes_user_id_foreign` (`user_id`),
  CONSTRAINT `intranet_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_likes`
--

LOCK TABLES `intranet_likes` WRITE;
/*!40000 ALTER TABLE `intranet_likes` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_mail_comptes`
--

DROP TABLE IF EXISTS `intranet_mail_comptes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_mail_comptes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `protocole` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'imap',
  `serveur_entrant` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port_entrant` smallint unsigned NOT NULL DEFAULT '993',
  `ssl_entrant` tinyint(1) NOT NULL DEFAULT '1',
  `serveur_sortant` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port_sortant` smallint unsigned NOT NULL DEFAULT '587',
  `ssl_sortant` tinyint(1) NOT NULL DEFAULT '1',
  `identifiant` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_shared` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_mail_comptes_user_id_foreign` (`user_id`),
  CONSTRAINT `intranet_mail_comptes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_mail_comptes`
--

LOCK TABLES `intranet_mail_comptes` WRITE;
/*!40000 ALTER TABLE `intranet_mail_comptes` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_mail_comptes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_mails`
--

DROP TABLE IF EXISTS `intranet_mails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_mails` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `compte_id` bigint unsigned NOT NULL,
  `uid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sujet` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expediteur` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destinataires` json NOT NULL,
  `cc` json DEFAULT NULL,
  `bcc` json DEFAULT NULL,
  `corps_html` longtext COLLATE utf8mb4_unicode_ci,
  `corps_texte` longtext COLLATE utf8mb4_unicode_ci,
  `dossier` enum('reception','envoyes','brouillons','archives','corbeille') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reception',
  `lu` tinyint(1) NOT NULL DEFAULT '0',
  `important` tinyint(1) NOT NULL DEFAULT '0',
  `date_envoi` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_mails_compte_id_foreign` (`compte_id`),
  KEY `intranet_mails_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_mails_compte_id_foreign` FOREIGN KEY (`compte_id`) REFERENCES `intranet_mail_comptes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_mails_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_mails`
--

LOCK TABLES `intranet_mails` WRITE;
/*!40000 ALTER TABLE `intranet_mails` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_mails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_media`
--

DROP TABLE IF EXISTS `intranet_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('image','video','audio','document','autre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'document',
  `fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_externe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plateforme` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `embed_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille` bigint unsigned DEFAULT NULL,
  `dossier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `album` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `album_id` bigint unsigned DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `largeur` smallint unsigned DEFAULT NULL,
  `hauteur` smallint unsigned DEFAULT NULL,
  `duree_secondes` int unsigned DEFAULT NULL,
  `telechargements_count` int unsigned NOT NULL DEFAULT '0',
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_media_slug_unique` (`slug`),
  KEY `intranet_media_created_by_foreign` (`created_by`),
  KEY `intranet_media_type_index` (`type`),
  KEY `intranet_media_album_index` (`album`),
  KEY `intranet_media_album_id_foreign` (`album_id`),
  CONSTRAINT `intranet_media_album_id_foreign` FOREIGN KEY (`album_id`) REFERENCES `intranet_media_albums` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_media_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_media`
--

LOCK TABLES `intranet_media` WRITE;
/*!40000 ALTER TABLE `intranet_media` DISABLE KEYS */;
INSERT INTO `intranet_media` VALUES (1,'photo-1','Photo 1','<p><br></p>','image','intranet/mediatheque/V0bGpygUGfIlgmILqJHQ3sS1m2zlVnCTvzrACN46.png',NULL,NULL,NULL,NULL,'Capture d’écran 2026-04-07 à 16.55.12.png','image/png',3750616,NULL,NULL,1,'[]',3024,1964,NULL,0,4,0,1,'2026-04-10 20:58:24','2026-04-10 22:09:30',NULL),(2,'photo-2','photo 2','<p><br></p>','image','intranet/mediatheque/Nhv8l6xcBBzlWIVYoudBLkzS0RutPrXCrtnwmHFB.png',NULL,NULL,NULL,NULL,'Capture d’écran 2026-04-07 à 17.13.05.png','image/png',3468047,NULL,NULL,1,'[]',3024,1964,NULL,0,4,0,1,'2026-04-10 21:06:35','2026-04-10 21:07:54',NULL);
/*!40000 ALTER TABLE `intranet_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_media_albums`
--

DROP TABLE IF EXISTS `intranet_media_albums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_media_albums` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `couverture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#7C3AED',
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_media_albums_slug_unique` (`slug`),
  KEY `intranet_media_albums_created_by_foreign` (`created_by`),
  KEY `intranet_media_albums_parent_id_index` (`parent_id`),
  CONSTRAINT `intranet_media_albums_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_media_albums_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `intranet_media_albums` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_media_albums`
--

LOCK TABLES `intranet_media_albums` WRITE;
/*!40000 ALTER TABLE `intranet_media_albums` DISABLE KEYS */;
INSERT INTO `intranet_media_albums` VALUES (1,'SGLP 2025 - 2026','sglp-2025-2026',NULL,'intranet/mediatheque/albums/sY0EYS7zi0Vk9qHKD6DAyHASVrysIaZDrlQlU9yF.png','#1b27e4',NULL,NULL,1,1,'2026-04-10 20:57:38','2026-04-10 20:57:38',NULL);
/*!40000 ALTER TABLE `intranet_media_albums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_module_parametres`
--

DROP TABLE IF EXISTS `intranet_module_parametres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_module_parametres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `likes_actifs` tinyint(1) NOT NULL DEFAULT '1',
  `commentaires_actifs` tinyint(1) NOT NULL DEFAULT '1',
  `partage_actif` tinyint(1) NOT NULL DEFAULT '0',
  `ciblage_users` tinyint(1) NOT NULL DEFAULT '1',
  `ciblage_groupes` tinyint(1) NOT NULL DEFAULT '1',
  `ciblage_entites` tinyint(1) NOT NULL DEFAULT '1',
  `peut_etre_public` tinyint(1) NOT NULL DEFAULT '1',
  `notif_publication` tinyint(1) NOT NULL DEFAULT '1',
  `notif_commentaire` tinyint(1) NOT NULL DEFAULT '1',
  `notif_like` tinyint(1) NOT NULL DEFAULT '0',
  `moderation_commentaires` tinyint(1) NOT NULL DEFAULT '0',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_module_parametres_module_unique` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_module_parametres`
--

LOCK TABLES `intranet_module_parametres` WRITE;
/*!40000 ALTER TABLE `intranet_module_parametres` DISABLE KEYS */;
INSERT INTO `intranet_module_parametres` VALUES (1,'annonces',1,1,0,1,1,1,1,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(2,'news',1,1,1,1,1,1,1,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(3,'evenements',1,1,0,1,1,1,1,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(4,'courriers',0,0,0,1,1,1,0,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(5,'ressources',0,1,0,1,1,1,0,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(6,'mediatheque',1,1,1,1,1,1,1,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(7,'archives',0,0,0,1,1,1,0,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(8,'templates',0,1,0,1,1,1,1,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(9,'projets',0,1,0,1,1,0,0,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(10,'taches',0,1,0,1,1,0,0,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(11,'rapports',0,1,0,1,1,1,0,1,1,0,1,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(12,'wiki',1,1,1,1,1,1,1,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(13,'opportunites',0,1,0,1,0,0,0,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(14,'objectifs',0,1,0,1,1,1,0,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(15,'kpi',0,0,0,1,1,1,0,1,1,0,0,1,'2026-04-07 21:43:43','2026-04-07 21:43:43'),(16,'evaluations',0,1,0,0,0,0,0,1,1,0,1,1,'2026-04-07 21:43:43','2026-04-07 21:43:43');
/*!40000 ALTER TABLE `intranet_module_parametres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_news`
--

DROP TABLE IF EXISTS `intranet_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_news` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extrait` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rubrique` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `a_la_une` tinyint(1) NOT NULL DEFAULT '0',
  `auteur_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temps_lecture` smallint unsigned NOT NULL DEFAULT '0',
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#7C3AED',
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_news_slug_unique` (`slug`),
  KEY `intranet_news_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_news_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_news`
--

LOCK TABLES `intranet_news` WRITE;
/*!40000 ALTER TABLE `intranet_news` DISABLE KEYS */;
INSERT INTO `intranet_news` VALUES (1,'Lancement du nouveau module Intranet',NULL,NULL,NULL,NULL,'OptimiZe intègre désormais un module intranet complet pour centraliser les communications et la gestion documentaire.',1,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,0,'#7C3AED',0,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(2,'Formation aux outils collaboratifs',NULL,NULL,NULL,NULL,'Une session de formation sur les nouveaux outils collaboratifs est prévue pour l\'ensemble des équipes.',1,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,0,'#7C3AED',0,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(3,'Gabon : mise en garde contre l’usage de l’IA mettant en scène de manière irrespectueuse les autorités','gabon-mise-en-garde-contre-lusage-de-lia-mettant-en-scene-de-maniere-irrespectueuse-les-autorites','Le ministère de la Communication et des Médias a publié ce mercredi 08 avril 2026 un communiqué sans équivoque annonçant la fin de l’impunité numérique. Face à la prolifération de contenus manipulés par l’intelligence artificielle (IA) mettant en scène de manière irrespectueuse les plus hautes autorités de l’État, le gouvernement gabonais tape du poing sur la table.',NULL,'[]','<h2><strong>Un arsenal juridique renforcé : l’ordonnance sur l’hypertrucage</strong></h2><p>Le temps de la sensibilisation laisse désormais place à celui de la répression. Le ministère rappelle que le cadre pénal gabonais a récemment évolué pour <a href=\"https://gabonmediatime.com/gabon-fin-de-limpunite-pour-les-administrateurs-de-groupe-et-les-adeptes-de-partage-sur-les-reseaux-sociaux/\" rel=\"noopener noreferrer\" target=\"_blank\">intégrer les spécificités du numérique</a>.</p><p>L’élément clé de cette offensive juridique est l’adoption, lors du Conseil des ministres du 26 février 2026, d’une ordonnance sanctionnant l’hypertrucage ou deepfake. Ce texte vise spécifiquement les créations numériques trompeuses qui portent atteinte à la cohésion nationale ou à l’image des représentants de l’État.</p><p><br></p><h2><strong>Vers un usage éthique du numérique</strong></h2><p>Le gouvernement exhorte désormais la population à un usage «<strong><em> éthique et responsable</em></strong> » des outils technologiques. Le message est clair : l’innovation ne doit pas être un vecteur de chaos social. Tout manquement aux nouvelles dispositions législatives exposera désormais les auteurs à des sanctions pénales sévères, marquant ainsi la volonté du Gabon de protéger sa souveraineté numérique et la dignité de ses dirigeants.</p>',1,NULL,'intranet/news/0gmfonbzAzDheQAfOqnzRHzzlUIZ0kdnvzDHZ0DT.png','image',NULL,NULL,1,NULL,NULL,1,'#7c3aed',4,'2026-04-09 18:21:00','2026-05-07 17:49:46',NULL);
/*!40000 ALTER TABLE `intranet_news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_objectifs`
--

DROP TABLE IF EXISTS `intranet_objectifs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_objectifs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `portee` enum('organisation','service','equipe','individuel') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'organisation',
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `statut` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'actif',
  `parent_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_objectifs_parent_id_foreign` (`parent_id`),
  KEY `intranet_objectifs_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_objectifs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_objectifs_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `intranet_objectifs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_objectifs`
--

LOCK TABLES `intranet_objectifs` WRITE;
/*!40000 ALTER TABLE `intranet_objectifs` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_objectifs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_opportunites`
--

DROP TABLE IF EXISTS `intranet_opportunites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_opportunites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_id` bigint unsigned DEFAULT NULL,
  `organisation_id` bigint unsigned DEFAULT NULL,
  `etape_id` bigint unsigned DEFAULT NULL,
  `valeur` decimal(15,2) DEFAULT NULL,
  `devise` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'XAF',
  `probabilite` tinyint unsigned NOT NULL DEFAULT '0',
  `date_echeance` date DEFAULT NULL,
  `date_creation_opp` date DEFAULT NULL,
  `date_cloture_reelle` date DEFAULT NULL,
  `raison_perte` text COLLATE utf8mb4_unicode_ci,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `ordre_kanban` int unsigned NOT NULL DEFAULT '0',
  `statut` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ouvert',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `responsable_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_opportunites_contact_id_foreign` (`contact_id`),
  KEY `intranet_opportunites_organisation_id_foreign` (`organisation_id`),
  KEY `intranet_opportunites_etape_id_foreign` (`etape_id`),
  KEY `intranet_opportunites_created_by_foreign` (`created_by`),
  KEY `intranet_opportunites_responsable_id_foreign` (`responsable_id`),
  CONSTRAINT `intranet_opportunites_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `intranet_contacts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_opportunites_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_opportunites_etape_id_foreign` FOREIGN KEY (`etape_id`) REFERENCES `intranet_crm_etapes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_opportunites_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `intranet_contact_organisations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_opportunites_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_opportunites`
--

LOCK TABLES `intranet_opportunites` WRITE;
/*!40000 ALTER TABLE `intranet_opportunites` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_opportunites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_pays`
--

DROP TABLE IF EXISTS `intranet_pays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_pays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code_iso2` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_iso3` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `indicatif_telephone` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drapeau_emoji` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `continent` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` smallint unsigned NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_pays_code_iso2_unique` (`code_iso2`)
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_pays`
--

LOCK TABLES `intranet_pays` WRITE;
/*!40000 ALTER TABLE `intranet_pays` DISABLE KEYS */;
INSERT INTO `intranet_pays` VALUES (1,'CM','CMR','Cameroun',NULL,'+237','🇨🇲','Afrique',1,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(2,'DZ','DZA','Algérie',NULL,'+213','🇩🇿','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(3,'AO','AGO','Angola',NULL,'+244','🇦🇴','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(4,'BJ','BEN','Bénin',NULL,'+229','🇧🇯','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(5,'BW','BWA','Botswana',NULL,'+267','🇧🇼','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(6,'BF','BFA','Burkina Faso',NULL,'+226','🇧🇫','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(7,'BI','BDI','Burundi',NULL,'+257','🇧🇮','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(8,'CV','CPV','Cap-Vert',NULL,'+238','🇨🇻','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(9,'CF','CAF','République centrafricaine',NULL,'+236','🇨🇫','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(10,'TD','TCD','Tchad',NULL,'+235','🇹🇩','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(11,'KM','COM','Comores',NULL,'+269','🇰🇲','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(12,'CG','COG','Congo',NULL,'+242','🇨🇬','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(13,'CD','COD','République démocratique du Congo',NULL,'+243','🇨🇩','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(14,'CI','CIV','Côte d\'Ivoire',NULL,'+225','🇨🇮','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(15,'DJ','DJI','Djibouti',NULL,'+253','🇩🇯','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(16,'EG','EGY','Égypte',NULL,'+20','🇪🇬','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(17,'GQ','GNQ','Guinée équatoriale',NULL,'+240','🇬🇶','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(18,'ER','ERI','Érythrée',NULL,'+291','🇪🇷','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(19,'SZ','SWZ','Eswatini',NULL,'+268','🇸🇿','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(20,'ET','ETH','Éthiopie',NULL,'+251','🇪🇹','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(21,'GA','GAB','Gabon',NULL,'+241','🇬🇦','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(22,'GM','GMB','Gambie',NULL,'+220','🇬🇲','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(23,'GH','GHA','Ghana',NULL,'+233','🇬🇭','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(24,'GN','GIN','Guinée',NULL,'+224','🇬🇳','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(25,'GW','GNB','Guinée-Bissau',NULL,'+245','🇬🇼','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(26,'KE','KEN','Kenya',NULL,'+254','🇰🇪','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(27,'LS','LSO','Lesotho',NULL,'+266','🇱🇸','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(28,'LR','LBR','Libéria',NULL,'+231','🇱🇷','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(29,'LY','LBY','Libye',NULL,'+218','🇱🇾','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(30,'MG','MDG','Madagascar',NULL,'+261','🇲🇬','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(31,'MW','MWI','Malawi',NULL,'+265','🇲🇼','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(32,'ML','MLI','Mali',NULL,'+223','🇲🇱','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(33,'MR','MRT','Mauritanie',NULL,'+222','🇲🇷','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(34,'MU','MUS','Maurice',NULL,'+230','🇲🇺','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(35,'MA','MAR','Maroc',NULL,'+212','🇲🇦','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(36,'MZ','MOZ','Mozambique',NULL,'+258','🇲🇿','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(37,'NA','NAM','Namibie',NULL,'+264','🇳🇦','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(38,'NE','NER','Niger',NULL,'+227','🇳🇪','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(39,'NG','NGA','Nigeria',NULL,'+234','🇳🇬','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(40,'RW','RWA','Rwanda',NULL,'+250','🇷🇼','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(41,'ST','STP','Sao Tomé-et-Principe',NULL,'+239','🇸🇹','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(42,'SN','SEN','Sénégal',NULL,'+221','🇸🇳','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(43,'SC','SYC','Seychelles',NULL,'+248','🇸🇨','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(44,'SL','SLE','Sierra Leone',NULL,'+232','🇸🇱','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(45,'SO','SOM','Somalie',NULL,'+252','🇸🇴','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(46,'ZA','ZAF','Afrique du Sud',NULL,'+27','🇿🇦','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(47,'SS','SSD','Soudan du Sud',NULL,'+211','🇸🇸','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(48,'SD','SDN','Soudan',NULL,'+249','🇸🇩','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(49,'TZ','TZA','Tanzanie',NULL,'+255','🇹🇿','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(50,'TG','TGO','Togo',NULL,'+228','🇹🇬','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(51,'TN','TUN','Tunisie',NULL,'+216','🇹🇳','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(52,'UG','UGA','Ouganda',NULL,'+256','🇺🇬','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(53,'ZM','ZMB','Zambie',NULL,'+260','🇿🇲','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(54,'ZW','ZWE','Zimbabwe',NULL,'+263','🇿🇼','Afrique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(55,'FR','FRA','France',NULL,'+33','🇫🇷','Europe',2,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(56,'BE','BEL','Belgique',NULL,'+32','🇧🇪','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(57,'CH','CHE','Suisse',NULL,'+41','🇨🇭','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(58,'LU','LUX','Luxembourg',NULL,'+352','🇱🇺','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(59,'DE','DEU','Allemagne',NULL,'+49','🇩🇪','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(60,'IT','ITA','Italie',NULL,'+39','🇮🇹','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(61,'ES','ESP','Espagne',NULL,'+34','🇪🇸','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(62,'PT','PRT','Portugal',NULL,'+351','🇵🇹','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(63,'GB','GBR','Royaume-Uni',NULL,'+44','🇬🇧','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(64,'IE','IRL','Irlande',NULL,'+353','🇮🇪','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(65,'NL','NLD','Pays-Bas',NULL,'+31','🇳🇱','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(66,'AT','AUT','Autriche',NULL,'+43','🇦🇹','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(67,'PL','POL','Pologne',NULL,'+48','🇵🇱','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(68,'CZ','CZE','Tchéquie',NULL,'+420','🇨🇿','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(69,'SK','SVK','Slovaquie',NULL,'+421','🇸🇰','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(70,'HU','HUN','Hongrie',NULL,'+36','🇭🇺','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(71,'RO','ROU','Roumanie',NULL,'+40','🇷🇴','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(72,'BG','BGR','Bulgarie',NULL,'+359','🇧🇬','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(73,'GR','GRC','Grèce',NULL,'+30','🇬🇷','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(74,'HR','HRV','Croatie',NULL,'+385','🇭🇷','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(75,'SI','SVN','Slovénie',NULL,'+386','🇸🇮','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(76,'EE','EST','Estonie',NULL,'+372','🇪🇪','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(77,'LV','LVA','Lettonie',NULL,'+371','🇱🇻','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(78,'LT','LTU','Lituanie',NULL,'+370','🇱🇹','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(79,'FI','FIN','Finlande',NULL,'+358','🇫🇮','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(80,'SE','SWE','Suède',NULL,'+46','🇸🇪','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(81,'NO','NOR','Norvège',NULL,'+47','🇳🇴','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(82,'DK','DNK','Danemark',NULL,'+45','🇩🇰','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(83,'IS','ISL','Islande',NULL,'+354','🇮🇸','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(84,'MT','MLT','Malte',NULL,'+356','🇲🇹','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(85,'CY','CYP','Chypre',NULL,'+357','🇨🇾','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(86,'AL','ALB','Albanie',NULL,'+355','🇦🇱','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(87,'BA','BIH','Bosnie-Herzégovine',NULL,'+387','🇧🇦','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(88,'MK','MKD','Macédoine du Nord',NULL,'+389','🇲🇰','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(89,'ME','MNE','Monténégro',NULL,'+382','🇲🇪','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(90,'RS','SRB','Serbie',NULL,'+381','🇷🇸','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(91,'XK','XKX','Kosovo',NULL,'+383','🇽🇰','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(92,'MD','MDA','Moldavie',NULL,'+373','🇲🇩','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(93,'UA','UKR','Ukraine',NULL,'+380','🇺🇦','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(94,'BY','BLR','Biélorussie',NULL,'+375','🇧🇾','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(95,'RU','RUS','Russie',NULL,'+7','🇷🇺','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(96,'AD','AND','Andorre',NULL,'+376','🇦🇩','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(97,'MC','MCO','Monaco',NULL,'+377','🇲🇨','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(98,'SM','SMR','Saint-Marin',NULL,'+378','🇸🇲','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(99,'VA','VAT','Vatican',NULL,'+379','🇻🇦','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(100,'LI','LIE','Liechtenstein',NULL,'+423','🇱🇮','Europe',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(101,'US','USA','États-Unis',NULL,'+1','🇺🇸','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(102,'CA','CAN','Canada',NULL,'+1','🇨🇦','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(103,'MX','MEX','Mexique',NULL,'+52','🇲🇽','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(104,'BR','BRA','Brésil',NULL,'+55','🇧🇷','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(105,'AR','ARG','Argentine',NULL,'+54','🇦🇷','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(106,'CL','CHL','Chili',NULL,'+56','🇨🇱','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(107,'CO','COL','Colombie',NULL,'+57','🇨🇴','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(108,'PE','PER','Pérou',NULL,'+51','🇵🇪','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(109,'VE','VEN','Venezuela',NULL,'+58','🇻🇪','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(110,'EC','ECU','Équateur',NULL,'+593','🇪🇨','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(111,'BO','BOL','Bolivie',NULL,'+591','🇧🇴','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(112,'PY','PRY','Paraguay',NULL,'+595','🇵🇾','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(113,'UY','URY','Uruguay',NULL,'+598','🇺🇾','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(114,'GY','GUY','Guyana',NULL,'+592','🇬🇾','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(115,'SR','SUR','Suriname',NULL,'+597','🇸🇷','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(116,'PA','PAN','Panama',NULL,'+507','🇵🇦','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(117,'CR','CRI','Costa Rica',NULL,'+506','🇨🇷','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(118,'NI','NIC','Nicaragua',NULL,'+505','🇳🇮','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(119,'HN','HND','Honduras',NULL,'+504','🇭🇳','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(120,'SV','SLV','Salvador',NULL,'+503','🇸🇻','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(121,'GT','GTM','Guatemala',NULL,'+502','🇬🇹','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(122,'BZ','BLZ','Belize',NULL,'+501','🇧🇿','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(123,'CU','CUB','Cuba',NULL,'+53','🇨🇺','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(124,'DO','DOM','République dominicaine',NULL,'+1','🇩🇴','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(125,'HT','HTI','Haïti',NULL,'+509','🇭🇹','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(126,'JM','JAM','Jamaïque',NULL,'+1','🇯🇲','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(127,'TT','TTO','Trinité-et-Tobago',NULL,'+1','🇹🇹','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(128,'BS','BHS','Bahamas',NULL,'+1','🇧🇸','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(129,'BB','BRB','Barbade',NULL,'+1','🇧🇧','Amérique',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(130,'CN','CHN','Chine',NULL,'+86','🇨🇳','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(131,'JP','JPN','Japon',NULL,'+81','🇯🇵','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(132,'KR','KOR','Corée du Sud',NULL,'+82','🇰🇷','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(133,'KP','PRK','Corée du Nord',NULL,'+850','🇰🇵','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(134,'IN','IND','Inde',NULL,'+91','🇮🇳','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(135,'PK','PAK','Pakistan',NULL,'+92','🇵🇰','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(136,'BD','BGD','Bangladesh',NULL,'+880','🇧🇩','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(137,'LK','LKA','Sri Lanka',NULL,'+94','🇱🇰','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(138,'NP','NPL','Népal',NULL,'+977','🇳🇵','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(139,'BT','BTN','Bhoutan',NULL,'+975','🇧🇹','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(140,'AF','AFG','Afghanistan',NULL,'+93','🇦🇫','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(141,'IR','IRN','Iran',NULL,'+98','🇮🇷','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(142,'IQ','IRQ','Irak',NULL,'+964','🇮🇶','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(143,'SY','SYR','Syrie',NULL,'+963','🇸🇾','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(144,'TR','TUR','Turquie',NULL,'+90','🇹🇷','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(145,'IL','ISR','Israël',NULL,'+972','🇮🇱','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(146,'PS','PSE','Palestine',NULL,'+970','🇵🇸','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(147,'JO','JOR','Jordanie',NULL,'+962','🇯🇴','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(148,'LB','LBN','Liban',NULL,'+961','🇱🇧','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(149,'SA','SAU','Arabie saoudite',NULL,'+966','🇸🇦','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(150,'AE','ARE','Émirats arabes unis',NULL,'+971','🇦🇪','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(151,'QA','QAT','Qatar',NULL,'+974','🇶🇦','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(152,'BH','BHR','Bahreïn',NULL,'+973','🇧🇭','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(153,'KW','KWT','Koweït',NULL,'+965','🇰🇼','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(154,'OM','OMN','Oman',NULL,'+968','🇴🇲','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(155,'YE','YEM','Yémen',NULL,'+967','🇾🇪','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(156,'TH','THA','Thaïlande',NULL,'+66','🇹🇭','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(157,'VN','VNM','Viêt Nam',NULL,'+84','🇻🇳','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(158,'KH','KHM','Cambodge',NULL,'+855','🇰🇭','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(159,'LA','LAO','Laos',NULL,'+856','🇱🇦','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(160,'MM','MMR','Birmanie',NULL,'+95','🇲🇲','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(161,'MY','MYS','Malaisie',NULL,'+60','🇲🇾','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(162,'SG','SGP','Singapour',NULL,'+65','🇸🇬','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(163,'ID','IDN','Indonésie',NULL,'+62','🇮🇩','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(164,'PH','PHL','Philippines',NULL,'+63','🇵🇭','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(165,'BN','BRN','Brunei',NULL,'+673','🇧🇳','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(166,'TL','TLS','Timor oriental',NULL,'+670','🇹🇱','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(167,'MN','MNG','Mongolie',NULL,'+976','🇲🇳','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(168,'KZ','KAZ','Kazakhstan',NULL,'+7','🇰🇿','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(169,'UZ','UZB','Ouzbékistan',NULL,'+998','🇺🇿','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(170,'KG','KGZ','Kirghizistan',NULL,'+996','🇰🇬','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(171,'TJ','TJK','Tadjikistan',NULL,'+992','🇹🇯','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(172,'TM','TKM','Turkménistan',NULL,'+993','🇹🇲','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(173,'GE','GEO','Géorgie',NULL,'+995','🇬🇪','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(174,'AM','ARM','Arménie',NULL,'+374','🇦🇲','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(175,'AZ','AZE','Azerbaïdjan',NULL,'+994','🇦🇿','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(176,'MV','MDV','Maldives',NULL,'+960','🇲🇻','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(177,'HK','HKG','Hong Kong',NULL,'+852','🇭🇰','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(178,'MO','MAC','Macao',NULL,'+853','🇲🇴','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(179,'TW','TWN','Taïwan',NULL,'+886','🇹🇼','Asie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(180,'AU','AUS','Australie',NULL,'+61','🇦🇺','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(181,'NZ','NZL','Nouvelle-Zélande',NULL,'+64','🇳🇿','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(182,'FJ','FJI','Fidji',NULL,'+679','🇫🇯','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(183,'PG','PNG','Papouasie-Nouvelle-Guinée',NULL,'+675','🇵🇬','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(184,'SB','SLB','Salomon',NULL,'+677','🇸🇧','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(185,'VU','VUT','Vanuatu',NULL,'+678','🇻🇺','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(186,'WS','WSM','Samoa',NULL,'+685','🇼🇸','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(187,'TO','TON','Tonga',NULL,'+676','🇹🇴','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(188,'KI','KIR','Kiribati',NULL,'+686','🇰🇮','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(189,'TV','TUV','Tuvalu',NULL,'+688','🇹🇻','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(190,'NR','NRU','Nauru',NULL,'+674','🇳🇷','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(191,'PW','PLW','Palaos',NULL,'+680','🇵🇼','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(192,'MH','MHL','Îles Marshall',NULL,'+692','🇲🇭','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(193,'FM','FSM','Micronésie',NULL,'+691','🇫🇲','Océanie',100,'2026-04-09 19:44:52','2026-04-09 19:44:52');
/*!40000 ALTER TABLE `intranet_pays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_pieces_jointes`
--

DROP TABLE IF EXISTS `intranet_pieces_jointes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_pieces_jointes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attachable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachable_id` bigint unsigned NOT NULL,
  `nom_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chemin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_mime` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille` bigint unsigned NOT NULL DEFAULT '0',
  `categorie` enum('image','video','document','audio','autre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'autre',
  `ordre` smallint unsigned NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_pieces_jointes_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`),
  KEY `intranet_pieces_jointes_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_pieces_jointes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_pieces_jointes`
--

LOCK TABLES `intranet_pieces_jointes` WRITE;
/*!40000 ALTER TABLE `intranet_pieces_jointes` DISABLE KEYS */;
INSERT INTO `intranet_pieces_jointes` VALUES (1,'App\\Models\\Intranet\\Annonce',4,'Capture d’écran 2026-04-07 à 17.13.05.png','intranet/annonces/4/XEPcuu0wfieKMzxtj3UMbzyYkzuAEtD9X3Yhv4Xu.png','image/png',3468047,'image',0,1,'2026-04-09 17:31:14','2026-04-09 17:31:14'),(2,'App\\Models\\Intranet\\Annonce',4,'Capture d’écran 2026-04-07 à 17.13.11.png','intranet/annonces/4/rBqi59ItzbmFGaE9n2Habr3ZP9IZJsjCieRpqYyT.png','image/png',3737796,'image',1,1,'2026-04-09 17:31:14','2026-04-09 17:31:14'),(3,'App\\Models\\Intranet\\Annonce',4,'Capture d’écran 2026-04-07 à 17.13.18.png','intranet/annonces/4/VevEVcNlG09904P2m3v0IKu1ZiqhYF7VeLa0KZEq.png','image/png',3731352,'image',2,1,'2026-04-09 17:31:14','2026-04-09 17:31:14');
/*!40000 ALTER TABLE `intranet_pieces_jointes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_priorites`
--

DROP TABLE IF EXISTS `intranet_priorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_priorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#64748b',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_priorites`
--

LOCK TABLES `intranet_priorites` WRITE;
/*!40000 ALTER TABLE `intranet_priorites` DISABLE KEYS */;
INSERT INTO `intranet_priorites` VALUES (1,'Basse','#94A3B8','2026-04-07 17:16:57','2026-04-07 17:16:57'),(2,'Normal','#4F46E5','2026-04-07 17:16:57','2026-04-07 17:16:57'),(3,'Haute','#D97706','2026-04-07 17:16:57','2026-04-07 17:16:57'),(4,'Urgente','#DC2626','2026-04-07 17:16:57','2026-04-07 17:16:57');
/*!40000 ALTER TABLE `intranet_priorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_changements`
--

DROP TABLE IF EXISTS `intranet_projet_changements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_changements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('perimetre','delai','cout','qualite','ressource','autre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'autre',
  `justification` text COLLATE utf8mb4_unicode_ci,
  `impact_cout` decimal(15,2) DEFAULT NULL,
  `impact_delai_jours` int DEFAULT NULL,
  `impact_qualite` text COLLATE utf8mb4_unicode_ci,
  `impact_risques` text COLLATE utf8mb4_unicode_ci,
  `statut` enum('soumis','en_analyse','approuve','rejete','implemente','annule') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'soumis',
  `demandeur_id` bigint unsigned NOT NULL,
  `approuve_par` bigint unsigned DEFAULT NULL,
  `date_soumission` date NOT NULL,
  `date_decision` date DEFAULT NULL,
  `decision_commentaire` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_changements_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_changements_demandeur_id_foreign` (`demandeur_id`),
  KEY `intranet_projet_changements_approuve_par_foreign` (`approuve_par`),
  KEY `intranet_projet_changements_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_projet_changements_approuve_par_foreign` FOREIGN KEY (`approuve_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_changements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_changements_demandeur_id_foreign` FOREIGN KEY (`demandeur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_changements_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_changements`
--

LOCK TABLES `intranet_projet_changements` WRITE;
/*!40000 ALTER TABLE `intranet_projet_changements` DISABLE KEYS */;
INSERT INTO `intranet_projet_changements` VALUES (1,2,'Ajout module gestion documentaire','Le sponsor demande l\'ajout d\'un module GED non prévu initialement.','perimetre','Besoin exprimé par la direction juridique.',8000000.00,30,NULL,NULL,'soumis',2,NULL,'2026-03-27',NULL,NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(2,2,'Report de la mise en production de 2 semaines','Tests insuffisants nécessitent un délai supplémentaire.','delai','Qualité insuffisante des livrables actuels.',2500000.00,14,NULL,NULL,'soumis',1,NULL,'2026-03-27',NULL,NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(3,2,'Remplacement du prestataire réseau','Le prestataire actuel ne respecte pas les SLA.','autre','Performances réseau insuffisantes.',3000000.00,7,NULL,NULL,'approuve',1,1,'2026-04-03','2026-04-12','Approuvé. Nouveau prestataire identifié.',1,'2026-04-17 12:41:18','2026-04-17 12:44:49');
/*!40000 ALTER TABLE `intranet_projet_changements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_couts`
--

DROP TABLE IF EXISTS `intranet_projet_couts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_couts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `phase_id` bigint unsigned DEFAULT NULL,
  `tache_id` bigint unsigned DEFAULT NULL,
  `activite_id` bigint unsigned DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` enum('main_oeuvre','materiel','service','deplacement','formation','autre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'autre',
  `montant_estime` decimal(15,2) NOT NULL DEFAULT '0.00',
  `montant_reel` decimal(15,2) NOT NULL DEFAULT '0.00',
  `date_cout` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_couts_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_couts_tache_id_foreign` (`tache_id`),
  KEY `intranet_projet_couts_activite_id_foreign` (`activite_id`),
  KEY `intranet_projet_couts_created_by_foreign` (`created_by`),
  KEY `intranet_projet_couts_phase_id_foreign` (`phase_id`),
  CONSTRAINT `intranet_projet_couts_activite_id_foreign` FOREIGN KEY (`activite_id`) REFERENCES `intranet_activites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_couts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_couts_phase_id_foreign` FOREIGN KEY (`phase_id`) REFERENCES `intranet_projet_phases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_couts_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_couts_tache_id_foreign` FOREIGN KEY (`tache_id`) REFERENCES `intranet_taches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_couts`
--

LOCK TABLES `intranet_projet_couts` WRITE;
/*!40000 ALTER TABLE `intranet_projet_couts` DISABLE KEYS */;
INSERT INTO `intranet_projet_couts` VALUES (1,2,NULL,NULL,NULL,'Licences cloud Azure','service',15000000.00,12500000.00,'2026-03-17',NULL,1,'2026-04-17 12:40:22','2026-04-17 12:44:49'),(2,2,NULL,NULL,NULL,'Prestation audit externe','service',5000000.00,5800000.00,'2026-02-17',NULL,1,'2026-04-17 12:40:22','2026-04-17 12:44:49'),(3,2,NULL,NULL,NULL,'Développement sur mesure','main_oeuvre',25000000.00,8000000.00,'2026-04-17',NULL,1,'2026-04-17 12:40:22','2026-04-17 12:44:49'),(4,2,NULL,NULL,NULL,'Formation équipe interne','formation',3000000.00,1200000.00,'2026-04-03',NULL,1,'2026-04-17 12:40:22','2026-04-17 12:44:49'),(5,2,NULL,NULL,NULL,'Matériel serveurs backup','materiel',8000000.00,9200000.00,'2026-03-27',NULL,1,'2026-04-17 12:40:22','2026-04-17 12:44:49');
/*!40000 ALTER TABLE `intranet_projet_couts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_evm`
--

DROP TABLE IF EXISTS `intranet_projet_evm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_evm` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `date_mesure` date NOT NULL,
  `bac` decimal(15,2) DEFAULT NULL,
  `pv` decimal(15,2) DEFAULT NULL,
  `ev` decimal(15,2) DEFAULT NULL,
  `ac` decimal(15,2) DEFAULT NULL,
  `sv` decimal(15,2) DEFAULT NULL,
  `cv` decimal(15,2) DEFAULT NULL,
  `spi` decimal(8,4) DEFAULT NULL,
  `cpi` decimal(8,4) DEFAULT NULL,
  `etc` decimal(15,2) DEFAULT NULL,
  `eac` decimal(15,2) DEFAULT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_evm_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_evm_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_projet_evm_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_evm_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_evm`
--

LOCK TABLES `intranet_projet_evm` WRITE;
/*!40000 ALTER TABLE `intranet_projet_evm` DISABLE KEYS */;
INSERT INTO `intranet_projet_evm` VALUES (1,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:40:22','2026-04-17 12:40:22'),(2,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:40:22','2026-04-17 12:40:22'),(3,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:40:34','2026-04-17 12:40:34'),(4,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:40:34','2026-04-17 12:40:34'),(5,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:41:18','2026-04-17 12:41:18'),(6,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:41:18','2026-04-17 12:41:18'),(7,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:41:27','2026-04-17 12:41:27'),(8,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:41:27','2026-04-17 12:41:27'),(9,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:41:57','2026-04-17 12:41:57'),(10,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:41:57','2026-04-17 12:41:57'),(11,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:42:06','2026-04-17 12:42:06'),(12,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:42:06','2026-04-17 12:42:06'),(13,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:42:44','2026-04-17 12:42:44'),(14,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:42:44','2026-04-17 12:42:44'),(15,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:43:05','2026-04-17 12:43:05'),(16,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:43:05','2026-04-17 12:43:05'),(17,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(18,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(19,2,'2026-03-17',75000000.00,20000000.00,18000000.00,22000000.00,-2000000.00,-4000000.00,0.9000,0.8200,69512195.00,91512195.00,'Dépassement coût identifié sur le poste infrastructure.',1,'2026-04-17 12:44:49','2026-04-17 12:44:49'),(20,2,'2026-04-17',75000000.00,30000000.00,26250000.00,36700000.00,-3750000.00,-10450000.00,0.8750,0.7150,68181818.00,104881818.00,'SPI et CPI en baisse. Action corrective requise sur les coûts.',1,'2026-04-17 12:44:49','2026-04-17 12:44:49');
/*!40000 ALTER TABLE `intranet_projet_evm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_lecons`
--

DROP TABLE IF EXISTS `intranet_projet_lecons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_lecons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('succes','echec','amelioration') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'amelioration',
  `categorie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `impact` text COLLATE utf8mb4_unicode_ci,
  `recommandation` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('brouillon','valide','publie') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_lecons_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_lecons_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_projet_lecons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_lecons_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_lecons`
--

LOCK TABLES `intranet_projet_lecons` WRITE;
/*!40000 ALTER TABLE `intranet_projet_lecons` DISABLE KEYS */;
INSERT INTO `intranet_projet_lecons` VALUES (1,2,'L\'audit initial a été sous-estimé en durée','echec','Planification','L\'audit du SI existant a pris 3 semaines au lieu de 2.','Décalage de 1 semaine sur la phase suivante.','Prévoir 50% de marge sur les audits techniques.','valide',1,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(2,2,'La documentation automatisée a accéléré la conception','succes','Technique','L\'utilisation d\'outils de génération automatique de documentation a réduit le temps de rédaction.','Gain de 2 semaines.','Systématiser l\'utilisation de ces outils.','valide',1,'2026-04-17 12:44:41','2026-04-17 12:44:41'),(3,2,'Impliquer les utilisateurs plus tôt','amelioration','Organisationnel','Les retours utilisateurs arrivent tard dans le cycle.',NULL,'Organiser des démos bimensuelles dès la phase de conception.','valide',1,'2026-04-17 12:44:41','2026-04-17 12:44:41');
/*!40000 ALTER TABLE `intranet_projet_lecons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_livrables`
--

DROP TABLE IF EXISTS `intranet_projet_livrables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_livrables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `phase_id` bigint unsigned DEFAULT NULL,
  `tache_id` bigint unsigned DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `criteres_acceptation` text COLLATE utf8mb4_unicode_ci,
  `statut` enum('planifie','en_cours','livre','accepte','rejete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planifie',
  `date_prevue` date DEFAULT NULL,
  `date_livraison` date DEFAULT NULL,
  `responsable_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_livrables_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_livrables_phase_id_foreign` (`phase_id`),
  KEY `intranet_projet_livrables_tache_id_foreign` (`tache_id`),
  KEY `intranet_projet_livrables_responsable_id_foreign` (`responsable_id`),
  KEY `intranet_projet_livrables_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_projet_livrables_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_livrables_phase_id_foreign` FOREIGN KEY (`phase_id`) REFERENCES `intranet_projet_phases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_livrables_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_livrables_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_livrables_tache_id_foreign` FOREIGN KEY (`tache_id`) REFERENCES `intranet_taches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_livrables`
--

LOCK TABLES `intranet_projet_livrables` WRITE;
/*!40000 ALTER TABLE `intranet_projet_livrables` DISABLE KEYS */;
INSERT INTO `intranet_projet_livrables` VALUES (1,2,2,NULL,'Document d\'architecture technique',NULL,'Validé par le comité technique. Couvre tous les modules.','en_cours','2026-05-01',NULL,1,1,'2026-04-17 12:41:57','2026-04-17 12:44:49'),(2,2,1,NULL,'Rapport d\'audit SI existant',NULL,NULL,'accepte','2026-02-17','2026-02-20',1,1,'2026-04-17 12:41:57','2026-04-17 12:44:49'),(3,2,3,NULL,'Module RH opérationnel',NULL,NULL,'planifie','2026-08-17',NULL,1,1,'2026-04-17 12:41:57','2026-04-17 12:44:49'),(4,2,2,NULL,'Plan de migration des données',NULL,NULL,'en_cours','2026-04-12',NULL,1,1,'2026-04-17 12:41:57','2026-04-17 12:44:49');
/*!40000 ALTER TABLE `intranet_projet_livrables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_parties_prenantes`
--

DROP TABLE IF EXISTS `intranet_projet_parties_prenantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_parties_prenantes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `contact_id` bigint unsigned DEFAULT NULL,
  `nom_externe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organisation_externe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_projet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie` enum('interne','externe','regulateur','media','autre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'interne',
  `interet` tinyint unsigned NOT NULL DEFAULT '3',
  `influence` tinyint unsigned NOT NULL DEFAULT '3',
  `engagement_actuel` enum('resistant','neutre','conscient','favorable','champion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'neutre',
  `engagement_desire` enum('resistant','neutre','conscient','favorable','champion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'favorable',
  `strategie_engagement` text COLLATE utf8mb4_unicode_ci,
  `attentes` text COLLATE utf8mb4_unicode_ci,
  `preoccupations` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_parties_prenantes_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_parties_prenantes_user_id_foreign` (`user_id`),
  KEY `intranet_projet_parties_prenantes_contact_id_foreign` (`contact_id`),
  KEY `intranet_projet_parties_prenantes_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_projet_parties_prenantes_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `intranet_contacts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_parties_prenantes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_parties_prenantes_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_parties_prenantes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_parties_prenantes`
--

LOCK TABLES `intranet_projet_parties_prenantes` WRITE;
/*!40000 ALTER TABLE `intranet_projet_parties_prenantes` DISABLE KEYS */;
INSERT INTO `intranet_projet_parties_prenantes` VALUES (1,2,1,NULL,NULL,NULL,'Chef de projet','interne',5,5,'champion','champion',NULL,'Livraison dans les délais et le budget.',NULL,1,'2026-04-17 12:42:44','2026-04-17 12:42:44'),(2,2,2,NULL,NULL,NULL,'Sponsor','interne',4,5,'favorable','champion',NULL,'ROI visible sous 12 mois.',NULL,1,'2026-04-17 12:42:44','2026-04-17 12:42:44'),(3,2,NULL,NULL,'Direction Générale','ANPI','Commanditaire','interne',5,5,'neutre','favorable',NULL,NULL,NULL,1,'2026-04-17 12:42:44','2026-04-17 12:42:44'),(4,2,NULL,NULL,'CloudTech SARL','Prestataire Cloud','Fournisseur infrastructure','externe',3,3,'favorable','favorable',NULL,NULL,NULL,1,'2026-04-17 12:42:44','2026-04-17 12:42:44');
/*!40000 ALTER TABLE `intranet_projet_parties_prenantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_phases`
--

DROP TABLE IF EXISTS `intranet_projet_phases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_phases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `code_wbs` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` int NOT NULL DEFAULT '0',
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `date_debut_reelle` date DEFAULT NULL,
  `date_fin_reelle` date DEFAULT NULL,
  `avancement` tinyint unsigned NOT NULL DEFAULT '0',
  `statut_id` bigint unsigned DEFAULT NULL,
  `statut_cloture` enum('ouvert','soumis','approuve','rejete','revisions') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ouvert',
  `justification_cloture` text COLLATE utf8mb4_unicode_ci,
  `soumis_le` timestamp NULL DEFAULT NULL,
  `valide_le` timestamp NULL DEFAULT NULL,
  `motif_rejet` text COLLATE utf8mb4_unicode_ci,
  `ponderation` decimal(5,2) DEFAULT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#4F46E5',
  `responsable_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_phases_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_phases_responsable_id_foreign` (`responsable_id`),
  KEY `intranet_projet_phases_created_by_foreign` (`created_by`),
  KEY `intranet_projet_phases_statut_id_foreign` (`statut_id`),
  CONSTRAINT `intranet_projet_phases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_phases_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_phases_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_phases_statut_id_foreign` FOREIGN KEY (`statut_id`) REFERENCES `intranet_statuts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_phases`
--

LOCK TABLES `intranet_projet_phases` WRITE;
/*!40000 ALTER TABLE `intranet_projet_phases` DISABLE KEYS */;
INSERT INTO `intranet_projet_phases` VALUES (1,2,'Analyse & Cadrage',NULL,'1.0',1,'2026-01-17','2026-03-17',NULL,NULL,100,NULL,'ouvert',NULL,NULL,NULL,NULL,NULL,'#4F46E5',1,1,'2026-04-17 12:24:53','2026-04-17 12:44:49',NULL),(2,2,'Conception technique',NULL,'2.0',2,'2026-03-17','2026-05-17',NULL,NULL,60,NULL,'ouvert',NULL,NULL,NULL,NULL,NULL,'#0891B2',2,1,'2026-04-17 12:24:53','2026-04-17 12:44:49',NULL),(3,2,'Développement',NULL,'3.0',3,'2026-05-17','2026-10-17',NULL,NULL,10,NULL,'ouvert',NULL,NULL,NULL,NULL,NULL,'#16A34A',1,1,'2026-04-17 12:24:53','2026-04-17 12:44:49',NULL),(4,2,'Tests & Déploiement',NULL,'4.0',4,'2026-10-17','2027-01-17',NULL,NULL,0,NULL,'ouvert',NULL,NULL,NULL,NULL,NULL,'#D97706',1,1,'2026-04-17 12:24:53','2026-04-17 12:44:49',NULL),(5,3,'Configuration CRM',NULL,'1.0',1,NULL,NULL,NULL,NULL,40,NULL,'ouvert',NULL,NULL,NULL,NULL,NULL,'#DB2777',NULL,1,'2026-04-17 12:44:41','2026-04-17 12:44:41',NULL),(6,5,'ANALYSE DU BESOIN','<p><br></p>',NULL,1,'2026-04-18','2026-04-18',NULL,NULL,0,2,'ouvert',NULL,NULL,NULL,NULL,10.00,'#0d9488',1,1,'2026-04-17 22:08:04','2026-04-18 06:34:35',NULL);
/*!40000 ALTER TABLE `intranet_projet_phases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_problemes`
--

DROP TABLE IF EXISTS `intranet_projet_problemes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_problemes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priorite_id` bigint unsigned DEFAULT NULL,
  `statut` enum('ouvert','en_cours','resolu','clos') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ouvert',
  `impact` text COLLATE utf8mb4_unicode_ci,
  `resolution` text COLLATE utf8mb4_unicode_ci,
  `responsable_id` bigint unsigned DEFAULT NULL,
  `risque_id` bigint unsigned DEFAULT NULL,
  `date_identification` date NOT NULL,
  `date_resolution` date DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_problemes_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_problemes_priorite_id_foreign` (`priorite_id`),
  KEY `intranet_projet_problemes_responsable_id_foreign` (`responsable_id`),
  KEY `intranet_projet_problemes_risque_id_foreign` (`risque_id`),
  KEY `intranet_projet_problemes_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_projet_problemes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_problemes_priorite_id_foreign` FOREIGN KEY (`priorite_id`) REFERENCES `intranet_priorites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_problemes_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_problemes_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_problemes_risque_id_foreign` FOREIGN KEY (`risque_id`) REFERENCES `intranet_projet_risques` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_problemes`
--

LOCK TABLES `intranet_projet_problemes` WRITE;
/*!40000 ALTER TABLE `intranet_projet_problemes` DISABLE KEYS */;
INSERT INTO `intranet_projet_problemes` VALUES (1,2,'Serveur de test indisponible depuis 3 jours','L\'environnement de test est en panne. Impact direct sur la phase de conception.',NULL,4,'ouvert','Blocage des tests de validation architecture.',NULL,2,NULL,'2026-04-10',NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(2,2,'API fournisseur non documentée','Le fournisseur cloud n\'a pas fourni la documentation API complète.',NULL,3,'en_cours','Retard de 5 jours sur le développement.',NULL,2,NULL,'2026-04-10',NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(3,2,'Conflit de versions sur les dépendances','Incompatibilité entre les versions de Laravel et le package LDAP.',NULL,2,'resolu','Résolu par mise à jour du package.','Upgrade du package vers v3.2.',2,NULL,'2026-04-10','2026-04-12',1,'2026-04-17 12:41:18','2026-04-17 12:44:49');
/*!40000 ALTER TABLE `intranet_projet_problemes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_ressources`
--

DROP TABLE IF EXISTS `intranet_projet_ressources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_ressources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role_projet_id` bigint unsigned DEFAULT NULL,
  `heures_allouees` decimal(8,2) DEFAULT NULL,
  `heures_reelles` decimal(8,2) NOT NULL DEFAULT '0.00',
  `taux_journalier` decimal(10,2) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_projet_ressources_projet_id_user_id_unique` (`projet_id`,`user_id`),
  KEY `intranet_projet_ressources_user_id_foreign` (`user_id`),
  KEY `intranet_projet_ressources_role_projet_id_foreign` (`role_projet_id`),
  CONSTRAINT `intranet_projet_ressources_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_ressources_role_projet_id_foreign` FOREIGN KEY (`role_projet_id`) REFERENCES `intranet_roles_projet` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projet_ressources_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_ressources`
--

LOCK TABLES `intranet_projet_ressources` WRITE;
/*!40000 ALTER TABLE `intranet_projet_ressources` DISABLE KEYS */;
INSERT INTO `intranet_projet_ressources` VALUES (1,2,1,3,1200.00,150.00,120000.00,'2026-01-17','2027-01-17',1,'2026-04-17 12:41:57','2026-04-17 12:44:49'),(2,2,2,4,600.00,220.00,180000.00,'2026-01-17','2027-01-17',1,'2026-04-17 12:41:57','2026-04-17 12:44:49'),(3,5,1,1,10.00,0.00,NULL,'2026-04-22','2026-04-22',1,'2026-04-21 09:32:04','2026-04-21 09:32:04');
/*!40000 ALTER TABLE `intranet_projet_ressources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_risques`
--

DROP TABLE IF EXISTS `intranet_projet_risques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_risques` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `probabilite` tinyint unsigned NOT NULL DEFAULT '3',
  `impact` tinyint unsigned NOT NULL DEFAULT '3',
  `score` tinyint unsigned GENERATED ALWAYS AS ((`probabilite` * `impact`)) STORED,
  `type_risque` enum('menace','opportunite') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menace',
  `strategie` enum('eviter','transferer','attenuer','accepter','exploiter','partager','ameliorer') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_reponse` text COLLATE utf8mb4_unicode_ci,
  `plan_contingence` text COLLATE utf8mb4_unicode_ci,
  `cout_contingence` decimal(15,2) DEFAULT NULL,
  `statut` enum('identifie','analyse','traite','surveille','clos') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'identifie',
  `responsable_id` bigint unsigned DEFAULT NULL,
  `date_identification` date DEFAULT NULL,
  `date_revue` date DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_risques_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_risques_responsable_id_foreign` (`responsable_id`),
  KEY `intranet_projet_risques_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_projet_risques_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_risques_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_risques_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_risques`
--

LOCK TABLES `intranet_projet_risques` WRITE;
/*!40000 ALTER TABLE `intranet_projet_risques` DISABLE KEYS */;
INSERT INTO `intranet_projet_risques` (`id`, `projet_id`, `titre`, `description`, `categorie`, `probabilite`, `impact`, `type_risque`, `strategie`, `plan_reponse`, `plan_contingence`, `cout_contingence`, `statut`, `responsable_id`, `date_identification`, `date_revue`, `created_by`, `created_at`, `updated_at`) VALUES (1,2,'Dépassement budgétaire infrastructure','Les coûts cloud dépassent les estimations initiales de 20%.','Financier',4,5,'menace','attenuer','Négocier les tarifs cloud. Envisager un plan B on-premise partiel.',NULL,NULL,'identifie',1,'2026-03-20',NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(2,2,'Résistance au changement des utilisateurs','Les utilisateurs métier risquent de ne pas adopter le nouveau SI.','Organisationnel',3,4,'menace','attenuer','Plan de communication et formation renforcé.',NULL,NULL,'analyse',1,'2026-03-27',NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(3,2,'Perte de données durant migration','La migration des données existantes peut entraîner des pertes.','Technique',2,5,'menace','eviter','Double sauvegarde + migration par lots avec validation.',NULL,NULL,'identifie',1,'2026-02-27',NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(4,2,'Indisponibilité du prestataire cloud','Le fournisseur cloud pourrait avoir des problèmes de disponibilité.','Externe',2,4,'menace','transferer','Clause SLA dans le contrat + prestataire backup identifié.',NULL,NULL,'analyse',1,'2026-03-27',NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(5,2,'Démission du développeur senior','Le développeur clé pourrait quitter le projet.','RH',3,5,'menace','attenuer','Documentation technique et formation croisée.',NULL,NULL,'identifie',1,'2026-02-20',NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(6,2,'Réduction des délais grâce à l\'IA','L\'IA pourrait accélérer le développement de 20%.','Technique',3,3,'opportunite','exploiter','Intégrer des outils IA dans le développement.',NULL,NULL,'identifie',1,'2026-03-20',NULL,1,'2026-04-17 12:41:18','2026-04-17 12:44:49'),(7,3,'Adoption faible par les commerciaux','Les commerciaux pourraient ne pas utiliser le CRM au quotidien.','Organisationnel',4,4,'menace','attenuer','Accompagnement individualisé et gamification.',NULL,NULL,'identifie',2,'2026-04-03',NULL,1,'2026-04-17 12:44:41','2026-04-17 12:44:49');
/*!40000 ALTER TABLE `intranet_projet_risques` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projet_user`
--

DROP TABLE IF EXISTS `intranet_projet_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projet_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projet_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projet_user_projet_id_foreign` (`projet_id`),
  KEY `intranet_projet_user_user_id_foreign` (`user_id`),
  CONSTRAINT `intranet_projet_user_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_projet_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projet_user`
--

LOCK TABLES `intranet_projet_user` WRITE;
/*!40000 ALTER TABLE `intranet_projet_user` DISABLE KEYS */;
INSERT INTO `intranet_projet_user` VALUES (1,2,1),(2,2,2),(3,3,1),(4,3,2),(5,5,1),(6,5,2);
/*!40000 ALTER TABLE `intranet_projet_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_projets`
--

DROP TABLE IF EXISTS `intranet_projets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_projets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_projet` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `objectifs` text COLLATE utf8mb4_unicode_ci,
  `perimetre_inclus` text COLLATE utf8mb4_unicode_ci,
  `perimetre_exclus` text COLLATE utf8mb4_unicode_ci,
  `hypotheses` text COLLATE utf8mb4_unicode_ci,
  `contraintes` text COLLATE utf8mb4_unicode_ci,
  `budget_approuve` decimal(15,2) DEFAULT NULL,
  `priorite_id` bigint unsigned DEFAULT NULL,
  `statut_id` bigint unsigned DEFAULT NULL,
  `objectif_id` bigint unsigned DEFAULT NULL,
  `statut_cloture` enum('ouvert','soumis','approuve','rejete','revisions') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ouvert',
  `justification_cloture` text COLLATE utf8mb4_unicode_ci,
  `soumis_le` timestamp NULL DEFAULT NULL,
  `valide_le` timestamp NULL DEFAULT NULL,
  `motif_rejet` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `sponsor_id` bigint unsigned DEFAULT NULL,
  `chef_projet_id` bigint unsigned DEFAULT NULL,
  `avancement` tinyint unsigned NOT NULL DEFAULT '0',
  `devise` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'XAF',
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_projets_priorite_id_foreign` (`priorite_id`),
  KEY `intranet_projets_statut_id_foreign` (`statut_id`),
  KEY `intranet_projets_created_by_foreign` (`created_by`),
  KEY `intranet_projets_sponsor_id_foreign` (`sponsor_id`),
  KEY `intranet_projets_chef_projet_id_foreign` (`chef_projet_id`),
  KEY `intranet_projets_objectif_id_foreign` (`objectif_id`),
  CONSTRAINT `intranet_projets_chef_projet_id_foreign` FOREIGN KEY (`chef_projet_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projets_objectif_id_foreign` FOREIGN KEY (`objectif_id`) REFERENCES `intranet_objectifs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projets_priorite_id_foreign` FOREIGN KEY (`priorite_id`) REFERENCES `intranet_priorites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projets_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_projets_statut_id_foreign` FOREIGN KEY (`statut_id`) REFERENCES `intranet_statuts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_projets`
--

LOCK TABLES `intranet_projets` WRITE;
/*!40000 ALTER TABLE `intranet_projets` DISABLE KEYS */;
INSERT INTO `intranet_projets` VALUES (1,'Déploiement OptimiZe ERP',NULL,NULL,'Mise en production complète de la solution ERP OptimiZe.',NULL,NULL,NULL,NULL,NULL,NULL,3,2,NULL,'ouvert',NULL,NULL,NULL,NULL,1,NULL,NULL,0,'XAF','2026-03-07','2026-06-07','2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(2,'Refonte du Système d\'Information','PRJ-001','IT','<p>Modernisation complète du SI : migration cloud, refonte des applications métier, sécurisation des données.</p>','<ul><li>Migrer 100% des services vers le cloud</li><li>Réduire les coûts d\'infrastructure de 30%</li><li>Améliorer le temps de réponse des applications</li></ul>',NULL,NULL,NULL,NULL,75000000.00,3,2,NULL,'ouvert',NULL,NULL,NULL,NULL,1,2,1,35,'XAF','2026-01-17','2027-01-17','2026-04-17 12:24:53','2026-04-17 12:44:49',NULL),(3,'Déploiement CRM Commercial','PRJ-002','Commercial','<p>Mise en place du CRM pour l\'équipe commerciale : contacts, opportunités, pipeline de vente.</p>',NULL,NULL,NULL,NULL,NULL,15000000.00,2,2,NULL,'ouvert',NULL,NULL,NULL,NULL,1,NULL,2,20,'XAF','2026-03-17','2026-07-17','2026-04-17 12:44:41','2026-04-17 12:44:49',NULL),(4,'Mise en conformité RGPD','PRJ-003','Juridique','<p>Audit et mise en conformité RGPD de l\'ensemble des traitements de données personnelles.</p>',NULL,NULL,NULL,NULL,NULL,5000000.00,3,4,NULL,'ouvert',NULL,NULL,NULL,NULL,1,NULL,1,100,'XAF','2025-10-17','2026-03-17','2026-04-17 12:44:41','2026-04-17 12:44:49',NULL),(5,'OptimiZe 360',NULL,NULL,'<p>OptimiZe est un ERP intelligent qui permet aux organisations de relier leur stratégie à leurs opérations, de piloter leurs projets selon les standards PMP, et de suivre en temps réel leur performance financière et opérationnelle. C’est un outil conçu pour améliorer la gouvernance, la prise de décision et l’efficacité globale des structures, notamment en Afrique.</p>','<p>Avec OptimiZe, une organisation peut :</p><ol><li data-list=\"bullet\"><span class=\"ql-ui\" contenteditable=\"false\"></span>📈 améliorer sa performance globale</li><li data-list=\"bullet\"><span class=\"ql-ui\" contenteditable=\"false\"></span>🎯 atteindre ses objectifs plus efficacement</li><li data-list=\"bullet\"><span class=\"ql-ui\" contenteditable=\"false\"></span>🔍 avoir une visibilité en temps réel</li><li data-list=\"bullet\"><span class=\"ql-ui\" contenteditable=\"false\"></span>🧩 éliminer les silos organisationnels</li><li data-list=\"bullet\"><span class=\"ql-ui\" contenteditable=\"false\"></span>⚙️ standardiser ses processus (PMP, gouvernance)</li></ol><p><br></p>',NULL,NULL,NULL,NULL,20000000.00,3,2,NULL,'ouvert',NULL,NULL,NULL,NULL,1,NULL,1,0,'XAF','2026-04-01','2026-04-30','2026-04-17 21:35:22','2026-04-17 21:35:22',NULL);
/*!40000 ALTER TABLE `intranet_projets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_publication_cibles`
--

DROP TABLE IF EXISTS `intranet_publication_cibles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_publication_cibles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `publication_id` bigint unsigned NOT NULL,
  `cible_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cible_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pub_cible_unique` (`publication_id`,`cible_type`,`cible_id`),
  KEY `intranet_publication_cibles_cible_type_cible_id_index` (`cible_type`,`cible_id`),
  CONSTRAINT `intranet_publication_cibles_publication_id_foreign` FOREIGN KEY (`publication_id`) REFERENCES `intranet_publications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_publication_cibles`
--

LOCK TABLES `intranet_publication_cibles` WRITE;
/*!40000 ALTER TABLE `intranet_publication_cibles` DISABLE KEYS */;
INSERT INTO `intranet_publication_cibles` VALUES (1,1,'user',1),(2,1,'user',2),(3,3,'user',1),(4,3,'user',2),(9,4,'user',1),(10,4,'user',2),(7,5,'user',1),(8,5,'user',2),(11,6,'user',1),(12,9,'user',2);
/*!40000 ALTER TABLE `intranet_publication_cibles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_publications`
--

DROP TABLE IF EXISTS `intranet_publications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_publications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `publishable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `publishable_id` bigint unsigned NOT NULL,
  `visibilite` enum('public','prive','brouillon') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'prive',
  `likes_actifs` tinyint(1) NOT NULL DEFAULT '0',
  `commentaires_actifs` tinyint(1) NOT NULL DEFAULT '0',
  `partage_actif` tinyint(1) NOT NULL DEFAULT '0',
  `publie_le` timestamp NULL DEFAULT NULL,
  `expire_le` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_publications_publishable_type_publishable_id_index` (`publishable_type`,`publishable_id`),
  KEY `intranet_publications_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_publications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_publications`
--

LOCK TABLES `intranet_publications` WRITE;
/*!40000 ALTER TABLE `intranet_publications` DISABLE KEYS */;
INSERT INTO `intranet_publications` VALUES (1,'App\\Models\\Intranet\\Annonce',4,'prive',0,0,0,'2026-04-08 23:00:00','2026-04-09 23:00:00',1,'2026-04-09 17:31:14','2026-04-09 17:31:14'),(2,'App\\Models\\Intranet\\News',3,'public',1,1,0,'2026-04-08 23:00:00','2026-04-08 23:00:00',1,'2026-04-09 18:21:00','2026-04-09 18:21:00'),(3,'App\\Models\\Intranet\\Courrier',1,'prive',0,0,0,'2026-04-09 22:07:42',NULL,1,'2026-04-09 22:07:42','2026-04-09 22:07:42'),(4,'App\\Models\\Intranet\\Ressource',1,'prive',0,0,0,'2026-04-10 20:27:35',NULL,1,'2026-04-10 19:57:44','2026-04-10 20:27:35'),(5,'App\\Models\\Intranet\\Ressource',2,'prive',1,1,0,'2026-04-10 20:00:04',NULL,1,'2026-04-10 20:00:04','2026-04-10 20:00:04'),(6,'App\\Models\\Intranet\\Ressource',3,'prive',1,1,0,'2026-04-10 20:38:25',NULL,1,'2026-04-10 20:38:25','2026-04-10 20:38:25'),(7,'App\\Models\\Intranet\\Media',1,'prive',0,0,0,'2026-04-10 20:58:24',NULL,1,'2026-04-10 20:58:24','2026-04-10 20:58:24'),(8,'App\\Models\\Intranet\\Media',2,'prive',0,0,0,'2026-04-10 21:06:35',NULL,1,'2026-04-10 21:06:35','2026-04-10 21:06:35'),(9,'App\\Models\\Intranet\\Tache',16,'prive',0,1,0,'2026-04-20 21:09:03',NULL,1,'2026-04-20 21:09:03','2026-04-20 21:09:03');
/*!40000 ALTER TABLE `intranet_publications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_rapports`
--

DROP TABLE IF EXISTS `intranet_rapports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_rapports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extrait` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('rapport','compte_rendu','note_interne','synthese') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rapport',
  `reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contenu` longtext COLLATE utf8mb4_unicode_ci,
  `statut` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'brouillon',
  `evenement_id` bigint unsigned DEFAULT NULL,
  `projet_id` bigint unsigned DEFAULT NULL,
  `phase_id` bigint unsigned DEFAULT NULL,
  `tache_id` bigint unsigned DEFAULT NULL,
  `activite_id` bigint unsigned DEFAULT NULL,
  `date_document` date DEFAULT NULL,
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `participants` json DEFAULT NULL,
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `valideur_id` bigint unsigned DEFAULT NULL,
  `statut_validation` enum('en_attente','approuve','rejete','revisions_demandees') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commentaire_validation` text COLLATE utf8mb4_unicode_ci,
  `valide_le` timestamp NULL DEFAULT NULL,
  `soumis_le` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_rapports_slug_unique` (`slug`),
  KEY `intranet_rapports_evenement_id_foreign` (`evenement_id`),
  KEY `intranet_rapports_created_by_foreign` (`created_by`),
  KEY `intranet_rapports_projet_id_foreign` (`projet_id`),
  KEY `intranet_rapports_type_index` (`type`),
  KEY `intranet_rapports_statut_index` (`statut`),
  KEY `intranet_rapports_phase_id_foreign` (`phase_id`),
  KEY `intranet_rapports_tache_id_foreign` (`tache_id`),
  KEY `intranet_rapports_activite_id_foreign` (`activite_id`),
  KEY `intranet_rapports_valideur_id_foreign` (`valideur_id`),
  CONSTRAINT `intranet_rapports_activite_id_foreign` FOREIGN KEY (`activite_id`) REFERENCES `intranet_activites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_rapports_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_rapports_evenement_id_foreign` FOREIGN KEY (`evenement_id`) REFERENCES `intranet_evenements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_rapports_phase_id_foreign` FOREIGN KEY (`phase_id`) REFERENCES `intranet_projet_phases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_rapports_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_rapports_tache_id_foreign` FOREIGN KEY (`tache_id`) REFERENCES `intranet_taches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_rapports_valideur_id_foreign` FOREIGN KEY (`valideur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_rapports`
--

LOCK TABLES `intranet_rapports` WRITE;
/*!40000 ALTER TABLE `intranet_rapports` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_rapports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_ressources`
--

DROP TABLE IF EXISTS `intranet_ressources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_ressources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('dossier','fichier') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fichier',
  `chemin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_original` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille` bigint unsigned NOT NULL DEFAULT '0',
  `categorie` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `acces_restreint` tinyint(1) NOT NULL DEFAULT '0',
  `version` smallint unsigned NOT NULL DEFAULT '1',
  `telechargements_count` int unsigned NOT NULL DEFAULT '0',
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `parent_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_ressources_slug_unique` (`slug`),
  KEY `intranet_ressources_created_by_foreign` (`created_by`),
  KEY `intranet_ressources_parent_id_index` (`parent_id`),
  KEY `intranet_ressources_type_index` (`type`),
  KEY `intranet_ressources_categorie_index` (`categorie`),
  CONSTRAINT `intranet_ressources_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_ressources_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `intranet_ressources` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_ressources`
--

LOCK TABLES `intranet_ressources` WRITE;
/*!40000 ALTER TABLE `intranet_ressources` DISABLE KEYS */;
INSERT INTO `intranet_ressources` VALUES (1,'dossier-juridique','DOSSIER JURIDIQUE','<p>Ensemble de document juridique</p>','dossier',NULL,NULL,NULL,0,NULL,'[]',NULL,0,0,1,0,0,2,1,'2026-04-10 19:57:44','2026-04-10 20:27:35',NULL),(2,'direction-generale','DIRECTION GENERALE','<p><br></p>','dossier',NULL,NULL,NULL,0,NULL,'[]',NULL,0,0,1,0,0,NULL,1,'2026-04-10 20:00:04','2026-04-10 20:00:04',NULL),(3,'fiche-circuit','FICHE CIRCUIT','<p><br></p>','fichier','intranet/ressources/qtun7epzJ1s7dtkkbkVKyoMeaDviWWskW98uq7V8.png','Capture d’écran 2026-04-05 à 12.50.48.png','image/png',314636,NULL,'[]',NULL,0,1,1,0,2,1,1,'2026-04-10 20:38:25','2026-04-10 20:39:07',NULL);
/*!40000 ALTER TABLE `intranet_ressources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_roles_projet`
--

DROP TABLE IF EXISTS `intranet_roles_projet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_roles_projet` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ordre` smallint unsigned NOT NULL DEFAULT '0',
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_roles_projet_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_roles_projet`
--

LOCK TABLES `intranet_roles_projet` WRITE;
/*!40000 ALTER TABLE `intranet_roles_projet` DISABLE KEYS */;
INSERT INTO `intranet_roles_projet` VALUES (1,'Chef de projet','chef','#0D9488',NULL,1,1,'2026-04-21 09:23:34','2026-04-21 09:23:34'),(2,'Co-chef de projet','co_chef','#0891B2',NULL,2,1,'2026-04-21 09:23:34','2026-04-21 09:23:34'),(3,'Membre','membre','#6366F1',NULL,3,1,'2026-04-21 09:23:34','2026-04-21 09:23:34'),(4,'Expert','expert','#7C3AED',NULL,4,1,'2026-04-21 09:23:34','2026-04-21 09:23:34'),(5,'Observateur','observateur','#94A3B8',NULL,5,1,'2026-04-21 09:23:34','2026-04-21 09:23:34'),(6,'Sponsor','sponsor','#D97706',NULL,6,1,'2026-04-21 09:23:34','2026-04-21 09:23:34');
/*!40000 ALTER TABLE `intranet_roles_projet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_secteurs_activite`
--

DROP TABLE IF EXISTS `intranet_secteurs_activite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_secteurs_activite` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#7C3AED',
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` smallint unsigned NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_secteurs_activite_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_secteurs_activite`
--

LOCK TABLES `intranet_secteurs_activite` WRITE;
/*!40000 ALTER TABLE `intranet_secteurs_activite` DISABLE KEYS */;
INSERT INTO `intranet_secteurs_activite` VALUES (1,'Agriculture','agriculture','#65A30D','fa-seedling',0,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(2,'Agroalimentaire','agroalimentaire','#84CC16','fa-wheat-awn',1,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(3,'Architecture','architecture','#0EA5E9','fa-compass-drafting',2,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(4,'Assurance','assurance','#0891B2','fa-shield-halved',3,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(5,'Automobile','automobile','#475569','fa-car',4,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(6,'Banque & Finance','banque','#4F46E5','fa-building-columns',5,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(7,'BTP & Construction','btp','#D97706','fa-helmet-safety',6,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(8,'Chimie','chimie','#7C3AED','fa-flask',7,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(9,'Commerce de détail','commerce','#EC4899','fa-store',8,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(10,'Conseil','conseil','#0D9488','fa-handshake',9,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(11,'Cosmétique','cosmetique','#DB2777','fa-spray-can-sparkles',10,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(12,'Distribution','distribution','#EA580C','fa-boxes-stacked',11,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(13,'Édition & Médias','edition','#9333EA','fa-newspaper',12,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(14,'Éducation','education','#2563EB','fa-graduation-cap',13,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(15,'Énergie','energie','#F59E0B','fa-bolt',14,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(16,'Environnement','environnement','#16A34A','fa-leaf',15,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(17,'Hôtellerie & Restauration','hotellerie','#C026D3','fa-utensils',16,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(18,'Immobilier','immobilier','#0369A1','fa-house',17,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(19,'Industrie','industrie','#525252','fa-industry',18,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(20,'Informatique & Tech','tech','#7C3AED','fa-microchip',19,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(21,'Logistique & Transport','logistique','#0284C7','fa-truck',20,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(22,'Luxe','luxe','#A16207','fa-gem',21,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(23,'Marketing & Communication','marketing','#F43F5E','fa-bullhorn',22,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(24,'Pharmacie','pharmacie','#06B6D4','fa-pills',23,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(25,'Santé','sante','#DC2626','fa-stethoscope',24,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(26,'Sport','sport','#EA580C','fa-dumbbell',25,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(27,'Télécommunications','telecom','#1D4ED8','fa-tower-broadcast',26,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(28,'Textile','textile','#A855F7','fa-shirt',27,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(29,'Tourisme','tourisme','#0EA5E9','fa-plane',28,'2026-04-09 19:44:52','2026-04-09 19:44:52'),(30,'Autre','autre','#64748B','fa-tag',29,'2026-04-09 19:44:52','2026-04-09 19:44:52');
/*!40000 ALTER TABLE `intranet_secteurs_activite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_services`
--

DROP TABLE IF EXISTS `intranet_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `chef_du_service_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_services_chef_du_service_id_foreign` (`chef_du_service_id`),
  CONSTRAINT `intranet_services_chef_du_service_id_foreign` FOREIGN KEY (`chef_du_service_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_services`
--

LOCK TABLES `intranet_services` WRITE;
/*!40000 ALTER TABLE `intranet_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_statuts`
--

DROP TABLE IF EXISTS `intranet_statuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_statuts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#64748b',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_statuts`
--

LOCK TABLES `intranet_statuts` WRITE;
/*!40000 ALTER TABLE `intranet_statuts` DISABLE KEYS */;
INSERT INTO `intranet_statuts` VALUES (1,'Non démarré','#64748b','2026-04-07 17:16:57','2026-04-07 17:16:57'),(2,'En cours','#4F46E5','2026-04-07 17:16:57','2026-04-07 17:16:57'),(3,'En pause','#D97706','2026-04-07 17:16:57','2026-04-07 17:16:57'),(4,'Terminé','#059669','2026-04-07 17:16:57','2026-04-07 17:16:57'),(5,'Annulé','#DC2626','2026-04-07 17:16:57','2026-04-07 17:16:57');
/*!40000 ALTER TABLE `intranet_statuts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_tache_checklist`
--

DROP TABLE IF EXISTS `intranet_tache_checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_tache_checklist` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tache_id` bigint unsigned NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complete` tinyint(1) NOT NULL DEFAULT '0',
  `ordre` int NOT NULL DEFAULT '0',
  `complete_par` bigint unsigned DEFAULT NULL,
  `complete_le` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_tache_checklist_tache_id_foreign` (`tache_id`),
  KEY `intranet_tache_checklist_complete_par_foreign` (`complete_par`),
  KEY `intranet_tache_checklist_created_by_foreign` (`created_by`),
  CONSTRAINT `intranet_tache_checklist_complete_par_foreign` FOREIGN KEY (`complete_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_tache_checklist_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_tache_checklist_tache_id_foreign` FOREIGN KEY (`tache_id`) REFERENCES `intranet_taches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_tache_checklist`
--

LOCK TABLES `intranet_tache_checklist` WRITE;
/*!40000 ALTER TABLE `intranet_tache_checklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_tache_checklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_tache_historique`
--

DROP TABLE IF EXISTS `intranet_tache_historique`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_tache_historique` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tache_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `action` enum('cree','modifie','soumis','approuve','rejete','revisions_demandees','resoumis','evalue','cloture','piece_jointe_ajoutee','commentaire') COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `note` tinyint unsigned DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_tache_historique_user_id_foreign` (`user_id`),
  KEY `intranet_tache_historique_tache_id_index` (`tache_id`),
  KEY `intranet_tache_historique_action_index` (`action`),
  CONSTRAINT `intranet_tache_historique_tache_id_foreign` FOREIGN KEY (`tache_id`) REFERENCES `intranet_taches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_tache_historique_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_tache_historique`
--

LOCK TABLES `intranet_tache_historique` WRITE;
/*!40000 ALTER TABLE `intranet_tache_historique` DISABLE KEYS */;
INSERT INTO `intranet_tache_historique` VALUES (1,16,1,'cree',NULL,NULL,'[]','2026-04-20 21:09:03','2026-04-20 21:09:03');
/*!40000 ALTER TABLE `intranet_tache_historique` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_tache_user`
--

DROP TABLE IF EXISTS `intranet_tache_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_tache_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tache_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `intranet_tache_user_tache_id_foreign` (`tache_id`),
  KEY `intranet_tache_user_user_id_foreign` (`user_id`),
  CONSTRAINT `intranet_tache_user_tache_id_foreign` FOREIGN KEY (`tache_id`) REFERENCES `intranet_taches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_tache_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_tache_user`
--

LOCK TABLES `intranet_tache_user` WRITE;
/*!40000 ALTER TABLE `intranet_tache_user` DISABLE KEYS */;
INSERT INTO `intranet_tache_user` VALUES (1,16,1),(2,16,2);
/*!40000 ALTER TABLE `intranet_tache_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_tache_valideurs`
--

DROP TABLE IF EXISTS `intranet_tache_valideurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_tache_valideurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tache_id` bigint unsigned NOT NULL,
  `valideur_type` enum('user','groupe') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valideur_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tache_valideur_unique` (`tache_id`,`valideur_type`,`valideur_id`),
  KEY `intranet_tache_valideurs_valideur_type_valideur_id_index` (`valideur_type`,`valideur_id`),
  CONSTRAINT `intranet_tache_valideurs_tache_id_foreign` FOREIGN KEY (`tache_id`) REFERENCES `intranet_taches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_tache_valideurs`
--

LOCK TABLES `intranet_tache_valideurs` WRITE;
/*!40000 ALTER TABLE `intranet_tache_valideurs` DISABLE KEYS */;
INSERT INTO `intranet_tache_valideurs` VALUES (1,16,'user',1,'2026-04-20 21:09:03','2026-04-20 21:09:03');
/*!40000 ALTER TABLE `intranet_tache_valideurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_taches`
--

DROP TABLE IF EXISTS `intranet_taches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_taches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `resume` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `besoins` text COLLATE utf8mb4_unicode_ci,
  `cout_execution` decimal(15,2) DEFAULT NULL,
  `devise_cout` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'XAF',
  `ponderation` decimal(5,2) DEFAULT NULL,
  `projet_id` bigint unsigned DEFAULT NULL,
  `objectif_id` bigint unsigned DEFAULT NULL,
  `phase_id` bigint unsigned DEFAULT NULL,
  `ordre` smallint unsigned NOT NULL DEFAULT '0',
  `heures_estimees` decimal(8,2) DEFAULT NULL,
  `heures_reelles` decimal(8,2) DEFAULT NULL,
  `avancement` tinyint unsigned NOT NULL DEFAULT '0',
  `responsable_id` bigint unsigned DEFAULT NULL,
  `est_jalon` tinyint(1) NOT NULL DEFAULT '0',
  `priorite_id` bigint unsigned DEFAULT NULL,
  `statut_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `date_debut_reelle` date DEFAULT NULL,
  `date_fin_reelle` date DEFAULT NULL,
  `temps_reel_heures` decimal(8,2) DEFAULT NULL,
  `statut_validation` enum('non_soumis','soumis','approuve','rejete','revisions') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'non_soumis',
  `soumis_le` timestamp NULL DEFAULT NULL,
  `valide_le` timestamp NULL DEFAULT NULL,
  `motif_rejet` text COLLATE utf8mb4_unicode_ci,
  `note_evaluation` tinyint unsigned DEFAULT NULL,
  `appreciation` text COLLATE utf8mb4_unicode_ci,
  `evalue_par` bigint unsigned DEFAULT NULL,
  `evalue_le` timestamp NULL DEFAULT NULL,
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `deadline_alerted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_taches_slug_unique` (`slug`),
  KEY `intranet_taches_projet_id_foreign` (`projet_id`),
  KEY `intranet_taches_priorite_id_foreign` (`priorite_id`),
  KEY `intranet_taches_statut_id_foreign` (`statut_id`),
  KEY `intranet_taches_created_by_foreign` (`created_by`),
  KEY `intranet_taches_responsable_id_foreign` (`responsable_id`),
  KEY `intranet_taches_phase_id_foreign` (`phase_id`),
  KEY `intranet_taches_evalue_par_foreign` (`evalue_par`),
  KEY `intranet_taches_objectif_id_foreign` (`objectif_id`),
  CONSTRAINT `intranet_taches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_taches_evalue_par_foreign` FOREIGN KEY (`evalue_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_taches_objectif_id_foreign` FOREIGN KEY (`objectif_id`) REFERENCES `intranet_objectifs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_taches_phase_id_foreign` FOREIGN KEY (`phase_id`) REFERENCES `intranet_projet_phases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_taches_priorite_id_foreign` FOREIGN KEY (`priorite_id`) REFERENCES `intranet_priorites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_taches_projet_id_foreign` FOREIGN KEY (`projet_id`) REFERENCES `intranet_projets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_taches_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_taches_statut_id_foreign` FOREIGN KEY (`statut_id`) REFERENCES `intranet_statuts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_taches`
--

LOCK TABLES `intranet_taches` WRITE;
/*!40000 ALTER TABLE `intranet_taches` DISABLE KEYS */;
INSERT INTO `intranet_taches` VALUES (1,NULL,'Configuration du module Finance',NULL,NULL,NULL,NULL,'XAF',NULL,1,NULL,NULL,0,NULL,NULL,0,NULL,0,2,2,1,NULL,'2026-04-17',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-07 17:16:57','2026-04-20 21:09:04',NULL),(2,NULL,'Import des données employés',NULL,NULL,NULL,NULL,'XAF',NULL,1,NULL,NULL,0,NULL,NULL,0,NULL,0,3,1,1,NULL,'2026-04-12',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-07 17:16:57','2026-04-20 21:09:04',NULL),(3,'audit-du-si-existant','Audit du SI existant',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,1,0,NULL,NULL,100,1,0,3,4,1,'2026-01-17','2026-02-17',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(4,'redaction-du-cahier-des-charges','Rédaction du cahier des charges',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,1,0,NULL,NULL,100,2,0,3,4,1,'2026-02-17','2026-03-17',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(5,'architecture-cloud-cible','Architecture cloud cible',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,2,0,NULL,NULL,70,2,0,3,2,1,'2026-03-17','2026-05-01',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(6,'maquettes-des-interfaces','Maquettes des interfaces',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,2,0,NULL,NULL,40,1,0,2,2,1,'2026-04-03','2026-05-08',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(7,'choix-du-prestataire-cloud','Choix du prestataire cloud',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,2,0,NULL,NULL,20,1,0,4,2,1,'2026-03-27','2026-04-12',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(8,'developpement-module-rh','Développement module RH',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,3,0,NULL,NULL,0,1,0,2,1,1,'2026-05-17','2026-08-17',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(9,'developpement-module-finance','Développement module Finance',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,3,0,NULL,NULL,0,2,0,3,1,1,'2026-05-17','2026-09-17',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(10,'migration-des-donnees','Migration des données',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,3,0,NULL,NULL,15,1,0,4,2,1,'2026-04-10','2026-04-15',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(11,'tests-dintegration','Tests d\'intégration',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,4,0,NULL,NULL,0,1,0,2,1,1,'2026-10-17','2026-11-17',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(12,'formation-utilisateurs','Formation utilisateurs',NULL,NULL,NULL,NULL,'XAF',NULL,2,NULL,4,0,NULL,NULL,0,1,0,2,1,1,'2026-11-17','2026-12-17',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:24:53','2026-04-20 21:09:04',NULL),(13,'parametrage-des-etapes-pipeline','Paramétrage des étapes pipeline',NULL,NULL,NULL,NULL,'XAF',NULL,3,NULL,5,0,NULL,NULL,100,2,0,3,4,1,'2026-03-17','2026-04-03',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,NULL,'2026-04-17 12:44:41','2026-04-29 16:30:18',NULL),(14,'import-des-contacts-existants','Import des contacts existants',NULL,NULL,NULL,NULL,'XAF',NULL,3,NULL,5,0,NULL,NULL,30,1,0,2,2,1,'2026-04-03','2026-04-24',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:44:41','2026-04-20 21:09:04',NULL),(15,'formation-equipe-commerciale','Formation équipe commerciale',NULL,NULL,NULL,NULL,'XAF',NULL,3,NULL,5,0,NULL,NULL,0,2,0,2,1,1,'2026-05-01','2026-06-17',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,NULL,'2026-04-17 12:44:41','2026-04-20 21:09:04',NULL),(16,'test-taches','test taches','<p>test taches</p>','test taches','<p>test taches</p>',NULL,'XAF',10.00,5,NULL,NULL,0,10.00,NULL,0,1,0,3,1,1,'2026-04-21','2026-04-24',NULL,NULL,NULL,'non_soumis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,'2026-04-20 21:09:03','2026-04-20 21:14:24',NULL);
/*!40000 ALTER TABLE `intranet_taches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_template_categories`
--

DROP TABLE IF EXISTS `intranet_template_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_template_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#7C3AED',
  `description` text COLLATE utf8mb4_unicode_ci,
  `ordre` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_template_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_template_categories`
--

LOCK TABLES `intranet_template_categories` WRITE;
/*!40000 ALTER TABLE `intranet_template_categories` DISABLE KEYS */;
INSERT INTO `intranet_template_categories` VALUES (1,'Contrat','contrat','fa-file-signature','#4F46E5',NULL,0,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(2,'Devis','devis','fa-file-invoice-dollar','#0891B2',NULL,1,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(3,'Facture','facture','fa-receipt','#16A34A',NULL,2,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(4,'Courrier','courrier','fa-envelope','#7C3AED',NULL,3,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(5,'Rapport','rapport','fa-file-lines','#D97706',NULL,4,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(6,'PV / CR','pv-cr','fa-clipboard-list','#0D9488',NULL,5,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(7,'Note interne','note-interne','fa-note-sticky','#F59E0B',NULL,6,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(8,'Attestation','attestation','fa-certificate','#DC2626',NULL,7,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(9,'Formulaire','formulaire','fa-list-check','#059669',NULL,8,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(10,'Présentation','presentation','fa-file-powerpoint','#EA580C',NULL,9,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL),(11,'Autre','autre','fa-file','#64748B',NULL,10,'2026-04-10 22:12:14','2026-04-10 22:12:14',NULL);
/*!40000 ALTER TABLE `intranet_template_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_templates`
--

DROP TABLE IF EXISTS `intranet_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `contenu` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `format` enum('html','fichier') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'html',
  `fichier_modele` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_original` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille` bigint unsigned NOT NULL DEFAULT '0',
  `variables` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `categorie_id` bigint unsigned DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `utilisations` int NOT NULL DEFAULT '0',
  `vues_count` int unsigned NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_templates_slug_unique` (`slug`),
  KEY `intranet_templates_created_by_foreign` (`created_by`),
  KEY `intranet_templates_categorie_id_index` (`categorie_id`),
  KEY `intranet_templates_format_index` (`format`),
  CONSTRAINT `intranet_templates_categorie_id_foreign` FOREIGN KEY (`categorie_id`) REFERENCES `intranet_template_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_templates`
--

LOCK TABLES `intranet_templates` WRITE;
/*!40000 ALTER TABLE `intranet_templates` DISABLE KEYS */;
INSERT INTO `intranet_templates` VALUES (1,'contrat-de-prestation-de-services','Contrat de prestation de services','Modèle standard de contrat de prestation entre deux parties.','<h1 style=\"text-align:center;\">CONTRAT DE PRESTATION DE SERVICES</h1>\n<p style=\"text-align:center;color:#64748B;\">Réf. : CPR-{{date_signature}}</p>\n<hr>\n<h2>ENTRE LES SOUSSIGNÉS</h2>\n<p><strong>Le Client :</strong><br>\n{{entreprise}}, dont le siège social est situé au {{adresse_entreprise}},<br>\nci-après dénommé « le Client »,</p>\n<p><strong>Le Prestataire :</strong><br>\n{{prestataire}}, dont le siège social est situé au {{adresse_prestataire}},<br>\nci-après dénommé « le Prestataire ».</p>\n\n<h2>ARTICLE 1 — OBJET</h2>\n<p>Le présent contrat a pour objet la réalisation par le Prestataire de la prestation suivante :</p>\n<p><strong>{{objet}}</strong></p>\n\n<h2>ARTICLE 2 — DURÉE</h2>\n<p>Le présent contrat est conclu pour une durée de <strong>{{duree}}</strong>, à compter du <strong>{{date_debut}}</strong>.</p>\n\n<h2>ARTICLE 3 — RÉMUNÉRATION</h2>\n<p>En contrepartie de la réalisation des prestations, le Client versera au Prestataire la somme de :</p>\n<p style=\"font-size:1.2em;text-align:center;\"><strong>{{montant}} {{devise}}</strong></p>\n<p>Ce montant est payable selon les conditions définies en annexe.</p>\n\n<h2>ARTICLE 4 — OBLIGATIONS DU PRESTATAIRE</h2>\n<ul>\n<li>Exécuter les prestations avec diligence et professionnalisme</li>\n<li>Respecter les délais convenus</li>\n<li>Informer le Client de tout obstacle à la bonne exécution</li>\n<li>Maintenir la confidentialité des informations communiquées</li>\n</ul>\n\n<h2>ARTICLE 5 — OBLIGATIONS DU CLIENT</h2>\n<ul>\n<li>Fournir au Prestataire toutes les informations nécessaires</li>\n<li>Procéder au paiement dans les délais convenus</li>\n<li>Faciliter l\'accès aux locaux si nécessaire</li>\n</ul>\n\n<h2>ARTICLE 6 — RÉSILIATION</h2>\n<p>Chaque partie peut résilier le contrat avec un préavis de 30 jours par lettre recommandée avec accusé de réception.</p>\n\n<h2>ARTICLE 7 — LITIGES</h2>\n<p>En cas de litige, les parties s\'engagent à rechercher une solution amiable. À défaut, le tribunal compétent sera saisi.</p>\n\n<br><br>\n<p>Fait à <strong>{{lieu}}</strong>, le <strong>{{date_signature}}</strong>, en deux exemplaires originaux.</p>\n<br>\n<table style=\"width:100%;\">\n<tr>\n<td style=\"width:50%;vertical-align:top;\"><strong>Pour le Client</strong><br><br><br><br>_________________________<br>{{entreprise}}</td>\n<td style=\"width:50%;vertical-align:top;\"><strong>Pour le Prestataire</strong><br><br><br><br>_________________________<br>{{prestataire}}</td>\n</tr>\n</table>','html',NULL,NULL,NULL,0,'[\"entreprise\", \"adresse_entreprise\", \"prestataire\", \"adresse_prestataire\", \"objet\", \"montant\", \"devise\", \"duree\", \"date_debut\", \"date_signature\", \"lieu\"]','[]',1,1,1,1,1,'2026-04-10 23:08:46','2026-04-10 23:13:19',NULL),(2,'attestation-de-travail','Attestation de travail','Attestation confirmant qu\'un employé travaille au sein de l\'entreprise.','<div style=\"text-align:right;margin-bottom:2em;\">\n<p>{{lieu}}, le {{date}}</p>\n</div>\n\n<h1 style=\"text-align:center;text-decoration:underline;\">ATTESTATION DE TRAVAIL</h1>\n\n<br>\n<p>Je soussigné(e), <strong>{{directeur}}</strong>, agissant en qualité de Directeur Général de la société <strong>{{entreprise}}</strong>, sise au {{adresse_entreprise}},</p>\n\n<p>atteste par la présente que :</p>\n\n<p style=\"font-size:1.1em;text-align:center;margin:1.5em 0;\">\n<strong>{{nom_employe}}</strong>\n</p>\n\n<p>est employé(e) au sein de notre entreprise depuis le <strong>{{date_embauche}}</strong> en qualité de <strong>{{poste}}</strong>.</p>\n\n<p>Cette attestation est délivrée à l\'intéressé(e) pour servir et valoir ce que de droit.</p>\n\n<br><br>\n<div style=\"text-align:right;\">\n<p><strong>{{directeur}}</strong><br>\nDirecteur Général<br>\n{{entreprise}}</p>\n<br><br>\n<p>Signature et cachet</p>\n</div>','html',NULL,NULL,NULL,0,'[\"entreprise\", \"adresse_entreprise\", \"directeur\", \"nom_employe\", \"poste\", \"date_embauche\", \"date\", \"lieu\"]','[]',8,1,0,0,1,'2026-04-10 23:08:46','2026-04-10 23:08:46',NULL),(3,'proces-verbal-de-reunion','Procès-verbal de réunion','Modèle de PV pour les réunions de travail ou comités.','<h1 style=\"text-align:center;\">PROCÈS-VERBAL DE RÉUNION</h1>\n<p style=\"text-align:center;color:#64748B;font-size:1.1em;\"><strong>{{titre_reunion}}</strong></p>\n<hr>\n\n<table style=\"width:100%;margin-bottom:1.5em;\">\n<tr><td style=\"width:30%;\"><strong>Date :</strong></td><td>{{date}}</td></tr>\n<tr><td><strong>Heure :</strong></td><td>{{heure_debut}} — {{heure_fin}}</td></tr>\n<tr><td><strong>Lieu :</strong></td><td>{{lieu}}</td></tr>\n<tr><td><strong>Président de séance :</strong></td><td>{{president}}</td></tr>\n<tr><td><strong>Secrétaire de séance :</strong></td><td>{{secretaire}}</td></tr>\n</table>\n\n<h2>Participants</h2>\n<p>{{participants}}</p>\n\n<h2>Ordre du jour</h2>\n<p>{{ordre_du_jour}}</p>\n\n<h2>Discussions et décisions</h2>\n<p>{{decisions}}</p>\n\n<h2>Prochaine réunion</h2>\n<p>{{prochaine_reunion}}</p>\n\n<br><br>\n<table style=\"width:100%;\">\n<tr>\n<td style=\"width:50%;vertical-align:top;\"><strong>Le Président de séance</strong><br><br><br><br>_________________________<br>{{president}}</td>\n<td style=\"width:50%;vertical-align:top;\"><strong>Le Secrétaire de séance</strong><br><br><br><br>_________________________<br>{{secretaire}}</td>\n</tr>\n</table>','html',NULL,NULL,NULL,0,'[\"titre_reunion\", \"date\", \"heure_debut\", \"heure_fin\", \"lieu\", \"president\", \"secretaire\", \"participants\", \"ordre_du_jour\", \"decisions\", \"prochaine_reunion\"]','[]',6,1,0,0,1,'2026-04-10 23:08:46','2026-04-10 23:08:46',NULL),(4,'devis-commercial','Devis commercial','Modèle de devis commercial avec tableau de prestations.','<table style=\"width:100%;margin-bottom:2em;\">\n<tr>\n<td style=\"width:50%;vertical-align:top;\">\n<h2 style=\"margin:0;color:#4F46E5;\">{{entreprise}}</h2>\n<p style=\"color:#64748B;font-size:.9em;\">\n{{adresse_entreprise}}<br>\nTél : {{telephone}}<br>\nEmail : {{email}}\n</p>\n</td>\n<td style=\"width:50%;text-align:right;vertical-align:top;\">\n<h1 style=\"margin:0;color:#4F46E5;\">DEVIS</h1>\n<p>\n<strong>Réf :</strong> {{reference_devis}}<br>\n<strong>Date :</strong> {{date}}<br>\n<strong>Validité :</strong> {{validite}}\n</p>\n</td>\n</tr>\n</table>\n\n<div style=\"background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:1em;margin-bottom:1.5em;\">\n<p style=\"margin:0;\"><strong>Client :</strong> {{client}}</p>\n<p style=\"margin:0;color:#64748B;\">{{adresse_client}}</p>\n</div>\n\n<h3>Objet : {{objet}}</h3>\n\n<table style=\"width:100%;border-collapse:collapse;margin:1.5em 0;\">\n<thead>\n<tr style=\"background:#4F46E5;color:#fff;\">\n<th style=\"padding:10px;text-align:left;\">Désignation</th>\n<th style=\"padding:10px;text-align:center;\">Qté</th>\n<th style=\"padding:10px;text-align:right;\">P.U. HT</th>\n<th style=\"padding:10px;text-align:right;\">Total HT</th>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td style=\"padding:10px;border-bottom:1px solid #E2E8F0;\">Prestation décrite ci-dessus</td>\n<td style=\"padding:10px;border-bottom:1px solid #E2E8F0;text-align:center;\">1</td>\n<td style=\"padding:10px;border-bottom:1px solid #E2E8F0;text-align:right;\">{{montant_ht}} {{devise}}</td>\n<td style=\"padding:10px;border-bottom:1px solid #E2E8F0;text-align:right;\">{{montant_ht}} {{devise}}</td>\n</tr>\n</tbody>\n</table>\n\n<table style=\"width:50%;margin-left:auto;\">\n<tr><td style=\"padding:5px;\"><strong>Total HT</strong></td><td style=\"padding:5px;text-align:right;\">{{montant_ht}} {{devise}}</td></tr>\n<tr><td style=\"padding:5px;\"><strong>TVA ({{tva}}%)</strong></td><td style=\"padding:5px;text-align:right;\">—</td></tr>\n<tr style=\"background:#F8FAFC;font-size:1.1em;\"><td style=\"padding:8px;\"><strong>Total TTC</strong></td><td style=\"padding:8px;text-align:right;\"><strong>{{montant_ttc}} {{devise}}</strong></td></tr>\n</table>\n\n<br>\n<h3>Conditions</h3>\n<p>{{conditions}}</p>\n\n<br><br>\n<p><em>Bon pour accord — Date et signature du client :</em></p>\n<br><br>\n<p>_________________________</p>','html',NULL,NULL,NULL,0,'[\"entreprise\", \"adresse_entreprise\", \"telephone\", \"email\", \"client\", \"adresse_client\", \"reference_devis\", \"date\", \"validite\", \"objet\", \"montant_ht\", \"tva\", \"montant_ttc\", \"devise\", \"conditions\"]','[]',2,1,0,0,1,'2026-04-10 23:08:46','2026-04-10 23:08:46',NULL),(5,'note-de-service','Note de service','Communication officielle interne de la direction.','<div style=\"text-align:center;margin-bottom:2em;\">\n<h2 style=\"margin:0;\">{{entreprise}}</h2>\n<p style=\"color:#64748B;\">Note de service</p>\n</div>\n\n<table style=\"width:100%;margin-bottom:1.5em;\">\n<tr><td style=\"width:20%;\"><strong>De :</strong></td><td>{{expediteur}}, {{fonction_expediteur}}</td></tr>\n<tr><td><strong>À :</strong></td><td>{{destinataires}}</td></tr>\n<tr><td><strong>Date :</strong></td><td>{{date}}</td></tr>\n<tr><td><strong>Objet :</strong></td><td><strong>{{objet}}</strong></td></tr>\n</table>\n\n<hr>\n\n<p>{{contenu}}</p>\n\n<br><br>\n<div style=\"text-align:right;\">\n<p>{{expediteur}}<br>\n<em>{{fonction_expediteur}}</em></p>\n</div>','html',NULL,NULL,NULL,0,'[\"entreprise\", \"expediteur\", \"fonction_expediteur\", \"destinataires\", \"objet\", \"contenu\", \"date\", \"lieu\"]','[]',7,1,0,0,1,'2026-04-10 23:08:46','2026-04-10 23:08:46',NULL),(6,'lettre-officielle','Lettre officielle','Modèle de courrier officiel à en-tête.','<table style=\"width:100%;margin-bottom:3em;\">\n<tr>\n<td style=\"width:50%;vertical-align:top;\">\n<strong>{{entreprise}}</strong><br>\n<span style=\"color:#64748B;\">{{adresse_entreprise}}</span>\n</td>\n<td style=\"width:50%;text-align:right;vertical-align:top;\">\n{{lieu}}, le {{date}}\n</td>\n</tr>\n</table>\n\n<p style=\"margin-top:2em;\">\n<strong>À l\'attention de :</strong><br>\n{{destinataire}}<br>\n<span style=\"color:#64748B;\">{{adresse_destinataire}}</span>\n</p>\n\n<p style=\"margin-top:2em;\"><strong>Objet : {{objet}}</strong></p>\n\n<br>\n<p>{{formule_appel}},</p>\n\n<p>{{corps}}</p>\n\n<p>Veuillez agréer, {{formule_appel}}, l\'expression de nos salutations distinguées.</p>\n\n<br><br>\n<div style=\"text-align:right;\">\n<p><strong>{{signataire}}</strong><br>\n<em>{{fonction}}</em><br>\n{{entreprise}}</p>\n</div>','html',NULL,NULL,NULL,0,'[\"entreprise\", \"adresse_entreprise\", \"destinataire\", \"adresse_destinataire\", \"lieu\", \"date\", \"objet\", \"formule_appel\", \"corps\", \"signataire\", \"fonction\"]','[]',4,1,0,0,1,'2026-04-10 23:08:46','2026-04-10 23:08:46',NULL),(7,'rapport-dactivite-mensuel','Rapport d\'activité mensuel','Template de rapport mensuel avec sections standards.','<h1 style=\"text-align:center;color:#4F46E5;\">RAPPORT D\'ACTIVITÉ MENSUEL</h1>\n<p style=\"text-align:center;font-size:1.1em;\">{{service}} — {{mois}} {{annee}}</p>\n<p style=\"text-align:center;color:#64748B;\">{{entreprise}}</p>\n<hr>\n\n<h2>1. Résumé exécutif</h2>\n<p>{{resume}}</p>\n\n<h2>2. Réalisations du mois</h2>\n<p>{{realisations}}</p>\n\n<h2>3. Difficultés rencontrées</h2>\n<p>{{difficultes}}</p>\n\n<h2>4. Perspectives pour le mois suivant</h2>\n<p>{{perspectives}}</p>\n\n<br><br>\n<p style=\"color:#64748B;\">Rapport rédigé par <strong>{{redacteur}}</strong> le {{date}}.</p>','html',NULL,NULL,NULL,0,'[\"entreprise\", \"service\", \"mois\", \"annee\", \"redacteur\", \"resume\", \"realisations\", \"difficultes\", \"perspectives\", \"date\"]','[]',5,1,0,0,1,'2026-04-10 23:08:46','2026-04-10 23:08:46',NULL),(8,'demande-de-conge','Demande de congé','Formulaire de demande de congé standard.','<h1 style=\"text-align:center;\">DEMANDE DE CONGÉ</h1>\n<hr>\n\n<h3>Informations de l\'employé</h3>\n<table style=\"width:100%;border-collapse:collapse;\">\n<tr><td style=\"padding:8px;border:1px solid #E2E8F0;width:30%;background:#F8FAFC;\"><strong>Nom complet</strong></td><td style=\"padding:8px;border:1px solid #E2E8F0;\">{{nom_employe}}</td></tr>\n<tr><td style=\"padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;\"><strong>Service</strong></td><td style=\"padding:8px;border:1px solid #E2E8F0;\">{{service}}</td></tr>\n<tr><td style=\"padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;\"><strong>Poste</strong></td><td style=\"padding:8px;border:1px solid #E2E8F0;\">{{poste}}</td></tr>\n</table>\n\n<h3>Détails du congé</h3>\n<table style=\"width:100%;border-collapse:collapse;\">\n<tr><td style=\"padding:8px;border:1px solid #E2E8F0;width:30%;background:#F8FAFC;\"><strong>Type de congé</strong></td><td style=\"padding:8px;border:1px solid #E2E8F0;\">{{type_conge}}</td></tr>\n<tr><td style=\"padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;\"><strong>Date de début</strong></td><td style=\"padding:8px;border:1px solid #E2E8F0;\">{{date_debut}}</td></tr>\n<tr><td style=\"padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;\"><strong>Date de fin</strong></td><td style=\"padding:8px;border:1px solid #E2E8F0;\">{{date_fin}}</td></tr>\n<tr><td style=\"padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;\"><strong>Nombre de jours</strong></td><td style=\"padding:8px;border:1px solid #E2E8F0;\">{{nombre_jours}}</td></tr>\n<tr><td style=\"padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;\"><strong>Motif</strong></td><td style=\"padding:8px;border:1px solid #E2E8F0;\">{{motif}}</td></tr>\n</table>\n\n<br>\n<p>Fait le {{date_demande}}</p>\n\n<br>\n<table style=\"width:100%;\">\n<tr>\n<td style=\"width:50%;vertical-align:top;\"><strong>Signature de l\'employé</strong><br><br><br><br>_________________________<br>{{nom_employe}}</td>\n<td style=\"width:50%;vertical-align:top;\"><strong>Avis du responsable</strong><br><br>☐ Approuvé &nbsp; ☐ Refusé<br><br>_________________________<br>{{nom_responsable}}</td>\n</tr>\n</table>','html',NULL,NULL,NULL,0,'[\"nom_employe\", \"service\", \"poste\", \"type_conge\", \"date_debut\", \"date_fin\", \"nombre_jours\", \"motif\", \"date_demande\", \"nom_responsable\"]','[]',9,1,0,0,1,'2026-04-10 23:08:46','2026-04-10 23:08:46',NULL);
/*!40000 ALTER TABLE `intranet_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_type_evenements`
--

DROP TABLE IF EXISTS `intranet_type_evenements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_type_evenements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#4F46E5',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_type_evenements`
--

LOCK TABLES `intranet_type_evenements` WRITE;
/*!40000 ALTER TABLE `intranet_type_evenements` DISABLE KEYS */;
INSERT INTO `intranet_type_evenements` VALUES (1,'Rendez-vous client','rdv','#DC2626',NULL,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(2,'Réunion interne','reunion','#4F46E5',NULL,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(3,'Formation','formation','#D97706',NULL,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(4,'Conférence','conf','#059669',NULL,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL),(5,'Atelier','atelier','#0891B2',NULL,'2026-04-07 17:16:57','2026-04-07 17:16:57',NULL);
/*!40000 ALTER TABLE `intranet_type_evenements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_valideurs`
--

DROP TABLE IF EXISTS `intranet_valideurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_valideurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `validable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validable_id` bigint unsigned NOT NULL,
  `valideur_type` enum('user','groupe') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valideur_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `valideur_unique` (`validable_type`,`validable_id`,`valideur_type`,`valideur_id`),
  KEY `valideurs_validable_idx` (`validable_type`,`validable_id`),
  KEY `valideurs_valideur_idx` (`valideur_type`,`valideur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_valideurs`
--

LOCK TABLES `intranet_valideurs` WRITE;
/*!40000 ALTER TABLE `intranet_valideurs` DISABLE KEYS */;
INSERT INTO `intranet_valideurs` VALUES (1,'App\\Models\\Intranet\\Jalon',8,'user',1,'2026-04-20 21:05:35','2026-04-20 21:05:35');
/*!40000 ALTER TABLE `intranet_valideurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_vues`
--

DROP TABLE IF EXISTS `intranet_vues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_vues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `viewable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `viewable_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `vu_le` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vue_unique` (`viewable_type`,`viewable_id`,`user_id`),
  KEY `intranet_vues_viewable_type_viewable_id_index` (`viewable_type`,`viewable_id`),
  KEY `intranet_vues_user_id_foreign` (`user_id`),
  CONSTRAINT `intranet_vues_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_vues`
--

LOCK TABLES `intranet_vues` WRITE;
/*!40000 ALTER TABLE `intranet_vues` DISABLE KEYS */;
INSERT INTO `intranet_vues` VALUES (1,'App\\Models\\Intranet\\Annonce',4,1,'2026-04-09 18:31:14'),(2,'App\\Models\\Intranet\\News',3,1,'2026-04-09 19:21:00'),(3,'App\\Models\\Intranet\\Evenement',1,1,'2026-04-09 19:41:37'),(4,'App\\Models\\Intranet\\Courrier',1,1,'2026-04-09 23:07:42'),(5,'App\\Models\\Intranet\\Ressource',3,1,'2026-04-10 21:38:25'),(6,'App\\Models\\Intranet\\Media',1,1,'2026-04-10 21:58:24'),(7,'App\\Models\\Intranet\\Media',2,1,'2026-04-10 22:06:35'),(8,'App\\Models\\Intranet\\Template',1,1,'2026-04-11 00:13:19');
/*!40000 ALTER TABLE `intranet_vues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_wiki_articles`
--

DROP TABLE IF EXISTS `intranet_wiki_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_wiki_articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extrait` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorie_id` bigint unsigned DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `is_epingle` tinyint(1) NOT NULL DEFAULT '0',
  `tags` json DEFAULT NULL,
  `media_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_principal_type` enum('image','video','document') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temps_lecture` smallint unsigned NOT NULL DEFAULT '0',
  `version` smallint unsigned NOT NULL DEFAULT '1',
  `parent_id` bigint unsigned DEFAULT NULL,
  `ordre` smallint unsigned NOT NULL DEFAULT '0',
  `vues` int NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_wiki_articles_slug_unique` (`slug`),
  KEY `intranet_wiki_articles_created_by_foreign` (`created_by`),
  KEY `intranet_wiki_articles_updated_by_foreign` (`updated_by`),
  KEY `intranet_wiki_articles_categorie_id_index` (`categorie_id`),
  KEY `intranet_wiki_articles_parent_id_index` (`parent_id`),
  CONSTRAINT `intranet_wiki_articles_categorie_id_foreign` FOREIGN KEY (`categorie_id`) REFERENCES `intranet_wiki_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_wiki_articles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `intranet_wiki_articles_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `intranet_wiki_articles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `intranet_wiki_articles_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_wiki_articles`
--

LOCK TABLES `intranet_wiki_articles` WRITE;
/*!40000 ALTER TABLE `intranet_wiki_articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_wiki_articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `intranet_wiki_categories`
--

DROP TABLE IF EXISTS `intranet_wiki_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intranet_wiki_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#4F46E5',
  `parent_id` bigint unsigned DEFAULT NULL,
  `ordre` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intranet_wiki_categories_slug_unique` (`slug`),
  KEY `intranet_wiki_categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `intranet_wiki_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `intranet_wiki_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `intranet_wiki_categories`
--

LOCK TABLES `intranet_wiki_categories` WRITE;
/*!40000 ALTER TABLE `intranet_wiki_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `intranet_wiki_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lignes`
--

DROP TABLE IF EXISTS `lignes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lignes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_titre` bigint unsigned NOT NULL,
  `nature` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seuil` double DEFAULT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `effacer` tinyint NOT NULL DEFAULT '0',
  `id_codeanalytique` int DEFAULT NULL,
  `id_famillecodeanalytique` int DEFAULT NULL,
  `id_user` int NOT NULL DEFAULT '1',
  `dateeffet` datetime DEFAULT NULL,
  `id_typecode` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lignes_id_titre_foreign` (`id_titre`),
  CONSTRAINT `lignes_id_titre_foreign` FOREIGN KEY (`id_titre`) REFERENCES `titres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lignes`
--

LOCK TABLES `lignes` WRITE;
/*!40000 ALTER TABLE `lignes` DISABLE KEYS */;
/*!40000 ALTER TABLE `lignes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `localites`
--

DROP TABLE IF EXISTS `localites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `localites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` int NOT NULL DEFAULT '1',
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int NOT NULL DEFAULT '1',
  `id_user` int NOT NULL DEFAULT '1',
  `effacer` int NOT NULL DEFAULT '0',
  `id_typelocalite` bigint unsigned NOT NULL,
  `id_region` bigint unsigned NOT NULL,
  `id_pays` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `localites_id_typelocalite_foreign` (`id_typelocalite`),
  KEY `localites_id_region_foreign` (`id_region`),
  KEY `localites_id_pays_foreign` (`id_pays`),
  CONSTRAINT `localites_id_pays_foreign` FOREIGN KEY (`id_pays`) REFERENCES `pays` (`id`),
  CONSTRAINT `localites_id_region_foreign` FOREIGN KEY (`id_region`) REFERENCES `regions` (`id`),
  CONSTRAINT `localites_id_typelocalite_foreign` FOREIGN KEY (`id_typelocalite`) REFERENCES `type_localites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `localites`
--

LOCK TABLES `localites` WRITE;
/*!40000 ALTER TABLE `localites` DISABLE KEYS */;
/*!40000 ALTER TABLE `localites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `new_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_02_18_224710_create_permission_tables',1),(5,'2026_02_18_224736_create_oauth_auth_codes_table',1),(6,'2026_02_18_224737_create_oauth_access_tokens_table',1),(7,'2026_02_18_224738_create_oauth_refresh_tokens_table',1),(8,'2026_02_18_224739_create_oauth_clients_table',1),(9,'2026_02_18_224740_create_oauth_device_codes_table',1),(10,'2026_02_18_230000_create_logs_table',1),(11,'2026_02_18_230001_create_authentifications_table',1),(12,'2026_02_18_230002_create_changespasswords_table',1),(13,'2026_02_18_230003_create_configs_table',1),(14,'2026_02_18_230004_create_typesorganisations_table',1),(15,'2026_02_18_230005_create_organisations_table',1),(16,'2026_02_18_230006_create_type_localites_table',1),(17,'2026_02_18_233400_create_pays_table',1),(18,'2026_02_18_233401_create_regions_table',1),(19,'2026_02_18_233402_create_localites_table',1),(20,'2026_02_18_233403_create_arrondissements_table',1),(21,'2026_02_18_233404_create_quartiers_table',1),(22,'2026_02_18_233405_create_entites_table',1),(23,'2026_02_18_233420_create_exercices_table',1),(24,'2026_02_18_233422_create_titres_table',1),(25,'2026_02_18_233425_create_budget_lignes_table',1),(26,'2026_02_18_233436_create_comptes_table',1),(27,'2026_02_18_233441_create_grand_livres_table',1),(28,'2026_02_18_233446_create_grand_livre_details_table',1),(29,'2026_02_18_233452_create_mode_reglements_table',1),(30,'2026_02_18_233502_create_transactions_table',1),(31,'2026_02_18_233710_create_employees_table',1),(32,'2026_02_18_233715_create_absences_table',1),(33,'2026_02_18_233721_create_competences_table',1),(34,'2026_02_18_233726_create_qualifications_table',1),(35,'2026_02_18_233731_create_formations_table',1),(36,'2026_02_18_233737_create_recrutements_table',1),(37,'2026_02_18_233742_create_postulants_table',1),(38,'2026_02_18_233747_create_paies_table',1),(39,'2026_02_18_233752_create_rubriques_table',1),(40,'2026_02_18_233758_create_missions_table',1),(41,'2026_02_18_234515_create_fournisseurs_table',1),(42,'2026_02_18_234521_create_produits_table',1),(43,'2026_02_18_234524_create_approvisionnements_table',1),(44,'2026_02_18_234526_create_commande_fournisseurs_table',1),(45,'2026_02_18_234537_create_immobilisations_table',1),(46,'2026_02_18_234542_create_dysfonctionnements_table',1),(47,'2026_02_18_234547_create_interventions_table',1),(48,'2026_02_18_235001_create_affilies_table',1),(49,'2026_02_18_235002_create_typesevenementscarrieres_table',1),(50,'2026_02_18_235003_create_evenementscarrieres_table',1),(51,'2026_02_18_235004_create_embauches_table',1),(52,'2026_04_07_180158_create_intranet_tables',2),(53,'2026_04_07_190923_create_intranet_extended_tables',3),(54,'2026_04_07_224005_create_intranet_module_settings',4),(55,'2026_04_07_224005_create_intranet_social_tables',4),(56,'2026_04_07_224855_alter_projets_taches_for_pmp',5),(57,'2026_04_07_224855_create_projet_pmp_tables',5),(58,'2026_04_09_100000_enrich_intranet_annonces_and_create_pieces_jointes',6),(59,'2026_04_09_110000_enrich_intranet_news',7),(60,'2026_04_09_120000_enrich_intranet_evenements',8),(61,'2026_04_09_130000_create_intranet_evenement_invites_externes',9),(62,'2026_04_09_140000_create_crm_referentiels_and_enrich',99),(63,'2026_04_09_150000_add_media_principal_to_crm',100),(64,'2026_04_09_160000_enrich_intranet_courriers',101),(65,'2026_04_10_100000_enrich_intranet_ressources',102),(66,'2026_04_10_110000_enrich_intranet_media',103),(67,'2026_04_10_120000_create_intranet_media_albums',104),(68,'2026_04_10_130000_add_video_externe_to_media',105),(69,'2026_04_10_140000_enrich_intranet_archives',106),(70,'2026_04_11_100000_enrich_intranet_templates',107),(71,'2026_04_11_110000_enrich_intranet_rapports',108),(72,'2026_04_11_120000_add_liens_validation_to_rapports',109),(73,'2026_04_11_130000_enrich_taches_validation_workflow',110),(74,'2026_04_11_140000_enrich_intranet_wiki',111),(75,'2026_04_18_100000_add_ponderation_dates_reelles_to_phases',112),(76,'2026_04_18_110000_add_statut_to_phases',113),(77,'2026_04_18_120000_add_phase_id_to_projet_couts',114),(78,'2026_04_18_130000_create_validation_cloture_system',115),(79,'2026_04_18_140000_create_demandes_modification_table',116),(80,'2026_04_21_100000_add_objectif_id_to_projets_and_taches',117),(81,'2026_04_21_110000_create_roles_projet_table',118);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `missions`
--

DROP TABLE IF EXISTS `missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `missions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `lieu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `budget` double DEFAULT NULL,
  `frais_reels` double DEFAULT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=planifie, 1=en cours, 2=termine',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `missions_employee_id_foreign` (`employee_id`),
  CONSTRAINT `missions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missions`
--

LOCK TABLES `missions` WRITE;
/*!40000 ALTER TABLE `missions` DISABLE KEYS */;
/*!40000 ALTER TABLE `missions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mode_reglements`
--

DROP TABLE IF EXISTS `mode_reglements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mode_reglements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` int NOT NULL DEFAULT '1',
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int NOT NULL DEFAULT '0',
  `id_user` int NOT NULL DEFAULT '1',
  `effacer` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mode_reglements`
--

LOCK TABLES `mode_reglements` WRITE;
/*!40000 ALTER TABLE `mode_reglements` DISABLE KEYS */;
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
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(4,'App\\Models\\User',2);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modification_budgetaires`
--

DROP TABLE IF EXISTS `modification_budgetaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modification_budgetaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_compte_emission` bigint unsigned DEFAULT NULL,
  `codecompte_emission` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_compte_reception` bigint unsigned DEFAULT NULL,
  `codecompte_reception` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objetmodification` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_modification` double DEFAULT NULL,
  `isbudgetligne` tinyint DEFAULT NULL,
  `commentaire` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_user` bigint unsigned NOT NULL,
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=brouillon, 1=soumis, 2=valide, 3=rejete',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `modification_budgetaires_id_compte_emission_foreign` (`id_compte_emission`),
  KEY `modification_budgetaires_id_compte_reception_foreign` (`id_compte_reception`),
  KEY `modification_budgetaires_id_user_foreign` (`id_user`),
  CONSTRAINT `modification_budgetaires_id_compte_emission_foreign` FOREIGN KEY (`id_compte_emission`) REFERENCES `comptes` (`id`),
  CONSTRAINT `modification_budgetaires_id_compte_reception_foreign` FOREIGN KEY (`id_compte_reception`) REFERENCES `comptes` (`id`),
  CONSTRAINT `modification_budgetaires_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modification_budgetaires`
--

LOCK TABLES `modification_budgetaires` WRITE;
/*!40000 ALTER TABLE `modification_budgetaires` DISABLE KEYS */;
/*!40000 ALTER TABLE `modification_budgetaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_access_tokens`
--

DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_access_tokens` (
  `id` char(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `client_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_access_tokens`
--

LOCK TABLES `oauth_access_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_auth_codes`
--

DROP TABLE IF EXISTS `oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_auth_codes` (
  `id` char(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `client_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_auth_codes`
--

LOCK TABLES `oauth_auth_codes` WRITE;
/*!40000 ALTER TABLE `oauth_auth_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_auth_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_clients`
--

DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_clients` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect_uris` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `grant_types` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_owner_type_owner_id_index` (`owner_type`,`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_clients`
--

LOCK TABLES `oauth_clients` WRITE;
/*!40000 ALTER TABLE `oauth_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_device_codes`
--

DROP TABLE IF EXISTS `oauth_device_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_device_codes` (
  `id` char(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `client_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_code` char(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `user_approved_at` datetime DEFAULT NULL,
  `last_polled_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oauth_device_codes_user_code_unique` (`user_code`),
  KEY `oauth_device_codes_user_id_index` (`user_id`),
  KEY `oauth_device_codes_client_id_index` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_device_codes`
--

LOCK TABLES `oauth_device_codes` WRITE;
/*!40000 ALTER TABLE `oauth_device_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_device_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_refresh_tokens`
--

DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` char(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` char(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_refresh_tokens`
--

LOCK TABLES `oauth_refresh_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_refresh_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organisations`
--

DROP TABLE IF EXISTS `organisations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `typesorganisation_id` bigint unsigned NOT NULL,
  `chefs` bigint unsigned DEFAULT NULL,
  `peres` bigint unsigned DEFAULT NULL,
  `statut` int NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisations_typesorganisation_id_foreign` (`typesorganisation_id`),
  KEY `organisations_chefs_foreign` (`chefs`),
  KEY `organisations_peres_foreign` (`peres`),
  CONSTRAINT `organisations_chefs_foreign` FOREIGN KEY (`chefs`) REFERENCES `users` (`id`),
  CONSTRAINT `organisations_peres_foreign` FOREIGN KEY (`peres`) REFERENCES `organisations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `organisations_typesorganisation_id_foreign` FOREIGN KEY (`typesorganisation_id`) REFERENCES `typesorganisations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organisations`
--

LOCK TABLES `organisations` WRITE;
/*!40000 ALTER TABLE `organisations` DISABLE KEYS */;
/*!40000 ALTER TABLE `organisations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pays`
--

DROP TABLE IF EXISTS `pays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` int DEFAULT NULL,
  `zipcode` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alpha2` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alpha3` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_en_gb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_fr_fr` varchar(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isvalide` int NOT NULL DEFAULT '1',
  `statut` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pays`
--

LOCK TABLES `pays` WRITE;
/*!40000 ALTER TABLE `pays` DISABLE KEYS */;
/*!40000 ALTER TABLE `pays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=469 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'create:exercice','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(2,'read:exercice','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(3,'update:exercice','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(4,'delete:exercice','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(5,'validate:exercice','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(6,'export:exercice','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(7,'create:budget','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(8,'read:budget','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(9,'update:budget','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(10,'delete:budget','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(11,'validate:budget','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(12,'export:budget','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(13,'create:grandlivre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(14,'read:grandlivre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(15,'update:grandlivre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(16,'delete:grandlivre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(17,'validate:grandlivre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(18,'export:grandlivre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(19,'create:compte','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(20,'read:compte','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(21,'update:compte','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(22,'delete:compte','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(23,'validate:compte','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(24,'export:compte','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(25,'create:titre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(26,'read:titre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(27,'update:titre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(28,'delete:titre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(29,'validate:titre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(30,'export:titre','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(31,'create:ligne','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(32,'read:ligne','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(33,'update:ligne','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(34,'delete:ligne','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(35,'validate:ligne','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(36,'export:ligne','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(37,'create:modification_budgetaire','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(38,'read:modification_budgetaire','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(39,'update:modification_budgetaire','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(40,'delete:modification_budgetaire','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(41,'validate:modification_budgetaire','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(42,'export:modification_budgetaire','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(43,'create:transaction','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(44,'read:transaction','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(45,'update:transaction','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(46,'delete:transaction','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(47,'validate:transaction','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(48,'export:transaction','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(49,'create:employee','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(50,'read:employee','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(51,'update:employee','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(52,'delete:employee','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(53,'validate:employee','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(54,'export:employee','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(55,'create:absence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(56,'read:absence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(57,'update:absence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(58,'delete:absence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(59,'validate:absence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(60,'export:absence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(61,'create:paie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(62,'read:paie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(63,'update:paie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(64,'delete:paie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(65,'validate:paie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(66,'export:paie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(67,'create:competence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(68,'read:competence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(69,'update:competence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(70,'delete:competence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(71,'validate:competence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(72,'export:competence','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(73,'create:qualification','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(74,'read:qualification','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(75,'update:qualification','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(76,'delete:qualification','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(77,'validate:qualification','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(78,'export:qualification','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(79,'create:formation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(80,'read:formation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(81,'update:formation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(82,'delete:formation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(83,'validate:formation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(84,'export:formation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(85,'create:recrutement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(86,'read:recrutement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(87,'update:recrutement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(88,'delete:recrutement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(89,'validate:recrutement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(90,'export:recrutement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(91,'create:postulant','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(92,'read:postulant','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(93,'update:postulant','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(94,'delete:postulant','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(95,'validate:postulant','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(96,'export:postulant','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(97,'create:mission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(98,'read:mission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(99,'update:mission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(100,'delete:mission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(101,'validate:mission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(102,'export:mission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(103,'create:affilie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(104,'read:affilie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(105,'update:affilie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(106,'delete:affilie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(107,'validate:affilie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(108,'export:affilie','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(109,'create:embauche','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(110,'read:embauche','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(111,'update:embauche','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(112,'delete:embauche','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(113,'validate:embauche','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(114,'export:embauche','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(115,'create:fournisseur','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(116,'read:fournisseur','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(117,'update:fournisseur','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(118,'delete:fournisseur','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(119,'validate:fournisseur','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(120,'export:fournisseur','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(121,'create:produit','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(122,'read:produit','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(123,'update:produit','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(124,'delete:produit','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(125,'validate:produit','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(126,'export:produit','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(127,'create:commande','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(128,'read:commande','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(129,'update:commande','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(130,'delete:commande','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(131,'validate:commande','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(132,'export:commande','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(133,'create:approvisionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(134,'read:approvisionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(135,'update:approvisionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(136,'delete:approvisionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(137,'validate:approvisionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(138,'export:approvisionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(139,'create:immobilisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(140,'read:immobilisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(141,'update:immobilisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(142,'delete:immobilisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(143,'validate:immobilisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(144,'export:immobilisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(145,'create:dysfonctionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(146,'read:dysfonctionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(147,'update:dysfonctionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(148,'delete:dysfonctionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(149,'validate:dysfonctionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(150,'export:dysfonctionnement','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(151,'create:intervention','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(152,'read:intervention','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(153,'update:intervention','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(154,'delete:intervention','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(155,'validate:intervention','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(156,'export:intervention','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(157,'create:user','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(158,'read:user','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(159,'update:user','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(160,'delete:user','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(161,'validate:user','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(162,'export:user','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(163,'create:role','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(164,'read:role','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(165,'update:role','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(166,'delete:role','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(167,'validate:role','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(168,'export:role','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(169,'create:permission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(170,'read:permission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(171,'update:permission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(172,'delete:permission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(173,'validate:permission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(174,'export:permission','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(175,'create:organisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(176,'read:organisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(177,'update:organisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(178,'delete:organisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(179,'validate:organisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(180,'export:organisation','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(181,'create:entite','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(182,'read:entite','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(183,'update:entite','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(184,'delete:entite','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(185,'validate:entite','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(186,'export:entite','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(187,'create:config','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(188,'read:config','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(189,'update:config','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(190,'delete:config','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(191,'validate:config','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(192,'export:config','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(193,'publish:exercice','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(194,'publish:budget','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(195,'publish:grandlivre','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(196,'publish:compte','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(197,'publish:titre','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(198,'publish:ligne','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(199,'publish:modification_budgetaire','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(200,'publish:transaction','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(201,'publish:employee','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(202,'publish:absence','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(203,'publish:paie','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(204,'publish:competence','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(205,'publish:qualification','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(206,'publish:formation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(207,'publish:recrutement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(208,'publish:postulant','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(209,'publish:mission','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(210,'publish:affilie','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(211,'publish:embauche','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(212,'publish:fournisseur','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(213,'publish:produit','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(214,'publish:commande','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(215,'publish:approvisionnement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(216,'publish:immobilisation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(217,'publish:dysfonctionnement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(218,'publish:intervention','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(219,'publish:user','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(220,'publish:role','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(221,'publish:permission','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(222,'publish:organisation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(223,'publish:entite','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(224,'publish:config','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(225,'create:annonce','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(226,'read:annonce','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(227,'update:annonce','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(228,'delete:annonce','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(229,'validate:annonce','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(230,'export:annonce','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(231,'publish:annonce','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(232,'create:news','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(233,'read:news','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(234,'update:news','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(235,'delete:news','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(236,'validate:news','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(237,'export:news','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(238,'publish:news','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(239,'create:evenement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(240,'read:evenement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(241,'update:evenement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(242,'delete:evenement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(243,'validate:evenement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(244,'export:evenement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(245,'publish:evenement','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(246,'create:courrier','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(247,'read:courrier','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(248,'update:courrier','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(249,'delete:courrier','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(250,'validate:courrier','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(251,'export:courrier','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(252,'publish:courrier','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(253,'create:ressource','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(254,'read:ressource','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(255,'update:ressource','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(256,'delete:ressource','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(257,'validate:ressource','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(258,'export:ressource','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(259,'publish:ressource','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(260,'create:media','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(261,'read:media','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(262,'update:media','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(263,'delete:media','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(264,'validate:media','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(265,'export:media','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(266,'publish:media','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(267,'create:archive','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(268,'read:archive','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(269,'update:archive','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(270,'delete:archive','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(271,'validate:archive','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(272,'export:archive','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(273,'publish:archive','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(274,'create:template','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(275,'read:template','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(276,'update:template','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(277,'delete:template','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(278,'validate:template','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(279,'export:template','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(280,'publish:template','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(281,'create:projet_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(282,'read:projet_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(283,'update:projet_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(284,'delete:projet_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(285,'validate:projet_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(286,'export:projet_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(287,'publish:projet_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(288,'create:tache_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(289,'read:tache_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(290,'update:tache_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(291,'delete:tache_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(292,'validate:tache_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(293,'export:tache_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(294,'publish:tache_intranet','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(295,'create:rapport','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(296,'read:rapport','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(297,'update:rapport','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(298,'delete:rapport','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(299,'validate:rapport','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(300,'export:rapport','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(301,'publish:rapport','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(302,'create:objectif','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(303,'read:objectif','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(304,'update:objectif','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(305,'delete:objectif','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(306,'validate:objectif','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(307,'export:objectif','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(308,'publish:objectif','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(309,'create:kpi','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(310,'read:kpi','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(311,'update:kpi','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(312,'delete:kpi','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(313,'validate:kpi','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(314,'export:kpi','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(315,'publish:kpi','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(316,'create:evaluation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(317,'read:evaluation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(318,'update:evaluation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(319,'delete:evaluation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(320,'validate:evaluation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(321,'export:evaluation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(322,'publish:evaluation','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(323,'create:wiki','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(324,'read:wiki','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(325,'update:wiki','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(326,'delete:wiki','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(327,'validate:wiki','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(328,'export:wiki','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(329,'publish:wiki','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(330,'create:contact','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(331,'read:contact','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(332,'update:contact','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(333,'delete:contact','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(334,'validate:contact','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(335,'export:contact','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(336,'publish:contact','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(337,'create:organisation_crm','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(338,'read:organisation_crm','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(339,'update:organisation_crm','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(340,'delete:organisation_crm','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(341,'validate:organisation_crm','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(342,'export:organisation_crm','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(343,'publish:organisation_crm','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(344,'create:opportunite','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(345,'read:opportunite','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(346,'update:opportunite','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(347,'delete:opportunite','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(348,'validate:opportunite','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(349,'export:opportunite','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(350,'publish:opportunite','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(351,'create:groupe','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(352,'read:groupe','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(353,'update:groupe','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(354,'delete:groupe','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(355,'validate:groupe','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(356,'export:groupe','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(357,'publish:groupe','web','2026-04-09 17:20:19','2026-04-09 17:20:19'),(358,'create:evenement_participation','web','2026-04-09 18:36:13','2026-04-09 18:36:13'),(359,'read:evenement_participation','web','2026-04-09 18:36:13','2026-04-09 18:36:13'),(360,'update:evenement_participation','web','2026-04-09 18:36:13','2026-04-09 18:36:13'),(361,'delete:evenement_participation','web','2026-04-09 18:36:13','2026-04-09 18:36:13'),(362,'validate:evenement_participation','web','2026-04-09 18:36:13','2026-04-09 18:36:13'),(363,'export:evenement_participation','web','2026-04-09 18:36:13','2026-04-09 18:36:13'),(364,'publish:evenement_participation','web','2026-04-09 18:36:13','2026-04-09 18:36:13'),(365,'assign:exercice','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(366,'process:exercice','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(367,'assign:budget','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(368,'process:budget','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(369,'assign:grandlivre','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(370,'process:grandlivre','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(371,'assign:compte','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(372,'process:compte','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(373,'assign:titre','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(374,'process:titre','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(375,'assign:ligne','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(376,'process:ligne','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(377,'assign:modification_budgetaire','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(378,'process:modification_budgetaire','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(379,'assign:transaction','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(380,'process:transaction','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(381,'assign:employee','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(382,'process:employee','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(383,'assign:absence','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(384,'process:absence','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(385,'assign:paie','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(386,'process:paie','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(387,'assign:competence','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(388,'process:competence','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(389,'assign:qualification','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(390,'process:qualification','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(391,'assign:formation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(392,'process:formation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(393,'assign:recrutement','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(394,'process:recrutement','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(395,'assign:postulant','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(396,'process:postulant','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(397,'assign:mission','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(398,'process:mission','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(399,'assign:affilie','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(400,'process:affilie','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(401,'assign:embauche','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(402,'process:embauche','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(403,'assign:fournisseur','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(404,'process:fournisseur','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(405,'assign:produit','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(406,'process:produit','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(407,'assign:commande','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(408,'process:commande','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(409,'assign:approvisionnement','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(410,'process:approvisionnement','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(411,'assign:immobilisation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(412,'process:immobilisation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(413,'assign:dysfonctionnement','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(414,'process:dysfonctionnement','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(415,'assign:intervention','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(416,'process:intervention','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(417,'assign:user','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(418,'process:user','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(419,'assign:role','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(420,'process:role','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(421,'assign:permission','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(422,'process:permission','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(423,'assign:organisation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(424,'process:organisation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(425,'assign:entite','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(426,'process:entite','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(427,'assign:config','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(428,'process:config','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(429,'assign:annonce','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(430,'process:annonce','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(431,'assign:news','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(432,'process:news','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(433,'assign:evenement','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(434,'process:evenement','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(435,'assign:evenement_participation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(436,'process:evenement_participation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(437,'assign:courrier','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(438,'process:courrier','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(439,'assign:ressource','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(440,'process:ressource','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(441,'assign:media','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(442,'process:media','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(443,'assign:archive','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(444,'process:archive','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(445,'assign:template','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(446,'process:template','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(447,'assign:projet_intranet','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(448,'process:projet_intranet','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(449,'assign:tache_intranet','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(450,'process:tache_intranet','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(451,'assign:rapport','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(452,'process:rapport','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(453,'assign:objectif','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(454,'process:objectif','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(455,'assign:kpi','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(456,'process:kpi','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(457,'assign:evaluation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(458,'process:evaluation','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(459,'assign:wiki','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(460,'process:wiki','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(461,'assign:contact','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(462,'process:contact','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(463,'assign:organisation_crm','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(464,'process:organisation_crm','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(465,'assign:opportunite','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(466,'process:opportunite','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(467,'assign:groupe','web','2026-04-09 21:02:59','2026-04-09 21:02:59'),(468,'process:groupe','web','2026-04-09 21:02:59','2026-04-09 21:02:59');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `postulants`
--

DROP TABLE IF EXISTS `postulants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `postulants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `noms` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenoms` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_naissance` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profil_id` bigint unsigned NOT NULL,
  `recrutement_id` bigint unsigned DEFAULT NULL,
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=nouveau, 1=entretenu, 2=retenu, 3=rejete',
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'CV, lettres',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `postulants_profil_id_foreign` (`profil_id`),
  KEY `postulants_recrutement_id_foreign` (`recrutement_id`),
  CONSTRAINT `postulants_profil_id_foreign` FOREIGN KEY (`profil_id`) REFERENCES `profils` (`id`),
  CONSTRAINT `postulants_recrutement_id_foreign` FOREIGN KEY (`recrutement_id`) REFERENCES `recrutements` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `postulants`
--

LOCK TABLES `postulants` WRITE;
/*!40000 ALTER TABLE `postulants` DISABLE KEYS */;
/*!40000 ALTER TABLE `postulants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produits`
--

DROP TABLE IF EXISTS `produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `categorie` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sous_categorie` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unite_mesure` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prix_unitaire` double NOT NULL DEFAULT '0',
  `taux_tva` double NOT NULL DEFAULT '0',
  `stock_actuel` int NOT NULL DEFAULT '0',
  `stock_minimum` int NOT NULL DEFAULT '0',
  `stock_maximum` int DEFAULT NULL,
  `emplacement` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `produits_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produits`
--

LOCK TABLES `produits` WRITE;
/*!40000 ALTER TABLE `produits` DISABLE KEYS */;
/*!40000 ALTER TABLE `produits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profils`
--

DROP TABLE IF EXISTS `profils`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profils` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nombres` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'nombre de postes',
  `recrutement_id` bigint unsigned NOT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profils_recrutement_id_foreign` (`recrutement_id`),
  CONSTRAINT `profils_recrutement_id_foreign` FOREIGN KEY (`recrutement_id`) REFERENCES `recrutements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profils`
--

LOCK TABLES `profils` WRITE;
/*!40000 ALTER TABLE `profils` DISABLE KEYS */;
/*!40000 ALTER TABLE `profils` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualifications`
--

DROP TABLE IF EXISTS `qualifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `qualifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `employee_id` bigint unsigned NOT NULL,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `organisme` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` int NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qualifications_employee_id_foreign` (`employee_id`),
  CONSTRAINT `qualifications_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualifications`
--

LOCK TABLES `qualifications` WRITE;
/*!40000 ALTER TABLE `qualifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `qualifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quartiers`
--

DROP TABLE IF EXISTS `quartiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quartiers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` int NOT NULL DEFAULT '1',
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int NOT NULL DEFAULT '1',
  `id_user` int NOT NULL DEFAULT '1',
  `effacer` int NOT NULL DEFAULT '0',
  `id_arrondissement` bigint unsigned NOT NULL,
  `id_localite` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quartiers_id_arrondissement_foreign` (`id_arrondissement`),
  KEY `quartiers_id_localite_foreign` (`id_localite`),
  CONSTRAINT `quartiers_id_arrondissement_foreign` FOREIGN KEY (`id_arrondissement`) REFERENCES `arrondissements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quartiers_id_localite_foreign` FOREIGN KEY (`id_localite`) REFERENCES `localites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quartiers`
--

LOCK TABLES `quartiers` WRITE;
/*!40000 ALTER TABLE `quartiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `quartiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recrutements`
--

DROP TABLE IF EXISTS `recrutements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recrutements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `statut` int NOT NULL DEFAULT '0' COMMENT '0=ouvert, 1=ferme, 2=pourvu',
  `fichiersjoin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recrutements`
--

LOCK TABLES `recrutements` WRITE;
/*!40000 ALTER TABLE `recrutements` DISABLE KEYS */;
/*!40000 ALTER TABLE `recrutements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` int NOT NULL DEFAULT '1',
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int NOT NULL DEFAULT '1',
  `id_user` int NOT NULL DEFAULT '1',
  `id_pays` bigint unsigned NOT NULL DEFAULT '80',
  `effacer` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `regions_id_pays_foreign` (`id_pays`),
  CONSTRAINT `regions_id_pays_foreign` FOREIGN KEY (`id_pays`) REFERENCES `pays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
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
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(115,1),(116,1),(117,1),(118,1),(119,1),(120,1),(121,1),(122,1),(123,1),(124,1),(125,1),(126,1),(127,1),(128,1),(129,1),(130,1),(131,1),(132,1),(133,1),(134,1),(135,1),(136,1),(137,1),(138,1),(139,1),(140,1),(141,1),(142,1),(143,1),(144,1),(145,1),(146,1),(147,1),(148,1),(149,1),(150,1),(151,1),(152,1),(153,1),(154,1),(155,1),(156,1),(157,1),(158,1),(159,1),(160,1),(161,1),(162,1),(163,1),(164,1),(165,1),(166,1),(167,1),(168,1),(169,1),(170,1),(171,1),(172,1),(173,1),(174,1),(175,1),(176,1),(177,1),(178,1),(179,1),(180,1),(181,1),(182,1),(183,1),(184,1),(185,1),(186,1),(187,1),(188,1),(189,1),(190,1),(191,1),(192,1),(193,1),(194,1),(195,1),(196,1),(197,1),(198,1),(199,1),(200,1),(201,1),(202,1),(203,1),(204,1),(205,1),(206,1),(207,1),(208,1),(209,1),(210,1),(211,1),(212,1),(213,1),(214,1),(215,1),(216,1),(217,1),(218,1),(219,1),(220,1),(221,1),(222,1),(223,1),(224,1),(225,1),(226,1),(227,1),(228,1),(229,1),(230,1),(231,1),(232,1),(233,1),(234,1),(235,1),(236,1),(237,1),(238,1),(239,1),(240,1),(241,1),(242,1),(243,1),(244,1),(245,1),(246,1),(247,1),(248,1),(249,1),(250,1),(251,1),(252,1),(253,1),(254,1),(255,1),(256,1),(257,1),(258,1),(259,1),(260,1),(261,1),(262,1),(263,1),(264,1),(265,1),(266,1),(267,1),(268,1),(269,1),(270,1),(271,1),(272,1),(273,1),(274,1),(275,1),(276,1),(277,1),(278,1),(279,1),(280,1),(281,1),(282,1),(283,1),(284,1),(285,1),(286,1),(287,1),(288,1),(289,1),(290,1),(291,1),(292,1),(293,1),(294,1),(295,1),(296,1),(297,1),(298,1),(299,1),(300,1),(301,1),(302,1),(303,1),(304,1),(305,1),(306,1),(307,1),(308,1),(309,1),(310,1),(311,1),(312,1),(313,1),(314,1),(315,1),(316,1),(317,1),(318,1),(319,1),(320,1),(321,1),(322,1),(323,1),(324,1),(325,1),(326,1),(327,1),(328,1),(329,1),(330,1),(331,1),(332,1),(333,1),(334,1),(335,1),(336,1),(337,1),(338,1),(339,1),(340,1),(341,1),(342,1),(343,1),(344,1),(345,1),(346,1),(347,1),(348,1),(349,1),(350,1),(351,1),(352,1),(353,1),(354,1),(355,1),(356,1),(357,1),(358,1),(359,1),(360,1),(361,1),(362,1),(363,1),(364,1),(365,1),(366,1),(367,1),(368,1),(369,1),(370,1),(371,1),(372,1),(373,1),(374,1),(375,1),(376,1),(377,1),(378,1),(379,1),(380,1),(381,1),(382,1),(383,1),(384,1),(385,1),(386,1),(387,1),(388,1),(389,1),(390,1),(391,1),(392,1),(393,1),(394,1),(395,1),(396,1),(397,1),(398,1),(399,1),(400,1),(401,1),(402,1),(403,1),(404,1),(405,1),(406,1),(407,1),(408,1),(409,1),(410,1),(411,1),(412,1),(413,1),(414,1),(415,1),(416,1),(417,1),(418,1),(419,1),(420,1),(421,1),(422,1),(423,1),(424,1),(425,1),(426,1),(427,1),(428,1),(429,1),(430,1),(431,1),(432,1),(433,1),(434,1),(435,1),(436,1),(437,1),(438,1),(439,1),(440,1),(441,1),(442,1),(443,1),(444,1),(445,1),(446,1),(447,1),(448,1),(449,1),(450,1),(451,1),(452,1),(453,1),(454,1),(455,1),(456,1),(457,1),(458,1),(459,1),(460,1),(461,1),(462,1),(463,1),(464,1),(465,1),(466,1),(467,1),(468,1),(1,2),(2,2),(3,2),(4,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(13,2),(14,2),(15,2),(16,2),(17,2),(18,2),(19,2),(20,2),(21,2),(22,2),(23,2),(24,2),(25,2),(26,2),(27,2),(28,2),(29,2),(30,2),(31,2),(32,2),(33,2),(34,2),(35,2),(36,2),(37,2),(38,2),(39,2),(40,2),(41,2),(42,2),(43,2),(44,2),(45,2),(46,2),(47,2),(48,2),(49,2),(50,2),(51,2),(52,2),(53,2),(54,2),(55,2),(56,2),(57,2),(58,2),(59,2),(60,2),(61,2),(62,2),(63,2),(64,2),(65,2),(66,2),(67,2),(68,2),(69,2),(70,2),(71,2),(72,2),(73,2),(74,2),(75,2),(76,2),(77,2),(78,2),(79,2),(80,2),(81,2),(82,2),(83,2),(84,2),(85,2),(86,2),(87,2),(88,2),(89,2),(90,2),(91,2),(92,2),(93,2),(94,2),(95,2),(96,2),(97,2),(98,2),(99,2),(100,2),(101,2),(102,2),(103,2),(104,2),(105,2),(106,2),(107,2),(108,2),(109,2),(110,2),(111,2),(112,2),(113,2),(114,2),(115,2),(116,2),(117,2),(118,2),(119,2),(120,2),(121,2),(122,2),(123,2),(124,2),(125,2),(126,2),(127,2),(128,2),(129,2),(130,2),(131,2),(132,2),(133,2),(134,2),(135,2),(136,2),(137,2),(138,2),(139,2),(140,2),(141,2),(142,2),(143,2),(144,2),(145,2),(146,2),(147,2),(148,2),(149,2),(150,2),(151,2),(152,2),(153,2),(154,2),(155,2),(156,2),(157,2),(158,2),(159,2),(160,2),(161,2),(162,2),(163,2),(164,2),(165,2),(167,2),(168,2),(169,2),(170,2),(171,2),(173,2),(174,2),(175,2),(176,2),(177,2),(178,2),(179,2),(180,2),(181,2),(182,2),(183,2),(184,2),(185,2),(186,2),(187,2),(188,2),(189,2),(190,2),(191,2),(192,2),(193,2),(194,2),(195,2),(196,2),(197,2),(198,2),(199,2),(200,2),(201,2),(202,2),(203,2),(204,2),(205,2),(206,2),(207,2),(208,2),(209,2),(210,2),(211,2),(212,2),(213,2),(214,2),(215,2),(216,2),(217,2),(218,2),(219,2),(220,2),(221,2),(222,2),(223,2),(224,2),(225,2),(226,2),(227,2),(228,2),(229,2),(230,2),(231,2),(232,2),(233,2),(234,2),(235,2),(236,2),(237,2),(238,2),(239,2),(240,2),(241,2),(242,2),(243,2),(244,2),(245,2),(246,2),(247,2),(248,2),(249,2),(250,2),(251,2),(252,2),(253,2),(254,2),(255,2),(256,2),(257,2),(258,2),(259,2),(260,2),(261,2),(262,2),(263,2),(264,2),(265,2),(266,2),(267,2),(268,2),(269,2),(270,2),(271,2),(272,2),(273,2),(274,2),(275,2),(276,2),(277,2),(278,2),(279,2),(280,2),(281,2),(282,2),(283,2),(284,2),(285,2),(286,2),(287,2),(288,2),(289,2),(290,2),(291,2),(292,2),(293,2),(294,2),(295,2),(296,2),(297,2),(298,2),(299,2),(300,2),(301,2),(302,2),(303,2),(304,2),(305,2),(306,2),(307,2),(308,2),(309,2),(310,2),(311,2),(312,2),(313,2),(314,2),(315,2),(316,2),(317,2),(318,2),(319,2),(320,2),(321,2),(322,2),(323,2),(324,2),(325,2),(326,2),(327,2),(328,2),(329,2),(330,2),(331,2),(332,2),(333,2),(334,2),(335,2),(336,2),(337,2),(338,2),(339,2),(340,2),(341,2),(342,2),(343,2),(344,2),(345,2),(346,2),(347,2),(348,2),(349,2),(350,2),(351,2),(352,2),(353,2),(354,2),(355,2),(356,2),(357,2),(358,2),(359,2),(360,2),(361,2),(362,2),(363,2),(364,2),(365,2),(366,2),(367,2),(368,2),(369,2),(370,2),(371,2),(372,2),(373,2),(374,2),(375,2),(376,2),(377,2),(378,2),(379,2),(380,2),(381,2),(382,2),(383,2),(384,2),(385,2),(386,2),(387,2),(388,2),(389,2),(390,2),(391,2),(392,2),(393,2),(394,2),(395,2),(396,2),(397,2),(398,2),(399,2),(400,2),(401,2),(402,2),(403,2),(404,2),(405,2),(406,2),(407,2),(408,2),(409,2),(410,2),(411,2),(412,2),(413,2),(414,2),(415,2),(416,2),(417,2),(418,2),(419,2),(420,2),(421,2),(422,2),(423,2),(424,2),(425,2),(426,2),(427,2),(428,2),(429,2),(430,2),(431,2),(432,2),(433,2),(434,2),(435,2),(436,2),(437,2),(438,2),(439,2),(440,2),(441,2),(442,2),(443,2),(444,2),(445,2),(446,2),(447,2),(448,2),(449,2),(450,2),(451,2),(452,2),(453,2),(454,2),(455,2),(456,2),(457,2),(458,2),(459,2),(460,2),(461,2),(462,2),(463,2),(464,2),(465,2),(466,2),(467,2),(468,2),(2,3),(5,3),(6,3),(8,3),(11,3),(12,3),(14,3),(17,3),(18,3),(20,3),(23,3),(24,3),(26,3),(29,3),(30,3),(32,3),(35,3),(36,3),(38,3),(41,3),(42,3),(44,3),(47,3),(48,3),(50,3),(53,3),(54,3),(56,3),(59,3),(60,3),(62,3),(65,3),(66,3),(68,3),(71,3),(72,3),(74,3),(77,3),(78,3),(80,3),(83,3),(84,3),(86,3),(89,3),(90,3),(92,3),(95,3),(96,3),(98,3),(101,3),(102,3),(104,3),(107,3),(108,3),(110,3),(113,3),(114,3),(116,3),(119,3),(120,3),(122,3),(125,3),(126,3),(128,3),(131,3),(132,3),(134,3),(137,3),(138,3),(140,3),(143,3),(144,3),(146,3),(149,3),(150,3),(152,3),(155,3),(156,3),(158,3),(161,3),(162,3),(164,3),(167,3),(168,3),(170,3),(173,3),(174,3),(176,3),(179,3),(180,3),(182,3),(185,3),(186,3),(188,3),(191,3),(192,3),(226,3),(229,3),(230,3),(233,3),(236,3),(237,3),(240,3),(243,3),(244,3),(247,3),(250,3),(251,3),(254,3),(257,3),(258,3),(261,3),(264,3),(265,3),(268,3),(271,3),(272,3),(275,3),(278,3),(279,3),(282,3),(285,3),(286,3),(289,3),(292,3),(293,3),(296,3),(299,3),(300,3),(303,3),(306,3),(307,3),(310,3),(313,3),(314,3),(317,3),(320,3),(321,3),(324,3),(327,3),(328,3),(331,3),(334,3),(335,3),(338,3),(341,3),(342,3),(345,3),(348,3),(349,3),(352,3),(355,3),(356,3),(359,3),(362,3),(363,3),(2,4),(8,4),(14,4),(20,4),(26,4),(32,4),(38,4),(44,4),(50,4),(56,4),(62,4),(68,4),(74,4),(80,4),(86,4),(92,4),(98,4),(104,4),(110,4),(116,4),(122,4),(128,4),(134,4),(140,4),(146,4),(152,4),(158,4),(164,4),(170,4),(176,4),(182,4),(188,4),(226,4),(233,4),(240,4),(247,4),(254,4),(261,4),(268,4),(275,4),(282,4),(289,4),(296,4),(303,4),(310,4),(317,4),(324,4),(331,4),(338,4),(345,4),(352,4),(359,4);
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
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `roles` VALUES (1,'super-admin','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(2,'admin','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(3,'manager','web','2026-03-04 12:27:16','2026-03-04 12:27:16'),(4,'user','web','2026-03-04 12:27:16','2026-03-04 12:27:16');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rubriques`
--

DROP TABLE IF EXISTS `rubriques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rubriques` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'gain, retenue, cotisation',
  `base_calcul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'fixe, pourcentage, formule',
  `taux` double DEFAULT NULL,
  `montant_fixe` double DEFAULT NULL,
  `formule` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `imposable` tinyint(1) NOT NULL DEFAULT '1',
  `cotisable` tinyint(1) NOT NULL DEFAULT '1',
  `ordre_affichage` int NOT NULL DEFAULT '0',
  `statut` int NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rubriques_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rubriques`
--

LOCK TABLES `rubriques` WRITE;
/*!40000 ALTER TABLE `rubriques` DISABLE KEYS */;
/*!40000 ALTER TABLE `rubriques` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('HdXRyzMbBhiwWsGRfiYbVVXtuhYcqidSSUWorIbc',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:149.0) Gecko/20100101 Firefox/149.0','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiU3VIQWo3dWE3NlZRUmdjbURZSHA5NG9WNmRYZUY5MldzWHRiM2VMRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9pbnRyYW5ldC90YWNoZXMvMTYiO3M6NToicm91dGUiO3M6MjA6ImludHJhbmV0LnRhY2hlcy5zaG93Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjQ6ImF1dGgiO2E6MTp7czoyMToicGFzc3dvcmRfY29uZmlybWVkX2F0IjtpOjE3NzY3MjI0MDY7fX0=',1776723265),('JQI5dj51Eb3gEkyxqUvJUaq32Bsb8u9GVtjozEm1',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:149.0) Gecko/20100101 Firefox/149.0','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVVFrWk9vZm5IUFFLaFU3c3VmN2x3elBxMGtWREtzTmtNUGdJY2JwQSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcHJvamV0LzUvamFsb25zIjtzOjU6InJvdXRlIjtzOjE5OiJwcm9qZXQuamFsb25zLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjQ6ImF1dGgiO2E6MTp7czoyMToicGFzc3dvcmRfY29uZmlybWVkX2F0IjtpOjE3NzY0OTc2MzU7fX0=',1776524636),('qWIvMKoSyBhuNsr2ckxXrE0gomrElFxsDzdk88oh',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:149.0) Gecko/20100101 Firefox/149.0','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiMllSSndld1I1N2h2bjk2eHlmemZzTXR3SlRvdlpkNXFSY2VFNUxmZSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ1OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcHJvamV0LzUvZmV1aWxsZXMtdGVtcHMiO3M6NToicm91dGUiO3M6Mjc6InByb2pldC5mZXVpbGxlcy10ZW1wcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo0OiJhdXRoIjthOjE6e3M6MjE6InBhc3N3b3JkX2NvbmZpcm1lZF9hdCI7aToxNzc2NzY1OTkxO319',1776767531),('W7dUVT9BPgYkWg974hROsTxJXvPa850A87KWZp5z',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:149.0) Gecko/20100101 Firefox/149.0','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiRVQwMXR0d3VTOWdrVUJnRkxGUmVrVVJQNmVyd1JzSHhWWUxtbnM5eSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM0OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcHJvamV0LzUvd2JzIjtzOjU6InJvdXRlIjtzOjE2OiJwcm9qZXQud2JzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjQ6ImF1dGgiO2E6MTp7czoyMToicGFzc3dvcmRfY29uZmlybWVkX2F0IjtpOjE3NzY0NjQ1OTM7fX0=',1776469238),('WrgHPTs5WA2TzlSh4HBs8GwjML1BHLQc2sCXXsSS',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:149.0) Gecko/20100101 Firefox/149.0','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiSWZieVJPTnJhaGdPY05NeFEyNzlvS1B1bDBFWkZLUFhEd2pvQ1pmRSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvaW50cmFuZXQvY291cnJpZXJzIjtzOjU6InJvdXRlIjtzOjI0OiJpbnRyYW5ldC5jb3VycmllcnMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc3ODE3OTc2Mjt9fQ==',1778179863),('wvNYRDq5y3sMAYv71l5MGvRBzFrjlBHOOI92HHUs',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:149.0) Gecko/20100101 Firefox/149.0','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiQzRRSVE5QVd6eE5YbU44R3NSNFpPOVVtSUNLYTBmVGRLRURVc2JaciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjQ6ImF1dGgiO2E6MTp7czoyMToicGFzc3dvcmRfY29uZmlybWVkX2F0IjtpOjE3Nzc0ODM3MTY7fX0=',1777484045);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `titres`
--

DROP TABLE IF EXISTS `titres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `titres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `imputation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seuil` double DEFAULT NULL,
  `type_ligne` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'depense, recette',
  `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `effacer` tinyint NOT NULL DEFAULT '0',
  `id_user` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `titres`
--

LOCK TABLES `titres` WRITE;
/*!40000 ALTER TABLE `titres` DISABLE KEYS */;
/*!40000 ALTER TABLE `titres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_comptes`
--

DROP TABLE IF EXISTS `transaction_comptes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_comptes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `compte_id` bigint unsigned NOT NULL,
  `sens` tinyint NOT NULL COMMENT '1=debit, 0=credit',
  `montant` double NOT NULL,
  `effacer` tinyint NOT NULL DEFAULT '0',
  `dateeffet` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_comptes_compte_id_foreign` (`compte_id`),
  CONSTRAINT `transaction_comptes_compte_id_foreign` FOREIGN KEY (`compte_id`) REFERENCES `comptes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_comptes`
--

LOCK TABLES `transaction_comptes` WRITE;
/*!40000 ALTER TABLE `transaction_comptes` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction_comptes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `type_localites`
--

DROP TABLE IF EXISTS `type_localites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_localites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime DEFAULT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribut1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int NOT NULL DEFAULT '1',
  `id_user` int NOT NULL DEFAULT '1',
  `effacer` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `type_localites`
--

LOCK TABLES `type_localites` WRITE;
/*!40000 ALTER TABLE `type_localites` DISABLE KEYS */;
/*!40000 ALTER TABLE `type_localites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `typesdysfonctionnements`
--

DROP TABLE IF EXISTS `typesdysfonctionnements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `typesdysfonctionnements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `typesdysfonctionnements`
--

LOCK TABLES `typesdysfonctionnements` WRITE;
/*!40000 ALTER TABLE `typesdysfonctionnements` DISABLE KEYS */;
/*!40000 ALTER TABLE `typesdysfonctionnements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `typesevenementscarrieres`
--

DROP TABLE IF EXISTS `typesevenementscarrieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `typesevenementscarrieres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `typesevenementscarrieres`
--

LOCK TABLES `typesevenementscarrieres` WRITE;
/*!40000 ALTER TABLE `typesevenementscarrieres` DISABLE KEYS */;
/*!40000 ALTER TABLE `typesevenementscarrieres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `typesorganisations`
--

DROP TABLE IF EXISTS `typesorganisations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `typesorganisations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `typesorganisations`
--

LOCK TABLES `typesorganisations` WRITE;
/*!40000 ALTER TABLE `typesorganisations` DISABLE KEYS */;
/*!40000 ALTER TABLE `typesorganisations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenoms` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint unsigned DEFAULT NULL,
  `profile_photo_path` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_interne` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apiclient` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricule` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` int NOT NULL DEFAULT '1',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_email_interne_unique` (`email_interne`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrateur','Systeme','admin@optimize.local',NULL,'$2y$12$oUKgfesEPHq9TTbTeCo.V.S5RK8I7/jXrPKUPyG/n2kB4YK7kO0zO',NULL,NULL,NULL,NULL,NULL,'000000000',NULL,NULL,NULL,1,NULL,NULL,'2026-03-04 12:27:17','2026-03-04 12:27:17'),(2,'Utilisateur','Test','test@optimize.local',NULL,'$2y$12$GMVzn0qYjUcLfPyQBCCIxODXCKDIFo78vTCq87Gl4oCHmKQuMJ7Vi',NULL,NULL,NULL,NULL,NULL,'000000001',NULL,NULL,NULL,1,NULL,NULL,'2026-03-04 12:27:18','2026-03-04 12:27:18');
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

-- Dump completed on 2026-05-08 18:36:51
