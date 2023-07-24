<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(31, $_SESSION["permisos"]) && !in_array(32, $_SESSION["permisos"])) {
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
  $categoria = cleanInput($_POST['categoria']);
  $volumen = cleanInput($_POST['volumen']);
  $horas = cleanInput($_POST['horas']);

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT idTipoCotizacion FROM tipocotizacion WHERE idCotizacionCategoria=:categoria AND idCotizacionVolumen=:volumen");
      $stmt2->bindParam(':categoria', $categoria);
      $stmt2->bindParam(':volumen', $volumen);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "REGISTRO NO COMPLETADO!. La combinacion seleccionada ya existe en la base de datos.";
          echo "<script>
                  alert('$message');
                  window.location.href='tipo_cotizacion.php';
              </script>";
          die();
      }
      // Valida que ningun campo este vacio
      elseif (empty($categoria) || empty($volumen) || empty($horas)) {
          $message = "Incomplete data. Please look for empty fields.";
          echo "<script>
                  alert('$message');
                  window.location.href='tipo_cotizacion.php';
              </script>";
          die();
      }
      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO tipocotizacion (idCotizacionCategoria, idCotizacionVolumen, horas) VALUES (?, ?, ?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $categoria);
          $stmt->bindParam(2, $volumen);
          $stmt->bindParam(3, $horas);

          // Ejecutar la consulta preparada
          $stmt->execute();
          $message = "Record added successfully.";
          echo "<script>
                    alert('$message');
                    window.location.href='tipo_cotizacion.php';
                </script>";
          die();
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
                    alert('$message');
                    window.location.href='tipo_cotizacion.php';
                </script>";
      die();
  }
