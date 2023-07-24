<?php
  include "inc/conexion.php";
  include "inc/headerBoostrap.php";

  if (isset($_SESSION["usuarioNombre"])) {
    if (!in_array(17, $_SESSION["permisos"]) && !in_array(18, $_SESSION["permisos"])) {
        $message = "Unauthorized User.";
        echo "<script>
                  alert('$message');
                  window.location.href='index.php';
              </script>";
        die();
    }
  }else {
      $message = "Please Log in.";
      echo "<script>
                alert('$message');
                window.location.href='login.php';
            </script>";
      die();
  }
  $stmt = $dbh->prepare("SELECT idProyectoCategoria, categoria, descripcion
                          FROM proyecto_categoria");
  $stmt->execute();
  $stmtServicio = $dbh->prepare("SELECT idProyectoServicio, servicio, descripcion
                                FROM proyecto_servicio");
  $stmtServicio->execute();
  $stmt2 = $dbh->prepare("SELECT idComplejidad, nombre
                          FROM complejidad");
  $stmt2->execute();
?>

<!DOCTYPE html>
  <div class="flex-container">
      <div class="card shadow p-3 mb-4 bg-body rounded w-100 text-center">
          <div class="card-header bg-info bg-gradient bg-opacity-50 fw-bold">
              <h3>Project Type Settings</h3>
          </div>
          <div class="card-body d-flex justify-content-center">
            <?php if (isset($_GET['id'])) { ?>
                      <div class="icon-container" style="margin: 20px 0px;">
                          <a href='proyecto_alta.php?id=<?php echo $_GET['id'] ?>'>
                              <div class='back-icon-green'></div>
                          </a>
                      </div>
            <?php } ?>
            <form class="card text-center m-0 p-3" id="form_empleados" action="tipo_proyecto_registro.php" method="post">
                <!-- Selector Categoria -->
                <div class="input-field">
                  <label for="categoria">Category</label>
                  <div class="inline-container">
                      <select name="categoria" required>
                          <option disabled selected value> -- Select -- </option>
                      <?php
                          while ($resultado = $stmt->fetch()) {
                      ?>
                          <option value="<?php echo $resultado->idProyectoCategoria; ?>">
                      <?php
                          echo $resultado->categoria . " - " . $resultado->descripcion; ?>
                          </option>
                      <?php
                          }
                      ?>
                      </select>
                      <!-- Boton de Selector -->
                      <div class="icon-container">
                          <a href="proyecto_categoria.php">
                              <div class='plus-icon'></div>
                          </a>
                      </div>
                  </div>
                </div>
                <!-- Selector Servicio -->
                <div class="input-field">
                  <label for="servicio">Service</label>
                  <div class="inline-container">
                      <select name="servicio" required>
                          <option disabled selected value> -- Select -- </option>
                      <?php
                          while ($resultado = $stmtServicio->fetch()) {
                      ?>
                          <option value="<?php echo $resultado->idProyectoServicio; ?>">
                      <?php
                          echo $resultado->servicio . " - " . $resultado->descripcion; ?>
                          </option>
                      <?php
                          }
                      ?>
                      </select>
                      <!-- Boton de Selector -->
                      <div class="icon-container">
                          <a href="proyecto_servicio.php">
                              <div class='plus-icon'></div>
                          </a>
                      </div>
                  </div>
                </div>
                <!-- Selector Volumen -->
                <div class="input-field">
                  <label for="complejidad">Complexity</label>
                  <div class="inline-container">
                      <select name="complejidad" required>
                          <option disabled selected value> -- Select -- </option>
                      <?php
                          while ($resultado = $stmt2->fetch()) {
                      ?>
                          <option value="<?php echo $resultado->idComplejidad; ?>">
                      <?php
                          echo $resultado->nombre; ?>
                          </option>
                      <?php
                          }
                      ?>
                      </select>
                      <!-- Boton de Selector -->
                      <div class="icon-container">
                          <a href="complejidad.php">
                              <div class='plus-icon'></div>
                          </a>
                      </div>
                  </div>
                </div>
                <div class="input-field">
                  <label for="horas">Hours</label>
                  <input name="horas" type="number" min="0" value="0" step=".01" required>
                </div>

                <input type="submit" value="Registrar">
            </form>
          </div>
      </div>
  </div>

  <div class="card shadow p-3 mb-5 bg-body rounded w-100 text-center">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr class="card-header bg-info bg-gradient bg-opacity-50">
          <th>ID</th>
          <th>Category</th>
          <th>Service</th>
          <th>Complexity</th>
          <th>Hours</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idTipoProyecto, proyecto_categoria.categoria, proyecto_categoria.descripcion,
                                    complejidad.nombre AS complex, tipoproyecto.horas, proyecto_servicio.descripcion AS servicio
                              FROM tipoproyecto
                              INNER JOIN proyecto_categoria
                              ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                              INNER JOIN complejidad
                              ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                              LEFT JOIN proyecto_servicio
                              ON tipoproyecto.idProyectoServicio = proyecto_servicio.idProyectoServicio
                              ORDER BY idTipoProyecto, categoria, servicio");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idTipoProyecto . "</td>";
          echo "<td>[". $resultado->categoria . "] - " . $resultado->descripcion . "</td>";
          echo "<td>". $resultado->servicio . "</td>";
          echo "<td>". $resultado->complex . "</td>";
          echo "<td>". $resultado->horas . "</td>";
          echo "</tr>";
      }
  ?>
    </table>
  </div>
  <?php include "inc/footer.html"; ?>
