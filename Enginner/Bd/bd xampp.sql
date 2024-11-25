-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2024 at 04:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shelfwise`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrador`
--

CREATE TABLE `administrador` (
  `Id_Administrador` int(11) NOT NULL,
  `Primer_Nombre` varchar(45) NOT NULL,
  `Segundo_Nombre` varchar(45) DEFAULT NULL,
  `Primer_Apellido` varchar(45) NOT NULL,
  `Segundo_Apellido` varchar(45) DEFAULT NULL,
  `Telefono` varchar(45) DEFAULT NULL,
  `Edad` varchar(45) DEFAULT NULL,
  `Email` varchar(45) NOT NULL,
  `Contraseña` varchar(45) NOT NULL,
  `Rol_idRol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `autor`
--

CREATE TABLE `autor` (
  `idAutor` int(11) NOT NULL,
  `Primer_Nombre` varchar(45) NOT NULL,
  `Segundo_Nombre` varchar(45) DEFAULT NULL,
  `Primer_Apellido` varchar(45) NOT NULL,
  `Segundo_Apellido` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detalle_prestamo`
--

CREATE TABLE `detalle_prestamo` (
  `Libro_idLibro` int(11) NOT NULL,
  `Prestamo_id_Prestamo` int(11) NOT NULL,
  `Prestamo_Lector_idLector` int(11) NOT NULL,
  `Prestamo_Administrador_Id_Administrador` int(11) NOT NULL,
  `Cantidad` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lector`
--

CREATE TABLE `lector` (
  `idLector` int(11) NOT NULL,
  `Primer_Nombre` varchar(45) NOT NULL,
  `Segundo_Nombre` varchar(45) DEFAULT NULL,
  `Primer_Apellido` varchar(45) NOT NULL,
  `Segundo_Apellido` varchar(45) DEFAULT NULL,
  `Telefono` varchar(45) DEFAULT NULL,
  `Correo` varchar(45) DEFAULT NULL,
  `Identificacion` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `libro`
--

CREATE TABLE `libro` (
  `idLibro` int(11) NOT NULL,
  `Editorial` varchar(45) DEFAULT NULL,
  `Titulo` varchar(45) NOT NULL,
  `Autor_idAutor` int(11) NOT NULL,
  `Genero` varchar(45) NOT NULL,
  `Cantidad_Libro` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prestamo`
--

CREATE TABLE `prestamo` (
  `id_Prestamo` int(11) NOT NULL,
  `Fecha_Prestamo` date DEFAULT NULL,
  `Fecha_Devolucion` date DEFAULT NULL,
  `Lector_idLector` int(11) NOT NULL,
  `Administrador_Id_Administrador` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rol`
--

CREATE TABLE `rol` (
  `idRol` int(11) NOT NULL,
  `Nombre` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`Id_Administrador`),
  ADD KEY `fk_usuario_Rol10` (`Rol_idRol`);

--
-- Indexes for table `autor`
--
ALTER TABLE `autor`
  ADD PRIMARY KEY (`idAutor`);

--
-- Indexes for table `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  ADD PRIMARY KEY (`Libro_idLibro`,`Prestamo_id_Prestamo`,`Prestamo_Lector_idLector`,`Prestamo_Administrador_Id_Administrador`),
  ADD KEY `fk_Detalle_Prestamo_Prestamo` (`Prestamo_id_Prestamo`,`Prestamo_Lector_idLector`,`Prestamo_Administrador_Id_Administrador`);

--
-- Indexes for table `lector`
--
ALTER TABLE `lector`
  ADD PRIMARY KEY (`idLector`);

--
-- Indexes for table `libro`
--
ALTER TABLE `libro`
  ADD PRIMARY KEY (`idLibro`),
  ADD KEY `fk_Libro_Autor1` (`Autor_idAutor`);

--
-- Indexes for table `prestamo`
--
ALTER TABLE `prestamo`
  ADD PRIMARY KEY (`id_Prestamo`,`Lector_idLector`,`Administrador_Id_Administrador`),
  ADD KEY `fk_Prestamo_Lector1` (`Lector_idLector`),
  ADD KEY `fk_Prestamo_Administrador1` (`Administrador_Id_Administrador`);

--
-- Indexes for table `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idRol`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lector`
--
ALTER TABLE `lector`
  MODIFY `idLector` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `libro`
--
ALTER TABLE `libro`
  MODIFY `idLibro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prestamo`
--
ALTER TABLE `prestamo`
  MODIFY `id_Prestamo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rol`
--
ALTER TABLE `rol`
  MODIFY `idRol` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT `fk_usuario_Rol10` FOREIGN KEY (`Rol_idRol`) REFERENCES `rol` (`idRol`);

--
-- Constraints for table `detalle_prestamo`
--
ALTER TABLE `detalle_prestamo`
  ADD CONSTRAINT `fk_Detalle_Prestamo_Libro` FOREIGN KEY (`Libro_idLibro`) REFERENCES `libro` (`idLibro`),
  ADD CONSTRAINT `fk_Detalle_Prestamo_Prestamo` FOREIGN KEY (`Prestamo_id_Prestamo`,`Prestamo_Lector_idLector`,`Prestamo_Administrador_Id_Administrador`) REFERENCES `prestamo` (`id_Prestamo`, `Lector_idLector`, `Administrador_Id_Administrador`);

--
-- Constraints for table `libro`
--
ALTER TABLE `libro`
  ADD CONSTRAINT `fk_Libro_Autor1` FOREIGN KEY (`Autor_idAutor`) REFERENCES `autor` (`idAutor`);

--
-- Constraints for table `prestamo`
--
ALTER TABLE `prestamo`
  ADD CONSTRAINT `fk_Prestamo_Administrador1` FOREIGN KEY (`Administrador_Id_Administrador`) REFERENCES `administrador` (`Id_Administrador`),
  ADD CONSTRAINT `fk_Prestamo_Lector1` FOREIGN KEY (`Lector_idLector`) REFERENCES `lector` (`idLector`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
