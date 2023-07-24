<?php session_start();
  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(7, $_SESSION["permisos"]) && !in_array(8, $_SESSION["permisos"])) {
          $message = "Unauthorized User.";
          echo "<script>
                    alert('$message');
                    window.location.href='/index.php';
                </script>";
          die();
      }
  } else {
      $message = "Please Log in.";
      echo "<script>
                alert('$message');
                window.location.href='/login.php';
            </script>";
      die();
  }
  // Funcion para limpiar campos
  function cleanInput($value)
  {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  if (isset($_GET['idRecurso'])) {
      $id = cleanInput($_GET['id']);
      $idRecurso = cleanInput($_GET['idRecurso']);
      require "../../inc/conexion.php";

      $stmt = $dbh-> prepare("DELETE FROM recursos_asignados
                              WHERE idRecurso = $idRecurso");
      // Ejecutar la consulta preparada
      $stmt->execute();
  }
  if (isset($_GET['idCapRequeridas'])) {
      $id = cleanInput($_GET['id']);
      $idCapRequeridas = cleanInput($_GET['idCapRequeridas']);
      require ".../../inc/conexion.php";

      $stmt = $dbh-> prepare("DELETE FROM cap_requeridas
                              WHERE idCapRequeridas = $idCapRequeridas");
      // Ejecutar la consulta preparada
      $stmt->execute();
  }
  if (isset($_GET['idEnsamble'])) {
      $id = cleanInput($_GET['id']);
      $idEnsamble = cleanInput($_GET['idEnsamble']);
      require "../../inc/conexion.php";

      $stmt = $dbh-> prepare("DELETE FROM ensambles
                              WHERE idEnsamble = $idEnsamble");
      // Ejecutar la consulta preparada
      $stmt->execute();
  }

  // Campos obtenidos en GET
  $URL = "../../index.php";
  $id;
  $capRequeridas;
  $complxHRS;
  $tipoHRS;
  $actHRS;
  $totalHRS;
  $currentStage;
  if (isset($_GET['id'])) {
      $id = cleanInput($_GET['id']);
      require "../../inc/headerBoostrap.php";
      require "../../inc/conexion.php";
      $stmt = $dbh->prepare("SELECT idProyecto, projectID, cliente.nombreCliente, proyecto.nombre AS pnombre, proyecto.descripcion,
                                  CONCAT(proyecto_categoria.categoria, ' - ', proyecto_categoria.descripcion) as tiponombre,
                                  complejidad.nombre AS cnombre, cobrarA, ventasPotenciales, tipoproyecto.horas, currentStage, idLiderProyecto, idGerenteProyecto, idCoordinadorProyecto, idIngenieroQA,
                                  PO, qtoNumber, IFNULL(cuenta.idCarrier,'') AS idCarrier, IFNULL(cuenta.cuenta,'') AS cuenta, tracking, appTrackID, sobreCarga, date(fechaReqCliente) AS fechaReqCliente,
                                  date(fechaPromesa) AS fechaPromesa, date(fechaEmbarque) AS fechaEmbarque, proyecto.notas AS pnotas, status.nombre AS snombre, overallComplet, etapa.nombre AS etapNombre,
                                  5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaInicio) + 1, 1) AS turnAround,
                                  status.idStatus, longestMaterial, date(longestETA) AS longestETA, awarded, prioridad, isApplication, isApplication,
                                  (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = proyecto.idRespDiseno) AS respDiseno,
                                  (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = proyecto.idRespManu) AS respManu,
                                  (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = proyecto.idRepreVentas) AS salesRep,
                                  (SELECT empleado.nombre FROM usuario
                                      INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
                                      WHERE usuario.idUsuario = proyecto.idLiderProyecto) AS liderProyecto,
                                  (SELECT empleado.nombre FROM usuario
                                      INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
                                      WHERE usuario.idUsuario = proyecto.idGerenteProyecto) AS gerenteProyecto,
                                  (SELECT empleado.nombre FROM usuario
                                      INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
                                      WHERE usuario.idUsuario = proyecto.idCoordinadorProyecto) AS coordinadorProyecto,
                                  (SELECT empleado.nombre FROM usuario
                                      INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
                                      WHERE usuario.idUsuario = proyecto.idIngenieroQA) AS ingenieroQA,
                                  FORMAT((SELECT COUNT(idActividades_proyecto)
                                   FROM actividades_proyecto
                                   WHERE idProyecto = $id AND completado <> 0) /
                                        (SELECT COUNT(idActividades_proyecto)
                                         FROM actividades_proyecto
                                         WHERE idProyecto = $id) * 100, 0) AS completed
                              FROM proyecto
                              INNER JOIN cliente
                              ON proyecto.idCliente = cliente.idCliente
                              INNER JOIN tipoproyecto
                              ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                              INNER JOIN proyecto_categoria
                              ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                              INNER JOIN complejidad
                              ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                              LEFT JOIN cuenta
                              ON proyecto.idCuenta = cuenta.idCuenta
                              INNER JOIN status
                              ON proyecto.idStatus = status.idStatus
                              INNER JOIN etapa
                              ON proyecto.currentStage = etapa.idEtapa
                              WHERE idProyecto = $id");
      $stmt->execute();
      $stmt2 = $dbh->prepare("SELECT GROUP_CONCAT(capacidad.nombreCapacidad SEPARATOR ' | ') AS capacidades FROM cap_requeridas
                              INNER JOIN capacidad
                              ON capacidad.idCapacidad = cap_requeridas.idCapacidad
                              WHERE idProyecto = $id");
      $stmt2->execute();
      // $stmt3 = $dbh->prepare("SELECT proyecto.nombre, IFNULL(complejidad.horas,0) AS COMPLX, IFNULL(tipoproyecto.horas,0) AS TIPO, IFNULL(SUM(actividad.horas),0) AS ACT,
      //                     		      (IFNULL(complejidad.horas,0) + IFNULL(tipoproyecto.horas,0) + IFNULL(SUM(actividad.horas),0)) AS TOTAL
      //                         FROM proyecto
      //                         INNER JOIN complejidad
      //                         ON proyecto.idComplejidad = complejidad.idComplejidad
      //                         INNER JOIN tipoproyecto
      //                         ON proyecto.idTipo = tipoproyecto.idTipo
      //                         LEFT OUTER JOIN actividades_proyecto
      //                         ON proyecto.idProyecto = actividades_proyecto.idProyecto
      //                         LEFT OUTER JOIN actividad
      //                         ON actividades_proyecto.idActividad = actividad.idActividad
      //                         WHERE proyecto.idProyecto = $id
      //                         GROUP BY proyecto.nombre");
      // $stmt3->execute();

      // TOTAL HOURS WORLOAD BASED ON Activities
      $stmtActHours = $dbh->prepare("SELECT p.idTipoProyecto, tp.idComplejidad, SUM(a.horasLow) AS low, SUM(a.horasMid) AS mid, SUM(a.horasHigh) AS high
                                    FROM proyecto AS p
                                    INNER JOIN tipoproyecto AS tp
                                    ON p.idTipoProyecto = tp.idTipoProyecto
                                    INNER JOIN actividades_proyecto AS ap
                                    ON p.idProyecto = ap.idProyecto
                                    INNER JOIN actividad AS a
                                    ON ap.idActividad = a.idActividad
                                    WHERE p.idProyecto = $id");
      $stmtActHours->execute();
      // COMPLETADAS
      $stmtActHoursCompleted = $dbh->prepare("SELECT p.idTipoProyecto, tp.idComplejidad, SUM(a.horasLow) AS low, SUM(a.horasMid) AS mid, SUM(a.horasHigh) AS high
                                    FROM proyecto AS p
                                    INNER JOIN tipoproyecto AS tp
                                    ON p.idTipoProyecto = tp.idTipoProyecto
                                    INNER JOIN actividades_proyecto AS ap
                                    ON p.idProyecto = ap.idProyecto
                                    INNER JOIN actividad AS a
                                    ON ap.idActividad = a.idActividad
                                    WHERE ap.completado <> 0 AND p.idProyecto = $id");
      $stmtActHoursCompleted->execute();


      while ($res = $stmtActHours->fetch()) {
          switch ($res->idComplejidad) {
              case 1:
                  $totalHours = $res->low;
                  break;
              case 2:
                  $totalHours = $res->mid;
                  break;
              case 3:
                  $totalHours = $res->high;
                  break;
          }
      }

      while ($res2 = $stmtActHoursCompleted->fetch()) {
          switch ($res2->idComplejidad) {
              case 1:
                  $totalHoursCompleted = $res2->low;
                  break;
              case 2:
                  $totalHoursCompleted = $res2->mid;
                  break;
              case 3:
                  $totalHoursCompleted = $res2->high;
                  break;
          }
      }

      while ($resultado = $stmt2->fetch()) {
          $capRequeridas= $resultado->capacidades;
      }
      // while ($resultado = $stmt3->fetch()) {
      //   $complxHRS = $resultado->COMPLX;
      //   $tipoHRS = $resultado->TIPO;
      //   $actHRS = $resultado->ACT;
      //   $totalHRS = $resultado->TOTAL;
      // }
  }

  while ($resultado = $stmt->fetch()) {
      $currentStage = $resultado->currentStage;
      ?>
<!DOCTYPE html>

<div class="flex-container">
  <!-- <h1>Project Detail</h1> -->
</div>
<div class='icon-container' style="margin: 20px 0px;">
  <a id='backBtn' href='/log.php'>
    <div class='back-icon-green'></div>
  </a>
</div>

<hr style="width:100%;">


<?php  if (in_array(7, $_SESSION["permisos"])) { ?>
<div class="stage-label">
  <a href="../proyecto_alta/proyecto_alta_coe.php?id=<?php echo $id; ?>">
    <h2 class="neutral_status" id="editButton">Edit</h2>
  </a>
  <a href="#" onclick="abrirVentanaCorto(event)">
    <h2 class="neutral_status" id="editShort">SHORT MAT</h2>
  </a>
  <?php if ($resultado->snombre == "YELLOW STATUS") { ?>
  <a href="#" onclick="abrirVentanaStatus(event)">
    <h2 class="yellow_status" idStatus='<?php echo $resultado->idStatus; ?>' id="statusLabel">YELLOW STATUS</h2>
  </a>
  <?php } elseif ($resultado->snombre == "GREEN STATUS") { ?>
  <a href="#" onclick="abrirVentanaStatus(event)">
    <h2 class="green_status" idStatus='<?php echo $resultado->idStatus; ?>' id="statusLabel">GREEN STATUS</h2>
  </a>
  <?php } elseif ($resultado->snombre == "RED STATUS") { ?>
  <a href="#" onclick="abrirVentanaStatus(event)">
    <h2 class="red_status" idStatus='<?php echo $resultado->idStatus; ?>' id="statusLabel">RED STATUS</h2>
  </a>
  <?php } else { ?>
  <a href="#" onclick="abrirVentanaStatus(event)">
    <h2 class="neutral_status" idStatus='<?php echo $resultado->idStatus; ?>' id="statusLabel"><?php echo $resultado->snombre ?></h2>
  </a>
  <?php }
            // $resultado->completed == 30 && $resultado->awarded != 1
          if ($resultado->awarded != 1) { ?>
  <a href="#" onclick="assignAwarded(this)">
    <h2 class="neutral_status" id="editAwarded" style="background-color: #2ed573;">Awarded</h2>
  </a>
  <?php } elseif ($resultado->awarded == 1) { ?>
  <a href="#" onclick="cancelAwarded(this)">
    <h2 class="neutral_status" id="cancelAwarded" style="background-color: #E59866;">UnAwarded</h2>
  </a>
  <?php } ?>

  <a href="#" onclick="abrirVentanaPrioridad()">
    <h2 class="neutral_status" id="editPriority" style="background-color: #BB8FCE;">Change Priority</h2>
  </a>

</div>
<?php  } ?>

<hr style="width:100%;">

<div class="card mb-3">
    <h2 class="card-header text-center ">Project Detail<h2>
      <div class="container text-center mt-3">
      <?php if ($resultado->isApplication == 1) { ?>
                <span class="badge bg-success">Application Project</span>
      <?php } else { ?>
                <span class="badge bg-primary">COE Project</span>
      <?php } ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="row">

                    <!-- FIRST COLUMN -->
                    <div class="col-6">
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Project ID:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->projectID;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Name:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->pnombre;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Description:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->descripcion;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Customer:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->nombreCliente;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Freight to:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->cobrarA;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Tracking ID:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->tracking;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Approved Tracker ID:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->appTrackID;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Potential Sales:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo '$ ' . $resultado->ventasPotenciales . ' US';?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>PO:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->PO;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>QO Number:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->qtoNumber;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Customer Req Date:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->fechaReqCliente;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Promise Date:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->fechaPromesa;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Ship Date:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->fechaEmbarque;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Turnaround:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->turnAround;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Priority:</h6>
                            </div>
                            <div class="col-6">
                                <h6 id='prioridad' idPrioridad='<?php echo $resultado->prioridad; ?>' style="text-align: left; font-weight: bold;"><?php echo $resultado->prioridad;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Awarded:</h6>
                            </div>
                            <div class="col-6">
                                <h6 name='awarded' id='awarded' awardedValue='<?php echo $resultado->awarded; ?>' style="text-align: left; font-weight: bold;">
                                  <?php if (is_null($resultado->awarded)) {
                                            echo "PENDING";
                                        } elseif ($resultado->awarded == 1) {
                                            echo "YES";
                                        } else {
                                            echo "NO";
                                        } ?>
                                <h6>
                            </div>
                        </div>

                        <hr style="width:100%; margin-bottom: 20px;">

                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Project Leader:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->liderProyecto;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>COE Project Manager:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->gerenteProyecto;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Project Coordinator:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->coordinadorProyecto;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>COE QA Engineer:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->ingenieroQA;?><h6>
                            </div>
                        </div>
                    </div>

                    <!-- SECOND COLUMN -->
                    <div class="col-6">
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Project Type:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->tiponombre;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Complexity:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->cnombre;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Status:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->snombre;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>General Note:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->pnotas;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Design Resp:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->respDiseno;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Manufacturing Resp:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->respManu;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Required Capabilities:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $capRequeridas;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Manufacturing Hours:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->horas * (2/3) . ' Hours'; ?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Design Hours:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->horas * (1/3). ' Hours';?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Total Hours:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->horas . ' Hours';?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Completed:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo($resultado->completed) . ' %';?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Remaining Hours:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo($resultado->horas * (1-($resultado->completed / 100))) . ' Hours';?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Longest ETA:</h6>
                            </div>
                            <div class="col-6">
                                <h6 id='longestETA' style="text-align: left; font-weight: bold;"><?php  echo($resultado->longestMaterial . " - " . $resultado->longestETA);?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Sales Rep:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->salesRep;?><h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Stage:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->currentStage;?><h6>
                            </div>
                        </div>

                        <!-- TOTAL HOURS BASED ON ACTIVITIES -->
                        <hr style="width:100%; margin-bottom: 20px;">

                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Project estimated Hours Workload:</h6>
                            </div>
                            <div class="col-6">
                                  <?php if (is_null($totalHours)) { ?>
                                            <h6 style="text-align: left; font-weight: bold;">0 Hours<h6>
                                 <?php  } else { ?>
                                             <h6 style="text-align: left; font-weight: bold;"><?php echo $totalHours  . ' Hours'; ?><h6>
                                 <?php  } ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Completed:</h6>
                            </div>
                            <div class="col-6">
                                  <?php if (is_null($totalHours)) { ?>
                                        <h6 style="text-align: left; font-weight: bold;">0 %<h6>
                                 <?php  } else { ?>
                                              <h6 style="text-align: left; font-weight: bold;"><?php echo round(($totalHoursCompleted / $totalHours) * 100) . ' %';?><h6>
                                 <?php  } ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Remaining Hours Workload:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $totalHours - $totalHoursCompleted . ' Hours';?><h6>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<hr style="width:100%; margin-bottom: 20px;">
<?php
  }
?>
<!-- Seccion de Tabs -->
<div class="tab">
  <button class="tablinks" onclick="openSubtab(event, 'actividades')" id="defaultOpen">Activities</button>
  <button class="tablinks" onclick="openSubtab(event, 'ensambles')">Assemblies</button>
  <button class="tablinks" onclick="openSubtab(event, 'capacidades')">Capabilities</button>
  <button class="tablinks" onclick="openSubtab(event, 'Resources')">Resources</button>
  <button class="tablinks" onclick="openSubtab(event, 'Actividad')">Record</button>
  <button class="tablinks" onclick="openSubtab(event, 'stages')" id="stageTab">Stage Record</button>
</div>

<div id="actividades" class="tabcontent" style="background-color: white;">
  <div class="row mt-3 mb-5">
      <div class="inline-container">
        <h1>Activities</h1>
        <a href='../proyecto_actividades/proyecto_actividades_coe.php?id=<?php echo $id  ?>'>
          <div class='icon-container' style='margin-left: 10px;'>
            <div class='plus-icon'></div>
          </div>
        </a>
      </div>
  </div>

  <div class="row">
    <table id="tablaActividades" class="table w-100">
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>Etapa</th>
          <th>Activity</th>
          <th>Resp</th>
          <th>Start Date</th>
          <th>Due Date</th>
          <th>Completed</th>
          <th>Path</th>
          <th>Notes</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
                      $stmt = $dbh->prepare("SELECT idActividades_proyecto, actividad.nombre AS anombre, actividades_proyecto.notas, DATE(actividades_proyecto.fechaInicio) AS fechaInicio,
	                                                   DATE(actividades_proyecto.fechaRequerida) AS fechaRequerida, DATE(actividades_proyecto.fechaEntrega) AS fechaEntrega, ubicacion, proyecto.idStatus,
                                                       (SELECT e.nombre
                                                        FROM actividades_proyecto AS ap
                                                        INNER JOIN actividad AS a
                                                        ON ap.idActividad = a.idActividad
                                                        INNER JOIN etapa AS e
                                                        ON a.idEtapa = e.idEtapa
                                                        WHERE ap.idActividades_proyecto = actividades_proyecto.idActividades_proyecto) AS eNombre, IF(actividad.resp IS NULL or actividad.resp = '', 'OBSOLETA', actividad.resp) AS resp
                                            FROM actividades_proyecto
                                            INNER JOIN actividad
                                            ON actividades_proyecto.idActividad = actividad.idActividad
                                            INNER JOIN proyecto
                                            ON actividades_proyecto.idProyecto = proyecto.idProyecto
                                            WHERE actividades_proyecto.idProyecto = $id");
                      $stmt->execute();
                      // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
                      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
                      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
                      // $stmt->execute();
                      while ($resultado = $stmt->fetch()) {
                          if ($resultado->fechaEntrega == "" && $resultado->fechaRequerida < date("Y-m-d") && $resultado->idStatus != 5 && $resultado->idStatus !=6) {
                              if ($resultado->fechaRequerida != "") {
                                  echo "<tr id='" . $resultado->idActividades_proyecto . "' style='background-color:#F1948A;'>";
                              } else {
                                  echo "<tr id='" . $resultado->idActividades_proyecto . "'>";
                              }
                          } else {
                              echo "<tr id='" . $resultado->idActividades_proyecto . "'>";
                          }

                          echo "<td>". $resultado->eNombre . "</td>";
                          echo "<td>". $resultado->anombre . "</td>";
                          echo "<td>". $resultado->resp . "</td>";
                          echo "<td>". $resultado->fechaInicio . "</td>";
                          if ($resultado->idStatus == 6) {
                            echo "<td>HOLD</td>";
                          }else {
                            echo "<td>". $resultado->fechaRequerida . "</td>";
                          }
                          echo "<td>". $resultado->fechaEntrega . "</td>";
                          echo "<td><span class='editSpan ubicacion'>" . $resultado->ubicacion . "</span>";
                          echo "<input class='editInput ubicacion' type='text' name='ubicacion' value='" . $resultado->ubicacion . "' style='display: none;'></td>";
                          echo "<td><span class='editSpan notas'>" . $resultado->notas . "</span>";
                          echo "<input class='editInput notas' type='text' name='notas' value='" . $resultado->notas . "' style='display: none;'></td>";
                          echo "<td>";
                          if (in_array(8, $_SESSION["permisos"])) {
                              echo "<div class='' style='display: flex; justify-content: space-evenly;'>
                                            <a class='editBtn' href='#' onclick='editMode(this)'>
                                                <div class='icon-container'>
                                                    <div class='plus-icon-yellow'></div>
                                                </div>
                                            </a>
                                            <a class='guardarBtn' href='#' onclick='editarActividad(this)' style='display: none;'>
                                                <div class='icon-container'>
                                                    <div class='plus-icon-green'></div>
                                                </div>
                                            </a>
                                            <a class='deleteBtn' href='#' onclick='cancel(this)' style='display: none;'>
                                                <div class='icon-container'>
                                                    <div class='cross-icon'></div>
                                                </div>
                                            </a>
                                        </div>";
                          }
                          echo "</td>
                          </tr>";
                      }
                  ?>
      </tbody>
    </table>
  </div>
</div>

<div id="ensambles" class="tabcontent">
  <div class="inline-container">
    <h1>Assemblies</h1>
    <?php  if (in_array(7, $_SESSION["permisos"])) { ?>
    <a href='../proyecto_detalle/proyecto_ensambles.php?id=<?php echo $id  ?>'>
      <div class='icon-container' style='margin-left: 10px;'>
        <div class='plus-icon'></div>
      </div>
    </a>
    <?php  } ?>
  </div>

  <?php
      $stmtCompleted = $dbh->prepare("SELECT SUM(cantReq) AS req, SUM(cantTerm) AS term
                            FROM ensambles
                            WHERE idProyecto = $id");
      $stmtCompleted->execute();
      $summary = $stmtCompleted->fetch()
   ?>

  <div class="column-format2" style="background-color: #f1f1f1;">
      <div class="inline-container" style="display: flex; justify-content: space-between; margin-top: 30px;">
          <div class="">
              <h4>Required:</h4>
          </div>
          <div class="">
              <p style="text-align: left;"><?php echo $summary->req; ?>
              </p>
          </div>

          <div class="">
              <h4>Completed:</h4>
          </div>
          <div class="">
              <p style="text-align: left;"><?php echo $summary->term; ?></h2>
              </p>
          </div>

          <div class="">
              <h4>Completition:</h4>
          </div>
          <div class="">
              <p style="text-align: left;"><?php echo number_format(($summary->term/$summary->req)*100, 2, '.', '') . "%"; ?>
              </p>
          </div>
      </div>
  </div>

  <div class="flex-container" style="margin-top: 20px;">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Part #</th>
          <th>Work Order</th>
          <th>Req Qty</th>
          <th>Done Qty</th>
          <th>Notes</th>
          <th>Actions</th>
        </tr>
      </thead>
      <?php
                $stmt = $dbh->prepare("SELECT idEnsamble, numParte, workorder, cantReq, cantTerm, notas
                                      FROM ensambles
                                      WHERE idProyecto = $id");
                $stmt->execute();

                // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
                // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
                // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
                // $stmt->execute();
                while ($resultado = $stmt->fetch()) {
                    echo "<tr id='" . $resultado->idEnsamble . "'>";
                    echo "<td>" . $resultado->idEnsamble . "</td>";
                    echo "<td><span class='editSpan numParte'>" . $resultado->numParte . "</span>";
                    echo "<input class='editInput numParte' type='text' name='numParte' value='" . $resultado->numParte . "' style='display: none;'></td>";
                    echo "<td><span class='editSpan workorder'>" . $resultado->workorder . "</span>";
                    echo "<input class='editInput workorder' type='text' name='workorder' value='" . $resultado->workorder . "' style='display: none;'></td>";
                    echo "<td><span class='editSpan cantReq'>" . $resultado->cantReq . "</span>";
                    echo "<input class='editInput cantReq' type='text' name='cantReq' value='" . $resultado->cantReq . "' style='display: none;'></td>";
                    echo "<td><span class='editSpan cantTerm'>" . $resultado->cantTerm . "</span>";
                    echo "<input class='editInput cantTerm' type='text' name='cantTerm' value='" . $resultado->cantTerm . "' style='display: none;'></td>";
                    echo "<td><span class='editSpan notas'>" . $resultado->notas . "</span>";
                    echo "<input class='editInput notas' type='text' name='notas' value='" . $resultado->notas . "' style='display: none;'></td>";
                    // echo "<td>". $resultado->idEnsamble . "</td>";
                    // echo "<td>". $resultado->numParte . "</td>";
                    // echo "<td>". $resultado->workorder . "</td>";
                    // echo "<td>". $resultado->cantReq . "</td>";
                    // echo "<td>". $resultado->cantTerm . "</td>";
                    // echo "<td>". $resultado->notas . "</td>";
                    echo "<td>
                              <div class='' style='display: flex; justify-content: space-evenly;'>
                                  <a class='editBtn' href='#' onclick='editMode(this)'>
                                      <div class='icon-container'>
                                          <div class='plus-icon-yellow'></div>
                                      </div>
                                  </a>
                                  <a class='guardarBtn' href='#' onclick='guardarEnsamble(this)' style='display: none;'>
                                      <div class='icon-container'>
                                          <div class='plus-icon-green'></div>
                                      </div>
                                  </a>
                                  <a class='deleteBtn' href='#' onclick='cancel(this)' style='display: none;'>
                                      <div class='icon-container'>
                                          <div class='cross-icon'></div>
                                      </div>
                                  </a>
                              </div>
                          </td>";
                    echo "</tr>";
                }
            ?>
    </table>
  </div>
</div>

<div id="capacidades" class="tabcontent">
  <div class="inline-container">
    <h1>Required Capabilities</h1>
    <a href='../proyecto_detalle/proyecto_capacidades.php?id=<?php echo $id  ?>'>
      <div class='icon-container' style='margin-left: 10px;'>
        <div class='plus-icon'></div>
      </div>
    </a>
  </div>
  <div class="flex-container">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Capability</th>
          <th>Actions</th>
        </tr>
      </thead>
      <?php

                  $stmt = $dbh->prepare("SELECT nombreCapacidad, cap_requeridas.idCapRequeridas
                  FROM cap_requeridas
                  INNER JOIN capacidad
                  ON cap_requeridas.idCapacidad = capacidad.idCapacidad
                  WHERE idProyecto = $id");
                  $stmt->execute();

                  // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
                  // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
                  // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
                  // $stmt->execute();
                  while ($resultado = $stmt->fetch()) {
                      echo "<tr>";
                      echo "<td>". $resultado->idCapRequeridas . "</td>";
                      echo "<td>". $resultado->nombreCapacidad . "</td>";
                      echo "<td>
                            <a href='proyecto_detalle_coe.php?id=" . $id . "&idCapRequeridas=" . $resultado->idCapRequeridas . "'>
                                <div class='icon-container'>
                                    <div class='cross-icon'></div>
                                </div>
                            </a>
                            </td>";
                      echo "</tr>";
                  }
              ?>
    </table>
  </div>
</div>

<div id="Resources" class="tabcontent">
  <div class="inline-container">
    <h1>Assigned Resources</h1>
  </div>

  <?php
              $stmt = $dbh->prepare("SELECT actividades_proyecto.idProyecto, actividad.nombre AS anombre, empleado.nombre AS enombre
                                    FROM actividades_proyecto
                                    LEFT JOIN actividad
                                    ON actividades_proyecto.idActividad = actividad.idActividad
                                    LEFT JOIN recursos_asignados
                                    ON actividades_proyecto.idActividades_proyecto = recursos_asignados.idActividades_proyecto
                                    LEFT JOIN empleado
                                    ON recursos_asignados.idEmpleado = empleado.idEmpleado
                                    WHERE idProyecto = $id");
              $stmt->execute();
              $act="";
              while ($resultado = $stmt->fetch()) {
                  if ($act == "") {
                      echo "<div class='inline-container' style='border-style:solid; border-width:1px; background-color: white; margin-top: 10px;'>";
                      echo "<div class='column' style='height: auto; text-align: right;'>";
                      echo "<h4>" . $resultado->anombre . "</h4>";
                      echo "</div>";
                      echo "<div class='column' style='height: auto;'>";
                      $act = $resultado->anombre;
                  } elseif ($act <> $resultado->anombre) {
                      echo "</div>"; // Cierra columna
                      echo "</div>"; // Cierra Inline Container
                      echo "<div class='inline-container' style='border-style:solid; border-width:1px; background-color: white; margin-top: 10px;'>";
                      echo "<div class='column' style='height: auto; text-align: right;'>";
                      echo "<h4>" . $resultado->anombre . "</h4>";
                      echo "</div>";
                      echo "<div class='column' style='height: auto;'>";
                      $act = $resultado->anombre;
                  }
                  echo "<p style='margin: 0px 20px;'>" . $resultado->enombre . "</p>";
              }
              echo "</div>";
              echo "</div>";
?>
</div>

<div id="Actividad" class="tabcontent">
  <div class="inline-container">
    <h1>Note</h1>
    <a href='#' onclick='nuevaNota(event)'>
      <div class='icon-container' style='margin-left: 10px;'>
        <div class='plus-icon'></div>
      </div>
    </a>
  </div>
  <div class="areaNotas" id="areaNotas">

    <?php
              $stmt = $dbh->prepare("SELECT idProyectoNota AS id, nota, empleado.nombre, DATE(proyecto_notas.fechaCrea) AS fecha, usuario.idUsuario
                                                FROM proyecto_notas
                                                INNER JOIN usuario
                                                ON proyecto_notas.idUsuario = usuario.idUsuario
                                                INNER JOIN empleado
                                                ON usuario.idEmpleado = empleado.idEmpleado
                                                WHERE idProyecto = $id
                                                ORDER BY proyecto_notas.fechaCrea DESC");
              $stmt->execute();
              while ($resultado = $stmt->fetch()) {
                  echo "<div class='card' id='nota" . $resultado->id . "'>";
                  echo "<div class='inline-containter' style='width: 100%; display: inline-flex;'>";
                  echo "<div class='column' style='width: 70%;'>";
                  echo "<p id='notaText" . $resultado->id . "'>";
                  echo $resultado->nota;
                  echo "</p>";
                  echo "</div>";
                  echo "<div class='column' style='width: 20%;'>";
                  echo "<h5>by ";
                  echo $resultado->nombre;
                  echo "</h5>";
                  echo "<p>on ";
                  echo $resultado->fecha;
                  echo "</p>";
                  echo "</div>";

                  echo "<div class='column' style='width: 10%; padding: 0; display: inline-flex;'>";

                  date_default_timezone_set('America/Hermosillo'); // CDT
                  $current_date = date('Y-m-d');
                  if ($resultado->fecha == $current_date && $resultado->idUsuario == $_SESSION['idUsuario']) {
                      echo "<div class='inline-container' style='justify-content: space-evenly;'>";
                      echo "<div class='icon-container'>";
                      echo "<a href='#' onclick='editarNota(" . $resultado->id . "); return false;'>";
                      echo "<div class='plus-icon-yellow'></div>";
                      echo "</a>";
                      echo "</div>";
                      echo "<a href='#' onclick='deleteNota(" . $resultado->id . "); return false;'>";
                      echo "<div class='icon-container'>";
                      echo "<div class='cross-icon'></div>";
                      echo "</div>";
                      echo "</a>";
                      echo "</div>";
                  }
                  echo "</div>";
                  echo "</div>";
                  echo "</div>";
              }
?>

  </div>
</div>

<div id="stages" class="tabcontent">
  <?php
      $archivoController = '../../pages/activityStage/activityStageController.php';

// WORKIN ON IT/


      require_once $archivoController;
      $controller = new ActivityStage();
      $controller->render($id);
      // return false;
   ?>
</div>

<!-- VENTANAS MODALES -->
<div class="back-modal">
    <div class="contenido-modal" style="height: 350px;">
    </div>
</div>

<span class="alerta ocultar">
  <span class="msg">This is a warning</span>
  <span class='icon-container'>
    <div id="cerrar_alerta" class='cross-icon'></div>
  </span>
</span>

<script type="text/javascript">
  $(document).ready(function() {
    $('#cerrar_alerta').click(function() {
      $('.alerta').removeClass('mostrar');
      $('.alerta').addClass('ocultar');
    });

    if ($('#awarded').attr('awardedValue') == 1) {
      $('#awarded').css("background-color", "lightgreen");
    }

    $('#tablaActividades').DataTable({
      responsive: true
    });

    <?php
    if (isset($_GET['back'])) {
      ?>
      $('#backBtn').attr('href', '/<?php echo $_GET['back'] ?>.php');
        <?php
    } ?>

  });
</script>
<script src="../../js/funciones.js"></script>

<?php require_once('proyecto_detalle_coe_func.php'); ?>

<script>
  function openSubtab(evt, concept) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(concept).style.display = "block";
    evt.currentTarget.className += " active";
  }

  // Pestaña abierta por default
  document.getElementById("stageTab").click();
</script>

<?php include "../../inc/footer.html"; ?>
