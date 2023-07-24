<?php
  include "inc/headerBoostrap.php";
  include 'inc/conexion.php';
  if (isset($_SESSION["usuarioNombre"])) {
      $application = false;
      switch ($_SESSION['idDepartamento']) {
        case 1: //COE DISENO
            $responsabilidades = "17,20,23,29,30,35,42,46,48,49,52,57,69,80,81,110,112,114,115,116,117,118,121,122,123,125,126,134,136,139,140";
            $where = " proyecto.idRespDiseno = " . $_SESSION['idEmpleado'];
            $whereSQL = $where . " AND actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL .= "AND actividades_proyecto.completado = 0";
            $whereSQL .= " AND proyecto.idStatus <> 5";
            // RECHAZADOS
            $whereSQL2 = $where . " AND actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL2 .= "AND actividades_proyecto.completado = 3";
            break;
        case 2: //COE MANUFACTURA
            $responsabilidades = "39,68,70,71,72,73,74,75,76,77,78,82,83,103,105,106,119,124,129,131,132,137,141,142,143,144";
            $where = " proyecto.idRespManu = " . $_SESSION['idEmpleado'];
            $whereSQL = $where . " AND actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL .= "AND actividades_proyecto.completado = 0";
            $whereSQL .= " AND proyecto.idStatus <> 5";
            // $whereSQL .= " GROUP BY empleado.nombre";
            // RECHAZADOS
            $whereSQL2 = $where . " AND actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL2 .= "AND actividades_proyecto.completado = 3";
            break;
        case 3: //COE COTIZACIONES
            // $responsabilidades = "69,70,71,72,73,74,75,76,77,78,79,80,81,82,84,90,94,97,98";
            $where = " proyecto.idRespManu = " . $_SESSION['idEmpleado'];
            $whereSQL = $where;
            // $whereSQL = $where . " AND actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL .= "AND actividades_proyecto.completado = 0";
            // RECHAZADOS
            $whereSQL2 = $where;
            $whereSQL2 .= "AND actividades_proyecto.completado = 3";
            break;
        case 5: //COE PROGRAM MANAGER
            $responsabilidades = "1,3,15,16,18,19,21,22,84,107,108,109,111,113,120";
            $whereSQL = "actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL .= "AND actividades_proyecto.completado = 0";
            $whereSQL .= " AND proyecto.idStatus <> 5";
            // RECHAZADOS
            $whereSQL2 = "actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL2 .= "AND actividades_proyecto.completado = 3";
            break;
        case 6: //COE QUALITY
            $validaciones = "121,68,78,82,105,136";
            $responsabilidades = "36,56,79,99,101,102,104,127,128,130,133,138";
            $whereSQL = "actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL .= "AND actividades_proyecto.completado = 0";
            $whereSQL .= " AND proyecto.idStatus <> 5";
            $whereSQL2 = "";

            $stmtQuality = $dbh->prepare("SELECT proyecto.idProyecto, proyecto.nombre, actividad.nombre AS actividad, actividades_proyecto.idActividades_proyecto,
                                              DATE(actividades_proyecto.fechaEntrega) AS fechaEntrega, actividades_proyecto.ubicacion, isApplication
                                          FROM proyecto
                                          LEFT JOIN actividades_proyecto
                                          ON proyecto.idProyecto = actividades_proyecto.idProyecto
                                          LEFT JOIN actividad
                                          ON actividades_proyecto.idActividad = actividad.idActividad
                                          WHERE actividades_proyecto.completado = 1 AND actividad.idActividad IN(". $validaciones .")
                                          ORDER BY proyecto.nombre, proyecto.fechaCrea DESC");
            $stmtQuality->execute();
            break;
        case 9:
        case 10: //APPLICATION2 TEAM
            $responsabilidades = "150,151,152,153,154,155";
            $where = " e.idEmpleado = " . $_SESSION['idEmpleado'];
            // $whereSQL = $where . " AND actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL = $where . " AND ara.fechaEntrega IS NULL";
            $whereSQL .= " AND p.idStatus <> 5";
            // RECHAZADOS
            // $whereSQL2 = $where . " AND actividad.idActividad IN(" . $responsabilidades . ")";
            $whereSQL2 = $where . " AND ap.completado = 3";
            $application = true;
            break;
        default:
          $where = " p.idRespManu = " . $_SESSION['idEmpleado'];
          $whereSQL = $where;
          $whereSQL .= " AND ap.completado = 0";
          // RECHAZADOS
          $whereSQL2 = $where;
          $whereSQL2 .= " AND ap.completado = 3";
          break;
      }

      // echo $application;
      if ($application <> 1) {
          $stmt = $dbh->prepare("SELECT numEmpleado, correo, celular, empleado.nombre AS enombre, usuario.usuarioNombre AS usuario, puesto.nombre AS puesto
                                  FROM empleado
                                  INNER JOIN usuario
                                  ON empleado.idEmpleado = usuario.idEmpleado
                                  INNER JOIN puesto
                                  ON empleado.idPuesto = puesto.idPuesto
                                  WHERE usuario.idUsuario = " . $_SESSION['idUsuario']);
          $stmt->execute();
          $stmt2 = $dbh->prepare("SELECT proyecto.idProyecto, proyecto.projectID, proyecto.descripcion, proyecto.nombre, proyecto.idStatus, actividad.nombre AS actividad, empleado.idEmpleado, empleado.nombre AS enombre, actividad.tipo AS tipo, isApplication,
                                          DATE(recursos_asignados.fechaInicio) AS fechaInicio, recursos_asignados.horas, actividades_proyecto.idActividades_proyecto, actividades_proyecto.ubicacion, DATE(actividades_proyecto.fechaRequerida) AS fechaRequerida
                                  FROM proyecto
                                  LEFT JOIN actividades_proyecto
                                  ON proyecto.idProyecto = actividades_proyecto.idProyecto
                                  LEFT JOIN actividad
                                  ON actividades_proyecto.idActividad = actividad.idActividad
                                  LEFT JOIN recursos_asignados
                                  ON actividades_proyecto.idActividades_proyecto = recursos_asignados.idActividades_proyecto
                                  LEFT JOIN empleado
                                  ON recursos_asignados.idEmpleado = empleado.idEmpleado
                                  LEFT JOIN departamento
                                  ON empleado.idDepartamento = departamento.idDepartamento
                                  WHERE " . $whereSQL . " ORDER BY proyecto.idProyecto, actividad.nombre, empleado.nombre");
          // WHERE " . '(proyecto.idRespDiseno = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idRespManu = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idDepartamento = ' . $_SESSION['idDepartamento'] . ")AND actividad.idActividad IN(" . $responsabilidades . ")");
          $stmt2->execute();
          // var_dump($stmt2);
          $stmtRechazadas = $dbh->prepare("SELECT proyecto.idProyecto, proyecto.nombre, actividad.nombre AS actividad, isApplication,
                                                  actividades_proyecto.idActividades_proyecto, actividades_proyecto.ubicacion,
                                                  DATE(actividades_proyecto.fechaAprobacion) AS fechaAprobacion, actividades_proyecto.notas
                                          FROM proyecto
                                          LEFT JOIN actividades_proyecto
                                          ON proyecto.idProyecto = actividades_proyecto.idProyecto
                                          LEFT JOIN actividad
                                          ON actividades_proyecto.idActividad = actividad.idActividad
                                          WHERE " . $whereSQL2 . " ORDER BY proyecto.nombre, proyecto.fechaCrea DESC");
          // WHERE " . '(proyecto.idRespDiseno = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idRespManu = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idDepartamento = ' . $_SESSION['idDepartamento'] . ")AND actividad.idActividad IN(" . $responsabilidades . ")");
          $stmtRechazadas->execute();
      } else { // ----------------------- APPLICATION SECTION  ------------------------------------------------------- //
          $stmt = $dbh->prepare("SELECT numEmpleado, correo, celular, empleado.nombre AS enombre, usuario.usuarioNombre AS usuario, puesto.nombre AS puesto
                                  FROM empleado
                                  INNER JOIN usuario
                                  ON empleado.idEmpleado = usuario.idEmpleado
                                  INNER JOIN puesto
                                  ON empleado.idPuesto = puesto.idPuesto
                                  WHERE usuario.idUsuario = " . $_SESSION['idUsuario']);
          $stmt->execute();
          // echo var_dump($stmt);
          $stmt2 = $dbh->prepare("SELECT
                                      p.idProyecto,
                                      p.projectID,
                                      p.descripcion,
                                      p.nombre,
                                      p.idStatus,
                                      a.nombre AS actividad,
                                      e.idEmpleado,
                                      e.nombre AS enombre,
                                      a.tipo AS tipo,
                                      p.isApplication,
                                      DATE(ra.fechaInicio) AS fechaInicio,
                                      ra.horas,
                                      ap.idActividades_proyecto,
                                      ara.idRecursosAdicionales,
                                      ap.ubicacion,
                                      DATE(ap.fechaRequerida) AS fechaRequerida
                                  FROM
                                      actividad_recursos_adicionales AS ara
                                      INNER JOIN actividades_proyecto AS ap ON ara.idActividades_proyecto = ap.idActividades_proyecto
                                      INNER JOIN actividad AS a ON ap.idActividad = a.idActividad
                                      INNER JOIN proyecto AS p ON ap.idProyecto = p.idProyecto
                                      INNER JOIN usuario AS u ON ara.idUsuario = u.idUsuario
                                      INNER JOIN empleado AS e ON u.idEmpleado = e.idEmpleado
                                      LEFT JOIN recursos_asignados AS ra ON ap.idActividades_proyecto = ra.idActividades_proyecto
                                  WHERE " . $whereSQL . " ORDER BY p.idProyecto, a.nombre, e.nombre");
          // WHERE " . '(proyecto.idRespDiseno = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idRespManu = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idDepartamento = ' . $_SESSION['idDepartamento'] . ")AND actividad.idActividad IN(" . $responsabilidades . ")");
          $stmt2->execute();
          $stmtRechazadas = $dbh->prepare("SELECT proyecto.idProyecto, proyecto.nombre, actividad.nombre AS actividad, isApplication,
                                                  actividades_proyecto.idActividades_proyecto, actividades_proyecto.ubicacion,
                                                  DATE(actividades_proyecto.fechaAprobacion) AS fechaAprobacion, actividades_proyecto.notas
                                          FROM proyecto
                                          LEFT JOIN actividades_proyecto
                                          ON proyecto.idProyecto = actividades_proyecto.idProyecto
                                          LEFT JOIN actividad
                                          ON actividades_proyecto.idActividad = actividad.idActividad
                                          WHERE " . $whereSQL2 . " ORDER BY proyecto.nombre, proyecto.fechaCrea DESC");
          // WHERE " . '(proyecto.idRespDiseno = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idRespManu = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idDepartamento = ' . $_SESSION['idDepartamento'] . ")AND actividad.idActividad IN(" . $responsabilidades . ")");
          $stmtRechazadas->execute();
      }

      $stmActividadUsuariosAsignados = $dbh->prepare("SELECT ua.idUsuarioAsignado, p.idProyecto, ap.idActividades_proyecto, ua.idUsuarioAsignado AS uaId, ua.idUsuario AS idUsuario, p.nombre AS pNombre, a.nombre AS aNombre, motivoReq, DATE(ua.fechaInicio) AS inicio
                                                      FROM usuarios_asignados AS ua
                                                      INNER JOIN actividades_proyecto AS ap
                                                      ON ap.idActividades_proyecto = ua.idActividades_proyecto
                                                      INNER JOIN actividad AS a
                                                      ON ap.idActividad = a.idActividad
                                                      INNER JOIN proyecto AS p
                                                      ON ap.idProyecto = p.idProyecto
                                                      WHERE ua.fechaAprobacion IS NULL AND ua.idUsuario = " . $_SESSION['idUsuario']);
      // WHERE " . '(proyecto.idRespDiseno = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idRespManu = ' . $_SESSION['idEmpleado'] . ' OR proyecto.idDepartamento = ' . $_SESSION['idDepartamento'] . ")AND actividad.idActividad IN(" . $responsabilidades . ")");
      $stmActividadUsuariosAsignados->execute();

  } else {
      $message = "Please Log in.";
      echo "<script>
              alert('$message');
              window.location.href='login.php';
          </script>";
      die();
  }
?>

<?php

    // USER ID SECTION
    if (!session_id()) {
        session_start();
    }
    if (isset($_SESSION['idUsuario'])) {
        $idUser = $_SESSION['idUsuario'];
    } else {
        echo "NOT FOUND USER ID";
    }

    $stmtEtapa = $dbh->prepare("SELECT p.idProyecto, c.nombreCliente AS cNombre, p.nombre AS pNombre, pc.descripcion, e.nombre AS eNombre, e.descripcion AS eDescripcion
                                FROM proyecto_aprobador_etapa AS pa
                                INNER JOIN proyecto_etapa AS pe
                                ON pa.idProyectoEtapa = pe.idProyectoEtapa
                                INNER JOIN etapa AS e
                                ON pe.idEtapa = e.idEtapa
                                INNER JOIN proyecto AS p
                                ON pe.idProyecto = p.idProyecto
                                INNER JOIN tipoproyecto AS tp
                                ON p.idTipoProyecto = tp.idTipoProyecto
                                INNER JOIN proyecto_categoria AS pc
                                ON tp.idProyectoCategoria = pc.idProyectoCategoria
                                INNER JOIN cliente AS c
                                ON p.idCliente = c.idCliente
                                WHERE pa.idUsuario = $idUser AND pa.approved <> 1");
    $stmtEtapa->execute();

?>

      <!DOCTYPE html>
        <div class="flex-container">
            <h1>User's Profile</h1>
            <h4>Password Change</h4>
            <a href='#' onclick='abrirVentanaContrasena()'>
                <div class='icon-container'>
                    <div class='plus-icon-yellow'></div>
                </div>
            </a>

<?php
          while ($resultado = $stmt->fetch()) {
              ?>
              <div class="" style="margin-top: 20px;">
                  <div class="input-field">
                      <label for="numEmpleado">Employee #</label>
                      <input name="numEmpleado" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->numEmpleado; ?>" disabled>
                  </div>
                  <div class="input-field">
                      <label for="nombre">Name</label>
                      <input name="nombre" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->enombre; ?>"disabled>
                  </div>
                  <div class="input-field">
                      <label for="puesto">Position</label>
                      <input name="puesto" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->puesto; ?>"disabled>
                  </div>
                  <div class="input-field">
                      <label for="correo">Email</label>
                      <input name="correo" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->correo; ?>"disabled>
                  </div>
                  <div class="input-field">
                      <label for="celular">Phone</label>
                      <input name="celular" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->celular; ?>"disabled>
                  </div>
                  <div class="input-field">
                      <label for="usuario">Username</label>
                      <input name="usuario" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->usuario; ?>"disabled>
                  </div>
              </div>
          </div>
<?php
          } ?>

          <!-- Seccion de Tabs -->
          <div class="tab" style="margin-top: 20px;">

          <?php   if ($_SESSION['idDepartamento'] == 6) { ?>
              <button class="tablinks" onclick="openSubtab(event, 'quality')">Quality</button>
          <?php  } else { ?>
              <button class="tablinks" onclick="openSubtab(event, 'rechazadas')">Rejected</button>
          <?php } ?>

            <button class="tablinks" onclick="openSubtab(event, 'actividades')" id="defaultOpen">Activities</button>
            <button class="tablinks" onclick="openSubtab(event, 'toSupport')">To Support</button>
            <button class="tablinks" onclick="openSubtab(event, 'stageValidation')">Project Stage Validation</button>
          </div>

          <!-- <div id="placeholder" class="tabcontent">
              <div class="inline-container">
                  <h1>To Approve</h1>
                  <a href='#'>
                      <div class='icon-container'>
                          <div class='plus-icon'></div>
                      </div>
                  </a>
              </div>
          </div> -->

          <!-- QUALITY SECTION -->
          <?php   if ($_SESSION['idDepartamento'] == 6) { ?>
                          <div id="quality" class="tabcontent">
                              <div class="inline-container">
                                  <h1>Pending to Review</h1>
                              </div>
                    <?php
                                  $pro="";
                                  $act="";

                                  while ($resultado2 = $stmtQuality->fetch()) {
                                      if ($pro == "") {
                                          echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                                          echo "<div class='column' style='width: 20%; height: auto; text-align: right;'>";
                                          if ($resultado2->isApplication == 1) {
                                              echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$resultado2->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado2->nombre . "</h5>";
                                          }else {
                                              echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_coe.php?id=".$resultado2->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado2->nombre . "</h5>";
                                          }
                                          echo "</div>";
                                          echo "<div class='column' style='width: 80%; height: auto;'>";
                                          $pro = $resultado2->nombre;
                                      } elseif ($pro <> $resultado2->nombre) {
                                          echo "</div>"; // Cierra columna
                                          echo "</div>"; // Cierra columna
                                          echo "</div>"; // Cierra columna
                                          echo "</div>"; // Cierra Inline Container
                                          echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                                          echo "<div class='column' style='width: 20%; height: auto; text-align: right;'>";
                                          if ($resultado2->isApplication == 1) {
                                              echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$resultado2->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado2->nombre . "</h5>";
                                          }else {
                                              echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_coe.php?id=".$resultado2->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado2->nombre . "</h5>";
                                          }
                                          echo "</div>";
                                          echo "<div class='column' style='width: 80%; height: auto;'>";
                                          $pro = $resultado2->nombre;
                                          $act="";
                                      }
                                      if ($act == "") {
                                          echo "<div class='inline-container'  id='act" . $resultado2->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                                          echo "<div class='column' style='width: 100%; height: auto; text-align: right;'>";
                                          if ($resultado2->actividad <> null) {
                                              // Inserta Boton
                                              echo "<div class='inline-container'  id='act" . $resultado2->idActividades_proyecto . "' style='margin: auto; justify-content: space-between;'>";

                                              echo    "<div class='inline-container' style='margin: auto; width:30%; justify-content: space-between; text-align:initial;'>";
                                              echo        "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado2->idActividades_proyecto . "' href='#' onclick='
                                              (" . $resultado2->idActividades_proyecto . ",1)'>
                                                              <div class='icon-container'>
                                                                  <div class='plus-icon-yellow'></div>
                                                              </div>
                                                            </a>";
                                              echo          "<h3 style='margin-right: auto; text-align: initial;'>" . $resultado2->actividad . "</h3>";
                                              echo    "</div>";
                                              echo "<div style='width:15%; text-align: -webkit-center;'>";
                                              echo    "<h3 style='margin-right: auto;'>" . $resultado2->fechaEntrega . "</h3>";
                                              echo "</div>";
                                              echo "<div style='width:55%; text-align: -webkit-center; text-align:initial;'>";
                                              echo    "<h3 style='margin-right: auto; white-space: pre-wrap;'>" . $resultado2->ubicacion . "</h3>";
                                              echo "</div>";

                                              echo "</div>";
                                              echo "</div>";
                                              echo "<div class='column column" . $resultado2->idActividades_proyecto . "' style='width: 0%; height: auto;'>";
                                              $act = $resultado2->actividad;
                                          } else {
                                          }
                                      } elseif ($act <> $resultado2->actividad) {
                                          echo "</div>"; // Cierra columna
                                          echo "</div>"; // Cierra Inline Container
                                          echo "<div class='inline-container'  id='act" . $resultado2->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                                          echo "<div class='column' style='width: 100%; height: auto; text-align: right;'>";
                                          if ($resultado2->actividad <> null) {
                                              // Inserta Boton
                                              echo "<div class='inline-container' style='margin: auto; justify-content: space-between; text-align:initial;'>";
                                              echo "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado2->idActividades_proyecto . "' href='#' onclick='
                                              (" . $resultado2->idActividades_proyecto . ",1)'>
                                                        <div class='icon-container'>
                                                            <div class='plus-icon-yellow'></div>
                                                        </div>
                                                    </a>";
                                              echo "<h3 style='margin-right: auto; text-align: initial;'>" . $resultado2->actividad . "</h3>";
                                              echo "<div style='width:15%; text-align: -webkit-center;'>";
                                              echo    "<h3 style='margin-right: auto;'>" . $resultado2->fechaEntrega . "</h3>";
                                              echo "</div>";
                                              echo "<div style='width:55%; text-align: -webkit-center; text-align:initial;'>";
                                              echo    "<h3 style='margin-right: auto; white-space: pre-wrap;'>" . $resultado2->ubicacion . "</h3>";
                                              echo "</div>";
                                              echo "</div>";
                                              echo "</div>";
                                              echo "<div class='column column" . $resultado2->idActividades_proyecto . "' style='width: 0%; height: auto;'>";
                                              $act = $resultado2->actividad;
                                          } else {
                                          }
                                      }
                                  }
                                  echo "</div>";
                                  echo "</div>";
                                  echo "</div>";
                                                    echo "</div>";
                    ?>
            </div>
            <!-- REJECTED SECTION -->
          <?php  } else { ?>
            <div id="rechazadas" class="tabcontent">
                <div class="inline-container">
                    <h1>Rejected Activities</h1>
                </div>
      <?php
                    $pro="";
                    $act="";

                    while ($resultado3 = $stmtRechazadas->fetch()) {
                        if ($pro == "") {
                            echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                            echo "<div class='column' style='width: 20%; height: auto; text-align: right;'>";
                            echo "<h3><a href='/proyecto_detalle.php?id=".$resultado3->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado3->nombre . "</h3>";
                            echo "</div>";
                            echo "<div class='column' style='width: 80%; height: auto;'>";
                            $pro = $resultado3->nombre;
                        } elseif ($pro <> $resultado3->nombre) {
                            echo "</div>"; // Cierra columna
                            echo "</div>"; // Cierra columna
                            echo "</div>"; // Cierra columna
                            echo "</div>"; // Cierra Inline Container
                            echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                            echo "<div class='column' style='width: 20%; height: auto; text-align: right;'>";
                            if ($resultado3->isApplication == 1) {
                                echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$resultado3->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado3->nombre . "</h5>";
                            }else {
                                echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_coe.php?id=".$resultado3->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado3->nombre . "</h5>";
                            }
                            echo "</div>";
                            echo "<div class='column' style='width: 80%; height: auto;'>";
                            $pro = $resultado3->nombre;
                            $act="";
                        }
                        if ($act == "") {
                            echo "<div class='inline-container'  id='act" . $resultado3->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                            echo "<div class='column' style='width: 100%; height: auto; text-align: right;'>";
                            if ($resultado3->actividad <> null) {
                                // Inserta Boton
                                echo "<div class='inline-container'  id='act" . $resultado3->idActividades_proyecto . "' style='margin: auto; justify-content: space-between;'>";

                                echo    "<div class='inline-container' style='margin: auto; width:30%; justify-content: space-between; text-align:initial;'>";
                                echo        "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado3->idActividades_proyecto . "' href='#' onclick='abrirVentanaActividad(" . $resultado3->idActividades_proyecto . ",0)'>
                                                <div class='icon-container'>
                                                    <div class='plus-icon-yellow'></div>
                                                </div>
                                              </a>";
                                echo          "<h2 style='margin-right: auto; text-align: initial;'>" . $resultado3->actividad . "</h2>";
                                echo    "</div>";
                                echo "<div style='width:35%; text-align: -webkit-center; text-align:initial;'>";
                                echo    "<h2 style='margin-right: auto;'>" . $resultado3->notas . "</h2>";
                                echo "</div>";
                                echo "<div style='width:15%; text-align: -webkit-center;'>";
                                echo    "<h2 style='margin-right: auto;'>" . $resultado3->fechaAprobacion . "</h2>";
                                echo "</div>";
                                echo "<div style='width:25%; text-align: -webkit-center; text-align:initial;'>";
                                echo    "<h2 style='margin-right: auto; white-space: pre-wrap;'>" . $resultado3->ubicacion . "</h2>";
                                echo "</div>";

                                echo "</div>";
                                echo "</div>";
                                echo "<div class='column column" . $resultado3->idActividades_proyecto . "' style='width: 0%; height: auto;'>";
                                $act = $resultado3->actividad;
                            } else {
                            }
                        } elseif ($act <> $resultado3->actividad) {
                            echo "</div>"; // Cierra columna
                            echo "</div>"; // Cierra Inline Container
                            echo "<div class='inline-container'  id='act" . $resultado3->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                            echo "<div class='column' style='width: 100%; height: auto; text-align: right;'>";
                            if ($resultado3->actividad <> null) {
                                // Inserta Boton
                                echo "<div class='inline-container' style='margin: auto; justify-content: space-between; text-align:initial;'>";
                                echo "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado3->idActividades_proyecto . "' href='#' onclick='abrirVentanaActividad(" . $resultado3->idActividades_proyecto . ",0)'>
                                          <div class='icon-container'>
                                              <div class='plus-icon-yellow'></div>
                                          </div>
                                      </a>";
                                echo "<h3 style='margin-right: auto; text-align: initial;'>" . $resultado3->actividad . "</h3>";
                                echo "<div style='width:15%; text-align: -webkit-center;'>";
                                echo    "<h3 style='margin-right: auto;'>" . $resultado3->fechaEntrega . "</h3>";
                                echo "</div>";
                                echo "<div style='width:55%; text-align: -webkit-center; text-align:initial;'>";
                                echo    "<h3 style='margin-right: auto; white-space: pre-wrap;'>" . $resultado3->ubicacion . "</h3>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                                echo "<div class='column column" . $resultado3->idActividades_proyecto . "' style='width: 0%; height: auto;'>";
                                $act = $resultado3->actividad;
                            } else {
                            }
                        }
                    }
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                                      echo "</div>";
                ?>
          </div>
          <?php  } ?>


          <div id="actividades" class="tabcontent">
              <div class="inline-container">
                  <h1>Pending to Complete</h1>
                  <!-- <a href='#'>
                      <div class='flex-container' style='display: flex; justify-content: center;'>
                          <div class='plus-icon-yellow'></div>
                      </div>
                  </a> -->
              </div>
    <?php
                  $pro="";
                  $act="";
                  $name="";

                  if ($_SESSION['idDepartamento'] == 2 || $_SESSION['idDepartamento'] == 1) { // MANUFACTURE ACTIVITY SECTION <----------------------------------
                      while ($resultado = $stmt2->fetch()) {
                          if ($pro == "") {
                              echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              echo "<div class='column p-4' style='width: 20%; height: auto; text-align: right;'>";
                              if ($resultado->isApplication == 1) {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado->nombre . "</h5>";
                              }else {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_coe.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado->nombre . "</h5>";
                              }
                              echo "</div>";
                              echo "<div class='column' style='width: 80%; height: auto;'>";
                              $pro = $resultado->nombre;
                          } elseif ($pro <> $resultado->nombre) {
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra Inline Container
                              echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              echo "<div class='column' style='width: 20%; height: auto; text-align: right;'>";
                              if ($resultado->isApplication == 1) {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado->nombre . "</h5>";
                              }else {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_coe.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado->nombre . "</h5>";
                              }
                              echo "</div>";
                              echo "<div class='column' style='width: 80%; height: auto;'>";
                              $pro = $resultado->nombre;
                              $act="";
                          }
                          if ($act == "") {
                              if ($resultado->fechaRequerida <= date("Y-m-d") && $resultado->fechaRequerida <> "" && $resultado->idStatus != 5 && $resultado->idStatus != 6) {
                                  echo "<div class='inline-container'  id='act" . $resultado->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: #F1948A; margin: auto;'>";
                              } else {
                                  echo "<div class='inline-container'  id='act" . $resultado->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              }

                              echo "<div class='column' style='width: 70%; height: auto; text-align: right;'>";
                              if ($resultado->actividad <> null) {
                                  // Inserta Boton
                                  echo "<div class='inline-container'  id='act" . $resultado->idActividades_proyecto . "' style='margin: auto; justify-content: space-between;'>";
                                  echo "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado->idActividades_proyecto . "' href='#' onclick='abrirVentanaActividad(" . $resultado->idActividades_proyecto . ",0)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon-green'></div>
                                            </div>
                                        </a>";
                                  echo "<h5 style='margin-right: auto; text-align: initial;'>" . $resultado->tipo . " - " . $resultado->actividad . "</h5>";
                                  echo "<div style='width:15%; text-align: -webkit-center;'>";
                                  if ($resultado->idStatus == 6) {
                                    echo    "<h5 style='margin-right: auto;'>HOLD</h5>";
                                  } else {
                                    echo    "<h5 style='margin-right: auto;'>" . $resultado->fechaRequerida . "</h5>";
                                  }
                                  echo "</div>";
                                  echo "<a class='btn-abrir add-resource' style='margin: 0px 20px;' actividad='" . $resultado->idActividades_proyecto . "' href='#' onclick='abrirVentanaRecursos()'>
                                                  <div class='icon-container'>
                                                      <div class='plus-icon-yellow'></div>
                                                  </div>
                                              </a>";
                                  echo "</div>";
                                  echo "</div>";
                                  echo "<div class='column column" . $resultado->idActividades_proyecto . "' style='width: 30%; height: auto;'>";
                                  $act = $resultado->actividad;
                              } else {
                              }
                          } elseif ($act <> $resultado->actividad) {
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra Inline Container
                              if ($resultado->fechaRequerida <= date("Y-m-d") && $resultado->fechaRequerida <> "" && $resultado->idStatus != 5 && $resultado->idStatus != 6) {
                                  echo "<div class='inline-container'  id='act" . $resultado->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: #F1948A; margin: auto;'>";
                              } else {
                                  echo "<div class='inline-container'  id='act" . $resultado->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              }
                              echo "<div class='column' style='width: 70%; height: auto; text-align: right;'>";
                              if ($resultado->actividad <> null) {
                                  // Inserta Boton
                                  echo "<div class='inline-container' style='margin: auto; justify-content: space-between;'>";
                                  echo "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado->idActividades_proyecto . "' href='#' onclick='abrirVentanaActividad(" . $resultado->idActividades_proyecto . ",0)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon-green'></div>
                                            </div>
                                        </a>";
                                  echo "<h5 style='margin-right: auto; text-align: initial;'>" . $resultado->tipo . " - " . $resultado->actividad . "</h5>";
                                  echo "<div style='width:15%; text-align: -webkit-center;'>";
                                  if ($resultado->idStatus == 6) {
                                    echo    "<h5 style='margin-right: auto;'>HOLD</h5>";
                                  } else {
                                    echo    "<h5 style='margin-right: auto;'>" . $resultado->fechaRequerida . "</h5>";
                                  }
                                  echo "</div>";
                                  echo "<a class='btn-abrir add-resource' style='margin: 0px 20px;' actividad='" . $resultado->idActividades_proyecto . "' href='#' onclick='abrirVentanaRecursos()'>
                                                  <div class='icon-container'>
                                                      <div class='plus-icon-yellow'></div>
                                                  </div>
                                              </a>";
                                  echo "</div>";
                                  echo "</div>";
                                  echo "<div class='column column" . $resultado->idActividades_proyecto . "' style='width: 30%; height: auto;'>";
                                  $act = $resultado->actividad;
                              } else {
                              }
                          }
                          if ($resultado->enombre <> null) {
                              if ($resultado->enombre != $name) {
                                  echo "<div class='col text-center p-2'><h5><a href='#' onclick='abrirModalAsinacionHoras(" . $resultado->idActividades_proyecto . "," . $resultado->idEmpleado . ")'>" . $resultado->enombre . "</a></h5></div>";
                                  $name = $resultado->enombre;
                              }
                          } elseif ($resultado->actividad == null) {
                              echo "<div class='col text-center p-2'><h5>SIN ACTIVIDADES ASIGNADAS</h5></div>";
                          } else {
                              echo "<div class='col text-center p-2'><h5>SIN RECURSOS ASIGNADOS</h5></div>";
                          }
                      }
                      echo "</div>";
                      echo "</div>";
                      echo "</div>";
                      echo "</div>";
                  } elseif ($_SESSION['idDepartamento'] == 9 OR $_SESSION['idDepartamento'] == 10) { // APPLICATION ACTIVITY SECTION <----------------------------------
                      while ($resultado = $stmt2->fetch()) {
                          if ($pro == "") {
                              echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              echo "<div class='column p-4' style='width: 20%; height: auto; text-align: right;'>";
                              if ($resultado->isApplication == 1) {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>";
                                  echo "<div>" . $resultado->projectID . "</div><div>" . $resultado->descripcion . "</div></h5>";
                              }else {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_coe.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>";
                                  echo "<div>" . $resultado->projectID . "</div><div>" . $resultado->descripcion . "</div></h5>";
                              }
                              echo "</div>";
                              echo "<div class='column' style='width: 80%; height: auto;'>";
                              $pro = $resultado->nombre;
                          } elseif ($pro <> $resultado->nombre) {
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra Inline Container
                              echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              echo "<div class='column p-4' style='width: 20%; height: auto; text-align: right;'>";
                              if ($resultado->isApplication == 1) {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>";
                                  echo "<div>" . $resultado->projectID . "</div><div>" . $resultado->descripcion . "</div></h5>";
                              }else {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_coe.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>";
                                  echo "<div>" . $resultado->projectID . "</div><div>" . $resultado->descripcion . "</div></h5>";
                              }
                              echo "</div>";
                              echo "<div class='column' style='width: 80%; height: auto;'>";
                              $pro = $resultado->nombre;
                              $act="";
                          }
                          if ($act == "") {
                              if ($resultado->fechaRequerida <= date("Y-m-d") && $resultado->fechaRequerida <> "" && $resultado->idStatus != 5 && $resultado->idStatus != 6) {
                                  echo "<div class='inline-container'  id='act" . $resultado->idRecursosAdicionales . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: #F1948A; margin: auto;'>";
                              } else {
                                  echo "<div class='inline-container'  id='act" . $resultado->idRecursosAdicionales . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              }

                              echo "<div class='column' style='width: 70%; height: auto; text-align: right;'>";
                              if ($resultado->actividad <> null) {
                                  // Inserta Boton
                                  echo "<div class='inline-container'  id='act" . $resultado->idRecursosAdicionales . "' style='margin: auto; justify-content: space-between;'>";
                                  echo "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado->idRecursosAdicionales . "' href='#' onclick='abrirVentanaActividad(" . $resultado->idRecursosAdicionales . ",2)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon-green'></div>
                                            </div>
                                        </a>";
                                  echo "<h5 style='margin-right: auto; text-align: initial;'>" . $resultado->tipo . " - " . $resultado->actividad . "</h5>";
                                  echo "<div style='width:15%; text-align: -webkit-center;'>";
                                  if ($resultado->idStatus == 6) {
                                    echo    "<h5 style='margin-right: auto;'>HOLD</h5>";
                                  } else {
                                    echo    "<h5 style='margin-right: auto;'>" . $resultado->fechaRequerida . "</h5>";
                                  }
                                  echo "</div>";
                                  echo "</div>";
                                  echo "</div>";
                                  echo "<div class='column column" . $resultado->idRecursosAdicionales . "' style='width: 30%; height: auto;'>";
                                  $act = $resultado->actividad;
                              }
                          } elseif ($act <> $resultado->actividad) {
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra Inline Container
                              if ($resultado->fechaRequerida <= date("Y-m-d") && $resultado->fechaRequerida <> "" && $resultado->idStatus != 5 && $resultado->idStatus != 6) {
                                  echo "<div class='inline-container'  id='act" . $resultado->idRecursosAdicionales . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: #F1948A; margin: auto;'>";
                              } else {
                                  echo "<div class='inline-container'  id='act" . $resultado->idRecursosAdicionales . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              }
                              echo "<div class='column' style='width: 70%; height: auto; text-align: right;'>";
                              if ($resultado->actividad <> null) {
                                  // Inserta Boton
                                  echo "<div class='inline-container' style='margin: auto; justify-content: space-between;'>";
                                  echo "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado->idRecursosAdicionales . "' href='#' onclick='abrirVentanaActividad(" . $resultado->idRecursosAdicionales . ",2)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon-green'></div>
                                            </div>
                                        </a>";
                                  echo "<h5 style='margin-right: auto; text-align: initial;'>" . $resultado->tipo . " - " . $resultado->actividad . "</h5>";
                                  echo "<div style='width:15%; text-align: -webkit-center;'>";
                                  if ($resultado->idStatus == 6) {
                                    echo    "<h5 style='margin-right: auto;'>HOLD</h5>";
                                  } else {
                                    echo    "<h5 style='margin-right: auto;'>" . $resultado->fechaRequerida . "</h5>";
                                  }
                                  echo "</div>";
                                  echo "</div>";
                                  echo "</div>";
                                  echo "<div class='column column" . $resultado->idRecursosAdicionales . "' style='width: 30%; height: auto;'>";
                                  $act = $resultado->actividad;
                              }
                          }
                      }
                      echo "</div>";
                      echo "</div>";
                      echo "</div>";
                      echo "</div>";
                  } else {  // GENERAL DEPARTMENT ACTIVITY SECTION <----------------------------------
                      while ($resultado = $stmt2->fetch()) {
                          if ($pro == "") {
                              echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              echo "<div class='column' style='width: 20%; height: auto; text-align: right;'>";
                              echo "<h3><a href='/proyecto_detalle.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado->nombre . "</h3>";
                              echo "</div>";
                              echo "<div class='column' style='width: 80%; height: auto;'>";
                              $pro = $resultado->nombre;
                          } elseif ($pro <> $resultado->nombre) {
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra Inline Container
                              echo "<div class='inline-container' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              echo "<div class='column' style='width: 20%; height: auto; text-align: right;'>";
                              if ($resultado->isApplication == 1) {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado->nombre . "</h5>";
                              }else {
                                  echo "<h5><a href='/pages/proyecto_detalle/proyecto_detalle_coe.php?id=".$resultado->idProyecto."&back=perfil' style='a:visited {color:#00FF00}'>" . $resultado->nombre . "</h5>";
                              }
                              echo "</div>";
                              echo "<div class='column' style='width: 80%; height: auto;'>";
                              $pro = $resultado->nombre;
                              $act="";
                          }
                          if ($act == "") {
                              if ($resultado->actividad <> null) {
                                  // Inserta Boton
                                  if ($resultado->fechaRequerida <= date("Y-m-d") && $resultado->fechaRequerida <> "" && $resultado->idStatus != 5 && $resultado->idStatus != 6) {
                                      echo "<div class='inline-container'  id='act" . $resultado->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: #F1948A; margin: auto;'>";
                                  } else {
                                      echo "<div class='inline-container'  id='act" . $resultado->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                                  }
                                  echo "<div class='column' style='width: 70%; height: auto; text-align: right;'>";
                                  echo "<div class='inline-container' style='margin: auto; justify-content: space-between;'>";
                                  echo "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado->idActividades_proyecto . "' href='#' onclick='abrirVentanaActividad(" . $resultado->idActividades_proyecto . ",0)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon-green'></div>
                                            </div>
                                        </a>";
                                  echo "<h3 style='margin-right: auto; text-align: initial;'>" . $resultado->tipo . " - " . $resultado->actividad . "</h3>";
                                  echo "<div style='width:15%; text-align: -webkit-center;'>";
                                  if ($resultado->idStatus == 6) {
                                    echo    "<h3 style='margin-right: auto;'>HOLD</h3>";
                                  } else {
                                    echo    "<h3 style='margin-right: auto;'>" . $resultado->fechaRequerida . "</h3>";
                                  }
                                  echo "</div>";
                                  // echo "<div style='width:55%; text-align: -webkit-center; text-align:initial;'>";
                                  // echo    "<h3 style='margin-right: auto;'>" . $resultado->ubicacion . "</h3>";
                                  // echo "</div>";
                                  echo "</div>";


                                  $act = $resultado->actividad;
                              } else {
                              }
                          } elseif ($act <> $resultado->actividad) {
                              echo "</div>"; // Cierra columna
                              echo "</div>"; // Cierra Inline Container
                              if ($resultado->fechaRequerida <= date("Y-m-d") && $resultado->fechaRequerida <> "" && $resultado->idStatus != 5 && $resultado->idStatus != 6) {
                                  echo "<div class='inline-container'  id='act" . $resultado->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: #F1948A; margin: auto;'>";
                              } else {
                                  echo "<div class='inline-container'  id='act" . $resultado->idActividades_proyecto . "' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>";
                              }

                              echo "<div class='column' style='width: 70%; height: auto; text-align: right;'>";
                              if ($resultado->actividad <> null) {
                                  // Inserta Boton
                                  echo "<div class='inline-container' style='margin: auto; justify-content: space-between;'>";
                                  echo "<a class='btn-abrir completar' style='margin: 0px 20px;' actividad='" . $resultado->idActividades_proyecto . "' href='#' onclick='abrirVentanaActividad(" . $resultado->idActividades_proyecto . ",0)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon-green'></div>
                                            </div>
                                        </a>";
                                  echo "<h3 style='margin-right: auto; text-align: initial;'>" . $resultado->tipo . " - " . $resultado->actividad . "</h3>";
                                  echo "<div style='width:15%; text-align: -webkit-center;'>";
                                  if ($resultado->idStatus == 6) {
                                    echo    "<h3 style='margin-right: auto;'>HOLD</h3>";
                                  } else {
                                    echo    "<h3 style='margin-right: auto;'>" . $resultado->fechaRequerida . "</h3>";
                                  }
                                  echo "</div>";
                                  echo "</div>";
                                  echo "</div>";
                                  echo "<div class='column column" . $resultado->idActividades_proyecto . "' style='width: 30%; height: auto;'>";
                                  $act = $resultado->actividad;
                              } else {
                              }
                          }
                      }
                      echo "</div>";
                      echo "</div>";
                      echo "</div>";
                      echo "</div>";
                  } ?>
              </div>
          </div>

          <div id="toSupport" class="tabcontent">
              <div class="inline-container">
                  <h1>Activities to support</h1>
              </div>
              <?php while ($resultado = $stmActividadUsuariosAsignados->fetch()) { ?>

                  <div class='inline-container'  id='act<?php echo $resultado->idUsuarioAsignado;?>' style='margin-top: 5px; border-radius: 10px; border-style:solid; border-width:1px; background-color: white; margin: auto;'>
                      <div class='inline-container' style='margin: auto; justify-content: space-between;'>
                          <div class='column' style='width: 70%; height: auto; text-align: right;'>
                              <h3><a href='/proyecto_detalle.php?id=<?php echo $resultado->idProyecto; ?>&back=perfil' style='a:visited {color:#00FF00}'><?php echo  $resultado->pNombre; ?></a></h3>
                          </div>
                          <div class='column' style='width: 70%; height: auto; text-align: right;'>
                              <h3 style='margin-right: auto; text-align: initial;'><?php echo $resultado->aNombre; ?></h3>
                          </div>
                          <div class='column' style='width: 70%; height: auto; text-align: right;'>
                              <p style='margin-right: auto; text-align: initial;'><?php echo $resultado->motivoReq; ?></p>
                          </div>
                          <div class='column' style='width: 70%; height: auto; text-align: right;'>
                              <p style='margin-right: auto; text-align: initial;'>on <?php echo $resultado->inicio; ?></p>
                          </div>
                          <div class='column' style='width: 70%; height: auto; text-align: right;'>
                              <a class='btn-abrir completar' style='margin: 0px 20px;' actividad='<?php echo $resultado->idUsuarioAsignado; ?>' href='#' onclick='abrirVentanaAsignacionUsuarios(<?php echo $resultado->idUsuarioAsignado; ?>)'>
                                  <div class='icon-container'>
                                      <div class='plus-icon-green'></div>
                                  </div>
                              </a>
                          </div>
                      </div>
                  </div>
          <?php } ?>
          </div>

          <div id="stageValidation" class="tabcontent">
              <!-- <h1 class="col-12 text-center danger">TESTING BY DEVELOPER, PLEASE DONT MOVE THIS SECTION!!!</h1> -->

              <div class="card shadow p-4 bg-body rounded-3">
                  <div class="inline-container">
                      <h1 class="mb-3">Stage Validation Section</h1>
                  </div>

                  <?php while ($resultado = $stmtEtapa->fetch()) { ?>
                  <div class="row p-2 mb-3">
                      <div class="card shadow p-4 bg-body rounded-3">
                          <div class="row">
                              <div class="col-4 text-center">
                                  <h4 class="text-primary"><?php echo $resultado->cNombre; ?></h4>
                                  <h5 class="text-dark"><?php echo $resultado->pNombre; ?></h5>
                                  <h5 class="text-secondary"><?php echo $resultado->descripcion; ?></h5>
                              </div>

                              <div class="col-4">
                                  <h3 class="text-info text-center border-bottom pl-4 pb-3"><?php echo $resultado->eNombre; ?></h3>
                                  <h5 class="text-dark text-center"><?php echo $resultado->eDescripcion; ?></h5>
                              </div>

                              <div class="col-4 d-flex aligns-items-center justify-content-center">
                                  <a href="/proyecto_detalle.php?id=<?php echo $resultado->idProyecto; ?>&back=perfil" class="btn btn-primary w-100" style="align-self: center;">Review</a>
                              </div>
                          </div>
                      </div>
                  </div>
                  <?php } ?>
              </div>
          </div>

          <!-- VENTANAS MODALES -->
          <div class="back-modal">
              <div class="contenido-modal" style="height: 400px;">
              </div>
          </div>

          <span class="alerta ocultar">
              <span class="msg">This is a warning</span>
                  <span class='icon-container'>
                      <div id="cerrar_alerta" class='cross-icon'></div>
                  </span>
          </span>

          <script src="js/funciones.js"></script>

          <?php
                if ($_SESSION['first'] == true) {
                    echo "<script>mostrarAlerta('success','Welcome " . $_SESSION["nombre"] . ".');</script>";
                    $_SESSION['first'] = false;
                }
          ?>

          <script type="text/javascript">
              function abrirVentanaContrasena() {
                  event.preventDefault();
                  $('.contenido-modal').css('height','400px');
                  $('.contenido-modal').css('width','460px');
                  // $('.contenido-modal').css('text-align','-webkit-center');
                  $('.contenido-modal').css('padding','0px');
                  $('.contenido-modal').html("<div class='flex-container' style='margin-top: 60px;'>" +
                                                  "<!-- Titulo -->" +
                                                  "<h1 id='tittle'>Password Change</h1>" +
                                                  "<a class='btn-cerrar' onclick='cerrarModal2()'>" +
                                                      "<div class='icon-container'>" +
                                                          "<div class='cross-icon'></div>" +
                                                      "</div>" +
                                                  "</a>" +
                                                  "<!-- Formulario -->" +
                                                  "<form id='form_empleados' action='' onsubmit='return modificarContrasena()'>" +
                                                      "<div class='input-field'>" +
                                                          "<label for='contraActual'>Current Password:</label>" +
                                                          "<input name='contraActual' type='text' id='contraActual' maxlength='30' value='' required>" +
                                                      "</div>" +
                                                      "<div class='input-field'>" +
                                                          "<label for='nuevaContra'>New Password:</label>" +
                                                          "<input name='nuevaContra' type='text' id='nuevaContra' maxlength='30' value='' required>" +
                                                      "</div>" +
                                                      "<!-- Button Submit -->" +
                                                      "<input type='submit' id='btnContra' value='Change'>" +
                                                  "</form>" +
                                              "</div>");
                  abrirModal();
              }

              function modificarContrasena() {
                  event.preventDefault();
                  var contraActual = $('#contraActual').val();
                  var nuevaContra = $('#nuevaContra').val();
                  $.ajax({
                      url: 'js/ajax.php',
                      type: 'POST',
                      async: true,
                      data: {
                        accion: 'cambiarContrasena',
                        contraActual: contraActual,
                        nuevaContra: nuevaContra
                      },
                      success: function(response) {
                          // console.log(response);
                          if (!response != "error") {
                              if (response == "success") {
                                  cerrarModal2();
                                  mostrarAlerta('success','Password Changed.');
                              } else {
                                  mostrarAlerta('warning','Wrong Current Password.');
                                  // console.log(response);
                              }
                          }
                      },
                      error: function(error) {
                        console.log(error);
                      }
                  });
                  // cerrarModal2();
                  return false;
              }

              function abrirVentanaRecursos() {

                  event.preventDefault();
                  $('.contenido-modal').css('height','600px');
                  $('.contenido-modal').css('width','480px');
                  $('.contenido-modal').css('text-align','-webkit-center');
                  $('.contenido-modal').css('padding','40px');
                  $('.contenido-modal').html("<h1>Activity</h1>" +
                                                "<a class='btn-cerrar' onclick='cerrarModal()'>" +
                                                    "<div class='icon-container'>" +
                                                        "<div class='cross-icon'></div>" +
                                                    "</div>" +
                                                "</a>" +
                                                "<div class=''>" +
                                                    "<div class='input-field'>" +
                                                      "<label for='idActividad'>Activity ID</label>" +
                                                      "<input name='idActividad' id='idActividad' style='text-align:center; font-weight:bold; background-color: AliceBlue;' type='text' value='' disabled>" +
                                                    "</div>" +
                                                    "<div class='input-field'>" +
                                                      "<label for='nombre'>Name</label>" +
                                                      "<input name='nombre' id='nombre' type='text' style='text-align:center; font-weight:bold; background-color: AliceBlue;' value='' disabled>" +
                                                    "</div>" +
                                                "</div>" +
                                                "<hr style='width:30%;margin: 30px 0px; text-align:center;margin-left:0'>" +
                                                "<div class=''>" +
                                                  "<h1>Resource Allocation</h1>" +
                                                  "<form id='form_empleados' onsubmit='registrarRecurso(event)' method='post'>" +
                                                      "<!--Selector en base a consulta BD-->" +
                                                      "<div class='input-field'>" +
                                                        "<label for='recurso'>Resources</label>" +
                                                        "<div class=''>" +
                                                          "<div class='inline-container'>" +
                                                            "<select name='recurso' id='recurso' required onchange='pedirFecha()'>" +
                                                              "<option disabled selected value> -- Select -- </option>" +
                                                            "</select>" +
                                                          "</div>" +
                                                        "</div>" +
                                                      "</div>" +
                                                      "<div class='input-field' id='fecha'>" +

                                                      "</div>" +
                                                      "<div class='input-field' id='workload'>" +

                                                      "</div>" +
                                                      "<div class='input-field' id='hours'>" +

                                                      "</div>" +
                                                      "<!--Boton Asignar Recursos-->" +
                                                      "<input name='btnAsignarRecursos' type='submit' value='Assign'>" +
                                                  "</form>" +
                                                "</div>" +
                                              "</div>");
                  abrirModal();
              }

              function abrirModalAsinacionHoras(idActividad, idEmpleado) {
                  event.preventDefault();

                  var proy = idActividad;
                  var emp = idEmpleado;
                  var chain = "modals/perfil_asignar_recursos_modal.php?idActividades_proyecto=" + proy + "&idEmpleado=" + emp;

                  $('.contenido-modal').css('width','550px');
                  $('.contenido-modal').css('height','700px');
                  $('.contenido-modal').css('text-align','-webkit-center');
                  $('.contenido-modal').css('padding','40px');
                  $('.contenido-modal').load(chain);
                  abrirModal();
              }

              function pedirFecha() {
                  event.preventDefault();
                  $('#fecha').html("<label for='fechaInicio'>Date</label>" +
                                  "<input type='date' id='fechaInicio' name='fechaInicio' value='' min='2021-01-01' required onchange='mostrarDisponibilidad()'>");
                  $('#workload').html("");
                  $('#hours').html("");
              }

              function mostrarDisponibilidad() {
                  event.preventDefault();

                  var idRecurso = $('#recurso').val();
                  var selDate = $('#fechaInicio').val();

                  $.ajax({
                      url: 'js/ajax.php',
                      type: 'POST',
                      async: true,
                      data: {
                        accion: 'mostrarDisponibilidad',
                        idRecurso: idRecurso,
                        selDate: selDate
                      },
                      success: function(response) {
                          // console.log(response);
                          if (!response != "error") {
                              var info = JSON.parse(response);
                              // console.log(info);
                              if (typeof info.result.horas != 'undefined') {
                                  $("#workload").html("<h4 style='color:red;'>Resource Workload: " + info.result.horas + "</h4>");
                              }else {
                                  $('#workload').html("");
                              }
                              $('#hours').html("<label for='horas'>Hours</label>" +
                                              "<input name='horas' id='horas' type='number' min='0' value='0' step='0.5' required>");
                          }
                      },
                      error: function(error) {
                        console.log(error);
                      }
                  });
                  // cerrarModal2();
                  return false;

              }

              // TIPOS DE VALIDACION
              // -1     N/A
              // 0      Sin Status
              // 1      Completado
              // 2      Aprobado
              // 3      Rechazado

              function abrirVentanaActividad(act, qual) {
                  event.preventDefault();
                  let options;
                  let action = 'completarActividad()';

                  if (qual == 1) {
                      options = "<option value='2'>Approved</option>" +
                                "<option value='3'>Rejected</option>";
                  } else if (qual == 2) {
                      options = "<option value='1'>Completed</option>";
                      action = 'completarActividadAdicional()';
                  } else {
                      options = "<option value='1'>Completed</option>" +
                               "<option value='-1'>N/A</option>" +
                               "<option value='4'>Assign Checker</option>";
                  }
                  $('.contenido-modal').css('height', '300px');
                  $('.contenido-modal').css('width','480px');
                  $('.contenido-modal').css('text-align','-webkit-center');
                  $('.contenido-modal').css('padding','40px');
                  $('.contenido-modal').html("<h1>Finish Activity</h1>" +
                                                "<a class='btn-cerrar' onclick='cerrarModal()'>" +
                                                    "<div class='icon-container'>" +
                                                        "<div class='cross-icon'></div>" +
                                                    "</div>" +
                                                "</a>" +

                                                "<form id='form_empleados' onsubmit='" + action + "' method='post'>" +
                                                    "<input type='hidden' id='idActividades_proyecto' name='idActividades_proyecto' value='" + act + "'>" +
                                                    "<div class='input-field'>" +
                                                      "<label for='recurso'>Activity Status</label>" +
                                                      "<div class=''>" +
                                                        "<div class='inline-container'>" +
                                                          "<select name='statusActividad' id='statusActividad' required onchange='pedirMotivo()'>" +
                                                              "<option disabled selected value> -- Select -- </option>" +
                                                                options +
                                                          "</select>" +
                                                        "</div>" +
                                                      "</div>" +
                                                    "</div>" +
                                                    "<div class='input-field' id='notesSection'>" +

                                                    "</div>" +
                                                    "<!--Boton Asignar Recursos-->" +
                                                    "<input name='btnCompletarActividad' type='submit' value='Complete'>" +
                                                "</form>");
                  abrirModal();
              }

              function abrirVentanaAsignacionUsuarios(act) {
                  event.preventDefault();
                  $('.contenido-modal').css('height','400px');
                  $('.contenido-modal').css('width','480px');
                  $('.contenido-modal').css('text-align','-webkit-center');
                  $('.contenido-modal').css('padding','40px');
                  $('.contenido-modal').html("<h1>Support Activity</h1>" +
                                                "<a class='btn-cerrar' onclick='cerrarModal()'>" +
                                                    "<div class='icon-container'>" +
                                                        "<div class='cross-icon'></div>" +
                                                    "</div>" +
                                                "</a>" +

                                                "<form id='form_empleados' onsubmit='completarAsignacionUsuarios()' method='post'>" +
                                                    "<input type='hidden' id='idUsuarioAsignado' name='idUsuarioAsignado' value='" + act + "'>" +
                                                    "<div class='input-field' id='notesSection'>" +
                                                        "<label for='notas'>Comment</label>" +
                                                        "<textarea style='width: 100%;' rows='4' cols='50' id='notas' name='notas' required></textarea>" +
                                                    "</div>" +
                                                    "<!--Boton Asignar Recursos-->" +
                                                    "<input name='btnCompletarAsignacionUsuarios' type='submit' value='Complete'>" +
                                                "</form>");
                  abrirModal();
              }

              function pedirMotivo() {
                  event.preventDefault();
                  var selected = $('#statusActividad').val();
                  console.log(selected);
                  switch (selected) {
                    case '2':
                        $('#notesSection').html("");
                        break;
                    case '-1':
                    case '3':
                        $('#notesSection').html("<label for='motivo'>Reason</label>" +
                                                "<textarea style='width: 100%;' rows='4' cols='50' id='motivo' name='motivo' required></textarea>");
                        break;
                    case '1':
                        $('.contenido-modal').css('height', '360px');
                        $('#notesSection').html("<label for='path'>Path</label>" +
                                                "<input type='text' style='width: 100%;' id='path' name='path' placeholder='M:\\Projects\\PDP\\project name' required></input>");
                        break;
                    case '4':
                        $('#notesSection').html("<div class='inline-container'>" +
                                                  "<select name='aprobadores' id='aprobadores' required>" +

                                                  "</select>" +
                                                "</div>" +
                                                "<label for='reqReason'>Reason</label>" +
                                                "<textarea style='width: 100%;' rows='4' cols='50' id='reqReason' name='reqReason' placeholder='Type a description for activity requested help.' required></textarea>");
                        actualizarAprobadores();
                        break;
                    default:
                        break;
                  }
              }

              function actualizarAprobadores() {
                  var idActividades_proyecto = $('#idActividades_proyecto').val();
                  $.ajax({
                    type:"POST",
                    url:"js/ajax.php",
                    async: true,
                    data: {
                      accion: 'actualizarAprobadores',
                      idActividades_proyecto: idActividades_proyecto
                    },
                    success:function(result){
                      var mySelect = $('#aprobadores');
                      mySelect.empty();
                      mySelect.append(result);
                    }
                  });
              }

              function completarActividad() {
                  event.preventDefault();
                  var idActividades_proyecto = $('#idActividades_proyecto').val();
                  var statusActividad = $('#statusActividad').val();

                  if ($('#reqReason').length) {
                      asignarAprobador();
                      return;
                  }

                  if ($('#motivo').length) {
                      var path;
                      var motivo = $('#motivo').val();
                  } else {
                      var path = $('#path').val();;
                      var motivo;
                  }

                  $.ajax({
                      url: 'js/ajax.php',
                      type: 'POST',
                      async: true,
                      data: {
                        accion: 'validarActividad',
                        idActividades_proyecto: idActividades_proyecto,
                        statusActividad: statusActividad,
                      },
                      success: function(response) {
                          // console.log(response);
                          if (!response != "error") {
                              if (response == "pendienteAprobacion") {
                                  mostrarAlerta("warning","There are users pending to approve the activity.")
                                  return;
                              }else if (response == "noRecursos") {
                                  if (confirm("The activity has no resources assigned. Complete anyway?")){

                                  }else {
                                      return;
                                  }
                              }
                              $.ajax({
                                  url: 'js/ajax.php',
                                  type: 'POST',
                                  async: true,
                                  data: {
                                    accion: 'completarActividad',
                                    idActividades_proyecto: idActividades_proyecto,
                                    statusActividad: statusActividad,
                                    notas: motivo,
                                    path: path
                                  },
                                  success: function(response) {
                                      // console.log(response);
                                      if (!response != "error") {
                                          var info = JSON.parse(response);
                                          // console.log(info);
                                          $("#act" + info.result.idActividades_proyecto).remove();
                                          cerrarModal2();
                                          mostrarAlerta('success','Activity Completed.');
                                      }

                                  },
                                  error: function(error) {
                                    console.log(error);
                                  }
                              });
                          }

                      },
                      error: function(error) {
                        console.log(error);
                      }
                  });
                  // cerrarModal2();
                  return false;
              }

              function completarActividadAdicional() {
                  event.preventDefault();
                  let idRecursosAdicionales = $('#idActividades_proyecto').val();
                  let statusActividad = $('#statusActividad').val();

                  let path = $('#path').val();;
                  let motivo;

                  $.ajax({
                      url: 'js/ajax.php',
                      type: 'POST',
                      async: true,
                      data: {
                          accion: 'completarActividadAdicional',
                          idRecursosAdicionales: idRecursosAdicionales,
                          statusActividad: statusActividad,
                          notas: motivo,
                          path: path
                      },
                      success: function(response) {
                          // console.log(response);
                          if (!response != "error") {
                              var info = JSON.parse(response);
                              console.log(info);
                              $("#act" + info.result.idRecursosAdicionales).remove();
                              cerrarModal2();
                              mostrarAlerta('success','Activity Completed.');
                          }
                      },
                      error: function(error) {
                          console.log(error);
                      }
                  });
                  // cerrarModal2();
                  return false;
              }

              function completarAsignacionUsuarios() {
                  event.preventDefault();
                  var idUsuarioAsignado = $('#idUsuarioAsignado').val();
                  var notas = $('#notas').val();

                  $.ajax({
                      url: 'js/ajax.php',
                      type: 'POST',
                      async: true,
                      data: {
                          accion: 'completarAsignacionUsuarios',
                          idUsuarioAsignado: idUsuarioAsignado,
                          notas: notas
                      },
                      success: function(response) {
                          $("#act" + idUsuarioAsignado).remove();
                          cerrarModal2();
                          mostrarAlerta('success','Activity Completed.');
                      },
                      error: function(error) {
                          console.log(error);
                      }
                  });
              }

              function asignarAprobador() {
                  var idActividades_proyecto = $('#idActividades_proyecto').val();
                  var aprobador = $('#aprobadores').val();
                  var motivoReq = $('#reqReason').val();

                  $.ajax({
                      url: 'js/ajax.php',
                      type: 'POST',
                      async: true,
                      data: {
                        accion: 'asignarAprobador',
                        idActividades_proyecto: idActividades_proyecto,
                        idUsuarioAsignado: aprobador,
                        motivoReq: motivoReq
                      },
                      success: function(response) {
                          // console.log(response);
                          if (!response != "error") {
                              var info = JSON.parse(response);
                              console.log(info);
                              cerrarModal2();
                              mostrarAlerta('success','Aprobador asignado correctamente.');
                          }

                      },
                      error: function(error) {
                        console.log(error);
                      }
                  });
                  // cerrarModal2();
                  return false;
              }
          </script>

          <script type="text/javascript">
              $(document).ready(function() {
                  $('#cerrar_alerta').click(function() {
                      $('.alerta').removeClass('mostrar');
                      $('.alerta').addClass('ocultar');
                  });
              });
          </script>

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
              document.getElementById("defaultOpen").click();
          </script>

          <?php include "inc/footer.html"; ?>
