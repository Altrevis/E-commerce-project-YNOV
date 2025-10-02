-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : jeu. 02 oct. 2025 à 09:37
-- Version du serveur : 8.0.43-0ubuntu0.24.04.1
-- Version de PHP : 8.3.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ecommerce-ynov`
--

-- --------------------------------------------------------

--
-- Structure de la table `adresses`
--

CREATE TABLE `adresses` (
  `id_adresse` int NOT NULL,
  `id_client` int NOT NULL,
  `adresse_ligne1` varchar(255) NOT NULL,
  `adresse_ligne2` varchar(255) DEFAULT NULL,
  `ville` varchar(100) NOT NULL,
  `code_postal` varchar(20) NOT NULL,
  `pays` varchar(100) NOT NULL,
  `type_adresse` enum('facturation','livraison') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `adresses`
--

INSERT INTO `adresses` (`id_adresse`, `id_client`, `adresse_ligne1`, `adresse_ligne2`, `ville`, `code_postal`, `pays`, `type_adresse`) VALUES
(1, 1, '10 rue de Paris', NULL, 'Paris', '75001', 'France', 'facturation'),
(2, 1, '12 rue de Lyon', NULL, 'Lyon', '69001', 'France', 'livraison'),
(3, 2, '5 avenue de la Gare', NULL, 'Bordeaux', '33000', 'France', 'facturation'),
(4, 3, '20 boulevard Saint-Michel', NULL, 'Marseille', '13001', 'France', 'livraison');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id_categorie` int NOT NULL,
  `nom_categorie` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id_categorie`, `nom_categorie`, `description`) VALUES
(1, 'Informatique', 'Ordinateurs, périphériques et accessoires'),
(2, 'Smartphones', 'Téléphones et accessoires'),
(3, 'Électroménager', 'Produits pour la maison'),
(4, 'Jeux vidéo', 'Consoles et jeux');

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE `clients` (
  `id_client` int NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('client','vendeur','gestionnaire_stock','comptable') DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id_client`, `prenom`, `nom`, `email`, `telephone`, `mot_de_passe`, `date_creation`, `role`) VALUES
(1, 'Jean', 'Dupont', 'jean.dupont@email.com', '0600000001', 'hash123', '2025-10-02 07:47:58', 'client'),
(2, 'Marie', 'Durand', 'marie.durand@email.com', '0600000002', 'hash456', '2025-10-02 07:47:58', 'client'),
(3, 'Paul', 'Martin', 'paul.martin@email.com', '0600000003', 'hash789', '2025-10-02 07:47:58', 'vendeur'),
(4, 'Claire', 'Bernard', 'claire.bernard@email.com', '0600000004', 'hash321', '2025-10-02 07:47:58', 'gestionnaire_stock'),
(5, 'Luc', 'Petit', 'luc.petit@email.com', '0600000005', 'hash654', '2025-10-02 07:47:58', 'comptable');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id_commande` int NOT NULL,
  `id_client` int NOT NULL,
  `date_commande` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('en cours','expédiée','livrée','annulée') DEFAULT 'en cours',
  `id_adresse_livraison` int DEFAULT NULL,
  `id_adresse_facturation` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id_commande`, `id_client`, `date_commande`, `statut`, `id_adresse_livraison`, `id_adresse_facturation`) VALUES
(1, 1, '2025-10-02 07:48:40', 'en cours', 2, 1),
(2, 2, '2025-10-02 07:48:40', 'expédiée', 3, 3),
(3, 3, '2025-10-02 07:48:40', 'livrée', 4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `fournisseurs`
--

CREATE TABLE `fournisseurs` (
  `id_fournisseur` int NOT NULL,
  `nom_fournisseur` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `fournisseurs`
--

INSERT INTO `fournisseurs` (`id_fournisseur`, `nom_fournisseur`, `email`, `telephone`, `adresse`) VALUES
(1, 'TechWorld', 'contact@techworld.com', '0101010101', '100 rue de l’Innovation, Paris'),
(2, 'PhoneShop', 'contact@phoneshop.com', '0202020202', '50 avenue du Mobile, Lyon'),
(3, 'ElectroHome', 'contact@electrohome.com', '0303030303', '200 boulevard Maison, Marseille');

-- --------------------------------------------------------

--
-- Structure de la table `lignes_commandes`
--

CREATE TABLE `lignes_commandes` (
  `id_ligne` int NOT NULL,
  `id_commande` int NOT NULL,
  `id_produit` int NOT NULL,
  `quantite` int NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `lignes_commandes`
--

INSERT INTO `lignes_commandes` (`id_ligne`, `id_commande`, `id_produit`, `quantite`, `prix_unitaire`) VALUES
(1, 1, 1, 1, 1299.99),
(2, 1, 2, 2, 89.99),
(3, 2, 3, 1, 999.99),
(4, 3, 5, 1, 549.99);

-- --------------------------------------------------------

--
-- Structure de la table `livraisons`
--

CREATE TABLE `livraisons` (
  `id_livraison` int NOT NULL,
  `id_commande` int NOT NULL,
  `date_expedition` date DEFAULT NULL,
  `date_livraison` date DEFAULT NULL,
  `transporteur` varchar(100) DEFAULT NULL,
  `statut_livraison` enum('en cours','livrée','retardée') DEFAULT 'en cours'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `livraisons`
--

INSERT INTO `livraisons` (`id_livraison`, `id_commande`, `date_expedition`, `date_livraison`, `transporteur`, `statut_livraison`) VALUES
(1, 1, '2025-10-01', NULL, 'Colissimo', 'en cours'),
(2, 2, '2025-09-28', '2025-09-30', 'UPS', 'livrée'),
(3, 3, '2025-09-29', NULL, 'DHL', 'retardée');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id_paiement` int NOT NULL,
  `id_commande` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `mode_paiement` enum('CB','PayPal','Virement') NOT NULL,
  `date_paiement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut_paiement` enum('validé','en attente','échoué') DEFAULT 'en attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id_paiement`, `id_commande`, `montant`, `mode_paiement`, `date_paiement`, `statut_paiement`) VALUES
(1, 1, 1479.97, 'CB', '2025-10-02 07:49:04', 'validé'),
(2, 2, 999.99, 'PayPal', '2025-10-02 07:49:04', 'validé'),
(3, 3, 549.99, 'Virement', '2025-10-02 07:49:04', 'en attente');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id_produit` int NOT NULL,
  `nom_produit` varchar(150) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  `stock` int DEFAULT '0',
  `id_categorie` int DEFAULT NULL,
  `id_fournisseur` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id_produit`, `nom_produit`, `description`, `prix`, `stock`, `id_categorie`, `id_fournisseur`) VALUES
(1, 'PC Portable Gamer', '16Go RAM, RTX 3060', 1299.99, 20, 1, 1),
(2, 'Clavier Mécanique RGB', 'Switchs rouges', 89.99, 50, 1, 1),
(3, 'iPhone 14', '128Go, Noir', 999.99, 30, 2, 2),
(4, 'Aspirateur Dyson', 'Sans sac, haute puissance', 499.99, 15, 3, 3),
(5, 'PlayStation 5', 'Console next-gen Sony', 549.99, 10, 4, 1);

-- --------------------------------------------------------

--
-- Structure de la table `remboursements`
--

CREATE TABLE `remboursements` (
  `id_remboursement` int NOT NULL,
  `id_retour` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_remboursement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut_remboursement` enum('validé','en attente','refusé') DEFAULT 'en attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `retours`
--

CREATE TABLE `retours` (
  `id_retour` int NOT NULL,
  `id_commande` int NOT NULL,
  `id_produit` int NOT NULL,
  `quantite` int NOT NULL,
  `motif` text,
  `date_retour` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `adresses`
--
ALTER TABLE `adresses`
  ADD PRIMARY KEY (`id_adresse`),
  ADD KEY `id_client` (`id_client`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id_client`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id_commande`),
  ADD KEY `id_client` (`id_client`),
  ADD KEY `id_adresse_livraison` (`id_adresse_livraison`),
  ADD KEY `id_adresse_facturation` (`id_adresse_facturation`);

--
-- Index pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  ADD PRIMARY KEY (`id_fournisseur`);

--
-- Index pour la table `lignes_commandes`
--
ALTER TABLE `lignes_commandes`
  ADD PRIMARY KEY (`id_ligne`),
  ADD KEY `id_commande` (`id_commande`),
  ADD KEY `id_produit` (`id_produit`);

--
-- Index pour la table `livraisons`
--
ALTER TABLE `livraisons`
  ADD PRIMARY KEY (`id_livraison`),
  ADD KEY `id_commande` (`id_commande`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id_paiement`),
  ADD KEY `id_commande` (`id_commande`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id_produit`),
  ADD KEY `id_categorie` (`id_categorie`),
  ADD KEY `id_fournisseur` (`id_fournisseur`);

--
-- Index pour la table `remboursements`
--
ALTER TABLE `remboursements`
  ADD PRIMARY KEY (`id_remboursement`),
  ADD KEY `id_retour` (`id_retour`);

--
-- Index pour la table `retours`
--
ALTER TABLE `retours`
  ADD PRIMARY KEY (`id_retour`),
  ADD KEY `id_commande` (`id_commande`),
  ADD KEY `id_produit` (`id_produit`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `adresses`
--
ALTER TABLE `adresses`
  MODIFY `id_adresse` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id_categorie` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `clients`
--
ALTER TABLE `clients`
  MODIFY `id_client` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id_commande` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  MODIFY `id_fournisseur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `lignes_commandes`
--
ALTER TABLE `lignes_commandes`
  MODIFY `id_ligne` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `livraisons`
--
ALTER TABLE `livraisons`
  MODIFY `id_livraison` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id_paiement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id_produit` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `remboursements`
--
ALTER TABLE `remboursements`
  MODIFY `id_remboursement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `retours`
--
ALTER TABLE `retours`
  MODIFY `id_retour` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `adresses`
--
ALTER TABLE `adresses`
  ADD CONSTRAINT `adresses_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id_client`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id_client`) ON DELETE CASCADE,
  ADD CONSTRAINT `commandes_ibfk_2` FOREIGN KEY (`id_adresse_livraison`) REFERENCES `adresses` (`id_adresse`) ON DELETE SET NULL,
  ADD CONSTRAINT `commandes_ibfk_3` FOREIGN KEY (`id_adresse_facturation`) REFERENCES `adresses` (`id_adresse`) ON DELETE SET NULL;

--
-- Contraintes pour la table `lignes_commandes`
--
ALTER TABLE `lignes_commandes`
  ADD CONSTRAINT `lignes_commandes_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id_commande`) ON DELETE CASCADE,
  ADD CONSTRAINT `lignes_commandes_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id_produit`) ON DELETE CASCADE;

--
-- Contraintes pour la table `livraisons`
--
ALTER TABLE `livraisons`
  ADD CONSTRAINT `livraisons_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id_commande`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id_commande`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id_categorie`) ON DELETE SET NULL,
  ADD CONSTRAINT `produits_ibfk_2` FOREIGN KEY (`id_fournisseur`) REFERENCES `fournisseurs` (`id_fournisseur`) ON DELETE SET NULL;

--
-- Contraintes pour la table `remboursements`
--
ALTER TABLE `remboursements`
  ADD CONSTRAINT `remboursements_ibfk_1` FOREIGN KEY (`id_retour`) REFERENCES `retours` (`id_retour`) ON DELETE CASCADE;

--
-- Contraintes pour la table `retours`
--
ALTER TABLE `retours`
  ADD CONSTRAINT `retours_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id_commande`) ON DELETE CASCADE,
  ADD CONSTRAINT `retours_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id_produit`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
