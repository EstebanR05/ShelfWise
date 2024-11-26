CREATE SCHEMA `shelfwise` ;
USE `shelfwise` ;

CREATE TABLE `shelfwise`.`Autor` (
  `idAutor` INT NOT NULL AUTO_INCREMENT,
  `Primer_Nombre` VARCHAR(45) NOT NULL,
  `Segundo_Nombre` VARCHAR(45) NULL,
  `Primer_Apellido` VARCHAR(45) NOT NULL,
  `Segundo_Apellido` VARCHAR(45) NULL,
  PRIMARY KEY (`idAutor`)
);

CREATE TABLE `Libro` (
  `idLibro` INT NOT NULL AUTO_INCREMENT,
  `Editorial` VARCHAR(45) NULL,
  `Titulo` VARCHAR(45) NOT NULL,
  `Autor_idAutor` INT NOT NULL,
  `Genero` VARCHAR(45) NOT NULL,
  `Cantidad_Libro` VARCHAR(45) NULL,
  PRIMARY KEY (`idLibro`),
  CONSTRAINT `fk_Libro_Autor1` FOREIGN KEY (`Autor_idAutor`) REFERENCES `Autor` (`idAutor`)
);

CREATE TABLE `shelfwise`.`Lector` (
  `idLector` INT NOT NULL AUTO_INCREMENT,
  `Primer_Nombre` VARCHAR(45) NOT NULL,
  `Segundo_Nombre` VARCHAR(45) NULL,
  `Primer_Apellido` VARCHAR(45) NOT NULL,
  `Segundo_Apellido` VARCHAR(45) NULL,
  `Telefono` VARCHAR(45) NULL,
  `Correo` VARCHAR(45) NULL,
  `Identificacion` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`idLector`)
);

CREATE TABLE `shelfwise`.`Rol` (
  `idRol` INT NOT NULL AUTO_INCREMENT,
  `Nombre` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`idRol`)
);

CREATE TABLE `shelfwise`.`Administrador` (
  `Id_Administrador` INT NOT NULL AUTO_INCREMENT,
  `Primer_Nombre` VARCHAR(45) NOT NULL,
  `Segundo_Nombre` VARCHAR(45) NULL,
  `Primer_Apellido` VARCHAR(45) NOT NULL,
  `Segundo_Apellido` VARCHAR(45) NULL,
  `Telefono` VARCHAR(45) NULL,
  `Edad` VARCHAR(45) NULL,
  `Email` VARCHAR(45) NOT NULL,
  `Contraseña` VARCHAR(45) NOT NULL,
  `Rol_idRol` INT NOT NULL,
  PRIMARY KEY (`Id_Administrador`),
  CONSTRAINT `fk_usuario_Rol10` FOREIGN KEY (`Rol_idRol`) REFERENCES `shelfwise`.`Rol` (`idRol`)
);
  
CREATE TABLE `shelfwise`.`Prestamo` (
  `id_Prestamo` INT NOT NULL AUTO_INCREMENT,
  `Fecha_Prestamo` DATE NULL,
  `Fecha_Devolucion` DATE NULL,
  `Lector_idLector` INT NOT NULL,
  `Administrador_Id_Administrador` INT NOT NULL,
  PRIMARY KEY (`id_Prestamo`, `Lector_idLector`, `Administrador_Id_Administrador`),
  CONSTRAINT `fk_Prestamo_Lector1` FOREIGN KEY (`Lector_idLector`) REFERENCES `shelfwise`.`Lector` (`idLector`),
  CONSTRAINT `fk_Prestamo_Administrador1` FOREIGN KEY (`Administrador_Id_Administrador`) REFERENCES `shelfwise`.`Administrador` (`Id_Administrador`)
);

CREATE TABLE `shelfwise`.`Detalle_Prestamo` (
  `Libro_idLibro` INT NOT NULL,
  `Prestamo_id_Prestamo` INT NOT NULL,
  `Prestamo_Lector_idLector` INT NOT NULL,
  `Prestamo_Administrador_Id_Administrador` INT NOT NULL,
  `Cantidad` VARCHAR(45) NULL,
  PRIMARY KEY (`Libro_idLibro`, `Prestamo_id_Prestamo`, `Prestamo_Lector_idLector`, `Prestamo_Administrador_Id_Administrador`),
  CONSTRAINT `fk_Detalle_Prestamo_Libro` FOREIGN KEY (`Libro_idLibro`) REFERENCES `shelfwise`.`Libro` (`idLibro`),
  CONSTRAINT `fk_Detalle_Prestamo_Prestamo` FOREIGN KEY (`Prestamo_id_Prestamo`, `Prestamo_Lector_idLector`, `Prestamo_Administrador_Id_Administrador`)
  REFERENCES `shelfwise`.`Prestamo` (`id_Prestamo`, `Lector_idLector`, `Administrador_Id_Administrador`)
);

