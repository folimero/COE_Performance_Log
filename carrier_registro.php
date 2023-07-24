<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(25, $_SESSION["permisos"]) && !in_array(26, $_SESSION["permisos"])) {
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
  $nombreCarrier=cleanInput($_POST['nombreCarrier']);

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT nombreCarrier FROM carrier WHERE nombreCarrier=:nombreCarrier");
      $stmt2->bindParam(':nombreCarrier', $nombreCarrier);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "REGISTRO NO COMPLETADO!. El Carrier " . $nombreCarrier . " ya existe en la base de datos.";
          echo "<script>
                    alert('$message');
                    window.location.href='carrier.php';
                </script>";
      }
      // Valida que ningun campo este vacio
      elseif (empty($nombreCarrier)) {
          $message = "Incomplete data. Please look for empty fields.";
          echo "<script>
                    alert('$message');
                    window.location.href='carrier.php';
                </script>";
      }
      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO carrier (nombreCarrier) VALUES (?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $nombreCarrier);

          // Ejecutar la consulta preparada
          $stmt->execute();
          $message = "Record added successfully.";
          echo "<script>
                    alert('$message');
                    window.location.href='carrier.php';
                </script>";
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
                alert('$message');
                window.location.href='login.php';
            </script>";
      die();
  }
