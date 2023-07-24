<?php include "inc/headerBoostrap.php"; ?>

<!DOCTYPE html>
  <div class="flex-container">
    <h1>Log in</h1>
    <form id="form_login" action="" method="post" onsubmit="ingresarLogin();">
        <div class="input-field">
            <label for="usuario">User</label>
            <input name="usuario" type="text" id="usuario" placeholder="Type User" required>
        </div>
        <div class="input-field">
            <label for="contrasena">Password</label>
            <input name="contrasena" type="password" id="contrasena" placeholder="Type Password" required>
        </div>
        <input type="submit" value="Log in">
    </form>
  </div>

  <!-- VENTANAS MODALES -->
  <span class="alerta ocultar">
      <span class="msg">This is a warning</span>
          <span class='icon-container'>
              <div id="cerrar_alerta" class='cross-icon'></div>
          </span>
  </span>

  <script type="text/javascript">
      $(document).ready(function() {
          $('#cerrar_alerta').click(function() {
              $('.alerta').removeClass('mostrar');
              $('.alerta').addClass('ocultar');
          });
      });

      function ingresarLogin() {
          event.preventDefault();
          var usuario = $('#usuario').val();
          var contrasena = $('#contrasena').val();

          $.ajax({
              url: 'login_ingreso.php',
              type: 'POST',
              async: true,
              data: {
                  usuario: usuario,
                  contrasena: contrasena,
                  accion: 'cargarActividades',
              },
              success: function(response) {
                  // location.replace("index.php")
                  // console.log(response);
                  switch (response) {
                      case "errorLoggeo":
                          mostrarAlerta('danger','Wrong data, Please review User and Password.');
                          break;
                      case "errorDB":
                          mostrarAlerta('danger','DataBase Error, Please try again later.');
                          break;
                      case "success":
                          location.replace("perfil.php")
                          // mostrarAlerta('success','Bienvenido');
                          break;
                      default:

                  }
              },
              error: function(error) {
                console.log(error);
              }
          });
      }
      </script>
  <script src="js/funciones.js"></script>
<?php include "inc/footer.html"; ?>
