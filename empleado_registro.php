<?php
  include "inc/conexion.php";
  include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(19, $_SESSION["permisos"]) && !in_array(20, $_SESSION["permisos"])) {
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
  $idEmpleado=cleanInput($_POST['idEmpleado']);
  $numEmpleado=cleanInput($_POST['numEmpleado']);
  $nombre=cleanInput($_POST['nombre']);
  $puesto=cleanInput($_POST['puesto']);
  $departamento=cleanInput($_POST['departamento']);
  $correo=cleanInput($_POST['correo']);
  $celular=cleanInput($_POST['celular']);
  $notas = "";
  if (isset($_POST['activo'])) {
      $activo = 1;
  } else {
      $activo = 0;
  }
  if (isset($_POST['asignable'])) {
      $asignable = 1;
  } else {
      $asignable = 0;
  }
  if (isset($_POST['asignambleAsProjectLeader'])) {
      $asignableAsResp = 1;
  } else {
      $asignableAsResp = 0;
  }

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se prepara una consulta para verificar si la matricula existe en la base de datos
      $stmt2 = $dbh-> prepare("SELECT * FROM empleado WHERE numEmpleado=:numEmpleado");
      $stmt2->bindParam(':numEmpleado', $numEmpleado);
      $stmt2->execute();
      $result = $stmt2->fetchAll();
      // En caso de ya existir se muestra un error al usuario
      if ($result!=null) {
          $message = "REGISTRO NO COMPLETADO!. El numero de empleado " . $numEmpleado . " ya existe en la base de datos.";
          echo "<script>
                  alert('$message');
                  window.location.href='empleado.php';
              </script>";
      }
      // En caso de que haya pasado todas las validaciones, valida si es modo edicion o creacion
      elseif (empty($idEmpleado)) {
          if (empty($numEmpleado) || empty($nombre) || empty($puesto) || empty($departamento)) {
              $message = "Incomplete data. Please look for empty fields.";
              echo "<script>
                      alert('$message');
                      window.location.href='empleado.php';
                  </script>";
          } else {
              // Se realiza una consulta preparada
              $stmt = $dbh-> prepare("INSERT INTO empleado (numEmpleado, nombre, idPuesto, correo, celular, activo, notas, idDepartamento, asignableAct, asignableAsResp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
              // Se asignan los valores a la consulta preparada
              $stmt->bindParam(1, $numEmpleado);
              $stmt->bindParam(2, $nombre);
              $stmt->bindParam(3, $puesto);
              $stmt->bindParam(4, $correo);
              $stmt->bindParam(5, $celular);
              $stmt->bindParam(6, $activo);
              $stmt->bindParam(7, $notas);
              $stmt->bindParam(8, $departamento);
              $stmt->bindParam(9, $asignable);
              $stmt->bindParam(10, $asignableAsResp);

              // Ejecutar la consulta preparada
              $stmt->execute();
              $message = "Record added successfully.";
              echo "<script>
                        alert('$message');
                        window.location.href='empleado.php';
                    </script>";
          }
      }
      else {
        if (empty($puesto) || empty($departamento)) {
            $message = "Incomplete data. Please look for empty fields.";
            echo "<script>
                    alert('$message');
                    window.location.href='empleado.php';
                </script>";
        } else {
          $stmt = $dbh-> prepare("UPDATE empleado
                                  SET idDepartamento=:idDepartamento, idPuesto=:idPuesto, correo=:correo, celular=:celular, activo=:activo, asignableAct=:asignable, asignableAsResp=:asignableAsResp
                                  WHERE idEmpleado = :idEmpleado");
          $stmt->bindParam(':idDepartamento', $departamento);
          $stmt->bindParam(':idPuesto', $puesto);
          $stmt->bindParam(':correo', $correo);
          $stmt->bindParam(':celular', $celular);
          $stmt->bindParam(':activo', $activo);
          $stmt->bindParam(':asignable', $asignable);
          $stmt->bindParam(':asignableAsResp', $asignableAsResp);
          $stmt->bindParam(':idEmpleado', $idEmpleado);

          $result = $stmt->execute();
          if ($result == TRUE) {
              $message = "El registro se Actualizo con éxito.";
          } else {
              $message = "Error en actualizacion.";
          }
          echo "<script>
                    alert('$message');
                    window.location.href='empleado.php';
                </script>";
        }
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
