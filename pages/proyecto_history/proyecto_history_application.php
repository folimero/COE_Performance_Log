<?php
    include "../../inc/conexion.php";
    include "../../inc/headerBoostrap.php";
    $stmt = ''; // Variable para almacenar consulta SQL para mostrar proyectos

    function cleanInput($value)
    {
        $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
        return $value;
    }
    if (isset($_SESSION["usuarioNombre"])) {
        if (!in_array(7, $_SESSION["permisos"]) && !in_array(8, $_SESSION["permisos"])) {
            $message = "Unauthorized User.";
            echo "<script>
            alert('$message');
            window.location.href='../../index.php';
            </script>";
            die();
        } elseif (isset($_POST['btnBuscarProyecto'])) {
            // $buscar = cleanInput($_POST['buscar']);
            $rangoFecha = "";
            $msg = "";

            if (isset($_POST['fechaRango'])) {
                if (isset($_POST['from']) && isset($_POST['to'])) {
                    $from = cleanInput($_POST['from']);
                    $to = cleanInput($_POST['to']);

                    if ($_POST['from'] != null && $_POST['to'] != null) {
                        if ($_POST['from'] > $_POST['to']) {
                            $msg = "mostrarAlerta('warning','Invalid date range.');"; // ERROR
                        } else {
                            $rangoFecha = "WHERE fechaInicio BETWEEN '$from' AND '$to' ";
                        }
                    } elseif ($_POST['from'] != null) {
                        $rangoFecha = "WHERE fechaInicio >= '$from' "; // Falta TO
                    } elseif ($_POST['to'] != null) {
                        $rangoFecha = " AND fechaInicio <= '$to' "; // Falta FROM
                    } else {
                        $msg = "mostrarAlerta('warning','No date selected for range.');"; // ERROR
                    }
                } else {
                    $msg = "mostrarAlerta('warning','No date selected for range.');"; // ERROR
                }
            }

            if (isset($_POST['filtro'])) {
                switch ($_POST['filtro']) {
                    case 'id':
                        $filtro = 'projectID';
                        break;
                    case 'respVentas':
                        $filtro = '(SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = proyecto.idRepreVentas)';
                        break;
                    case 'cliente':
                        $filtro = 'cliente.nombreCliente';
                        break;
                    case 'nombre':
                        $filtro = 'proyecto.nombre';
                        break;
                    case 'type':
                        $filtro = 'proyecto_categoria.categoria';
                        break;
                    case 'status':
                        $filtro = 'status.nombre';
                        break;
                    case 'priority':
                        $filtro = "CASE
                                  WHEN prioridad = 1 THEN 'HIGH'
                                  WHEN prioridad = 2 THEN 'MEDIUM'
                                  ELSE 'LOW'
                                  END";
                        break;
                    default:
                        echo "NO SE ENCONTRO NINGUN FILTRO";
                        break;
                }

                $stmt = $dbh->prepare("SELECT idProyecto, projectID, cliente.nombreCliente, proyecto.nombre AS pnombre, proyecto.descripcion, DATE(proyecto.fechaCrea) AS fechaCrea,
                                      proyecto_categoria.categoria as tiponombre, complejidad.nombre AS cnombre, cobrarA, ventasPotenciales, isApplication,
                                      PO, qtoNumber, tracking, sobreCarga, date(fechaReqCliente) AS fechaReqCliente,
                                      date(fechaEmbarque) AS fechaEmbarque, proyecto.notas AS pnotas, status.nombre AS snombre, date(longestETA) AS longestETA, prioridad,
                                      5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaEmbarque) + 1, 1) AS turnAround,
                                      proyecto.awarded, (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = proyecto.idRepreVentas) AS salesRep, tipoproyecto.horas AS horas,
                                      (SELECT IFNULL(COUNT(ap.idActividades_proyecto),0) AS total
                                       FROM proyecto AS p
                                       LEFT JOIN actividades_proyecto AS ap
                                       ON p.idProyecto = ap.idProyecto
                                       WHERE p.idProyecto = proyecto.idProyecto AND (ap.completado = 0 OR ap.completado = 3)
                                      ) AS pendAct
                                      FROM proyecto
                                      INNER JOIN cliente
                                      ON proyecto.idCliente = cliente.idCliente
                                      INNER JOIN tipoproyecto
                                      ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                      INNER JOIN proyecto_categoria
                                      ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                      INNER JOIN complejidad
                                      ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                                      INNER JOIN status
                                      ON proyecto.idStatus = status.idStatus
                                      WHERE (proyecto.idStatus = 5 OR proyecto.idStatus = 7 OR proyecto.idStatus = 6) AND
                                      $filtro LIKE '%$buscar%' $rangoFecha AND proyecto.isApplication = 1
                                      ORDER BY proyecto.fechaCrea DESC");

                $stmtAVG = $dbh->prepare("SELECT AVG(5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaEmbarque) + 1, 1)) AS promedio
                                          FROM proyecto
                                          INNER JOIN status
                                          ON proyecto.idStatus = status.idStatus
                                          INNER JOIN proyecto_categoria
                                          ON proyecto.idTipoProyecto = proyecto_categoria.idProyectoCategoria
                                          INNER JOIN cliente
                                          ON proyecto.idCliente = cliente.idCliente
                                          WHERE (proyecto.idStatus = 5 OR proyecto.idStatus = 7 OR proyecto.idStatus = 6) AND
                                          $filtro LIKE '%$buscar%' $rangoFecha AND proyecto.isApplication = 1
                                          ORDER BY DATE(proyecto.fechaInicio) DESC");
            } else {
                if ($rangoFecha == "") {
                    $stmt = $dbh->prepare("SELECT idProyecto, projectID, cliente.nombreCliente, proyecto.nombre AS pnombre, proyecto.descripcion, isApplication,
                                              proyecto_categoria.categoria as tiponombre, complejidad.nombre AS cnombre, cobrarA, ventasPotenciales,
                                              PO, qtoNumber, tracking, sobreCarga, date(fechaReqCliente) AS fechaReqCliente, DATE(proyecto.fechaCrea) AS fechaCrea,
                                              date(fechaEmbarque) AS fechaEmbarque, proyecto.notas AS pnotas, status.nombre AS snombre, date(longestETA) AS longestETA, prioridad,
                                              5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaEmbarque) + 1, 1) AS turnAround,
                                              proyecto.awarded, (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = proyecto.idRepreVentas) AS salesRep, tipoproyecto.horas AS horas,
                                              (SELECT IFNULL(COUNT(ap.idActividades_proyecto),0) AS total
                                               FROM proyecto AS p
                                               LEFT JOIN actividades_proyecto AS ap
                                               ON p.idProyecto = ap.idProyecto
                                               WHERE p.idProyecto = proyecto.idProyecto AND (ap.completado = 0 OR ap.completado = 3)
                                              ) AS pendAct
                                          FROM proyecto
                                          INNER JOIN cliente
                                          ON proyecto.idCliente = cliente.idCliente
                                          INNER JOIN tipoproyecto
                                          ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                          INNER JOIN proyecto_categoria
                                          ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                          INNER JOIN complejidad
                                          ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                                          INNER JOIN status
                                          ON proyecto.idStatus = status.idStatus
                                          WHERE (proyecto.idStatus = 5 OR proyecto.idStatus = 7 OR proyecto.idStatus = 6) AND proyecto.isApplication = 1
                                          ORDER BY proyecto.fechaCrea DESC");
                    $stmtAVG = $dbh->prepare("SELECT AVG(5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaEmbarque) + 1, 1)) AS promedio
                                              FROM proyecto
                                              INNER JOIN status
                                              ON proyecto.idStatus = status.idStatus
                                              INNER JOIN cliente
                                              ON proyecto.idCliente = cliente.idCliente
                                              WHERE (proyecto.idStatus = 5 OR proyecto.idStatus = 7 OR proyecto.idStatus = 6) AND proyecto.isApplication = 1");
                } else {
                    $stmt = $dbh->prepare("SELECT idProyecto, projectID, cliente.nombreCliente, proyecto.nombre AS pnombre, proyecto.descripcion, isApplication,
                                              proyecto_categoria.categoria as tiponombre, complejidad.nombre AS cnombre, cobrarA, ventasPotenciales,
                                              PO, qtoNumber, tracking, sobreCarga, date(fechaReqCliente) AS fechaReqCliente, DATE(proyecto.fechaCrea) AS fechaCrea,
                                              date(fechaEmbarque) AS fechaEmbarque, proyecto.notas AS pnotas, status.nombre AS snombre, date(longestETA) AS longestETA, prioridad,
                                              5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaEmbarque) + 1, 1) AS turnAround,
                                              proyecto.awarded, (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = proyecto.idRepreVentas) AS salesRep, tipoproyecto.horas AS horas,
                                              (SELECT IFNULL(COUNT(ap.idActividades_proyecto),0) AS total
                                               FROM proyecto AS p
                                               LEFT JOIN actividades_proyecto AS ap
                                               ON p.idProyecto = ap.idProyecto
                                               WHERE p.idProyecto = proyecto.idProyecto AND (ap.completado = 0 OR ap.completado = 3)
                                              ) AS pendAct
                                          FROM proyecto
                                          INNER JOIN cliente
                                          ON proyecto.idCliente = cliente.idCliente
                                          INNER JOIN tipoproyecto
                                          ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                          INNER JOIN proyecto_categoria
                                          ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                          INNER JOIN complejidad
                                          ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                                          INNER JOIN status
                                          ON proyecto.idStatus = status.idStatus
                                          $rangoFecha AND (proyecto.idStatus = 5 OR proyecto.idStatus = 7 OR proyecto.idStatus = 6) AND proyecto.isApplication = 1
                                          ORDER BY proyecto.fechaCrea DESC");
                    $stmtAVG = $dbh->prepare("SELECT AVG(5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaEmbarque) + 1, 1)) AS promedio
                                              FROM proyecto
                                              INNER JOIN status
                                              ON proyecto.idStatus = status.idStatus
                                              INNER JOIN cliente
                                              ON proyecto.idCliente = cliente.idCliente
                                              $rangoFecha AND (proyecto.idStatus = 5 OR proyecto.idStatus = 7 OR proyecto.idStatus = 6) AND proyecto.isApplication = 1");
                }
            }
        } else {
            $stmt = $dbh->prepare("SELECT idProyecto, projectID, cliente.nombreCliente, proyecto.nombre AS pnombre, proyecto.descripcion, isApplication,
                                  proyecto_categoria.categoria as tiponombre, complejidad.nombre AS cnombre, cobrarA, ventasPotenciales,
                                  PO, qtoNumber, tracking, sobreCarga, date(fechaReqCliente) AS fechaReqCliente, DATE(proyecto.fechaCrea) AS fechaCrea,
                                  date(fechaEmbarque) AS fechaEmbarque, proyecto.notas AS pnotas, status.nombre AS snombre, date(longestETA) AS longestETA, prioridad,
                                  5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaEmbarque) + 1, 1) AS turnAround,
                                  proyecto.awarded, (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = proyecto.idRepreVentas) AS salesRep, tipoproyecto.horas AS horas,
                                  (SELECT IFNULL(COUNT(ap.idActividades_proyecto),0) AS total
                                   FROM proyecto AS p
                                   LEFT JOIN actividades_proyecto AS ap
                                   ON p.idProyecto = ap.idProyecto
                                   WHERE p.idProyecto = proyecto.idProyecto AND (ap.completado = 0 OR ap.completado = 3)
                                  ) AS pendAct
                                  FROM proyecto
                                  INNER JOIN cliente
                                  ON proyecto.idCliente = cliente.idCliente
                                  INNER JOIN tipoproyecto
                                  ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                  INNER JOIN proyecto_categoria
                                  ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                  INNER JOIN complejidad
                                  ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                                  INNER JOIN status
                                  ON proyecto.idStatus = status.idStatus
                                  WHERE (proyecto.idStatus = 5 OR proyecto.idStatus = 7 OR proyecto.idStatus = 6) AND proyecto.isApplication = 1
                                  ORDER BY proyecto.fechaCrea DESC");
            $stmtAVG = $dbh->prepare("SELECT AVG(5 * (DATEDIFF(proyecto.fechaEmbarque, proyecto.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(proyecto.fechaInicio) + WEEKDAY(proyecto.fechaEmbarque) + 1, 1)) AS promedio
                                      FROM proyecto
                                      INNER JOIN status
                                      ON proyecto.idStatus = status.idStatus
                                      WHERE (proyecto.idStatus = 5 OR proyecto.idStatus = 7) AND proyecto.isApplication = 1");
        }
        $stmtAVG->execute();
        $resultadoAVG = $stmtAVG->fetch();
        // print_r( $resultadoAVG);
        // exit;
    } else {
        $message = "Please Log in.";
        echo "<script>
              alert('$message');
              window.location.href='../../login.php';
              </script>";
        die();
    }
?>

<!DOCTYPE html>
    <!-- <h1 class="col-12 text-center danger mt-4">TESTING BY DEVELOPER, PLEASE DONT MOVE THIS SECTION!!!</h1> -->

    <div class="card mt-3 mb-3">
        <h2 class="card-header text-center bg-success text-white">Application Project History</h2>
        <div class="card-body">
            <div class="row">
              <form class="" action="proyecto_history_application.php" style="width: 100%;" method="post">
                <div class="row mb-4">
                  <div class="col-6">
                      <div class="row g-3 align-items-center">
                          <div class="col-auto">
                              <label class="" for="avg">Average Turn Around:</label>
                          </div>
                          <div class="col-auto">
                              <input class="" type="text" id="avg" name="country" value="<?php echo round($resultadoAVG->promedio, 1); ?>" readonly>
                          </div>
                          <div class="col-4"></div>
                      </div>
                      <!-- <div class="d-flex align-items-end flex-column">
                          <button type="button" class="btn btn-success" onclick="excelExport()">Excel</button>
                      </div> -->
                  </div>
                  <div class="col-6">
                      <!-- <div class="row">
                          <div class="col-5">
                              <select class="input-field" name="filtro" id="filtro">
                                  <option disabled selected value>  N/A  </option>
                                  <option value="id">PROJECT ID</option>
                                  <option value="cliente">CUSTOMER</option>
                                  <option value="nombre">NAME</option>
                                  <option value="type">TYPE</option>
                                  <option value="status">STATUS</option>
                                  <option value="respVentas">SALES REPRESENTATIVE</option>
                                  <option value="priority">PRIORITY</option>
                              </select>
                          </div>
                          <div class="col-5">
                              <input name="buscar" type="text" id="buscar" placeholder="Type text to search">
                          </div>
                          <div class="col-2">
                              <input name="btnBuscarProyecto" class="btn-buscar" style="width: auto; margin-top: 0;" type="submit" value="Find">
                          </div>
                      </div> -->

                      <div class="row g-3 align-items-center">
                          <div class="col-2">
                              <label for="fechaRango">Date Range</label>
                              <input type="checkbox" name="fechaRango" id="fechaRango" value="0" onclick='handleClick(this);'>
                          </div>
                          <div class="col-4">
                              <input type="date" id="from" name="from" value="" min="2010-01-01" disabled>
                          </div>
                          <div class="col-4">
                              <input type="date" id="to" name="to" value="" min="2010-01-01" disabled>
                          </div>
                          <div class="col-2">
                              <input name="btnBuscarProyecto" class="btn-buscar" style="width: auto; margin-top: 0;" type="submit" value="Find">
                          </div>
                      </div>

                  </div>
                </div>
              </form>
            </div>

            <div class="col-12">
              <table id="projectHistoryTable" class="table">
                  <thead>
                      <!-- Encabezados de tabla -->
                      <tr>
                          <th>Detail</th>
                          <th>ID</th>
                          <th>Customer</th>
                          <th>Name</th>
                          <th>QO Number</th>
                          <th>Complexity</th>
                          <th>Due Date</th>
                          <th>Hours</th>
                          <th>Notes</th>
                          <th>Status</th>
                      </tr>
                      <tr>
                          <th>Detail</th>
                          <th>ID</th>
                          <th>Customer</th>
                          <th>Name</th>
                          <th>QO Number</th>
                          <th>Complexity</th>
                          <th>Due Date</th>
                          <th>Hours</th>
                          <th>Notes</th>
                          <th>Status</th>
                      </tr>
                  </thead>

                  <tfoot>
                      <tr>
                        <th>Detail</th>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Name</th>
                        <th>QO Number</th>
                        <th>Complexity</th>
                        <th>Due Date</th>
                        <th>Hours</th>
                        <th>Notes</th>
                        <th>Status</th>
                      </tr>
                  </tfoot>

                  <tbody>
                      <?php
                      $stmt->execute();
                      while ($resultado = $stmt->fetch()) {
                          echo "<tr>";
                          if ($resultado->isApplication == 0) {
                              echo "<td>
                                      <a href='../proyecto_detalle/proyecto_detalle_coe.php?id=" . $resultado->idProyecto . "&back=pages/proyecto_history/proyecto_history_coe'>
                                      <div class='icon-container'>
                                          <div class='plus-icon'></div>
                                      </div>
                                      </a>
                                    </td>";
                          } else {
                            echo "<td>
                                    <a href='../proyecto_detalle/proyecto_detalle_application.php?id=" . $resultado->idProyecto . "&back=pages/proyecto_history/proyecto_history_application'>
                                    <div class='icon-container'>
                                        <div class='plus-icon'></div>
                                    </div>
                                    </a>
                                  </td>";
                          }
                          echo "<td>". $resultado->projectID . "</td>";
                          echo "<td>". $resultado->nombreCliente . "</td>";
                          echo "<td>". $resultado->descripcion;
                              if ($resultado->pendAct > 0) {
                                  // echo '<span class="badge bg-danger">'.$resultado->pendAct.'</span>';
                              }
                          echo "</td>";
                          echo "<td>". $resultado->qtoNumber . "</td>";
                          echo "<td>". $resultado->cnombre . "</td>";
                          echo "<td>". $resultado->fechaReqCliente . "</td>";
                          echo "<td>". $resultado->horas . "</td>";
                          echo "<td>". $resultado->pnotas . "</td>";
                          switch ($resultado->snombre) {
                            case 'YELLOW STATUS':
                                echo "<td><p class='yellow_status'>". $resultado->snombre . "</p></td>";
                                break;
                            case 'GREEN STATUS':
                                echo "<td><p class='green_status'>". $resultado->snombre . "</p></td>";
                                break;
                            case 'RED STATUS':
                                echo "<td><p class='red_status'>". $resultado->snombre . "</p></td>";
                                break;
                            default:
                                echo "<td>". $resultado->snombre . "</td>";
                                break;
                          }
                          echo "</tr>";
                      }
                      ?>
                  </tbody>
              </table>
            </div>
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

    <script src="../../js/funciones.js"></script>

    <script language="javascript" type="text/javascript">
        $(document).ready(function() {
            <?php if (isset($_POST['btnBuscarProyecto']) && $msg != "") {
                echo "$msg";
            } ?>

            $('#buscar').change(function() {
                $(this).val($(this).val().trim());
            });

            // DataTable
            var table = $('#projectHistoryTable').DataTable({
              // responsive: true,
              orderCellsTop: true,
              fixedHeader: true,
              pageLength: 20,
              // scrollX: true,
              dom: 'Bfrtip',
              buttons: [
                  // 'copyHtml5',
                  'excelHtml5',
              ],
              columnDefs: [
                  // {
                  //     target: 4,
                  //     visible: false,
                  //     // searchable: false,
                  // },
                  // {
                  //     target: 8,
                  //     visible: false,
                  // },
                  // {
                  //     target: 10,
                  //     visible: false,
                  // },
              ],
              // drawCallback: () => $('#avg').val(updateAverage())
            });

            // Setup - add a text input to each footer cell
            $('#projectHistoryTable thead tr:eq(1) th').each( function () {
                var title = $(this).text();
                $(this).html( '<input type="text" placeholder="Search '+title+'" class="column_search" />' );
            } );

            // Apply the search
            $( '#projectHistoryTable thead'  ).on( 'keyup', ".column_search",function () {
                var customIndex = table.column($(this).parent().index() +':visible');
                // console.log(customIndex);
                table
                    .column( customIndex )
                    .search( this.value )
                    .draw();
            } );
        });

        //average age calculation
        function updateAverage() {
            let columnData = $('#projectHistoryTable').DataTable().column(12,{search:'applied'}).data().toArray();
            var suma = columnData.reduce((a, item) => (a + Number(item)), 0);
            let avg = suma/columnData.length;
            return avg.toFixed(1);
        };

        function handleClick(cb) {
            // display("Clicked, new value = " + cb.checked);
            if (cb.checked == true ) {
                jQuery('#from').prop('disabled', false);
                jQuery('#to').prop('disabled', false);
            }
            else {
                jQuery('#from').prop('disabled', true);
                jQuery('#to').prop('disabled', true);
            }
        }

        function createExportHeader(dataSource, separator) {
            var headerRow = "",
                columns = dataSource.columns,
                newLine = "\r\n";

            for (var i=0; i < columns.length; i++) {
                headerRow += (i > 0 ? separator : '') + columns[i].displayName;
            }
            return headerRow + newLine;
        }

        function createExportRows(dataSource, separator) {
            var content = "",
                columns = dataSource.columns,
                data = dataSource.data,
                newLine = "\r\n",
                dataField;

            for(var j=0; j < data.length; j++) {
                for (var i=0; i < columns.length; i++) {
                    dataField = columns[i].dataField;
                    content += (i > 0 ? separator : '') + data[j][dataField];
                }
                content += newLine;
            }
            return content;
        }

        function excelExport() {
            var separator = ',',
                dataSource = {
                    data: [
                        {
                            name: "Frank Über",
                            age: 49
                        },
                        {
                            name: "Toni Köhl",
                            age: 56
                        }
                    ],
                    columns: [
                        {
                            dataField: "name",
                            displayName: "Name"
                        },
                        {
                            dataField: "age",
                            displayName: "Alter"
                        }
                    ]
                };
            var content = createExportHeader(dataSource, separator);
            content += createExportRows(dataSource, separator);

            //an anchor html element on the page (or create dynamically one)
            //to use its download attribute to set filename
            var a = document.getElementById('csv');
            a.textContent='download';
            a.download="MyFile.csv";
            a.href='data:text/csv;charset=utf-8,%EF%BB%BF'+encodeURIComponent(content);
            a.click();
        }

        function exportData(){
            /* Get the HTML data using Element by Id */
            var table = document.getElementById("projectHistoryTable");

            /* Declaring array variable */
            var rows =[];

            //iterate through rows of table
            for(var i=0,row; row = table.rows[i];i++){
                //rows would be accessed using the "row" variable assigned in the for loop
                //Get each cell value/column from the row
                // column1 = row.cells[0].innerText;
                column2 = row.cells[1].innerText;
                column3 = row.cells[2].innerText;
                column4 = row.cells[3].innerText;
                column5 = row.cells[4].innerText;
                column6 = row.cells[6].innerText;
                column7 = row.cells[7].innerText;
                column8 = row.cells[8].innerText;
                column9 = row.cells[9].innerText;
                column10 = row.cells[10].innerText;
                column11 = row.cells[11].innerText;
                column12 = row.cells[12].innerText;
                column13 = row.cells[13].innerText;
                column14 = row.cells[14].innerText;

                /* add a new records in the array */
                rows.push(
                    [
                        // column1,
                        column2.replace('#','\#'),
                        column3.replace('#','\#'),
                        column4.replace('#','\#'),
                        column5.replace('#','\#'),
                        column6.replace('#','\#'),
                        column7.replace('#','\#'),
                        column8.replace('#','\#'),
                        column9.replace('#','\#'),
                        column10.replace('#','\#'),
                        column11.replace('#','\#'),
                        column12.replace('#','\#'),
                        column13.replace('#','\#'),
                        column14.replace('#','\#')
                    ]
                );

            }
            // alert(rows.length);
            csvContent = "data:text/csv;charset=utf-8,%EF%BB%BF'";
            /* add the column delimiter as comma(,) and each row splitted by new line character (\n) */
            rows.forEach(function(rowArray){
                row = rowArray.join(",");
                csvContent += row + "\r\n";
            });

            /* create a hidden <a> DOM node and set its download attribute */
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "project_history.csv");
            document.body.appendChild(link);
            /* download the data file named "Stock_Price_Report.csv" */
            link.click();
        }
    </script>

    <?php include "../../inc/footer.html"; ?>
