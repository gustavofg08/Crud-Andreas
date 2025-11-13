-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13-Nov-2025 às 20:37
-- Versão do servidor: 10.4.27-MariaDB
-- versão do PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `touchyourbutton`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `dataHora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `uploads`
--

CREATE TABLE `uploads` (
  `id` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `fotoPerfil` varchar(255) DEFAULT NULL,
  `audio` varchar(255) DEFAULT NULL,
  `dataUpload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `uploads`
--

INSERT INTO `uploads` (`id`, `idUsuario`, `fotoPerfil`, `audio`, `dataUpload`) VALUES
(1, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(2, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(3, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(4, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(5, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(6, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(7, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(8, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(9, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(10, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(11, 13, '69162bbbbac48_big-monke-flips-you-off-what-u-do-v0-861gk9gqka0c1.png', NULL, '2025-11-13 19:04:27'),
(12, 13, 'foto_13_1763060711.png', NULL, '2025-11-13 19:05:11'),
(13, 13, 'pfp_13_1763061066.png', NULL, '2025-11-13 19:11:06'),
(14, 13, NULL, 'audio_13_1763061066.mp3', '2025-11-13 19:11:06');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuario`
--

INSERT INTO `usuario` (`id`, `nome`, `senha`) VALUES
(1, 'GustavoAdmin', 'senhasupersecretafodastica'),
(2, '', ''),
(3, 'Testes', '123'),
(5, 'Testes2', '1234'),
(12, 'AdminGrande', '$2y$10$YrxRwkpVJUYBTWE1VCGWx.10N2WAyzGhCYN8OJxnyA0jf4XZLaQna'),
(13, 'AdminPequeno', '$2y$10$fL5Lh4BlCDmU1RS9ybzhVu1.Y2ryU4RvIKaub/Ff/.s9UejHL3fQS');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `senha` (`senha`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `uploads`
--
ALTER TABLE `uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
