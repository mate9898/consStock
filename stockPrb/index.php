<?php
// Incluyo archivo de configuracion
require_once "config.php";

session_start();
if (empty($_SESSION['logueo'])) {
    header('Location: login.php');
    exit;
}

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

    // Si es para marcas (at4), aplicar orden personalizado
    if ($id === 'at4') {
        // Orden de marcas según la lista proporcionada (primeras 150)
        $ordenMarcas = [
            'REEBOK', 'CHAMPION', 'ADIDAS', 'CROCS', 'FILA', 'WAKE SPORT', 'KAPPA', 'HUNTLEY', 
            '47 STREET', 'FOOTY', 'PENALTY', 'TOPPER', 'DIADORA', 'ASICS', 'ATOMIK', 'UMBRO', 
            'KEVINGSTON', '361', 'HAVAIANA', 'FAF', 'UNDERARMOUR', 'MONTAGNE', 'PONY', 'REEF', 
            'KONNA', 'MUNDO MEDIAS', 'PUMA', 'ALL DAY', 'DRB', 'ADMITONE', 'HYDRO', 
            'ACQUA di FIORE', 'PUNTO DEPORTIVO', 'SATORI', 'PROYEC', 'RUSTY', 'RIP CURL', 
            'EVERLAST', 'CONVERSE', 'COCA COLA', 'HEAD', 'SPEED', 'NEW BALANCE', 'APTITUD', 
            'VANS', 'SNAUWAERT', 'KAPPA AUTHENTIC', 'SAMBA', 'VOLCOM', 'PROCER', 'TRENDY', 
            'LE COQ SPORTIF', 'NO APLICA', 'SPY GO SURFING', 'DISCOVERY', 'JESABA', 'SENSEI', 
            'CIRCA', 'RAPTOR', 'LA GLORIA ETERNA', 'SANTA CRUZ', 'FOXSOCKS', 'OLYMPIKUS', 
            'DERMOGREEN', 'ADDNICE', 'SIMBRA', 'KICK OFF', 'SIXZERO', 'FINDERS', 'BAGUNZA', 
            'NBA', 'BESTWAY', 'JOMA', 'ROCAMADOUR', 'M70', 'NEVADOS', 'VARIOS', 'DOYEN', 
            'OSIRIS', 'BURTON', 'UPV Un pasito a la vez', 'BULLPADEL', 'CONTIGO', 'INDEPEND', 
            'BARCELONA', 'ISABEL LA CATOLICA', 'TIFOX', 'DUNLOP', 'BIG LIFE', 'ALPINE SKATE', 
            'TRAVEL TECH', 'SPIRIT', 'AGUILA SPORT', '12 ONZAS', 'INDEPENDIENTE', 'GILBERT', 
            'KIOSHI', 'Y TU QUIQUE?', 'SORMA', 'SPORTS FAMILY SA', 'RANGO', 'SPEEDO', 
            'VAN COMO PIÑA', 'RACING', 'SUPERGA', 'KOOGA', 'VIEJA SCUL', 'TOP FORCE', 'AVIA', 
            'REUSCH', 'FLYWING', 'MORE CORE MCD', 'BROOKS', 'FOLAU', 'NSM', 'FLASH', 'CALIPSO', 
            'SAEKO', 'EZLIFE', 'MUTZ', 'BOCA', 'CARTAGO', 'NEW ERA', 'REFLEX', 'LEJOPI', 
            'DIPORTTO', 'GIVOVA', 'SET', 'QIX', 'ZOO YORK', 'SHARK', 'LAS OREIRO', 
            'STAR SPORT', 'MORMAII', 'JDH', 'BIG SPORT', 'ELEPANTS', 'BILLABONG', 
            'PUNTO SPORTY', 'CLASS LIFE', 'ROFFT', 'BUNJI', 'SWIMFIT', 'AFA', 'VONNE', 
            'KRIAL', 'LSD', 'HANG LOOSE', 'VILAS', 'GOMAS GASPAR', 'HAWAIANAS', 'GRAYS', 
            'TEAMWEAR', 'ONE DAY', 'AQUAT', 'ALITRA', 'TBT', 'NASSAU', 'SUNFLEX', 'Penguin', 
            'MOUL', 'JUVENTUS', 'K-SWISS', 'ATLETIC SERVICE SA', 'GOODCHILLS', 'CAPI', 
            'HI TEC', 'MAS7', 'NET', 'ORANGE', 'NASA', 'BAISIDIWEI', 'IGUANA', 'PRAHIERAS', 
            'TORO Y PAMPA', 'STARKE', 'IPANEMA', 'BONNE', 'DINDAN SRL', 'RETHINK', 
            'GOORIN BROS', 'TIMING', 'REACC', 'POWERBLADE', 'Y-LOVERS', 'DUDE', 'SPADY', 
            'KBL', 'SIUX', 'WEB ELLIS', 'BODY THERM', 'SHOTER', 'STEMAX', 'SISTEMA', 
            'QUIKSILVER', 'WELSTAR', 'ROXY', 'NOX', 'ACTION', 'SUFIX', 'TDP', 'SOFTEE', 
            'COCODRILE', 'DC', 'alpinestar', 'MODESIO', 'SPORT SOQUET', 'KALEL', 
            'LANDSURFER', 'ELEMENT', 'PERFECT SPORTS', 'BAGSS', 'RIVER', 'ELECTRIC', 'DREAMER'
        ];
        
        // Obtener todos los registros primero
        $todosItems = [];
        while ($registro = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
            $code = (string)$registro[0];
            $desc = trim((string)$registro[1]);
            $todosItems[] = ['code' => $code, 'desc' => $desc];
        }
        
        // Crear un mapa de descripción a índice de orden
        $ordenMap = [];
        foreach ($ordenMarcas as $index => $marca) {
            $ordenMap[strtoupper(trim($marca))] = $index;
        }
        
        // Ordenar los items: primero los que están en el orden, luego los demás
        usort($todosItems, function($a, $b) use ($ordenMap) {
            $descA = strtoupper(trim($a['desc']));
            $descB = strtoupper(trim($b['desc']));
            
            $indexA = isset($ordenMap[$descA]) ? $ordenMap[$descA] : 9999;
            $indexB = isset($ordenMap[$descB]) ? $ordenMap[$descB] : 9999;
            
            if ($indexA !== $indexB) {
                return $indexA - $indexB;
            }
            // Si ambos están fuera del orden o tienen el mismo índice, ordenar alfabéticamente
            return strcmp($descA, $descB);
        });
        
        // Generar el datalist y el array JS con el orden correcto
        $itemsJS = [];
        foreach ($todosItems as $item) {
            $code = $item['code'];
            $desc = $item['desc'];
            $label = $desc . " (" . $code . ")";
            $displayLabel = $desc;
            
            echo "<option value=\"" . htmlspecialchars($displayLabel, ENT_QUOTES, 'UTF-8') . "\"></option>\n";
            $itemsJS[] = ['label' => $label, 'code' => $code];
        }
    } else {
        // Para otros campos, mantener el comportamiento original
        $itemsJS = [];
        while ($registro = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
            // Asumimos: col 0 = código, col 1 = descripción (como en tu código original)
            $code = (string)$registro[0];
            $desc = (string)$registro[1];
            $label = $desc . " (" . $code . ")";
            $displayLabel = $desc; // Label sin el código para mostrar

            echo "<option value=\"" . htmlspecialchars($displayLabel, ENT_QUOTES, 'UTF-8') . "\"></option>\n";
            $itemsJS[] = ['label' => $label, 'code' => $code];
        }
    }
    echo "</datalist>\n";

    // JS específico de este campo (id-aislado)
    $json = json_encode($itemsJS, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $safeId = json_encode($id, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Exponer items globalmente para selección múltiple (marcas, atributos, disciplinas)
    $exposeGlobal = "";
    if ($id === 'at4') {
        $exposeGlobal = "window.itemsMarcasGlobal = items;\n";
    } elseif ($id === 'at1') {
        $exposeGlobal = "window.itemsAtributosGlobal = items;\n";
    } elseif ($id === 'at8') {
        $exposeGlobal = "window.itemsDisciplinasGlobal = items;\n";
    }
    
    echo "<script>(function(){\n".
         "const fid = $safeId;\n".
         "const items = $json || [];\n".
         $exposeGlobal.
         "const inputText = document.getElementById(fid + '_text');\n".
         "const inputHidden = document.getElementById(fid);\n".
         "function resolveCode(txt){\n".
         "  if(!txt) return '';\n".
         "  const exact = items.find(it => it.label === txt);\n".
         "  if (exact) return exact.code;\n".
         "  // Buscar por descripción sola (sin el código entre paréntesis)\n".
         "  const byDesc = items.find(it => it.label.startsWith(txt + ' ('));\n".
         "  if (byDesc) return byDesc.code;\n".
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
<html lang="es">
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
    <link rel="stylesheet" href="estilos.css?v=<?php echo filemtime('estilos.css'); ?>">
    <link href="/imagenes/favicon.webp" rel="icon" type="image/svg" sizes="16x16">
</head>
<body>
    <main class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xs-12 col-md-10 col-lg-6">
                <a href="index.php"> <img class="logo " src="/imagenes/LogoRojo.jpg" alt=""> </a>
            </div>
            <form class="menu">
                <!-- Primera fila: Búsqueda y Sucursal -->
                <div class="filters-row">
                    <div class="search-wrapper">
                        <input type="text" id="inputText" placeholder="Introducir codigo o descripción">
                        <i class="bi bi-search search-icon"></i>
                    </div>
                    <div class="sucursal-wrapper">
                        <div class="sucursal-select-container">
                            <?php
                                $sql = "SELECT  rtrim(Codsuc) as Sucursal, NOMBRE FROM dbo.sucursales2 where codsuc <> 5 and codsuc<>401 and codsuc<>402 and codsuc <>403 order by Codsuc";
                                $params = array();
                                $options =  array("Scrollable" => SQLSRV_CURSOR_KEYSET);
                                $stmt = sqlsrv_query($conn, $sql, $params, $options);

                                $row_count = sqlsrv_num_rows($stmt);

                                if ($row_count === false) echo "Error al obtener datos.";

                                $menu = "<select class='select' name='suc' id='suc'>\n";
                                $menu .= "\n<option value=''>Seleccioná sucursal...</option>";
                                // Opción especial para seleccionar todas las sucursales
                                $menu .= "\n<option value='__ALL_SUC__'>Seleccionar todas las sucursales</option>";

                                while ($registro = sqlsrv_fetch_array($stmt)) {
                                    $menu .= "\n<option value='" . htmlspecialchars($registro[0], ENT_QUOTES, 'UTF-8') . "' data-nombre='" . htmlspecialchars($registro[1], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($registro[1], ENT_QUOTES, 'UTF-8') . "</option>";
                                }
                                $menu .= "\n</select>";
                                echo $menu;
                            ?>
                            <div id="sucursalesSelected" class="selected-tags-container-sucursal empty">
                                <span class="selected-tags-placeholder">Ninguna</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Primera fila de filtros: Atributo, Marca, Disciplina -->
                <div class="filters-row filters-row-center">
                    <label for="at1_text" class="atributo">Atributo
                        <div class="marca-select-container">
                            <?php
                                $sql = "SELECT RTRIM(CODATRVAL) as valor, DESCRIPCION
                                        FROM dbo.ATRIBUTOSVAL
                                        WHERE CODATR = '001'
                                        ORDER BY CODATRVAL";
                                printPredictiveFromSql($conn, $sql, 'at1', 'at1', 'Ej: Calzado, Indumentaria');
                            ?>
                        </div>
                    </label>
                    <label for="at4_text" class="atributo">Marca
                        <div class="marca-select-container">
                            <?php
                                $sql = "[FAM450].[dbo].[SP_MarcasStockWeb]";
                                printPredictiveFromSql($conn, $sql, 'at4', 'at4', 'Ej: Adidas, Fila');
                            ?>
                            <div id="marcasSelected" class="selected-tags-container-marca empty">
                                <span class="selected-tags-placeholder">Ninguna</span>
                            </div>
                        </div>
                    </label>
                    <label for="at8_text" class="atributo">Disciplina
                        <div class="marca-select-container">
                            <?php
                                $sql = "SELECT RTRIM(CODATRVAL) as valor, DESCRIPCION
                                        FROM dbo.ATRIBUTOSVAL
                                        WHERE CODATR = '008'
                                        ORDER BY CODATRVAL";
                                printPredictiveFromSql($conn, $sql, 'at8', 'at8', 'Ej: Running, Fútbol');
                            ?>
                        </div>
                    </label>
                </div>
                
                <!-- Segunda fila de filtros: Promoción, Género -->
                <div class="filters-row filters-row-center">
                    <label for="at7_text" class="atributo">Promoción
                        <?php
                            $sql = "SELECT RTRIM(CODATRVAL) as valor, DESCRIPCION
                                    FROM dbo.ATRIBUTOSVAL
                                    WHERE CODATR = '007'
                                    ORDER BY CODATRVAL";
                            printPredictiveFromSql($conn, $sql, 'at7', 'at7', 'Ej: 2x1, Descuento');
                        ?>
                    </label>
                    <label for="at3_text" class="atributo">Género
                        <?php
                            $sql = "SELECT RTRIM(CODATRVAL) as valor, DESCRIPCION
                                    FROM dbo.ATRIBUTOSVAL
                                    WHERE CODATR = '003'
                                    ORDER BY DESCRIPCION";
                            printPredictiveFromSql($conn, $sql, 'at3', 'at3', 'Ej: Hombre, Mujer');
                        ?>
                    </label>
                </div> 
        
                <!-- Botón de búsqueda rediseñado -->
                <div class="boton">
                    <button type="button" onclick="ejecutarConsulta()" class="btn-search">
                        Buscar
                        <i class="bi bi-search"></i>
                    </button>
                    <!-- Ícono de flecha embellecido debajo del botón -->
                    <div class="arrow-indicators">
                        <i id="arrowIndicator" data-icon="arrow" class="bi bi-arrow-down-circle arrow-indicator d-none result-arrow" aria-hidden="true" title="Resultados debajo"></i>
                    </div>
                </div>
            </form>  
        <div id="result" style="margin: 10px; font-weight: bold;"></div>  
        
                <!-- MODAL Bootstrap: popup de selección talle-->
        <div class="modal fade" id="paramModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Seleccioná uno o más talles</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div id="modalAlert" class="alert alert-danger d-none"></div>

                        <div class="mb-3">
                            <label class="form-label">Talles seleccionados</label>
                            <div id="selectedTags" class="selected-tags-container empty">
                                <span class="selected-tags-placeholder">Ningún talle seleccionado</span>
                            </div>
                        </div>

                        <select id="paramSelect" class="form-select" disabled>
                            <option value="">Seleccioná un talle...</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" id="btnAceptar" class="btn btn-primary btn-gradient-red" disabled>Aceptar</button>
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
        const marcasSeleccionadas = selectedMarcas.map(m => m.value);
        const at7Value = document.getElementById('at7').value;
        const at8Value = document.getElementById('at8').value;
        const sucursalesSeleccionadas = selectedSucursales.map(s => s.value);

        // Mostrar spinner en ambas flechas durante la búsqueda
        const arrows = document.querySelectorAll('.result-arrow');
        arrows.forEach(a => {
            a.classList.remove('bi-arrow-down-circle', 'arrow-bounce');
            a.classList.add('bi-arrow-repeat', 'search-spin'); // spinner
            a.classList.remove('d-none');
        });

        // Selección de talle (el spinner queda visible mientras tanto)
        // Para talles, necesitamos pasar los valores como string (o vacío)
        const at1ValueForTalle = at1Value || '';
        const at4ValueForTalle = marcasSeleccionadas.length > 0 ? marcasSeleccionadas[0] : '';
        const at8ValueForTalle = at8Value || '';
        const tallesSeleccionados = await abrirPopupTalle({txtValue, at1Value: at1ValueForTalle, at3Value, at4Value: at4ValueForTalle, at7Value, at8Value: at8ValueForTalle});

        // Envío AJAX
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'process.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        const params = new URLSearchParams();
        params.append('inputText', txtValue);
        
        if (Array.isArray(sucursalesSeleccionadas) && sucursalesSeleccionadas.length > 0) {
            sucursalesSeleccionadas.forEach(suc => {
                params.append('suc[]', suc);
            });
        } else {
            params.append('suc', '');
        }
        
        params.append('at1', at1Value || '');
        
        params.append('at3', at3Value);
        
        if (Array.isArray(marcasSeleccionadas) && marcasSeleccionadas.length > 0) {
            marcasSeleccionadas.forEach(marca => {
                params.append('at4[]', marca);
            });
        } else {
            params.append('at4', '');
        }
        
        params.append('at7', at7Value);
        
        params.append('at8', at8Value || '');

        if (Array.isArray(tallesSeleccionados) && tallesSeleccionados.length > 0) {
            tallesSeleccionados.forEach(talle => {
                params.append('talle[]', talle);
            });
        } else {
            params.append('talle', '');
        }

        xhr.send(params.toString());

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

        // Manejo de selección múltiple de sucursales
        let selectedSucursales = [];
        (function() {
            const selectSuc = document.getElementById('suc');
            const tagsContainer = document.getElementById('sucursalesSelected');
            const placeholderText = 'Ninguna';

            if (!selectSuc || !tagsContainer) return;

            let sucursalesTodasSeleccionadas = false;

            const obtenerTodasLasSucursales = () => {
                return Array.from(selectSuc.options)
                    .map(o => o.value)
                    .filter(v => v && v !== '__ALL_SUC__');
            };

            const syncSucursalesDisabled = () => {
                const todas = obtenerTodasLasSucursales();
                Array.from(selectSuc.options).forEach(opt => {
                    if (!opt.value || opt.value === '__ALL_SUC__') return;
                    opt.disabled = selectedSucursales.some(s => s.value === opt.value);
                });
                sucursalesTodasSeleccionadas = selectedSucursales.length === todas.length && todas.length > 0;
            };

            const renderSucursalesTags = () => {
                tagsContainer.innerHTML = '';
                if (selectedSucursales.length === 0) {
                    tagsContainer.classList.add('empty');
                    const span = document.createElement('span');
                    span.className = 'selected-tags-placeholder';
                    span.textContent = placeholderText;
                    tagsContainer.appendChild(span);
                    return;
                }

                tagsContainer.classList.remove('empty');
                selectedSucursales.forEach(({ value, nombre }) => {
                    const tag = document.createElement('span');
                    tag.className = 'selected-tag';

                    const label = document.createElement('span');
                    label.textContent = nombre;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'selected-tag-remove';
                    removeBtn.setAttribute('aria-label', `Quitar sucursal ${nombre}`);
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', () => {
                        // Quitar sucursal de la selección
                        selectedSucursales = selectedSucursales.filter(s => s.value !== value);
                        const option = selectSuc.querySelector(`option[value="${value}"]`);
                        if (option) option.disabled = false;

                        // Al quitar una cuando estaban todas, pasamos a estado "no todas"
                        if (sucursalesTodasSeleccionadas) {
                            sucursalesTodasSeleccionadas = false;
                        }

                        syncSucursalesDisabled();
                        renderSucursalesTags();
                        selectSuc.focus();
                    });

                    tag.appendChild(label);
                    tag.appendChild(removeBtn);
                    tagsContainer.appendChild(tag);
                });

                // Botón para borrar toda la selección de sucursales
                if (sucursalesTodasSeleccionadas && selectedSucursales.length > 0) {
                    const clearAllBtn = document.createElement('button');
                    clearAllBtn.type = 'button';
                    clearAllBtn.className = 'btn btn-sm btn-outline-secondary mb-2';
                    clearAllBtn.textContent = 'Borrar selección';
                    clearAllBtn.addEventListener('click', () => {
                        // Limpiar todas las sucursales seleccionadas
                        selectedSucursales = [];
                        sucursalesTodasSeleccionadas = false;
                        // Habilitar nuevamente todas las opciones (excepto placeholder y "todas")
                        Array.from(selectSuc.options).forEach(opt => {
                            if (!opt.value || opt.value === '__ALL_SUC__') return;
                            opt.disabled = false;
                        });
                        syncSucursalesDisabled();
                        renderSucursalesTags();
                        selectSuc.focus();
                    });
                    // Lo insertamos arriba de todos los tags
                    tagsContainer.insertBefore(clearAllBtn, tagsContainer.firstChild);
                }
            };

            selectSuc.addEventListener('change', function() {
                const value = this.value;
                if (!value) return;

                const todas = obtenerTodasLasSucursales();

                // Opción especial: seleccionar todas las sucursales
                if (value === '__ALL_SUC__') {
                    selectedSucursales = todas.map(v => {
                        const opt = this.querySelector(`option[value="${v}"]`);
                        const nombre = opt ? (opt.getAttribute('data-nombre') || opt.textContent) : v;
                        return { value: v, nombre };
                    });
                    sucursalesTodasSeleccionadas = true;
                    syncSucursalesDisabled();
                    renderSucursalesTags();
                    this.value = '';
                    return;
                }

                // Si están todas seleccionadas, no permitimos agregar más desde el select
                if (sucursalesTodasSeleccionadas) {
                    this.value = '';
                    return;
                }

                if (!selectedSucursales.find(s => s.value === value)) {
                    const selectedOption = this.options[this.selectedIndex];
                    const nombre = selectedOption.getAttribute('data-nombre') || selectedOption.textContent;
                    selectedSucursales.push({ value, nombre });
                    selectedOption.disabled = true;
                    syncSucursalesDisabled();
                    renderSucursalesTags();
                }
                this.value = '';
            });

            tagsContainer.addEventListener('click', () => {
                selectSuc.focus();
            });

            // Inicial
            syncSucursalesDisabled();
            renderSucursalesTags();
        })();

        // Manejo de selección múltiple de marcas
        let selectedMarcas = [];
        
        (function() {
            const inputTextMarca = document.getElementById('at4_text');
            const inputHiddenMarca = document.getElementById('at4');
            const tagsContainerMarca = document.getElementById('marcasSelected');
            const placeholderTextMarca = 'Ninguna';

            if (!inputTextMarca || !inputHiddenMarca || !tagsContainerMarca) return;

            // Obtener items de la variable global expuesta por printPredictiveFromSql
            const obtenerItemsMarcas = () => {
                if (typeof window.itemsMarcasGlobal !== 'undefined' && Array.isArray(window.itemsMarcasGlobal)) {
                    return window.itemsMarcasGlobal;
                }
                return [];
            };

            const resolveCodeMarca = (txt) => {
                const itemsMarcasGlobal = obtenerItemsMarcas();
                if (!txt || itemsMarcasGlobal.length === 0) return '';
                const low = txt.toLowerCase();
                
                // Buscar coincidencia exacta con label completo
                const exact = itemsMarcasGlobal.find(it => {
                    const labelLow = (it.label || '').toLowerCase();
                    return labelLow === low || labelLow === (txt + ' (').toLowerCase();
                });
                if (exact) return exact.code;
                
                // Buscar por descripción sola (sin el código entre paréntesis)
                const byDesc = itemsMarcasGlobal.find(it => {
                    const label = it.label || '';
                    return label.toLowerCase().startsWith(txt.toLowerCase() + ' (');
                });
                if (byDesc) return byDesc.code;
                
                // Buscar por código solo
                const m = txt.match(/^\s*([A-Za-z0-9]+)\s*$/);
                if (m) {
                    const only = m[1].toLowerCase();
                    const byCode = itemsMarcasGlobal.find(it => (it.code || '').toLowerCase() === only);
                    if (byCode) return byCode.code;
                }
                
                // Buscar parcial
                const match = itemsMarcasGlobal.filter(it => {
                    const label = (it.label || '').toLowerCase();
                    const code = (it.code || '').toLowerCase();
                    return label.includes(low) || code.includes(low);
                });
                if (match.length === 1) return match[0].code;
                
                return '';
            };

            const renderMarcasTags = () => {
                tagsContainerMarca.innerHTML = '';
                if (selectedMarcas.length === 0) {
                    tagsContainerMarca.classList.add('empty');
                    const span = document.createElement('span');
                    span.className = 'selected-tags-placeholder';
                    span.textContent = placeholderTextMarca;
                    tagsContainerMarca.appendChild(span);
                    return;
                }

                tagsContainerMarca.classList.remove('empty');
                selectedMarcas.forEach(({ value, label }) => {
                    const tag = document.createElement('span');
                    tag.className = 'selected-tag';

                    const labelSpan = document.createElement('span');
                    labelSpan.textContent = label;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'selected-tag-remove';
                    removeBtn.setAttribute('aria-label', `Quitar marca ${label}`);
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', () => {
                        selectedMarcas = selectedMarcas.filter(m => m.value !== value);
                        renderMarcasTags();
                        inputTextMarca.focus();
                    });

                    tag.appendChild(labelSpan);
                    tag.appendChild(removeBtn);
                    tagsContainerMarca.appendChild(tag);
                });
            };

            const syncMarca = () => {
                const code = resolveCodeMarca(inputTextMarca.value);
                inputHiddenMarca.value = code;
                inputTextMarca.classList.toggle('is-invalid', !code && inputTextMarca.value.trim().length > 0);
                inputTextMarca.classList.toggle('is-valid', !!code);
            };

            // Agregar marca cuando se presiona Enter o se pierde el foco con un valor válido
            const agregarMarca = () => {
                const code = resolveCodeMarca(inputTextMarca.value);
                if (!code) return;

                // Verificar si ya está seleccionada
                if (!selectedMarcas.find(m => m.value === code)) {
                    const itemsMarcasGlobal = obtenerItemsMarcas();
                    const item = itemsMarcasGlobal.find(it => it.code === code);
                    let label = inputTextMarca.value;
                    if (item && item.label) {
                        // Extraer solo la descripción del label (sin el código)
                        const match = item.label.match(/^(.+?)\s*\(/);
                        label = match ? match[1].trim() : item.label;
                    }
                    selectedMarcas.push({ value: code, label: label });
                    renderMarcasTags();
                }
                inputTextMarca.value = '';
                inputHiddenMarca.value = '';
                inputTextMarca.classList.remove('is-valid', 'is-invalid');
            };

            inputTextMarca.addEventListener('input', syncMarca);
            inputTextMarca.addEventListener('change', () => {
                syncMarca();
                if (inputTextMarca.value.trim() && resolveCodeMarca(inputTextMarca.value)) {
                    agregarMarca();
                }
            });
            inputTextMarca.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    syncMarca();
                    if (inputTextMarca.value.trim() && resolveCodeMarca(inputTextMarca.value)) {
                        agregarMarca();
                    }
                }
            });

            tagsContainerMarca.addEventListener('click', () => {
                inputTextMarca.focus();
            });

            renderMarcasTags();
        })();

        // (Atributo y Disciplina ahora usan selección simple mediante el campo predictivo original)

        function abrirPopupTalle(filtros) {

            const modalEl = document.getElementById('paramModal');
            const selectEl = document.getElementById('paramSelect');
            const btnAceptar = document.getElementById('btnAceptar');
            const alertEl = document.getElementById('modalAlert');
            const tagsContainer = document.getElementById('selectedTags');
            const placeholderText = 'Ningún talle seleccionado';
  
            // Limpieza inicial
            selectEl.innerHTML = '<option value="">Cargando opciones...</option>';
            selectEl.disabled = true;
            btnAceptar.disabled = true;
            alertEl.classList.add('d-none');
            alertEl.textContent = '';
            if (tagsContainer) {
                tagsContainer.innerHTML = `<span class="selected-tags-placeholder">${placeholderText}</span>`;
                tagsContainer.classList.add('empty');
            }

            // Creamos instancia del modal
            const bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

            // Devolvemos una Promise que se resuelve/rechaza según acción del usuario
            return new Promise(async (resolve, reject) => {
                let seCerroPorAccion = false;
                let accionFinalizada = false;
                let selectedValues = [];
                let todosSeleccionados = false; // indica si están seleccionados todos los talles

                const obtenerTodosLosTalles = () => {
                    return Array.from(selectEl.options)
                        .map(o => o.value)
                        .filter(v => v && v !== '__ALL__');
                };

                const syncOptionsDisabled = () => {
                    const todosLosTalles = obtenerTodosLosTalles();
                    Array.from(selectEl.options).forEach(opt => {
                        if (!opt.value || opt.value === '__ALL__') return;
                        opt.disabled = selectedValues.includes(opt.value);
                    });
                    todosSeleccionados = selectedValues.length === todosLosTalles.length && todosLosTalles.length > 0;
                };

                const renderSelectedTags = () => {
                    if (!tagsContainer) return;
                    tagsContainer.innerHTML = '';
                    if (selectedValues.length === 0) {
                        tagsContainer.classList.add('empty');
                        const span = document.createElement('span');
                        span.className = 'selected-tags-placeholder';
                        span.textContent = placeholderText;
                        tagsContainer.appendChild(span);
                        return;
                    }
                    tagsContainer.classList.remove('empty');
                    for (const value of selectedValues) {
                        const opt = Array.from(selectEl.options).find(o => o.value === value);
                        const labelText = opt ? opt.textContent : value;

                        const tag = document.createElement('span');
                        tag.className = 'selected-tag badge rounded-pill bg-light text-dark border border-secondary-subtle d-inline-flex align-items-center gap-2 px-3 py-2';

                        const label = document.createElement('span');
                        label.className = 'selected-tag-label fw-semibold';
                        label.textContent = labelText;

                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'selected-tag-remove btn btn-sm btn-link text-secondary p-0';
                        removeBtn.setAttribute('aria-label', `Quitar talle ${labelText}`);
                        removeBtn.innerHTML = '<span aria-hidden="true">&times;</span>';
                        removeBtn.addEventListener('click', () => {
                            if (accionFinalizada) return;
                            selectedValues = selectedValues.filter(item => item !== value);
                            const option = Array.from(selectEl.options).find(o => o.value === value);
                            if (option) option.disabled = false;
                            actualizarEstadoSeleccion();
                            selectEl.focus();
                        });

                        tag.appendChild(label);
                        tag.appendChild(removeBtn);
                        tagsContainer.appendChild(tag);
                    }

                    // Botón para borrar toda la selección de talles
                    if (todosSeleccionados && selectedValues.length > 0) {
                        const clearAllBtn = document.createElement('button');
                        clearAllBtn.type = 'button';
                        clearAllBtn.className = 'btn btn-sm btn-outline-secondary mt-2';
                        clearAllBtn.textContent = 'Borrar selección';
                        clearAllBtn.addEventListener('click', () => {
                            if (accionFinalizada) return;
                            // Limpiar todos los talles seleccionados
                            selectedValues = [];
                            todosSeleccionados = false;
                            // Habilitar nuevamente todas las opciones (excepto placeholder y "todas")
                            Array.from(selectEl.options).forEach(opt => {
                                if (!opt.value || opt.value === '__ALL__') return;
                                opt.disabled = false;
                            });
                            syncOptionsDisabled();
                            actualizarEstadoSeleccion();
                            selectEl.focus();
                        });
                        tagsContainer.appendChild(clearAllBtn);
                    }
                };

                const actualizarEstadoSeleccion = () => {
                    renderSelectedTags();
                    if (selectedValues.length > 0) {
                        alertEl.classList.add('d-none');
                        alertEl.textContent = '';
                    }
                    btnAceptar.disabled = selectedValues.length === 0;
                };

                const onTagsContainerClick = () => {
                    if (!accionFinalizada) {
                        selectEl.focus();
                    }
                };

                if (tagsContainer) {
                    tagsContainer.addEventListener('click', onTagsContainerClick);
                }

                const onSelectChange = () => {
                    if (accionFinalizada) return;
                    const value = selectEl.value;
                    if (!value) return;

                    const todosLosTalles = obtenerTodosLosTalles();

                    // Opción especial: seleccionar todos los talles
                    if (value === '__ALL__') {
                        selectedValues = [...todosLosTalles];
                        syncOptionsDisabled();
                        actualizarEstadoSeleccion();
                        selectEl.value = '';
                        return;
                    }

                    // Si seguimos en estado "todos seleccionados" (sin haber quitado ninguno),
                    // no permitimos seleccionar otros talles desde el select.
                    if (todosSeleccionados) {
                        selectEl.value = '';
                        return;
                    }

                    if (!selectedValues.includes(value)) {
                        selectedValues.push(value);
                        syncOptionsDisabled();
                        actualizarEstadoSeleccion();
                    }
                    selectEl.value = '';
                };

                // Eventos temporales para aceptar/cancelar
                const onAceptar = () => {
                    if (accionFinalizada) return;
                    if (selectedValues.length === 0) {
                        alertEl.textContent = 'Debés seleccionar al menos un talle.';
                        alertEl.classList.remove('d-none');
                        return;
                    }

                    accionFinalizada = true;
                    seCerroPorAccion = true;
                    bsModal.hide();
                    resolve(selectedValues.slice());
                };

                // Listeners
                selectEl.addEventListener('change', onSelectChange);
                btnAceptar.addEventListener('click', onAceptar);
                modalEl.addEventListener('hidden.bs.modal', function handler() {
                    modalEl.removeEventListener('hidden.bs.modal', handler);
                    selectEl.removeEventListener('change', onSelectChange);
                    btnAceptar.removeEventListener('click', onAceptar);
                    if (tagsContainer) {
                        tagsContainer.removeEventListener('click', onTagsContainerClick);
                        tagsContainer.innerHTML = `<span class="selected-tags-placeholder">${placeholderText}</span>`;
                        tagsContainer.classList.add('empty');
                    }
                    // Si se cierra sin aceptar, rechazamos (para cubrir cierre con X)
                    if (!seCerroPorAccion) {
                        reject(new Error('Popup cerrado'));
                    }
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
                    const placeholderOption = document.createElement('option');
                    placeholderOption.value = '';
                    placeholderOption.textContent = 'Seleccioná un talle...';
                    selectEl.appendChild(placeholderOption);

                    // Opción especial: seleccionar todos los talles
                    const todosOption = document.createElement('option');
                    todosOption.value = '__ALL__';
                    todosOption.textContent = 'Seleccionar todos los talles';
                    selectEl.appendChild(todosOption);

                    for (const item of data) {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.text;
                        selectEl.appendChild(opt);
                    }
                    selectedValues = [];
                    todosSeleccionados = false;
                    selectEl.disabled = false;
                    btnAceptar.disabled = false;
                    selectEl.value = '';
                    syncOptionsDisabled();
                    actualizarEstadoSeleccion();
                    selectEl.focus();
                }

                } catch (e) {
                alertEl.textContent = 'Error al cargar opciones: ' + (e.message || e);
                alertEl.classList.remove('d-none');
                selectEl.innerHTML = '<option value="">(Error)</option>';
                selectEl.disabled = true;
                btnAceptar.disabled = true;
                actualizarEstadoSeleccion();
                }
            });
        }
    </script> 
</body>
</html>
