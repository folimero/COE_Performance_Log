<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
    if (!in_array(1, $_SESSION["permisos"]) && !in_array(2, $_SESSION["permisos"])) {
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
  $nombre = cleanInput($_POST['nombre']);
  $descripcion = cleanInput($_POST['descripcion']);
  $responsabilidades = cleanInput($_POST['responsabilidades']);

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT nombre FROM puesto WHERE nombre=:nombre");
      $stmt2->bindParam(':nombre', $nombre);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
        $message = "REGISTRO NO COMPLETADO!. El puesto " . $nombre . " ya existe en la base de datos.";
        echo "<script>
                    alert('$message');
                    window.location.href='puesto.php';
                </script>";
        die();
      }
      // Valida que ningun campo este vacio
      elseif (empty($nombre) || empty($descripcion)) {
        $message = "Incomplete data. Please look for empty fields.";
        echo "<script>
                    alert('$message');
                    window.location.href='puesto.php';
                </script>";
        die();
      }
      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO puesto (nombre, descripcion, responsabilidades) VALUES (?, ?, ?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $nombre);
          $stmt->bindParam(2, $descripcion);
          $stmt->bindParam(3, $responsabilidades);

          // Ejecutar la consulta preparada
          $stmt->execute();
          $message = "Record added successfully.";
          echo "<script>
                      alert('$message');
                      window.location.href='empleado.php';
                  </script>";
          die();
      }
      //Cierra conexión
      $dbh=null;
      } else {
        $message = "DataBase Connection Error. Please try again later.";
        echo "<script>
                    alert('$message');
                    window.location.href='puesto.php';
                </script>";
        die();
      }
