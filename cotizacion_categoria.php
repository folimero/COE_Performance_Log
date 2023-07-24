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
  $stmt = $dbh->prepare("SELECT idComplejidad, nombre
                          FROM complejidad");
  $stmt->execute();
?>

<!DOCTYPE html>
  <div class="flex-container">
    <h1>Quoting Category Settings</h1>
    <a href='tipo_cotizacion.php'>
        <div class='icon-container' style="margin: 20px 0px;">
            <div class='back-icon-green'></div>
        </div>
    </a>
    <form id="form_tipoProyecto" action="cotizacion_categoria_registro.php" method="post">
        <div class="input-field">
            <label for="nombreCategoria">Category</label>
            <input name="nombreCategoria" type="text" id="nombreCategoria" required>
        </div>
        <div class="input-field">
            <label for="descripcion">Description</label>
            <textarea id="descripcion" style="width: 100%;" name="descripcion" rows="4" cols="50" required></textarea>
        </div>
        <!-- Selector Complejidad -->
        <div class="input-field">
            <label for="complejidad">Complexity</label>
            <div class="inline-container">
                <select name="complejidad" required>
                <?php
                    while ($resultado = $stmt->fetch()) {
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
          <th>Description</th>
          <th>Complexity</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idCotizacionCategoria, categoria, descripcion, complejidad.nombre
                              FROM cotizacion_categoria
                              INNER JOIN complejidad
                              ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idCotizacionCategoria . "</td>";
          echo "<td>". $resultado->categoria . "</td>";
          echo "<td>". $resultado->descripcion . "</td>";
          echo "<td>". $resultado->nombre . "</td>";
          echo "</tr>";
      }
  ?>
    </table>
  </div>
  <?php include "inc/footer.html"; ?>
