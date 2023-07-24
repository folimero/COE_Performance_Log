<?php
  include "inc/conexion.php";
  include "inc/header.php";

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
  // Funcion para limpiar campos
  function cleanInput($value) {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  // Campos obtenidos en POST
  $etapa=cleanInput($_POST['etapa']);  $tipo=cleanInput($_POST['tipo']);  $nombre=cleanInput($_POST['nombre']);
  $descripcion=cleanInput($_POST['descripcion']); $horasLow=cleanInput($_POST['horasLow']); $horasMid=cleanInput($_POST['horasMid']); $horasHigh=cleanInput($_POST['horasHigh']);

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT * FROM actividad WHERE nombre=:nombre");
      $stmt2->bindParam(':nombre', $nombre);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "Record NO completed. Activity " . $nombre . " already on Database.";
          echo "<script>
                    alert('$message');
                    window.location.href='actividad.php';
                </script>";
      }
      // Valida que ningun campo este vacio
      elseif (empty($etapa) || empty($tipo) || empty($nombre) || empty($descripcion)) {
          $message = "Incomplete data. Please look for empty fields.";
          echo "<script>
                    alert('$message');
                    window.location.href='actividad.php';
                </script>";
      }
      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO actividad (tipo, nombre, descripcion, horasLow, horasMid, horasHigh, idEtapa) VALUES (?, ?, ?, ?, ?, ?, ?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $tipo);
          $stmt->bindParam(2, $nombre);
          $stmt->bindParam(3, $descripcion);
          $stmt->bindParam(4, $horasLow);
          $stmt->bindParam(5, $horasMid);
          $stmt->bindParam(6, $horasHigh);
          $stmt->bindParam(7, $etapa);

          // Ejecutar la consulta preparada
          if ($stmt->execute()) {
              $message = "Record added successfully.";
          }else {
              $message = "An error ocurred.";
          }

          echo "<script>
                    alert('$message');
                    window.location.href='actividad.php';
                </script>";
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
                alert('$message');
                window.location.href='actividad.php';
            </script>";
      die();
  }
