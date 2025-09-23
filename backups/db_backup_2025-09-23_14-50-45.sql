-- MySQL dump 10.13  Distrib 8.0.43, for Linux (aarch64)
--
-- Host: db    Database: document_vault_app
-- ------------------------------------------------------
-- Server version	8.0.43

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
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_file_id_foreign` (`file_id`),
  KEY `idx_audit_user_time` (`user_id`,`created_at`),
  KEY `idx_audit_action_time` (`action`,`created_at`),
  CONSTRAINT `audit_logs_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,2,'login',NULL,'172.19.0.6','{\"ua\": \"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36\"}','2025-09-23 11:05:35','2025-09-23 11:05:35'),(2,2,'upload',1,'172.19.0.6','{\"mime\": \"application/pdf\", \"size\": 438043}','2025-09-23 11:05:49','2025-09-23 11:05:49');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-492d4d7531d21d05fd8cbb4585d673e7806d27e1','i:1;',1758625595),('laravel-cache-492d4d7531d21d05fd8cbb4585d673e7806d27e1:timer','i:1758625595;',1758625595);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'IT Department','2025-09-23 11:05:09','2025-09-23 11:05:09'),(2,'HR Department','2025-09-23 11:05:09','2025-09-23 11:05:09'),(3,'Finance Department','2025-09-23 11:05:09','2025-09-23 11:05:09');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
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
-- Table structure for table `file_keys`
--

DROP TABLE IF EXISTS `file_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `file_keys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_id` bigint unsigned NOT NULL,
  `recipient_user_id` bigint unsigned NOT NULL,
  `encrypted_aes_key` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `key_encryption_algo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RSA-OAEP-SHA256',
  `key_fingerprint` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_file_recipient` (`file_id`,`recipient_user_id`),
  KEY `file_keys_recipient_user_id_foreign` (`recipient_user_id`),
  CONSTRAINT `file_keys_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  CONSTRAINT `file_keys_recipient_user_id_foreign` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_keys`
--

LOCK TABLES `file_keys` WRITE;
/*!40000 ALTER TABLE `file_keys` DISABLE KEYS */;
INSERT INTO `file_keys` VALUES (1,1,2,'KghNUkD+SkyPAQZa2p8EdQgdZFE31vsBtr4P6OpmbOOnnpMuuKu9Op9vCnfreQPcMufVWFwNWNu42M/Mr/twndCjrh7HZZgHYHDf9gw2kVMcvSXUyXAplUi9KABu2m5csDfIzrLoFUymaakuf1j/56MGdt5n+Q7t2PXbwP/Dy3i2yUv/cTohqViKEpw1XQjQwF+tIflQn1AnQef/VCVg/6bxQVTVBXhgvrAiLQaAb39AL7SRQDiBTuInXAJCSFS4P2YZtHn+qDW94KkXAkULMBofqi/tyGqv9Lr2LYqKuwBvt83RCzGF8S2O3Stql/qDAzHCC3trtuGwRgq9TOBTKw==','RSA-OAEP-2048','70c4994cb9b90d10','2025-09-23 11:05:49','2025-09-23 11:05:49');
/*!40000 ALTER TABLE `file_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_shares`
--

DROP TABLE IF EXISTS `file_shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `file_shares` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_id` bigint unsigned NOT NULL,
  `shared_with` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_share` (`file_id`,`shared_with`),
  KEY `file_shares_shared_with_foreign` (`shared_with`),
  CONSTRAINT `file_shares_file_id_foreign` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  CONSTRAINT `file_shares_shared_with_foreign` FOREIGN KEY (`shared_with`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_shares`
--

LOCK TABLES `file_shares` WRITE;
/*!40000 ALTER TABLE `file_shares` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_bytes` bigint unsigned NOT NULL,
  `hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ocr_text` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `files_user_id_index` (`user_id`),
  KEY `files_department_id_index` (`department_id`),
  KEY `files_title_index` (`title`),
  KEY `files_hash_index` (`hash`),
  FULLTEXT KEY `ft_files_title_ocr` (`title`,`ocr_text`),
  CONSTRAINT `files_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `files_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (1,2,NULL,'01_Activity_5.pdf','01_Activity_5.pdf','vault/2025/09/be593fd0-6909-4241-90da-96bd3c571dbb.bin','application/pdf',438043,'f5bc411ccab738ad1ab2a3e022d4a515a83ec36bfb88a58743297466f7aff0d4',NULL,'2025-09-23 11:05:49','2025-09-23 11:05:49');
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
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
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_cache_table',1),(2,'0001_01_01_000002_create_jobs_table',1),(3,'2025_09_18_233948_create_departments_table',1),(4,'2025_09_18_234033_create_users_table',1),(5,'2025_09_18_234042_create_user_keys_table',1),(6,'2025_09_18_234053_create_files_table',1),(7,'2025_09_18_234101_add_fulltext_index_to_files',1),(8,'2025_09_18_234112_create_file_keys_table',1),(9,'2025_09_18_234121_create_file_shares_table',1),(10,'2025_09_18_234128_create_audit_logs_table',1),(11,'2025_09_19_011722_add_updated_at_to_audit_logs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_keys`
--

DROP TABLE IF EXISTS `user_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_keys` (
  `user_id` bigint unsigned NOT NULL,
  `public_key` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `encrypted_private_key` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_keys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_keys`
--

LOCK TABLES `user_keys` WRITE;
/*!40000 ALTER TABLE `user_keys` DISABLE KEYS */;
INSERT INTO `user_keys` VALUES (2,'-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApQwXaiV4hPudmUKWdHG1\nzdt9YjMFaI4lFq20ws1H05+6LE6QXbSVOJj/zq7hLPctnj3IWbRU1kp9awDqVM9n\nUtvfecZYrJkLckB3tGoUXvx0QTFMxp1hJbRpY+aSiMrEsUA7cpnsb/tYdzKtMmqr\ng88tBYP69n/zSx+pKOh2UbeorYNf2KcEkylSVuL+6Cdpka2uX3jiRSuxIZjcgsos\naC3RrUTVOLJMhNBEH1HdBEwRm/s6u1KO4cI3rNko5ptpGuCirIkpt2vgjgzoUGXp\nQnmxk4AdF2Lwb9Xx7jBjA/y44Ay0Ew8IYcl+GvSIo+aTsPX1fmdYhmDhehsrLd9m\nEQIDAQAB\n-----END PUBLIC KEY-----\n','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','2025-09-23 11:05:35','2025-09-23 11:05:35');
/*!40000 ALTER TABLE `user_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','staff','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `department_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_department_id_index` (`department_id`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Alice','Johnson','alice.johnson@example.com','$2y$12$aI/oN3TcUFxi.WtF019bZenyXC/eLNbAyuLP9zZiaHczVmgOqSqyC','admin',NULL,'2025-09-23 11:05:09','2025-09-23 11:05:09'),(2,'Bob','Smith','bob.smith@example.com','$2y$12$cj1tPrih2upRL2SmyUOpyejhC9tnMTMk6THqGGv/6N0ySBGOc0/wK','admin',NULL,'2025-09-23 11:05:09','2025-09-23 11:05:09'),(3,'Carol','Davis','carol.davis@example.com','$2y$12$NFiJwV85eVcKTK38U6paEeyy5UCJM83fGvFVkBVF7gKW8r.jrDvkK','staff',1,'2025-09-23 11:05:10','2025-09-23 11:05:10'),(4,'David','Martinez','david.martinez@example.com','$2y$12$InIyxBHptEkvO5XVfmyfZO6/91AjPyE.bKQx.tSd.KUtqXNmsO/Uu','staff',2,'2025-09-23 11:05:10','2025-09-23 11:05:10'),(5,'Eve','Wilson','eve.wilson@example.com','$2y$12$PWcjQ2eGcBt7Lb89.9bo8ekOPiactHcMosS8g8hs2iaLxXZiG7ThS','user',1,'2025-09-23 11:05:10','2025-09-23 11:05:10'),(6,'Frank','Brown','frank.brown@example.com','$2y$12$IVZOiB1CNhQ.e8UacUdDZeI5E8CZfOpN5KCzZueblcobo1vkbctdW','user',3,'2025-09-23 11:05:10','2025-09-23 11:05:10');
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

-- Dump completed on 2025-09-23 14:50:45
