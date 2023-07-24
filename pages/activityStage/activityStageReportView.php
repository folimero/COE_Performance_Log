<?php
    $archivoController = 'activityStageController.php';
    require_once $archivoController;
    $controller = new ActivityStage();

    $idProyecto = $_POST['idProyecto'];
    $idEtapa = $_POST['idEtapa'];
    $activityStages = $controller->getActivityStages($idProyecto);
    $stage = $controller->getStageReport($idProyecto, $idEtapa);
    $activities = $stage->getActivities();
    $approvers = $controller->getApprovers($idProyecto, $idEtapa);
    $idUser = "";

      if ($controller->checkForStage($idProyecto, $idEtapa) == false) { ?>
          <!DOCTYPE html>
          <html lang="en">
              <div class="card shadow p-3 bg-body rounded">
                  <div class="row">
                      <h3 class="text-center ">Report not Available</h3>
                  </div>
              </div>
          </html>
<?php     exit;
      }
      // USER ID SECTION
      if (!session_id()) {
          session_start();
      }
      if (isset($_SESSION['idUsuario'])) {
          $idUser = $_SESSION['idUsuario'];
      } else {
          echo "NOT FOUND USER ID";
          exit;
      }
?>

<!DOCTYPE html>
<html lang="en">
<div class="card shadow p-3 bg-body rounded">
  <!-- <h1 class="col-12 text-center danger">TESTING BY DEVELOPER, PLEASE DONT MOVE THIS SECTION!!!</h1> -->
    <div class="row">

      <div class="col-12 p-4">
          <div class="card shadow p-3 bg-body rounded">
              <div class="card-body">
                  <!-- General Info -->
                  <div class="row mb-3">
                    <div class="col-6">
                        <h3 class="text-start "><?php echo $stage->getName() . " - " . $stage->getDescription(); ?></h3>
                        <div id='etapaStatus' class="col-8 ">
                            <?php if ($approvers[0]->getProjectStatus() == 1): ?>
                                      <h2>STATUS: <small class="text- rounded-3 success">Approved</small></h2>
                            <?php elseif ($approvers[0]->getProjectStatus() == 0): ?>
                                      <h2>STATUS: <small class="text- rounded-3 warning">Pending</small></h2>
                            <?php else: ?>
                                      <h2>STATUS: <small class="text- rounded-3 danger">Rejected</small></h2>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-6">
                      <div class="text-end">
                          <h4>Created Date: <?php echo $approvers[0]->getCreatedDate(); ?></h4>
                          <?php if (!is_null($approvers[0]->getApprovedDate())): ?>
                                    <h4>Approved Date: <?php echo $approvers[0]->getApprovedDate(); ?></h4>
                          <?php endif; ?>
                      </div>
                    </div>
                  </div>

                  <h4>Project Title: <?php echo $activityStages->getName(); ?></h4>

                      <?php foreach ($approvers as $approver): ?>
                        <?php if ($approver->getIdRol() == 1): ?>
                                  <?php if ($approver->getApprovedStatus() == 1): ?>
                                    <h4>Project Leader: <?php echo $approver->getUserName(); ?> <i class="bi bi-check rounded-3 success"></i></h4>
                                  <?php elseif($approver->getApprovedStatus() == 0): ?>
                                            <h4 class="inline-container">Project Leader: <?php echo $approver->getUserName(); ?>
                                                <div id='approver<?php echo $approver->getIdApprover();?>'>
                                                    <a class="text-dark" style="text-decoration: none;" href="#" onclick="checkCanOpenModal(<?php echo $approver->getIdApprover(); ?>,<?php echo $approver->getIdUser(); ?>,'<?php echo $approver->getReason(); ?>')">
                                                        <i class="bi bi-check rounded-3 warning"></i>
                                                    </a>
                                                </div>
                                            </h4>
                                  <?php else: ?>
                                            <h4 class="inline-container">Project Leader: <?php echo $approver->getUserName(); ?>
                                                <div id='approver<?php echo $approver->getIdApprover();?>'>
                                                    <a class="text-dark" style="text-decoration: none;" href="#" onclick="checkUser(<?php echo $approver->getIdApprover(); ?>,<?php echo $approver->getIdUser(); ?>,'<?php echo $approver->getReason(); ?>')">
                                                        <i class="bi bi-check rounded-3 danger"></i>
                                                    </a>
                                                </div>
                                            </h4>
                                  <?php endif; ?>

                        <?php     break;
                              endif; ?>
                    <?php endforeach; ?>


                  <hr class="100 mt-4 mb-4">

                  <!-- Approvers -->
                  <div class="row">
                      <div class="col-6">
                          <div class="row">
                              <div class="col-6">
                                  <h4>Role</h4>
                              </div>
                              <div class="col-6">
                                  <h4>Name</h4>
                              </div>
                          </div>

                          <?php foreach ($approvers as $approver): ?>
                              <?php if ($approver->getIdRol() != 1): ?>
                                        <div class="row">
                                            <div class="col-6">
                                                <h5><?php echo $approver->getRolName(); ?>:</h5>
                                            </div>
                                            <div class="col-6">
                                                <?php if ($approver->getApprovedStatus() == 1): ?>
                                                  <h5 class="inline-container"><?php echo $approver->getUserName(); ?>  <i class="bi bi-check rounded-3 success"></i></h5>
                                                <?php elseif($approver->getApprovedStatus() == 0): ?>
                                                  <h5 class="inline-container"><?php echo $approver->getUserName(); ?>
                                                      <div id='approver<?php echo $approver->getIdApprover();?>'>
                                                          <a class="text-dark" style="text-decoration: none;" href="#" onclick="checkCanOpenModal(<?php echo $approver->getIdApprover(); ?>,<?php echo $approver->getIdUser(); ?>,'<?php echo $approver->getReason(); ?>')">
                                                              <i class="bi bi-check rounded-3 warning"></i>
                                                          </a>
                                                      </div>
                                                  </h5>
                                                <?php else: ?>
                                                  <h5 class="inline-container"><?php echo $approver->getUserName(); ?>
                                                      <div id='approver<?php echo $approver->getIdApprover();?>'>
                                                          <a class="text-dark" style="text-decoration: none;" href="#" onclick="checkUser(<?php echo $approver->getIdApprover(); ?>,<?php echo $approver->getIdUser(); ?>,'<?php echo $approver->getReason(); ?>')">
                                                              <i class="bi bi-check rounded-3 danger"></i>
                                                          </a>
                                                      </div>
                                                  </h5>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                              <?php endif ?>
                          <?php endforeach; ?>

                      </div>
                      <div id="reasonArea"class="col-6">

                      </div>
                  </div>

                  <hr class="100 mt-4 mb-4">

                  <div class="row mt-4">
                      <h4 class="text-start ">Gate Review Checklist</h4>
                      <table id="tablaActividades" class="table">
                          <thead>
                              <tr class="table-info">
                                  <th>Activity ID</th>
                                  <th>Type</th>
                                  <th>Name</th>
                                  <th>Owner</th>
                                  <th>Completed</th>
                                  <th>Comments</th>
                              </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?php echo $activity->getId(); ?></td>
                                    <td><?php echo $activity->getType(); ?></td>
                                    <td><?php echo $activity->getName(); ?></td>
                                    <td>Felix Norzagaray</td>
                                    <td>
                                      <?php
                                          // TIPOS DE VALIDACION
                                          // -1     N/A
                                          // 0      Sin Status
                                          // 1      Completado
                                          // 2      Aprobado
                                          // 3      Rechazado
                                          switch ($activity->getCompletado()) {
                                              case '0':
                                                  echo "Incompleted";
                                                  break;
                                              case '1':
                                                  echo "Y";
                                                  break;
                                              case '-1':
                                                  echo "N/A";
                                                  break;
                                              case '2':
                                                  echo "Y";
                                                  break;
                                              case '3':
                                                  echo "Rejected";
                                                  break;
                                              default:
                                                  echo "Undefined";
                                                break;
                                          }
                                      ?>
                                    </td>
                                    <td>None</td>
                                </tr>
                            <?php endforeach; ?>
                          </tbody>
                      </table>
                  </div>

                  <hr class="100 mt-4">
                  <div class="row">
                      <h4>Note: The list of deliverables can variate according to the requirements  and type of Market of project</h4>
                  </div>

              </div>
          </div>
      </div>
    </div>
</div>

</html>

<script type="text/javascript">
    function checkUser(idProyectoAprobador, idUser, reason) {
        event.preventDefault();
        var userId = <?php echo $idUser; ?>;
        if (idUser == userId) {
            if (confirm("Change Project Stage to Approve?")){
                approveStatusStage(idProyectoAprobador, idUser, reason);
                completeStage(idProyectoAprobador);
            }else {
                return;
            }
        } else {
            showReason(reason);
        }
    }

    function checkCanOpenModal(idProyectoAprobador, idUser, reason) {
        event.preventDefault();
        var userId = <?php echo $idUser; ?>;
        if (idUser == userId) {
            openStatusModal(idProyectoAprobador, idUser, reason);
        }
    }

    function showReason(msg) {
        event.preventDefault();
        var reason = "<h4 class='text-danger'>" + msg + "</h4>";
        $("#reasonArea").html(reason);
    }

    function openStatusModal(idProyectoAprobador, idUser, reason) {
        event.preventDefault();
        $('.contenido-modal').css('height','460px');
        // $('.contenido-modal').css('width','480px');
        $('.contenido-modal').html("<div class='flex-container' style='margin-top: 60px;'>" +
                                        "<!-- Titulo -->" +
                                        "<h1 id='tittle'>Notes</h1>" +
                                        "<a class='btn-cerrar' onclick='cerrarModal2()'>" +
                                        "<div class='icon-container'>" +
                                            "<div class='cross-icon'></div>" +
                                        "</div>" +
                                        "</a>" +
                                        "<!-- Formulario -->" +
                                        "<form id='form_empleados' action='' onsubmit=\"return approveStatusStage("+idProyectoAprobador+","+idUser+",'"+reason+"')\">" +
                                            "<!-- ID -->" +
                                            "<input type='hidden' name='idProyectoAprobador' id='idProyectoAprobador' value=''>" +
                                            "<input type='hidden' name='idUsuario' id='idUsuario' value=''>" +
                                            "<!-- Campo Nota -->" +
                                            "<div class='input-field'>" +
                                            "<label for='status'>Action</label>" +
                                            "<select class='form-select' name='status' id='status' aria-label='Default select example' onchange='pedirMotivo()'>" +
                                                "<option selected disabled>-- Select and Option --</option>" +
                                                "<option value='1'>Approve</option>" +
                                                "<option value='-1'>Reject</option>" +
                                            "</select>" +
                                            "<div class='input-field' id='notesSection'>" +

                                            "</div>" +
                                            "</div>" +
                                            "<!-- Button Submit -->" +
                                            "<input type='submit' id='btnApprove' value='Send'>" +
                                        "</form>" +
                                    "</div>");
        $('#tittle').html('Approve Status Stage');

        $('#idProyectoAprobador').val(idProyectoAprobador);
        $('#idUsuario').val( <?php echo $_SESSION['idUsuario']; ?> );
        abrirModal();
    }

    function pedirMotivo() {
        event.preventDefault();
        var selected = $('#status').val();
        // console.log(selected);
        switch (selected) {
            case '1':
                $('#notesSection').html("");
                break;
            case '-1':
                $('#notesSection').html("<label for='motivo'>Reason</label>" +
                "<textarea style='width: 100%;' rows='4' cols='50' id='motivo' name='motivo' required></textarea>");
                break;
            default:
                break;
        }
    }

    function approveStatusStage(idProyectoAprobador, idUser, reason) {
        event.preventDefault();

        if ($('#motivo').length) {
            var status = -1;
            var motivo = $('#motivo').val();
        } else {
            var status = 1;
            var motivo = "";
        }

        $.ajax({
        url: 'js/ajax.php',
        type: 'POST',
        async: true,
        data: {
            accion: 'aprobarEtapaStatus',
            idProyectoAprobador: idProyectoAprobador,
            status: status,
            motivo: motivo
        },
        success: function(response) {
            // console.log(response);
            if (!response != "error") {
                if (response != "failed") {
                    if (response == 1) {
                        $('#approver' + idProyectoAprobador).html('<i class="bi bi-check rounded-3 success"></i>');
                        completeStage(idProyectoAprobador);
                    } else {
                        var dangerButton = "<a class='text-dark' style='text-decoration: none;' href='#' onclick=\"checkUser(" + idProyectoAprobador + "," + idUser + ",'" + motivo + "')\">"+
                                              "<i class='bi bi-check rounded-3 danger'></i>"+
                                           "</a>";
                        $('#approver' + idProyectoAprobador).html(dangerButton);
                        $('#etapaStatus').html('<h2>STATUS: <small class="text- rounded-3 danger">Rejected</small></h2>')

                    }
                    cerrarModal2();
                    mostrarAlerta("success","Record Updated.")
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

    function completeStage(idProyectoAprobador) {

        $.ajax({
        url: 'js/ajax.php',
        type: 'POST',
        async: true,
        data: {
            accion: 'completarEtapaStatus',
            idProyectoAprobador: idProyectoAprobador
        },
        success: function(response) {
            // console.log(response);
            if (!response != "error") {
                if (response == "completed") {
                    $('#etapaStatus').html('<h2>STATUS: <small class="text- rounded-3 success">Approved</small></h2>')
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
</script>
