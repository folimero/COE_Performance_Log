SELECT
    DATE(proyecto.fechaInicio) AS fechaInicio,
    YEAR(proyecto.fechaInicio) AS anio,
    WEEK(proyecto.fechaInicio) AS week,
    proyecto.projectID,
    proyecto.nombre,
    proyecto.descripcion,
    proyecto.isApplication,
    DATE(actividades_proyecto.fechaEntrega) AS apFechaEntrega,
    WEEK(actividades_proyecto.fechaEntrega) AS apWeek,
    YEAR(actividades_proyecto.fechaEntrega) AS apYear,
    (
        ROUND(
            (
                tipoproyecto.horas + IFNULL(proyecto.sobreCarga, 0)
            ) / IF(
                (
                    SELECT
                        COUNT(*)
                    FROM
                        actividades_proyecto AS ap
                    WHERE
                        ap.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                ) = 0,
                1,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        actividades_proyecto AS ap
                    WHERE
                        ap.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                )
            ),
            2
        )
    ) AS apHrs,
    DATE(ara.fechaEntrega) AS araFechaEntrega,
    WEEK(ara.fechaEntrega) AS araWeek,
    YEAR(ara.fechaEntrega) AS araYear,
    (
        ROUND(
            (
                tipoproyecto.horas + IFNULL(proyecto.sobreCarga, 0)
            ) / IF(
                (
                    SELECT
                        COUNT(*)
                    FROM
                        actividad_recursos_adicionales araIN
                        INNER JOIN actividades_proyecto AS apIN ON araIN.idActividades_proyecto = apIN.idActividades_proyecto
                    WHERE
                        apIN.idProyecto = proyecto.idProyecto
                ) = 0,
                1,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        actividad_recursos_adicionales araIN
                        INNER JOIN actividades_proyecto AS apIN ON araIN.idActividades_proyecto = apIN.idActividades_proyecto
                    WHERE
                        apIN.idProyecto = proyecto.idProyecto
                )
            ),
            2
        )
    ) AS araHrs,
    empleado.nombre AS eNombre
FROM
    proyecto
    INNER JOIN tipoproyecto ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
    INNER JOIN actividades_proyecto ON proyecto.idProyecto = actividades_proyecto.idProyecto
    LEFT JOIN actividad_recursos_adicionales AS ara ON actividades_proyecto.idActividades_proyecto = ara.idActividades_proyecto
    INNER JOIN usuario ON ara.idUsuario = usuario.idUsuario
    INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
WHERE
    actividades_proyecto.fechaEntrega IS NOT NULL
    OR ara.fechaEntrega IS NOT NULL
UNION ALL
SELECT
    DATE(proyecto.fechaInicio) AS fechaInicio,
    YEAR(proyecto.fechaInicio) AS anio,
    WEEK(proyecto.fechaInicio) AS week,
    proyecto.projectID,
    proyecto.nombre,
    proyecto.descripcion,
    proyecto.isApplication,
    DATE(proyecto_soporte_adicional.fechaSoporte) AS apFechaEntrega,
    WEEK(proyecto_soporte_adicional.fechaSoporte) AS apWeek,
    YEAR(proyecto_soporte_adicional.fechaSoporte) AS apYear,
    ROUND(proyecto_soporte_adicional.horas, 2) AS apHrs,
    DATE(proyecto_soporte_adicional.fechaSoporte) AS araFechaEntrega,
    WEEK(proyecto_soporte_adicional.fechaSoporte) AS araWeek,
    YEAR(proyecto_soporte_adicional.fechaSoporte) AS araYear,
    ROUND(proyecto_soporte_adicional.horas, 2) AS araHrs,
    empleado.nombre AS eNombre
FROM
    proyecto_soporte_adicional
    INNER JOIN proyecto ON proyecto_soporte_adicional.idProyecto = proyecto.idProyecto
    INNER JOIN usuario ON proyecto_soporte_adicional.idUsuario = usuario.idUsuario
    INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
ORDER BY
    anio DESC,
    week DESC