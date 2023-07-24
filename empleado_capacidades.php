<?php
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
  // Funcion para limpiar campos
  function cleanInput($value)
  {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  // Campos obtenidos en GET
  $URL = "index.php";
  $id;
  $idCapacidad;

  if (isset($_GET['idCapacidades'])) {
      $id=cleanInput($_GET['id']);
      $idCapacidades=cleanInput($_GET['idCapacidades']);
      include "inc/conexion.php";

      $stmt = $dbh-> prepare("DELETE FROM capacidades
                              WHERE idCapacidades = $idCapacidades");
      // Ejecutar la consulta preparada
      $stmt->execute();
  } elseif (isset($_GET['id'])) {
      $id=cleanInput($_GET['id']);
      include "inc/conexion.php";
  } elseif (isset($_POST['btnAsignarCapacidades'])) {
      $id=cleanInput($_POST['id']);
      $idCapacidad=cleanInput($_POST['idCapacidad']);

      include "inc/conexion.php";
      // Se prueba la conexion
        if ($dbh!=null) {  //Se logró la conexión con la BD
            // Valida que ningun campo este vacio
            if (empty($id) || empty($idCapacidad)) {
                $message = "Incomplete data. Please look for empty fields.";
                echo "<script>
                        alert('$message');
                        window.location.href='login.php';
                    </script>";
            } else { //               ----------------     REGISTRO     -----------------------
                $stmt = $dbh-> prepare("INSERT INTO capacidades (idEmpleado, idCapacidad)
                                      VALUES (?, ?)");
                // Se asignan los valores a la consulta preparada
                $stmt->bindParam(1, $id);
                $stmt->bindParam(2, $idCapacidad);

                // Ejecutar la consulta preparada
                $stmt->execute();
            }
        } else {
            $message = "DataBase Connection Error. Please try again later.";
            echo "<script>
                    alert('$message');
                    window.location.href='login.php';
                </script>";
        }
  } else {
      $message = "No record found.";
      echo "<script>
                alert('$message');
                window.location.href='empleado.php';
            </script>";
      die();
  }
      try {
          $stmt = $dbh->prepare("SELECT idEmpleado, empleado.nombre AS enombre, puesto.nombre AS pnombre
                                FROM empleado
                                INNER JOIN puesto
                                ON empleado.idPuesto = puesto.idPuesto
                                WHERE idEmpleado = $id");
          $stmt->execute();
          $stmt2 = $dbh->prepare("SELECT idEmpleado, nombre FROM empleado");
          $stmt2->execute();
          // Funcion para llenar Selector de actividades
          $stmt3 = $dbh->prepare("SELECT idCapacidad, nombreCapacidad
                                  FROM capacidad
                                  WHERE  idCapacidad NOT IN (SELECT idCapacidad FROM capacidades WHERE idEmpleado = $id)");
          $stmt3->execute();
      } catch (\Exception $e) {
          alert("Error: $e");
      }

?>
      <!DOCTYPE html>
        <div class="flex-container">
          <h1>Employee</h1>
          <a href='empleado.php'>
              <div class='icon-container' style="margin: 20px 0px;">
                  <div class='back-icon-green'></div>
              </div>
          </a>
<?php
          while ($resultado = $stmt->fetch()) {
              ?>
              <div class="">
                  <div class="input-field">
                      <label for="idEmpleado">Employee ID</label>
                      <input name="idEmpleado" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->idEmpleado; ?>" disabled>
                  </div>
                  <div class="input-field">
                      <label for="nombre">Name</label>
                      <input name="nombre" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->enombre; ?>"disabled>
                  </div>
                  <div class="input-field">
                      <label for="puesto">Position</label>
                      <input name="puesto" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->pnombre; ?>"disabled>
                  </div>
              </div>
<?php
          } ?>
          <hr style="width:30%;margin: 30px 0px; text-align:center;margin-left:0">
          <h1>Capabilities</h1>
          <form id="form_empleados" action="empleado_capacidades.php" method="post">
              <div class="input-field">
                  <input type="hidden" name='id' value='<?php echo $id; ?>' />
                  <div class="input-field">
                      <!-- Lista Permisos -->
                      <label for="idCapacidad">Capability</label>
                      <div class="">
                        <div class="inline-container">
                          <select name="idCapacidad" required>
                        <?php
                            while ($resultado = $stmt3->fetch()) {
                                ?>
                              <option value="<?php echo $resultado->idCapacidad; ?>">
                        <?php
                              echo $resultado->nombreCapacidad; ?>
                              </option>
                        <?php
                            } ?>
                          </select>
                          <!-- Boton de Selector -->
                          <div class="icon-container">
                              <a href="capacidad.php">
                                  <div class="plus-icon"></div>
                              </a>
                          </div>
                        </div>
                      </div>
                  </div>
              </div>

              <input name="btnAsignarCapacidades" type="submit" value="Assign">
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
                <th>Actions</th>
              </tr>
            </thead>

        <?php

        $stmt = $dbh->prepare("SELECT capacidades.idCapacidades, nombreCapacidad, descripcion
                              FROM capacidades
                              INNER JOIN capacidad
                              ON capacidades.idCapacidad = capacidad.idCapacidad
                              WHERE idEmpleado = $id");
      $stmt->execute();
      //Cierra conexión
      $dbh=null;
      // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
      // $stmt->execute();
      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idCapacidades . "</td>";
          echo "<td>". $resultado->nombreCapacidad . "</td>";
          echo "<td>". $resultado->descripcion . "</td>";
          echo "<td>
                  <div class='icon-container'>
                      <a href='empleado_capacidades.php?id=" . $id . "&idCapacidades=" . $resultado->idCapacidades . "'>
                          <div class='cross-icon'></div>
                      </a>
                  </div>
                </td>";
          echo "</tr>";
      } ?>
          </table>
        </div>

      <?php include "inc/footer.html"; ?>
