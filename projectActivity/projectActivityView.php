<?php
    $activityInfo = $this->d['activityInfo'];
    // $idProyecto = $stageModel->getId();
    include "inc/headerBoostrap.php";
?>

<!DOCTYPE html>
<html lang="es">
<h1 class="col-12 text-center danger">TESTING BY DEVELOPER, PLEASE DONT MOVE THIS SECTION!!!</h1>

  <div class="row">

    <div class="col-12 p-4">
        <div class="card shadow p-3 bg-body rounded">
            <h2 class="card-header text-center ">Activity Detail</h2>
            <div class="card-body">
                <div class="row">
                  <div class="col-12 mb-3">
                      <div class="row" style="align-items: center;">
                        <div class="col-6 text-end title-label">
                            <h6>Name:</h6>
                        </div>
                        <div class="col-6">
                            <h6 id="nameEditNombre" style="text-align: left;"><?php echo $activityInfo->getProyectoNombre();?><h6>
                            <input type="text" class="form-control-sm" id="nameInputNombre" style="display:none;">
                        </div>
                      </div>
                      <div class="row" style="align-items: center;">
                        <div class="col-6 text-end title-label">
                            <h6>Description:</h6>
                        </div>
                        <div class="col-6">
                            <h6 class="align-self-center" id="nameEditDesc" style="text-align: left;"><?php echo $activityInfo->getActividadNombre();?><h6>
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
        </div>
    </div>

    <div id="reportArea" class="col-12 p-4">

    </div>

  </div>

  <!-- <span class="alerta ocultar">
    <span class="msg">This is a warning</span>
    <span class='icon-container'>
      <div id="cerrar_alerta" class='cross-icon'></div>
    </span>
  </span> -->

</html>

<script type="text/javascript">
    function showReason(msg) {
        event.preventDefault();
        var reason = "<h4 class='text-danger'>" + msg + "</h4>";
        $("#reasonArea").html(reason);
    }

    function displayReport(idEtapa) {
        event.preventDefault();

        var file = "activityStage/activityStageReportView.php";
        $("#reportArea").load(file, {idProyecto: <?php echo $idProyecto; ?>, idEtapa: idEtapa});


        // JQUERY LOAD HTML WITH CALLBACK EXAMPLE

        // $('#msgDiv').load('/jquery/getdata', // url
        //     { name: 'bill' },    // data
        //         function(data, status, jqXGR) {  // callback function
        //             alert('data loaded');
        //         });

    }

    function existStageReport(idEtapa) {
      event.preventDefault();
      var idProyecto = <?php echo $idProyecto; ?> ;

      $.ajax({
        type: 'POST',
        url: 'js/ajax.php',
        async: true,
        data: {
          accion: 'existStageReport',
          idProyecto: idProyecto,
          idEtapa: idEtapa
        },
        success: function(response) {
          if (response != "ERROR") {
              if (response == "yes") {
                  displayReport(idEtapa);
              } else if (response == "no") {
                  checkForReport(idEtapa);
              }
          } else {
               var msg = "Error. Please contact Developer.";
               mostrarAlerta('danger', msg);
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
    }

    function checkForReport(idEtapa) {
      event.preventDefault();
      var idProyecto = <?php echo $idProyecto; ?> ;

      $.ajax({
        type: 'POST',
        url: 'js/ajax.php',
        async: true,
        data: {
          accion: 'checkCompletionStageActivities',
          idProyecto: idProyecto,
          idEtapa: idEtapa
        },
        success: function(response) {
          if (response == "noStageActivities") {
              var msg = "Cannot create report. There are no activities assinged in this Stage.";
              mostrarAlerta('danger', msg);
          } else if (response == "pendingStageActivities") {
              var msg = "Cannot create report. There are pending activities to complete.";
              mostrarAlerta('warning', msg);
          } else if (response == "allStageActivitiesCompleted") {
              checkApprovers(idEtapa);
          } else {
              var msg = "Error. Please contact Developer.";
              mostrarAlerta('danger', msg);
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
    }

    function checkApprovers(idEtapa) {
      event.preventDefault();
      var idProyecto = <?php echo $idProyecto; ?> ;

      $.ajax({
        type: 'POST',
        url: 'js/ajax.php',
        async: true,
        data: {
          accion: 'checkStageApprovers',
          idProyecto: idProyecto
        },
        success: function(response) {
          if (response == "missingStageApprover") {
              var msg = "Cannot create report. There are missing stage approvers."; // ASK FOR REPORT CREATION
              mostrarAlerta('warning', msg);
          } else if (response == "allStageApproversFound") {
              if (confirm('Generate report for Stage ' + idEtapa + '?')) {
                  createReport(idEtapa);
              }
          } else {
              var msg = "Error. Please contact Developer.";
              mostrarAlerta('danger', msg);
          }
        },
        error: function(error) {
          console.log(error);
        }
      });
    }

    function createReport(idEtapa) {
        var idProyecto = <?php echo $idProyecto; ?> ;

        $.ajax({
          type: 'POST',
          url: 'js/ajax.php',
          async: true,
          data: {
            accion: 'createStageReport',
            idProyecto: idProyecto,
            idEtapa: idEtapa
          },
          success: function(response) {
            alert(response);
            if (response == 'reportCreated') {
              displayReport(idEtapa);
              mostrarAlerta('success', 'Stage Report Created.');
            } else {
              mostrarAlerta('danger', 'Cannot create report, contact developer!.');
            }
          },
          error: function(error) {
            console.log(error);
          }
        });
    }
</script>
