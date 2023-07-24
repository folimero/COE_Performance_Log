<?php
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
  // Funcion para limpiar campos
  function cleanInput($value)
  {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  // Campos obtenidos en GET
  $URL = "index.php";
  $id;
  $actividad;
  $idUsuario;
  $idPermiso;




  if (isset($_GET['idPrivilegios'])) {
      $id = cleanInput($_GET['id']);
      $idPrivilegios = cleanInput($_GET['idPrivilegios']);
      include "inc/conexion.php";

      $stmt = $dbh-> prepare("DELETE FROM privilegios
                              WHERE idPrivilegios = $idPrivilegios");
      // Ejecutar la consulta preparada
      $stmt->execute();
  } elseif (isset($_GET['id'])) {
      $id = cleanInput($_GET['id']);
      include "inc/conexion.php";
  } elseif (isset($_POST['btnAsignarPrivilegios'])) {
      if (isset($_POST['idPlantilla'])) {
          $id = cleanInput($_POST['id']);
          $idPlantilla = cleanInput($_POST['idPlantilla']);

          if (empty($id) || empty($idPlantilla)) {
              $message = "ERROR. No valid user found.";
              echo "<script>
                      alert('$message');
                  </script>";
          } else {
              include "inc/conexion.php";
              // Se prueba la conexion
              if ($dbh!=null) {  //Se logró la conexión con la BD
                  // Valida que ningun campo este vacio
                  if ($idPlantilla == 0) {
                      $message = "Cannot create record. Please select a template.";
                      echo "<script>
                              alert('$message');
                          </script>";
                  } else { //               ----------------     REGISTRO     -----------------------
                      switch ($idPlantilla) {
                        case 0:
                            $message = "Cannot create record. Please select a template.";
                            echo "<script>
                                    alert('$message');
                                </script>";
                            break;
                        case 1:
                            // PERMISOS DE ADMIN
                            $permisos = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32);
                            $datafields = array('idPermiso', 'idUsuario');

                            foreach ($permisos as &$value) {
                                $data[] = array('idPermiso' => $value, 'idUsuario' => $id);
                            }

                            function placeholders($text, $count=0, $separator=","){
                                $result = array();
                                if($count > 0){
                                    for($x=0; $x<$count; $x++){
                                        $result[] = $text;
                                    }
                                }
                                return implode($separator, $result);
                            }

                            $dbh->beginTransaction(); // also helps speed up your inserts.
                            $insert_values = array();
                            foreach($data as $d) {
                                $question_marks[] = '('  . placeholders('?', sizeof($d)) . ')';
                                $insert_values = array_merge($insert_values, array_values($d));
                            }

                            $sql = "INSERT INTO privilegios (" . implode(",", $datafields ) . ") VALUES " .
                                   implode(',', $question_marks);

                            $stmt = $dbh->prepare ($sql);
                            $stmt->execute($insert_values);
                            $dbh->commit();
                            break;
                        case 2:
                            // PERMISOS DE PROJECT MANAGER
                            $permisos = array(1,2,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,22,25,26,27,29,30,31,32);
                            $datafields = array('idPermiso', 'idUsuario');

                            foreach ($permisos as &$value) {
                                $data[] = array('idPermiso' => $value, 'idUsuario' => $id);
                            }

                            function placeholders($text, $count=0, $separator=","){
                                $result = array();
                                if($count > 0){
                                    for($x=0; $x<$count; $x++){
                                        $result[] = $text;
                                    }
                                }
                                return implode($separator, $result);
                            }

                            $dbh->beginTransaction(); // also helps speed up your inserts.
                            $insert_values = array();
                            foreach($data as $d) {
                                $question_marks[] = '('  . placeholders('?', sizeof($d)) . ')';
                                $insert_values = array_merge($insert_values, array_values($d));
                            }

                            $sql = "INSERT INTO privilegios (" . implode(",", $datafields ) . ") VALUES " .
                                   implode(',', $question_marks);

                            $stmt = $dbh->prepare ($sql);
                            $stmt->execute($insert_values);
                            $dbh->commit();
                            break;
                        case 3:
                            // PERMISOS DE ING. OPERACIONES
                            $permisos = array(3,4,6,8);
                            $datafields = array('idPermiso', 'idUsuario');

                            foreach ($permisos as &$value) {
                                $data[] = array('idPermiso' => $value, 'idUsuario' => $id);
                            }

                            function placeholders($text, $count=0, $separator=","){
                                $result = array();
                                if($count > 0){
                                    for($x=0; $x<$count; $x++){
                                        $result[] = $text;
                                    }
                                }
                                return implode($separator, $result);
                            }

                            $dbh->beginTransaction(); // also helps speed up your inserts.
                            $insert_values = array();
                            foreach($data as $d) {
                                $question_marks[] = '('  . placeholders('?', sizeof($d)) . ')';
                                $insert_values = array_merge($insert_values, array_values($d));
                            }

                            $sql = "INSERT INTO privilegios (" . implode(",", $datafields ) . ") VALUES " .
                                   implode(',', $question_marks);

                            $stmt = $dbh->prepare ($sql);
                            $stmt->execute($insert_values);
                            $dbh->commit();
                            break;
                        default:
                            // code...
                            break;
                      }
                  }
              } else {
                  $message = "Database Error. Please try again later.";
                  echo "<script>
                              alert('$message');
                          </script>";
              }
          }

      } else {
          $id = cleanInput($_POST['id']);
          $idPermiso = cleanInput($_POST['idPermiso']);

          include "inc/conexion.php";
          // Se prueba la conexion
          if ($dbh!=null) {  //Se logró la conexión con la BD
              // Valida que ningun campo este vacio
              if (empty($id) || empty($idPermiso)) {
                  $message = "Cannot create record. Please check for empty fields.";
                  echo "<script>
                          alert('$message');
                      </script>";
              } else { //               ----------------     REGISTRO     -----------------------
                  $stmt = $dbh-> prepare("INSERT INTO privilegios (idUsuario, idPermiso)
                                        VALUES (?, ?)");
                  // Se asignan los valores a la consulta preparada
                  $stmt->bindParam(1, $id);
                  $stmt->bindParam(2, $idPermiso);

                  // Ejecutar la consulta preparada
                  $stmt->execute();
              }
          } else {
              $message = "Database Connection Error. Please try again later.";
              echo "<script>
                          alert('$message');
                      </script>";
          }
      }
  } else {
      header('Location: '.$URL);
      die();
  }
      $stmt = $dbh->prepare("SELECT idUsuario, empleado.nombre AS enombre, puesto.nombre AS pnombre
                            FROM usuario
                            INNER JOIN empleado
                            ON usuario.idEmpleado = empleado.idEmpleado
                            INNER JOIN puesto
                            ON empleado.idPuesto = puesto.idPuesto
                            WHERE idUsuario = $id");
      $stmt->execute();
      $stmt2 = $dbh->prepare("SELECT idEmpleado, nombre FROM empleado");
      $stmt2->execute();
      // Funcion para llenar Selector de actividades
      $stmt3 = $dbh->prepare("SELECT idPermiso, nombre
                              FROM permiso
                              WHERE  idPermiso NOT IN (SELECT idPermiso FROM privilegios WHERE idUsuario = $id)");
      $stmt3->execute(); ?>
      <!DOCTYPE html>
        <div class="flex-container">
          <h1>User</h1>
          <a href='usuario.php'>
              <div class='icon-container' style="margin: 20px 0px;">
                  <div class='back-icon-green'></div>
              </div>
          </a>
<?php
          while ($resultado = $stmt->fetch()) {
              ?>
              <div class="">
                  <div class="input-field">
                      <label for="idUsuario">User ID</label>
                      <input name="idUsuario" type="text"  style="text-align:center; font-weight:bold; background-color: GhostWhite;"value="<?php echo $resultado->idUsuario; ?>" disabled>
                  </div>
                  <div class="input-field">
                      <label for="nombre">Employee</label>
                      <input name="nombre" type="text"  style="text-align:center; font-weight:bold; background-color: GhostWhite;" value="<?php echo $resultado->enombre; ?>"disabled>
                  </div>
                  <div class="input-field">
                      <label for="puesto">Position</label>
                      <input name="puesto" type="text"  style="text-align:center; font-weight:bold; background-color: GhostWhite;" value="<?php echo $resultado->pnombre; ?>"disabled>
                  </div>
              </div>
<?php
          } ?>
          <hr style="width:30%;margin: 30px 0px; text-align:center;margin-left:0">
          <h1>Rights</h1>
          <form id="form_empleados" action="usuario_privilegios.php" method="post">
            <input type="hidden" name='id' value='<?php echo $id; ?>' />
                <!-- Campo Checkbox para cambiar Permisos a modo Plantilla -->
                <div class="input-field">
                    <div class="checkbox-container">
                        <label for="plantilla">Template</label><br>
                        <input type="checkbox" id="chkPlantilla" name="plantilla" value="0" onclick="mostrarPlantilla()">
                    </div>
                </div>
                <div class="input-field"  id="selectPermiso" >
                  <!-- Lista Permisos -->
                  <label for="idPermiso">Right</label>
                  <div class="">
                    <div class="inline-container">
                      <select name="idPermiso" required>
                    <?php
                        while ($resultado = $stmt3->fetch()) {
                            ?>
                          <option value="<?php echo $resultado->idPermiso; ?>">
                    <?php
                          echo $resultado->nombre; ?>
                          </option>
                    <?php
                        } ?>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="input-field" id="SelectPlantilla" style="display: none">
                  <!-- Lista Permisos -->
                  <label for="idPlantilla">Template</label>
                  <div class="">
                    <div class="inline-container">
                      <select name="idPlantilla" id="idPlantilla" required>
                          <option value="0" selected disabled hidden>Select a template</option>
                          <option value="1">Administrator</option>
                          <option value="2">Project Manager</option>
                          <option value="3">Eng. User</option>
                      </select>
                    </div>
                  </div>
                </div>
              <input name="btnAsignarPrivilegios" type="submit" value="Assign">
          </form>
        </div>

        <div class="flex-container">
          <table>
            <thead>
              <!-- Encabezados de tabla -->
              <tr>
                <th>ID</th>
                <th>Right</th>
                <th>Description</th>
                <th>Actions</th>
              </tr>
            </thead>

        <?php

        $stmt = $dbh->prepare("SELECT permiso.idPermiso, privilegios.idPrivilegios, permiso.nombre, descripcion
                              FROM privilegios
                              INNER JOIN permiso
                              ON privilegios.idPermiso = permiso.idPermiso
                              WHERE idUsuario = $id");
      $stmt->execute();

      // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
      // $stmt->execute();
      while ($resultado = $stmt->fetch()) {
          echo "<tr>";
          echo "<td>". $resultado->idPermiso . "</td>";
          echo "<td>". $resultado->nombre . "</td>";
          echo "<td>". $resultado->descripcion . "</td>";
          echo "<td>
                  <div class='icon-container'>
                      <a href='usuario_privilegios.php?id=" . $id . "&idPrivilegios=" . $resultado->idPrivilegios . "'>
                          <div class='cross-icon'></div>
                      </a>
                  </div>
                </td>";
          echo "</tr>";
      } ?>
          </table>
        </div>

        <script type="text/javascript">
            function mostrarPlantilla() {
                // Elementos HTML
                var checkBox = document.getElementById("chkPlantilla");
                var SelectPlantilla = document.getElementById("SelectPlantilla");
                var selectPermiso = document.getElementById("selectPermiso");
                let idPlantilla = document.getElementById("idPlantilla");

                // Muestra campos basado en Checkbox
                if (checkBox.checked == true){
                    // text.style.display = "block";
                    SelectPlantilla.style.display = "block";
                    selectPermiso.style.display = "none";
                } else {
                    // text.style.display = "none";
                    SelectPlantilla.style.display = "none";
                    idPlantilla.value = 0;
                    selectPermiso.style.display = "block";
                }
            }
        </script>

        <?php include "inc/footer.html"; ?>
