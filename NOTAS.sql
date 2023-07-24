SELECT DISTINCT empleado.nombre, capacidad.nombreCapacidad
FROM capacidades
INNER JOIN empleado
ON capacidades.idEmpleado = empleado.idEmpleado
INNER JOIN cap_requeridas
ON capacidades.idCapacidad = cap_requeridas.idCapacidad
INNER JOIN capacidad
ON capacidad.idCapacidad = capacidades.idCapacidad
WHERE capacidades.idCapacidad IN (SELECT idCapacidad FROM cap_requeridas WHERE idProyecto = 16)


SELECT DISTINCT capacidades.idEmpleado, cap_requeridas.idCapacidad
FROM capacidades
INNER JOIN cap_requeridas
ON cap_requeridas.idCapacidad = capacidades.idCapacidad
WHERE capacidades.idCapacidad IN (SELECT idCapacidad FROM cap_requeridas WHERE idProyecto = 16)



SELECT capacidades.idEmpleado, GROUP_CONCAT(cap_requeridas.idCapacidad SEPARATOR ', ')
FROM capacidades
INNER JOIN cap_requeridas
ON cap_requeridas.idCapacidad = capacidades.idCapacidad
WHERE capacidades.idCapacidad IN (SELECT idCapacidad FROM cap_requeridas WHERE idProyecto = 16)
GROUP BY capacidades.idEmpleado

-- GOOD ONE ------------------------------------------------------------------>
SELECT DISTINCT empleado.nombre, GROUP_CONCAT(DISTINCT capacidad.nombreCapacidad SEPARATOR ', ') AS capacidades
FROM capacidades
INNER JOIN empleado
ON capacidades.idEmpleado = empleado.idEmpleado
INNER JOIN cap_requeridas
ON capacidades.idCapacidad = cap_requeridas.idCapacidad
INNER JOIN capacidad
ON capacidad.idCapacidad = capacidades.idCapacidad
WHERE capacidades.idCapacidad IN (SELECT idCapacidad FROM cap_requeridas WHERE idProyecto = 16)
GROUP BY empleado.nombre
-- GOOD ONE ------------------------------------------------------------------>

-- BETTER------------------------------------------------------------------>
SELECT DISTINCT empleado.idEmpleado, empleado.nombre, GROUP_CONCAT(DISTINCT capacidad.nombreCapacidad SEPARATOR ', ') AS capacidades
FROM capacidades
INNER JOIN empleado
ON capacidades.idEmpleado = empleado.idEmpleado
INNER JOIN cap_requeridas
ON capacidades.idCapacidad = cap_requeridas.idCapacidad
INNER JOIN capacidad
ON capacidad.idCapacidad = capacidades.idCapacidad
WHERE capacidades.idCapacidad IN (SELECT idCapacidad FROM cap_requeridas WHERE idProyecto = 16)
AND
empleado.idEmpleado NOT IN (SELECT idEmpleado FROM recursos_asignados WHERE idProyecto = 16)
GROUP BY empleado.nombre
-- BETTER------------------------------------------------------------------>


SELECT person_id,
   GROUP_CONCAT(hobbies SEPARATOR ', ')
FROM peoples_hobbies
GROUP BY person_id;



-- QTY para Horas totales de proyecto ---------------------------------------------------------------->
SELECT IFNULL(complejidad.horas,0) AS COMPLX, IFNULL(tipoproyecto.horas,0) AS TIPO, IFNULL(SUM(actividad.horas),0) AS ACT,
  		(IFNULL(complejidad.horas,0) + IFNULL(tipoproyecto.horas,0) + IFNULL(SUM(actividad.horas),0)) AS TOTAL
FROM proyecto
INNER JOIN complejidad
ON proyecto.idComplejidad = complejidad.idComplejidad
INNER JOIN tipoproyecto
ON proyecto.idTipo = tipoproyecto.idTipo
INNER JOIN actividades_proyecto
ON proyecto.idProyecto = actividades_proyecto.idProyecto
INNER JOIN actividad
ON actividades_proyecto.idActividad = actividad.idActividad
WHERE proyecto.idProyecto = 16
-- QTY para Horas totales de proyecto ---------------------------------------------------------------->



-- Totales Proyectos QTY para Horas Contemplando Horas por Actividad ---------------------------------------------------------------->
SELECT proyecto.nombre, IFNULL(complejidad.horas,0) AS COMPLX, IFNULL(tipoproyecto.horas,0) AS TIPO, IFNULL(SUM(actividad.horas),0) AS ACT,
      (IFNULL(complejidad.horas,0) + IFNULL(tipoproyecto.horas,0) + IFNULL(SUM(actividad.horas),0)) AS TOTAL
FROM proyecto
INNER JOIN complejidad
ON proyecto.idComplejidad = complejidad.idComplejidad
INNER JOIN tipoproyecto
ON proyecto.idTipo = tipoproyecto.idTipo
LEFT OUTER JOIN actividades_proyecto
ON proyecto.idProyecto = actividades_proyecto.idProyecto
LEFT OUTER JOIN actividad
ON actividades_proyecto.idActividad = actividad.idActividad
WHERE tipoproyecto.nombre <> 'BUILT TO PRINT'
GROUP BY proyecto.nombre
-- Totales Proyectos QTY para Horas Contemplando Horas por Actividad ---------------------------------------------------------------->


-- Totales Proyectos QTY para Horas contemplando Sobrecarga de Trabajo -------------------------------------------------------->
SELECT proyecto.nombre, IFNULL(complejidad.horas,0) AS COMPLX, IFNULL(tipoproyecto.horas,0) AS TIPO, TRIM(proyecto.Sobrecarga * 100) + 0 AS SOBRE,
      ((IFNULL(complejidad.horas,0) + IFNULL(tipoproyecto.horas,0)) * (1 + proyecto.Sobrecarga) ) AS TOTAL
FROM proyecto
INNER JOIN complejidad
ON proyecto.idComplejidad = complejidad.idComplejidad
INNER JOIN tipoproyecto
ON proyecto.idTipo = tipoproyecto.idTipo
LEFT OUTER JOIN actividades_proyecto
ON proyecto.idProyecto = actividades_proyecto.idProyecto
LEFT OUTER JOIN actividad
ON actividades_proyecto.idActividad = actividad.idActividad
WHERE tipoproyecto.nombre = 'BUILT TO PRINT'
GROUP BY proyecto.nombre
-- Totales Proyectos QTY para Horas contemplando Sobrecarga de Trabajo -------------------------------------------------------->

-- Consulta para obtener los nombres de los recursos asignados a cada actividad del proyecto---------------------------------------->
SELECT actividades_proyecto.idProyecto, actividad.nombre AS anombre, empleado.nombre AS enombre
FROM actividades_proyecto
LEFT JOIN actividad
ON actividades_proyecto.idActividad = actividad.idActividad
LEFT JOIN recursos_asignados
ON actividades_proyecto.idActividades_proyecto = recursos_asignados.idActividades_proyecto
LEFT JOIN empleado
ON recursos_asignados.idEmpleado = empleado.idEmpleado
WHERE idProyecto = 17
-- Consulta para obtener los nombres de los recursos asignados a cada actividad del proyecto---------------------------------------->




-- Consulta para obtener los nombres de los recursos asignados a cada actividad del proyecto Asignadas a un responsable---------------->
SELECT proyecto.idProyecto, proyecto.nombre, actividad.nombre AS Actividad, empleado.nombre AS enombre
FROM proyecto
LEFT JOIN actividades_proyecto
ON proyecto.idProyecto = actividades_proyecto.idProyecto
LEFT JOIN actividad
ON actividades_proyecto.idActividad = actividad.idActividad
LEFT JOIN recursos_asignados
ON actividades_proyecto.idActividades_proyecto = recursos_asignados.idActividades_proyecto
LEFT JOIN empleado
ON recursos_asignados.idEmpleado = empleado.idEmpleado
WHERE proyecto.idRespDiseno = 3 OR proyecto.idRespManu = 3
-- Consulta para obtener los nombres de los recursos asignados a cada actividad del proyecto Asignadas a un responsable---------------->


select player,
  stuff((SELECT distinct ', ' + cast(score as varchar(10))
           FROM yourtable t2
           where t2.player = t1.player
           FOR XML PATH('')),1,1,'')
from yourtable t1
group by player

-- UNIQUE Doble campo Constraints ------------------------------------------------->
ALTER TABLE `privilegios` ADD UNIQUE `UQ_USRID_PERMID` (`idUsuario`, `idPermiso`);
ALTER TABLE `cap_requeridas` ADD UNIQUE `UQ_PROID_CAPID` (`idProyecto`, `idCapacidad`);
ALTER TABLE `recursos_asignados` ADD UNIQUE `UQ_ACTSID_EMPID` (`idActividades_proyecto`, `idEmpleado`);
ALTER TABLE `capacidades` ADD UNIQUE `UQ_EMPID_CAPID` (`idEmpleado`, `idCapacidad`);
ALTER TABLE `privilegios` ADD UNIQUE `UQ_USRID_PERMID` (`idUsuario`, `idPermiso`);

-- UNIQUE Doble campo Constraints ------------------------------------------------->


-- Consulta obtener Workload Hours de cotizaciones --------------->
SELECT idTipoCotizacion, categoria, descripcion, complejidad.nombre, cotizacion_volumen.nombre, tipocotizacion.horas
FROM tipocotizacion
INNER JOIN cotizacion_categoria
ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
INNER JOIN complejidad
ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad
INNER JOIN cotizacion_volumen
ON tipocotizacion.idCotizacionVolumen = cotizacion_volumen.idCotizacionVolumen
-- Consulta obtener Workload Hours de cotizaciones --------------->


SELECT idActividades_proyecto, proyecto.nombre AS pnombre, actividad.nombre AS anombre
FROM actividades_proyecto
INNER JOIN proyecto
ON actividades_proyecto.idProyecto = proyecto.idProyecto
INNER JOIN actividad
ON actividades_proyecto.idActividad = actividad.idActividad
WHERE idActividades_proyecto = 5


SELECT
   SS.SEC_NAME,
   (SELECT '; ' + US.USR_NAME
    FROM USRS US
    WHERE US.SEC_ID = SS.SEC_ID
    FOR XML PATH('')) [SECTORS/USERS]
FROM SALES_SECTORS SS
GROUP BY SS.SEC_ID, SS.SEC_NAME
ORDER BY 1




-- QUOTE NOTES-------------------------------------------->
SELECT idCotizacion, quoteID, cotizacion.nombre, cotizacion.descripcion, cotizacion_categoria.categoria, cotizacion_categoria.descripcion AS quoteDesc,
        complejidad.nombre AS complex, cotizacion_volumen.nombre AS volumen, tipocotizacion.horas AS hoursquote, cliente_contacto.nombre AS custcontact,
        uniqueFG, lineItems, overallComplet, status.nombre AS stat, notas, fechaInicio, fechaLanzamiento, fechaReqCliente, consolidatedEAU,
        awarded, cliente.nombreCliente,
        (SELECT cotizacion_categoria.categoria
            FROM tipocotizacion
            INNER JOIN cotizacion_categoria
            ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
            WHERE idTipoCotizacion = cotizacion.BOMType) AS BOMQuote,
        (SELECT cotizacion_categoria.descripcion
            FROM tipocotizacion
            INNER JOIN cotizacion_categoria
            ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
            WHERE idTipoCotizacion = cotizacion.BOMType) AS BOMDescripcion,
        (SELECT tipocotizacion.horas
            FROM tipocotizacion
            WHERE idTipoCotizacion = cotizacion.BOMType) AS BOMHours,
        (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idResponsable) AS respCotizacion,
        (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idRepreVentas) AS repVentas
FROM cotizacion
INNER JOIN cliente
ON cotizacion.idCliente = cliente.idCliente
INNER JOIN tipocotizacion
ON cotizacion.idTipoCotizacion = tipocotizacion.idTipoCotizacion
INNER JOIN cotizacion_volumen
ON tipocotizacion.idCotizacionVolumen = cotizacion_volumen.idCotizacionVolumen
INNER JOIN cotizacion_categoria
ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
INNER JOIN complejidad
ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad
INNER JOIN cliente_contacto
ON cliente_contacto.idClienteContacto = cotizacion.idClienteContacto
INNER JOIN status
ON cotizacion.idStatus = status.idStatus
WHERE idCotizacion = $id



-- SELECTOR DATOS PROYECTO Gantt
SELECT projectID, proyecto.nombre,
YEAR(fechaInicio) AS IY, MONTH(fechaInicio) AS IM, DAY(fechaInicio) AS ID,
YEAR(DATE_ADD(fechaInicio, INTERVAL ROUND(tipoproyecto.horas/24) DAY)) AS TY,
MONTH(DATE_ADD(fechaInicio, INTERVAL ROUND(tipoproyecto.horas/24) DAY)) AS TM,
DAY(DATE_ADD(fechaInicio, INTERVAL ROUND(tipoproyecto.horas/24) DAY)) AS TD,
overallComplet
FROM proyecto
INNER JOIN tipoproyecto
ON proyecto.idTipoproyecto = tipoproyecto.idTipoProyecto
WHERE fechaInicio is not null


------------------------- HORAS SEMANALAES ASIGNADAS A recursos ------------------------------------------>
SELECT CONVERT(fechaInicio, DATE) AS fecha, empleado.nombre AS empleado, SUM(horas) AS 'Hrs'
FROM recursos_asignados
INNER JOIN empleado
ON recursos_asignados.idEmpleado = empleado.idEmpleado
WHERE  YEARWEEK(fechaInicio, 1) = YEARWEEK(CURDATE(), 1)
GROUP BY fechaInicio, empleado.nombre
ORDER BY fechaInicio
------------------------- HORAS SEMANALAES ASIGNADAS A recursos ------------------------------------------>


------------------------- HORAS SEMANALAES ASIGNADAS A recursos por Mes Actual ------------------------------>
SELECT CONVERT(fechaInicio, DATE) AS fecha, empleado.nombre AS empleado, SUM(horas) AS 'Hrs'
                                FROM recursos_asignados
                                INNER JOIN empleado
                                ON recursos_asignados.idEmpleado = empleado.idEmpleado
                                WHERE  MONTH(fechaInicio) = MONTH(CURDATE())
                                GROUP BY fechaInicio, empleado.nombre
                                ORDER BY fechaInicio
------------------------- HORAS SEMANALAES ASIGNADAS A recursos por Mes Actual ------------------------------>

------------------------- HORAS SEMANALAES TRABAJADAS POR EMPLEADO ------------------------------------------>
SELECT WEEK(fechaInicio) AS week, empleado.nombre AS empleado, SUM(horas) AS 'Hrs'
                                  FROM recursos_asignados
                                  INNER JOIN empleado
                                  ON recursos_asignados.idEmpleado = empleado.idEmpleado
                                  WHERE WEEK(fechaInicio) IS NOT NULL
                            	    GROUP BY WEEK(fechaInicio), empleado.nombre
                                  ORDER BY fechaInicio
------------------------- HORAS SEMANALAES TRABAJADAS POR EMPLEADO ------------------------------------------>


------------------------- CONSULTA PARA OBTENER LAS NOTAS DE 1 PROYECTO ------------------------------------------>
SELECT idProyectoNota AS id, nota, empleado.nombre, DATE(proyecto_notas.fechaCrea) AS fecha
                                  FROM proyecto_notas
                                  INNER JOIN usuario
                                  ON proyecto_notas.idUsuario = usuario.idUsuario
                                  INNER JOIN empleado
                                  ON usuario.idEmpleado = empleado.idEmpleado
                                  WHERE idProyecto = %id
                                  ORDER BY proyecto_notas.fechaCrea DESC
------------------------- CONSULTA PARA OBTENER LAS NOTAS DE 1 PROYECTO ------------------------------------------>




SELECT idProduct, SUM(qty) AS qty
FROM
(
 SELECT p.idProduct, sd.qty
 FROM sales_detail AS sd
 INNER JOIN products AS p
 ON sd.idProduct = p.idProduct
WHERE sd.idSale = 16 AND p.assembly <> 1
UNION ALL
SELECT p.idProduct, sd.qty * ad.qty AS qty
 FROM assy_detail AS ad
 INNER JOIN products AS p
 ON ad.rawMaterial =  p.idProduct
 INNER JOIN sales_detail AS sd
 ON ad.idProduct = sd.idProduct
 WHERE sd.idSale = 16
) AS vt
GROUP BY idProduct



-- ENSAMBLES contenidos en proyectos
SELECT * FROM `ensambles`
INNER JOIN proyecto
ON ensambles.idProyecto = proyecto.idProyecto
WHERE YEAR(proyecto.fechaInicio) BETWEEN '2021' AND '2022'


SELECT * FROM `proyecto`, proyecto.fechaInicio AS NEW
INNER JOIN tipoproyecto
ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
WHERE YEAR(proyecto.fechaInicio) BETWEEN '2021' AND '2022'
