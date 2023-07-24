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
?>

<!DOCTYPE html>
  <div class="flex-container">
    <h1>Customer Settings</h1>
<?php if (isset($_GET['id'])) { ?>
          <div class="icon-container" style="margin: 20px 0px;">
              <a href='proyecto_alta.php?id=<?php echo $_GET['id'] ?>'>
                  <div class='back-icon-green'></div>
              </a>
          </div>
<?php } ?>
    <form id="form_cliente" action="cliente_registro.php" method="post">
        <div class="input-field">
            <label for="nombreCliente">Customer</label>
            <input name="nombreCliente" type="text" id="nombreCliente" required>
        </div>
        <div class="input-field">
            <label for="comentarios">Comments</label>
            <textarea id="comentarios" style="width: 100%;"name="comentarios" rows="4" cols="50"></textarea>
        </div>
        <input type="submit" value="Save">
    </form>
  </div>

  <div class="flex-container">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Comments</th>
          <th>Actions</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idCliente, nombreCliente, comentarios FROM cliente");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr id='" . $resultado->idCliente . "'>";
          echo "<td>". $resultado->idCliente . "</td>";
          echo "<td><span class='editSpan nombreCliente'>" . $resultado->nombreCliente . "</span>";
          echo "<input class='editInput nombreCliente' type='text' name='nombreCliente' value='" . $resultado->nombreCliente . "' style='display: none;'></td>";
          echo "<td><span class='editSpan comentarios'>" . $resultado->comentarios . "</span>";
          echo "<input class='editInput comentarios' type='text' name='comentarios' value='" . $resultado->comentarios . "' style='display: none;'></td>";
          echo "<td>
                    <div class='' style='display: flex; justify-content: space-evenly;'>
                        <a class='editBtn' href='#' onclick='editMode(this)'>
                            <div class='icon-container'>
                                <div class='plus-icon-yellow'></div>
                            </div>
                        </a>
                        <a class='guardarBtn' href='#' onclick='editarCliente(this)' style='display: none;'>
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

  <!-- VENTANAS MODALES -->
      <div class="back-modal">
          <div class="contenido-modal" style="height: 350px;">
          </div>
      </div>

      <span class="alerta ocultar">
          <span class="msg">This is a warning</span>
              <span class='icon-container'>
                  <div id="cerrar_alerta" class='cross-icon'></div>
              </span>
      </span>

  <script src="js/funciones.js"></script>
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

          trObj.find(".editInput.nombreCliente").val(trObj.find(".editSpan.nombreCliente").text());
          trObj.find(".editInput.comentarios").val(trObj.find(".editSpan.comentarios").text());
          // mostrarAlerta('warning','Cancelado.');
      }

      function editarCliente(sender) {
          event.preventDefault();
          var trObj = $(sender).closest("tr");
          var idCliente = $(sender).closest("tr").attr('id');
          var nombreCliente =   trObj.find(".editInput.nombreCliente").val();
          var comentarios = trObj.find(".editInput.comentarios").val();;
          // alert(notas);
          $.ajax({
              type:'POST',
              url:'js/ajax.php',
              async: true,
              data: {
                accion: 'editarCliente',
                idCliente: idCliente,
                nombreCliente: nombreCliente,
                comentarios: comentarios
              },
              // data: 'accion=editarEnsamble',
              success:function(response) {
                  if (response == 'DUPLICATED') {
                      mostrarAlerta('danger','Registro no completado, el nombre seleccionado ya existe en la BD.');
                  } else {
                      var info = JSON.parse(response);
                      console.log(info);
                      if(info.result) {
                          trObj.find(".editSpan.nombreCliente").text(info.result.nombreCliente);
                          trObj.find(".editSpan.comentarios").text(info.result.comentarios);

                          trObj.find(".editInput.nombreCliente").text(info.result.nombreCliente);
                          trObj.find(".editInput.comentarios").text(info.result.comentarios);

                          trObj.find(".editInput").hide();
                          trObj.find(".guardarBtn").hide();
                          trObj.find(".deleteBtn").hide();
                          trObj.find(".editSpan").show();
                          trObj.find(".editBtn").show();
                          mostrarAlerta('success','Changes made.');
                      } else {
                          alert(response.result);
                      }
                  }
              },
              error: function(error) {
                  console.log(error);
              }
          });
      }

  </script>
  <?php include "inc/footer.html"; ?>
