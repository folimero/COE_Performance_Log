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
  // Funcion para limpiar campos
  function cleanInput($value) {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  $tipo=cleanInput($_POST['tipo']);

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT tipo FROM cotizacion_archivo_tipo WHERE tipo=:tipo");
      $stmt2->bindParam(':tipo', $tipo);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "REGISTRO NO COMPLETADO!. El tipo " . $tipo . " ya existe en la base de datos.";
          echo "<script>
                  alert('$message');
                  window.location.href='cotizacion_tipo_archivo.php';
              </script>";
      }
      // Valida que ningun campo este vacio
      elseif (empty($tipo)) {
          $message = "Incomplete data. Please look for empty fields.";
          echo "<script>
                  alert('$message');
                  window.location.href='cotizacion_tipo_archivo.php';
              </script>";
      }
      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO cotizacion_archivo_tipo (tipo) VALUES (?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $tipo);

          // Ejecutar la consulta preparada
          $stmt->execute();
          $message = "Record added successfully.";
          echo "<script>
                    alert('$message');
                    window.location.href='cotizacion_tipo_archivo.php';
                </script>";
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
                  alert('$message');
                  window.location.href='cotizacion_tipo_archivo.php';
              </script>";
      die();
  }
