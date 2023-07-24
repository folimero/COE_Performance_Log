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
  $idProyecto;

  if (isset($_GET['idRecurso'])) {
      $id = cleanInput($_GET['id']);
      $idRecurso = cleanInput($_GET['idRecurso']);
      $idProyecto = cleanInput($_GET['idProyecto']);
      include "inc/conexion.php";

      $stmt = $dbh-> prepare("DELETE FROM recursos_asignados
                              WHERE idRecurso = $idRecurso");
        // Ejecutar la consulta preparada
        $stmt->execute();
  } elseif (isset($_GET['id'])) {
      $id = cleanInput($_GET['id']);
      $idProyecto = cleanInput($_GET['idProyecto']);
      include "inc/conexion.php";
  } elseif (isset($_POST['btnAsignarRecursos'])) {
      $id = cleanInput($_POST['id']);
      $idProyecto = cleanInput($_POST['idProyecto']);
      $recurso = cleanInput($_POST['recurso']);

      include "inc/conexion.php";
        // Se prueba la conexion
        if ($dbh!=null) {  //Se logró la conexión con la BD
            // Valida que ningun campo este vacio
            if (empty($recurso)) {
              $message = "Incomplete data. Please look for empty fields.";
              echo "<script>
                            alert('$message');
                        </script>";
            } else { //               ----------------     REGISTRO     -----------------------
              $stmt = $dbh-> prepare("INSERT INTO recursos_asignados (idActividades_proyecto, idEmpleado)
                                      VALUES (?, ?)");
                // Se asignan los valores a la consulta preparada
                $stmt->bindParam(1, $id);
                $stmt->bindParam(2, $recurso);

                // Ejecutar la consulta preparada
                $stmt->execute();
            }
        } else {
          $message = "DataBase Connection Error. Please try again later.";
          echo "<script>
                    alert('$message');
                </script>";
          die();
        }
  } else {
    header('Location: '.$URL);
    die();
  }
      $stmt = $dbh->prepare("SELECT idActividades_proyecto, actividad.nombre
                            FROM actividades_proyecto
                            INNER JOIN actividad
                            ON actividades_proyecto.idActividad = actividad.idActividad
                            WHERE idActividades_proyecto = $id");
      $stmt->execute();
      // Funcion para llenar Selector de actividades
      $stmt3 = $dbh->prepare("SELECT empleado.idEmpleado, empleado.nombre AS enombre, puesto.nombre AS pnombre
                              FROM empleado
                              INNER JOIN puesto
                              ON empleado.idPuesto = puesto.idPuesto
                              WHERE empleado.idEmpleado NOT IN (SELECT idEmpleado FROM recursos_asignados WHERE idActividades_proyecto = $id)");
      $stmt3->execute();
      $stmt4 = $dbh->prepare("SELECT idProyecto, nombre, descripcion
                            FROM proyecto
                            WHERE idProyecto = $id");
      $stmt4->execute();?>
      <!DOCTYPE html>
        <div class="flex-container">
          <h1>Actividad</h1>
          <a href='proyecto_actividades.php?id=<?php echo $idProyecto  ?>'>
              <div class='icon-container' style="margin: 20px 0px;">
                  <div class='back-icon-green'></div>
              </div>
          </a>
<?php
          while ($resultado = $stmt->fetch()) {
              ?>
              <div class="">
                <div class="input-field">
                  <label for="idActividad">Actividad ID</label>
                  <input name="idActividad" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->idActividades_proyecto; ?>" disabled>
                </div>
                <div class="input-field">
                  <label for="nombre">Nombre</label>
                  <input name="nombre" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->nombre; ?>"disabled>
                </div>
              </div>
<?php
          }
?>
          <hr style="width:30%;margin: 30px 0px; text-align:center;margin-left:0">
          <h1>Asignar Recursos</h1>

          <form id="form_empleados" action="proyecto_recursos.php" method="post">
            <input type="hidden" name='id' value='<?php echo $id; ?>' />
            <input type="hidden" name='idProyecto' value='<?php echo $idProyecto; ?>' />
              <!-- Selector en base a consulta BD -->
              <div class="input-field">
                <label for="recurso">Recursos</label>
                <div class="">
                  <div class="inline-container">
                    <select name="recurso" required>
                  <?php
                      while ($resultado = $stmt3->fetch()) {
                          ?>
                        <option value="<?php echo $resultado->idEmpleado; ?>">
                  <?php
                        echo $resultado->enombre . " - " . $resultado->pnombre; ?>
                        </option>
                  <?php
                      } ?>
                    </select>
                  </div>
                </div>
              </div>
              <!-- Boton Asignar Recursos -->
              <input name="btnAsignarRecursos" type="submit" value="Registrar">
          </form>
        </div>

        <div class="flex-container">
          <table>
            <thead>
              <!-- Encabezados de tabla -->
              <tr>
                <th>ID</th>
                <th>Empleado</th>
                <th>Puesto</th>
                <th>Acciones</th>
              </tr>
            </thead>

        <?php

        $stmt = $dbh->prepare("SELECT idRecurso, empleado.nombre AS enombre, puesto.nombre AS pnombre
                              FROM recursos_asignados
                              INNER JOIN empleado
                              ON recursos_asignados.idEmpleado = empleado.idEmpleado
                              INNER JOIN actividades_proyecto
                              ON recursos_asignados.idActividades_proyecto = actividades_proyecto.idActividades_proyecto
                              INNER JOIN puesto
                              ON empleado.idPuesto = puesto.idPuesto
                              WHERE actividades_proyecto.idActividades_proyecto = $id
                              GROUP BY empleado.nombre");
      $stmt->execute();

      // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
      // $stmt->execute();
      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idRecurso . "</td>";
          echo "<td>". $resultado->enombre . "</td>";
          echo "<td>". $resultado->pnombre . "</td>";
          echo "<td>
                  <a href='proyecto_recursos.php?id=" . $id . "&idRecurso=" . $resultado->idRecurso . "&idProyecto=" . $idProyecto ."'>
                  <div class='icon-container'>
                      <div class='cross-icon'></div>
                  </div>
                  </a>
                </td>";
          echo "</tr>";
      } ?>
          </table>
        </div>
