-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 28, 2025 at 08:17 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `khaitriedu`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `instructor_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_popular` tinyint(1) NOT NULL DEFAULT 0,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `slug`, `description`, `short_description`, `price`, `sale_price`, `thumbnail`, `banner_image`, `level`, `duration`, `lessons_count`, `students_count`, `rating`, `total_rating`, `instructor_id`, `category_id`, `status`, `is_featured`, `is_popular`, `meta`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Lập trình C# cơ bản', 'lap-trinh-csharp-co-ban', 'Học lập trình C# từ cơ bản', 'C# căn bản', 500000.00, NULL, 'courses/thumbnails/3iSrXHMDIPAMTk0r8AcJOjJ7jnkAKtBeRDopmzpi.jpg', 'courses/banners/ckXCnC1jH3TSiq0yEtkJeSrFhAmSZiwIsqUBH7yf.jpg', 'beginner', 100, 20, 51, 4.5, 10, 2, 1, 'published', 1, 1, '{\"tags\":[\"c#\",\"programming\"]}', '2025-11-24 14:05:38', '2025-11-24 10:01:56', NULL),
(2, 'React 18 từ cơ bản đến nâng cao', 'react-18-full', 'Học React 18 chi tiết', 'React 18 nâng cao', 700000.00, 650000.00, 'courses/thumbnails/LF7omE1lk17MQE9nIyLFFdWpUwFNILZHW4eImjNZ.png', 'courses/banners/p1NpeGG7oyBhPDb2j5yZXwfoPkq7KzQeLf0scQUn.png', 'all', 80, 25, 60, 4.8, 15, 2, 7, 'published', 1, 1, '{\"tags\":[\"react\",\"javascript\"]}', '2025-11-24 14:05:38', '2025-11-24 09:30:37', NULL),
(3, 'Thiết kế UI/UX với Figma', 'ui-ux-figma', 'Học thiết kế UI/UX', 'Figma cơ bản', 600000.00, NULL, 'courses/thumbnails/2Yeg6WI2PkA40nUgZcGXDCwQah4pA2IvxEoZjPVe.png', 'courses/banners/Ez9nsAh6COkGrtEWajinx6wzKrWmRLmszpw4wHND.jpg', 'beginner', 60, 18, 40, 4.2, 8, 3, 8, 'published', 1, 0, '{\"tags\":[\"figma\",\"design\"]}', '2025-11-24 14:05:38', '2025-11-24 09:31:02', NULL),
(4, 'Python nâng cao', 'python-nang-cao', 'Học Python nâng cao', 'Python nâng cao', 800000.00, 750000.00, 'courses/thumbnails/QCGKxUb4XJKYhTUiXrGh7DmUOTLrVlA1WtFA9J6P.jpg', 'courses/banners/mzbezgSMkzfAsiSIOfxw69J0Y3WIEGIMLqOzaQgp.jpg', 'advanced', 120, 30, 71, 4.9, 20, 3, 6, 'published', 1, 1, '{\"tags\":[\"python\"]}', '2025-11-24 14:05:38', '2025-11-25 00:28:03', NULL),
(5, 'Marketing online 2025', 'marketing-online-2025', 'Học Marketing Online mới nhất', 'Marketing cơ bản', 400000.00, NULL, 'courses/thumbnails/1gDnRrydWMA8IrsRJpceSEUKnWDJXk4jDLHnTbBZ.jpg', 'courses/banners/pfCOTln1fqZtm5kzevSMLfTVqfHv34Tw7UOIJCTo.jpg', 'intermediate', 90, 22, 35, 4.1, 7, 2, 3, 'published', 0, 1, '{\"tags\":[\"marketing\"]}', '2025-11-24 14:05:38', '2025-11-24 09:32:07', NULL),
(6, 'Kinh doanh hiệu quả', 'kinh-doanh-hieu-qua', 'Khóa học kinh doanh', 'Kinh doanh cơ bản', 450000.00, NULL, 'courses/thumbnails/9agDn6BugkQGRP48MBVqySwVQ7iRDAnrHcwGag15.png', 'courses/banners/zAtroKC70TDlUITI6sVnlahJwiAoYD6Mw9ABi5kO.jpg', 'beginner', 80, 15, 28, 4.0, 5, 2, 4, 'published', 0, 0, '{\"tags\":[\"business\"]}', '2025-11-24 14:05:38', '2025-11-24 09:32:32', NULL),
(7, 'AI & Machine Learning cơ bản', 'ai-ml-co-ban', 'Khóa học AI & ML', 'AI cơ bản', 900000.00, 850000.00, 'courses/thumbnails/Jo4P9NWsyD8HpA6R01hlUPgioGLb9UemHyYSeC35.jpg', 'courses/banners/GPzqP5w3M3KChiiKtzXbDsPWJa8fFz1U9CC77A38.jpg', 'beginner', 110, 25, 50, 4.6, 12, 3, 5, 'published', 1, 1, '{\"tags\":[\"AI\",\"ML\"]}', '2025-11-24 14:05:38', '2025-11-24 09:32:57', NULL),
(8, 'JavaScript nâng cao', 'javascript-nang-cao', 'Khóa học JS nâng cao', 'JS nâng cao', 700000.00, 680000.00, 'courses/thumbnails/jHk3O2PDM3Hww8DCSUIAAIVOKPkq8Iz5DYLirR9r.jpg', 'courses/banners/2g8fRo34Q6wyT8kNmFv93FmBFsHeUOnK5cqbsqff.png', 'advanced', 90, 28, 60, 4.7, 14, 2, 7, 'published', 1, 1, '{\"tags\":[\"javascript\"]}', '2025-11-24 14:05:38', '2025-11-24 09:33:27', NULL),
(9, 'WordPress từ A-Z', 'wordpress-full', 'Khóa học WP', 'WP cơ bản', 500000.00, 450000.00, 'courses/thumbnails/2iXszLM2px8BOuRKCUQhykdCU3LjcIws2iSIwsmW.png', 'courses/banners/6v1VzMX1lCpZg29R3qwxGSebZSzjwi5VHyFEjYjg.jpg', 'all', 60, 20, 40, 4.3, 9, 3, 9, 'published', 1, 0, '{\"tags\":[\"wordpress\"]}', '2025-11-24 14:05:38', '2025-11-24 09:33:52', NULL),
(10, 'SEO chuyên sâu', 'seo-chuyen-sau', 'Khóa học SEO nâng cao', 'SEO nâng cao', 600000.00, NULL, 'courses/thumbnails/CYSdAwHKyvx9N3sBlP1o8VuACh2ZCEyg4nu2iTiK.jpg', 'courses/banners/AXBloL5nCo4EvGD5vgECCzVvG4713K5KSdQsbzHR.jpg', 'advanced', 70, 18, 35, 4.4, 10, 3, 10, 'published', 0, 1, '{\"tags\":[\"seo\"]}', '2025-11-24 14:05:38', '2025-11-24 09:34:16', NULL),
(11, 'đbd', 'dbd', 'bedbe', 'dbdsb', 1000000.00, NULL, 'courses/thumbnails/8lMBqnbKmAW6ZcifxuPXPqmMQZ0WtVXcdWwsqEUy.png', NULL, 'beginner', 1000, 0, 0, 0.0, 0, 3, 2, 'published', 0, 0, NULL, '2025-11-24 10:04:24', '2025-11-24 10:29:24', '2025-11-24 10:29:24'),
(12, 'Hsduvud', 'hsduvud', 'sdigbhsighisg', 'uhivhdsiogvsodi', 1000000.00, NULL, 'courses/thumbnails/2feZVUu74uWg6uXTnyPQvYbQBsreBzJGNSTJCLIv.jpg', 'courses/banners/9unlC7xrwIhlm5T5H4MevoOgLwC0aPN1rYNxrryL.png', 'beginner', 100, 0, 0, 0.0, 0, 2, 1, 'published', 0, 0, NULL, '2025-11-25 00:30:00', '2025-11-25 00:30:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_categories`
--

CREATE TABLE `course_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#2c5aa0',
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_categories`
--

INSERT INTO `course_categories` (`id`, `name`, `slug`, `description`, `parent_id`, `icon`, `color`, `order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Lập trình', 'lap-trinh', 'Các khóa học lập trình', NULL, 'fas fa-laptop-code', '#2c5aa0', 0, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(2, 'Thiết kế', 'thiet-ke', 'Các khóa học thiết kế', NULL, 'fas fa-pencil-ruler', '#ff6600', 1, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(3, 'Marketing', 'marketing', 'Khóa học marketing', NULL, 'fas fa-bullhorn', '#fbbc05', 2, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(4, 'Kinh doanh', 'kinh-doanh', 'Khóa học kinh doanh', NULL, 'fas fa-briefcase', '#34a853', 3, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(5, 'AI & ML', 'ai-ml', 'Khóa học AI & ML', NULL, 'fas fa-robot', '#e91e63', 4, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(6, 'Python', 'python', 'Khóa học Python', 1, 'fas fa-python', '#607d8b', 5, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(7, 'JavaScript', 'javascript', 'Khóa học JavaScript', 1, 'fas fa-js', '#9c27b0', 6, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(8, 'UI/UX', 'ui-ux', 'Khóa học UI/UX', 2, 'fas fa-pencil-alt', '#ff5722', 7, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(9, 'WordPress', 'wordpress', 'Khóa học WordPress', 2, 'fas fa-wordpress', '#3f51b5', 8, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(10, 'SEO', 'seo', 'Khóa học SEO', 3, 'fas fa-search', '#795548', 9, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38');

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `requires_approval` tinyint(1) NOT NULL DEFAULT 1,
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_enrollments`
--

INSERT INTO `course_enrollments` (`id`, `user_id`, `course_id`, `status`, `requires_approval`, `enrolled_at`, `completed_at`, `notes`, `approved_at`, `rejected_at`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 'approved', 1, '2025-11-24 14:05:38', NULL, NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(2, 6, 2, 'approved', 1, '2025-11-24 14:05:38', NULL, NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(3, 7, 3, 'approved', 1, '2025-11-24 14:05:38', NULL, NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(4, 8, 4, 'pending', 1, '2025-11-24 14:05:38', NULL, NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(5, 9, 5, 'approved', 1, '2025-11-24 14:05:38', NULL, NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(6, 10, 6, 'pending', 1, '2025-11-24 14:05:38', NULL, NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(7, 5, 7, 'approved', 1, '2025-11-24 14:05:38', NULL, NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(8, 6, 8, 'approved', 1, '2025-11-24 14:05:38', NULL, NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(9, 7, 9, 'completed', 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38', NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(10, 8, 10, 'approved', 1, '2025-11-24 14:05:38', NULL, NULL, NULL, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(11, 11, 1, 'approved', 0, '2025-11-24 10:01:56', NULL, 'Đã đóng tiền', '2025-11-24 10:01:56', NULL, '2025-11-24 10:01:56', '2025-11-24 10:01:56'),
(12, 12, 4, 'approved', 1, '2025-11-25 00:28:03', NULL, NULL, '2025-11-25 00:28:03', NULL, '2025-11-25 00:27:02', '2025-11-25 00:28:03'),
(13, 1, 12, 'pending', 1, '2025-11-25 08:50:21', NULL, NULL, NULL, NULL, '2025-11-25 08:50:21', '2025-11-25 08:50:21');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(17, '2025_11_08_175856_create_users_table', 1),
(18, '2025_11_08_181315_create_sessions_table', 1),
(19, '2025_11_14_050154_create_post_categories_table', 1),
(20, '2025_11_14_050158_create_posts_table', 1),
(21, '2025_11_20_124941_create_course_categories_table', 1),
(22, '2025_11_20_124942_create_courses_table', 1),
(23, '2025_11_20_163739_create_course_enrollments_table', 1),
(24, '2025_11_20_171744_add_requires_approval_to_course_enrollments_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `view_count` int(11) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author_id`, `category_id`, `status`, `view_count`, `published_at`, `meta`, `created_at`, `updated_at`, `deleted_at`) VALUES
(13, 'Tin tức công nghệ mới', 'tin-tuc-cong-nghe-moi', 'Cập nhật tin tức công nghệ mới nhất', 'Trong thế giới công nghệ, mọi thứ thay đổi liên tục. Bài viết này tổng hợp những tin tức nổi bật trong tuần, bao gồm các sản phẩm mới ra mắt, cập nhật phần mềm và xu hướng phát triển trong ngành.', 'posts/iFeRi0IFaXMbuFDDB51t6XpvnBbFyukRAxLEMnAK.jpg', 1, 1, 'published', 1, '2025-11-24 07:55:59', '{\"title\":null,\"description\":null,\"is_featured\":false}', '2025-11-24 07:55:59', '2025-11-24 09:40:27', NULL),
(14, 'React 18: Những tính năng mới', 'react-18-nhung-tinh-nang-moi', 'Khám phá các tính năng mới của React 18', 'React 18 mang đến nhiều cải tiến quan trọng như Concurrent Mode, Suspense Updates, và Automatic Batching. Bài viết hướng dẫn cách áp dụng các tính năng này vào dự án thực tế, giúp tăng hiệu năng và trải nghiệm người dùng.', 'posts/fHokBbLU6zcY7gWyKqKqPhWFaSX0Yu40ybW1Zz0p.jpg', 1, 2, 'published', 0, '2025-11-24 09:29:05', '{\"title\":null,\"description\":null,\"is_featured\":false}', '2025-11-24 09:29:05', '2025-11-24 09:29:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `post_categories`
--

CREATE TABLE `post_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#2c5aa0',
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_categories`
--

INSERT INTO `post_categories` (`id`, `name`, `slug`, `description`, `color`, `order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Tin tức', 'tin-tuc', 'Các bài viết tin tức', '#23a112', 0, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(2, 'Công nghệ', 'cong-nghe', 'Bài viết về công nghệ', '#ff6600', 1, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(3, 'Giáo dục', 'giao-duc', 'Bài viết về giáo dục', '#1a73e8', 2, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(4, 'Khuyến mãi', 'khuyen-mai', 'Thông tin khuyến mãi', '#e91e63', 3, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(5, 'Sự kiện', 'su-kien', 'Các sự kiện nổi bật', '#fbbc05', 4, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(6, 'Thủ thuật', 'thu-thuat', 'Tips & tricks', '#34a853', 5, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(7, 'Video', 'video', 'Bài viết dạng video', '#ff5722', 6, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(8, 'Review', 'review', 'Đánh giá sản phẩm', '#9c27b0', 7, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(9, 'Phỏng vấn', 'phong-van', 'Bài phỏng vấn', '#607d8b', 8, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(10, 'Blog', 'blog', 'Bài viết blog', '#3f51b5', 9, 1, '2025-11-24 14:05:38', '2025-11-24 14:05:38');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('V0UM1fbjKJvFXifNH42VXhjcpEhfzbrQjUDf96MK', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZmF1QnNyWGduNjNUekRMS3Z0MXp1WFowNkRaWXNuMU1TVlZGbmxPNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Qva2hhaS10cmktZWR1L3B1YmxpYyI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1764087081),
('wGxdUSKszgiTulU7s0Zf1EdAhKRHFAMRLrNYPKY4', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoia0d3bklGcjdpZVptNzgwanJ3SzBGcUpuM2NWWENNdWdKQzBlaU1OViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly9sb2NhbGhvc3Qva2hhaS10cmktZWR1L3B1YmxpYy9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MTc6InN0dWRlbnQuZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NTt9', 1764086440);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff','student','instructor') NOT NULL DEFAULT 'student',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `email`, `password`, `avatar`, `otp`, `role`, `is_verified`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Đặng Hoàng Thành', 'admin1@example.com', '$2y$12$24mDO38NS1vTOIg6yrM1QONmyzejPpybIMu.TjgICkT7V024JsA2u', NULL, NULL, 'admin', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(2, 'instructor1', 'Nguyễn Văn A', 'instructor1@example.com', '$2y$12$24mDO38NS1vTOIg6yrM1QONmyzejPpybIMu.TjgICkT7V024JsA2u', NULL, NULL, 'instructor', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(3, 'instructor2', 'Trần Thị B', 'instructor2@example.com', '$2y$12$24mDO38NS1vTOIg6yrM1QONmyzejPpybIMu.TjgICkT7V024JsA2u\r\n', NULL, NULL, 'instructor', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(4, 'instructor3', 'Lê Văn C', 'instructor3@example.com', '$2y$12$24mDO38NS1vTOIg6yrM1QONmyzejPpybIMu.TjgICkT7V024JsA2u', NULL, NULL, 'instructor', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(5, 'student1', 'Phạm Minh D', 'student1@example.com', '$2y$12$24mDO38NS1vTOIg6yrM1QONmyzejPpybIMu.TjgICkT7V024JsA2u', NULL, NULL, 'student', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(6, 'student2', 'Nguyễn Thị E', 'student2@example.com', '$2y$12$24mDO38NS1vTOIg6yrM1QONmyzejPpybIMu.TjgICkT7V024JsA2u', NULL, NULL, 'student', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(7, 'student3', 'Lê Quang F', 'student3@example.com', '$2y$12$hashedpass7', NULL, NULL, 'student', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(8, 'student4', 'Phan Thị G', 'student4@example.com', '$2y$12$hashedpass8', NULL, NULL, 'student', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(9, 'student5', 'Trương Văn H', 'student5@example.com', '$2y$12$hashedpass9', NULL, NULL, 'student', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(10, 'student6', 'Ngô Thị I', 'student6@example.com', '$2y$12$hashedpass10', NULL, NULL, 'student', 1, NULL, '2025-11-24 14:05:38', '2025-11-24 14:05:38'),
(11, 'dangthanh', 'Đặng Hoàng Thành', 'dhthanh2004@gmail.com', '$2y$12$tBGE6bOJg/zIhMGl/QQC/OaHZN/153J1uvm6qJkxUMLx0q8kIWH5q', NULL, NULL, 'student', 1, NULL, '2025-11-24 10:01:56', '2025-11-24 10:01:56'),
(12, 'proden', 'Đặng Thành', 'Thanh_dth225765@student.agu.edu.vn', '$2y$12$um05kOKVTp5rIOgBJMjGZ.5eHi.du20XyxD5xSl.9UHQH4YRsJJLu', NULL, NULL, 'student', 1, NULL, '2025-11-25 00:23:47', '2025-11-25 00:24:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `courses_slug_unique` (`slug`),
  ADD KEY `courses_instructor_id_foreign` (`instructor_id`),
  ADD KEY `courses_category_id_foreign` (`category_id`);

--
-- Indexes for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_categories_slug_unique` (`slug`),
  ADD KEY `course_categories_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_enrollments_user_id_course_id_unique` (`user_id`,`course_id`),
  ADD KEY `course_enrollments_course_id_foreign` (`course_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `posts_slug_unique` (`slug`),
  ADD KEY `posts_author_id_foreign` (`author_id`),
  ADD KEY `posts_status_index` (`status`),
  ADD KEY `posts_published_at_index` (`published_at`),
  ADD KEY `posts_category_id_index` (`category_id`);

--
-- Indexes for table `post_categories`
--
ALTER TABLE `post_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_categories_slug_unique` (`slug`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_is_verified_index` (`is_verified`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `course_categories`
--
ALTER TABLE `course_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `post_categories`
--
ALTER TABLE `post_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD CONSTRAINT `course_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `post_categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
