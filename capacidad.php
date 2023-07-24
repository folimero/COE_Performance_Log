<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(3, $_SESSION["permisos"]) && !in_array(4, $_SESSION["permisos"])) {
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
    <h1>Capability Settings</h1>
    <form id="form_capacidad" action="capacidad_registro.php" method="post">
      <div class="input-field">
        <label for="nombreCapacidad">Capability</label>
        <input name="nombreCapacidad" type="text" id="nombreCapacidad" required>
      </div>
      <div class="input-field">
        <label for="descripcion">Description</label>
        <textarea id="descripcion" style="width: 100%" name="descripcion" rows="4" cols="50" required></textarea>
      </div>
        <input type="submit" value="Save">
    </form>
  </div>

  <div class="flex-container">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Capability</th>
          <th>Description</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idCapacidad, nombreCapacidad, descripcion FROM capacidad");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idCapacidad . "</td>";
          echo "<td>". $resultado->nombreCapacidad . "</td>";
          echo "<td>". $resultado->descripcion . "</td>";
          echo "</tr>";
      }
  ?>
    </table>
  </div>
  <?php include "inc/footer.html"; ?>
