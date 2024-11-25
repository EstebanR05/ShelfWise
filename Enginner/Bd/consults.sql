use shelfwise;

-- cuantos lectores tienen un libro en especial
SELECT COUNT(DISTINCT dp.Prestamo_Lector_idLector) AS cantidad_lectores, l.Titulo
FROM Detalle_Prestamo dp
JOIN Libro l ON dp.Libro_idLibro = l.idLibro
WHERE l.idLibro = (
    SELECT dp.Libro_idLibro
    FROM Detalle_Prestamo dp
    GROUP BY dp.Libro_idLibro
    ORDER BY COUNT(*) DESC
    LIMIT 1
);

-- cuantos lectores leyeron un libro en un rango de fechas
SELECT COUNT(DISTINCT dp.Prestamo_Lector_idLector) AS cantidad_lectores, l.Titulo
FROM Detalle_Prestamo dp
JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
JOIN Libro l ON dp.Libro_idLibro = l.idLibro
WHERE l.idLibro = (
    SELECT dp.Libro_idLibro
    FROM Detalle_Prestamo dp
    JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
    WHERE p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20'
    GROUP BY dp.Libro_idLibro
    ORDER BY COUNT(*) DESC
    LIMIT 1
)
AND p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20';


-- cuantas veces se ha prestado un libro en un rango de fechas
SELECT COUNT(*) AS cantidad_prestamos, l.Titulo
FROM Detalle_Prestamo dp
JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
JOIN Libro l ON dp.Libro_idLibro = l.idLibro
WHERE l.idLibro = (
    SELECT dp.Libro_idLibro
    FROM Detalle_Prestamo dp
    JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
    WHERE p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20'
    GROUP BY dp.Libro_idLibro
    ORDER BY COUNT(*) DESC
    LIMIT 1
)
AND p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20';


-- lista de libros y numero de lectores, sin que sea 0
SELECT l.Titulo, COUNT(DISTINCT dp.Prestamo_Lector_idLector) AS numero_lectores
FROM Libro l
JOIN Detalle_Prestamo dp ON l.idLibro = dp.Libro_idLibro
GROUP BY l.idLibro, l.Titulo
HAVING numero_lectores > 0;

-- lista de lectores y sus libros mas leidos en un rango de fechas
WITH LectoresLibros AS (
    SELECT 
        le.idLector,
        le.Primer_Nombre,
        le.Primer_Apellido,
        l.idLibro,
        l.Titulo,
        COUNT(*) AS veces_leido
    FROM Lector le
    JOIN Prestamo p ON le.idLector = p.Lector_idLector
    JOIN Detalle_Prestamo dp ON p.id_Prestamo = dp.Prestamo_id_Prestamo
    JOIN Libro l ON dp.Libro_idLibro = l.idLibro
    WHERE p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20'
    GROUP BY le.idLector, le.Primer_Nombre, le.Primer_Apellido, l.idLibro, l.Titulo
),
MaxLecturas AS (
    SELECT 
        idLector,
        MAX(veces_leido) AS max_leido
    FROM LectoresLibros
    GROUP BY idLector
)
SELECT 
    ll.Primer_Nombre,
    ll.Primer_Apellido,
    ll.Titulo,
    ll.veces_leido
FROM LectoresLibros ll
JOIN MaxLecturas ml ON ll.idLector = ml.idLector AND ll.veces_leido = ml.max_leido;


-- lista de libros que se han prestado en un rango de fechas
SELECT l.Titulo, COUNT(dp.Libro_idLibro) AS veces_prestado
FROM Libro l
JOIN Detalle_Prestamo dp ON l.idLibro = dp.Libro_idLibro
JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
WHERE p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-12-31'
GROUP BY l.idLibro, l.Titulo
HAVING veces_prestado > 0;

-- lista de libros que se deben devolver en un rango de 3 dias atras en base a su fecha_devolucion
SELECT DISTINCT l.Titulo, p.Fecha_Devolucion
FROM Detalle_Prestamo dp
JOIN Libro l ON dp.Libro_idLibro = l.idLibro
JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
WHERE p.Fecha_Devolucion BETWEEN DATE_SUB(CURDATE(), INTERVAL 3 DAY) AND CURDATE();


-- lista de lectores que no devolvieron los libros y su fecha de devolucion caduco
SELECT l.Primer_Nombre, l.Primer_Apellido, p.Fecha_Devolucion
FROM Prestamo p
JOIN Lector l ON p.Lector_idLector = l.idLector
WHERE p.Fecha_Devolucion < CURDATE()
  AND NOT EXISTS (
      SELECT 1
      FROM Detalle_Prestamo dp
      WHERE dp.Prestamo_id_Prestamo = p.id_Prestamo
  );
