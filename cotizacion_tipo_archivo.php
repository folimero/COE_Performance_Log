<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(31, $_SESSION["permisos"])) {
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
    <h1>Quote File Type</h1>
<?php if (isset($_GET['id'])) { ?>
          <div class="icon-container" style="margin: 20px 0px;">
              <a href='cotizacion_detalle.php?id=<?php echo $_GET['id'] ?>'>
                  <div class='back-icon-green'></div>
              </a>
          </div>
<?php } ?>
    <form id="form_cliente" action="cotizacion_tipo_archivo_registro.php" method="post">
        <div class="input-field">
            <label for="tipo">Type</label>
            <input name="tipo" type="text" id="tipo" required>
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
          <th>Type</th>
          <th>Actions</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idCotizacionArchivoTipo, tipo FROM cotizacion_archivo_tipo");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr id='" . $resultado->idCotizacionArchivoTipo . "'>";
          echo "<td>". $resultado->idCotizacionArchivoTipo . "</td>";
          echo "<td><span class='editSpan tipo'>" . $resultado->tipo . "</span>";
          echo "<input class='editInput tipo' type='text' name='tipo' value='" . $resultado->tipo . "' style='display: none;'></td>";

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

          trObj.find(".editInput.tipo").val(trObj.find(".editSpan.nombreCliente").text());
          // mostrarAlerta('warning','Cancelado.');
      }

      function editarCliente(sender) {
          event.preventDefault();
          var trObj = $(sender).closest("tr");
          var idTipoArchivo = $(sender).closest("tr").attr('id');
          var tipo =   trObj.find(".editInput.tipo").val();
          // alert(notas);
          $.ajax({
              type:'POST',
              url:'js/ajax.php',
              async: true,
              data: {
                // accion: 'editarCliente',
                accion: 'editarTipoArchivo',
                idTipoArchivo: idTipoArchivo,
                tipo: tipo
              },
              // data: 'accion=editarEnsamble',
              success:function(response) {
                  if (response == 'DUPLICATED') {
                      mostrarAlerta('danger','Registro no completado, el tipo seleccionado ya existe en la BD.');
                  } else {
                      var info = JSON.parse(response);
                      console.log(info);
                      if(info.result) {
                          trObj.find(".editSpan.tipo").text(info.result.tipo);
                          trObj.find(".editInput.tipo").text(info.result.tipo);

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
