<?php
// Incluyo archivo de configuracion
require_once "config.php";

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); 


function printPredictiveFromSql($conn, $sql, $name, $id = null, $placeholder = 'Escribí para buscar…') {
    $id = $id ?: $name;

    $params  = [];
    $options = ["Scrollable" => SQLSRV_CURSOR_KEYSET];
    $stmt    = sqlsrv_query($conn, $sql, $params, $options);

    if ($stmt === false) {
        echo "<small class='text-danger'>Error al obtener datos.</small>";
        return;
    }

    // Input visible + hidden + datalist
    echo "<input type='text' class='form-control select' id='".htmlspecialchars($id,ENT_QUOTES,'UTF-8')."_text' ".
         "placeholder='".htmlspecialchars($placeholder,ENT_QUOTES,'UTF-8')."' list='".htmlspecialchars($id,ENT_QUOTES,'UTF-8')."List' autocomplete='off'>\n";
    echo "<input type='hidden' name='".htmlspecialchars($name,ENT_QUOTES,'UTF-8')."' id='".htmlspecialchars($id,ENT_QUOTES,'UTF-8')."'>\n";
    echo "<datalist id='".htmlspecialchars($id,ENT_QUOTES,'UTF-8')."List'>\n";

    $itemsJS = [];
    while ($registro = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
        // Asumimos: col 0 = código, col 1 = descripción (como en tu código original)
        $code = (string)$registro[0];
        $desc = (string)$registro[1];
        $label = $desc . " (" . $code . ")";

        echo "<option value=\"" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "\"></option>\n";
        $itemsJS[] = ['label' => $label, 'code' => $code];
    }
    echo "</datalist>\n";

    // JS específico de este campo (id-aislado)
    $json = json_encode($itemsJS, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $safeId = json_encode($id, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "<script>(function(){\n".
         "const fid = $safeId;\n".
         "const items = $json || [];\n".
         "const inputText = document.getElementById(fid + '_text');\n".
         "const inputHidden = document.getElementById(fid);\n".
         "function resolveCode(txt){\n".
         "  if(!txt) return '';\n".
         "  const exact = items.find(it => it.label === txt);\n".
         "  if (exact) return exact.code;\n".
         "  const m = txt.match(/^\\s*([A-Za-z0-9]+)\\s*$/);\n".
         "  if (m) {\n".
         "    const only = m[1].toLowerCase();\n".
         "    const byCode = items.find(it => it.code.toLowerCase() === only);\n".
         "    if (byCode) return byCode.code;\n".
         "  }\n".
         "  const low = txt.toLowerCase();\n".
         "  const match = items.filter(it => it.label.toLowerCase().includes(low));\n".
         "  if (match.length === 1) return match[0].code;\n".
         "  return '';\n".
         "}\n".
         "function sync(){\n".
         "  const code = resolveCode(inputText.value);\n".
         "  inputHidden.value = code;\n".
         "  inputText.classList.toggle('is-invalid', !code && inputText.value.trim().length>0);\n".
         "  inputText.classList.toggle('is-valid', !!code);\n".
         "}\n".
         "inputText.addEventListener('input', sync);\n".
         "inputText.addEventListener('change', sync);\n".
         "const form = inputText.closest('form');\n".
         "if (form) form.addEventListener('submit', function(){ sync(); });\n".
         "})();</script>\n";
}

?>

<!DOCTYPE html>
<html lang="es"></html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Stock</title>
    <!--< link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos.css">
    <link href="/imagenes/favicon.webp" rel="icon" type="image/svg" sizes="16x16">
</head>
<body>
    <main class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xs-12 col-md-10 col-lg-6">
                <a href="index.php"> <img class="logo " src="/imagenes/LogoRojo.jpg" alt=""> </a>
            </div>
            <form class="menu">
                <label for="at1_text" class="atributo">Atributo
                    <?php
                        $sql = "SELECT RTRIM(CODATRVAL) as valor, DESCRIPCION
                                FROM dbo.ATRIBUTOSVAL
                                WHERE CODATR = '001'
                                ORDER BY CODATRVAL";
                        printPredictiveFromSql($conn, $sql, 'at1', 'at1');
                    ?>
                </label>
                <label for="at4_text" class="atributo">Marca
                    <?php
                        // Tu código original usaba el texto como $sql. Si es vista/tabla, podés hacer SELECT * FROM ...
                        // Si es un SP que devuelve 2 columnas (codigo, descripcion), dejalo igual.
                        $sql = "[FAM450].[dbo].[AP_MarcasActivas]";
                        printPredictiveFromSql($conn, $sql, 'at4', 'at4');
                    ?>
                </label>
                <label for="at8_text" class="atributo">Disciplina
                    <?php
                        $sql = "SELECT RTRIM(CODATRVAL) as valor, DESCRIPCION
                                FROM dbo.ATRIBUTOSVAL
                                WHERE CODATR = '008'
                                ORDER BY CODATRVAL";
                        printPredictiveFromSql($conn, $sql, 'at8', 'at8');
                    ?>
                </label>
                <label for="at7_text" class="atributo">Promoción
                    <?php
                        $sql = "SELECT RTRIM(CODATRVAL) as valor, DESCRIPCION
                                FROM dbo.ATRIBUTOSVAL
                                WHERE CODATR = '007'
                                ORDER BY CODATRVAL";
                        printPredictiveFromSql($conn, $sql, 'at7', 'at7');
                    ?>
                </label>

                <label for="at3_text" class="atributo">Género
                    <?php
                        $sql = "SELECT RTRIM(CODATRVAL) as valor, DESCRIPCION
                                FROM dbo.ATRIBUTOSVAL
                                WHERE CODATR = '003'
                                ORDER BY DESCRIPCION";
                        printPredictiveFromSql($conn, $sql, 'at3', 'at3');
                    ?>
                </label>
                <label for="suc" class="atributo">Sucursal
                    <?php
                        //genero la consulta Sucursal
                        $sql = "SELECT  rtrim(Codsuc) as Sucursal, NOMBRE FROM dbo.sucursales2 where codsuc <> 5 and codsuc<>401 and codsuc<>402 and codsuc <>403 order by Codsuc";
                        $params = array();
                        $options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
                        $stmt = sqlsrv_query($conn, $sql, $params, $options);

                        $row_count = sqlsrv_num_rows($stmt);

                        if ($row_count === false) echo "Error al obtener datos.";

                        $menu = "<select  class='select' name='suc'  id='suc'>\n";
                        $menu .= "\n<option value=''></option>";

                        while ($registro = sqlsrv_fetch_array($stmt)) {
                            $menu .= "\n<option value='" . $registro[0] . "'>" . $registro[1] . "</option>";
                        }
                        $menu .= "\n</select>";
                        echo $menu;
                    ?>
                </label>  
                <div class="input">
                    <input type="text" id="inputText" placeholder="Introducir codigo o descripción">    
                </div> 
        
                <div class="boton d-inline-flex align-items-center">
                    <i id="arrowIndicatorLeft" data-icon="arrow" class="bi bi-arrow-down-circle me-5 d-none result-arrow" aria-hidden="true" title="Resultados debajo" style="font-size:32px;display:inline-block;vertical-align:middle;color:#dc3545;filter: drop-shadow(0 0 2px #ffffff);"></i>
                    <button type="button" onclick="ejecutarConsulta()" class="btn btn-danger btn-lg btn-block">Buscar</button>
                    <i id="arrowIndicator" data-icon="arrow" class="bi bi-arrow-down-circle ms-5 d-none result-arrow" aria-hidden="true" title="Resultados debajo" style="font-size:32px;display:inline-block;vertical-align:middle;color:#dc3545;filter: drop-shadow(0 0 2px #ffffff);"></i>
                </div>
            </form>  
        <div id="result" style="margin: 10px; font-weight: bold;"></div>  
        
                <!-- MODAL Bootstrap: popup de selección talle-->
        <div class="modal fade" id="paramModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Seleccioná un Talle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div id="modalAlert" class="alert alert-danger d-none"></div>

                        <label for="paramSelect" class="form-label">Valor</label>

                        <select id="paramSelect" class="form-select" disabled>
                            <option value="">Cargando opciones...</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" id="btnCancelar" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="btnAceptar" class="btn btn-primary" disabled>Aceptar</button>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
      async function ejecutarConsulta() {
    try {
        // Valores del formulario
        let inputValue = document.getElementById('inputText').value;
        const txtValue = document.getElementById('inputText').value;
        const at1Value = document.getElementById('at1').value;
        const at3Value = document.getElementById('at3').value;
        const at4Value = document.getElementById('at4').value;
        const at7Value = document.getElementById('at7').value;
        const at8Value = document.getElementById('at8').value;
        const sucValue = document.getElementById('suc').value;

        // Mostrar spinner en ambas flechas durante la búsqueda
        const arrows = document.querySelectorAll('.result-arrow');
        arrows.forEach(a => {
            a.classList.remove('bi-arrow-down-circle', 'arrow-bounce');
            a.classList.add('bi-arrow-repeat', 'search-spin'); // spinner
            a.classList.remove('d-none');
        });

        // Selección de talle (el spinner queda visible mientras tanto)
        const valorTalle = await abrirPopupTalle({txtValue, at1Value, at3Value, at4Value, at7Value, at8Value});

        // Envío AJAX
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'process.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.send('inputText=' + encodeURIComponent(txtValue) +
                 '&suc=' + encodeURIComponent(sucValue) +
                 '&at1=' + encodeURIComponent(at1Value) +
                 '&at3=' + encodeURIComponent(at3Value) +
                 '&at4=' + encodeURIComponent(at4Value) +
                 '&at7=' + encodeURIComponent(at7Value) +
                 '&at8=' + encodeURIComponent(at8Value) +
                 '&talle=' + encodeURIComponent(valorTalle)
                );

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Mostrar resultados
                document.getElementById('result').innerHTML = xhr.responseText;

                // Pasar de spinner a flecha con rebote en ambas
                const arrows = document.querySelectorAll('.result-arrow');
                arrows.forEach(a => {
                    a.classList.remove('bi-arrow-repeat', 'search-spin');
                    a.classList.add('bi-arrow-down-circle', 'arrow-bounce');
                    a.classList.remove('d-none');
                });
            }
        };

        // Limpiar input
        document.getElementById('inputText').value = '';
    } catch (err) {
        // En cancelación o error, ocultar spinners
        const arrows = document.querySelectorAll('.result-arrow');
        arrows.forEach(a => {
            a.classList.remove('bi-arrow-repeat', 'search-spin');
            a.classList.add('d-none');
        });
        console.warn('Acción cancelada o error:', err?.message || err);
    }
};

        function abrirPopupTalle(filtros) {

            const modalEl = document.getElementById('paramModal');
            const selectEl = document.getElementById('paramSelect');
            const btnAceptar = document.getElementById('btnAceptar');
            const alertEl = document.getElementById('modalAlert');
  
            // Limpieza inicial
            selectEl.innerHTML = '<option value="">Cargando opciones...</option>';
            selectEl.disabled = true;
            btnAceptar.disabled = true;
            alertEl.classList.add('d-none');
            alertEl.textContent = '';

            // Creamos instancia del modal
            const bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

            // Devolvemos una Promise que se resuelve/rechaza según acción del usuario
            return new Promise(async (resolve, reject) => {
                // Eventos temporales para aceptar/cancelar
                const onAceptar = () => {
                const val = selectEl.value;
                if (!val) {
                    alertEl.textContent = 'Debés seleccionar un valor.';
                    alertEl.classList.remove('d-none');
                    return;
                }
                // Cerramos y resolvemos
                bsModal.hide();
                resolve(val);
                };

                //const onCancel = () => {
                //reject(new Error('Usuario canceló la selección'));
                //};

                const onCancel = () => {
                const val = '';
  
                // Cerramos y resolvemos
                bsModal.hide();
                resolve(val);
                };



                // Listeners
                document.getElementById('btnAceptar').addEventListener('click', onAceptar, { once: true });
                document.getElementById('btnCancelar').addEventListener('click', onCancel, { once: true });
                modalEl.addEventListener('hidden.bs.modal', function handler() {
                modalEl.removeEventListener('hidden.bs.modal', handler);
                // Si se cierra sin aceptar, rechazamos (para cubrir cierre con X)
                reject(new Error('Popup cerrado'));
                }, { once: true });

                // Abrimos el modal ya mismo (mientras se cargan opciones)
                bsModal.show();

                // Cargar opciones desde el servidor (get_options.php)
                try {
                const qs = new URLSearchParams(filtros).toString();
                const resp = await fetch('talles.php?' + qs, { method: 'GET' });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);

                const data = await resp.json(); // Espera: [{id:"...", text:"..."}, ...]
                selectEl.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {
                    selectEl.innerHTML = '<option value="">(Sin resultados)</option>';
                    selectEl.disabled = true;
                    btnAceptar.disabled = true;
                } else {
                    // Agrego opción placeholder
                    selectEl.insertAdjacentHTML('beforeend', `<option value="">-- Seleccione --</option>`);
                    for (const item of data) {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.text;
                    selectEl.appendChild(opt);
                    }
                    selectEl.disabled = false;
                    btnAceptar.disabled = false;
                }

                } catch (e) {
                alertEl.textContent = 'Error al cargar opciones: ' + (e.message || e);
                alertEl.classList.remove('d-none');
                selectEl.innerHTML = '<option value="">(Error)</option>';
                selectEl.disabled = true;
                btnAceptar.disabled = true;
                }
            });
        }
    </script> 
</body>
</html>
