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
  }else {
      $message = "Please Log in.";
      echo "<script>
                alert('$message');
                window.location.href='login.php';
            </script>";
      die();
  }
  $stmt = $dbh->prepare("SELECT idCotizacionCategoria, categoria, descripcion
                          FROM cotizacion_categoria");
  $stmt->execute();
  $stmt2 = $dbh->prepare("SELECT idCotizacionVolumen, nombre
                          FROM cotizacion_volumen");
  $stmt2->execute();
?>

<!DOCTYPE html>
  <div class="flex-container">
    <h1>Quoting Type Settings</h1>
    <a href='cotizacion_alta.php'>
        <div class='icon-container' style="margin: 20px 0px;">
            <div class='back-icon-green'></div>
        </div>
    </a>
    <form id="form_empleados" action="tipo_cotizacion_registro.php" method="post">
        <!-- Selector Categoria -->
        <div class="input-field">
            <label for="categoria">Category</label>
            <div class="inline-container">
                <select name="categoria" required>
                <?php
                    while ($resultado = $stmt->fetch()) {
                ?>
                    <option value="<?php echo $resultado->idCotizacionCategoria; ?>">
                <?php
                    echo $resultado->categoria . " - " . $resultado->descripcion; ?>
                    </option>
                <?php
                    }
                ?>
                </select>
                <!-- Boton de Selector -->
                <div class="icon-container">
                    <a href="cotizacion_categoria.php">
                        <div class='plus-icon'></div>
                    </a>
                </div>
            </div>
        </div>
        <!-- Selector Volumen -->
        <div class="input-field">
            <label for="volumen">Volume</label>
            <div class="inline-container">
                <select name="volumen" required>
                <?php
                    while ($resultado = $stmt2->fetch()) {
                ?>
                    <option value="<?php echo $resultado->idCotizacionVolumen; ?>">
                <?php
                    echo $resultado->nombre; ?>
                    </option>
                <?php
                    }
                ?>
                </select>
                <!-- Boton de Selector -->
                <div class="icon-container">
                    <a href="cotizacion_volumen.php">
                        <div class='plus-icon'></div>
                    </a>
                </div>
            </div>
        </div>
        <div class="input-field">
            <label for="horas">Hours</label>
            <input name="horas" type="number" min="0" value="0" step=".01" required>
        </div>

        <input type="submit" value="Add">
    </form>
  </div>

  <div class="flex-container">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Category</th>
          <th>Complexity</th>
          <th>Description</th>
          <th>Volume</th>
          <th>Hours</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idTipoCotizacion, complejidad.nombre AS complex, cotizacion_categoria.categoria, cotizacion_categoria.descripcion,
                                    cotizacion_volumen.nombre, tipocotizacion.horas
                              FROM tipocotizacion
                              INNER JOIN cotizacion_categoria
                              ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
                              INNER JOIN complejidad
                              ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad
                              INNER JOIN cotizacion_volumen
                              ON tipocotizacion.idCotizacionVolumen = cotizacion_volumen.idCotizacionVolumen
                              ORDER BY categoria");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idTipoCotizacion . "</td>";
          echo "<td>". $resultado->categoria . "</td>";
          echo "<td>". $resultado->complex . "</td>";
          echo "<td>". $resultado->descripcion . "</td>";
          echo "<td>". $resultado->nombre . "</td>";
          echo "<td>". $resultado->horas . "</td>";
          echo "</tr>";
      }
  ?>
    </table>
  </div>
  <?php include "inc/footer.html"; ?>
