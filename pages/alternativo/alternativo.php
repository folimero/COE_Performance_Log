<?php session_start();
  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(39, $_SESSION["permisos"]) && !in_array(40, $_SESSION["permisos"])) {
          $message = "Unauthorized User.";
          echo "<script>
                    alert('$message');
                    window.location.href='/index.php';
                </script>";
          die();
      } else {
          require "../../inc/conexion.php";
          require "../../inc/headerBoostrap.php";
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
  // if (isset($_GET['idRecurso'])) {
  //     $id = cleanInput($_GET['id']);
  //     $idRecurso = cleanInput($_GET['idRecurso']);
  //     require "../../inc/conexion.php";
  //
  //     $stmt = $dbh-> prepare("DELETE FROM recursos_asignados
  //                             WHERE idRecurso = $idRecurso");
  //     // Ejecutar la consulta preparada
  //     $stmt->execute();
  // }

  // Campos obtenidos en GET
      $stmt2 = $dbh->prepare("SELECT ");
      $stmt2->execute();
?>
<!DOCTYPE html>
<div class="card mb-3 mt-3">
    <h2 class="card-header text-center bg-success text-white">Alternatives</h2>

    <div class="card-body">
      <div id="open" class="container text-center">
          <h1 class="text-center mb-3">Alternative Log</h1>
          <a href='#' onclick='altaAlternativo()'>
              <div class='icon-container mb-3'>
                  <div class='plus-icon'></div>
              </div>
          </a>
          <div class="col-12">
            <table id="historyAlternatives" class="table w-100">
                <thead>
                    <!-- Encabezados de tabla -->
                    <tr>
                        <th>ID</th>
                        <th>NAI/QP Part Number</th>
                        <th>NAI/QP MFG PN</th>
                        <th>TYPE</th>
                        <th>Description</th>
                        <th>Alternative</th>
                        <th>Alternative MFG PN</th>
                        <th>Requested By</th>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>NAI/QP Part Number</th>
                        <th>NAI/QP MFG PN</th>
                        <th>TYPE</th>
                        <th>Description</th>
                        <th>Alternative</th>
                        <th>Alternative MFG PN</th>
                        <th>Requested By</th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>NAI/QP Part Number</th>
                        <th>NAI/QP MFG PN</th>
                        <th>TYPE</th>
                        <th>Description</th>
                        <th>Alternative</th>
                        <th>Alternative MFG PN</th>
                        <th>Requested By</th>
                    </tr>
                </tfoot>

                <tbody>
                    <?php
                    $stmt = $dbh->prepare("SELECT
                                              alternativo.idAlternativo,
                                              alternativo.NumParteNAIQP,
                                              alternativo.numParteManufacturador,
                                              alternativo_tipo.nombre AS aNombre,
                                              alternativo.descripcion,
                                              alternativo.alternativoNumParteManufacturador,
                                              manufacturador.nombre AS mNombre,
                                              empleado.nombre AS eNombre
                                          FROM
                                              alternativo
                                              INNER JOIN alternativo_tipo ON alternativo.idAlternativoTipo = alternativo_tipo.idAlternativoTipo
                                              INNER JOIN manufacturador ON alternativo.idAlternativoManufacturador = manufacturador.idManufacturador
                                              INNER JOIN usuario ON alternativo.idNegocioVieneDeUsuario = usuario.idUsuario
                                              INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado");
                    $stmt->execute();
                    while ($resultado = $stmt->fetch()) {
                        echo "<tr>";
                            echo "<td>". $resultado->idAlternativo . "</td>";
                            echo "<td>". $resultado->NumParteNAIQP . "</td>";
                            echo "<td>". $resultado->numParteManufacturador . "</td>";
                            echo "<td>". $resultado->aNombre . "</td>";
                            echo "<td>". $resultado->descripcion . "</td>";
                            echo "<td>". $resultado->alternativoNumParteManufacturador . "</td>";
                            echo "<td>". $resultado->mNombre . "</td>";
                            echo "<td>". $resultado->eNombre . "</td>";
                    }
                    ?>
                </tbody>
            </table>
          </div>
      </div>
    </div>
</div>

<!-- VENTANAS MODALES -->
    <div class="back-modal">
        <div class="contenido-modal">
            <div class="flex-container" style="margin-top: 60px;">
                <!-- Titulo -->
                <h1 id="tittle">Alternative Request</h1>
                <a class="btn-cerrar" onclick="cerrarModal()">
                    <div class='icon-container'>
                        <div class='cross-icon'></div>
                    </div>
                </a>
                <!-- Formulario -->
                <form id="form_alternativo" onSubmit="return createAlternative()" method="post">
                    <!-- Selector departamento -->
                    <div class="input-field">
                        <label for="idAlternativeReason">Motive</label>
                        <select id="idAlternativeReason" name="idAlternativeReason" required>
                            <option disabled selected value> -- Select -- </option>
                            <?php
                            $stmt = $dbh->prepare("SELECT idAlternativoMotivo, nombre FROM alternativo_motivo WHERE idAlternativoMotivo <> 6;");
                            $stmt->execute();
                            while ($resultado = $stmt->fetch()) {
                                ?>
                                <option value="<?php echo $resultado->idAlternativoMotivo; ?>">
                                    <?php
                                    echo $resultado->nombre; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class="input-field">
                      <label for="idAlternativeType">Alternative Type</label>
                      <select id="idAlternativeType" name="idAlternativeType" required>
                        <option disabled selected value> -- Select -- </option>
                        <?php
                        $stmt = $dbh->prepare("SELECT idAlternativoTipo, nombre FROM alternativo_tipo;");
                        $stmt->execute();
                        while ($resultado = $stmt->fetch()) {
                          ?>
                          <option value="<?php echo $resultado->idAlternativoTipo; ?>">
                            <?php
                            echo $resultado->nombre; ?>
                          </option>
                          <?php
                        }
                        ?>
                      </select>
                    </div>
                    <div class="input-field">
                        <label for="naiPN">NAI / QP Part Number</label>
                        <input name="naiPN" type="text" id="naiPN" required>
                    </div>
                    <div class="input-field">
                        <label for="description">Description</label>
                        <input name="description" type="text" id="description" required>
                    </div>
                    <div class="input-field">
                        <label for="spec">Spec</label>
                        <input name="spec" type="text" id="spec">
                    </div>

                    <!-- Button Submit -->
                    <input type="submit" id='btnAlternativo' value="Request">
                </form>
            </div>
        </div>
    </div>

<span class="alerta ocultar">
  <span class="msg">This is a warning</span>
  <span class='icon-container'>
    <div id="cerrar_alerta" class='cross-icon'></div>
  </span>
</span>

<script type="text/javascript">
    function altaAlternativo() {
          // $('#tittle').html('New Ticket');
          abrirModal();
    }

    function createAlternative() {
        event.preventDefault();

        alert("TODO -> CREATE ALTERNATIVE REQUEST.");
        // var idTicketType = $('#idTicketType').val();
        // var title = $('#title').val();
        // var issue = $('#issue').val();
        //
        // $.ajax({
        //     url: '../../js/ajax.php',
        //     type: 'POST',
        //     async: true,
        //     data: {
        //         accion: 'crearTicket',
        //         idTicketType: idTicketType,
        //         title: title,
        //         issue: issue
        //     },
        //     success: function(response) {
        //         // console.log(response);
        //         if (!response != "error") {
        //             var info = JSON.parse(response);
        //             // console.log(info.result);
        //             mostrarAlerta('success', 'Resource Added!.');
        //             cerrarModal();
        //
        //             let content = "";
        //             content += '<div class="col-4 p-2">' +
        //                 '<div class="card rounded-5 shadow p-4">' +
        //                     '<h5 class="text-end text-danger mb-3">Ticket: ' + info.result.idTicket + '</h5>';
        //             switch (info.result.idTicketType) {
        //                 case "1":
        //                     content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-danger text-end">' + info.result.type + '</span></h5>';
        //                     break;
        //                 case "2":
        //                     content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-primary text-end">' + info.result.type + '</span></h5>';
        //                     break;
        //                 case "3":
        //                     content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-danger text-end"><' + info.result.type + '</span></h5>';
        //                     break;
        //                 case "4":
        //                     content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-info text-end text-black">' + info.result.type + '</span></h5>';
        //                     break;
        //                 case "5":
        //                     content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-success text-end">' + info.result.type + '</span></h5>';
        //                     break;
        //                 case "6":
        //                     content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-secondary text-end">' + info.result.type + '</span></h5>';
        //                     break;
        //                 case "7":
        //                     content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-warning text-end text-black">' + info.result.type + '</span></h5>';
        //                     break;
        //                 default:
        //                     content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-secondary text-end">' + info.result.type + '</span></h5>';
        //                     break;
        //             }
        //
        //             switch (info.result.idTicketStatus) {
        //                 case "1":
        //                     content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-secondary text-end">' + info.result.status + '</span></h5>';
        //                     break;
        //                 case "2":
        //                     content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-success text-end">' + info.result.status + '</span></h5>';
        //                     break;
        //                 case "3":
        //                     content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-warning text-end">' + info.result.status + '</span></h5>';
        //                     break;
        //                 case "4":
        //                     content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-primary text-end">' + info.result.status + '</span></h5>';
        //                     break;
        //                 case "5":
        //                     content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-info text-end">' + info.result.status + '</span></h5>';
        //                     break;
        //                 default:
        //                     content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-secondary text-end">' + info.result.status + '</span></h5>';
        //                     break;
        //             }
        //             content += '<h4 class="text-primary text-start mt-3">' + info.result.title + '</h4>' +
        //                                       '<p class="text-start">' + info.result.issue + '</p>' +
        //                                       '<div class="row justify-content-between">' +
        //                                           '<div class="col">' +
        //                                           '<h5 class="text-start mt-4">Hours: <span class="badge rounded-pill bg-danger text-start">TBD</span></h5>' +
        //                                           '<p class="text-start">Due: </p>' +
        //                                           '</div>' +
        //                                           '<div class="col">' +
        //                                               '<h5 class="text-end mt-4">by: ' + info.result.createdBy + '</h5>' +
        //                                               '<p class="text-end">' + info.result.areatedAt + '</p>' +
        //                                           '</div>' +
        //                                       '</div>' +
        //                                       '<hr>' +
        //                                       '<h4 class="text-success text-start mt-2">Response:</h4>' +
        //                                       '<p class="text-start">PENDING</p>' +
        //                                   '</div>' +
        //                               '</div>';
        //             $("#ticketsArea").append(content);
        //         }
        //     },
        //     error: function(error) {
        //         mostrarAlerta('danger', 'Error on creating ticket, please notify to System Administrator.');
        //         console.log(error);
        //     }
        // });
    }
</script>

<script type="text/javascript">
  $(document).ready(function() {
    $('#cerrar_alerta').click(function() {
      $('.alerta').removeClass('mostrar');
      $('.alerta').addClass('ocultar');
    });

    <?php
    if (isset($_GET['back'])) {
      ?>
      $('#backBtn').attr('href', '/<?php echo $_GET['back'] ?>.php');
        <?php
    } ?>

    // DataTable
    var table = $("#historyAlternatives").DataTable({
        responsive: true,
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 20,
        // scrollX: true,
        dom: "Bfrtip",
        buttons: [
            // 'copyHtml5',
            "excelHtml5",
        ],
    });
    // Setup - add a text input to each footer cell
    $("#historyAlternatives thead tr:eq(1) th").each(function () {
        var title = $(this).text();
        $(this).html(
            '<input type="text" placeholder="Search ' +
                title +
                '" class="column_search" />'
        );
    });

    // Apply the search
    $("#historyAlternatives thead").on("keyup", ".column_search", function () {
        var customIndex = table.column($(this).parent().index() + ":visible");
        // console.log(customIndex);
        table.column(customIndex).search(this.value).draw();
    });

  });
</script>
<script src="../../js/funciones.js"></script>

<?php include "../../inc/footer.html"; ?>
