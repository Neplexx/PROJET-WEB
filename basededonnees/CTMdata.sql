-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 30 avr. 2025 à 09:17
-- Version du serveur : 5.7.24
-- Version de PHP : 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ctmdata`
--

-- --------------------------------------------------------

--
-- Structure de la table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `editor_id` int(11) NOT NULL,
  `message` text,
  `proposed_rate` decimal(10,2) DEFAULT NULL,
  `status` enum('en attente','accepté','refusé','annulé') DEFAULT 'en attente',
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `applications`
--

INSERT INTO `applications` (`application_id`, `project_id`, `editor_id`, `message`, `proposed_rate`, `status`, `applied_at`) VALUES
(1, 1, 1, 'Bonjour, je suis très intéressé par votre projet. J\'ai une grande expérience dans le montage documentaire comme vous pouvez le voir dans mon portfolio.', '14500.00', 'en attente', '2025-04-07 18:19:08'),
(2, 2, 2, 'Votre campagne publicitaire correspond parfaitement à mes compétences en motion design. Je propose un tarif de 7500€ pour les 5 spots.', '7500.00', 'en attente', '2025-04-07 18:19:08');

-- --------------------------------------------------------

--
-- Structure de la table `editors`
--

CREATE TABLE `editors` (
  `editor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `years_experience` int(11) DEFAULT NULL,
  `daily_rate` decimal(10,2) DEFAULT NULL,
  `availability` enum('disponible','occupé','indisponible') DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `editors`
--

INSERT INTO `editors` (`editor_id`, `user_id`, `years_experience`, `daily_rate`, `availability`) VALUES
(1, 1, 5, '350.00', 'disponible'),
(2, 2, 3, '280.00', 'disponible');

-- --------------------------------------------------------

--
-- Structure de la table `editor_skills`
--

CREATE TABLE `editor_skills` (
  `editor_skill_id` int(11) NOT NULL,
  `editor_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `proficiency_level` enum('débutant','intermédiaire','avancé','expert') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `editor_skills`
--

INSERT INTO `editor_skills` (`editor_skill_id`, `editor_id`, `skill_id`, `proficiency_level`) VALUES
(1, 1, 1, 'expert'),
(2, 1, 4, 'avancé'),
(3, 1, 5, 'intermédiaire'),
(4, 1, 10, 'avancé'),
(5, 2, 2, 'avancé'),
(6, 2, 4, 'expert'),
(7, 2, 5, 'expert'),
(8, 2, 11, 'avancé');

-- --------------------------------------------------------

--
-- Structure de la table `employers`
--

CREATE TABLE `employers` (
  `employer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `company_size` enum('1-10','11-50','51-200','201-500','500+') DEFAULT NULL,
  `industry` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `employers`
--

INSERT INTO `employers` (`employer_id`, `user_id`, `company_name`, `company_size`, `industry`) VALUES
(1, 3, 'Films Indépendants', '1-10', 'Cinéma'),
(2, 4, 'CréaPub', '51-200', 'Publicité');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `project_id`, `content`, `is_read`, `sent_at`) VALUES
(1, 3, 1, 1, 'Bonjour Jean, merci pour votre candidature. Pourriez-vous nous envoyer des références supplémentaires ?', 0, '2025-04-07 18:19:08'),
(2, 1, 3, 1, 'Bien sûr, je vous envoie cela dès ce soir. J\'ai travaillé sur plusieurs documentaires similaires.', 1, '2025-04-07 18:19:08'),
(3, 4, 2, 2, 'Nous avons bien reçu votre proposition et sommes très intéressés. Pouvez-vous commencer lundi prochain ?', 0, '2025-04-07 18:19:08');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `notification_type` enum('application','message','project','review','system','support') NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `content`, `is_read`, `notification_type`, `related_id`, `created_at`) VALUES
(1, 5, 'Nouveau message de support:\n\nDe: dahi badis (englishbadisdahi@gmail.com)\n\nMessage:\nProblème de paiement', 1, 'support', 5, '2025-04-30 09:07:40');

-- --------------------------------------------------------

--
-- Structure de la table `portfolios`
--

CREATE TABLE `portfolios` (
  `portfolio_id` int(11) NOT NULL,
  `editor_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `video_url` varchar(255) DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `portfolios`
--

INSERT INTO `portfolios` (`portfolio_id`, `editor_id`, `title`, `description`, `video_url`, `thumbnail_url`, `is_featured`, `created_at`) VALUES
(1, 1, 'Documentaire \"Les Oubliés\"', 'Documentaire primé sur les sans-abris à Paris', 'https://vimeo.com/123456', 'docu_thumbnail.jpg', 1, '2025-04-07 18:19:08'),
(2, 1, 'Reportage Arte', 'Montage d\'un reportage de 26 minutes pour Arte', 'https://vimeo.com/234567', 'arte_thumbnail.jpg', 0, '2025-04-07 18:19:08'),
(3, 2, 'Campagne Nike', 'Motion design pour une campagne Nike', 'https://vimeo.com/345678', 'nike_thumbnail.jpg', 1, '2025-04-07 18:19:08'),
(4, 2, 'Spot publicitaire Renault', 'Montage et animation d\'un spot de 30 secondes', 'https://vimeo.com/456789', 'renault_thumbnail.jpg', 1, '2025-04-07 18:19:08');

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `project_type` enum('court-métrage','documentaire','publicité','film institutionnel','série','autre') NOT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('brouillon','publié','en cours','terminé','annulé') DEFAULT 'brouillon',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`project_id`, `employer_id`, `title`, `description`, `project_type`, `budget`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 1, 'Documentaire sur le changement climatique', 'Documentaire de 52 minutes sur les effets du changement climatique en Europe. Recherche monteur expérimenté en documentaire.', 'documentaire', '15000.00', '2023-09-01', '2023-12-15', 'publié', '2025-04-07 18:19:08'),
(2, 2, 'Campagne publicitaire pour une marque de sport', 'Montage de 5 spots publicitaires de 30 secondes pour une nouvelle campagne. Recherche monteur créatif avec expérience en motion design.', 'publicité', '8000.00', '2023-08-15', '2023-09-30', 'publié', '2025-04-07 18:19:08');

-- --------------------------------------------------------

--
-- Structure de la table `project_requirements`
--

CREATE TABLE `project_requirements` (
  `requirement_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `importance` enum('requis','important','optionnel') DEFAULT 'important'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `project_requirements`
--

INSERT INTO `project_requirements` (`requirement_id`, `project_id`, `skill_id`, `importance`) VALUES
(1, 1, 1, 'requis'),
(2, 1, 10, 'requis'),
(3, 1, 6, 'important'),
(4, 2, 4, 'requis'),
(5, 2, 5, 'requis'),
(6, 2, 11, 'important');

-- --------------------------------------------------------

--
-- Structure de la table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewed_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `reviews`
--

INSERT INTO `reviews` (`review_id`, `reviewer_id`, `reviewed_id`, `project_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 1, 1, 5, 'Excellent travail de montage, très professionnel et respect des délais. Je recommande vivement !', '2025-04-07 18:19:08'),
(2, 4, 2, 2, 4, 'Très bon montage et créativité, juste quelques retards mineurs dans les rendus.', '2025-04-07 18:19:08');

-- --------------------------------------------------------

--
-- Structure de la table `skills`
--

CREATE TABLE `skills` (
  `skill_id` int(11) NOT NULL,
  `skill_name` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `skills`
--

INSERT INTO `skills` (`skill_id`, `skill_name`, `category`) VALUES
(1, 'Premiere Pro', 'Logiciel'),
(2, 'Final Cut Pro', 'Logiciel'),
(3, 'DaVinci Resolve', 'Logiciel'),
(4, 'After Effects', 'Logiciel'),
(5, 'Motion Design', 'Compétence'),
(6, 'Color Grading', 'Compétence'),
(7, 'Étalonnage', 'Compétence'),
(8, 'Sound Design', 'Compétence'),
(9, 'Montage narratif', 'Style'),
(10, 'Montage documentaire', 'Style'),
(11, 'Montage publicitaire', 'Style'),
(12, 'Montage cinéma', 'Style'),
(13, 'VFX basiques', 'Compétence'),
(14, 'Animation 2D', 'Compétence'),
(15, 'Sous-titrage', 'Compétence');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text,
  `user_type` enum('client','graphiste','manager','développeur','beatmaker','monteur','employeur','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `first_name`, `last_name`, `phone`, `profile_picture`, `bio`, `user_type`, `created_at`, `updated_at`) VALUES
(1, 'monteur1@example.com', 'salutlesmecs', 'Jean', 'Dupont', '0612345678', 'profile1.jpg', 'Monteur professionnel avec 5 ans d\'expérience dans le documentaire et la publicité.', 'monteur', '2025-04-07 18:19:08', '2025-04-26 10:58:04'),
(2, 'monteur2@example.com', 'jenesaispas', 'Marie', 'Martin', '0698765432', 'profile2.jpg', 'Spécialisée en motion design et montage créatif pour les réseaux sociaux.', 'monteur', '2025-04-07 18:19:08', '2025-04-07 18:19:08'),
(3, 'employeur1@example.com', 'motdepassetemporaire', 'Pierre', 'Durand', '0687654321', 'company1.jpg', 'Producteur indépendant de courts-métrages et documentaires.', 'employeur', '2025-04-07 18:19:08', '2025-04-07 18:19:08'),
(4, 'employeur2@example.com', 'oiseau', 'Sophie', 'Leroy', '0678912345', 'company2.jpg', 'Directrice de production dans une agence de publicité.', 'employeur', '2025-04-07 18:19:08', '2025-04-07 18:19:08'),
(5, 'englishbadisdahi@gmail.com', 'motdepasse', 'dahi', 'badis', '454', NULL, '5454\r\n', 'admin', '2025-04-08 17:47:46', '2025-04-30 08:17:36'),
(6, 'max.castagne@example.com', 'maxpass23', 'Max', 'Castagne', '0678452310', 'profile1.jpg', 'Spécialiste du montage court-métrage avec un sens aigu du rythme.', 'monteur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(7, 'jack.chrass@example.com', 'jackedit99', 'Jack', 'Chrass', '0770213456', 'profile1.jpg', 'Monteur avec une grande expérience dans le clip musical et les transitions dynamiques.', 'monteur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(8, 'pierre.calvasse@example.com', 'pierrevideo88', 'Pierre', 'Calvasse', '0679981203', 'profile1.jpg', 'Expert en storytelling vidéo, adepte des logiciels Adobe Premiere et DaVinci.', 'monteur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(9, 'pierre.calvasse@example.com', 'quentinpro', 'Quentin', 'Michel', '0765123498', 'profile1.jpg', 'Monteur passionné par le documentaire, toujours à la recherche de l’émotion juste.', 'monteur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(10, 'graphiste1@example.com', 'lucdesign', 'Luc', 'Pliuc', '0670012398', 'profile1.jpg', 'Graphiste expert en branding et création d’identités visuelles fortes.', 'graphiste', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(11, 'graphiste2@example.com', 'khaledart77', 'Khaled', 'Prime', '0679981123', 'profile1.jpg', 'Spécialisé en motion design, j’anime vos idées avec fluidité et style.', 'graphiste', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(12, 'graphiste3@example.com', 'martinviz', 'Martin', 'Jiopo', '0776542312', 'profile1.jpg', 'Graphiste polyvalent avec un œil affuté pour le détail et la composition.', 'graphiste', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(13, 'graphiste4@example.com', 'luciencrea', 'Lucien', 'Dekune', '0678459901', 'profile1.jpg', 'Illustrateur et graphiste, je donne vie à vos projets avec originalité.', 'graphiste', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(14, 'manager1@example.com', 'julienlead', 'Julien', 'Martin', '0670034567', 'profile1.jpg', 'Manager expérimenté, je coordonne les talents pour des projets efficaces.', 'manager', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(15, 'manager2@example.com', 'elodiemgmt', 'Élodie', 'Dupont', '0778234510', 'profile1.jpg', 'Experte en gestion de production, je fais le lien entre la vision et l’exécution.', 'manager', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(16, 'manager3@example.com', 'antoineorga', 'Antoine', 'Bernard', '0678945623', 'profile1.jpg', 'Chef de projet rigoureux, j’assure la qualité et le respect des délais.', 'manager', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(17, 'manager4@example.com', 'claireboss', 'Claire', 'Lefèvre', '0767894501', 'profile1.jpg', 'Manager créative, j’encourage la collaboration et l’innovation.', 'manager', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(18, 'dev1@example.com', 'lucasdev', 'Lucas', 'Girard', '0671123987', 'profile1.jpg', 'Développeur full-stack spécialisé en React et Node.js, passionné de tech.', 'développeur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(19, 'dev2@example.com', 'camillecode', 'Camille', 'Moreau', '0770034598', 'profile1.jpg', 'Développeuse web amoureuse du clean code et des expériences fluides.', 'développeur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(20, 'dev3@example.com', 'thomasroot', 'Thomas', 'Dubois', '0678324590', 'profile1.jpg', 'Backend dev orienté performance et sécurité, avec une touche DevOps.', 'développeur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(21, 'dev4@example.com', 'sophiehtml', 'Sophie', 'Laurent', '0765432109', 'profile1.jpg', 'Développeuse front-end, experte UI/UX avec une sensibilité design.', 'développeur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(22, 'beatmaker1@example.com', 'antoinebeat', 'Antoine', 'Perrot', '0678912345', 'profile1.jpg', 'Beatmaker influencé par le boom bap et les textures vintage.', 'beatmaker', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(23, 'beatmaker2@example.com', 'juliebass', 'Julie', 'Barbier', '0778891234', 'profile1.jpg', 'Productrice musicale, je crée des sons doux et percutants à la fois.', 'beatmaker', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(24, 'beatmaker3@example.com', 'maximeloop', 'Maxime', 'Richard', '0670098765', 'profile1.jpg', 'Spécialiste des instrumentales trap et RnB, je façonne des hits sur mesure.', 'beatmaker', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(25, 'beatmaker4@example.com', 'sarahprod', 'Sarah', 'Gauthier', '0767801234', 'profile1.jpg', 'Beatmakeuse polyvalente, de l’électro chill au hip-hop expérimental.', 'beatmaker', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(26, 'maxlamax@gmail.com', 'maxlamax', 'Max', 'LaMax', NULL, NULL, NULL, 'client', '2025-04-30 08:45:55', '2025-04-30 08:45:55');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `editor_id` (`editor_id`);

--
-- Index pour la table `editors`
--
ALTER TABLE `editors`
  ADD PRIMARY KEY (`editor_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `editor_skills`
--
ALTER TABLE `editor_skills`
  ADD PRIMARY KEY (`editor_skill_id`),
  ADD UNIQUE KEY `editor_id` (`editor_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Index pour la table `employers`
--
ALTER TABLE `employers`
  ADD PRIMARY KEY (`employer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `portfolios`
--
ALTER TABLE `portfolios`
  ADD PRIMARY KEY (`portfolio_id`),
  ADD KEY `editor_id` (`editor_id`);

--
-- Index pour la table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `employer_id` (`employer_id`);

--
-- Index pour la table `project_requirements`
--
ALTER TABLE `project_requirements`
  ADD PRIMARY KEY (`requirement_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Index pour la table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Index pour la table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD UNIQUE KEY `skill_name` (`skill_name`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `editor_skills`
--
ALTER TABLE `editor_skills`
  MODIFY `editor_skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `portfolios`
--
ALTER TABLE `portfolios`
  MODIFY `portfolio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `project_requirements`
--
ALTER TABLE `project_requirements`
  MODIFY `requirement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `skills`
--
ALTER TABLE `skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
