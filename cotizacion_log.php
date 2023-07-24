<?php
  include "inc/conexion.php";
  include "inc/headerBoostrap.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(33, $_SESSION["permisos"])) {
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

      $stmt = $dbh->prepare("SELECT cotizacion.idCotizacion, cotizacion.quoteID, cotizacion.nombre AS pnombre, date(cotizacion.fechaInicio) AS fechaInicio, cliente.nombreCliente AS cNombre,
                                    date(cotizacion.fechaReqCliente) AS fechaReqCliente,
                                    (SELECT cotizacion_notas.nota FROM cotizacion_notas WHERE cotizacion_notas.idCotizacion = cotizacion.idCotizacion ORDER BY cotizacion_notas.idCotizacionNota DESC LIMIT 1) AS nota, cotizacion.overallComplet, empleado.nombre AS resp,
                                TRUNCATE((IFNULL(tipocotizacion.horas,0) + IFNULL((SELECT tipocotizacion.horas
                                    FROM tipocotizacion
                                    WHERE idTipoCotizacion = cotizacion.BOMType),0)) * (1 - overallComplet),2) AS horasTotales
                            FROM cotizacion
                            LEFT JOIN cotizacion_notas
                            ON cotizacion_notas.idCotizacion = cotizacion.idCotizacion
                            INNER JOIN cliente
                            ON cotizacion.idCliente = cliente.idCliente
                            INNER JOIN tipocotizacion
                            ON cotizacion.idTipoCotizacion = tipocotizacion.idTipoCotizacion
                            INNER JOIN cotizacion_categoria
                            ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
                            INNER JOIN complejidad
                            ON tipocotizacion.idCotizacionVolumen = complejidad.idComplejidad
                            INNER JOIN status
                            ON cotizacion.idStatus = status.idStatus
                            INNER JOIN empleado
                            ON cotizacion.idResponsable = empleado.idEmpleado
                            WHERE cotizacion.idStatus <> 5 AND cotizacion.idStatus <> 7 AND cotizacion.idStatus <> 6
                            GROUP BY cotizacion.idCotizacion, cotizacion.quoteID, cotizacion.nombre, DATE(cotizacion.fechaReqCliente)
                            ORDER BY fechaReqCliente ASC");
        $stmt->execute();
        $stmtOpenHours = $dbh->prepare("SELECT SUM(TRUNCATE((IFNULL(tipocotizacion.horas,0) + IFNULL((SELECT tipocotizacion.horas
                                                FROM tipocotizacion
                                                WHERE idTipoCotizacion = cotizacion.BOMType),0)) * (1 - overallComplet),2)) AS OpenHours
                                        FROM cotizacion
                                        INNER JOIN tipocotizacion
                                        ON cotizacion.idTipoCotizacion = tipocotizacion.idTipoCotizacion
                                        INNER JOIN status
                                        ON cotizacion.idStatus = status.idStatus
                                        WHERE cotizacion.idStatus <> 5 AND cotizacion.idStatus <> 7 AND cotizacion.idStatus <> 6");
          $stmtOpenHours->execute();
          $resultadoOpen = $stmtOpenHours->fetch();
        $data = "";
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


<div class="card mt-3 mb-3">
    <h2 class="card-header text-center ">Quotes Open Log</h2>
    <div class="card-body">
        <div class="row">
            <div class="row">
                <div class="inline-container align-items-center">
                    <h1 style="margin-right: 10px;">New Quote</h1>
                    <a href='cotizacion_alta.php'>
                        <div class='icon-container'>
                            <div class='plus-icon'></div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row mb-4">
                <div class="flex-container">

                    <!-- /////////////////////////////////////// BUSCADOR ///////////////////////////////////// -->
                    <!-- <form class="" action="proyecto.php" style="width: 100%;" method="post">
                    <div class="inline-container" style="margin-bottom: 10px; width: 40%; float: right; text-align: right;" >
                    <div class="input-field" style="width: 500px; margin-bottom: 4px;">
                    <input name="buscar" type="text" id="buscar" placeholder="Ingrese Texto de Busqueda" onblur="this.value=removeSpaces(this.value);">
                    </div>
                    <input name="btnBuscarProyecto" class="btn-buscar" style="width: auto; margin-top: 0;" type="submit" value="Buscar">
                    </div>
                    </form> -->

                    <div class="inline-container" style="width: 30%;">
                        <label for="openHours" style="width:80%;">Total Open Hours:</label>
                        <input type="text" id="openHours" name="openHours" value="<?php echo round($resultadoOpen->OpenHours,0); ?>" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
          <table id="quoteOpenTable" class="table">
              <thead>
                  <!-- Encabezados de tabla -->
                  <tr>
                      <th>Quote ID</th>
                      <th>Customer</th>
                      <th>Name</th>
                      <th>Start Date</th>
                      <th>Req Date</th>
                      <th>Resp</th>
                      <th>Progress</th>
                      <th>Prog %</th>
                      <th>Open Hours</th>
                      <th>Notes</th>
                      <th>Actions</th>
                  </tr>
              </thead>
          <?php

              $data = array();

              $currentQuoteID = "";

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
                  if ($value->idCotizacion != $currentQuoteID) {
                      echo "<tr id='" . $value->idCotizacion . "'>";
                      echo "<td>" . $value->quoteID . "</td>";
                      echo "<td>" . $value->cNombre . "</td>";
                      echo "<td><a href='/cotizacion_detalle.php?id=".$value->idCotizacion."&back=cotizacion_log' style='a:visited {color:#00FF00}'>" . $value->pnombre . "</a></td>";
                      $currentQuoteID = $value->quoteID;

                      echo "<td>" . $value->fechaInicio . "</td>";
                      echo "<td>" . $value->fechaReqCliente . "</td>";
                      echo "<td>" . $value->resp . "</td>";
                      echo "<td><progress Style='margin-left: 10px; margin-right: 10px;' id='file' value='" . $value->overallComplet * 100 . "' max='100'> $value->overallComplet% </progress></td>";
                      echo "<td><span class='editSpan progressNumberDisplay' id='progressNumberDisplay'>" . $value->overallComplet * 100 . "</span>";
                      echo "<input class='editInput progressNumber' type='number' name='progressNumber' id='progressNumber' value='" . $value->overallComplet * 100 . "' style='display: none;' min='0' max='100'></td>";
                      echo "<td id='openLineHours'>" . round($value->horasTotales,0) . "</td>";
                      echo "<td><span class='editSpan nota'>" . $value->nota . "</span>";
                      echo "<input class='editInput nota' type='text' name='nota' value='" . $value->nota . "' style='display: none;'></td>";
                      echo "<input class='prevNota' type='hidden' name='prevNota' value='' style='display: none;'>";
                      echo "<td>
                                <div class='' style='display: flex; justify-content: space-evenly;'>
                                    <a class='editBtn' href='#' onclick='editMode(this)'>
                                        <div class='icon-container'>
                                            <div class='plus-icon'></div>
                                        </div>
                                    </a>
                                    <a class='guardarBtn' href='#' onclick='insertarCotizacionNota(this)' style='display: none;'>
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
          $( document ).ready(function() {
              $("#progressNumber").change(function() {
                 var max = parseInt($(this).attr('max'));
                 var min = parseInt($(this).attr('min'));
                 if ($(this).val() > max)
                 {
                     $(this).val(max);
                 }
                 else if ($(this).val() < min)
                 {
                     $(this).val(min);
                 }
               });

               var table = $('#quoteOpenTable').DataTable({
                  paging: false,
                  ordering: true,
                  info: false,
                  searching: false
                 // // responsive: true,
                 // orderCellsTop: true,
                 // fixedHeader: true,
                 // pageLength: 100,
                 // // scrollX: true,
                 // dom: 'Bfrtip',
                 // buttons: [
                 //     // 'copyHtml5',
                 //     'excelHtml5',
                 // ],
                 // columnDefs: [
                 //
                 // ],
                 // drawCallback: () => $('#avg').val(updateAverage())
               });
          });

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

          function insertarCotizacionNota(sender) {
              event.preventDefault();
              var trObj = $(sender).closest("tr");
              var idCotizacion = $(sender).closest("tr").attr('id');
              var nota = trObj.find(".editInput.nota").val();
              var progress = trObj.find(".editInput.progressNumber").val();

              // alert(notas);
              $.ajax({
                  type:'POST',
                  url:'js/ajax.php',
                  async: true,
                  data: {
                      accion: 'nuevaNotaCotizacion',
                      idCotizacion: idCotizacion,
                    	overallComplet: progress / 100,
                      nota: nota
                  },
                  // data: 'accion=editarEnsamble',
                  success:function(response) {
                      var progressNumber = trObj.find("#progressNumber").val();
                      var openHours = trObj.find("#openLineHours").text();
                      var progressNumberOld = trObj.find("#progressNumberDisplay").text();

                      if (response == 'porciento') {
                          trObj.find(".editInput").hide();
                          trObj.find(".guardarBtn").hide();
                          trObj.find(".deleteBtn").hide();
                          trObj.find(".editSpan").show();
                          trObj.find(".editBtn").show();
                          trObj.find("#file").val(progressNumber);
                          trObj.find("#openLineHours").text(calculateOpenHours(progressNumber, progressNumberOld, openHours));
                          trObj.find("#progressNumberDisplay").text(progressNumber);
                          mostrarAlerta('success','Progress Updated.');
                      } else {
                          var info = JSON.parse(response);
                          console.log(info);
                          if(info.result) {
                              trObj.find(".editSpan.nota").text(info.result.nota);
                              trObj.find(".editInput.nota").text(info.result.nota);

                              trObj.find(".editInput").hide();
                              trObj.find(".guardarBtn").hide();
                              trObj.find(".deleteBtn").hide();
                              trObj.find(".editSpan").show();
                              trObj.find(".editBtn").show();
                              trObj.find("#file").val(progressNumber);
                              trObj.find("#openLineHours").text(calculateOpenHours(progressNumber, progressNumberOld, openHours));
                              trObj.find("#progressNumberDisplay").text(progressNumber);
                              mostrarAlerta('success','Changes made Successfully.');
                          } else {
                              alert(response.result);
                          }
                      }
                  },
                  error: function(error) {
                      console.log(error);
                  }
              });

          }

          function calculateOpenHours(progressNumber, progressNumberOld, openHours){

            return Math.round(((100-progressNumber)*openHours)/(100-progressNumberOld));
          }
      </script>


    <?php include "inc/footer.html"; ?>
