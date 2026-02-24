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

// Guardar filas de stock para construir la tabla después
$stockFilas = [];
while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $stockFilas[] = $fila;
}

// Construir HTML de la tabla de stock
$stockTableHtml = '';
if (count($stockFilas) > 0) {
    $stockTableHtml .= "<table class='table table-hover caption-top table-striped table-sm narrow-table stock-table' border='2'>";
    $stockTableHtml .= "<thead><tr>";
    foreach (array_keys($stockFilas[0]) as $col) {
        $stockTableHtml .= "<th>" . htmlspecialchars($col) . "</th>";
    }
    $stockTableHtml .= "</tr></thead><tbody class='table-group-divider'>";
    foreach ($stockFilas as $fila) {
        $stockTableHtml .= "<tr>";
        foreach ($fila as $valor) {
            $stockTableHtml .= "<td>" . htmlspecialchars($valor) . "</td>";
        }
        $stockTableHtml .= "</tr>";
    }
    $stockTableHtml .= "</tbody></table>";
} else {
    $stockTableHtml = "<p class='text-muted'>No hay resultados.</p>";
}

// Construir HTML de la tabla de códigos de barras
$barcodeTableHtml = "<table class='table table-hover caption-top table-light table-striped narrow-table barcode-table' border='2'>";
$barcodeTableHtml .= "<thead><tr><th>Barra</th><th>Codtal</th><th>Equiv</th></tr></thead>";
$barcodeTableHtml .= "<tbody class='table-group-divider'>";
foreach ($curva as $a) {
    $barcodeTableHtml .= "<tr><td>" . htmlspecialchars($a["coditmalernativo"] ?? '') . "</td>";
    $barcodeTableHtml .= "<td>" . htmlspecialchars($a["codtal"] ?? '') . "</td>";
    $barcodeTableHtml .= "<td>" . htmlspecialchars($a["equivalencia"] ?? '') . "</td></tr>";
}
$barcodeTableHtml .= "</tbody></table>";

?>

<!DOCTYPE html>
<html lang="es">

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
                <td style="background-color:#D9000D; height: 60px; width: 100%; text-align: center; cursor: pointer;">
                    <a href="index.php" style="text-decoration: none; display: block; height: 100%; line-height: 60px;" title="Ir a búsqueda principal">
                        <img src="https://puntodeportivoar.vteximg.com.br/arquivos/logo-email.png" alt="Logo Punto Deportivo" style="vertical-align: middle;">
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <div class="productos-volver-container">
        <a href="index.php" class="btn btn-outline-secondary productos-volver-btn" title="Volver a búsqueda principal">← Volver a búsqueda</a>
    </div>

    <?php $close = sqlsrv_close($conn); ?>

    <div class="producto-detail-layout">
        <!-- Columna izquierda: imagen y botones -->
        <div class="producto-detail-left">
            <div id="carouselfoto" class="carousel slide producto-detail-carousel" data-bs-theme="dark" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php if (empty($imagenes)) : ?>
                        <div class="carousel-item active">
                            <div class="d-block w-100 producto-detail-img-placeholder">
                                <span class="text-muted">Sin imágenes disponibles</span>
                            </div>
                        </div>
                    <?php else : ?>
                        <?php foreach ($imagenes as $index => $ruta): ?>
                            <?php
                                $rutaLimpia = trim((string)$ruta);
                                if ($rutaLimpia === '') { continue; }
                                $esAbsoluta = preg_match('/^https?:\/\//i', $rutaLimpia) === 1;
                                if (!$esAbsoluta) {
                                    if (str_starts_with($rutaLimpia, '/')) {
                                        $rutaLimpia = 'https://rcmdr.com.ar' . $rutaLimpia;
                                    } elseif (str_starts_with($rutaLimpia, 'articulos/')) {
                                        $rutaLimpia = 'https://rcmdr.com.ar/' . $rutaLimpia;
                                    }
                                }
                                if (preg_match('/^http:\/\//i', $rutaLimpia)) {
                                    $rutaLimpia = preg_replace('/^http:\/\//i', 'https://', $rutaLimpia);
                                }
                                $rutaLimpia = str_replace(' ', '%20', $rutaLimpia);
                                $src = htmlspecialchars($rutaLimpia, ENT_QUOTES, 'UTF-8');
                            ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="<?= $src ?>" class="d-block w-100" alt="Imagen del producto <?= htmlspecialchars($coditm, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" referrerpolicy="no-referrer">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($imagenes) && count($imagenes) > 1) : ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselfoto" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselfoto" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
                <?php endif; ?>
            </div>

            <div class="producto-detail-buttons">
                <button type="button" class="producto-detail-btn active" data-panel="descripcion" aria-pressed="true">
                    <svg class="producto-detail-btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Descripción y especificaciones</span>
                </button>
                <button type="button" class="producto-detail-btn" data-panel="stock">
                    <svg class="producto-detail-btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    <span>Stock por sucursales y talles</span>
                </button>
                <button type="button" class="producto-detail-btn" data-panel="barras">
                    <svg class="producto-detail-btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M8 7h8"/><path d="M6 11h12"/><path d="M4 15h16"/><path d="M2 19h20"/></svg>
                    <span>Código de barras y equivalencias</span>
                </button>
            </div>
        </div>

        <!-- Columna derecha: datos del producto y paneles -->
        <div class="producto-detail-right">
            <div class="producto-detail-header">
                <h1 class="product-name-left"><?php echo htmlspecialchars($curva[0]['descripcion']); ?></h1>
                <div class="product-price-container">
                    <span class="product-price">$ <?php echo number_format($curva[0]['precio'], 0, '.', '.'); ?></span>
                </div>
                <div class="product-details-container">
                    <div class="product-detail-item">
                        <span class="detail-label">Referencia:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($coditm); ?></span>
                    </div>
                </div>
            </div>

            <div class="producto-detail-panels">
                <div id="panel-descripcion" class="producto-detail-panel active" role="tabpanel">
                    <div class="product-description-section">
                        <h2 class="section-title">Descripción del producto</h2>
                        <div class="description-content"><?php echo $data; ?></div>
                    </div>
                    <div class="specifications-section">
                        <h2 class="section-title">Especificaciones</h2>
                        <ul class="spec-list">
                            <li><strong>Marca:</strong> <?php echo htmlspecialchars(strtoupper($curva[0]['marca'])); ?></li>
                            <li><strong>Género:</strong> <?php echo htmlspecialchars(strtoupper($curva[0]['genero'])); ?></li>
                            <li><strong>Disciplina:</strong> <?php echo htmlspecialchars(strtoupper($curva[0]['disciplina'])); ?></li>
                            <li><strong>Tipo de venta:</strong> <?php echo htmlspecialchars($curva[0]['tipoventa']); ?></li>
                            <li><strong>Lista:</strong> <?php echo htmlspecialchars($curva[0]['lista']); ?></li>
                        </ul>
                    </div>
                </div>
                <div id="panel-stock" class="producto-detail-panel" role="tabpanel" hidden>
                    <h2 class="section-title">Stock por sucursales y talles</h2>
                    <div class="producto-detail-table-wrap"><?php echo $stockTableHtml; ?></div>
                </div>
                <div id="panel-barras" class="producto-detail-panel" role="tabpanel" hidden>
                    <h2 class="section-title">Código de barras y equivalencias</h2>
                    <div class="producto-detail-table-wrap"><?php echo $barcodeTableHtml; ?></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var buttons = document.querySelectorAll('.producto-detail-btn');
        var panels = document.querySelectorAll('.producto-detail-panel');
        buttons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var panelId = 'panel-' + this.getAttribute('data-panel');
                buttons.forEach(function(b) { b.classList.remove('active'); b.setAttribute('aria-pressed', 'false'); });
                this.classList.add('active');
                this.setAttribute('aria-pressed', 'true');
                panels.forEach(function(p) {
                    if (p.id === panelId) {
                        p.classList.add('active');
                        p.removeAttribute('hidden');
                    } else {
                        p.classList.remove('active');
                        p.setAttribute('hidden', '');
                    }
                });
            });
        });
    })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>