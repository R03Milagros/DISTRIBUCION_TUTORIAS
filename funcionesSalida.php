<?php

# funcion anonima que convierte un arreglo asociativo
# a numerico
$asocANumerico = function($arregloAsoc){
  return array_values($arregloAsoc);
};

# funcion anonima que pone un dato dentro de una etiqueta
$encerrarTag = function($dato, $tag='td'){
  return "<" . $tag . ">" . $dato . "</" . $tag . ">";
};

# genera una etiqueta th con el encabezado de un tabla
$generarEncabezadoHtml = function($columna){
  global $encerrarTag;
  return $encerrarTag($columna, 'th');
};

# genera un fila etiquetada por tr correspondiente al
# tbody de una tabla
$generarFilaBodyHtml = function($fila){
  global $encerrarTag;
  $fila_td = array_map($encerrarTag, $fila);
  return $encerrarTag(implode('', $fila_td), 'tr');
};

# Funcion que exporta un arreglo asociativo de datos en un archiv csv
function exportarCsv($arregloAsocDatos, $elementosEncabezado, $nombreArchivo){
  global $asocANumerico;
  # -- abrir un archivo
  $f = fopen('php://memory', 'w');
  # -- el titulo de cada columna
  fputcsv($f, $elementosEncabezado, ',');
  $arregloNumDatos = array_map($asocANumerico, $arregloAsocDatos);
  foreach ($arregloNumDatos as $fila)
    fputcsv($f, $fila, ',');
  # -- retornamos a la primera posicion del archivo
  fseek($f, 0);
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="' . $nombreArchivo . '";');
  fpassthru($f);
}

# Funcion que retorna los datos de un arreglo asociativo
# en etiquetas html formateadas
function generarCuerpoTableHtml($arregloAsocDatos, $elementosEncabezado){
  global $asocANumerico, $generarFilaBodyHtml, $encerrarTag, $generarEncabezadoHtml;
  # -- convertir cada elemento en un arreglo numerico
  $arregloNumDatos = array_map($asocANumerico, $arregloAsocDatos);
  # -- generar el thead de la tabla
  $thead = array_map($generarEncabezadoHtml, $elementosEncabezado);
  $thead = $encerrarTag(implode('', $thead), 'tr');
  $thead = $encerrarTag($thead, 'thead');
  # -- generar el tbody de la tabla
  $tbody = array_map($generarFilaBodyHtml, $arregloNumDatos);
  $tbody = $encerrarTag(implode('', $tbody), 'tbody');
  return $thead . $tbody;
}
?>