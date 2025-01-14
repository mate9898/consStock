<?php
// Incluyo archivo de configuracion
require_once "config.php";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Stock</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
    <header>
        <h2>Stock</h2>
    </header>

    <!-- Formulario de input -->
    </div>
    <form action="rotacion.php" method="post" class="grid2">
    <label for="suc" class="label">Sucursal
        <?php
        //genero la consulta sucursal
        $sql = "SELECT Codsuc,Nombre FROM dbo.Sucursales2";
        $params = array();
        $options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $stmt = sqlsrv_query($conn, $sql, $params, $options);

        $row_count = sqlsrv_num_rows($stmt);

        if ($row_count === false) echo "Error al obtener datos.";

        $menu = "<select name='suc'>\n";
        $menu .= "\n<option value=''></option>";

        while ($registro = sqlsrv_fetch_array($stmt)) {
            $menu .= "\n<option value='" . $registro[0] . "'>" . $registro[1] . "</option>";
        }
        $menu .= "\n</select>";
        echo $menu;
        ?>
    </label>

    <div id="result" style="margin-top: 20px; font-weight: bold;"></div>
    </form>
    <script>
        // Detecta el cambio en el campo select
        document.getElementById('suc').addEventListener('change', function() {
            let sucursal = this.value;

            // Solo ejecuta si se seleccionó una sucursal
            if (sucursal) {
                // Crear una solicitud AJAX
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'stockitem.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                // Enviar los datos al servidor
                xhr.send('suc=' + encodeURIComponent(sucursal));

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
