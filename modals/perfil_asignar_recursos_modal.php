
<?php

if (isset($_GET['idActividades_proyecto']) && isset($_GET['idEmpleado'])) {
        include_once "../inc/conexion.php";
        $idActividades_proyecto = $_GET['idActividades_proyecto'];
        $idEmpleado = $_GET['idEmpleado'];

        $stmt = $dbh->prepare("SELECT ra.idRecurso, ra.idActividades_proyecto, a.nombre AS aNombre, e.nombre AS eNombre, DATE(ra.fechaInicio) AS fechaInicio, ra.horas
                                FROM recursos_asignados AS ra
                                INNER JOIN actividades_proyecto AS ap
                                ON ra.idActividades_proyecto = ap.idActividades_proyecto
                                INNER JOIN actividad AS a
                                ON ap.idActividad = a.idActividad
                                INNER JOIN empleado AS e
                                ON ra.idEmpleado = e.idEmpleado
                                WHERE ra.idActividades_proyecto = $idActividades_proyecto AND ra.idEmpleado = $idEmpleado
                                ORDER BY ra.fechaInicio DESC");
        $stmt->execute();
        $result = $stmt->fetchAll();
    }


?>

<div class=container>
    <a class='btn-cerrar' onclick='cerrarModal()'>
        <div class='icon-container'>
            <div class='cross-icon'></div>
        </div>
    </a>
    <div class="row">
        <div class="col-12">
            <h3>Activity</h3>
        </div>
        <div class="col-12">
            <div class='input-field'>
                <label for='idActividad'>Activity</label>
                <input name='idActividad' id='idActividad' style='text-align:center; font-weight:bold; background-color: AliceBlue;' type='text' value='<?php echo $result[0]->idActividades_proyecto; ?>' disabled>
            </div>
            <div class='input-field'>
                <label for='nombre'>Name</label>
                <input name='nombre' id='nombre' type='text' style='text-align:center; font-weight:bold; background-color: AliceBlue;' value='<?php echo $result[0]->aNombre; ?>' disabled>
            </div>
        </div>

        <hr style='width:30%;margin: 30px 0px; text-align:center;margin-left:0'>
        <div class="col-12">
            <h3>Resource Allocation</h3>
        </div>

        <div class="col-12 mb-2">
            <a href="#" onclick="insertarNuevoRecurso()">
                <input class="text-white" name='nombreEmpleado' id='nombreEmpleado' style='text-align:center; font-weight:bold; background-color: DarkCyan;' type='text' value='<?php echo $result[0]->eNombre; ?>' disabled>
            </a>
        </div>
        <div class="col-12">
            <table class="table" id="myTable">
                <thead>
                    <tr>
                        <th scope="col">Day</th>
                        <th scope="col">Hours</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <?php foreach ($result as $recurso) { ?>
                        <tr id='<?php echo $recurso->idRecurso  ?>'>
                            <td>
                                <span class='editSpan fecha'><?php echo $recurso->fechaInicio; ?></span>
                                <input class='editInput fecha' type='text' name='fecha' value='<?php echo $recurso->fechaInicio; ?>' style='display: none;'>
                            </td>
                            <td>
                                <span class='editSpan horas'><?php echo $recurso->horas; ?></span>
                                <input class='editInput horas' type='text' name='horas' value='<?php echo $recurso->horas; ?>' style='display: none;'>
                            </td>
                            <td>
                                <div class='' style='display: flex; justify-content: space-evenly;'>
                                    <a class='editBtn' href='#' onclick='editMode(this)'>
                                        <div class='icon-container'>
                                            <div class='plus-icon-yellow'></div>
                                        </div>
                                    </a>
                                    <a class='guardarBtn' href='#' onclick='editarRecurso(this)' style='display: none;'>
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
                            </td>
                        </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#cerrar_alerta').click(function() {
            $('.alerta').removeClass('mostrar');
            $('.alerta').addClass('ocultar');
        });

        $('#myTable').DataTable({
            responsive: true,
            searching: false,
            paging: false,
            info: false,
            aaSorting: [0,'desc'],
        });
    });

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

        trObj.find(".editInput.fecha").val(trObj.find(".editSpan.fecha").text());
        trObj.find(".editInput.horas").val(trObj.find(".editSpan.horas").text());
        // mostrarAlerta('warning','Cancelado.');
    }

    function editarRecurso(sender) {
        event.preventDefault();
        var trObj = $(sender).closest("tr");
        var idRecurso = trObj.attr('id');
        var fecha = trObj.find(".editInput.fecha").val();
        var horas = trObj.find(".editInput.horas").val();
        // alert(notas);
        $.ajax({
            type: 'POST',
            url: 'js/ajax.php',
            async: true,
            data: {
                accion: 'editarRecurso',
                idRecurso: idRecurso,
                fechaInicio: fecha,
                horas: horas,
            },
            // data: 'accion=editarActUbicacion',
            success: function(response) {
                var info = JSON.parse(response);
                console.log(info);
                if (info.result) {
                    if (info.result == "fechaIncorrecta") {
                        mostrarAlerta('danger', 'Date Format Incorrect.');
                    } else {
                        trObj.find(".editSpan.fecha").text(info.result.fechaInicio);
                        trObj.find(".editSpan.horas").text(info.result.horas);

                        trObj.find(".editInput.fecha").text(info.result.fechaInicio);
                        trObj.find(".editInput.horas").text(info.result.horas);

                        trObj.find(".editInput").hide();
                        trObj.find(".guardarBtn").hide();
                        trObj.find(".deleteBtn").hide();
                        trObj.find(".editSpan").show();
                        trObj.find(".editBtn").show();
                        mostrarAlerta('success', 'Changes made.');
                    }
                } else {
                    alert(response.result);
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function insertarNuevoRecurso() {
      event.preventDefault();

      var idActividades_proyecto = <?php echo $idActividades_proyecto; ?>;
      var idEmpleado = <?php echo $idEmpleado; ?>;

      $.ajax({
          type: 'POST',
          url: 'js/ajax.php',
          async: true,
          data: {
              accion: 'insertarRecursoNuevo',
              idActividades_proyecto: idActividades_proyecto,
              idEmpleado: idEmpleado,
          },
          // data: 'accion=editarActUbicacion',
          success: function(response) {
              var info = JSON.parse(response);
              console.log(info);
              if (info.result) {

                  $('#tbody') // select table tbody
                  .prepend(addTableRow(info.result.idRecurso, info.result.fechaInicio, info.result.horas)) // prepend table row
                  //
                  // trObj.find(".editInput").hide();
                  // trObj.find(".guardarBtn").hide();
                  // trObj.find(".deleteBtn").hide();
                  // trObj.find(".editSpan").show();
                  // trObj.find(".editBtn").show();
                  var trObj = $("#" + info.result.idRecurso + "");
                  editMode(trObj);

                  // mostrarAlerta('success', 'Changes made.');
              } else {
                  alert(response);
              }
          },
          error: function(error) {
              console.log(error);
          }
      });
    }

    function addTableRow(idRecurso, date, horas) {
        var newRow =  "<tr id='" + idRecurso + "'>" +
                          "<td>" +
                              "<span class='editSpan fecha'>" + date + "</span>" +
                              "<input class='editInput fecha' type='text' name='fecha' value='" + date + "' style='display: none;'>" +
                          "</td>" +
                          "<td>" +
                              "<span class='editSpan horas'>" + horas + "</span>" +
                              "<input class='editInput horas' type='text' name='horas' value='" + horas + "' style='display: none;'>" +
                          "</td>" +
                          "<td>" +
                              "<div class='' style='display: flex; justify-content: space-evenly;'>" +
                                  "<a class='editBtn' href='#' onclick='editMode(this)'>" +
                                      "<div class='icon-container'>" +
                                          "<div class='plus-icon-yellow'></div>" +
                                      "</div>" +
                                  "</a>" +
                                  "<a class='guardarBtn' href='#' onclick='editarRecurso(this)' style='display: none;'>" +
                                      "<div class='icon-container'>" +
                                          "<div class='plus-icon-green'></div>" +
                                      "</div>" +
                                  "</a>" +
                                  "<a class='deleteBtn' href='#' onclick='cancel(this)' style='display: none;'>" +
                                      "<div class='icon-container'>" +
                                          "<div class='cross-icon'></div>" +
                                      "</div>" +
                                  "</a>" +
                              "</div>" +
                          "</td>" +
                      "</tr>";
        return newRow;
    }
</script>
