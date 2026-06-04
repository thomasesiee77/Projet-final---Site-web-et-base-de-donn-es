-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql-dump-peche-ybert-delafollye-dougdad-perrot.alwaysdata.net
-- Generation Time: Jun 04, 2026 at 01:12 PM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dump-peche-ybert-delafollye-dougdad-perrot_1`
--

-- --------------------------------------------------------

--
-- Table structure for table `CAPTURE`
--

CREATE TABLE `CAPTURE` (
  `id_capture` int(11) NOT NULL,
  `date_capture` date NOT NULL,
  `poids` int(11) DEFAULT NULL,
  `taille` int(11) DEFAULT NULL,
  `id_pecheur` int(11) DEFAULT NULL,
  `id_lieu` int(11) DEFAULT NULL,
  `id_espece` int(11) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CAPTURE`
--

INSERT INTO `CAPTURE` (`id_capture`, `date_capture`, `poids`, `taille`, `id_pecheur`, `id_lieu`, `id_espece`, `latitude`, `longitude`) VALUES
(6, '2026-06-04', 450, 70, 11, 5, 1, 48.862209, 2.626934),
(7, '2026-06-04', 100, 100, 12, 6, 4, 48.843911, 2.547283),
(8, '2026-06-04', 11, 123, 13, 9, 1, 48.86772, -3.222122);

-- --------------------------------------------------------

--
-- Table structure for table `ESPECE`
--

CREATE TABLE `ESPECE` (
  `id_espece` int(11) NOT NULL,
  `nom_espece` varchar(50) NOT NULL,
  `taille_legale` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ESPECE`
--

INSERT INTO `ESPECE` (`id_espece`, `nom_espece`, `taille_legale`) VALUES
(1, 'Brochet', 60),
(2, 'Carpe', 0),
(3, 'Truite', 23),
(4, 'Chaussure', 23),
(6, 'Sandre', NULL),
(7, 'Maquereau', NULL),
(8, 'Bar', NULL),
(9, 'Daurade', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `LIEU`
--

CREATE TABLE `LIEU` (
  `id_lieu` int(11) NOT NULL,
  `nom_lieu` varchar(100) NOT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `categorie_piscicole` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `LIEU`
--

INSERT INTO `LIEU` (`id_lieu`, `nom_lieu`, `ville`, `categorie_piscicole`) VALUES
(5, 'Vaires-sur-Marne', NULL, NULL),
(6, 'Noisy-le-Grand', NULL, NULL),
(9, 'Plougrescant', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `PECHEUR`
--

CREATE TABLE `PECHEUR` (
  `id_pecheur` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `niveau` varchar(20) DEFAULT NULL,
  `est_valide` tinyint(1) DEFAULT 0,
  `est_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `PECHEUR`
--

INSERT INTO `PECHEUR` (`id_pecheur`, `nom`, `prenom`, `email`, `mot_de_passe`, `niveau`, `est_valide`, `est_admin`) VALUES
(11, 'Ybert', 'Thomas', 'thomas.ybert@edu.esiee.fr', '$2y$12$rZhjrnAd9EWnz6AxntDqUO9BIBZdheCA4YK3MZMyE0EbZtSMqnSLW', NULL, 1, 1),
(12, 'Dougdag', 'Boris', 'boris.dougdag@edu.esiee.fr', '$2y$12$bSJbVnPUd/GgP6u4Q4I./uKr3JdnHnfKNQJVn50pdiv3yggbK8pOm', NULL, 1, 1),
(13, 'de Joux', 'Thomas', 'tomas2joux@gmail.com', '$2y$12$InNHEMjNp9Oge/4qiaOQWe/oRcvzDq0kRk2INqDYcWzGCerhQkKeu', NULL, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `CAPTURE`
--
ALTER TABLE `CAPTURE`
  ADD PRIMARY KEY (`id_capture`),
  ADD KEY `id_pecheur` (`id_pecheur`),
  ADD KEY `id_lieu` (`id_lieu`),
  ADD KEY `id_espece` (`id_espece`);

--
-- Indexes for table `ESPECE`
--
ALTER TABLE `ESPECE`
  ADD PRIMARY KEY (`id_espece`);

--
-- Indexes for table `LIEU`
--
ALTER TABLE `LIEU`
  ADD PRIMARY KEY (`id_lieu`);

--
-- Indexes for table `PECHEUR`
--
ALTER TABLE `PECHEUR`
  ADD PRIMARY KEY (`id_pecheur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `CAPTURE`
--
ALTER TABLE `CAPTURE`
  MODIFY `id_capture` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ESPECE`
--
ALTER TABLE `ESPECE`
  MODIFY `id_espece` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `LIEU`
--
ALTER TABLE `LIEU`
  MODIFY `id_lieu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `PECHEUR`
--
ALTER TABLE `PECHEUR`
  MODIFY `id_pecheur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `CAPTURE`
--
ALTER TABLE `CAPTURE`
  ADD CONSTRAINT `CAPTURE_ibfk_1` FOREIGN KEY (`id_pecheur`) REFERENCES `PECHEUR` (`id_pecheur`) ON DELETE CASCADE,
  ADD CONSTRAINT `CAPTURE_ibfk_2` FOREIGN KEY (`id_lieu`) REFERENCES `LIEU` (`id_lieu`),
  ADD CONSTRAINT `CAPTURE_ibfk_3` FOREIGN KEY (`id_espece`) REFERENCES `ESPECE` (`id_espece`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
