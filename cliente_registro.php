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
  // Funcion para limpiar campos
  function cleanInput($value) {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  $nombreCliente=cleanInput($_POST['nombreCliente']);  $comentarios=cleanInput($_POST['comentarios']);

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT nombreCliente FROM cliente WHERE nombreCliente=:nombreCliente");
      $stmt2->bindParam(':nombreCliente', $nombreCliente);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "REGISTRO NO COMPLETADO!. El cliente " . $nombreCliente . " ya existe en la base de datos.";
          echo "<script>
                  alert('$message');
                  window.location.href='cliente.php';
              </script>";
      }
      // Valida que ningun campo este vacio
      elseif (empty($nombreCliente)) {
          $message = "Incomplete data. Please look for empty fields.";
          echo "<script>
                  alert('$message');
                  window.location.href='cliente.php';
              </script>";
      }
      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO cliente (nombreCliente, comentarios) VALUES (?, ?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $nombreCliente);
          $stmt->bindParam(2, $comentarios);

          // Ejecutar la consulta preparada
          $stmt->execute();
          $message = "Record added successfully.";
          echo "<script>
                    alert('$message');
                    window.location.href='cliente.php';
                </script>";
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
                  alert('$message');
                  window.location.href='cliente.php';
              </script>";
      die();
  }
