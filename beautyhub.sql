-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 27 avr. 2026 à 21:00
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_panier`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`) VALUES
(1, 'Soins Visage'),
(2, 'Soins Cheveux'),
(3, 'Maquillage'),
(4, 'Parfums'),
(5, 'Accessoires de Beauté'),
(6, 'Soins Corps'),
(7, 'Soins Ongles'),
(8, 'Hygiène & Bien-être');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `date_commande` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `statut` varchar(50) DEFAULT 'en_attente',
  `adresse_livraison` varchar(255) DEFAULT NULL,
  `frais_livraison` decimal(10,2) DEFAULT 8.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `utilisateur_id`, `date_commande`, `total`, `statut`, `adresse_livraison`, `frais_livraison`) VALUES
(4, 1, '2026-01-15 10:23:00', 139.50, 'livree', 'Bizerte - Bizerte Centre', 8.00),
(5, 1, '2026-02-03 14:05:00', 82.00, 'livree', 'Bizerte - Bizerte Centre', 8.00),
(6, 2, '2026-03-10 09:00:00', 455.00, 'confirmee', 'Tunis - Lafayette', 8.00),
(7, 3, '2026-03-18 11:30:00', 215.90, 'expediee', 'Sfax - Centre Ville', 8.00),
(8, 3, '2026-02-20 16:45:00', 63.00, 'annulee', 'Sfax - Centre Ville', 8.00),
(9, 4, '2026-04-01 08:15:00', 178.00, 'en_attente', 'Sousse - Khezama', 8.00),
(10, 4, '2026-01-28 13:00:00', 320.00, 'livree', 'Sousse - Khezama', 8.00),
(11, 5, '2026-04-05 17:20:00', 512.00, 'confirmee', 'Ariana - Raoued', 8.00),
(12, 1, '2026-04-10 10:00:00', 248.00, 'expediee', 'Bizerte - Bizerte Centre', 8.00),
(13, 3, '2026-04-20 09:45:00', 95.00, 'en_attente', 'Sfax - Centre Ville', 8.00),
(14, 2, '2026-04-26 20:21:33', 153.50, 'en_attente', 'Tunis - Lafayette', 8.00),
(15, 2, '2026-04-27 00:54:01', 200.00, 'en_attente', 'Tunis - Lafayette', 8.00);

-- --------------------------------------------------------

--
-- Structure de la table `conseils`
--

CREATE TABLE `conseils` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `question` text NOT NULL,
  `type_peau` varchar(50) DEFAULT NULL,
  `reponse` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `conseils`
--

INSERT INTO `conseils` (`id`, `utilisateur_id`, `nom`, `email`, `question`, `type_peau`, `reponse`, `created_at`) VALUES
(1, 1, 'Eline Jemili', 'Eline@gmail.com', 'Quelle routine me conseillez-vous pour une peau grasse avec imperfections ?', 'grasse', 'Nous vous conseillons le CeraVe Nettoyant matin et soir suivi du serum Niacinamide 10%. Optez pour SVR Sebiaclear comme hydratant.', '2026-04-26 16:59:33'),
(2, 3, 'Sara Trabelsi', 'sara@gmail.com', 'J\'ai les cheveux tres secs et abimes par la teinture, quels soins choisir ?', 'cheveux_secs', NULL, '2026-04-26 16:59:33'),
(3, 4, 'Mohamed Amri', 'mohamed@gmail.com', 'Quel parfum recommandez-vous pour un usage quotidien au bureau ?', 'autre', 'Pour le bureau nous recommandons Armani Acqua di Gio : frais, discret et elegant. Evitez les parfums trop lourds en journee.', '2026-04-26 16:59:33'),
(4, 5, 'Amira Jaziri', 'amira@gmail.com', 'Je recherche une routine complete anti-age, par ou commencer ?', 'mixte', NULL, '2026-04-26 16:59:33');

-- --------------------------------------------------------

--
-- Structure de la table `historique`
--

CREATE TABLE `historique` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `historique`
--

INSERT INTO `historique` (`id`, `user_id`, `action`, `created_at`) VALUES
(1, 2, 'Connexion', '2026-04-24 12:31:10'),
(2, 1, 'Connexion', '2026-04-24 12:32:52'),
(3, 6, 'Inscription', '2026-04-24 12:36:03'),
(4, 7, 'Inscription', '2026-04-24 12:36:56'),
(5, 7, 'Connexion', '2026-04-24 12:37:07'),
(6, 1, 'Connexion', '2026-04-24 12:38:19'),
(7, 2, 'Connexion', '2026-04-26 16:24:36'),
(8, 2, 'Commande #14 passée — 153.50 DT (dont 8 DT livraison)', '2026-04-26 20:21:33'),
(9, 2, 'Commande #15 passée — 200.00 DT (dont 8 DT livraison)', '2026-04-27 00:54:01'),
(10, 2, 'Connexion', '2026-04-27 19:40:33');

-- --------------------------------------------------------

--
-- Structure de la table `ligne_commande`
--

CREATE TABLE `ligne_commande` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ligne_commande`
--

INSERT INTO `ligne_commande` (`id`, `commande_id`, `produit_id`, `quantite`, `prix_unitaire`) VALUES
(7, 4, 1, 2, 48.50),
(8, 4, 3, 1, 25.00),
(10, 6, 10, 1, 320.00),
(11, 6, 11, 1, 400.00),
(13, 7, 4, 1, 135.00),
(14, 7, 8, 1, 55.00),
(15, 7, 9, 1, 85.00),
(16, 8, 15, 1, 120.00),
(17, 9, 2, 2, 72.00),
(18, 10, 10, 1, 320.00),
(19, 11, 11, 1, 400.00),
(20, 11, 12, 1, 350.00),
(22, 12, 13, 1, 45.00),
(23, 12, 14, 2, 25.00),
(25, 14, 1, 1, 48.50),
(26, 14, 3, 1, 25.00),
(27, 14, 2, 1, 72.00),
(28, 15, 20, 1, 45.00),
(29, 15, 18, 1, 89.00),
(30, 15, 17, 1, 58.00);

-- --------------------------------------------------------

--
-- Structure de la table `panier`
--

CREATE TABLE `panier` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `quantite` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `categorie_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `description`, `prix`, `stock`, `image`, `categorie_id`) VALUES
(1, 'CeraVe Nettoyant', 'Gel moussant peaux grasses', 48.50, 19, 'prod_1777243376_584.jpg', 1),
(2, 'La Roche-Posay Effaclar', 'Crème anti-imperfections', 72.00, 14, 'prod_1777243396_253.jpg', 1),
(3, 'Nivea Soft', 'Crème hydratante visage', 25.00, 29, 'prod_1777243939_145.jpg', 1),
(4, 'Kérastase Masque', 'Nutrition cheveux secs', 135.00, 10, 'prod_1777244164_489.jpg', 2),
(5, 'L’Oréal Shampooing', 'Réparation cheveux abîmés', 29.90, 25, 'prod_1777244199_265.jpg', 2),
(6, 'Garnier Ultra Doux', 'Shampooing naturel', 18.50, 40, 'prod_1777244045_548.jpg', 2),
(8, 'Fond de teint Maybelline', 'Teint parfait longue tenue', 55.00, 18, 'prod_1777245019_455.jpg', 3),
(9, 'Rouge à lèvres Dior', 'Couleur intense', 150.00, 10, 'prod_1777245122_786.jpg', 3),
(10, 'Dior Sauvage', 'Parfum homme', 320.00, 8, 'prod_1777245189_138.jpg', 4),
(11, 'Chanel N°5', 'Parfum femme classique', 400.00, 5, 'prod_1777245174_427.jpg', 4),
(12, 'YSL Black Opium', 'Parfum femme', 350.00, 7, 'default.jpg', 4),
(13, 'Pinceau maquillage', 'Set de pinceaux', 45.00, 20, 'prod_1777315457_262.jpg', 5),
(14, 'Beauty Blender', 'Éponge maquillage', 25.00, 35, 'prod_1777315368_929.jpg', 5),
(15, 'Miroir LED', 'Miroir avec lumière', 120.00, 6, 'prod_1777315423_353.jpg', 5),
(16, 'Vichy Normaderm', 'Gel nettoyant anti-imperfections peau grasse', 65.00, 22, 'prod_1777243992_617.jpg', 1),
(17, 'Bioderma Sensibio', 'Eau micellaire demaquillante peaux sensibles', 58.00, 34, 'prod_1777243303_613.jpg', 1),
(18, 'Neutrogena Hydro Boost', 'Gel creme hydratant a l\'acide hyaluronique', 89.00, 17, 'prod_1777243414_769.jpg', 1),
(19, 'SVR Sebiaclear', 'Creme matifiante pores dilates', 72.00, 14, 'prod_1777243955_251.jpg', 1),
(20, 'Avene Eau Thermale', 'Spray apaisant peaux irritees', 45.00, 39, 'prod_1777243269_257.jpg', 1),
(21, 'The Ordinary Niacinamide', 'Serum niacinamide 10% + zinc 1%', 55.00, 30, 'prod_1777243978_124.jpg', 1),
(22, 'Caudalie Vinoperfect', 'Serum eclat anti-taches a la viniferine', 145.00, 9, 'prod_1777243343_405.jpg', 1),
(23, 'Kerastase Elixir Ultime', 'Huile precieuse sublimante pour tous types de cheveux', 89.98, 12, 'prod_1777244099_862.jpg', 2),
(24, 'Schwarzkopf Gliss', 'Apres-shampooing reparation extreme', 32.00, 28, 'prod_1777244764_890.jpg', 2),
(25, 'OGX Argan Oil', 'Huile d\'argan du Maroc pour cheveux brillants', 48.00, 20, 'prod_1777244714_604.jpg', 2),
(26, 'Pantene Pro-V Miracles', 'Shampooing force et brillance', 22.00, 45, 'prod_1777244732_319.jpg', 2),
(27, 'Dove Intense Repair', 'Apres-shampooing reparation intense cheveux abimes', 19.90, 38, 'prod_1777244015_839.jpg', 2),
(28, 'Moroccanoil Treatment', 'Traitement a l\'huile d\'argan — soin iconique', 180.00, 7, 'prod_1777244316_121.jpg', 2),
(29, 'NYX Setting Spray', 'Spray fixateur maquillage longue tenue 24h', 38.00, 25, 'prod_1777245098_255.jpg', 3),
(30, 'MAC Highlighter', 'Poudre enlumineur eclat naturel', 120.00, 11, 'prod_1777245034_366.jpg', 3),
(31, 'Urban Decay Primer', 'Base a paupieres — tenue 24h sans pli', 75.00, 16, 'prod_1777245143_249.jpg', 3),
(32, 'Charlotte Tilbury Blush', 'Blush creme bonne mine teint hale', 95.00, 13, 'prod_1777244822_164.jpg', 3),
(33, 'Benefit Brow Pencil', 'Crayon sourcils precision pointe ultra-fine', 68.00, 20, 'prod_1777244797_485.jpg', 3),
(34, 'Fenty Beauty Concealer', 'Anti-cernes couvrance modulable 50 teintes', 89.00, 17, 'prod_1777245002_876.jpg', 3),
(35, 'Lancome La Vie Est Belle', 'Parfum femme — floral gourmand iconique', 380.00, 6, 'prod_1777245210_468.jpg', 4),
(36, 'Armani Acqua di Gio', 'Parfum homme — frais aquatique mediterraneen', 290.00, 8, 'prod_1777245157_395.jpg', 4),
(37, 'Versace Bright Crystal', 'Parfum femme — floral fruite petillant', 220.00, 10, 'prod_1777315344_165.jpg', 4),
(38, 'Paco Rabanne 1 Million', 'Parfum homme — boise epice charnel', 310.00, 7, 'prod_1777315284_399.jpg', 4),
(39, 'Narciso Rodriguez For Her', 'Parfum femme — musque floral sensuel', 260.00, 9, 'prod_1777245242_213.jpg', 4),
(40, 'Brosse a cheveux Wet', 'Brosse demeelante tous types de cheveux sec et mouille', 35.00, 30, 'prod_1777315383_626.jpg', 5),
(41, 'Serre-tete maquillage', 'Serre-tete en eponge pour application soins visage', 12.00, 50, 'prod_1777315520_746.jpg', 5),
(42, 'Rouleau jade', 'Rouleau facial en jade — drainage lymphatique', 55.00, 22, 'prod_1777315473_982.jpg', 5),
(43, 'Gua Sha quartz rose', 'Pierre gua sha en quartz rose — lissage du visage', 48.00, 18, 'prod_1777315404_191.jpg', 5),
(44, 'Pince a cils', 'Recourbe-cils professionnel acier inoxydable', 25.00, 35, 'prod_1777315439_634.jpg', 5),
(45, 'Trousse maquillage pro', 'Trousse organisatrice grande capacite 3 compartiments', 65.00, 15, 'prod_1777315536_910.jpg', 5),
(46, 'Nivea Body Lotion', 'Lait corps hydratation 48h peaux seches', 28.00, 40, 'prod_1777315672_584.jpg', 6),
(48, 'Palmers Cocoa Butter', 'Creme corps beurre de cacao anti-vergetures', 42.00, 25, 'prod_1777315691_716.jpg', 6),
(49, 'Garnier Body Superfood', 'Creme corps a l\'avocat et beurre de karite', 35.00, 30, 'prod_1777315568_288.jpg', 6),
(50, 'Dove Body Scrub', 'Exfoliant corps douceur et eclat a la grenade', 38.00, 22, 'prod_1777315549_608.jpg', 6),
(51, 'Rituals Hammam Scrub', 'Gommage corps sel de mer et huile d\'argan', 95.00, 14, 'prod_1777315711_488.jpg', 6),
(52, 'Mustela Lait Corps', 'Lait hydratant peaux sensibles bebe et adulte', 55.00, 18, 'prod_1777315590_508.jpg', 6),
(53, 'OPI Nail Lacquer', 'Vernis a ongles longue tenue — large palette de couleurs', 32.00, 40, 'prod_1777315870_399.jpg', 7),
(54, 'Essie Nail Polish', 'Vernis a ongles tendance — formule vegan', 28.00, 45, 'prod_1777315772_584.jpg', 7),
(55, 'Sally Hansen Hard as Nails', 'Base fortifiante ongles fragiles et cassants', 22.00, 30, 'prod_1777315889_952.jpg', 7),
(56, 'Manucurist', 'Vernis semi-permanent a base de plantes', 48.00, 20, 'prod_1777315853_606.jpg', 7),
(58, 'Kit manucure 10 pieces', 'Set complet lime coupe-ongles et repousse-cuticules', 45.00, 25, 'prod_1777315788_983.jpg', 7),
(61, 'Le Petit Marseillais', 'Huile de douche surgras amande douce et calendula', 24.00, 45, 'prod_1777315948_233.jpg', 8),
(63, 'Kiehls Lip Balm', 'Baume a levres hydratant formule pour peaux seches', 42.00, 28, 'prod_1777315931_614.jpg', 8),
(64, 'Biotherm Deodorant', 'Deodorant 48h sans alcool peaux sensibles', 38.00, 30, 'prod_1777315915_578.jpg', 8);

-- --------------------------------------------------------

--
-- Structure de la table `reclamations`
--

CREATE TABLE `reclamations` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `sujet` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `commande_ref` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT 'en_attente',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reclamations`
--

INSERT INTO `reclamations` (`id`, `utilisateur_id`, `nom`, `email`, `sujet`, `message`, `commande_ref`, `statut`, `created_at`) VALUES
(1, 1, 'Eline Jemili', 'Eline@gmail.com', 'Livraison en retard', 'Ma commande est arrivee avec 5 jours de retard sans explication.', '#1', 'resolue', '2026-04-26 16:59:33'),
(2, 3, 'Sara Trabelsi', 'sara@gmail.com', 'Produit defectueux', 'Le mascara recu etait completement sec et inutilisable a reception.', '#4', 'en_cours', '2026-04-26 16:59:33'),
(3, 4, 'Mohamed Amri', 'mohamed@gmail.com', 'Produit manquant', 'Il manquait le pinceau maquillage dans mon colis a la reception.', '#6', 'en_attente', '2026-04-26 16:59:33'),
(4, 5, 'Amira Jaziri', 'amira@gmail.com', 'Points fidelite', 'Mes points n\'ont pas ete credites apres ma commande.', '#8', 'en_attente', '2026-04-26 16:59:33');

-- --------------------------------------------------------

--
-- Structure de la table `routines`
--

CREATE TABLE `routines` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icone` varchar(50) DEFAULT 'bi-stars',
  `couleur` varchar(20) DEFAULT '#fce4ec',
  `accent` varchar(20) DEFAULT '#d63384',
  `conseil` text DEFAULT NULL,
  `ordre` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `routines`
--

INSERT INTO `routines` (`id`, `titre`, `description`, `icone`, `couleur`, `accent`, `conseil`, `ordre`) VALUES
(1, 'Routine Soin du Visage (Matin)', 'Préparez votre peau pour la journée avec ces étapes essentielles.', 'bi-brightness-high', '#fce4ec', '#d63384', 'Appliquez toujours vos soins sur peau propre et légèrement humide.', 1),
(2, 'Routine Cheveux (Semaine)', 'Nourrissez et réparez vos cheveux avec cette routine hebdomadaire.', 'bi-scissors', '#e8f5e9', '#2e7d32', 'Utilisez le masque 1 à 2 fois par semaine pour des résultats optimaux.', 2),
(3, 'Routine Maquillage Naturel', 'Un look frais et naturel pour votre quotidien.', 'bi-palette', '#f3e5f5', '#9c27b0', 'Humidifiez légèrement le Beauty Blender avant utilisation pour un rendu plus frais.', 3),
(4, 'Routine Maquillage de Soirée', 'Sublimez votre regard et vos lèvres pour les grandes occasions.', 'bi-moon-stars', '#fff3e0', '#e65100', 'Utilisez une base à lèvres pour prolonger la tenue du rouge à lèvres.', 4),
(5, 'Routine Parfum & Accessoires', 'Finalisez votre style avec le bon parfum et les accessoires essentiels.', 'bi-flower1', '#e3f2fd', '#0277bd', 'Ne frottez jamais les poignets après avoir appliqué le parfum — cela casse les molécules.', 5),
(6, 'Routine Soin du Corps', 'Hydratez et chouchoutez votre corps du gommage a la creme.', 'bi-heart-pulse', '#e8f5e9', '#2e7d32', 'Appliquez votre creme corps juste apres la douche sur peau encore legerement humide.', 6),
(7, 'Routine Anti-Age Visage', 'Luttez contre les signes du temps avec des actifs cibles.', 'bi-clock-history', '#f3e5f5', '#9c27b0', 'Appliquez vos soins du plus leger au plus riche : serum puis creme puis huile.', 7),
(8, 'Routine Nail Art', 'Des ongles soignes et colores en quelques etapes simples.', 'bi-brush', '#fff3e0', '#e65100', 'Appliquez toujours une base protectrice avant votre vernis couleur.', 8),
(9, 'Routine Bien-etre et Detente', 'Prenez soin de vous de la tete aux pieds pour vous ressourcer.', 'bi-stars', '#e3f2fd', '#0277bd', 'Consacrez au moins 20 minutes a votre routine bien-etre pour un effet relaxant optimal.', 9);

-- --------------------------------------------------------

--
-- Structure de la table `routine_etapes`
--

CREATE TABLE `routine_etapes` (
  `id` int(11) NOT NULL,
  `routine_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `ordre` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `routine_etapes`
--

INSERT INTO `routine_etapes` (`id`, `routine_id`, `description`, `ordre`) VALUES
(1, 1, 'Nettoyez votre visage avec le nettoyant doux.', 1),
(2, 1, 'Appliquez la crème hydratante sur tout le visage.', 2),
(3, 1, 'Terminez par la crème anti-imperfections sur les zones concernées.', 3),
(4, 2, 'Lavez vos cheveux avec le shampooing doux naturel.', 1),
(5, 2, 'Appliquez le shampooing réparateur en deuxième lavage si nécessaire.', 2),
(6, 2, 'Appliquez le masque nutritif, laissez poser 10–15 min, rincez.', 3),
(7, 3, 'Appliquez le fond de teint avec le Beauty Blender pour un effet naturel.', 1),
(8, 3, 'Définissez les yeux avec une touche de mascara.', 2),
(9, 3, 'Finalisez avec les pinceaux pour estomper parfaitement.', 3),
(10, 4, 'Appliquez le fond de teint longue tenue pour une base parfaite.', 1),
(11, 4, 'Intensifiez le regard avec le mascara volume intense.', 2),
(12, 4, 'Ajoutez le rouge à lèvres couleur intense pour un look glamour.', 3),
(13, 4, 'Vérifiez votre maquillage avec le miroir LED éclairé.', 4),
(14, 5, 'Choisissez votre parfum selon l\'occasion (jour ou soirée).', 1),
(15, 5, 'Vaporisez aux points de chaleur : poignets, cou, décolleté.', 2),
(16, 5, 'Préparez vos pinceaux pour le lendemain.', 3),
(17, 6, 'Commencez par un gommage corps pour eliminer les cellules mortes.', 1),
(18, 6, 'Rincez a l\'eau tiede puis sechez avec une serviette douce.', 2),
(19, 6, 'Appliquez votre lait corps en massant de facon circulaire.', 3),
(20, 6, 'Insistez sur les zones seches : coudes, genoux, talons.', 4),
(21, 7, 'Nettoyez votre visage avec l\'eau micellaire.', 1),
(22, 7, 'Appliquez le serum niacinamide sur peau propre — attendez 2 minutes.', 2),
(23, 7, 'Massez votre visage avec le rouleau jade pour stimuler la circulation.', 3),
(24, 7, 'Terminez avec votre creme hydratante en tapotant du bout des doigts.', 4),
(25, 8, 'Limez vos ongles et repoussez delicatement les cuticules.', 1),
(26, 8, 'Appliquez une base fortifiante et laissez secher 1 minute.', 2),
(27, 8, 'Posez deux couches fines de vernis couleur en attendant entre chaque.', 3),
(28, 8, 'Finalisez avec un top coat brillant pour une tenue prolongee.', 4),
(29, 9, 'Commencez par une douche relaxante avec un savon artisanal.', 1),
(30, 9, 'Appliquez le gommage corps puis rincez soigneusement.', 2),
(31, 9, 'Hydratez votre corps avec l\'huile de massage en mouvements lents.', 3),
(32, 9, 'Terminez par un baume levres nourrissant pour un effet cocooning.', 4);

-- --------------------------------------------------------

--
-- Structure de la table `routine_produits`
--

CREATE TABLE `routine_produits` (
  `id` int(11) NOT NULL,
  `routine_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `ordre` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `routine_produits`
--

INSERT INTO `routine_produits` (`id`, `routine_id`, `produit_id`, `ordre`) VALUES
(1, 1, 1, 1),
(2, 1, 3, 2),
(3, 1, 2, 3),
(4, 2, 6, 1),
(5, 2, 5, 2),
(6, 2, 4, 3),
(7, 3, 8, 1),
(9, 3, 13, 3),
(10, 3, 14, 4),
(11, 4, 8, 1),
(13, 4, 9, 3),
(14, 4, 15, 4),
(15, 5, 10, 1),
(16, 5, 11, 2),
(17, 5, 12, 3),
(18, 5, 13, 4),
(19, 6, 46, 1),
(21, 6, 50, 3),
(22, 6, 51, 4),
(26, 7, 17, 1),
(27, 7, 18, 2),
(28, 7, 21, 3),
(29, 7, 42, 4),
(33, 8, 53, 1),
(34, 8, 55, 2),
(35, 8, 56, 3),
(36, 8, 58, 4),
(40, 9, 50, 1);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'user',
  `adresse` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `points_fidelite` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `email`, `password`, `role`, `adresse`, `created_at`, `points_fidelite`) VALUES
(1, 'Eline Jemili', 'Eline@gmail.com', 'AL555', 'user', 'Bizerte- Bizerte', '2026-04-10 11:06:19', 22),
(2, 'Ali Ben Ali', 'ali@gmail.com', '123456', 'admin', 'Tunis - Lafayette', '2026-04-10 11:06:19', 35),
(3, 'Sara Trabelsi', 'sara@gmail.com', 'abcdef', 'user', 'Sfax - Centre Ville', '2026-04-10 11:06:19', 21),
(4, 'Mohamed Amri', 'mohamed@gmail.com', 'pass123', 'user', 'Sousse - Khezama', '2026-04-10 11:06:19', 32),
(5, 'Amira Jaziri', 'amira@gmail.com', 'mypassword', 'user', 'Ariana - Raoued', '2026-04-10 11:06:19', 51),
(6, 'Zameli LAMISS', 'zamelilamiss@gmail.com', '$2y$10$EQjgdcRYPzVqBzSpMaEfN.Ga0c/uHmVIwBCDgvU5uZAaXe5K7Urge', 'user', 'Bizerte', '2026-04-24 12:36:03', 0),
(7, 'jemili ala', 'jemiliala@gmail.com', '$2y$10$Pv5v0snCfoiiNXWkVCx2RevJFP6mWYRVFkP4U1SK1JaAF3a4tQkh6', 'user', 'Bizerte', '2026-04-24 12:36:56', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `conseils`
--
ALTER TABLE `conseils`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `historique`
--
ALTER TABLE `historique`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Index pour la table `panier`
--
ALTER TABLE `panier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Index pour la table `reclamations`
--
ALTER TABLE `reclamations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `routines`
--
ALTER TABLE `routines`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `routine_etapes`
--
ALTER TABLE `routine_etapes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `routine_id` (`routine_id`);

--
-- Index pour la table `routine_produits`
--
ALTER TABLE `routine_produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `routine_id` (`routine_id`),
  ADD KEY `fk_rp_produit` (`produit_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `conseils`
--
ALTER TABLE `conseils`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `historique`
--
ALTER TABLE `historique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pour la table `panier`
--
ALTER TABLE `panier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT pour la table `reclamations`
--
ALTER TABLE `reclamations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `routines`
--
ALTER TABLE `routines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `routine_etapes`
--
ALTER TABLE `routine_etapes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `routine_produits`
--
ALTER TABLE `routine_produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `conseils`
--
ALTER TABLE `conseils`
  ADD CONSTRAINT `conseils_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `historique`
--
ALTER TABLE `historique`
  ADD CONSTRAINT `historique_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `ligne_commande`
--
ALTER TABLE `ligne_commande`
  ADD CONSTRAINT `ligne_commande_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ligne_commande_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `panier`
--
ALTER TABLE `panier`
  ADD CONSTRAINT `panier_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `panier_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `reclamations`
--
ALTER TABLE `reclamations`
  ADD CONSTRAINT `reclamations_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `routine_etapes`
--
ALTER TABLE `routine_etapes`
  ADD CONSTRAINT `routine_etapes_ibfk_1` FOREIGN KEY (`routine_id`) REFERENCES `routines` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `routine_produits`
--
ALTER TABLE `routine_produits`
  ADD CONSTRAINT `fk_rp_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `routine_produits_ibfk_1` FOREIGN KEY (`routine_id`) REFERENCES `routines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `routine_produits_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
