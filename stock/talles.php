<?php
header('Content-Type: application/json; charset=utf-8');

require_once "config.php";

try {
    // Lee EXACTAMENTE estos parámetros (vacíos si no vienen)
    $txtValue = $_GET['txtValue'] ?? '';
    $at1Value = $_GET['at1Value'] ?? '';
    $at3Value = $_GET['at3Value'] ?? '';
    $at4Value = $_GET['at4Value'] ?? '';
    $at7Value = $_GET['at7Value'] ?? '';
    $at8Value = $_GET['at8Value'] ?? '';

    // (Opcional) Validaciones mínimas
    // if ($txtValue === '') { http_response_code(400); echo json_encode(["error"=>"Falta txtValue"]); exit; }

    $sql = "[FAM450].[dbo].[AP_BuscaTalles2] '$txtValue','$at1Value','$at3Value','$at4Value','$at7Value','$at8Value' ";
    $params = array();
    $options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
    $stmt = sqlsrv_query($conn, $sql, $params, $options);
    $row_count = sqlsrv_num_rows($stmt);

    if ($row_count === 0) { echo json_encode(["error"=>"sin resultados","detail"=>sqlsrv_errors()]); exit; }

    //if ($row_count === false) { http_response_code(500); echo json_encode(["error"=>"Error en prepare","detail"=>sqlsrv_errors()]); exit; }
    if ($stmt === false) { http_response_code(500); echo json_encode(["error"=>"Error en prepare","detail"=>sqlsrv_errors()]); exit; }

    //if (!sqlsrv_execute($stmt)) { http_response_code(500); echo json_encode(["error"=>"Error en execute","detail"=>sqlsrv_errors()]); exit; }

    
    while ($row = sqlsrv_fetch_array($stmt)) {     
        $rows[] = ["id" => $row["id"], "text" =>$row["text"]];
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    echo json_encode($rows);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
