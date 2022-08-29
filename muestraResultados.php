<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Tutorias</title>
	<link rel="stylesheet" type="text/css" href="estilo1.css">
	<link rel="stylesheet" type="text/css" href="estilosTabla.css">
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
require('servicios.php');

$opcion = '5';
$contenido = '';

if (isset($_GET['opciones'])){
  $opcion = $_GET['opciones'];
  switch ($opcion){
    case "0":
      [$datos, $encabezado] = nuevosMatriculados2022();
      $contenido = generarCuerpoTableHtml($datos, $encabezado);
      break;
    case "1":
      [$datos, $encabezado] = noMatriculados2022();
      $contenido = generarCuerpoTableHtml($datos, $encabezado);
      break;
    case "2":
      [$datos, $encabezado] = distribucionTutorados2022();
      $contenido = generarCuerpoTableHtml($datos, $encabezado);
      break;
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Resultados</title>
    <link rel="stylesheet" type="text/css" href="estilos1.css">
  </head>
  <body>
  <div id="table-wrapper">
    <div id="table-scroll">
      <table>
          <?php echo $contenido; ?>
      </table>
    </div>
  </div>
    <a href="exportar.php?opcion=<?php echo $opcion; ?>"><button>Exportar Resultado</button></a>
    <a href="index.php"><button>Regresar Pagina</button></a>
  </body>
</html>