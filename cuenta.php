<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(9, $_SESSION["permisos"]) && !in_array(10, $_SESSION["permisos"])) {
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

  // Funcion para llenar Selector de cliente de formulario
  $stmt = $dbh->prepare("SELECT idCliente, nombreCliente FROM cliente");
  $stmt->execute();
  // Funcion para llenar Selector de carrier de formulario
  $stmt2 = $dbh->prepare("SELECT idCarrier, nombreCarrier FROM carrier");
  $stmt2->execute();

?>

<!DOCTYPE html>
    <div class="flex-container">
        <h1>Account Settings</h1>
        <?php if (isset($_GET['id'])) { ?>
                  <div class="icon-container" style="margin: 20px 0px;">
                      <a href='proyecto_alta.php?id=<?php echo $_GET['id'] ?>'>
                          <div class='back-icon-green'></div>
                      </a>
                  </div>
        <?php } ?>
        <form id="form_empleados" action="cuenta_registro.php" method="post">
            <div class="input-field">
              <!-- Selector de Cliente -->
              <label for="cliente">Customer</label>
                  <div class="inline-container">
                      <select name="cliente" required>
                      <?php
                          while ($resultado = $stmt->fetch()) {
                              ?>
                          <option value="<?php echo $resultado->idCliente; ?>">
                      <?php
                          echo $resultado->nombreCliente; ?>
                          </option>
                      <?php
                          }
                      ?>
                      </select>
                      <!-- Boton de Selector -->
                      <div class="icon-container">
                          <a href="cliente.php">
                              <div class="plus-icon"></div>
                          </a>
                      </div>
                  </div>
            </div>
            <div class="input-field">
                <!-- Selector de Carrier -->
                <label for="idCarrier">Carrier</label>
                    <div class="inline-container">
                        <select name="idCarrier" required>
                        <?php
                            while ($resultado = $stmt2->fetch()) {
                                ?>
                            <option value="<?php echo $resultado->idCarrier; ?>">
                        <?php
                            echo $resultado->idCarrier . " " . $resultado->nombreCarrier; ?>
                            </option>
                        <?php
                            }
                        ?>
                        </select>
                        <!-- Boton de Selector -->
                        <div class="icon-container">
                            <a href="carrier.php">
                                <div class="plus-icon"></div>
                            </a>
                        </div>
                    </div>
            </div>
            <div class="input-field">
                <!-- Campo Cuenta -->
                <label for="cuenta">Account</label>
                <input name="cuenta" type="text" id="cuenta" required>
            </div>
            <!-- Boton Submit -->
            <input type="submit" value="Save">
        </form>
    </div>
    <!-- Despliegue de Tabla -->
    <div class="flex-container">
        <table>
            <thead>
                <!-- Encabezados de tabla -->
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Carrier</th>
                    <th>Account</th>
                </tr>
            </thead>
        <?php
            $stmt = $dbh->prepare("SELECT idCuenta, nombreCliente, nombreCarrier, cuenta
                                  FROM cuenta
                                  INNER JOIN cliente
                                  ON cuenta.idCliente = cliente.idCliente
                                  INNER JOIN carrier
                                  ON cuenta.idCarrier = carrier.idCarrier");
            $stmt->execute();
            // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
            // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
            // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
            // $stmt->execute();
            while ($resultado = $stmt->fetch()) {
                echo "<tr>";
                echo "<td>". $resultado->idCuenta . "</td>";
                echo "<td>". $resultado->nombreCliente . "</td>";
                echo "<td>". $resultado->nombreCarrier . "</td>";
                echo "<td>". $resultado->cuenta . "</td>";
                echo "</tr>";
            }
        ?>
        </table>
    </div>
    <?php include "inc/footer.html"; ?>
