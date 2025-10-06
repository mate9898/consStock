<?php
// Incluyo archivo de configuracion
require_once "config.php";

$coditm = $_GET["coditm"];

//genero la consulta
$sql = "[FAM450].[dbo].[AP_UrlFoto] '$coditm'";
$params = array();
$options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
$stmt = sqlsrv_query($conn, $sql, $params, $options);

$row_count = sqlsrv_num_rows($stmt);

if ($row_count === false) echo "Error al obtener datos.";

$imagenes = [];
while ($fila = sqlsrv_fetch_array($stmt)) {
    $imagenes[] = $fila['ruta'];
    $data = $fila['HTML'];
}

if (isset($data)=== false) {
    $data="Sin Datos";
};

$sql = "[FAM450].[dbo].[AP_ItemDetalles] '$coditm'";
$params = array();
$options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
$stmt2 = sqlsrv_query($conn, $sql, $params, $options);
$row_count = sqlsrv_num_rows($stmt2);

if ($row_count === false) echo "Error al obtener datos.";
if ($row_count == 0) exit("Sin datos!");

while ($row = sqlsrv_fetch_array($stmt2)) {

    $precio = $row['Precio'];
    $lista = $row['CODLIS'];
    $marca = $row['Marca'];
    $tipoventa = $row['TipoVenta'];
    $disciplina = $row['Disciplina'];
    $genero = $row['Genero'];
    $coditmalternativo = $row['CODITMALTERNATIVO'];
    $talle = $row['CODTAL'];
    $equiv = $row['Equivalencia'];
    $descripcion = $row['DESCRIPCION'];
    $url = $row['URL'];

    $curva[] = array(
        'precio' => $precio,
        'lista' => $lista,
        'marca' => $marca,
        'tipoventa' => $tipoventa,
        'disciplina' => $disciplina,
        'url' => $url,
        'genero' => $genero,
        'coditmalernativo' => $coditmalternativo,
        'codtal' => $talle,
        'equivalencia' => $equiv,
        'descripcion' => $descripcion
    );
}

//genero la consulta Stock
$sql = "[FAM450].[dbo].[AP_StockItem] '$coditm'";
$params = array();
$options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
$stmt = sqlsrv_query($conn, $sql, $params, $options);
$row_count = sqlsrv_num_rows($stmt);

if ($row_count === false) echo "Error al obtener datos.";
if ($row_count == 0) exit("Sin datos!");

?>

<!DOCTYPE html>
<html lang="es">

</html>

<head>
    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv='expires' content='0'>
    <meta http-equiv='pragma' content='no-cache'>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css" />
    <link href="/imagenes/favicon.webp" rel="icon" type="image/svg" sizes="16x16">
    <script>
        function cerrarPagina() {
            window.close();
        }
    </script>

</head>

<body>
    <div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="background-color:#D9000D; height: 60px; width: 100%; text-align: center;">
                    <a href=<?php echo $curva[0]['url']; ?> style="text-decoration: none;" target="_blank">
                        <img src="https://puntodeportivoar.vteximg.com.br/arquivos/logo-email.png" alt="Logo Punto Deportivo">
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <div id="carouselfoto" class="carousel slide" data-bs-theme="dark">
        <div class="carousel-inner">
            <?php foreach ($imagenes as $index => $ruta): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= $ruta ?>" class="d-block w-100">
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselfoto" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselfoto" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <!-- Nombre del producto debajo de la imagen -->
    <div class="product-name-container">
        <h1 class="product-name-left"><?php echo $curva[0]['descripcion']; ?></h1>
    </div>

    <!-- Precio debajo del nombre -->
    <div class="product-price-container">
        <span class="product-price">$ <?php echo $curva[0]['precio']; ?></span>
    </div>

    <!-- Referencia debajo del precio -->
    <div class="product-details-container">
        <div class="product-detail-item">
            <span class="detail-label">Referencia:</span>
            <span class="detail-value"><?php echo $coditm; ?></span>
        </div>
    </div>

    <!-- Línea separadora -->
    <hr class="separator-line">
    <!-- Descripción del Producto -->

    <div class="product-description-section">
        <h2 class="section-title">DESCRIPCIÓN DEL PRODUCTO</h2>
        <div class="description-content">
            <?php echo ($data); ?>
        </div>
    </div>

    <!-- Especificaciones -->
    <div class="specifications-section">
        <h2 class="section-title">ESPECIFICACIONES:</h2>
        <ul style="padding-left: 20px;">
            <li><strong>Marca:</strong><?php echo strtoupper($curva[0]['marca']); ?></li>
            <li><strong>Género:</strong> <?php echo strtoupper($curva[0]['genero']); ?></li>
            <li><strong>Disciplina:</strong> <?php echo strtoupper($curva[0]['disciplina']); ?></li>
            <li><strong>Tipo de venta:</strong> <?php echo $curva[0]['tipoventa']; ?></li>
            <li><strong>Lista:</strong> <?php echo $curva[0]['lista']; ?></li>

        </ul>
    </div>
    <!-- Línea separadora -->
    <hr class="separator-line">
    <div style="padding-right: 2.5vw; padding-left: 2.5vw; ">
        <?php
        if ($row_count === false || $row_count == 0) {
            echo "No hay resultados.";
        } else {
            echo "<table class='table table-hover caption-top table-striped table-sm narrow-table stock-table' border='2'>";
            // Obtener los nombres de las columnas dinámicamente
            echo "<thead>";
            echo "<tr>";
            foreach (sqlsrv_field_metadata($stmt) as $field) {
                echo "<th>" . htmlspecialchars($field["Name"]) . "</th>";
            }
            echo "</tr>";
            echo "</thead>";
            echo "<tbody class='table-group-divider'>";
            // Obtener y mostrar los datos
            while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                echo "<tr>";
                foreach ($fila as $valor) {
                    echo "<td>" . htmlspecialchars($valor) . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        }

        $close = sqlsrv_close($conn)
            or die("Ha sucedido un error inexperado en la desconexion de la base de datos");
        echo "<table class='table table-hover caption-top table-light table-striped narrow-table barcode-table' border='2'>";
        echo "<thead>
                                <tr>
                                    <th>Barra</th>
                                    <th>Codtal</th>
                                    <th>Equiv</th>
                                </tr>
                        </thead>";
        echo "<tbody class='table-group-divider'>";
        foreach ($curva as $a) {
            echo ("<tr>");
            echo "<td>" . $a["coditmalernativo"] . "</td>";
            echo "<td>" . $a["codtal"] . "</td>";
            echo "<td>" . $a["equivalencia"] . "</td>";
            echo ("</tr>");
        }
        echo "</tbody>";
        echo "</table>";
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>