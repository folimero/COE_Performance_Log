<?php
  include "inc/conexion.php";
  include "inc/headerBoostrap.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(35, $_SESSION["permisos"])) {
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
  $filterYear = '';
  if (isset($_GET['year'])) {
      $filterYear = $_GET['year'];
  } elseif (isset($_GET['YEAR'])) {
      $filterYear = $_GET['YEAR'];
  } else {
    $filterYear = "2021";
  }

  ?>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<?php
  // DESIGN SECTION - ACTUAL
  $stmtActividades = $dbh->prepare("SELECT e.nombre,
    (SELECT count(ap.idActividad) FROM proyecto AS p
            inner JOIN actividades_proyecto AS ap
            ON p.idProyecto = ap.idProyecto
            INNER JOIN actividad AS a
            ON ap.idActividad = a.idActividad
            WHERE p.idRespDiseno = e.idEmpleado AND p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND
            ap.idActividad IN (17,20,23,29,30,35,42,46,48,49,52,57,69,80,81,110,112,114,115,116,117,118,121,122,123,125,126,134,136,139,140) AND
            a.obsoleta = 0) AS actividades,
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespDiseno = e.idEmpleado AND p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND
ap.idActividad IN (17,20,23,29,30,35,42,46,48,49,52,57,69,80,81,110,112,114,115,116,117,118,121,122,123,125,126,134,136,139,140) AND
a.obsoleta = 0 AND ap.entregadoPor IS NOT NULL) AS completadas,
((SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespDiseno = e.idEmpleado AND p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND ap.idActividad IN (17,20,23,29,30,35,42,46,48,49,52,57,69,80,81,110,112,114,115,116,117,118,121,122,123,125,126,134,136,139,140) AND
a.obsoleta = 0 AND ap.entregadoPor IS NOT NULL) /
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespDiseno = e.idEmpleado AND p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND ap.idActividad IN (17,20,23,29,30,35,42,46,48,49,52,57,69,80,81,110,112,114,115,116,117,118,121,122,123,125,126,134,136,139,140) AND
a.obsoleta = 0)) AS percent
FROM proyecto AS p
INNER JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad as a
ON ap.idActividad = a.idActividad
INNER JOIN empleado AS e
ON p.idRespDiseno = e.idEmpleado
WHERE p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND ap.idActividad IN (17,20,23,29,30,35,42,46,48,49,52,57,69,80,81,110,112,114,115,116,117,118,121,122,123,125,126,134,136,139,140) AND a.obsoleta = 0
GROUP BY nombre");

$stmtActividades->execute();

// COE Historic
$stmtActividadesHistoric = $dbh->prepare("SELECT e.nombre,
  (SELECT count(ap.idActividad) FROM proyecto AS p
          inner JOIN actividades_proyecto AS ap
          ON p.idProyecto = ap.idProyecto
          INNER JOIN actividad AS a
          ON ap.idActividad = a.idActividad
          WHERE p.idRespDiseno = e.idEmpleado AND p.idStatus <> 5 AND
          a.resp = 'DE') AS actividades,
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespDiseno = e.idEmpleado AND p.idStatus <> 5 AND
a.resp = 'DE' AND ap.entregadoPor IS NOT NULL)
AS completadas,
((SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespDiseno = e.idEmpleado AND p.idStatus <> 5 AND a.resp = 'DE'
AND ap.entregadoPor IS NOT NULL) /
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespDiseno = e.idEmpleado AND p.idStatus <> 5 AND a.resp = 'DE'))
AS percent
FROM proyecto AS p
INNER JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad as a
ON ap.idActividad = a.idActividad
INNER JOIN empleado AS e
ON p.idRespDiseno = e.idEmpleado
WHERE p.idStatus <> 5 AND a.resp = 'DE' AND p.isApplication <> 1
GROUP BY nombre");

  $stmtActividadesHistoric->execute();

  // Application Historic
  $stmtApplicationActividadesHistoric = $dbh->prepare("SELECT e.nombre,
  (SELECT count(ap.idActividad) FROM proyecto AS p
          inner JOIN actividades_proyecto AS ap
          ON p.idProyecto = ap.idProyecto
          INNER JOIN actividad AS a
          ON ap.idActividad = a.idActividad
          WHERE p.idRespDiseno = e.idEmpleado AND (p.idStatus <> 5 OR ap.entregadoPor IS NOT NULL) AND p.isApplication = 1) AS actividades,
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespDiseno = e.idEmpleado AND ap.entregadoPor IS NOT NULL AND p.isApplication = 1) AS completadas,
((SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespDiseno = e.idEmpleado AND
 ap.entregadoPor IS NOT NULL AND p.isApplication = 1) /
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespDiseno = e.idEmpleado AND p.isApplication = 1)) AS percent
FROM proyecto AS p
INNER JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad as a
ON ap.idActividad = a.idActividad
INNER JOIN empleado AS e
ON p.idRespDiseno = e.idEmpleado
WHERE p.isApplication = 1 AND e.activo = 1
GROUP BY nombre");

    $stmtApplicationActividadesHistoric->execute();

  // MANUFACTURING SECTION - Actual
  $stmtCompletados = $dbh->prepare("SELECT e.nombre,
    (SELECT count(ap.idActividad) FROM proyecto AS p
            inner JOIN actividades_proyecto AS ap
            ON p.idProyecto = ap.idProyecto
            INNER JOIN actividad AS a
            ON ap.idActividad = a.idActividad
            WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND
            ap.idActividad IN (39,68,70,71,72,73,74,75,76,77,78,82,83,103,105,106,119,124,129,132,137,141,142,143,144) AND
            a.obsoleta = 0) AS actividades,
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND
ap.idActividad IN (39,68,70,71,72,73,74,75,76,77,78,82,83,103,105,106,119,124,129,132,137,141,142,143,144) AND
a.obsoleta = 0 AND ap.entregadoPor IS NOT NULL) AS completadas,
((SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND ap.idActividad IN (39,68,70,71,72,73,74,75,76,77,78,82,83,103,105,106,119,124,129,132,137,141,142,143,144) AND
a.obsoleta = 0 AND ap.entregadoPor IS NOT NULL) /
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND ap.idActividad IN (39,68,70,71,72,73,74,75,76,77,78,82,83,103,105,106,119,124,129,132,137,141,142,143,144) AND
a.obsoleta = 0)) AS percent
FROM proyecto AS p
INNER JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad as a
ON ap.idActividad = a.idActividad
INNER JOIN empleado AS e
ON p.idRespManu = e.idEmpleado
WHERE p.idStatus <> 7 AND p.idStatus <> 5 AND p.idStatus <> 6 AND ap.idActividad IN (39,68,70,71,72,73,74,75,76,77,78,82,83,103,105,106,119,124,129,132,137,141,142,143,144) AND a.obsoleta = 0
GROUP BY nombre");

$stmtCompletadosHistoric = $dbh->prepare("SELECT e.nombre,
  (SELECT count(ap.idActividad) FROM proyecto AS p
          inner JOIN actividades_proyecto AS ap
          ON p.idProyecto = ap.idProyecto
          INNER JOIN actividad AS a
          ON ap.idActividad = a.idActividad
          WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 5 AND
          a.resp = 'ME') AS actividades,
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 5 AND
a.resp = 'ME' AND ap.entregadoPor IS NOT NULL) AS completadas,
((SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 5 AND a.resp = 'ME' AND
 ap.entregadoPor IS NOT NULL) /
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 5 AND a.resp = 'ME')) AS percent
FROM proyecto AS p
INNER JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad as a
ON ap.idActividad = a.idActividad
INNER JOIN empleado AS e
ON p.idRespManu = e.idEmpleado
WHERE p.idStatus <> 5 AND ap.idActividad AND a.resp = 'ME'
GROUP BY nombre");

$stmtOrverdue = $dbh->prepare("SELECT e.nombre,
  (SELECT count(ap.idActividad) FROM proyecto AS p
          inner JOIN actividades_proyecto AS ap
          ON p.idProyecto = ap.idProyecto
          INNER JOIN actividad AS a
          ON ap.idActividad = a.idActividad
          WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 5 AND p.idStatus <> 6 AND
          a.resp = 'ME') AS actividades,
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 5 AND p.idStatus <> 6 AND
a.resp = 'ME' AND ap.entregadoPor IS NOT NULL) AS completadas,
((SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 5 AND p.idStatus <> 6 AND a.resp = 'ME' AND
 ap.entregadoPor IS NOT NULL) /
(SELECT count(ap.idActividad)
from proyecto as p
inner JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad AS a
ON ap.idActividad = a.idActividad
WHERE p.idRespManu = e.idEmpleado AND p.idStatus <> 5 AND p.idStatus <> 6 AND a.resp = 'ME')) AS percent
FROM proyecto AS p
INNER JOIN actividades_proyecto AS ap
ON p.idProyecto = ap.idProyecto
INNER JOIN actividad as a
ON ap.idActividad = a.idActividad
INNER JOIN empleado AS e
ON p.idRespManu = e.idEmpleado
WHERE p.idStatus <> 5 AND p.idStatus <> 6 AND ap.idActividad AND a.resp = 'ME'
GROUP BY nombre");

  $stmtOverdueDisenoCOUNT2 = $dbh->prepare("SELECT e.nombre, COUNT(e.nombre) AS overdue
                                      FROM actividades_proyecto AS ap
                                      INNER JOIN proyecto AS p
                                      ON ap.idProyecto = p.idProyecto
                                      INNER JOIN actividad AS a
                                      ON ap.idActividad = a.idActividad
                                      INNER JOIN empleado AS e
                                      ON p.idRespDiseno = e.idEmpleado
                                      WHERE ap.entregadoPor IS NULL AND ap.fechaRequerida <= DATE(now()) AND p.idStatus <> 5 AND p.idStatus <> 6 AND a.obsoleta <> 1 AND a.idActividad IN(17,20,23,29,30,35,42,46,48,49,52,57,69,80,81,110,112,114,115,116,117,118,121,122,123,125,126,134,136,139,140)
                                      GROUP BY e.nombre");
  $stmtOverdueDisenoCOUNT2->execute();
  $dataOverdueDiseno = $stmtOverdueDisenoCOUNT2->fetchAll(PDO::FETCH_ASSOC);

  $stmtOverdueManuCOUNT2 = $dbh->prepare("SELECT e.nombre, COUNT(e.nombre) AS overdue
                                    FROM `actividades_proyecto` AS ap
                                    INNER JOIN proyecto AS p
                                    ON ap.idProyecto = p.idProyecto
                                    INNER JOIN actividad AS a
                                    ON ap.idActividad = a.idActividad
                                    INNER JOIN empleado AS e
                                    ON p.idRespManu = e.idEmpleado
                                    WHERE ap.entregadoPor IS NULL AND ap.fechaRequerida <= DATE(now()) AND p.idStatus <> 5 AND p.idStatus <> 6 AND a.obsoleta <> 1 AND a.idActividad IN(39,68,70,71,72,73,74,75,76,77,78,82,83,103,105,106,119,124,129,131,132,137,141,142,143,144)
                                    GROUP BY e.nombre");
  $stmtOverdueManuCOUNT2->execute();
  $dataOverdueManufactura = $stmtOverdueManuCOUNT2->fetchAll(PDO::FETCH_ASSOC);
  // var_dump($dataOverdueDiseno[0]['nombre']);

// Actual Activities
  $stmtCompletados->execute();
  $maxActivities = 0;
  $data = "";
  $data .= "['ENGINEER', 'ACTIVITIES', 'COMPLETED', 'OVERDUE', 'PERCENTAGE'],";

  // DESIGN
  // var_dump($dataOverdueDiseno);
  // exit;
  while ($resultado = $stmtActividades->fetch()) {
      if ($maxActivities < $resultado->actividades) {
          $maxActivities = $resultado->actividades;
      }
      if (isset($resultado->nombre, $dataOverdueDiseno[0]['nombre'])) {
          if (in_array($resultado->nombre, $dataOverdueDiseno[0])) {
              $data .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . $dataOverdueDiseno[0]['overdue'] . "," . $resultado->percent . "],";
          } else {
              $data .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . 0 . "," . $resultado->percent . "],";
          }
      } else {
          $data .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . 0 . "," . $resultado->percent . "],";
      }

  }

  // MANUFACTURING
  while ($resultado = $stmtCompletados->fetch()) {
      if ($maxActivities < $resultado->actividades) {
          $maxActivities = $resultado->actividades;
      }
      if (isset($dataOverdueManufactura[0]['nombre'])) {
          if (in_array($resultado->nombre, $dataOverdueManufactura[0])) {
              $data .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . $dataOverdueManufactura[0]['overdue'] . "," . $resultado->percent . "],";
          } else {
              $data .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . 0 . "," . $resultado->percent . "],";
          }
      } else {
          $data .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . 0 . "," . $resultado->percent . "],";
      }

  }

    // Overdue Activities ----------------------------------->
    // $stmtOrverdue->execute();
    // $maxActivities = 0;
    // $dataOverdue = "";
    // $dataOverdue .= "['ENGINEER', 'ACTIVITIES', 'COMPLETED', 'PERCENTAGE'],";
    //
    // while ($resultado = $stmtActividades->fetch()) {
    //     if ($maxActivities < $resultado->actividades) {
    //         $maxActivities = $resultado->actividades;
    //     }
    //     $dataOverdue .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . $resultado->percent . "],";
    // }
    //
    // while ($resultado = $stmtCompletados->fetch()) {
    //     if ($maxActivities < $resultado->actividades) {
    //         $maxActivities = $resultado->actividades;
    //     }
    //     $dataOverdue .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . $resultado->percent . "],";
    // }

  // COE Historic ACTIVITIES
  $stmtCompletadosHistoric->execute();
  $maxActivities2 = 0;
  $dataHistoric = "";
  $dataHistoric .= "['ENGINEER', 'ACTIVITIES', 'COMPLETED', 'PERCENTAGE'],";

  while ($resultado = $stmtActividadesHistoric->fetch()) {
      if ($maxActivities2 < $resultado->actividades) {
          $maxActivities2 = $resultado->actividades;
      }
      $dataHistoric .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . $resultado->percent . "],";
  }

  while ($resultado = $stmtCompletadosHistoric->fetch()) {
      if ($maxActivities2 < $resultado->actividades) {
          $maxActivities2 = $resultado->actividades;
      }
      $dataHistoric .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . $resultado->percent . "],";
  }
  // Application Historic ACTIVITIES
  // $stmtApplicationCompletadosHistoric->execute();
  $maxApplicationActivities = 0;
  $dataApplicationHistoric = "";
  $dataApplicationHistoric .= "['ENGINEER', 'ACTIVITIES', 'COMPLETED', 'PERCENTAGE'],";

  while ($resultado = $stmtApplicationActividadesHistoric->fetch()) {
      if ($maxApplicationActivities < $resultado->actividades) {
          $maxApplicationActivities = $resultado->actividades;
      }
      $dataApplicationHistoric .= "['" . $resultado->nombre . "'," . $resultado->actividades . "," . $resultado->completadas . "," . $resultado->percent . "],";
  }
?>

  <script type="text/javascript">
      // Load google charts
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);

      // Tabla Recursos Asignados / SEMANALAES
      function drawVisualization() {
          // Some raw data (not necessarily accurate)
          var data = google.visualization.arrayToDataTable([
            <?php echo $data; ?>
          ]);

          var tick = [];
          var records = <?php echo $maxActivities; ?>;

          for (var i = 0; i < records; i = i+10) {
              tick.push(i);
          }

          // Table 2
          var dataHistoric = google.visualization.arrayToDataTable([
            <?php echo $dataHistoric; ?>
          ]);

          var applicationDataHistoric = google.visualization.arrayToDataTable([
            <?php echo $dataApplicationHistoric; ?>
          ]);

          var tick2 = [];
          var records2 = <?php echo $maxActivities2; ?>;

          for (var i = 0; i < records2; i = i+10) {
              tick2.push(i);
          }

          var applicationTick = [];
          var applicationRecords = <?php echo $maxApplicationActivities; ?>;

          for (var i = 0; i < applicationRecords; i = i+10) {
              applicationTick.push(i);
          }

          var options = {
            title : 'Open Projects Activities',
            vAxes: {
              0: {
                title:'QTY',
                textStyle: {color: '#34495E'},
                ticks: tick
                // ticks: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
              },
              1: {
                title:'% Completion',
                textStyle: {color: '#34495E'},
                format: 'percent',
                viewWindow: {
                    min: 0,
                    max: 1
                },
                ticks: [0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1]
              }
            },
            hAxis: {title: 'ENGINEER'},
            seriesType: 'bars',
            isStacked: false,
            series: {
              0: {
                targetAxisIndex:0,
                color: '#3498DB'
                // visibleInLegend: false
              },
              1: {
                targetAxisIndex:0,
                color: '#52BE80'
                // visibleInLegend: false
              },
              2: {
                targetAxisIndex:0,
                color: '#CB4335'
              },
              3: {
                targetAxisIndex: 1,
                type: 'line',
                color: '#F1C40F',
              }
            }
          };

          var options2 = {
            title : 'Assigned Activities',
            vAxes: {
              0: {
                title:'QTY',
                textStyle: {color: '#34495E'},
                ticks: tick2
                // ticks: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
              },
              1: {
                title:'% Completion',
                textStyle: {color: '#34495E'},
                format: 'percent',
                viewWindow: {
                    min: 0,
                    max: 1
                },
                ticks: [0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1]
              }
            },
            hAxis: {title: 'ENGINEER'},
            seriesType: 'bars',
            isStacked: false,
            series: {
              0: {
                targetAxisIndex:0,
                color: '#3498DB'
                // visibleInLegend: false
              },
              1: {
                targetAxisIndex:0,
                color: '#52BE80'
                // visibleInLegend: false
              },
              2: {
                targetAxisIndex: 1,
                type: 'line',
                color: '#F1C40F'
              }
            }
          };

          var applicationOptions = {
            title : 'Assigned Activities',
            vAxes: {
              0: {
                title:'QTY',
                textStyle: {color: '#34495E'},
                ticks: applicationTick
                // ticks: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
              },
              1: {
                title:'% Completion',
                textStyle: {color: '#34495E'},
                format: 'percent',
                viewWindow: {
                    min: 0,
                    max: 1
                },
                ticks: [0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1]
              }
            },
            hAxis: {title: 'ENGINEER'},
            seriesType: 'bars',
            isStacked: false,
            series: {
              0: {
                targetAxisIndex:0,
                color: '#3498DB'
                // visibleInLegend: false
              },
              1: {
                targetAxisIndex:0,
                color: '#52BE80'
                // visibleInLegend: false
              },
              2: {
                targetAxisIndex: 1,
                type: 'line',
                color: '#F1C40F'
              }
            }
          };

          var formatter = new google.visualization.NumberFormat({pattern: '#%'});
          formatter.format(data, 4); // format column 1
          formatter.format(dataHistoric, 3); // format column 1
          formatter.format(applicationDataHistoric, 3); // format column 1

          var chart = new google.visualization.ComboChart(document.getElementById('activitiesPerEngineer'));
          chart.draw(data, options);

          var chart2 = new google.visualization.ComboChart(document.getElementById('historicActPerEng'));
          chart2.draw(dataHistoric, options2);

          var chart3 = new google.visualization.ComboChart(document.getElementById('applicationHistoricActPerEng'));
          chart3.draw(applicationDataHistoric, applicationOptions);

          google.visualization.events.addListener(chart, 'select', selectHandler);

          function selectHandler() {
              var selection = chart.getSelection();
              var message = '';
              for (var i = 0; i < selection.length; i++) {
                  var item = selection[i];
                  if (item.row != null && item.column != null) {
                      // var str = data.getFormattedValue(item.row, item.column);
                      var category = data.getValue(chart.getSelection()[0].row, 0)
                      // var type
                      // if (item.column == 1) {
                      //     type = "sale";
                      // } else if(item.column == 2){
                      //     type = "Expense";
                      // }else{
                      //     type = "Profit";
                      // }
                      // message += '{row:' + item.row + ',column:' + item.column
                      // + '} = ' + str + '  The Category is:' + category
                      // + ' it belongs to : ' + type + '\n';
                  }
                  // else if (item.row != null) {
                  //     var str = data.getFormattedValue(item.row, 0);
                  //     message += '{row:' + item.row
                  //     + ', column:none}; value (col 0) = ' + str
                  //     + '  The Category is:' + category + '\n';
                  // } else if (item.column != null) {
                  //     var str = data.getFormattedValue(0, item.column);
                  //     message += '{row:none, column:' + item.column
                  //     + '}; value (row 0) = ' + str
                  //     + '  The Category is:' + category + '\n';
                  // }
              }
              // if (message == '') {
              //     message = 'nothing';
              // }
              // alert('You selected ' + message);
              showEngineerDetail(category);
          }
      }

      function showEngineerDetail(engName) {
          $.ajax({
              type:"POST",
              url:"js/ajax.php",
              async: true,
              data: {
                  accion: 'mostrarDetalleAct',
                  engineer: engName
              },
              success: function(response) {
                  if (!response != "error") {
                      var info = JSON.parse(response);

                      $('#detail').html("");
                      var tempNombre = "";

                      for (var i = 0; i < info.result.length; i++){
                          var obj = info.result[i];

                          if (obj['pNombre'] != tempNombre) {
                              $('#detail').append("<br>");

                              var divCard = document.createElement("div");
                              divCard.classList.add("container");
                              var divCardHeader = document.createElement("div");
                              divCardHeader.classList.add("card-header");
                              divCard.appendChild(divCardHeader);
                              var ulListGRoup = document.createElement("ul");
                              ulListGRoup.classList.add("list-group");
                              ulListGRoup.classList.add("list-group-flush");
                              divCard.appendChild(ulListGRoup);

                              var text = document.createTextNode(obj['pNombre']);
                              divCardHeader.appendChild(text);
                              var liListGroupItem = document.createElement("li");
                              liListGroupItem.classList.add("list-group-item");
                              var text2 = document.createTextNode(obj['aNombre']);
                              liListGroupItem.appendChild(text2);
                              ulListGRoup.appendChild(liListGroupItem);

                              tempNombre = obj['pNombre'];
                          }else {
                              var liListGroupItem = document.createElement("li");
                              liListGroupItem.classList.add("list-group-item");
                              var text2 = document.createTextNode(obj['aNombre']);
                              liListGroupItem.appendChild(text2);
                              ulListGRoup.appendChild(liListGroupItem);
                          }
                          $('#detail').append(divCard);
                      }
                  }
              }
          });
      }
  </script>
<?php
$stmtOverdueDiseno = $dbh->prepare("SELECT
                                        e.nombre AS eNombre,
                                        ap.idActividades_proyecto,
                                        p.idProyecto,
                                        p.nombre AS pNombre,
                                        ap.idActividad,
                                        a.nombre AS aNombre,
                                        DATE(ap.fechaRequerida) AS fechaRequerida
                                    FROM
                                        actividades_proyecto AS ap
                                        INNER JOIN proyecto AS p ON ap.idProyecto = p.idProyecto
                                        INNER JOIN actividad AS a ON ap.idActividad = a.idActividad
                                        INNER JOIN empleado AS e ON p.idRespDiseno = e.idEmpleado
                                    WHERE
                                        ap.entregadoPor IS NULL
                                        AND ap.fechaRequerida <= DATE(now())
                                        AND p.idStatus <> 5
                                        AND p.idStatus <> 6
                                        AND a.obsoleta <> 1
                                        AND p.isApplication <> 1
                                        AND a.idActividad IN(
                                            SELECT
                                                actividad.idActividad
                                            FROM
                                                actividad
                                            WHERE
                                                actividad.resp = 'DE'
                                                AND actividad.obsoleta <> 1
                                        )
                                        AND e.activo <> 0
                                    ORDER BY
                                        idProyecto,
                                        fechaRequerida DESC");
  $stmtOverdueDiseno->execute();
  $stmtOverdueDisenoCOUNT = $dbh->prepare("SELECT e.nombre, COUNT(e.nombre) AS overdue
                                      FROM actividades_proyecto AS ap
                                      INNER JOIN proyecto AS p
                                      ON ap.idProyecto = p.idProyecto
                                      INNER JOIN actividad AS a
                                      ON ap.idActividad = a.idActividad
                                      INNER JOIN empleado AS e
                                      ON p.idRespDiseno = e.idEmpleado
                                      WHERE ap.entregadoPor IS NULL AND ap.fechaRequerida <= DATE(now()) AND p.idStatus <> 5 AND p.idStatus <> 6 AND a.obsoleta <> 1 AND a.idActividad IN(17,20,23,29,30,35,42,46,48,49,52,57,69,80,81,110,112,114,115,116,117,118,121,122,123,125,126,134,136,139,140)
                                      GROUP BY e.nombre");
    $stmtOverdueDisenoCOUNT->execute();

  $stmtOverdueApplication = $dbh->prepare("SELECT
                                              e.nombre AS eNombre,
                                              ap.idActividades_proyecto,
                                              p.idProyecto,
                                              p.projectID,
                                              p.descripcion AS pDescripcion,
                                              p.nombre AS pNombre,
                                              ap.idActividad,
                                              a.nombre AS aNombre,
                                              DATE(ap.fechaRequerida) AS fechaRequerida
                                          FROM
                                              actividad_recursos_adicionales AS ara
                                              INNER JOIN actividades_proyecto AS ap ON ara.idActividades_proyecto = ap.idActividades_proyecto
                                              INNER JOIN proyecto AS p ON ap.idProyecto = p.idProyecto
                                              INNER JOIN actividad AS a ON ap.idActividad = a.idActividad
                                              INNER JOIN empleado AS e ON p.idRespDiseno = e.idEmpleado
                                          WHERE
                                              ara.fechaEntrega IS NULL
                                              AND ap.fechaRequerida <= DATE(now())
                                              AND p.idStatus <> 5
                                              AND p.idStatus <> 6
                                              AND a.obsoleta <> 1
                                              AND p.isApplication = 1
                                              AND a.idActividad IN(
                                                  SELECT
                                                      actividad.idActividad
                                                  FROM
                                                      actividad
                                                  WHERE
                                                      actividad.resp = 'DE'
                                                      AND actividad.obsoleta <> 1
                                              )
                                              AND e.activo <> 0
                                          ORDER BY
                                              eNombre,
                                              idProyecto,
                                              fechaRequerida DESC");
  $stmtOverdueApplication->execute();
  $stmtOverdueApplicationCOUNT = $dbh->prepare("SELECT e.nombre, COUNT(e.nombre) AS overdue
                                                FROM
                                                    actividad_recursos_adicionales AS ara
                                                    INNER JOIN actividades_proyecto AS ap ON ara.idActividades_proyecto = ap.idActividades_proyecto
                                                    INNER JOIN proyecto AS p ON ap.idProyecto = p.idProyecto
                                                    INNER JOIN actividad AS a ON ap.idActividad = a.idActividad
                                                    INNER JOIN empleado AS e ON p.idRespDiseno = e.idEmpleado
                                                WHERE
                                                    ara.fechaEntrega IS NULL
                                                    AND ap.fechaRequerida <= DATE(now())
                                                    AND p.idStatus <> 5
                                                    AND p.idStatus <> 6
                                                    AND a.obsoleta <> 1
                                                    AND p.isApplication = 1
                                                    AND e.activo <> 0");
  $stmtOverdueApplicationCOUNT->execute();
  $stmtOverdueManu = $dbh->prepare("SELECT e.nombre AS eNombre, ap.idActividades_proyecto, p.idProyecto, p.nombre AS pNombre, ap.idActividad, a.nombre AS aNombre, DATE(ap.fechaRequerida) AS fechaRequerida
                                    FROM actividades_proyecto AS ap
                                    INNER JOIN proyecto AS p
                                    ON ap.idProyecto = p.idProyecto
                                    INNER JOIN actividad AS a
                                    ON ap.idActividad = a.idActividad
                                    INNER JOIN empleado AS e
                                    ON p.idRespManu = e.idEmpleado
                                    WHERE ap.entregadoPor IS NULL AND ap.fechaRequerida <= DATE(now()) AND p.idStatus <> 5 AND p.idStatus <> 6 AND a.obsoleta <> 1 AND a.idActividad IN(39,68,70,71,72,73,74,75,76,77,78,82,83,103,105,106,119,124,129,131,132,137)
                                    ORDER BY idProyecto, fechaRequerida DESC");
    $stmtOverdueManu->execute();
    $stmtOverdueManuCOUNT = $dbh->prepare("SELECT e.nombre, COUNT(e.nombre) AS overdue
                                      FROM `actividades_proyecto` AS ap
                                      INNER JOIN proyecto AS p
                                      ON ap.idProyecto = p.idProyecto
                                      INNER JOIN actividad AS a
                                      ON ap.idActividad = a.idActividad
                                      INNER JOIN empleado AS e
                                      ON p.idRespManu = e.idEmpleado
                                      WHERE ap.entregadoPor IS NULL AND ap.fechaRequerida <= DATE(now()) AND p.idStatus <> 5 AND p.idStatus <> 6 AND a.obsoleta <> 1 AND a.idActividad IN(39,68,70,71,72,73,74,75,76,77,78,82,83,103,105,106,119,124,129,131,132,137)
                                      GROUP BY e.nombre");
      $stmtOverdueManuCOUNT->execute();

 ?>
<!DOCTYPE html>

      <br>
          <div class="card">
              <h5 class="card-header text-center">Application Overdue Activities</h5>
              <div class="card-body">
                  <div class="row">
                      <?php
                            $name2 = "";
                            $ul2 = 0;
                            $counts = $stmtOverdueApplicationCOUNT->fetchAll();
                            // var_dump($counts);
                            while ($resultado = $stmtOverdueApplication->fetch()) {
                                if ($name2 == ""){
                                    echo '<div class="col-6">';
                                    echo '<div class="card">';
                                    echo    '<div class="card-header bg-info fw-bold">';
                                    echo        $resultado->eNombre . "  ";
                                    foreach ($counts as $resultado2) {
                                        if ($resultado2->nombre == $resultado->eNombre) {
                                            echo '<span class="badge bg-danger">'.$resultado2->overdue.'</span>';
                                        }
                                    }
                                    // while ($resultado2 = $stmtOverdueApplicationCOUNT->fetch()) {
                                    //     if ($resultado2->nombre == $resultado->eNombre) {
                                    //         echo '<span class="badge bg-danger">'.$resultado2->overdue.'</span>';
                                    //     }
                                    // }
                                    echo     '</div>';
                                }elseif ($name2 != $resultado->eNombre) {
                                    $ul2 = 0;
                                    echo '</ul>'; // Cierra UL
                                    echo '</div>'; // Cierra DIV
                                    echo '</div>'; // Cierra DIV de col-6
                                    echo '<div class="col-6">';
                                    echo '<div class="card">';
                                    echo    '<div class="card-header bg-info fw-bold">';
                                    echo $resultado->eNombre;
                                    foreach ($counts as $resultado2) {
                                        if ($resultado2->nombre == $resultado->eNombre) {
                                            echo '<span class="badge bg-danger">'.$resultado2->overdue.'</span>';
                                        }
                                    }
                                    echo '</div>';
                                }
                                if ($ul2 == 0) {
                                    echo '<ul class="list-group list-group-flush">';
                                    $ul2 = 1;
                                }
                                echo        '<li class="list-group-item"><a href="/pages/proyecto_detalle/proyecto_detalle_application.php?id='.$resultado->idProyecto.'&back=engineer_activities" style="text-decoration: none; a:visited {color:#00FF00}">' .
                                $resultado->projectID . ' - ' . $resultado->pDescripcion . '</a> - ' .
                                $resultado->aNombre . ' - Due <span style="color: red">' .
                                $resultado->fechaRequerida . '</span>';
                                echo        '</li>';
                                $name2 = $resultado->eNombre;
                            }
                            echo '</ul>'; // Cierra UL
                            echo '</div>'; // Cierra DIV
                            echo '</div>'; // Cierra DIV
                            ?>
                  </div>
              </div>
          </div>
          <div class="card mt-3">
              <h5 class="card-header text-center">Overdue Activities</h5>
              <div class="card-body">
                  <div class="row">
                    <div class="col-6">
                      <?php
                            $name = "";
                            $ul = 0;
                            while ($resultado = $stmtOverdueManu->fetch()) {
                                if ($name == ""){
                                    echo '<div class="card">';
                                    echo    '<div class="card-header bg-info fw-bold">';
                                    echo        $resultado->eNombre . "  ";
                                    while ($resultado2 = $stmtOverdueManuCOUNT->fetch()) {
                                        if ($resultado2->nombre == $resultado->eNombre) {
                                            echo '<span class="badge bg-danger">'.$resultado2->overdue.'</span>';
                                        }
                                    }
                                    echo     '</div>';
                                }elseif ($name != $resultado->eNombre) {
                                    $ul = 0;
                                    echo '</ul>'; // Cierra UL
                                    echo '</div>'; // Cierra DIV
                                    echo '<div class="card" style="width: 18rem;">';
                                    echo '<div class="card-header">';
                                    echo $resultado->eNombre;
                                    echo '</div>';
                                }
                                if ($ul == 0) {
                                    echo '<ul class="list-group list-group-flush">';
                                    $ul = 1;
                                }
                                echo        '<li class="list-group-item"><a href="/pages/proyecto_detalle/proyecto_detalle_coe.php?id='.$resultado->idProyecto.'&back=engineer_activities" style="text-decoration: none; a:visited {color:#00FF00}">' .
                                $resultado->pNombre . '</a> - ' .
                                $resultado->aNombre . ' - Due <span style="color: red">' .
                                $resultado->fechaRequerida . '</span>';
                                echo        '</li>';
                                $name = $resultado->eNombre;
                            }
                            echo '</ul>'; // Cierra UL
                            echo '</div>'; // Cierra DIV
                            ?>
                    </div>
                    <div class="col-6">
                      <?php
                            $name2 = "";
                            $ul2 = 0;
                            while ($resultado = $stmtOverdueDiseno->fetch()) {
                                if ($name2 == ""){
                                    echo '<div class="card">';
                                    echo    '<div class="card-header bg-info fw-bold">';
                                    echo        $resultado->eNombre . "  ";
                                    while ($resultado2 = $stmtOverdueDisenoCOUNT->fetch()) {
                                        if ($resultado2->nombre == $resultado->eNombre) {
                                            echo '<span class="badge bg-danger">'.$resultado2->overdue.'</span>';
                                        }
                                    }
                                    echo     '</div>';
                                }elseif ($name2 != $resultado->eNombre) {
                                    $ul2 = 0;
                                    echo '</ul>'; // Cierra UL
                                    echo '</div>'; // Cierra DIV
                                    echo '<div class="card" style="width: 18rem;">';
                                    echo '<div class="card-header">';
                                    echo $resultado->eNombre;
                                    echo '</div>';
                                }
                                if ($ul2 == 0) {
                                    echo '<ul class="list-group list-group-flush">';
                                    $ul2 = 1;
                                }
                                echo        '<li class="list-group-item"><a href="/pages/proyecto_detalle/proyecto_detalle_coe.php?id='.$resultado->idProyecto.'&back=engineer_activities" style="text-decoration: none; a:visited {color:#00FF00}">' .
                                $resultado->pNombre . '</a> - ' .
                                $resultado->aNombre . ' - Due <span style="color: red">' .
                                $resultado->fechaRequerida . '</span>';
                                echo        '</li>';
                                $name2 = $resultado->eNombre;
                            }
                            echo '</ul>'; // Cierra UL
                            echo '</div>'; // Cierra DIV
                            ?>
                    </div>
                  </div>
              </div>
          </div>

          <div class="card mt-4">
              <h5 class="card-header text-center">Activities per Engineer</h5>
              <div class="card-body">
                  <div class="" style="height: 600px; width: 100%;" id="activitiesPerEngineer"></div>
                  <div class="" style="width: 100%;" name="detail" id="detail"></div>
              </div>
          </div>

          <div class="card mt-4">
              <h5 class="card-header text-center bg-primary text-white fw-bold">COE Historic Activities per Engineer</h5>
              <div class="card-body">
                  <div class="" style="height: 600px; width: 100%;" id="historicActPerEng"></div>
              </div>
          </div>

          <div class="card mt-4">
              <h5 class="card-header text-center bg-success text-white fw-bold">Application Historic Activities per Engineer</h5>
              <div class="card-body">
                  <div class="" style="height: 600px; width: 100%;" id="applicationHistoricActPerEng"></div>
              </div>
          </div>

      <!-- <div class="" style="display: flex; justify-content: space-around;">
          <div class="column card" style="width: 100%">
              <div class="flex-containter" >
                <h3 class="" style="margin-bottom: 20px; text-align: center;">Activities per Engineer</h3>
                <div class="" style="height: 600px; width: 100%;" id="activitiesPerEngineer"></div>
              </div>
          </div>
      </div> -->

      <!-- <div class="" style="display: flex; justify-content: space-around;">
          <div class="column card" style="width: 100%">
              <div class="flex-containter" >
                <h3 class="" style="margin-bottom: 20px; text-align: center;">Activity Detail</h3>
                <div class="" style="width: 100%;" name="detail" id="detail"></div>
              </div>
          </div>
      </div> -->

      <!-- <div class="" style="display: flex; justify-content: space-around;">
          <div class="column card" style="width: 100%">
              <div class="flex-containter" >
                <h3 class="" style="margin-bottom: 20px; text-align: center;">Historic Activities per Engineer</h3>
                <div class="" style="height: 600px; width: 100%;" id="historicActPerEng"></div>
              </div>
          </div>
      </div> -->


<?php include "inc/footer.html"; ?>
