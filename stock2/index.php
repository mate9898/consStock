<?php
// Incluyo archivo de configuracion
require_once "config.php";
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
                <a href="index.php"> <img class="logo " src="/imagenes/LogoRojo.jpg" alt=""> </a>
            </div>
            <div>
                <form class="menu"    >
                    <div>
                        <input type="text" id="inputText" placeholder="Introducir codigo de barras">
                        <div id="result" style="margin-top: 20px; font-weight: bold;"></div>
                    </div>
                    <label for="at1" class="label">Atributo1
                            <?php
                            //genero la consulta at1
                            $sql = "SELECT rtrim(CODATRVAL) as valor, DESCRIPCION FROM dbo.ATRIBUTOSVAL WHERE  (CODATR = '001') order by descripcion";
                            $params = array();
                            $options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
                            $stmt = sqlsrv_query($conn, $sql, $params, $options);

                            $row_count = sqlsrv_num_rows($stmt);

                            if ($row_count === false) echo "Error al obtener datos.";

                            $menu = "<select name='at1'>\n";
                            $menu .= "\n<option value=''></option>";

                            while ($registro = sqlsrv_fetch_array($stmt)) {
                                $menu .= "\n<option value='" . $registro[0] . "'>" . $registro[1] . "</option>";
                            }
                            $menu .= "\n</select>";
                            echo $menu;
                            ?>
                    </label>
                    <label for="at4" class="label">Marca
                            <?php
                            //genero la consulta at4
                            $sql = "SELECT  rtrim(CODATRVAL) as valor, DESCRIPCION FROM dbo.ATRIBUTOSVAL WHERE  (CODATR = '004') order by descripcion";
                            $params = array();
                            $options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
                            $stmt = sqlsrv_query($conn, $sql, $params, $options);

                            $row_count = sqlsrv_num_rows($stmt);

                            if ($row_count === false) echo "Error al obtener datos.";

                            $menu = "<select name='at4'>\n";
                            $menu .= "\n<option value=''></option>";

                            while ($registro = sqlsrv_fetch_array($stmt)) {
                                $menu .= "\n<option value='" . $registro[0] . "'>" . $registro[1] . "</option>";
                            }
                            $menu .= "\n</select>";
                            echo $menu;
                            ?>
                        </label>
                        <label for="at7" class="label">Promoción
                            <?php
                            //genero la consulta at7
                            $sql = "SELECT  rtrim(CODATRVAL) as valor, DESCRIPCION FROM dbo.ATRIBUTOSVAL WHERE  (CODATR = '007') order by descripcion";
                            $params = array();
                            $options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
                            $stmt = sqlsrv_query($conn, $sql, $params, $options);

                            $row_count = sqlsrv_num_rows($stmt);

                            if ($row_count === false) echo "Error al obtener datos.";

                            $menu = "<select name='at7'>\n";
                            $menu .= "\n<option value=''></option>";

                            while ($registro = sqlsrv_fetch_array($stmt)) {
                                $menu .= "\n<option value='" . $registro[0] . "'>" . $registro[1] . "</option>";
                            }
                            $menu .= "\n</select>";
                            echo $menu;
                            ?>
                        </label>    
                
                </form>
        </div>
    </main>
    <!-- Formulario de input -->
    <script>
        // Detecta la tecla Enter en el input
        document.getElementById('inputText').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Evita el envío predeterminado

                // Obtener el valor del input
                let inputValue = this.value;

                // Crear una solicitud AJAX
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'process.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                // Enviar los datos al servidor
                xhr.send('inputText=' + encodeURIComponent(inputValue));

                // Recibir la respuesta del servidor
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Mostrar el resultado en la página
                        document.getElementById('result').innerHTML = xhr.responseText;
                    }
                };
                // Limpiar el input después de enviar
                this.value = '';
            }
        });
    </script>
</body>
</html>
