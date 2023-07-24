<?php
  include "inc/conexion.php";
  session_start();
  // include "inc/header.php";

  if (isset($_SESSION["usuarioNombre"])) {
      if (!in_array(7, $_SESSION["permisos"]) && !in_array(8, $_SESSION["permisos"])) {
          $message = "Unauthorized User.";
          echo "<script>
                    alert('$message');
                    window.location.href='/index.php';
                </script>";
          die();
      }
  } else {
      $message = "Please Log in.";
      echo "<script>
                alert('$message');
                window.location.href='/login.php';
            </script>";
      die();
  }
  // Funcion para limpiar campos
  function cleanInput($value) {
      $value = preg_replace("/[\'\")$(;|`,<>]/", "", $value);
      return $value;
  }
  function clean($value) {
     // $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
     if (is_null($value)) {
        $value = 0;
     }elseif (is_numeric($value)) {
       return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT) / 100;
     }
     return $value;
     // return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
  }

  if(isset($_POST['idProyectoRequester'])){$idProyectoRequester = cleanInput($_POST['idProyectoRequester']);}
  if(isset($_POST['isApplication'])){$isApplication = cleanInput($_POST['isApplication']);}
  if(isset($_POST['projectID'])){$projectID = cleanInput($_POST['projectID']);}
  if(isset($_POST['cliente'])){$cliente = cleanInput($_POST['cliente']);}
  if(isset($_POST['nombre'])){$nombre = cleanInput($_POST['nombre']);}
  if(isset($_POST['descripcion'])){$descripcion = cleanInput($_POST['descripcion']);}
  if(isset($_POST['categoria'])){$categoria = cleanInput($_POST['categoria']);}
  if(isset($_POST['tipo'])){$tipo = cleanInput($_POST['tipo']);}
  if(isset($_POST['cobrarA'])){$cobrarA = cleanInput($_POST['cobrarA']);}
  if(isset($_POST['po'])){$po = cleanInput($_POST['po']);}
  if(isset($_POST['qto'])){$qto = cleanInput($_POST['qto']);}
  if(isset($_POST['appTrackID'])){$appTrackID = cleanInput($_POST['appTrackID']);}
  if(isset($_POST['cuenta'])){$cuenta = cleanInput($_POST['cuenta']);}
  if(isset($_POST['tracking'])){$tracking = cleanInput($_POST['tracking']);}
  if(isset($_POST['empleadoDiseno'])){$empleadoDiseno = cleanInput($_POST['empleadoDiseno']);}
  if(isset($_POST['empleadoManu'])){$empleadoManu = cleanInput($_POST['empleadoManu']);}
  if(isset($_POST['overLoad'])){$overLoad = cleanInput($_POST['overLoad']);}
  if(isset($_POST['fechaReqCliente'])){$fechaReqCliente = cleanInput($_POST['fechaReqCliente']);}
  if(isset($_POST['fechaPromesa'])){$fechaPromesa = cleanInput($_POST['fechaPromesa']);}
  if(isset($_POST['fechaTentativa'])){$fechaTentativa = cleanInput($_POST['fechaTentativa']);}
  if(isset($_POST['fechaEmbarque'])){$fechaEmbarque = cleanInput($_POST['fechaEmbarque']);}
  if(isset($_POST['fechaInicio'])){$fechaInicio = cleanInput($_POST['fechaInicio']);}
  if(isset($_POST['fechaTermino'])){$fechaTermino = cleanInput($_POST['fechaTermino']);}
  if(isset($_POST['tiempoVida'])){$tiempoVida = cleanInput($_POST['tiempoVida']);}
  if(isset($_POST['repreVentas'])){$repreVentas = cleanInput($_POST['repreVentas']);}
  if(isset($_POST['notas'])){$notas = cleanInput($_POST['notas']);}

  if(isset($_POST['idProjectLeader'])){$idProjectLeader = cleanInput($_POST['idProjectLeader']);}
  if(isset($_POST['idProjectManager'])){$idProjectManager = cleanInput($_POST['idProjectManager']);}
  if(isset($_POST['idProjectCoordinator'])){$idProjectCoordinator = cleanInput($_POST['idProjectCoordinator']);}
  if(isset($_POST['idQAEngineer'])){$idQAEngineer = cleanInput($_POST['idQAEngineer']);}

  if(isset($_POST['ventasPotenciales'])) {

          $ventasPotenciales = clean(cleanInput($_POST['ventasPotenciales']));

  }

  $status = 1;
  // if(isset($_POST['status'])){$status = cleanInput($_POST['status']);}
  // if(isset($_POST['etapa'])){$etapa = cleanInput($_POST['etapa']);}

  if (isset($_POST['pdp'])) {
      $pdp = 1;
  } else {
      $pdp = 0;
  }
  if(isset($_SESSION['idUsuario'])){$usuario = cleanInput($_SESSION['idUsuario']);}

  switch ($categoria) {
      case 1:
          $etapa = 1;
          break;
      case 2:
      case 3:
      case 4:
          $etapa = 1;
          break;
      case 5:
          $etapa = 1;
          break;
      case 6:
          $etapa = 1;
          break;
      case 7:
          $etapa = 1;
          break;
      default:
          $etapa = 1;
          break;
  }

if (!isset($_POST["isApplication"])) {
    switch (true) {
        case ($ventasPotenciales <= 249000):
            $prioridad = 3;
            break;
        case ($ventasPotenciales <= 500000):
            $prioridad = 2;
            break;
        case ($ventasPotenciales > 500000):
            $prioridad = 1;
            break;
        default:
            $prioridad = 0;
            break;
    }
}

if (empty($projectID)) {
    $projectID = assignProjectId($tipo);
}

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // En caso de que haya pasado todas las validaciones, se procede a registrar o editar
      if ($_POST['edicion'] == 1)  { // -------------------------------------------------------------------- EDICION DE REGISTRO

          if(isset($_POST['idProyecto'])){$idProyecto = cleanInput($_POST['idProyecto']);}
          // Valida que ningun campo este vacio
          if (empty($tipo) || empty($status) || empty($etapa) || empty($usuario)) {
              // $message = "Incomplete data. Please look for empty fields.";
              echo "errorVacio";
              die();
          } else {

                if (isSameType($idProyecto, $tipo) == false) {
                    $projectID = assignProjectId($tipo);
                }

                // EDICION APPLICATION
                if ($isApplication == 1){
                    $stmt = $dbh-> prepare("UPDATE proyecto SET projectID=:projectID, descripcion=:descripcion, idTipoProyecto=:idTipoProyecto, cobrarA=:cobrarA, ventasPotenciales=:ventasPotenciales,
                    PO=:PO, qtoNumber=:qtoNumber, idCuenta=:idCuenta, tracking=:tracking, idRespDiseno=:idRespDiseno, idRespManu=:idRespManu, sobreCarga=:sobreCarga,
                    fechaReqCliente=:fechaReqCliente, fechaPromesa=:fechaPromesa, fechaTentativa=:fechaTentativa, fechaEmbarque=:fechaEmbarque, fechaInicio=:fechaInicio,
                    fechaTermino=:fechaTermino, notas=:notas, appTrackID=:appTrackID, idRepreVentas=:repreVentas,
                    idLiderProyecto=:idLiderProyecto, idGerenteProyecto=:idGerenteProyecto, idCoordinadorProyecto=:idCoordinadorProyecto, idIngenieroQA=:idIngenieroQA,
                    idProyectoRequester=:idProyectoRequester
                    WHERE idProyecto = $idProyecto");
                } else {
                    $stmt = $dbh-> prepare("UPDATE proyecto SET projectID=:projectID, nombre=:nombre, descripcion=:descripcion, idTipoProyecto=:idTipoProyecto, cobrarA=:cobrarA, ventasPotenciales=:ventasPotenciales,
                    PO=:PO, qtoNumber=:qtoNumber, idCuenta=:idCuenta, tracking=:tracking, idRespDiseno=:idRespDiseno, idRespManu=:idRespManu, sobreCarga=:sobreCarga,
                    fechaReqCliente=:fechaReqCliente, fechaPromesa=:fechaPromesa, fechaTentativa=:fechaTentativa, fechaEmbarque=:fechaEmbarque, fechaInicio=:fechaInicio,
                    fechaTermino=:fechaTermino, notas=:notas, appTrackID=:appTrackID, idRepreVentas=:repreVentas,
                    idLiderProyecto=:idLiderProyecto, idGerenteProyecto=:idGerenteProyecto, idCoordinadorProyecto=:idCoordinadorProyecto, idIngenieroQA=:idIngenieroQA,
                    idProyectoRequester=:idProyectoRequester
                    WHERE idProyecto = $idProyecto");
                    $stmt->bindParam(':nombre', $nombre);
                }
                // Valida los campos de fecha para que sean compatibles con la BD
                if (empty($fechaReqCliente)) {
                    $fechaReqCliente = null;
                }
                if (empty($fechaPromesa)) {
                    $fechaPromesa = null;
                }
                if (empty($fechaTentativa)) {
                    $fechaTentativa = null;
                }
                if (empty($fechaEmbarque)) {
                    $fechaEmbarque = null;
                }
                if (empty($fechaInicio)) {
                    $fechaInicio = null;
                }
                if (empty($fechaTermino)) {
                    $fechaTermino = null;
                }
                if (empty($appTrackID)) {
                    $appTrackID = null;
                }
                $stmt->bindParam(':projectID', $projectID);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':idTipoProyecto', $tipo);
                $stmt->bindParam(':cobrarA', $cobrarA);
                $stmt->bindParam(':ventasPotenciales', $ventasPotenciales);
                $stmt->bindParam(':PO', $po);
                $stmt->bindParam(':qtoNumber', $qto);
                $stmt->bindParam(':idCuenta', $cuenta);
                $stmt->bindParam(':tracking', $tracking);
                $stmt->bindParam(':idRespDiseno', $empleadoDiseno);
                $stmt->bindParam(':idRespManu', $empleadoManu);
                $stmt->bindParam(':sobreCarga', $overLoad);
                $stmt->bindParam(':fechaReqCliente', $fechaReqCliente);
                $stmt->bindParam(':fechaPromesa', $fechaPromesa);
                $stmt->bindParam(':fechaTentativa', $fechaTentativa);
                $stmt->bindParam(':fechaEmbarque', $fechaEmbarque);
                $stmt->bindParam(':fechaInicio', $fechaInicio);
                $stmt->bindParam(':fechaTermino', $fechaTermino);
                $stmt->bindParam(':notas', $notas);
                // $stmt->bindParam(':idStatus', $status);
                $stmt->bindParam(':appTrackID', $appTrackID);
                // $stmt->bindParam(':prioridad', $prioridad);
                $stmt->bindParam(':repreVentas', $repreVentas);
                // APPROVERS
                $stmt->bindParam(':idLiderProyecto', $idProjectLeader);
                $stmt->bindParam(':idGerenteProyecto', $idProjectManager);
                $stmt->bindParam(':idCoordinadorProyecto', $idProjectCoordinator);
                $stmt->bindParam(':idIngenieroQA', $idQAEngineer);
                $stmt->bindParam(':idProyectoRequester', $idProyectoRequester);
                // Ejecutar la consulta preparada
                $result = $stmt->execute();

                if (!$result) {
                  $res = $stmt->errorInfo();
                  print_r($res);
                } else {
                    if ($fechaPromesa != null) {
                        $fechaReq = new DateTime($fechaPromesa);
                        $fechaReq = $fechaReq->modify('-1 day');
                        actualizarFechaReqActs($fechaReq);
                    } elseif ($fechaReqCliente != null) {
                        $fechaReq = new DateTime($fechaReqCliente);
                        $fechaReq = $fechaReq->modify('-1 day');
                        actualizarFechaReqActs($fechaReq);
                    }
                    echo "successEdit";
                }

                // echo "<script>
                //           alert('$message');
                //           window.location.href='proyecto_detalle.php?id=$idProyecto';
                //       </script>";
          }
      } else {  // -------------------------------------------------------------------- VALIDACION DE INFO
        // Verifica si existen duplicados
        $stmt2 = $dbh-> prepare("SELECT * FROM proyecto WHERE nombre=:nombre");
        $stmt2->bindParam(':nombre', $nombre);
        $stmt2->execute();
        $result = $stmt2->fetchAll();

        $stmt3 = $dbh-> prepare("SELECT * FROM proyecto WHERE projectID=:projectID");
        $stmt3->bindParam(':projectID', $projectID);
        $stmt3->execute();
        $result2 = $stmt3->fetchAll();
        // En caso de ya existir se muestra un error al usuario
        if ($result!=null) {
            echo "duplicatedName";
            die();
        } elseif ($result2!=null) {
            echo "duplicatedID";
            die();
        }
        else {   // -------------------------------------------------------------------- REGISTRO NUEVO
              if (empty($isApplication)) {
                  if (empty($cliente) || empty($nombre) || empty($tipo) || empty($cobrarA) || empty($projectID) ||
                          empty($empleadoDiseno) || empty($empleadoManu) || empty($status) || empty($etapa) || empty($usuario)) {
                      // $message = "Incomplete data. Please look for empty fields.";
                      // echo "<script>
                      //           alert('$message');
                      //           window.location.href='proyecto_alta.php';
                      //       </script>";
                      echo "errorVacio";
                      die();
                  }
              } else {
                 $nombre = generateApplicationAutomaticName();
              }

              // Se realiza una consulta preparada
              $stmt = $dbh-> prepare("INSERT INTO proyecto (idCliente, nombre, descripcion, idTipoProyecto , cobrarA, ventasPotenciales,
              PO, qtoNumber, idCuenta , tracking, idRespDiseno, idRespManu, sobreCarga, fechaReqCliente, fechaPromesa, fechaTentativa, fechaEmbarque, fechaInicio,
              fechaTermino, tiempoVida, notas, idStatus, currentStage, idUsuario, appTrackID, projectID, prioridad, idRepreVentas, idLiderProyecto, idGerenteProyecto,
              idCoordinadorProyecto, idIngenieroQA, isApplication, idProyectoRequester) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
              // Valida los campos de fecha para que sean compatibles con la BD

              if (empty($fechaReqCliente)) {
                  $fechaReqCliente = null;
              }
              if (empty($fechaPromesa)) {
                  $fechaPromesa = null;
              }
              if (empty($fechaTentativa)) {
                  $fechaTentativa = null;
              }
              if (empty($fechaEmbarque)) {
                  $fechaEmbarque = null;
              }
              if (empty($fechaInicio)) {
                  $fechaInicio = null;
              }
              if (empty($fechaTermino)) {
                  $fechaTermino = null;
              }
              if (empty($appTrackID)) {
                  $appTrackID = null;
              }
              if (empty($isApplication)) {
                  $isApplication = 0;
              }
              if (empty($cobrarA)) {
                  $cobrarA = "NAI";
              }
              if (empty($prioridad)) {
                  $prioridad = 0;
              }
              // Se asignan los valores a la consulta preparada
              $stmt->bindParam(1, $cliente);
              $stmt->bindParam(2, $nombre);
              $stmt->bindParam(3, $descripcion);
              $stmt->bindParam(4, $tipo);
              $stmt->bindParam(5, $cobrarA);
              $stmt->bindParam(6, $ventasPotenciales);
              $stmt->bindParam(7, $po);
              $stmt->bindParam(8, $qto);
              $stmt->bindParam(9, $cuenta);
              $stmt->bindParam(10, $tracking);
              $stmt->bindParam(11, $empleadoDiseno);
              $stmt->bindParam(12, $empleadoManu);
              $stmt->bindParam(13, $overLoad);
              $stmt->bindParam(14, $fechaReqCliente);
              $stmt->bindParam(15, $fechaPromesa);
              $stmt->bindParam(16, $fechaTentativa);
              $stmt->bindParam(17, $fechaEmbarque);
              $stmt->bindParam(18, $fechaInicio);
              $stmt->bindParam(19, $fechaTermino);
              $stmt->bindParam(20, $tiempoVida);
              $stmt->bindParam(21, $notas);
              $stmt->bindParam(22, $status);
              $stmt->bindParam(23, $etapa);
              $stmt->bindParam(24, $usuario);
              $stmt->bindParam(25, $appTrackID);
              $stmt->bindParam(26, $projectID);
              $stmt->bindParam(27, $prioridad);
              $stmt->bindParam(28, $repreVentas);
              // APPROVERS
              $stmt->bindParam(29, $idProjectLeader);
              $stmt->bindParam(30, $idProjectManager);
              $stmt->bindParam(31, $idProjectCoordinator);
              $stmt->bindParam(32, $idQAEngineer);
              $stmt->bindParam(33, $isApplication);
              $stmt->bindParam(34, $idProyectoRequester);

              // Ejecutar la consulta preparada
              $result = $stmt->execute();

              if (!$result) {
                $res = $stmt->errorInfo();
                print_r($res);
              } else {
                  echo "success";
                  $idProyecto = $dbh->lastInsertId();

                  if ($fechaPromesa != null) {
                      $fechaReq = new DateTime($fechaPromesa);
                      $fechaReq = $fechaReq->modify('-1 day');
                  } elseif ($fechaReqCliente != null) {
                      $fechaReq = new DateTime($fechaReqCliente);
                      $fechaReq = $fechaReq->modify('-1 day');
                  } else {
                      $fechaReq = null;
                  }
                  if ($isApplication == 1) {
                      // ADD APPLICATION ACTIVITIES FUNCTION
                  }else {
                      agregarActividades($idProyecto, $categoria, $fechaReq);
                  }
              }
            }
        }
      //Cierra conexión
      $dbh=null;
  } else {
      echo "errorDB";
      die();
  }

  function agregarActividades($idProyecto, $categoria, $fechaReq) {
      // PROYECTO PDP
      switch ($categoria) {
          case 1: // MS - Material Substitution
              $actividades = array(1,15,16,68,69,70,71,72,73,74,75,76,77,78,79,80,
                                    81,82,84,102,103,104);
              break;
          case 2:
          case 3:
          case 4: // PTI - Product Technology Innovation
              $actividades = array(1,3,15,16,17,18,19,20,21,22,23,29,30,35,36,39,42,46,48,49,52,56,57,68,69,
                                  70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,101,102,103,104,105,106,107,
                                  108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,
                                  127,128,129,130,131,132,133,134,136,137,138,139,140);
              break;
          case 5: // DB - Design BOM
              $actividades = array(1,3,23);
              break;
          case 6: // DD - Design Drawing
              $actividades = array(1,3,23,139,140);
              break;
          case 7: // BTP - Built to print
              $actividades = array(15,16,18,19,21,22,70,71,82,101,102,103,104,105,106);
              break;
          default: // 8 VT - Validation Test (PENDING)
              $actividades = array(69,70,72,73,74,75,76,77,78,79,80,81,84,132);
              break;
      }

      include "inc/conexion.php";
      // ASIGNACION DE ACTIVIDADES A PROYECTO TIPO PDP

      $datafields = array('idActividad', 'idProyecto', 'fechaRequerida');

      if (!is_null($fechaReq)) {
          $fechaReq = $fechaReq->format('Y-m-d H:i:s');
      }

      foreach ($actividades as $value) {
          $data[] = array('idActividad' => $value, 'idProyecto' => $idProyecto, 'fechaRequerida' => $fechaReq);
      }

      function placeholders($text, $count=0, $separator=","){
          $result = array();
          if($count > 0){
              for($x=0; $x<$count; $x++){
                  $result[] = $text;
              }
          }
          return implode($separator, $result);
      }

      $dbh->beginTransaction(); // also helps speed up your inserts.
      $insert_values = array();
      foreach($data as $d) {
          $question_marks[] = '('  . placeholders('?', sizeof($d)) . ')';
          $insert_values = array_merge($insert_values, array_values($d));
      }

      $sql = "INSERT INTO actividades_proyecto (" . implode(",", $datafields ) . ") VALUES " .
             implode(',', $question_marks);

      $stmt = $dbh->prepare ($sql);
      $stmt->execute($insert_values);
      $dbh->commit();

    }

    function actualizarFechaReqActs($fechaReq) {
        include "inc/conexion.php";
        $id = $_POST['idProyecto'];
        $fecha = $fechaReq->format('Y-m-d H:i:s');
        $stmt = $dbh->prepare("UPDATE actividades_proyecto SET fechaRequerida = :fechaRequerida WHERE idProyecto = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fechaRequerida', $fecha);
        $stmt->execute();
      }

    function assignProjectId($tipo) {
        include "inc/conexion.php";
        $stmt = $dbh-> prepare("SELECT proyecto_categoria.categoria, complejidad.nombre, COUNT(tipoproyecto.idTipoProyecto) + 1 AS idTipoProyecto
                                FROM proyecto
                                INNER JOIN tipoproyecto
                                ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                INNER JOIN proyecto_categoria
                                ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                INNER JOIN complejidad
                                ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                                WHERE proyecto.idTipoProyecto = $tipo");
        $stmt->execute();
        $resultado = $stmt->fetch();
        $stmt3 = $dbh-> prepare("SELECT pc.categoria
                                FROM proyecto_categoria AS pc
                                INNER JOIN tipoproyecto AS tp
                                ON pc.idProyectoCategoria = tp.idProyectoCategoria
                                WHERE tp.idTipoProyecto = $tipo");
        $stmt3->execute();
        $res2 = $stmt3->fetch();

        $consecutive = $resultado->idTipoProyecto;
        $found = 0;

        while ($found == 0) {
            $projectID = $res2->categoria . "-" . $resultado->nombre . "-" . $consecutive;

            $stmt3 = $dbh-> prepare("SELECT * FROM proyecto WHERE projectID=:projectID");
            $stmt3->bindParam(':projectID', $projectID);
            $stmt3->execute();
            $result2 = $stmt3->fetchAll();

            if ($result2 != null) {
                $consecutive ++;
            } else {
                $found = 1;
            }
        }
        return $projectID;
    }

    function generateApplicationAutomaticName() {
        include "inc/conexion.php";
        $stmt = $dbh-> prepare("SELECT COUNT(*) AS qty
                                FROM proyecto
                                WHERE isApplication = 1");
        $stmt->execute();
        $resultado = $stmt->fetch();
        $consecutive = $resultado->qty + 1;
        $found = 0;

        while ($found == 0) {
            $projectName = "Application - " . $consecutive;

            $stmt3 = $dbh-> prepare("SELECT * FROM proyecto WHERE nombre=:projectName");
            $stmt3->bindParam(':projectName', $projectName);
            $stmt3->execute();
            $result2 = $stmt3->fetchAll();

            if ($result2 != null) {
                $consecutive ++;
            } else {
                $found = 1;
            }
        }
        return "Application - " . $consecutive;
    }

    function isSameType($idProyecto, $tipo) {
        include "inc/conexion.php";
        $stmt = $dbh-> prepare("SELECT idTipoProyecto
                                FROM proyecto
                                WHERE idProyecto = $idProyecto");
        $stmt->execute();
        $resultado = $stmt->fetch();

        if ($resultado->idTipoProyecto == $tipo) {
            return true;
        } else {
            return false;
        }
    }
