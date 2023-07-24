<?php
  include "inc/conexion.php";
  include "inc/header.php";

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
?>

<!DOCTYPE html>
  <div class="flex-container">
    <h1>Project Category Settings</h1>
    <a href='tipo_proyecto.php'>
        <div class='icon-container' style="margin: 20px 0px;">
            <div class='back-icon-green'></div>
        </div>
    </a>
    <form id="form_tipoProyecto" action="proyecto_categoria_registro.php" method="post">
        <div class="input-field">
          <label for="nombreCategoria">Category</label>
          <input name="nombreCategoria" type="text" id="nombreCategoria" required>
        </div>
        <div class="input-field">
          <label for="descripcion">Description</label>
          <input name="descripcion" type="text" id="descripcion" required>
        </div>
        <div class="input-field">
          <label for="scope">Scope</label>
          <textarea id="scope" style="width: 100%;" name="scope" rows="4" cols="50" required></textarea>
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
          <th>Category</th>
          <th>Description</th>
          <th>Scope</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idProyectoCategoria, categoria, descripcion, scope
                              FROM proyecto_categoria");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idProyectoCategoria . "</td>";
          echo "<td>". $resultado->categoria . "</td>";
          echo "<td>". $resultado->descripcion . "</td>";
          echo "<td>". $resultado->scope . "</td>";
          echo "</tr>";
      }
  ?>
    </table>
  </div>
  <?php include "inc/footer.html"; ?>
