<?php
  include "inc/conexion.php";
  include "inc/headerBoostrap.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(27, $_SESSION["permisos"])) {
          $message = "Unauthorized User.";
          echo "<script>
                alert('$message');
                window.location.href='index.php';
            </script>";
          die();
      }
  } else {
      $message = "Please Log in.";
      echo "<script>
            alert('$message');
            window.location.href='login.php';
        </script>";
      die();
  }
  ?>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<?php
  // Funcionamiento para llenar valores de grafica proyectos por cliente
  $stmt = $dbh->prepare("SELECT cliente.nombreCliente, COUNT(idProyecto) AS QTY
                        FROM proyecto
                        INNER JOIN cliente
                        ON proyecto.idCliente = cliente.idCliente
                        GROUP BY nombreCliente");
  $stmt->execute();
  $data = "";
  while ($resultado = $stmt->fetch()) {
      $data .= "['" . $resultado->nombreCliente . "'," . $resultado->QTY ."],";
  }
  // Funcionamiento para llenar valores de grafica cotizaciones por cliente
  $stmtQuote = $dbh->prepare("SELECT cliente.nombreCliente, COUNT(idCotizacion) AS QTY
                              FROM cotizacion
                              INNER JOIN cliente
                              ON cotizacion.idCliente = cliente.idCliente
                              GROUP BY nombreCliente");
  $stmtQuote->execute();
  $dataQuote = "";
  while ($resultado = $stmtQuote->fetch()) {
      $dataQuote .= "['" . $resultado->nombreCliente . "'," . $resultado->QTY ."],";
  }
  // Funcionamiento para llenar valores de grafica capacidad instalada
  $stmt2 = $dbh->prepare("SELECT empleado.nombre, GROUP_CONCAT(capacidad.nombreCapacidad SEPARATOR '> <') AS cap
                          FROM capacidades
                          INNER JOIN empleado
                          ON capacidades.idEmpleado = empleado.idEmpleado
                          INNER JOIN capacidad
                          ON capacidades.idCapacidad = capacidad.idCapacidad
                          GROUP BY nombre");
  $stmt2->execute();
  $data2 = "";
  while ($resultado = $stmt2->fetch()) {
      $data2 .= "['" . $resultado->nombre . "','" . $resultado->cap ."'],";
  }
  $stmt3 = $dbh->prepare("SELECT proyecto.nombre, complejidad.nombre AS COMPLX, proyecto_categoria.categoria AS TIPO, TRIM(proyecto.Sobrecarga * 100) + 0 AS SOBRE,
                                  TRUNCATE((tipoproyecto.horas + (IFNULL(proyecto.sobreCarga,0) * tipoproyecto.horas)),2) AS TOTAL
                          FROM proyecto
                          INNER JOIN tipoproyecto
                          ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                          INNER JOIN proyecto_categoria
                          ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                          INNER JOIN complejidad
                          ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                          LEFT OUTER JOIN actividades_proyecto
                          ON proyecto.idProyecto = actividades_proyecto.idProyecto
                          LEFT OUTER JOIN actividad
                          ON actividades_proyecto.idActividad = actividad.idActividad
                          GROUP BY proyecto.nombre");
  $stmt3->execute();
  $data3 = "";
  while ($resultado = $stmt3->fetch()) {
      $data3 .= "['" . str_replace("'","\\'",$resultado->nombre) .
                "','" . str_replace("'","\\'",$resultado->TIPO)  .
                "','" . str_replace("'","\\'",$resultado->COMPLX) .
                "','" .  str_replace("'","\\'",$resultado->SOBRE) .
                " %'," .  $resultado->TOTAL . "],";
  }
  $stmt4 = $dbh->prepare("SELECT cotizacion.nombre, CONCAT(cotizacion_categoria.categoria, ' - ', complejidad.nombre) AS TIPO, cotizacion_volumen.nombre AS VOLUMEN,
                              		(SELECT horas FROM tipocotizacion WHERE idTipoCotizacion = cotizacion.idTipoCotizacion) AS QuoteHrs,
                                  (SELECT horas FROM tipocotizacion WHERE idTipoCotizacion = cotizacion.BOMType) AS BOMHours,
                                  IFNULL((SELECT horas FROM tipocotizacion WHERE idTipoCotizacion = cotizacion.idTipoCotizacion),0) +
                                  IFNULL((SELECT horas FROM tipocotizacion WHERE idTipoCotizacion = cotizacion.BOMType),0) AS total
                          FROM cotizacion
                          INNER JOIN tipocotizacion
                          ON cotizacion.idTipoCotizacion = tipocotizacion.idTipoCotizacion
                          INNER JOIN cotizacion_volumen
                          ON tipocotizacion.idCotizacionVolumen = cotizacion_volumen.idCotizacionVolumen
                          INNER JOIN cotizacion_categoria
                          ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
                          INNER JOIN complejidad
                          ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad
                          GROUP BY cotizacion.nombre");
  $stmt4->execute();
  $data4 = "";
  while ($resultado = $stmt4->fetch()) {
      $data4 .= "['" . str_replace("'","\\'",$resultado->nombre) .
                "','" . str_replace("'","\\'",$resultado->TIPO)  .
                "','" . str_replace("'","\\'",$resultado->VOLUMEN) .
                "','" . str_replace("'","\\'",$resultado->QuoteHrs) .
                "','" . str_replace("'","\\'",$resultado->BOMHours) .
                "','" . str_replace("'","\\'",$resultado->total) . "'],";
  }
  $stmtGantt = $dbh->prepare("SELECT projectID, proyecto.nombre,
                                      YEAR(proyecto.fechaInicio) AS IY, MONTH(proyecto.fechaInicio) AS IM, DAY(proyecto.fechaInicio) AS ID,
                                      YEAR(DATE_ADD(proyecto.fechaInicio, INTERVAL ROUND(tipoproyecto.horas/24) DAY)) AS TY,
                                      MONTH(DATE_ADD(proyecto.fechaInicio, INTERVAL ROUND(tipoproyecto.horas/24) DAY)) AS TM,
                                      DAY(DATE_ADD(proyecto.fechaInicio, INTERVAL ROUND(tipoproyecto.horas/24) DAY)) AS TD,
                                      overallComplet, (IFNULL(SUM(recursos_asignados.horas),0) / tipoproyecto.horas) AS ASIGNADO
                              FROM proyecto
                              INNER JOIN tipoproyecto
                              ON proyecto.idTipoproyecto = tipoproyecto.idTipoProyecto
                              LEFT JOIN actividades_proyecto
                              ON proyecto.idProyecto = actividades_proyecto.idProyecto
                              LEFT JOIN recursos_asignados
                              ON actividades_proyecto.idActividades_proyecto = recursos_asignados.idActividades_proyecto
                              WHERE proyecto.fechaInicio IS NOT NULL AND proyecto.idStatus <> 5 AND proyecto.idStatus <> 6 AND proyecto.idStatus <> 7 AND MONTH(proyecto.fechaInicio) > MONTH(NOW()) - 4
                              GROUP BY proyecto.idProyecto");
  $stmtGantt->execute();
  $dataGantt = "";
  while ($r = $stmtGantt->fetch()) {
      $dataGantt .= "['" . $r->projectID . "','" . str_replace("'","",$r->nombre)  . "',new Date(" . $r->IY . "," . ($r->IM - 1) . "," .  $r->ID . ")," .
      "new Date(" . $r->TY . "," . ($r->TM - 1) . ",";
       if($r->TD == $r->ID){
         $dataGantt .= $r->TD + 1;
       }else {
         $dataGantt .= $r->TD;
       }
       $dataGantt .= "),null," . $r->ASIGNADO * 100 . ",null],";
  }
  // var_dump($dataGantt);
  // exit;
  // Obtener Recursos
  $stmtRecursos = $dbh->prepare("SELECT CONVERT(fechaInicio, DATE) AS fecha, empleado.nombre AS empleado, SUM(horas) AS 'Hrs'
                                  FROM recursos_asignados
                                  INNER JOIN empleado
                                  ON recursos_asignados.idEmpleado = empleado.idEmpleado
                                  WHERE  WEEK(fechaInicio) = WEEK(now()) AND YEAR(fechaInicio) = YEAR(now())
                                  GROUP BY CONVERT(fechaInicio, DATE), empleado.nombre
                                  ORDER BY fechaInicio");
  $stmtRecursos->execute();
  $dataRecursos = "['Day',";

  $dias = array();
  $nombres = array();

  $res = $stmtRecursos->fetchAll();

  $stmtWorkload = $dbh->prepare("SELECT projectID, proyecto.nombre,
                                        DATE(fechaInicio) AS fechaInicio,
                                        DATE(DATE_ADD(fechaInicio, INTERVAL ROUND(tipoproyecto.horas/24) DAY)) AS fechaTermino,
                                        DATEDIFF(DATE_ADD(fechaInicio, INTERVAL ROUND(tipoproyecto.horas/24) DAY), fechaInicio) AS diference,
                                        tipoproyecto.horas
                                FROM proyecto
                                INNER JOIN tipoproyecto
                                ON proyecto.idTipoproyecto = tipoproyecto.idTipoProyecto
                                WHERE fechaInicio IS NOT NULL
                                ORDER BY fechaInicio");
  $stmtWorkload->execute();
  $dataWorkload = "";

  $proyectoFecha = array();

  $resWorkload = $stmtWorkload->fetchAll();

  // Obtiene columnas y Filas
  for ($i=0; $i  < count($res); $i++) {
      // Columnas
      if (!in_array($res[$i]->empleado, $nombres)) {
          array_push($nombres, $res[$i]->empleado);
          $dataRecursos .= "'" . $res[$i]->empleado . "',";
      }
      // Filas
      if (!in_array($res[$i]->fecha, $dias)) {
          array_push($dias, $res[$i]->fecha);
      }
  }

  $totalHoras = 0;
  $dataRecursos .= "'Req Hours'],";

  for ($j=0; $j < count($dias); $j++) {
      $dataRecursos .= "['" . $dias[$j] . "',";
      $avrg=0;
      $workHrs=0;
      $counter2 = 0;

      for ($k=0; $k < count($nombres); $k++) {
          $counter = 0;

          for ($i=0; $i < count($res); $i++) {
              if ($res[$i]->fecha == $dias[$j]) {
                  if ($res[$i]->empleado == $nombres[$k]) {
                      $dataRecursos .= $res[$i]->Hrs . ",";
                      $counter = 1;
                      $totalHoras += $res[$i]->Hrs;
                  }
              }
          }
          if ($counter == 0) {
              $dataRecursos .= "0,";
          } else {
              $counter2 += 1;
          }
      }

      // Agrega Proyectos Workload
      for ($n=0; $n < count($resWorkload); $n++) {
          $myDate = new DateTime($resWorkload[$n]->fechaInicio);
          for ($m=0; $m < $resWorkload[$n]->diference; $m++) {
              if ($myDate->format('Y-m-d') == $dias[$j]) {
                  $workHrs += ($resWorkload[$n]->horas / ($resWorkload[$n]->diference));
              }
              $myDate->add(new DateInterval('P' . 1 . 'D'));
          }
      }
      if ($j == count($dias) - 1) {
          $dataRecursos .=  $workHrs . "]";
      } else {
          $dataRecursos .=  $workHrs . "],";
      }
  }

  // Obtencion de datos para chart de horas de empleados trabajadas por Semana DISENO -------------------------------------------------------------------->>
  // 1 - COE - Diseño
  // 2 - COE - Manufactura
  // 3 - COE - Cotizaciones
  // 4 - COE - Rep Ventas
  // 5 - COE - Administracion
  // 6 - COE - Quality
  // 7 - COE - Technician
  // 8 - COE - Documentacion

  $dataResourcesDesign = weeklyWorkedHours($dbh, 1);
  $dataResourcesManufacturing = weeklyWorkedHours($dbh, 2);
  $dataResourcesTechnician = weeklyWorkedHours($dbh, 7);
  $dataResourcesDocumentation = weeklyWorkedHours($dbh, 8);
  $dataResourcesAdministration = weeklyWorkedHours($dbh, 5);
  $dataResourcesQuality = weeklyWorkedHours($dbh, 6);
  $dataResourcesQuoting = weeklyWorkedHours($dbh, 3);
  // APPLICATION DATA
  $dataResourcesApplication = weeklyWorkedHoursApplication($dbh, 9);
  $dataResourcesApplicationDesign = weeklyWorkedHoursApplication($dbh, 10);
  $dataResourcesApplicationPerDepartment = weeklyWorkedHoursApplicationPerDepartment($dbh, 9);
  // echo var_dump($dataResourcesApplication);
  // var_dump($dataResourcesDesign);
  // exit;
  function weeklyWorkedHours($con, $idDepartamento){
      $result = array();

      $stmtWorkedHours = $con->prepare("SELECT week, empleado, Hrs
                                              FROM (
                                                  (
                                                      SELECT WEEK(fechaInicio) AS week, empleado.nombre AS empleado, SUM(horas) AS 'Hrs'
                                                      FROM recursos_asignados
                                                      INNER JOIN empleado
                                                      ON recursos_asignados.idEmpleado = empleado.idEmpleado
                                                      WHERE WEEK(fechaInicio) IS NOT NULL AND WEEK(fechaInicio) BETWEEN WEEK(now()) - 3 AND WEEK(now()) AND empleado.idDepartamento = $idDepartamento AND empleado.activo = 1
                                                      GROUP BY week, empleado
                                                  ) UNION ALL (
                                                      SELECT WEEK(now()) AS week, nombre, 0 AS 'Hrs' FROM empleado WHERE idDepartamento = $idDepartamento AND activo = 1
                                                  )
                                              ) AS tmp
                                              GROUP BY week, empleado");
      $stmtWorkedHours->execute();

      $dataResources = "['Week',";
      $weeks = array();
      $names = array();

      $resWorkedHoursDesign = $stmtWorkedHours->fetchAll();
      // Obtiene columnas y Filas
      for ($i=0; $i  < count($resWorkedHoursDesign); $i++) {
          // Columnas
          if (!in_array($resWorkedHoursDesign[$i]->empleado, $names)) {
              array_push($names, $resWorkedHoursDesign[$i]->empleado);
              $dataResources .= "'" . $resWorkedHoursDesign[$i]->empleado . "',";
          }
          // Filas
          if (!in_array($resWorkedHoursDesign[$i]->week, $weeks)) {
              array_push($weeks, $resWorkedHoursDesign[$i]->week);
          }
      }
      $totalHorasDesign = 0;
      $dataResources .= "'Standard'],";

      for ($j=0; $j < count($weeks); $j++) {
          $dataResources .= "['" . $weeks[$j] . "',";
          $counterDesign = 0;

          for ($k=0; $k < count($names); $k++) {
              $counter = 0;

              for ($i=0; $i < count($resWorkedHoursDesign); $i++) {
                  if ($resWorkedHoursDesign[$i]->week == $weeks[$j]) {
                      if ($resWorkedHoursDesign[$i]->empleado == $names[$k]) {
                          $dataResources .= $resWorkedHoursDesign[$i]->Hrs . ",";
                          $counter = 1;
                          $totalHorasDesign += $resWorkedHoursDesign[$i]->Hrs;
                      }
                  }
              }
              if ($counter == 0) {
                  $dataResources .= "0,";
              } else {
                  $counterDesign += 1;
              }
          }
          if ($j == count($weeks) - 1) {
              $dataResources .=  "45]";
          } else {
              $dataResources .=  "45],";
          }
      }
      $result["data"] = $dataResources;
      $result["names"] = $names;
      $result["weeks"] = $weeks;
      return $result;
  }

  function weeklyWorkedHoursApplication($con, $idDepartamento){
      $result = array();

      $stmtWorkedHours = $con->prepare("SELECT
                                            week,
                                            empleado,
                                            Hrs
                                        FROM
                                            (
                                                (
                                                    SELECT
                                                        WEEK(actividad_recursos_adicionales.fechaInicio) AS week,
                                                        empleado.nombre AS empleado,
                                                        (
                                                            SUM(
                                                                (tipoproyecto.horas + IFNULL(proyecto.sobrecarga,0)) / (
                                                                    SELECT
                                                                        COUNT(*)
                                                                    FROM
                                                                        actividad_recursos_adicionales
                                                                        INNER JOIN actividades_proyecto ON actividad_recursos_adicionales.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                                                                    WHERE
                                                                        actividades_proyecto.idProyecto = ap.idProyecto
                                                                )
                                                            )
                                                        ) AS 'Hrs'
                                                    FROM
                                                        actividad_recursos_adicionales
                                                        INNER JOIN actividades_proyecto AS ap ON actividad_recursos_adicionales.idActividades_proyecto = ap.idActividades_proyecto
                                                        INNER JOIN proyecto ON ap.idProyecto = proyecto.idProyecto
                                                        INNER JOIN tipoproyecto ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                                        INNER JOIN usuario ON actividad_recursos_adicionales.idUsuario = usuario.idUsuario
                                                        INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
                                                    WHERE
                                                        WEEK(actividad_recursos_adicionales.fechaInicio) IS NOT NULL
                                                        AND WEEK(actividad_recursos_adicionales.fechaInicio) BETWEEN WEEK(now()) - 3
                                                        AND WEEK(now())
                                                        AND empleado.idDepartamento = $idDepartamento
                                                        AND empleado.activo = 1
                                                    GROUP BY
                                                        week,
                                                        empleado
                                                )
                                                UNION
                                                ALL (
                                                    SELECT
                                                        WEEK(now()) AS week,
                                                        empleado.nombre,
                                                        0 AS 'Hrs'
                                                    FROM
                                                        usuario
                                                        INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
                                                    WHERE
                                                        idDepartamento = $idDepartamento
                                                        AND usuario.activo = 1
                                                )
                                            ) AS tmp
                                        GROUP BY
                                            week,
                                            empleado");
      $stmtWorkedHours->execute();
      // return $stmtWorkedHours;
      $dataResources = "['Week',";
      $weeks = array();
      $names = array();

      $resWorkedHoursDesign = $stmtWorkedHours->fetchAll();
      // Obtiene columnas y Filas
      for ($i=0; $i  < count($resWorkedHoursDesign); $i++) {
          // Columnas
          if (!in_array($resWorkedHoursDesign[$i]->empleado, $names)) {
              array_push($names, $resWorkedHoursDesign[$i]->empleado);
              $dataResources .= "'" . $resWorkedHoursDesign[$i]->empleado . "',";
          }
          // Filas
          if (!in_array($resWorkedHoursDesign[$i]->week, $weeks)) {
              array_push($weeks, $resWorkedHoursDesign[$i]->week);
          }
      }
      $totalHorasDesign = 0;
      $dataResources .= "'Standard'],";

      for ($j=0; $j < count($weeks); $j++) {
          $dataResources .= "['" . $weeks[$j] . "',";
          $counterDesign = 0;

          for ($k=0; $k < count($names); $k++) {
              $counter = 0;

              for ($i=0; $i < count($resWorkedHoursDesign); $i++) {
                  if ($resWorkedHoursDesign[$i]->week == $weeks[$j]) {
                      if ($resWorkedHoursDesign[$i]->empleado == $names[$k]) {
                          $dataResources .= $resWorkedHoursDesign[$i]->Hrs . ",";
                          $counter = 1;
                          $totalHorasDesign += $resWorkedHoursDesign[$i]->Hrs;
                      }
                  }
              }
              if ($counter == 0) {
                  $dataResources .= "0,";
              } else {
                  $counterDesign += 1;
              }
          }
          if ($j == count($weeks) - 1) {
              $dataResources .=  "45]";
          } else {
              $dataResources .=  "45],";
          }
      }
      $result["data"] = $dataResources;
      $result["names"] = $names;
      $result["weeks"] = $weeks;
      return $result;
  }

  function weeklyWorkedHoursApplicationPerDepartment($con, $idDepartamento){
      $result = array();

      $stmtWorkedHours = $con->prepare("SELECT
                                          WEEK(actividad_recursos_adicionales.fechaInicio) AS week,
                                          IF(empleado.idDepartamento=9, 'Application', 'Design') AS categoria,
                                          SUM(
                                              (
                                                  tipoproyecto.horas + IFNULL(proyecto.sobreCarga, 0)
                                              ) / (
                                                  (
                                                      SELECT
                                                          COUNT(*)
                                                      FROM
                                                          actividad_recursos_adicionales
                                                          INNER JOIN actividades_proyecto ON actividad_recursos_adicionales.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                                                      WHERE
                                                          actividades_proyecto.idProyecto = ap.idProyecto
                                                  )
                                              )
                                          ) AS 'Hrs'
                                      FROM
                                          actividad_recursos_adicionales
                                          INNER JOIN actividades_proyecto AS ap ON actividad_recursos_adicionales.idActividades_proyecto = ap.idActividades_proyecto
                                          INNER JOIN proyecto ON ap.idProyecto = proyecto.idProyecto
                                          INNER JOIN tipoproyecto ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                          INNER JOIN proyecto_categoria ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                          INNER JOIN usuario ON actividad_recursos_adicionales.idUsuario = usuario.idUsuario
                                          INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
                                      WHERE
                                          WEEK(actividad_recursos_adicionales.fechaInicio) IS NOT NULL
                                          AND WEEK(actividad_recursos_adicionales.fechaInicio) BETWEEN WEEK(now()) - 3
                                          AND WEEK(now())
                                          AND (
                                              empleado.idDepartamento = 9
                                              OR empleado.idDepartamento = 10
                                          )
                                          AND empleado.activo = 1
                                          AND proyecto.isApplication = 1
                                        GROUP BY
                                        week,
                                        empleado.idDepartamento");
      $stmtWorkedHours->execute();
      $stmtAvailableHours = $con->prepare("SELECT
                                                (
                                                    SELECT
                                                        COUNT(*) AS availableApplication
                                                    From
                                                        empleado
                                                    WHERE
                                                        empleado.activo = 1
                                                        AND empleado.idDepartamento = 9
                                                ) * 45 AS availableApplication,
                                                (
                                                    SELECT
                                                        COUNT(*) AS availableApplication
                                                    From
                                                        empleado
                                                    WHERE
                                                        empleado.activo = 1
                                                        AND empleado.idDepartamento = 10
                                                ) * 45 AS availableDesign");
      $stmtAvailableHours->execute();
            // return $stmtWorkedHours;
      $dataResources = "['Week',";
      $weeks = array();
      $names = array();

      $resWorkedHoursDesign = $stmtWorkedHours->fetchAll();
      $resAvailable = $stmtAvailableHours->fetchAll();
      // Obtiene columnas y Filas
      for ($i=0; $i  < count($resWorkedHoursDesign); $i++) {
          // Columnas
          if (!in_array($resWorkedHoursDesign[$i]->categoria, $names)) {
              array_push($names, $resWorkedHoursDesign[$i]->categoria);
              $dataResources .= "'" . $resWorkedHoursDesign[$i]->categoria . "',";
          }
          // Filas
          if (!in_array($resWorkedHoursDesign[$i]->week, $weeks)) {
              array_push($weeks, $resWorkedHoursDesign[$i]->week);
          }
      }
      $totalHorasDesign = 0;
      $dataResources .= "'Installed Application', 'Installed Design'],";

      for ($j=0; $j < count($weeks); $j++) {
          $dataResources .= "['" . $weeks[$j] . "',";
          $counterDesign = 0;

          for ($k=0; $k < count($names); $k++) {
              $counter = 0;

              for ($i=0; $i < count($resWorkedHoursDesign); $i++) {
                  if ($resWorkedHoursDesign[$i]->week == $weeks[$j]) {
                      if ($resWorkedHoursDesign[$i]->categoria == $names[$k]) {
                          $dataResources .= $resWorkedHoursDesign[$i]->Hrs . ",";
                          $counter = 1;
                          $totalHorasDesign += $resWorkedHoursDesign[$i]->Hrs;
                      }
                  }
              }
              if ($counter == 0) {
                  $dataResources .= "0,";
              } else {
                  $counterDesign += 1;
              }
          }
          if ($j == count($weeks) - 1) {
              $dataResources .=  $resAvailable[0]->availableApplication . "," . $resAvailable[0]->availableDesign ."]";
          } else {
              $dataResources .=  $resAvailable[0]->availableApplication . "," . $resAvailable[0]->availableDesign ."],";
          }
      }
      $result["data"] = $dataResources;
      $result["names"] = $names;
      $result["weeks"] = $weeks;
      return $result;
  }

  $stmt5 = $dbh->prepare("SELECT empleado.nombre, IFNULL(WEEK(fechaInicio),0) AS semana, IFNULL(SUM(recursos_asignados.horas),0) AS 'Hrs'
                          FROM empleado
                          LEFT JOIN recursos_asignados
                          ON recursos_asignados.idEmpleado = empleado.idEmpleado
                          WHERE idDepartamento <> 4 AND empleado.idDepartamento <> 3 AND activo = 1 AND (empleado.idPuesto <> 2 AND empleado.idPuesto <> 3)
                    	    GROUP BY empleado.nombre, WEEK(fechaInicio)
                          ORDER BY fechaInicio");
  $stmt5->execute();
  $coeResCapInstalada = $stmt5->fetchAll();

  $stmt6 = $dbh->prepare("SELECT empleado.nombre, IFNULL(WEEK(fechaInicio),0) AS semana, IFNULL(SUM(recursos_asignados.horas),0) AS 'Hrs'
                          FROM empleado
                          LEFT JOIN recursos_asignados
                          ON recursos_asignados.idEmpleado = empleado.idEmpleado
                          WHERE idDepartamento <> 4 AND empleado.idDepartamento = 3 AND activo = 1
                    	    GROUP BY empleado.nombre, WEEK(fechaInicio)
                          ORDER BY fechaInicio");
  $stmt6->execute();
  $quoteResCapInstalada = $stmt6->fetchAll();

  $stmt7 = $dbh->prepare("SELECT empleado.nombre, IFNULL(WEEK(fechaInicio),0) AS semana, IFNULL(SUM(recursos_asignados.horas),0) AS 'Hrs'
                          FROM empleado
                          LEFT JOIN recursos_asignados
                          ON recursos_asignados.idEmpleado = empleado.idEmpleado
                          WHERE idDepartamento <> 4 AND (empleado.idPuesto = 2 || empleado.idPuesto = 3) AND activo = 1
                          GROUP BY empleado.nombre, WEEK(fechaInicio)
                          ORDER BY fechaInicio");
  $stmt7->execute();
  $coeTechnicianResCapInstalada = $stmt7->fetchAll();

  $coe_dataTable_resources = "";
  $quote_dataTable_resources = "";
  $coe_technician_dataTable_resources = "";

  $filterWeek = 24;

  // COE
  $nombres3 = array();
  $dataNombres3 = array();

  // Filtra los resultados para mostrar todos los Recursos y las horas trabajadas en una semana especifica
  for ($i=0; $i < count($coeResCapInstalada); $i++) {
      if (!in_array($coeResCapInstalada[$i]->nombre, $nombres3)) {
          array_push($nombres3, $coeResCapInstalada[$i]->nombre);
          $dataNombres3[$coeResCapInstalada[$i]->nombre] = array();
          $dataNombres3[$coeResCapInstalada[$i]->nombre]['nombre'] = $coeResCapInstalada[$i]->nombre;

          if ($coeResCapInstalada[$i]->semana == $filterWeek || $coeResCapInstalada[$i]->semana == 0) {
              $dataNombres3[$coeResCapInstalada[$i]->nombre]['horasTrabajadas'] = $coeResCapInstalada[$i]->Hrs;
          } else {
              $dataNombres3[$coeResCapInstalada[$i]->nombre]['horasTrabajadas'] = 0;
          }
      } else {
            if ($coeResCapInstalada[$i]->semana == $filterWeek || $coeResCapInstalada[$i]->semana == 0) {
                  $dataNombres3[$coeResCapInstalada[$i]->nombre]['horasTrabajadas'] += $coeResCapInstalada[$i]->Hrs;
            }
      }
  }

  for ($i=0; $i < count($nombres3); $i++) {
      $coe_dataTable_resources .= "['" . $dataNombres3[$nombres3[$i]]['nombre'] . "',45," . $dataNombres3[$nombres3[$i]]['horasTrabajadas'] .
                              "," . (45 - (float)$dataNombres3[$nombres3[$i]]['horasTrabajadas']) . "],";
  }

  // QUOTE
  $nombres4 = array();
  $dataNombres4 = array();

  // Filtra los resultados para mostrar todos los Recursos y las horas trabajadas en una semana especifica
  for ($i=0; $i < count($quoteResCapInstalada); $i++) {
      if (!in_array($quoteResCapInstalada[$i]->nombre, $nombres4)) {
          array_push($nombres4, $quoteResCapInstalada[$i]->nombre);
          $dataNombres4[$quoteResCapInstalada[$i]->nombre] = array();
          $dataNombres4[$quoteResCapInstalada[$i]->nombre]['nombre'] = $quoteResCapInstalada[$i]->nombre;

          if ($quoteResCapInstalada[$i]->semana == $filterWeek || $quoteResCapInstalada[$i]->semana == 0) {
              $dataNombres4[$quoteResCapInstalada[$i]->nombre]['horasTrabajadas'] = $quoteResCapInstalada[$i]->Hrs;
          } else {
              $dataNombres4[$quoteResCapInstalada[$i]->nombre]['horasTrabajadas'] = 0;
          }
      } else {
            if ($quoteResCapInstalada[$i]->semana == $filterWeek || $quoteResCapInstalada[$i]->semana == 0) {
                  $dataNombres4[$quoteResCapInstalada[$i]->nombre]['horasTrabajadas'] += $quoteResCapInstalada[$i]->Hrs;
            }
      }
  }

  for ($i=0; $i < count($nombres4); $i++) {
      $quote_dataTable_resources .= "['" . $dataNombres4[$nombres4[$i]]['nombre'] . "',45," . $dataNombres4[$nombres4[$i]]['horasTrabajadas'] .
                              "," . (45 - (float)$dataNombres4[$nombres4[$i]]['horasTrabajadas']) . "],";
  }

  // COE TECHNICIAN
  $nombres5 = array();
  $dataNombres5 = array();

  // Filtra los resultados para mostrar todos los Recursos y las horas trabajadas en una semana especifica
  for ($i=0; $i < count($coeTechnicianResCapInstalada); $i++) {
      if (!in_array($coeTechnicianResCapInstalada[$i]->nombre, $nombres5)) {
          array_push($nombres5, $coeTechnicianResCapInstalada[$i]->nombre);
          $dataNombres5[$coeTechnicianResCapInstalada[$i]->nombre] = array();
          $dataNombres5[$coeTechnicianResCapInstalada[$i]->nombre]['nombre'] = $coeTechnicianResCapInstalada[$i]->nombre;

          if ($coeTechnicianResCapInstalada[$i]->semana == $filterWeek || $coeTechnicianResCapInstalada[$i]->semana == 0) {
              $dataNombres5[$coeTechnicianResCapInstalada[$i]->nombre]['horasTrabajadas'] = $coeTechnicianResCapInstalada[$i]->Hrs;
          } else {
              $dataNombres5[$coeTechnicianResCapInstalada[$i]->nombre]['horasTrabajadas'] = 0;
          }
      } else {
            if ($coeTechnicianResCapInstalada[$i]->semana == $filterWeek || $coeTechnicianResCapInstalada[$i]->semana == 0) {
                  $dataNombres5[$coeTechnicianResCapInstalada[$i]->nombre]['horasTrabajadas'] += $coeTechnicianResCapInstalada[$i]->Hrs;
            }
      }
  }

  for ($i=0; $i < count($nombres5); $i++) {
      $coe_technician_dataTable_resources .= "['" . $dataNombres5[$nombres5[$i]]['nombre'] . "',45," . $dataNombres5[$nombres5[$i]]['horasTrabajadas'] .
                              "," . (45 - (float)$dataNombres5[$nombres5[$i]]['horasTrabajadas']) . "],";
  }

?>

  <script type="text/javascript">
      // Load google charts
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      google.charts.load('current', {'packages':['table']});
      google.charts.setOnLoadCallback(drawTable);
      google.charts.load('current', {'packages':['gantt']});
      google.charts.setOnLoadCallback(drawGantt);
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);

      // Diagrama de Gantt
      function daysToMilliseconds(days) {
        return days * 24 * 60 * 60 * 1000;
      }

      function drawGantt() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Task ID');
        data.addColumn('string', 'Task Name');
        data.addColumn('date', 'Start Date');
        data.addColumn('date', 'End Date');
        data.addColumn('number', 'Duration');
        data.addColumn('number', 'Percent Complete');
        data.addColumn('string', 'Dependencies');

        data.addRows([
          <?php echo $dataGantt; ?>
        ]);

        var trackHeight = 40;

        var options = {
          height: data.getNumberOfRows() * trackHeight + 80,
          percentEnabled: true,

          gantt: {
            labelMaxWidth: 300, // Aparentemente maximo es 300
            palette: [
              {
                "color": "#2E86C1",
                "dark": "#52BE80",
                "light": "#F4D03F"
              }
            ]
          }

        };

        var chart = new google.visualization.Gantt(document.getElementById('gantt_div'));

        chart.draw(data, options);
      }

      // Tabla Recursos Asignados / SEMANALAES
      function drawVisualization() {
          // Some raw data (not necessarily accurate)
          var data = google.visualization.arrayToDataTable([
            <?php echo $dataRecursos ?>
          ]);

          var options = {
            // chartArea: {
            //   // leave room for y-axis labels
            //   height: '600x'
            // },
            // // legend: {
            // //   position: 'top'
            // // },
            // height: '700',
            title : 'Week load ----- ( <?php echo $totalHoras; ?>  Horas)',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Days'},
            seriesType: 'bars',
            isStacked: true,
            series: {
                        <?php echo count($nombres); ?>: {type: 'line'}
                    }
          };

          var dataDesign = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesDesign["data"]; ?>
          ]);

          var optsDesign = {
            // chartArea: {
            //   // leave room for y-axis labels
            //   height: '600'
            // },
            // // legend: {
            // //   position: 'top'
            // // },
            // height: '700',
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        <?php echo count($dataResourcesDesign["names"]); ?>: {type: 'line'},
                    }
          };

          var dataManufacturing = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesManufacturing["data"]; ?>
          ]);

          var optsManufacturing = {
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        <?php echo count($dataResourcesManufacturing["names"]); ?>: {type: 'line'},
                    }
          };
            // APPLICATION SECTION ------------------------------------------------------------>
          var dataApplication = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesApplication["data"]; ?>
          ]);

          var optsApplication = {
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        <?php echo count($dataResourcesApplication["names"]); ?>: {type: 'line'},
                    }
          };

          var dataApplicationDesign = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesApplicationDesign["data"]; ?>
          ]);

          var optsApplicationDesign = {
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        <?php echo count($dataResourcesApplicationDesign["names"]); ?>: {type: 'line'},
                    }
          };

          var dataApplicationPerDepartment = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesApplicationPerDepartment["data"]; ?>
          ]);

          var optsApplicationPerDepartment = {
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        0: {type: 'bar', color: 'blue'},
                        1: {type: 'bar', color: 'green'},
                        <?php echo count($dataResourcesApplicationPerDepartment["names"]); ?>: { type: 'line', lineDashStyle: [2, 2, 20, 2, 20, 2], color: 'blue'},
                        <?php echo count($dataResourcesApplicationPerDepartment["names"]) + 1; ?>: {type: 'line', color: 'green'},
                    }
          };
          // APPLICATION SECTION ------------------------------------------------------------>
          var dataTechnician = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesTechnician["data"]; ?>
          ]);

          var optsTechnician = {
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        <?php echo count($dataResourcesTechnician["names"]); ?>: {type: 'line'},
                    }
          };

          var dataDocumentation = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesDocumentation["data"]; ?>
          ]);

          var optsDocumentation = {
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        <?php echo count($dataResourcesDocumentation["names"]); ?>: {type: 'line'},
                    }
          };

          var dataAdministration = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesAdministration["data"]; ?>
          ]);

          var optsAdministration = {
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        <?php echo count($dataResourcesAdministration["names"]); ?>: {type: 'line'},
                    }
          };

          var dataQuality = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesQuality["data"]; ?>
          ]);

          var optsTQuality = {
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        <?php echo count($dataResourcesQuality["names"]); ?>: {type: 'line'},
                    }
          };

          var dataQuoting = google.visualization.arrayToDataTable([
            <?php echo $dataResourcesQuoting["data"]; ?>
          ]);

          var optsTQuoting = {
            title : 'Weekly worked hours',
            vAxis: {title: 'Hours'},
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {
                        <?php echo count($dataResourcesQuoting["names"]); ?>: {type: 'line'},
                    }
          };

          var chart = new google.visualization.ComboChart(document.getElementById('recursos_div'));
          chart.draw(data, options);
          var chartDesign = new google.visualization.ComboChart(document.getElementById('design_workload_div'));
          chartDesign.draw(dataDesign, optsDesign);
          var chartManufacturing = new google.visualization.ComboChart(document.getElementById('manufacturing_workload_div'));
          chartManufacturing.draw(dataManufacturing, optsManufacturing);
          var chartApplication = new google.visualization.ComboChart(document.getElementById('application_workload_div'));
          chartApplication.draw(dataApplication, optsApplication);
          var chartApplicationDesign = new google.visualization.ComboChart(document.getElementById('application_design_workload_div'));
          chartApplicationDesign.draw(dataApplicationDesign, optsApplicationDesign);
          var chartApplicationPerDepartment = new google.visualization.ComboChart(document.getElementById('application_per_department_workload_div'));
          chartApplicationPerDepartment.draw(dataApplicationPerDepartment, optsApplicationPerDepartment);
          var chartTechnician = new google.visualization.ComboChart(document.getElementById('technician_workload_div'));
          chartTechnician.draw(dataTechnician, optsTechnician);
          var chartDocumentation = new google.visualization.ComboChart(document.getElementById('documentation_workload_div'));
          chartDocumentation.draw(dataDocumentation, optsDocumentation);
          var chartAdministration = new google.visualization.ComboChart(document.getElementById('administration_workload_div'));
          chartAdministration.draw(dataAdministration, optsAdministration);
          var chartQuality = new google.visualization.ComboChart(document.getElementById('quality_workload_div'));
          chartQuality.draw(dataQuality, optsTQuality);
          var chartQuoting = new google.visualization.ComboChart(document.getElementById('quoting_workload_div'));
          chartQuoting.draw(dataQuoting, optsTQuoting);
      }

      // Draw the chart and set the chart values
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Cliente', 'Cantidad'],
            <?php echo $data; ?>
      ]);
        var dataQuote = google.visualization.arrayToDataTable([
            ['Cliente', 'Cantidad'],
            <?php echo $dataQuote; ?>
      ]);

        // Optional; add a title and set the width and height of the chart
        var options = {
            'title':'PROJECT BY CUSTOMER',
            is3D: true,
            'width': 600,
            height: 450,
            pieSliceText: 'percentage'
        };
        var options2 = {
            'title':'QUOTINGS BY CUSTOMER',
            is3D: true,
            'width': 600,
            height: 450,
            pieSliceText: 'percentage'
        };

        // Display the chart inside the <div> element with id="piechart"
        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);
        var chart2 = new google.visualization.PieChart(document.getElementById('piechart2'));
        chart2.draw(dataQuote, options2);
      }

      function drawTable() {
      var data = new google.visualization.DataTable();
          data.addColumn('string', 'Resource');
          data.addColumn('string', 'Capabilities');

          // data.addColumn('number', 'Salary');
          // data.addColumn('boolean', 'Full Time Employee');
          data.addRows([
            <?php echo $data2 ?>

            // ['Jim',   {v:8000,   f: '$8,000'},  false],
            // ['Alice', {v: 12500, f: '$12,500'}, true],
            // ['Bob',   {v: 7000,  f: '$7,000'},  true]
          ]);

      var data2 = new google.visualization.DataTable();
          data2.addColumn('string', 'Project');
          data2.addColumn('string', 'Type');
          data2.addColumn('string', 'Complexity');
          data2.addColumn('string', 'Overload');
          data2.addColumn('number', 'Total Hours');

          // data.addColumn('number', 'Salary');
          // data.addColumn('boolean', 'Full Time Employee');
          data2.addRows([
            <?php echo $data3 ?>
            // ['Jim',   {v:8000,   f: '$8,000'},  false],
            // ['Alice', {v: 12500, f: '$12,500'}, true],
            // ['Bob',   {v: 7000,  f: '$7,000'},  true]
          ]);

          function getSumData2(data, column) {
              var total = 0;
              for (i = 0; i < data2.getNumberOfRows(); i++){
                  total = total + data2.getValue(i, column);
              }
              return total;
          }

          data2.addRow(['','','','TOTALS',getSumData2(data,4)]);

        var data3 = new google.visualization.DataTable();
            data3.addColumn('string', 'Project');
            data3.addColumn('string', 'Type');
            data3.addColumn('string', 'Volume');
            data3.addColumn('string', 'Hours (Quote)');
            data3.addColumn('string', 'Hours (BOM)');
            data3.addColumn('string', 'Total Hours');

            // data.addColumn('number', 'Salary');
            // data.addColumn('boolean', 'Full Time Employee');
            data3.addRows([
              <?php echo $data4 ?>
              // ['Jim',   {v:8000,   f: '$8,000'},  false],
              // ['Alice', {v: 12500, f: '$12,500'}, true],
              // ['Bob',   {v: 7000,  f: '$7,000'},  true]
            ]);

        var data4 = new google.visualization.DataTable();
            data4.addColumn('string', 'Resource');
            data4.addColumn('number', 'Available Hours');
            data4.addColumn('number', 'Worked');
            data4.addColumn('number', 'Free');

            // data.addColumn('number', 'Salary');
            // data.addColumn('boolean', 'Full Time Employee');
            data4.addRows([
                <?php echo $coe_dataTable_resources ?>
              // ['Jim',   {v:8000,   f: '$8,000'},  false],
              // ['Alice', {v: 12500, f: '$12,500'}, true],
              // ['Bob',   {v: 7000,  f: '$7,000'},  true]
            ]);

            function getSum(data, column) {
                var total = 0;
                for (i = 0; i < data4.getNumberOfRows(); i++){
                    total = total + data4.getValue(i, column);
                }
                return total;
            }

            data4.addRow(['TOTALS',getSum(data,1),getSum(data,2),getSum(data,3)]);

            var data5 = new google.visualization.DataTable();
                data5.addColumn('string', 'Resource');
                data5.addColumn('number', 'Available Hours');
                data5.addColumn('number', 'Worked');
                data5.addColumn('number', 'Free');

                // data.addColumn('number', 'Salary');
                // data.addColumn('boolean', 'Full Time Employee');
                data5.addRows([
                    <?php echo $quote_dataTable_resources ?>
                  // ['Jim',   {v:8000,   f: '$8,000'},  false],
                  // ['Alice', {v: 12500, f: '$12,500'}, true],
                  // ['Bob',   {v: 7000,  f: '$7,000'},  true]
                ]);

            var data6 = new google.visualization.DataTable();
                data6.addColumn('string', 'Resource');
                data6.addColumn('number', 'Available Hours');
                data6.addColumn('number', 'Worked');
                data6.addColumn('number', 'Free');

                // data.addColumn('number', 'Salary');
                // data.addColumn('boolean', 'Full Time Employee');
                data6.addRows([
                    <?php echo $coe_technician_dataTable_resources ?>
                  // ['Jim',   {v:8000,   f: '$8,000'},  false],
                  // ['Alice', {v: 12500, f: '$12,500'}, true],
                  // ['Bob',   {v: 7000,  f: '$7,000'},  true]
                ]);

          // var table = new google.visualization.Table(document.getElementById('table_div'));
          var table2 = new google.visualization.Table(document.getElementById('table_div2'));
          // var table3 = new google.visualization.Table(document.getElementById('table_div1'));
          var table4 = new google.visualization.Table(document.getElementById('coe_table_resources'));
          var table5 = new google.visualization.Table(document.getElementById('quote_table_resources'));
          var table6 = new google.visualization.Table(document.getElementById('coe_technician_dataTable_resources'));

          // table.draw(data, {showRowNumber: false, width: '100%', height: '100%'});
          table2.draw(data2, {showRowNumber: false, width: '100%', height: '100%'});
          // table3.draw(data3, {showRowNumber: false, width: '100%', height: '100%'});
          table4.draw(data4, {showRowNumber: false, width: '100%', height: '60%'});
          table5.draw(data5, {showRowNumber: false, width: '100%', height: '20%'});
          table6.draw(data6, {showRowNumber: false, width: '100%', height: '40%'});
        }
  </script>
  <script type="text/javascript">
    $(document).ready(function() {

        // DataTable
      var table = $("#appdesigninstalledcapacitytable").DataTable({
          responsive: true,
          orderCellsTop: true,
          fixedHeader: true,
          pageLength: 15,
          // scrollX: true,
          dom: "Bfrtip",
          buttons: [
              // 'copyHtml5',
              "excelHtml5",
          ],
      });
      // Setup - add a text input to each footer cell
      $("#appdesigninstalledcapacitytable thead tr:eq(1) th").each(function () {
          var title = $(this).text();
          $(this).html(
              '<input type="text" placeholder="Search ' +
                  title +
                  '" class="column_search" />'
          );
      });

      // Apply the search
      $("#appdesigninstalledcapacitytable thead").on("keyup", ".column_search", function () {
          var customIndex = table.column($(this).parent().index() + ":visible");
          // console.log(customIndex);
          table.column(customIndex).search(this.value).draw();
      });

      // DataTable
    var table2 = $("#appdesigninstalledcompletetable").DataTable({
        responsive: true,
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 15,
        // scrollX: true,
        dom: "Bfrtip",
        buttons: [
            // 'copyHtml5',
            "excelHtml5",
        ],
    });
    // Setup - add a text input to each footer cell
    $("#appdesigninstalledcompletetable thead tr:eq(1) th").each(function () {
        var title2 = $(this).text();
        $(this).html(
            '<input type="text" placeholder="Search ' +
                title2 +
                '" class="column_search" />'
        );
    });

    // Apply the search
    $("#appdesigninstalledcompletetable thead").on("keyup", ".column_search", function () {
        var customIndex2 = table2.column($(this).parent().index() + ":visible");
        // console.log(customIndex);
        table2.column(customIndex2).search(this.value).draw();
    });

    });
  </script>

<!DOCTYPE html>
      <div class="text-center mt-4">
          <h1>DASHBOARD</h1>
      </div>

      <div class="mt-4 p-5 bg-light text-center rounded">
          <div class="row">
              <div class="col-12">
                  <div class="card" style="width: 100%">
                      <h3 class="" style="margin-bottom: 20px; text-align: center;">ACTIVE PROJECTS</h3>
                      <div class="text-center" style="width: 100%;" id="gantt_div"></div>
                  </div>
              </div>
          </div>
      </div>

      <div class="mt-4 p-5 bg-light text-center rounded">
          <div class="row">
              <div class="col-12">
                  <div class="card" style="width: 100%">
                      <h3 class="" style="margin-bottom: 20px; text-align: center;">PROJECT WORKLOAD VS ASSIGNED RESOURCES</h3>
                      <div class="" style="padding: 0px; height: 450px;  width: 100%;" id="recursos_div"></div>
                  </div>
              </div>
          </div>
      </div>

      <div class="card shadow p-3 mb-5 bg-body rounded w-100 mt-4">
          <div class="card-header bg-info text-white text-center fw-bold">
              APP & DESIGN Installed Capacity
          </div>
          <div class="row mt-4 p-5 bg-light text-center rounded">
            <h4 class="text-center">DEMAND</h4>
            <div class="col-12">
              <table id="appdesigninstalledcapacitytable" class="table w-100">
                  <thead>
                      <!-- Encabezados de tabla -->
                      <tr>
                          <th>ID</th>
                          <th>Date</th>
                          <th>Year</th>
                          <th>Week</th>
                          <th>Name</th>
                          <th>Description</th>
                          <th>Hrs</th>
                          <th>Overload</th>
                          <th>Total</th>
                          <th>Type</th>
                      </tr>
                      <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Year</th>
                        <th>Week</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Hrs</th>
                        <th>Overload</th>
                        <th>Total</th>
                        <th>Type</th>
                      </tr>
                  </thead>

                  <tfoot>
                      <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Year</th>
                        <th>Week</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Hrs</th>
                        <th>Overload</th>
                        <th>Total</th>
                        <th>Type</th>
                      </tr>
                  </tfoot>

                  <tbody>
                      <?php
                      $stmt = $dbh->prepare("SELECT
                                                  DATE(proyecto.fechaInicio) AS fechaInicio,
                                                  YEAR(proyecto.fechaInicio) AS anio,
                                                  WEEK(proyecto.fechaInicio) AS week,
                                                  proyecto.projectID,
                                                  proyecto.nombre,
                                                  proyecto.descripcion,
                                                  ROUND(IFNULL(tipoproyecto.horas, 0),2) AS hrs,
                                                  ROUND(IFNULL(proyecto.sobreCarga, 0),2) AS overLoad,
                                                  (
                                                      ROUND(IFNULL(tipoproyecto.horas, 0) + IFNULL(proyecto.sobreCarga, 0),2)
                                                  ) AS total,
                                                  proyecto.isApplication
                                              FROM
                                                  proyecto
                                                  INNER JOIN tipoproyecto ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                              ORDER BY
                                                  anio DESC,
                                                  week DESC");
                      $stmt->execute();
                      while ($resultado = $stmt->fetch()) {
                          echo "<tr>";
                              echo "<td>". $resultado->projectID . "</td>";
                              echo "<td>". $resultado->fechaInicio . "</td>";
                              echo "<td>". $resultado->anio . "</td>";
                              echo "<td>". $resultado->week . "</td>";
                              echo "<td>". $resultado->nombre . "</td>";
                              echo "<td>". $resultado->descripcion . "</td>";
                              echo "<td>". $resultado->hrs . "</td>";
                              echo "<td>". $resultado->overLoad . "</td>";
                              echo "<td>". $resultado->total . "</td>";
                              if($resultado->isApplication == 1){
                                  echo "<td>Application</td>";
                              }else{
                                  echo "<td>COE</td>";
                              }
                      }
                      ?>
                  </tbody>
              </table>
            </div>
            <h4 class="text-center mt-4">Completed</h4>
            <div class="col-12">
              <table id="appdesigninstalledcompletetable" class="table w-100">
                  <thead>
                      <!-- Encabezados de tabla -->
                      <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Year</th>
                        <th>Week</th>
                        <th>Hrs</th>
                        <th>Resource</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Type</th>
                      </tr>
                      <tr>
                      <th>ID</th>
                        <th>Date</th>
                        <th>Year</th>
                        <th>Week</th>
                        <th>Hrs</th>
                        <th>Resource</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Type</th>
                      </tr>
                  </thead>

                  <tfoot>
                      <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Year</th>
                        <th>Week</th>
                        <th>Hrs</th>
                        <th>Resource</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Type</th>
                      </tr>
                  </tfoot>

                  <tbody>
                      <?php
                      $stmt = $dbh->prepare("SELECT
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
                                                week DESC");
                      $stmt->execute();
                      while ($resultado = $stmt->fetch()) {
                          echo "<tr>";
                              echo "<td>". $resultado->projectID . "</td>";
                              if ($resultado->isApplication) {
                                  echo "<td>". $resultado->araFechaEntrega . "</td>";
                                  echo "<td>". $resultado->araYear . "</td>";
                                  echo "<td>". $resultado->araWeek . "</td>";
                                  echo "<td>". $resultado->araHrs . "</td>";
                              }else {
                                  echo "<td>". $resultado->apFechaEntrega . "</td>";
                                  echo "<td>". $resultado->apYear . "</td>";
                                  echo "<td>". $resultado->apWeek . "</td>";
                                  echo "<td>". $resultado->apHrs . "</td>";
                              }

                              echo "<td>". $resultado->eNombre . "</td>";
                              echo "<td>". $resultado->nombre . "</td>";
                              echo "<td>". $resultado->descripcion . "</td>";
                              if($resultado->isApplication == 1){
                                  echo "<td>Application</td>";
                              }else{
                                  echo "<td>COE</td>";
                              }
                      }
                      ?>
                  </tbody>
              </table>
            </div>
          </div>
      </div>

      <div class="card shadow p-3 mb-5 bg-body rounded w-100 mt-4">
          <div class="card-header bg-success text-white text-center fw-bold">
              Application Projects
          </div>
          <div class="row mt-4 p-5 bg-light text-center rounded">
            <div class="col-6">
                <h5 class="bg-white" style="">Workload per Application Engineer</h5>
                <div class="" style="padding: 0px; height: 450px; width: 100%;" id="application_workload_div"></div>
            </div>
            <div class="col-6">
                <h5 class="bg-white" style="">Workload per Design Engineer</h5>
                <div class="" style="padding: 0px; height: 450px; width: 100%;" id="application_design_workload_div"></div>
            </div>
          </div>
          <div class="row mt-4 p-5 bg-light text-center rounded">
            <div class="col-12">
                <h5 class="bg-white" style="">Workload per Department</h5>
                <div class="" style="padding: 0px; height: 450px; width: 100%;" id="application_per_department_workload_div"></div>
            </div>
          </div>
      </div>

      <div class="mt-4 p-5 bg-light text-center rounded">
          <div class="row">
              <div class="col-6">
                  <div class="card p-5" style="width: 100%">
                      <h3 class="" style="">COE INSTALLED CAPABILITY</h3>
                      <div class="" style="padding: 0px; height: 450px; width: 100%;" id="coe_table_resources"></div>
                  </div>
              </div>
              <div class="col-6">
                  <div class="card p-5" style="width: 100%">
                      <h3 class="" style="">COE INSTALLED CAPABILITY</h3>
                      <div class="" style="padding: 0px; height: 450px;  width: 100%;" id="quote_table_resources"></div>
                  </div>
              </div>
              <div class="col-6 mt-4">
                  <div class="card p-5" style="width: 100%">
                      <h3 class="" style="">COE INSTALLED CAPABILITY</h3>
                      <div class="" style="padding: 0px; height: 450px; width: 100%;" id="coe_technician_dataTable_resources"></div>
                  </div>
              </div>
          </div>
      </div>

      <div class="mt-4 p-5 bg-light text-center rounded">
          <div class="row mt-2">
              <h3 class="col-12" style="">ASSIGNED RESOURCES</h3>
              <div class="col-6">
                  <h3 class="" style="">DESIGN</h3>
                  <div class="" style="padding: 0px; height: 450px; width: 100%;" id="design_workload_div"></div>
              </div>
              <div class="col-6">
                  <h3 class="" style="">MANUFACTURING ENGINEERS</h3>
                  <div class="" style="padding: 0px; height: 450px; width: 100%;" id="manufacturing_workload_div"></div>
              </div>
          </div>
          <br>
          <div class="row mt-2">
              <div class="col-6">
                  <h3 class="" style="">TECHNICIAN</h3>
                  <div class="" style="padding: 0px; height: 450px;  width: 100%;" id="technician_workload_div"></div>
              </div>
              <div class="col-6">
                  <h3 class="" style="">DOCUMENTATION</h3>
                  <div class="" style="padding: 0px; height: 450px; width: 100%;" id="documentation_workload_div"></div>
              </div>
          </div>
          <br>
          <div class="row mt-2">
              <div class="col-6">
                  <h3 class="" style="">ADMINISTRATION</h3>
                  <div class="" style="padding: 0px; height: 450px;  width: 100%;" id="administration_workload_div"></div>
              </div>
              <div class="col-6">
                  <h3 class="" style="">QUALITY</h3>
                  <div class="" style="padding: 0px; height: 450px;  width: 100%;" id="quality_workload_div"></div>
              </div>
          </div>
          <br>
          <div class="row">
              <div class="col-6">
                  <h3 class="" style="">QUOTING</h3>
                  <div class="" style="padding: 0px; height: 450px;  width: 100%;" id="quoting_workload_div"></div>
              </div>
          </div>
      </div>

      <div class="mt-4 p-5 bg-light text-center rounded">
          <div class="row">
              <h3 class="" style="">CUSTOMER ACTIVITY</h3>
              <div class="col-6">
                  <div class="" id="piechart" style="width: 100%; margin-top: 40px; text-align: -webkit-center;"></div>
              </div>
              <div class="col-6">
                  <div class="" id="piechart2" style="width: 100%; margin-top: 40px; text-align: -webkit-center;"></div>
              </div>
          </div>
      </div>


      <div class="mt-4 p-5 bg-light text-center rounded">
          <div class="row">
              <div class="col-12">
                  <h3 class="" style="width: 100%; margin-bottom: 20px; text-align: center;">TOTAL PROJECT HOURS</h3>
                  <div class="" style="padding: 0; width: 100%;" id="table_div2"></div>
              </div>
          </div>
      </div>


      <!-- <div class="" style="display: flex;justify-content: space-around;">
          <div class="column card" style="width: 90%">
            <h3 class="flex-container" style="margin-bottom: 20px;">TOTAL HOURS (QUOTING)</h3>
            <div class="flex-containter" id="table_div1"></div>
          </div>
          <div class="column card" style="width: 90%">
            <h3 class="flex-container" style="margin-bottom: 20px;">INSTALLED CAPABILITY</h3>
            <div class="flex-containter" id="table_div"></div>
          </div>
      </div> -->

      <!-- <div class="flex-containter" style="margin-top: 50px; align: center;">
        <h3 class="flex-container" style="margin-bottom: 20px;">Carga de Trabajo</h3>
        <div class="" id="gantt_div"></div>
      </div>
      <div class="flex-container" style="">
        <h3 class="" style="">Recursos Asignados</h3>
        <div class="" style="padding: 0px; width: 100%;" id="recursos_div"></div>
      </div>
      <div class="flex-container" style="">
        <h3 class="" style="">Horas trabajadas</h3>
        <div class="" style="padding: 0px; width: 100%;" id="design_workload_div"></div>
      </div>
      <div class="flex-containter" style="align: center;">
        <h3 class="flex-container" style="margin-bottom: 20px;">Horas totales por Proyecto</h3>
        <div class="flex-containter" id="table_div2"></div>
      </div>
      <div class="flex-containter" style="align: center;">
        <h3 class="flex-container" style="margin-bottom: 20px;">Horas totales por Cotizaciones</h3>
        <div class="flex-containter" id="table_div1"></div>
      </div>
      <h3 class="flex-container">Proyectos por Cliente</h3>
      <div class="inline-container" style="justify-content: space-evenly;">
        <div class="" id="piechart" style="margin-top: 40px; text-align: -webkit-center;"></div>
        <div class="" id="piechart2" style="margin-top: 40px; text-align: -webkit-center;"></div>
      </div>
      <div class="flex-containter" style="align: center;">
        <h3 class="flex-container" style="margin-bottom: 20px;">Capacidades Instaladas</h3>
        <div class="flex-containter" id="table_div"></div>
      </div> -->


<?php include "inc/footer.html"; ?>
