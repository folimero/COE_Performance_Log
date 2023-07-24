<?php
  include "inc/conexion.php";
  include "inc/headerBoostrap.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(5, $_SESSION["permisos"]) && !in_array(6, $_SESSION["permisos"])) {
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

  // Funcion para llenar Selector de puesto de formulario
  $stmt = $dbh->prepare("SELECT nombre, idEtapa FROM etapa");
  $stmt->execute();
?>

<!DOCTYPE html>
    <div class="flex-container">
        <h1>Activities Settings</h1>
        <form id="form_actividades" action="actividad_registro.php" method="post">
            <div class="input-field">
              <!-- Selector de Etapa -->
              <label for="etapa">Stage</label>
              <div class="">
                  <div class="inline-container">
                      <select name="etapa">
                      <?php
                          while ($resultado = $stmt->fetch()) {
                              ?>
                          <option value="<?php echo $resultado->idEtapa; ?>">
                      <?php
                          echo $resultado->nombre; ?>
                          </option>
                      <?php
                          }
                      ?>
                      </select>
                      <!-- Boton de Selector -->
                      <div class="icon-container">
                          <a href="etapa.php">
                              <div class='plus-icon'></div>
                          </a>
                      </div>
                  </div>
              </div>
            </div>
            <div class="input-field">
              <!-- Selector de Tipo -->
              <label for="tipo">Type</label>
              <div class="">
                  <div class="inline-container">
                      <select name="tipo">
                          <option value="INPUT">INPUT</option>
                          <option value="OUTPUT">OUTPUT</option>
                      </select>
                  </div>
              </div>
            </div>
            <!-- Campo Actividad -->
            <div class="input-field">
              <label for="nombre">Activity</label>
              <input name="nombre" type="text" id="nombre" required>
            </div>
            <!-- Texto Descripcion -->
            <div class="input-field">
              <label for="descripcion">Description</label>
              <textarea id="descripcion" style="width: 100%;" name="descripcion" rows="4" cols="50" required></textarea>
            </div>
            <!-- Campo horasLow -->
            <div class="input-field">
              <label for="horasLow">Hours Low</label>
              <input name="horasLow" type="number" min="0" value="0" step=".01" required>
            </div>
            <!-- Campo horasMid -->
            <div class="input-field">
              <label for="horasMid">Hours Mid</label>
              <input name="horasMid" type="number" min="0" value="0" step=".01" required>
            </div>
            <!-- Campo horasLow -->
            <div class="input-field">
              <label for="horasHigh">Hours High</label>
              <input name="horasHigh" type="number" min="0" value="0" step=".01" required>
            </div>
            <!-- Boton Submit -->
            <input type="submit" value="Assign">
        </form>
    </div>

  <div class="flex-container">
    <table class="table">
      <thead>
        <!-- Encabezados de tabla -->
        <tr>
          <th>ID</th>
          <th>Stage</th>
          <th>Type</th>
          <th>Name</th>
          <th>Description</th>
          <th>Hrs Low</th>
          <th>Hrs Mid</th>
          <th>Hrs High</th>
          <th>Obsolete</th>
          <th>Actions</th>
        </tr>
      </thead>

  <?php

      $stmt = $dbh->prepare("SELECT idActividad, etapa.nombre AS enombre, tipo, actividad.nombre AS anombre, actividad.descripcion, horasLow, horasMid, horasHigh, obsoleta
                            FROM actividad
                            INNER JOIN etapa
                            ON actividad.idEtapa = etapa.idEtapa");
      $stmt->execute();

      // Se prepara la consulta para obtener las calificaciones del estudiante desde la BD
      // $stmt = $dbh->prepare("SELECT * FROM calificaciones WHERE matricula=:matricula");
      // $stmt->bindParam(':matricula', $_SESSION["matricula"]);
      // $stmt->execute();
      while ($resultado = $stmt->fetch()) {
          if ($resultado->obsoleta == 1) {
            echo "<tr id='" . $resultado->idActividad . "' class='table-danger'>";
          }else {
            echo "<tr id='" . $resultado->idActividad . "'>";
          }
          echo "<td>". $resultado->idActividad . "</td>";
          echo "<td>" . $resultado->enombre . "</td>";
          echo "<td>". $resultado->tipo . "</td>";
          echo "<td><a href='/actividad_detalle.php?id=" . $resultado->idActividad . "' style='a:visited {color:#00FF00}'>".$resultado->anombre."</td>";
          echo "<td>". $resultado->descripcion . "</td>";
          echo "<td><span class='editSpan horasLow'>" . $resultado->horasLow . "</span>";
          echo "<input class='editInput horasLow' type='number' min='1' step='1' name='horasLow' value='" . $resultado->horasLow . "' style='display: none;'></td>";
          echo "<td><span class='editSpan horasMid'>" . $resultado->horasMid . "</span>";
          echo "<input class='editInput horasMid' type='number' min='1' step='1' name='horasMid' value='" . $resultado->horasMid . "' style='display: none;'></td>";
          echo "<td><span class='editSpan horasHigh'>" . $resultado->horasHigh . "</span>";
          echo "<input class='editInput horasHigh' type='number' min='1' step='1' name='horasHigh' value='" . $resultado->horasHigh . "' style='display: none;'></td>";
          if ($resultado->obsoleta == 1) {
            echo "<td>YES</td>";
          }else {
            echo "<td>NO</td>";
          }
          echo "<td>
                    <div class='' style='display: flex; justify-content: space-evenly;'>
                        <a class='editBtn' href='#' onclick='editMode(this)'>
                            <div class='icon-container'>
                                <div class='plus-icon'></div>
                            </div>
                        </a>
                        <a class='guardarBtn' href='#' onclick='editarhorasLow(this)' style='display: none;'>
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
          var trObj = $(sender).closest("tr");
          $(sender).closest("tr").find(".editSpan").hide();
          $(sender).closest("tr").find(".editBtn").hide();
          $(sender).closest("tr").find(".deleteBtn").show();
          $(sender).closest("tr").find(".editInput").show();
          $(sender).closest("tr").find(".guardarBtn").show();

          trObj.find(".editInput.horasLow").val('');
          trObj.find(".editInput.horasMid").val('');
          trObj.find(".editInput.horasHigh").val('');

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

          trObj.find(".editInput.horasLow").val(trObj.find(".editSpan.horasLow").text());
          trObj.find(".editInput.horasMid").val(trObj.find(".editSpan.horasMid").text());
          trObj.find(".editInput.horasHigh").val(trObj.find(".editSpan.horasHigh").text());

          // mostrarAlerta('warning','Cancelado.');
      }

      function editarhorasLow(sender) {
          event.preventDefault();
          var trObj = $(sender).closest("tr");
          var idActividad = $(sender).closest("tr").attr('id');
          var horasLow = trObj.find(".editInput.horasLow").val();
          var horasMid = trObj.find(".editInput.horasMid").val();
          var horasHigh = trObj.find(".editInput.horasHigh").val();

          if (horasLow == "" || horasMid == "" || horasHigh == "") {
              mostrarAlerta('warning','Cannot Add empty Hours');
          }else if (horasLow < 0 || horasMid < 0 || horasHigh < 0) {
              mostrarAlerta('warning','Hours must be greather than 0');
          }else {
              // alert(notas);
              $.ajax({
                  type:'POST',
                  url:'js/ajax.php',
                  async: true,
                  data: {
                      accion: 'editarActividadHoras',
                      idActividad: idActividad,
                      horasLow: horasLow,
                      horasMid: horasMid,
                      horasHigh: horasHigh
                  },
                  // data: 'accion=editarEnsamble',
                  success:function(response) {
                      var info = JSON.parse(response);
                      console.log(info);
                      if(info.result) {
                          trObj.find(".editSpan.horasLow").text(info.result.horasLow);
                          trObj.find(".editInput.horasLow").text(info.result.horasLow);
                          trObj.find(".editSpan.horasMid").text(info.result.horasMid);
                          trObj.find(".editInput.horasMid").text(info.result.horasMid);
                          trObj.find(".editSpan.horasHigh").text(info.result.horasHigh);
                          trObj.find(".editInput.horasHigh").text(info.result.horasHigh);

                          trObj.find(".editInput").hide();
                          trObj.find(".guardarBtn").hide();
                          trObj.find(".deleteBtn").hide();
                          trObj.find(".editSpan").show();
                          trObj.find(".editBtn").show();
                          mostrarAlerta('success','Hours changed Successfully.');
                      } else {
                          alert(response.result);
                      }
                  },
                  error: function(error) {
                      console.log(error);
                  }
              });
          }
      }
  </script>

  <?php include "inc/footer.html"; ?>
