<?php
// Incluyo archivo de configuracion
require_once "config.php";

if( !isset($_GET["coditm"]) ){
	exit("falta codigo de articulo");
	}
$item=$_GET["coditm"];

//genero la consulta de datos
$sql = "[FAM450].[dbo].[AP_ItemDetalles] '$item'";
$params = array();
$options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
$stmt2 = sqlsrv_query($conn, $sql, $params, $options);
$row_count = sqlsrv_num_rows($stmt2);

if ($row_count === false) echo "Error al obtener datos.";
if ($row_count == 0) exit("Sin datos!");


//genero la consulta Stock
$sql = "[FAM450].[dbo].[AP_StockItem] '$item'";
$params = array();
$options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
$stmt = sqlsrv_query($conn, $sql, $params, $options);

$row_count = sqlsrv_num_rows($stmt);

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
            <a href="index.html"> <img class="logo " src="/imagenes/LogoRojo.jpg" alt=""> </a>
            </div>   
            <div col-12>
                    <?php   
                    if ($row_count === false || $row_count == 0) {
                        echo "No hay resultados.";
                    } 
                    else
                    {
                        echo "<table class='table table-striped table-hover' border='1'>";
                        echo "<br>";
                        // Obtener los nombres de las columnas dinámicamente
                        echo "<tr>";
                        foreach (sqlsrv_field_metadata($stmt) as $field) {
                            echo "<th>" . htmlspecialchars($field["Name"]) . "</th>";
                        }
                        echo "</tr>";

                        // Obtener y mostrar los datos
                        while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        echo "<tr>";
                        foreach ($fila as $valor) {
                            echo "<td>" . htmlspecialchars($valor) . "</td>";
                        }
                        echo "</tr>";
                        }
                        echo "</table>";
                    }
                 

                    if ($row = sqlsrv_fetch_array($stmt2)) {
                        $precio = $row['Precio'];
                        $lista = $row['CODLIS'];
                        $marca = $row['Marca'];
                        $tipoventa = $row['TipoVenta'];
                        $disciplina = $row['Disciplina'];
                        $genero = $row['Genero'];
                    
                    echo "<div class='containerMT'>";
                    echo "<div class='box'><strong>Precio: &nbsp <span> </span>" . $precio ."</strong></div>";
                    echo "<div class='box'><strong>Lista: &nbsp <span> </span></strong>" . $lista ."</div>";
                    echo "<div class='box'><strong>Tipo Venta: &nbsp <span> </span></strong>" . $tipoventa ."</div>";
                    echo"<div class='box'><strong>Marca: &nbsp <span> </span></strong>"  . $marca ."</div>";
                    echo"<div class='box'><strong>Disciplina: &nbsp <span> </span></strong>"  . $disciplina ."</div>";
                    echo"<div class='box'><strong>Genero: &nbsp <span> </span></strong>"  . $genero ."</div>";
                    echo "</div>";

                    }                     
                    
                    echo "<table class='table table-striped' border='2'>";
                    echo "<thead>
                            <tr>
                                <th>Barra</th>
                                <th>Codtal</th>
                                <th>Equiv</th>
                            </tr>
                          </thead>";
                    echo "<tbody>";

                    while ($row = sqlsrv_fetch_array($stmt2)) {
                        $precio = $row['Precio'];
                        $lista = $row['CODLIS'];
                        $marca = $row['Marca'];
                        $tipoventa = $row['TipoVenta'];
                        
                        echo "<tr>
                                <td>{$row['CODITMALTERNATIVO']}</td>
                                <td>{$row['CODTAL']}</td>
                                <td>{$row['Equivalencia']}</td>          
                              </tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";

                    $close = sqlsrv_close($conn)
                    or die("Ha sucedido un error inexperado en la desconexion de la base de datos");
                      
                    ?>        
            </div>
        </div>
    </main>     
</body>
</html>
