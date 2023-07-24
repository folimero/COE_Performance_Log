<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(23, $_SESSION["permisos"]) && !in_array(24, $_SESSION["permisos"])) {
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
  <div class="inline-container">
      <h1 style="margin-top: 20px; margin-right: 10px;">User</h1>
      <a href='#' onclick="recargarLista()">
          <div class='icon-container'>
              <div class='plus-icon'></div>
          </div>
      </a>
  </div>

  <div class="flex-container">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Employee #</th>
          <th>Name</th>
          <th>User</th>
          <!-- <th>contraseña</th> -->
          <th>Active</th>
          <th>Actions</th>
        </tr>
      </thead>

  <?php

      $stmt2 = $dbh->prepare("SELECT idUsuario, usuarioNombre, contrasena, usuario.activo, nombre, numEmpleado FROM usuario
                             INNER JOIN empleado ON usuario.idEmpleado = empleado.idEmpleado");
      $stmt2->execute();

      // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
      // $stmt->execute();
      while ($resultado = $stmt2->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idUsuario . "</td>";
          echo "<td>". $resultado->numEmpleado . "</td>";
          echo "<td>". $resultado->nombre . "</td>";
          echo "<td>". $resultado->usuarioNombre . "</td>";
          // echo "<td>". $resultado->contrasena . "</td>";

          if ($resultado->activo == 1) {
              echo "<td>SI</td>";
          } else {
              echo "<td>NO</td>";
          }

          echo "<td>
                    <div class='' style='display: flex; justify-content: space-evenly;'>
                        <div class='icon-container'>
                            <a href='usuario_privilegios.php?id=" . $resultado->idUsuario . "'>
                                <div class='plus-icon'></div>
                            </a>
                        </div>
                        <div class='icon-container edit-user' idUsuario='" . $resultado->idUsuario . "'>
                            <a href='#' onclick='abrirModal()'>
                                <div class='plus-icon-yellow'></div>
                            </a>
                        </div>
                    </div>
                </td>";
          echo "</tr>";
      }

  ?>
    </table>
  </div>

  <!-- VENTANAS MODALES -->
      <div class="back-modal">
          <div class="contenido-modal">
              <div class="flex-container" style="margin-top: 60px;">
                  <!-- Titulo -->
                  <h1 id="tittle">User Admin</h1>
                  <a class="btn-cerrar" onclick="cerrarModal()">
                      <div class='icon-container'>
                          <div class='cross-icon'></div>
                      </div>
                  </a>
                  <!-- Formulario -->
                  <form id="form_empleados" action="usuario_registro.php" method="post">
                      <!-- ID -->
                      <input type="hidden" name="idUsuario" id="idUsuario" value="">
                      <!-- Campo Usuario -->
                      <div class="input-field">
                          <label for="usuario">User</label>
                          <input name="usuario" type="text" id="usuario" required>
                      </div>
                      <!-- Campo Contraseña -->
                      <div class="input-field">
                          <label for="contrasena">Password</label>
                          <input name="contrasena" type="password" id="contrasena" required>
                      </div>
                      <!-- Selector Empleado -->
                      <div class="input-field">
                          <label for="empleado">Employee</label>
                          <div class="inline-container">
                              <select name="empleado" id="empleado" required></select>
                              <!-- Boton de Selector -->
                              <div class="flex-container" style="display:flex; justify-content: center;">
                                  <a href="empleado.php">
                                      <div class='plus-icon' style="height: 40px; width: 40px;"></div>
                                  </a>
                              </div>
                          </div>
                      </div>
                      <!-- Campo Checkbox Activo -->
                      <div class="input-field">
                          <div class="checkbox-container">
                              <input type="checkbox" id="activo" name="activo" value="1">
                              <label for="activo">Active</label><br>
                          </div>
                      </div>
                      <!-- Button Submit -->
                      <input type="submit" id='btnModal' value="Register">
                  </form>
              </div>
          </div>
      </div>
  <script src="js/funciones.js"></script>

  <script type="text/javascript">
      function recargarLista(){
          $.ajax({
            type:"POST",
            url:"js/ajax.php",
            async: true,
            data: {
              accion: 'cargarUsuarios',
            },
            success:function(response){
                if (!response != "error") {
                  // console.log(response);
                  var info = JSON.parse(response);
                  // console.log(info);
                  var mySelect = document.getElementById("empleado");
                  var index = 0;
                  info.forEach((item, i) => {
                    // console.log(item);
                    var myOption = document.createElement("option");
                    myOption.text = item.numEmpleado + " - " + item.nombre;
                    myOption.value = item.idEmpleado;
                    mySelect.appendChild(myOption);
                    index++;
                  });
                }
            }
          });
          $('#tittle').html('New User');
          $('#btnModal').html('Register');          $
          abrirModal();
      }
  </script>

  <?php include "inc/footer.html"; ?>
