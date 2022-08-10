<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Tutorias</title>
	<link rel="stylesheet" type="text/css" href="estilo1.css">
	<script>
		function confirmarRegistro()
		{
			return confirm("\u00BFEst\u00E1 seguro que desea registrar datos?");
		}
	</script>
</head>
<body>
	<header>
		<h1><center>TUTORÍAS</center></h1>
	</header>
<?php
include('conexion.php');

$opcion = $_GET['opciones'];

if ($opcion == 'LISTA DE ALUMNOS QUE YA NO SON CONSIDERADOS EN LA TUTORÍA' )
  $idOp = 1;
else
  $idOp = 2;

$encerrarTag = function($dato, $tag='tr'){
  return "<" . $tag . ">" . $dato . "</" . $tag . ">";
};

$formarFilaAlumno = function($datosAlumno){
  global $encerrarTag;
  return $encerrarTag($datosAlumno['nombreApellido'], 'td') .
    $encerrarTag($datosAlumno['codAlumno'], 'td');
};

$agregarNumeracion = function($datos, $numero){
  global $encerrarTag;
  return $encerrarTag($numero, 'td') . $datos;
};

function MostrarAlumnosNuevos(){
  global $con;
  global $formarFilaAlumno, $encerrarTag, $agregarNumeracion;
  # Procedimientos mysql
  $procedimiento = "CALL nuevosmatriculados('2022-1');";
  $consulta = "SELECT * FROM tablanuevosmatriculados";
  mysqli_query($con, $procedimiento);
  $alumnosNuevos = mysqli_query($con, $consulta);
  $alumnosNuevos = ConvertirMysql($alumnosNuevos);
  $numeracion = GenerarNumeracion(count($alumnosNuevos));
  # Ordenar por nombres
  $nombres = array_column($alumnosNuevos, 'nombreApellido');
  array_multisort($nombres, SORT_ASC, $alumnosNuevos);
  $contenido = array_map($formarFilaAlumno, $alumnosNuevos);
  $contenido = array_map($agregarNumeracion, $contenido, $numeracion);
  $contenido = array_map($encerrarTag, $contenido);
  $contenido = implode('', $contenido);
  return $encerrarTag($contenido, 'tbody');
}

$formarFilaDistribucion = function($datosDistribucion){
  global $encerrarTag;
  return $encerrarTag($datosDistribucion['nombreDocente'], 'td') .
    $encerrarTag($datosDistribucion['nombreAlumno'], 'td') .
    $encerrarTag($datosDistribucion['codAlumno'], 'td');
};

function MostrarDistribucion2022(){
  #
  global $encerrarTag, $formarFilaDistribucion, $agregarNumeracion;
  #
  global $con;
  # Procedimientos mysql
  $procedimiento = "CALL conteotutoradosxdocente();";
  $consulta = "SELECT * FROM tutoradoxdocente2022;";
  mysqli_query($con, $procedimiento);
  $conteoTutorados = mysqli_query($con, $consulta);
  $procedimiento = "CALL nuevosmatriculados('2022-1');";
  $consulta = "SELECT * FROM tablanuevosmatriculados;";
  mysqli_query($con, $procedimiento);
  $alumnosNuevos = mysqli_query($con, $consulta);

  $conteoTutorados = ConvertirMysql($conteoTutorados);
  $alumnosNuevos = ConvertirMysql($alumnosNuevos);

  $asignaciones = Balancear($conteoTutorados, $alumnosNuevos);

  # Recuperar distribucion del 2021 quitando los alumnos no tutorados
  $procedimiento = "CALL distribucionparcial2022();";
  $consulta = "SELECT * FROM tabladistribucionparcial2022;";
  mysqli_query($con, $procedimiento);
  $distribucionParcial = mysqli_query($con, $consulta);

  $distribucionParcial = ConvertirMysql($distribucionParcial);

  # Juntar ambos resultados

  $distribucion2022 = array_merge($distribucionParcial, $asignaciones);
  $numeracion = GenerarNumeracion(count($distribucion2022));
  # Ordenar y mostrar
  $nombresDocentes = array_column($distribucion2022, 'nombreDocente');
  array_multisort($nombresDocentes, SORT_STRING, $distribucion2022);
  $contenido = array_map($formarFilaDistribucion, $distribucion2022);
  $contenido = array_map($agregarNumeracion, $contenido, $numeracion);
  $contenido = array_map($encerrarTag, $contenido);
  $contenido = implode('', $contenido);
  return $encerrarTag($contenido, 'tbody');
}

function ConvertirMysql($resultado){
  while ($fila = mysqli_fetch_assoc($resultado))
    $arregloTabla[] = $fila;
  return $arregloTabla;
}

function Balancear($conteo, $matriculadosNuevos){
  # Los dos parametros ya son arreglos asociativos
  #$copiaDistribucion = array_slice($conteo, 0);
  # obtener el maximo valor para el numero de tutorados
  $maximo = -1;
  foreach ($conteo as $registro){
    if ($registro['NumeroTutorados2022'] > $maximo)
      $maximo = $registro['NumeroTutorados2022'];
  }
  # balancear/distribuir los alumnos nuevos
  $k = 0;
  $cantidadNuevos = count($matriculadosNuevos);
  for ($i = 0; $i < count($conteo); $i++){
    if ($conteo[$i]['NumeroTutorados2022'] < $maximo){
      $diferencia = $maximo - $conteo[$i]['NumeroTutorados2022'];
      while ($diferencia > 0 && $k < $cantidadNuevos){
        $asignacionAlumnos[] = array('codDocente' => $conteo[$i]['codDocente'],
        'codAlumno' => $matriculadosNuevos[$k]['codAlumno'],
        'nombreAlumno' => $matriculadosNuevos[$k]['nombreApellido'],
        'nombreDocente' => $conteo[$i]['nombreApellido']);
        $diferencia--;
        $k++;
      }
    }
  }

  $j = 0;
  for ($i = $k; $i < $cantidadNuevos; $i++){
    $asignacionAlumnos[] = array('codDocente' => $asignacionAlumnos[$j]['codDocente'],
    'codAlumno' => $matriculadosNuevos[$i]['codAlumno'],
    'nombreAlumno' => $matriculadosNuevos[$i]['nombreApellido'],
    'nombreDocente' => $asignacionAlumnos[$j]['nombreApellido']);
    $j++;
  }

  return $asignacionAlumnos;
}

function GenerarNumeracion($cantidad){
  for ($i = 1; $i <= $cantidad; $i++)
    $numeracion[] = $i;
  return $numeracion;
}

# main
if ($idOp == 1)
  $resultado = MostrarAlumnosNuevos();
else
  $resultado = MostrarDistribucion2022();
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Resultados</title>
    <link rel="stylesheet" href="estilos-tabla1.css">
  </head>
  <body>
    <table>
      <!--Ponerle su thead -->
      <!--Ponerle su tbody -->
      <?php echo $resultado; ?>
    </table>
    <div>
      <form action="exportar1.php" method="post">
        <button type="submit" id="export_data" name="export_data" value="Export to excel" class="btn btn-info">Exportar a Excel</button>
      </form>
    </div>
  </body>
</html>