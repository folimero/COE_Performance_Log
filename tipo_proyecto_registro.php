<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(17, $_SESSION["permisos"]) && !in_array(18, $_SESSION["permisos"])) {
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
  $servicio = cleanInput($_POST['servicio']);
  $complejidad = cleanInput($_POST['complejidad']);
  $horas = cleanInput($_POST['horas']);

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT idTipoProyecto FROM tipoproyecto WHERE idProyectoCategoria=:categoria AND idProyectoServicio=:servicio AND idComplejidad=:complejidad");
      $stmt2->bindParam(':categoria', $categoria);
      $stmt2->bindParam(':servicio', $servicio);
      $stmt2->bindParam(':complejidad', $complejidad);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "REGISTRO NO COMPLETADO!. La combinacion seleccionada ya existe en la base de datos.";
          echo "<script>
                  alert('$message');
                  window.location.href='tipo_proyecto.php';
              </script>";
          die();
      }
      // Valida que ningun campo este vacio
      elseif (empty($categoria) || empty($complejidad) || empty($servicio)) {
          $message = "Incomplete data. Please look for empty fields.";
          echo "<script>
                  alert('$message');
                  window.location.href='tipo_proyecto.php';
              </script>";
          die();
      }
      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO tipoproyecto (idProyectoCategoria, idProyectoServicio, idComplejidad, horas) VALUES (?, ?, ?, ?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $categoria);
          $stmt->bindParam(2, $servicio);
          $stmt->bindParam(3, $complejidad);
          $stmt->bindParam(4, $horas);

          // Ejecutar la consulta preparada
          $stmt->execute();
          $message = "Record added successfully.";
          echo "<script>
                    alert('$message');
                    window.location.href='tipo_proyecto.php';
                </script>";
          die();
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
                    alert('$message');
                    window.location.href='tipo_proyecto.php';
                </script>";
      die();
  }
