<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
    if (!in_array(13, $_SESSION["permisos"]) && !in_array(14, $_SESSION["permisos"])) {
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
?>

<!DOCTYPE html>
  <div class="flex-container">
    <h1>Status Settings</h1>
    <?php if (isset($_GET['id'])) { ?>
              <div class="icon-container" style="margin: 20px 0px;">
                  <a href='proyecto_detalle.php?id=<?php echo $_GET['id'] ?>'>
                      <div class='back-icon-green'></div>
                  </a>
              </div>
    <?php } ?>
    <form id="form_cliente" action="status_registro.php" method="post">
        <div class="input-field">
            <label for="nombre">Status</label>
            <input name="nombre" type="text" id="nombre" required>
        </div>
        <div class="input-field">
            <label for="descripcion">Description</label>
            <textarea id="descripcion" style="width: 100%;" name="descripcion" rows="4" cols="50" required></textarea>
        </div>
        <input type="submit" value="Assign">
    </form>
  </div>

  <div class="flex-container">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Status</th>
          <th>Description</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idStatus, nombre, descripcion FROM status");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idStatus . "</td>";
          echo "<td>". $resultado->nombre . "</td>";
          echo "<td>". $resultado->descripcion . "</td>";
          echo "</tr>";
      }
  ?>
    </table>
  </div>
  <?php include "inc/footer.html"; ?>
