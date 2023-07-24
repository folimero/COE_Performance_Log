<?php session_start();
  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(31, $_SESSION["permisos"]) && !in_array(32, $_SESSION["permisos"])) {
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
  // Funcion para limpiar campos
  function cleanInput($value) {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  // if (isset($_GET['idRecurso'])) {
  //     $id=$_GET['id'];
  //     $idRecurso=$_GET['idRecurso'];
  //     include "inc/conexion.php";
  //
  //     $stmt = $dbh-> prepare("DELETE FROM recursos_asignados
  //                             WHERE idRecurso = $idRecurso");
  //       // Ejecutar la consulta preparada
  //       $stmt->execute();
  // }
  // if (isset($_GET['idCapRequeridas'])) {
  //     $id=$_GET['id'];
  //     $idCapRequeridas=$_GET['idCapRequeridas'];
  //     include "inc/conexion.php";
  //
  //     $stmt = $dbh-> prepare("DELETE FROM cap_requeridas
  //                             WHERE idCapRequeridas = $idCapRequeridas");
  //     // Ejecutar la consulta preparada
  //     $stmt->execute();
  // }
  // if (isset($_GET['idEnsamble'])) {
  //     $id=$_GET['id'];
  //     $idEnsamble=$_GET['idEnsamble'];
  //     include "inc/conexion.php";
  //
  //     $stmt = $dbh-> prepare("DELETE FROM ensambles
  //                             WHERE idEnsamble = $idEnsamble");
  //       // Ejecutar la consulta preparada
  //       $stmt->execute();
  // }

  // Campos obtenidos en GET
  $URL = "index.php";
  $id;
  $isAwarded;
  $bomHrs;
  $tipoHRS;
  $actHRS;
  $totalHRS;
  $idStatus;
  $VentasTotales = 0;
  if (isset($_GET['id'])) {
      $id = cleanInput($_GET['id']);
      include "inc/headerBoostrap.php";
      include "inc/conexion.php";
      $stmt = $dbh->prepare("SELECT idCotizacion, quoteID, cotizacion.nombre, cotizacion.descripcion, cotizacion_categoria.categoria, cotizacion_categoria.descripcion AS quoteDesc,
                                      complejidad.nombre AS complex, cotizacion_volumen.nombre AS volumen, tipocotizacion.horas AS hoursquote, cliente_contacto.nombre AS custcontact,
                                      uniqueFG, lineItems, overallComplet, status.nombre AS stat, notas, DATE(fechaInicio) AS fechaInicio, DATE(fechaLanzamiento) AS fechaLanzamiento,
                                      DATE(fechaReqCliente) AS fechaReqCliente, consolidatedEAU, status.idStatus, consOTC, IF(ventasPotenciales IS NULL or ventasPotenciales = '', 0, ventasPotenciales) AS ventasPotenciales,
                                      5 * (DATEDIFF(cotizacion.fechaLanzamiento, cotizacion.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(cotizacion.fechaInicio) + WEEKDAY(cotizacion.fechaLanzamiento) + 1, 1) AS turnAround,
                                      awarded, cliente.nombreCliente, DATE(sourcMatStartDate) AS sourcMatStartDate, DATE(sourcMatEndDate) AS sourcMatEndDate, DATE(cotizacion.dateBDM) AS reqBDM,
                              	      (SELECT cotizacion_categoria.categoria
                                          FROM tipocotizacion
                                          INNER JOIN cotizacion_categoria
                                          ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
                                          WHERE idTipoCotizacion = cotizacion.BOMType) AS BOMQuote,
                                      (SELECT cotizacion_categoria.descripcion
                                          FROM tipocotizacion
                                          INNER JOIN cotizacion_categoria
                                          ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
                                          WHERE idTipoCotizacion = cotizacion.BOMType) AS BOMDescripcion,
                                      (SELECT tipocotizacion.horas
                                          FROM tipocotizacion
                                          WHERE idTipoCotizacion = cotizacion.BOMType) AS BOMHours,
                                      TRUNCATE((IFNULL(tipocotizacion.horas,0) + IFNULL((SELECT tipocotizacion.horas
                                          FROM tipocotizacion
                                          WHERE idTipoCotizacion = cotizacion.BOMType),0)) * (1 - overallComplet),2) AS horasTotales,
                                      (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idResponsable) AS respCotizacion,
                                      (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idRepreVentas) AS repVentas
                              FROM cotizacion
                              INNER JOIN cliente
                              ON cotizacion.idCliente = cliente.idCliente
                              INNER JOIN tipocotizacion
                              ON cotizacion.idTipoCotizacion = tipocotizacion.idTipoCotizacion
                              INNER JOIN cotizacion_volumen
                              ON tipocotizacion.idCotizacionVolumen = cotizacion_volumen.idCotizacionVolumen
                              INNER JOIN cotizacion_categoria
                              ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
                              INNER JOIN complejidad
                              ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad
                              LEFT JOIN cliente_contacto
                              ON cliente_contacto.idClienteContacto = cotizacion.idClienteContacto
                              INNER JOIN status
                              ON cotizacion.idStatus = status.idStatus
                              WHERE idCotizacion = $id");
      $stmt->execute();
  }

  while ($resultado = $stmt->fetch()) {
    $idStatus = $resultado->idStatus;
    if ($resultado->awarded == 1) {
        $isAwarded = 'YES';
    } else {
        $isAwarded = 'NO';
    }
    if ($resultado->BOMHours <> "") {
        $bomHrs = $resultado->BOMHours . ' Hrs.';
    } else {
        $bomHrs = '';
    }
  ?>
  <!DOCTYPE html>
      <!-- <div class="flex-container">
          <h1>Quote Detail</h1>
      </div> -->
      <div class='icon-container' style="margin: 20px 0px;">
          <a id='backBtn' href='cotizacion.php'>
              <div class='back-icon-green'></div>
          </a>
      </div>

      <hr style="width:100%;">

      <?php  if (in_array(31, $_SESSION["permisos"])) { ?>
                <div class="stage-label">
                  <a href="cotizacion_alta.php?id=<?php echo $id; ?>">
                      <h2 class="neutral_status" id="editButton">Edit</h2>
                  </a>
                <?php if ($resultado->stat == "YELLOW STATUS") { ?>
                          <a href="#" onclick="abrirVentanaStatus()">
                              <h2 class="yellow_status" idStatus= '<?php echo $resultado->idStatus; ?>' id="statusLabel">YELLOW STATUS</h2>
                          </a>
                <?php } elseif ($resultado->stat == "GREEN STATUS") { ?>
                          <a href="#" onclick="abrirVentanaStatus()">
                              <h2 class="green_status" idStatus= '<?php echo $resultado->idStatus; ?>' id="statusLabel">GREEN STATUS</h2>
                          </a>
                <?php } elseif ($resultado->stat == "RED STATUS") { ?>
                          <a href="#" onclick="abrirVentanaStatus()">
                              <h2 class="red_status" idStatus= '<?php echo $resultado->idStatus; ?>' id="statusLabel">RED STATUS</h2>
                          </a>
                <?php } else { ?>
                          <a href="#" onclick="abrirVentanaStatus()">
                              <h2 class="neutral_status" idStatus= '<?php echo $resultado->idStatus; ?>' id="statusLabel"><?php echo $resultado->stat ?></h2>
                          </a>
                <?php }

                    if ($resultado->awarded != 1) { ?>
                        <a href="#" onclick="assignAwarded(this)">
                            <h2 class="neutral_status" id="editAwarded" style="background-color: #E59866;">Non-Awarded</h2>
                            <!-- <h2 class="neutral_status" id="editAwarded" style="background-color: #2ed573;">Awarded</h2> -->
                        </a>
                  <?php } elseif ($resultado->awarded == 1) { ?>
                      <a href="#" onclick="cancelAwarded(this)">
                          <h2 class="neutral_status" id="cancelAwarded" style="background-color: #2ed573;">Awarded</h2>
                          <!-- <h2 class="neutral_status" id="cancelAwarded" style="background-color: #E59866;">UnAwarded</h2> -->
                      </a>
                  <?php } ?>

                </div>
      <?php  } ?>

      <hr style="width:100%;">

      <!-- <div class="container w-100" > -->
          <div class="card mb-3">
              <h2 class="card-header text-center ">Quote Detail</h2>
              <div class="card-body">
                <div class="row">
                  <div class="col-12">
                      <div class="row">
                      <div class="col-6 text-center">
                          <h4 class="border-bottom border-2 ">General</h4>
                      </div>
                      <div class="col-6 text-center">
                          <h4 class="border-bottom border-2 ">Dates and timeline</h4>
                      </div>

                      <!-- FIRST COLUMN -->
                      <div class="col-6">
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Quote ID:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->quoteID;?><h6>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Customer:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->nombreCliente;?><h6>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Type:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><h6>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Name:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->nombre;?><h6>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Quote Description:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->descripcion;?><h6>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Completed:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->overallComplet * 100 . ' %';?><h6>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-6 text-end title-label">
                                <h6>Awarded:</h6>
                            </div>
                            <div class="col-6">
                                <h6 name='awarded' id='awarded' awardedValue='<?php echo $resultado->awarded; ?>' style="text-align: left; font-weight: bold;">
                                  <?php if (is_null($resultado->awarded)) {
                                    echo "PENDING";
                                  }else if ($resultado->awarded == 1) {
                                    echo "YES";
                                  }else {
                                    echo "NO";
                                  } ?>
                                <h6>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Quote Category:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->categoria . " / " . $resultado->quoteDesc;?><h6>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Volume:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo '$ ' . $resultado->volumen . ' US';?><h6>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Quote Hours:</h6>
                            </div>
                            <div class="col-6">
                                <h6 id='idQuoteHrs' style="text-align: left; font-weight: bold;"><?php echo $resultado->hoursquote . " Hrs.";?><h6>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-6 text-end title-label">
                                <h6>Quoting Engineer:</h6>
                            </div>
                            <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->respCotizacion;?><h6>
                            </div>
                          </div>
                      </div>

                      <!-- SECOND COLUMN -->
                      <div class="col-6">
                        <div class="row">
                          <div class="col-6">
                              <div class="row">
                                <div class="col-8 text-end title-label">
                                    <h6>Date Received by BDM:</h6>
                                </div>
                                <div class="col-4">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->reqBDM;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-8 text-end title-label">
                                    <h6>Start Date:</h6>
                                </div>
                                <div class="col-4">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->fechaInicio;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-8 text-end title-label">
                                    <h6>Sourcing Materials Start Date:</h6>
                                </div>
                                <div class="col-4">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->sourcMatStartDate;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-8 text-end title-label">
                                    <h6>Sourcing Materials End Date:</h6>
                                </div>
                                <div class="col-4">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->sourcMatEndDate;?></h6>
                                </div>
                              </div>
                          </div>
                          <div class="col-6">
                              <div class="row">
                                <div class="col-8 text-end title-label">
                                    <h6>Customer Requested Date:</h6>
                                </div>
                                <div class="col-4">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->fechaReqCliente;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-8 text-end title-label">
                                    <h6>Release Date:</h6>
                                </div>
                                <div class="col-4">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->fechaLanzamiento;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-8 text-end title-label">
                                    <h6>Consider for OTC?:</h6>
                                </div>
                                <div class="col-4">
                                    <h6 name='consOTC' id='consOTC' consOTCValue='<?php echo $resultado->consOTC; ?>' style="text-align: left; font-weight: bold;">
                                      <?php if (is_null($resultado->consOTC)) {
                                        echo "PENDING";
                                      }else if ($resultado->consOTC == 1) {
                                        echo "YES";
                                      }else {
                                        echo "NO";
                                      } ?>
                                    </h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-8 text-end title-label">
                                    <h6>Quote Turnaround:</h6>
                                </div>
                                <div class="col-4">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->turnAround;?></h6>
                                </div>
                              </div>
                          </div>
                        </div>

                        <!-- THIRD SECTION -->
                        <div class="row">
                          <div class="col-12 text-center mt-3">
                              <h4 class="border-bottom border-2 ">Assemblies</h4>
                          </div>
                          <div class="row">
                            <div class="col-6">
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>Unique FGs:</h6>
                                </div>
                                <div class="col-6">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->uniqueFG;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>Line Items:</h6>
                                </div>
                                <div class="col-6">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->lineItems;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>Total Workload Hours:</h6>
                                </div>
                                <div class="col-6">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->horasTotales . " Hrs.";?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>Consolidated EAU:</h6>
                                </div>
                                <div class="col-6">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->consolidatedEAU;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>Sales per Consolidated EAU (smallest EAU):</h6>
                                </div>
                                <div class="col-6">
                                    <h6 style="text-align: left; font-weight: bold;">$ <?php echo number_format($resultado->ventasPotenciales, 2, ".", ",");?></h6>
                                </div>
                              </div>
                            </div>

                            <!-- SECOND COLUMN -->
                            <div class="col-6">
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>Complexity:</h6>
                                </div>
                                <div class="col-6">
                                <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->complex;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>BOM Category:</h6>
                                </div>
                                <div class="col-6">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->BOMQuote;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>BOM description:</h6>
                                </div>
                                <div class="col-6">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->BOMDescripcion;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>BOM Hours:</h6>
                                </div>
                                <div class="col-6">
                                    <h6 id='idBOMHrs'style="text-align: left; font-weight: bold;"><?php echo $bomHrs;?></h6>
                                </div>
                              </div>
                            </div>
                          </div>

                        </div>

                      </div>
                    </div>
                  </div>

                  <!-- BOTTOM SECTION -->
                  <div class="row">
                    <div class="col-12">
                      <div class="row">
                          <div class="col-6 text-center">
                              <h4 class="border-bottom border-2 ">Contact Information</h4>
                          </div>
                          <div class="col-6 text-center">
                              <h4 class="border-bottom border-2 ">Quotation Note</h4>
                          </div>

                          <div class="col-6">
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>Customer Contact:</h6>
                                </div>
                                <div class="col-6">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->custcontact;?></h6>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-6 text-end title-label">
                                    <h6>Sales Rep:</h6>
                                </div>
                                <div class="col-6">
                                    <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->repVentas;?></h6>
                                </div>
                              </div>
                          </div>
                          <div class="col-6">
                            <div class="row">
                              <div class="col-12">
                                  <h6 style="text-align: left; font-weight: bold;"><?php echo $resultado->notas;?></h6>
                              </div>
                            </div>
                          </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          </div>
      <!-- </div> -->

      <hr style="width:100%; margin-bottom: 20px;">
<?php
    }
?>
      <!-- Seccion de Tabs -->
      <div class="tab">
        <button class="tablinks" onclick="openSubtab(event, 'Actividad')" id="defaultOpen">Record</button>
        <button class="tablinks" onclick="openSubtab(event, 'ensambles')">Quoted Part #</button>
        <button class="tablinks" onclick="openSubtab(event, 'Ventas')">Sales</button>
        <button class="tablinks" onclick="openSubtab(event, 'archivos')">Files</button>

        <!-- <button class="tablinks" onclick="openSubtab(event, 'capacidades')">Capacidades</button>
        <button class="tablinks" onclick="openSubtab(event, 'Resources')">Recursos</button>  -->
      </div>

      <div id="Ventas" class="tabcontent">
        <div class="inline-container">
            <h1>Sales</h1>

        <?php if (in_array(34, $_SESSION["permisos"]) && $isAwarded == 'YES') { ?>
          <a href='#' onclick='abrirNuevaVenta()'>
              <div class='icon-container' style='margin-left: 10px;'>
                  <div class='plus-icon'></div>
              </div>
          </a>
        <?php } ?>

        </div>
        <div class="inline-container" style="justify-content: center;">
            <label for="ventasTotales" style="width: 100px;">Total Sales</label>
            <input type="text" name="ventasTotales" id="ventasTotales" value="" style="width: 200px;" readonly>
        </div>
        <div class="flex-container">
            <table id='tableVentas' class="table">
                <thead>
                    <!-- Encabezados de tabla -->
                    <tr>
                        <th>Year</th>
                        <th>Month</th>
                        <th>Sales</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            <?php
                $stmt = $dbh->prepare("SELECT idVenta, anio, MONTHNAME(STR_TO_DATE(mes, '%m')) AS mesNombre, venta, notas
                                      FROM ventas
                                      WHERE idCotizacion = $id
                                      ORDER BY anio ASC, mes ASC");
                $stmt->execute();

                // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
                // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
                // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
                // $stmt->execute();
                while ($resultado = $stmt->fetch()) {
                    $VentasTotales += $resultado->venta;
                    echo "<tr id='" . $resultado->idVenta . "'>";
                    echo "<td>" . $resultado->anio . "</td>";
                    echo "<td>" . $resultado->mesNombre . "</td>";
                    echo "<td><span class='editSpan venta'> $ " . number_format($resultado->venta,2) . "</span>";
                    echo "<input class='editInput venta' type='number' name='venta' step='any' value='" . $resultado->venta . "' style='display: none;'></td>";
                    echo "<td><span class='editSpan notas'>" . $resultado->notas . "</span>";
                    echo "<input class='editInput notas' type='text' name='notas' value='" . $resultado->notas . "' style='display: none;'></td>";
                    echo "<td>";
                    if (in_array(34, $_SESSION["permisos"]) && $isAwarded == 'YES') {
                        echo "<div class='' style='display: flex; justify-content: space-evenly;'>
                                  <a class='editBtn' href='#' onclick='editMode(this)'>
                                      <div class='icon-container'>
                                          <div class='plus-icon-yellow'></div>
                                      </div>
                                  </a>
                                  <a class='guardarBtn' href='#' onclick='guardarVenta(this)' style='display: none;'>
                                      <div class='icon-container'>
                                          <div class='plus-icon-green'></div>
                                      </div>
                                  </a>
                                  <a class='deleteBtn' href='#' onclick='cancel(this)' style='display: none;'>
                                      <div class='icon-container'>
                                          <div class='cross-icon'></div>
                                      </div>
                                  </a>
                              </div>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            ?>
            </table>
        </div>
      </div>

<?php
      $stmtTipo = $dbh->prepare("SELECT idCotizacionArchivoTipo, tipo
                                      FROM cotizacion_archivo_tipo");
      $stmtTipo->execute();
?>

      <div id="archivos" class="tabcontent">
          <h1>Upload File</h1>
          <form onsubmit="return uploadFile()" method="post" enctype="multipart/form-data" name="formFile" id="formFile" >
              <div class="input-field">
                  <!-- Selector de Cliente -->
                  <label for="tipo">Tipo</label>
                  <div class="">
                      <div class="inline-container">
                          <select id="tipo" name="tipo" required>
                              <?php
                              while ($resultado = $stmtTipo->fetch()) {
                              ?>
                              <option value="<?php echo $resultado->idCotizacionArchivoTipo; ?>">
                              <?php
                              echo $resultado->tipo;
                              ?>
                              </option>
                              <?php
                              }
                              ?>
                          </select>

                          <div class="icon-container" style="display: flex; justify-content: center;">
                              <a href="cotizacion_tipo_archivo.php?id=<?php echo $id; ?>">
                                  <div class="plus-icon"></div>
                              </a>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="inline-container">
                  <label for="file_name">Filename:</label>
                  <input type="file" name="anyfile" id="anyfile" accept=".xls, .xlsx, .pdf, .csv, .msg, .rar">
              </div>
              <input type="submit" name="submit" value="Upload">
              <h6><strong>Note:</strong> Max doc size of 20 MB.</h6>
          </form>

          <br>
          <div class="flex-container">
              <table id='tableFiles'>
                  <thead>
                      <!-- Encabezados de tabla -->
                      <tr>
                          <th>Type</th>
                          <th>Name</th>
                          <th>Size</th>
                          <th>Uploaded by</th>
                          <th>Date</th>
                          <th>Action</th>
                      </tr>
                  </thead>
                  <tbody id='tableFilesBody'>
                      <?php
                          $stmtFiles = $dbh->prepare("SELECT ca.idCotizacionArchivo, ca.nombre AS aNombre, ca.tamano, cat.tipo, e.nombre AS eNombre, ca.fechaCrea
                                                      FROM cotizacion_archivo AS ca
                                                      INNER JOIN usuario AS u
                                                      ON ca.subidoPor = u.idUsuario
                                                      INNER JOIN empleado AS e
                                                      ON u.idEmpleado = e.idEmpleado
                                                      INNER JOIN cotizacion_archivo_tipo AS cat
                                                      ON ca.tipo = cat.idCotizacionArchivoTipo
                                                      WHERE idCotizacion = $id");
                          $stmtFiles->execute();

                          // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
                          // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
                          // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
                          // $stmt->execute();
                          while ($resultado = $stmtFiles->fetch()) {
                              echo "<tr id='" . $resultado->idCotizacionArchivo . "'>";
                              echo "<td>" . $resultado->tipo . "</td>";
                              echo "<td>" . $resultado->aNombre . "</td>";
                              echo "<td>" . $resultado->tamano . "</td>";
                              echo "<td>" . $resultado->eNombre . "</td>";
                              echo "<td>" . $resultado->fechaCrea . "</td>";
                              echo "<td>";

                              echo "<div class='' style='display: flex; justify-content: space-evenly; align-items: center;'>
                                        <a href='images/quotes/$id/$resultado->aNombre' download>
                                            <i class='fas fa-download fa-2x' style='color: DarkCyan;'></i>
                                        </a>";
                              if (in_array(31, $_SESSION["permisos"])) {
                                    echo  "<a class='deleteBtn' href='#' onclick='deleteFile(this)'>
                                                <div class='icon-container'>
                                                    <div class='cross-icon'></div>
                                                </div>
                                          </a>";
                              }
                              echo "</div>";

                              echo "</td>";
                              echo "</tr>";
                          }
                      ?>
                  </tbody>
              </table>
          </div>

      </div>

      <div id="Actividad" class="tabcontent">
          <div class="inline-container">
              <h1>Notes</h1>
              <a href='#' onclick='nuevaNota()'>
                  <div class='icon-container' style='margin-left: 10px;'>
                      <div class='plus-icon'></div>
                  </div>
              </a>
          </div>
          <div class="areaNotas" id="areaNotas">

<?php
              $stmt = $dbh->prepare("SELECT idCotizacionNota AS id, nota, empleado.nombre, DATE(cotizacion_notas.fechaCrea) AS fecha, usuario.idUsuario
                                                FROM cotizacion_notas
                                                INNER JOIN usuario
                                                ON cotizacion_notas.idUsuario = usuario.idUsuario
                                                INNER JOIN empleado
                                                ON usuario.idEmpleado = empleado.idEmpleado
                                                WHERE idCotizacion = $id
                                                ORDER BY cotizacion_notas.fechaCrea DESC");
              $stmt->execute();
              while ($resultado = $stmt->fetch()) {
                  echo "<div class='card' id='nota" . $resultado->id . "'>";
                      echo "<div class='inline-containter' style='width: 100%; display: inline-flex;'>";
                          echo "<div class='column' style='width: 70%;'>";
                              echo "<h6 id='notaText" . $resultado->id . "'>";
                                  echo $resultado->nota;
                              echo "</h6>";
                          echo "</div>";
                          echo "<div class='column' style='width: 20%;'>";
                              echo "<h6>by ";
                                  echo $resultado->nombre;
                              echo "</h6>";
                              echo "<h6>on ";
                                  echo $resultado->fecha;
                              echo "</h6>";
                          echo "</div>";

                          echo "<div class='column' style='width: 10%; padding: 0; display: inline-flex;'>";

                          date_default_timezone_set('America/Hermosillo'); // CDT
                          $current_date = date('Y-m-d');
                          if ($resultado->fecha == $current_date && $resultado->idUsuario == $_SESSION['idUsuario']) {
                              echo "<div class='inline-container' style='justify-content: space-evenly;'>";
                              echo "<div class='icon-container'>";
                              echo "<a href='#' onclick='editarNota(" . $resultado->id . "); return false;'>";
                              echo "<div class='plus-icon-yellow'></div>";
                              echo "</a>";
                              echo "</div>";
                              echo "<a href='#' onclick='deleteNota(" . $resultado->id . "); return false;'>";
                              echo "<div class='icon-container'>";
                              echo "<div class='cross-icon'></div>";
                              echo "</div>";
                              echo "</a>";
                              echo "</div>";
                          }
                          echo "</div>";
                          echo "</div>";
                          echo "</div>";
              }
?>

          </div>
      </div>

      <div id="ensambles" class="tabcontent">
        <!-- <h1 class="col-12 text-center danger">TESTING BY DEVELOPER, PLEASE DONT MOVE THIS SECTION!!!</h1> -->
        <div class="inline-container">
          <h1>Quoted Part Numbers</h1>
          <a href='#' onclick="addQuotePartNumber()">
              <div class='icon-container' style='margin-left: 10px;'>
                  <div class='plus-icon'></div>
              </div>
          </a>
        </div>

        <div class="col-12" style="margin-top: 20px;">
          <table id="myTable" class="table w-100">
            <thead>
              <!-- Encabezados de tabla -->
              <tr>
                  <!-- <th>ID</th> -->
                  <th>Part #</th>
                  <th>Description</th>
                  <th>EAU</th>
                  <th>Selling Price</th>
                  <th>Notes</th>
                  <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody">
            <?php
                      $stmt = $dbh->prepare("SELECT idCotizacionEnsamble, numParte, descripcion, eau, selling_price, notas
                                            FROM cotizacion_ensambles
                                            WHERE idCotizacion = $id
                                            ORDER BY fechaCrea DESC");
                      $stmt->execute();

                      // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
                      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
                      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
                      // $stmt->execute();
                      while ($resultado = $stmt->fetch()) {
                          echo "<tr id='" . $resultado->idCotizacionEnsamble . "'>";
                          // echo "<td>" . $resultado->idEnsamble . "</td>";
                          echo "<td><span class='editSpan numParte'>" . $resultado->numParte . "</span>";
                          echo "<input class='editInput numParte' type='text' name='numParte' value='" . $resultado->numParte . "' style='display: none;'></td>";
                          echo "<td><span class='editSpan descripcion'>" . $resultado->descripcion . "</span>";
                          echo "<input class='editInput descripcion' type='text' name='descripcion' value='" . $resultado->descripcion . "' style='display: none;'></td>";
                          echo "<td><span class='editSpan eau'>" . $resultado->eau . "</span>";
                          echo "<input class='editInput eau' type='number' name='eau' value='" . $resultado->eau . "' style='display: none;' step='1' pattern='/d+'></td>";
                          echo "<td><span class='editSpan selling_price'>" . $resultado->selling_price . "</span>";
                          echo "<input class='editInput selling_price' type='number' name='price' value='" . $resultado->selling_price . "' style='display: none;'></td>";
                          echo "<td><span class='editSpan notas'>" . $resultado->notas . "</span>";
                          echo "<input class='editInput notas' type='text' name='notas' value='" . $resultado->notas . "' style='display: none;'></td>";

                          echo "<td>
                                    <div class='' style='display: flex; justify-content: space-evenly;'>
                                        <a class='editBtn' href='#' onclick='editMode(this)'>
                                            <div class='icon-container'>
                                                <div class='plus-icon-yellow'></div>
                                            </div>
                                        </a>
                                        <a class='guardarBtn' href='#' onclick='editaQuotePartNumber(this)' style='display: none;'>
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
                                </td>";
                          echo "</tr>";
                      }
                  ?>
              </tbody>
          </table>
        </div>
      </div>

  <!-- VENTANAS MODALES -->
      <div class="back-modal">
          <div class="contenido-modal" style="height: 350px;">
          </div>
      </div>

      <span class="alerta ocultar">
          <span class="msg">This is a warning</span>
              <span class='icon-container'>
                  <div id="cerrar_alerta" class='cross-icon'></div>
              </span>
      </span>

      <script src="js/funciones.js"></script>

      <script type="text/javascript">
          $(document).ready(function() {
              $('#cerrar_alerta').click(function() {
                  $('.alerta').removeClass('mostrar');
                  $('.alerta').addClass('ocultar');
              });

              $('#ventasTotales').val(getTotalSales());

              if ($('#awarded').attr('awardedValue') == 1) {
                  $('#awarded').css("background-color","lightgreen");
              }

              <?php if (isset($_GET['back'])) { ?>
                        $('#backBtn').attr('href', '/<?php echo $_GET['back'] ?>.php');
              <?php } ?>

              // TABLE SE#CTION
              $('#myTable').DataTable({
                  responsive: true,
                  searching: false,
                  paging: false,
                  info: false,
                  // aaSorting: [0,'desc'],
              });

          });
      </script>

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
              trObj.find(".editInput.price").val(trObj.find(".editSpan.price").text());
              trObj.find(".editInput.notas").val(trObj.find(".editSpan.notas").text());
              // mostrarAlerta('warning','Cancelado.');
          }

          function addQuotePartNumber() {
              event.preventDefault();

              var idCotizacion = <?php echo $id; ?>;

              $.ajax({
                  type: 'POST',
                  url: 'js/ajax.php',
                  async: true,
                  data: {
                      accion: 'insertarCotizacionEnsambleNuevo',
                      idCotizacion: idCotizacion,
                  },
                  // data: 'accion=editarActUbicacion',
                  success: function(response) {
                      var info = JSON.parse(response);
                      console.log(info);
                      if (info.result) {

                          $('#tbody') // select table tbody
                          .prepend(addTableRow(info.result.idCotizacionEnsamble, info.result.numParte, info.result.descripcion,
                                                info.result.eau, info.result.selling_price, info.result.notas)) // prepend table row
                          //
                          // trObj.find(".editInput").hide();
                          // trObj.find(".guardarBtn").hide();
                          // trObj.find(".deleteBtn").hide();
                          // trObj.find(".editSpan").show();
                          // trObj.find(".editBtn").show();
                          var trObj = $("#" + info.result.idCotizacionEnsamble + "");
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

          function addTableRow(idCotizacionEnsamble, numParte, descripcion, eau, selling_price, notas) {
              var newRow =  "<tr id='" + idCotizacionEnsamble + "'>" +
                                "<td>" +
                                    "<span class='editSpan numParte'>" + numParte + "</span>" +
                                    "<input class='editInput numParte' type='text' name='numParte' value='" + numParte + "' style='display: none;'>" +
                                "</td>" +
                                "<td>" +
                                    "<span class='editSpan descripcion'>" + descripcion + "</span>" +
                                    "<input class='editInput descripcion' type='text' name='descripcion' value='" + descripcion + "' style='display: none;'>" +
                                "</td>" +
                                "<td>" +
                                    "<span class='editSpan eau'>" + eau + "</span>" +
                                    "<input class='editInput eau' type='number' name='eau' value='" + eau + "' style='display: none;' step='1' pattern='/d+'>" +
                                "</td>" +
                                "<td>" +
                                    "<span class='editSpan selling_price'>" + selling_price + "</span>" +
                                    "<input class='editInput selling_price' type='number' name='selling_price' value='" + selling_price + "' style='display: none;'>" +
                                "</td>" +
                                "<td>" +
                                    "<span class='editSpan notas'>" + notas + "</span>" +
                                    "<input class='editInput notas' type='text' name='notas' value='" + notas + "' style='display: none;'>" +
                                "</td>" +
                                "<td>" +
                                    "<div class='' style='display: flex; justify-content: space-evenly;'>" +
                                        "<a class='editBtn' href='#' onclick='editMode(this)'>" +
                                            "<div class='icon-container'>" +
                                                "<div class='plus-icon-yellow'></div>" +
                                            "</div>" +
                                        "</a>" +
                                        "<a class='guardarBtn' href='#' onclick='editaQuotePartNumber(this)' style='display: none;'>" +
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

          function editaQuotePartNumber(sender) {
              event.preventDefault();
              var trObj = $(sender).closest("tr");
              var idCotizacionEnsamble = trObj.attr('id');
              var numParte = trObj.find(".editInput.numParte").val();
              var descripcion = trObj.find(".editInput.descripcion").val();
              var eau = trObj.find(".editInput.eau").val();
              var selling_price = trObj.find(".editInput.selling_price").val();
              var notas = trObj.find(".editInput.notas").val();
              // alert(notas);
              $.ajax({
                  type: 'POST',
                  url: 'js/ajax.php',
                  async: true,
                  data: {
                      accion: 'editarCotizacionEnsamble',
                      idCotizacionEnsamble: idCotizacionEnsamble,
                      numParte: numParte,
                      descripcion: descripcion,
                      eau: eau,
                      selling_price: selling_price,
                      notas: notas,
                  },
                  // data: 'accion=editarActUbicacion',
                  success: function(response) {
                      var info = JSON.parse(response);
                      console.log(info);
                      if (info.result) {
                          trObj.find(".editSpan.numParte").text(info.result.numParte);
                          trObj.find(".editSpan.descripcion").text(info.result.descripcion);
                          trObj.find(".editSpan.eau").text(info.result.eau);
                          trObj.find(".editSpan.selling_price").text(info.result.selling_price);
                          trObj.find(".editSpan.notas").text(info.result.notas);

                          trObj.find(".editInput.numParte").text(info.result.numParte);
                          trObj.find(".editInput.descripcion").text(info.result.descripcion);
                          trObj.find(".editInput.eau").text(info.result.eau);
                          trObj.find(".editInput.selling_priceprice").text(info.result.selling_priceprice);
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

          function getTotalSales() {
              const options = { style: 'currency', currency: 'USD' };
              const numberFormat = new Intl.NumberFormat('en-US', options);

              <?php
                  $stmt = $dbh->prepare("SELECT IFNULL(SUM(venta),0) AS totales
                                          FROM ventas
                                          WHERE idCotizacion = $id");
                  $stmt->execute();

                  if ($stmt->rowCount() > 0) {
                      $value = $stmt->fetchColumn();
                  }else {
                      $value = 0;
                  }
              ?>
              var value = numberFormat.format(<?php echo $value;?>);
              return value;
          }

          function assignAwarded(sender) {
              event.preventDefault();
              var idCotizacion = <?php echo $id; ?>;

              $.ajax({
                  type:'POST',
                  url:'js/ajax.php',
                  async: true,
                  data: {
                      accion: 'validateForAwarded',
                      idCotizacion: idCotizacion
                  },
                  success:function(response) {
                      if (response == 'success') {
                          if (confirm('Change quote to Awarded?')) {
                              $.ajax({
                                  type:'POST',
                                  url:'js/ajax.php',
                                  async: true,
                                  data: {
                                      accion: 'awardedQuote',
                                      idCotizacion: idCotizacion
                                  },
                                  success:function(response) {
                                      if (response == 'success') {
                                          mostrarAlerta('success','Quote Awarded!.');
                                          $('#awarded').html("YES");
                                          $('#awarded').css("background-color","lightgreen");
                                          $(sender).remove();
                                      }else {
                                          console.log(response);
                                      }
                                  },
                                  error: function(error) {
                                      console.log(error);
                                  }
                              });
                          }
                      }else {
                          mostrarAlerta('warning','Release Date or Customer Requested date Pending!.');
                          console.log(response);
                      }
                  },
                  error: function(error) {
                      console.log(error);
                  }
              });
          }

          function cancelAwarded(sender) {
              event.preventDefault();
              var idCotizacion = <?php echo $id; ?>;

              if (confirm('Cancel Awarded?')) {
                  $.ajax({
                      type:'POST',
                      url:'js/ajax.php',
                      async: true,
                      data: {
                          accion: 'cancelAwardedQuote',
                          idCotizacion: idCotizacion
                      },
                      success:function(response) {
                          if (response == 'success') {
                              mostrarAlerta('success','Awarded canceled!.');
                              $('#awarded').html("NO");
                              $('#awarded').css("background-color","#f1f1f1");
                              $(sender).remove();
                          }
                      },
                      error: function(error) {
                          console.log(error);
                      }
                  });
              }
          }

          function uploadFile() {
              event.preventDefault();
              if( document.getElementById("anyfile").files.length == 0 ){
                  mostrarAlerta('warning','No file found');
                  return;
              }
              let idCotizacion = <?php echo $id; ?>;
              let form = $('#formFile');

              var formData = new FormData($(form)[0]);
              formData.append('idCotizacion', idCotizacion);

              $.ajax({
                  type:'POST',
                  url:'js/fileupload.php',
                  data: formData,
                  processData: false,
                  contentType: false,

                  success: function (response) {
                      if (isJson(response)) {
                          var info = JSON.parse(response);
                          switch (info.result) {
                              case "format":
                                  mostrarAlerta('danger','File type not supported');
                                  break;
                              case "duplicated":
                                  mostrarAlerta('warning','File already exists');
                                  break;
                              case "maxSize":
                                  mostrarAlerta('danger','File bigger than 8MB not allowed');
                                  break;
                              case "error":
                                  mostrarAlerta('danger','Failed to load file, please try again later');
                                  break;
                              default:

                                  var html = "";

                                  for(var i = 0; i < info.length; i++) {
                                      var obj = info[i];

                                      // console.log(obj.id);
                                      html += "<tr id='" + obj.idCotizacionArchivo + "'>";
                                      html += "<td>" + obj.tipo + "</td>";
                                      html += "<td>" + obj.aNombre + "</td>";
                                      html += "<td>" + obj.tamano + "</td>";
                                      html += "<td>" + obj.eNombre + "</td>";
                                      html += "<td>" + obj.fechaCrea + "</td>";
                                      html += "<td>";

                                          html += "<div class='' style='display: flex; justify-content: space-evenly; align-items: center;'>";
                                          html +=           "<a href='images/quotes/<?php echo $id; ?>/" + obj.aNombre + "' download>";
                                          html +=               "<i class='fas fa-download fa-2x' style='color: DarkCyan;'></i>";
                                          html +=           "</a>";
                          <?php       if (in_array(31, $_SESSION["permisos"])) {  ?>
                                          html +=           "<a class='deleteBtn' href='#' onclick='deleteFile(this)'>";
                                          html +=               "<div class='icon-container'>";
                                          html +=                   "<div class='cross-icon'></div>";
                                          html +=               "</div>";
                                          html +=          "</a>";
                          <?php      } ?>
                                          html +=      "</div>";

                                      html += "</td>";
                                      html += "</tr>";
                                  }
                                  $("#tableFilesBody").html(html);
                                  mostrarAlerta('success','File uploaded');
                          }
                          $("#anyfile").val(null);
                      }else {
                          mostrarAlerta('danger','File not compatible');
                          $("#anyfile").val(null);
                      }
                  }
              });
          }

          function nuevaNota() {
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
                                          "<input type='hidden' name='idCotizacionNota' id='idCotizacionNota' value=''>" +
                                          "<input type='hidden' name='idNota' id='idNota' value=''>" +
                                          "<input type='hidden' name='idUsuario' id='idUsuario' value=''>" +
                                          "<!-- Campo Nota -->" +
                                          "<div class='input-field'>" +
                                              "<label for='nota'>Note</label>" +
                                              "<textarea name='nota' rows='4' cols='50' id='nota' style='resize: none; width: 100%; height: 100px;' required></textarea>" +
                                          "</div>" +
                                          "<!-- Button Submit -->" +
                                          "<input type='submit' id='btnNota' value='Add'>" +
                                      "</form>" +
                                  "</div>");
              $('#tittle').html('New Note');
              $('#idCotizacionNota').val('<?php echo $id; ?>');
              $('#idNota').val('0');
              $('#idUsuario').val(<?php echo $_SESSION['idUsuario']; ?>);
              $('.contenido-modal').height('350px');
              abrirModal();
          }

          function abrirNuevaVenta() {
            var current = new Date().getFullYear();
            event.preventDefault();
            $('.contenido-modal').html("<div class='flex-container' style='margin-top: 60px;'>" +
                                      "<!-- Titulo -->" +
                                      "<h1 id='tittle'>Sales</h1>" +
                                      "<a class='btn-cerrar' onclick='cerrarModal2()'>" +
                                          "<div class='icon-container'>" +
                                              "<div class='cross-icon'></div>" +
                                          "</div>" +
                                      "</a>" +
                                      "<!-- Formulario -->" +
                                      "<form id='form_empleados' action='' onsubmit='return nuevaVenta(event)'>" +
                                          "<!-- ID -->" +
                                          "<input type='hidden' name='idCotizacion' id='idCotizacion'>" +
                                          "<input type='hidden' name='idVenta' id='idVenta' value=''>" +
                                          "<!-- Selector Anio -->" +
                                          "<div class='input-field'>" +
                                              "<!-- Lista Anio -->" +
                                              "<label for='anio'>Year</label>" +
                                              "<div class='inline-container'>" +
                                                  "<select name='anio' id='anio' required>" +
                                                      "<option value='" + (current-4) + "' >"+(current-4)+"</option>" +
                                                      "<option value='" + (current-3) + "' >"+(current-3)+"</option>" +
                                                      "<option value='" + (current-2) + "' >"+(current-2)+"</option>" +
                                                      "<option value='" + (current-1) + "' >"+(current-1)+"</option>" +
                                                      "<option value='" + current + "' selected>"+current+"</option>" +
                                                      "<option value='" + (current+1) + "' >"+(current+1)+"</option>" +
                                                      "<option value='" + (current+2) + "' >"+(current+2)+"</option>" +
                                                      "<option value='" + (current+3) + "' >"+(current+3)+"</option>" +
                                                      "<option value='" + (current+4) + "' >"+(current+4)+"</option>" +
                                                  "</select>" +
                                              "</div>" +
                                          "</div>" +
                                          "<div class='input-field'>" +
                                              "<!-- Lista Mes -->" +
                                              "<label for='mes'>Month</label>" +
                                              "<div class='inline-container'>" +
                                                  "<select name='mes' id='mes' required>" +
                                                      "<option value='1'>January</option>" +
                                                      "<option value='2'>February</option>" +
                                                      "<option value='3'>March</option>" +
                                                      "<option value='4'>April</option>" +
                                                      "<option value='5'>May</option>" +
                                                      "<option value='6'>June</option>" +
                                                      "<option value='7'>July</option>" +
                                                      "<option value='8'>August</option>" +
                                                      "<option value='9'>September</option>" +
                                                      "<option value='10'>October</option>" +
                                                      "<option value='11'>November</option>" +
                                                      "<option value='12'>December</option>" +
                                                  "</select>" +
                                              "</div>" +
                                          "</div>" +
                                          "<!-- Campo Venta -->" +
                                          "<div class='input-field'>" +
                                              "<label for='venta'>Sales</label>" +
                                              "<input type='number' name='venta' id='venta' min='1' step='any' required/>" +
                                          "</div>" +
                                          "<!-- Campo Nota -->" +
                                          "<div class='input-field'>" +
                                              "<label for='nota'>Note</label>" +
                                              "<textarea name='nota' rows='4' cols='50' id='nota' style='resize: none; width: 100%; height: 100px;'></textarea>" +
                                          "</div>" +
                                          "<!-- Button Submit -->" +
                                          "<input type='submit' id='btnVenta' value='Add'>" +
                                      "</form>" +
                                  "</div>");
              $('#tittle').html('New Sale');
              $('#idCotizacion').val('<?php echo $id; ?>');
              $('#idVenta').val('0');
              $('.contenido-modal').height('600px');
              abrirModal();
          }

          function abrirVentanaStatus() {
              $('#tittle').html('Status Change');

          var idStatus = $('#statusLabel').attr('idStatus');
          $.ajax({
              url: 'js/ajax.php',
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
                                                                      "<a href='status.php'>" +
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
                $('.contenido-modal').height('350px');
                abrirModal();
                return false;
          }

          function cambiarStatus() {
              var idCotizacion = <?php echo $id; ?>;
              var idStatus = $('#idStatus').val();
              $.ajax({
                  url: 'js/ajax.php',
                  type: 'POST',
                  async: true,
                  data: {
                    accion: 'cambiarStatusCotizacion',
                    idCotizacion: idCotizacion,
                    idStatus: idStatus
                  },
                  success: function(response) {
                      // console.log(response);
                      if (!response != "error") {
                          // console.log(response);
                          var info = JSON.parse(response);
                          // console.log(info);
                          $('#statusLabel').html(info.result.nombre);
                          $('#statusLabel').attr("idstatus",info.result.idStatus);
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
                          mostrarAlerta('success','Status changed.')
                      }
                  },
                  error: function(error) {
                      console.log(error);
                  }
              });
              cerrarModal2();
              return false;
          }

          function notaInfo() {
              $(".contenido-modal").html("<div class='flex-container' style='margin-top: 60px;'>" +
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
                                                  "<input type='hidden' name='idCotizacionNota' id='idCotizacionNota' value=''>" +
                                                  "<input type='hidden' name='idNota' id='idNota' value=''>" +
                                                  "<input type='hidden' name='idUsuario' id='idUsuario' value=''>" +
                                                  "<!-- Campo Nota -->" +
                                                  "<div class='input-field'>" +
                                                      "<label for='nota'>Note</label>" +
                                                      "<textarea name='nota' rows='4' cols='50' id='nota' style='resize: none; width: 100%; height: 100px;' required></textarea>" +
                                                  "</div>" +
                                                  "<!-- Button Submit -->" +
                                                  "<input type='submit' id='btnNota' value='Add'>" +
                                              "</form>" +
                                          "</div>");
          }

          function editarNota(id) {
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
                                              "<input type='hidden' name='idCotizacionNota' id='idCotizacionNota' value=''>" +
                                              "<input type='hidden' name='idNota' id='idNota' value=''>" +
                                              "<input type='hidden' name='idUsuario' id='idUsuario' value=''>" +
                                              "<!-- Campo Nota -->" +
                                              "<div class='input-field'>" +
                                                  "<label for='nota'>Note</label>" +
                                                  "<textarea name='nota' rows='4' cols='50' id='nota' style='resize: none; width: 100%; height: 100px;' required></textarea>" +
                                              "</div>" +
                                              "<!-- Button Submit -->" +
                                              "<input type='submit' id='btnNota' value='Change'>" +
                                          "</form>" +
                                      "</div>");
                $('#tittle').html('Note Edit');

                $.ajax({
                  url: 'js/ajax.php',
                  type: 'POST',
                  async: true,
                  data: {
                    accion: 'mostrarNotaCotizacion',
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
                      $('#idCotizacionNota').val('0');
                    }
                  },
                  error: function(error) {
                    console.log(error);
                  }
                });
                $('.contenido-modal').height('350px');
                abrirModal();
                return false;
          }

          function deleteNota(id) {
              $.ajax({
                  url: 'js/ajax.php',
                  type: 'POST',
                  async: true,
                  data: {
                      accion: 'eliminarNotaCotizacion',
                      idCotizacionNota: id,
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

          function nuevaVenta(){
            event.preventDefault();
            var idNota = $('#idNota').val();
            var idCotizacion = $('#idCotizacion').val();
            var anio = $('#anio').val();
            var mes = $('#mes').val();
            var venta = $('#venta').val();
            var nota = $('#nota').val();

            $.ajax({
              url: 'js/ajax.php',
              type: 'POST',
              async: true,
              data: {
                accion: 'nuevaVenta',
                idCotizacion: idCotizacion,
                anio: anio,
                mes: mes,
                venta: venta,
                nota: nota
              },
              success: function(response) {
                  console.log(response);
                  if (!response != "error") {
                      if (response == "duplicados") {
                          mostrarAlerta('warning','There is already a sale created for the selected date.');
                      } else {
                        // console.log(response);
                        // var info = JSON.parse(response);
                        var info = JSON.parse(response);
                        // console.log(info);
                        var id = info.result.idVenta;
                        var idCotizacion = info.result.idCotizacion;
                        var anio = info.result.anio;
                        var mes = info.result.mes;
                        var venta = info.result.venta;
                        var notas = info.result.notas;

                        $("#tableVentas>tbody").prepend("<tr id='"+venta+"'>" +
                                                            "<td>" +anio+ "</td>" +
                                                             "<td>" +mes+ "</td>" +
                                                             "<td><span class='editSpan venta'>"+venta+"</span>" +
                                                             "<input class='editInput venta' type='number' name='venta' step='any' value='" +venta+ "' style='display: none;'></td>" +
                                                             "<td><span class='editSpan notas'>" +notas+ "</span>" +
                                                             "<input class='editInput notas' type='text' name='notas' value='" +notas+"' style='display: none;'></td>" +
                                                             "<td>"+
                                                                      "<div class='' style='display: flex; justify-content: space-evenly;'>" +
                                                                          "<a class='editBtn' href='#' onclick='editMode(this)'>" +
                                                                              "<div class='icon-container'>" +
                                                                                  "<div class='plus-icon-yellow'></div>" +
                                                                              "</div>" +
                                                                          "</a>" +
                                                                          "<a class='guardarBtn' href='#' onclick='guardarVenta(this)' style='display: none;'>" +
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
                                                          "</tr>"
                        );
                        cerrarModal2();
                      }
                  }
              },
              error: function(error) {
                console.log(error);
              }
            });
          }

          function modificacionNota(sender) {
            event.preventDefault();
            var idNota = $('#idNota').val();
            var idCotizacion = $('#idCotizacionNota').val();
            var idUsuario = $('#idUsuario').val();
            var nota = $('#nota').val();

            if (idNota == 0) {
                $.ajax({
                  url: 'js/ajax.php',
                  type: 'POST',
                  async: true,
                  data: {
                    accion: 'nuevaNotaCotizacion',
                    idCotizacion: idCotizacion,
                    idUsuario: idUsuario,
                    nota: nota
                  },
                  success: function(response) {
                    // console.log(response);
                    if (!response != "error") {
                      // console.log(response);
                      // var info = JSON.parse(response);
                      var info = JSON.parse(response);
                      // console.log(info);
                      var id = info.result.idCotizacionNota;
                      var nota = info.result.nota;
                      var nombre = info.result.nombre;
                      var fecha = info.result.fechaCrea;

                      $('#areaNotas').prepend(" <div class='card' id='nota" + id + "'>" +
                                                    "<div class='inline-containter' style='width: 100%; display: inline-flex;'>" +
                                                        "<div class='column' style='width: 70%;'>" +
                                                            "<h6 id='notaText" + id + "'>" + nota + "</h6>" +
                                                        "</div>" +
                                                        "<div class='column' style='width: 20%;'>" +
                                                            "<h6>by " + nombre + "</h6>" +
                                                            "<h6>on " + fecha + "</h6>" +
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
                    url: 'js/ajax.php',
                    type: 'POST',
                    async: true,
                    data: {
                        accion: 'actualizarNotaCotizacion',
                        idCotizacionNota: idNota,
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

              trObj.find(".editInput.sales").val(trObj.find(".editSpan.sales").text());
              trObj.find(".editInput.notas").val(trObj.find(".editSpan.notas").text());
          }

          function guardarVenta(sender) {
              event.preventDefault();
              var trObj = $(sender).closest("tr");
              var idVenta = $(sender).closest("tr").attr('id');
              var venta = trObj.find(".editInput.venta").val();
              var notas = trObj.find(".editInput.notas").val();
              // alert(notas);
              $.ajax({
                  type:'POST',
                  url:'js/ajax.php',
                  async: true,
                  data: {
                    accion: 'editarVenta',
                    idVenta: idVenta,
                    venta: venta,
                    notas: notas
                  },
                  // data: 'accion=editarEnsamble',
                  success:function(response) {

                      var info = JSON.parse(response);
                      console.log(info);
                      if(info.result) {
                          trObj.find(".editSpan.venta").text(info.result.venta);
                          trObj.find(".editSpan.notas").text(info.result.notas);

                          trObj.find(".editInput.venta").text(info.result.venta);
                          trObj.find(".editInput.notas").text(info.result.notas);

                          trObj.find(".editInput").hide();
                          trObj.find(".guardarBtn").hide();
                          trObj.find(".deleteBtn").hide();
                          trObj.find(".editSpan").show();
                          trObj.find(".editBtn").show();
                          mostrarAlerta('success','Changes made.');
                      } else {
                          alert(response.result);
                      }
                  },
                  error: function(error) {
                      console.log(error);
                  }
              });
          }

          function deleteFile(sender) {
              event.preventDefault();

              if (confirm("Are you sure to delete file?")) {
                  var trObj = $(sender).closest("tr");
                  var idArchivo = trObj.attr('id');
                  var filename = trObj.find("td:eq(1)").text();
                  var idCotizacion = <?php echo $id; ?>;
                  // alert(filename);
                  // return;
                  $.ajax({
                      type:'POST',
                      url:'js/ajax.php',
                      async: true,
                      data: {
                        accion: 'eliminarFile',
                        id: idArchivo,
                        idCotizacion: idCotizacion,
                        filename: filename
                      },
                      // data: 'accion=editarEnsamble',
                      success:function(response) {
                          var info = JSON.parse(response);

                          if (info.result == "deleted") {
                              trObj.remove();
                              mostrarAlerta("success","File Deleted");
                          }else {
                              mostrarAlerta("danger","Cannot Delete File");
                          }
                      },
                      error: function(error) {
                          console.log(error);
                      }
                  });
              }
          }
      </script>

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

      <?php include "inc/footer.html"; ?>
