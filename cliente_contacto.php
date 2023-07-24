<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(15, $_SESSION["permisos"]) && !in_array(16, $_SESSION["permisos"])) {
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
?>

<!DOCTYPE html>
    <div class="flex-container">
        <h1>Customer Contact Settings</h1>
        <a href='cotizacion_alta.php'>
            <div class='icon-container' style="margin: 20px 0px;">
                <div class='back-icon-green'></div>
            </div>
        </a>

        <form id="form_empleados" action="cliente_contacto_registro.php" method="post">
            <div class="input-field">
              <!-- Selector de Cliente -->
              <label for="cliente">Customer</label>
              <div class="">
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
            </div>
            <!-- Campo Contacto -->
            <div class="input-field">
              <label for="contacto">Contact</label>
              <input name="contacto" type="text" id="contacto" required>
            </div>
            <div class="input-field">
              <label for="email">Email</label>
              <input name="email" type="email" id="email">
            </div>
            <div class="input-field">
              <label for="phone">Phone</label>
              <input name="phone" type="text" id="phone">
            </div>
            <!-- Campo Checkbox Activo -->
            <div class="input-field">
                <div class="checkbox-container">
                    <input type="checkbox" id="activo" name="activo" value="1">
                    <label for="activo">Active</label><br>
                </div>
            </div>
            <!-- Boton Submit -->
            <input type="submit" value="Add">
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
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Active</th>
                    <th>Since</th>
                    <th>Actions</th>
                </tr>
            </thead>
        <?php
            $stmt = $dbh->prepare("SELECT idClienteContacto, cliente.nombreCliente, nombre, email, telefono, activo, DATE(cliente_contacto.fechaCrea) AS fechaCrea
                                  FROM cliente_contacto
                                  INNER JOIN cliente
                                  ON cliente_contacto.idCliente = cliente.idCliente");
            $stmt->execute();
            // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
            // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
            // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
            // $stmt->execute();
            while ($resultado = $stmt->fetch()) {
                echo "<tr id='" . $resultado->idClienteContacto . "'>";
                echo "<td>". $resultado->idClienteContacto . "</td>";
                echo "<td>". $resultado->nombreCliente . "</td>";
                echo "<td>". $resultado->nombre . "</td>";
                echo "<td><span class='editSpan email'>" . $resultado->email . "</span>";
                echo "<input class='editInput email' type='text' name='email' value='" . $resultado->email . "' style='display: none;'></td>";
                echo "<td><span class='editSpan phone'>" . $resultado->telefono . "</span>";
                echo "<input class='editInput phone' type='text' name='phone' value='" . $resultado->telefono . "' style='display: none;'></td>";
                echo "<td>". $resultado->activo . "</td>";
                echo "<td>". $resultado->fechaCrea . "</td>";
                echo "<td>
                          <div class='' style='display: flex; justify-content: space-evenly;'>
                              <a class='editBtn' href='#' onclick='editMode(this)'>
                                  <div class='icon-container'>
                                      <div class='plus-icon-yellow'></div>
                                  </div>
                              </a>
                              <a class='guardarBtn' href='#' onclick='editarInfoContacto(this)' style='display: none;'>
                                  <div class='icon-container'>
                                      <div class='plus-icon-green'></div>
                                  </div>
                              </a>
                              <a class='deleteBtn' href='#' onclick='cancel(this)' style='display: none;'>
                                  <div class='icon-container'>
                                      <div class='cross-icon'></div>
                                  </div>
                              </a>
                          </div>
                      </td>";
                echo "</tr>";
            }
        ?>
        </table>
    </div>

    <script type="text/javascript">
        function editMode(sender) {
            event.preventDefault();
            //hide edit span
            $(sender).closest("tr").find(".editSpan").hide();
            $(sender).closest("tr").find(".editBtn").hide();
            $(sender).closest("tr").find(".deleteBtn").show();
            $(sender).closest("tr").find(".editInput").show();
            $(sender).closest("tr").find(".guardarBtn").show();
            // $(this).closest("tr").find(".saveBtn").show();
        }

        function cancel(sender) {
            event.preventDefault();
            //hide edit span
            var trObj = $(sender).closest("tr");
            trObj.find(".editSpan").show();
            trObj.find(".editBtn").show();
            trObj.find(".deleteBtn").hide();
            trObj.find(".editInput").hide();
            trObj.find(".guardarBtn").hide();

            trObj.find(".editInput.numParte").val(trObj.find(".editSpan.numParte").text());
            trObj.find(".editInput.workorder").val(trObj.find(".editSpan.workorder").text());
            trObj.find(".editInput.cantReq").val(trObj.find(".editSpan.cantReq").text());
            trObj.find(".editInput.cantTerm").val(trObj.find(".editSpan.cantTerm").text());
            trObj.find(".editInput.notas").val(trObj.find(".editSpan.notas").text());
            // mostrarAlerta('warning','Cancelado.');
        }

        function editarInfoContacto(sender) {
            event.preventDefault();
            var trObj = $(sender).closest("tr");
            var idContacto = $(sender).closest("tr").attr('id');
            var email =   trObj.find(".editInput.email").val();
            var phone = trObj.find(".editInput.phone").val();;
            // alert(notas);
            $.ajax({
                type:'POST',
                url:'js/ajax.php',
                async: true,
                data: {
                  accion: 'editarInfoContacto',
                  idContacto: idContacto,
                  email: email,
                  telefono: phone
                },
                // data: 'accion=editarEnsamble',
                success:function(response) {
                    var info = JSON.parse(response);
                    console.log(info);
                    if(info.result) {
                        trObj.find(".editSpan.email").text(info.result.email);
                        trObj.find(".editSpan.phone").text(info.result.telefono);

                        trObj.find(".editInput.email").text(info.result.email);
                        trObj.find(".editInput.phone").text(info.result.telefono);

                        trObj.find(".editInput").hide();
                        trObj.find(".guardarBtn").hide();
                        trObj.find(".deleteBtn").hide();
                        trObj.find(".editSpan").show();
                        trObj.find(".editBtn").show();
                        mostrarAlerta('success','Changes made.');
                    } else {
                        alert(response.result);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
    </script>
    <?php include "inc/footer.html"; ?>
