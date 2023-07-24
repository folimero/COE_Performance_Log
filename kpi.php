<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(27, $_SESSION["permisos"])) {
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
    $filterYear = date("Y");
  }

  ?>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<?php
  // QUOTES SECTION
  $stmtOTC = $dbh->prepare("SELECT monthname(fechaLanzamiento) AS mes,
                          SUM(
                              CASE
                              WHEN DATEDIFF(IFNULL(fechaReqCliente,fechaLanzamiento),fechaLanzamiento) >= 0 THEN 1
                              ELSE 0
                          END
                              ) AS onTIME,
                          COUNT(idCotizacion) AS cotizaciones, (SUM(
                              CASE
                              WHEN DATEDIFF(IFNULL(fechaReqCliente,fechaLanzamiento),fechaLanzamiento) >= 0 THEN 1
                              ELSE 0
                          END )/COUNT(idCotizacion))*1 AS percentage
                          FROM cotizacion
                          WHERE cotizacion.idStatus = 7 AND YEAR(fechaLanzamiento) = '$filterYear' AND cotizacion.consOTC <> -1
                          GROUP by month(fechaLanzamiento)");
  $stmtOTC->execute();
  $maxQuotes = 0;
  $data = "";
  $data .= "['MONTH', 'ONTIME', 'QUOTES', 'PERCENTAGE'],";
  while ($resultado = $stmtOTC->fetch()) {
      if ($maxQuotes < $resultado->cotizaciones) {
          $maxQuotes = $resultado->cotizaciones;
      }
      $data .= "['" . $resultado->mes . "'," . $resultado->onTIME . "," . $resultado->cotizaciones . "," . $resultado->percentage . "],";
  }

  $stmtTotalQuote = $dbh->prepare("SELECT COUNT(idCotizacion) AS quoteTotales FROM `cotizacion`
                                  WHERE idStatus = 7  AND year(fechaLanzamiento) = $filterYear");
  $stmtTotalQuote->execute();
  $stmtAwardeQuote = $dbh->prepare("SELECT COUNT(idCotizacion) AS quoteAwarded FROM `cotizacion`
                                    WHERE idStatus = 7 AND year(fechaLanzamiento) = $filterYear AND awarded = 1");
  $stmtAwardeQuote->execute();
  $stmtAVG = $dbh->prepare("SELECT AVG(5 * (DATEDIFF(cotizacion.fechaLanzamiento, cotizacion.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(cotizacion.fechaInicio) + WEEKDAY(cotizacion.fechaLanzamiento) + 1, 1)) AS promedio
                        FROM cotizacion
                        INNER JOIN cliente
                        ON cotizacion.idCliente = cliente.idCliente
                        LEFT JOIN cliente_contacto
                        ON cotizacion.idClienteContacto = cliente_contacto.idClienteContacto
                        INNER JOIN status
                        ON cotizacion.idStatus = status.idStatus
                        WHERE (cotizacion.idStatus = 5 OR cotizacion.idStatus = 7) AND YEAR(fechaLanzamiento) = '$filterYear' AND consOTC = 1");
  $stmtAVG->execute();


  // COE SECTION
  $stmtTotalProjects = $dbh->prepare("SELECT COUNT(idProyecto) AS proyectos  FROM `proyecto`
                                      WHERE idStatus = 7 AND year(fechaInicio) = $filterYear AND idTipoProyecto <> 22");
  $stmtTotalProjects->execute();
  $stmtShippedQty = $dbh->prepare("SELECT IFNULL(SUM(ensambles.cantTerm),0) AS ensambles FROM `proyecto`
                                  INNER JOIN ensambles
                                  ON proyecto.idProyecto = ensambles.idProyecto
                                  WHERE idStatus = 7 AND year(fechaInicio) = '$filterYear' AND idTipoProyecto <> 22");
  $stmtShippedQty->execute();
  $stmtApprovedProjects = $dbh->prepare("SELECT COUNT(idProyecto) AS aprobados  FROM `proyecto`
                                      WHERE idStatus = 7 AND year(fechaInicio) = $filterYear AND idTipoProyecto <> 22 AND proyecto.awarded = 1");
  $stmtApprovedProjects->execute();
  $stmtAVGProjects = $dbh->prepare("SELECT AVG(5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaEmbarque) + 1, 1)) AS promedio
                                    FROM proyecto
                                    WHERE (proyecto.idStatus = 7) AND YEAR(proyecto.fechaInicio) = '$filterYear'");
  $stmtAVGProjects->execute();
  $stmtTotalSales = $dbh->prepare("SELECT IFNULL(sum(cotizacion.ventasPotenciales), 0) AS total
                                    FROM `cotizacion`
                                    WHERE idStatus = 7 AND awarded = 1 AND year(fechaLanzamiento) = $filterYear");
  $stmtTotalSales->execute();
  $stmtProyectoVentasPotenciales = $dbh->prepare("SELECT IFNULL(sum(proyecto.ventasPotenciales), 0) AS total
                                                  FROM `proyecto`
                                                  WHERE idStatus = 7 AND awarded = 1 AND year(fechaInicio) = $filterYear");
  $stmtProyectoVentasPotenciales->execute();

  $currentYear = date("Y");
  // TABLA VENTAS
  $stmtVentasCompare = $dbh->prepare("SELECT DATE(fechaLanzamiento) AS fechaLanzamiento, DATE(dateAwarded) AS dateAwarded, cotizacion.idCotizacion, quoteID, nombre, IF(ventasPotenciales IS NULL or ventasPotenciales = '', 0, ventasPotenciales) as ventasPotenciales,
                                            IFNULL((SELECT SUM(ventas.venta) FROM ventas WHERE idCotizacion = cotizacion.idCotizacion AND ventas.anio = $filterYear),0) AS ventasTotalesYTD,
                                            IFNULL((SELECT SUM(ventas.venta) AS venta FROM ventas  WHERE idCotizacion = cotizacion.idCotizacion GROUP BY idCotizacion),0) AS ventasTotalesLife
                                      FROM cotizacion
                                      WHERE awarded = 1
                                      ORDER BY fechaLanzamiento ASC");
  $stmtVentasCompare->execute();
  $dataVentas = "";

  $salesYTD = 0;
  while ($resultado = $stmtVentasCompare->fetch()) {
      $dataVentas .= "['" . str_replace("'","\\'",$resultado->fechaLanzamiento) .
                "','" . str_replace("'","\\'",$resultado->dateAwarded) .
                "','" . str_replace("'","\\'",$resultado->quoteID) .
                "','" . str_replace("'","\\'",$resultado->nombre)  .
                // "'," . number_format($resultado->ventasTotalesYTD * 1,1) .
                "'," . $resultado->ventasTotalesYTD .
                "," . $resultado->ventasTotalesLife .
                "," .  $resultado->ventasPotenciales ."],";

      $salesYTD += $resultado->ventasTotalesYTD;
  }

  $stmtAnios = $dbh->prepare("SELECT YEAR(fechaLanzamiento) AS anio FROM cotizacion
                              WHERE fechaLanzamiento IS NOT NULL
                              GROUP BY YEAR(fechaLanzamiento) DESC");
  $stmtAnios->execute();
  $stmtHitRatio = $dbh->prepare("SELECT YEAR(fechaLanzamiento) AS anio,
                                    sum(case when idStatus = 7 then 1 else 0 end) AS totalQuotes,
                                    sum(case when idStatus = 7 AND awarded = 1 then 1 else 0 end) AS awardedQuotes,
                                    (sum(case when idStatus = 7 AND awarded = 1 then 1 else 0 end) / sum(case when idStatus = 7 then 1 else 0 end)) AS hitRatio
                                FROM cotizacion
                                WHERE idStatus = 7
                                GROUP BY anio");
  $stmtHitRatio->execute();
  $dataHitbyYear = "['YEAR','HIT RATIO'],";
  while ($resultado = $stmtHitRatio->fetch()) {
      $dataHitbyYear .= "['" . str_replace("'","\\'",$resultado->anio) .
                        "'," .  $resultado->hitRatio ."],";
  }

  // VARIABLES
  $totalQuote = $stmtTotalQuote->fetch()->quoteTotales;
  $totalAwardedQuote = $stmtAwardeQuote->fetch()->quoteAwarded;
  $totalTurnAround = $stmtAVG->fetch()->promedio;
  $totalSales = $stmtTotalSales->fetch()->total;
  $totalEstimatedSalesProject = $stmtProyectoVentasPotenciales->fetch()->total;

  $totalProjects = $stmtTotalProjects->fetch()->proyectos;
  $totalShippedQty = $stmtShippedQty->fetch()->ensambles;
  $ApprovedProjects = $stmtApprovedProjects->fetch()->aprobados;
  $totalTurnAroundProyecto = $stmtAVGProjects->fetch()->promedio;

?>

  <script type="text/javascript">
      // Load google charts
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);
      google.charts.setOnLoadCallback(drawChart);
      google.charts.load('current', {'packages':['table']});
      google.charts.setOnLoadCallback(drawTable);

      // Tabla Recursos Asignados / SEMANALAES
      function drawVisualization() {
          // Some raw data (not necessarily accurate)
          var data = google.visualization.arrayToDataTable([
            <?php echo $data; ?>
          ]);

          var tick = [];
          var records = <?php echo $maxQuotes; ?>;

          for (var i = 0; i < records + 2; i++) {
              tick.push(i);
          }

          var options = {
            title : 'Quote OTC <?php echo "$filterYear"; ?>',
            titlePosition: 'center',
            vAxes: {
              0: {
                title:'Qty',
                textStyle: {color: 'red'},
                ticks: tick
                // ticks: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
              },
              1: {
                title:'% RFQ On Time',
                textStyle: {color: 'red'},
                format: 'percent',
                viewWindow: {
                    min: 0,
                    max: 1
                },
                ticks: [0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1]
              }
            },
            hAxis: {title: 'Month'},
            seriesType: 'bars',
            isStacked: false,
            series: {
              0: {targetAxisIndex:0},
              1: {targetAxisIndex:0},
              2: {targetAxisIndex: 1, type: 'line'}
            }
          };

          var formatter = new google.visualization.NumberFormat({pattern: '#%'});
          formatter.format(data, 3); // format column 1

          var chart = new google.visualization.ComboChart(document.getElementById('OTC'));
          chart.draw(data, options);
      }

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
            <?php echo $dataHitbyYear; ?>
        ]);

        var options = {
            title: '',
            // curveType: 'function',
            vAxis: {
              format: 'percent',
              // ticks: tick
              ticks: [0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1]
            },
            legend: { position: 'bottom' },
        };

        var formatter = new google.visualization.NumberFormat({pattern: '#%'});
        formatter.format(data, 1); // format column 1

        var chart = new google.visualization.LineChart(document.getElementById('hitByYear'));

        chart.draw(data, options);
      }

      function drawTable() {

          var data = new google.visualization.DataTable();
          data.addColumn('string', 'Completed Date');
          data.addColumn('string', 'Awarded Date');
          data.addColumn('string', 'Quote ID');
          data.addColumn('string', 'Quote Name');
          data.addColumn('number', 'Sales YTD (<?php echo $filterYear; ?>)');
          data.addColumn('number', 'Total Sales');
          data.addColumn('number', 'Sales Per Quoted EAU');

          // var formatter = new google.visualization.NumberFormat({decimalSymbol: ',',groupingSymbol: '.', negativeColor: 'red', negativeParens: true, prefix: '$ '});

          // data.addColumn('number', 'Salary');
          // data.addColumn('boolean', 'Full Time Employee');
          data.addRows([
            <?php echo $dataVentas ?>
            // ['Jim',   {v:8000,   f: '$8,000'},  false],
            // ['Alice', {v: 12500, f: '$12,500'}, true],
            // ['Bob',   {v: 7000,  f: '$7,000'},  true]
          ]);

          function getSum(data, column) {
              var total = 0;
              for (i = 0; i < data.getNumberOfRows(); i++){
                  total = total + data.getValue(i, column);
              }
              return total;
          }

          data.addRow(['','','','TOTALS',getSum(data,4),getSum(data,5),getSum(data,6)]);

          var formatter = new google.visualization.NumberFormat({prefix: '$', negativeColor: 'red', negativeParens: true});
          formatter.format(data, 4);
          formatter.format(data, 5);
          formatter.format(data, 6);

          var table = new google.visualization.Table(document.getElementById('table_div'));

          // formatter.format(data, 2);
          // formatter.format(data, 3);

          table.draw(data, {allowHtml: true, showRowNumber: false, width: '100%', height: '100%'});
        }

  </script>

<!DOCTYPE html>
    <div class="flex-container"  style="margin: 40px 0px;">
        <h1>KPI</h1>
        <div class="row">
            <div class="col-md-9">
                <h3 class="panel-title">Filter by Year</h3>
            </div>
            <div class="col-md-3">
                <select name="year" class="form-control" id="year" onchange="javascript:location.href = this.value;">
                    <option disabled value> -- Select year -- </option>
                <?php
                while ($ResultadoAnios = $stmtAnios->fetch()) {
                    if ($filterYear == $ResultadoAnios->anio) {
                        echo '<option selected value="kpi.php?year=' . $ResultadoAnios->anio .'">' . $ResultadoAnios->anio . '</option>';
                    }else {
                        echo '<option value="kpi.php?year=' . $ResultadoAnios->anio .'">' . $ResultadoAnios->anio . '</option>';
                    }
                }
                ?>
                </select>
            </div>
        </div>
    </div>

      <div class="" style="display: flex; justify-content: space-around;">
          <div class="column card" style="width: 100%">
              <div class="flex-containter" >
                <!-- <h3 class="" style="margin-top: 20px; text-align: center;"></h3> -->
                <div class="" style="height: 600px; width: 100%;" id="OTC"></div>
              </div>
          </div>
      </div>
      <!-- <div class="" style="display: flex; justify-content: space-around;">
          <div class="column card" style="width: 100%">
              <div class="flex-containter" >
                <h3 class="" style="margin-bottom: 20px; text-align: center;">Hit Ratio by Year</h3>
                <div class="" style="height: 600px; width: 100%;" id="hitByYear"></div>
              </div>
          </div>
      </div> -->

      <!-- QUOTE HIT RAQTIO -->
      <div class="" style="display: flex; justify-content: space-around;">
          <div class="column card" style="width: 50%">
              <div class="flex-containter" >
                <h3 class="" style="margin-bottom: 20px; text-align: center;">QUOTING HIT RATIO <?php echo $filterYear; ?> AND TURNAROUND</h3>
                <div class="" style="width: 100%;" id="OTC">
                  <div class="row" id="rowDetalleProyecto">
                      <!-- Primera Columna -->
                      <div class="column" name="columna 1" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                     <h4>YTD Completed RFQ Projects: </h4>
                                  </div>
                                  <div class="">
                                    <p style="text-align: left;"><?php echo $totalQuote; ?><p>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <!-- Segunda Columna -->
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>Approved projects (FA PO at least):</h4>
                                  </div>
                                  <div class="">
                                    <p style="text-align: left;"><?php echo $totalAwardedQuote; ?><p>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>COE HIT RATIO% (APPROVED PROJECTS):</h4>
                                  </div>
                                  <div class="">
                                  <?php if (!is_null($totalQuote) && $totalQuote>0 ) { ?>
                                      <p style="text-align: left;"><?php echo number_format($totalAwardedQuote / $totalQuote * 100) . "%"; ?><p>
                                  <?php } else { ?>
                                      <p style="text-align: left;">0%<p>
                                  <?php } ?>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>Potential Sales per EAU (per approved projects):</h4>
                                  </div>
                                  <div class="">
                                    <?php if (!is_null($totalSales) && $totalSales > 0 ) { ?>
                                        <p style="text-align: left;">$ <?php echo number_format($totalSales * 1,1); ?><p>
                                    <?php } else { ?>
                                        <p style="text-align: left;">$ 0.00<p>
                                    <?php } ?>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>QUOTING TURNAROUND YTD:</h4>
                                  </div>
                                  <div class="">
                                    <p style="text-align: left;"><?php echo number_format($totalTurnAround * 1,1); ?><p>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                </div>
                <div class="" style="height: 300px; width: 100%;" id="hitByYear"></div>
              </div>
          </div>

          <!-- COE HIT RATIO -->
          <div class="column card" style="width: 50%">
              <div class="flex-containter" >
                <h3 class="" style="margin-bottom: 20px; text-align: center;">COE HIT RATIO <?php echo $filterYear; ?></h3>
                <div class="" style="height: 450px; width: 100%;" id="OTC">
                  <div class="row" id="rowDetalleProyecto">
                      <!-- Primera Columna -->
                      <div class="column" name="columna 1" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                     <h4>YTD Completed RFQ Projects: </h4>
                                  </div>
                                  <div class="">
                                    <p style="text-align: left;"><?php echo $totalProjects; ?><p>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <!-- Segunda Columna -->
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>FA's Shipped:</h4>
                                  </div>
                                  <div class="">
                                    <p style="text-align: left;"><?php echo $totalShippedQty; ?><p>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>Approved projects:</h4>
                                  </div>
                                  <div class="">
                                    <p style="text-align: left;"><?php echo $ApprovedProjects; ?><p>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>COE HIT RATIO% (APPROVED PROJECTS):</h4>
                                  </div>
                                  <div class="">
                                  <?php if (!is_null($totalProjects) && $totalProjects>0 ) { ?>
                                      <p style="text-align: left;"><?php echo number_format($ApprovedProjects / $totalProjects * 100) . "%"; ?><p>
                                  <?php } else { ?>
                                      <p style="text-align: left;">0%<p>
                                  <?php } ?>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>Sales YTD:</h4>
                                  </div>
                                  <div class="">
                                    <?php if (!is_null($salesYTD) && $salesYTD > 0 ) { ?>
                                        <p style="text-align: left;">$ <?php echo number_format($salesYTD * 1,1); ?><p>
                                    <?php } else { ?>
                                        <p style="text-align: left;">$ 0.00<p>
                                    <?php } ?>
                                  </div>
                              </div>
                          </div>
                      </div>
                            <hr style="width:100%;">
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>* COE YTD Turnaround (Lead Time) :</h4>
                                  </div>
                                  <div class="">
                                    <p style="text-align: left;"><?php echo number_format($totalTurnAroundProyecto,1); ?><p>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <div class="column" name="columna 2" style="width: 100%; margin: 0;">
                          <div class="column-format2">
                              <div class="inline-container" style="display: flex; justify-content: space-between;">
                                  <div class="">
                                    <h4>
                                      This considers cycle time from Project receipt dateto prototype/FA ship date<br>
                                      * This is an average LT, details on "Completed COE Projects"<br>
                                      * KPI measures Project start date vs Project ship date
                                    </h4>
                                  </div>
                                  <div class="">
                                    <p style="text-align: left;"><p>
                                  </div>
                              </div>
                          </div>
                      </div>

                  </div>
                </div>
              </div>
          </div>
      </div>

      <!-- TABLAS -->
      <div class="" style="display: flex; justify-content: space-around; text-align-last: center;">
          <div class="column card" style="width: 100%">
            <div class="flex-containter" style="margin-bottom: 20px;">
              <h3 class="" style="width: 100%; margin-bottom: 20px; text-align: center;">Quoted YTD Sales (<?php echo $filterYear; ?>)</h3>
              <div class="" style="padding: 0; width: 100%;" id="table_div"></div>
            </div>
          </div>
      </div>


<?php include "inc/footer.html"; ?>
