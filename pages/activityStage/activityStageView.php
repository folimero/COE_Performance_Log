<?php
    $stageModel = $this->d['activityStages'];
    $idProyecto = $stageModel->getId();
?>

<!DOCTYPE html>
<html lang="es">
<!-- <h1 class="col-12 text-center danger">TESTING BY DEVELOPER, PLEASE DONT MOVE THIS SECTION!!!</h1> -->

  <div class="row">

    <div class="col-12 p-4">
        <div class="card shadow p-3 bg-body rounded">
            <h2 class="card-header text-center ">Summary</h2>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                          <?php foreach ($stageModel->getStages() as $stage) { ?>
                            <div class="col col-lg-3 col-md-3 col-sm-12 p-2">
                                <div class="card shadow p-3 mb-5 bg-body rounded h-100">
                                    <h3 class="text-info text-center border-bottom pl-4 pb-3"><?php echo $stage->getName(); ?></h3>
                                    <h5 class="text-dark text-center"><?php echo $stage->getDescription(); ?></h5>

                                    <div class="row mt-auto v-100">
                                        <div class="col-12">
                                            <h5 class="text-primary text-center">Activities</h5>
                                        </div>
                                        <div class="col-6">
                                            <h5 class="text-dark text-center">Total</h5>
                                            <h4 class="text-primary text-center"><?php echo $stage->getTotal(); ?></h4>
                                        </div>
                                        <div class="col-6 border-start pl-4">
                                            <h5 class="text-dark text-center">Remaining</h5>
                                            <?php if ($stage->getTotal() - $stage->getCompleted() == 0): ?>
                                                      <h4 class="text-success text-center">0</h4>
                                            <?php else: ?>
                                                      <h4 class="text-danger text-center"><?php echo $stage->getTotal() - $stage->getCompleted(); ?></h4>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="row p-4">
                                        <button type="button" class="btn btn-primary" onclick="existStageReport(<?php echo $stage->getId(); ?>)">Detail</button>
                                    </div>
                                </div>
                            </div>
                          <?php } ?>
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
