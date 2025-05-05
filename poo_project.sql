-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-05-2025 a las 02:53:19
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `poo_project`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `nombre` varchar(60) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `password` varchar(50) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`nombre`, `correo`, `telefono`, `password`, `id`) VALUES
('Sara Esmeralda Lopez', 'saralopezzzz@gmail.com', '8192830419', 'susy', 1),
('Ririr', 'angel@gmail.com', '92931813222222', 'roro', 3),
('Angel', 'kiko@gmail.com', '92931813222222', 'ssoossos', 4),
('Angel', 'kiko@gmail.com', '92931813222222', 'sdkoasdas', 5),
('Luis Miguel', 'kiko@gmail.com', '81292832894', 'sosososos', 6),
('Walter', 'kiko@gmail.com', '81292832894', 'sosososos', 7),
('Walter', 'kiko@gmail.com', '81292832894', 'sosososos', 8),
('Miranda', 'kiko@gmail.com', '81292832894', 'WSJDSJSD', 9),
('Roberto Lopez Bolanos', 'bolanos@gmail.com', '192093928482939', 'lul', 11),
('Paloma Peruana', 'perrupe@gmail.com', '81920394212', 'peruanita', 12),
('Manuel', 'tonto@gmail.com', '091823892', 'angelo', 13),
('Triunviratus', 'tonto@gmail.com', '81920394212', 'angelo', 14),
('Kika nieto', 'sero@gmail.com', '000000000000', 'sero', 15),
('Mimo', 'mimo@gmail.com', '9282492931', 'aro', 16),
('osodosod', 'mimo@gmail.com', '9282492931', 'sosa', 17),
('Tonto', 'yo@gmail.com', '81923842942', 'yoyo', 18);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
