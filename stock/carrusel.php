
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

//desconecto la base de datos

$close = sqlsrv_close($conn)
	or die("Ha sucedido un error inexperado en la desconexion de la base de datos");

?>

<!DOCTYPE html>
<html lang="es"></html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrusel</title>
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
    <div class="row justify-content-center">
        <div class="col-xs-12 col-md-10 col-lg-6">
             <img class="logo" src="/imagenes/LogoRojo.jpg" alt="" onclick="cerrarPagina()" style="cursor:pointer;" > 
        </div>
    </div>
    <div class="container mt-5" >
        <div id="carouselfoto" class="carousel slide" data-bs-theme="dark">
            <div class="carousel-inner">
                <?php foreach ($imagenes as $index => $ruta): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= $ruta ?>" class="d-block w-100">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselfoto" data-bs-slide="prev" >
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselfoto" data-bs-slide="next" >
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <div class="dataCRS" id="dataCRS">
        <?php
            echo ($data);
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

