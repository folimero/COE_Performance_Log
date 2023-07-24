<?php

 ?>
<script type="text/javascript">
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

      trObj.find(".editInput.numParte").val(trObj.find(".editSpan.numParte").text());
      trObj.find(".editInput.workorder").val(trObj.find(".editSpan.workorder").text());
      trObj.find(".editInput.cantReq").val(trObj.find(".editSpan.cantReq").text());
      trObj.find(".editInput.cantTerm").val(trObj.find(".editSpan.cantTerm").text());
      trObj.find(".editInput.notas").val(trObj.find(".editSpan.notas").text());
      // mostrarAlerta('warning','Cancelado.');
    }

    function assignAwarded(sender) {
      event.preventDefault();
      var idProyecto = <?php echo $id; ?> ;

      $.ajax({
        type: 'POST',
        url: '../../js/ajax.php',
        async: true,
        data: {
          accion: 'checkCompletedActivities',
          idProyecto: idProyecto
        },
        success: function(response) {
          if (response == 'completed') {
            if (confirm('Project Awarded?')) {
              $.ajax({
                type: 'POST',
                url: '../../js/ajax.php',
                async: true,
                data: {
                  accion: 'awardedProject',
                  idProyecto: idProyecto
                },
                success: function(response) {
                  if (response == 'success') {
                    mostrarAlerta('success', 'Project Awarded!.');
                    $('#awarded').html("YES");
                    $('#awarded').css("background-color", "lightgreen");
                    $(sender).remove();
                  }
                },
                error: function(error) {
                  console.log(error);
                }
              });
            }
          } else {
            if (confirm('There are activities pending to complete,\nContinue?')) {
              $.ajax({
                type: 'POST',
                url: '../../js/ajax.php',
                async: true,
                data: {
                  accion: 'awardedProject',
                  idProyecto: idProyecto
                },
                success: function(response) {
                  if (response == 'success') {
                    mostrarAlerta('success', 'Project Awarded!.');
                    $('#awarded').html("YES");
                    $('#awarded').css("background-color", "lightgreen");
                    $(sender).remove();
                  } else {
                    console.log(response);
                  }
                },
                error: function(error) {
                  console.log(error);
                }
              });
            }
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
    }

    function cancelAwarded(sender) {
      event.preventDefault();
      var idProyecto = <?php echo $id; ?> ;

      if (confirm('Cancel Awarded?')) {
        $.ajax({
          type: 'POST',
          url: '../../js/ajax.php',
          async: true,
          data: {
            accion: 'cancelAwardedProject',
            idProyecto: idProyecto
          },
          success: function(response) {
            if (response == 'success') {
              mostrarAlerta('success', 'Awarded canceled!.');
              $('#awarded').html("NO");
              $('#awarded').css("background-color", "#f1f1f1");
              $(sender).remove();
            }
          },
          error: function(error) {
            console.log(error);
          }
        });
      }
    }

    function abrirVentanaPrioridad() {
      event.preventDefault();
      var titulo = "Change Priority";
      var idProyecto = <?php echo $id; ?> ;
      var idPrioridad = $('#prioridad').attr('idPrioridad');
      // var idStatus = $('#statusLabel').attr('idStatus');

      $(".contenido-modal").html("<div class='flex-container' style='margin-top: 60px;'>" +
        "<!-- Titulo -->" +
        "<h1 id='tittle'>" + titulo + "</h1>" +
        "<a class='btn-cerrar' onclick='cerrarModal2()'>" +
        "<div class='icon-container'>" +
        "<div class='cross-icon'></div>" +
        "</div>" +
        "</a>" +
        "<form id='form_empleados' action='' onsubmit='return cambiarPrioridad()'>" +
        "<input type='hidden' name='idProyecto' id='idProyecto' value='" + idProyecto + "'>" +
        "<label for='idPrioridad'>Priority</label>" +
        "<div class='inline-container'>" +
        "<select name='idPrioridad' id='idPrioridad' required>" +
        "<option disabled selected value> -- Select -- </option>" +
        "<option value='3'>LOW</option>" +
        "<option value='2'>MEDIUM</option>" +
        "<option value='1'>HIGH</option>" +
        "</select>" +
        "</div>" +
        "<input type='submit' id='btnPrioridad' value='Register'>" +
        "</form>" +
        "</div>");


      abrirModal();
      return false;
    }

    function cambiarPrioridad() {
      event.preventDefault();
      var idProyecto = <?php echo $id; ?> ;
      var idPrioridad = $('#idPrioridad').val();

      $.ajax({
        type: 'POST',
        url: '../../js/ajax.php',
        async: true,
        data: {
          accion: 'cambiarPrioridad',
          idProyecto: idProyecto,
          idPrioridad: idPrioridad
        },
        success: function(response) {
          var info = JSON.parse(response);
          console.log(info);
          if (info.result == 'success') {
            mostrarAlerta('success', 'Priority Changed!.');
            $('#prioridad').html(info.priority);
            cerrarModal2();
          } else {
            mostrarAlerta('warning', 'Error on changing Priority.');
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
    }

    function guardarEnsamble(sender) {
      event.preventDefault();
      var trObj = $(sender).closest("tr");
      var idEnsamble = $(sender).closest("tr").attr('id');
      var numParte = trObj.find(".editInput.numParte").val();
      var workorder = trObj.find(".editInput.workorder").val();;
      var cantReq = trObj.find(".editInput.cantReq").val();
      var cantTerm = trObj.find(".editInput.cantTerm").val();
      var notas = trObj.find(".editInput.notas").val();
      // alert(notas);
      $.ajax({
        type: 'POST',
        url: '../../js/ajax.php',
        async: true,
        data: {
          accion: 'editarEnsamble',
          idEnsamble: idEnsamble,
          numParte: numParte,
          workorder: workorder,
          cantReq: cantReq,
          cantTerm: cantTerm,
          notas: notas
        },
        // data: 'accion=editarEnsamble',
        success: function(response) {
          var info = JSON.parse(response);
          console.log(info);
          if (info.result) {
            trObj.find(".editSpan.numParte").text(info.result.numParte);
            trObj.find(".editSpan.workorder").text(info.result.workorder);
            trObj.find(".editSpan.cantReq").text(info.result.cantReq);
            trObj.find(".editSpan.cantTerm").text(info.result.cantTerm);
            trObj.find(".editSpan.notas").text(info.result.notas);

            trObj.find(".editInput.numParte").text(info.result.numParte);
            trObj.find(".editInput.workorder").text(info.result.workorder);
            trObj.find(".editInput.cantReq").text(info.result.cantReq);
            trObj.find(".editInput.cantTerm").text(info.result.cantTerm);
            trObj.find(".editInput.notas").text(info.result.notas);

            trObj.find(".editInput").hide();
            trObj.find(".guardarBtn").hide();
            trObj.find(".deleteBtn").hide();
            trObj.find(".editSpan").show();
            trObj.find(".editBtn").show();
            mostrarAlerta('success', 'Changes made.');
          } else {
            alert(response.result);
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
    }

    function editarActividad(sender) {
      event.preventDefault();
      var trObj = $(sender).closest("tr");
      var idActividades_proyecto = $(sender).closest("tr").attr('id');
      var fechaInicio = trObj.find(".editInput.fechaInicio").val();
      var fechaRequerida = trObj.find(".editInput.fechaRequerida").val();
      var ubicacion = trObj.find(".editInput.ubicacion").val();
      var notas = trObj.find(".editInput.notas").val();

      // alert(fechaRequerida);
      // alert(notas);
      $.ajax({
        type: 'POST',
        url: '../../js/ajax.php',
        async: true,
        data: {
          accion: 'editarActUbicacion',
          idActividades_proyecto: idActividades_proyecto,
          fechaInicio: fechaInicio,
          fechaRequerida: fechaRequerida,
          ubicacion: ubicacion,
          notas: notas,
        },
        // data: 'accion=editarActUbicacion',
        success: function(response) {
          var info = JSON.parse(response);
          console.log(info);
          if (info.result) {
            if (info.result.fechaRequerida < "<?php echo date("Y-m-d"); ?>") {
                trObj.find(".editSpan.fechaRequerida").closest("tr").css("background-color", "#F1948A");
            }else {
                trObj.find(".editSpan.fechaRequerida").closest("tr").css("background-color", "white");
            }
            trObj.find(".editSpan.fechaRequerida").text(info.result.fechaRequerida);
            trObj.find(".editSpan.ubicacion").text(info.result.ubicacion);
            trObj.find(".editSpan.notas").text(info.result.notas);

            trObj.find(".editInput.fechaRequerida").text(info.result.fechaRequerida);
            trObj.find(".editInput.ubicacion").text(info.result.ubicacion);
            trObj.find(".editInput.notas").text(info.result.notas);

            trObj.find(".editInput").hide();
            trObj.find(".guardarBtn").hide();
            trObj.find(".deleteBtn").hide();
            trObj.find(".editSpan").show();
            trObj.find(".editBtn").show();
            mostrarAlerta('success', 'Changes made.');
          } else {
            alert(response.result);
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
    }

    function abrirVentanaStatus(e) {
      e.preventDefault();
      $('#tittle').html('Status Change');

      var idStatus = $('#statusLabel').attr('idStatus');
      $.ajax({
        url: '../../js/ajax.php',
        type: 'POST',
        async: true,
        data: {
          accion: 'cargarListaStatus',
          idStatus: idStatus
        },
        success: function(response) {
          // console.log(response);
          if (!response != "error") {
            var info = JSON.parse(response);
            // console.log(info.result);

            $(".contenido-modal").html("<div class='flex-container' style='margin-top: 60px;'>" +
              "<!-- Titulo -->" +
              "<h1 id='tittle'>Status Change</h1>" +
              "<a class='btn-cerrar' onclick='cerrarModal2()'>" +
              "<div class='icon-container'>" +
              "<div class='cross-icon'></div>" +
              "</div>" +
              "</a>" +
              "<form id='form_empleados' action='' onsubmit='return cambiarStatus(event)'>" +
              "<div class='input-field'>" +
              "<!-- Lista Status -->" +
              "<label for='idStatus'>Status</label>" +
              "<div class='inline-container'>" +
              "<select name='idStatus' id='idStatus' required>" +
              "</select>" +
              "<!-- Boton de Selector -->" +
              "<div class='icon-container'>" +
              "<a href='/status.php?id=<?php echo $id ?>'>" +
              "<div class='plus-icon'></div>" +
              "</a>" +
              "</div>" +
              "</div>" +
              "</div>" +
              "<input type='submit' id='btnNota' value='Change'>" +
              "</form>" +
              "</div>");
            var mySelect = document.getElementById("idStatus");
            info.result.forEach((item, i) => {
              var myOption = document.createElement("option");
              myOption.value = item.idStatus;
              myOption.innerHTML = item.nombre;
              mySelect.appendChild(myOption);
            });
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
      abrirModal();
      return false;
    }

    function cambiarStatus() {
      var idProyecto = <?php echo $id; ?> ;
      var idStatus = $('#idStatus').val();
      $.ajax({
        url: '../../js/ajax.php',
        type: 'POST',
        async: true,
        data: {
          accion: 'cambiarStatusProyecto',
          idProyecto: idProyecto,
          idStatus: idStatus
        },
        success: function(response) {
          // console.log(response);
          if (!response != "error") {
            // console.log(response);
            var info = JSON.parse(response);
            // console.log(info);
            $('#statusLabel').html(info.result.nombre);
            $('#statusLabel').attr("idStatus", info.result.idStatus);
            $('#statusLabel').removeClass();
            switch (info.result.nombre) {
              case "YELLOW STATUS":
                $('#statusLabel').addClass("yellow_status");
                break;
              case "GREEN STATUS":
                $('#statusLabel').addClass("green_status");
                break;
              case "RED STATUS":
                $('#statusLabel').addClass("red_status");
                break;
              default:
                $('#statusLabel').addClass("neutral_status");
            }
            mostrarAlerta('success', 'Status Changed.');
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
      cerrarModal2();
      return false;
    }

    function nuevaNota(e) {
      e.preventDefault();
      $('.contenido-modal').html("<div class='flex-container' style='margin-top: 60px;'>" +
        "<!-- Titulo -->" +
        "<h1 id='tittle'>Notes</h1>" +
        "<a class='btn-cerrar' onclick='cerrarModal2()'>" +
        "<div class='icon-container'>" +
        "<div class='cross-icon'></div>" +
        "</div>" +
        "</a>" +
        "<!-- Formulario -->" +
        "<form id='form_empleados' action='' onsubmit='return modificacionNota(event)'>" +
        "<!-- ID -->" +
        "<input type='hidden' name='idProyectoNota' id='idProyectoNota' value=''>" +
        "<input type='hidden' name='idNota' id='idNota' value=''>" +
        "<input type='hidden' name='idUsuario' id='idUsuario' value=''>" +
        "<!-- Campo Nota -->" +
        "<div class='input-field'>" +
        "<label for='nota'>Note</label>" +
        "<textarea name='nota' rows='4' cols='50' id='nota' style='resize: none; width: 100%; height: 100px;' required></textarea>" +
        "</div>" +
        "<!-- Button Submit -->" +
        "<input type='submit' id='btnNota' value='Add Note'>" +
        "</form>" +
        "</div>");
      $('#tittle').html('New Note');
      $('#idProyectoNota').val('<?php echo $id; ?>');
      $('#idNota').val('0');

      $('#idUsuario').val( <?php echo $_SESSION['idUsuario']; ?> );
      abrirModal();
    }

    function editarNota(id) {
      event.preventDefault();
      $('.contenido-modal').html("<div class='flex-container' style='margin-top: 60px;'>" +
        "<!-- Titulo -->" +
        "<h1 id='tittle'>Notes</h1>" +
        "<a class='btn-cerrar' onclick='cerrarModal2()'>" +
        "<div class='icon-container'>" +
        "<div class='cross-icon'></div>" +
        "</div>" +
        "</a>" +
        "<!-- Formulario -->" +
        "<form id='form_empleados' action='' onsubmit='return modificacionNota(event)'>" +
        "<!-- ID -->" +
        "<input type='hidden' name='idProyectoNota' id='idProyectoNota' value=''>" +
        "<input type='hidden' name='idNota' id='idNota' value=''>" +
        "<input type='hidden' name='idUsuario' id='idUsuario' value=''>" +
        "<!-- Campo Nota -->" +
        "<div class='input-field'>" +
        "<label for='nota'>Note</label>" +
        "<textarea name='nota' rows='4' cols='50' id='nota' style='resize: none; width: 100%; height: 100px;' required></textarea>" +
        "</div>" +
        "<!-- Button Submit -->" +
        "<input type='submit' id='btnNota' value='Edit'>" +
        "</form>" +
        "</div>");
      $('#tittle').html('Note Edition');

      $.ajax({
        url: '../../js/ajax.php',
        type: 'POST',
        async: true,
        data: {
          accion: 'mostrarNota',
          idNota: id
        },
        success: function(response) {
          // console.log(response);
          if (!response != "error") {
            // console.log(response);
            var info = JSON.parse(response);
            // console.log(info);
            $('#idNota').val(info.result.id);
            $('#nota').val(info.result.nota);
            $('#idProyectoNota').val('0');
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
      abrirModal();
      return false;
    }

    function deleteNota(id) {
      $.ajax({
        url: '../../js/ajax.php',
        type: 'POST',
        async: true,
        data: {
          accion: 'eliminarNota',
          idProyectoNota: id,
        },
        success: function(response) {
          // console.log(response);
          if (!response != "error") {
            $("#nota" + id).html("");
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
      return false;
    }

    function modificacionNota(e) {
      e.preventDefault();
      var idNota = $('#idNota').val();
      var idProyecto = $('#idProyectoNota').val();
      var idUsuario = $('#idUsuario').val();
      var nota = $('#nota').val();

      if (idNota == 0) {
        $.ajax({
          url: '../../js/ajax.php',
          type: 'POST',
          async: true,
          data: {
            accion: 'nuevaNota',
            idProyecto: idProyecto,
            idUsuario: idUsuario,
            nota: nota
          },
          success: function(response) {
            // console.log(response);
            if (!response != "error") {
              // console.log(response);
              // var info = JSON.parse(response);
              var info = JSON.parse(response);
              console.log(info);
              var id = info.result.idProyectoNota;
              var nota = info.result.nota;
              var nombre = info.result.nombre;
              var fecha = info.result.fechaCrea;

              $('#areaNotas').prepend(" <div class='card' id='nota" + id + "'>" +
                "<div class='inline-containter' style='width: 100%; display: inline-flex;'>" +
                "<div class='column' style='width: 70%;'>" +
                "<p id='notaText" + id + "'>" + nota + "</p>" +
                "</div>" +
                "<div class='column' style='width: 20%;'>" +
                "<h5>by " + nombre + "</h5>" +
                "<p>on " + fecha + "</p>" +
                "</div>" +
                "<div class='column' style='width: 10%; padding: 0; display: inline-flex;'>" +
                "<div class='inline-container' style='justify-content: space-evenly;'>" +
                "<div class='icon-container'>" +
                "<a href='#' onclick='editarNota(" + id + ")'>" +
                "<div class='plus-icon-yellow'></div>" +
                "</a>" +
                "</div>" +
                "<a href='#' onclick='deleteNota(" + id + "); return false;'>" +
                "<div class='icon-container'>" +
                "<div class='cross-icon'></div>" +
                "</div>" +
                "</a>" +
                "</div>" +
                "</div>" +
                "</div>" +
                "</div>");
              cerrarModal2();
            }
          },
          error: function(error) {
            console.log(error);
          }
        });
      } else {
        $.ajax({
          url: '../../js/ajax.php',
          type: 'POST',
          async: true,
          data: {
            accion: 'actualizarNota',
            idProyectoNota: idNota,
            nota: nota
          },
          success: function(response) {
            // console.log(response);
            if (!response != "error") {
              // console.log(response);
              // var info = JSON.parse(response);
              // var info = JSON.parse(response);
              // console.log(info);
              // var id = info.result.idProyectoNota;
              $("#notaText" + idNota).html(response);
              cerrarModal2();
            } else {
              console.log("ERROR");
            }
          },
          error: function(error) {
            console.log(error);
          }
        });
      }
      return false;
    }

    function addAditionalResource(){
        event.preventDefault();
        var activity = $('#selectActivity').val();
        var resource = $('#selectResource').val();
        if (!activity) {
            alert("Please select Activity");
        } else {
            if (!resource) {
                alert("Please select Resource");
            } else {
                $.ajax({
                    url: '../../js/ajax.php',
                    type: 'POST',
                    async: true,
                    data: {
                        accion: 'agregarRecurso',
                        idActividades_proyecto: activity,
                        idUsuario: resource
                    },
                    success: function(response) {
                        // console.log(response);
                        if (!response != "error") {
                            $('#selectActivity').prop("selectedIndex", 0);
                            $('#selectResource').prop("selectedIndex", 0);
                            mostrarAlerta('success', 'Resource Added!.');

                            var info = JSON.parse(response);
                            if (info.result) {

                                $('#tbody') // select table tbody
                                .append(addTableRow(info.result.idRecursosAdicionales, info.result.aNombre, info.result.eNombre, info.result.fechaInicio, info.result.fechaRequerida)) // apend table row
                            }
                        } else {
                            console.log("ERROR");
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            }
        }
    }

    function deleteResource(sender){
        event.preventDefault();
        if (confirm("Are you sure to delete resource?")) {
            var trObj = $(sender).closest("tr");
            var idRecursosAdicionales = trObj.attr('id');

            $.ajax({
                type:'POST',
                url:'../../js/ajax.php',
                async: true,
                data: {
                  accion: 'eliminarRecurso',
                  idRecursosAdicionales: idRecursosAdicionales
                },
                // data: 'accion=editarEnsamble',
                success:function(response) {
                    var info = JSON.parse(response);

                    if (info.result == "deleted") {
                        trObj.remove();
                        mostrarAlerta("success","Resource Deleted");
                    }else {
                        mostrarAlerta("danger","Cannot Delete Resource");
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
    }

    function addTableRow(idRecursosAdicionales, aNombre, eNombre, fechaInicio, fechaRequerida) {
        var newRow =  "<tr id='" + idRecursosAdicionales + "'>" +
                          "<td>" + aNombre + "</td>" +
                          "<td>" + eNombre + "</td>" +
                          "<td><span class='editSpan fechaInicio'>" + fechaInicio + "</span>" +
                          "<input class='editInput fechaInicio' type='date' name='fechaInicio' value='" + fechaInicio + "' style='display: none;'></td>" +
                          "<td><span class='editSpan fechaRequerida'></span>" +
                          "<input class='editInput fechaRequerida' type='date' name='fechaRequerida' value='' style='display: none;'></td>" +
                          "<td>" +

                          "</td>" +
                          "<td><span class='editSpan ubicacion'></span>" +
                          "<input class='editInput ubicacion' type='text' name='ubicacion' value='' style='display: none;'></td>" +
                          "<td><span class='editSpan comentarios'></span>" +
                          "<input class='editInput comentarios' type='text' name='comentarios' value='' style='display: none;'></td>" +
                          "<td>" +
                              "<div class='' style='display: flex; justify-content: space-evenly;'>" +
                                  "<a class='editBtn' href='#' onclick='editMode(this)'>" +
                                      "<div class='icon-container'>" +
                                          "<div class='plus-icon-yellow'></div>" +
                                      "</div>" +
                                  "</a>" +
                                  "<a class='editBtn ms-2' href='#' onclick='deleteResource(this)' style='display: flex; justify-content: space-evenly; align-items: center;'>" +
                                      "<i class='fa fa-trash fa-2x' style='color: red;'></i>" +
                                  "</a>" +
                                  "<a class='guardarBtn' href='#' onclick='editarRecursoAdicional(this)' style='display: none;'>" +
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

    function editarRecursoAdicional(sender){
        event.preventDefault();
        var trObj = $(sender).closest("tr");
        var idRecursosAdicionales = $(sender).closest("tr").attr('id');
        var fechaInicio = trObj.find(".editInput.fechaInicio").val();
        var fechaRequerida = trObj.find(".editInput.fechaRequerida").val();
        var ubicacion = trObj.find(".editInput.ubicacion").val();
        var comentarios = trObj.find(".editInput.comentarios").val();

        if (!fechaRequerida) {
            fechaRequerida = null;
        }
        // return alert(fechaRequerida);
        // alert(notas);
        $.ajax({
          type: 'POST',
          url: '../../js/ajax.php',
          async: true,
          data: {
            accion: 'editarRecursoAdicional',
            idRecursosAdicionales: idRecursosAdicionales,
            fechaInicio: fechaInicio,
            fechaRequerida: fechaRequerida,
            ubicacion: ubicacion,
            comentarios: comentarios,
          },
          // data: 'accion=editarActUbicacion',
          success: function(response) {
              var info = JSON.parse(response);
              console.log(info);
              if (info.result) {
                  if (info.result.fechaRequerida < "<?php echo date("Y-m-d"); ?>") {
                      trObj.find(".editSpan.fechaRequerida").closest("tr").css("background-color", "#F1948A");
                  }else {
                      trObj.find(".editSpan.fechaRequerida").closest("tr").css("background-color", "white");
                  }
                  trObj.find(".editSpan.fechaInicio").text(info.result.fechaInicio);
                  trObj.find(".editSpan.fechaRequerida").text(info.result.fechaRequerida);
                  trObj.find(".editSpan.ubicacion").text(info.result.ubicacion);
                  trObj.find(".editSpan.comentarios").text(info.result.comentarios);

                  trObj.find(".editInput.fechaInicio").text(info.result.fechaInicio);
                  trObj.find(".editInput.fechaRequerida").text(info.result.fechaRequerida);
                  trObj.find(".editInput.ubicacion").text(info.result.ubicacion);
                  trObj.find(".editInput.comentarios").text(info.result.comentarios);

                  trObj.find(".editInput").hide();
                  trObj.find(".guardarBtn").hide();
                  trObj.find(".deleteBtn").hide();
                  trObj.find(".editSpan").show();
                  trObj.find(".editBtn").show();
                  mostrarAlerta('success', 'Changes made.');
              } else {
                  alert(response.result);
              }
          },
          error: function(error) {
              console.log(error);
          }
        });
    }
</script>
