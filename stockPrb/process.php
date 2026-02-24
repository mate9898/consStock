<!DOCTYPE html>
<html lang="es"></html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="estilos.css">
    <link href="/imagenes/favicon.webp" rel="icon" type="image/svg" sizes="16x16">
</head>

<?php

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['inputText']) &&
    array_key_exists('suc', $_POST) &&
    isset($_POST['at1']) &&
    isset($_POST['at4']) &&
    isset($_POST['at7']) &&
    isset($_POST['at3']) &&
    isset($_POST['at8']) &&
    array_key_exists('talle', $_POST)
) {
    $inputText = htmlspecialchars($_POST['inputText']);
    $sucPost = $_POST['suc'];
    $at1Post = $_POST['at1'];
    $at4Post = $_POST['at4'];
    $at7 = htmlspecialchars($_POST['at7']);
    $at3 = htmlspecialchars($_POST['at3']);
    $at8Post = $_POST['at8'];
    $tallePost = $_POST['talle'];

    // Procesar sucursales
    $sucursales = [];
    if (is_array($sucPost)) {
        foreach ($sucPost as $valorSuc) {
            $valorSuc = trim($valorSuc);
            if ($valorSuc === '') {
                continue;
            }
            $sucursales[] = htmlspecialchars($valorSuc);
        }
    } else {
        $valorSuc = trim($sucPost);
        if ($valorSuc !== '') {
            $sucursales[] = htmlspecialchars($valorSuc);
        }
    }
    $sucursales = array_values(array_unique($sucursales));

    // Procesar talles
    $talles = [];
    if (is_array($tallePost)) {
        foreach ($tallePost as $valorTalle) {
            $valorTalle = trim($valorTalle);
            if ($valorTalle === '') {
                continue;
            }
            $talles[] = $valorTalle;
        }
    } else {
        $valorTalle = trim($tallePost);
        if ($valorTalle !== '') {
            $talles[] = $valorTalle;
        }
    }
    $talles = array_values(array_unique($talles));

    // Procesar marcas
    $marcas = [];
    if (is_array($at4Post)) {
        foreach ($at4Post as $valorMarca) {
            $valorMarca = trim($valorMarca);
            if ($valorMarca === '') {
                continue;
            }
            $marcas[] = htmlspecialchars($valorMarca);
        }
    } else {
        $valorMarca = trim($at4Post);
        if ($valorMarca !== '') {
            $marcas[] = htmlspecialchars($valorMarca);
        }
    }
    $marcas = array_values(array_unique($marcas));
    
    // Procesar atributos (at1)
    $atributos = [];
    if (is_array($at1Post)) {
        foreach ($at1Post as $valorAtributo) {
            $valorAtributo = trim($valorAtributo);
            if ($valorAtributo === '') {
                continue;
            }
            $atributos[] = htmlspecialchars($valorAtributo);
        }
    } else {
        $valorAtributo = trim($at1Post);
        if ($valorAtributo !== '') {
            $atributos[] = htmlspecialchars($valorAtributo);
        }
    }
    $atributos = array_values(array_unique($atributos));
    
    // Procesar disciplinas (at8)
    $disciplinas = [];
    if (is_array($at8Post)) {
        foreach ($at8Post as $valorDisciplina) {
            $valorDisciplina = trim($valorDisciplina);
            if ($valorDisciplina === '') {
                continue;
            }
            $disciplinas[] = htmlspecialchars($valorDisciplina);
        }
    } else {
        $valorDisciplina = trim($at8Post);
        if ($valorDisciplina !== '') {
            $disciplinas[] = htmlspecialchars($valorDisciplina);
        }
    }
    $disciplinas = array_values(array_unique($disciplinas));
    
    // Llama a una función personalizada
    $resultado = buscaItem($inputText, $sucursales, $atributos, $at3, $marcas, $at7, $disciplinas, $talles);

    // Devuelve el resultado al cliente
    echo "<div class='products-container'>";
    foreach ($resultado as $item) {
        $coditm = trim($item['coditm']);
        $descripcion = $item['descripcion'];
        $precio = $item['precio'];
        $Tipoventa = $item['Tipoventa'];
        $foto=$item['foto'];
        $Talles=$item['Talles'];
        
        // Formatear precio sin decimales y con punto de millares
        $precio_formateado = number_format($precio, 0, '.', '.');
        
        echo "<div class='descImg'>";
            echo "<a href='productos.php?coditm=$coditm' target='_blank'>";
                echo "<img class='imgProd' src='https://rcmdr.com.ar/articulos/$foto/$coditm.jpg'>" ;
            echo "</a>";
            echo "<div class='list-group'>";
               echo "<div class='list-group-item'>";
               echo "<div>Codigo de Item: <a href='productos.php?coditm=$coditm'>$coditm</a></div>";
               echo "<div>Descripción: $descripcion</div>";
               echo "<div>Precio: <FONT SIZE=+1>$$precio_formateado</FONT></div>";
               echo "<div>Tipo de Venta: $Tipoventa</div>";
               echo "<div>Talles Disponibles: $Talles</div>";
               echo "</div>";  
            echo "</div>";
        echo "</div>";
    }
    echo "</div>";
}

// Función personalizada: una sola llamada a AP_BuscaItem7 con listas (sucursales, marcas, etc.)
// Requiere haber creado el SP AP_BuscaItem7 en FAM450 (script AP_BuscaItem7.sql).
function buscaItem($texto, $sucursales, $atributos, $atr3, $marcas, $atr7, $disciplinas, $talles = []) {

    include_once "config.php";

    $escape = function ($v) { return str_replace("'", "''", $v); };

    $depo = empty($sucursales) ? '' : implode(',', array_map($escape, $sucursales));
    $at1  = empty($atributos)  ? '' : implode(',', array_map($escape, $atributos));
    $at4  = empty($marcas)     ? '' : implode(',', array_map($escape, $marcas));
    $at8  = empty($disciplinas) ? '' : implode(',', array_map($escape, $disciplinas));
    $talleList = empty($talles) ? '' : implode(',', array_map($escape, $talles));

    $textoSql = $escape($texto);
    $atr3Sql  = $escape($atr3);
    $atr7Sql  = $escape($atr7);

    $sql = "[FAM450].[dbo].[AP_BuscaItem7] '$textoSql','$depo','$at1','$atr3Sql','$at4','$atr7Sql','$at8','$talleList'";

    $params = [];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        sqlsrv_close($conn);
        exit("Error en la búsqueda. Asegurate de tener creado el SP AP_BuscaItem7 en la base (script AP_BuscaItem7.sql).");
    }

    $items = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $coditm = trim($row['coditm']);
        $items[$coditm] = [
            'coditm'     => $coditm,
            'descripcion' => $row['descripcion'],
            'foto'       => $row['Foto'],
            'precio'     => $row['Precio'],
            'Tipoventa'  => $row['Tipoventa'],
            'Talles'     => $row['Talles']
        ];
    }
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    if (empty($items)) {
        exit("No se encontraron productos con esas características!");
    }

    return array_values($items);
}
?>

