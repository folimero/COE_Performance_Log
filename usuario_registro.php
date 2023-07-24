<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(23, $_SESSION["permisos"]) && !in_array(24, $_SESSION["permisos"])) {
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
                window.location.href='index.php';
            </script>";
      die();
  }
  // Funcion para limpiar campos
  function cleanInput($value)
  {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  $idUsuario = cleanInput($_POST['idUsuario']);
  $usuario = cleanInput($_POST['usuario']);
  $contrasena = cleanInput($_POST['contrasena']);
  $empleado = cleanInput($_POST['empleado']);
  if (isset($_POST['activo'])) {
      $activo = 1;
  } else {
      $activo = 0;
  }
  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT * FROM usuario WHERE usuarioNombre=:usuario");
      $stmt2->bindParam(':usuario', $usuario);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      if (empty($idUsuario)) {
          // En caso de ya existir se muestra un error al usuario
          if ($result!=null) {
              $message = "REGISTRO NO COMPLETADO!. El usuario " . $usuario . " ya existe en la base de datos.";
              echo "<script>
                    alert('$message');
                    window.location.href='usuario.php';
                </script>";
              die();
          }
          // Valida que ningun campo este vacio
          elseif (empty($usuario) || empty($contrasena) || empty($empleado)) {
              $message = "Incomplete data. Please look for empty fields.";
              echo "<script>
                      alert('$message');
                      window.location.href='usuario.php';
                  </script>";
              die();
          }

          // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
          else {
              // Se realiza una consulta preparada
              $stmt = $dbh-> prepare("INSERT INTO usuario (usuarioNombre, contrasena, activo, idEmpleado) VALUES (?, ?, ?, ?)");
              // Se asignan los valores a la consulta preparada
              $cifrada = md5($contrasena);
              $stmt->bindParam(1, $usuario);
              $stmt->bindParam(2, $cifrada);
              $stmt->bindParam(3, $activo);
              $stmt->bindParam(4, $empleado);
              // Ejecutar la consulta preparada
              $stmt->execute();
              $message = "Record added successfully.";
              echo "<script>
                      alert('$message');
                      window.location.href='usuario.php';
                  </script>";
              die();
          }
      } else { // UPDATE RECORD <-----------
          if (empty($contrasena)) {
              $message = "Incomplete data. Please look for empty fields.";
              echo "<script>
                    alert('$message');
                    window.location.href='usuario.php';
                </script>";
          } else {
              $stmt = $dbh-> prepare("UPDATE usuario
                                      SET contrasena=:contrasena, activo=:activo
                                      WHERE idUsuario = :idUsuario");
              $cifrada = md5($contrasena);
              $stmt->bindParam(':contrasena', $cifrada);
              $stmt->bindParam(':activo', $activo);
              $stmt->bindParam(':idUsuario', $idUsuario);

              $result = $stmt->execute();
              if ($result == true) {
                  $message = "El registro se Actualizo con éxito.";
              } else {
                  $message = "Error en actualizacion.";
              }
              echo "<script>
                    alert('$message');
                    window.location.href='usuario.php';
                </script>";
          }
      }
      //Cierra conexión
      $dbh=null;
  } else {
      $message = "DataBase Connection Error. Please try again later.";
      echo "<script>
              alert('$message');
              window.location.href='usuario.php';
          </script>";
      die();
  }
