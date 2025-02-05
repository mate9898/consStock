<?php
$item = $_POST["item"];
$suc = $_POST["suc"];
include_once "config.php";

$sql = "[FAM450].[dbo].[AP_StockItem] '$item', $suc";
$params = array();
$options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
$stmt = sqlsrv_query($conn, $sql, $params, $options);
$row_count = sqlsrv_num_rows($stmt);

if ($row_count === false) echo "Error al obtener datos.";
if ($row_count == 0) exit("Sin datos!");

echo "<table class='stock-table'>";
echo "<thead>
        <tr>
            <th>Cod Barra</th>
            <th>Cod Talle</th>
            <th>Equiv</th>
            <th>Cant</th>
        </tr>
      </thead>";
echo "<tbody>";

if ($row = sqlsrv_fetch_array($stmt)) {
    $precio = $row['Precio'];
    $lista = $row['CODLIS'];
    $marca = $row['Marca'];
    $tipoventa = $row['TipoVenta'];
echo "<div class='containerPL'>";
echo "<div class='box'><strong>Precio: &nbsp <span> </span></strong>" . $precio ."</div>";;
echo "<div class='box'><strong>Lista: &nbsp <span> </span></strong>" . $lista ."</div>";;
echo "</div>";

echo "<div class='containerMT'>";
echo"<div class='box2'><strong>Marca: &nbsp <span> </span></strong>"  . $marca ."</div>";;
echo "<div class='box2'><strong>Tipo Venta: &nbsp <span> </span></strong>" . $tipoventa ."</div>";;
echo "</div>";
}

while ($row = sqlsrv_fetch_array($stmt)) {
    $precio = $row['Precio'];
    $lista = $row['CODLIS'];
    $marca = $row['Marca'];
    $tipoventa = $row['TipoVenta'];
    
    echo "<tr>
            <td>{$row['CODITMALTERNATIVO']}</td>
            <td>{$row['CODTAL']}</td>
            <td>{$row['Equivalencia']}</td>
            <td>{$row['Cantidad']}</td>
            
          </tr>";
}
echo "</tbody>";
echo "</table>";

sqlsrv_close($conn);
?>
