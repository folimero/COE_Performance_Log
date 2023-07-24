<?php
    include "inc/conexion.php";
    include "inc/headerBoostrap.php";
    $stmt = ''; // Variable para almacenar consulta SQL para mostrar proyectos
    // Funcion para limpiar campos
    function cleanInput($value)
    {
        $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
        return $value;
    }
    if (isset($_SESSION["usuarioNombre"])) {
        if (!in_array(31, $_SESSION["permisos"]) && !in_array(32, $_SESSION["permisos"])) {
            $message = "Unauthorized User.";
            echo "<script>
                  alert('$message');
                  window.location.href='index.php';
                  </script>";
            die();
        } elseif (isset($_POST['btnBuscarCotizacion'])) {
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
                            $rangoFecha = "WHERE fechaLanzamiento BETWEEN '$from' AND '$to' ";
                        }
                    } elseif ($_POST['from'] != null) {
                        $rangoFecha = "WHERE fechaLanzamiento >= '$from' "; // Falta TO
                    } elseif ($_POST['to'] != null) {
                        $rangoFecha = " AND fechaLanzamiento <= '$to' "; // Falta FROM
                    } else {
                        $msg = "mostrarAlerta('warning','No date selected for range.');"; // ERROR
                    }
                }else {
                    $msg = "mostrarAlerta('warning','No date selected for range.');"; // ERROR
                }
            }

            if (isset($_POST['filtro'])) {
                switch ($_POST['filtro']) {
                    case 'id':
                        $filtro = 'quoteID';
                        break;
                    case 'cliente':
                        $filtro = 'cliente.nombreCliente';
                        break;
                    case 'nombre':
                        $filtro = 'cotizacion.nombre';
                        break;
                    case 'contacto':
                        $filtro = 'cliente_contacto.nombre';
                        break;
                    case 'responsable':
                        $filtro = '(SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idResponsable)';
                        break;
                    case 'respVentas':
                        $filtro = '(SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idRepreVentas)';
                        break;
                    case 'status':
                        $filtro = 'status.nombre';
                        break;
                        // case 'start':
                        //     $filtro = 'fechaInicio';
                        //     break;
                        // case 'release':
                        //     $filtro = 'fechaLanzamiento';
                        //     break;
                    case 'OTC':
                        $filtro = "IF(consOTC=1, 'YES', 'NO') ";
                        break;
                    case 'awarded':
                        $filtro = "CASE
                                  WHEN awarded = 1 THEN 'YES'
                                  WHEN awarded = -1 THEN 'NO'
                                  ELSE 'TBD'
                                  END";
                        break;
                    default:
                        echo "NO SE ENCONTRO NINGUN FILTRO";
                        break;
                }
                $stmt = $dbh->prepare("SELECT idCotizacion, quoteID, cliente.nombreCliente AS clinombre, cotizacion.nombre, cliente_contacto.nombre AS contnombre,
                                            uniqueFG, status.nombre AS stat, DATE(cotizacion.fechaInicio) AS startDate, DATE(cotizacion.fechaLanzamiento) AS releaseDate,
                                            5 * (DATEDIFF(cotizacion.fechaLanzamiento, cotizacion.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(cotizacion.fechaInicio) + WEEKDAY(cotizacion.fechaLanzamiento) + 1, 1) AS turnAround, cotizacion.awarded, consOTC,
                                            (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idResponsable) AS respCoti,
                                            (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idRepreVentas) AS repreVentas
                                      FROM cotizacion
                                      INNER JOIN cliente
                                      ON cotizacion.idCliente = cliente.idCliente
                                      LEFT JOIN cliente_contacto
                                      ON cotizacion.idClienteContacto = cliente_contacto.idClienteContacto
                                      INNER JOIN status
                                      ON cotizacion.idStatus = status.idStatus
                                      WHERE (cotizacion.idStatus = 5 OR cotizacion.idStatus = 7 OR cotizacion.idStatus = 6) AND
                                            $filtro LIKE '%$buscar%' $rangoFecha
                                      ORDER BY DATE(cotizacion.fechaLanzamiento) DESC");
                $stmtAVG = $dbh->prepare("SELECT AVG(5 * (DATEDIFF(cotizacion.fechaLanzamiento, cotizacion.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(cotizacion.fechaInicio) + WEEKDAY(cotizacion.fechaLanzamiento) + 1, 1)) AS promedio
                                          FROM cotizacion
                                          INNER JOIN cliente
                                          ON cotizacion.idCliente = cliente.idCliente
                                          LEFT JOIN cliente_contacto
                                          ON cotizacion.idClienteContacto = cliente_contacto.idClienteContacto
                                          INNER JOIN status
                                          ON cotizacion.idStatus = status.idStatus
                                          $rangoFecha
                                          ORDER BY DATE(cotizacion.fechaLanzamiento) DESC");
            } else {
                $stmt = $dbh->prepare("SELECT idCotizacion, quoteID, cliente.nombreCliente AS clinombre, cotizacion.nombre, cliente_contacto.nombre AS contnombre,
                                              uniqueFG, status.nombre AS stat, DATE(cotizacion.fechaInicio) AS startDate, DATE(cotizacion.fechaLanzamiento) AS releaseDate,
                                              5 * (DATEDIFF(cotizacion.fechaLanzamiento, cotizacion.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(cotizacion.fechaInicio) + WEEKDAY(cotizacion.fechaLanzamiento) + 1, 1) AS turnAround, cotizacion.awarded, consOTC,
                                              (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idResponsable) AS respCoti,
                                              (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idRepreVentas) AS repreVentas
                                        FROM cotizacion
                                        INNER JOIN cliente
                                        ON cotizacion.idCliente = cliente.idCliente
                                        LEFT JOIN cliente_contacto
                                        ON cotizacion.idClienteContacto = cliente_contacto.idClienteContacto
                                        INNER JOIN status
                                        ON cotizacion.idStatus = status.idStatus
                                        $rangoFecha
                                        ORDER BY DATE(cotizacion.fechaLanzamiento) DESC");
                $stmtAVG = $dbh->prepare("SELECT AVG(5 * (DATEDIFF(cotizacion.fechaLanzamiento, cotizacion.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(cotizacion.fechaInicio) + WEEKDAY(cotizacion.fechaLanzamiento) + 1, 1)) AS promedio
                                          FROM cotizacion
                                          INNER JOIN cliente
                                          ON cotizacion.idCliente = cliente.idCliente
                                          LEFT JOIN cliente_contacto
                                          ON cotizacion.idClienteContacto = cliente_contacto.idClienteContacto
                                          INNER JOIN status
                                          ON cotizacion.idStatus = status.idStatus
                                          $rangoFecha");
            }
        } else {
            $stmt = $dbh->prepare("SELECT idCotizacion, quoteID, cliente.nombreCliente AS clinombre, cotizacion.nombre, cliente_contacto.nombre AS contnombre,
                                          uniqueFG, status.nombre AS stat, DATE(cotizacion.fechaInicio) AS startDate, DATE(cotizacion.fechaLanzamiento) AS releaseDate,
                                          5 * (DATEDIFF(cotizacion.fechaLanzamiento, cotizacion.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(cotizacion.fechaInicio) + WEEKDAY(cotizacion.fechaLanzamiento) + 1, 1) AS turnAround, cotizacion.awarded, consOTC,
                                          (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idResponsable) AS respCoti,
                                          (SELECT empleado.nombre FROM empleado WHERE empleado.idEmpleado = cotizacion.idRepreVentas) AS repreVentas
                                    FROM cotizacion
                                    INNER JOIN cliente
                                    ON cotizacion.idCliente = cliente.idCliente
                                    LEFT JOIN cliente_contacto
                                    ON cotizacion.idClienteContacto = cliente_contacto.idClienteContacto
                                    INNER JOIN status
                                    ON cotizacion.idStatus = status.idStatus
                                    WHERE (cotizacion.idStatus = 5 OR cotizacion.idStatus = 7 OR cotizacion.idStatus = 6)
                                    ORDER BY DATE(cotizacion.fechaLanzamiento) DESC");

            $stmtAVG = $dbh->prepare("SELECT AVG(5 * (DATEDIFF(cotizacion.fechaLanzamiento, cotizacion.fechaInicio) DIV 7) + MID('0123444401233334012222340111123400012345001234550', 7 * WEEKDAY(cotizacion.fechaInicio) + WEEKDAY(cotizacion.fechaLanzamiento) + 1, 1)) AS promedio
                                      FROM cotizacion
                                      INNER JOIN cliente
                                      ON cotizacion.idCliente = cliente.idCliente
                                      LEFT JOIN cliente_contacto
                                      ON cotizacion.idClienteContacto = cliente_contacto.idClienteContacto
                                      INNER JOIN status
                                      ON cotizacion.idStatus = status.idStatus
                                      WHERE (cotizacion.idStatus = 5 OR cotizacion.idStatus = 7 OR cotizacion.idStatus = 6) AND cotizacion.consOTC = 1");
        }
        $stmtAVG->execute();
        $resultadoAVG = $stmtAVG->fetch();
    } else {
        $message = "Please Log in.";
        echo "<script>
                alert('$message');
                window.location.href='login.php';
              </script>";
        die();
    }
?>

<!DOCTYPE html>

    <!-- <h1 class="col-12 text-center danger mt-4">TESTING BY DEVELOPER, PLEASE DONT MOVE THIS SECTION!!!</h1> -->

    <div class="card mt-3 mb-3">
        <h2 class="card-header text-center ">Quoting History</h2>
        <div class="card-body">
            <div class="row">
                <form class="" action="cotizacion.php" style="width: 100%;" method="post">
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
                        </div>

                        <div class="col-6">
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
                                    <input name="btnBuscarCotizacion" class="btn-buscar" style="width: auto; margin-top: 0;" type="submit" value="Find">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-12">
                <table id="quoteHistoryTable" class="table">
                    <thead>
                        <!-- Encabezados de tabla -->
                        <tr>
                            <th>Detail</th>
                            <th>Project ID</th>
                            <th>Customer</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Responsible</th>
                            <th>Sales Rep</th>
                            <th>Unique FG</th>
                            <th>Status</th>
                            <!-- <th>Start Date</th> -->
                            <th>Release Date</th>
                            <th>Turnaround</th>
                            <th>Cons OTC?</th>
                            <th>Awarded</th>
                        </tr>
                        <tr>
                            <th>Detail</th>
                            <th>Project ID</th>
                            <th>Customer</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Responsible</th>
                            <th>Sales Rep</th>
                            <th>Unique FG</th>
                            <th>Status</th>
                            <!-- <th>Start Date</th> -->
                            <th>Release Date</th>
                            <th>Turnaround</th>
                            <th>Cons OTC?</th>
                            <th>Awarded</th>
                        </tr>
                    </thead>

                    <tfoot>
                        <tr>
                          <th>Detail</th>
                          <th>Project ID</th>
                          <th>Customer</th>
                          <th>Name</th>
                          <th>Contact</th>
                          <th>Responsible</th>
                          <th>Sales Rep</th>
                          <th>Unique FG</th>
                          <th>Status</th>
                          <th>Release Date</th>
                          <th>Turnaround</th>
                          <th>Cons OTC?</th>
                          <th>Awarded</th>
                        </tr>
                    </tfoot>

                    <tbody>
                        <?php
                        $stmt->execute();
                        // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
                        // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
                        // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
                        // $stmt->execute();
                        while ($resultado = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>
                                      <div class='icon-container'>
                                          <a href='cotizacion_detalle.php?id=" . $resultado->idCotizacion . "'>
                                              <div class='plus-icon'></div>
                                          </a>
                                      </div>
                                  </td>";
                            echo "<td>". $resultado->quoteID . "</td>";
                            echo "<td>". $resultado->clinombre . "</td>";
                            echo "<td>". $resultado->nombre . "</td>";
                            echo "<td>". $resultado->contnombre . "</td>";
                            echo "<td>". $resultado->respCoti . "</td>";
                            echo "<td>". $resultado->repreVentas . "</td>";
                            echo "<td>". $resultado->uniqueFG . "</td>";
                            // echo "<td>". $resultado->stat . "</td>";
                            switch ($resultado->stat) {
                              case 'YELLOW STATUS':
                                  echo "<td><p class='yellow_status'>". $resultado->stat . "</p></td>";
                                  break;
                              case 'GREEN STATUS':
                                  echo "<td><p class='green_status'>". $resultado->stat . "</p></td>";
                                  break;
                              case 'RED STATUS':
                                  echo "<td><p class='red_status'>". $resultado->stat . "</p></td>";
                                  break;
                              default:
                                  echo "<td>". $resultado->stat . "</td>";
                                  break;
                            }

                            // echo "<td>". $resultado->startDate . "</td>";
                            echo "<td>". $resultado->releaseDate . "</td>";
                            echo "<td>". $resultado->turnAround . "</td>";
                            switch ($resultado->consOTC) {
                              case -1:
                                echo "<td>NO</td>";
                                break;
                              case 1:
                                echo "<td>YES</td>";
                                break;
                              default:
                                echo "<td>TBD</td>";
                                break;
                            }
                            switch ($resultado->awarded) {
                              case -1:
                                echo "<td>NO</td>";
                                break;
                              case 1:
                                echo "<td>YES</td>";
                                break;
                              default:
                                echo "<td>TBD</td>";
                                break;
                            }

                            // // echo "<td>". $resultado->creador . "</td>";
                            // echo "<td>
                            //         <a href='proyecto_actividades.php?id=" . $resultado->idProyecto . "'>
                            //         <div class='flex-container' style='display: flex; justify-content: center;'>
                            //             <div class='plus-icon'></div>
                            //         </div>
                            //         </a>
                            //       </td>";
                            // echo "<td>
                            //         <a href='proyecto_capacidades.php?id=" . $resultado->idProyecto . "'>
                            //         <div class='flex-container' style='display: flex; justify-content: center;'>
                            //             <div class='plus-icon-green'></div>
                            //         </div>
                            //         </a>
                            //       </td>";
                            // echo "<td>
                            //         <a href='proyecto_ensambles.php?id=" . $resultado->idProyecto . "'>
                            //         <div class='flex-container' style='display: flex; justify-content: center;'>
                            //             <div class='plus-icon-yellow'></div>
                            //         </div>
                            //         </a>
                            //       </td>";
                            // echo "<td>
                            //         <a href='proyecto_recursos.php?id=" . $resultado->idProyecto . "'>
                            //         <div class='flex-container' style='display: flex; justify-content: center;'>
                            //             <div class='plus-icon'></div>
                            //         </div>
                            //         </a>
                            //       </td>";
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

    <script src="js/funciones.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            <?php if (isset($_POST['btnBuscarCotizacion']) && $msg != "") {
                echo "$msg";
            } ?>

            // DataTable
            var table = $('#quoteHistoryTable').DataTable({
              // responsive: true,
              orderCellsTop: true,
              fixedHeader: true,
              pageLength: 100,
              // scrollX: true,
              dom: 'Bfrtip',
              buttons: [
                  // 'copyHtml5',
                  'excelHtml5',
              ],
              columnDefs: [
                  // {
                  //     target: 10,
                  //     render: $.fn.dataTable.render.number(',', '.', 0, '')
                  //     // visible: false,
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
            $('#quoteHistoryTable thead tr:eq(1) th').each( function () {
                var title = $(this).text();
                $(this).html( '<input type="text" placeholder="Search '+title+'" class="column_search" />' );
            } );

            // Apply the search
            $( '#quoteHistoryTable thead'  ).on( 'keyup', ".column_search",function () {
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
            let columnData = $('#quoteHistoryTable').DataTable().column(10,{search:'applied'}).data().toArray();
            var suma = columnData.reduce((a, item) => (a + Number(item)), 0);
            let avg = suma/columnData.length;
            return avg.toFixed(1);
        };
        // //Append <tfoot>
        // $('#mytable').append(`<tfoot><tr><td colspan="3">Average age: <span id="avgage">${avgAge()}</span></td></tr></tfoot>`);


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
    </script>

    <?php include "inc/footer.html"; ?>
