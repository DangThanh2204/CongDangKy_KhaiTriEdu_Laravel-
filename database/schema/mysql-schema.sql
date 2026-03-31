/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `assistant_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assistant_conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `session_id` varchar(120) NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assistant_conversations_user_id_session_id_index` (`user_id`,`session_id`),
  KEY `assistant_conversations_session_id_index` (`session_id`),
  KEY `assistant_conversations_last_message_at_index` (`last_message_at`),
  CONSTRAINT `assistant_conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assistant_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assistant_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `assistant_conversation_id` bigint(20) unsigned NOT NULL,
  `role` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `recommended_courses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recommended_courses`)),
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assistant_messages_assistant_conversation_id_created_at_index` (`assistant_conversation_id`,`created_at`),
  CONSTRAINT `assistant_messages_assistant_conversation_id_foreign` FOREIGN KEY (`assistant_conversation_id`) REFERENCES `assistant_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `class_change_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_change_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `old_class_id` bigint(20) unsigned DEFAULT NULL,
  `new_class_id` bigint(20) unsigned NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_change_logs_enrollment_id_foreign` (`enrollment_id`),
  KEY `class_change_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `class_change_logs_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_change_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `class_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint(20) unsigned NOT NULL,
  `weekday` varchar(5) NOT NULL COMMENT '2..7 or CN',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_schedules_class_id_index` (`class_id`),
  CONSTRAINT `class_schedules_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `instructor_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  `meeting_info` text DEFAULT NULL,
  `max_students` int(11) NOT NULL DEFAULT 0,
  `price_override` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classes_course_id_foreign` (`course_id`),
  KEY `classes_instructor_id_foreign` (`instructor_id`),
  CONSTRAINT `classes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `classes_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#2c5aa0',
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_categories_slug_unique` (`slug`),
  KEY `course_categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `course_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_certificates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `enrollment_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `certificate_no` varchar(255) NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_certificates_course_id_enrollment_id_unique` (`course_id`,`enrollment_id`),
  UNIQUE KEY `course_certificates_certificate_no_unique` (`certificate_no`),
  KEY `course_certificates_enrollment_id_foreign` (`enrollment_id`),
  KEY `course_certificates_user_id_foreign` (`user_id`),
  CONSTRAINT `course_certificates_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_certificates_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_certificates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `class_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_enrollments_user_id_class_id_unique` (`user_id`,`class_id`),
  KEY `course_enrollments_class_id_foreign` (`class_id`),
  CONSTRAINT `course_enrollments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_material_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_material_progress` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint(20) unsigned NOT NULL,
  `course_material_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `progress_percent` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `last_viewed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `best_quiz_score` decimal(5,2) DEFAULT NULL,
  `quiz_attempts_count` int(10) unsigned NOT NULL DEFAULT 0,
  `passed_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_material_progress_enrollment_id_course_material_id_unique` (`enrollment_id`,`course_material_id`),
  KEY `course_material_progress_course_material_id_foreign` (`course_material_id`),
  KEY `course_material_progress_user_id_completed_at_index` (`user_id`,`completed_at`),
  CONSTRAINT `course_material_progress_course_material_id_foreign` FOREIGN KEY (`course_material_id`) REFERENCES `course_materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_material_progress_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_material_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_material_quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_material_quiz_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint(20) unsigned NOT NULL,
  `course_material_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `attempt_number` int(10) unsigned NOT NULL DEFAULT 1,
  `total_questions` int(10) unsigned NOT NULL DEFAULT 0,
  `correct_answers` int(10) unsigned NOT NULL DEFAULT 0,
  `score_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_passed` tinyint(1) NOT NULL DEFAULT 0,
  `answers_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers_summary`)),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_material_quiz_attempts_enrollment_id_foreign` (`enrollment_id`),
  KEY `course_material_quiz_attempts_user_id_foreign` (`user_id`),
  KEY `course_material_quiz_attempts_course_material_id_user_id_index` (`course_material_id`,`user_id`),
  CONSTRAINT `course_material_quiz_attempts_course_material_id_foreign` FOREIGN KEY (`course_material_id`) REFERENCES `course_materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_material_quiz_attempts_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_material_quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `course_module_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('video','pdf','assignment','quiz','meeting') NOT NULL DEFAULT 'video',
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `estimated_duration_minutes` int(10) unsigned DEFAULT NULL,
  `order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_materials_course_id_foreign` (`course_id`),
  KEY `course_materials_course_module_id_foreign` (`course_module_id`),
  CONSTRAINT `course_materials_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_materials_course_module_id_foreign` FOREIGN KEY (`course_module_id`) REFERENCES `course_modules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_modules_course_id_order_index` (`course_id`,`order`),
  CONSTRAINT `course_modules_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_review_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_review_replies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_review_replies_review_id_foreign` (`review_id`),
  KEY `course_review_replies_user_id_foreign` (`user_id`),
  CONSTRAINT `course_review_replies_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `course_reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_review_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `instructor_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `instructor_rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_reviews_course_id_user_id_unique` (`course_id`,`user_id`),
  KEY `course_reviews_user_id_foreign` (`user_id`),
  KEY `course_reviews_instructor_id_foreign` (`instructor_id`),
  CONSTRAINT `course_reviews_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_reviews_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_videos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned DEFAULT NULL,
  `original_filename` varchar(255) NOT NULL,
  `video_path` varchar(255) NOT NULL,
  `hls_playlist_path` varchar(255) DEFAULT NULL,
  `hls_segments_path` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `video_codec` varchar(255) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `processing_status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `processing_error` text DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_videos_course_id_order_index` (`course_id`,`order`),
  KEY `course_videos_lesson_id_order_index` (`lesson_id`,`order`),
  KEY `course_videos_processing_status_index` (`processing_status`),
  CONSTRAINT `course_videos_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_videos_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `course_materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `short_description` text NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `level` enum('beginner','intermediate','advanced','all') NOT NULL DEFAULT 'beginner',
  `duration` int(11) NOT NULL DEFAULT 0,
  `lessons_count` int(11) NOT NULL DEFAULT 0,
  `students_count` int(11) NOT NULL DEFAULT 0,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `total_rating` int(11) NOT NULL DEFAULT 0,
  `category_id` bigint(20) unsigned NOT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `learning_type` enum('online','offline','hybrid') NOT NULL DEFAULT 'online',
  `announcement` text DEFAULT NULL,
  `has_default_quiz` tinyint(1) NOT NULL DEFAULT 0,
  `default_quiz_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`default_quiz_data`)),
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_popular` tinyint(1) NOT NULL DEFAULT 0,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `series_key` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_slug_unique` (`slug`),
  KEY `courses_category_id_foreign` (`category_id`),
  KEY `courses_series_key_index` (`series_key`),
  CONSTRAINT `courses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `class_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_reference_unique` (`reference`),
  KEY `payments_status_index` (`status`),
  KEY `payments_user_id_index` (`user_id`),
  KEY `payments_class_id_index` (`class_id`),
  CONSTRAINT `payments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#2c5aa0',
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `view_count` int(11) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug_unique` (`slug`),
  KEY `posts_author_id_foreign` (`author_id`),
  KEY `posts_status_index` (`status`),
  KEY `posts_published_at_index` (`published_at`),
  KEY `posts_category_id_index` (`category_id`),
  CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  CONSTRAINT `posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `post_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `user_answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`user_answer`)),
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_answers_question_id_foreign` (`question_id`),
  KEY `quiz_answers_attempt_id_question_id_index` (`attempt_id`,`question_id`),
  CONSTRAINT `quiz_answers_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `attempt_number` int(11) NOT NULL DEFAULT 1,
  `total_questions` int(11) NOT NULL,
  `correct_answers` int(11) NOT NULL DEFAULT 0,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `earned_points` int(11) NOT NULL DEFAULT 0,
  `percentage_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` enum('in_progress','completed','timed_out') NOT NULL DEFAULT 'in_progress',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `time_taken` int(11) DEFAULT NULL,
  `answers_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers_summary`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_attempts_user_id_quiz_id_index` (`user_id`,`quiz_id`),
  KEY `quiz_attempts_quiz_id_status_index` (`quiz_id`,`status`),
  KEY `quiz_attempts_status_index` (`status`),
  CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quiz_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quiz_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer','essay') NOT NULL DEFAULT 'multiple_choice',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`correct_answers`)),
  `explanation` text DEFAULT NULL,
  `points` int(11) NOT NULL DEFAULT 1,
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_questions_quiz_id_order_index` (`quiz_id`,`order`),
  KEY `quiz_questions_question_type_index` (`question_type`),
  CONSTRAINT `quiz_questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quizzes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quizzes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('pre_test','post_test','practice','exam') NOT NULL DEFAULT 'practice',
  `time_limit` int(11) DEFAULT NULL,
  `passing_score` int(11) NOT NULL DEFAULT 70,
  `max_attempts` int(11) NOT NULL DEFAULT 3,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `shuffle_questions` tinyint(1) NOT NULL DEFAULT 0,
  `show_results` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quizzes_lesson_id_foreign` (`lesson_id`),
  KEY `quizzes_course_id_type_index` (`course_id`,`type`),
  KEY `quizzes_is_active_index` (`is_active`),
  CONSTRAINT `quizzes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quizzes_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `course_materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_logs_category_action_index` (`category`,`action`),
  KEY `system_logs_user_id_index` (`user_id`),
  KEY `system_logs_category_index` (`category`),
  KEY `system_logs_action_index` (`action`),
  KEY `system_logs_reference_index` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `provider_id` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff','student','instructor') NOT NULL DEFAULT 'student',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `total_rating` int(11) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  UNIQUE KEY `users_facebook_id_unique` (`facebook_id`),
  KEY `users_role_index` (`role`),
  KEY `users_is_verified_index` (`is_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallet_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wallet_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('deposit','purchase','refund') NOT NULL DEFAULT 'deposit',
  `amount` decimal(14,2) NOT NULL,
  `status` enum('pending','completed','failed','expired') NOT NULL DEFAULT 'pending',
  `reference` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_transactions_wallet_id_foreign` (`wallet_id`),
  KEY `wallet_transactions_course_id_foreign` (`course_id`),
  CONSTRAINT `wallet_transactions_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wallet_transactions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `firefly_identity` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallets_firefly_identity_unique` (`firefly_identity`),
  KEY `wallets_user_id_foreign` (`user_id`),
  CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2025_03_06_000000_create_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2025_11_08_175856_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_11_08_181315_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_11_14_050154_create_post_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_11_14_050158_create_posts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_11_20_124941_create_course_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_11_20_124942_create_courses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_11_20_160000_create_classes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_11_20_163739_create_course_enrollments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_03_11_000000_add_oauth_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_03_13_000000_create_wallets_and_transactions_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_03_16_000000_add_instructor_video_pdfSeries_to_courses_and_create_course_materials_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_03_16_000001_create_course_reviews_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_03_16_000002_add_rating_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_03_16_000003_create_course_review_replies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_03_16_081529_add_course_features_to_courses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_03_16_090000_create_payments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_03_16_132923_create_quizzes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_03_16_133153_create_quiz_questions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_03_16_133623_create_quiz_attempts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_03_16_133633_create_quiz_answers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_03_16_133641_create_course_videos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_03_16_164740_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_03_21_000001_create_class_change_logs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_03_21_000002_create_class_schedules_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_03_21_000003_change_payments_method_to_string',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_03_21_120000_create_system_logs_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_03_25_120000_create_course_material_progress_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_03_25_120100_create_course_material_quiz_attempts_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_03_25_120200_create_course_certificates_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_03_25_150000_create_assistant_conversations_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_03_25_150100_create_assistant_messages_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_03_26_090000_create_course_modules_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_03_26_090100_add_course_module_id_to_course_materials_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_03_27_100000_add_estimated_duration_minutes_to_course_materials_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_03_27_120000_add_enrollment_workflow_fields_to_course_enrollments_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_03_28_090000_add_meeting_type_to_course_materials_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_03_28_140000_add_expiration_to_wallet_transactions_table',8);
