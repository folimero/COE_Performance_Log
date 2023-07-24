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
  $nombreCategoria=cleanInput($_POST['nombreCategoria']);
  $descripcion=cleanInput($_POST['descripcion']);
  $scope=cleanInput($_POST['scope']);

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT categoria FROM proyecto_categoria WHERE categoria=:nombreCategoria");
      $stmt2->bindParam(':nombreCategoria', $nombreCategoria);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "REGISTRO NO COMPLETADO!. La categoria " . $nombreCategoria . " ya existe en la base de datos.";
          echo "<script>
                  alert('$message');
                  window.location.href='proyecto_categoria.php';
              </script>";
          die();
      }
      // Valida que ningun campo este vacio
      elseif (empty($nombreCategoria) || empty($descripcion) || empty($scope)) {
          $message = "Incomplete data. Please look for empty fields.";
          echo "<script>
                  alert('$message');
                  window.location.href='proyecto_categoria.php';
              </script>";
          die();
      }
      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          // Se realiza una consulta preparada
          $stmt = $dbh-> prepare("INSERT INTO proyecto_categoria (categoria, descripcion, scope) VALUES (?, ?, ?)");
          // Se asignan los valores a la consulta preparada
          $stmt->bindParam(1, $nombreCategoria);
          $stmt->bindParam(2, $descripcion);
          $stmt->bindParam(3, $scope);

          // Ejecutar la consulta preparada
          $stmt->execute();
          $message = "Record added successfully.";
          echo "<script>
                    alert('$message');
                    window.location.href='proyecto_categoria.php';
                </script>";
          die();
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
                    alert('$message');
                    window.location.href='proyecto_categoria.php';
                </script>";
      die();
  }
