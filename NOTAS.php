<?php
//cadena de conexion
mysql_connect("host","usuario","password");
// DEBO PREPARAR LOS TEXTOS QUE VOY A BUSCAR si la cadena existe
if ($busqueda <> ''){
  //CUENTA EL NUMERO DE PALABRAS
  $trozos = explode(" ",$busqueda);
  $numero = count($trozos);
  if ($numero==1) {
    //SI SOLO HAY UNA PALABRA DE BUSQUEDA SE ESTABLECE UNA INSTRUCION CON LIKE
    $cadbusca = "SELECT  REFERENCIA, TITULO FROM ARTICULOS WHERE VISIBLE =1
      AND DESARROLLO LIKE  '%$busqueda%' OR TITULO LIKE  '%$busqueda%' LIMIT 50";
  } elseif ($numero>1) {
    //SI HAY UNA FRASE SE UTILIZA EL ALGORTIMO DE BUSQUEDA AVANZADO DE MATCH AGAINST
    //busqueda de frases con mas de una palabra y un algoritmo especializado
    $cadbusca = "SELECT  REFERENCIA, TITULO, MATCH ( TITULO, DESARROLLO )
      AGAINST (  '$busqueda' ) AS Score FROM ARTICULOS WHERE
      MATCH ( TITULO, DESARROLLO ) AGAINST (  '$busqueda' ) ORDER  BY Score DESC LIMIT 50";
  }
  $result = mysql("teleformacion", $cadbusca);
  While($row = mysql_fetch_object($result))
  {
    //Mostramos los titulos de los articulos o lo que deseemos...
    $referencia = $row->REFERENCIA;
    $titulo = $row->TITULO;
    echo $referencia . " - " . $titulo . "<br>";
  }
}
?>



<div class="flex-container">
    <h1>Administracion de Proyectos</h1>
    <form id="form_empleados" action="proyecto_registro.php" method="post">
        <!-- Selector de Cliente -->
        <label for="cliente">Cliente</label>
        <div class="flex-container">
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
        <label for="nombre">Nombre</label>
        <input name="nombre" type="text" id="nombre" required>
        <label for="descripcion">Descripcion</label>
        <textarea id="descripcion" name="descripcion" rows="4" cols="50" required></textarea>
        <!-- Selector de Tipo -->
        <label for="tipo">Tipo Proyecto</label>
        <div class="flex-container">
            <div class="inline-container">
                <select name="tipo" required>
                    <?php
                    while ($resultado = $stmtTipo->fetch()) {
                    ?>
                    <option value="<?php echo $resultado->idTipo; ?>">
                    <?php
                    echo $resultado->nombre; ?>
                    </option>
                    <?php
                    }
                    ?>
                </select>
                <div class="flex-container" style="display: flex; justify-content: center;">
                    <a href="tipoProyecto.php">
                        <div class="plus-icon"></div>
                    </a>
                </div>
            </div>
        </div>
        <!-- Selector de Complejidad -->
        <label for="complejidad">Complejidad</label>
        <div class="flex-container">
            <div class="inline-container">
                <select name="complejidad" required>
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
                <div class="flex-container" style="display: flex; justify-content: center;">
                    <a href="complejidad.php">
                        <div class="plus-icon"></div>
                    </a>
                </div>
            </div>
        </div>

        <label for="cobrarA">Freight To</label>
        <select name="cobrarA" required>
            <option value="NAI">NAI</option>
            <option value="CLIENTE">Cliente</option>
        </select>
        <label for="ventasPotenciales">Ventas Potenciales</label>
        <input name="ventasPotenciales" type="number" min="0" value="0" step=".01" required>
        <label for="po">PO</label>
        <input name="po" type="text" id="po" required>
        <label for="qto">QO Number</label>
        <input name="qto" type="text" id="qto" required>
        <!-- Selector de Cuenta -->
        <label for="cuenta">Cuenta</label>
        <div class="flex-container">
            <div class="inline-container">
                <select name="cuenta">
                    <?php
                    while ($resultado = $stmtCuenta->fetch()) {
                    ?>
                    <option value="<?php echo $resultado->idCuenta; ?>">
                    <?php
                    echo $resultado->nombreCarrier . " " . $resultado->cuenta; ?>
                    </option>
                    <?php
                    }
                    ?>
                </select>
                <div class="flex-container" style="display: flex; justify-content: center;">
                    <a href="cuenta.php">
                        <div class="plus-icon"></div>
                    </a>
                </div>
            </div>
        </div>
        <label for="tracking">Tracking #</label>
        <input name="tracking" type="text" id="tracking">
        <!-- Selector de Responsable de Diseño -->
        <label for="empleadoDiseno">Responsable de Diseño</label>
        <div class="flex-container">
            <div class="inline-container">
                <select name="empleadoDiseno" required>
                    <?php
                    while ($resultado = $stmtResponsable->fetch()) {
                    ?>
                    <option value="<?php echo $resultado->idEmpleado; ?>">
                    <?php
                    echo $resultado->numEmpleado . " " . $resultado->nombre; ?>
                    </option>
                    <?php
                    }
                    ?>
                </select>
                <div class="flex-container" style="display: flex; justify-content: center;">
                    <a href="empleado.php">
                        <div class="plus-icon"></div>
                    </a>
                </div>
            </div>
        </div>
        <!-- Selector de Responsable de Manufactura -->
        <label for="empleadoManu">Responsable de Manufactura</label>
        <div class="flex-container">
            <div class="inline-container">
                <select name="empleadoManu" required>
                    <?php
                    while ($resultado = $stmtResponsable2->fetch()) {
                    ?>
                    <option value="<?php echo $resultado->idEmpleado; ?>">
                    <?php
                    echo $resultado->numEmpleado . " " . $resultado->nombre; ?>
                    </option>
                    <?php
                    }
                    ?>
                </select>
                <div class="flex-container" style="display: flex; justify-content: center;">
                    <a href="empleado.php">
                        <div class="plus-icon"></div>
                    </a>
                </div>
            </div>
        </div>
        <!-- Campos de Fecha -->
        <label for="olverLoad">Sobre Carga de Proyecto</label>
        <input name="olverLoad" type="number" min="0" max="1" value="0" step=".01" placeholder="%" >
        <label for="fechaReqCliente">Fecha Requerida por Cliente:</label>
        <input type="date" id="fechaReqCliente" name="fechaReqCliente" value="" min="2021-01-01">
        <label for="fechaPromesa">Fecha Promesa:</label>
        <input type="date" id="fechaPromesa" name="fechaPromesa" value="" min="2021-01-01">
        <label for="fechaTentativa">Fecha Tentativa:</label>
        <input type="date" id="fechaTentativa" name="fechaTentativa" value="" min="2021-01-01">
        <label for="fechaEmbarque">Fecha de Embarque:</label>
        <input type="date" id="fechaEmbarque" name="fechaEmbarque" value="" min="2021-01-01">
        <label for="fechaInicio">Fecha Inicio de Proyecto:</label>
        <input type="date" id="fechaInicio" name="fechaInicio" value="" min="2021-01-01">
        <label for="fechaTermino">Fecha Termino de Proyecto:</label>
        <input type="date" id="fechaTermino" name="fechaTermino" value="" min="2021-01-01">
        <!-- Campo Tiempo de Vida -->
        <label for="tiempoVida">Tiempo de Vida</label>
        <input name="tiempoVida" type="number" min="0" value="0" step=".01" required>
        <!-- Notas -->
        <label for="notas">Notas</label>
        <textarea id="notas" name="notas" rows="4" cols="50"></textarea>
        <!-- Selector de Status -->
        <label for="status">Status</label>
        <div class="flex-container">
            <div class="inline-container">
                <select name="status" required>
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
                <div class="flex-container" style="display: flex; justify-content: center;">
                    <a href="status.php">
                        <div class="plus-icon"></div>
                    </a>
                </div>
            </div>
        </div>
        <!-- Selector de Etapa -->
        <label for="etapa">Status</label>
        <div class="flex-container">
            <div class="inline-container">
                <select name="etapa" required>
                    <?php
                    while ($resultado = $stmtStage->fetch()) {
                    ?>
                    <option value="<?php echo $resultado->idEtapa; ?>">
                    <?php
                    echo $resultado->nombre; ?>
                    </option>
                    <?php
                    }
                    ?>
                </select>
                <div class="flex-container" style="display: flex; justify-content: center;">
                    <a href="etapa.php">
                        <div class="plus-icon"></div>
                    </a>
                </div>
            </div>
        </div>
        <!-- Selector de Usuario -->
        <label for="usuario">Usuario</label>
        <div class="flex-container">
            <div class="inline-container">
                <select name="usuario" required>
                    <?php
                    while ($resultado = $stmtUsuario->fetch()) {
                    ?>
                    <option value="<?php echo $resultado->idUsuario; ?>">
                    <?php
                    echo $resultado->nombre; ?>
                    </option>
                    <?php
                    }
                    ?>
                </select>
                <div class="flex-container" style="display: flex; justify-content: center;">
                    <a href="usuario.php">
                        <div class="plus-icon"></div>
                    </a>
                </div>
            </div>
        </div>
        <input type="submit" value="Registrar">
    </form>
</div>
