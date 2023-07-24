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
  // Campos obtenidos en POST
  $cliente=cleanInput($_POST['cliente']);  $contacto=cleanInput($_POST['contacto']);
  if (isset($_POST['activo'])) {
      $activo=cleanInput($_POST['activo']);
  } else {
      $activo = 0;
  }
  if (isset($_POST['email'])) {
      $email=cleanInput($_POST['email']);
  } else {
      $email = NULL;
  }
  if (isset($_POST['phone'])) {
      $phone=cleanInput($_POST['phone']);
  } else {
      $phone = NULL;
  }

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT * FROM cliente_contacto WHERE nombre=:contacto");
      $stmt2->bindParam(':contacto', $contacto);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "REGISTRO NO COMPLETADO!. El contacto " . $contacto . " ya existe en la base de datos.";
          echo "<script>
                  alert('$message');
                  window.location.href='cliente_contacto.php';
              </script>";
      }
      // Valida que ningun campo este vacio
      elseif (empty($cliente) || empty($contacto)) {
          $message = "Incomplete data. Please look for empty fields.";
          echo "<script>
                  alert('$message');
                  window.location.href='cliente_contacto.php';
              </script>";
      }

      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO cliente_contacto (idCliente, nombre, email, telefono, activo) VALUES (?, ?, ?, ?, ?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $cliente);
          $stmt->bindParam(2, $contacto);
          $stmt->bindParam(3, $email);
          $stmt->bindParam(4, $phone);
          $stmt->bindParam(5, $activo);
          // Ejecutar la consulta preparada
          $stmt->execute();
          $message = "Record added successfully.";
          echo "<script>
                    alert('$message');
                    window.location.href='cliente_contacto.php';
                </script>";
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
              alert('$message');
              window.location.href='cliente_contacto.php';
          </script>";
      die();
  }
