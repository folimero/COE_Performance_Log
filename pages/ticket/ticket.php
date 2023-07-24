<?php session_start();
  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(36, $_SESSION["permisos"]) && !in_array(37, $_SESSION["permisos"])) {
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
    <h2 class="card-header text-center bg-warning">Tickets</h2>
    <!-- Seccion de Tabs -->
    <div class="tab text-center">
        <button class="tablinks" onclick="openSubtab(event, 'open')" id="defaultOpen">Open Tickets</button>
        <button class="tablinks" onclick="openSubtab(event, 'closed')">Closed Tickets</button>
    </div>

    <div class="card-body">
        <div id="open" class="tabcontent container text-center">
            <h1 class="text-center mb-3">Open Tickets</h1>
            <a href='#' onclick='altaTicket()'>
                <div class='icon-container mb-3'>
                    <div class='plus-icon'></div>
                </div>
            </a>
            <div class="container">
                <div id="ticketsArea" class="row">
                    <?php
                    $stmt = $dbh->prepare("SELECT t.idTicket, t.title, t.issue, tt.type, ts.status, e.nombre AS createdBy,
                                                  DATE(t.createdAt) AS createdAt, t.response, DATE(t.assignedDate) AS assignedDate,
                                                  DATE(t.dueDate) AS dueDate, t.hrs, t.idTicketStatus, t.idTicketType
                                          FROM ticket AS t
                                          INNER JOIN ticket_type AS tt
                                          ON t.idTicketType = tt.idTicketType
                                          INNER JOIN ticket_status AS ts
                                          ON t.idTicketStatus = ts.idTicketStatus
                                          INNER JOIN usuario AS u
                                          ON t.idUser = u.idUsuario
                                          INNER JOIN empleado AS e
                                          ON u.idEmpleado = e.idEmpleado
                                          WHERE t.idTicketStatus <> 4 AND t.idTicketStatus <> 5");
                    $stmt->execute();
                    while ($resultado = $stmt->fetch()) { ?>
                      <div class="col-4 p-2">
                          <div class="card rounded-5 shadow p-4">
                              <h5 class="text-end text-danger mb-3">Ticket: <?php echo $resultado->idTicket ?></h5>

                          <!-- TICKET TYPE SECTION -->
                          <?php
                              switch ($resultado->idTicketType) {
                                  case 1: ?>
                                      <h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-danger text-end"><?php echo $resultado->type ?></span></h5>
                            <?php     break;
                                  case 2: ?>
                                      <h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-primary text-end"><?php echo $resultado->type ?></span></h5>
                            <?php     break;
                                  case 3: ?>
                                      <h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-danger text-end"><?php echo $resultado->type ?></span></h5>
                            <?php     break;
                                  case 4: ?>
                                      <h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-info text-end text-black"><?php echo $resultado->type ?></span></h5>
                            <?php     break;
                                  case 5: ?>
                                      <h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-success text-end"><?php echo $resultado->type ?></span></h5>
                            <?php     break;
                                  case 6: ?>
                                      <h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-secondary text-end"><?php echo $resultado->type ?></span></h5>
                            <?php     break;
                                  case 7: ?>
                                      <h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-warning text-end text-black"><?php echo $resultado->type ?></span></h5>
                            <?php     break;
                                  default:
                                      // code...
                                      break;
                              }
                          ?>
                          <!-- TICKET STATUS SECTION -->
                          <?php
                              switch ($resultado->idTicketStatus) {
                                  case 1: ?>
                                      <h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-secondary text-end"><?php echo $resultado->status ?></span></h5>
                            <?php     break;
                                  case 2: ?>
                                      <h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-success text-end"><?php echo $resultado->status ?></span></h5>
                            <?php     break;
                                  case 3: ?>
                                      <h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-warning text-end"><?php echo $resultado->status ?></span></h5>
                            <?php     break;
                                  case 4: ?>
                                      <h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-primary text-end"><?php echo $resultado->status ?></span></h5>
                            <?php     break;
                                  case 5: ?>
                                      <h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-info text-end"><?php echo $resultado->status ?></span></h5>
                            <?php     break;
                                  default:
                                      // code...
                                      break;
                              }
                          ?>
                              <h4 class="text-primary text-start mt-3"><?php echo $resultado->title ?></h4>
                              <p class="text-start"><?php echo $resultado->issue ?></p>
                              <div class="row justify-content-between">
                                  <div class="col">
                                  <?php if ($resultado->hrs) { ?>
                                            <h5 class="text-start mt-4">Hours: <span class="badge rounded-pill bg-danger text-start"><?php echo $resultado->hrs ?></span></h5>
                                  <?php } else { ?>
                                            <h5 class="text-start mt-4">Hours: <span class="badge rounded-pill bg-danger text-start">TBD</span></h5>
                                  <?php }

                                        if ($resultado->dueDate && strtotime($resultado->dueDate) <= strtotime('now')) { ?>
                                            <p class="text-start bg-danger text-white">Due: <?php echo $resultado->dueDate ?></p>
                                  <?php } else { ?>
                                            <p class="text-start">Due: <?php echo $resultado->dueDate ?></p>
                                  <?php } ?>
                                  </div>
                                  <div class="col">
                                      <h5 class="text-end mt-4">by: <?php echo $resultado->createdBy ?></h5>
                                      <p class="text-end"><?php echo $resultado->createdAt ?></p>
                                  </div>
                              </div>
                              <hr>
                              <h4 class="text-success text-start mt-2">Response:</h4>

                              <?php if ($resultado->assignedDate) { ?>
                                        <p class="text-start"><?php echo $resultado->response ?></p>
                                        <h5 class="text-end mt-4">Assigned to: Kain Abdala</h5>
                                        <p class="text-end"><?php echo $resultado->assignedDate ?></p>
                              <?php } else { ?>
                                        <p class="text-start">PENDING</p>
                              <?php } ?>
                          </div>
                      </div>
              <?php } ?>
                </div>
            </div>
        </div>

        <div id="closed" class="tabcontent container text-center">
            <h1 class="text-center mb-3">Closed Tickets</h1>
            <div class="col-12">
              <table id="historyTickets" class="table w-100">
                  <thead>
                      <!-- Encabezados de tabla -->
                      <tr>
                          <!-- <th>Detail</th> -->
                          <th>ID</th>
                          <th>Type</th>
                          <th>Status</th>
                          <th>Title</th>
                          <th>Description</th>
                          <th>Assigned in</th>
                          <th>Completed in</th>
                          <th>Hrs</th>
                          <th>Requested By</th>
                          <th>Completed By</th>
                      </tr>
                      <tr>
                          <!-- <th>Detail</th> -->
                          <th>ID</th>
                          <th>Type</th>
                          <th>Status</th>
                          <th>Title</th>
                          <th>Description</th>
                          <th>Assigned in</th>
                          <th>Completed in</th>
                          <th>Hrs</th>
                          <th>Requested By</th>
                          <th>Completed By</th>
                      </tr>
                  </thead>

                  <tfoot>
                      <tr>
                          <!-- <th>Detail</th> -->
                          <th>ID</th>
                          <th>Type</th>
                          <th>Status</th>
                          <th>Title</th>
                          <th>Description</th>
                          <th>Assigned in</th>
                          <th>Completed in</th>
                          <th>Hrs</th>
                          <th>Requested By</th>
                          <th>Completed By</th>
                      </tr>
                  </tfoot>

                  <tbody>
                      <?php
                      $stmt = $dbh->prepare("SELECT t.idTicket, t.title, t.issue, tt.type, ts.status, e.nombre AS createdBy,
                                                    DATE(t.createdAt) AS createdAt, t.response, DATE(t.assignedDate) AS assignedDate,
                                                    DATE(t.dueDate) AS dueDate, t.hrs, t.idTicketStatus, t.idTicketType,
                                                    DATE(t.completedDate) AS completedDate
                                            FROM ticket AS t
                                            INNER JOIN ticket_type AS tt
                                            ON t.idTicketType = tt.idTicketType
                                            INNER JOIN ticket_status AS ts
                                            ON t.idTicketStatus = ts.idTicketStatus
                                            INNER JOIN usuario AS u
                                            ON t.idUser = u.idUsuario
                                            INNER JOIN empleado AS e
                                            ON u.idEmpleado = e.idEmpleado
                                            WHERE t.idTicketStatus = 4 OR t.idTicketStatus = 5");
                      $stmt->execute();
                      while ($resultado = $stmt->fetch()) {
                          echo "<tr>";
                          // echo "<td>
                          //         <a href='#'>
                          //         <div class='icon-container'>
                          //             <div class='plus-icon'></div>
                          //         </div>
                          //         </a>
                          //       </td>";
                          echo "<td>". $resultado->idTicket . "</td>";
                              // TICKET TYPE SECTION
                              switch ($resultado->idTicketType) {
                                  case 1: ?>
                                      <td><span class="badge rounded-pill bg-danger text-end"><?php echo $resultado->type ?></span></td>
                            <?php     break;
                                  case 2: ?>
                                      <td><span class="badge rounded-pill bg-primary text-end"><?php echo $resultado->type ?></span></td>
                            <?php     break;
                                  case 3: ?>
                                      <td><span class="badge rounded-pill bg-info text-end text-black"><?php echo $resultado->type ?></span></td>
                            <?php     break;
                                  case 4: ?>
                                      <td><span class="badge rounded-pill bg-info text-end text-black"><?php echo $resultado->type ?></span></td>
                            <?php     break;
                                  case 5: ?>
                                      <td><span class="badge rounded-pill bg-success text-end"><?php echo $resultado->type ?></span></td>
                            <?php     break;
                                  case 6: ?>
                                      <td><span class="badge rounded-pill bg-secondary text-end"><?php echo $resultado->type ?></span></td>
                            <?php     break;
                                  case 7: ?>
                                      <td><span class="badge rounded-pill bg-warning text-end text-black"><?php echo $resultado->type ?></span></td>
                            <?php     break;
                                  default:
                                      // code...
                                      break;
                              }
                          ?>
                          <!-- TICKET STATUS SECTION -->
                          <?php
                              switch ($resultado->idTicketStatus) {
                                  case 1: ?>
                                      <td><span class="badge rounded-pill bg-secondary text-end"><?php echo $resultado->status ?></span></td>
                            <?php     break;
                                  case 2: ?>
                                      <td><span class="badge rounded-pill bg-primary text-end"><?php echo $resultado->status ?></span></td>
                            <?php     break;
                                  case 3: ?>
                                      <td><span class="badge rounded-pill bg-warning text-end"><?php echo $resultado->status ?></span></td>
                            <?php     break;
                                  case 4: ?>
                                      <td><span class="badge rounded-pill bg-success text-end"><?php echo $resultado->status ?></span></td>
                            <?php     break;
                                  case 5: ?>
                                      <td><span class="badge rounded-pill bg-info text-end"><?php echo $resultado->status ?></span></td>
                            <?php     break;
                                  default:
                                      // code...
                                      break;
                              }
                          echo "<td>". $resultado->title . "</td>";
                          echo "<td>". $resultado->issue . "</td>";
                          echo "<td>". $resultado->assignedDate . "</td>";
                          echo "<td>". $resultado->completedDate . "</td>";
                          echo "<td>". $resultado->hrs . "</td>";
                          echo "<td>". $resultado->createdBy . "</td>";
                          echo "<td>Kain Abdala</td>";
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
                <h1 id="tittle">Create Ticket</h1>
                <a class="btn-cerrar" onclick="cerrarModal()">
                    <div class='icon-container'>
                        <div class='cross-icon'></div>
                    </div>
                </a>
                <!-- Formulario -->
                <form id="form_ticket" onSubmit="return createTicket()" method="post">
                    <!-- Selector departamento -->
                    <div class="input-field">
                        <label for="idTicketType">Ticket Type</label>
                        <select id="idTicketType" name="idTicketType" required>
                            <option disabled selected value> -- Select -- </option>
                            <?php
                            $stmt = $dbh->prepare("SELECT idTicketType, type, description FROM ticket_type;");
                            $stmt->execute();
                            while ($resultado = $stmt->fetch()) {
                                ?>
                                <option value="<?php echo $resultado->idTicketType; ?>">
                                    <?php
                                    echo "[" . $resultado->type ."] - " . $resultado->description; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <!-- Campo Empleado -->
                    <div class="input-field">
                        <label for="title">Title</label>
                        <input name="title" type="text" id="title" required>
                    </div>
                    <!-- Campo Nombre -->
                    <div class="input-field">
                        <label for="issue">Description (As clear as posible)</label>
                        <textarea name="issue" id="issue" rows="8" cols="50" required>Affected Page:
                         </textarea>
                    </div>
                    <!-- Button Submit -->
                    <input type="submit" id='btnTicket' value="Create">
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
    function altaTicket() {
          $('#tittle').html('New Ticket');
          abrirModal();
    }

    function createTicket() {
        event.preventDefault();

        var idTicketType = $('#idTicketType').val();
        var title = $('#title').val();
        var issue = $('#issue').val();

        $.ajax({
            url: '../../js/ajax.php',
            type: 'POST',
            async: true,
            data: {
                accion: 'crearTicket',
                idTicketType: idTicketType,
                title: title,
                issue: issue
            },
            success: function(response) {
                // console.log(response);
                if (!response != "error") {
                    var info = JSON.parse(response);
                    // console.log(info.result);
                    mostrarAlerta('success', 'Resource Added!.');
                    cerrarModal();

                    let content = "";
                    content += '<div class="col-4 p-2">' +
                        '<div class="card rounded-5 shadow p-4">' +
                            '<h5 class="text-end text-danger mb-3">Ticket: ' + info.result.idTicket + '</h5>';
                    switch (info.result.idTicketType) {
                        case "1":
                            content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-danger text-end">' + info.result.type + '</span></h5>';
                            break;
                        case "2":
                            content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-primary text-end">' + info.result.type + '</span></h5>';
                            break;
                        case "3":
                            content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-danger text-end"><' + info.result.type + '</span></h5>';
                            break;
                        case "4":
                            content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-info text-end text-black">' + info.result.type + '</span></h5>';
                            break;
                        case "5":
                            content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-success text-end">' + info.result.type + '</span></h5>';
                            break;
                        case "6":
                            content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-secondary text-end">' + info.result.type + '</span></h5>';
                            break;
                        case "7":
                            content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-warning text-end text-black">' + info.result.type + '</span></h5>';
                            break;
                        default:
                            content += '<h5 class="d-flex justify-content-end">Type: <span class="badge rounded-pill bg-secondary text-end">' + info.result.type + '</span></h5>';
                            break;
                    }

                    switch (info.result.idTicketStatus) {
                        case "1":
                            content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-secondary text-end">' + info.result.status + '</span></h5>';
                            break;
                        case "2":
                            content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-success text-end">' + info.result.status + '</span></h5>';
                            break;
                        case "3":
                            content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-warning text-end">' + info.result.status + '</span></h5>';
                            break;
                        case "4":
                            content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-primary text-end">' + info.result.status + '</span></h5>';
                            break;
                        case "5":
                            content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-info text-end">' + info.result.status + '</span></h5>';
                            break;
                        default:
                            content += '<h5 class="d-flex justify-content-end">Status: <span class="badge rounded-pill bg-secondary text-end">' + info.result.status + '</span></h5>';
                            break;
                    }
                    content += '<h4 class="text-primary text-start mt-3">' + info.result.title + '</h4>' +
                                              '<p class="text-start">' + info.result.issue + '</p>' +
                                              '<div class="row justify-content-between">' +
                                                  '<div class="col">' +
                                                  '<h5 class="text-start mt-4">Hours: <span class="badge rounded-pill bg-danger text-start">TBD</span></h5>' +
                                                  '<p class="text-start">Due: </p>' +
                                                  '</div>' +
                                                  '<div class="col">' +
                                                      '<h5 class="text-end mt-4">by: ' + info.result.createdBy + '</h5>' +
                                                      '<p class="text-end">' + info.result.areatedAt + '</p>' +
                                                  '</div>' +
                                              '</div>' +
                                              '<hr>' +
                                              '<h4 class="text-success text-start mt-2">Response:</h4>' +
                                              '<p class="text-start">PENDING</p>' +
                                          '</div>' +
                                      '</div>';
                    $("#ticketsArea").append(content);
                }
            },
            error: function(error) {
                mostrarAlerta('danger', 'Error on creating ticket, please notify to System Administrator.');
                console.log(error);
            }
        });
    }
</script>

<script type="text/javascript">
  $(document).ready(function() {
    $('#cerrar_alerta').click(function() {
      $('.alerta').removeClass('mostrar');
      $('.alerta').addClass('ocultar');
    });

    if ($('#awarded').attr('awardedValue') == 1) {
      $('#awarded').css("background-color", "lightgreen");
    }

    <?php
    if (isset($_GET['back'])) {
      ?>
      $('#backBtn').attr('href', '/<?php echo $_GET['back'] ?>.php');
        <?php
    } ?>

    // DataTable
    var table = $('#historyTickets').DataTable({
      responsive: true,
      orderCellsTop: true,
      fixedHeader: true,
      pageLength: 20,
      // scrollX: true,
      dom: 'Bfrtip',
      buttons: [
          // 'copyHtml5',
          'excelHtml5',
      ]
      // columnDefs: [
      //     {
      //         target: 4,
      //         visible: false,
      //         // searchable: false,
      //     },
      //     {
      //         target: 8,
      //         visible: false,
      //     },
      //     {
      //         target: 10,
      //         visible: false,
      //     },
      // ],
      // drawCallback: () => $('#avg').val(updateAverage())
    });
    // Setup - add a text input to each footer cell
    $('#historyTickets thead tr:eq(1) th').each( function () {
        var title = $(this).text();
        $(this).html( '<input type="text" placeholder="Search '+title+'" class="column_search" />' );
    } );

    // Apply the search
    $( '#historyTickets thead'  ).on( 'keyup', ".column_search",function () {
        var customIndex = table.column($(this).parent().index() +':visible');
        // console.log(customIndex);
        table
            .column( customIndex )
            .search( this.value )
            .draw();
    } );

  });
</script>
<script src="../../js/funciones.js"></script>

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

<?php include "../../inc/footer.html"; ?>
