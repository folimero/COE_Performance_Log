<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(25, $_SESSION["permisos"]) && !in_array(26, $_SESSION["permisos"])) {
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
    <h1>Carrier Settings</h1>
    <form id="form_cliente" action="carrier_registro.php" method="post">
        <label for="nombreCarrier">Carrier</label>
        <input name="nombreCarrier" type="text" id="nombreCarrier" required>
        <input type="submit" value="Save">
    </form>
  </div>

  <div class="flex-container">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Carrier</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idCarrier, nombreCarrier FROM carrier");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idCarrier . "</td>";
          echo "<td>". $resultado->nombreCarrier . "</td>";
          echo "</tr>";
      }
  ?>
    </table>
  </div>
  <?php include "inc/footer.html"; ?>
