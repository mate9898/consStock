-- =============================================
-- AP_BuscaItem7: misma lógica que AP_BuscaItem5 pero acepta
-- listas separadas por coma en depo (sucursales), at4 (marcas),
-- at1, at8 y talle. Una sola ejecución = búsqueda rápida.
-- Ejecutar este script en la base FAM450 (SQL Server 2016+ por STRING_SPLIT).
-- =============================================
USE [FAM450]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE OR ALTER PROCEDURE [dbo].[AP_BuscaItem7]
	@ITEM varchar(18),
	@depo varchar(1000),   -- sucursales: '' = todas, o '1,2,3'
	@at1 varchar(1000),   -- '' o lista separada por coma
	@at3 varchar(3),
	@at4 varchar(1000),    -- marcas: '' = todas, o '174,175,176'
	@at7 varchar(3),
	@at8 varchar(1000),   -- '' o lista separada por coma
	@talle varchar(1000) -- '' o lista separada por coma
AS
BEGIN
	SET NOCOUNT ON;

	CREATE TABLE #tempo(coditm varchar(20), descripcion varchar(100), foto char(1), Precio decimal(18,2), tipoventa varchar(10));

	DECLARE @str varchar(5000);
	-- Escapar comillas simples en listas para el SQL dinámico
	DECLARE @depoSafe varchar(2000) = REPLACE(ISNULL(@depo,''), '''', '''''');
	DECLARE @at1Safe  varchar(2000) = REPLACE(ISNULL(@at1,''), '''', '''''');
	DECLARE @at4Safe  varchar(2000) = REPLACE(ISNULL(@at4,''), '''', '''''');
	DECLARE @at8Safe  varchar(2000) = REPLACE(ISNULL(@at8,''), '''', '''''');
	DECLARE @talleSafe varchar(2000) = REPLACE(ISNULL(@talle,''), '''', '''''');

	SET @str = 'INSERT #tempo(coditm,descripcion,foto,Precio,tipoventa)
		SELECT coditm,descripcion,Foto,Precio,Tipoventa 
		FROM (
		SELECT i.CODITM AS coditm,i.DESCRIPCION AS descripcion,iw.Foto,ia.CODTAL,SUM(stkactual) AS stock,CAST(l.Precio AS int) AS Precio,av7.descripcion AS Tipoventa 
		FROM
		ITEMS i INNER JOIN ITEMSMEDCOL imc ON i.CODITM=imc.CODITM INNER JOIN ITEMSWEB iw ON i.CODITM=iw.CODITM
		INNER JOIN ITEMSACUM ia ON i.CODITM=ia.CODITM ';
	IF @at1 <> '' SET @str = @str + ' INNER JOIN ITEMSATRIB AS at1 ON i.CODITM=at1.CODITM AND at1.CODATR=''001'' ';
	IF @at3 <> '' SET @str = @str + ' INNER JOIN ITEMSATRIB AS at3 ON i.CODITM=at3.CODITM AND at3.CODATR=''003'' ';
	IF @at4 <> '' SET @str = @str + ' INNER JOIN ITEMSATRIB AS at4 ON i.CODITM=at4.CODITM AND at4.CODATR=''004'' ';
	SET @str = @str + ' INNER JOIN ITEMSATRIB AS at7 ON i.CODITM=at7.CODITM AND at7.CODATR=''007'' 
		  INNER JOIN atributosval av7 ON at7.CODATR=av7.CODATR AND at7.codatrval=av7.codatrval 
		  INNER JOIN ITEMSATRIB AS at2 ON i.CODITM=at2.CODITM AND at2.CODATR=''002'' 
		  INNER JOIN ATRIBUTOSVAL AS av2 ON at2.CODATR=av2.CODATR AND at2.CODATRVAL=av2.CODATRVAL 
		  INNER JOIN sucursales2 s ON ia.coddep=s.coddep 
		  INNER JOIN listasprecios L ON i.coditm=L.CODITM AND L.codemp=1 AND l.vigencia=CONVERT(varchar(6),''01-01-'')+CONVERT(varchar(4), YEAR(GETDATE())) AND l.codlis=s.codlis1 AND L.codlis=''L01''';
	IF @at8 <> '' SET @str = @str + ' INNER JOIN ITEMSATRIB AS at8 ON i.CODITM=at8.CODITM AND at8.CODATR=''008'' ';
	SET @str = @str + ' WHERE (i.ITEMPREFI=''B'' AND imc.CODITMALTERNATIVO IS NOT NULL AND ia.CODDEP <> 5 AND ia.CODDEP <> 402 AND ia.CODDEP <> 92) 
		 AND (i.CODITM LIKE ''%' + REPLACE(@ITEM, '''', '''''') + '%'' OR imc.CODITMALTERNATIVO LIKE ''%' + REPLACE(@ITEM, '''', '''''') + '%'' OR av2.descripcion LIKE ''%' + REPLACE(@ITEM, '''', '''''') + '%'' OR i.DESCRIPCION LIKE ''%' + REPLACE(@ITEM, '''', '''''') + '%'') ';

	IF @depo <> '' SET @str = @str + ' AND ia.CODDEP IN (SELECT LTRIM(RTRIM(value)) FROM STRING_SPLIT(''' + @depoSafe + ''', '','')) ';
	IF @at1 <> '' SET @str = @str + ' AND at1.codatrval IN (SELECT LTRIM(RTRIM(value)) FROM STRING_SPLIT(''' + @at1Safe + ''', '','')) ';
	IF @at3 <> '' SET @str = @str + ' AND at3.codatrval = ''' + REPLACE(@at3, '''', '''''') + ''' ';
	IF @at4 <> '' SET @str = @str + ' AND at4.codatrval IN (SELECT LTRIM(RTRIM(value)) FROM STRING_SPLIT(''' + @at4Safe + ''', '','')) ';
	IF @at7 <> '' SET @str = @str + ' AND at7.codatrval = ''' + REPLACE(@at7, '''', '''''') + ''' ';
	IF @at8 <> '' SET @str = @str + ' AND at8.codatrval IN (SELECT LTRIM(RTRIM(value)) FROM STRING_SPLIT(''' + @at8Safe + ''', '','')) ';

	SET @str = @str + ' GROUP BY i.CODITM,i.DESCRIPCION,iw.Foto,ia.CODTAL,av7.descripcion,l.precio
		HAVING SUM(stkactual)>0
		) AS i
		GROUP BY coditm,descripcion,Foto,Precio,Tipoventa';

	EXEC (@str);

	-- Segundo bloque: Talles (también con listas para depo y talle)
	SET @str = 'SELECT 
    t.coditm,
    t.descripcion,
    t.Foto,
    CAST(t.Precio AS int) AS Precio,
    t.Tipoventa,
    STRING_AGG(CONVERT(varchar(10), t.Equivalencia), '' - '') 
        WITHIN GROUP (ORDER BY t.Equivalencia) AS Talles
FROM 
	(SELECT t.coditm,t.descripcion,t.Foto,t.Precio,t.Tipoventa,vt.Equivalencia
	FROM #tempo t 
	INNER JOIN ITEMSACUM ia ON t.coditm=ia.coditm AND ia.STKACTUAL>0 ';
	IF @depo <> '' SET @str = @str + ' AND ia.CODDEP IN (SELECT LTRIM(RTRIM(value)) FROM STRING_SPLIT(''' + @depoSafe + ''', '','')) ';
	SET @str = @str + ' INNER JOIN VistaTalles vt ON ia.CODITM=vt.CODITM AND ia.CODCOL=vt.CODCOL AND ia.codtal=vt.codtal ';
	IF @talle <> '' SET @str = @str + ' WHERE vt.Equivalencia IN (SELECT LTRIM(RTRIM(value)) FROM STRING_SPLIT(''' + @talleSafe + ''', '','')) ';
	SET @str = @str + ' GROUP BY t.coditm,t.descripcion,t.Foto,t.Precio,t.Tipoventa,ia.codtal,vt.Equivalencia) AS t
GROUP BY 
    t.coditm, 
    t.descripcion, 
    t.Foto, 
    t.Precio, 
    t.Tipoventa';

	EXEC (@str);

	DROP TABLE #tempo;
END
GO
