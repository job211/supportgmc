-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : palladvticket.mysql.db
-- Généré le : ven. 09 jan. 2026 à 16:12
-- Version du serveur : 8.0.43-34
-- Version de PHP : 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `palladvticket`
--

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','agent','client') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` int DEFAULT NULL,
  `service_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `has_seen_tutorial` tinyint(1) DEFAULT '0',
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `direction_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `country_id`, `service_id`, `created_at`, `has_seen_tutorial`, `reset_token`, `reset_token_expires_at`, `direction_id`) VALUES
(1, 'A.Marcel', '$2y$12$gCNCyarbfEwtl6xZiPF4QeEFSHFC764Qd2krIgbQFtD4YBKb6KSl6', 'tmarcel@hoope-africa.com', 'admin', 10, 2, '2025-06-23 11:31:05', 1, NULL, NULL, NULL),
(10, 'Mickaël PADONOU', '$2y$12$LKiQOd4cSpHJx42.rH5X1.a4d/oRB4BcXaCiXkhZjv/Dz1qpe9Ute', 'mpadonou@palladium-tech.com', 'admin', 10, 2, '2025-07-01 18:08:42', 1, NULL, NULL, NULL),
(11, 'anselme.hounsounon', '$2y$12$AvAvnDlv.qB/dIgRQ/JBe.4v4VK2QSm7oBYiLYQAjMvVE0U1QGspu', 'ahounsounon@gmail.com', 'admin', 13, 2, '2025-07-01 19:16:11', 1, NULL, NULL, NULL),
(12, 'james manga', '$2y$12$XFyL0q84PQm0hwsyeGe.PuwQyCoAZJ1vLiSCtanqipEm0rvWMOrG2', 'jmanga@groupmediacontact.com', 'client', 19, NULL, '2025-07-01 20:04:43', 1, NULL, NULL, NULL),
(13, 'FULVIO', '$2y$12$5eQe1UtPf2LjRvYK8IYlM.lNp8Kb/ia54o36s0E2MrWSMz7KizEXe', 'famadou@groupmediacontact.com', 'client', 13, NULL, '2025-07-02 10:08:57', 1, NULL, NULL, NULL),
(14, 'Marcel VODOUNGBE', '$2y$12$iIBpp9YcZFxJ5sHSGTDmXOgaEyCcmG08PI9HNImn2EIVdYeKCx2lC', 'mvodoungbe@groupmediacontact.com', 'client', 13, NULL, '2025-07-02 11:49:07', 1, NULL, NULL, NULL),
(15, 'laguiah', '$2y$12$nhzm6CRLJQ45MFlJyMAQceLT1R.a8cq77YlnnsftlmQ5YW/PIWV9q', 'laguiah@groupmediacontact.com', 'admin', 13, NULL, '2025-07-02 12:26:57', 1, NULL, NULL, NULL),
(16, 'idalmeida', '$2y$12$JvL7Uwgv4eD7Sz9ybxhYVucpm2itSHYPRdBERiWwtDlMAzzjqQ.1G', 'idalmeida@groupmediacontact.com', 'client', 13, NULL, '2025-07-03 08:37:39', 1, NULL, NULL, NULL),
(19, 'MICHELINE KWAMINAN', '$2y$12$xpePhKxIo1hMfKkE6GdbOOF5b2RjihLsz8fjttZuKIaMwKr6XSAzm', 'mkwaminan@groupmediacontact.com', 'client', 10, NULL, '2025-07-17 13:57:52', 1, NULL, NULL, NULL),
(20, 'KASSI RAISSA', '$2y$12$DAnOpUBWgpt1ay/jgV0tyOLSdPH95CeiWNhF.iYZQzSnwF0tvwD5O', 'rkassi@groupmediacontact.com', 'admin', 10, NULL, '2025-07-17 13:57:52', 1, NULL, NULL, NULL),
(21, 'Ayou GNANGNE', '$2y$12$IpZR02keX9sMG2zQFu2xe.MTLZrNCNxBnZezIv5ZdofE.PiFPO.Gm', 'mgnangne@groupmediacontact.com', 'client', 10, NULL, '2025-07-18 17:26:30', 1, NULL, NULL, 3),
(22, 'nseutchuang', '$2y$12$RDEdshENXlDJQZdvW5jbTOoYWeJiIQ0dPo0tQ7u63dE5MP02rMxQy', 'nseutchuang@groupmediacontact.com', 'admin', 19, NULL, '2025-07-21 12:57:51', 1, 'c92ff01251d2f72893239db6f5e7cbac0c4c67f47b151b885a8e97edb741d7656a91015b01929b5d6e860f6578199699ee4f', '2025-08-28 18:33:56', 1),
(23, 'ZINSOU', '$2y$12$DAnOpUBWgpt1ay/jgV0tyOLSdPH95CeiWNhF.iYZQzSnwF0tvwD5O', 'jzinzou@groupmediacontact.com', 'admin', 10, NULL, '2025-07-21 13:29:08', 1, NULL, NULL, 1),
(24, 'CLAUDE KONAN', '$2y$12$16bhM8rSMLTiGu/1tzCcsuSGVvWO8twmtiJwUxI1YssaVk6XVveVi', 'lkonan@groupmediacontact.com', 'client', 10, NULL, '2025-07-21 13:30:38', 1, NULL, NULL, 1),
(25, 'OKIE', '$2y$12$W2Nk2Xn/W/HPcw/dKqevQu9p7QBIvTR03mLHLodn7bGH5b4MLyAuq', 'eokie@groupmediacontact.com', 'client', 10, NULL, '2025-07-21 19:08:59', 1, NULL, NULL, 5),
(26, 'Carin', '$2y$12$12xyU8256ZHvYo.M4AVLnuBOWgfKNybNdVTVMfFKCcOnvjkVL23IS', 'cgouany@groupmediacontact.com', 'admin', 20, NULL, '2025-07-22 09:27:07', 1, NULL, NULL, 1),
(27, 'ugbodogbe', '$2y$12$a2HII.soLlcfdPlj0wgwBuaJ7aMGv4iAZ9vOXhrLxlko/aeC8bwIi', 'ugbodogbe@groupmediacontact.com', 'client', 13, NULL, '2025-07-23 18:05:57', 1, NULL, NULL, 2),
(28, 'Lucien METOTONDJI', '$2y$12$Zv9/dMZk1ZObRwmEIA3HhukoGr0pLJynVLM7xL93kkIcfKY8BteVm', 'lmetotondji@groupmediacontact.com', 'client', 13, NULL, '2025-07-24 12:58:46', 1, NULL, NULL, 6),
(29, 'ybessala', '$2y$12$SdrpnbqsDZnIWVwDQIELy.RGFfhreH1kRvw8/TM9WscbHVFCk9AIy', 'ybessalla@groupmediacontact.com', 'client', 19, NULL, '2025-07-25 14:22:30', 1, NULL, NULL, 3),
(30, 'apedro', '$2y$12$EL6QyHBMFVlZxopL1WPOjey5LtCK/hX3wacmzfZPpHHrLMZsZt4yK', 'apedro@groupmediacontact.com', 'client', 13, NULL, '2025-08-06 11:49:11', 1, NULL, NULL, 6),
(31, 'YOVO-AYI', '$2y$12$CapFa.zQAvZGf5Zz2dRUb.0HkUz0QV9D6dsUm83PbmM7xAbv7ubiy', 'syovo@groupmediacontact.com', 'client', 13, NULL, '2025-08-06 12:40:52', 1, NULL, NULL, 2),
(32, 'jlobe', '$2y$12$uYiGBXJTlweWXyL1Ne8A7e8fJ5cN./ilAF70h0bgtFJyexDtV/Lte', 'jlobe@groupmediacontact.com', 'client', NULL, NULL, '2025-08-08 15:27:32', 1, NULL, NULL, NULL),
(33, 'sgnimavo', '$2y$12$wdmDvNIUUcaLpz5YosWBtuTSaez1qqrMXpGW5CK880kEY//IaiCLO', 'sgnimavo@groupmediacontact.com', 'client', 20, NULL, '2025-09-11 18:00:04', 1, NULL, NULL, 4),
(34, 'TAIROU', '$2y$12$SyYLgDlLNyLTNze1OHK5juS4fy9VFOrEmFH1u8sTKdLlcucfCofzm', 'ctairou@groupmediacontact.com', 'client', 13, NULL, '2025-09-19 17:40:25', 1, NULL, NULL, 4),
(35, 'jgankpa', '$2y$12$SR/68/8PgAftwOqXIdNlNuvttrWrt0S7spTaeAkmxYdy3cLXworYq', 'jgankpa@groupmediacontact.com', 'client', 13, NULL, '2025-09-19 18:37:40', 1, NULL, NULL, 6),
(36, 'ZANNOU BORIS', '$2y$12$cP0cTZjzHN.wYGV0uCD.7u8vH0a.sfGljtLz5gK9kNYvhL5.iXxA2', 'bzannou@hoope-africa.com', 'client', NULL, NULL, '2025-10-07 12:30:03', 1, NULL, NULL, 4),
(37, 'ruffinh11', '$2y$12$9VQ1JTFR9LlIrI.euUOrl.AlDapDKnXLgSAgV8zEaYBfBEO0Eq/fW', 'ruffin221@gmail.com', 'admin', 13, NULL, '2026-01-05 14:22:07', 1, NULL, NULL, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `fk_user_country` (`country_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
