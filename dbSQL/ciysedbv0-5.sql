-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-05-2025 a las 21:45:12
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
-- Base de datos: `ciysedb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `icons`
--

CREATE TABLE `icons` (
  `iconId` int(11) NOT NULL,
  `iconBi` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isDeleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `icons`
--

INSERT INTO `icons` (`iconId`, `iconBi`, `createdAt`, `updatedAt`, `isDeleted`) VALUES
(1, 'house', '2025-05-09 12:47:14', '2025-05-09 12:47:14', 0),
(2, 'speedometer', '2025-05-09 12:47:14', '2025-05-09 12:47:14', 0),
(3, 'dropbox', '2025-05-09 12:47:14', '2025-05-09 12:47:14', 0),
(4, 'clipboard-check', '2025-05-09 12:47:14', '2025-05-09 12:47:14', 0),
(5, 'door-open', '2025-05-09 12:47:14', '2025-05-09 12:47:14', 0),
(6, 'wrench', '2025-05-09 12:47:14', '2025-05-09 12:47:14', 0),
(7, 'box-arrow-in-right', '2025-05-09 12:47:14', '2025-05-09 12:47:14', 0),
(8, 'card-checklist', '2025-05-09 13:02:03', '2025-05-09 13:02:03', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `logId` int(11) NOT NULL,
  `tableName` varchar(100) NOT NULL,
  `recordId` int(11) NOT NULL,
  `actionType` enum('INSERT','UPDATE','DELETE','RESTORE') NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `oldValues` text DEFAULT NULL,
  `newValues` text DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `productId` int(11) NOT NULL,
  `productName` varchar(255) NOT NULL,
  `productDescription` text NOT NULL,
  `productStock` int(11) NOT NULL DEFAULT 0,
  `productImgPath` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isDeleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `requests`
--

CREATE TABLE `requests` (
  `requestId` int(11) NOT NULL,
  `serviceId` int(11) NOT NULL,
  `statusId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `tecId` int(11) DEFAULT NULL,
  `requestTitle` varchar(255) NOT NULL,
  `requestComments` text NOT NULL,
  `requestDate` datetime NOT NULL,
  `requestAddress` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isDeleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `request_products`
--

CREATE TABLE `request_products` (
  `requestId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `productAmount` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `roleId` int(11) NOT NULL,
  `roleName` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isDeleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`roleId`, `roleName`, `createdAt`, `updatedAt`, `isDeleted`) VALUES
(1, 'Administrador', '2025-05-09 13:08:02', '2025-05-09 13:08:02', 0),
(2, 'Cliente', '2025-05-09 13:08:02', '2025-05-09 13:08:02', 0),
(3, 'Tecnico', '2025-05-09 13:08:02', '2025-05-09 13:08:02', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_urls`
--

CREATE TABLE `role_urls` (
  `roleId` int(11) NOT NULL,
  `urlId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `services`
--

CREATE TABLE `services` (
  `serviceId` int(11) NOT NULL,
  `serviceName` varchar(255) NOT NULL,
  `serviceDescription` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isDeleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `services`
--

INSERT INTO `services` (`serviceId`, `serviceName`, `serviceDescription`, `createdAt`, `updatedAt`, `isDeleted`) VALUES
(1, 'Instalación', 'Instalación profesional de sistemas de seguridad en hogares y negocios.', '2025-05-09 12:31:21', '2025-05-09 12:31:21', 0),
(2, 'Mantenimiento', 'Mantenimiento preventivo y correctivo para sistemas de seguridad.', '2025-05-09 12:31:21', '2025-05-09 12:31:21', 0),
(3, 'Desinstalación', 'Desinstalación segura y profesional de equipos de seguridad.', '2025-05-09 12:31:21', '2025-05-09 12:31:21', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `statusrequests`
--

CREATE TABLE `statusrequests` (
  `statusId` int(11) NOT NULL,
  `statusName` varchar(255) NOT NULL,
  `statusClassName` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isDeleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `statusrequests`
--

INSERT INTO `statusrequests` (`statusId`, `statusName`, `statusClassName`, `createdAt`, `updatedAt`, `isDeleted`) VALUES
(1, 'Pendiente', 'primary', '2025-05-09 12:33:56', '2025-05-09 12:33:56', 0),
(2, 'En Proceso', 'warning', '2025-05-09 12:33:56', '2025-05-09 12:33:56', 0),
(3, 'Completado', 'success', '2025-05-09 12:33:56', '2025-05-09 12:33:56', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `urls`
--

CREATE TABLE `urls` (
  `urlId` int(11) NOT NULL,
  `iconId` int(11) DEFAULT NULL,
  `urlTitle` varchar(20) NOT NULL,
  `urlAddress` text NOT NULL,
  `showOrder` int(11) NOT NULL,
  `allowAll` tinyint(1) NOT NULL DEFAULT 0,
  `isDisabled` tinyint(1) NOT NULL DEFAULT 0,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isDeleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `urls`
--

INSERT INTO `urls` (`urlId`, `iconId`, `urlTitle`, `urlAddress`, `showOrder`, `allowAll`, `isDisabled`, `createdAt`, `updatedAt`, `isDeleted`) VALUES
(9, 1, 'Inicio', 'index.php', 1, 1, 0, '2025-05-09 13:01:07', '2025-05-09 13:01:07', 0),
(10, 3, 'Productos', 'productos.php', 2, 1, 0, '2025-05-09 13:01:07', '2025-05-09 13:01:07', 0),
(11, 6, 'Servicios', 'servicios.php', 3, 1, 0, '2025-05-09 13:01:07', '2025-05-09 13:01:07', 0),
(12, 7, 'Iniciar Sesión', 'login.php', 4, 0, 0, '2025-05-09 13:01:07', '2025-05-09 13:01:07', 0),
(13, 2, 'Dashboard', 'indexADMIN.php', 5, 0, 0, '2025-05-09 13:01:07', '2025-05-09 13:01:07', 0),
(14, 3, 'Productos', 'productosADMIN.php', 6, 0, 0, '2025-05-09 13:01:07', '2025-05-09 13:01:07', 0),
(15, 8, 'Solicitudes', 'solicitudesADMIN.php', 7, 0, 0, '2025-05-09 13:01:07', '2025-05-09 13:02:51', 0),
(16, 5, 'Cerrar Sesión', 'logout.php', 8, 0, 0, '2025-05-09 13:01:07', '2025-05-09 13:01:07', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `userName` varchar(20) NOT NULL,
  `userMail` varchar(100) NOT NULL,
  `userPhone` bigint(20) NOT NULL,
  `userPass` text NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `updatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `isDeleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `icons`
--
ALTER TABLE `icons`
  ADD PRIMARY KEY (`iconId`),
  ADD UNIQUE KEY `iconBi` (`iconBi`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`logId`),
  ADD KEY `userId` (`userId`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productId`);

--
-- Indices de la tabla `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`requestId`),
  ADD KEY `serviceId` (`serviceId`),
  ADD KEY `statusId` (`statusId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `tecId` (`tecId`);

--
-- Indices de la tabla `request_products`
--
ALTER TABLE `request_products`
  ADD UNIQUE KEY `requestId_2` (`requestId`,`productId`),
  ADD KEY `requestId` (`requestId`),
  ADD KEY `productId` (`productId`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`roleId`),
  ADD UNIQUE KEY `roleName` (`roleName`);

--
-- Indices de la tabla `role_urls`
--
ALTER TABLE `role_urls`
  ADD UNIQUE KEY `roleId_2` (`roleId`,`urlId`),
  ADD KEY `roleId` (`roleId`),
  ADD KEY `urlId` (`urlId`);

--
-- Indices de la tabla `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`serviceId`),
  ADD UNIQUE KEY `serviceName` (`serviceName`);

--
-- Indices de la tabla `statusrequests`
--
ALTER TABLE `statusrequests`
  ADD PRIMARY KEY (`statusId`),
  ADD UNIQUE KEY `statusName` (`statusName`);

--
-- Indices de la tabla `urls`
--
ALTER TABLE `urls`
  ADD PRIMARY KEY (`urlId`),
  ADD UNIQUE KEY `showOrder` (`showOrder`),
  ADD UNIQUE KEY `urlAddress` (`urlAddress`) USING HASH,
  ADD KEY `iconId` (`iconId`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `userMail` (`userMail`),
  ADD KEY `roleId` (`roleId`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `icons`
--
ALTER TABLE `icons`
  MODIFY `iconId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `logId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `productId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `requests`
--
ALTER TABLE `requests`
  MODIFY `requestId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `roleId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `services`
--
ALTER TABLE `services`
  MODIFY `serviceId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `statusrequests`
--
ALTER TABLE `statusrequests`
  MODIFY `statusId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `urls`
--
ALTER TABLE `urls`
  MODIFY `urlId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`serviceId`) REFERENCES `services` (`serviceId`),
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`statusId`) REFERENCES `statusrequests` (`statusId`),
  ADD CONSTRAINT `requests_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  ADD CONSTRAINT `requests_ibfk_4` FOREIGN KEY (`tecId`) REFERENCES `users` (`userId`);

--
-- Filtros para la tabla `request_products`
--
ALTER TABLE `request_products`
  ADD CONSTRAINT `request_products_ibfk_1` FOREIGN KEY (`requestId`) REFERENCES `requests` (`requestId`),
  ADD CONSTRAINT `request_products_ibfk_2` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`);

--
-- Filtros para la tabla `role_urls`
--
ALTER TABLE `role_urls`
  ADD CONSTRAINT `role_urls_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `roles` (`roleId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `role_urls_ibfk_2` FOREIGN KEY (`urlId`) REFERENCES `urls` (`urlId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `urls`
--
ALTER TABLE `urls`
  ADD CONSTRAINT `urls_ibfk_1` FOREIGN KEY (`iconId`) REFERENCES `icons` (`iconId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `roles` (`roleId`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
