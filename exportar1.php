<?php
  require('conexion.php');

  global $con;

  $procedimiento = "CALL NoTutorados2022();";
  if (!mysqli_query($con, $procedimiento))
    echo "alert('Ocurrio un error');";
  $consulta = "SELECT * FROM tablaNoTutorados;";
  $registros = mysqli_query($con, $consulta);
  if (!$registros)
    echo "alert('Ocurrio errores');";

  //le informamos que será un archivo txt
  header('Content-type: application/csv');

  //también le damos un nombre
  header('Content-Disposition: attachment; filename="alumnosNoTutorados.csv');
  echo "Codigo,Nombres";
  while (list($codigo, $nombre) = mysqli_fetch_array($registros, PDO::FETCH_NUM)){
    echo $codigo ."," .$nombre."\n";
  }


?>