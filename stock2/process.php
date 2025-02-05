<!DOCTYPE html>
<html lang="es"></html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../estilos/estilos.css" />
    <link href="/imagenes/favicon.webp" rel="icon" type="image/svg" sizes="16x16">
</head>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inputText'])) {
    $inputText = htmlspecialchars($_POST['inputText']);

    // Llama a una función personalizada
    $resultado = buscaItem($inputText);

    // Devuelve el resultado al cliente
    foreach ($resultado as $item) {
        $coditm = trim($item['coditm']);
        $descripcion = $item['descripcion'];
        $foto=$item['foto'];
echo "<div class='descImg'>";
        echo "<ul class='list-group'>";
       echo "<li class='list-group-item'>";
       echo "<div><strong>Codigo Item:</strong> <a href='stock.php?coditm=$coditm'>$coditm</a></div>";
       echo "<div><strong>Descripción:</strong> $descripcion</div>";
       echo "</li>";  
       echo "</ul>";
       echo "<img class='imgProd' src='http://rcmdr.com.ar/articulos/$foto/$coditm.jpg'>" ;
echo "</div>";
    }
}

// Función personalizada
function buscaItem($texto) {
    
    include_once "config.php";

    $sql = "[FAM450].[dbo].[AP_BuscaItem] '$texto'";
    $params = array();
    $options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
    $stmt = sqlsrv_query($conn, $sql, $params, $options);
    
    $row_count = sqlsrv_num_rows($stmt);

    if ($row_count === false) echo "Error al obtener datos.";
    if ($row_count == 0) exit("No se encontraron productos con esas características!");

    while ($row = sqlsrv_fetch_array($stmt)) {
        $coditm = $row['coditm'];
        $descripcion = $row['descripcion'];
        $foto = $row['Foto'];
        $item[] = array(
            'coditm'=>$coditm,'descripcion'=>$descripcion,'foto'=>$foto
        );
    }


    $close = sqlsrv_close($conn)
	or die("Ha sucedido un error inexperado en la desconexion de la base de datos");
    
    return ($item); 
    
   

}
?>

