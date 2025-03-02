-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Mar 2025 pada 18.45
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `instagramm`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `followers`
--

CREATE TABLE `followers` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `followers`
--

INSERT INTO `followers` (`id`, `follower_id`, `following_id`, `created_at`) VALUES
(11, 6, 1, '2025-02-16 22:37:47'),
(12, 1, 6, '2025-02-19 13:44:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `timestamp`, `status`, `created_at`) VALUES
(1, 1, 6, 'ji', '2025-02-14 10:58:37', 'unread', '2025-02-14 11:09:42'),
(2, 6, 1, 'apa', '2025-02-14 11:00:41', 'unread', '2025-02-14 11:09:42'),
(3, 1, 6, 'oka', '2025-02-14 11:03:11', 'unread', '2025-02-14 11:09:42'),
(4, 1, 6, 'lkkl', '2025-02-14 11:06:43', 'unread', '2025-02-14 11:09:42'),
(5, 1, 6, 'kj', '2025-02-14 11:10:38', 'unread', '2025-02-14 11:10:38'),
(6, 1, 6, 'jk', '2025-02-14 11:10:44', 'unread', '2025-02-14 11:10:44'),
(7, 1, 6, 'ok', '2025-02-14 13:16:11', 'unread', '2025-02-14 13:16:11'),
(8, 1, 1, 'halo', '2025-02-14 13:27:55', 'unread', '2025-02-14 13:27:55'),
(9, 6, 6, 'ya', '2025-02-14 13:28:15', 'unread', '2025-02-14 13:28:15'),
(10, 1, 6, 'ji', '2025-02-14 13:29:11', 'unread', '2025-02-14 13:29:11'),
(11, 6, 1, 'aok', '2025-02-14 13:29:18', 'unread', '2025-02-14 13:29:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `caption` text DEFAULT NULL,
  `media_paths` text NOT NULL,
  `likes` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `type` enum('image','video') NOT NULL DEFAULT 'image'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `caption`, `media_paths`, `likes`, `created_at`, `type`) VALUES
(46, 1, 'j', '[\"..\\/uploads\\/posts\\/67c444172ab84_OIP.jpg\"]', 0, '2025-03-02 18:42:15', 'image'),
(47, 1, 'tiktok', '[\"..\\/uploads\\/posts\\/67c4448d0bf2b_Jelajahi - Temukan video favorit Anda di TikTok.mp4\"]', 0, '2025-03-02 18:44:13', 'video');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reel_comments`
--

CREATE TABLE `reel_comments` (
  `id` int(11) NOT NULL,
  `reel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reel_likes`
--

CREATE TABLE `reel_likes` (
  `id` int(11) NOT NULL,
  `reel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stories`
--

CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `time_shown` enum('1','12','24') NOT NULL,
  `tags` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiration_time` datetime NOT NULL,
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stories`
--

INSERT INTO `stories` (`id`, `user_id`, `image_path`, `time_shown`, `tags`, `created_at`, `expires_at`, `file_path`, `uploaded_at`, `expiration_time`, `group_id`) VALUES
(55, 1, '', '1', NULL, '2025-02-13 12:38:30', NULL, '../uploads/stories/67ad85566894a_IGDownloader.App_3432800336925640997.mp4', '2025-02-13 05:38:30', '2025-02-13 13:38:30', NULL),
(56, 1, '', '1', NULL, '2025-02-13 12:40:27', NULL, '../uploads/stories/67ad85cb0791a_IGDownloader.App_3432800336925640997.mp4', '2025-02-13 05:40:27', '2025-02-13 13:40:27', NULL),
(57, 1, '', '1', NULL, '2025-02-13 12:41:13', NULL, '../uploads/stories/67ad85f9e9c02_foto.jpg', '2025-02-13 05:41:13', '2025-02-13 13:41:13', NULL),
(58, 1, '', '1', NULL, '2025-02-13 12:41:38', NULL, '../uploads/stories/67ad861242c82_foto.jpg', '2025-02-13 05:41:38', '2025-02-13 13:41:38', NULL),
(59, 1, '', '1', NULL, '2025-02-13 14:54:53', NULL, '../uploads/stories/67ada54d10078_foto.jpg', '2025-02-13 07:54:53', '2025-02-13 15:54:53', NULL),
(60, 1, '', '1', NULL, '2025-02-13 14:55:21', NULL, '../uploads/stories/67ada5697c965_IGDownloader.App_3432800336925640997.mp4', '2025-02-13 07:55:21', '2025-02-13 15:55:21', NULL),
(61, 1, '', '1', NULL, '2025-02-13 14:55:31', NULL, '../uploads/stories/67ada573e4deb_IGDownloader.App_3432800336925640997.mp4', '2025-02-13 07:55:31', '2025-02-13 15:55:31', NULL),
(62, 1, '', '1', NULL, '2025-02-13 14:57:19', NULL, '../uploads/stories/67ada5dfaff75_IGDownloader.App_3432800336925640997.mp4', '2025-02-13 07:57:19', '2025-02-13 15:57:19', NULL),
(63, 1, '', '1', NULL, '2025-02-13 14:59:36', NULL, '../uploads/stories/67ada66837d48_foto.jpg', '2025-02-13 07:59:36', '2025-02-13 15:59:36', NULL),
(64, 1, '', '1', NULL, '2025-02-17 10:33:38', NULL, '../uploads/stories/67b2ae1231318_Jelajahi - Temukan video favorit Anda di TikTok.mp4', '2025-02-17 03:33:38', '2025-02-17 11:33:38', NULL),
(65, 1, '', '1', NULL, '2025-02-17 10:34:11', NULL, '../uploads/stories/67b2ae332e48a_Jelajahi - Temukan video favorit Anda di TikTok.mp4', '2025-02-17 03:34:11', '2025-02-17 11:34:11', NULL),
(66, 1, '', '1', NULL, '2025-02-17 10:34:52', NULL, '../uploads/stories/67b2ae5c6edec_Jelajahi - Temukan video favorit Anda di TikTok.mp4', '2025-02-17 03:34:52', '2025-02-17 11:34:52', NULL),
(67, 1, '', '1', NULL, '2025-02-17 10:44:28', NULL, '../uploads/stories/67b2b09c86cc0_Jelajahi - Temukan video favorit Anda di TikTok.mp4', '2025-02-17 03:44:28', '2025-02-17 11:44:28', NULL),
(68, 1, '', '1', NULL, '2025-02-17 10:45:05', NULL, '../uploads/stories/67b2b0c110add_Jelajahi - Temukan video favorit Anda di TikTok.mp4', '2025-02-17 03:45:05', '2025-02-17 11:45:05', NULL),
(69, 1, '', '1', NULL, '2025-02-17 20:49:29', NULL, '../uploads/stories/67b33e69484c7_Jelajahi - Temukan video favorit Anda di TikTok.mp4', '2025-02-17 13:49:29', '2025-02-17 21:49:29', NULL),
(70, 1, '', '1', NULL, '2025-02-17 20:52:00', NULL, '../uploads/stories/67b33f007352a_Jelajahi - Temukan video favorit Anda di TikTok.mp4', '2025-02-17 13:52:00', '2025-02-17 21:52:00', NULL),
(71, 1, '', '1', NULL, '2025-02-19 20:43:49', NULL, '../uploads/stories/67b5e015ea8a1_IGDownloader.App_3432800336925640997.mp4', '2025-02-19 13:43:49', '2025-02-19 21:43:49', NULL),
(72, 6, '', '1', NULL, '2025-02-19 20:44:18', NULL, '../uploads/stories/67b5e0321e001_IGDownloader.App_3432800336925640997.mp4', '2025-02-19 13:44:18', '2025-02-19 21:44:18', NULL),
(73, 1, '', '1', NULL, '2025-02-19 20:44:26', NULL, '../uploads/stories/67b5e03a68ff2_IGDownloader.App_3432800336925640997.mp4', '2025-02-19 13:44:26', '2025-02-19 21:44:26', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `story_groups`
--

CREATE TABLE `story_groups` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `story_groups`
--

INSERT INTO `story_groups` (`id`, `user_id`, `created_at`) VALUES
(1, 1, '2025-01-12 06:40:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('Laki-laki','Perempuan') NOT NULL,
  `birthdate` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT 'default-profile.jpg',
  `profile_photo` varchar(255) DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `first_name`, `username`, `last_name`, `email`, `password`, `gender`, `birthdate`, `created_at`, `profile_picture`, `profile_photo`, `otp_code`, `bio`) VALUES
(1, 'jon', 'jon', 'yg', 'sahruloji119@gmail.com', '$2y$10$7HW3bf.TBnN2k6nyC4lLMOhZBRJ/ACVuS4v1fiLpi1s0YBfsUMR7S', 'Laki-laki', '1990-11-11', '2025-01-12 05:04:08', '1_1740915682_OIP.jpg', NULL, NULL, NULL),
(6, 'jon', 'jon1', 'sd', 'sshahruroji@gmail.com', '$2y$10$09IXr9bBdcdrPAYioXmd2eRJiGk4rYO.1ZDMk4Z0KTK2w5uyMkPf2', 'Laki-laki', '1990-12-12', '2025-02-14 03:23:48', '6_1740915549_04-image_1551632967_5c7c0a4731d17.jpg', NULL, '491871', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `follower_id` (`follower_id`),
  ADD KEY `following_id` (`following_id`);

--
-- Indeks untuk tabel `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indeks untuk tabel `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `reel_comments`
--
ALTER TABLE `reel_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reel_id` (`reel_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `reel_likes`
--
ALTER TABLE `reel_likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reel_id` (`reel_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `story_groups`
--
ALTER TABLE `story_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `followers`
--
ALTER TABLE `followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT untuk tabel `reel_comments`
--
ALTER TABLE `reel_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `reel_likes`
--
ALTER TABLE `reel_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT untuk tabel `story_groups`
--
ALTER TABLE `story_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `followers`
--
ALTER TABLE `followers`
  ADD CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `reel_comments`
--
ALTER TABLE `reel_comments`
  ADD CONSTRAINT `reel_comments_ibfk_1` FOREIGN KEY (`reel_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reel_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reel_likes`
--
ALTER TABLE `reel_likes`
  ADD CONSTRAINT `reel_likes_ibfk_1` FOREIGN KEY (`reel_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reel_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `stories`
--
ALTER TABLE `stories`
  ADD CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `story_groups`
--
ALTER TABLE `story_groups`
  ADD CONSTRAINT `story_groups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
