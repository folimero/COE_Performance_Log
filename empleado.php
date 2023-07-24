<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(19, $_SESSION["permisos"]) && !in_array(20, $_SESSION["permisos"])) {
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

  // Funcion para llenar Selector de puesto de formulario
  $stmt3 = $dbh->prepare("SELECT nombre, idPuesto FROM puesto");
  $stmt3->execute();
  $stmt2 = $dbh->prepare("SELECT idDepartamento, nombre FROM departamento");
  $stmt2->execute();
?>

<!DOCTYPE html>
  <div class="inline-container">
      <h1 style="margin-top: 20px; margin-right: 10px;">Employee</h1>
      <a href='#' onclick='altaEmpleado()'>
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
          <th>Department</th>
          <th>Position</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Active</th>
          <th>Assignable in Act</th>
          <th>Assignable as Project Leader</th>
          <th>Actions</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idEmpleado, numEmpleado, empleado.nombre AS enombre, puesto.nombre AS pnombre,
                              departamento.nombre AS dnombre, correo, celular, activo, asignableAct, asignableAsResp
                            FROM empleado
                            INNER JOIN departamento
                            ON empleado.idDepartamento = departamento.idDepartamento
                            INNER JOIN puesto
                            ON empleado.idPuesto = puesto.idPuesto
                            ORDER BY idEmpleado");
      $stmt->execute();

      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
      // $stmt->execute();
      while ($resultado = $stmt->fetch()) {
        // <tr data-id="345678">
          echo "<tr>";
          echo "<td>". $resultado->idEmpleado . "</td>";
          echo "<td>". $resultado->numEmpleado . "</td>";
          echo "<td>". $resultado->enombre . "</td>";
          echo "<td>". $resultado->dnombre . "</td>";
          echo "<td>". $resultado->pnombre . "</td>";
          echo "<td>". $resultado->correo . "</td>";
          echo "<td>". $resultado->celular . "</td>";

          if ($resultado->activo == 1) {
              echo "<td>YES</td>";
          } else {
              echo "<td>NO</td>";
          }
          if ($resultado->asignableAct == 1) {
              echo "<td>YES</td>";
          } else {
              echo "<td>NO</td>";
          }
          if ($resultado->asignableAsResp == 1) {
              echo "<td>YES</td>";
          } else {
              echo "<td>NO</td>";
          }

          echo "<td>
                    <div class='' style='display: flex; justify-content: space-evenly;'>
                        <div class='icon-container'>
                            <a href='empleado_capacidades.php?id=" . $resultado->idEmpleado . "'>
                                <div class='plus-icon'></div>
                            </a>
                        </div>
                        <div class='icon-container edit-employee' idEmpleado='" . $resultado->idEmpleado . "'>
                            <a href='#' onclick='editarEmpleado()'>
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
                  <h1 id="tittle">Employee Settings</h1>
                  <a class="btn-cerrar" onclick="cerrarModal()">
                      <div class='icon-container'>
                          <div class='cross-icon'></div>
                      </div>
                  </a>
                  <!-- Formulario -->
                  <form id="form_empleados" action="empleado_registro.php" method="post">
                      <!-- ID -->
                        <input type="hidden" name="idEmpleado" id="idEmpleado" value="">
                      <!-- Campo Empleado -->
                      <div class="input-field">
                          <label for="numEmpleado">Employee #</label>
                          <input name="numEmpleado" type="text" id="numEmpleado" required>
                      </div>
                      <!-- Campo Nombre -->
                      <div class="input-field">
                          <label for="nombre">Name</label>
                          <input name="nombre" type="text" id="nombre" required>
                      </div>
                      <!-- Selector departamento -->
                      <div class="input-field">
                          <label for="departamento">Department</label>
                          <div class="inline-container">
                              <select name="departamento" required>
                                  <?php
                                  while ($resultado = $stmt2->fetch()) {
                                      ?>
                                      <option value="<?php echo $resultado->idDepartamento; ?>">
                                          <?php
                                          echo $resultado->nombre; ?>
                                      </option>
                                      <?php
                                  }
                                  ?>
                              </select>
                              <!-- Boton de Selector -->
                              <div class="icon-container">
                                  <a href="departamento.php">
                                      <div class="plus-icon"></div>
                                  </a>
                              </div>
                          </div>
                      </div>
                      <!-- Selector Puesto -->
                      <div class="input-field">
                          <label for="puesto">Position</label>
                          <div class="inline-container">
                              <select name="puesto" required>
                                  <?php
                                  while ($resultado = $stmt3->fetch()) {
                                      ?>
                                      <option value="<?php echo $resultado->idPuesto; ?>">
                                          <?php
                                          echo $resultado->nombre; ?>
                                      </option>
                                      <?php
                                  }
                                  ?>
                              </select>
                              <!-- Boton de Selector -->
                              <div class="icon-container">
                                  <a href="puesto.php">
                                      <div class="plus-icon"   style="offset-position-right: 35px;"></div>
                                  </a>
                              </div>
                          </div>
                      </div>
                      <!-- Campo Correo -->
                      <div class="input-field">
                          <label for="correo">Email</label>
                          <input name="correo" type="email" id="correo">
                      </div>
                      <!-- Campo Celular -->
                      <div class="input-field">
                          <label for="celular">Phone</label>
                          <input name="celular" type="text" id="celular" minlength="10" maxlength="10">
                      </div>
                      <!-- Campo Checkbox Activo -->
                      <div class="input-field">
                          <div class="checkbox-container">
                              <input type="checkbox" id="activo" name="activo" value="1">
                              <label for="activo">Active</label><br>
                          </div>
                      </div>
                      <div class="input-field">
                          <div class="checkbox-container">
                              <input type="checkbox" id="asignable" name="asignable" value="1">
                              <label for="asignable">Assignable in project activities</label><br>
                          </div>
                      </div>
                      <div class="input-field">
                          <div class="checkbox-container">
                              <input type="checkbox" id="asignambleAsProjectLeader" name="asignambleAsProjectLeader" value="1">
                              <label for="asignambleAsProjectLeader">Assignable as Project Leader</label><br>
                          </div>
                      </div>
                      <!-- Button Submit -->
                      <input type="submit" id='btnEmpleado' value="Save">
                  </form>
              </div>
          </div>
      </div>


  <script src="js/funciones.js"></script>
  <script type="text/javascript">
      function editarEmpleado() {
            $('#tittle').html('Employee Edition');
            abrirModal();
      }
      function altaEmpleado() {
            $('#tittle').html('New Employee');
            abrirModal();
      }
  </script>

  <?php include "inc/footer.html"; ?>
