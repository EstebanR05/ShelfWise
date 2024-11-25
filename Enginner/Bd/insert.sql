INSERT INTO `shelfwise`.`Autor` (`idAutor`, `Primer_Nombre`, `Segundo_Nombre`, `Primer_Apellido`, `Segundo_Apellido`) VALUES
(1, 'Gabriel', 'José', 'García', 'Márquez'),
(2, 'Julio', NULL, 'Cortázar', NULL),
(3, 'Isabel', NULL, 'Allende', NULL),
(4, 'Mario', NULL, 'Vargas', 'Llosa'),
(5, 'Jorge', 'Francisco', 'Luis', 'Borges'),
(6, 'Carlos', 'Ruiz', 'Zafón', NULL),
(7, 'Paulo', NULL, 'Coelho', NULL),
(8, 'Laura', 'Esquivel', 'Desconocido', NULL),
(9, 'Octavio', NULL, 'Paz', NULL),
(10, 'Pablo', NULL, 'Neruda', NULL);

INSERT INTO `shelfwise`.`Rol` (`idRol`, `Nombre`) VALUES
(1, 'Usuario'),
(2, 'Administrador');

INSERT INTO `shelfwise`.`Administrador` (`Id_Administrador`, `Primer_Nombre`, `Segundo_Nombre`, `Primer_Apellido`, `Segundo_Apellido`, `Telefono`, `Edad`, `Email`, `Contraseña`, `Rol_idRol`) VALUES
(1, 'Admin', 'Admin', 'Admin', 'Admin', '123456789', '35', 'admin@gmail.com', 'password123', 1),
(2, 'Juan', 'Carlos', 'Ramírez', 'Lopez', '123456789', '35', 'juan@gmail.com', 'password123', 1),
(3, 'Ana', 'Lucía', 'Martinez', 'Hernandez', '987654321', '29', 'ana.martinez@gmail.com', 'ana2023', 1),
(4, 'Carlos', NULL, 'Perez', 'Mendoza', '456123789', '42', 'carlos.perez@gmail.com', 'securepass', 1),
(5, 'Maria', 'Del', 'Carmen', 'Fernandez', '789321654', '31', 'maria.fernandez@gmail.com', 'marfer2023', 1),
(6, 'José', NULL, 'Gutierrez', 'Ramos', '321654987', '28', 'jose.gutierrez@gmail.com', 'joegutierrez', 1),
(7, 'Luis', 'Alberto', 'Ortiz', 'Rivera', '111222333', '39', 'luis.ortiz@gmail.com', 'luispassword', 1),
(8, 'Miguel', 'Angel', 'Ruiz', NULL, '444555666', '36', 'miguel.ruiz@gmail.com', 'miguelforlibros', 1),
(9, 'Laura', 'María', 'Gomez', 'Soto', '777888999', '32', 'laura.gomez@gmail.com', 'laur', 1),
(10, 'Sofia', NULL, 'Hernandez', 'Diaz', '112233445', '40', 'sofia.hernandez@gmail.com', 'sofia2024', 1),
(11, 'Diego', 'Armando', 'Castro', NULL, '667788990', '33', 'diego.castro@gmail.com', 'diego', 1);

INSERT INTO `shelfwise`.`Lector` (`idLector`, `Primer_Nombre`, `Segundo_Nombre`, `Primer_Apellido`, `Segundo_Apellido`, `Telefono`, `Correo`, `Identificacion`) VALUES
(1, 'Andrea', NULL, 'Perez', 'Lopez', '5566778899', 'andrea.perez@gmail.com', '12345678'),
(2, 'Carlos', 'Manuel', 'Garcia', 'Hernandez', '9988776655', 'carlos.garcia@gmail.com', '87654321'),
(3, 'Maria', NULL, 'Fernandez', 'Torres', '5566443322', 'maria.fernandez@gmail.com', '11223344'),
(4, 'Luis', 'Antonio', 'Ruiz', 'Martinez', '6655443322', 'luis.ruiz@gmail.com', '22113344'),
(5, 'Ana', 'Sofia', 'Gonzalez', 'Diaz', '5544332211', 'ana.gonzalez@gmail.com', '33441122'),
(6, 'Jose', NULL, 'Lopez', 'Castro', '4433221100', 'jose.lopez@gmail.com', '44552211'),
(7, 'Laura', 'Isabel', 'Ramirez', 'Soto', '3322110099', 'laura.ramirez@gmail.com', '55663344'),
(8, 'Diego', 'Alberto', 'Sanchez', 'Moreno', '2211009988', 'diego.sanchez@gmail.com', '66774455'),
(9, 'Sofia', NULL, 'Gutierrez', 'Ramirez', '1100998877', 'sofia.gutierrez@gmail.com', '77885566'),
(10, 'Pablo', 'Andres', 'Vega', 'Salazar', '9988776655', 'pablo.vega@gmail.com', '88996655');

INSERT INTO `shelfwise`.`Libro` (`idLibro`, `Editorial`, `Titulo`, `Autor_idAutor`, `Genero`, `Cantidad_Libro`) VALUES
(1, 'Editorial A', 'Cien Años de Soledad', 1, 'Novela', '10'),
(2, 'Editorial B', 'Rayuela', 2, 'Ficción', '8'),
(3, 'Editorial C', 'La Casa de los Espíritus', 3, 'Drama', '12'),
(4, 'Editorial D', 'La Ciudad y los Perros', 4, 'Novela', '5'),
(5, 'Editorial E', 'Ficciones', 5, 'Cuento', '6'),
(6, 'Editorial F', 'La Sombra del Viento', 6, 'Misterio', '7'),
(7, 'Editorial G', 'El Alquimista', 7, 'Aventura', '9'),
(8, 'Editorial H', 'Como Agua para Chocolate', 8, 'Romance', '4'),
(9, 'Editorial I', 'El Laberinto de la Soledad', 9, 'Ensayo', '3'),
(10, 'Editorial J', 'Veinte Poemas de Amor', 10, 'Poesía', '11');

INSERT INTO `shelfwise`.`Prestamo` (`id_Prestamo`, `Fecha_Prestamo`, `Fecha_Devolucion`, `Lector_idLector`, `Administrador_Id_Administrador`) VALUES
(1, '2024-11-01', '2024-11-15', 1, 1),
(2, '2024-11-02', '2024-11-16', 2, 1),
(3, '2024-11-03', '2024-11-17', 3, 2),
(4, '2024-11-04', '2024-11-18', 4, 2),
(5, '2024-11-05', '2024-11-19', 5, 3),
(6, '2024-11-06', '2024-11-20', 6, 3),
(7, '2024-11-07', '2024-11-21', 7, 4),
(8, '2024-11-08', '2024-11-22', 8, 4),
(9, '2024-11-09', '2024-11-23', 9, 5),
(10, '2024-11-10', '2024-11-24', 10, 5);

INSERT INTO `shelfwise`.`Detalle_Prestamo` (`Libro_idLibro`, `Prestamo_id_Prestamo`, `Prestamo_Lector_idLector`, `Prestamo_Administrador_Id_Administrador`, `Cantidad`) VALUES
(1, 1, 1, 1, '1'),
(2, 1, 1, 1, '2'),
(3, 2, 2, 1, '1'),
(4, 2, 2, 1, '1'),
(5, 3, 3, 2, '1'),
(6, 3, 3, 2, '1'),
(7, 4, 4, 2, '2'),
(8, 4, 4, 2, '1'),
(9, 5, 5, 3, '1'),
(10, 5, 5, 3, '1'),
(1, 6, 6, 3, '1'),
(2, 6, 6, 3, '2'),
(3, 7, 7, 4, '1'),
(4, 7, 7, 4, '1'),
(5, 8, 8, 4, '1'),
(6, 8, 8, 4, '2'),
(7, 9, 9, 5, '1'),
(8, 9, 9, 5, '1'),
(9, 10, 10, 5, '1'),
(10, 10, 10, 5, '1');
