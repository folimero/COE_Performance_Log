<?php session_start();
  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(5, $_SESSION["permisos"]) && !in_array(6, $_SESSION["permisos"])) {
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
  // Funcion para limpiar campos
  function cleanInput($value) {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }

  // Campos obtenidos en GET
  $URL = "index.php";
  $id;
  $isAwarded;
  $bomHrs;
  $tipoHRS;
  $actHRS;
  $totalHRS;
  $idStatus;
  $VentasTotales = 0;
  if (isset($_GET['id'])) {
      $id = cleanInput($_GET['id']);
      include "inc/headerBoostrap.php";
      include "inc/conexion.php";
      $stmt = $dbh->prepare("SELECT a.idActividad, a.tipo, a.nombre, a.descripcion, a.horasLow, a.horasMid, a.horasHigh, a.idEtapa, e.nombre AS eNombre, a.resp, a.obsoleta
                            FROM actividad AS a
                            INNER JOIN etapa AS e
                            ON a.idEtapa = e.idEtapa
                            WHERE a.idActividad = $id");
      $stmt->execute();

      $stmtDependencias = $dbh->prepare("SELECT ad.idActividadDependencia, ad.idActRequerida, a.nombre, a.tipo, a.descripcion, e.nombre AS eNombre, a.resp
                                          FROM actividad_dependencia AS ad
                                          INNER JOIN actividad AS a
                                          ON ad.idActRequerida = a.idActividad
                                          INNER JOIN etapa AS e
                                          ON a.idEtapa = e.idEtapa
                                          WHERE ad.idActividad = $id");
      $stmtDependencias->execute();
  } else {
        $message = "Activity not found!.";
        echo "<script>
                  alert('$message');
                  window.location.href='index.php';
              </script>";
        die();
  }

  while ($resultado = $stmt->fetch()) {

    if ($resultado->obsoleta == "1") {
        $obsolet = "YES";
    }else {
        $obsolet = "NO";
    }
  ?>

  <!DOCTYPE html>
      <!-- <div class="flex-container">
          <h1>Quote Detail</h1>
      </div> -->
      <div class='icon-container' style="margin: 20px 0px;">
          <a id='backBtn' href='actividad.php'>
              <div class='back-icon-green'></div>
          </a>
      </div>

      <hr style="width:100%;">

      <!-- <div class="container w-100" > -->
          <div class="card mb-3">
              <h2 class="card-header text-center ">Activity Detail</h2>
              <div class="card-body">
                  <div class="row">

                    <!-- DETAIL SECTION -->
                    <div class="col-12">
                        <div class="row">

                            <div class="col-12 mb-3">
                                <div class="row" style="align-items: center;">
                                  <div class="col-6 text-end title-label">
                                      <h6>Name:</h6>
                                  </div>
                                  <div class="col-6">
                                      <h6 id="nameEditNombre" style="text-align: left;"><?php echo $resultado->nombre;?><h6>
                                      <input type="text" class="form-control-sm" id="nameInputNombre" style="display:none;">
                                  </div>
                                </div>
                                <div class="row" style="align-items: center;">
                                  <div class="col-6 text-end title-label">
                                      <h6>Description:</h6>
                                  </div>
                                  <div class="col-6">
                                      <h6 class="align-self-center" id="nameEditDesc" style="text-align: left;"><?php echo $resultado->descripcion;?><h6>
                                      <input type="text" class="form-control-sm" id="nameInputDesc" style="display:none;">
                                  </div>
                                </div>
                                <div class="row" style="align-items: center;">
                                  <div class="col-6 text-end title-label">
                                      <h6>Stage:</h6>
                                  </div>
                                  <div class="col-6">
                                      <h6 class="align-self-center" id="nameEditEtapa" style="text-align: left;"><?php echo $resultado->eNombre;?><h6>
                                      <!-- <input type="text" class="form-control" id="nameInputEtapa" style="display:none;"> -->
                                  </div>
                                </div>
                                <div class="row" style="align-items: center;">
                                  <div class="col-6 text-end title-label">
                                      <h6>Type:</h6>
                                  </div>
                                  <div class="col-6">
                                    <h6 class="align-self-center" id="nameEditTipo" style="text-align: left;"><?php echo $resultado->tipo;?><h6>
                                      <select class="form-select-sm" id="nameInputTipo" style="display:none;">
                                          <option value="INPUT">INPUT</option>
                                          <option value="OUTPUT">OUTPUT</option>
                                      </select>
                                  </div>
                                </div>
                                <div class="row" style="align-items: center;">
                                  <div class="col-6 text-end title-label">
                                      <h6>Hours Low:</h6>
                                  </div>
                                  <div class="col-6">
                                      <h6 style="text-align: left;"><?php echo $resultado->horasLow . ' Hrs';?><h6>
                                  </div>
                                </div>
                                <div class="row" style="align-items: center;">
                                  <div class="col-6 text-end title-label">
                                      <h6>Hours Mid:</h6>
                                  </div>
                                  <div class="col-6">
                                      <h6 style="text-align: left;"><?php echo $resultado->horasMid . ' Hrs';?><h6>
                                  </div>
                                </div>
                                <div class="row" style="align-items: center;">
                                  <div class="col-6 text-end title-label">
                                      <h6>Hours High:</h6>
                                  </div>
                                  <div class="col-6">
                                      <h6 style="text-align: left;"><?php echo $resultado->horasHigh . ' Hrs';?><h6>
                                  </div>
                                </div>
                                <div class="row mb-3" style="align-items: center;">
                                  <div class="col-6 text-end title-label">
                                      <h6>Responsable:</h6>
                                  </div>
                                  <div class="col-6">
                                      <h6 class="align-self-center" id="nameEditResp" style="text-align: left;"><?php echo $resultado->resp;?><h6>
                                      <input type="text" class="form-control-sm" id="nameInputResp" style="display:none;">
                                  </div>
                                </div>
                                <div class="row" style="align-items: center;">
                                    <div class="col-6 text-end title-label">
                                        <h6>Obsolete:</h6>
                                    </div>
                                    <div class="col-6">
                                          <h6 class="align-self-center" id="nameEditObsolet" style="text-align: left;"><?php echo $obsolet;?><h6>
                                          <select class="form-select-sm" id="nameInputObsolet" style="display:none;">
                                              <option value="YES">YES</option>
                                              <option value="NO">NO</option>
                                          </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 text-center">
                                        <button type="button" class="btn btn-primary" id="editBtn" onclick="editar()">Editar</button>
                                        <button type="button" class="btn btn-primary" id="saveBtn" style="display:none;" onclick="guardar()">Save</button>
                                    </div>
                                </div>
                            </div>
                      </div>
                    </div>

                    <!-- BOTTOM SECTION -->
                    <!-- <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <h4 class="border-bottom border-2 ">Required Activities</h4>
                                </div>

                                <div class="col-4"></div>
                                <div class="col-4">
                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <select class="form-select" aria-label="Default select example">
                                                <option selected>Open this select menu</option>
                                                <option value="1">One</option>
                                                <option value="2">Two</option>
                                                <option value="3">Three</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4"></div>

                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <h6>Requiered Activities</h6>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div> -->

                  </div>
              </div>
          </div>
      <!-- </div> -->

      <hr style="width:100%; margin-bottom: 20px;">
<?php
    }
?>

      <div class="inline-container">
          <h1>Linked Activities</h1>
          <a href='#' onclick="abrirVentanaDependencias(); return false;">
              <div class='icon-container' style='margin-left: 10px;'>
                  <div class='plus-icon'></div>
              </div>
          </a>
      </div>

      <div class="flex-container">
        <table id="tablaDependencias" class="display">
          <thead>
              <!-- Encabezados de tabla -->
              <tr>
                  <th>ID</th>
                  <th>Dependency</th>
                  <th>Stage</th>
                  <th>Type</th>
                  <th>Responsable</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody id="areaTabla">
              <?php
                  while ($resultado = $stmtDependencias->fetch()) {

                      echo "<tr id='" . $resultado->idActividadDependencia . "'>";
                      echo "<td>". $resultado->idActRequerida . "</td>";
                      echo "<td>". $resultado->nombre . "</td>";
                      echo "<td>". $resultado->eNombre . "</td>";
                      echo "<td>". $resultado->tipo . "</td>";
                      echo "<td>". $resultado->resp . "</td>";

                      if (in_array(5, $_SESSION["permisos"])) {
                          echo "<td>";
                          echo "<div class='' style='display: flex; justify-content: space-evenly;'>
                                        <a class='editBtn' href='#' onclick='deleteActDependency(this)'>
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


  <!-- VENTANAS MODALES -->
      <div class="back-modal">
          <div class="contenido-modal" style="height: 350px; width: 1000px;">
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
          $(document).ready(function() {
              $('#cerrar_alerta').click(function() {
                  $('.alerta').removeClass('mostrar');
                  $('.alerta').addClass('ocultar');
              });

              <?php if (isset($_GET['back'])) { ?>
                        $('#backBtn').attr('href', '/<?php echo $_GET['back'] ?>.php');
              <?php } ?>

          });
      </script>

      <script type="text/javascript">

          function editar(sender) {
              event.preventDefault();
              //hide edit span
              var dataName = $('#nameEditNombre').text();
              var dataDesc = $('#nameEditDesc').text();
              // var dataEtapa = $('#nameEditEtapa').text();
              var dataTipo = $('#nameEditTipo').text();
              var dataResp = $('#nameEditResp').text();
              var dataObsolet = $('#nameEditObsolet').text();

              $('#nameInputNombre').val(dataName);
              $('#nameEditNombre').hide();
              $('#nameInputNombre').css('display', 'block');
              $('#nameInputNombre').show();

              $('#nameInputDesc').val(dataDesc);
              $('#nameEditDesc').hide();
              $('#nameInputDesc').css('display', 'block');
              $('#nameInputDesc').show();

              // $('#nameInputEtapa').val(dataEtapa);
              // $('#nameEditEtapa').hide();
              // $('#nameInputEtapa').css('display', 'block');
              // $('#nameInputEtapa').show();

              $('#nameInputTipo').val(dataTipo);
              $('#nameEditTipo').hide();
              $('#nameInputTipo').css('display', 'block');
              $('#nameInputTipo').show();

              $('#nameInputResp').val(dataResp);
              $('#nameEditResp').hide();
              $('#nameInputResp').css('display', 'block');
              $('#nameInputResp').show();

              $('#nameInputObsolet').val(dataObsolet);
              $('#nameEditObsolet').hide();
              $('#nameInputObsolet').css('display', 'block');
              $('#nameInputObsolet').show();

              $('#editBtn').hide();
              $('#saveBtn').show();
          }

          function cerrarEditar() {

              $('#nameEditNombre').show();
              $('#nameInputNombre').css('display', 'none');
              $('#nameInputNombre').hide();

              $('#nameEditDesc').show();
              $('#nameInputDesc').css('display', 'none');
              $('#nameInputDesc').hide();

              $('#nameEditTipo').show();
              $('#nameInputTipo').css('display', 'none');
              $('#nameInputTipo').hide();

              $('#nameEditResp').show();
              $('#nameInputResp').css('display', 'none');
              $('#nameInputResp').hide();

              $('#nameEditObsolet').show();
              $('#nameInputObsolet').css('display', 'none');
              $('#nameInputObsolet').hide();

              $('#editBtn').show();
              $('#saveBtn').hide();
          }

          function guardar(sender) {
              event.preventDefault();

              var id = <?php echo $id; ?>;
              var dataName = $('#nameInputNombre').val();
              var dataDesc = $('#nameInputDesc').val();
              // var dataEtapa = $('#nameInputEtapa').val();
              var dataTipo = $('#nameInputTipo').val();
              var dataResp = $('#nameInputResp').val();
              var dataObsolet = $('#nameInputObsolet').val();

              $.ajax({
                  type:'POST',
                  url:'js/ajax.php',
                  async: true,
                  data: {
                      accion: 'editarActividad',
                      id: id,
                      dataName: dataName,
                      dataDesc: dataDesc,
                      dataTipo: dataTipo,
                      dataResp: dataResp,
                      dataObsolet: dataObsolet
                  },
                  // data: 'accion=editarEnsamble',
                  success:function(response) {
                      var info = JSON.parse(response);
                      // console.log(info.result);
                      if (info.result != "error") {

                          var nombre = info.result.nombre;
                          var descripcion = info.result.descripcion;
                          var tipo = info.result.tipo;
                          var resp = info.result.resp;


                          if (info.result.obsoleta == 1) {
                              var obsoleta = "YES";
                          } else {
                              var obsoleta = "NO";
                          }

                          $('#nameEditNombre').text(nombre);
                          $('#nameEditDesc').text(descripcion);
                          $('#nameEditTipo').text(tipo);
                          $('#nameEditResp').text(resp);
                          $('#nameEditObsolet').text(obsoleta);

                          mostrarAlerta("success","Activity Edited");
                      }else {
                          mostrarAlerta("danger","Cannot Update Activity");
                      }
                      cerrarEditar();
                  },
                  error: function(error) {
                      console.log(error);
                  }
              });
          }

          function abrirVentanaDependencias() {

              var idActivity = <?php echo $id; ?>;
              $.ajax({
                  url: 'js/ajax.php',
                  type: 'POST',
                  async: true,
                  data: {
                      accion: 'cargarListaActividades',
                      idActivity: idActivity
                  },
                  success: function(response) {
                      // console.log(response);
                      if (!response != "error") {
                          var info = JSON.parse(response);
                          // console.log(info.result);

                          $(".contenido-modal").html("<div class='flex-container' style='margin-top: 60px;'>" +
                                                          "<!-- Titulo -->" +
                                                          "<h1 id='tittle'>Linked Activity</h1>" +
                                                          "<a class='btn-cerrar' onclick='cerrarModal2()'>" +
                                                              "<div class='icon-container'>" +
                                                                  "<div class='cross-icon'></div>" +
                                                              "</div>" +
                                                          "</a>" +
                                                          "<form class='w-75' id='' action='' onsubmit='return addActReq(event)'>" +
                                                              "<div class='input-field'>" +
                                                                  "<!-- Lista Actividades -->" +
                                                                  "<label for='idStatus'>Activity</label>" +
                                                                  "<div class='inline-container'>" +
                                                                      "<select name='idActivity' id='idActivity' required>" +
                                                                      "</select>" +
                                                                  "</div>" +
                                                              "</div>" +
                                                              "<input type='submit' id='btnNota' value='Add'>" +
                                                          "</form>" +
                                                      "</div>");
                          var mySelect = document.getElementById("idActivity");
                          info.result.forEach((item, i) => {
                              var myOption = document.createElement("option");
                              myOption.value = item.idActividad;
                              myOption.innerHTML = item.eNombre + "/" + item.tipo + " - " + item.nombre;
                              mySelect.appendChild(myOption);
                          });
                      }
                  },
                  error: function(error) {
                      console.log(error);
                  }
          });
                $('.contenido-modal').height('350px');
                abrirModal();
                return false;
          }

          function deleteActDependency(sender) {
              event.preventDefault();

              if (confirm("Are you sure to delete dependency activity?")) {
                  var trObj = $(sender).closest("tr");
                  var idReqActivity = trObj.attr('id');

                  $.ajax({
                      type:'POST',
                      url:'js/ajax.php',
                      async: true,
                      data: {
                          accion: 'eliminarActividadRequerida',
                          id: idReqActivity
                      },
                      // data: 'accion=editarEnsamble',
                      success:function(response) {
                          var info = JSON.parse(response);
                          console.log(info.result);
                          if (info.result == "deleted") {
                              trObj.remove();
                              mostrarAlerta("success","Activity Removed");
                          }else {
                              mostrarAlerta("danger","Cannot Remove Activity");
                          }
                      },
                      error: function(error) {
                          console.log(error);
                      }
                  });
              }
          }

          function addActReq(sender) {
              event.preventDefault();
              var idActivity = <?php echo $id; ?>;
              var idReqActivity = $('#idActivity').val();

              $.ajax({
                  url: 'js/ajax.php',
                  type: 'POST',
                  async: true,
                  data: {
                      accion: 'nuevaActividadRequerida',
                      idActivity: idActivity,
                      idReqActivity: idReqActivity
                  },
                  success: function(response) {
                      // console.log(response);
                      if (response != "error") {
                          console.log(response);
                          // var info = JSON.parse(response);
                          var info = JSON.parse(response);
                          // console.log(info);
                          var idActividadDependencia = info.result.idActividadDependencia;
                          var idActRequerida = info.result.idActRequerida;
                          var nombre = info.result.nombre;
                          var tipo = info.result.tipo;
                          var descripcion = info.result.descripcion;
                          var eNombre = info.result.eNombre;
                          var resp = info.result.resp;

                          $('#areaTabla').prepend("<tr id='" + idActividadDependencia + "'>" +
                                                    "<td>"+ idActRequerida + "</td>" +
                                                    "<td>" + nombre + "</td>" +
                                                    "<td>" + eNombre + "</td>" +
                                                    "<td>" + tipo + "</td>" +
                                                    "<td>" + resp + "</td>" +

                          <?php if (in_array(5, $_SESSION["permisos"])) {  ?>
                                "<td>" +
                                "<div class='' style='display: flex; justify-content: space-evenly;'>" +
                                    "<a class='editBtn' href='#' onclick='deleteActDependency(this)'>" +
                                        "<div class='icon-container'>" +
                                            "<div class='cross-icon'></div>" +
                                        "</div>" +
                                    "</a>" +
                                "</div>" +
                          <?php } ?>
                          "</td>" +
                          "</tr>");
                          cerrarModal2();
                      }
                  },
                  error: function(error) {
                  console.log(error);
                  }
              });
          }

          function editMode(sender) {
              event.preventDefault();
              //hide edit span
              $(sender).closest("tr").find(".editSpan").hide();
              $(sender).closest("tr").find(".editBtn").hide();
              $(sender).closest("tr").find(".deleteBtn").show();
              $(sender).closest("tr").find(".editInput").show();
              $(sender).closest("tr").find(".guardarBtn").show();
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

              trObj.find(".editInput.sales").val(trObj.find(".editSpan.sales").text());
              trObj.find(".editInput.notas").val(trObj.find(".editSpan.notas").text());
          }

      </script>

      <?php include "inc/footer.html"; ?>
