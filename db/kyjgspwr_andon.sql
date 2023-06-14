-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Mag 12, 2023 alle 14:39
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
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `andon_board`
--

INSERT INTO `andon_board` (`id`, `id_operatore`, `id_risorsa`, `id_ciclo`, `orario`, `num_pz_ora`, `num_pz_realizzati`, `num_pz_scarti`, `pranzo`, `note`) VALUES
(4, 1, 1, 1, '12-13', 5, 3, 0, 0, ''),
(5, 1, 1, 1, '13-14', 5, 5, 0, NULL, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `cicli`
--

CREATE TABLE `cicli` (
  `id_ciclo` int(11) UNSIGNED NOT NULL,
  `codice_ciclo` varchar(64) NOT NULL,
  `tempo_ciclo` int(11) UNSIGNED NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `cicli`
--

INSERT INTO `cicli` (`id_ciclo`, `codice_ciclo`, `tempo_ciclo`, `created_at`) VALUES
(1, '01', 660, '2023-05-03'),
(2, '02', 550, '2023-05-03');

-- --------------------------------------------------------

--
-- Struttura della tabella `operatori`
--

CREATE TABLE `operatori` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `sigla` varchar(10) NOT NULL,
  `nome` varchar(64) NOT NULL,
  `cognome` varchar(64) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `operatori`
--

INSERT INTO `operatori` (`id`, `sigla`, `nome`, `cognome`, `created_at`) VALUES
(1, 'SG', 'Gloria', 'Spada', '2023-05-08'),
(2, 'ZG', 'Geronimo', 'Zuniga', '2023-05-08'),
(3, 'CG', 'Cams', 'Gabriel', '2023-05-08');

-- --------------------------------------------------------

--
-- Struttura della tabella `risorse`
--

CREATE TABLE `risorse` (
  `id` int(10) UNSIGNED NOT NULL,
  `risorsa` varchar(8) NOT NULL,
  `nome_risorsa` varchar(128) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `risorse`
--

INSERT INTO `risorse` (`id`, `risorsa`, `nome_risorsa`, `note`, `created_at`) VALUES
(1, '022', 'Brother', 'Brother nuovo', '2023-05-08');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(254) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'user', '$2y$10$Dq/3jKj2Z/EhNFmKuTZjEOdQcNOgYgvbyvnf3LEpxm.dgLWG/qwp2'),
(2, 'andon_021', '$2y$10$Dq/3jKj2Z/EhNFmKuTZjEOdQcNOgYgvbyvnf3LEpxm.dgLWG/qwp2'),
(3, 'andon_020', '$2y$10$Dq/3jKj2Z/EhNFmKuTZjEOdQcNOgYgvbyvnf3LEpxm.dgLWG/qwp2'),
(4, 'andon_011', '$2y$10$Dq/3jKj2Z/EhNFmKuTZjEOdQcNOgYgvbyvnf3LEpxm.dgLWG/qwp2'),
(5, 'andon_004', '$2y$10$Dq/3jKj2Z/EhNFmKuTZjEOdQcNOgYgvbyvnf3LEpxm.dgLWG/qwp2');

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
-- Indici per le tabelle `cicli`
--
ALTER TABLE `cicli`
  ADD PRIMARY KEY (`id_ciclo`);

--
-- Indici per le tabelle `operatori`
--
ALTER TABLE `operatori`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `risorse`
--
ALTER TABLE `risorse`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `andon_board`
--
ALTER TABLE `andon_board`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `cicli`
--
ALTER TABLE `cicli`
  MODIFY `id_ciclo` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `operatori`
--
ALTER TABLE `operatori`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `risorse`
--
ALTER TABLE `risorse`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
