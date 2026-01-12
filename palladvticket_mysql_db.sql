-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : palladvticket.mysql.db
-- Généré le : jeu. 08 jan. 2026 à 10:38
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
CREATE DATABASE IF NOT EXISTS `palladvticket` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `palladvticket`;

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_system_message` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `comments`
--

INSERT INTO `comments` (`id`, `ticket_id`, `user_id`, `comment`, `created_at`, `is_system_message`) VALUES
(45, 45, 11, 'Le statut a été changé de \'Nouveau\' à \'En cours\'.', '2025-07-17 12:19:14', 1),
(50, 45, 11, 'Le statut a été changé de \'En cours\' à \'Fermé\'.', '2025-07-18 13:25:43', 1),
(51, 48, 1, 'Le statut a été changé de \'Nouveau\' à \'En cours\'.', '2025-07-21 13:08:58', 1),
(52, 48, 1, 'Preparer l\'environnement :\r\n- Installer XAMP version : V3.2.4\r\nOn se fera un meet à 14H GMT\r\nLien : https://meet.google.com/itg-rksi-wvr\r\nMerci pour ton retour stp !\r\n👍️', '2025-07-21 13:17:49', 0),
(53, 49, 1, 'Bien reçu ton ticket.', '2025-07-21 13:49:15', 0),
(54, 49, 1, 'Le statut a été changé de \'Nouveau\' à \'En attente\'.', '2025-07-21 13:49:30', 1),
(55, 48, 22, 'Bien reçu, L\'application a été installé.\r\nNous sommes disponible pour le meet.', '2025-07-21 13:57:55', 0),
(56, 48, 1, 'Super !\r\nA toute à l\'heure donc.', '2025-07-21 13:59:36', 0),
(57, 48, 1, 'Bonjour Noêl !\r\nOn peut debuter la seance d\'aujourd\'hui à 10H GMT ?', '2025-07-22 11:21:40', 0),
(58, 48, 22, 'Bonjour Jean marcel, je suis disponible.', '2025-07-22 12:16:28', 0),
(59, 48, 22, 'Hello Jean Marcel, Merci pour la mise en place déjà de l\'application. \r\n\r\nPermet moi d\'ajouter certains détails. Nous avons besoin que les autorisations soient bien définies notamment que les seules personne autorisés à faire cette extraction sont les personnes ayant les profils que je citerais ci dessous:\r\n-Team Leader\r\n-Assistant Qualité\r\n-Responsable de filiale\r\n- Les noms d\'utilisateur [nseutchuang nono, rkwedi, mndtoungou]\r\n\r\nAussi Pour le nom d\'utilisateur [nseutchuang nono] qui est le mien, j\'aimerais s\'il te plait que le mot de passe soit réintialisé et qu\'il me soit envoyé.', '2025-07-22 14:30:36', 0),
(60, 48, 1, 'Bien reçu !\r\nOn le fera tout à l\'heure..', '2025-07-22 15:20:27', 0),
(61, 48, 22, 'Merci Jean Marcel, tout est OK à notre niveau tous les point ont été résolus 😀', '2025-07-22 16:55:33', 0),
(62, 48, 1, 'Super !\r\n👏', '2025-07-22 16:56:43', 0),
(63, 48, 1, 'Le statut a été changé de \'En cours\' à \'Résolu\'.', '2025-07-22 16:56:49', 1),
(64, 50, 1, 'Bonjour Carin !\r\nJe prends en charge ce ticket\r\nJe te propose une séance pour demain :\r\nHeure : 10H30 GMT\r\nLien : https://meet.google.com/itg-rksi-wvr\r\n=> J\'attends ton retour conncernant ton créneau\r\n👍️', '2025-07-22 17:02:29', 0),
(65, 50, 26, 'Seriez-vous disponible demain matin ?', '2025-07-23 17:45:12', 0),
(66, 50, 1, 'On se fait un meet à 14H GMT', '2025-07-24 11:49:05', 0),
(67, 50, 26, 'D\'accord !', '2025-07-24 11:50:02', 0),
(68, 52, 1, 'Bonjour Lucien !\r\nJe prends en charge le ticket', '2025-07-24 18:14:23', 0),
(69, 52, 1, 'Le statut a été changé de \'Nouveau\' à \'En cours\'.', '2025-07-24 18:14:33', 1),
(70, 52, 1, 'Bonjour Lucien !\r\nJe viens par cette note te signifier que I\'implementation du Reporting Hermes 360 est 👍️ OK 👍️\r\nJe reste disponible pour d\'autres action dans ce sens.\r\nMerci bien', '2025-07-25 12:52:40', 0),
(71, 52, 1, 'Le statut a été changé de \'En cours\' à \'Résolu\'.', '2025-07-25 12:52:51', 1),
(72, 48, 22, 'Bonsoir, nous rencontrons des soucis avec l\'application. Ellle ne nous ressors pas les abandons, nous aimerions que la colone Endby agent soit supprimé s\'il vous plait nous avons besoin de votre assistance.', '2025-07-25 20:12:23', 0),
(73, 48, 22, 'Le statut a été changé de \'Résolu\' à \'En attente\'.', '2025-07-25 20:12:32', 1),
(74, 51, 10, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-07-31 12:45:31', 1),
(75, 48, 1, 'Bonjour Noel !\r\nQuel est le soucis à ton niveau, je n\'arrive pas à comprendre puisse que c\'est toi qui m\'a communiqué la requete avec laquelle tu  travaille.', '2025-07-31 12:56:23', 0),
(76, 53, 10, 'Donne plus détails de s\'il te plait', '2025-07-31 12:57:19', 0),
(77, 45, 10, 'Le statut a été changé de \'Fermé\' à \'Résolu\'.', '2025-07-31 12:57:55', 1),
(78, 54, 1, 'Bonjour Marie Helene !\r\nBien reçu...\r\nQuels seront les niveaux d\'acces ?', '2025-07-31 13:40:50', 0),
(79, 54, 1, 'Le statut a été changé de \'Nouveau\' à \'En cours\'.', '2025-07-31 13:41:00', 1),
(80, 53, 29, 'au niveau de statut du personnel vous verrez l\'absence du statut formation pourtant nous avons au Cameroun les agents en formation qu\'on enregistre dans zeus vous pouvez vérifiez cela sur la capture en pièce jointe que je vous avais envoyez.', '2025-07-31 13:46:58', 0),
(81, 54, 21, 'L\'ensemble des accès sauf la paie', '2025-08-01 11:56:47', 0),
(82, 54, 1, 'Bien reçu...\r\n👍️', '2025-08-01 12:02:31', 0),
(83, 54, 1, 'Le statut a été changé de \'En cours\' à \'Fermé\'.', '2025-08-01 12:24:25', 1),
(84, 54, 1, 'Le statut a été changé de \'Fermé\' à \'Résolu\'.', '2025-08-01 12:24:48', 1),
(85, 49, 10, 'Le statut a été changé de \'En attente\' à \'En cours\'.', '2025-08-08 10:23:08', 1),
(86, 59, 31, 'Bonjour Team,\r\nNous sommes toujours en attente de résolution du dysfonctionnement.\r\nEn attente de votre retour diligent. Merci', '2025-08-12 10:06:54', 0),
(87, 63, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-08-12 18:13:30', 1),
(88, 65, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-08-13 14:26:28', 1),
(89, 59, 1, 'Le statut a été changé de \'Nouveau\' à \'Fermé\'.', '2025-08-13 14:26:53', 1),
(90, 58, 1, 'Le statut a été changé de \'Nouveau\' à \'Fermé\'.', '2025-08-13 14:27:25', 1),
(91, 57, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-08-13 14:27:47', 1),
(92, 68, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-08-18 20:06:18', 1),
(93, 69, 10, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-08-21 12:23:06', 1),
(94, 67, 10, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-08-21 12:23:50', 1),
(95, 66, 10, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-08-21 12:24:15', 1),
(96, 67, 10, 'Le statut a été changé de \'Résolu\' à \'En attente\'.', '2025-08-21 13:26:48', 1),
(97, 66, 10, 'Le statut a été changé de \'Résolu\' à \'En attente\'.', '2025-08-21 13:27:41', 1),
(98, 70, 10, 'Le statut a été changé de \'Nouveau\' à \'Fermé\'.', '2025-08-21 13:28:54', 1),
(99, 48, 22, 'Le statut a été changé de \'En attente\' à \'En cours\'.', '2025-09-03 17:35:17', 1),
(100, 48, 22, 'Le statut a été changé de \'En cours\' à \'En attente\'.', '2025-09-03 17:35:31', 1),
(101, 71, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-09-03 20:01:53', 1),
(102, 72, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-09-03 20:02:11', 1),
(103, 48, 22, 'Du nouveau s\'il vous plait ? bonsoir', '2025-09-04 17:54:06', 0),
(104, 48, 22, 'Bonsoir, \r\nDésolé pour la réponse tardive.\r\nEn effet, le fichier extrait sort mais vierge.', '2025-09-04 18:27:32', 0),
(105, 67, 10, 'Le statut a été changé de \'En attente\' à \'Fermé\'.', '2025-09-05 11:08:20', 1),
(106, 66, 10, 'Le statut a été changé de \'En attente\' à \'Fermé\'.', '2025-09-05 11:08:45', 1),
(107, 48, 22, 'Bonsoir, nous n\'avons pas encore eu de retour concernant ce point s\'il vous plait la situation est urgente', '2025-09-08 17:14:45', 0),
(108, 67, 29, 'jusqu\'a ce jour je n\'arrive pas a résoudre ce souci, je n\'arrive pas a supprimer des données mal renseigner (les congés)', '2025-09-18 16:40:35', 0),
(109, 79, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-09-25 14:48:00', 1),
(110, 78, 1, 'Le statut a été changé de \'Nouveau\' à \'Fermé\'.', '2025-09-26 18:42:44', 1),
(111, 76, 10, 'Le statut a été changé de \'Nouveau\' à \'Fermé\'.', '2025-09-26 18:44:20', 1),
(112, 76, 10, 'Le statut a été changé de \'Fermé\' à \'Résolu\'.', '2025-09-26 18:44:24', 1),
(113, 74, 10, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-09-26 18:44:39', 1),
(114, 73, 10, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-09-26 18:45:00', 1),
(115, 74, 22, 'Bonjour, j\'espère que vous allez bien.\r\nCe soucis n\'est pas encore résolu à notre niveau.', '2025-09-29 11:01:47', 0),
(116, 74, 22, 'Le statut a été changé de \'Résolu\' à \'En attente\'.', '2025-09-29 11:01:53', 1),
(117, 81, 1, 'Le statut a été changé de \'Nouveau\' à \'Fermé\'.', '2025-10-14 14:52:01', 1),
(118, 81, 1, 'Le statut a été changé de \'Fermé\' à \'Résolu\'.', '2025-10-14 14:52:38', 1),
(119, 83, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-10-20 11:51:27', 1),
(120, 82, 1, 'Le statut a été changé de \'Nouveau\' à \'Fermé\'.', '2025-10-20 12:08:45', 1),
(121, 77, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-10-20 12:09:10', 1),
(122, 86, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-11-12 18:14:17', 1),
(123, 87, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-11-12 18:14:30', 1),
(124, 88, 1, 'Le statut a été changé de \'Nouveau\' à \'En cours\'.', '2025-11-12 18:15:13', 1),
(125, 88, 1, 'Le statut a été changé de \'En cours\' à \'Résolu\'.', '2025-11-19 11:02:45', 1),
(126, 90, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2025-11-26 15:43:42', 1),
(127, 84, 32, 'nous sommes toujours en attente pour ce ticket please', '2025-12-01 13:37:45', 0),
(128, 84, 32, 'nous sommes toujours en attente pour ce ticket please', '2025-12-01 13:37:45', 0),
(129, 100, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2026-01-07 16:58:26', 1),
(130, 96, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2026-01-07 16:59:13', 1),
(131, 92, 1, 'Le statut a été changé de \'Nouveau\' à \'Résolu\'.', '2026-01-07 16:59:36', 1);

-- --------------------------------------------------------

--
-- Structure de la table `countries`
--

CREATE TABLE `countries` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(2) NOT NULL COMMENT 'ISO 3166-1 alpha-2 code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `countries`
--

INSERT INTO `countries` (`id`, `name`, `code`) VALUES
(9, 'Sénégal', 'sn'),
(10, 'Côte d\'Ivoire', 'ci'),
(11, 'Mali', 'ml'),
(12, 'Burkina Faso', 'bf'),
(13, 'Bénin', 'bj'),
(14, 'Togo', 'tg'),
(15, 'Niger', 'ne'),
(16, 'Guinée', 'gn'),
(17, 'Nigeria', 'ng'),
(18, 'Guinée-Bissau', 'gw'),
(19, 'Cameroun', 'cm'),
(20, 'Congo', 'cg');

-- --------------------------------------------------------

--
-- Structure de la table `directions`
--

CREATE TABLE `directions` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `directions`
--

INSERT INTO `directions` (`id`, `name`) VALUES
(1, 'Pole DSI'),
(2, 'Pole Commerciale'),
(3, 'Pole Ressources Humaines'),
(4, 'Pole Financière'),
(6, 'Pole Performance'),
(5, 'POLE EPC'),
(6, 'Pole Operation');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `comment_id` int NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`id`, `name`, `created_at`) VALUES
(1, 'Support Technique', '2025-06-23 11:31:05'),
(2, 'Support Digital Palladium', '2025-06-23 11:31:05');

-- --------------------------------------------------------

--
-- Structure de la table `specifications`
--

CREATE TABLE `specifications` (
  `id` int NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `service_id` int DEFAULT NULL,
  `budget_estimation` decimal(10,2) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  `version` varchar(50) NOT NULL DEFAULT '1.0',
  `status` enum('Brouillon','En revue','Approuvé','Archivé') NOT NULL DEFAULT 'Brouillon',
  `content` longtext,
  `created_by` int NOT NULL,
  `last_modified_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `priority` enum('Basse','Moyenne','Haute','Urgente') NOT NULL DEFAULT 'Moyenne'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `specifications`
--

INSERT INTO `specifications` (`id`, `project_name`, `client_name`, `service_id`, `budget_estimation`, `service`, `version`, `status`, `content`, `created_by`, `last_modified_by`, `created_at`, `updated_at`, `priority`) VALUES
(16, 'EVOLUTION ZEUS', 'POLE PERFORMANCE', 2, NULL, '', 'V 1.5', 'Approuvé', '<figure class=\"table\"><table><thead><tr><th>1. Contexte du projet</th></tr></thead><tbody><tr><td>Faire évoluer l’outil Zeus pour permettre d’implémenter le nouveau système de rémunération de nos CRCD</td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>2. Objectifs</th></tr></thead><tbody><tr><td><ol><li><strong>Génération Automatique ID ZEUS : </strong>Nous souhaitons que les ID Zeus soient générés automatiquement pour chaque<br>nouvelle ressources dès la création</li><li><strong>Une nouvelle façon de calculer le temps à payer pour nos collaborateurs : </strong>Nous souhaitons que le temps a payer de nos collaborateurs soit adossé à leur planification et non au nombre de jours ouvrés sur la période</li><li><strong>Une nouvelle façon les primes à la performance de nos CRCD :&nbsp;</strong></li></ol></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>3. Exigences fonctionnelles</th></tr></thead><tbody><tr><td><p><strong>Objectif 1 : </strong>Nous souhaitons que l’ID Zeus soit généré automatiquement pour toute nouvelle ressource. Nous souhaitons que l’ID Zeus apparaissent pour l’ensemble de nos reportings à la maille agents.</p><p><strong>Objectif 2 : </strong>Nous ne souhaitons plus renseigner le nombre de jour ouvrés à l’ouverture d’une période. Cette information n’interviendra pour la paie de nos CRCD. Nous souhaitons <i><strong>injecter les heures travaillées et les heures planifiées</strong></i> dans Zeus. C’est 2 éléments permettrons de dégager un nouvel indicateur que nous appellerons %Temps à payer. Cet indicateur sera le ratio entre le temps travaillé et le temps planifié de de chaque CRCD sur la période.</p><p><strong>Objectif 3: </strong>Nous souhaitons basé notre méthode de calcul de prime à la performance sur 3 indicateurs: <i><strong>Un taux d’absentéisme, une note pondérée quantitative et une note pondérée qualitative. </strong></i>Pour chacune des 2 notes pondérées un taux d’atteinte devra être calculé. Les paliers de déclenchement de seront fonction des taux d’atteintes. Le Taux d’absentéisme interviendra uniquement en malus avec des paliers prédéfinis</p></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>4. Exigences techniques</th></tr></thead><tbody><tr><td>&nbsp;</td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>5. SUIVI DU PROJET</th></tr></thead><tbody><tr><td><p><i>Présentez un calendrier prévisionnel des grandes phases du projet (jalons, livrables).</i></p><figure class=\"table\"><table><tbody><tr><th>Meeting</th><th>Participants</th><th>Orde du jour</th><th>Next Steps</th></tr><tr><th>28/05/2025</th><th>James, Rodolphine, Anselme, Mickael</th><th>Présentation des éléments fixes qui ont été mise à jour dans ZEUS.&nbsp;</th><th>&nbsp;</th></tr><tr><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th></tr></tbody></table></figure></td></tr></tbody></table></figure><p>&nbsp;</p>', 12, 10, '2025-07-02 11:55:16', '2025-08-01 09:50:09', 'Haute'),
(17, 'Application E-learning', 'AAIM', 2, 0.00, NULL, 'V 1.1', 'Brouillon', '<figure class=\"table\"><table><thead><tr><th>1. Contexte du projet</th></tr></thead><tbody><tr><td><i>Décrivez ici le contexte général, le marché, la concurrence, et la raison d\'être de ce projet.</i></td></tr></tbody></table></figure><p>L’e-learning est une solution d’apprentissage en ligne organisée à distance sur le Web. Les apprenants peuvent accéder à<br>des modules pédagogiques qui se présentent sous la forme de textes, de vidéos ou d’animations, et ainsi apprendre à leur<br>rythme quand leur agenda le permet. Ces modules sont associés à des tests (questionnaires, quiz, jeux éducatifs) pour<br>l’évaluation et la mesure des progrès réalisés au fil du temps. Ces formations à distance sont de nos jours très répandues et<br>utilisées dans des domaines fort variés.</p><figure class=\"table\"><table><thead><tr><th>2. Objectifs</th></tr></thead><tbody><tr><td><p><i>Listez les objectifs principaux et secondaires du projet (SMART : Spécifiques, Mesurables, Atteignables, Réalistes, Temporellement définis).</i></p><ul><li>Objectif 1...</li><li>Objectif 2...</li><li>Objectif 3...</li></ul></td></tr></tbody></table></figure><p>Pour l’apprenant :&nbsp;</p><p> Flexibilité dans l’apprentissage ;&nbsp;</p><p> Partage autonomie ;&nbsp;</p><p> Acteur de sa propre formation ;&nbsp;</p><p> Auto évaluation, pré et post formation.</p><p>Pour les formateurs :&nbsp;</p><p> Pré requis pour l’évaluation du niveau des apprenants ;&nbsp;</p><p> Tracking et suivi en temps réel ;&nbsp;</p><p> Existence d’une base de données de formation ;&nbsp;</p><p> Flexibilité des horaires.</p><figure class=\"table\"><table><thead><tr><th>3. Exigences fonctionnelles</th></tr></thead><tbody><tr><td><p><i>Détaillez ici toutes les fonctionnalités attendues du point de vue de l\'utilisateur.</i></p><p><strong>Exemple : Gestion des utilisateurs</strong></p><ul><li>L\'administrateur peut créer, modifier, et supprimer des comptes utilisateurs.</li><li>L\'utilisateur peut réinitialiser son mot de passe.</li></ul></td></tr></tbody></table></figure><p>Gestion des cours</p><p>&nbsp;Gestion de parcours</p><p>Gestion des classes virtuelles&nbsp;</p><p>Gestion des apprenants&nbsp;</p><p>Gestion des accompagnements</p><figure class=\"table\"><table><tbody><tr><td>Gérer la structure pédagogique des apprentissages</td></tr><tr><td>Créer les séances</td></tr><tr><td>Créer les modules, en utilisant les outils auteurs éventuellement proposés par la plate-forme</td></tr><tr><td>Créer les étapes ou activités</td></tr><tr><td>Inscrire un apprenant sur un parcours</td></tr><tr><td>Définir une classe virtuelle (groupe, session)</td></tr><tr><td>Définir une session spécifique (individuelle)</td></tr><tr><td>Gérer la communication entre apprenants et tuteurs</td></tr><tr><td>Créer un contenu pédagogique</td></tr><tr><td>Créer le parcours pédagogique</td></tr><tr><td>Créer les étapes d\'apprentissage d\'un parcours</td></tr><tr><td>Créer et/ou importer des ressources didactiques</td></tr><tr><td>Associer les étapes aux ressources</td></tr><tr><td>Communiquer avec les apprenants via les outils de communication et les outils collaboratifs</td></tr><tr><td>Créer des évaluations</td></tr><tr><td>Dérouler un cours</td></tr><tr><td>Envoyer un message via les outils synchrones et asynchrones</td></tr><tr><td>Stocker un document (upload)</td></tr><tr><td>Extraire un document (download)</td></tr><tr><td>Lire et écrire un message dans un dialogue en ligne</td></tr><tr><td>Télécharger une ressource</td></tr><tr><td>Afficher une ressource</td></tr><tr><td>Demander un rendez-vous avec un tuteur</td></tr><tr><td>Réaliser une évaluation</td></tr><tr><td>Consulter le résultat d\'une évaluation</td></tr><tr><td>Calculer un score</td></tr><tr><td>Imprimer une ressource</td></tr><tr><td>Visualiser le suivi d\'un parcours</td></tr><tr><td>Voir / gérer son agenda privé</td></tr><tr><td>Déposer des travaux</td></tr><tr><td>Partager des informations</td></tr><tr><td>Poser des questions à la communauté</td></tr><tr><td>Envoyer un message</td></tr><tr><td>Stocker un document (upload)</td></tr><tr><td>Extraire un document (download)</td></tr><tr><td>Télécharger une ressource</td></tr><tr><td>Inscrire un apprenant sur un parcours</td></tr><tr><td>Planifier un rendez-vous, répondre à une demande de rendez-vous</td></tr><tr><td>Afficher une ressource</td></tr><tr><td>Vérifier une évaluation</td></tr><tr><td>Consulter le résultat d\'une évaluation</td></tr><tr><td>Suivre le parcours de l\'apprenant</td></tr><tr><td>Poster des annonces</td></tr><tr><td>Programmer des événements via les agendas</td></tr><tr><td>Animer un wiki, un forum, un réseau social</td></tr></tbody></table></figure><figure class=\"table\"><table><thead><tr><th>4. Exigences techniques</th></tr></thead><tbody><tr><td><p><i>Spécifiez les contraintes techniques, les technologies à utiliser, les performances attendues, la sécurité, etc.</i></p><ul><li><strong>Hébergement :</strong> Serveur dédié Linux (Debian)</li><li><strong>Langages :</strong> PHP 8.1, JavaScript (ES6)</li><li><strong>Base de données :</strong> MySQL 8.0</li><li><strong>Sécurité :</strong> Connexion HTTPS (SSL), protection contre les injections SQL et XSS.</li></ul></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>5. Planning</th></tr></thead><tbody><tr><td><p><i>Présentez un calendrier prévisionnel des grandes phases du projet (jalons, livrables).</i></p><figure class=\"table\"><table><tbody><tr><th>Phase</th><th>Livrable</th><th>Date de fin estimée</th></tr><tr><th>Phase 1 : Conception</th><th>Maquettes validées</th><th>11/07/2025</th></tr><tr><th>Phase 2 : Développement</th><th>Version Bêta</th><th>18/07/2024</th></tr></tbody></table></figure></td></tr></tbody></table></figure><p>&nbsp;</p>', 14, 14, '2025-07-02 17:22:17', '2025-07-03 14:05:20', 'Moyenne'),
(18, 'Plateforme newsletter : La Voix du Contact', 'Pôle Communication Interne et Culture d\'Entreprise', 2, NULL, NULL, 'V 1.1', 'Brouillon', '<figure class=\"table\"><table><thead><tr><th>1. Contexte du projet</th></tr></thead><tbody><tr><td>Dans le cadre de sa stratégie de communication interne et d\'engagement collaborateur,<br>MEDIA CONTACT souhaite déployer une plateforme newsletter interactive.</td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>2. Objectifs</th></tr></thead><tbody><tr><td>❖ Informer régulièrement l’ensemble des collaborateurs<br>❖ Mettre en avant les bonnes pratiques, talents, projets internes<br>❖ Donner la parole aux équipes (témoignages, interviews, initiatives)<br>❖ Valoriser la culture de performance et d’écoute client<br>❖ Consolider le sentiment d’appartenance à l’entreprise</td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>3. Exigences fonctionnelles</th></tr></thead><tbody><tr><td><p>1) Conception et gestion des newsletters<br>❖ Interface d’édition intuitive (glisser-déposer, modèles, images)<br>❖ Nécessité d’avoir un large choix de police et de taille. (Sans oublier l’ensemble<br>des outils de mis en forme textuelle)<br>❖ Possibilité d\'intégrer :<br>• Articles (formats courts ou longs)<br>• Témoignages, interviews, portraits<br>• Vidéos, visuels, sondages interactifs<br>• Rubriques personnalisables<br>❖ Planification des envois</p><p>2) Diffusion multicanale<br>❖ Envoi automatique aux emails professionnels<br>❖ Intégration possible l’intranet<br>❖ Génération de lien partageable ou QR Code<br>❖ Option de version imprimable (PDF ou autre)</p><p><br>3) Gestion des destinataires<br>❖ Import automatisé des listes<br>❖ Segmentation des publics (direction, production, RH, etc.)<br>❖ Possibilité de personnalisation par site ou service</p><p><br>4) Sécurité et conformité<br>❖ Authentification restreinte (accès réservé aux collaborateurs)<br>❖ Hébergement sécurisé (, confidentialité des données)<br>❖ Protection contre la diffusion externe non autorisée</p><p><br>5) Statistiques &amp; reporting<br>❖ Taux d’ouverture, clics, articles les plus lus<br>❖ Feedbacks internes (commentaires, likes, réactions)<br>❖ Tableaux de bord accessibles par l’équipe Communication</p><p><br>6) Créer l’appel a l’action<br>❖ « Envoyez vos idées à la com »<br>❖ « Contactez-nous pour proposer un sujet »</p><p>&nbsp;</p></td></tr><tr><td><p><strong>Exemple : Contenus types de la newsletter « La Voix du Contact » modulable</strong></p><p>❖ Mot de la Direction<br>❖ Actus internes : projets, chiffres clés, challenges, annonces internes, recrutement<br>❖ Talents/ service ou équipe en lumière : portraits de collaborateurs, promotions<br>❖ La minute Qualité / Voix du Client<br>❖ Focus site ou métier<br>❖ Culture d’entreprise et valeurs<br>❖ Espace sondage ou quiz<br>❖ Calendrier des événements internes : Afterwork, formations, team building<br>❖ Infos importantes : Changements d’organisation, alertes RH<br>❖ Conseil pro / Motivation : Citation, Tips RH, Tips bien-être au travail</p></td></tr></tbody></table></figure><figure class=\"table\"><table><thead><tr><th>5. Planning</th></tr></thead><tbody><tr><td><p><i>Présentez un calendrier prévisionnel des grandes phases du projet (jalons, livrables).</i></p><figure class=\"table\"><table><tbody><tr><th>Phase</th><th>Livrable</th><th>Date de fin estimée</th></tr><tr><th>Phase 1 : Conception</th><th>Maquettes validées</th><th>JJ/MM/AAAA</th></tr><tr><th>Phase 2 : Développement</th><th>Version Bêta</th><th>JJ/MM/AAAA</th></tr></tbody></table></figure></td></tr></tbody></table></figure><p>&nbsp;</p>', 10, 10, '2025-07-15 12:49:23', '2025-07-15 15:56:31', 'Moyenne'),
(20, 'REMONTEES CLIENTS', 'DIRECTION EXPERIENCE CLIENT ET PROJETS', 2, NULL, NULL, 'V 1.2', 'Brouillon', '<figure class=\"table\"><table><thead><tr><th>1. Contexte du projet</th></tr></thead><tbody><tr><td><p>La restructuration de la direction qualité et expérience client ayant entraîné la mise en place du pôle parcours et voix du client a permis de déceler un besoin d’automatisation de données incluant toutes remontées du donneur d’ordres et des enquêtes ou sondages. Dans le cadre de cette démarche d’amélioration continue et de pilotage de l’expérience client, nous souhaitons digitaliser et automatiser les flux de données issus :&nbsp;</p><ul><li><strong>Des remontées clients et donneurs d’ordres (par mail, appels, formulaires…),</strong></li></ul><p>&nbsp;</p><ul><li><strong>Des enquêtes et sondages menés par la Direction Qualité et Expérience Client,</strong></li></ul><p>&nbsp;</p><ul><li><strong>Et des observations des parcours clients sur les différents canaux.</strong></li></ul><p>&nbsp;</p><p>L’objectif est de structurer un référentiel unique de la Voix du Client, de fiabiliser l’analyse et d’optimiser les boucles d’amélioration.<i>Décrivez ici le contexte général, le marché, la concurrence, et la raison d\'être de ce projet.</i></p></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>2. Objectifs</th></tr></thead><tbody><tr><td><p><i>Listez les objectifs principaux et secondaires du projet (SMART : Spécifiques, Mesurables, Atteignables, Réalistes, Temporellement définis).</i></p><ul><li>Objectif 1...Centraliser toutes les données VoC issues de sources internes et externes.</li><li>Objectif 2...Automatiser la collecte, le tri, la catégorisation et le classement des données.</li><li>Objectif 3...Fournir des dashboards dynamiques pour le pilotage qualité et l’expérience client.</li><li>Objectif 4.. Permettre un suivi temps réel des irritants récurrents, suggestions et signaux faibles.</li><li>Objectif 5...Mettre en place des alertes automatiques et des workflows d’escalade.</li><li>Objectif 6…Accompagner en temps réel les filiales</li><li>Objectif 7…Avoir un outil assez collaboratif pour faciliter le travail en équipe</li><li>Objectif 8…Garantir l’efficacité et la cohérence des systèmes utilisés pour collecter les données</li></ul></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>3. Exigences fonctionnelles</th><th>Sources de données à intégrer :</th><th>&nbsp;</th></tr></thead><tbody><tr><td><strong>Origine</strong></td><td><strong>Type de données</strong></td><td><strong>Fréquence</strong></td></tr><tr><td>Donneurs d’ordre/ Responsable de compte</td><td>Réclamations, feedbacks directs</td><td>Journalier</td></tr><tr><td>Enquêtes de satisfaction</td><td>NPS, CES, CSAT, verbatim</td><td>Hebdo / mensuel</td></tr><tr><td>Canaux vocaux / e-mails</td><td>Appels, e-mails entrants / sortants</td><td>Temps réel</td></tr><tr><td>Observations terrain / agents parcours</td><td>Suivi qualitatif, indicateurs émotionnels</td><td>Hebdo</td></tr></tbody></table></figure><p><strong>Fonctions attendues :</strong></p><p>•&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Extraction automatique via API, fichiers plats, formulaires web</p><p>•&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Catégorisation automatique par type, canal, motif (via règles ou IA)</p><p>•&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Interface de pilotage des feedbacks avec filtres multicritères</p><p>•&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Génération automatique de tableaux de bord&nbsp;</p><p>•&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Workflow d’escalade automatique selon criticité</p><p>•&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Archivage, recherche et historisation des cas</p><p>•&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Intégration possible avec le CRM ou ERP existant</p><p><strong>Activité et Normes</strong></p><ul><li>Permettre de mettre en ligne les différentes remontées DO par filiale</li><li>Permettre de créer des enquêtes et/ou sondages</li><li>Créer les différents indicateurs (CSAT-DSAT- NPS etc., ...)</li><li>Analyser les données des indicateurs</li><li>Créer un espace remontées DO</li><li>Permettre de créer un espace par filiale et par DO dans chaque filiale</li><li>Permettre de catégoriser les remontées par direction dans chaque filiale après traitement global</li><li>Permettre de définir des SLA de traitement des remontées par catégories de remontées et par direction</li><li>Permettre de faire le suivi des remontées au travers des statuts</li><li>Permettre d’alerter sur les remontées dont le SLA veut être atteint : monitoring des SLA</li><li>Possibilité d’avoir un tableau synthèse des remontées de toutes les filiales et de leurs directions respectives ; de leurs statuts et de leurs alertes</li><li>Associer les indicateurs aux fins de mesure et d’impact</li><li>Possibilité de ranger les remontées par mois et par année ; tout ceci par filiale et par direction</li><li>Possibilité d’avoir de graphes à partir des remontées</li><li>Possibilité d’archiver les retours sur les remontées par mois, par année, par filiale et par direction</li><li>Possibilité d’extraire les résultats en format Word, Excel ou PDF</li></ul><p><strong>Créer des enquêtes et ou sondages&nbsp;</strong></p><ul><li>Possibilité de créer d’enquête(s) ou de sondage(s) à l’image de google forms</li><li>Possibilité d’ajouter ou de ne pas ajouter les adresses mails des répondants</li><li>Possibilité de disposer de deux profils : le 1er profil peut enregistrer les préoccupations DO en précisant les sources puis avoir la possibilité de consulter toutes les autres pages auxquelles il aura droit ; le 2nd profil aura tous les droits : enregistrement-consultation-modification et édition.</li><li>Possibilité d’avoir l’extraction des résultats d’enquête ou de sondage par filiale, par groupe de filiales ou pour toutes les filiales ensemble et qu’elle soit accompagnée de graphes</li><li>Possibilité d’ajouter de question(s) à l’enquête ou au sondage mis en ligne&nbsp;</li><li>Possibilité de corriger une erreur dans une question en ligne</li><li>Possibilité de ranger les enquêtes ou sondages par mois et par année ; tout ceci par filiale et par direction</li><li>Possibilité d’archiver les rapports des enquêtes ou sondages par mois, par année et par filiale au besoin</li><li>Possibilité de suivre les recommandations par filiale et par direction avec des SLA</li><li>Possibilité d’extraire les résultats en format Word, Excel ou PDF</li></ul><p><strong>Créer les différents indicateurs (CSAT-DSAT- NPS etc., ...)</strong></p><ul><li>Possibilité de créer une liaison entre l’ERP et cet outil d’automatisation de données afin de rendre disponible les indicateurs créés</li><li>Possibilité d’afficher les résultats par filiales et par programmes par mois, trimestre, semestre et par an</li><li>Possibilité d’afficher les raisons d’insatisfaction, de satisfaction, de notes attribuées et de raisons d’appels</li><li>Possibilité d’avoir le top 5 et 10 des raisons d’insatisfaction, de satisfaction, de notes attribuées et de raisons d’appels</li><li>Possibilité d’associer à ces raisons d’insatisfaction et de faibles notes attribuées, l’identité des CRCD, TL et leur programme</li><li>Possibilité d’extraire les résultats en format Word, Excel ou PDF</li></ul><p><strong>Analyser les données des indicateurs)</strong></p><ul><li>Possibilité de faire afficher les différents graphes relatifs à un indicateur précis à analyser selon la (es) filiale (s), la période et avec les données déjà disponibles bien sûr</li><li>Possibilité de nettoyer des données : identifier et supprimer les données non pertinentes ou incorrectes dans un ensemble de données&nbsp;</li><li>Possibilité de faire d’analyse prédictive : Prédire les résultats futurs pour repérer les risques et les opportunités. L\'analyse prédictive est basée sur les données historiques, l\'apprentissage automatique et les techniques d\'exploration de données</li><li>Possibilité de faire d’analyse statistique : Collecte d\'échantillons de données afin d\'identifier des modèles et des tendances. Parmi les méthodes d\'analyse statistique figurent la régression, la moyenne et l\'écart-type</li><li>Possibilité de faire d’analyse descriptive : Résumer et organiser des points de données à partir de données quantitatives antérieures (c.-à-d. le \"quoi\"). Vous pouvez démêler ces données non structurées à l\'aide d\'outils d\'analyse de données, tels que l\'outil de statistiques descriptives d\'Excel.</li><li>Possibilité de faire d’analyse diagnostique : Examiner les données de l\'analyse descriptive pour identifier le \"pourquoi\"</li><li>Possibilité de faire d’analyse normative : Trouver le meilleur plan d\'action (c\'est-à-dire le \"comment\") grâce à l\'analyse des données brutes. Cela implique l\'utilisation d\'outils de veille stratégique tels que Tableau.</li><li>Possibilité de faire d’analyse de texte : Extraire des informations d\'un texte (c\'est-à-dire des données qualitatives). Les exemples incluent l\'extraction de phrases clés et l\'analyse des sentiments dans les réponses aux enquêtes et aux questionnaires des clients. Pour rendre cette section encore plus utile, nous demandons à nos clients finaux comme donneurs d’ordres de nous faire part de leurs meilleurs conseils en matière d\'analyse de données.</li><li>Possibilité donnée à visualiser les données de manière à ce qu’elles soient utiles et faciles à comprendre.</li></ul><p><strong>Possibilité de créer des réunions</strong></p><ul><li>Possibilité de détecter la nécessité de réunion sur la base des résultats de NPS- CSAT- DSAT… (liés aux objectifs non atteints)</li><li>Définir la note seuil pour déclencher l\'organisation d\'une réunion de crise (tenir compte du résultat sur la campagne et sur la filiale)</li><li>Faire et archiver les rapports de réunion</li><li>Possibilité de faire le point de réunions tenues par mois, trimestre, semestre, année et par filiale</li><li>Créer un espace PDA et le compartimenter par filiale</li><li>Possibilité de suivre le PDA défini au cours d’une réunion</li></ul><h2><strong>BIBLIOTHEQUE</strong></h2><ul><li>Créer un espace bibliothèque</li><li>Possibilité de compatir la bibliothèque et d’avoir des parties suivantes&nbsp;: guides d’utilisation des applications&nbsp;; process de gestion des enquêtes ou sondages ; rapports d’enquêtes ou de sondages&nbsp;; archives&nbsp;; etc… &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Indicateurs clés de suivi (KPIs)</li></ul><figure class=\"table\"><table><tbody><tr><td><strong>Domaine</strong></td><td><strong>Indicateur</strong></td></tr><tr><td>Réclamations</td><td>% traitées dans le délai / réouvertures / TAT moyen</td></tr><tr><td>Enquêtes</td><td>Taux de réponse / Score NPS / Score CSAT / Evolution mensuelle</td></tr><tr><td>Voix du client</td><td>% de feedbacks par canal / Top 5 irritants / Délai de résolution</td></tr><tr><td>Parcours</td><td>Taux d’abandon / points de friction / % d’actions correctives mises en œuvre</td></tr><tr><td>Pilotage</td><td>Taux d\'automatisation / taux d’alertes traitées / taux de données exploitables</td></tr></tbody></table></figure><figure class=\"table\"><table><thead><tr><th>4. Exigences techniques</th></tr></thead><tbody><tr><td><p><i>Spécifiez les contraintes techniques, les technologies à utiliser, les performances attendues, la sécurité, etc.</i></p><ul><li><strong>Hébergement :</strong> Serveur dédié Linux (Debian)</li><li><strong>Langages :</strong> PHP 8.1, JavaScript (ES6)</li><li><strong>Base de données :</strong> MySQL 8.0</li><li><strong>Sécurité :</strong> Connexion HTTPS (SSL), protection contre les injections SQL et XSS.</li></ul></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>5. Planning</th><th>&nbsp;</th><th>&nbsp;</th></tr></thead><tbody><tr><td><strong>Étape</strong></td><td><strong>Période</strong></td><td><strong>Livrables</strong></td></tr><tr><td>Phase 1 : Diagnostic &amp; cadrage</td><td>Semaine 1-2</td><td>Cartographie des sources, des besoins</td></tr><tr><td>Phase 2 : Spécifications &amp; choix de la solution</td><td>Semaine 3-4</td><td>Spécifications fonctionnelles &amp; techniques</td></tr><tr><td>Phase 3 : Développement &amp; intégration</td><td>Mois 2-3</td><td>Outil paramétré &amp; connecté</td></tr><tr><td>Phase 4 : Tests &amp; corrections</td><td>Mois 4</td><td>Recette, ajustements</td></tr><tr><td>Phase 5 : Déploiement &amp; formation</td><td>Mois 5</td><td>Formation utilisateurs / mise en service</td></tr><tr><td>Phase 6 : Suivi &amp; évolution</td><td>Mois 6+</td><td>Monitoring, évolution continue</td></tr></tbody></table></figure><ol><li>Livrables attendus</li></ol><ul><li>Dossier de cadrage technique</li><li>Plateforme ou module automatisé fonctionnel</li><li>Plan de tests + PV de recette</li><li>Manuel d’utilisation et formation des équipes</li><li>Support post-déploiement (3 à 6 mois minimum)</li></ul>', 16, 16, '2025-07-17 11:31:21', '2025-07-21 18:17:02', 'Haute'),
(21, 'PLATEFORME EPC', 'DIRECTION EXPERIENCE CLIENT ET PROJET', 2, NULL, NULL, 'V 3.1', 'En revue', '<figure class=\"table\"><table><thead><tr><th>1. Contexte du projet</th></tr></thead><tbody><tr><td><p>Notre organisation gère les activités des points de contact (Réception d’appel, Digital, appel sortant, back office..) et s\'appuie sur la norme COPC (Customer Operations Performance Center) pour optimiser ses performances. Actuellement, le processus d\'évaluation de la qualité des interactions client par nos évaluateurs est géré de manière fragmentée (fichiers Excel, documents partagés). Cette approche manuelle génère des incohérences, des erreurs de saisie et rend difficile la consolidation des données ainsi que l\'analyse des performances par filiales et pour tout le groupe.</p><p>&nbsp;</p></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>2. Objectifs</th></tr></thead><tbody><tr><td><p>L\'objectif est de concevoir une plateforme centralisée, et sécurisée, qui permettra de :</p><ul><li>Standardiser le processus d\'évaluation de la qualité des points de contact selon les méthodologies et les métriques de la norme COPC.</li><li>Centraliser toutes les données d\'évaluation pour une vision claire et en temps réel (par canaux, par donneur d’ordre, par filiales et pour tout le groupe).</li><li>Automatiser la production de rapports et de tableaux de bord pour chaque niveau de l\'organisation.</li><li>Faciliter le suivi des actions correctives et l\'amélioration continue de la qualité de service produite.</li><li>Assurer l\'intégrité et la confidentialité des données.</li></ul><p>Les principaux acteurs de la plateforme sont&nbsp;:</p><ul><li><strong>Les Évaluateurs de Point de Contact (EPC) :</strong> Pour la saisie des évaluations et vu générale de ses évaluations et des performances uniquement de sa filiales.</li><li><strong>Les Chefs d\'Équipe &nbsp;(TL) :</strong> Pour suivre les performances de leurs équipes.</li><li><strong>Les Chargées des EPC :</strong> Pour l\'analyse globale et le reporting et la gestion des utilisateurs par filiales et pour tout le groupe.</li><li><strong>Les Administrateurs :</strong> Pour la gestion des utilisateurs et des paramètres de la plateforme.</li></ul></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>3. Exigences fonctionnelles</th></tr></thead><tbody><tr><td><ol><li><strong>Module d\'Évaluation COPC</strong></li></ol><ul><li><strong>Formulaires d\'évaluation :</strong> La plateforme doit permettre d’effectuer les évaluations par canaux, par donneur d’ordre</li><li><strong>Saisie des données :</strong> Interface &nbsp;pour la saisie des évaluations par les évaluateurs.</li><li><strong>Pièces jointes :</strong> Possibilité de lier des enregistrements audio des conversations, des captures d\'écran de chats ou tout autre document justificatif à chaque évaluation.</li><li><strong>Calcul automatique :</strong> Calcul instantané du score de l\'évaluation selon la pondération des critères et des règles COPC</li></ul><p><strong>&nbsp;&nbsp;</strong></p><p><strong>&nbsp; &nbsp;2.Module de Centralisation et de Gestion des Données</strong></p><ul><li><strong>Base de données centralisée :</strong> Stockage sécurisé de toutes les évaluations (par filiale, et du groupe)</li><li><strong>Fonction de recherche :</strong> Recherche multicritère (par évaluateur, agent, date, équipe, KPI COPC).</li><li><strong>Historique :</strong> Accès à l\'historique complet des évaluations pour chaque agent.</li></ul><p><strong>&nbsp; &nbsp;&nbsp;</strong></p><p><strong>&nbsp; &nbsp;3.Module de Reporting et de Tableaux de bord</strong></p><ul><li><strong>Tableaux de bord dynamiques :</strong><ul><li><strong>Pour les Chefs d\'Équipe :</strong> Vue d\'ensemble des performances de leur équipe, scores moyens, agents les plus performants/en difficulté.</li><li><strong>Pour les Chargées des EPC :</strong> Vue globale de la performance par filiale, du groupe, analyse des tendances, identification des critères non respectés (par canaux, par donneur d’ordre et par EPC)</li></ul></li><li><strong>Indicateurs clés de performance (KPI) :</strong> Affichage des indicateurs piloté selon la norme COPC et d\'autres indicateurs personnalisables.</li><li><strong>Génération de rapports :</strong> Possibilité de générer des rapports détaillés exportables aux formats PDF, Excel...</li><li><strong>Filtrage :</strong> Filtres par date, période, équipe, type d\'activité (appels, chat, etc.).</li><li><strong>Cartographie de CRCD:&nbsp;</strong></li></ul><p><strong>&nbsp; &nbsp; 1- Agent TOP: &nbsp;</strong>tout agent ayant100% sur tous les 04 indicateurs&nbsp;</p><p><strong>&nbsp; &nbsp; 2- &nbsp;Agent MIDDLE: &nbsp;</strong> agent ayant100% sur les 03 Erreurs Critiques et un target en Erreur Non critique compris entre 99 et 95%; agent ayant une ou deux erreurs critiques et un target en Erreur Non critique compris entre 99 et 95%</p><p><strong>&nbsp; &nbsp; 3- Agent MIDDLE: &nbsp;</strong> agent ayant plus de deux erreurs critiques avec ou sans erreur non critique; agent n\'ayant pas d\'erreur critique et un targuet d\'erreur non critique inférieur à 95%</p><p><strong>&nbsp;4.Module de Suivi et d\'Actions Correctives</strong></p><ul><li><strong>Feedback :</strong> Fonctionnalité permettant aux évaluateurs de laisser un feedback écrit ou vocal à l\'agent.</li><li><strong>Plans d\'action :</strong> Possibilité de créer des plans d\'action basés sur les résultats des évaluations, avec un suivi de leur progression (assignation, statut, commentaires).</li><li><strong>Notifications :</strong> Système de notifications pour les agents et les managers concernant les nouvelles évaluations ou les actions à mener.</li></ul></td></tr><tr><td><ul><li><i><strong>Suivi du parcours de l\'évaluateur : Permettre aux chargés de suivre le volume des évaluateurs de manière hebdomadaire</strong></i></li><li><i><strong>Interconnexion avec la WFM pour envoie des rapports par agent</strong></i></li><li><i><strong>Bien définir les KPI sur les fiches rapports envoyés aux donneurs d\'ordres (\"Accueillir\" , “comprendre la demande”)</strong></i></li><li><i><strong>La solution doit couvrir l\'ensemble des filiales&nbsp;</strong></i></li></ul></td></tr></tbody></table></figure><figure class=\"table\"><table><thead><tr><th>4. Exigences techniques</th></tr></thead><tbody><tr><td><ul><li><strong>Performance :</strong> La plateforme doit être facile à utiliser, temps de chargement des pages rapide, &nbsp;La plateforme doit pouvoir supporter un nombre élevé d\'utilisateurs simultanés,&nbsp;</li><li><strong>Accessibilité :</strong> La plateforme doit être accessible depuis différents navigateurs et appareils (ordinateurs de bureau, tablettes, mobiles).</li></ul></td></tr></tbody></table></figure><figure class=\"table\"><table><thead><tr><th>5. Existant</th></tr></thead><tbody><tr><td><p>Les données partagées:</p><p>1- Grilles d\'évaluation (RA, EA, Digital, BO)</p><p>2- Calculette COPC</p><p>3- Les différents KPI pilotés et les différentes formules de calculs</p><p>4- Référentiel de coaching</p></td></tr></tbody></table></figure><figure class=\"table\"><table><thead><tr><th>5. Ordonnancement des tâches</th></tr></thead><tbody><tr><td><figure class=\"table\"><table><tbody><tr><td><strong>Tâches</strong></td><td><strong>Durée</strong></td><td><strong>Prédécesseurs</strong></td><td><strong>Successeurs</strong></td></tr><tr><td>Mise à jour de la fonction d\'échantillonnage &nbsp;(A)</td><td>2 jours</td><td>&nbsp;</td><td>C</td></tr><tr><td>Implémentation du suivi des évaluateurs (B)</td><td>2 jours&nbsp;</td><td>C</td><td>D</td></tr><tr><td>Redéfinition des KPI sur les fiches d\'extractions (C)</td><td>2 jours</td><td>A</td><td>B</td></tr><tr><td>Déploiement de la solution dans l\'environnement test (Bénin et CIV) (D)</td><td>2 jours</td><td>B</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table></figure><p>&nbsp;</p></td></tr></tbody></table></figure><figure class=\"table\"><table><thead><tr><th>6. Réseau PERT</th></tr></thead><tbody><tr><td>&nbsp;</td></tr></tbody></table></figure><figure class=\"table\"><table><thead><tr><th>6. Planning</th></tr></thead><tbody><tr><td><p><i>Présentez un calendrier prévisionnel des grandes phases du projet (jalons, livrables).</i></p><figure class=\"table\"><table><tbody><tr><th>Phase</th><th>Livrable</th><th>Date de fin estimée</th></tr><tr><th>Mise à jour de la fonction d\'échantillonnage&nbsp;</th><th>Fonction validée</th><th>02/10/2025</th></tr><tr><th>KPI fiches d\'extractions</th><th>Rapport conforme</th><th>06/10/2025</th></tr><tr><th>Suivi des évaluateurs</th><th>Fonctions validées</th><th>07/10/2025</th></tr><tr><th>Déploiment test</th><th>Version Bêta</th><th>09/10/2025</th></tr></tbody></table></figure></td></tr></tbody></table></figure><p>&nbsp;</p>', 19, 10, '2025-07-17 14:51:21', '2025-10-01 17:36:46', 'Haute'),
(22, 'SIGNATURE ELECTRONIQUE', '', NULL, NULL, NULL, 'V 1.1', 'Brouillon', '<figure class=\"table\"><table><thead><tr><th>1. Contexte du projet</th></tr></thead><tbody><tr><td><p>L’outil de signature électronique sera utilisé essentiellement dans le cadre de la signature des documents/courriers émis par la Direction du Capital Humain, notamment :</p><p>&nbsp;</p><ul><li>-Les contrats et avenants au contrat de travail</li><li>-Les lettres de mission/nomination</li><li>-Ordre de mission</li><li>-Courrier de licenciement</li><li>-Demande d’explication / Sanction</li><li>-Note de service</li></ul></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>2. Objectifs</th></tr></thead><tbody><tr><td><p>&nbsp;</p><ul><li>Dématérialisation des signatures des documents de la DCH au T4 2025&nbsp;</li><li><strong>Réduction des risques légaux : </strong>Signature des contrats dans les délais</li><li><strong>Gain de Temps : </strong>Signature en un clic</li><li><strong>Flexibilité et Accessibilité : </strong>Document dématérialisé que les utilisateurs peuvent signer à tout moment à partir de leur appareil via leur adresse email personnelle ou professionnelle, sans qu’il soit nécessaire d’installer un logiciel ou de s’enregistrer dans une application.&nbsp;</li><li><strong>&nbsp;Impact Environnemental Positif :&nbsp;&nbsp; </strong>Résolution de la problématique liée à la gestion du stock de papier rame + action RSE</li></ul></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th><p>Les documents seront émis exclusivement par la Direction du Capital Humain (interface initiateur).</p><p>L’outil doit permettre aux équipes du Capital Humain :</p><p>-D’envoyer des documents individuels</p><p>-D’effectuer des envois de documents groupés à plusieurs destinataires en simultané</p><p>-De modifier les adresses email des signataires au besoin</p><p>-D’insérer ou modifier des champs directement</p><p>-D’annuler un envoi</p><p>-Faire des rappels aux signataires</p><p>-De collecter les copies signées afin de procéder à leur archivage numérique</p><p>-Gestion multi-signataires&nbsp;</p><p>-Choix de signataire par priorité : signataire 1, signataire 2, etc…</p><p>-Signature au choix : Proposition de signature (initial de nom), insertion de signature ou élaboration de signature dans l’outil</p><p>-Réception de la copie signée dans les adresses email de chaque signataire</p><p>-Visibilité des RH sur le suivi des documents en cours de signature ( en attente de signature, signés, etc…)</p><p>-Dashboard de suivi sur les volumes des documents envoyés, en attente de signature, documents complétés (signés)</p><p>-Réactivation de document envoyé et non signé</p><ul><li>Durée d’archivage 3 mois</li></ul></th></tr></thead><tbody><tr><td>&nbsp;</td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>4. Exigences techniques</th></tr></thead><tbody><tr><td><i>RAS à l\'appréciation du développeur&nbsp;</i></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>5. Planning</th></tr></thead><tbody><tr><td><p><i>Présentez un calendrier prévisionnel des grandes phases du projet (jalons, livrables).</i></p><figure class=\"table\"><table><tbody><tr><th>Phase</th><th>Livrable</th><th>Date de fin estimée</th></tr><tr><th>Phase 1 : Conception</th><th>Maquettes validées</th><th>05/09/2025</th></tr><tr><th>Phase 2 : Développement</th><th>Version Bêta</th><th>1er/10/2025</th></tr></tbody></table></figure></td></tr></tbody></table></figure><p>&nbsp;</p>', 21, 21, '2025-08-10 17:23:40', '2025-08-10 20:52:55', 'Haute'),
(23, 'Interface digitale de gestion des besoins et des tickets d’incident', 'MOYENS GENERAUX', 2, NULL, NULL, 'V 1.0', 'Brouillon', '<figure class=\"table\"><table><thead><tr><th>1. Contexte du projet</th></tr></thead><tbody><tr><td><i>la mise en place d\'une solution digitale pour la gestion des besoins en approvisionnements et des incidents logistique par l’équipe des moyens généraux.</i></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>2. Objectifs</th></tr></thead><tbody><tr><td><p><i>Listez les objectifs principaux et secondaires du projet (SMART : Spécifiques, Mesurables, Atteignables, Réalistes, Temporellement définis</i></p><ul><li>Collecte des besoins: Simplifier la demande d\'approvisionnement en matériels et équipements avec des formulaires structurés et un suivi transparent</li><li>Gestion des incidents: Centraliser la création, le suivi et la résolution des tickets d\'incident avec une traçabilité complète</li><li>Pilotage par la donnée: Générer des reportings détaillés pour optimiser la prise de décision et améliorer les performances</li></ul></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>3. Exigences fonctionnelles</th></tr></thead><tbody><tr><td><h3>Collecte des besoins en approvisionnement</h3><p>Formulaire permettant de saisir :</p><p>Nom du demandeur</p><p>Type de besoin (matériel, équipement, autre)</p><p>Description du besoin</p><p>Priorité (Basse, Moyenne, Élevée)</p><p>Date souhaitée de livraison</p><p>Possibilité d’attacher un fichier (ex : devis, photo).</p><p>Historique des besoins soumis avec statut : <i>Ouvert, En cours, Résolu, Rejeté</i>.</p><h3>Gestion des tickets d’incident</h3><p>Chaque ticket doit contenir les informations suivantes :</p><p>Code ticket (généré automatiquement)</p><p>Date de création</p><p>Nom du demandeur</p><p>Objet de la demande</p><p>Statut du ticket (Ouvert, En cours de traitement, En attente de réponse, Résolu)</p><p>Temps de traitement</p><p>Date d’échéance (Due date)</p><p>Niveau de priorité (Basse, Moyenne, Élevée)</p><p>Possibilité de mise à jour du ticket par les agents (ajout de commentaires, changement de statut).</p><p>Historique visible pour chaque ticket.</p><p><strong>Suivi en temps réel</strong></p><p><strong>Historique complet et transparent de chaque demande :</strong></p><p>Statut du ticket: ouvert, en cours de&nbsp; traitement , résolu, rejeté</p><p>Notification automatique des changement de statut via mail</p><p>Commentaires et échanges avec les équipes</p><h3>Reporting et tableau de bord</h3><p>Statistiques sur une période choisie (par date de début et de fin) :</p><p>Nombre de nouveaux tickets</p><p>Nombre de tickets ouverts</p><p>Nombre de tickets résolus</p><p>Temps moyen de traitement</p><p>Présentation des reporting sous forme de tableau</p><p>Possibilité de générer des graphiques interactifs en barres et camemberts pour les analyses</p></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>4. Exigences techniques</th></tr></thead><tbody><tr><td><p><i>Spécifiez les contraintes techniques, les technologies à utiliser, les performances attendues, la sécurité, etc.</i></p><ul><li><strong>Hébergement :</strong> Serveur dédié Linux (Debian)</li><li><strong>Langages :</strong> PHP 8.1, JavaScript (ES6)</li><li><strong>Base de données :</strong> MySQL 8.0</li><li><strong>Sécurité :</strong> Connexion HTTPS (SSL), protection contre les injections SQL et XSS.</li><li>Sécurité: Authentification robuste par login/mot de passe avec gestion des rôles et permissions</li><li>Génération des rapports sous forme Excel et PDF</li><li>Utilisateurs cibles: Agents demandeurs( Collaborateurs créant des demandes d\'approvisionnement et déclarant des incidents. Interface simplifiée pour une saisie rapide et intuitive); &nbsp;Equipe support: Moyens Généraux traitant les tickets; Managers: Responsables qui consultent les reportings, analysent les tendances et exportent les données pour le pilotage stratégique</li></ul></td></tr></tbody></table></figure><p>&nbsp;</p><figure class=\"table\"><table><thead><tr><th>5. Planning</th></tr></thead><tbody><tr><td><p><i>Présentez un calendrier prévisionnel des grandes phases du projet (jalons, livrables).</i></p><figure class=\"table\"><table><tbody><tr><th>Phase</th><th>Livrable</th><th>Date de fin estimée</th></tr><tr><th>Phase 1 : Conception</th><th>Maquettes validées</th><th>20/10/2025</th></tr><tr><th>Phase 2 : Développement</th><th>Version Bêta</th><th>01/12/2025</th></tr></tbody></table></figure></td></tr></tbody></table></figure><p>&nbsp;</p>', 35, NULL, '2025-09-22 11:07:06', '2025-09-22 11:07:06', 'Haute'),
(24, 'SITE WEB GMC - INFORMATIONS A ACTUALISER', 'POLE COMMUNICATION INTERNE ET CULTURE D\'ENTREPRISE', NULL, NULL, NULL, 'V 1.0', 'Brouillon', '<p>Bonjour Messieurs,<br><br>Prière nous assister à mener les actions qui suivent sur le site web Groupe Media Contact.<br>Adresse&nbsp;: <a href=\"https://groupmediacontact.com/\">https://groupmediacontact.com/</a></p><p>Fenêtre modale&nbsp;: CONTACTEZ-NOUS<br><br>Chemin&nbsp;: CONTACTEZ-NOUS&lt;<strong> Enquêtes de presse</strong></p><ol><li><strong>Elément à supprimer</strong>&nbsp;:</li></ol><p><strong>Marius OGOUDEDJI</strong></p><p><i>Responsable Communication</i></p><p>+229 66 26 00 10<br>+229 95 17 00 16</p><p><a href=\"mailto:mogoudedji@benin.groupmediacontact.com\">mogoudedji@benin.groupmediacontact.com</a></p><p>&nbsp;</p><p><strong>2. Elément à modifier&nbsp;:</strong></p><p>Chemin&nbsp;: CONTACTEZ-NOUS&lt;<strong> Demandes de recrutement</strong></p><p>&nbsp;</p><p>Ancien contenu</p><p>+229 95 17 00 16</p><p><a href=\"mailto:recrutement@groupmediacontact.com\">recrutement@groupmediacontact.com</a></p><p><br><strong>Contenu actualisé</strong><br>+229 <strong>01</strong> 95 17 00 16</p><p><a href=\"mailto:recrutement@groupmediacontact.com\">recrutement@groupmediacontact.com</a></p><p>&nbsp;</p><p>---</p><p>Prière nous assister pour la suppression des éléments à supprimer sur toutes les fenêtres modales sur lesquelles elles seraient présentes.<br><br>Merci pour votre accompagnement.<br><br><strong>POLE CICE</strong></p>', 13, NULL, '2025-12-01 14:33:01', '2025-12-01 14:33:01', 'Haute');

-- --------------------------------------------------------

--
-- Structure de la table `specification_history`
--

CREATE TABLE `specification_history` (
  `id` int NOT NULL,
  `specification_id` int NOT NULL,
  `version` varchar(50) NOT NULL,
  `changed_by` int NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changes_summary` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `specification_history`
--

INSERT INTO `specification_history` (`id`, `specification_id`, `version`, `changed_by`, `changed_at`, `changes_summary`) VALUES
(102, 16, 'V 1.0', 1, '2025-07-02 11:55:16', 'Création du document.'),
(103, 17, 'V 1.0', 14, '2025-07-02 17:22:17', 'Création du document.'),
(104, 16, 'V 1.1', 10, '2025-07-02 17:40:43', 'Contenu principal modifié.'),
(105, 16, 'V 1.2', 10, '2025-07-02 17:48:52', 'Contenu principal modifié.'),
(106, 17, 'V 1.1', 14, '2025-07-03 14:05:20', 'Sauvegarde sans changement notable.'),
(107, 18, 'V 1.0', 10, '2025-07-15 12:49:23', 'Création du document.'),
(108, 18, 'V 1.1', 10, '2025-07-15 15:56:31', 'Contenu principal modifié.'),
(111, 20, 'V 1.0', 16, '2025-07-17 11:31:21', 'Création du document.'),
(112, 20, 'V 1.1', 16, '2025-07-17 12:18:56', 'Nom du projet mis à jour.'),
(113, 21, 'V 1.0', 19, '2025-07-17 14:51:21', 'Création du document.'),
(114, 21, 'V 1.1', 19, '2025-07-17 15:09:23', 'Contenu principal modifié.'),
(115, 21, 'V 1.2', 19, '2025-07-17 15:48:58', 'Contenu principal modifié.'),
(116, 21, 'V 1.3', 19, '2025-07-17 16:01:10', 'Contenu principal modifié.'),
(117, 16, 'V 1.3', 10, '2025-07-21 09:56:05', 'Statut changé de \'Brouillon\' à \'En revue\'.'),
(118, 21, 'V 1.4', 10, '2025-07-21 09:58:46', 'Sauvegarde sans changement notable.'),
(119, 21, 'V 1.5', 19, '2025-07-21 12:09:29', 'Contenu principal modifié.'),
(120, 21, 'V 1.6', 19, '2025-07-21 12:10:52', 'Sauvegarde sans changement notable.'),
(121, 21, 'V 1.7', 1, '2025-07-21 13:20:57', 'Contenu principal modifié.'),
(122, 21, 'V 1.8', 19, '2025-07-21 15:28:56', 'Contenu principal modifié.'),
(123, 21, 'V 1.9', 19, '2025-07-21 15:34:26', 'Contenu principal modifié.'),
(124, 21, 'V 2.0', 19, '2025-07-21 15:42:59', 'Contenu principal modifié.'),
(125, 21, 'V 2.1', 19, '2025-07-21 15:51:22', 'Contenu principal modifié.'),
(126, 21, 'V 2.2', 19, '2025-07-21 15:56:13', 'Contenu principal modifié.'),
(127, 20, 'V 1.2', 16, '2025-07-21 18:17:02', 'Sauvegarde sans changement notable.'),
(128, 21, 'V 2.3', 19, '2025-07-22 09:30:51', 'Sauvegarde sans changement notable.'),
(129, 21, 'V 2.4', 19, '2025-07-23 10:26:26', 'Sauvegarde sans changement notable.'),
(130, 21, 'V 2.5', 19, '2025-07-24 10:33:26', 'Contenu principal modifié.'),
(131, 21, 'V 2.6', 10, '2025-07-28 10:00:30', 'Statut changé de \'Brouillon\' à \'En revue\'.'),
(132, 21, 'V 2.7', 19, '2025-07-28 15:21:08', 'Contenu principal modifié.'),
(133, 16, 'V 1.4', 10, '2025-07-28 16:55:50', 'Contenu principal modifié.'),
(134, 16, 'V 1.5', 10, '2025-08-01 09:50:09', 'Statut changé de \'En revue\' à \'Approuvé\'.'),
(135, 21, 'V 2.8', 10, '2025-08-05 11:54:58', 'Sauvegarde sans changement notable.'),
(136, 22, 'V 1.0', 21, '2025-08-10 17:23:40', 'Création du document.'),
(137, 22, 'V 1.1', 21, '2025-08-10 20:52:55', 'Sauvegarde sans changement notable.'),
(138, 23, 'V 1.0', 35, '2025-09-22 11:07:06', 'Création du document.'),
(139, 21, 'V 2.9', 10, '2025-09-30 17:24:29', 'Contenu principal modifié.'),
(140, 21, 'V 3.0', 10, '2025-09-30 19:06:34', 'Contenu principal modifié.'),
(141, 21, 'V 3.1', 10, '2025-10-01 17:36:46', 'Contenu principal modifié.'),
(142, 24, 'V 1.0', 13, '2025-12-01 14:33:01', 'Création du document.');

-- --------------------------------------------------------

--
-- Structure de la table `specification_stakeholders`
--

CREATE TABLE `specification_stakeholders` (
  `id` int NOT NULL,
  `specification_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `specification_stakeholders`
--

INSERT INTO `specification_stakeholders` (`id`, `specification_id`, `user_id`) VALUES
(338, 16, 1),
(340, 16, 10),
(339, 16, 11),
(256, 17, 1),
(259, 17, 10),
(257, 17, 11),
(258, 17, 15),
(357, 21, 1),
(361, 21, 10),
(358, 21, 16),
(360, 21, 19),
(359, 21, 20),
(346, 23, 10);

-- --------------------------------------------------------

--
-- Structure de la table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `ticket_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `status` enum('À faire','En cours','En attente','Terminé','Annulé') DEFAULT 'À faire',
  `priority` enum('Basse','Normale','Haute','Urgente') DEFAULT 'Normale',
  `assigned_to` int NOT NULL,
  `created_by` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `specification_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tasks`
--

INSERT INTO `tasks` (`id`, `ticket_id`, `title`, `description`, `status`, `priority`, `assigned_to`, `created_by`, `created_at`, `updated_at`, `due_date`, `completed_at`, `specification_id`) VALUES
(9, NULL, 'Ajouter champs BDD + modèle Planning', 'Ajouter champs BDD + modèle Planning', 'Terminé', 'Haute', 1, 1, '2025-07-02 14:01:57', '2025-07-04 04:32:29', '2025-07-02', NULL, 16),
(10, NULL, 'Modifier validation_heure()', 'Modifier validation_heure()', 'Terminé', 'Haute', 1, 1, '2025-07-02 14:03:02', '2025-07-04 04:32:56', '2025-07-03', NULL, 16),
(11, NULL, 'Tests avec données réelles', 'Tests avec données réelles', 'Terminé', 'Haute', 1, 1, '2025-07-02 14:06:03', '2025-08-01 11:49:44', '2025-07-03', NULL, 16),
(12, NULL, 'Adapter le calcul du salaire', 'Adapter le calcul du salaire', 'Terminé', 'Haute', 1, 1, '2025-07-02 14:06:59', '2025-07-04 20:54:41', '2025-07-05', NULL, 16),
(13, NULL, 'Mettre en place l’algorithme d’attribution des matricules pour chaque filiale.', '...', 'Terminé', 'Haute', 11, 11, '2025-07-02 20:27:31', '2025-07-02 20:36:39', '2025-07-02', NULL, 16),
(14, NULL, 'Attribuer un matricule unique aux employés dépourvus de matricule.', 'Cette fonctionnalité permet d’attribuer un matricule unique uniquement aux utilisateurs qui n’en possèdent pas encore. Elle garantit que chaque utilisateur dispose d’une identification unique et cohérente dans le système, tout en évitant la duplication pour ceux qui ont déjà un matricule.', 'Terminé', 'Haute', 11, 11, '2025-07-02 20:34:00', '2025-07-03 22:12:43', '2025-07-04', NULL, 16),
(15, NULL, 'Automatiser l’attribution de matricules lors de l’enregistrement des nouveaux employés.', 'Mettre en place pour chaque filiale un mécanisme automatique qui génère et attribue un matricule unique à chaque nouvel employé lors de son enregistrement dans le système.', 'Terminé', 'Haute', 11, 11, '2025-07-02 20:41:34', '2025-07-07 12:10:16', '2025-07-04', NULL, 16),
(16, NULL, 'Implémenter %ratio temps à payer et prise en compte dans le calcul du salaire.', 'Implémenter le pourcentage de ratio du temps à payer pour chaque agent. Ce ratio est ensuite intégré dans le calcul du salaire afin d’assurer une rémunération proportionnelle au temps réellement payé.', 'Terminé', 'Haute', 11, 11, '2025-07-07 12:27:37', '2025-07-09 17:44:13', '2025-07-08', NULL, 16),
(17, NULL, 'Ajout du taux d’absentéisme, note pondérée quantitative et note pondérée qualitative.', '', 'Terminé', 'Haute', 11, 11, '2025-07-09 17:38:29', '2025-07-11 21:39:36', '2025-07-10', NULL, 16),
(18, NULL, 'Définir et appliquer les critères de prime de performance propres à chaque campagne de MCB.', '', 'Terminé', 'Haute', 11, 11, '2025-07-11 21:38:37', '2025-07-17 11:06:02', '2025-07-15', NULL, 16),
(20, 49, 'Analyse et Conception', 'Audit des besoins RH : Définir les règles de calcul (heures normales, supplémentaires, nuit/week-end)\r\n\r\nAnalyse technique des badgeuses : Protocole de communication, format des données, fréquence\r\n\r\nModélisation des données : Schéma DB + relations entre employés/badges/paie\r\n\r\nDesign API : Spécification OpenAPI/Swagger', 'À faire', 'Normale', 1, 1, '2025-07-21 13:46:24', '2025-07-21 13:46:24', '2025-07-23', NULL, NULL),
(21, 49, 'Développement Backend', 'Setup projet : Initialisation du framework (Spring Boot/Django/etc.)\r\n\r\nCréation des modèles :\r\n\r\nEmployé\r\n\r\nBadge (entrée/sortie)\r\n\r\nPériode de paie\r\n\r\nHeuresCalculées\r\n\r\nImplémentation des endpoints :\r\n\r\n/badges (POST/GET)\r\n\r\n/calcul/{periode} (POST)\r\n\r\n/export-paie/{periode} (GET)\r\n\r\nLogique métier :\r\n\r\nAlgorithme de matching entrées/sorties\r\n\r\nGestion des cas edge (oubli de badge, doublons)\r\n\r\nCalcul des heures supp (avec règles spécifiques)', 'À faire', 'Normale', 1, 1, '2025-07-21 13:47:40', '2025-07-21 13:47:40', '2025-07-28', NULL, NULL),
(22, 49, 'Intégration Badgeuses', 'Connecteur :\r\n\r\nAPI directe (si disponible)\r\n\r\nImport CSV/Excel (Plan B)\r\n\r\nScript d\'extraction BDD (si accès possible)\r\n\r\nSynchronisation automatique :\r\n\r\nJob quotidien/hebdomadaire\r\n\r\nSystème de retry en cas d\'échec', 'À faire', 'Normale', 1, 1, '2025-07-21 13:48:38', '2025-07-21 13:48:38', '2025-07-31', NULL, NULL),
(23, 52, 'MAJ application extraction', 'Refonte et ciblagle des requetes sql', 'À faire', 'Normale', 1, 1, '2025-07-24 18:13:32', '2025-07-24 18:13:32', '2025-07-25', NULL, NULL),
(24, NULL, 'PLAN DE REFONTE DE L\'INTERFACE EPC', '- [x] Appliquer le nouveau style d\'en-tête aux pages de gestion des grilles.\r\n- [x] Appliquer le nouveau style d\'en-tête à la page d\'ajout d\'échantillonnage.\r\n- [x] Appliquer le nouveau style d\'en-tête à la page des rapports.\r\n- [x] Modifier la page de création d\'évaluation pour :\r\n  - [x] Afficher le champ Chef d\'équipe en premier (avec filtrage par site).\r\n  - [x] Mettre à jour dynamiquement la liste des agents selon le chef choisi.\r\n  - [x] Afficher ensuite la sélection de la grille.\r\n- [x] Déboguer et fiabiliser la récupération dynamique des agents (analyse des logs si besoin).\r\n  - [x] Corriger le formulaire et la logique de création d\'équipe pour utiliser l\'id_employe comme id_chef_equipe (formulaire, JS, modèle).\r\n  - [x] Vérifier que les IDs proposés dans le formulaire correspondent bien à des id_employe existants (table employes).\r\n- [x] Revoir et fiabiliser l\'UX du formulaire `/epc/new` : ordre des champs, filtrage dynamique des chefs d\'équipe par site, mise à jour dynamique des agents selon le chef d\'équipe sélectionné.\r\n- [x] Analyser la base SQL (gmc_core_connect.sql) pour comprendre l\'origine de l\'erreur de contrainte étrangère lors de la création d\'une équipe.\r\n- [x] Fiabiliser l\'attribution d\'un agent à une équipe (contrôle de l\'id_equipe transmis, validation côté serveur et formulaire, correction du flux si besoin).\r\n- [x] Ajouter les champs manquants sur le formulaire `/epc/new` : Type d\'évaluation, Motif de l\'appel / CC, Motif réel, et calcul automatique du statut_cc (ANCIEN/NOUVEAU selon l\'ancienneté de l\'agent).\r\n- [x] Créer la colonne SQL `statut_cc` dans la table `qualite_evaluations`.\r\n- [x] Récupérer automatiquement la période en cours et le nom du chef d\'équipe dans le formulaire d\'évaluation.\r\n- [x] Afficher le statut CC de l\'agent dans la liste et les détails des évaluations.\r\n- [x] Implémenter la liste déroulante (GLOBAL/CAMPAGNE) pour la colonne `designation` dans `qualite_echantillonnage_calculs` et adapter la logique de répartition des résultats selon le site (automatique/manuelle).\r\n  - [x] Créer/améliorer la table SQL `qualite_echantillonnage_calculs` (ajout fk_periode)\r\n  - [x] Intégrer la liste déroulante dans l\'interface et la logique\r\n- [x] Adapter la logique de répartition GLOBAL pour qu\'elle ne concerne que les évaluateurs du site de la règle (et non tous les sites).\r\n- [x] Corriger le formulaire d\'ajout de règle d\'échantillonnage (affichage des erreurs, validation, UX, aide sur la précision).\r\n- [x] Valider la logique sur l\'interface et en test utilisateur.', 'Terminé', 'Haute', 1, 1, '2025-07-28 13:09:08', '2025-07-28 13:09:08', '2025-07-24', NULL, 21),
(25, NULL, 'RESTRUCTURATION DE LA GRILLE SELON LA NORME EPC', '- [x] Vérifier la structure des tables (grilles/items) dans la base de données\r\n  - [x] Ajouter les méthodes nécessaires dans le modèle EpcModel.php\r\n  - [x] Ajouter la méthode grille_view($id) dans le contrôleur Epc.php\r\n  - [x] Créer la vue app/views/epc/grille_view.php\r\n  - [x] Gérer l\'ajout/suppression d\'items\r\n  - [x] Gérer la modification d\'items', 'Terminé', 'Haute', 1, 1, '2025-07-28 13:16:53', '2025-07-28 13:16:53', '2025-07-25', NULL, 21),
(26, NULL, 'IMPLEMENTATION VUE TEAM LEADER ET AGENT', '✅ Affichage du suivi d\'échantillonnage EPC\r\n✅ Correction des équipes pour Team Leaders\r\n✅ Pourcentages par section sur la page détails\r\n✅ Page des évaluations pour Team Leaders\r\n✅ Navigation adaptative avec boutons retour\r\n✅ Permissions d\'accès corrigées pour Team Leaders\r\n✅ Rechercher les endroits où la table `qualite_evaluation_details` est manipulée dans le code.\r\n✅ Adapter la méthode `saveEvaluationDetails` pour convertir les valeurs textuelles en binaire (1/0).\r\n✅ Adapter l\'affichage dans `details.php` pour afficher des badges \"Conforme\"/\"Non-conforme\" selon la valeur binaire.\r\n✅ Vérifier le traitement des données lors de la soumission d\'une évaluation (contrôleur, formulaire, etc.) afin de garantir que seules les valeurs binaires sont transmises au modèle.\r\n✅ Implémenter la synthèse des scores par section et l\'enregistrement dans `qualite_evaluation_section_scores`\r\n✅ Mettre à jour/réviser les vues pour cohérence binaire (si besoin)\r\n✅ Ajouter une pagination à la page de liste des évaluations d\'équipe\r\n✅ Implémenter un filtrage par période/date sur la liste des évaluations (agents et Team Leaders)\r\n✅ Créer le filtrage et l\'affichage pour les agents\r\n✅ Ajouter le filtrage pour les Team Leaders\r\n✅ Ajouter un graphique d\'évolution des performances de l\'agent (graphe linéaire) en bas de la page agent_evaluations', 'Terminé', 'Haute', 1, 1, '2025-07-28 13:38:22', '2025-08-05 13:48:11', '2025-07-31', NULL, 21);

-- --------------------------------------------------------

--
-- Structure de la table `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `uploaded_by` int NOT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `templates`
--

CREATE TABLE `templates` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `templates`
--

INSERT INTO `templates` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Modèle Standard', 'Un modèle de base pour les projets web et logiciels.', '2025-06-30 19:06:33', '2025-06-30 19:06:33');

-- --------------------------------------------------------

--
-- Structure de la table `template_sections`
--

CREATE TABLE `template_sections` (
  `id` int NOT NULL,
  `template_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `template_sections`
--

INSERT INTO `template_sections` (`id`, `template_id`, `title`, `content`, `display_order`, `created_at`) VALUES
(1, 1, 'Contexte du projet', '<table style=\"width: 100%; border-collapse: collapse; margin-bottom: 20px; font-family: Arial, sans-serif; border: 1px solid #ddd;\"><thead><tr><th style=\"background-color: #f2f2f2; color: #333; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 18px;\">1. Contexte du projet</th></tr></thead><tbody><tr><td style=\"padding: 12px; vertical-align: top;\"><p><em>Décrivez ici le contexte général, le marché, la concurrence, et la raison d\'être de ce projet.</em></p></td></tr></tbody></table>', 1, '2025-06-30 19:06:33'),
(2, 1, 'Objectifs', '<table style=\"width: 100%; border-collapse: collapse; margin-bottom: 20px; font-family: Arial, sans-serif; border: 1px solid #ddd;\"><thead><tr><th style=\"background-color: #f2f2f2; color: #333; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 18px;\">2. Objectifs</th></tr></thead><tbody><tr><td style=\"padding: 12px; vertical-align: top;\"><p><em>Listez les objectifs principaux et secondaires du projet (SMART : Spécifiques, Mesurables, Atteignables, Réalistes, Temporellement définis).</em></p><ul><li>Objectif 1...</li><li>Objectif 2...</li><li>Objectif 3...</li></ul></td></tr></tbody></table>', 2, '2025-06-30 19:06:33'),
(3, 1, 'Exigences fonctionnelles', '<table style=\"width: 100%; border-collapse: collapse; margin-bottom: 20px; font-family: Arial, sans-serif; border: 1px solid #ddd;\"><thead><tr><th style=\"background-color: #f2f2f2; color: #333; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 18px;\">3. Exigences fonctionnelles</th></tr></thead><tbody><tr><td style=\"padding: 12px; vertical-align: top;\"><p><em>Détaillez ici toutes les fonctionnalités attendues du point de vue de l\'utilisateur.</em></p><p><strong>Exemple : Gestion des utilisateurs</strong></p><ul><li>L\'administrateur peut créer, modifier, et supprimer des comptes utilisateurs.</li><li>L\'utilisateur peut réinitialiser son mot de passe.</li></ul></td></tr></tbody></table>', 3, '2025-06-30 19:06:33'),
(4, 1, 'Exigences techniques', '<table style=\"width: 100%; border-collapse: collapse; margin-bottom: 20px; font-family: Arial, sans-serif; border: 1px solid #ddd;\"><thead><tr><th style=\"background-color: #f2f2f2; color: #333; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 18px;\">4. Exigences techniques</th></tr></thead><tbody><tr><td style=\"padding: 12px; vertical-align: top;\"><p><em>Spécifiez les contraintes techniques, les technologies à utiliser, les performances attendues, la sécurité, etc.</em></p><ul><li><strong>Hébergement :</strong> Serveur dédié Linux (Debian)</li><li><strong>Langages :</strong> PHP 8.1, JavaScript (ES6)</li><li><strong>Base de données :</strong> MySQL 8.0</li><li><strong>Sécurité :</strong> Connexion HTTPS (SSL), protection contre les injections SQL et XSS.</li></ul></td></tr></tbody></table>', 4, '2025-06-30 19:06:33'),
(5, 1, 'Planning', '<table style=\"width: 100%; border-collapse: collapse; margin-bottom: 20px; font-family: Arial, sans-serif; border: 1px solid #ddd;\"><thead><tr><th style=\"background-color: #f2f2f2; color: #333; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 18px;\">5. Planning</th></tr></thead><tbody><tr><td style=\"padding: 12px; vertical-align: top;\"><p><em>Présentez un calendrier prévisionnel des grandes phases du projet (jalons, livrables).</em></p><table style=\"width: 100%; border-collapse: collapse;\"><tr style=\"background-color: #fafafa;\"><th style=\"padding: 8px; border: 1px solid #e0e0e0; text-align: left;\">Phase</th><th style=\"padding: 8px; border: 1px solid #e0e0e0; text-align: left;\">Livrable</th><th style=\"padding: 8px; border: 1px solid #e0e0e0; text-align: left;\">Date de fin estimée</th></tr><tr><td style=\"padding: 8px; border: 1px solid #e0e0e0;\">Phase 1 : Conception</td><td style=\"padding: 8px; border: 1px solid #e0e0e0;\">Maquettes validées</td><td style=\"padding: 8px; border: 1px solid #e0e0e0;\">JJ/MM/AAAA</td></tr><tr><td style=\"padding: 8px; border: 1px solid #e0e0e0;\">Phase 2 : Développement</td><td style=\"padding: 8px; border: 1px solid #e0e0e0;\">Version Bêta</td><td style=\"padding: 8px; border: 1px solid #e0e0e0;\">JJ/MM/AAAA</td></tr></table></td></tr></tbody></table>', 5, '2025-06-30 19:06:33');

-- --------------------------------------------------------

--
-- Structure de la table `tickets`
--

CREATE TABLE `tickets` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Nouveau','Ouvert','En cours','Fermé','En attente','Résolu') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ouvert',
  `priority` enum('Basse','Moyenne','Haute','Urgente') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Moyenne',
  `created_by_id` int NOT NULL,
  `assigned_to_id` int DEFAULT NULL,
  `country_id` int DEFAULT NULL,
  `service_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `closed_at` datetime DEFAULT NULL,
  `type_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tickets`
--

INSERT INTO `tickets` (`id`, `title`, `description`, `status`, `priority`, `created_by_id`, `assigned_to_id`, `country_id`, `service_id`, `created_at`, `updated_at`, `closed_at`, `type_id`) VALUES
(45, 'IVR CM: Parser les réponses SOAP en JSON', 'IVR CM', 'Résolu', 'Urgente', 11, 11, 13, 2, '2025-07-17 11:58:10', '2025-07-31 12:57:55', '2025-07-18 13:25:43', 4),
(48, 'DEMANDE DE MISE EN PLACE DE PLATE FORME DE TIRAGE SQL', 'Bonjour, nous avons besoin de votre assistance s\'il vous plait pour la mise en place d\'une plate forme sur notre site qui permettra aux teams leaders de faire des extractions depuis le serveur SQL.\r\n\r\nEn effet, ils ont constamment besoin des informations qui s\'y trouve et à des heures n\'étant pas très souvent en harmonie avec la planification de la DSI et étant donné que c\'est un ticket qui revient beaucoup l\'idéal est de les rendre autonomes sur ce plan.', 'En attente', 'Haute', 22, 1, 19, 2, '2025-07-21 13:06:02', '2025-09-03 17:35:31', NULL, 3),
(49, 'BADGEUSE', 'POUR LES BESOIN DES RESSOURCE HUMAINE NOUS AVONS BESOIN DE METTRE EN PLACE UNE API CAPABE DE RECUPERER LES DONNEES DES BADGEUSES POUR ENSUITE EFFECTUER LE CALCUL DES HEURES DE PREENSE POUR LA PAYE', 'En cours', 'Moyenne', 23, 1, 10, 2, '2025-07-21 13:38:56', '2025-08-08 10:23:08', NULL, 3),
(50, 'BADGEUSES CONGO', 'Bonjour,\r\nJe viens a travers ce mail solliciter une séance de travail avec vous, selon votre disponibilité, concernant nos différentes badgeuses déjà interconnectées en réseau.\r\nL’objectif est de mettre en place une interface dédiée permettant au service des ressources humaines d’extraire les données de manière autonome, sans nécessiter l’intervention de la DSI.', 'Nouveau', 'Haute', 26, NULL, 20, 2, '2025-07-22 09:34:31', '2025-07-22 09:34:31', NULL, 3),
(51, 'CREATION USERS PORTAIL APPLICATION', 'Ton appui pour la création des accès au portail intranet.\r\nTu trouveras ci-dessous la liste des ressources concernées :\r\nKOUNLE Emma \r\nMAMA A. Anliyat\r\nMOHAMED Abdou Fataou\r\nMOUMOUNI Nassirou Anassa\r\nADEOTI Hadiyyah\r\n\r\nOKE-VE Hillary\r\nOKOYA Denise Roberte \r\nSOTON Gilles-Christ \r\nTCHEGNON Sidoine   \r\nTELLA Séra \r\nZAMBA Damien\r\nFADEKON Kékéli Gloria\r\nGBETO Pélagie\r\nGADABOU Espoir\r\nd\'OLIVEIRA Anabelle\r\nFATON Félicité\r\nGBAGUIDI Sedami Emmanuella\r\nHOUNSOU Ella\r\nHOUNVIO Charbel\r\nSEWADE Samuel', 'Résolu', 'Urgente', 27, 11, 13, 2, '2025-07-23 18:09:52', '2025-07-31 12:45:31', '2025-07-31 12:45:31', 4),
(52, 'DBCALL CENTER , APPELS REPETES, FCR', 'Impossibilité d\'extraction du dbcall center , appels répétés , fcr suite au basculement sur Hermes 360 au niveau de MCCI', 'Résolu', 'Urgente', 28, 1, 13, 2, '2025-07-24 13:09:38', '2025-07-25 12:52:51', '2025-07-25 12:52:51', 3),
(53, 'plate forme zeus enregistrements des employés', 'sur zueus Cameroun il n\'y a pas des informations sur les agents en formation\r\n\r\n\r\nbesoin de votre assistance svp', 'Nouveau', 'Haute', 29, NULL, 19, 1, '2025-07-25 14:36:15', '2025-07-25 14:36:15', NULL, NULL),
(54, 'Demande d\'accès Zeus', 'Demande d\'accès Zeus pour GNANGNE AYOU MARIE-HELENE (mgnangne@groupmediacontact.com) et BOHOUSSOU RITA (rbohoussou@@groupmediacontact.com) des filiales du Congo et des deux Guinées', 'Résolu', 'Urgente', 21, 1, 10, 2, '2025-07-30 14:05:52', '2025-08-01 12:24:48', '2025-08-01 12:24:25', 4),
(55, 'AJOUT ONGLET', 'Les collaborateurs Ulrich Judicael GBODOGBE & Aldo Precieux PEDRO n\'ont pas l\'onglet OBJECTIFGMC au niveau de leurs accès intranet.', 'Nouveau', 'Haute', 28, NULL, 13, 2, '2025-07-30 16:11:03', '2025-07-30 16:11:03', NULL, 4),
(56, 'souci d\'enregistrement des sanction', 'lorsque je finis de renseigné les informations de l\'employé j\'enregistre mais les éléments enregistrés ne se voient pas sur la liste de sanction.\r\nvous verrez en attache les captures de la page qui s\'affiche directement lorsque j\'enregistre également vous verrez la page de la liste de sanction après enregistrement', 'Nouveau', 'Haute', 29, NULL, 19, 1, '2025-08-06 11:52:15', '2025-08-06 11:52:15', NULL, NULL),
(57, 'Dysfonctionnement dbcallcenter', 'Nous n\'arrivons pas à avoir accès aux éléments du dbcallcenter ni pour le mois de juillet ni pour le mois d\'aout', 'Résolu', 'Urgente', 30, 1, 13, 2, '2025-08-06 11:56:48', '2025-08-13 14:27:47', '2025-08-13 14:27:47', 2),
(58, 'DEMANDE DE FORMATION PUTIL PAIE', 'Bonjour,\r\n\r\nNous souhaitons une formation relative au traitement des salaires dans Zeus avec le nouveau modèle sur le Cameroun et la CIV.\r\n\r\nUne seule séance avec l\'ensemble des acteurs afin de démarrer la paie dès le lundi 18 dans l\'application.', 'Fermé', 'Urgente', 21, 1, 10, 2, '2025-08-08 13:17:33', '2025-08-13 14:27:25', '2025-08-13 14:27:25', 4),
(59, 'Dysfonctionnement DBCALLCENTER', 'Bonjour Team,\r\nNous n\'arrivons pas à avoir les données du dbcall juillet et Août 2025.\r\nNous avons constamment le pression du client pour les données à leur mettre à disposition.\r\nMerci', 'Fermé', 'Urgente', 31, 1, 13, 2, '2025-08-08 13:35:08', '2025-08-13 14:26:53', '2025-08-13 14:26:53', 2),
(60, 'BASE DE CONNAISSANCE AVEC IA', 'Bonjour Team,\r\nJe viens solliciter votre soutien afin de nous accompagner dans la réalisation de ce projet en référence aux mails joints à ce ticket.\r\nNous avons collecté les foires aux questions de chaque filiale, qui serviront de base de connaissance pour l\'outil. Je vous prie de bien vouloir prendre le relais pour la finalisation de ce projet svp. La demande avait été exprimée le 14/03/2025.\r\nAfin de nous permettre de suivre efficacement l’évolution des différentes étapes, vous voudrez bien nous aider à renseigner la fiche \"SUIVI DASH PROJET\" ci-jointe, en y indiquant les tâches à accomplir ainsi que le nombre de jours alloués à chaque tâche (Feuille data. Fichier qui sera envoyé dans un autre (ticket).\r\nDans l\'attente de votre retour.\r\n\r\nCdt,', 'Nouveau', 'Haute', 31, NULL, 13, 2, '2025-08-08 14:27:10', '2025-08-08 14:27:10', NULL, 3),
(61, 'COMPLEMENTS D\'ELEMENTS OUTILS IA', 'Bonsoir Team,\r\nEn référence au ticket #60. \r\nJe partage avec vous les mails envoyés dans le cadre de la demande de réalisation du projet IA.\r\nCdt,', 'Nouveau', 'Haute', 31, NULL, 13, 2, '2025-08-08 14:40:06', '2025-08-12 17:57:04', NULL, 3),
(62, 'COMPLEMENTS D\'ELEMENTS OUTILS IA', 'Bonjour Team,\r\nJe partage avec vous la fiche SUIVI PROJET que vous allez nous aider à renseigner afin de suivre le projet svp.\r\nCdt,', 'Nouveau', 'Haute', 31, NULL, 13, 2, '2025-08-08 14:53:28', '2025-08-12 17:57:39', NULL, 3),
(63, 'dysfonctionnement application objectifgmc', 'Bonjour \r\nd\'une part nous n\'arrivons pas a validé les objectif au niveau de l\'application d\'autre part  nous ne voyons pas les objectifs assignés.\r\nnous sommes proches des deadlines fixé par le capital humain. votre accompagnement diligeante sera apprécié.\r\nsalutation', 'Résolu', 'Urgente', 32, 1, NULL, 2, '2025-08-08 15:34:34', '2025-08-12 18:13:30', '2025-08-12 18:13:30', 2),
(64, 'dysfonctionnement API cameroun', 'Bonjour \r\nnous observons un dysfonctionnement des api au niveau de l\'ivr sur le Cameroun ; nous n\'avons pas  les retours sms et code puk pour ne citer que cela', 'Nouveau', 'Urgente', 32, NULL, NULL, 2, '2025-08-08 15:37:46', '2025-08-08 15:37:46', NULL, 2),
(65, 'Dbcall pour le client sur la plateforme 2 benin', 'Bonjour team support \r\n\r\nsuite a la migration de la production sur la plateforme 2  les configurations necessaire pour l\'extraction du dbcall n\'ont pas été effectué. et le client donneur d\'ordre ne parvient pas a faire les extraction ainsi que les équipe de la production. Comptant sur votre diligence afin de clôturé ce ticket ouvert par le donneur d\'ordre nous restons en attente.\r\n\r\nSalutations', 'Résolu', 'Urgente', 32, 1, NULL, 2, '2025-08-11 17:46:45', '2025-08-13 14:26:28', '2025-08-13 14:26:28', 4),
(66, 'suivi des absence et congé', 'nous souhaitons avoir des ajouts dans zeus\r\npour le suivi des absences:\r\n- absence pour sanction\r\n- permission légal\r\n- permission non légal\r\n\r\npour les congés:\r\n- faire-part\r\n- acte de mariage\r\n\r\nmerci...', 'Fermé', 'Haute', 29, NULL, 19, 1, '2025-08-14 12:35:35', '2025-09-05 11:08:45', '2025-09-05 11:08:45', NULL),
(67, 'la suppression', 'je n\'arrive pas a supprimé des données dans zeus est-il possible de me donner la possibilité de svp\r\n\r\nexemple: la suppression d\'un contrat mal renseigné svp\r\n\r\nMerci', 'Fermé', 'Moyenne', 29, NULL, 19, 1, '2025-08-14 13:19:53', '2025-09-05 11:08:20', '2025-09-05 11:08:20', NULL),
(68, 'Demande d\'accès Zeus', 'Pouvez vous svp donner accès aux onglets Paie à Grâce PEHOU dans Zeus.\r\n\r\nMail : gpehou@groupmediacontact.com', 'Résolu', 'Urgente', 21, 1, 10, 2, '2025-08-18 20:03:13', '2025-08-18 20:06:18', '2025-08-18 20:06:18', 4),
(69, 'MISE A JOUR FORMULE PAIE CAMEROUN DANS ZEUS', 'Hello la team très forte.\r\nJe sollicite auprès de vous pour demain une séance de travail d\'1H avec le HRBP et le Financier du Cameroun afin de nous assurer que les formules de Zeus sont correctes.', 'Résolu', 'Urgente', 21, NULL, 10, 2, '2025-08-18 21:32:01', '2025-08-21 12:23:06', '2025-08-21 12:23:06', 3),
(70, 'intégration et validation des effectifs dans les éléments de la paie', 'depuis le 18 aout 2025 nous essayons d\'effectuer une intégration des ressources sur les différents  segments de la paie nous recevons un message d\'erreur. \r\nje vous prie de regarder en attache la capture d\'écran', 'Fermé', 'Urgente', 29, NULL, 19, 1, '2025-08-19 20:32:48', '2025-08-21 13:28:54', '2025-08-21 13:28:54', NULL),
(71, 'EXCEPTION COTISATION CMU', 'Nous avons des exceptions pour le calcul de Zeus.\r\n6 collaborateurs', 'Résolu', 'Urgente', 21, 1, 10, 2, '2025-08-20 21:42:50', '2025-09-03 20:01:53', '2025-09-03 20:01:53', 3),
(72, 'ftp files reporting', 'bonjour team \r\nsuite au mail de serge yovo , je formalise par ce ticket. le souci dois surement comme pour le dbcall etre lié au changement de plateforme si vous pouvez nous aider a corriger cela car le client est un peu tendu.  anselme avait mis en place ce systeme automatique en son temps pour rendre fluide la mise a disposition du reporting au client a un certain timing sans dependre de la prod qui le faisait manuellement\r\nmerci d\'avance pour la diligence', 'Résolu', 'Urgente', 32, 1, NULL, 2, '2025-08-22 13:43:19', '2025-09-03 20:02:11', '2025-09-03 20:02:11', 2),
(73, 'CREATION D\'UNE TABLE DE DONNEE SQL', 'Bonsoir, nous avons besoin de votre assistance pour la création d\'une table de données sur la SQL', 'Résolu', 'Urgente', 22, NULL, 19, 2, '2025-09-08 17:59:08', '2025-09-26 18:45:00', '2025-09-26 18:45:00', 3),
(74, 'MISE EN PLACE INTERFACE DBCALL POUR LES MANAGERS', 'Bonsoir, nous avons besoin s\'il vous plait de votre assistance pour qu\'une interface soit mise à la disposition des teams leaders pour qu\'ils puissent extraire les dbcall par eux mêmes.', 'En attente', 'Moyenne', 22, NULL, 19, 2, '2025-09-08 18:00:57', '2025-09-29 11:01:53', NULL, 3),
(75, 'Implémentation du calcul du solde de tout compte dans Zeus au Bénin', 'Dans le cadre de l’optimisation des processus RH, la Direction du Capital Humain (DCH) en support avec la FINANCE souhaite mettre à jour ZEUS RH (application de gestion des ressources humaines) afin d’automatiser le calcul et le traitement du solde de tout compte des collaborateurs sortants.\r\nEn somme, lors du calcul des salaires, nous souhaitons intégrer la fonctionnalité pour le calcul automatique du solde de tout compte.\r\nNous avons joint le fichier actuel que nous utilisons pour calculer le solde de tout compte pour une meilleure compréhension.', 'Nouveau', 'Moyenne', 33, NULL, 20, 2, '2025-09-11 18:55:30', '2025-09-11 18:55:30', NULL, 3),
(76, 'souci d\'intégration des agents dans les éléments de paye', 'nous n\'arrivons pas a faire l\'intégration du personnel administratif depuis ce matin et ceci nous ralenti dans le traitement svp', 'Résolu', 'Urgente', 29, NULL, 19, 1, '2025-09-18 16:52:14', '2025-09-26 18:44:24', '2025-09-26 18:44:20', NULL),
(77, 'DEMANDE DE CREATION DES ACCES A ZEUS SUR TOUTES LES FILIALES  A  L \'AUDIT', 'L\'audit a besoin d\'avoir la possibilité de faire directement ses extractions de ZEUS pour mettre en oeuvre ses controles quelque soit le pays\r\nVoir en piece jointe les demande de création validé par le DCH', 'Résolu', 'Moyenne', 34, 1, 13, 2, '2025-09-19 17:58:59', '2025-10-20 12:09:09', '2025-10-20 12:09:09', 3),
(78, 'titre', '*', 'Fermé', 'Haute', 35, 1, 13, 2, '2025-09-22 10:51:05', '2025-09-26 18:42:44', '2025-09-26 18:42:44', 3),
(79, 'LIMITATION D\'APPEL', 'BESOIN D\'IMPLEMENTER L\'EXTRACTION DES LIMITATIONS D\'APPEL SUR HERMES 360 COMME ON L\'AVAIT SUR LA V5', 'Résolu', 'Haute', 23, 1, 10, 2, '2025-09-25 14:43:26', '2025-09-25 14:48:00', '2025-09-25 14:48:00', 3),
(80, 'IMPLETENTATION INTERFACE SMS', 'Nous sollicitons votre appuis sur le besoin  du DO a envoyer des sms au clients apres  interactions avec les agents. nous sommes disponible pour un meet afin de s\'aligner sur la démarche à suivre.', 'Nouveau', 'Urgente', 23, NULL, 10, 2, '2025-09-30 17:20:50', '2025-09-30 17:20:50', NULL, 3),
(81, 'ETAT BANQUE \"ZEUS MCCI & HOOPE CI)', 'Y\'ello\r\nNous souhaitons revoir l\'extraction des états banque et le rendre conformes au format en pièces jointes( ce qui implique une revu de la partie de RIBs à l\'insertion de chaque ressource)', 'Résolu', 'Urgente', 36, 1, NULL, 2, '2025-10-07 17:33:47', '2025-10-14 14:52:38', '2025-10-14 14:52:01', 3),
(82, 'besoin d\'assistance integration IA', 'Bonjour Jean marcel \r\n\r\nDans le cadre du projet IA nous aurons besoin de votre assistance. Demain a 14H d\'Abidjan  soit 15h a Cotonou , pour plus d\'éclaircissement vous êtes convié a une reunion avec le prestataire.', 'Fermé', 'Haute', 32, 1, NULL, 2, '2025-10-13 19:07:15', '2025-10-20 12:08:45', '2025-10-20 12:08:45', 4),
(83, 'Demande d\'accès Zeus', 'Pouvez-vous svp donner un accès Zeus au nouveau coordinateur RH pour les filiales suivantes :\r\n- Congo\r\n- Guinée Conakry\r\n- Guinée Bissau \r\n\r\nSon adresse : rmabayamene@groupmediacontact.com', 'Résolu', 'Urgente', 21, 1, 10, 2, '2025-10-17 15:30:32', '2025-10-20 11:51:27', '2025-10-20 11:51:27', 4),
(84, 'configuration api MCCI sur le serveur du benin', 'Bonjour jean marcel \r\ndans le cadre du bcp nous avons recu une nouvelle orientation de la direction générale en attendant que les souci du cloud ne soit resolu. c\'est pourquoi nous te sollicitons pour la configuration des api de MCCI sur le serveur du Benin qui servira de redondance. je reste disponible ceci doit etre effectif dans les meilleurs delais merci d\'avance pour l\'accompagnement', 'Nouveau', 'Haute', 32, NULL, NULL, 2, '2025-10-28 15:16:24', '2025-10-28 15:16:24', NULL, 4),
(85, 'Accès Lecteur RH du Congo et des Deux Guinées', 'Hello Team,\r\n\r\nPouvez-vous svp donner accès au lecteur RH à mon collaborateur NORTHON dans le cadre de l\'exercice de ses fonctions.', 'Nouveau', 'Urgente', 21, NULL, 10, 1, '2025-10-30 16:11:59', '2025-10-30 16:11:59', NULL, NULL),
(86, 'INDISPONIBILITE DONNEES APPELS REPETES/DBCALL', 'Bonjour à tous,\r\n\r\nJe viens par ce mail vous informer de l\'indisponibilité des données \"Appels répétés\" dans le dbcall.\r\n\r\nBien vouloir nous aider à les avoir pour les besoins du DO MTN svp.\r\n\r\nVous trouverez ci-joint à titre d\'exemple la capture.\r\n\r\n\r\n\r\nCdt,', 'Résolu', 'Urgente', 31, 1, 13, 2, '2025-10-31 13:36:27', '2025-11-12 18:14:17', '2025-11-12 18:14:17', 2),
(87, 'Création d\'accès', 'Bonsoir Team,\r\nNous venons par ce ticket solliciter votre support afin de nous créer l\'accès à l\'outils \"ObjectifGMC\"  pour le pôle GESTION DES COMPTES.\r\nCi-dessous les noms pour la création d\'accès.\r\n\r\n- Gisèle AKPAMOLI \r\n- John Mery KITIHOUN \r\n- Serge YOVO-AYI\r\n\r\nCdt,', 'Résolu', 'Urgente', 31, 1, 13, 2, '2025-11-10 18:03:29', '2025-11-12 18:14:30', '2025-11-12 18:14:30', 4),
(88, 'Paramétrage prélèvement caisse social Zeus', 'Pouvez-vous svp nous aider à automatiser le prélèvement à la source des collaborateurs dans le cadre de la caisse sociale.', 'Résolu', 'Haute', 21, 1, 10, 2, '2025-11-12 18:09:21', '2025-11-19 11:02:45', '2025-11-19 11:02:45', 3),
(89, 'DETAIL CNPS (ZEUS  MCCI & HOOPE-CI)', 'Hello Team PALLADIUM\r\n\r\nVotre support pour la mise à jour du fichier DETAIL CNPS conformément au fichier qui sera joint...\r\n\r\nToutes les ressources figurant sur le livre de paie doivent s\'y trouver avec leurs informations.\r\n\r\nles colonnes déjà remplies resteront telle.', 'Nouveau', 'Haute', 36, NULL, NULL, 2, '2025-11-17 23:08:55', '2025-11-17 23:08:55', NULL, 3),
(90, 'MISE A JOUR BULLETIN DE SALAIRE MCCI & HOOPE AFRICA', 'Livre de paie \r\n\r\n     . Dissocier le salaire de base et le sursalaire comme le prévoit les grilles insérées dans Zeus\r\n\r\n    . Supprimer la ligne salaire brut social\r\n\r\n    . Rajouter à l\'affichage le salaire brut non imposable\r\n\r\n    . Modifier le montant de la masse salariale :  qui est égal au salaire brut non imposable + l\'ensemble des cotisations patronales\r\n\r\n   \r\n\r\n2- Bulletin de salaire\r\n\r\n     . supprimer les rubriques : Contribution nationale, Impôt général sur revenu\r\n\r\n     . La rubrique Impôt sur salaire à maintenir et doit être égale au Total RITS', 'Résolu', 'Haute', 21, 1, 10, 2, '2025-11-18 13:31:02', '2025-11-26 15:43:42', '2025-11-26 15:43:42', 3),
(91, 'CREATION DE BOUCLE DE MAIL : INTERIM AGENCE & DIGITAL CIV', 'Pouvez-vous svp nous aider à créer une boucle de mail unique avec l\'ensemble des mails ci-dessous :\r\nKOUDA.KOUADIO@mtn.com; maimouna.bouare@mtn.com; GHISLAIN.KOUADIO@mtn.com; MLAINZI.LIZADE@mtn.com; LINDA.BADOU@mtn.com; Toure.Abdoul@mtn.com; Ibrahim.Toure2@mtn.com; MARIAM.KEITA@mtn.com; AMICHIA AFFIBA [ MTNCI - Temporaire ] ;AFFIBA.AMICHIA@mtn.com;Ibrahim.Toure2@mtn.com ; Emmanuel.Ncho@mtn.com ;EMMANUEL.ALLA@mtn.com ;Gnakale.Sidibe1@mtn ;BENEDICTE.SAPIM@mtn.com;Amenan.Kouakou1@mtn.com;Julias.Djatte@mtn.com;Hermann.DOUO@mtn.com;DELAFOSSE.AMANY@mtn.com;becho.yapo@mtn.com;Serge.Touha@mtn.com;Edmond.OUATTARA2@mtn.com;Martial.Kouakou@mtn.com;Mariama.Diallo2@mtn.com', 'Nouveau', 'Haute', 21, NULL, 10, 1, '2025-11-18 18:01:43', '2025-11-18 18:01:43', NULL, NULL),
(92, 'ASSISTANT DONNEE IVR ( fichier de codage)', 'Bonjour @tmarcel@hoope-africa.com, @mpadonou@palladium-tech.com\r\n\r\nNous sollicitons votre assistance concernant les données IVR envoyées au client par le biais du fichier de codage que vous nous aviez réaliser par le passé. \r\nEn effet comme vous pouvez le voie en attaché plus bas le client a remarqué une incohérence entre deux données. SVI_555_FR_PUK et SVI_555_FR_PUK_OK\r\n \r\n\r\n\r\nApres analyse de notre coté nous soupçonnons une erreur dans le fichier de codage qui a entrainée à ce résultat soit une erreur au niveau des requêtes devant donner les résultats.\r\nMerci de nous aider', 'Résolu', 'Urgente', 24, 1, 10, 2, '2025-11-19 15:18:36', '2026-01-07 16:59:36', '2026-01-07 16:59:36', 4),
(93, 'Mise à jour livre de paie MCCI', 'Le livre de paie extrait depuis Zeus sur la Côte d\'ivoire ne ressort pas la colonne Catégrorie/Campagne comme nous l\'avons sur le Congo. Nous sollicitons l\'intégration de cette colonne sur la Côte d\'ivoire.', 'Nouveau', 'Haute', 33, NULL, 20, 2, '2025-12-01 09:31:39', '2025-12-01 09:31:39', NULL, 3),
(94, 'api MTN BENIN', 'Bonjour Jean marcel \r\n je viens par ce ticket te ralancer sur les api de MTN benin que j\'ai partagé par mail , il est urgent que nous ayons un retour pour au besoin programmé une reunion avec l\'equipe du client pour avoir un complement d\'info si besoin y est afin de pouvoir cloturer ce ticket qui a deja pris beaucoup de temps. comptant sur ta diligence nous restons en attente.\r\nCordialement', 'Nouveau', 'Urgente', 32, NULL, NULL, 2, '2025-12-01 13:40:44', '2025-12-01 13:40:44', NULL, 4),
(95, 'Archivage/Espace boîte mail GNANGNE AYOU', 'Bonsoir à tous,\r\n\r\nMa boîte mail est pleine.\r\nJe n\'arrive pas également à joindre des documents par mail', 'Nouveau', 'Haute', 21, NULL, 10, 1, '2025-12-04 17:25:14', '2025-12-04 17:25:14', NULL, NULL),
(96, 'Paramétrage d\'un nouvel élément de paie_Prime de fin d\'année', 'Bonjour Marcel, j\'espère que mon mail te trouve en parfaite santé.\r\n\r\nJe te prie de nous aider à formaliser dans Zeus le traitement de la prime de fin d\'année sur l\'ensemble des filiales.\r\nIl s\'agit de créer un onglet au même titre que l\'onglet régularisation.\r\n\r\nLa prime de fin d\'année sera un montant brut imposable que nous allons renseigner manuellement qui sera rajouter en addition dans la formule du Salaire Brut donc implicitement dans le SBI également.\r\n\r\nCdt,', 'Résolu', 'Urgente', 21, 1, 10, 2, '2025-12-08 16:04:35', '2026-01-07 16:59:13', '2026-01-07 16:59:13', 3),
(97, 'INACCESSIBILITE AUX APPLICATIONS DU PORTAIL GMC', 'Bonjour Digital By Palladium,\r\n\r\nJe viens vers vous ce matin au sujet des mes applications du portail GMC.\r\n\r\nM. MANGA, vous a certainement déjà parlé de cela il y 4 mois environ.\r\n\r\nA date, je n\'ai toujours pas accès aux application du portail intranet GMC raison pour la quelle je viens vers vous ce matin à travers le mail.', 'Nouveau', 'Haute', 16, NULL, 13, 2, '2025-12-18 09:01:18', '2025-12-18 09:01:18', NULL, 2),
(98, 'INACCESSIBILITE AUX APPLICATIONS DU PORTAIL GMC', 'Bonjour Digital By Palladium,\r\n\r\nJe viens vers vous ce matin au sujet des mes applications du portail GMC.\r\n\r\nM. MANGA, vous a certainement déjà parlé de cela il y 4 mois environ.\r\n\r\nA date, je n\'ai toujours pas accès aux application du portail intranet GMC raison pour la quelle je viens vers vous ce matin à travers le mail.', 'Nouveau', 'Haute', 16, NULL, 13, 2, '2025-12-18 09:01:24', '2025-12-18 09:01:24', NULL, 2),
(99, 'custum', 'un exemple de ticket a ne pas prendre en compte', 'Nouveau', 'Moyenne', 37, NULL, 13, 2, '2026-01-05 14:25:42', '2026-01-05 14:25:42', NULL, 3),
(100, 'Paramétrage fonction dans Zeus', 'Bonjour Marcelle,\r\n\r\nJ’espère que tu vas bien.\r\n\r\nPourrais-tu, s’il te plaît, apporter une modification sur l’affichage de la grille de salaires au niveau de la fonction sur l’interface collaborateur ? Nous souhaiterions que ce soit la définition qui apparaisse plutôt que le libellé.\r\n\r\nMerci d’avance pour ton aide.', 'Résolu', 'Haute', 21, 1, 10, 2, '2026-01-06 13:31:01', '2026-01-07 16:58:26', '2026-01-07 16:58:26', 3);

-- --------------------------------------------------------

--
-- Structure de la table `ticket_attachments`
--

CREATE TABLE `ticket_attachments` (
  `id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ticket_attachments`
--

INSERT INTO `ticket_attachments` (`id`, `ticket_id`, `file_name`, `file_path`, `file_size`, `uploaded_at`) VALUES
(9, 53, 'status agents sur zeus.docx', 'uploads/53_1753446975_status agents sur zeus.docx', 204599, '2025-07-25 14:36:15'),
(10, 55, 'GBODOGBE ULRICH.PNG', 'uploads/55_1753884663_GBODOGBE ULRICH.PNG', 37596, '2025-07-30 16:11:03'),
(11, 56, 'MESSAGE d\'erreur de zeus.docx', 'uploads/56_1754473935_MESSAGE d\'erreur de zeus.docx', 251229, '2025-08-06 11:52:15'),
(12, 57, 'Capture évidence dbcall.xlsx', 'uploads/57_1754474208_Capture évidence dbcall.xlsx', 10700, '2025-08-06 11:56:48'),
(13, 59, 'dysf.xlsx', 'uploads/59_1754652908_dysf.xlsx', 11339, '2025-08-08 13:35:08'),
(14, 60, 'FAQ GENERALE.xlsx', 'uploads/60_1754656030_FAQ GENERALE.xlsx', 59595, '2025-08-08 14:27:10'),
(15, 61, 'BASE DE CONNAISSANCE AVEC IA.eml', 'uploads/61_1754656806_BASE DE CONNAISSANCE AVEC IA.eml', 479077, '2025-08-08 14:40:06'),
(16, 62, 'SUIVI DASH PROJET 2025.xlsx', 'uploads/62_1754657608_SUIVI DASH PROJET 2025.xlsx', 61447, '2025-08-08 14:53:28'),
(17, 70, 'message d\'erreur zeus.docx', 'uploads/70_1755628368_message d\'erreur zeus.docx', 226032, '2025-08-19 20:32:48'),
(18, 71, 'EXCEPTION COTISATION CMU.ods', 'uploads/71_1755718970_EXCEPTION COTISATION CMU.ods', 4643, '2025-08-20 21:42:50'),
(19, 75, 'EXEMPLE.xlsx', 'uploads/75_1757609730_EXEMPLE.xlsx', 268231, '2025-09-11 18:55:30'),
(20, 76, 'souci avec Zeus.docx', 'uploads/76_1758207134_souci avec Zeus.docx', 305761, '2025-09-18 16:52:14'),
(21, 77, 'DEMANDE D\'ACCES  ZEUS (2).docx', 'uploads/77_1758297539_DEMANDE D\'ACCES  ZEUS (2).docx', 888732, '2025-09-19 17:58:59'),
(22, 78, 'INTERFACE DIGITALE GESTION DES BESOINS ET TICKET D\'INCIDENTS.pptx', 'uploads/78_1758531065_INTERFACE DIGITALE GESTION DES BESOINS ET TICKET D\'INCIDENTS.pptx', 10865871, '2025-09-22 10:51:05'),
(23, 79, 'LIM.PNG', 'uploads/79_1758804206_LIM.PNG', 13601, '2025-09-25 14:43:26'),
(24, 80, '1 IM.jpg', 'uploads/80_1759245650_1 IM.jpg', 78729, '2025-09-30 17:20:50'),
(25, 81, 'ETATS BANQUE.xlsx', 'uploads/81_1759851227_ETATS BANQUE.xlsx', 40114, '2025-10-07 17:33:47'),
(26, 86, 'appels repétés.xlsx', 'uploads/86_1761914187_appels repétés.xlsx', 11776, '2025-10-31 13:36:27'),
(27, 89, 'DETAIL CNPS.xlsx', 'uploads/89_1763417335_DETAIL CNPS.xlsx', 36317, '2025-11-17 23:08:55'),
(28, 97, 'CAPTURE PORTAIL INNOCENT.doc', 'uploads/97_1766044878_CAPTURE PORTAIL INNOCENT.doc', 169472, '2025-12-18 09:01:18'),
(29, 98, 'CAPTURE PORTAIL INNOCENT.doc', 'uploads/98_1766044884_CAPTURE PORTAIL INNOCENT.doc', 169472, '2025-12-18 09:01:24');

-- --------------------------------------------------------

--
-- Structure de la table `ticket_types`
--

CREATE TABLE `ticket_types` (
  `id` int NOT NULL,
  `service_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `ticket_types`
--

INSERT INTO `ticket_types` (`id`, `service_id`, `name`, `description`, `created_at`) VALUES
(2, 2, 'Disfonctionnement Application', 'Disfonctionnement constaté sur une application (ZEUS, Hermès, Ares...)', '2025-06-27 16:10:35'),
(3, 2, 'Evolution Application', 'Application qui nécessite une mise à jour', '2025-07-16 14:06:24'),
(4, 2, 'Support technique', 'Besoin d\'une assistance sur une fonctionnalité existante', '2025-07-16 14:15:21');

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
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_ticket_comment` (`user_id`,`ticket_id`,`comment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `notifications_ibfk_3` (`comment_id`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `specifications`
--
ALTER TABLE `specifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_last_modified_by` (`last_modified_by`);

--
-- Index pour la table `specification_history`
--
ALTER TABLE `specification_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `specification_id` (`specification_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Index pour la table `specification_stakeholders`
--
ALTER TABLE `specification_stakeholders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stakeholder` (`specification_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Index pour la table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `template_sections`
--
ALTER TABLE `template_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`);

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
-- Index pour la table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Index pour la table `ticket_types`
--
ALTER TABLE `ticket_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

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
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT pour la table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `specifications`
--
ALTER TABLE `specifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `specification_history`
--
ALTER TABLE `specification_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT pour la table `specification_stakeholders`
--
ALTER TABLE `specification_stakeholders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=362;

--
-- AUTO_INCREMENT pour la table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `template_sections`
--
ALTER TABLE `template_sections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT pour la table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `ticket_types`
--
ALTER TABLE `ticket_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `specifications`
--
ALTER TABLE `specifications`
  ADD CONSTRAINT `fk_last_modified_by` FOREIGN KEY (`last_modified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `specifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `specification_history`
--
ALTER TABLE `specification_history`
  ADD CONSTRAINT `specification_history_ibfk_1` FOREIGN KEY (`specification_id`) REFERENCES `specifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `specification_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `specification_stakeholders`
--
ALTER TABLE `specification_stakeholders`
  ADD CONSTRAINT `specification_stakeholders_ibfk_1` FOREIGN KEY (`specification_id`) REFERENCES `specifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `specification_stakeholders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `task_attachments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `template_sections`
--
ALTER TABLE `template_sections`
  ADD CONSTRAINT `template_sections_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_ticket_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`assigned_to_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`type_id`) REFERENCES `ticket_types` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD CONSTRAINT `ticket_attachments_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ticket_types`
--
ALTER TABLE `ticket_types`
  ADD CONSTRAINT `ticket_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

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
