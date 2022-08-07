USE bdtutoria;

DROP PROCEDURE IF EXISTS NoTutorados2022;
DROP PROCEDURE IF EXISTS DistribucionParcial2022;
DROP PROCEDURE IF EXISTS ConteoTutoradosxDocente;
DROP PROCEDURE IF EXISTS NuevosMatriculados;

DELIMITER //
CREATE PROCEDURE NuevosMatriculados(semestre varchar(6))
BEGIN
DROP TEMPORARY TABLE IF EXISTS tablaNuevosMatriculados;
CREATE TEMPORARY TABLE tablaNuevosMatriculados
AS
SELECT a.codAlumno, a.nombreApellido FROM
(SELECT * FROM alumno) as a
INNER JOIN
(SELECT * FROM alumnomatriculado WHERE codigoSemestre = semestre and tipo = 'Nuevo') nuevo
ON a.codAlumno = nuevo.codAlumno;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE NoTutorados2022()
BEGIN
DROP TEMPORARY TABLE IF EXISTS tablaNoTutorados;
CREATE TEMPORARY TABLE tablaNoTutorados
AS
SELECT a.codAlumno, a.nombreApellido FROM
(SELECT * FROM alumno) as a
INNER JOIN
(SELECT ma2021.codAlumno FROM
    (SELECT * FROM alumnomatriculado WHERE codigoSemestre='2021-2') as ma2021
	LEFT JOIN
	(SELECT * FROM alumnomatriculado WHERE codigoSemestre='2022-1') as ma2022
	ON ma2021.codAlumno = ma2022.codAlumno
	WHERE ma2022.codAlumno is null ) as noAptos
ON a.codAlumno = noAptos.codAlumno;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE DistribucionParcial2022()
BEGIN
CALL notutorados2022();
DROP TEMPORARY TABLE IF EXISTS tablaDistribucionParcial2022;
CREATE TEMPORARY TABLE tablaDistribucionParcial2022
AS
SELECT codAlumno, codDocente
FROM tutoria
WHERE codigoSemestre = '2021-2' and codAlumno not in (SELECT codAlumno from tablanotutorados);
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE ConteoTutoradosxDocente()
BEGIN
CALL DistribucionParcial2022();
DROP TABLE IF EXISTS tutoradoxdocente2022;
CREATE TEMPORARY TABLE tutoradoxdocente2022
AS
SELECT codDocente, count(codAlumno) as NumeroTutorados2022
FROM tablaDistribucionParcial2022
GROUP BY codDocente;
END //
DELIMITER ;