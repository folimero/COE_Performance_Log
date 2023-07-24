<?php
  include "inc/header.php";
  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(7, $_SESSION["permisos"]) && !in_array(8, $_SESSION["permisos"])) {
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
  function cleanInput($value) {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  // Campos obtenidos en GET
  $URL = "index.php";
  $id;
  $capacidad;
  if (isset($_GET['idCapRequeridas'])) {
      $id = cleanInput($_GET['id']);
      $idCapRequeridas = cleanInput($_GET['idCapRequeridas']);
      include "inc/conexion.php";

      $stmt = $dbh-> prepare("DELETE FROM cap_requeridas
                              WHERE idCapRequeridas = $idCapRequeridas");
      // Ejecutar la consulta preparada
      $stmt->execute();
  } elseif (isset($_GET['id'])) {
      $id = cleanInput($_GET['id']);
      include "inc/conexion.php";
  } elseif (isset($_POST['btnAsignarCapacidades'])) {
      $id = cleanInput($_POST['id']);
      $capacidad = cleanInput($_POST['capacidad']);

      include "inc/conexion.php";
      // Se prueba la conexion
        if ($dbh!=null) {  //Se logró la conexión con la BD
            // Valida que ningun campo este vacio
            if (empty($capacidad)) {
                $message = "Incomplete data. Please look for empty fields.";
                echo "<script>
                        alert('$message');
                    </script>";
            } else { //               ----------------     REGISTRO     -----------------------
                $stmt = $dbh-> prepare("INSERT INTO cap_requeridas (idProyecto, idCapacidad)
                                      VALUES (?, ?)");
                // Se asignan los valores a la consulta preparada
                $stmt->bindParam(1, $id);
                $stmt->bindParam(2, $capacidad);

                // Ejecutar la consulta preparada
                $stmt->execute();
            }
        } else {
            $message = "DataBase Connection Error. Please try again later.";
            echo "<script>
                    alert('$message');
                </script>";
        }
  } else {
      header('Location: '.$URL);
      die();
  }
      $stmt = $dbh->prepare("SELECT idProyecto, nombre, descripcion
                            FROM proyecto
                            WHERE idProyecto = $id");
      $stmt->execute();
      // Funcion para llenar Selector de capacidades
      $stmt3 = $dbh->prepare("SELECT idCapacidad, nombreCapacidad
                            FROM   capacidad
                            WHERE  idCapacidad NOT IN (SELECT idCapacidad FROM cap_requeridas WHERE idProyecto = $id)");
      $stmt3->execute(); ?>
      <!DOCTYPE html>
        <div class="flex-container">
          <h1>Project</h1>
          <a href='proyecto_detalle.php?id=<?php echo $id  ?>'>
              <div class='icon-container' style="margin: 20px 0px;">
                  <div class='back-icon-green'></div>
              </div>
          </a>
<?php
          while ($resultado = $stmt->fetch()) {
              ?>
              <div class="">
                <div class="input-field">
                  <label for="idProyecto">Project ID</label>
                  <input name="idProyecto" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->idProyecto; ?>" disabled>
                </div>
                <div class="input-field">
                  <label for="nombre">Name</label>
                  <input name="nombre" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->nombre; ?>"disabled>
                </div>
                <div class="input-field">
                  <label for="descripcion">Description</label>
                  <input name="descripcion" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->descripcion; ?>"disabled>
                </div>
              </div>
<?php
          } ?>
          <hr style="width:30%;margin: 30px 0px; text-align:center;margin-left:0">
          <h1>Required Capabilities</h1>

          <form id="form_empleados" action="proyecto_capacidades.php" method="post">
            <input type="hidden" name='id' value='<?php echo $id; ?>' />
              <!-- Selector en base a consulta BD -->
              <div class="input-field">
                <label for="capacidad">Capabilities</label>
                <div class="">
                  <div class="inline-container">
                    <select name="capacidad" required>
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
              <input name="btnAsignarCapacidades" type="submit" value="Assign">
          </form>
        </div>

        <div class="flex-container">
          <table>
            <thead>
              <!-- Encabezados de tabla -->
              <tr>
                <th>ID</th>
                <th>Capabilities</th>
                <th>Actions</th>
              </tr>
            </thead>
        <?php

        $stmt = $dbh->prepare("SELECT nombreCapacidad, cap_requeridas.idCapRequeridas
                              FROM cap_requeridas
                              INNER JOIN capacidad
                              ON cap_requeridas.idCapacidad = capacidad.idCapacidad
                              WHERE idProyecto = $id");
      $stmt->execute();

      // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
      // $stmt->execute();
      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idCapRequeridas . "</td>";
          echo "<td>". $resultado->nombreCapacidad . "</td>";
          echo "<td>
                  <a href='proyecto_capacidades.php?id=" . $id . "&idCapRequeridas=" . $resultado->idCapRequeridas . "'>
                  <div class='icon-container'>
                      <div class='cross-icon'></div>
                  </div>
                  </a>
                </td>";
          echo "</tr>";
      } ?>
          </table>
        </div>

        <?php include "inc/footer.html"; ?>
