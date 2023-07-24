<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
    if (!in_array(29, $_SESSION["permisos"]) && !in_array(30, $_SESSION["permisos"])) {
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
    <h1>Department Settings</h1>
    <a href='empleado.php'>
        <div class='icon-container' style="margin: 20px 0px;">
            <div class='back-icon-green'></div>
        </div>
    </a>
    <form id="form_puesto" action="departamento_registro.php" method="post">
        <div class="input-field">
          <label for="nombre">Department</label>
          <input name="nombre" type="text" id="nombre" required>
        </div>
        <div class="input-field">
          <label for="descripcion">Description</label>
          <textarea id="descripcion" style="width: 100%;" name="descripcion" rows="4" cols="50" required></textarea>
        </div>
        <input type="submit" value="Registrar">
    </form>
  </div>

  <div class="flex-container">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idDepartamento, nombre, descripcion FROM departamento");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idDepartamento . "</td>";
          echo "<td>". $resultado->nombre . "</td>";
          echo "<td>". $resultado->descripcion . "</td>";
          echo "</tr>";
      }

  ?>
    </table>
  </div>
