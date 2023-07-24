<?php if (!session_id()) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>COE Performance System</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <link rel="stylesheet" href="../../css/style1.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.11.3/datatables.min.css"/>
  <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.11.3/datatables.min.js"></script>
  <script src="https://kit.fontawesome.com/b654958467.js" crossorigin="anonymous"></script>

</head>

<body>
    <nav>
        <ul>
            <li><a class="" href="/index.php">Home</a></li>
    <?php   if (!isset($_SESSION["usuarioNombre"])) { ?>
                <li><a href="/login.php">Log in</a></li>
    <?php   } else { ?>
              <?php   if (in_array(27, $_SESSION["permisos"])) { ?>
                          <li class="dropdown">
                              <a href="#">Reporting</a>
                              <div class="dropdown-content">
                                  <a href='/dashboard.php'>Dashboard</a>
                                  <a href='/kpi.php?year=<?php echo date("Y"); ?>'>KPI</a>
                              </div>
                          </li>
                <?php } ?>
                <li class="dropdown">
                    <a href="#">Resources</a>
                    <div class="dropdown-content">
                <?php   if (in_array(15, $_SESSION["permisos"]) || in_array(16, $_SESSION["permisos"])) {
                            echo "<a href='/cliente.php'>Customer</a>";
                        } ?>
                <?php   if (in_array(25, $_SESSION["permisos"]) || in_array(26, $_SESSION["permisos"])) {
                            echo "<a href='/carrier.php'>Carrier</a>";
                        } ?>
                <?php   if (in_array(9, $_SESSION["permisos"]) || in_array(10, $_SESSION["permisos"])) {
                            echo "<a href='/cuenta.php'>Account</a>";
                        } ?>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Personal</a>
                    <div class="dropdown-content">
                <?php
                        if (in_array(19, $_SESSION["permisos"]) || in_array(20, $_SESSION["permisos"])) {
                            echo "<a href='/empleado.php'>Employee</a>";
                        }
                        if (in_array(23, $_SESSION["permisos"]) || in_array(24, $_SESSION["permisos"])) {
                            echo "<a href='/usuario.php'>User</a>";
                        }
                        if (in_array(3, $_SESSION["permisos"]) || in_array(4, $_SESSION["permisos"])) {
                            echo "<a href='/capacidad.php'>Capabilities</a>";
                        }
                ?>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Project</a>
                    <div class="dropdown-content">
                <?php
                        if (in_array(5, $_SESSION["permisos"]) || in_array(6, $_SESSION["permisos"])) {
                            echo "<a href='/actividad.php'>Activities</a>";
                        }
                        if (in_array(11, $_SESSION["permisos"]) || in_array(12, $_SESSION["permisos"])) {
                            echo "<a href='/tipo_proyecto.php'>Complexity</a>";
                        }
                        if (in_array(13, $_SESSION["permisos"]) || in_array(14, $_SESSION["permisos"])) {
                            echo "<a href='/status.php'>Status</a>";
                        }
                        if (in_array(7, $_SESSION["permisos"]) || in_array(8, $_SESSION["permisos"])) {
                            echo "<a href='/proyecto.php'>History</a>";
                        }
                        if (in_array(28, $_SESSION["permisos"])) {
                            echo "<a href='/log.php'>Open Log</a>";
                        }
                ?>
                    </div>
                </li>

                <li class="dropdown">
                    <a href="#">Quote</a>
                    <div class="dropdown-content">
                <?php
                        if (in_array(31, $_SESSION["permisos"]) || in_array(32, $_SESSION["permisos"])) {
                            echo "<a href='/cotizacion.php'>History</a>";
                        }
                        if (in_array(33, $_SESSION["permisos"])) {
                            echo "<a href='/cotizacion_log.php'>Open Log</a>";
                        }
                ?>
                    </div>
                </li>

                <li><a href="/perfil.php">Profile</a></li>
                <?php
                        if (in_array(35, $_SESSION["permisos"])) {
                            echo "<li><a href='/engineer_activities.php'>Act per Eng</a></li>";
                        }
                ?>
                <li><a href="/salir.php">Log Out</a></li>
    <?php   } ?>
        </ul>
    </nav>
