-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-04-2026 a las 23:02:49
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
-- Base de datos: `db_academica`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `id` int(11) NOT NULL,
  `idAlumno` char(36) NOT NULL,
  `codigo` char(20) NOT NULL,
  `nombre` char(100) NOT NULL,
  `direccion` char(150) NOT NULL,
  `telefono` char(10) NOT NULL,
  `email` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO `alumnos` (`id`, `idAlumno`, `codigo`, `nombre`, `direccion`, `telefono`, `email`) VALUES
(10, 'ca9bfad3-57f1-496f-b865-3204c082c566', 'USSS000007', 'Cristiano Ronaldo dos Santos Aveiro', 'Portugal', '7777-7777', 'CR7@realmadrid.fc'),
(11, '394c9531-8a89-45da-a93d-94f3362a5d64', 'PSG000025', 'Nuno Mendez', 'Portugal', '0025-0025', 'NM25@psg.fc'),
(8, '01debd76-0598-47b9-b800-c77d93cb1d48', 'USSS027724', 'ZIdane', 'FRANCIA', '4777-6999', 'zidane@realmadrid.fc');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id` int(11) NOT NULL,
  `Id_Docentes` char(36) NOT NULL,
  `codigo` char(20) NOT NULL,
  `nombre` char(100) NOT NULL,
  `direccion` char(150) NOT NULL,
  `telefono` char(10) NOT NULL,
  `email` text NOT NULL,
  `escalafon` char(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id`, `Id_Docentes`, `codigo`, `nombre`, `direccion`, `telefono`, `email`, `escalafon`) VALUES
(1, '36f039b4-7c61-4203-858b-9dda84e28fba', 'Usis44', 'luis', 'batres', '78787878', 'josechavarria241073@gmail.com', 'ingeniero'),
(3, 'fc9aae4b-f4ec-4c52-9b7a-38df5770fe83', 'usus24', 'torres', 'usu', '4265-1425', 'torres@gmail.com', 'ingeniero'),
(12, 'f31a43ae-2ec8-422f-9c42-061f0d3c9ad0', 'USIS02444', 'Luis hernandez', 'El transito', '5477-5987', 'luis@ugb.edu.sv', 'ingeniero');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `idInscripcion` int(11) NOT NULL,
  `idAlumno` char(36) NOT NULL,
  `idMateria` char(36) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`idInscripcion`, `idAlumno`, `idMateria`, `fecha`) VALUES
(7, 'ca9bfad3-57f1-496f-b865-3204c082c566', '069d1211-cac7-4b75-993e-4b436b30f9ea', '2026-04-18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `idMateria` char(36) NOT NULL,
  `codigo` char(20) NOT NULL,
  `nombre` char(100) NOT NULL,
  `uv` char(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `idMateria`, `codigo`, `nombre`, `uv`) VALUES
(1, 'b3ab4561-131e-4170-8508-3ba613dd8329', '410', 'redes', '4'),
(2, '1657d5de-b9ad-46bb-b8d8-2e528e6adf5e', 'MAT001', 'Programacion IV', '4'),
(3, '069d1211-cac7-4b75-993e-4b436b30f9ea', 'MAT01', 'Programacion IV', '4');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `idMatricula` char(36) NOT NULL,
  `idAlumno` char(36) NOT NULL,
  `idMateria` char(36) NOT NULL,
  `idDocente` char(36) NOT NULL,
  `fecha` date NOT NULL,
  `estado` char(20) NOT NULL,
  `periodo` char(20) NOT NULL,
  `gestion` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `matriculas`
--

INSERT INTO `matriculas` (`id`, `idMatricula`, `idAlumno`, `idMateria`, `idDocente`, `fecha`, `estado`, `periodo`, `gestion`) VALUES
(9, 'ec5f8c12-7789-4bb2-a56f-77e6e3cae868', '394c9531-8a89-45da-a93d-94f3362a5d64', 'b3ab4561-131e-4170-8508-3ba613dd8329', 'fc9aae4b-f4ec-4c52-9b7a-38df5770fe83', '2026-03-25', 'INACTIVO', 'Ciclo I', 2026),
(15, '5f71cdef-de6c-461b-bdc5-b589e4e96b42', '394c9531-8a89-45da-a93d-94f3362a5d64', 'b3ab4561-131e-4170-8508-3ba613dd8329', 'f31a43ae-2ec8-422f-9c42-061f0d3c9ad0', '2026-04-21', 'activo', 'CICLO I', 2026);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`idInscripcion`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `idInscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
