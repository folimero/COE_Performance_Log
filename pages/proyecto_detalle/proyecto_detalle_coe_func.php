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
      var ubicacion = trObj.find(".editInput.ubicacion").val();
      var notas = trObj.find(".editInput.notas").val();
      // alert(notas);
      $.ajax({
        type: 'POST',
        url: '../../js/ajax.php',
        async: true,
        data: {
          accion: 'editarActUbicacion',
          idActividades_proyecto: idActividades_proyecto,
          ubicacion: ubicacion,
          notas: notas,
        },
        // data: 'accion=editarActUbicacion',
        success: function(response) {
          var info = JSON.parse(response);
          console.log(info);
          if (info.result) {
            trObj.find(".editSpan.ubicacion").text(info.result.ubicacion);
            trObj.find(".editSpan.notas").text(info.result.notas);

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
              "<a href='status.php?id=<?php echo $id ?>'>" +
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

    function abrirVentanaCorto(e) {
      e.preventDefault();
      $('#tittle').html('Shortage Material');
      // var idStatus = $('#statusLabel').attr('idStatus');
      $.ajax({
        url: '../../js/ajax.php',
        type: 'POST',
        async: true,
        data: {
          accion: 'cargarListadoCorto',
          idProyecto: <?php echo $id; ?>
        },
        success: function(response) {
          // console.log(response);
          if (!response != "error") {
            var info = JSON.parse(response);
            // console.log(info.result);
            if (info.result.longestMaterial == null) {
              var material = "";
            } else {
              var material = info.result.longestMaterial;
            }
            $(".contenido-modal").height(430);
            $(".contenido-modal").html("<div class='flex-container' style='margin-top: 60px;'>" +
              "<!-- Titulo -->" +
              "<h1 id='tittle'>Shortage Material</h1>" +
              "<a class='btn-cerrar' onclick='cerrarModal2()'>" +
              "<div class='icon-container'>" +
              "<div class='cross-icon'></div>" +
              "</div>" +
              "</a>" +
              "<form id='form_empleados' action='' onsubmit='return cambiarCorto(event)'>" +
              "<input type='hidden' name='idProyecto' id='idProyecto' value='" + info.result.idProyecto + "'>" +
              "<div class='input-field'>" +
              "<label for='material'>Material</label>" +
              "<input name='material' type='text' id='material' maxlength='30' value='" + material + "' required>" +
              "</div>" +
              "<div class='input-field'>" +
              "<label for='fechaETA'>ETA:</label>" +
              "<input type='date' id='fechaETA' name='fechaETA' value='" + info.result.longestETA + "' min='2021-01-01' required>" +
              "</div>" +
              "<input type='submit' id='btnCorto' value='Assign'>" +
              "</form>" +
              "</div>");
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
      abrirModal();
      return false;
    }

    function cambiarCorto() {
      var idProyecto = <?php echo $id; ?> ;
      var material = $('#material').val();
      var fechaETA = $('#fechaETA').val();
      $.ajax({
        url: '../../js/ajax.php',
        type: 'POST',
        async: true,
        data: {
          accion: 'asignarCorto',
          idProyecto: idProyecto,
          longestMaterial: material,
          longestETA: fechaETA
        },
        success: function(response) {
          // console.log(response);
          if (!response != "error") {
            // console.log(response);
            var info = JSON.parse(response);
            console.log(info);
            $('#longestETA').html(info.result.longestMaterial + " - " + info.result.longestETA);
            mostrarAlerta('success', 'Material Assigned.');
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
      cerrarModal2();
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
          if (response == 'success') {
            mostrarAlerta('success', 'Priority Changed!.');
            $('#prioridad').html("YES");
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

    function actualizarStage() {
      event.preventDefault();
      var idProyecto = <?php echo $id; ?> ;
      var newStageID = $('#idPrioridad').val();

      $.ajax({
        type: 'POST',
        url: '../../js/ajax.php',
        async: true,
        data: {
          accion: 'actualizarProyectoStage',
          idProyecto: idProyecto,
          idPrioridad: idPrioridad
        },
        success: function(response) {
          if (response == 'success') {
            mostrarAlerta('success', 'Priority Changed!.');
            $('#prioridad').html("YES");
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
</script>