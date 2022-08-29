DROP DATABASE IF EXISTS bdtutoria;

CREATE DATABASE bdtutoria;

USE bdtutoria;

CREATE TABLE alumno(
  -- Atributos
  codAlumno varchar(6),
  nombreApellido varchar(100) not null,
  -- Definir claves
  primary key (codAlumno)
);

CREATE TABLE semestre(
  -- Atributos
  codigoSemestre varchar(6),
  -- Definir clave
  primary key (codigoSemestre)
);

CREATE TABLE alumnoMatriculado(
  -- Atributos
  codAlumno varchar(6),
  codigoSemestre varchar(6),
  tipo varchar(8) check (tipo in ('Regular', 'Nuevo')),
  -- Definir variables
  primary key (codAlumno, codigoSemestre),
  foreign key (codAlumno) references alumno(codAlumno),
  foreign key (codigoSemestre) references semestre(codigoSemestre)
);

CREATE TABLE docente(
  -- Atributos
  codDocente smallint,
  nombreApellido varchar(100) not null,
  -- Definir claves
  primary key (codDocente)
);

CREATE TABLE docenteContratado(
  -- Atributos
  codDocente smallint,
  codigoSemestre varchar(6),
  -- Definir claves
  primary key (codDocente, codigoSemestre),
  foreign key (codDocente) references docente(codDocente),
  foreign key (codigoSemestre) references semestre(codigoSemestre)
);

CREATE TABLE tutoria(
  -- Atributos
  idTutoria smallint,
  codAlumno varchar(6),
  codigoSemestre varchar(6),
  codDocente smallint,
  -- Definir claves
  primary key (idTutoria),
  foreign key (codAlumno) references alumno(codAlumno),
  foreign key (codDocente) references docente(codDocente),
  foreign key (codigoSemestre) references semestre(codigoSemestre)
);

DELIMITER //
CREATE PROCEDURE NuevosMatriculados(semestre varchar(6))
BEGIN
DROP TEMPORARY TABLE IF EXISTS tablaNuevosMatriculados;
-- crear la tabla que contendra el resultado
CREATE TEMPORARY TABLE tablaNuevosMatriculados
AS
SELECT a.codAlumno, a.nombreApellido FROM
(SELECT * FROM alumno) as a
INNER JOIN
-- seleccionar todos los alumnos matriculado en determinado semestre
(SELECT * FROM alumnomatriculado WHERE codigoSemestre = semestre and tipo = 'Nuevo') nuevo
ON a.codAlumno = nuevo.codAlumno;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE NoTutorados2022()
BEGIN
DROP TEMPORARY TABLE IF EXISTS tablaNoTutorados;
-- crear la tabla que contendra el resultado
CREATE TEMPORARY TABLE tablaNoTutorados
AS
SELECT a.codAlumno, a.nombreApellido FROM
(SELECT * FROM alumno) as a
INNER JOIN
-- seleccionar los alumnos que estan matriculados en el 2021-2
-- pero no estan matriculados en el semestre 2022-1
(SELECT ma2021.codAlumno FROM
  -- seleccionar los alumnos matriculados en el semestre 2021-2
  (SELECT * FROM alumnomatriculado WHERE codigoSemestre='2021-2') as ma2021
	LEFT JOIN
  -- seleccionar los alumnos matriculados en el semestre 2022-1
	(SELECT * FROM alumnomatriculado WHERE codigoSemestre='2022-1') as ma2022
	ON ma2021.codAlumno = ma2022.codAlumno
	WHERE ma2022.codAlumno is null ) as noAptos
ON a.codAlumno = noAptos.codAlumno;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE DistribucionParcial2022()
BEGIN
-- generar una tabla con los alumnos no matriculados
-- en el 2022-1
CALL notutorados2022();
DROP TEMPORARY TABLE IF EXISTS tablaDistribucionParcial2022;
-- crear la tabla que contendra el resultado
CREATE TEMPORARY TABLE tablaDistribucionParcial2022
AS
-- recuperar la distribucion del 2021-2 quitando los alumnos
-- no matriculados en el 2022
SELECT c.codAlumno, c.nombreApellido as nombreAlumno, d.codDocente, d.nombreApellido as nombreDocente
FROM
(SELECT * FROM docente) as D
INNER JOIN
(SELECT a.codAlumno, a.nombreApellido, b.codDocente
FROM
(SELECT * FROM alumno) as A
INNER JOIN
-- seleccionar los alumnos matriculados del 2021-2 que no
-- estan matriculados en el 2022-1
(SELECT codAlumno, codDocente
FROM tutoria
WHERE codigoSemestre = '2021-2' and codAlumno not in (SELECT codAlumno from tablanotutorados)) as B
ON a.codAlumno = b.codAlumno) as C
ON d.codDocente = c.codDocente;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE ConteoTutoradosxDocente()
BEGIN
CALL DistribucionParcial2022();
DROP TABLE IF EXISTS tutoradoxdocente2022;
-- crear la tabla que contendra el resultado
CREATE TEMPORARY TABLE tutoradoxdocente2022
AS
SELECT d.codDocente, a.NumeroTutorados2022, d.nombreApellido
FROM
-- por cada docente contar el numero de tutorados asignados
-- en el semestre 2021-2
(SELECT * FROM docente) as D
INNER JOIN
(SELECT codDocente, count(codAlumno) as NumeroTutorados2022
FROM tablaDistribucionParcial2022
GROUP BY codDocente) as A
ON d.codDocente = a.codDocente;
END //
DELIMITER ;