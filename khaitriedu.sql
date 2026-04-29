-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2026 at 01:36 AM
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
-- Table structure for table `assistant_conversations`
--

CREATE TABLE `assistant_conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(120) NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assistant_conversations`
--

INSERT INTO `assistant_conversations` (`id`, `user_id`, `session_id`, `started_at`, `last_message_at`, `meta`, `created_at`, `updated_at`) VALUES
(1, 1, 'I2leaoS5DutaujgebjjkOfCF7KzGbfPICWsS6sqC', '2026-03-24 22:18:47', '2026-03-24 23:26:57', NULL, '2026-03-24 22:18:47', '2026-03-24 23:26:57'),
(2, NULL, 'hRB29lIhUqnD2AYHgHOnkFwHVLVlM8DiTcdpwptX', '2026-03-25 00:02:56', '2026-03-25 00:02:57', NULL, '2026-03-25 00:02:56', '2026-03-25 00:02:57'),
(3, 1, '6If01aqK5jr8Pfz8FyUuEkadZKfhH1WHaSg4V0Sg', '2026-03-26 07:19:39', '2026-03-26 14:16:49', NULL, '2026-03-26 07:19:39', '2026-03-26 14:16:49'),
(4, 5, 'JASXCaDFhVkncle7SK43NucrpQmtFS4N2VIlRUNv', '2026-03-26 14:37:39', '2026-03-26 14:38:22', NULL, '2026-03-26 14:37:39', '2026-03-26 14:38:22'),
(5, 1, 'zRIiFKk2Esn2qUqjtRYDJzbk85csr3cl47TXF8XQ', '2026-03-30 07:52:55', '2026-03-30 07:52:57', NULL, '2026-03-30 07:52:55', '2026-03-30 07:52:57');

-- --------------------------------------------------------

--
-- Table structure for table `assistant_messages`
--

CREATE TABLE `assistant_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `assistant_conversation_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `recommended_courses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recommended_courses`)),
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assistant_messages`
--

INSERT INTO `assistant_messages` (`id`, `assistant_conversation_id`, `role`, `message`, `recommended_courses`, `meta`, `created_at`, `updated_at`) VALUES
(1, 1, 'user', 'Tôi muốn đăng ký học', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-24 22:18:47', '2026-03-24 22:18:47'),
(2, 1, 'assistant', 'Tro ly AI tam thoi gap loi ket noi, nen minh dang goi y nhanh theo du lieu san co tren website.\n\nBan co the tham khao:\n1. Lập trình java (Lập trình, 2.000 VND)\n2. Lập trình C# (Lập trình, 10.000 VND)\n\nBan co the noi ro hon ban muon hoc mon gi, muc tieu la di lam, thi chung chi hay hoc co ban de minh goi y dung hon.', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":100,\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"phu hop voi nhu cau hien tai\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":100,\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"phu hop voi nhu cau hien tai\"}]', '{\"source\":\"fallback_local\",\"status\":429}', '2026-03-24 22:18:49', '2026-03-24 22:18:49'),
(3, 1, 'user', 'Hé lô', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-24 22:38:30', '2026-03-24 22:38:30'),
(4, 1, 'assistant', 'Trợ lý AI tạm thời gặp lỗi kết nối, nên mình đang gợi ý nhanh theo dữ liệu sẵn có trên website.\n\nBạn có thể tham khảo:\n1. Lập trình java (Lập trình, 2.000 VND)\n2. Lập trình C# (Lập trình, 10.000 VND)\n\nBạn có thể nói rõ hơn bạn muốn học môn gì, mục tiêu là đi làm, thi chứng chỉ hay học cơ bản để mình gợi ý đúng hơn.', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":100,\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":100,\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"}]', '{\"source\":\"fallback_local\",\"status\":429}', '2026-03-24 22:38:31', '2026-03-24 22:38:31'),
(5, 1, 'user', 'hé lô', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-24 22:46:43', '2026-03-24 22:46:43'),
(6, 1, 'assistant', 'Chào bạn, Khai Tri Edu rất vui được hỗ trợ bạn!\n\nBạn có thể tham khảo các khóa học Lập trình hiện có:\n*   Lập trình Java (offline, 2.000 VND, trình độ intermediate)\n*   Lập trình C# (online, 10.000 VND, trình độ beginner)\n\nBạn quan tâm đến khóa học nào hay có mục tiêu học tập cụ thể không ạ?', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":100,\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":100,\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"}]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-24 22:46:45', '2026-03-24 22:46:45'),
(7, 1, 'user', 'Bạn có thể hướng dẫn tui đăng nhập được không', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-24 22:47:07', '2026-03-24 22:47:07'),
(8, 1, 'assistant', 'Chào bạn, rất tiếc Khai Tri Edu chưa có thông tin về chức năng đăng nhập trên website.\n\nĐể được hỗ trợ tốt nhất, bạn vui lòng liên hệ trực tiếp bộ phận tư vấn của Khai Tri Edu nhé.', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":100,\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":100,\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"}]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-24 22:47:08', '2026-03-24 22:47:08'),
(9, 1, 'user', 'Hiện tại bạn hướng dẫn tui đăng ký tài khoản được không', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-24 23:26:55', '2026-03-24 23:26:55'),
(10, 1, 'assistant', 'Chào bạn, Khai Tri Edu rất tiếc chưa có thông tin hướng dẫn đăng ký tài khoản trên website.\n\nĐể được hỗ trợ nhanh nhất, bạn vui lòng liên hệ trực tiếp bộ phận tư vấn của Khai Tri Edu nhé.', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":100,\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":100,\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"}]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-24 23:26:57', '2026-03-24 23:26:57'),
(11, 2, 'user', 'Tôi muốn biết học phí', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-25 00:02:56', '2026-03-25 00:02:56'),
(12, 2, 'assistant', 'Chào bạn, Khai Tri Edu có các khóa học Lập trình Java và Lập trình C# với các mức học phí khác nhau.\n\n*   **Lập trình Java:** 2.000 VND\n*   **Lập trình C#:** 10.000 VND\n\nBạn quan tâm đến khóa học nào ạ?', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":100,\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":100,\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"}]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-25 00:02:57', '2026-03-25 00:02:57'),
(13, 3, 'user', 'hé lô', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-26 07:19:39', '2026-03-26 07:19:39'),
(14, 3, 'assistant', 'Chào bạn, Khai Tri Edu có các khóa học Lập trình. Bạn quan tâm đến khóa học nào ạ?\n\nHiện tại, mình có thông tin về:\n*   **Lập trình Java:** Học offline, giá 2.000 VND, trình độ intermediate.\n*   **Lập trình C#:** Học online, giá 10.000 VND, trình độ beginner.', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":100,\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":100,\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"}]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-26 07:19:41', '2026-03-26 07:19:41'),
(15, 3, 'user', 'đụ má', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-26 07:19:53', '2026-03-26 07:19:53'),
(16, 3, 'assistant', 'Xin lỗi, mình không hiểu câu hỏi của bạn. Bạn có thể nói rõ hơn được không?', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":100,\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":100,\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"}]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-26 07:19:54', '2026-03-26 07:19:54'),
(17, 3, 'user', 'hướng dẫn đăng ký tài khoản', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-26 07:21:04', '2026-03-26 07:21:04'),
(18, 3, 'assistant', 'Chào bạn, Khai Tri Edu là nền tảng học tập trực tuyến. Hiện tại, mình chưa có thông tin về cách đăng ký tài khoản trên trang web.\n\nBạn vui lòng liên hệ bộ phận tư vấn để được hỗ trợ tốt nhất nhé.', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":100,\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":100,\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"}]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-26 07:21:10', '2026-03-26 07:21:10'),
(19, 3, 'user', 'hướng dẫn đăng ký tài khoản đi', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang Ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-26 14:16:48', '2026-03-26 14:16:48'),
(20, 3, 'assistant', 'Chào bạn, Khai Tri Edu là nền tảng học tập trực tuyến. Hiện tại, mình chưa có thông tin về cách đăng ký tài khoản trên trang web.\n\nBạn vui lòng liên hệ bộ phận tư vấn để được hỗ trợ tốt nhất nhé.', '[]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-26 14:16:49', '2026-03-26 14:16:49'),
(21, 4, 'user', 'tôi nạp tiền thêm vào ví saon', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/profile\\/edit\",\"page_title\":\"Ch\\u1ec9nh s\\u1eeda th\\u00f4ng tin c\\u00e1 nh\\u00e2n - Khai Tri Edu\"}', '2026-03-26 14:37:39', '2026-03-26 14:37:39'),
(22, 4, 'assistant', 'Chào bạn, để nạp tiền vào ví, bạn vui lòng thực hiện các bước sau:\n1. Truy cập vào mục \"Ví\" trên trang web.\n2. Chọn tùy chọn \"Nạp tiền\".\n3. Làm theo hướng dẫn để hoàn tất giao dịch.\n\nNếu gặp khó khăn, bạn có thể liên hệ bộ phận hỗ trợ để được giúp đỡ nhé.', '[]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-26 14:37:40', '2026-03-26 14:37:40'),
(23, 4, 'user', 'trung tâm học ở đâu vậy', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/profile\\/edit\",\"page_title\":\"Ch\\u1ec9nh s\\u1eeda th\\u00f4ng tin c\\u00e1 nh\\u00e2n - Khai Tri Edu\"}', '2026-03-26 14:37:59', '2026-03-26 14:37:59'),
(24, 4, 'assistant', 'Chào bạn, Khai Tri Edu là nền tảng học tập trực tuyến nên chúng mình không có địa điểm học tập cố định ạ.', '[]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-26 14:38:00', '2026-03-26 14:38:00'),
(25, 4, 'user', 'tui mua khóa học xong hủy thì có được hoàn tiền không', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/profile\\/edit\",\"page_title\":\"Ch\\u1ec9nh s\\u1eeda th\\u00f4ng tin c\\u00e1 nh\\u00e2n - Khai Tri Edu\"}', '2026-03-26 14:38:21', '2026-03-26 14:38:21'),
(26, 4, 'assistant', 'Chào bạn, về chính sách hoàn tiền khi hủy khóa học, bạn vui lòng liên hệ bộ phận hỗ trợ của Khai Tri Edu để được tư vấn chi tiết nhé.', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":\"1 gi\\u1edd 40 ph\\u00fat\",\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":\"1 gi\\u1edd 40 ph\\u00fat\",\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00f9 h\\u1ee3p v\\u1edbi nhu c\\u1ea7u hi\\u1ec7n t\\u1ea1i\"}]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-26 14:38:22', '2026-03-26 14:38:22'),
(27, 5, 'user', 'Tôi muốn tư vấn khóa học', NULL, '{\"current_url\":\"http:\\/\\/localhost:8000\\/\",\"page_title\":\"Trang ch\\u1ee7 - Khai Tri Edu\"}', '2026-03-30 07:52:55', '2026-03-30 07:52:55'),
(28, 5, 'assistant', 'Chào bạn, bạn quan tâm đến lĩnh vực lập trình phải không ạ?', '[{\"id\":2,\"title\":\"L\\u1eadp tr\\u00ecnh java\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"offline\",\"level\":\"intermediate\",\"duration\":\"1 gi\\u1edd 40 ph\\u00fat\",\"price_label\":\"2.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/2\",\"reason\":\"ph\\u00c3\\u00b9 h\\u00e1\\u00bb\\u00a3p v\\u00e1\\u00bb\\u203ai nhu c\\u00e1\\u00ba\\u00a7u hi\\u00e1\\u00bb\\u2021n t\\u00e1\\u00ba\\u00a1i\"},{\"id\":1,\"title\":\"L\\u1eadp tr\\u00ecnh C#\",\"category\":\"L\\u1eadp tr\\u00ecnh\",\"learning_type\":\"online\",\"level\":\"beginner\",\"duration\":\"1 gi\\u1edd 40 ph\\u00fat\",\"price_label\":\"10.000 VND\",\"url\":\"http:\\/\\/localhost:8000\\/courses\\/1\",\"reason\":\"ph\\u00c3\\u00b9 h\\u00e1\\u00bb\\u00a3p v\\u00e1\\u00bb\\u203ai nhu c\\u00e1\\u00ba\\u00a7u hi\\u00e1\\u00bb\\u2021n t\\u00e1\\u00ba\\u00a1i\"}]', '{\"source\":\"gemini\",\"status\":null}', '2026-03-30 07:52:57', '2026-03-30 07:52:57');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `instructor_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  `meeting_info` text DEFAULT NULL,
  `max_students` int(11) NOT NULL DEFAULT 0,
  `price_override` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`, `course_id`, `instructor_id`, `start_date`, `end_date`, `schedule`, `meeting_info`, `max_students`, `price_override`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Fashion men', 2, 3, '2026-03-31', '2026-04-10', NULL, NULL, 10, NULL, 'active', '2026-03-22 13:22:39', '2026-03-22 13:22:39'),
(7, 'Lập trình C# 01', 2, 3, '2026-04-08', '2026-04-15', NULL, NULL, 50, NULL, 'active', '2026-03-22 13:51:19', '2026-03-22 13:56:31'),
(8, 'Lập trình C#', 1, 3, '2026-03-26', '2031-03-26', NULL, NULL, 0, NULL, 'active', '2026-03-26 14:02:49', '2026-03-26 14:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `class_change_logs`
--

CREATE TABLE `class_change_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `enrollment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `old_class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `new_class_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_schedules`
--

CREATE TABLE `class_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `weekday` varchar(5) NOT NULL COMMENT '2..7 or CN',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_schedules`
--

INSERT INTO `class_schedules` (`id`, `class_id`, `weekday`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(2, 6, '2', '18:00:00', '20:00:00', '2026-03-22 13:38:42', '2026-03-22 13:38:42'),
(5, 7, '2', '07:30:00', '09:30:00', '2026-03-22 13:56:31', '2026-03-22 13:56:31'),
(6, 7, '2', '17:30:00', '19:30:00', '2026-03-22 13:56:31', '2026-03-22 13:56:31');

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
  `category_id` bigint(20) UNSIGNED NOT NULL,
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
  `series_key` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `slug`, `description`, `short_description`, `price`, `sale_price`, `thumbnail`, `banner_image`, `level`, `duration`, `lessons_count`, `students_count`, `rating`, `total_rating`, `category_id`, `status`, `learning_type`, `announcement`, `has_default_quiz`, `default_quiz_data`, `is_featured`, `is_popular`, `meta`, `created_at`, `updated_at`, `deleted_at`, `video_url`, `pdf_path`, `series_key`) VALUES
(1, 'Lập trình C#', 'lap-trinh-c', 'cucwcw', 'scscasc', 10000.00, NULL, 'courses/thumbnails/ifRYzz6XJEFCSLsWq4TEtNhakLF7K57m4IDmxhb1.png', 'courses/banners/XmGAs77vwoiTIxF9MaLYJ51TeuRrbKLi8c8lRJoq.png', 'beginner', 100, 0, 0, 0.0, 0, 1, 'published', 'online', 'fefefef', 0, NULL, 0, 0, NULL, '2026-03-20 00:30:09', '2026-03-26 14:13:06', NULL, NULL, NULL, NULL),
(2, 'Lập trình java', 'lap-trinh-java', '2d32f23f2fwfdwdwqd', 'aocjao', 10000.00, 2000.00, 'courses/thumbnails/tfTyJYc5sauaVR50Cn6Q4o3m66TXnfsfGyDEkMgA.png', 'courses/banners/LXDdna0cJlLfHY3QGSmxHDpLFUdCOBCmERukhwK1.png', 'intermediate', 100, 0, 1, 0.0, 0, 1, 'published', 'offline', 'đwdqwdq', 0, NULL, 0, 0, NULL, '2026-03-20 19:25:28', '2026-03-26 16:42:42', NULL, NULL, NULL, NULL);

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
(1, 'Lập trình', 'lap-trinh', 'Lập trình web', NULL, 'fas fa-yoga', '#2c5aa0', 0, 1, '2026-03-20 00:20:57', '2026-03-20 00:20:57');

-- --------------------------------------------------------

--
-- Table structure for table `course_certificates`
--

CREATE TABLE `course_certificates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `enrollment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `certificate_no` varchar(255) NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_enrollments`
--

INSERT INTO `course_enrollments` (`id`, `user_id`, `class_id`, `status`, `enrolled_at`, `approved_at`, `rejected_at`, `cancelled_at`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES
(12, 5, 7, 'approved', '2026-03-26 16:42:42', '2026-03-26 16:42:42', NULL, NULL, NULL, NULL, '2026-03-26 16:42:24', '2026-03-26 16:42:42');

-- --------------------------------------------------------

--
-- Table structure for table `course_materials`
--

CREATE TABLE `course_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `course_module_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('video','pdf','assignment','quiz','meeting') NOT NULL DEFAULT 'video',
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `estimated_duration_minutes` int(10) UNSIGNED DEFAULT NULL,
  `order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_material_progress`
--

CREATE TABLE `course_material_progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `enrollment_id` bigint(20) UNSIGNED NOT NULL,
  `course_material_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `progress_percent` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `last_viewed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `best_quiz_score` decimal(5,2) DEFAULT NULL,
  `quiz_attempts_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `passed_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_material_quiz_attempts`
--

CREATE TABLE `course_material_quiz_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `enrollment_id` bigint(20) UNSIGNED NOT NULL,
  `course_material_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `attempt_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `total_questions` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `correct_answers` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `score_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_passed` tinyint(1) NOT NULL DEFAULT 0,
  `answers_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers_summary`)),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_modules`
--

CREATE TABLE `course_modules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_reviews`
--

CREATE TABLE `course_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `instructor_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `instructor_rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_review_replies`
--

CREATE TABLE `course_review_replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_videos`
--

CREATE TABLE `course_videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, '2025_03_06_000000_create_settings_table', 1),
(2, '2025_11_08_175856_create_users_table', 1),
(3, '2025_11_08_181315_create_sessions_table', 1),
(4, '2025_11_14_050154_create_post_categories_table', 1),
(5, '2025_11_14_050158_create_posts_table', 1),
(6, '2025_11_20_124941_create_course_categories_table', 1),
(7, '2025_11_20_124942_create_courses_table', 1),
(8, '2025_11_20_160000_create_classes_table', 1),
(9, '2025_11_20_163739_create_course_enrollments_table', 1),
(10, '2026_03_11_000000_add_oauth_to_users_table', 1),
(11, '2026_03_13_000000_create_wallets_and_transactions_tables', 1),
(12, '2026_03_16_000000_add_instructor_video_pdfSeries_to_courses_and_create_course_materials_table', 1),
(13, '2026_03_16_000001_create_course_reviews_table', 1),
(14, '2026_03_16_000002_add_rating_to_users_table', 1),
(15, '2026_03_16_000003_create_course_review_replies_table', 1),
(16, '2026_03_16_081529_add_course_features_to_courses_table', 1),
(17, '2026_03_16_090000_create_payments_table', 1),
(18, '2026_03_16_132923_create_quizzes_table', 1),
(19, '2026_03_16_133153_create_quiz_questions_table', 1),
(20, '2026_03_16_133623_create_quiz_attempts_table', 1),
(21, '2026_03_16_133633_create_quiz_answers_table', 1),
(22, '2026_03_16_133641_create_course_videos_table', 1),
(23, '2026_03_16_164740_create_cache_table', 1),
(24, '2026_03_21_000001_create_class_change_logs_table', 2),
(25, '2026_03_21_000002_create_class_schedules_table', 2),
(26, '2026_03_21_000003_change_payments_method_to_string', 3),
(27, '2026_03_21_120000_create_system_logs_table', 4),
(28, '2026_03_25_120000_create_course_material_progress_table', 5),
(29, '2026_03_25_120100_create_course_material_quiz_attempts_table', 5),
(30, '2026_03_25_120200_create_course_certificates_table', 5),
(31, '2026_03_25_150000_create_assistant_conversations_table', 5),
(32, '2026_03_25_150100_create_assistant_messages_table', 5),
(33, '2026_03_26_090000_create_course_modules_table', 6),
(34, '2026_03_26_090100_add_course_module_id_to_course_materials_table', 6),
(35, '2026_03_27_100000_add_estimated_duration_minutes_to_course_materials_table', 7),
(36, '2026_03_27_120000_add_enrollment_workflow_fields_to_course_enrollments_table', 7),
(37, '2026_03_28_090000_add_meeting_type_to_course_materials_table', 8),
(38, '2026_03_28_140000_add_expiration_to_wallet_transactions_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('pre_test','post_test','practice','exam') NOT NULL DEFAULT 'practice',
  `time_limit` int(11) DEFAULT NULL,
  `passing_score` int(11) NOT NULL DEFAULT 70,
  `max_attempts` int(11) NOT NULL DEFAULT 3,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `shuffle_questions` tinyint(1) NOT NULL DEFAULT 0,
  `show_results` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `attempt_id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `user_answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`user_answer`)),
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `quiz_id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quiz_id` bigint(20) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer','essay') NOT NULL DEFAULT 'multiple_choice',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`correct_answers`)),
  `explanation` text DEFAULT NULL,
  `points` int(11) NOT NULL DEFAULT 1,
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'site_logo', 'logos/mjaZXiQxENmSxToFyuuz6cHVG6svtycF1KT1BsBR.png', '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(2, 'site_favicon', 'favicons/uGeJbrQlYAVzACkha5MOZG68m14GgSkffo4f17wu.png', '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(3, 'site_name', 'Khai Trí Edu', '2026-03-22 16:21:34', '2026-03-30 22:57:53'),
(4, 'site_tagline', 'Nền tảng của Thành đẹp trai', '2026-03-22 16:21:34', '2026-03-30 22:57:53'),
(5, 'contact_email', NULL, '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(6, 'contact_phone', NULL, '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(7, 'contact_address', NULL, '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(8, 'facebook_url', NULL, '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(9, 'twitter_url', NULL, '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(10, 'instagram_url', NULL, '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(11, 'footer_text', NULL, '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(12, 'ai_assistant_prompt', NULL, '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(13, 'class_change_deadline_days', '0', '2026-03-22 16:21:34', '2026-03-22 16:21:34'),
(14, 'allow_class_change', '0', '2026-03-22 16:21:34', '2026-03-22 16:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `category`, `action`, `details`, `reference`, `ip`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 4, 'transaction', 'topup_requested', '{\"amount\":1000000,\"method\":\"direct\",\"tx_id\":22}', 'DEP69BEC11F72659', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-21 09:02:39', '2026-03-21 09:02:39'),
(2, 2, 'transaction', 'refund_issued', '{\"purchase_id\":17,\"refund_tx_id\":24,\"amount\":\"2000.00\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 09:43:18', '2026-03-22 09:43:18'),
(3, 2, 'transaction', 'topup_requested', '{\"amount\":1000000,\"method\":\"direct\",\"tx_id\":25}', 'DEP69C029C0051AF', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 10:41:20', '2026-03-22 10:41:20'),
(4, 1, 'transaction', 'topup_confirmed', '{\"wallet_tx_id\":25,\"amount\":\"1000000.00\",\"reference\":\"DEP69C029C0051AF\",\"method\":\"direct\",\"confirmed_by\":1}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-22 10:50:27', '2026-03-22 10:50:27'),
(5, 4, 'security', 'login_success', '{\"user_id\":4}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-22 10:53:02', '2026-03-22 10:53:02'),
(6, 4, 'transaction', 'topup_requested', '{\"amount\":100000,\"method\":\"direct\",\"tx_id\":26}', 'DEP69C02C8A9E172', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-22 10:53:14', '2026-03-22 10:53:14'),
(7, 5, 'security', 'login_success', '{\"user_id\":5}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-26 14:10:41', '2026-03-26 14:10:41'),
(8, 5, 'transaction', 'topup_requested', '{\"amount\":10000,\"method\":\"direct\",\"tx_id\":27}', 'DEP69C5A0DFE8622', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-26 14:10:55', '2026-03-26 14:10:55'),
(9, 1, 'transaction', 'topup_confirmed', '{\"wallet_tx_id\":27,\"amount\":\"10000.00\",\"reference\":\"DEP69C5A0DFE8622\",\"method\":\"direct\",\"confirmed_by\":1}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 14:11:14', '2026-03-26 14:11:14'),
(10, 5, 'transaction', 'refund_issued', '{\"purchase_id\":28,\"refund_tx_id\":29,\"amount\":\"2000.00\"}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-26 14:12:25', '2026-03-26 14:12:25'),
(11, 5, 'transaction', 'topup_requested', '{\"amount\":10000,\"method\":\"direct\",\"tx_id\":31}', 'DEP69C5C3CE16B9B', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-26 16:39:58', '2026-03-26 16:39:58'),
(12, 1, 'transaction', 'topup_confirmed', '{\"wallet_tx_id\":31,\"amount\":\"10000.00\",\"reference\":\"DEP69C5C3CE16B9B\",\"method\":\"direct\",\"confirmed_by\":1}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-26 16:40:11', '2026-03-26 16:40:11'),
(13, 2, 'transaction', 'topup_requested', '{\"amount\":100000,\"method\":\"qr\",\"tx_id\":35}', '10f8a536-c260-4c38-87a3-da7a557059c9', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-30 23:58:21', '2026-03-30 23:58:21'),
(14, 2, 'transaction', 'topup_requested', '{\"amount\":10000,\"method\":\"qr\",\"tx_id\":36}', 'a4f0a440-68eb-4057-875e-863a8cabec62', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-31 00:26:13', '2026-03-31 00:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `email`, `google_id`, `facebook_id`, `provider`, `provider_id`, `password`, `avatar`, `otp`, `role`, `is_verified`, `rating`, `total_rating`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Đặnghoàngthành', 'Đặng Hoàng Thành', 'dhthanh2004@gmail.com', '112375129696642063235', NULL, 'google', '112375129696642063235', NULL, 'avatars/oauth_112375129696642063235.jpg', NULL, 'admin', 1, 0.0, 0, NULL, '2026-03-20 00:20:04', '2026-03-27 20:14:55'),
(2, 'hoàngthành', 'Hoàng Thành', 'hoangthanh2004pubg@gmail.com', NULL, '968984065708084', 'facebook', '968984065708084', NULL, 'avatars/oauth_968984065708084.jpg', NULL, 'student', 1, 0.0, 0, NULL, '2026-03-20 18:12:33', '2026-03-30 23:23:49'),
(3, 'giangvien1', 'Nguyễn Văn A', 'dhthanh20041@gmail.com', NULL, NULL, NULL, NULL, '$2y$12$P5VGMS1q8lMId9i2GmzBUOmPwE0YS0w8ekoGN.JPb8ePEbwY8rff.', NULL, NULL, 'instructor', 1, 0.0, 0, NULL, '2026-03-20 18:37:39', '2026-03-20 18:37:39'),
(4, 'student1', 'Nguyễn Văn A', 'thanh_dth225765@student.agu.edu.vn', NULL, NULL, NULL, NULL, '$2y$12$SBrEyXFd3Nucx3D1Bhlnm.A5ElAEdB4biRNzQ2/f2Td8ttMiYOYTS', NULL, NULL, 'student', 1, 0.0, 0, NULL, '2026-03-21 09:01:53', '2026-03-21 09:02:24'),
(5, 'student2', 'Nguyễn Văn A', 'genshincloneryo1@gmail.com', NULL, NULL, NULL, NULL, '$2y$12$QeaugDDG8H9j3HzUam5H1ukgyoYZBJOuRf1wOW4qlFeHukDOLRVw.', NULL, NULL, 'student', 1, 0.0, 0, NULL, '2026-03-26 07:00:12', '2026-03-26 07:00:43');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `created_at`, `updated_at`) VALUES
(1, 2, 12596000.00, '2026-03-20 18:25:02', '2026-03-30 23:58:30'),
(2, 1, 0.00, '2026-03-20 20:30:02', '2026-03-20 20:30:02'),
(3, 4, 0.00, '2026-03-21 09:02:28', '2026-03-21 09:02:28'),
(4, 5, 4000.00, '2026-03-26 07:00:48', '2026-03-26 16:42:24');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('deposit','purchase','refund') NOT NULL DEFAULT 'deposit',
  `amount` decimal(14,2) NOT NULL,
  `status` enum('pending','completed','failed','expired') NOT NULL DEFAULT 'pending',
  `reference` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `wallet_id`, `course_id`, `type`, `amount`, `status`, `reference`, `expires_at`, `expired_at`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'deposit', 10000000.00, 'completed', NULL, '2026-03-22 18:38:59', NULL, '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 18:38:59', '2026-03-20 18:38:59'),
(2, 1, NULL, 'purchase', 10000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":1,\"class_id\":3}', '2026-03-20 18:39:07', '2026-03-20 18:39:07'),
(3, 1, NULL, 'purchase', 2000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":2,\"class_id\":4}', '2026-03-20 19:27:33', '2026-03-20 19:27:33'),
(4, 1, NULL, 'purchase', 2000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":2,\"class_id\":5}', '2026-03-20 19:36:04', '2026-03-20 19:36:04'),
(5, 1, NULL, 'purchase', 10000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":1,\"class_id\":3}', '2026-03-20 20:30:19', '2026-03-20 20:30:19'),
(6, 1, NULL, 'deposit', 2000.00, 'completed', NULL, NULL, NULL, '{\"refunded_purchase_id\":3,\"course_id\":2,\"class_id\":4,\"reason\":\"refund_on_unenroll_before_start\"}', '2026-03-20 20:30:28', '2026-03-20 20:30:28'),
(7, 1, NULL, 'deposit', 2000.00, 'completed', NULL, NULL, NULL, '{\"refunded_purchase_id\":4,\"course_id\":2,\"class_id\":5,\"reason\":\"refund_on_unenroll_before_start\"}', '2026-03-20 20:30:36', '2026-03-20 20:30:36'),
(8, 1, NULL, 'purchase', 2000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":2,\"class_id\":4}', '2026-03-20 20:30:49', '2026-03-20 20:30:49'),
(9, 1, NULL, 'deposit', 500000.00, 'completed', NULL, '2026-03-22 20:49:26', NULL, '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 20:49:26', '2026-03-20 20:49:26'),
(10, 1, NULL, 'deposit', 1000.00, 'completed', NULL, '2026-03-22 20:49:39', NULL, '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 20:49:39', '2026-03-20 20:49:39'),
(11, 1, NULL, 'deposit', 10000.00, 'completed', NULL, '2026-03-22 20:50:01', NULL, '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 20:50:01', '2026-03-20 20:50:01'),
(12, 1, NULL, 'deposit', 1000000.00, 'completed', NULL, '2026-03-22 20:51:22', NULL, '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 20:51:22', '2026-03-20 20:51:22'),
(13, 1, NULL, 'deposit', 1000.00, 'completed', 'DEP69BE168AD0415', '2026-03-22 20:54:50', NULL, '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 20:54:50', '2026-03-20 20:55:05'),
(14, 1, NULL, 'deposit', 2000.00, 'completed', NULL, NULL, NULL, '{\"refunded_purchase_id\":8,\"course_id\":2,\"class_id\":4,\"reason\":\"refund_on_unenroll_before_start\"}', '2026-03-20 21:02:15', '2026-03-20 21:02:15'),
(15, 1, NULL, 'deposit', 2000.00, 'completed', NULL, NULL, NULL, '{\"refunded_purchase_id\":4,\"course_id\":2,\"class_id\":5,\"reason\":\"refund_on_unenroll_before_start\"}', '2026-03-20 21:02:30', '2026-03-20 21:02:30'),
(16, 1, NULL, 'deposit', 2000.00, 'completed', NULL, NULL, NULL, '{\"refunded_purchase_id\":8,\"course_id\":2,\"class_id\":4,\"reason\":\"refund_on_unenroll_before_start\"}', '2026-03-20 21:26:15', '2026-03-20 21:26:15'),
(17, 1, NULL, 'purchase', 2000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":2,\"class_id\":4}', '2026-03-20 21:26:24', '2026-03-20 21:26:24'),
(18, 1, NULL, 'deposit', 10000.00, 'expired', 'DEP69BE1DF96DD6E', '2026-03-22 21:26:33', '2026-03-28 03:04:50', '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 21:26:33', '2026-03-20 21:26:33'),
(19, 1, NULL, 'deposit', 10000.00, 'completed', 'DEP69BE1EC1A4853', '2026-03-22 21:29:53', NULL, '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 21:29:53', '2026-03-20 21:30:02'),
(20, 1, NULL, 'deposit', 10000.00, 'expired', 'DEP69BE21C703037', '2026-03-22 21:42:47', '2026-03-28 03:04:50', '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 21:42:47', '2026-03-20 21:42:47'),
(21, 1, NULL, 'deposit', 100000.00, 'expired', 'DEP69BE221D8B246', '2026-03-22 21:44:13', '2026-03-28 03:04:50', '{\"method\":\"direct\",\"requested_by\":2}', '2026-03-20 21:44:13', '2026-03-20 21:44:13'),
(22, 3, NULL, 'deposit', 1000000.00, 'expired', 'DEP69BEC11F72659', '2026-03-23 09:02:39', '2026-03-28 03:04:50', '{\"method\":\"direct\",\"requested_by\":4}', '2026-03-21 09:02:39', '2026-03-21 09:02:39'),
(23, 1, NULL, 'purchase', 10000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":1,\"class_id\":3}', '2026-03-22 09:42:55', '2026-03-22 09:42:56'),
(24, 1, NULL, 'deposit', 2000.00, 'completed', NULL, NULL, NULL, '{\"refunded_purchase_id\":17,\"course_id\":2,\"class_id\":4,\"reason\":\"refund_on_unenroll_before_start\"}', '2026-03-22 09:43:18', '2026-03-22 09:43:18'),
(25, 1, NULL, 'deposit', 1000000.00, 'completed', 'DEP69C029C0051AF', '2026-03-24 10:41:20', NULL, '{\"method\":\"direct\",\"requested_by\":2,\"confirmed_by\":1,\"confirmed_at\":\"2026-03-22 17:50:27\"}', '2026-03-22 10:41:20', '2026-03-22 10:50:27'),
(26, 3, NULL, 'deposit', 100000.00, 'expired', 'DEP69C02C8A9E172', '2026-03-24 10:53:14', '2026-03-28 03:04:50', '{\"method\":\"direct\",\"requested_by\":4}', '2026-03-22 10:53:14', '2026-03-22 10:53:14'),
(27, 4, NULL, 'deposit', 10000.00, 'completed', 'DEP69C5A0DFE8622', '2026-03-28 14:10:55', NULL, '{\"method\":\"direct\",\"requested_by\":5,\"confirmed_by\":1,\"confirmed_at\":\"2026-03-26 21:11:14\"}', '2026-03-26 14:10:55', '2026-03-26 14:11:14'),
(28, 4, NULL, 'purchase', 2000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":2,\"class_id\":7}', '2026-03-26 14:11:37', '2026-03-26 14:11:37'),
(29, 4, NULL, 'deposit', 2000.00, 'completed', NULL, NULL, NULL, '{\"refunded_purchase_id\":28,\"course_id\":2,\"class_id\":7,\"reason\":\"refund_on_unenroll_before_start\"}', '2026-03-26 14:12:25', '2026-03-26 14:12:25'),
(30, 4, NULL, 'purchase', 10000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":1,\"class_id\":8}', '2026-03-26 14:12:49', '2026-03-26 14:12:49'),
(31, 4, NULL, 'deposit', 10000.00, 'completed', 'DEP69C5C3CE16B9B', '2026-03-28 16:39:58', NULL, '{\"method\":\"direct\",\"requested_by\":5,\"confirmed_by\":1,\"confirmed_at\":\"2026-03-26 23:40:11\"}', '2026-03-26 16:39:58', '2026-03-26 16:40:11'),
(32, 4, NULL, 'purchase', 2000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":2,\"class_id\":6}', '2026-03-26 16:40:21', '2026-03-26 16:40:21'),
(33, 4, NULL, 'purchase', 2000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":2,\"class_id\":7}', '2026-03-26 16:41:12', '2026-03-26 16:41:12'),
(34, 4, NULL, 'purchase', 2000.00, 'completed', NULL, NULL, NULL, '{\"course_id\":2,\"class_id\":7}', '2026-03-26 16:42:24', '2026-03-26 16:42:24'),
(35, 1, NULL, 'deposit', 100000.00, 'completed', '10f8a536-c260-4c38-87a3-da7a557059c9', NULL, NULL, '{\"method\":\"qr\",\"requested_by\":2}', '2026-03-30 23:58:21', '2026-03-30 23:58:30'),
(36, 1, NULL, 'deposit', 10000.00, 'pending', 'a4f0a440-68eb-4057-875e-863a8cabec62', NULL, NULL, '{\"method\":\"qr\",\"requested_by\":2}', '2026-03-31 00:26:13', '2026-03-31 00:26:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assistant_conversations`
--
ALTER TABLE `assistant_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assistant_conversations_user_id_session_id_index` (`user_id`,`session_id`),
  ADD KEY `assistant_conversations_session_id_index` (`session_id`),
  ADD KEY `assistant_conversations_last_message_at_index` (`last_message_at`);

--
-- Indexes for table `assistant_messages`
--
ALTER TABLE `assistant_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assistant_messages_assistant_conversation_id_created_at_index` (`assistant_conversation_id`,`created_at`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classes_course_id_foreign` (`course_id`),
  ADD KEY `classes_instructor_id_foreign` (`instructor_id`);

--
-- Indexes for table `class_change_logs`
--
ALTER TABLE `class_change_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_change_logs_enrollment_id_foreign` (`enrollment_id`),
  ADD KEY `class_change_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_schedules_class_id_index` (`class_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `courses_slug_unique` (`slug`),
  ADD KEY `courses_category_id_foreign` (`category_id`),
  ADD KEY `courses_series_key_index` (`series_key`);

--
-- Indexes for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_categories_slug_unique` (`slug`),
  ADD KEY `course_categories_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `course_certificates`
--
ALTER TABLE `course_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_certificates_course_id_enrollment_id_unique` (`course_id`,`enrollment_id`),
  ADD UNIQUE KEY `course_certificates_certificate_no_unique` (`certificate_no`),
  ADD KEY `course_certificates_enrollment_id_foreign` (`enrollment_id`),
  ADD KEY `course_certificates_user_id_foreign` (`user_id`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_enrollments_user_id_class_id_unique` (`user_id`,`class_id`),
  ADD KEY `course_enrollments_class_id_foreign` (`class_id`);

--
-- Indexes for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_materials_course_id_foreign` (`course_id`),
  ADD KEY `course_materials_course_module_id_foreign` (`course_module_id`);

--
-- Indexes for table `course_material_progress`
--
ALTER TABLE `course_material_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_material_progress_enrollment_id_course_material_id_unique` (`enrollment_id`,`course_material_id`),
  ADD KEY `course_material_progress_course_material_id_foreign` (`course_material_id`),
  ADD KEY `course_material_progress_user_id_completed_at_index` (`user_id`,`completed_at`);

--
-- Indexes for table `course_material_quiz_attempts`
--
ALTER TABLE `course_material_quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_material_quiz_attempts_enrollment_id_foreign` (`enrollment_id`),
  ADD KEY `course_material_quiz_attempts_user_id_foreign` (`user_id`),
  ADD KEY `course_material_quiz_attempts_course_material_id_user_id_index` (`course_material_id`,`user_id`);

--
-- Indexes for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_modules_course_id_order_index` (`course_id`,`order`);

--
-- Indexes for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_reviews_course_id_user_id_unique` (`course_id`,`user_id`),
  ADD KEY `course_reviews_user_id_foreign` (`user_id`),
  ADD KEY `course_reviews_instructor_id_foreign` (`instructor_id`);

--
-- Indexes for table `course_review_replies`
--
ALTER TABLE `course_review_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_review_replies_review_id_foreign` (`review_id`),
  ADD KEY `course_review_replies_user_id_foreign` (`user_id`);

--
-- Indexes for table `course_videos`
--
ALTER TABLE `course_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_videos_course_id_order_index` (`course_id`,`order`),
  ADD KEY `course_videos_lesson_id_order_index` (`lesson_id`,`order`),
  ADD KEY `course_videos_processing_status_index` (`processing_status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_reference_unique` (`reference`),
  ADD KEY `payments_status_index` (`status`),
  ADD KEY `payments_user_id_index` (`user_id`),
  ADD KEY `payments_class_id_index` (`class_id`);

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
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizzes_lesson_id_foreign` (`lesson_id`),
  ADD KEY `quizzes_course_id_type_index` (`course_id`,`type`),
  ADD KEY `quizzes_is_active_index` (`is_active`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_answers_question_id_foreign` (`question_id`),
  ADD KEY `quiz_answers_attempt_id_question_id_index` (`attempt_id`,`question_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_attempts_user_id_quiz_id_index` (`user_id`,`quiz_id`),
  ADD KEY `quiz_attempts_quiz_id_status_index` (`quiz_id`,`status`),
  ADD KEY `quiz_attempts_status_index` (`status`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_questions_quiz_id_order_index` (`quiz_id`,`order`),
  ADD KEY `quiz_questions_question_type_index` (`question_type`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_logs_category_action_index` (`category`,`action`),
  ADD KEY `system_logs_user_id_index` (`user_id`),
  ADD KEY `system_logs_category_index` (`category`),
  ADD KEY `system_logs_action_index` (`action`),
  ADD KEY `system_logs_reference_index` (`reference`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_google_id_unique` (`google_id`),
  ADD UNIQUE KEY `users_facebook_id_unique` (`facebook_id`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_is_verified_index` (`is_verified`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallets_user_id_foreign` (`user_id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_transactions_wallet_id_foreign` (`wallet_id`),
  ADD KEY `wallet_transactions_course_id_foreign` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assistant_conversations`
--
ALTER TABLE `assistant_conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `assistant_messages`
--
ALTER TABLE `assistant_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `class_change_logs`
--
ALTER TABLE `class_change_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_schedules`
--
ALTER TABLE `class_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `course_categories`
--
ALTER TABLE `course_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `course_certificates`
--
ALTER TABLE `course_certificates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_material_progress`
--
ALTER TABLE `course_material_progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_material_quiz_attempts`
--
ALTER TABLE `course_material_quiz_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_modules`
--
ALTER TABLE `course_modules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_reviews`
--
ALTER TABLE `course_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_review_replies`
--
ALTER TABLE `course_review_replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_videos`
--
ALTER TABLE `course_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_categories`
--
ALTER TABLE `post_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assistant_conversations`
--
ALTER TABLE `assistant_conversations`
  ADD CONSTRAINT `assistant_conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assistant_messages`
--
ALTER TABLE `assistant_messages`
  ADD CONSTRAINT `assistant_messages_assistant_conversation_id_foreign` FOREIGN KEY (`assistant_conversation_id`) REFERENCES `assistant_conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_change_logs`
--
ALTER TABLE `class_change_logs`
  ADD CONSTRAINT `class_change_logs_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_change_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD CONSTRAINT `class_schedules_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD CONSTRAINT `course_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_certificates`
--
ALTER TABLE `course_certificates`
  ADD CONSTRAINT `course_certificates_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_certificates_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_certificates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD CONSTRAINT `course_materials_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_materials_course_module_id_foreign` FOREIGN KEY (`course_module_id`) REFERENCES `course_modules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_material_progress`
--
ALTER TABLE `course_material_progress`
  ADD CONSTRAINT `course_material_progress_course_material_id_foreign` FOREIGN KEY (`course_material_id`) REFERENCES `course_materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_material_progress_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_material_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_material_quiz_attempts`
--
ALTER TABLE `course_material_quiz_attempts`
  ADD CONSTRAINT `course_material_quiz_attempts_course_material_id_foreign` FOREIGN KEY (`course_material_id`) REFERENCES `course_materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_material_quiz_attempts_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_material_quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD CONSTRAINT `course_modules_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_reviews`
--
ALTER TABLE `course_reviews`
  ADD CONSTRAINT `course_reviews_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_reviews_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_review_replies`
--
ALTER TABLE `course_review_replies`
  ADD CONSTRAINT `course_review_replies_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `course_reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_review_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_videos`
--
ALTER TABLE `course_videos`
  ADD CONSTRAINT `course_videos_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_videos_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `course_materials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `post_categories` (`id`);

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `course_materials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `quiz_answers_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wallet_transactions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
