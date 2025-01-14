<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inputText'])) {
    $inputText = htmlspecialchars($_POST['inputText']);

    // Llama a una función personalizada
    $resultado = buscaItem($inputText);

    // Devuelve el resultado al cliente
    foreach ($resultado as $item) {
        $coditmalternativo = $item['coditmalternativo'];
        $coditm = $item['coditm'];
        $descripcion = $item['descripcion'];

        // Generamos el hyperlink que lleva a detalle.php
       // echo "<li><a href='detalle.php?coditmalternativo=$coditmalternativo'>
       //         [$coditmalternativo] $coditm - $descripcion
       //       </a></li>";
       echo "<hr>";
       echo "<li>";
       echo "<div><strong>Codigo Item:</strong> <a href='detalle.php?coditm=$coditm'>$coditm</a></div>";
       echo "<div><strong>Codigo de Barras:</strong> $coditmalternativo</div>";
       echo "<div><strong>Descripción:</strong> $descripcion</div>";
       echo "</li>";
        
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

    while ($row = sqlsrv_fetch_array($stmt)) {
        $coditmalternativo = $row['coditmalternativo'];
        $coditm = $row['coditm'];
        $descripcion = $row['descripcion'];

        $item[] = array(
            'coditmalternativo' => $coditmalternativo,'coditm'=>$coditm,'descripcion'=>$descripcion
        );
    }

    $close = sqlsrv_close($conn)
	or die("Ha sucedido un error inexperado en la desconexion de la base de datos");
    
    return ($item); 
}
?>