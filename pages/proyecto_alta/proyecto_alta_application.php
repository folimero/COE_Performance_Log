<?php
  include "../../inc/conexion.php";
  include "../../inc/headerBoostrap.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(7, $_SESSION["permisos"]) && !in_array(8, $_SESSION["permisos"])) {
          $message = "Unauthorized User.";
          echo "<script>
                    alert('$message');
                    window.location.href='/index.php';
                </script>";
          die();
      }
  } else {
      $message = "Please Log in.";
      echo "<script>
                alert('$message');
                window.location.href='/login.php';
            </script>";
      die();
  }

  // Funcion para llenar Selector de Cliente de formulario
  $stmtCliente = $dbh->prepare("SELECT idCliente, nombreCliente FROM cliente");
  $stmtCliente->execute();
  $stmtRequester = $dbh->prepare("SELECT idProyectoRequester, nombre FROM proyecto_requester");
  $stmtRequester->execute();
  // Funcion para llenar Selector de Categoria de formulario
  $stmtCategoria = $dbh->prepare("SELECT idProyectoCategoria, categoria, descripcion FROM proyecto_categoria
  WHERE idProyectoCategoria IN (2,6,9)");
  $stmtCategoria->execute();

  $stmtServicio = $dbh->prepare("SELECT idProyectoServicio, servicio, descripcion FROM proyecto_servicio");
  $stmtServicio->execute();

  // Funcion para llenar Selector de Complejidad de formulario
  $stmtComplejidad = $dbh->prepare("SELECT idComplejidad, nombre FROM complejidad");
  $stmtComplejidad->execute();
  $projectLeader = $dbh->prepare("SELECT empleado.numEmpleado, empleado.nombre, usuario.idUsuario
                                  FROM usuario
                                  INNER JOIN empleado
                                  ON usuario.idEmpleado = empleado.idEmpleado
                                  WHERE empleado.asignableAsResp = 1 AND empleado.activo = 1");
  $projectLeader->execute();
  $stmtResponsable = $dbh->prepare("SELECT numEmpleado, nombre, idEmpleado FROM empleado
                                    WHERE (empleado.idDepartamento = 1 OR empleado.idDepartamento = 2 OR empleado.idDepartamento = 9 OR empleado.idDepartamento = 10 OR empleado.idPuesto = 16) AND empleado.activo = 1");
  $stmtResponsable->execute();
  // Funcion para llenar Selector de Responsable de formulario
  $stmtResponsable2 = $dbh->prepare("SELECT numEmpleado, nombre, idEmpleado FROM empleado
                                    WHERE (empleado.idDepartamento = 9 OR empleado.idDepartamento = 10) AND empleado.activo = 1");
  $stmtResponsable2->execute();
  // Funcion para llenar Selector de Status de formulario
  $stmtStatus = $dbh->prepare("SELECT idStatus, nombre FROM status");
  $stmtStatus->execute();

  if (isset($_GET["id"])){
      $editMode = true;
  } else {
      $editMode = false;
  }
?>

<!DOCTYPE html>
    <div class="flex-container w-100 mt-3">
        <div class="card shadow p-3 bg-body rounded w-100 m-0">
          <div class="card-header bg-success text-center text-white fw-bold">
              <h3>Application Project Register</h3>
          </div>
          <div class="row w-100 bg-light">
              <div class="col">
                  <div class='icon-container' style="margin: 20px 0px;">
                      <a href='/log.php' id="backIcon">
                          <div class='back-icon-green'></div>
                      </a>
                  </div>
              </div>
          </div>
        </div>
    </div>

    <hr style="width:100%;">
    <form class="card shadow p-3 bg-body rounded" id="form_empleados" style="width: 100%; margin-top: 0;" action="../../proyecto_registro.php" method="post">
            <h2 class="card-header text-center ">Project Detail</h2>
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <div class="row">
                        <div class="input-field">
                          <!-- <label for="projectID">Project ID</label> -->
                          <input name="projectID" type="hidden" id="projectID">
                          <input name="isApplication" type="hidden" id="isApplication" value="1">
                        </div>
                        <div class="input-field">
                          <!-- Selector de Cliente -->
                          <label for="cliente">Customer</label>
                          <div class="inline-container">
                            <select id="cliente" name="cliente" required>
                              <option disabled selected value> -- Select -- </option>
                              <?php
                                              while ($resultado = $stmtCliente->fetch()) {
                                                  ?>
                              <option value="<?php echo $resultado->idCliente; ?>">
                                <?php
                                                      echo $resultado->nombreCliente; ?>
                              </option>
                              <?php
                                              }
                                          ?>
                            </select>

                            <div class="icon-container">
                              <a href="/cliente.php" id="clienteIcon">
                                <div class="plus-icon"></div>
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="input-field">
                          <label for="descripcion">Description</label>
                          <textarea id="descripcion" style="width: 100%; height: 33px;" name="descripcion" rows="4" cols="50" required></textarea>
                        </div>
                        <div class="input-field">
                          <label for="po">PO</label>
                          <input name="po" type="text" id="po">
                        </div>
                        <div class="input-field">
                          <!-- Selector de Cliente -->
                          <label for="idProyectoRequester">Requested by</label>
                          <div class="inline-container">
                            <select id="idProyectoRequester" name="idProyectoRequester" required>
                              <option disabled selected value> -- Select -- </option>
                              <?php
                                          while ($resultado = $stmtRequester->fetch()) {
                                              ?>
                              <option value="<?php echo $resultado->idProyectoRequester; ?>">
                                <?php
                                          echo $resultado->nombre; ?>
                                          <!-- echo $resultado->numEmpleado . " " . $resultado->nombre; ?> -->
                              </option>
                              <?php
                                          }
                                          ?>
                            </select>
                            <div class="icon-container">
                              <a href="../proyecto_requester/proyecto_requester.php" id="clienteIcon">
                                <div class="plus-icon"></div>
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="input-field">
                            <!-- Selector de Responsable de Diseño -->
                            <label for="idProjectLeader">Project Leader.</label>
                            <div class="">
                                <div class="inline-container">
                                    <select name="idProjectLeader" id="idProjectLeader" required>
                                        <option disabled selected value> -- Select -- </option>
                                        <?php
                                                    while ($resultado = $projectLeader->fetch()) {
                                                        ?>
                                        <option value="<?php echo $resultado->idUsuario; ?>">
                                          <?php
                                                    echo $resultado->nombre; ?>
                                                    <!-- echo $resultado->numEmpleado . " " . $resultado->nombre; ?> -->
                                        </option>
                                        <?php
                                                    }
                                                    ?>
                                    </select>
                                    <div class="icon-container">
                                        <a href="/empleado.php">
                                            <div class="plus-icon"></div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="input-field">
                            <!-- Selector de Responsable de Diseño -->
                            <label for="empleadoDiseno">Activities Owner</label>
                            <div class="">
                                <div class="inline-container">
                                    <select name="empleadoDiseno" id="respDiseno">
                                        <option disabled selected value> -- Select -- </option>
                                        <?php
                                                    while ($resultado = $stmtResponsable->fetch()) {
                                                        ?>
                                        <option value="<?php echo $resultado->idEmpleado; ?>">
                                          <?php
                                                    echo $resultado->nombre; ?>
                                                    <!-- echo $resultado->numEmpleado . " " . $resultado->nombre; ?> -->
                                        </option>
                                        <?php
                                                    }
                                                    ?>
                                    </select>
                                    <div class="icon-container">
                                        <a href="/empleado.php">
                                            <div class="plus-icon"></div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-4">
                        <div class="column-format">
                        <!-- <div class="input-field" style="margin-bottom: 20px;"> -->
                            <input type="hidden" name="pdp" value="1">
                            <!-- <label for="pdp">PDP</label> -->
                        <!-- </div> -->
                        <div class="input-field">
                          <!-- Selector de Categoria -->
                          <label for="categoria">Type</label>
                          <div class="inline-container">
                            <select id='idCategoria' name="categoria" required>
                              <option disabled selected value> -- Select -- </option>
                              <?php
                                  while ($resultado = $stmtCategoria->fetch()) {
                                      ?>
                                      <option value="<?php echo $resultado->idProyectoCategoria; ?>">
                                      <?php
                                          echo "[" . $resultado->categoria . "] - " .$resultado->descripcion; ?>
                                      </option>
                                      <?php
                                  }
                              ?>
                            </select>
                            <div class="icon-container">
                              <a href="/proyecto_categoria.php" id="categoriaIcon">
                                <div class="plus-icon"></div>
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="input-field">
                          <!-- Selector de Categoria -->
                          <label for="servicio">Service</label>
                          <div class="inline-container">
                            <select id='idServicio' name="servicio" style="background-color: AliceBlue;" required>
                              <option disabled selected value> -- Select -- </option>
                            </select>
                            <div class="icon-container">
                              <a href="../proyecto_servicio/proyecto_servicio.php?id=proyecto_alta_application.php" id="servicioIcon">
                                <div class="plus-icon"></div>
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="input-field">
                          <!-- Selector de Complejidad -->
                          <label for="complejidad">Complexity</label>
                          <div class="">
                            <div class="inline-container">
                              <select id='idComplejidad' name="complejidad" required>
                                <option disabled selected value> -- Select -- </option>
                                <?php
                                            while ($resultado = $stmtComplejidad->fetch()) {
                                                ?>
                                <option value="<?php echo $resultado->idComplejidad; ?>">
                                  <?php
                                                echo $resultado->nombre; ?>
                                </option>
                                <?php
                                            }
                                            ?>
                              </select>
                              <div class="icon-container">
                                <a href="/complejidad.php" id="complejidadIcon">
                                  <div class="plus-icon"></div>
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="input-field">
                            <label for="hrs">Hours</label>
                            <input name="hrs" type="text" id="idHrs" style="font-weight:bold; background-color: AliceBlue;" disabled required>
                        </div>
                        <div class="input-field">
                            <label for="overLoad">Overload Hours</label>
                            <input name="overLoad" type="number" id="overLoad" step='0.1' style="font-weight:bold;" required>
                        </div>
                        <div class="input-field">
                          <!-- <label for="tipo">Tipo</label> -->
                          <input name="tipo" type="hidden" id="idTipoProyecto">
                        </div>
                      </div>
                    </div>
                    <div class="col-4">
                        <div class="column-format">
                        <div class="input-field">
                          <label for="fechaInicio">Requested Date:</label>
                          <input type="date" id="fechaInicio" name="fechaInicio" value="" min="2017-01-01">
                        </div>
                        <div class="input-field">
                          <label for="fechaReqCliente">Due Date:</label>
                          <input type="date" id="fechaReqCliente" name="fechaReqCliente" value="" min="2017-01-01">
                        </div>
                        <div class="input-field">
                          <label for="fechaEmbarque">Completed Date:</label>
                          <input type="date" id="fechaEmbarque" name="fechaEmbarque" value="" min="2017-01-01">
                        </div>
                        <div class="input-field">
                          <label for="qto">QO Number</label>
                          <input name="qto" type="text" id="qto">
                        </div>
                        <div class="input-field">
                          <!-- Notas -->
                          <label for="notas">General Note</label>
                          <textarea id="notas" style="width: 100%; height:30px;" name="notas" rows="4" cols="50"></textarea>
                        </div>

                        <div class="input-field">
                          <input name="edicion" type="hidden" id="edicion">
                          <input name="idProyecto" type="hidden" id="idProyecto">
                        </div>
                      </div>
                    </div>
                </div>
            </div>
            <hr style="width:100%;">

            <div class="row d-flex justify-content-center">
                <input class="w-75" type="submit" value="Register">
            </div>
    </form>

    <div class="flex-container" style="height: 80px;"></div>

    <!-- VENTANAS MODALES -->
    <span class="alerta ocultar">
        <span class="msg">This is a warning</span>
        <span class='icon-container'>
            <div id="cerrar_alerta" class='cross-icon'></div>
        </span>
    </span>


    <script type="text/javascript">
      	$(document).ready(function() {
        		// $('#lista1').val(1);
            var pathname = window.location.pathname;
            $('.nav > li > a[href="'+pathname+'"]').parent().addClass('active');

            $('#idCategoria').change(function(){
              recargarServicios();
              recargarTipoProyecto();
            });
            $('#idServicio').change(function(){
              recargarTipoProyecto();
            });
            $('#idComplejidad').change(function(){
              recargarTipoProyecto();
            });

            <?php if (isset($_GET["id"])) { ?>
                editarProyecto(<?php echo $_GET["id"]; ?>);
                $('#edicion').val(1);
                $('#idProyecto').val(<?php echo $_GET["id"]; ?>);
                $('#projectID').css({"font-weight": "bold", "background-color": "AliceBlue"})
                // $('#projectID').prop( "disabled", true );
                $('#cliente').css({"font-weight": "bold", "background-color": "AliceBlue"})
                $('#cliente').prop( "disabled", true );
                // $('#nombre').css({"font-weight": "bold", "background-color": "AliceBlue"})
                // $('#nombre').prop( "disabled", true );
            <?php } ?>

            $('#cerrar_alerta').click(function() {
                $('.alerta').removeClass('mostrar');
                $('.alerta').addClass('ocultar');
            });
        })
    </script>
    <script src="/js/funciones.js"></script>
    <script src="proyecto_alta_application_func.js"></script>

    <?php include "../../inc/footer.html"; ?>
