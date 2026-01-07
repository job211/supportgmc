-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 27 juin 2025 à 15:34
-- Version du serveur :  10.4.13-MariaDB
-- Version de PHP : 7.2.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ticket_app`
--

-- --------------------------------------------------------

--
-- Structure de la table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Nouveau','Ouvert','En cours','Fermé','En attente','Résolu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ouvert',
  `priority` enum('Basse','Moyenne','Haute','Urgente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Moyenne',
  `created_by_id` int(11) NOT NULL,
  `assigned_to_id` int(11) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` datetime DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tickets`
--

INSERT INTO `tickets` (`id`, `title`, `description`, `status`, `priority`, `created_by_id`, `assigned_to_id`, `country_id`, `service_id`, `created_at`, `updated_at`, `closed_at`, `type_id`) VALUES
(20, 'AAAAAAAA', 'AAAAAAAAAA', 'Résolu', 'Urgente', 4, 2, 10, 1, '2025-06-24 09:29:14', '2025-06-27 12:50:03', '2025-06-24 11:41:02', NULL),
(21, 'bbbbbbbbb', 'bbbbbbbbbbbbb', 'Résolu', 'Moyenne', 4, 2, 10, 1, '2025-06-24 09:31:53', '2025-06-27 12:50:08', '2025-06-24 11:41:20', NULL),
(22, 'eeeeeeeeeeeeeeeee', 'eeeeeeeeeeeeeeeeee', 'En cours', 'Moyenne', 4, 2, NULL, 1, '2025-06-24 09:41:02', '2025-06-24 11:04:32', NULL, NULL),
(23, 'zzzzzzzzzzz', 'zzzzzzzzzzzzzzzzzzzzz', 'En cours', 'Moyenne', 4, 2, NULL, 1, '2025-06-24 10:28:00', '2025-06-24 11:06:38', NULL, NULL),
(24, 'wwwwwwwwwwwwwwwwww', 'wwwwwwwwwwwwwwwwwwww', 'En cours', 'Moyenne', 4, 2, NULL, 1, '2025-06-24 10:31:47', '2025-06-24 12:13:08', NULL, NULL),
(25, 'wwwwwwwwwwwwwwwwww', 'wwwwwwwwwwwwwwwwwwww', 'Fermé', 'Moyenne', 4, 2, NULL, 1, '2025-06-24 10:34:20', '2025-06-24 12:28:10', '2025-06-24 12:28:10', NULL),
(26, 'zzzzzzaaaaaa111111111111', 'zzzzza', 'Nouveau', 'Moyenne', 4, NULL, NULL, 1, '2025-06-24 10:37:08', '2025-06-24 14:28:52', NULL, NULL),
(30, 'REFAIRE LE SITE AAIM', 'DDDSSD', 'Résolu', 'Urgente', 4, 2, NULL, 1, '2025-06-24 14:33:36', '2025-06-24 14:47:34', '2025-06-24 14:47:34', NULL),
(31, 'dfgjdfkbdflb', 'rgfgdfgfgdgd', 'En cours', 'Urgente', 4, 2, NULL, 1, '2025-06-24 14:53:43', '2025-06-24 14:56:02', NULL, NULL),
(32, 'ERREUR APPLICATION ZEUS RH', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', 'En cours', 'Haute', 5, 2, 13, 1, '2025-06-27 13:16:04', '2025-06-27 13:20:09', NULL, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_id` (`created_by_id`),
  ADD KEY `assigned_to_id` (`assigned_to_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `fk_ticket_country` (`country_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_ticket_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`assigned_to_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`type_id`) REFERENCES `ticket_types` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
