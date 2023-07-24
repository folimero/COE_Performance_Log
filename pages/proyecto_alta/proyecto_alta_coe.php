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
  // Funcion para llenar Selector de Categoria de formulario
  $stmtCategoria = $dbh->prepare("SELECT idProyectoCategoria, categoria, descripcion FROM proyecto_categoria");
  $stmtCategoria->execute();
  // Funcion para llenar Selector de Complejidad de formulario
  $stmtComplejidad = $dbh->prepare("SELECT idComplejidad, nombre FROM complejidad");
  $stmtComplejidad->execute();
  // Funcion para llenar Selector de Responsable de formulario
  $stmtResponsable = $dbh->prepare("SELECT numEmpleado, nombre, idEmpleado FROM empleado
                                    WHERE (empleado.idDepartamento = 1 OR empleado.idDepartamento = 2 OR empleado.idDepartamento = 9 OR empleado.idPuesto = 16) AND empleado.activo = 1");
  $stmtResponsable->execute();
  // Funcion para llenar Selector de Responsable de formulario
  $stmtResponsable2 = $dbh->prepare("SELECT numEmpleado, nombre, idEmpleado FROM empleado
                                    WHERE (empleado.idDepartamento = 1 OR empleado.idDepartamento = 2 OR empleado.idDepartamento = 9) AND empleado.activo = 1");
  $stmtResponsable2->execute();
  $stmtRepVentas = $dbh->prepare("SELECT numEmpleado, empleado.nombre, idEmpleado
                                        FROM empleado
                                        INNER JOIN departamento
                                        ON empleado.idDepartamento = departamento.idDepartamento
                                        WHERE departamento.nombre = 'Representante de Ventas'");
  $stmtRepVentas->execute();
  // Funcion para llenar Selector de Status de formulario
  $stmtStatus = $dbh->prepare("SELECT idStatus, nombre FROM status");
  $stmtStatus->execute();
  // Funcion para llenar Selector de Etapa de formulario
  $stmtStage = $dbh->prepare("SELECT idEtapa, nombre FROM etapa");
  $stmtStage->execute();

  // Project Leader
  $projectLeader = $dbh->prepare("SELECT empleado.numEmpleado, empleado.nombre, usuario.idUsuario
                                  FROM usuario
                                  INNER JOIN empleado
                                  ON usuario.idEmpleado = empleado.idEmpleado
                                  WHERE empleado.idPuesto = 12 AND empleado.activo = 1");
  $projectLeader->execute();
  // COE Project Manager
  $projectManager = $dbh->prepare("SELECT empleado.numEmpleado, empleado.nombre, usuario.idUsuario
                                  FROM usuario
                                  INNER JOIN empleado
                                  ON usuario.idEmpleado = empleado.idEmpleado
                                  WHERE empleado.idDepartamento = 5 AND empleado.idPuesto <> 12 AND empleado.activo = 1");
  $projectManager->execute();
  // COE Project Coordinator
  $projectCoordinator = $dbh->prepare("SELECT empleado.numEmpleado, empleado.nombre, usuario.idUsuario
                                  FROM usuario
                                  INNER JOIN empleado
                                  ON usuario.idEmpleado = empleado.idEmpleado
                                  WHERE empleado.idDepartamento = 5 AND empleado.idPuesto <> 12 AND empleado.activo = 1");
  $projectCoordinator->execute();
  // COE QA Engineer
  $qaEngineer = $dbh->prepare("SELECT empleado.numEmpleado, empleado.nombre, usuario.idUsuario
                                  FROM usuario
                                  INNER JOIN empleado
                                  ON usuario.idEmpleado = empleado.idEmpleado
                                  WHERE empleado.idDepartamento = 6 AND empleado.activo = 1");
  $qaEngineer->execute();

  if (isset($_GET["id"])){
      $editMode = true;
  } else {
      $editMode = false;
  }
?>

<!DOCTYPE html>
    <div class="flex-container w-100 mt-3">
        <div class="card shadow p-3 bg-body rounded w-100 m-0">
          <div class="card-header bg-primary text-center text-white fw-bold">
              <h3>COE Project Register</h3>
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
                          <label for="nombre">Name</label>
                          <input name="nombre" type="text" id="nombre" required>
                        </div>
                        <div class="input-field">
                          <label for="descripcion">Description</label>
                          <textarea id="descripcion" style="width: 100%; height: 33px;" name="descripcion" rows="4" cols="50"></textarea>
                        </div>
                        <div class="input-field">
                          <label for="cobrarA">Freight To</label>
                          <select name="cobrarA" id="cobrarA" required>
                            <option disabled selected value> -- Select -- </option>
                            <option value="NAI">NAI</option>
                            <option value="CUSTOMER">CUSTOMER</option>
                          </select>
                        </div>
                        <div class="input-field">
                          <!-- Selector de Cuenta -->
                          <label for="cuenta">Account</label>
                          <div class="">
                            <div class="inline-container">
                              <div class="inline-container" id="idCuenta"></div>
                              <div class="icon-container">
                                <?php if (isset($_GET['id'])) { ?>
                                          <a href="/cuenta.php?id=<?php echo $_GET["id"]; ?>">
                                <?php } else { ?>
                                          <a href="/cuenta.php">
                                <?php } ?>
                                  <div class="plus-icon"></div>
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="input-field">
                          <label for="tracking">Tracking #</label>
                          <input name="tracking" type="text" id="tracking">
                        </div>
                        <div class="input-field">
                          <label for="appTrackID">Approved Tracker ID</label>
                          <input name="appTrackID" type="text" id="appTrackID" maxlength="30">
                        </div>
                        <div class="input-field">
                          <label for="po">PO</label>
                          <input name="po" type="text" id="po">
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
                          <input name="hrs" type="text" id="idHrs" style="font-weight:bold; background-color: AliceBlue;" disabled>
                        </div>
                        <div class="input-field">
                          <label for="ventasPotenciales">Potential Sales</label>
                          <!-- <input name="ventasPotenciales" id="ventasPotenciales" type="number" min="0" value="0" step=".01" required> -->
                          <input type="text" name="ventasPotenciales" id="ventasPotenciales" pattern="^\$\d{1,3}(,\d{3})*(\.\d+)?$" value="" data-type="currency" placeholder="$10,000.00">
                        </div>
                        <div class="input-field">
                          <!-- <label for="tipo">Tipo</label> -->
                          <input name="tipo" type="hidden" id="idTipoProyecto">
                        </div>
                        <div class="input-field">
                          <label for="olverLoad">Project Overload</label>
                          <input name="olverLoad" id="olverLoad" type="number" min="0" max="1" value="0" step=".01" placeholder="%">
                        </div>
                        <div class="input-field">
                            <!-- Selector de Responsable de Diseño -->
                            <label for="empleadoDiseno">Design Resp.</label>
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
                        <div class="input-field">
                          <!-- Selector de Responsable de Manufactura -->
                          <label for="empleadoManu">Manufacturing Resp.</label>
                          <div class="">
                            <div class="inline-container">
                              <select name="empleadoManu" id="respManu" required>
                                <option disabled selected value> -- Select -- </option>
                                <?php
                                            while ($resultado = $stmtResponsable2->fetch()) {
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
                        <div class="input-field">
                          <label for="fechaReqCliente">Customer Req Date:</label>
                          <input type="date" id="fechaReqCliente" name="fechaReqCliente" value="" min="2017-01-01">
                        </div>
                        <div class="input-field">
                          <label for="fechaPromesa">Promise Date:</label>
                          <input type="date" id="fechaPromesa" name="fechaPromesa" value="" min="2017-01-01">
                        </div>
                        <!-- <div class="input-field">
                          <label for="fechaTentativa">Fecha Tentativa:</label>
                          <input type="date" id="fechaTentativa" name="fechaTentativa" value="" min="2021-01-01">
                        </div> -->
                        <div class="input-field">
                          <label for="fechaInicio">Start Date:</label>
                          <input type="date" id="fechaInicio" name="fechaInicio" value="" min="2017-01-01">
                        </div>
                        <div class="input-field">
                          <label for="fechaEmbarque">Ship Date:</label>
                          <input type="date" id="fechaEmbarque" name="fechaEmbarque" value="" min="2017-01-01">
                        </div>
                        <!-- <div class="input-field">
                          <label for="fechaTermino">Fecha Termino de Proyecto:</label>
                          <input type="date" id="fechaTermino" name="fechaTermino" value="" min="2021-01-01">
                        </div> -->
                        <!-- <div class="input-field">
                          <label for="tiempoVida">Tiempo de Vida</label>
                          <input name="tiempoVida" type="number" min="0" value="0" step=".01" required>
                        </div> -->
                        <div class="input-field">
                          <label for="qto">QO Number</label>
                          <input name="qto" type="text" id="qto">
                        </div>
                        <div class="input-field">
                          <!-- Selector de Representante de Ventas -->
                          <label for="repreVentas">Sales Representative</label>
                          <div class="">
                              <div class="inline-container">
                                  <select name="repreVentas" id='repreVentas'>
                                      <option disabled selected value> -- N/A -- </option>
                                      <?php
                                      while ($resultado = $stmtRepVentas->fetch()) {
                                      ?>
                                          <option value="<?php echo $resultado->idEmpleado; ?>">
                                          <?php
                                          echo $resultado->numEmpleado . " - " . $resultado->nombre; ?>
                                          </option>
                                          <?php
                                      }
                                      ?>
                                  </select>
                                  <div class="icon-container" style="display: flex; justify-content: center;">
                                      <a href="/empleado.php">
                                          <div class="plus-icon"></div>
                                      </a>
                                  </div>
                              </div>
                          </div>
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

            <!-- APPROVERS SECTION -->
            <div class="row">
                <h2 class="card-header text-center ">Approvers</h2>

                <div class="row d-flex justify-content-center">
                  <div class="col-4">
                      <div class="input-field">
                          <label for="empleadoDiseno">Project Leader</label>
                          <div class="">
                              <div class="inline-container">
                                  <select name="idProjectLeader" id="idProjectLeader">
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
                                      <a href="/usuario.php">
                                          <div class="plus-icon"></div>
                                      </a>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-4">
                      <div class="input-field">
                          <label for="empleadoDiseno">COE Project Manager</label>
                          <div class="">
                              <div class="inline-container">
                                  <select name="idProjectManager" id="idProjectManager">
                                      <option disabled selected value> -- Select -- </option>
                                      <?php
                                                  while ($resultado = $projectManager->fetch()) {
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
                                      <a href="/usuario.php">
                                          <div class="plus-icon"></div>
                                      </a>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col-4">
                    <div class="input-field">
                        <label for="empleadoDiseno">COE Project Coordinator</label>
                        <div class="">
                            <div class="inline-container">
                                <select name="idProjectCoordinator" id="idProjectCoordinator">
                                    <option disabled selected value> -- Select -- </option>
                                    <?php
                                                while ($resultado = $projectCoordinator->fetch()) {
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
                                    <a href="/usuario.php">
                                        <div class="plus-icon"></div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="input-field">
                        <!-- Selector de Responsable de Diseño -->
                        <label for="empleadoDiseno">COE QA Engineer</label>
                        <div class="">
                            <div class="inline-container">
                                <select name="idQAEngineer" id="idQAEngineer">
                                    <option disabled selected value> -- Select -- </option>
                                    <?php
                                                while ($resultado = $qaEngineer->fetch()) {
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
                                    <a href="/usuario.php">
                                        <div class="plus-icon"></div>
                                    </a>
                                </div>
                            </div>
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

        		recargarLista();

        		$('#cliente').change(function(){
        			recargarLista();
        		});
            $('#idCategoria').change(function(){
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
    <script src="proyecto_alta_coe_func.js"></script>

    <?php include "../../inc/footer.html"; ?>
