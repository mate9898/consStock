# 🔍 consStock – Retail Product Search Tool

Herramienta de consulta de stock en tiempo real para entornos retail multi-sucursal.
Permite buscar productos por múltiples filtros combinados (marca, atributo, disciplina, género, talle y sucursal)
con selección múltiple dinámica y visualización de detalle por producto, incluyendo imágenes, stock por sucursal y códigos de barras.

---

## 📸 ¿Qué incluye?

| Archivo | Descripción |
|---|---|
| `index.php` | Formulario de búsqueda con filtros predictivos y selección múltiple |
| `process.php` | Procesamiento AJAX del formulario y renderizado de resultados |
| `productos.php` | Vista de detalle de un producto: imágenes, specs, stock y códigos de barras |
| `talles.php` | Endpoint JSON que devuelve talles disponibles según filtros activos |
| `config.php` | Conexión a SQL Server |
| `estilos.css` | Estilos del sistema |

---

## 🚀 Features

- Búsqueda por texto libre (código o descripción de producto)

- Filtros combinables: Atributo, Marca, Disciplina, Género, Promoción, Talle y Sucursal

- Selección múltiple de marcas y sucursales con sistema de tags removibles

- Popup modal de selección de talles cargado dinámicamente según los filtros activos

- Opción "Seleccionar todos" para talles y sucursales con botón de limpieza

- Campos predictivos con datalist y resolución automática de código interno

- Orden de marcas personalizado (primeras 150 marcas priorizadas por relevancia)

- Resultados renderizados vía AJAX sin recargar la página

- Vista de detalle de producto con carrusel de imágenes y tres paneles: descripción, stock y códigos de barras

- Protección de sesión: redirige a `login.php` si no hay sesión activa

---

## 🛠️ Stack

- **Backend:** PHP 8+ con extensión `sqlsrv`

- **Base de datos:** SQL Server — base `FAM450`

- **Frontend:** HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, JavaScript vanilla (`fetch`, `XMLHttpRequest`)

- **Stored Procedures usados:** `AP_BuscaItem7`, `AP_BuscaTalles2`, `AP_UrlFoto`, `AP_ItemDetalles`, `AP_StockItem`, `SP_MarcasStockWeb`

---

## 📁 Estructura del proyecto
```
/
├── index.php        # Formulario principal de búsqueda
├── process.php      # Procesamiento AJAX + renderizado de resultados
├── productos.php    # Vista de detalle de producto
├── talles.php       # Endpoint JSON: talles disponibles por filtro
├── config.php       # Conexión a SQL Server
├── estilos.css      # Estilos del sistema
└── imagenes/
    └── favicon.webp
```

---

## ⚙️ Requisitos

- PHP 8.0+

- Extensión `php_sqlsrv` instalada y habilitada

- Acceso a SQL Server con la base `FAM450`

- Stored procedures creados en el servidor:
```
AP_BuscaItem7       -- Búsqueda principal de productos
AP_BuscaTalles2     -- Talles disponibles según filtros
AP_UrlFoto          -- URLs de imágenes por producto
AP_ItemDetalles     -- Detalle, precio y especificaciones
AP_StockItem        -- Stock por sucursal y talle
SP_MarcasStockWeb   -- Listado de marcas disponibles
```

- Sesión PHP activa (sistema de login propio requerido)

---

## 🔄 Flujo de datos
```
index.php  (formulario con filtros)
  │
  ├── GET  talles.php?...filtros...
  │         └── CALL AP_BuscaTalles2  →  JSON con talles disponibles
  │
  └── POST process.php
            ├── CALL AP_BuscaItem7    →  lista de productos filtrados
            └── renderiza tarjetas de resultado (AJAX)

productos.php?coditm=XXX
  ├── CALL AP_UrlFoto       →  imágenes del producto
  ├── CALL AP_ItemDetalles  →  precio, marca, género, talles, barcodes
  └── CALL AP_StockItem     →  tabla de stock por sucursal
```

---

## 📌 Notas

> Los campos predictivos (Atributo, Marca, Disciplina, Género, Promoción) resuelven automáticamente
> el código interno a partir del texto visible, sin necesidad de selects tradicionales.

> El orden de las marcas en el desplegable sigue una lista de prioridad personalizada
> de 200+ marcas. Las marcas fuera de esa lista se ordenan alfabéticamente al final.
