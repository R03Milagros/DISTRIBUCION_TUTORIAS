<?php
require('funcionesInsercion.php');

# funcion anonima 
$unirCampos = function($campo1, $campo2){
  return array($campo1, $campo2);
};

# Borra los espacios en blanco que no agregan significado a un dato
# Mas especificamente, borra los espacios en blanco sobrantes por izquierda
# y por derecha
$borrarEspaciosBlanco = function($dato){
  return trim($dato);
};

# Genera un arreglo con los datos contenidos en
# una fila (cadena con comas, csv)
$splitFilacsv = function($fila){
  global $borrarEspaciosBlanco;
  # -- genera un arreglo con valores separados por ',' a partir de $fila
  $filaTrim = explode(',', $fila);
  # -- borra los espacios en blanco de cada valor de la fila
  $filaTrim = array_map($borrarEspaciosBlanco, $filaTrim);
  return $filaTrim;
};

# dado un archivo, solo se recupera 2 de sus columnas importantes
# determinados por indice1 e indice2
function prepararDatos($archivo, $indice1, $indice2){
  global $splitFilacsv, $unirCampos;
  # -- abrimos el archivo tmp
  $arregloArchivo = file($archivo);
  # -- separamos cada fila del archivo en sus datos representativos
  $listaDatos = array_map($splitFilacsv, $arregloArchivo);
  # -- recuperamos solo 2 columnas completas
  $datoPrincipal = array_column($listaDatos, $indice1);
  $datoSecundario = array_column($listaDatos, $indice2);
  # -- formamos un nuevo arreglo con las 2 columnas anteriores
  $listaDatos = array_map($unirCampos, $datoPrincipal, $datoSecundario);
  return $listaDatos;
}

# main <-> programa principal

# insertamos los semestres en la base de datos
insertar($at_semestre, ['2022-1'], 'semestre');
insertar($at_semestre, ['2021-2'], 'semestre');

# Procesar csv: distribucion de tutorias 2021
# En el archivo "Distribucion x Docente 2021-2.csv" recuperamos las filas:
#   0: codigo del alumno
#   1: nombre del docente o alumno
$listaDatos = prepararDatos($_FILES['Tutorias']['tmp_name'], 0, 1);
insertarDatosDistribucion2021($listaDatos);

# Procesar csv: alumnos 2022
# En el archivo "Alumnos 2022-1.csv" recuperamos las filas:
#   1: codigo del alumno
#   2: nombre del alumno
$listaDatos = prepararDatos($_FILES['Alumnos']['tmp_name'], 1, 2);
# insetar los alumnos matriculados en el 2022-1 en la base de datos
insertarAlumnos2022($listaDatos);

# Procesar csv: docentes 2022
# En el archivo "Docentes 2022-I.csv" recuperamos las filas:
#   1: nombre del docente
#   1: categoria del docente
$listaDatos = prepararDatos($_FILES['Docentes']['tmp_name'], 1, 2);
# insertar relacion de docentes 2022-1 en la base de datos
insertarDocentes2022($listaDatos);
header('Location: index.php');
?>