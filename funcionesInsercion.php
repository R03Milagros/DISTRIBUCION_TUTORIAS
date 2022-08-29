<?php
require('conexion.php');
# Contador del numero de tutorias
$numeroTutorias = 0;

# Contador del numero de docentes, servirá como clave primaria dentro de
# la base de datos. Esto debido a que el los datos de entrada ningun docente
# se muestra con su respectivo código de docente
$numeroDocentes = 0;

# Definir los atributos de cada tabla en la base de datos para las inserciones
$at_alumno = ['codAlumno', 'nombreApellido'];
$at_docente = ['codDocente', 'nombreApellido'];
$at_tutoria = ['idTutoria', 'codAlumno', 'codigoSemestre', 'codDocente'];
$at_docenteContratado = ['codDocente', 'codigoSemestre'];
$at_alumnoMatriculado = ['codAlumno', 'codigoSemestre', 'tipo'];
$at_semestre = ['codigoSemestre'];

# Aplica una comilla doble a un dato
$ponerComillaDoble = function($dato){
  return '"' . $dato . '"';
};

# Recupera una tabla sql que resulta de buscar un valor en una
# determinada tabla bajo un atributo especifo
function recuperarRegistro($tabla, $atributo, $valorBuscar){
  global $con;
  $consulta = "SELECT * FROM $tabla WHERE $atributo='$valorBuscar';";
  return mysqli_query($con, $consulta);
}

# Recupera una tabla sql, que resulta de buscar el nombre de un
# docente
function buscarNombre($nombre){
  global $con;
  $consulta = "SELECT * FROM docente WHERE nombreApellido LIKE '$nombre%';";
  return mysqli_query($con, $consulta);
}

# Inserta registros en la base de datos, funciona para cualquier tabla
function insertar($atributosTabla, $valoresInsertar, $tablaInsertar){
  global $con, $ponerComillaDoble;
  $valoresInsertar = array_map($ponerComillaDoble, $valoresInsertar);
  $consulta = 'INSERT INTO ' . $tablaInsertar . '(' . implode(', ', $atributosTabla)
      . ') VALUES (' . implode(', ', $valoresInsertar) . ');';
  mysqli_query($con, $consulta);
}

# $lista es un arreglo de arreglos con la siguiente estructura
/* [
    0: [dato1, dato2]
    1: [dato1, dato2]
    2: [dato1, dato2]
    .....
   ] */
function insertarDocentes2022($lista){
  global $at_docente, $at_docenteContratado, $numeroDocentes;
  for ($i = 0; $i < count($lista); $i++){
    [$nombre, $_] = $lista[$i];
    if (!empty($nombre)){
      # -- busqueda por nombre
      $registro = buscarNombre($nombre);
      if (mysqli_num_rows($registro) <= 0){
        # -- el docente no existe en la base de datos
        # --- insertar en la tabla docente
        insertar($at_docente, [++$numeroDocentes, $nombre], 'docente');
        # --- insertar en la tabla docente contratado
        insertar($at_docenteContratado, [$numeroDocentes, '2022-1'], 'docenteContratado');
      } else {
        # -- el docente ya existe en la base de datos
        # --- recuperar el codigo de docente
        $registro = mysqli_fetch_array($registro);
        $codDocente = $registro[0];
        # --- insertar en la tabla docente contratado
        insertar($at_docenteContratado, [$codDocente, '2022-1'], 'docenteContratado');
      }
    }
  }
}

# se encargade insertar los datos de un alumno en las tablas
# que está involucrado
function insertarAlumno($valores, $semestre, $tutor){
  global $at_alumno, $at_alumnoMatriculado, $at_tutoria, $numeroTutorias;
  # -- recuperamos el codigo y el nombre
  [$codAlumno, $nombre] = $valores;
  # -- verificar que los datos sean consistentes
  if (!str_contains($codAlumno, 'codigo') && !str_contains($codAlumno, 'CODIGO') &&
    !empty($codAlumno) && !empty($nombre)){
    # -- recuperamos el registro de la base de datos
    $registro = recuperarRegistro('alumno', 'codAlumno', $codAlumno);
    if (mysqli_num_rows($registro) <= 0){ # si no existe alumno, alumno nuevo
      if ($semestre == '2021-2' && str_starts_with($codAlumno, '22')){
        # --- insertar en la tabla alumno
        insertar($at_alumno, $valores, 'alumno');
        # --- insertar en matriculado 2022
        insertar($at_alumnoMatriculado, [$codAlumno, '2022-1', 'Nuevo'], 'alumnoMatriculado');
      } else if ($semestre == '2021-2' && !str_starts_with($codAlumno, '22')){
        # --- insertar en la tabla alumno
        insertar($at_alumno, $valores, 'alumno');
        # --- insertar en la tabla alumnomatriculado
        insertar($at_alumnoMatriculado, [$codAlumno, $semestre, 'Nuevo'], 'alumnoMatriculado');
        # --- insertar en la tabla de tutoria
        insertar($at_tutoria, [++$numeroTutorias, $codAlumno, '2021-2', $tutor], 'tutoria');
      } else { # semestre=2022-1
        # --- insertar en la tabla alumno
        insertar($at_alumno, $valores, 'alumno');
        # --- insertar alumno matriculado en su correspondiente semestre
        insertar($at_alumnoMatriculado, [$codAlumno, $semestre, 'Nuevo'], 'alumnoMatriculado');
      }
    } else { # alumno regular respecto a 2021-2
      if ($semestre == '2022-1' && !str_starts_with($codAlumno, '22')){
        # --- insertar en alumnomatriculado
        insertar($at_alumnoMatriculado, [$codAlumno, $semestre, 'Regular'], 'alumnoMatriculado');
      }
    }
  }
}

# $lista es un arreglo de arreglos con la siguiente estructura
/* [
    0: [codAlumno, nombre]
    1: [codAlumno, nombre]
    2: [codAlumno, nombre]
    .....
   ] */
function insertarAlumnos2022($lista){
  for ($i = 0; $i < count($lista); $i++)
    insertarAlumno($lista[$i], '2022-1', -1);
}

# $lista es un arreglo de arreglos con la siguiente estructura
/* [
    0: [codAlumno, nombreAlumno o docente]
    1: [codAlumno, nombreAlumno o docente]
    2: [codAlumno, nombreAlumno o docente]
    .....
   ] */
function insertarDatosDistribucion2021($lista){
  global $numeroDocentes, $at_docente, $at_docenteContratado;
  $i = 0;
  $longitud = count($lista);
  while ($i < $longitud){
    [$codigo, $nombre] = $lista[$i];
    if (str_starts_with($codigo, 'Docente')){
      # --- insertar en la tabla docente
      insertar($at_docente, [++$numeroDocentes, $nombre], 'docente');
      # --- insertar en la tabla docentecontratado
      insertar($at_docenteContratado, [$numeroDocentes, '2021-2'], 'docenteContratado');
      $j = $i + 1;
      while ($j < $longitud){
        [$codAlumno, $_] = $lista[$j];
        if (str_starts_with($codAlumno, "Docente"))
          break;
        else {
          # --- insertar registro a alumno y alumnomatriculado
          insertarAlumno($lista[$j], '2021-2', $numeroDocentes);
          $j++;
        }
      }
      $i = $j;
    } else $i++;
  }
}
?>