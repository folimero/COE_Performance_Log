<?php
  include "inc/conexion.php";
  include "inc/header.php";

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

  // Funcion para llenar Selector de Cliente de formulario
  $stmtCliente = $dbh->prepare("SELECT idCliente, nombreCliente FROM cliente");
  $stmtCliente->execute();
  // $stmtTipoCotizacion = $dbh->prepare("SELECT idTipoCotizacion, categoria, descripcion, complejidad.nombre AS complex, cotizacion_volumen.nombre, tipocotizacion.horas
  //                                       FROM tipocotizacion
  //                                       INNER JOIN cotizacion_categoria
  //                                       ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
  //                                       INNER JOIN complejidad
  //                                       ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad
  //                                       INNER JOIN cotizacion_volumen
  //                                       ON tipocotizacion.idCotizacionVolumen = cotizacion_volumen.idCotizacionVolumen
  //                                       WHERE categoria = 'QUO-1' OR categoria = 'QUO-2' OR categoria = 'QUO-3' OR categoria = 'QUO-7' OR categoria = 'QUO-8'
  //                                       ORDER BY `tipocotizacion`.`idTipoCotizacion` ASC");
  // $stmtTipoCotizacion->execute();
  // Funcion para llenar Selector de Categoria de formulario
  $stmtCategoria = $dbh->prepare("SELECT idCotizacionCategoria, categoria, descripcion, complejidad.nombre
                                  FROM cotizacion_categoria
                                  INNER JOIN complejidad
                                  ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad
                                  WHERE categoria = 'QUO-1' OR categoria = 'QUO-2' OR categoria = 'QUO-3' OR categoria = 'QUO-7' OR categoria = 'QUO-8'
                                  ORDER BY idCotizacionCategoria ASC");
  $stmtCategoria->execute();
  // Funcion para llenar Selector de Complejidad de formulario
  $stmtVolumen = $dbh->prepare("SELECT idCotizacionVolumen, nombre FROM cotizacion_volumen ORDER BY idCotizacionVolumen ASC");
  $stmtVolumen->execute();
  // Funcion para llenar Selector de Responsable de formulario
  $stmtRespCotizaciones = $dbh->prepare("SELECT numEmpleado, empleado.nombre, idEmpleado
                                        FROM empleado
                                        INNER JOIN departamento
                                        ON empleado.idDepartamento = departamento.idDepartamento
                                        WHERE (departamento.nombre = 'COE - Cotizaciones' OR empleado.idEmpleado = 18) AND empleado.activo = 1");
  $stmtRespCotizaciones->execute();
  // Funcion para llenar Selector de Responsable de formulario
  $stmtResponsableBOM = $dbh->prepare("SELECT numEmpleado, nombre, idEmpleado FROM empleado
                                        WHERE empleado.idDepartamento = 1");
  $stmtResponsableBOM->execute();
  // Funcion para llenar Selector de Responsable de formulario
  $stmtRepVentas = $dbh->prepare("SELECT numEmpleado, empleado.nombre, idEmpleado
                                        FROM empleado
                                        INNER JOIN departamento
                                        ON empleado.idDepartamento = departamento.idDepartamento
                                        WHERE departamento.nombre = 'Representante de Ventas'");
  $stmtRepVentas->execute();
  $stmtBOMType = $dbh->prepare("SELECT idTipoCotizacion, categoria, descripcion, complejidad.nombre AS complex, cotizacion_volumen.nombre, tipocotizacion.horas
                                        FROM tipocotizacion
                                        INNER JOIN cotizacion_categoria
                                        ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
                                        INNER JOIN complejidad
                                        ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad
                                        INNER JOIN cotizacion_volumen
                                        ON tipocotizacion.idCotizacionVolumen = cotizacion_volumen.idCotizacionVolumen
                                        WHERE categoria = 'QUO-4' OR categoria = 'QUO-5' OR categoria = 'QUO-6'
                                        ORDER BY `tipocotizacion`.`idTipoCotizacion` ASC");
  $stmtBOMType->execute();
  // Funcion para llenar Selector de Status de formulario
  $stmtStatus = $dbh->prepare("SELECT idStatus, nombre FROM status");
  $stmtStatus->execute();
?>

<!DOCTYPE html>
    <div class="flex-container">
      <h1 id="tittle">New Quoting</h1>
      <div class='icon-container' style="margin: 20px 0px;">
          <a href='cotizacion_log.php' id="backIcon">
              <div class='back-icon-green'></div>
          </a>
      </div>
    </div>

    <form id="form_empleados" style="width: 100%; margin-top: 0;" action="cotizacion_registro.php" method="post">
      <div class="row" style="width: 100%; text-align: center;">
        <hr style="width:100%;">
        <div class="column" name="columna 1" style="margin: 0;">
          <div class="column-format">
            <div class="input-field">
                <label for="quoteID">Quote ID</label>
                <input name="quoteID" type="text" id="quoteID" required>
            </div>

            <div class="input-field">
                <!-- Selector de Cliente -->
                <label for="cliente">Customer</label>
                <div class="">
                    <div class="inline-container">
                        <select id="cliente" name="cliente" required>
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

                        <div class="flex-container" style="display: flex; justify-content: center;">
                            <a href="cliente.php">
                              <div class="plus-icon"></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="input-field">
              <label for="nombre">Name</label>
              <input name="nombre" type="text" id="nombre" required>
            </div>
            <div class="input-field">
              <label for="descripcion">Description</label>
              <textarea id="descripcion" style="width: 100%; height: 31px;" name="descripcion" rows="4" cols="50"></textarea>
            </div>
            <div class="input-field">
              <!-- Selector de Contacto Cliente -->
              <label for="clienteContacto">Customer Contact</label>
              <div class="">
                  <div class="inline-container">
                      <div class="inline-container" id="clienteContacto"></div>
                      <div class="icon-container" style="display: flex; justify-content: center;">
                          <a id='customerContactIcon' href="cliente_contacto.php">
                              <div class="plus-icon"></div>
                          </a>
                      </div>
                  </div>
              </div>
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
                          <a href="empleado.php">
                              <div class="plus-icon"></div>
                          </a>
                      </div>
                  </div>
              </div>
            </div>
            <div class="input-field">
              <label for="consolidatedEAU">Consolidated EAU</label>
              <input name="consolidatedEAU" id="consolidatedEAU" type="text" value="">
            </div>

            <div class="input-field">
              <label for="ventasPotenciales">Potential Sales</label>
              <input name="ventasPotenciales" id="ventasPotenciales" type="number" min="0" value="0" step=".01">
            </div>
          </div>
        </div>
        <div class="column" name="columna 2" style="margin: 0;">
          <div class="column-format">
            <div class="input-field">
              <!-- Selector de Categoria de Cotizacion -->
              <label for="categoria">Quoting Category</label>
              <div class="">
                  <div class="inline-container">
                      <select id='idCotizacionCategoria' name="categoria" required>
                          <option disabled selected value> -- Select -- </option>
                          <?php
                          while ($resultado = $stmtCategoria->fetch()) {
                              ?>
                          <option value="<?php echo $resultado->idCotizacionCategoria; ?>">
                          <?php
                          echo "[" . $resultado->categoria . "] [" . $resultado->nombre . "] --- " .
                                $resultado->descripcion; ?>
                          </option>
                          <?php
                          }
                          ?>
                      </select>
                      <div class="icon-container">
                          <a href="cotizacion_categoria.php">
                              <div class="plus-icon"></div>
                          </a>
                      </div>
                  </div>
              </div>
            </div>
            <div class="input-field">
              <!-- Selector de Volumen -->
              <label for="volumen">Volume</label>
              <div class="">
                  <div class="inline-container">
                      <select id='idVolumen' name="volumen" required>
                          <option disabled selected value> -- Select -- </option>
                      <?php
                          while ($resultado = $stmtVolumen->fetch()) {
                              ?>
                              <option value="<?php echo $resultado->idCotizacionVolumen; ?>">
                      <?php
                                  echo $resultado->nombre; ?>
                              </option>
                      <?php
                          }
                      ?>
                      </select>
                      <div class="icon-container" style="display: flex; justify-content: center;">
                          <a href="cotizacion_categoria.php">
                              <div class="plus-icon"></div>
                          </a>
                      </div>
                  </div>
              </div>
            </div>
            <div class="input-field">
              <label for="hrs">Hours</label>
              <input id='idHoras' name="hrs" type="text" value="" style="font-weight:bold; background-color: AliceBlue;" disabled>
            </div>
            <div class="input-field">
              <!-- <label for="tipoCotizacion">Tipo</label> -->
              <input id='idTipoCotizacion' name="tipoCotizacion" type="hidden" value="">
            </div>
            <div class="input-field">
              <!-- Selector de Responsable de Cotizaciones -->
              <label for="responCotizaciones">Quotation Representative</label>
              <div class="">
                  <div class="inline-container">
                      <select name="responCotizaciones" id="responCotizaciones" required>
                          <option disabled selected value> -- Select -- </option>
                          <?php
                          while ($resultado = $stmtRespCotizaciones->fetch()) {
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
                          <a href="empleado.php">
                              <div class="plus-icon"></div>
                          </a>
                      </div>
                  </div>
              </div>
            </div>
            <div class="input-field">
              <label for="lineItems">Line Items</label>
              <input name="lineItems" id="lineItems" type="number" min="0">
            </div>
            <div class="input-field">
              <label for="uniqueFG">Unique FG</label>
              <input name="uniqueFG" id="uniqueFG" type="number" id="uniqueFG" min="0">
            </div>
            <div class="input-field">
              <!-- Selector de Status -->
              <label for="status">Status</label>
              <div class="">
                  <div class="inline-container">
                      <select name="status" id="status" required>
                          <?php
                          while ($resultado = $stmtStatus->fetch()) {
                              ?>
                          <option value="<?php echo $resultado->idStatus; ?>">
                          <?php
                          echo $resultado->nombre; ?>
                          </option>
                          <?php
                          }
                          ?>
                      </select>
                      <div class="icon-container" style="display: flex; justify-content: center;">
                          <a href="status.php">
                              <div class="plus-icon"></div>
                          </a>
                      </div>
                  </div>
              </div>
            </div>
            <div class="input-field">
              <label for="sourcMatStartDate">Sourcing Materials Start Date:</label>
              <input type="date" id="sourcMatStartDate" name="sourcMatStartDate" value="" min="2010-01-01">
            </div>
          </div>
        </div>
        <div class="column" name="columna 3" style="margin: 0;">
          <div class="column-format">
            <div class="input-field">
              <label for="fechaInicio">Quote Start Date:</label>
              <input type="date" id="fechaInicio" name="fechaInicio" value="" min="2010-01-01">
            </div>
            <div class="input-field">
              <label for="fechaLanzamiento">Quote Release Date:</label>
              <input type="date" id="fechaLanzamiento" name="fechaLanzamiento" value="" min="2010-01-01">
            </div>
            <div class="input-field">
              <label for="fechaReqCliente">Quote Customer Requested Date:</label>
              <input type="date" id="fechaReqCliente" name="fechaReqCliente" value="" min="2010-01-01">
            </div>
            <div class="input-field">
              <!-- Selector de Tipo de BOM -->
              <label for="BOMType">BOM Type</label>
              <div class="">
                  <div class="inline-container">
                      <select name="BOMType" id="BOMType">
                          <option disabled selected value> -- N/A -- </option>
                          <?php
                          while ($resultado = $stmtBOMType->fetch()) {
                              ?>
                              <option value="<?php echo $resultado->idTipoCotizacion; ?>">
                          <?php
                              echo $resultado->categoria . " --- [" . $resultado->complex . "] --- [" .
                                    $resultado->nombre . "] --- " .$resultado->descripcion; ?>
                              </option>
                          <?php
                          }
                          ?>
                      </select>
                      <div class="icon-container">
                          <a href="tipo_cotizacion.php">
                              <div class="plus-icon"></div>
                          </a>
                      </div>
                  </div>
              </div>
            </div>

            <div class="input-field">
              <!-- Campo % Completado -->
              <label for="overallComplet">Overall Completation</label>
              <input name="overallComplet" id="overallComplet"type="number" min="0" max="1" value="0" step=".01" required>
            </div>
            <div class="input-field">
              <!-- Notas -->
              <label for="notas">Notes</label>
              <textarea id="notas" style="width: 100%; height: 31px;" name="notas" rows="4" cols="50"></textarea>
            </div>

            <div class="input-field">
              <div class="">
                  <label for="consOTC">Consider for OTC?</label>
                  <select name="consOTC" id="consOTC">
                      <option disabled selected value> -- N/A -- </option>
                      <option value="1">YES</option>
                      <option value="-1">NO</option>
                  </select>
              </div>
            </div>
            <div class="input-field">
              <label for="sourcMatEndDate">Sourcing Materials End Date:</label>
              <input type="date" id="sourcMatEndDate" name="sourcMatEndDate" value="" min="2010-01-01">
            </div>
            <div class="input-field">
              <label for="dateBDM">Date Received by BDM:</label>
              <input type="date" id="dateBDM" name="dateBDM" value="" min="2010-01-01">
            </div>
            <div class="input-field">
              <input name="edicion" type="hidden" id="edicion">
              <input name="idCotizacion" type="hidden" id="idCotizacion">
            </div>
          </div>
        </div>
        <hr style="width:100%;">
        <input type="submit" style="width: 30%; text-align: center;" value="Assign">
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
    	$(document).ready(function(){
    		// $('#lista1').val(1);
        var pathname = window.location.pathname;
        $('.nav > li > a[href="'+pathname+'"]').parent().addClass('active');

    		recargarLista();

    		$('#cliente').change(function(){
    			recargarLista();
    		});
        $('#idCotizacionCategoria').change(function(){
          recargarTipoCotizacion();
        });
        $('#idVolumen').change(function(){
          recargarTipoCotizacion();
        });

      <?php if (isset($_GET["id"])) { ?>
          editarCotizacion(<?php echo $_GET["id"]; ?>);
          $('#edicion').val(1);
          $('#idCotizacion').val(<?php echo $_GET["id"]; ?>);
          // $('#quoteID').css({"font-weight": "bold", "background-color": "AliceBlue"})
          // $('#quoteID').prop( "disabled", true );
          // $('#cliente').css({"font-weight": "bold", "background-color": "AliceBlue"})
          // $('#cliente').prop( "disabled", true );
          // $('#nombre').css({"font-weight": "bold", "background-color": "AliceBlue"})
          // $('#nombre').prop( "disabled", true );
      <?php } ?>

      $('#cerrar_alerta').click(function() {
          $('.alerta').removeClass('mostrar');
          $('.alerta').addClass('ocultar');
      });

      $("#form_empleados").submit(function(e) {
          e.preventDefault(); // avoid to execute the actual submit of the form.
          var form = $(this);
          var url = form.attr('action');
          $.ajax({
              type: "POST",
              url: url,
              data: form.serialize(), // serializes the form's elements.
              success: function(response) {
                  console.log(response);
                  switch (response) {
                      case "errorVacio":
                          mostrarAlerta('warning','Incomplete data. Please look for empty fields.');
                          // console.log(response);
                          break;
                      case "success":
                          location.replace("cotizacion_log.php")
                          // console.log(response);
                          break;
                      case "successEdit":
                          mostrarAlerta('success','Record Updated.');
                          // console.log(response);
                          // mostrarAlerta('success','Bienvenido');
                          break;
                      case "duplicatedName":
                          mostrarAlerta('warning','Record NOT completed, Selected NAME is already in Data Base.');
                          $('#nombre').focus();
                          break;
                      case "duplicatedID":
                          mostrarAlerta('warning','Record NOT completed, Selected ID is already in Data Base.');
                          $('#projectID').focus();
                          break;
                      case "errorDB":
                          mostrarAlerta('danger','DataBase Connection Error. Please try again later.');
                          break;
                      default:
                  }
              }
          });
      });
  })
    </script>
    <script type="text/javascript">
    	function recargarLista(){
    		$.ajax({
    			type:"POST",
    			url:"js/ajax.php",
          async: false,
    			data: {
            accion: 'actualizarClienteContacto',
            cliente: $('#cliente').val()
          },
    			success:function(result){
    				$('#clienteContacto').html(result);
    			}
    		});
    	}
      function recargarComplejidad(){
        $.ajax({
          type:"POST",
          url:"js/ajax.php",
          async: true,
          data: {
            accion: 'actualizarComplejidad',
            cliente: $('#idTipoCotizacion').val()
          },
          success:function(result){
            $('#idComplejidad').attr('value', result);
          }
        });
      }
      function recargarVolumen(){
        $.ajax({
          type:"POST",
          url:"js/ajax.php",
          async: true,
          data: {
            accion: 'actualizarVolumen',
            cliente: $('#idTipoCotizacion').val()
          },
          success:function(result){
            $('#idVolumen').attr('value', result);
          }
        });
      }
      function recargarTipoCotizacion(){
        $.ajax({
          type:"POST",
          url:"js/ajax.php",
          async: true,
          data: {
            accion: 'actualizarTipoCotizacion',
            categoria: $('#idCotizacionCategoria').val(),
            volumen: $('#idVolumen').val()
          },
          success:function(result){
            $('#idTipoCotizacion').attr('value', result);
            recargarHoras();
          }
        });
      }
      function recargarHoras(){
        $.ajax({
          type:"POST",
          url:"js/ajax.php",
          async: true,
          data: {
            accion: 'actualizarHoras',
            cliente: $('#idTipoCotizacion').val()
          },
          success:function(result){
            $('#idHoras').attr('value', result);
          }
        });
      }
      function editarCotizacion(id){
        $.ajax({
          type:"POST",
          url:"js/ajax.php",
          async: true,
          data: {
            accion: 'editarCotizacion',
            idCotizacion: id
          },
          success:function(response) {
            if (!response != "error") {
              var info = JSON.parse(response);
              // console.log(info);
                $('#tittle').html('Edit Quote');
                $('#quoteID').val(info.result.quoteID);
                $("#cliente option[value=" + info.result.idCliente + "]").attr('selected', 'selected');
                $('#nombre').val(info.result.nombre);
                $('#descripcion').val(info.result.descripcion);
                recargarLista();
                $("#clienteContacto option[value=" + info.result.idClienteContacto + "]").attr('selected', 'selected');
                $("#repreVentas option[value=" + info.result.idRepreVentas + "]").attr('selected', 'selected');
                $('#consolidatedEAU').val(info.result.consolidatedEAU);
                $('#ventasPotenciales').val(info.result.ventasPotenciales);
                $('#consolidatedEAU').val(info.result.consolidatedEAU);
                $('#idTipoCotizacion').val(info.result.idTipoCotizacion);
                $("#idCotizacionCategoria option[value=" + info.result.idCotizacionCategoria + "]").attr('selected', 'selected');
                $("#idVolumen option[value=" + info.result.idCotizacionVolumen + "]").attr('selected', 'selected');
                $("#responCotizaciones option[value=" + info.result.idResponsable + "]").attr('selected', 'selected');
                $('#ventasPotenciales').val(info.result.ventasPotenciales);
                $('#lineItems').val(info.result.lineItems);
                $('#uniqueFG').val(info.result.uniqueFG);
                $('#overallComplet').val(info.result.overallComplet);
                $("#status option[value=" + info.result.idStatus + "]").attr('selected', 'selected');
                $("#BOMType option[value=" + info.result.BOMType + "]").attr('selected', 'selected');
                $("#consOTC option[value=" + info.result.consOTC + "]").attr('selected', 'selected');
                $("#backIcon").attr("href", "cotizacion_detalle.php?id=" + id);
                $("#clienteIcon").attr("href", "cliente.php?id=" + id);
                $("#customerContactIcon").attr("href", "cliente_contacto.php?id=" + id);
                $("#categoriaIcon").attr("href", "tipo_proyecto.php?id=" + id);
                $("#complejidadIcon").attr("href", "tipo_proyecto.php?id=" + id);

                var fechaReqCliente;
                var fechaLanzamiento;
                var fechaInicio;
                var sourcMatStartDate;
                var sourcMatEndDate;
                var dateBDM;

                if (info.result.fechaReqCliente) {
                  fechaReqCliente = new Date(info.result.fechaReqCliente).toISOString().slice(0,10);
                }
                if (info.result.fechaLanzamiento) {
                  fechaLanzamiento = new Date(info.result.fechaLanzamiento).toISOString().slice(0,10);
                }
                if (info.result.fechaInicio) {
                  fechaInicio = new Date(info.result.fechaInicio).toISOString().slice(0,10);
                }
                if (info.result.sourcMatStartDate) {
                  sourcMatStartDate = new Date(info.result.sourcMatStartDate).toISOString().slice(0,10);
                }
                if (info.result.sourcMatEndDate) {
                  sourcMatEndDate = new Date(info.result.sourcMatEndDate).toISOString().slice(0,10);
                }
                if (info.result.dateBDM) {
                  dateBDM = new Date(info.result.dateBDM).toISOString().slice(0,10);
                }

                $('#fechaReqCliente').val(fechaReqCliente);
                $('#fechaLanzamiento').val(fechaLanzamiento);
                $('#fechaInicio').val(fechaInicio);
                $('#sourcMatStartDate').val(sourcMatStartDate);
                $('#sourcMatEndDate').val(sourcMatEndDate);
                $('#dateBDM').val(dateBDM);
                $('#notas').val(info.result.notas);

                recargarHoras();
            }
          },
          error: function(error) {
            console.log(error);
          }
        });
      }
    </script>

    <script src="js/funciones.js"></script>
    <?php include "inc/footer.html"; ?>
