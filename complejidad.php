<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(11, $_SESSION["permisos"]) && !in_array(12, $_SESSION["permisos"])) {
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
      <h1>Complexity Settings</h1>
      <form id="form_complejidad" action="complejidad_registro.php" method="post">
          <div class="input-field">
              <label for="nombreComplejidad">Complexity</label>
              <input name="nombreComplejidad" type="text" id="nombreComplejidad" required>
          </div>
          <div class="input-field">
              <label for="horas">Hours</label>
              <input name="horas" type="number" min="0" value="0" step=".01" required>
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
          <th>Complexity</th>
          <th>Hours</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idComplejidad, nombre, horas FROM complejidad");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idComplejidad . "</td>";
          echo "<td>". $resultado->nombre . "</td>";
          echo "<td>". $resultado->horas . "</td>";
          echo "</tr>";
      }
  ?>
    </table>
  </div>
  <?php include "inc/footer.html"; ?>
