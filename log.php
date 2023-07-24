<?php
  include "inc/conexion.php";
  include "inc/headerBoostrap.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(28, $_SESSION["permisos"])) {
          $message = "Unauthorized User.";
          echo "<script>
                  alert('$message');
                  window.location.href='index.php';
              </script>";
          die();
      }
  ?>
      <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <?php

      $stmt = $dbh->prepare("SELECT proyecto.idProyecto, projectID, proyecto.nombre AS pnombre, ensambles.numParte, ensambles.cantReq, date(fechaReqCliente) AS fechaReqCliente, cliente.nombreCliente AS cNombre,
                                (SELECT proyecto_notas.nota FROM proyecto_notas WHERE proyecto_notas.idProyecto = proyecto.idProyecto ORDER BY idProyectoNota DESC LIMIT 1) AS nota,
                                MONTH((SELECT proyecto_notas.fechaCrea FROM proyecto_notas WHERE proyecto_notas.idProyecto = proyecto.idProyecto ORDER BY idProyectoNota DESC LIMIT 1)) AS notaMonth,
                                DAY((SELECT proyecto_notas.fechaCrea FROM proyecto_notas WHERE proyecto_notas.idProyecto = proyecto.idProyecto ORDER BY idProyectoNota DESC LIMIT 1)) AS notaDay,
                                FORMAT((SELECT COUNT(idActividades_proyecto)
                                 FROM actividades_proyecto
                                 WHERE idProyecto = proyecto.idProyecto AND completado <> 0) /
                                      (SELECT COUNT(idActividades_proyecto)
                                       FROM actividades_proyecto
                                       WHERE idProyecto = proyecto.idProyecto AND completado <> -1) * 100, 0) AS completed
                            FROM proyecto
                            LEFT JOIN ensambles
                            ON ensambles.idProyecto = proyecto.idProyecto
                            LEFT JOIN proyecto_notas
                            ON proyecto_notas.idProyecto = proyecto.idProyecto
                            INNER JOIN cliente
                            ON proyecto.idCliente = cliente.idCliente
                            INNER JOIN tipoproyecto
                            ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                            INNER JOIN proyecto_categoria
                            ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                            INNER JOIN complejidad
                            ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                            INNER JOIN status
                            ON proyecto.idStatus = status.idStatus
                            WHERE proyecto.idStatus <> 5 AND proyecto.idStatus <> 7 AND proyecto.idStatus <> 6 AND isApplication = 0
                            GROUP BY proyecto.idProyecto, projectID, proyecto.nombre, ensambles.numParte, ensambles.cantReq,
                                DATE(fechaEmbarque)
                            ORDER BY longestETA ASC,
                                     prioridad ASC,
                                     fechaReqCliente ASC");
        $stmt->execute();
        $data = "";

        $stmtApplication = $dbh->prepare("SELECT
                                              proyecto.idProyecto,
                                              projectID,
                                              proyecto.nombre AS pnombre,
                                              ensambles.numParte,
                                              ensambles.cantReq,
                                              date(fechaReqCliente) AS fechaReqCliente,
                                              empleado.nombre AS eNombre,
                                              cliente.nombreCliente AS cNombre,
                                              actividades_proyecto.idActividades_proyecto,
                                              tipoproyecto.horas,
                                              (
                                                  SELECT
                                                      COUNT(*)
                                                  FROM
                                                      actividad_recursos_adicionales AS ara
                                                  WHERE
                                                      ara.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                                                      AND ara.fechaEntrega IS NOT NULL
                                              ) / (
                                                  SELECT
                                                      COUNT(*)
                                                  FROM
                                                      actividad_recursos_adicionales AS ara
                                                  WHERE
                                                      ara.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                                              ) * 100 AS percentCompleted,
                                              (
                                                  SELECT
                                                      proyecto_notas.nota
                                                  FROM
                                                      proyecto_notas
                                                  WHERE
                                                      proyecto_notas.idProyecto = proyecto.idProyecto
                                                  ORDER BY
                                                      idProyectoNota DESC
                                                  LIMIT
                                                      1
                                              ) AS nota,
                                              proyecto.descripcion AS pDescripcion,
                                              MONTH(
                                                  (
                                                      SELECT
                                                          proyecto_notas.fechaCrea
                                                      FROM
                                                          proyecto_notas
                                                      WHERE
                                                          proyecto_notas.idProyecto = proyecto.idProyecto
                                                      ORDER BY
                                                          idProyectoNota DESC
                                                      LIMIT
                                                          1
                                                  )
                                              ) AS notaMonth,
                                              DAY(
                                                  (
                                                      SELECT
                                                          proyecto_notas.fechaCrea
                                                      FROM
                                                          proyecto_notas
                                                      WHERE
                                                          proyecto_notas.idProyecto = proyecto.idProyecto
                                                      ORDER BY
                                                          idProyectoNota DESC
                                                      LIMIT
                                                          1
                                                  )
                                              ) AS notaDay,
                                              FORMAT(
                                                  (
                                                      SELECT
                                                          COUNT(idActividades_proyecto)
                                                      FROM
                                                          actividades_proyecto
                                                      WHERE
                                                          idProyecto = proyecto.idProyecto
                                                          AND completado <> 0
                                                  ) / (
                                                      SELECT
                                                          COUNT(idActividades_proyecto)
                                                      FROM
                                                          actividades_proyecto
                                                      WHERE
                                                          idProyecto = proyecto.idProyecto
                                                          AND completado <> -1
                                                  ) * 100,
                                                  0
                                              ) AS completed
                                          FROM
                                              proyecto
                                              LEFT JOIN ensambles ON ensambles.idProyecto = proyecto.idProyecto
                                              LEFT JOIN proyecto_notas ON proyecto_notas.idProyecto = proyecto.idProyecto
                                              INNER JOIN cliente ON proyecto.idCliente = cliente.idCliente
                                              INNER JOIN tipoproyecto ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                              INNER JOIN proyecto_categoria ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                              INNER JOIN complejidad ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                                              INNER JOIN status ON proyecto.idStatus = status.idStatus
                                              LEFT JOIN usuario ON proyecto.idLiderProyecto = usuario.idUsuario
                                              INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado
                                              LEFT JOIN actividades_proyecto ON proyecto.idProyecto = actividades_proyecto.idProyecto
                                              LEFT JOIN actividad_recursos_adicionales ON actividades_proyecto.idActividades_proyecto = actividad_recursos_adicionales.idActividades_proyecto
                                          WHERE
                                              proyecto.idStatus <> 5
                                              AND proyecto.idStatus <> 7
                                              AND proyecto.idStatus <> 6
                                              AND isApplication = 1
                                              AND tipoproyecto.HORAS > 10
                                          GROUP BY
                                              proyecto.idProyecto,
                                              projectID,
                                              proyecto.nombre,
                                              ensambles.numParte,
                                              ensambles.cantReq,
                                              DATE(fechaEmbarque)
                                          ORDER BY
                                              longestETA ASC,
                                              prioridad ASC,
                                              fechaReqCliente ASC");
          $stmtApplication->execute();
          $stmtSupportApplication = $dbh->prepare("SELECT
                                                      proyecto.idProyecto,
                                                      projectID,
                                                      proyecto.nombre AS pnombre,
                                                      ensambles.numParte,
                                                      ensambles.cantReq,
                                                      date(fechaReqCliente) AS fechaReqCliente,
                                                      empleado.nombre AS eNombre,
                                                      cliente.nombreCliente AS cNombre,
                                                      actividades_proyecto.idActividades_proyecto,
                                                      tipoproyecto.horas,
                                                      (
                                                          SELECT
                                                              COUNT(*)
                                                          FROM
                                                              actividad_recursos_adicionales AS ara
                                                          WHERE
                                                              ara.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                                                              AND ara.fechaEntrega IS NOT NULL
                                                      ) / (
                                                          SELECT
                                                              COUNT(*)
                                                          FROM
                                                              actividad_recursos_adicionales AS ara
                                                          WHERE
                                                              ara.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                                                      ) * 100 AS percentCompleted,
                                                      (
                                                          SELECT
                                                              proyecto_notas.nota
                                                          FROM
                                                              proyecto_notas
                                                          WHERE
                                                              proyecto_notas.idProyecto = proyecto.idProyecto
                                                          ORDER BY
                                                              idProyectoNota DESC
                                                          LIMIT
                                                              1
                                                      ) AS nota,
                                                      proyecto.descripcion AS pDescripcion,
                                                      MONTH(
                                                          (
                                                              SELECT
                                                                  proyecto_notas.fechaCrea
                                                              FROM
                                                                  proyecto_notas
                                                              WHERE
                                                                  proyecto_notas.idProyecto = proyecto.idProyecto
                                                              ORDER BY
                                                                  idProyectoNota DESC
                                                              LIMIT
                                                                  1
                                                          )
                                                      ) AS notaMonth,
                                                      DAY(
                                                          (
                                                              SELECT
                                                                  proyecto_notas.fechaCrea
                                                              FROM
                                                                  proyecto_notas
                                                              WHERE
                                                                  proyecto_notas.idProyecto = proyecto.idProyecto
                                                              ORDER BY
                                                                  idProyectoNota DESC
                                                              LIMIT
                                                                  1
                                                          )
                                                      ) AS notaDay,
                                                      FORMAT(
                                                          (
                                                              SELECT
                                                                  COUNT(*)
                                                              FROM
                                                                  actividad_recursos_adicionales
                                                                  INNER JOIN actividades_proyecto ON actividad_recursos_adicionales.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                                                              WHERE
                                                                  actividades_proyecto.idProyecto = proyecto.idProyecto
                                                                  AND actividad_recursos_adicionales.fechaEntrega IS NOT NULL
                                                          ) / (
                                                              SELECT
                                                                  COUNT(*)
                                                              FROM
                                                                  actividad_recursos_adicionales
                                                                  INNER JOIN actividades_proyecto ON actividad_recursos_adicionales.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                                                              WHERE
                                                                  actividades_proyecto.idProyecto = proyecto.idProyecto
                                                          ) * 100,
                                                          0
                                                      ) AS completed
                                                  FROM
                                                      proyecto
                                                      LEFT JOIN ensambles ON ensambles.idProyecto = proyecto.idProyecto
                                                      LEFT JOIN proyecto_notas ON proyecto_notas.idProyecto = proyecto.idProyecto
                                                      INNER JOIN cliente ON proyecto.idCliente = cliente.idCliente
                                                      INNER JOIN tipoproyecto ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                                      INNER JOIN proyecto_categoria ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                                      INNER JOIN complejidad ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                                                      INNER JOIN status ON proyecto.idStatus = status.idStatus
                                                      LEFT JOIN usuario ON proyecto.idLiderProyecto = usuario.idUsuario
                                                      INNER JOIN empleado ON proyecto.idRespDiseno = empleado.idEmpleado
                                                      LEFT JOIN actividades_proyecto ON proyecto.idProyecto = actividades_proyecto.idProyecto
                                                      LEFT JOIN actividad_recursos_adicionales ON actividades_proyecto.idActividades_proyecto = actividad_recursos_adicionales.idActividades_proyecto
                                                  WHERE
                                                      proyecto.idStatus <> 5
                                                      AND proyecto.idStatus <> 7
                                                      AND proyecto.idStatus <> 6
                                                      AND isApplication = 1
                                                      AND tipoproyecto.HORAS <= 10
                                                  GROUP BY
                                                      proyecto.idProyecto,
                                                      projectID,
                                                      proyecto.nombre,
                                                      ensambles.numParte,
                                                      ensambles.cantReq,
                                                      DATE(fechaEmbarque)
                                                  ORDER BY
                                                      longestETA ASC,
                                                      prioridad ASC,
                                                      fechaReqCliente ASC");
            $stmtSupportApplication->execute();
          $dataApplication = "";
        // while ($resultado = $stmt->fetch()) {
        //     $data .= "['" . $resultado->projectID . "','" . $resultado->pnombre . "','" . $resultado->numParte . "'," .
        //                     $resultado->cantReq . ",'" . $resultado->fechaEmbarque . "','" . trim(preg_replace('/\s+/', ' ', $resultado->nota)) . "'],";
        // }

  } else {
      $message = "Please Log in.";
      echo "<script>
                alert('$message');
                window.location.href='login.php';
            </script>";
      die();
  }
?>

<!DOCTYPE html>


        <!-- <a href='proyecto_alta.php'>
            <div class='icon-container'>
                <div class='plus-icon'></div>
            </div>
        </a> -->
<div class="container">
        <div class="flex-container w-100 mt-3">
            <div class="card shadow p-3 bg-body rounded w-100 m-0">
              <div class="card-header bg-secondary text-center text-white fw-bold">
                  <h3>Projects Open Log</h3>
              </div>
            </div>
        </div>

        <!-- /////////////////////////////////////// BUSCADOR ///////////////////////////////////// -->
        <!-- <form class="" action="proyecto.php" style="width: 100%;" method="post">
          <div class="inline-container" style="margin-bottom: 10px; width: 40%; float: right; text-align: right;" >
            <div class="input-field" style="width: 500px; margin-bottom: 4px;">
                <input name="buscar" type="text" id="buscar" placeholder="Ingrese Texto de Busqueda" onblur="this.value=removeSpaces(this.value);">
            </div>
            <input name="btnBuscarProyecto" class="btn-buscar" style="width: auto; margin-top: 0;" type="submit" value="Buscar">
          </div>
        </form> -->
        <div class="flex-container">
            <div class="card shadow p-3 mb-5 bg-body rounded w-100">
              <div class="card-header bg-primary text-white text-center fw-bold">
                  COE Projects
              </div>
              <div class="icon-container m-3">
                <?php if (in_array(7, $_SESSION["permisos"])) { ?>
                          <a href="/pages/proyecto_alta/proyecto_alta_coe.php">
                            <div class="plus-icon-green"></div>
                          </a>
                <?php } ?>
              </div>
              <table>
                  <thead>
                      <!-- Encabezados de tabla -->
                      <tr>
                          <th>Project ID</th>
                          <!-- <th>Customer</th> -->
                          <th>Name</th>
                          <th>Part #</th>
                          <th>Req Qty</th>
                          <th>Req Date</th>
                          <th>Progress</th>
                          <th>Notes</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
              <?php

                  $data = array();

                  $currentProjectID = "";
                  $currentNumParte = "";
                  $currentQty = "";

                  while ($resultado = $stmt->fetch()) {
                        $data[] = $resultado;
                      // array_push($data,'idProyecto',$resultado->idProyecto);
                      // array_push($data,'projectID',$resultado->projectID);
                      // array_push($data,'pnombre',$resultado->pnombre);
                      // array_push($data,'numParte',$resultado->numParte);
                      // array_push($data,'cantReq',$resultado->cantReq);
                      // array_push($data,'fechaEmbarque',$resultado->fechaEmbarque);
                      // array_push($data,'nota',$resultado->nota);


                      // $data['idProyecto'] = $resultado->idProyecto;
                      // $data['projectID'] = $resultado->projectID;
                      // $data['pnombre'] = $resultado->pnombre;
                      // $data['numParte'] = $resultado->numParte;
                      // $data['cantReq'] = $resultado->cantReq;
                      // $data['fechaEmbarque'] = $resultado->fechaEmbarque;
                      // $data['nota'] = $resultado->nota;
                  }

  // print_r($data);
                  foreach ($data as $value) {
                      if ($value->projectID != $currentProjectID) {
                          echo "<tr id='" . $value->idProyecto . "'>";
                          echo "<td>" . $value->projectID . "</td>";
                          // echo "<td>" . $value->cNombre . "</td>";
                          echo "<td><a href='/pages/proyecto_detalle/proyecto_detalle_coe.php?id=".$value->idProyecto."&back=log' style='a:visited {color:#00FF00}'>" . $value->pnombre . "</a></td>";
                          $currentProjectID = $value->projectID;
                          echo "<td style='text-align: initial;'>";
                          $qty = 0;
                          foreach ($data as $value2) {
                              if ($value2->projectID == $currentProjectID) {
                                  if ($qty != 0) {
                                      echo ", ";
                                  }
                                  echo $value2->numParte;
                                  $qty += $value2->cantReq;
                              }
                          }
                          echo "</td>";
                          echo "<td>" . $qty . "</td>";
                          $qty = 0;
                          echo "<td>" . $value->fechaReqCliente . "</td>";
                          echo "<td><progress Style='margin-left: 10px; margin-right: 10px;' id='file' value='" . $value->completed . "' max='100'> $value->completed% </progress></td>";
                          if ($value->nota != NULL) {
                              echo "<td style='text-align: initial;'><span class='editSpan nota'>" . $value->notaMonth . "/" . $value->notaDay . ": " . $value->nota . "</span>";
                          } else {
                              echo "<td></td>";
                          }
                          echo "<input class='editInput nota' type='text' name='nota' value='" . $value->nota . "' style='display: none;'></td>";
                          echo "<input class='prevNota' type='hidden' name='prevNota' value='' style='display: none;'>";
                          echo "<td>
                                    <div class='' style='display: flex; justify-content: space-evenly;'>
                                        <a class='editBtn' href='#' onclick='editMode(this)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon'></div>
                                            </div>
                                        </a>
                                        <a class='guardarBtn' href='#' onclick='insertarNota(this)' style='display: none;'>
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
                      }else {
                          // code...
                      }
                  }
                  // foreach ($data as $k => $v) {
                  //     echo "\$a[$k] => $v.\n";
                  // }


                  // echo "<tr id='" . $resultado->idProyecto . "'>";
                  // echo "<td>" . $resultado->projectID . "</td>";
                  // echo "<td><a href='/proyecto_detalle.php?id=".$resultado->idProyecto."&back=log' style='a:visited {color:#00FF00}'>" . $resultado->pnombre . "</a></td>";
                  //
                  // echo "<td>" . $resultado->numParte . "</td>";
                  // echo "<td>" . $resultado->cantReq . "</td>";
                  // echo "<td>" . $resultado->fechaEmbarque . "</td>";
                  //
                  // echo "<td><span class='editSpan nota'>" . $resultado->nota . "</span>";
                  // echo "<input class='editInput nota' type='text' name='nota' value='" . $resultado->nota . "' style='display: none;'></td>";
                  // echo "<input class='prevNota' type='hidden' name='prevNota' value='' style='display: none;'>";
                  // // echo "<td>". $resultado->idEnsamble . "</td>";
                  // // echo "<td>". $resultado->numParte . "</td>";
                  // // echo "<td>". $resultado->workorder . "</td>";
                  // // echo "<td>". $resultado->cantReq . "</td>";
                  // // echo "<td>". $resultado->cantTerm . "</td>";
                  // // echo "<td>". $resultado->notas . "</td>";
                  // echo "<td>
                  //           <div class='' style='display: flex; justify-content: space-evenly;'>
                  //               <a class='editBtn' href='#' onclick='editMode(this)'>
                  //                   <div class='icon-container'>
                  //                       <div class='plus-icon'></div>
                  //                   </div>
                  //               </a>
                  //               <a class='guardarBtn' href='#' onclick='insertarNota(this)' style='display: none;'>
                  //                   <div class='icon-container'>
                  //                       <div class='plus-icon-green'></div>
                  //                   </div>
                  //               </a>
                  //               <a class='deleteBtn' href='#' onclick='cancel(this)' style='display: none;'>
                  //                   <div class='icon-container'>
                  //                       <div class='cross-icon'></div>
                  //                   </div>
                  //               </a>
                  //           </div>
                  //       </td>";
                  // echo "</tr>";

              ?>
              </table>
            </div>

            <div class="card shadow p-3 mb-5 bg-body rounded w-100">
              <div class="card-header bg-success text-white text-center fw-bold">
                  Application Projects
              </div>
              <div class="icon-container m-3">
                <?php if (in_array(7, $_SESSION["permisos"])) { ?>
                          <a href="/pages/proyecto_alta/proyecto_alta_application.php?isApplication=1">
                                <div class="plus-icon-green"></div>
                          </a>
                <?php } ?>
              </div>
              <table>
                  <thead>
                      <!-- Encabezados de tabla -->
                      <tr>
                          <th>Project ID</th>
                          <th>Customer</th>
                          <th>Name</th>
                          <th>Leader</th>
                          <th>Req Qty</th>
                          <th>Due Date</th>
                          <th>Progress</th>
                          <th>Hours</th>
                          <th>Notes</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
              <?php

                  $dataApplication = array();

                  $currentProjectID = "";
                  $currentNumParte = "";
                  $currentQty = "";

                  while ($resultado = $stmtApplication->fetch()) {
                        $dataApplication[] = $resultado;
                      // array_push($data,'idProyecto',$resultado->idProyecto);
                      // array_push($data,'projectID',$resultado->projectID);
                      // array_push($data,'pnombre',$resultado->pnombre);
                      // array_push($data,'numParte',$resultado->numParte);
                      // array_push($data,'cantReq',$resultado->cantReq);
                      // array_push($data,'fechaEmbarque',$resultado->fechaEmbarque);
                      // array_push($data,'nota',$resultado->nota);


                      // $data['idProyecto'] = $resultado->idProyecto;
                      // $data['projectID'] = $resultado->projectID;
                      // $data['pnombre'] = $resultado->pnombre;
                      // $data['numParte'] = $resultado->numParte;
                      // $data['cantReq'] = $resultado->cantReq;
                      // $data['fechaEmbarque'] = $resultado->fechaEmbarque;
                      // $data['nota'] = $resultado->nota;
                  }

  // print_r($data);
                  foreach ($dataApplication as $value) {
                      if ($value->projectID != $currentProjectID) {
                          echo "<tr id='" . $value->idProyecto . "'>";
                          echo "<td>" . $value->projectID . "</td>";
                          echo "<td>" . $value->cNombre . "</td>";
                          echo "<td><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$value->idProyecto."&back=log' style='a:visited {color:#00FF00}'>" . $value->pDescripcion . "</a></td>";
                          $currentProjectID = $value->projectID;
                          echo "<td>" . $value->eNombre . "</td>";
                          $qty = 0;
                          foreach ($dataApplication as $value2) {
                              if ($value2->projectID == $currentProjectID) {
                                  $qty += $value2->cantReq;
                              }
                          }
                          echo "<td>" . $qty . "</td>";
                          $qty = 0;
                          echo "<td>" . $value->fechaReqCliente . "</td>";
                          echo "<td><progress Style='margin-left: 10px; margin-right: 10px;' id='file' value='" . $value->percentCompleted . "' max='100'> $value->percentCompleted% </progress></td>";
                          echo "<td>" . number_format($value->horas, 0) . "</td>";
                          if ($value->nota != NULL) {
                              echo "<td style='text-align: initial;'><span class='editSpan nota'>" . $value->notaMonth . "/" . $value->notaDay . ": " . $value->nota . "</span>";
                          } else {
                              echo "<td></td>";
                          }
                          echo "<input class='editInput nota' type='text' name='nota' value='" . $value->nota . "' style='display: none;'></td>";
                          echo "<input class='prevNota' type='hidden' name='prevNota' value='' style='display: none;'>";
                          echo "<td>
                                    <div class='' style='display: flex; justify-content: space-evenly;'>
                                        <a class='editBtn' href='#' onclick='editMode(this)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon'></div>
                                            </div>
                                        </a>
                                        <a class='guardarBtn' href='#' onclick='insertarNota(this)' style='display: none;'>
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
                      }else {
                          // code...
                      }
                  }
                  // foreach ($data as $k => $v) {
                  //     echo "\$a[$k] => $v.\n";
                  // }


                  // echo "<tr id='" . $resultado->idProyecto . "'>";
                  // echo "<td>" . $resultado->projectID . "</td>";
                  // echo "<td><a href='/proyecto_detalle.php?id=".$resultado->idProyecto."&back=log' style='a:visited {color:#00FF00}'>" . $resultado->pnombre . "</a></td>";
                  //
                  // echo "<td>" . $resultado->numParte . "</td>";
                  // echo "<td>" . $resultado->cantReq . "</td>";
                  // echo "<td>" . $resultado->fechaEmbarque . "</td>";
                  //
                  // echo "<td><span class='editSpan nota'>" . $resultado->nota . "</span>";
                  // echo "<input class='editInput nota' type='text' name='nota' value='" . $resultado->nota . "' style='display: none;'></td>";
                  // echo "<input class='prevNota' type='hidden' name='prevNota' value='' style='display: none;'>";
                  // // echo "<td>". $resultado->idEnsamble . "</td>";
                  // // echo "<td>". $resultado->numParte . "</td>";
                  // // echo "<td>". $resultado->workorder . "</td>";
                  // // echo "<td>". $resultado->cantReq . "</td>";
                  // // echo "<td>". $resultado->cantTerm . "</td>";
                  // // echo "<td>". $resultado->notas . "</td>";
                  // echo "<td>
                  //           <div class='' style='display: flex; justify-content: space-evenly;'>
                  //               <a class='editBtn' href='#' onclick='editMode(this)'>
                  //                   <div class='icon-container'>
                  //                       <div class='plus-icon'></div>
                  //                   </div>
                  //               </a>
                  //               <a class='guardarBtn' href='#' onclick='insertarNota(this)' style='display: none;'>
                  //                   <div class='icon-container'>
                  //                       <div class='plus-icon-green'></div>
                  //                   </div>
                  //               </a>
                  //               <a class='deleteBtn' href='#' onclick='cancel(this)' style='display: none;'>
                  //                   <div class='icon-container'>
                  //                       <div class='cross-icon'></div>
                  //                   </div>
                  //               </a>
                  //           </div>
                  //       </td>";
                  // echo "</tr>";

              ?>
              </table>
            </div>

            <div class="card shadow p-3 mb-5 bg-body rounded w-100">
              <div class="card-header bg-warning text-black text-center fw-bold">
                  Application Support Projects
              </div>
              <div class="icon-container m-3">
                <?php if (in_array(38, $_SESSION["permisos"])) { ?>
                          <a href="/pages/proyecto_alta/proyecto_alta_application_support.php?isApplication=1">
                                <div class="plus-icon-green"></div>
                          </a>
                <?php } ?>
              </div>
              <table>
                  <thead>
                      <!-- Encabezados de tabla -->
                      <tr>
                          <th>Project ID</th>
                          <th>Customer</th>
                          <th>Name</th>
                          <th>Owner</th>
                          <th>Req Qty</th>
                          <th>Due Date</th>
                          <th>Progress</th>
                          <th>Hours</th>
                          <th>Notes</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
              <?php

                  $dataApplication = array();

                  $currentProjectID = "";
                  $currentNumParte = "";
                  $currentQty = "";

                  while ($resultado = $stmtSupportApplication->fetch()) {
                        $dataApplication[] = $resultado;
                      // array_push($data,'idProyecto',$resultado->idProyecto);
                      // array_push($data,'projectID',$resultado->projectID);
                      // array_push($data,'pnombre',$resultado->pnombre);
                      // array_push($data,'numParte',$resultado->numParte);
                      // array_push($data,'cantReq',$resultado->cantReq);
                      // array_push($data,'fechaEmbarque',$resultado->fechaEmbarque);
                      // array_push($data,'nota',$resultado->nota);


                      // $data['idProyecto'] = $resultado->idProyecto;
                      // $data['projectID'] = $resultado->projectID;
                      // $data['pnombre'] = $resultado->pnombre;
                      // $data['numParte'] = $resultado->numParte;
                      // $data['cantReq'] = $resultado->cantReq;
                      // $data['fechaEmbarque'] = $resultado->fechaEmbarque;
                      // $data['nota'] = $resultado->nota;
                  }

  // print_r($data);
                  foreach ($dataApplication as $value) {
                      if ($value->projectID != $currentProjectID) {
                          echo "<tr id='" . $value->idProyecto . "'>";
                          echo "<td>" . $value->projectID . "</td>";
                          echo "<td>" . $value->cNombre . "</td>";
                          echo "<td><a href='/pages/proyecto_detalle/proyecto_detalle_application.php?id=".$value->idProyecto."&back=log' style='a:visited {color:#00FF00}'>" . $value->pDescripcion . "</a></td>";
                          $currentProjectID = $value->projectID;
                          echo "<td>" . $value->eNombre . "</td>";
                          $qty = 0;
                          foreach ($dataApplication as $value2) {
                              if ($value2->projectID == $currentProjectID) {
                                  $qty += $value2->cantReq;
                              }
                          }
                          echo "<td>" . $qty . "</td>";
                          $qty = 0;
                          echo "<td>" . $value->fechaReqCliente . "</td>";
                          echo "<td><progress Style='margin-left: 10px; margin-right: 10px;' id='file' value='" . $value->completed . "' max='100'> $value->completed% </progress></td>";
                          echo "<td>" . number_format($value->horas, 0) . "</td>";
                          if ($value->nota != NULL) {
                              echo "<td style='text-align: initial;'><span class='editSpan nota'>" . $value->notaMonth . "/" . $value->notaDay . ": " . $value->nota . "</span>";
                          } else {
                              echo "<td></td>";
                          }
                          echo "<input class='editInput nota' type='text' name='nota' value='" . $value->nota . "' style='display: none;'></td>";
                          echo "<input class='prevNota' type='hidden' name='prevNota' value='' style='display: none;'>";
                          echo "<td>
                                    <div class='' style='display: flex; justify-content: space-evenly;'>
                                        <a class='editBtn' href='#' onclick='editMode(this)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon'></div>
                                            </div>
                                        </a>
                                        <a class='guardarBtn' href='#' onclick='insertarNota(this)' style='display: none;'>
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
                      }else {
                          // code...
                      }
                  }
                  // foreach ($data as $k => $v) {
                  //     echo "\$a[$k] => $v.\n";
                  // }


                  // echo "<tr id='" . $resultado->idProyecto . "'>";
                  // echo "<td>" . $resultado->projectID . "</td>";
                  // echo "<td><a href='/proyecto_detalle.php?id=".$resultado->idProyecto."&back=log' style='a:visited {color:#00FF00}'>" . $resultado->pnombre . "</a></td>";
                  //
                  // echo "<td>" . $resultado->numParte . "</td>";
                  // echo "<td>" . $resultado->cantReq . "</td>";
                  // echo "<td>" . $resultado->fechaEmbarque . "</td>";
                  //
                  // echo "<td><span class='editSpan nota'>" . $resultado->nota . "</span>";
                  // echo "<input class='editInput nota' type='text' name='nota' value='" . $resultado->nota . "' style='display: none;'></td>";
                  // echo "<input class='prevNota' type='hidden' name='prevNota' value='' style='display: none;'>";
                  // // echo "<td>". $resultado->idEnsamble . "</td>";
                  // // echo "<td>". $resultado->numParte . "</td>";
                  // // echo "<td>". $resultado->workorder . "</td>";
                  // // echo "<td>". $resultado->cantReq . "</td>";
                  // // echo "<td>". $resultado->cantTerm . "</td>";
                  // // echo "<td>". $resultado->notas . "</td>";
                  // echo "<td>
                  //           <div class='' style='display: flex; justify-content: space-evenly;'>
                  //               <a class='editBtn' href='#' onclick='editMode(this)'>
                  //                   <div class='icon-container'>
                  //                       <div class='plus-icon'></div>
                  //                   </div>
                  //               </a>
                  //               <a class='guardarBtn' href='#' onclick='insertarNota(this)' style='display: none;'>
                  //                   <div class='icon-container'>
                  //                       <div class='plus-icon-green'></div>
                  //                   </div>
                  //               </a>
                  //               <a class='deleteBtn' href='#' onclick='cancel(this)' style='display: none;'>
                  //                   <div class='icon-container'>
                  //                       <div class='cross-icon'></div>
                  //                   </div>
                  //               </a>
                  //           </div>
                  //       </td>";
                  // echo "</tr>";

              ?>
              </table>
            </div>

            <hr style="width:100%; margin-top: 50px; margin-bottom: 50px;">

            <table>
                <thead>
                    <!-- Encabezados de tabla -->
                    <tr>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Scope</th>
                    </tr>
                </thead>
                <tbody>
                  <tr>
                    <td style="font-weight: bold; color: Deepskyblue;">MS</td>
                    <td style="text-align: left; padding-left: 8px">Material Substitution</td>
                    <td style="text-align: left; padding-left: 8px">Customer request to susbtitute existing material to resolve form, fit, function issue, improve product performance, cost saving initiative or VA/VE initiatives</td>
                  <tr>
                  <tr>
                    <td style="font-weight: bold; color: Deepskyblue;">DS</td>
                    <td style="text-align: left; padding-left: 8px">Design Services</td>
                    <td style="text-align: left; padding-left: 8px">Design process, product or component</td>
                  </tr>
                  <tr>
                    <td style="font-weight: bold; color: Deepskyblue;">MTI</td>
                    <td style="text-align: left; padding-left: 8px;">Manufacturing Technology Innovation</td>
                    <td style="text-align: left; padding-left: 8px;">Fiber technology innovation/development with 'breakthrough" mindset this excludes basic plant mfg practices</td>
                  </tr>
                  <tr>
                    <td style="font-weight: bold; color: Deepskyblue;">PTI</td>
                    <td style="text-align: left; padding-left: 8px;">Product Technology Innovation</td>
                    <td style="text-align: left; padding-left: 8px;">New fiber products, new to the world</td>
                  </tr>
                  <tr>
                    <td style="font-weight: bold; color: Deepskyblue;">DB</td>
                    <td style="text-align: left; padding-left: 8px;">Design BOM</td>
                    <td style="text-align: left; padding-left: 8px;">BOM design for new Fiber product</td>
                  </tr>
                  <tr>
                    <td style="font-weight: bold; color: Deepskyblue;">DD</td>
                    <td style="text-align: left; padding-left: 8px">Design Drawing</td>
                    <td style="text-align: left; padding-left: 8px">Drawing design for new Drawing product</td>
                  </tr>
                  <tr>
                    <td style="font-weight: bold; color: Deepskyblue;">VT</td>
                    <td style="text-align: left; padding-left: 8px">Validation Test</td>
                    <td style="text-align: left; padding-left: 8px">Enviromental Test</td>
                  </tr>
                  <tr>
                    <td style="font-weight: bold; color: Deepskyblue;">AE</td>
                    <td style="text-align: left; padding-left: 8px">Application Engineering</td>
                    <td style="text-align: left; padding-left: 8px">Alternative Material, Reverse Engeenering, Point to Point, Toling List</td>
                  </tr>
                </tbody>
            </table>
        </div>
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

      <script src="js/funciones.js"></script>
      <script type="text/javascript">
          function openNewProject(sender) {
              event.preventDefault();
              //hide edit span
              var trObj = $('#project').val();
              switch (trObj) {
                case "0":
                  window.location.href = "/pages/proyecto_alta/proyecto_alta_coe.php";
                  break;
                case "1":
                  window.location.href = "/pages/proyecto_alta/proyecto_alta_application.php";
                  break;
                default:
                  alert("Please select type of project");
              }
              // $(this).closest("tr").find(".saveBtn").show();
          }
      </script>
      <script type="text/javascript">
          function editMode(sender) {
              event.preventDefault();
              //hide edit span
              var trObj = $(sender).closest("tr");
              $(sender).closest("tr").find(".editSpan").hide();
              $(sender).closest("tr").find(".editBtn").hide();
              $(sender).closest("tr").find(".deleteBtn").show();
              $(sender).closest("tr").find(".editInput").show();
              $(sender).closest("tr").find(".guardarBtn").show();

              trObj.find(".editInput.nota").val('');

              // $(this).closest("tr").find(".saveBtn").show();
          }

          function cancel(sender) {
              event.preventDefault();
              //hide edit span
              var trObj = $(sender).closest("tr");
              trObj.find(".editSpan").show();
              trObj.find(".editBtn").show();
              trObj.find(".deleteBtn").hide();
              trObj.find(".editInput").hide();
              trObj.find(".guardarBtn").hide();

              trObj.find(".editInput.note").val(trObj.find(".editSpan.note").text());

              // mostrarAlerta('warning','Cancelado.');
          }

          function insertarNota(sender) {
              event.preventDefault();
              var trObj = $(sender).closest("tr");
              var idProyecto = $(sender).closest("tr").attr('id');
              var nota = trObj.find(".editInput.nota").val();

              if (nota == "") {
                  mostrarAlerta('warning','Cannot Add empty Note.');
              }else {
                  // alert(notas);
                  $.ajax({
                      type:'POST',
                      url:'js/ajax.php',
                      async: true,
                      data: {
                          accion: 'nuevaNota',
                          idProyecto: idProyecto,
                          nota: nota
                      },
                      // data: 'accion=editarEnsamble',
                      success:function(response) {
                          var info = JSON.parse(response);
                          console.log(info);
                          if(info.result) {
                              let text = info.result.notaMonth + "/" + info.result.notaDay + ": " + info.result.nota;
                              trObj.find(".editSpan.nota").text(text);
                              trObj.find(".editInput.nota").text(text);

                              trObj.find(".editInput").hide();
                              trObj.find(".guardarBtn").hide();
                              trObj.find(".deleteBtn").hide();
                              trObj.find(".editSpan").show();
                              trObj.find(".editBtn").show();
                              mostrarAlerta('success','Note added Successfully.');
                          } else {
                              alert(response.result);
                          }
                      },
                      error: function(error) {
                          console.log(error);
                      }
                  });
              }
          }
      </script>


    <?php include "inc/footer.html"; ?>
