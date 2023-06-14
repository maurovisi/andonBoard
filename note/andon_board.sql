-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Mag 31, 2023 alle 17:59
-- Versione del server: 10.3.38-MariaDB-cll-lve
-- Versione PHP: 8.1.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kyjgspwr_andon`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `andon_board`
--

CREATE TABLE `andon_board` (
  `id` int(11) NOT NULL,
  `id_operatore` smallint(5) UNSIGNED NOT NULL,
  `id_risorsa` int(11) UNSIGNED NOT NULL,
  `id_ciclo` int(11) UNSIGNED NOT NULL,
  `orario` varchar(255) NOT NULL,
  `num_pz_ora` smallint(5) UNSIGNED NOT NULL,
  `num_pz_realizzati` smallint(5) UNSIGNED NOT NULL,
  `num_pz_scarti` smallint(5) UNSIGNED DEFAULT NULL,
  `pranzo` tinyint(1) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `data_turno` date NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `andon_board`
--
ALTER TABLE `andon_board`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cod_lavorazione` (`id_ciclo`),
  ADD KEY `id_risorsa` (`id_risorsa`),
  ADD KEY `id_operatore` (`id_operatore`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `andon_board`
--
ALTER TABLE `andon_board`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `andon_board`
--
ALTER TABLE `andon_board`
  ADD CONSTRAINT `andon_board_ibfk_1` FOREIGN KEY (`id_ciclo`) REFERENCES `cicli` (`id_ciclo`),
  ADD CONSTRAINT `andon_board_ibfk_2` FOREIGN KEY (`id_risorsa`) REFERENCES `risorse` (`id`),
  ADD CONSTRAINT `andon_board_ibfk_3` FOREIGN KEY (`id_operatore`) REFERENCES `operatori` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
