<?php
// Incluyo archivo de configuracion
require_once "config.php";

if( !isset($_GET["coditm"]) ){
	exit("falta codigo de articulo");
	}
$item=$_GET["coditm"];
?>


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
<body>

<main class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xs-12 col-md-10 col-lg-6">
                <img class="logo " src="/imagenes/LogoRojo.jpg" alt="">
            </div>
        </div>     
                <label for="suc" class="label">Sucursal
                    <?php
                    //genero la consulta sucursal
                    $sql = "SELECT Codsuc,Nombre FROM dbo.Sucursales2";
                    $params = array();
                    $options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
                    $stmt = sqlsrv_query($conn, $sql, $params, $options);

                    $row_count = sqlsrv_num_rows($stmt);

                    if ($row_count === false) echo "Error al obtener datos.";

                    $menu = "<select name='suc' id='suc'>\n";
                    $menu .= "\n<option value=''></option>";

                    while ($registro = sqlsrv_fetch_array($stmt)) {
                        $menu .= "\n<option value='" . $registro[0] . "'>" . $registro[1] . "</option>";
                    }
                    $menu .= "\n</select>";
                    echo $menu; 
                    ?> 
                </label>
            
                

    <p id="output1"></p>
    <p id="output2"></p>

    <div id="result" style="margin-top: 20px; font-weight: bold;"></div>

    <script>
    
        // Detecta el cambio en el campo select
        document.getElementById('suc').addEventListener('change', function() {
            let sucursal = this.value;
            let item="<?php echo $item;?>"; 

            // Solo ejecuta si se seleccionó una sucursal
            if (sucursal) {
                // Crear una solicitud AJAX
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'detalle.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                // Enviar los datos al servidor
                xhr.send('suc=' + encodeURIComponent(sucursal) + '&item=' + encodeURIComponent(item));

                // Recibir la respuesta del servidor
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Mostrar el resultado en la página
                        document.getElementById('result').innerHTML = xhr.responseText;
                    }
                };
            } else {
                // Si no se selecciona nada, limpia el resultado
                document.getElementById('result').innerHTML = '';
            }
        });
    </script>

</body>
</html>
