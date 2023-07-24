<?php
  include "../../inc/header.php";
  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(7, $_SESSION["permisos"]) && !in_array(8, $_SESSION["permisos"])) {
          $message = "Unauthorized User.";
          echo "<script>
                    alert('$message');
                    window.location.href='../../index.php';
                </script>";
          die();
      }
  } else {
      $message = "Please Log in.";
      echo "<script>
                alert('$message');
                window.location.href='../../login.php';
            </script>";
      die();
  }
  // Funcion para limpiar campos
  function cleanInput($value) {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  // Campos obtenidos en GET
  if (isset($_GET["isApplication"])) {
    $back = "proyecto_detalle_application";
  }else {
    $back = "proyecto_detalle_coe";
  }
  $URL = "index.php";
  $id;

  if (isset($_GET['idEnsamble'])) {
      $id = cleanInput($_GET['id']);
      $idEnsamble = cleanInput($_GET['idEnsamble']);
      include "../../inc/conexion.php";

      $stmt = $dbh-> prepare("DELETE FROM ensambles
                              WHERE idEnsamble = $idEnsamble");
      // Ejecutar la consulta preparada
      $stmt->execute();
  } elseif (isset($_GET['id'])) {
      $id = cleanInput($_GET['id']);
      include "../../inc/conexion.php";
  } elseif (isset($_POST['btnAsignarEnsambles'])) {
      $id = cleanInput($_POST['id']);
      $numParte = cleanInput($_POST['numParte']);
      $workorder = cleanInput($_POST['workorder']);
      $cantReq = cleanInput($_POST['cantReq']);
      $cantTerm = cleanInput($_POST['cantTerm']);
      $notas = cleanInput($_POST['notas']);

      include "../../inc/conexion.php";
      // Se prueba la conexion
        if ($dbh!=null) {  //Se logró la conexión con la BD
            // Valida que ningun campo este vacio
            if (empty($numParte)) {
                $message = "Incomplete data. Please look for empty fields.";
                echo "<script>
                          alert('$message');
                      </script>";
            } else { //               ----------------     REGISTRO     -----------------------
                $stmt = $dbh-> prepare("INSERT INTO ensambles (numParte, workorder, cantReq, cantTerm, notas, idProyecto)
                                      VALUES (?, ?, ?, ?, ?, ?)");
                // Se asignan los valores a la consulta preparada
                $stmt->bindParam(1, $numParte);
                $stmt->bindParam(2, $workorder);
                $stmt->bindParam(3, $cantReq);
                $stmt->bindParam(4, $cantTerm);
                $stmt->bindParam(5, $notas);
                $stmt->bindParam(6, $id);

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
?>
      <!DOCTYPE html>
        <div class="flex-container">
          <h1>Project</h1>
          <a href='<?php echo $back; ?>.php?id=<?php echo $id;  ?>'>
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
          <h1>Assemblies</h1>

          <form id="form_empleados" action="proyecto_ensambles.php<?php
              if (isset($_GET["isApplication"])) {
                  echo "?isApplication=1";
              }
          ?>" method="post">
            <input type="hidden" name='id' value='<?php echo $id; ?>' />
              <!-- Campo Num Parte -->
              <div class="input-field">
                <label for="numParte">Part #</label>
                <input type="text" id="numParte" name="numParte" required>
              </div>
              <!-- Campo WO -->
              <div class="input-field">
                <label for="workorder">Work Order</label>
                <input type="text" id="workorder" name="workorder">
              </div>
              <!-- Campo Cant Req -->
              <div class="input-field">
                <label for="cantReq">Req Qty</label>
                <input name="cantReq" type="number" min="0" value="0" step="1" required>
              </div>
              <!-- Campo Cant Terminada -->
              <div class="input-field">
                <label for="cantTerm">Done Qty</label>
                <input name="cantTerm" type="number" min="0" value="0" step="1" required>
              </div>
              <!-- Campo Notas -->
              <div class="input-field">
                <label for="notas">Notes</label>
                <textarea id="notas" style="width: 100%;" name="notas" rows="4" cols="50"></textarea>
              </div>
              <!-- Boton Asignar Registro -->
              <input name="btnAsignarEnsambles" type="submit" value="Assign">
        </div>

        <div class="flex-container">
          <table>
            <thead>
              <!-- Encabezados de tabla -->
              <tr>
                <th>ID</th>
                <th>Part #</th>
                <th>Work Order</th>
                <th>Req Qty</th>
                <th>Done Qty</th>
                <th>Notes</th>
                <th>Actions</th>
              </tr>
            </thead>

        <?php

        $stmt = $dbh->prepare("SELECT idEnsamble, numParte, workorder, cantReq, cantTerm, notas
                              FROM ensambles
                              WHERE idProyecto = $id");
      $stmt->execute();

      // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
      // $stmt->execute();
      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idEnsamble . "</td>";
          echo "<td>". $resultado->numParte . "</td>";
          echo "<td>". $resultado->workorder . "</td>";
          echo "<td>". $resultado->cantReq . "</td>";
          echo "<td>". $resultado->cantTerm . "</td>";
          echo "<td>". $resultado->notas . "</td>";
          echo "<td>
                  <a href='proyecto_ensambles.php?id=" . $id . "&idEnsamble=" . $resultado->idEnsamble;
          if (isset($_GET["isApplication"])) {
              echo "&isApplication=1'>";
          }else {
              echo "'>";
          }
          echo "<div class='icon-container'>
                    <div class='cross-icon'></div>
                </div>
                </a>
              </td>";
          echo "</tr>";
      } ?>
          </table>
        </div>

      <?php include "../../inc/footer.html"; ?>
