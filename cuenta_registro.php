<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(9, $_SESSION["permisos"]) && !in_array(10, $_SESSION["permisos"])) {
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
  $cliente=cleanInput($_POST['cliente']);  
  $idCarrier=cleanInput($_POST['idCarrier']);
  $cuenta=cleanInput($_POST['cuenta']);

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT * FROM cuenta WHERE cuenta=:cuenta");
      $stmt2->bindParam(':cuenta', $cuenta);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "REGISTRO NO COMPLETADO!. La Cuenta " . $cuenta . " ya existe en la base de datos.";
          echo "<script>
                  alert('$message');
                  window.location.href='cuenta.php';
              </script>";
      }
      // Valida que ningun campo este vacio
      elseif (empty($cliente) || empty($idCarrier) || empty($cuenta)) {
          $message = "Incomplete data. Please look for empty fields.";
          echo "<script>
                  alert('$message');
                  window.location.href='cuenta.php';
              </script>";
      }

      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO cuenta (idCarrier, cuenta, idCliente) VALUES (?, ?, ?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $idCarrier);
          $stmt->bindParam(2, $cuenta);
          $stmt->bindParam(3, $cliente);

          // Ejecutar la consulta preparada
          $stmt->execute();
          $message = "Record added successfully.";
          echo "<script>
                    alert('$message');
                    window.location.href='cuenta.php';
                </script>";
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
              alert('$message');
              window.location.href='cuenta.php';
          </script>";
      die();
  }
