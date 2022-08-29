<?php
require('servicios.php');
$opcion = $_GET['opcion'];
# procesar segun la opcion
switch ($opcion){
  case '0':
    # -- recupera la relacion de alumnos nuevos matriculados en el 2022 con su
    # -- encabezado (que ira en la primera fila del archivo csv)
    [$datos, $encabezado] = nuevosMatriculados2022();
    # -- exportamos los datos en un archivo llamado 'nuevos2022.csv'
    exportarCsv($datos, $encabezado, 'nuevos2022.csv');
    break;
  case '1':
    # -- recupera la relacion de alumnos que no continuan sus estudios en el 2022
    # -- con su encabezado (que ira en la primera fila del archivo csv)
    [$datos, $encabezado] = noMatriculados2022();
    exportarCsv($datos, $encabezado, 'noAptos2022.csv');
    break;
  case '2':
    # -- recupera la relacion de la distribucion de tutorias 2022 con su
    # -- encabezado (que ira en la primera fila del archivo csv)
    [$datos, $encabezado] = distribucionTutorados2022();
    exportarCsv($datos, $encabezado, 'distribucion2022.csv');
    break;
}
?>