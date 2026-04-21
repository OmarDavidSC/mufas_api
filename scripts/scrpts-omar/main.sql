-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.30 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para db_mufas_app
CREATE DATABASE IF NOT EXISTS `db_mufas_app` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_mufas_app`;

-- Volcando estructura para tabla db_mufas_app.clients
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `dni` int DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `document_number` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text,
  `district` varchar(150) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_clients_location` (`latitude`,`longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla db_mufas_app.clients: ~0 rows (aproximadamente)
DELETE FROM `clients`;

-- Volcando estructura para tabla db_mufas_app.companies
CREATE TABLE IF NOT EXISTS `companies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `host_client` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `mailer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mailer_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mailer_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mailer_host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla db_mufas_app.companies: ~0 rows (aproximadamente)
DELETE FROM `companies`;

-- Volcando estructura para tabla db_mufas_app.fibers
CREATE TABLE IF NOT EXISTS `fibers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `node_id` int unsigned NOT NULL,
  `cable_number` int DEFAULT NULL,
  `status` enum('free','occupied','damaged') DEFAULT 'free',
  `color` varchar(50) DEFAULT NULL,
  `total_fibers` int DEFAULT '24',
  `tube_type` enum('multi','single') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'multi',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `node_id` (`node_id`,`cable_number`),
  KEY `idx_fibers_node` (`node_id`),
  CONSTRAINT `fk_fibers_node` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla db_mufas_app.fibers: ~0 rows (aproximadamente)
DELETE FROM `fibers`;

-- Volcando estructura para tabla db_mufas_app.fiber_assignments
CREATE TABLE IF NOT EXISTS `fiber_assignments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `fiber_thread_id` int unsigned DEFAULT NULL,
  `client_id` int unsigned NOT NULL,
  `assigned_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','released') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_assignments_client` (`client_id`),
  KEY `FK_fiber_assignments_fiber_threads` (`fiber_thread_id`),
  CONSTRAINT `fk_assignment_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_fiber_assignments_fiber_threads` FOREIGN KEY (`fiber_thread_id`) REFERENCES `fiber_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla db_mufas_app.fiber_assignments: ~0 rows (aproximadamente)
DELETE FROM `fiber_assignments`;

-- Volcando estructura para tabla db_mufas_app.fiber_splices
CREATE TABLE IF NOT EXISTS `fiber_splices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `from_thread_id` int unsigned NOT NULL,
  `to_thread_id` int unsigned NOT NULL,
  `splice_type` enum('fusion','mechanical') DEFAULT 'fusion',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_splice_from` (`from_thread_id`),
  KEY `fk_splice_to` (`to_thread_id`),
  CONSTRAINT `fk_splice_from` FOREIGN KEY (`from_thread_id`) REFERENCES `fiber_threads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_splice_to` FOREIGN KEY (`to_thread_id`) REFERENCES `fiber_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla db_mufas_app.fiber_splices: ~0 rows (aproximadamente)
DELETE FROM `fiber_splices`;

-- Volcando estructura para tabla db_mufas_app.fiber_threads
CREATE TABLE IF NOT EXISTS `fiber_threads` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tube_id` int unsigned NOT NULL,
  `thread_number` int NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `status` enum('free','occupied','damaged') DEFAULT 'free',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tube_id` (`tube_id`,`thread_number`),
  CONSTRAINT `fiber_threads_ibfk_1` FOREIGN KEY (`tube_id`) REFERENCES `tubes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla db_mufas_app.fiber_threads: ~0 rows (aproximadamente)
DELETE FROM `fiber_threads`;

-- Volcando estructura para tabla db_mufas_app.nodes
CREATE TABLE IF NOT EXISTS `nodes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `code` varchar(100) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `reference` text,
  `district` varchar(150) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `last_status` enum('up','down') DEFAULT 'down',
  `last_check` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_nodes_location` (`latitude`,`longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla db_mufas_app.nodes: ~0 rows (aproximadamente)
DELETE FROM `nodes`;

-- Volcando estructura para tabla db_mufas_app.node_connections
CREATE TABLE IF NOT EXISTS `node_connections` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `origin_node_id` int unsigned NOT NULL,
  `destination_node_id` int unsigned NOT NULL,
  `distance_meters` decimal(10,2) DEFAULT NULL,
  `distance_km` decimal(10,2) DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_origin_node` (`origin_node_id`),
  KEY `fk_destination_node` (`destination_node_id`),
  CONSTRAINT `fk_destination_node` FOREIGN KEY (`destination_node_id`) REFERENCES `nodes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_origin_node` FOREIGN KEY (`origin_node_id`) REFERENCES `nodes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla db_mufas_app.node_connections: ~0 rows (aproximadamente)
DELETE FROM `node_connections`;

-- Volcando estructura para tabla db_mufas_app.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `permission` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla db_mufas_app.permissions: ~2 rows (aproximadamente)
DELETE FROM `permissions`;
INSERT INTO `permissions` (`id`, `name`, `permission`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(6, 'Admin.', 'administrator', 1, '2025-06-12 17:31:00', '2025-06-12 17:32:31', NULL),
	(7, 'Colab.', 'collaborator', 1, '2025-06-12 17:31:11', '2025-06-12 17:32:43', NULL);

-- Volcando estructura para tabla db_mufas_app.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla db_mufas_app.roles: ~2 rows (aproximadamente)
DELETE FROM `roles`;
INSERT INTO `roles` (`id`, `name`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(5, 'Administrador', 1, '2025-06-12 17:27:11', '2025-06-12 17:27:11', NULL),
	(6, 'invitado', 1, '2025-06-12 17:27:26', '2025-06-12 17:27:26', NULL);

-- Volcando estructura para tabla db_mufas_app.role_permission
CREATE TABLE IF NOT EXISTS `role_permission` (
  `role_id` int unsigned NOT NULL,
  `permission_id` int unsigned NOT NULL,
  `permission` int DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla db_mufas_app.role_permission: ~3 rows (aproximadamente)
DELETE FROM `role_permission`;
INSERT INTO `role_permission` (`role_id`, `permission_id`, `permission`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(5, 6, 1, '2025-06-12 17:35:28', '2025-06-12 17:35:28', NULL),
	(5, 7, 1, '2025-06-12 17:35:40', '2025-06-12 17:35:40', NULL),
	(6, 7, 1, '2025-06-12 17:35:52', '2025-06-12 17:35:52', NULL);

-- Volcando estructura para tabla db_mufas_app.storage_files
CREATE TABLE IF NOT EXISTS `storage_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `type` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `size_b` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `size` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `format` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `embedded` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `folder` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `uri` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `bucket` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `upload_file_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `uploaded_file` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1384 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla db_mufas_app.storage_files: ~5 rows (aproximadamente)
DELETE FROM `storage_files`;
INSERT INTO `storage_files` (`id`, `name`, `path`, `type`, `size_b`, `size`, `format`, `embedded`, `folder`, `uri`, `bucket`, `upload_file_json`, `uploaded_file`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1379, 'IMG_20250604_104113.jpg', '/uploads/profile/175218561368703b0d7578e-img_20250604_104113.jpg', 'image/jpeg', '4602916', '4.39 MB', 'jpg', NULL, NULL, NULL, 'localhost', NULL, NULL, '2025-07-10 17:13:33', '2025-07-10 17:13:33', NULL),
	(1380, 'IMG_20250604_104113.jpg', '/uploads/profile/175218568968703b593715b-img_20250604_104113.jpg', 'image/jpeg', '4602916', '4.39 MB', 'jpg', NULL, NULL, NULL, 'localhost', NULL, NULL, '2025-07-10 17:14:49', '2025-07-10 17:14:49', NULL),
	(1381, 'IMG20241030190157.jpg', '/uploads/profile/175218586968703c0dd2bb0-img20241030190157.jpg', 'image/jpeg', '2156097', '2.06 MB', 'jpg', NULL, NULL, NULL, 'localhost', NULL, NULL, '2025-07-10 17:17:49', '2025-07-10 17:17:49', NULL),
	(1382, 'IMG_20250128_092506.jpg', '/uploads/profile/175218602468703ca8a8293-img_20250128_092506.jpg', 'image/jpeg', '2762133', '2.63 MB', 'jpg', NULL, NULL, NULL, 'localhost', NULL, NULL, '2025-07-10 17:20:24', '2025-07-10 17:20:24', NULL),
	(1383, '305e3cd8-5882-44f9-af31-a41e4e991055.jfif', '/uploads/profile/175218667568703f3350857-305e3cd8-5882-44f9-af31-a41e4e991055.jfif', 'image/jpeg', '94430', '92.22 KB', 'jfif', NULL, NULL, NULL, 'localhost', NULL, NULL, '2025-07-10 17:31:15', '2025-07-10 17:31:15', NULL);

-- Volcando estructura para tabla db_mufas_app.tubes
CREATE TABLE IF NOT EXISTS `tubes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `fiber_id` int unsigned NOT NULL,
  `tube_number` int NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fiber_id` (`fiber_id`,`tube_number`),
  CONSTRAINT `tubes_ibfk_1` FOREIGN KEY (`fiber_id`) REFERENCES `fibers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla db_mufas_app.tubes: ~0 rows (aproximadamente)
DELETE FROM `tubes`;

-- Volcando estructura para tabla db_mufas_app.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `foto_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paternal_surname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `maternal_surname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `users_FK1` (`foto_id`) USING BTREE,
  CONSTRAINT `users_FK1` FOREIGN KEY (`foto_id`) REFERENCES `storage_files` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla db_mufas_app.users: ~2 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `foto_id`, `name`, `paternal_surname`, `maternal_surname`, `username`, `email`, `password`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(38, 1383, 'Brian Arturo', 'Coronado', 'Nizama', 'brian', 'omarsc@gmail.com', '$2y$10$xgmK7Nlc34AR1WmqwVn8teNNBPvw6.9byqeStOT7Ay8PJj.07B1JC', 1, '2025-06-12 17:34:03', '2025-07-10 17:31:15', NULL),
	(39, 1382, 'Nicolas', 'Cotrina', 'Llontop', 'nico', 'stafano@gmail.com', '$2y$10$xgmK7Nlc34AR1WmqwVn8teNNBPvw6.9byqeStOT7Ay8PJj.07B1JC', 1, '2025-06-18 18:08:03', '2025-07-10 17:20:24', NULL);

-- Volcando estructura para tabla db_mufas_app.user_company_role
CREATE TABLE IF NOT EXISTS `user_company_role` (
  `user_id` int unsigned NOT NULL,
  `role_id` int unsigned NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla db_mufas_app.user_company_role: ~2 rows (aproximadamente)
DELETE FROM `user_company_role`;
INSERT INTO `user_company_role` (`user_id`, `role_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(38, 5, '2025-06-14 08:57:52', '2025-06-14 08:57:52', NULL),
	(39, 6, '2025-06-18 18:08:51', '2025-07-08 17:30:27', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
