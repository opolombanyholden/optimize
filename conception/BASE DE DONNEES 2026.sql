-- ============================================================
-- BASE DE DONNÉES OPTIMIZE - SUITE COMPLÈTE
-- Modules: Finance, RH/Paie, Achat & Moyens Généraux
-- Version: 2.0
-- Date: 2024
-- SGBD: MySQL 5.7+ / PostgreSQL
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- 1. TABLES SYSTÈME & AUTHENTIFICATION
-- ============================================================

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenoms` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_interne` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apiclient` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricule` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_email_interne_unique` (`email_interne`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'finance, rh, achat, mg',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_user` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse_ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `authentifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operations` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'login, logout, failed',
  `lieu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `changespasswords` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `users` bigint(20) UNSIGNED DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `changespasswords_users_foreign` (`users`),
  CONSTRAINT `changespasswords_users_foreign` FOREIGN KEY (`users`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. TABLES GÉOGRAPHIQUES & ORGANISATION
-- ============================================================

CREATE TABLE `pays` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` int(11) DEFAULT NULL,
  `zipcode` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alpha2` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alpha3` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_en_gb` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_fr_fr` varchar(225) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isvalide` int(11) DEFAULT 1,
  `statut` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `regions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime DEFAULT CURRENT_TIMESTAMP,
  `num` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` int(11) DEFAULT 1,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int(11) DEFAULT 1,
  `id_user` int(11) NOT NULL DEFAULT 1,
  `id_pays` bigint(20) UNSIGNED NOT NULL DEFAULT 80,
  `effacer` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_region_pays` (`id_pays`),
  CONSTRAINT `fk_region_pays` FOREIGN KEY (`id_pays`) REFERENCES `pays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `type_localites` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int(11) DEFAULT 1,
  `id_user` int(11) NOT NULL DEFAULT 1,
  `effacer` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `localites` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` int(11) DEFAULT 1,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int(11) DEFAULT 1,
  `id_user` int(11) NOT NULL DEFAULT 1,
  `effacer` int(11) DEFAULT 0,
  `id_typelocalite` bigint(20) UNSIGNED NOT NULL,
  `id_region` bigint(20) UNSIGNED NOT NULL,
  `id_pays` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_localite_pays1` (`id_pays`),
  KEY `fk_localite_region1` (`id_region`),
  KEY `fk_localite_typelocalite1` (`id_typelocalite`),
  CONSTRAINT `fk_localite_pays1` FOREIGN KEY (`id_pays`) REFERENCES `pays` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_localite_region1` FOREIGN KEY (`id_region`) REFERENCES `regions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_localite_typelocalite1` FOREIGN KEY (`id_typelocalite`) REFERENCES `type_localites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `arrondissements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` int(11) DEFAULT 1,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int(11) DEFAULT 1,
  `id_user` int(11) NOT NULL DEFAULT 1,
  `effacer` int(11) DEFAULT 0,
  `id_localite` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_arrondissement_localite1` (`id_localite`),
  CONSTRAINT `fk_arrondissement_localite1` FOREIGN KEY (`id_localite`) REFERENCES `localites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_unicode_ci;

CREATE TABLE `quartiers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` int(11) DEFAULT 1,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int(11) DEFAULT 1,
  `id_user` int(11) NOT NULL DEFAULT 1,
  `effacer` int(11) DEFAULT 0,
  `id_arrondissement` bigint(20) UNSIGNED NOT NULL,
  `id_localite` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_quartier_localite1` (`id_localite`),
  KEY `fk_quartier_arrondissement1` (`id_arrondissement`),
  CONSTRAINT `fk_quartier_arrondissement1` FOREIGN KEY (`id_arrondissement`) REFERENCES `arrondissements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quartier_localite1` FOREIGN KEY (`id_localite`) REFERENCES `localites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `entites` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_localite` bigint(20) UNSIGNED DEFAULT NULL,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `effacer` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entites_id_localite_foreign` (`id_localite`),
  CONSTRAINT `entites_id_localite_foreign` FOREIGN KEY (`id_localite`) REFERENCES `localites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `organisations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `typesorganisation_id` bigint(20) UNSIGNED NOT NULL,
  `chefs` bigint(20) UNSIGNED NOT NULL COMMENT 'user_id',
  `peres` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'organisation_id parent',
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisations_typesorganisation_id_foreign` (`typesorganisation_id`),
  KEY `organisations_chefs_foreign` (`chefs`),
  KEY `organisations_peres_foreign` (`peres`),
  CONSTRAINT `organisations_typesorganisation_id_foreign` FOREIGN KEY (`typesorganisation_id`) REFERENCES `typesorganisations` (`id`),
  CONSTRAINT `organisations_chefs_foreign` FOREIGN KEY (`chefs`) REFERENCES `users` (`id`),
  CONSTRAINT `organisations_peres_foreign` FOREIGN KEY (`peres`) REFERENCES `organisations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `typesorganisations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. MODULE FINANCE
-- ============================================================

CREATE TABLE `configs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `config_object` json NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `exercices` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `exercice` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_regroupement` bigint(20) UNSIGNED DEFAULT NULL,
  `dateeffect` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datedebut` datetime DEFAULT NULL,
  `datefin` datetime DEFAULT NULL,
  `entite` datetime DEFAULT NULL,
  `budgetglobalinitial` double DEFAULT NULL,
  `commentaire` mediumtext COLLATE utf8mb4_unicode_ci,
  `attribut1` mediumtext COLLATE utf8mb4_unicode_ci,
  `attribut2` mediumtext COLLATE utf8mb4_unicode_ci,
  `attribut3` mediumtext COLLATE utf8mb4_unicode_ci,
  `isvalide` int(11) DEFAULT 0,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `effacer` int(11) NOT NULL DEFAULT 0,
  `version_exercice` int(11) NOT NULL DEFAULT 1,
  `statut` int(11) NOT NULL DEFAULT 3 COMMENT '1=planifié, 2=en cours, 3=clôturé',
  `dotationglobale` double(8,2) NOT NULL DEFAULT 0.00,
  `fondpropreglobal` double(8,2) NOT NULL DEFAULT 0.00,
  `reportbudgetiare` double(8,2) NOT NULL DEFAULT 0.00,
  `reporttresorerieglobal` double(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exercices_id_user_foreign` (`id_user`),
  CONSTRAINT `exercices_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `titres` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `imputation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seuil` double UNSIGNED DEFAULT NULL,
  `type_ligne` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'depense, recette',
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `effacer` tinyint(1) NOT NULL DEFAULT 0,
  `id_user` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lignes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_titre` bigint(20) UNSIGNED NOT NULL,
  `nature` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seuil` double UNSIGNED DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `isvalide` int(11) DEFAULT 0,
  `effacer` tinyint(1) NOT NULL DEFAULT 0,
  `id_codeanalytique` int(11) DEFAULT NULL,
  `id_famillecodeanalytique` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL DEFAULT 1,
  `dateeffet` datetime DEFAULT NULL,
  `id_typecode` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lignes_id_titre_index` (`id_titre`),
  CONSTRAINT `lignes_id_titre_foreign` FOREIGN KEY (`id_titre`) REFERENCES `titres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `budget_lignes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_budgetligne` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateeffect` timestamp NULL DEFAULT NULL,
  `id_exercicebudgetaire` bigint(20) UNSIGNED DEFAULT NULL,
  `exercice` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_famillecodeanalytique` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'id titre',
  `id_codeanalytique` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'id ligne',
  `codecompte` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `budgetligne` double(8,2) NOT NULL DEFAULT 0.00,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `attribut1` text COLLATE utf8mb4_unicode_ci,
  `attribut2` text COLLATE utf8mb4_unicode_ci,
  `attribut3` text COLLATE utf8mb4_unicode_ci,
  `isvalide` tinyint(4) NOT NULL DEFAULT 0,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `effacer` tinyint(4) NOT NULL DEFAULT 0,
  `dotation_etat` double(8,2) DEFAULT NULL,
  `fonds_propres` double(8,2) DEFAULT NULL,
  `reports_budgetaire` double(8,2) DEFAULT NULL,
  `reports_tresorerie` double(8,2) DEFAULT NULL,
  `transfert` double(8,2) NOT NULL DEFAULT 0.00,
  `engagement` double(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `budget_lignes_id_exercicebudgetaire_foreign` (`id_exercicebudgetaire`),
  KEY `budget_lignes_id_user_foreign` (`id_user`),
  CONSTRAINT `budget_lignes_id_exercicebudgetaire_foreign` FOREIGN KEY (`id_exercicebudgetaire`) REFERENCES `exercices` (`id`),
  CONSTRAINT `budget_lignes_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `modification_budgetaires` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_compte_emission` bigint(20) UNSIGNED DEFAULT NULL,
  `codecompte_emission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_compte_reception` bigint(20) UNSIGNED DEFAULT NULL,
  `codecompte_reception` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objetmodification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_modification` double(8,2) DEFAULT NULL,
  `isbudgetligne` tinyint(4) DEFAULT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `statut` int(11) DEFAULT 0 COMMENT '0=brouillon, 1=soumis, 2=validé, 3=rejeté',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `modification_budgetaires_id_user_foreign` (`id_user`),
  CONSTRAINT `modification_budgetaires_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comptes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL COMMENT '1=banque, 2=caisse, 3=tiers',
  `entite_id` int(11) DEFAULT NULL,
  `solde` double(8,2) DEFAULT 0.00,
  `rib` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsable` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestionnaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_gestionnaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domiciliation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut1` text COLLATE utf8mb4_unicode_ci,
  `attribut2` text COLLATE utf8mb4_unicode_ci,
  `attribut3` text COLLATE utf8mb4_unicode_ci,
  `effacer` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transaction_comptes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `compte_id` bigint(20) UNSIGNED NOT NULL,
  `sens` tinyint(1) NOT NULL COMMENT '1=débit, 0=crédit',
  `montant` double(8,2) NOT NULL,
  `effacer` tinyint(1) NOT NULL DEFAULT 0,
  `dateeffet` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_comptes_compte_id_foreign` (`compte_id`),
  CONSTRAINT `transaction_comptes_compte_id_foreign` FOREIGN KEY (`compte_id`) REFERENCES `comptes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `grand_livres` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_ecriture` date DEFAULT NULL,
  `id_entite` bigint(20) UNSIGNED DEFAULT NULL,
  `dateeffet` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `montant_tc` double DEFAULT NULL,
  `sens` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode_reglement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exercice` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_exercicebudgetaire` bigint(20) UNSIGNED DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `id_user` bigint(20) UNSIGNED DEFAULT NULL,
  `rib` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banque` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_beneficiaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiaire_interne` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_beneficiaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_beneficiaire_interne` bigint(20) UNSIGNED DEFAULT NULL,
  `libelle_piece` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_piece` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_ligne` bigint(20) UNSIGNED DEFAULT NULL,
  `id_titre` bigint(20) UNSIGNED DEFAULT NULL,
  `imputation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_piece` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_piece` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nature_piece` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_signe_tc` double DEFAULT NULL,
  `compte_id` bigint(20) UNSIGNED DEFAULT NULL,
  `compte_general` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `compte_auxiliaire` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_tiers` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `journal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `devise` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_tr` double DEFAULT NULL,
  `code_lettrage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_marquage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_lettrage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_pointage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lettre_rappro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_rappro` date DEFAULT NULL,
  `type_ecriture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_lot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_ecriture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_tiers` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant_signe_tr` double(8,2) DEFAULT NULL,
  `id_famillecodeanalytique` bigint(20) UNSIGNED DEFAULT NULL,
  `id_codeanalytique` bigint(20) UNSIGNED DEFAULT NULL,
  `id_organisation` bigint(20) UNSIGNED DEFAULT NULL,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` int(11) DEFAULT 0,
  `effacer` int(11) NOT NULL DEFAULT 0,
  `num_stat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nif` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_facture` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grand_livres_compte_id_foreign` (`compte_id`),
  KEY `grand_livres_id_user_foreign` (`id_user`),
  CONSTRAINT `grand_livres_compte_id_foreign` FOREIGN KEY (`compte_id`) REFERENCES `comptes` (`id`),
  CONSTRAINT `grand_livres_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `grand_livre_details` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_facture` bigint(20) UNSIGNED DEFAULT NULL,
  `id_produit` bigint(20) UNSIGNED DEFAULT NULL,
  `prix` double DEFAULT NULL,
  `quantite` double DEFAULT NULL,
  `total_ht` double DEFAULT NULL,
  `total_taxe` double DEFAULT NULL,
  `total_reduction` double DEFAULT NULL,
  `total_ttc` double DEFAULT NULL,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `effacer` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mode_reglements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dateeffet` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` int(11) NOT NULL DEFAULT 1,
  `attribut1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribut3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isvalide` tinyint(4) DEFAULT 1,
  `id_user` int(11) NOT NULL DEFAULT 1,
  `effacer` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. MODULE RH & PAIE
-- ============================================================

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `noms` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenoms` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `matricule` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexe` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `situation_matrimoniale` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_enfants` int(11) DEFAULT 0,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text COLLATE utf8mb4_unicode_ci,
  `id_quartier` bigint(20) UNSIGNED DEFAULT NULL,
  `id_ville` bigint(20) UNSIGNED DEFAULT NULL,
  `id_pays` bigint(20) UNSIGNED DEFAULT NULL,
  `date_embauche` date DEFAULT NULL,
  `type_contrat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_fin_contrat` date DEFAULT NULL,
  `poste` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `superieur_hierarchique` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'employee_id',
  `salaire_base` double(8,2) DEFAULT 0.00,
  `iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_secu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` int(11) DEFAULT 1 COMMENT '1=actif, 2=inactif, 3=parti',
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci COMMENT 'CV, pièces jointes',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_matricule_unique` (`matricule`),
  KEY `employees_user_id_foreign` (`user_id`),
  KEY `employees_superieur_hierarchique_foreign` (`superieur_hierarchique`),
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `employees_superieur_hierarchique_foreign` FOREIGN KEY (`superieur_hierarchique`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `affilies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `liens` text COLLATE utf8mb4_unicode_ci COMMENT 'conjoint, enfant, etc.',
  `noms` text COLLATE utf8mb4_unicode_ci,
  `prenoms` text COLLATE utf8mb4_unicode_ci,
  `date_naissance` date DEFAULT NULL,
  `contact1` text COLLATE utf8mb4_unicode_ci,
  `contact2` text COLLATE utf8mb4_unicode_ci,
  `email` text COLLATE utf8mb4_unicode_ci,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affilies_employee_id_foreign` (`employee_id`),
  CONSTRAINT `affilies_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `abscences` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_abscence` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'conge_paye, maladie, mission, etc.',
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci COMMENT 'justificatifs',
  `statut` int(11) DEFAULT 0 COMMENT '0=brouillon, 1=soumis, 2=validé, 3=rejeté',
  `validé_par` bigint(20) UNSIGNED DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `abscences_employee_id_foreign` (`employee_id`),
  KEY `abscences_valide_par_foreign` (`validé_par`),
  CONSTRAINT `abscences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `abscences_valide_par_foreign` FOREIGN KEY (`validé_par`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `competences` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `niveau` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'débutant, intermédiaire, expert',
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci COMMENT 'certificats, diplômes',
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `competences_employee_id_foreign` (`employee_id`),
  CONSTRAINT `competences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `qualifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `organisme` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qualifications_employee_id_foreign` (`employee_id`),
  CONSTRAINT `qualifications_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `evenementscarrieres` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `typesevenementscarriere_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date_effet` date DEFAULT NULL,
  `ancien_poste` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nouveau_poste` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evenementscarrieres_employee_id_foreign` (`employee_id`),
  CONSTRAINT `evenementscarrieres_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `typesevenementscarrieres` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'promotion, mutation, licenciement, etc.',
  `description` text COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recrutements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `statut` int(11) DEFAULT 0 COMMENT '0=ouvert, 1=fermé, 2=pourvu',
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `postulants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `noms` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenoms` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_naissance` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profil_id` bigint(20) UNSIGNED NOT NULL,
  `recrutement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `statut` int(11) DEFAULT 0 COMMENT '0=nouveau, 1=entretenu, 2=retenu, 3=rejeté',
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci COMMENT 'CV, lettres',
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

CREATE TABLE `profils` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `nombres` text COLLATE utf8mb4_unicode_ci COMMENT 'nombre de postes',
  `recrutement_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profils_recrutement_id_foreign` (`recrutement_id`),
  CONSTRAINT `profils_recrutement_id_foreign` FOREIGN KEY (`recrutement_id`) REFERENCES `recrutements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `embauches` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `valider` tinyint(1) DEFAULT 0,
  `postulant_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci COMMENT 'contrat signé',
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `embauches_postulant_id_foreign` (`postulant_id`),
  KEY `embauches_employee_id_foreign` (`employee_id`),
  CONSTRAINT `embauches_postulant_id_foreign` FOREIGN KEY (`postulant_id`) REFERENCES `postulants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `embauches_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pcompetences` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `postulant_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pcompetences_postulant_id_foreign` (`postulant_id`),
  CONSTRAINT `pcompetences_postulant_id_foreign` FOREIGN KEY (`postulant_id`) REFERENCES `postulants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pqualifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `postulant_id` bigint(20) UNSIGNED NOT NULL,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pqualifications_postulant_id_foreign` (`postulant_id`),
  CONSTRAINT `pqualifications_postulant_id_foreign` FOREIGN KEY (`postulant_id`) REFERENCES `postulants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pexamens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `postulant_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pexamens_postulant_id_foreign` (`postulant_id`),
  CONSTRAINT `pexamens_postulant_id_foreign` FOREIGN KEY (`postulant_id`) REFERENCES `postulants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `resultats` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `pexamen_id` bigint(20) UNSIGNED NOT NULL,
  `note` double(5,2) DEFAULT NULL,
  `appreciation` text COLLATE utf8mb4_unicode_ci,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resultats_pexamen_id_foreign` (`pexamen_id`),
  CONSTRAINT `resultats_pexamen_id_foreign` FOREIGN KEY (`pexamen_id`) REFERENCES `pexamens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pentretiens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `postulant_id` bigint(20) UNSIGNED NOT NULL,
  `interviewer_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'user_id',
  `appreciation` text COLLATE utf8mb4_unicode_ci,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pentretiens_postulant_id_foreign` (`postulant_id`),
  CONSTRAINT `pentretiens_postulant_id_foreign` FOREIGN KEY (`postulant_id`) REFERENCES `postulants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tests` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `profil_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tests_profil_id_foreign` (`profil_id`),
  CONSTRAINT `tests_profil_id_foreign` FOREIGN KEY (`profil_id`) REFERENCES `profils` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `missions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `lieu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objet` text COLLATE utf8mb4_unicode_ci,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `missions_employee_id_foreign` (`employee_id`),
  CONSTRAINT `missions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `groupes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `groupes_users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `create_by` bigint(20) UNSIGNED NOT NULL,
  `groupe_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupes_users_groupe_id_foreign` (`groupe_id`),
  KEY `groupes_users_user_id_foreign` (`user_id`),
  CONSTRAINT `groupes_users_groupe_id_foreign` FOREIGN KEY (`groupe_id`) REFERENCES `groupes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groupes_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `messagesgroupes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `objects` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forusers` text COLLATE utf8mb4_unicode_ci COMMENT 'user_ids JSON',
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `messages` text COLLATE utf8mb4_unicode_ci,
  `messagesgroupe_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_messagesgroupe_id_foreign` (`messagesgroupe_id`),
  KEY `messages_employee_id_foreign` (`employee_id`),
  CONSTRAINT `messages_messagesgroupe_id_foreign` FOREIGN KEY (`messagesgroupe_id`) REFERENCES `messagesgroupes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `privileges` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `privileges_employee_id_foreign` (`employee_id`),
  CONSTRAINT `privileges_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `remarques` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `remarques_employee_id_foreign` (`employee_id`),
  CONSTRAINT `remarques_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `incidents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `typesincident_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incidents_employee_id_foreign` (`employee_id`),
  CONSTRAINT `incidents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `typesincidents` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `inconveniants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inconveniants_employee_id_foreign` (`employee_id`),
  CONSTRAINT `inconveniants_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `integrations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `integrations_employee_id_foreign` (`employee_id`),
  CONSTRAINT `integrations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invitations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `annuaire_id` bigint(20) UNSIGNED NOT NULL,
  `evenement_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `perfectionements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `typesperfectionement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `perfectionements_employee_id_foreign` (`employee_id`),
  CONSTRAINT `perfectionements_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `typesperfectionements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. MODULE PAIE
-- ============================================================

CREATE TABLE `gpaies` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `affectation_id` bigint(20) UNSIGNED NOT NULL COMMENT 'employee_id',
  `periode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_paiement` date DEFAULT NULL,
  `salaire_base` double(8,2) DEFAULT 0.00,
  `primes` double(8,2) DEFAULT 0.00,
  `cotisations` double(8,2) DEFAULT 0.00,
  `net_a_payer` double(8,2) DEFAULT 0.00,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci COMMENT 'bulletin PDF',
  `statut` int(11) DEFAULT 0 COMMENT '0=brouillon, 1=validé, 2=payé',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gpaies_affectation_id_foreign` (`affectation_id`),
  CONSTRAINT `gpaies_affectation_id_foreign` FOREIGN KEY (`affectation_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rubriques` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `gpaie_id` bigint(20) UNSIGNED NOT NULL,
  `field1` bigint(20) UNSIGNED NOT NULL COMMENT 'rubrique_id source',
  `field2` bigint(20) UNSIGNED NOT NULL COMMENT 'rubrique_id source',
  `operateur` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '+, -, *, /',
  `types` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'gain, retenue, info',
  `base` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rubriques_gpaie_id_foreign` (`gpaie_id`),
  CONSTRAINT `rubriques_gpaie_id_foreign` FOREIGN KEY (`gpaie_id`) REFERENCES `gpaies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payementglobals` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `types` text COLLATE utf8mb4_unicode_ci,
  `gpaie_id` bigint(20) UNSIGNED NOT NULL,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `montant_total` double(8,2) DEFAULT 0.00,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payementglobals_gpaie_id_foreign` (`gpaie_id`),
  CONSTRAINT `payementglobals_gpaie_id_foreign` FOREIGN KEY (`gpaie_id`) REFERENCES `gpaies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `payementglobal_id` bigint(20) UNSIGNED NOT NULL,
  `affectation_id` bigint(20) UNSIGNED NOT NULL COMMENT 'employee_id',
  `montant` double(8,2) DEFAULT 0.00,
  `mode_paiement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payements_payementglobal_id_foreign` (`payementglobal_id`),
  KEY `payements_affectation_id_foreign` (`affectation_id`),
  CONSTRAINT `payements_payementglobal_id_foreign` FOREIGN KEY (`payementglobal_id`) REFERENCES `payementglobals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payements_affectation_id_foreign` FOREIGN KEY (`affectation_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. MODULE ACHAT & MOYENS GÉNÉRAUX
-- ============================================================

CREATE TABLE `types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bannieres` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `produits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `noms` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bannieres` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_id` bigint(20) UNSIGNED NOT NULL,
  `priceachat` double(8,2) NOT NULL DEFAULT 0.00,
  `pricevente` double(8,2) NOT NULL DEFAULT 0.00,
  `stock_min` int(11) DEFAULT 0,
  `stock_actuel` int(11) DEFAULT 0,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produits_type_id_foreign` (`type_id`),
  CONSTRAINT `produits_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attributs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attributs_produit_id_foreign` (`produit_id`),
  CONSTRAINT `attributs_produit_id_foreign` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fournisseurs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text COLLATE utf8mb4_unicode_ci,
  `rib` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nif` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fournisseursdetails` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `fournisseur_id` bigint(20) UNSIGNED DEFAULT NULL,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fournisseursdetails_fournisseur_id_foreign` (`fournisseur_id`),
  CONSTRAINT `fournisseursdetails_fournisseur_id_foreign` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approvisionements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `fournisseur_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `statut` int(11) DEFAULT 0 COMMENT '0=brouillon, 1=soumis, 2=validé, 3=livré',
  `montant_total` double(8,2) DEFAULT 0.00,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approvisionements_fournisseur_id_foreign` (`fournisseur_id`),
  CONSTRAINT `approvisionements_fournisseur_id_foreign` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approvisionementsproduits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `approvisionement_id` bigint(20) UNSIGNED NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` double(8,2) DEFAULT 0.00,
  `montant_total` double(8,2) DEFAULT 0.00,
  `quantite_recue` int(11) DEFAULT 0,
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approvisionementsproduits_produit_id_foreign` (`produit_id`),
  KEY `approvisionementsproduits_approvisionement_id_foreign` (`approvisionement_id`),
  CONSTRAINT `approvisionementsproduits_produit_id_foreign` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  CONSTRAINT `approvisionementsproduits_approvisionement_id_foreign` FOREIGN KEY (`approvisionement_id`) REFERENCES `approvisionements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commandesfournisseurs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `fournisseur_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 0,
  `montant_total` double(8,2) DEFAULT 0.00,
  `date_commande` date DEFAULT NULL,
  `date_livraison_prevue` date DEFAULT NULL,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commandesfournisseurs_organisation_id_foreign` (`organisation_id`),
  KEY `commandesfournisseurs_fournisseur_id_foreign` (`fournisseur_id`),
  CONSTRAINT `commandesfournisseurs_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`),
  CONSTRAINT `commandesfournisseurs_fournisseur_id_foreign` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commandesfournisseursproduits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `commandesfournisseur_id` bigint(20) UNSIGNED NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` double(8,2) DEFAULT 0.00,
  `montant_total` double(8,2) DEFAULT 0.00,
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commandesfournisseursproduits_produit_id_foreign` (`produit_id`),
  KEY `commandesfournisseursproduits_commandesfournisseur_id_foreign` (`commandesfournisseur_id`),
  CONSTRAINT `commandesfournisseursproduits_produit_id_foreign` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  CONSTRAINT `commandesfournisseursproduits_commandesfournisseur_id_foreign` FOREIGN KEY (`commandesfournisseur_id`) REFERENCES `commandesfournisseurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commandesclients` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 0,
  `montant_total` double(8,2) DEFAULT 0.00,
  `date_commande` date DEFAULT NULL,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commandesclientsproduits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `commandesclient_id` bigint(20) UNSIGNED NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` double(8,2) DEFAULT 0.00,
  `montant_total` double(8,2) DEFAULT 0.00,
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commandesclientsproduits_produit_id_foreign` (`produit_id`),
  KEY `commandesclientsproduits_commandesclient_id_foreign` (`commandesclient_id`),
  CONSTRAINT `commandesclientsproduits_produit_id_foreign` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  CONSTRAINT `commandesclientsproduits_commandesclient_id_foreign` FOREIGN KEY (`commandesclient_id`) REFERENCES `commandesclients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `livraisonsfournisseurs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `commandesfournisseur_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `livraisonsfournisseurs_commandesfournisseur_id_foreign` (`commandesfournisseur_id`),
  CONSTRAINT `livraisonsfournisseurs_commandesfournisseur_id_foreign` FOREIGN KEY (`commandesfournisseur_id`) REFERENCES `commandesfournisseurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `livraisonsfournisseursproduits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `livraisonsfournisseur_id` bigint(20) UNSIGNED NOT NULL,
  `quantite` int(11) NOT NULL,
  `quantite_recue` int(11) DEFAULT 0,
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `livraisonsfournisseursproduits_produit_id_foreign` (`produit_id`),
  KEY `livraisonsfournisseursproduits_livraisonsfournisseur_id_foreign` (`livraisonsfournisseur_id`),
  CONSTRAINT `livraisonsfournisseursproduits_produit_id_foreign` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  CONSTRAINT `livraisonsfournisseursproduits_livraisonsfournisseur_id_foreign` FOREIGN KEY (`livraisonsfournisseur_id`) REFERENCES `livraisonsfournisseurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `livraisonsclients` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `commandesclient_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `livraisonsclients_commandesclient_id_foreign` (`commandesclient_id`),
  CONSTRAINT `livraisonsclients_commandesclient_id_foreign` FOREIGN KEY (`commandesclient_id`) REFERENCES `commandesclients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `livraisonsclientsproduits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `produit_id` bigint(20) UNSIGNED NOT NULL,
  `livraisonsclient_id` bigint(20) UNSIGNED NOT NULL,
  `quantite` int(11) NOT NULL,
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `livraisonsclientsproduits_produit_id_foreign` (`produit_id`),
  KEY `livraisonsclientsproduits_livraisonsclient_id_foreign` (`livraisonsclient_id`),
  CONSTRAINT `livraisonsclientsproduits_produit_id_foreign` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  CONSTRAINT `livraisonsclientsproduits_livraisonsclient_id_foreign` FOREIGN KEY (`livraisonsclient_id`) REFERENCES `livraisonsclients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `imobilisations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `noms` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `references` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bannieres` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priceachat` double(8,2) NOT NULL DEFAULT 0.00,
  `date_acquisition` date DEFAULT NULL,
  `duree_amortissement` int(11) DEFAULT NULL COMMENT 'en mois',
  `mode_amortissement` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `localisation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsable_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'user_id',
  `statut` int(11) DEFAULT 1 COMMENT '1=en service, 2=hors service, 3=sorti',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imobilisations_responsable_id_foreign` (`responsable_id`),
  CONSTRAINT `imobilisations_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `imobilisationsdetails` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `imobilisation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imobilisationsdetails_imobilisation_id_foreign` (`imobilisation_id`),
  CONSTRAINT `imobilisationsdetails_imobilisation_id_foreign` FOREIGN KEY (`imobilisation_id`) REFERENCES `imobilisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `imobilisationsentretiens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `imobilisation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `periodelabel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `periodevalue` int(11) DEFAULT NULL COMMENT 'fréquence en jours',
  `dernier_entretien` date DEFAULT NULL,
  `prochain_entretien` date DEFAULT NULL,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imobilisationsentretiens_imobilisation_id_foreign` (`imobilisation_id`),
  CONSTRAINT `imobilisationsentretiens_imobilisation_id_foreign` FOREIGN KEY (`imobilisation_id`) REFERENCES `imobilisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `imobilisationsentretiensusers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `imobilisationsentretien_id` bigint(20) UNSIGNED DEFAULT NULL,
  `users` bigint(20) UNSIGNED DEFAULT NULL,
  `date_entretien` date DEFAULT NULL,
  `cout` double(8,2) DEFAULT 0.00,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imobilisationsentretiensusers_imobilisationsentretien_id_foreign` (`imobilisationsentretien_id`),
  KEY `imobilisationsentretiensusers_users_foreign` (`users`),
  CONSTRAINT `imobilisationsentretiensusers_imobilisationsentretien_id_foreign` FOREIGN KEY (`imobilisationsentretien_id`) REFERENCES `imobilisationsentretiens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `imobilisationsentretiensusers_users_foreign` FOREIGN KEY (`users`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `typesdisfonctionements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `categorie` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'materiel, logiciel, processus',
  `niveau_critique` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'moyen',
  `delai_traitement_requis` int(11) DEFAULT NULL COMMENT 'en heures',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `disfonctionements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `typesdisfonctionement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `imobilisation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `declare_par` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'user_id',
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `date_declaration` datetime DEFAULT CURRENT_TIMESTAMP,
  `statut` int(11) DEFAULT 0 COMMENT '0=nouveau, 1=en traitement, 2=résolu, 3=clôturé',
  `priorite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'moyenne',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disfonctionements_typesdisfonctionement_id_foreign` (`typesdisfonctionement_id`),
  KEY `disfonctionements_imobilisation_id_foreign` (`imobilisation_id`),
  KEY `disfonctionements_declare_par_foreign` (`declare_par`),
  CONSTRAINT `disfonctionements_typesdisfonctionement_id_foreign` FOREIGN KEY (`typesdisfonctionement_id`) REFERENCES `typesdisfonctionements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `disfonctionements_imobilisation_id_foreign` FOREIGN KEY (`imobilisation_id`) REFERENCES `imobilisations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `disfonctionements_declare_par_foreign` FOREIGN KEY (`declare_par`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `interventions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `disfonctionement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `intervenants` text COLLATE utf8mb4_unicode_ci COMMENT 'user_ids JSON',
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `cout` double(8,2) DEFAULT 0.00,
  `statut` int(11) DEFAULT 0 COMMENT '0=planifié, 1=en cours, 2=terminé',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `interventions_disfonctionement_id_foreign` (`disfonctionement_id`),
  CONSTRAINT `interventions_disfonctionement_id_foreign` FOREIGN KEY (`disfonctionement_id`) REFERENCES `disfonctionements` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attributions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `imobilisation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `users` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `debut` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attributions_imobilisation_id_foreign` (`imobilisation_id`),
  KEY `attributions_users_foreign` (`users`),
  KEY `attributions_employee_id_foreign` (`employee_id`),
  CONSTRAINT `attributions_imobilisation_id_foreign` FOREIGN KEY (`imobilisation_id`) REFERENCES `imobilisations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attributions_users_foreign` FOREIGN KEY (`users`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attributions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. WORKFLOWS & VALIDATIONS
-- ============================================================

CREATE TABLE `validations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `table` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `etapes` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validateurs` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'user_ids JSON',
  `rangs` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iseditable` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `childiseditable` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `depends` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` int(11) DEFAULT 0 COMMENT '0=en attente, 1=validé, 2=rejeté',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `allresources` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_type` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_id` bigint(20) UNSIGNED NOT NULL,
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `resourcespipelines` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `allresource_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pipelines` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'workflow steps JSON',
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resourcespipelines_allresource_id_foreign` (`allresource_id`),
  CONSTRAINT `resourcespipelines_allresource_id_foreign` FOREIGN KEY (`allresource_id`) REFERENCES `allresources` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `resourcesprogressions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `resourcespipeline_id` bigint(20) UNSIGNED DEFAULT NULL,
  `validateurs` text COLLATE utf8mb4_unicode_ci COMMENT 'user_ids JSON',
  `etats` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `steps` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `messages` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 0,
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resourcesprogressions_resourcespipeline_id_foreign` (`resourcespipeline_id`),
  CONSTRAINT `resourcesprogressions_resourcespipeline_id_foreign` FOREIGN KEY (`resourcespipeline_id`) REFERENCES `resourcespipelines` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. FICHIERS & DONNÉES GÉNÉRALES
-- ============================================================

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `old_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `descriptions` text COLLATE utf8mb4_unicode_ci,
  `extensions` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `web_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `files_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `booles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `extras` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `files` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `textarea` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cruds` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `models` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actions` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_first` text COLLATE utf8mb4_unicode_ci,
  `data_end` text COLLATE utf8mb4_unicode_ci,
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `models` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `extra_attributes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `apis` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'GET',
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. TEAMS (OPTIONNEL - Laravel Jetstream)
-- ============================================================

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_team` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teams_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `team_user` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_user_team_id_user_id_unique` (`team_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `team_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `team_invitations_team_id_email_unique` (`team_id`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. ORGANISATION EXTRAS
-- ============================================================

CREATE TABLE `organisationsextras` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `key` text COLLATE utf8mb4_unicode_ci,
  `value` text COLLATE utf8mb4_unicode_ci,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `fichiersjoin` text COLLATE utf8mb4_unicode_ci,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisationsextras_organisation_id_foreign` (`organisation_id`),
  CONSTRAINT `organisationsextras_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `organisationsprivileges` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `organisation_id` bigint(20) UNSIGNED NOT NULL,
  `privilege_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` int(11) DEFAULT 1,
  `extra_attributes` json DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organisationsprivileges_organisation_id_foreign` (`organisation_id`),
  CONSTRAINT `organisationsprivileges_organisation_id_foreign` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. INDEXES SUPPLÉMENTAIRES & CONTRAINTES
-- ============================================================

-- Indexes pour les tables OAuth (Laravel Passport)
CREATE TABLE IF NOT EXISTS `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oauth_clients` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oauth_personal_access_clients` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;