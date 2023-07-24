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
                  <h3>Requester Settings</h3>
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
                  <form class="" id="form_cliente" action="requester_registro.php" method="post">
                      <div class="input-field">
                          <label for="nombre">REQUESTER</label>
                          <input name="nombre" type="text" id="nombre" required>
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
          <th>Requester</th>
          <th>Actions</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idProyectoRequester, nombre FROM proyecto_requester");
      $stmt->execute();

      while ($resultado = $stmt->fetch()) {
          echo "<tr id='" . $resultado->idProyectoRequester . "'>";
          echo "<td>". $resultado->idProyectoRequester . "</td>";
          echo "<td><span class='editSpan nombre'>" . $resultado->nombre . "</span>";
          echo "<input class='editInput nombre' type='text' name='nombre' value='" . $resultado->nombre . "' style='display: none;'></td>";
          echo "<td>
                    <div class='' style='display: flex; justify-content: space-evenly;'>
                        <a class='editBtn' href='#' onclick='editMode(this)'>
                            <div class='icon-container'>
                                <div class='plus-icon-yellow'></div>
                            </div>
                        </a>
                        <a class='guardarBtn' href='#' onclick='editarRequester(this)' style='display: none;'>
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

          trObj.find(".editInput.nombre").val(trObj.find(".editSpan.nombre").text());
          // mostrarAlerta('warning','Cancelado.');
      }

      function editarRequester(sender) {
          event.preventDefault();
          var trObj = $(sender).closest("tr");
          var idProyectoRequester = $(sender).closest("tr").attr('id');
          var nombre =   trObj.find(".editInput.nombre").val();
          // alert(notas);
          $.ajax({
              type:'POST',
              url:'../../js/ajax.php',
              async: true,
              data: {
                accion: 'editarRequester',
                idProyectoRequester: idProyectoRequester,
                nombre: nombre
              },
              // data: 'accion=editarEnsamble',
              success:function(response) {
                  if (response == 'DUPLICATED') {
                      mostrarAlerta('danger','Record not saved, selected requester already exists!.');
                  } else {
                      var info = JSON.parse(response);
                      console.log(info);
                      if(info.result) {
                          trObj.find(".editSpan.nombre").text(info.result.nombre);

                          trObj.find(".editInput.nombre").text(info.result.nombre);

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
