<?php

require('conexion.php');
require('funcionesSalida.php');

# recupera un conjunto de registros en base a una opcion
function recuperarDataSet($opcion){
  global $con;
  # -- definir los procedimientos almacenados en la base de datos
  $procedimientos = ["CALL nuevosmatriculados('2022-1');",
  "CALL notutorados2022();", "CALL distribucionparcial2022();",
  "CALL ConteoTutoradosxDocente();"];
  # -- definir la tabla que contiene los resultados
  $consultas = ["SELECT * FROM tablaNuevosMatriculados;",
  "SELECT * FROM tablanotutorados;", "SELECT * FROM tabladistribucionparcial2022;",
  "SELECT * FROM tutoradoxdocente2022;"];
  # -- ejecutar el procedimientos almacenado
  mysqli_query($con, $procedimientos[$opcion]);
  # -- recuperar el dataset (los registros)
  $registros = mysqli_query($con, $consultas[$opcion]);
  # -- convertir el objeto mysql retornado en un arreglo asociativo
  while ($fila = mysqli_fetch_assoc($registros))
    $dataset[] = $fila;
  return $dataset;
}

function nuevosMatriculados2022(){
  # -- la opcion 0 de recuperarDataSet retorna los alumnos nuevos
  # -- matricualdos en el 2022
  $dataset = recuperarDataSet(0);
  $encabezado = ['Codigo', 'Nombre y Apellido'];
  return [$dataset, $encabezado];
}

function noMatriculados2022(){
  # -- la opcion 1 de recuperarDataSet retorna los alumnos que no
  # -- se matricularon en el 2022
  $dataset = recuperarDataSet(1);
  $encabezado = ['Codigo', 'Nombre y Apellido'];
  return [$dataset, $encabezado];
}

function distribucionTutorados2022(){
  # -- la opcion 2 de recuperarDataSet retorna la
  # -- distribucion parcial (distribucion del 2021-2) sin los alumnos matriculados en el 2022
  $distribucionParcial = recuperarDataSet(2);
  $alumnosNuevos = recuperarDataSet(0);
  # -- la opcion 3 de recuperarDataSet retorna el numero de tutorados
  # -- por cada docente
  $conteoTutorados = recuperarDataSet(3);

  # -- balancear tutorados
  # --- obtener el numero maximo de tutorados por docente
  $maximo = -1;
  for ($i = 0; $i < count($conteoTutorados); $i++){
    if ($conteoTutorados[$i]['NumeroTutorados2022'] > $maximo)
      $maximo = $conteoTutorados[$i]['NumeroTutorados2022'];
  }

  # --- distribuir alumnos nuevos a cada docente
  $j = -1;
  $cantidadDocentes = count($conteoTutorados);
  for ($i = 0; $i < count($alumnosNuevos); $i++){
    if ($j == $cantidadDocentes - 1) $j = -1;
    if ($conteoTutorados[++$j]['NumeroTutorados2022'] < $maximo)
      $distribucionParcial[] = array('codAlumno' => $alumnosNuevos[$i]['codAlumno'],
        'nombreAlumno' => $alumnosNuevos[$i]['nombreApellido'],
        'codDocente' => $conteoTutorados[$j]['codDocente'],
        'nombreDocente' => $conteoTutorados[$j]['nombreApellido']);
  }
  # -- ordenar la nueva distribucion por nombre de docente
  $nombresDocente = array_column($distribucionParcial, 'nombreDocente');
  array_multisort($nombresDocente, SORT_ASC, $distribucionParcial);
  $encabezado = ['Codigo Alumno', 'Nombre Alumno', 'Codigo Docente', 'Nombre Docente'];
  return [$distribucionParcial, $encabezado];
}
?>