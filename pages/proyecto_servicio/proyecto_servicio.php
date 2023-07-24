<?php
  include "../../inc/conexion.php";
  include "../../inc/headerBoostrap.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(7, $_SESSION["permisos"]) && !in_array(7, $_SESSION["permisos"])) {
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
?>

<!DOCTYPE html>
  <div class="flex-container w-100">
      <div class="flex-container w-100 mt-3">
          <div class="card shadow p-3 bg-body rounded w-100 m-0 text-center">
              <div class="card-header bg-success text-center text-white fw-bold">
                  <h3>Service Settings</h3>
              </div>
              <div class="row w-100 bg-light">
                  <div class="col">
                      <div class='icon-container' style="margin: 20px 0px;">
                          <a href='../proyecto_alta/proyecto_alta_application.php' id="backIcon">
                              <div class='back-icon-green'></div>
                          </a>
                      </div>
                  </div>
              </div>
              <div class="text-center" style="align-self: center;">
                  <form class="" id="form_cliente" action="servicio_registro.php" method="post">
                      <div class="input-field">
                          <label for="servicio">ID SERVICIO</label>
                          <input name="servicio" type="text" id="servicio" required>
                      </div>
                      <div class="input-field">
                          <label for="descripcion">Detail</label>
                          <textarea id="descripcion" style="width: 100%;"name="descripcion" rows="4" cols="50"></textarea>
                      </div>
                      <input type="submit" value="Save">
                  </form>
              </div>
          </div>
      </div>
  </div>

  <div class="flex-container">
    <table>
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Service</th>
          <th>Detail</th>
          <th>Actions</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idProyectoServicio, servicio, descripcion FROM proyecto_servicio");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr id='" . $resultado->idProyectoServicio . "'>";
          echo "<td>". $resultado->idProyectoServicio . "</td>";
          echo "<td><span class='editSpan servicio'>" . $resultado->servicio . "</span>";
          echo "<input class='editInput servicio' type='text' name='servicio' value='" . $resultado->servicio . "' style='display: none;'></td>";
          echo "<td><span class='editSpan descripcion'>" . $resultado->descripcion . "</span>";
          echo "<input class='editInput descripcion' type='text' name='descripcion' value='" . $resultado->descripcion . "' style='display: none;'></td>";
          echo "<td>
                    <div class='' style='display: flex; justify-content: space-evenly;'>
                        <a class='editBtn' href='#' onclick='editMode(this)'>
                            <div class='icon-container'>
                                <div class='plus-icon-yellow'></div>
                            </div>
                        </a>
                        <a class='guardarBtn' href='#' onclick='editarServicio(this)' style='display: none;'>
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

  <script src="../../js/funciones.js"></script>
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

          trObj.find(".editInput.servicio").val(trObj.find(".editSpan.servicio").text());
          trObj.find(".editInput.descripcion").val(trObj.find(".editSpan.descripcion").text());
          // mostrarAlerta('warning','Cancelado.');
      }

      function editarServicio(sender) {
          event.preventDefault();
          var trObj = $(sender).closest("tr");
          var idProyectoServicio = $(sender).closest("tr").attr('id');
          var servicio =   trObj.find(".editInput.servicio").val();
          var descripcion = trObj.find(".editInput.descripcion").val();;
          // alert(notas);
          $.ajax({
              type:'POST',
              url:'../../js/ajax.php',
              async: true,
              data: {
                accion: 'editarServicio',
                idProyectoServicio: idProyectoServicio,
                servicio: servicio,
                descripcion: descripcion
              },
              // data: 'accion=editarEnsamble',
              success:function(response) {
                  if (response == 'DUPLICATED') {
                      mostrarAlerta('danger','Record not saved, selected service already exists!.');
                  } else {
                      var info = JSON.parse(response);
                      console.log(info);
                      if(info.result) {
                          trObj.find(".editSpan.servicio").text(info.result.servicio);
                          trObj.find(".editSpan.descripcion").text(info.result.descripcion);

                          trObj.find(".editInput.servicio").text(info.result.servicio);
                          trObj.find(".editInput.descripcion").text(info.result.ddescripcion);

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
  <?php include "../../inc/footer.html"; ?>
