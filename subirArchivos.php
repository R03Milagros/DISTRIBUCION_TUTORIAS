<?php
require('funcionesInsercionDatos.php');

# Codigo principal
# -- Insertar los semestres
Insertar($at_semestre, ['2021-2'], 'semestre', $con);
Insertar($at_semestre, ['2022-1'], 'semestre', $con);

# -- Procesar CSV: distribucion x docente 2021-2
$archivoTutorias2021 = $_FILES['Tutorias']['tmp_name'];
InsertarDistribucionDocentes2021($archivoTutorias2021);

# -- Procesar los alumnos matriculados en 2022
$archivoAlumnos2022 = $_FILES['Alumnos']['tmp_name'];
InsertarAlumnos2022($archivoAlumnos2022);

# -- Procesar CSV de los docentes contratados en 2022
$archivoDocentes2022 = $_FILES['Docentes']['tmp_name'];
InsertarDocentes2022($archivoDocentes2022);

header('Location: index.php');
?>