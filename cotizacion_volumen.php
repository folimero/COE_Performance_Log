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
?>

<!DOCTYPE html>
  <div class="flex-container">
    <h1>Quoting Volume Settings</h1>
    <a href='tipo_cotizacion.php'>
        <div class='icon-container' style="margin: 20px 0px;">
            <div class='back-icon-green'></div>
        </div>
    </a>
    <form id="form_tipoProyecto" action="cotizacion_volumen_registro.php" method="post">
        <div class="input-field">
            <label for="nombre">Volume</label>
            <input name="nombre" type="text" id="nombre" required>
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
          <th>Volume</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idCotizacionVolumen, nombre
                              FROM cotizacion_volumen");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idCotizacionVolumen . "</td>";
          echo "<td>". $resultado->nombre . "</td>";
          echo "</tr>";
      }
  ?>
    </table>
  </div>
  <?php include "inc/footer.html"; ?>
