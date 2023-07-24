<?php if (!session_id()) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>COE Performance System</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <script src="https://kit.fontawesome.com/b654958467.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="/css/styleBoostrap.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">

  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.11.3/datatables.min.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.dataTables.min.css">

  <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.11.3/datatables.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid ps-5">
        <a class="navbar-brand" href="/index.php">Home</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-start" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                        <?php   if (!isset($_SESSION["usuarioNombre"])) { ?>
                                    <li class="nav-item"><a class="nav-link" href="/login.php">Log in</a></li>
                        <?php   } else { ?>
                                  <?php   if (in_array(27, $_SESSION["permisos"])) { ?>
                                              <li class="nav-item dropdown">
                                                  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reporting</a>
                                                  <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                                      <li><a class="dropdown-item" href="/dashboard.php">Dashboard</a></li>
                                                      <li><a class="dropdown-item" href="/kpi.php?year=<?php echo date("Y");?>">KPI</a></li>
                                                  </ul>
                                              </li>
                                    <?php } ?>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Resources</a>
                                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <?php   if (in_array(15, $_SESSION["permisos"]) || in_array(16, $_SESSION["permisos"])) {
                        echo "<li><a class='dropdown-item' href='/cliente.php'>Customer</a></li>";
                    } ?>
                                    <?php   if (in_array(25, $_SESSION["permisos"]) || in_array(26, $_SESSION["permisos"])) {
                        echo "<li><a class='dropdown-item' href='/carrier.php'>Carrier</a></li>";
                    } ?>
                                    <?php   if (in_array(9, $_SESSION["permisos"]) || in_array(10, $_SESSION["permisos"])) {
                        echo "<li><a class='dropdown-item' href='/cuenta.php'>Account</a></li>";
                    } ?>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                      <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Personal</a>
                                      <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <?php
                                            if (in_array(19, $_SESSION["permisos"]) || in_array(20, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/empleado.php'>Employee</a></li>";
                                            }
                                            if (in_array(23, $_SESSION["permisos"]) || in_array(24, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/usuario.php'>User</a></li>";
                                            }
                                            if (in_array(3, $_SESSION["permisos"]) || in_array(4, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/capacidad.php'>Capabilities</a></li>";
                                            }
                                    ?>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                      <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Project</a>
                                      <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <?php
                                            if (in_array(5, $_SESSION["permisos"]) || in_array(6, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/actividad.php'>Activities</a></li>";
                                            }
                                            if (in_array(11, $_SESSION["permisos"]) || in_array(12, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/tipo_proyecto.php'>Complexity</a></li>";
                                            }
                                            if (in_array(13, $_SESSION["permisos"]) || in_array(14, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/status.php'>Status</a></li>";
                                            }
                                            if (in_array(7, $_SESSION["permisos"]) || in_array(8, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/pages/proyecto_history/proyecto_history_coe.php'>COE History</a></li>";
                                                echo "<li><a class='dropdown-item' href='/pages/proyecto_history/proyecto_history_application.php'>Application History</a></li>";
                                            }
                                            if (in_array(28, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/log.php'>Open Log</a></li>";
                                            }
                                    ?>
                                        </ul>
                                    </li>

                                    <li class="nav-item dropdown">
                                      <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Quote</a>
                                      <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <?php
                                            if (in_array(31, $_SESSION["permisos"]) || in_array(32, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/cotizacion.php'>History</a></li>";
                                            }
                                            if (in_array(33, $_SESSION["permisos"])) {
                                                echo "<li><a class='dropdown-item' href='/cotizacion_log.php'>Open Log</a></li>";
                                            }
                                    ?>
                                        </ul>
                                    </li>

                                    <?php
                                            if (in_array(35, $_SESSION["permisos"])) {
                                                echo "<li class='nav-item'><a class='nav-link' href='/engineer_activities.php'>Act per Eng</a></li>";
                                            }
                                            if (in_array(36, $_SESSION["permisos"])) {
                                                echo "<li class='nav-item'><a class='nav-link' href='/pages/ticket/ticket.php'>Tickets</a></li>";
                                            }
                                    ?>

            </ul>
          </div>

          <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
              <ul class="navbar-nav mb-2 mb-lg-0 me-5">
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">User</a>
                      <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                          <li><a class="dropdown-item" href="/perfil.php">Profile</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item" href="/salir.php">Log Out</a></li>
                      </ul>
                  </li>
              </ul>
          </div>
                        <?php   } ?>

      </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
