-- Consulta SQL Server para mostrar artículos de la marca "MUNDO MEDIAS" con su stock por sucursal
-- Utilizando AP_BuscaItem5 para obtener artículos y consulta directa para obtener stock

USE FAM450;
GO

SET NOCOUNT ON;

-- Código de marca para "MUNDO MEDIAS"
DECLARE @CodigoMarca VARCHAR(100) = '174';

-- Obtener todos los artículos de la marca (sin filtrar por sucursal)
-- Eliminar la tabla si ya existe
IF OBJECT_ID('tempdb..#TodosArticulos') IS NOT NULL
    DROP TABLE #TodosArticulos;

-- Crear tabla temporal con todas las columnas que devuelve AP_BuscaItem5
CREATE TABLE #TodosArticulos (
    coditm VARCHAR(50),
    descripcion VARCHAR(500),
    Foto VARCHAR(100),
    Precio DECIMAL(18,2),
    Tipoventa VARCHAR(100),
    Talles VARCHAR(500)
);

INSERT INTO #TodosArticulos
EXEC [FAM450].[dbo].[AP_BuscaItem5] '', '', '', '', @CodigoMarca, '', '', '';

DECLARE @TotalArticulos INT;
SELECT @TotalArticulos = COUNT(DISTINCT coditm) FROM #TodosArticulos;
PRINT 'Artículos encontrados: ' + CAST(@TotalArticulos AS VARCHAR(10));

-- Verificar si hay artículos
IF @TotalArticulos = 0
BEGIN
    PRINT 'No se encontraron artículos para la marca ' + @CodigoMarca;
    DROP TABLE #TodosArticulos;
    RETURN;
END

-- Verificar si hay datos en ITEMSACUM para estos artículos (prueba de depuración)
DECLARE @ConStock INT;
SELECT @ConStock = COUNT(DISTINCT ia.CODITM)
FROM ITEMSACUM ia
INNER JOIN #TodosArticulos ta ON ta.coditm = ia.CODITM;
PRINT 'Artículos con datos en ITEMSACUM: ' + CAST(@ConStock AS VARCHAR(10));

-- Consulta que muestra cada artículo con su descripción, sucursal y stock
SELECT 
    ta.coditm AS 'Código Artículo',
    ta.descripcion AS 'Descripción',
    RTRIM(CAST(s.CODDEP AS VARCHAR)) AS 'Código Sucursal',
    s.NOMBRE AS 'Nombre Sucursal',
    SUM(CAST(ia.STKACTUAL AS INT)) AS 'Stock'
FROM ITEMSACUM ia
INNER JOIN sucursales2 s ON s.CODDEP = ia.CODDEP
INNER JOIN #TodosArticulos ta ON ta.coditm = ia.CODITM
WHERE s.CODDEP <> 5 AND s.CODDEP <> 401 AND s.CODDEP <> 402 AND s.CODDEP <> 403
GROUP BY ta.coditm, ta.descripcion, s.CODDEP, s.NOMBRE
HAVING SUM(CAST(ia.STKACTUAL AS INT)) > 0
ORDER BY ta.coditm, s.CODDEP;

-- Limpiar tabla temporal
DROP TABLE #TodosArticulos;
