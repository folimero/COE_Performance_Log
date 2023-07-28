<?php session_start();
  include '../inc/conexion.php';

  // Funcion para limpiar campos
  function cleanInput($value)
  {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }

  function cleanInput2($value)
  {
      $value = preg_replace("/[\'\")(;|`<>]/", "", $value);
      return $value;
  }

  function cleanPath($value)
  {
      $value = preg_replace("/\r|\n|[\'\"\{\}\[\]),(;|`<>]/", "", $value);
      return $value;
  }

  function isRealDate($date) {
      if (false === strtotime($date)) {
          return false;
      }
      list($year, $month, $day) = explode('-', $date);
      return checkdate($month, $day, $year);
  }

  if (!empty($_POST)) {
      if ($_POST['accion'] == 'mostrarRecursos') {
          $actId = cleanInput($_POST['actividad']);

          $stmt = $dbh->prepare("SELECT idActividades_proyecto, actividad.nombre
                                FROM actividades_proyecto
                                INNER JOIN actividad
                                ON actividades_proyecto.idActividad = actividad.idActividad
                                WHERE idActividades_proyecto = $actId");
          $stmt->execute();

          // if ($data = $stmt->fetch()) {
          //   echo json_encode($data, JSON_UNESCAPED_UNICODE);
          // }else {
          //   echo "errorActividad";
          // }
          // $stmt2 = $dbh->prepare("SELECT empleado.idEmpleado, empleado.nombre AS enombre, puesto.nombre AS pnombre
          //                       FROM empleado
          //                       INNER JOIN puesto
          //                       ON empleado.idPuesto = puesto.idPuesto
          //                       WHERE empleado.idEmpleado NOT IN (SELECT idEmpleado FROM recursos_asignados WHERE idActividades_proyecto = $actId)
          //                       AND empleado.idDepartamento = 2");

          $stmt2 = $dbh->prepare("SELECT empleado.idEmpleado, empleado.nombre AS enombre, puesto.nombre AS pnombre
                                FROM empleado
                                INNER JOIN puesto
                                ON empleado.idPuesto = puesto.idPuesto
                                WHERE empleado.asignableAct = 1 AND empleado.activo = 1 AND idEmpleado NOT IN (
                                      SELECT idEmpleado FROM recursos_asignados
                                      WHERE idActividades_proyecto = $actId
                                      GROUP BY idEmpleado)");
          $stmt2->execute();
          $data = array();
          $data2 = array();
          $data['result1'] = $stmt->fetch();

          while ($resultado = $stmt2->fetch()) {
              $data2[] = $resultado;
          }
          $data['result2'] = $data2;

          echo json_encode($data, JSON_UNESCAPED_UNICODE);
      // if ($dataTotal) {
          // echo json_encode(array('result1'=>$data,'result2'=>$data2));
            // echo json_encode($dataTotal, JSON_UNESCAPED_UNICODE);
          // }else {
          //   echo "errorActividad";
          // }
      } elseif ($_POST['accion'] == 'mostrarDisponibilidad') {
          try {
              $idEmpleado = cleanInput($_POST['idRecurso']);
              $selDate = cleanInput($_POST['selDate']);

              $stmt = $dbh->prepare("SELECT IFNULL(SUM(horas),0) AS horas
                                    FROM recursos_asignados
                                    WHERE idEmpleado = ? AND DATE(fechaInicio) = ?");
              $stmt->bindParam(1, $idEmpleado);
              $stmt->bindParam(2, $selDate);
              $stmt->execute();

              $data = array();
              $data['result'] = $stmt->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'mostrarEmpleado') {
          try {
              $idEmpleado = cleanInput($_POST['idEmpleado']);

              $stmt = $dbh->prepare("SELECT idEmpleado, numEmpleado, nombre, idDepartamento, idPuesto, correo, celular, activo, asignableAct, asignableAsResp
                                    FROM empleado
                                    WHERE idEmpleado = $idEmpleado");
              $stmt->execute();
              $data = array();
              $data['result'] = $stmt->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'mostrarUsuario') {
          try {
              $idUsuario = cleanInput($_POST['idUsuario']);

              $stmt = $dbh->prepare("SELECT idUsuario, usuarioNombre, usuario.activo AS act, empleado.numEmpleado AS numEmp, empleado.nombre AS emp
                                    FROM usuario
                                    INNER JOIN empleado
                                    ON usuario.idEmpleado = empleado.idEmpleado
                                    WHERE idUsuario = $idUsuario");
              $stmt->execute();
              $data = array();
              $data['result'] = $stmt->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'registrarRecurso') {
          try {
              $actId = cleanInput($_POST['actividad']);
              $recId = cleanInput($_POST['recurso']);
              $fechaInicio = cleanInput($_POST['fechaInicio']);
              $horas = cleanInput($_POST['horas']);

              $stmt = $dbh-> prepare("INSERT INTO recursos_asignados (idActividades_proyecto, idEmpleado, fechaInicio, horas)
                                      VALUES (?, ?, ?, ?)");
              // Se asignan los valores a la consulta preparada
              $stmt->bindParam(1, $actId);
              $stmt->bindParam(2, $recId);
              $stmt->bindParam(3, $fechaInicio);
              $stmt->bindParam(4, $horas);
              $stmt->execute();
              echo "Registro capturado con Exito.";
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'insertarRecursoNuevo') {
          try {
              $id = cleanInput($_POST['idActividades_proyecto']);
              $idEmpleado = cleanInput($_POST['idEmpleado']);
              $fechaInicio = date("Y-m-d");
              $horas = 0;

              $stmt = $dbh-> prepare("INSERT INTO recursos_asignados (idActividades_proyecto, idEmpleado, fechaInicio, horas)
                                      VALUES (?, ?, ?, ?)");
              // Se asignan los valores a la consulta preparada
              $stmt->bindParam(1, $id);
              $stmt->bindParam(2, $idEmpleado);
              $stmt->bindParam(3, $fechaInicio);
              $stmt->bindParam(4, $horas);
              $stmt->execute();

              $data = array();

              $idInserted = $dbh->lastInsertId();
              $stmt2 = $dbh-> prepare("SELECT idRecurso, DATE(fechaInicio) AS fechaInicio, horas FROM recursos_asignados
                                       WHERE idRecurso = $idInserted");
              $stmt2->execute();
              $data['result'] = $stmt2->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'insertarCotizacionEnsambleNuevo') {
          try {
              $id = cleanInput($_POST['idCotizacion']);

              $stmt = $dbh-> prepare("INSERT INTO cotizacion_ensambles (idCotizacion)
                                      VALUES (?)");
              // Se asignan los valores a la consulta preparada
              $stmt->bindParam(1, $id);
              $stmt->execute();

              $data = array();

              $idInserted = $dbh->lastInsertId();
              $stmt2 = $dbh-> prepare("SELECT idCotizacionEnsamble, IFNULL(numParte,'') AS numParte, IFNULL(descripcion,'') AS descripcion,
                                        IFNULL(eau,'') AS eau, IFNULL(selling_price,'') AS selling_price, IFNULL(notas,'') AS notas
                                        FROM cotizacion_ensambles
                                        WHERE idCotizacionEnsamble = $idInserted");
              $stmt2->execute();
              $data['result'] = $stmt2->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarCuenta') {
          try {
              $cliente = cleanInput($_POST['cliente']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmtCuenta = $dbh->prepare("SELECT nombreCarrier, cuenta, idCuenta FROM cuenta
                                          INNER JOIN carrier ON cuenta.idCarrier = carrier.idCarrier
                                          WHERE idCliente = $cliente");
              $stmtCuenta->execute();
              $cadena = "<select name='cuenta' id='cuenta'><option disabled selected value> -- Select -- </option>";

              while ($resultado = $stmtCuenta->fetch()) {
                  $cadena=$cadena.'<option value='.$resultado->idCuenta.'>'.
                                    $resultado->nombreCarrier . " - " . $resultado->cuenta;
                  '</option>';
              }

              echo  $cadena."</select>";
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'cargarServicios') {
          try {
              $categoria = cleanInput($_POST['categoria']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmtCategoria = $dbh->prepare("SELECT DISTINCT proyecto_servicio.idProyectoServicio, servicio, descripcion
                                              FROM `proyecto_servicio`
                                              INNER JOIN tipoproyecto
                                              ON proyecto_servicio.idProyectoServicio = tipoproyecto.idProyectoServicio
                                              WHERE tipoproyecto.idProyectoCategoria = $categoria");
              $stmtCategoria->execute();
              $cadena = "<select name='cuenta' id='cuenta'><option disabled selected value> -- Select -- </option>";

              while ($resultado = $stmtCategoria->fetch()) {
                  $cadena=$cadena.'<option value='.$resultado->idProyectoServicio.'>'.
                                    $resultado->servicio . " - " . $resultado->descripcion;
                  '</option>';
              }

              echo  $cadena."</select>";
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarClienteContacto') {
          try {
              $cliente = cleanInput($_POST['cliente']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmtClienteContacto = $dbh->prepare("SELECT idClienteContacto, nombre
                                          FROM cliente_contacto
                                          WHERE idCliente = $cliente");
              $stmtClienteContacto->execute();
              $cadena = "<select name='clienteContacto' id='clienteContacto'>
              <option disabled selected value> -- N/A -- </option>";

              while ($resultado = $stmtClienteContacto->fetch()) {
                  $cadena=$cadena.'<option value='.$resultado->idClienteContacto.'>'.
                                    $resultado->nombre;
                  '</option>';
              }

              echo  $cadena."</select>";
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarAprobadores') {
         try {
             $id = cleanInput($_POST['idActividades_proyecto']);
             // Funcion para llenar Selector de Cuenta de formulario
             $stmtAprobadores = $dbh->prepare("SELECT usuario.idUsuario, empleado.nombre
                                                    FROM usuario
                                                    INNER JOIN empleado
                                                    ON usuario.idEmpleado = empleado.idEmpleado
                                                    WHERE usuario.idUsuario NOT IN (SELECT idUsuario FROM usuarios_asignados WHERE usuarios_asignados.idActividades_proyecto = $id)");
             $stmtAprobadores->execute();
             $cadena = "<option disabled selected value> -- N/A -- </option>";

             while ($resultado = $stmtAprobadores->fetch()) {
                 $cadena=$cadena.'<option value='.$resultado->idUsuario.'>'.
                                   $resultado->nombre;
                 '</option>';
             }

             echo  $cadena;
         } catch (\Exception $e) {
             alert($e);
         }
     } elseif ($_POST['accion'] == 'actualizarComplejidad') {
          try {
              $cliente = cleanInput($_POST['cliente']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmt = $dbh->prepare("SELECT complejidad.nombre AS cnombre
                                      FROM tipocotizacion
                                      INNER JOIN cotizacion_categoria
                                      ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
                                      INNER JOIN complejidad
                                      ON cotizacion_categoria.idComplejidad = complejidad.idComplejidad
                                      WHERE idTipoCotizacion = $cliente");
              $stmt->execute();
              $cadena = "";

              while ($resultado = $stmt->fetch()) {
                  $cadena = "" . $resultado->cnombre;
              }
              echo  $cadena;
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarVolumen') {
          try {
              $cliente = cleanInput($_POST['cliente']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmt = $dbh->prepare("SELECT cotizacion_volumen.nombre AS cnombre
                                      FROM tipocotizacion
                                      INNER JOIN cotizacion_volumen
                                      ON tipocotizacion.idCotizacionVolumen=cotizacion_volumen.idCotizacionVolumen
                                      WHERE idTipoCotizacion= $cliente");
              $stmt->execute();
              $cadena = "";

              while ($resultado = $stmt->fetch()) {
                  $cadena = "" . $resultado->cnombre;
              }
              echo  $cadena;
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarTipoProyecto') {
          try {
              $categoria = cleanInput($_POST['categoria']);
              $complejidad = cleanInput($_POST['complejidad']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmt = $dbh->prepare("SELECT idTipoProyecto FROM tipoproyecto
                                          WHERE idProyectoCategoria = $categoria AND idComplejidad = $complejidad");
              $stmt->execute();
              $cadena = "";

              while ($resultado = $stmt->fetch()) {
                  $cadena = "" . $resultado->idTipoProyecto;
              }
              echo  $cadena;
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'agregarSoporte') {
        try {
            $idPoryecto = cleanInput($_POST['idPoryecto']);
            $horas = cleanInput($_POST['horas']);
            $fechaSoporte = cleanInput($_POST['fechaSoporte']);
            $idUsuario = cleanInput($_POST['idUsuario']);

            // Funcion para llenar Selector de puesto de formulario
            $stmt = $dbh-> prepare("INSERT INTO proyecto_soporte_adicional (idProyecto, idUsuario, fechaSoporte, horas)
                                    VALUES (?, ?, ?, ?)");
            // Se asignan los valores a la consulta preparada
            $stmt->bindParam(1, $idPoryecto);
            $stmt->bindParam(2, $idUsuario);
            $stmt->bindParam(3, $fechaSoporte);
            $stmt->bindParam(4, $horas);

            $stmt->execute();
            $data = array();

            $idInserted = $dbh->lastInsertId();
            $stmt2 = $dbh-> prepare("SELECT psa.idSoporteAdicional, e.nombre AS eNombre, psa.horas, DATE(psa.fechaSoporte) AS fechaSoporte, psa.comentarios
                                        FROM proyecto_soporte_adicional AS psa
                                        INNER JOIN proyecto AS p
                                        ON psa.idProyecto = p.idProyecto
                                        INNER JOIN usuario AS u
                                        ON psa.idUsuario = u.idUsuario
                                        INNER JOIN empleado AS e
                                        ON u.idEmpleado = e.idEmpleado
                                        WHERE psa.idSoporteAdicional = $idInserted");
            $stmt2->execute();
            $data['result'] = $stmt2->fetch();

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            $data['result'] = "error";
        }
    } elseif ($_POST['accion'] == 'agregarRecurso') {
          try {
              $idActividades_proyecto = cleanInput($_POST['idActividades_proyecto']);
              $idUsuario = cleanInput($_POST['idUsuario']);

              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh-> prepare("INSERT INTO actividad_recursos_adicionales (idActividades_proyecto, idUsuario)
                                      VALUES (?, ?)");
              // Se asignan los valores a la consulta preparada
              $stmt->bindParam(1, $idActividades_proyecto);
              $stmt->bindParam(2, $idUsuario);
              $stmt->execute();
              $data = array();

              $idInserted = $dbh->lastInsertId();
              $stmt2 = $dbh-> prepare("SELECT ara.idRecursosAdicionales, a.nombre AS aNombre, e.nombre AS eNombre, DATE(ara.fechaInicio) AS fechaInicio, DATE(ara.fechaRequerida) AS fechaRequerida, DATE(ara.fechaEntrega) AS fechaEntrega, ara.ubicacion, ara.comentarios
                                      FROM actividad_recursos_adicionales AS ara
                                      INNER JOIN actividades_proyecto AS ap
                                      ON ara.idActividades_proyecto = ap.idActividades_proyecto
                                      INNER JOIN actividad AS a
                                      ON a.idActividad = ap.idActividad
                                      INNER JOIN proyecto AS p
                                      ON ap.idProyecto = p.idProyecto
                                      INNER JOIN usuario AS u
                                      ON ara.idUsuario = u.idUsuario
                                      INNER JOIN empleado AS e
                                      ON u.idEmpleado = e.idEmpleado
                                      WHERE ara.idRecursosAdicionales = $idInserted");
              $stmt2->execute();
              $data['result'] = $stmt2->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
          }
      } elseif ($_POST['accion'] == 'actualizarTipoProyectoServicio') {
          try {
              $categoria = cleanInput($_POST['categoria']);
              $complejidad = cleanInput($_POST['complejidad']);

              if (isset($_POST['servicio'])) {
                  $servicio = cleanInput($_POST['servicio']);
                  $stmt = $dbh->prepare("SELECT idTipoProyecto FROM tipoproyecto
                                              WHERE idProyectoCategoria = $categoria AND idComplejidad = $complejidad AND idProyectoServicio = $servicio");
              }else {
                  $stmt = $dbh->prepare("SELECT idTipoProyecto FROM tipoproyecto
                                              WHERE idProyectoCategoria = $categoria AND idComplejidad = $complejidad AND  idProyectoServicio IS NULL");
              }
              // Funcion para llenar Selector de Cuenta de formulario

              $stmt->execute();
              $cadena = "";

              while ($resultado = $stmt->fetch()) {
                  $cadena = "" . $resultado->idTipoProyecto;
              }
              echo  $cadena;
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarTipoCotizacion') {
          try {
              $categoria = cleanInput($_POST['categoria']);
              $volumen = cleanInput($_POST['volumen']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmt = $dbh->prepare("SELECT idTipoCotizacion FROM tipocotizacion
                                        WHERE idCotizacionCategoria = $categoria AND idCotizacionVolumen = $volumen");
              $stmt->execute();
              $cadena = "";

              while ($resultado = $stmt->fetch()) {
                  $cadena = "" . $resultado->idTipoCotizacion;
              }
              echo  $cadena;
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarHoras') {
          try {
              $cliente = cleanInput($_POST['cliente']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmt = $dbh->prepare("SELECT horas
                                      FROM tipocotizacion
                                      WHERE idTipoCotizacion= $cliente");
              $stmt->execute();
              $cadena = "";

              while ($resultado = $stmt->fetch()) {
                  $cadena = "" . $resultado->horas;
              }
              echo  $cadena;
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarCategoriaProyecto') {
          try {
              $cliente = cleanInput($_POST['cliente']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmt = $dbh->prepare("SELECT proyecto_categoria.categoria AS cnombre
                                      FROM tipoproyecto
                                      INNER JOIN proyecto_categoria
                                      ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                      WHERE idTipoProyecto = $cliente");
              $stmt->execute();
              $cadena = "";

              while ($resultado = $stmt->fetch()) {
                  $cadena = "" . $resultado->cnombre;
              }
              echo  $cadena;
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarComplejidadProyecto') {
          try {
              $cliente = cleanInput($_POST['cliente']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmt = $dbh->prepare("SELECT complejidad.nombre AS cnombre
                                      FROM tipoproyecto
                                      INNER JOIN complejidad
                                      ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                                      WHERE idTipoProyecto = $cliente");
              $stmt->execute();
              $cadena = "";

              while ($resultado = $stmt->fetch()) {
                  $cadena = "" . $resultado->cnombre;
              }
              echo  $cadena;
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarHorasProyecto') {
          try {
              $cliente = cleanInput($_POST['cliente']);
              // Funcion para llenar Selector de Cuenta de formulario
              $stmt = $dbh->prepare("SELECT horas
                                      FROM tipoproyecto
                                      WHERE idTipoProyecto= $cliente");
              $stmt->execute();
              $cadena = "";

              while ($resultado = $stmt->fetch()) {
                  $cadena = "" . $resultado->horas;
              }
              echo  $cadena;
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'cargarUsuarios') {
          try {
              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh->prepare("SELECT nombre, empleado.idEmpleado, numEmpleado FROM empleado
                                    WHERE empleado.idEmpleado NOT IN (SELECT idEmpleado FROM usuario)");
              $stmt->execute();
              $data = array();

              while ($resultado = $stmt->fetch()) {
                  $data[] = $resultado;
              }

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'mostrarNota') {
          try {
              $id = cleanInput($_POST['idNota']);
              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh->prepare("SELECT idProyectoNota AS id, nota FROM proyecto_notas
                                    WHERE idProyectoNota = $id");
              $stmt->execute();
              $data = array();

              $data['result'] = $stmt->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'nuevaNota') {
          try {
              $id = cleanInput($_POST['idProyecto']);
              $nota = cleanInput2($_POST['nota']);
              $idUsuario = $_SESSION['idUsuario'];

              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh-> prepare("INSERT INTO proyecto_notas (idProyecto, nota, idUsuario)
                                      VALUES (?, ?, ?)");
              // Se asignan los valores a la consulta preparada
              $stmt->bindParam(1, $id);
              $stmt->bindParam(2, $nota);
              $stmt->bindParam(3, $idUsuario);
              $stmt->execute();
              $data = array();

              $idInserted = $dbh->lastInsertId();
              $stmt2 = $dbh-> prepare("SELECT idProyectoNota, nota, empleado.nombre, DATE(proyecto_notas.fechaCrea) AS fechaCrea,
                                            DAY(proyecto_notas.fechaCrea) AS notaDay,
                                            MONTH(proyecto_notas.fechaCrea) AS notaMonth,
                                            YEAR(proyecto_notas.fechaCrea) AS notaYear
                                        FROM proyecto_notas
                                        INNER JOIN usuario
                                        ON proyecto_notas.idUsuario = usuario.idUsuario
                                        INNER JOIN empleado
                                        ON usuario.idEmpleado = empleado.idEmpleado
                                        WHERE idProyectoNota = $idInserted");
              $stmt2->execute();
              $data['result'] = $stmt2->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'nuevaVenta') {
          try {
              $id = cleanInput($_POST['idCotizacion']);
              $anio = cleanInput($_POST['anio']);
              $mes = cleanInput($_POST['mes']);
              $venta = cleanInput($_POST['venta']);
              $notas = cleanInput2($_POST['nota']);
              $idUsuario = $_SESSION['idUsuario'];

              $stmtCheck = $dbh->prepare("SELECT idCotizacion, anio, mes
                                          FROM ventas
                                          WHERE idCotizacion=$id AND anio='$anio' AND mes=$mes");
              $stmtCheck->execute();

              if ($stmtCheck->rowCount()>0) {
                  echo "duplicados";
              } else {
                  // Funcion para llenar Selector de puesto de formulario
                  $stmt = $dbh-> prepare("INSERT INTO ventas (idCotizacion, anio, mes, venta, notas, createdBy)
                                          VALUES (?, ?, ?, ?, ?, ?)");
                  // Se asignan los valores a la consulta preparada
                  $stmt->bindParam(1, $id);
                  $stmt->bindParam(2, $anio);
                  $stmt->bindParam(3, $mes);
                  $stmt->bindParam(4, $venta);
                  $stmt->bindParam(5, $notas);
                  $stmt->bindParam(6, $idUsuario);
                  $stmt->execute();
                  $data = array();

                  $idInserted = $dbh->lastInsertId();
                  $stmt2 = $dbh-> prepare("SELECT idVenta, idCotizacion, anio, MONTHNAME(STR_TO_DATE(mes, '%m')) AS mes, venta,notas
                                            FROM ventas
                                            WHERE idVenta = $idInserted");
                  $stmt2->execute();
                  $data['result'] = $stmt2->fetch();

                  echo json_encode($data, JSON_UNESCAPED_UNICODE);
              }
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'nuevaActividadRequerida') {
          try {
              $idActivity = cleanInput($_POST['idActivity']);
              $idReqActivity = cleanInput2($_POST['idReqActivity']);

              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh-> prepare("INSERT INTO actividad_dependencia (idActividad, idActRequerida)
                                      VALUES (?, ?)");
              // Se asignan los valores a la consulta preparada
              $stmt->bindParam(1, $idActivity);
              $stmt->bindParam(2, $idReqActivity);
              $stmt->execute();
              $data = array();

              $idInserted = $dbh->lastInsertId();
              $stmt2 = $dbh-> prepare("SELECT ad.idActividadDependencia, ad.idActRequerida, a.nombre, a.tipo, a.descripcion, e.nombre AS eNombre, a.resp
                                      FROM actividad_dependencia AS ad
                                      INNER JOIN actividad AS a
                                      ON ad.idActRequerida = a.idActividad
                                      INNER JOIN etapa AS e
                                      ON a.idEtapa = e.idEtapa
                                      WHERE ad.idActividadDependencia = $idInserted");
              $stmt2->execute();
              $data['result'] = $stmt2->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
          }
      } elseif ($_POST['accion'] == 'eliminarActividadRequerida') {
          $data = array();
          try {
              $id = cleanInput($_POST['id']);

              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh-> prepare("DELETE FROM actividad_dependencia
                                       WHERE idActividadDependencia = $id");
              $stmt->execute();
              $data['result'] = "deleted";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
              alert($e);
          }
      } elseif ($_POST['accion'] == 'eliminarNota') {
          $data = array();
          try {
              $id = cleanInput($_POST['idProyectoNota']);

              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh-> prepare("DELETE FROM proyecto_notas
                                       WHERE idProyectoNota = $id");
              $stmt->execute();

              $idInserted = $dbh->lastInsertId();
              $stmt2 = $dbh-> prepare("SELECT idProyectoNota, nota, empleado.nombre, DATE(proyecto_notas.fechaCrea) AS fechaCrea
                                         FROM proyecto_notas
                                         INNER JOIN usuario
                                         ON proyecto_notas.idUsuario = usuario.idUsuario
                                         INNER JOIN empleado
                                         ON usuario.idEmpleado = empleado.idEmpleado
                                         WHERE idProyectoNota = $idInserted");
              $stmt2->execute();
              $data['result'] = $stmt2->fetch();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarNota') {
          try {
              $id = cleanInput($_POST['idProyectoNota']);
              $nota = cleanInput2($_POST['nota']);

              $stmt = $dbh-> prepare("UPDATE proyecto_notas SET nota = '$nota'
                                          WHERE idProyectoNota = $id");
              $stmt->execute();
              $data = $nota;
              echo $data;
              // echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
              alert($e);
          }
      } elseif ($_POST['accion'] == 'mostrarNotaCotizacion') { ////////-------------------------> COTIZACION NOTAS
          try {
              $id = cleanInput($_POST['idNota']);
              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh->prepare("SELECT idCotizacionNota AS id, nota FROM cotizacion_notas
                                        WHERE idCotizacionNota = $id");
              $stmt->execute();
              $data = array();

              $data['result'] = $stmt->fetch();

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'nuevaNotaCotizacion') {
          try {
              $id = cleanInput($_POST['idCotizacion']);
              // $overallComplet = cleanInput($_POST['overallComplet']);
              $idUsuario = $_SESSION['idUsuario'];
              if (isset($_POST['nota']) && !empty($_POST['nota'])) {
                  $nota = cleanInput($_POST['nota']);
                  // Funcion para llenar Selector de puesto de formulario
                  $stmt = $dbh-> prepare("INSERT INTO cotizacion_notas (idCotizacion, nota, idUsuario)
                                              VALUES (?, ?, ?)");
                  // Se asignan los valores a la consulta preparada
                  $stmt->bindParam(1, $id);
                  $stmt->bindParam(2, $nota);
                  $stmt->bindParam(3, $idUsuario);
                  $stmt->execute();
                  $data = array();

                  $idInserted = $dbh->lastInsertId();
                  $stmt2 = $dbh-> prepare("SELECT idCotizacionNota, nota, empleado.nombre, DATE(cotizacion_notas.fechaCrea) AS fechaCrea
                                                FROM cotizacion_notas
                                                INNER JOIN usuario
                                                ON cotizacion_notas.idUsuario = usuario.idUsuario
                                                INNER JOIN empleado
                                                ON usuario.idEmpleado = empleado.idEmpleado
                                                WHERE idCotizacionNota = $idInserted");
                  $stmt2->execute();

                  if (isset($_POST['overallComplet'])) {
                      $comp = cleanInput($_POST['overallComplet']);
                      $stmtComplete = $dbh-> prepare("UPDATE cotizacion SET overallComplet = $comp
                                                      WHERE idCotizacion = $id");
                      $stmtComplete->execute();
                  }

                  $data['result'] = $stmt2->fetch();

                  echo json_encode($data, JSON_UNESCAPED_UNICODE);
              } else {
                  if (isset($_POST['overallComplet'])) {
                      $comp = cleanInput($_POST['overallComplet']);
                      $stmtComplete = $dbh-> prepare("UPDATE cotizacion SET overallComplet = $comp
                                                      WHERE idCotizacion = $id");
                      $stmtComplete->execute();
                  }
                  echo 'porciento';
              }
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'eliminarNotaCotizacion') {
          $data = array();
          try {
              $id = cleanInput($_POST['idCotizacionNota']);

              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh-> prepare("DELETE FROM cotizacion_notas
                                           WHERE idCotizacionNota = $id");
              $stmt->execute();

              $idInserted = $dbh->lastInsertId();
              $stmt2 = $dbh-> prepare("SELECT idCotizacionNota, nota, empleado.nombre, DATE(cotizacion_notas.fechaCrea) AS fechaCrea
                                             FROM cotizacion_notas
                                             INNER JOIN usuario
                                             ON cotizacion_notas.idUsuario = usuario.idUsuario
                                             INNER JOIN empleado
                                             ON usuario.idEmpleado = empleado.idEmpleado
                                             WHERE idCotizacionNota = $idInserted");
              $stmt2->execute();
              $data['result'] = $stmt2->fetch();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
              alert($e);
          }
      } elseif ($_POST['accion'] == 'actualizarNotaCotizacion') {
          try {
              $id = cleanInput($_POST['idCotizacionNota']);
              $nota = cleanInput($_POST['nota']);

              $stmt = $dbh-> prepare("UPDATE cotizacion_notas SET nota = '$nota'
                                              WHERE idCotizacionNota = $id");
              $stmt->execute();
              $data = $nota;
              echo $data;
              // echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
              alert($e);
          }
      } elseif ($_POST['accion'] == 'cambiarStatusCotizacion') {
          try {
              $id = cleanInput($_POST['idCotizacion']);
              $idStatus = cleanInput($_POST['idStatus']);

              $stmt = $dbh-> prepare("UPDATE cotizacion SET idStatus = $idStatus
                                                 WHERE idCotizacion = $id");
              $stmt->execute();
              $stmt2 = $dbh-> prepare("SELECT idStatus, nombre FROM status
                                       WHERE idStatus = $idStatus");
              $stmt2->execute();
              $data['result'] = $stmt2->fetch();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
              alert($e);
          }
      } elseif ($_POST['accion'] == 'cargarListaStatus') {
          try {
              $id = cleanInput($_POST['idStatus']);
              $idStatus = cleanInput($_POST['idStatus']);

              $stmt = $dbh->prepare("SELECT idStatus, nombre FROM status
                                    WHERE idStatus NOT IN (SELECT idStatus FROM status WHERE idStatus = $id)");
              $stmt->execute();
              $data['result'] = $stmt->fetchAll();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
              alert($e);
          }
      } elseif ($_POST['accion'] == 'cargarListaActividades') {
          try {
              $id = cleanInput($_POST['idActivity']);

              $stmt = $dbh->prepare("SELECT a.idActividad, a.nombre, e.nombre AS eNombre, a.tipo
                                    FROM actividad AS a
                                    INNER JOIN etapa AS e
                                    ON a.idEtapa = e.idEtapa
                                    WHERE a.idActividad NOT IN (SELECT idActRequerida FROM actividad_dependencia WHERE idActividad = $id) AND
                                    a.obsoleta <> 1
                                    ORDER BY eNombre, tipo ASC");
              $stmt->execute();
              $data['result'] = $stmt->fetchAll();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
              alert($e);
          }
      } elseif ($_POST['accion'] == 'cambiarStatusProyecto') {
          try {
              $id = cleanInput($_POST['idProyecto']);
              $idStatus = cleanInput($_POST['idStatus']);

              $stmt = $dbh-> prepare("UPDATE proyecto SET idStatus = $idStatus
                                                 WHERE idProyecto = $id");
              $stmt->execute();
              $stmt2 = $dbh-> prepare("SELECT idStatus, nombre FROM status
                                       WHERE idStatus = $idStatus");
              $stmt2->execute();
              $data['result'] = $stmt2->fetch();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
              alert($e);
          }
      } elseif ($_POST['accion'] == 'editarProyecto') {
          try {
              // Funcion para llenar Selector de puesto de formulario
              $id = cleanInput($_POST['idProyecto']);
              $stmt = $dbh-> prepare("SELECT idCliente, proyecto.nombre, proyecto.descripcion, proyecto.idTipoProyecto , cobrarA, ventasPotenciales, PO, qtoNumber, idCuenta, proyecto.idProyectoRequester, tracking, idRespDiseno, idRespManu, sobreCarga, fechaReqCliente, fechaPromesa, fechaTentativa, tipoproyecto.idProyectoServicio,
                                              fechaEmbarque, fechaInicio, fechaTermino, tiempoVida, notas, idStatus, currentStage, idUsuario,
                                              appTrackID, projectID, tipoproyecto.idProyectoCategoria, tipoproyecto.idComplejidad, idRepreVentas,
                                              idLiderProyecto, idGerenteProyecto, idCoordinadorProyecto, idIngenieroQA
                                      FROM proyecto
                                      INNER JOIN tipoproyecto
                                      ON proyecto.idTipoProyecto = tipoproyecto.idTipoProyecto
                                      INNER JOIN proyecto_categoria
                                      ON tipoproyecto.idProyectoCategoria = proyecto_categoria.idProyectoCategoria
                                      INNER JOIN complejidad
                                      ON tipoproyecto.idComplejidad = complejidad.idComplejidad
                                      WHERE idProyecto = $id");
              $stmt->execute();
              $data = array();

              $data['result'] = $stmt->fetch();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'editarCotizacion') {
          try {
              // Funcion para llenar Selector de puesto de formulario
              $id = cleanInput($_POST['idCotizacion']);
              $stmt = $dbh-> prepare("SELECT idCliente, cotizacion.nombre, cotizacion.quoteID, cotizacion_categoria.idCotizacionCategoria, cotizacion.descripcion,
                                              cotizacion.idTipoCotizacion , ventasPotenciales, lineItems, cotizacion_volumen.idCotizacionVolumen,
                                              idClienteContacto, idResponsable, idRepreVentas, uniqueFG, BOMType, fechaInicio, fechaLanzamiento,
                                              fechaReqCliente, consolidatedEAU, notas, cotizacion.overallComplet, cotizacion.idStatus, consOTC, sourcMatStartDate,
                                              sourcMatEndDate, cotizacion.dateBDM
                                      FROM cotizacion
                                      INNER JOIN tipocotizacion
                                      ON cotizacion.idTipoCotizacion = tipocotizacion.idTipoCotizacion
                                      INNER JOIN cotizacion_categoria
                                      ON tipocotizacion.idCotizacionCategoria = cotizacion_categoria.idCotizacionCategoria
                                      INNER JOIN cotizacion_volumen
                                      ON tipocotizacion.idCotizacionVolumen = cotizacion_volumen.idCotizacionVolumen
                                      WHERE idCotizacion = $id");
              $stmt->execute();
              $data = array();

              $data['result'] = $stmt->fetch();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'cargarActividades') {
          try {
              // Funcion para llenar Selector de puesto de formulario
              $stmt = $dbh-> prepare("SELECT idActividad, tipo, actividad.nombre AS aNombre, actividad.idEtapa, etapa.nombre AS eNombre, actividad.resp
                                      FROM `actividad`
                                      INNER JOIN etapa
                                      ON actividad.idEtapa = etapa.idEtapa
                                      WHERE actividad.obsoleta = 0
                                      ORDER BY idEtapa, tipo, idActividad");
              $stmt->execute();
              $data = array();

              $data['result'] = $stmt->fetchAll();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'mostrarDetalleAct') {
          try {
              $engineer = $_POST['engineer'];
              $data = array();

              $stmt = $dbh-> prepare("SELECT proyecto.nombre AS pNombre , actividad.nombre AS aNombre
                                      FROM proyecto
                                      INNER JOIN actividades_proyecto
                                      ON proyecto.idProyecto = actividades_proyecto.idProyecto
                                      INNER JOIN actividad
                                      ON actividades_proyecto.idActividad = actividad.idActividad
                                      INNER JOIN empleado
                                      ON proyecto.idRespDiseno = empleado.idEmpleado
                                      WHERE proyecto.idStatus <> 7 AND proyecto.idStatus <> 5 AND
                                      actividades_proyecto.idActividad IN (3,4,17,20,23,24,25,26,28,29,30,34,35,36,37,38,40,42,46,48,49,50,51,52,53,54,56,57,58,59,60,61,64,65,67,70,84,89) AND
                                      actividades_proyecto.entregadoPor IS NULL AND
                                      empleado.nombre = '$engineer'
                                      ORDER BY pNombre");
              $stmt->execute();

              if ($stmt->rowCount()) {
                  $data['result'] = $stmt->fetchAll();
              }else {
                  $stmt2 = $dbh-> prepare("SELECT proyecto.nombre AS pNombre , actividad.nombre AS aNombre
                                          FROM proyecto
                                          INNER JOIN actividades_proyecto
                                          ON proyecto.idProyecto = actividades_proyecto.idProyecto
                                          INNER JOIN actividad
                                          ON actividades_proyecto.idActividad = actividad.idActividad
                                          INNER JOIN empleado
                                          ON proyecto.idRespManu = empleado.idEmpleado
                                          WHERE proyecto.idStatus <> 7 AND proyecto.idStatus <> 5 AND
                                          actividades_proyecto.idActividad IN (39,41,43,45,62,63,66,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,90,94,97,98,105,106) AND
                                          actividades_proyecto.entregadoPor IS NULL AND
                                          empleado.nombre = '$engineer'
                                          ORDER BY pNombre");
                  $stmt2->execute();
                  $data['result'] = $stmt2->fetchAll();
              }

              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      }  elseif ($_POST['accion'] == 'asignarActividades') {
          try {
              // Funcion para llenar Selector de puesto de formulario
              $idProyecto = cleanInput($_POST['idProyecto']);
              $idActividades = $_POST['actividades'];
              $addAditionalResources = false;

              if (isset($_POST['idRespDiseno'])) {
                  $idRespDiseno = $_POST['idRespDiseno'];
                  $stmtUser = $dbh-> prepare("SELECT idUsuario FROM usuario WHERE idEmpleado = $idRespDiseno");
                  $stmtUser->execute();
                  $usuario = $stmtUser->fetch();
                  $addAditionalResources = true;
              }

              //SE OBTIENEN LOS DATOS ACTUALES PARA DEFINIR CUALES ELIMINAR
              $stmt3 = $dbh-> prepare("SELECT idActividad FROM actividades_proyecto WHERE idProyecto = $idProyecto");
              $stmt3->execute();
              $dataSQL = array();
              $dataSQLINSERT = array();
              $dataSQLDELETE = array();
              while ($resultado = $stmt3->fetch()) {
                array_push($dataSQL, $resultado->idActividad);
              }
              // REGISTROS A ELIMINAR
              foreach($dataSQL as $item) {
                  if (!in_array($item, $idActividades)) {
                      array_push($dataSQLDELETE, $item);
                  }
              }

              // REGISTROS A INSERTAR
              foreach($idActividades as $item) {
                  if (!in_array($item, $dataSQL)) {
                      array_push($dataSQLINSERT, $item);
                  }
              }
              // INSERTA LOS REGISTROS FALTANTES
              $sqlDELETE= "";
              $actividades_count = count($dataSQLDELETE);

              // $data = $actividades_count;
              // echo $data;

              foreach($dataSQLDELETE as $item) {
                  $stmtDelete = $dbh-> prepare("DELETE FROM actividades_proyecto
                                                WHERE idActividad = $item AND idProyecto = $idProyecto");
                  $stmtDelete->execute();
              }

              $stmt = $dbh-> prepare($sqlDELETE);
              $stmt->execute();

              // INSERTA LOS REGISTROS FALTANTES
              $sqlInsert= "INSERT INTO actividades_proyecto (`idProyecto`,`idActividad`) VALUES ";
              if ($addAditionalResources) {
                  $sqlInsertAditionalResources= "INSERT INTO actividad_recursos_adicionales (`idActividades_proyecto`,`idUsuario`) VALUES ";
              }

              $i=0;
              $actividades_count = count($dataSQLINSERT);
              $newActivities = 0;
              // $data = $actividades_count;
              // echo $data;

              foreach($dataSQLINSERT as $item) {

                  $end = ($i == $actividades_count-1) ? ';' : ',';

                  $sqlInsert .= "('".$idProyecto."','".$item."')".$end;
                  $i++;
                  $newActivities++;
              }

              $stmt = $dbh-> prepare($sqlInsert);
              // $stmt->execute();
              if ($stmt->execute()) {
                  $currentId = $dbh->lastInsertId();

                  for ($i=0; $i < $newActivities; $i++) {
                    $end = ($i == $actividades_count-1) ? ';' : ',';
                    if ($addAditionalResources) {
                        $sqlInsertAditionalResources .= "('".$currentId."','".$usuario->idUsuario."')".$end;
                    }
                    $currentId++;
                  }
                  if ($addAditionalResources) {
                      $stmtAditionalResources = $dbh-> prepare($sqlInsertAditionalResources);
                      $stmtAditionalResources->execute();
                  }

                  $stmt2 = $dbh-> prepare("SELECT idActividades_proyecto, actividad.nombre, IFNULL(fechaInicio,'') AS fechaInicio, IFNULL(fechaEntrega,'') AS fechaEntrega, idProyecto, actividades_proyecto.idActividad
                                          FROM actividades_proyecto
                                          INNER JOIN actividad
                                          ON actividades_proyecto.idActividad = actividad.idActividad
                                          WHERE idProyecto = $idProyecto");
                  $stmt2->execute();

                  $data2 = array();
                  $data2['result'] = $stmt2->fetchAll();
                  echo json_encode($data2, JSON_UNESCAPED_UNICODE);
              }
              else {
                  echo "error";
              }
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'loadCheckedActivities') {
          try {
              // Funcion para llenar Selector de puesto de formulario
              $idProyecto = cleanInput($_POST['idProyecto']);
              $stmt = $dbh-> prepare("SELECT idActividad FROM actividades_proyecto WHERE idProyecto = $idProyecto");
              $stmt->execute();
              $data = array();

              $data['result'] = $stmt->fetchAll();
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'editarEnsamble') {
            try {
                $id = cleanInput($_POST['idEnsamble']);
                $numParte = cleanInput($_POST['numParte']);
                $workorder = cleanInput($_POST['workorder']);
                $cantReq = cleanInput($_POST['cantReq']);
                $cantTerm = cleanInput($_POST['cantTerm']);
                $notas = cleanInput($_POST['notas']);

                $stmt = $dbh->prepare("UPDATE ensambles SET numParte = '$numParte', workorder = '$workorder',
                                                cantReq = $cantReq, cantTerm = $cantTerm, notas = '$notas'
                                        WHERE idEnsamble = $id");
                $stmt->execute();
                $stmt2 = $dbh-> prepare("SELECT numParte, workorder, cantReq, cantTerm, notas
                                         FROM ensambles
                                         WHERE idEnsamble = $id");
                $stmt2->execute();
                $data['result'] = $stmt2->fetch();
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarCliente') {
            try {
                $id = cleanInput($_POST['idCliente']);
                $nombreCliente = cleanInput($_POST['nombreCliente']);
                $comentarios = cleanInput($_POST['comentarios']);

                // CHECK FOR NO DUPLICATED NAME
                $stmt2 = $dbh-> prepare("SELECT nombreCliente FROM cliente WHERE nombreCliente=:nombreCliente AND idCliente <> :idCliente");
                $stmt2->bindParam(':nombreCliente', $nombreCliente);
                $stmt2->bindParam(':idCliente', $id);
                $stmt2->execute();
                $result = $stmt2->fetchAll();
                // En caso de ya existir se muestra un error al usuario
                if ($result!=null) {
                    echo "DUPLICATED";
                } else {
                    // PROCEED TO RECORD
                    $stmt = $dbh->prepare("UPDATE cliente SET nombreCliente = '$nombreCliente', comentarios = '$comentarios'
                                            WHERE idCliente = $id");
                    $stmt->execute();
                    $stmt2 = $dbh-> prepare("SELECT nombreCliente, comentarios
                                             FROM cliente
                                             WHERE idCliente = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarServicio') {
            try {
                $id = cleanInput($_POST['idProyectoServicio']);
                $servicio = cleanInput($_POST['servicio']);
                $descripcion = cleanInput($_POST['descripcion']);

                // CHECK FOR NO DUPLICATED NAME
                $stmt2 = $dbh-> prepare("SELECT servicio FROM proyecto_servicio WHERE servicio=:servicio AND idProyectoServicio <> :idProyectoServicio");
                $stmt2->bindParam(':servicio', $servicio);
                $stmt2->bindParam(':idProyectoServicio', $id);
                $stmt2->execute();
                $result = $stmt2->fetchAll();
                // En caso de ya existir se muestra un error al usuario
                if ($result!=null) {
                    echo "DUPLICATED";
                } else {
                    // PROCEED TO RECORD
                    $stmt = $dbh->prepare("UPDATE proyecto_servicio SET servicio = '$servicio', descripcion = '$descripcion'
                                            WHERE idProyectoServicio = $id");
                    $stmt->execute();
                    $stmt2 = $dbh-> prepare("SELECT servicio, descripcion
                                             FROM proyecto_servicio
                                             WHERE idProyectoServicio = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarRequester') {
            try {
                $id = cleanInput($_POST['idProyectoRequester']);
                $nombre = cleanInput($_POST['nombre']);

                // CHECK FOR NO DUPLICATED NAME
                $stmt2 = $dbh-> prepare("SELECT nombre FROM proyecto_requester WHERE nombre=:nombre AND idProyectoRequester <> :idProyectoRequester");
                $stmt2->bindParam(':nombre', $nombre);
                $stmt2->bindParam(':idProyectoRequester', $id);
                $stmt2->execute();
                $result = $stmt2->fetchAll();
                // En caso de ya existir se muestra un error al usuario
                if ($result!=null) {
                    echo "DUPLICATED";
                } else {
                    // PROCEED TO RECORD
                    $stmt = $dbh->prepare("UPDATE proyecto_requester SET nombre = '$nombre'
                                            WHERE idProyectoRequester = $id");
                    $stmt->execute();
                    $stmt2 = $dbh-> prepare("SELECT nombre
                                             FROM proyecto_requester
                                             WHERE idProyectoRequester = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      }  elseif ($_POST['accion'] == 'editarTipoArchivo') {
            try {
                $id = cleanInput($_POST['idTipoArchivo']);
                $tipo = cleanInput($_POST['tipo']);

                // CHECK FOR NO DUPLICATED NAME
                $stmt2 = $dbh-> prepare("SELECT tipo FROM cotizacion_archivo_tipo WHERE tipo=:tipo AND idCotizacionArchivoTipo <> :idCotizacionArchivoTipo");
                $stmt2->bindParam(':tipo', $tipo);
                $stmt2->bindParam(':idCotizacionArchivoTipo', $id);
                $stmt2->execute();
                $result = $stmt2->fetchAll();
                // En caso de ya existir se muestra un error al usuario
                if ($result!=null) {
                    echo "DUPLICATED";
                } else {
                    // PROCEED TO RECORD
                    $stmt = $dbh->prepare("UPDATE cotizacion_archivo_tipo SET tipo = '$tipo'
                                            WHERE idCotizacionArchivoTipo = $id");
                    $stmt->execute();
                    $stmt2 = $dbh-> prepare("SELECT tipo
                                             FROM cotizacion_archivo_tipo
                                             WHERE idCotizacionArchivoTipo = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarInfoContacto') {
            try {
                $id = cleanInput($_POST['idContacto']);
                $email = cleanInput($_POST['email']);
                $telefono = cleanInput($_POST['telefono']);

                $stmt = $dbh->prepare("UPDATE cliente_contacto SET email = '$email', telefono = '$telefono'
                                        WHERE idClienteContacto	 = $id");
                $stmt->execute();
                $stmt2 = $dbh-> prepare("SELECT email, telefono
                                         FROM cliente_contacto
                                         WHERE idClienteContacto = $id");
                $stmt2->execute();
                $data['result'] = $stmt2->fetch();
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarVenta') {
            try {
                $id = cleanInput($_POST['idVenta']);
                $venta = cleanInput($_POST['venta']);
                $notas = cleanInput($_POST['notas']);

                $stmt = $dbh->prepare("UPDATE ventas SET venta = $venta, notas = '$notas'
                                        WHERE idVenta = $id");
                if ($stmt->execute()) {
                    $stmt2 = $dbh-> prepare("SELECT venta, notas
                                             FROM ventas
                                             WHERE idVenta = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }else {
                    $data['result'] = "error";
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarActividad') {
            try {
                $id = cleanInput($_POST['id']);
                $name = cleanInput($_POST['dataName']);
                $desc = cleanInput($_POST['dataDesc']);
                $tipo = cleanInput($_POST['dataTipo']);
                $resp = cleanInput($_POST['dataResp']);
                $obsolet = cleanInput($_POST['dataObsolet']);

                if ($obsolet == "YES") {
                    $obsolet = 1;
                }else {
                    $obsolet = 0;
                }
                $stmt = $dbh->prepare("UPDATE actividad SET nombre = '$name', descripcion = '$desc', tipo = '$tipo', resp = '$resp', obsoleta = $obsolet
                                        WHERE idActividad = $id");
                if ($stmt->execute()) {
                    $stmt2 = $dbh-> prepare("SELECT nombre, descripcion, tipo, resp, obsoleta
                                             FROM actividad
                                             WHERE idActividad = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }else {
                    $data['result'] = "error";
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarActividadHoras') {
            try {
                $id = cleanInput($_POST['idActividad']);
                $horasLow = cleanInput($_POST['horasLow']);
                $horasMid = cleanInput($_POST['horasMid']);
                $horasHigh = cleanInput($_POST['horasHigh']);

                $stmt = $dbh->prepare("UPDATE actividad SET horasLow = $horasLow, horasMid = $horasMid, horasHigh = $horasHigh
                                        WHERE idActividad = $id");
                if ($stmt->execute()) {
                    $stmt2 = $dbh-> prepare("SELECT horasLow, horasMid, horasHigh
                                             FROM actividad
                                             WHERE idActividad = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }else {
                    $data['result'] = "error";
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'cargarListadoCorto') {
            try {
                $id = cleanInput($_POST['idProyecto']);
                $stmt = $dbh-> prepare("SELECT idProyecto, longestMaterial, date(longestETA) AS longestETA
                                         FROM proyecto
                                         WHERE idProyecto = $id");
                $stmt->execute();
                $data['result'] = $stmt->fetch();
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'asignarCorto') {
            try {
                $id = cleanInput($_POST['idProyecto']);
                $longestMaterial = cleanInput($_POST['longestMaterial']);
                $longestETA = cleanInput($_POST['longestETA']);

                $stmt = $dbh->prepare("UPDATE proyecto SET longestMaterial = '$longestMaterial', longestETA = '$longestETA'
                                        WHERE idProyecto = $id");
                $stmt->execute();
                $stmt2 = $dbh-> prepare("SELECT idProyecto, longestMaterial, date(longestETA) AS longestETA
                                         FROM proyecto
                                         WHERE idProyecto = $id");
                $stmt2->execute();
                $data['result'] = $stmt2->fetch();
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'asignarAprobador') {
            try {
                $id = cleanInput($_POST['idActividades_proyecto']);
                $idUsuarioAsignado = cleanInput($_POST['idUsuarioAsignado']);
                $motivoReq = cleanInput($_POST['motivoReq']);

                $stmt = $dbh-> prepare("INSERT INTO usuarios_asignados (idActividades_proyecto, idUsuario, motivoReq)
                                        VALUES (?, ?, ?)");
                // Se asignan los valores a la consulta preparada
                $stmt->bindParam(1, $id);
                $stmt->bindParam(2, $idUsuarioAsignado);
                $stmt->bindParam(3, $motivoReq);
                $stmt->execute();

                $idInserted = $dbh->lastInsertId();
                $stmt2 = $dbh-> prepare("SELECT *
                                         FROM usuarios_asignados
                                         WHERE idUsuarioAsignado = $idInserted");
                $stmt2->execute();

                $data['result'] = $stmt2->fetch();
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }
      } elseif ($_POST['accion'] == 'crearTicket') {
            try {
                $idUser = $_SESSION['idUsuario'];
                $idTicketType = cleanInput($_POST['idTicketType']);
                $title = cleanInput($_POST['title']);
                $issue = cleanInput($_POST['issue']);

                $stmt = $dbh-> prepare("INSERT INTO ticket (title, issue, idTicketType, idUser)
                                        VALUES (?, ?, ?, ?)");
                // Se asignan los valores a la consulta preparada
                $stmt->bindParam(1, $title);
                $stmt->bindParam(2, $issue);
                $stmt->bindParam(3, $idTicketType);
                $stmt->bindParam(4, $idUser);
                $stmt->execute();

                $idInserted = $dbh->lastInsertId();
                $stmt2 = $dbh-> prepare("SELECT t.idTicket, t.title, t.issue, tt.type, ts.status, e.nombre AS createdBy,
                                                  DATE(t.createdAt) AS createdAt, t.response, DATE(t.assignedDate) AS assignedDate,
                                                  DATE(t.dueDate) AS dueDate, t.hrs, t.idTicketStatus, t.idTicketType,
                                                  DATE(t.completedDate) AS completedDate
                                          FROM ticket AS t
                                          INNER JOIN ticket_type AS tt
                                          ON t.idTicketType = tt.idTicketType
                                          INNER JOIN ticket_status AS ts
                                          ON t.idTicketStatus = ts.idTicketStatus
                                          INNER JOIN usuario AS u
                                          ON t.idUser = u.idUsuario
                                          INNER JOIN empleado AS e
                                          ON u.idEmpleado = e.idEmpleado
                                           WHERE idTicket = $idInserted");
                $stmt2->execute();

                $data['result'] = $stmt2->fetch();
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }
      } elseif ($_POST['accion'] == 'cambiarContrasena') {
            try {
                $id = $_SESSION['idUsuario'];
                $contraActual = cleanInput($_POST['contraActual']);
                $nuevaContra = cleanInput($_POST['nuevaContra']);
                $cifrada = md5($nuevaContra);

                // Valida contrasena Actual
                $stmtContAct = $dbh-> prepare("SELECT contrasena FROM usuario WHERE idUsuario=:idUsuario");
                $stmtContAct->bindParam(':idUsuario', $id);
                $stmtContAct->execute();

                while ($result = $stmtContAct->fetch()) {
                    if ($result->contrasena == md5($contraActual)) {
                      $stmt = $dbh->prepare("UPDATE usuario SET contrasena = '$cifrada'
                                              WHERE idUsuario = $id");
                      if ($stmt->execute()) {
                          echo "success";
                      } else {
                          echo "errorDB";
                      }
                    }else {
                       echo "noMatch";
                    }
                }


                //
                // $stmt = $dbh->prepare("UPDATE proyecto SET longestMaterial = '$longestMaterial', longestETA = '$longestETA'
                //                         WHERE idProyecto = $id");
                // $stmt->execute();
                // $stmt2 = $dbh-> prepare("SELECT idProyecto, longestMaterial, date(longestETA) AS longestETA
                //                          FROM proyecto
                //                          WHERE idProyecto = $id");
                // $stmt2->execute();
                // $data['result'] = $stmt2->fetch();
                // echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                echo "error";
                // echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'completarActividad') {
            try {
                $id = cleanInput($_POST['idActividades_proyecto']);
                $idUsuario = $_SESSION['idUsuario'];
                $statusActividad = cleanInput($_POST['statusActividad']);

                $data = array();
                $SQLFields;
                switch ($statusActividad) {
                  case '-1':  // -1     N/A
                      $notas = cleanInput($_POST['notas']);
                      $SQLFields = ",notas = '$notas',entregadoPor = '$idUsuario',fechaEntrega = now()";
                      break;
                  case '1':   // 1      Completado
                      $ubicacion = cleanPath(str_replace("\\","\\\\",$_POST["path"]));
                      $SQLFields = ",ubicacion = '$ubicacion',entregadoPor = '$idUsuario',fechaEntrega = now()";
                      break;
                  case '2':   // 2      Aprobado
                      $SQLFields = ",aprobadoPor = '$idUsuario',fechaAprobacion = now()";
                      break;
                  case '3':   // 3      Rechazado
                      $notas = cleanInput($_POST['notas']);
                      $SQLFields = ",aprobadoPor = '$idUsuario',fechaAprobacion = now(),notas = '$notas'";
                      break;
                  default:
                      break;
                }

                $stmt = $dbh->prepare("UPDATE actividades_proyecto SET completado = '$statusActividad'" . $SQLFields .
                                        "WHERE idActividades_proyecto = $id");
                if ($stmt->execute()) {
                    $stmt2 = $dbh-> prepare("SELECT idActividades_proyecto, completado, notas, entregadoPor, fechaEntrega
                                             FROM actividades_proyecto
                                             WHERE idActividades_proyecto = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                } else {
                    $data['result'] = "error";
                }
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'completarActividadAdicional') {
            try {
                $id = cleanInput($_POST['idRecursosAdicionales']);
                $idUsuario = $_SESSION['idUsuario'];
                $statusActividad = cleanInput($_POST['statusActividad']);

                $data = array();
                $SQLFields;
                switch ($statusActividad) {
                  case '-1':  // -1     N/A
                      $notas = cleanInput($_POST['notas']);
                      $SQLFields = ",notas = '$notas',entregadoPor = '$idUsuario',fechaEntrega = now()";
                      break;
                  case '1':   // 1      Completado
                      $ubicacion = cleanPath(str_replace("\\","\\\\",$_POST["path"]));
                      $SQLFields = "ubicacion = '$ubicacion',fechaEntrega = now()";
                      break;
                  case '2':   // 2      Aprobado
                      $SQLFields = ",aprobadoPor = '$idUsuario',fechaAprobacion = now()";
                      break;
                  default:
                      break;
                }

                $stmt = $dbh->prepare("UPDATE actividad_recursos_adicionales SET " . $SQLFields .
                                        "WHERE idRecursosAdicionales = $id");
                if ($stmt->execute()) {
                    $stmt2 = $dbh-> prepare("SELECT idRecursosAdicionales, ubicacion, comentarios, fechaEntrega
                                             FROM actividad_recursos_adicionales
                                             WHERE idRecursosAdicionales = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                } else {
                    $data['result'] = "error";
                }
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'completarAsignacionUsuarios') {
            try {
                $id = cleanInput($_POST['idUsuarioAsignado']);
                $notas = cleanInput($_POST['notas']);

                $stmt = $dbh->prepare("UPDATE usuarios_asignados SET notas = '$notas', fechaAprobacion = now()
                                        WHERE idUsuarioAsignado = $id");
                if ($stmt->execute()) {
                    echo "success";
                } else {
                    echo "error";
                }
            } catch (\Exception $e) {
                echo "error";
            }
      } elseif ($_POST['accion'] == 'completarEtapaStatus') {
            try {
                $id = cleanInput($_POST['idProyectoAprobador']);

                $stmt = $dbh->prepare("SELECT COUNT(pa.idProyectoAprobador) AS approvers
                                        FROM proyecto_aprobador_etapa AS pa
                                        WHERE pa.approved = 1 AND idProyectoEtapa = (SELECT idProyectoEtapa
                                                                 FROM proyecto_aprobador_etapa
                                                                 WHERE idProyectoAprobador = $id)");
                 $stmt->execute();
                 $result = $stmt->fetch();
                 if ($result->approvers == 4) {
                     $stmt2 = $dbh->prepare("UPDATE proyecto_etapa SET status = 1, approvedDate = NOW()
                                              WHERE idProyectoEtapa = (SELECT idProyectoEtapa
                                                                       FROM proyecto_aprobador_etapa
                                                                       WHERE idProyectoAprobador = $id)");
                      if($stmt2->execute()) {
                          echo "completed";
                      } else {
                          echo "errorUpdate";
                      }
                 } else {
                     echo "pending";
                 }
            } catch (\Exception $e) {
                echo "error";
            }
      } elseif ($_POST['accion'] == 'validarActividad') {
            try {
                $id = cleanInput($_POST['idActividades_proyecto']);
                $statusActividad = cleanInput($_POST['statusActividad']);

                $stmtUsuariosAsignados = $dbh->prepare("SELECT * from usuarios_asignados
                                              WHERE fechaAprobacion IS NULL AND idActividades_proyecto = $id");
                $stmtUsuariosAsignados->execute();
                $stmtRecursosAsignados = $dbh->prepare("SELECT * from recursos_asignados
                                      WHERE idActividades_proyecto = $id");
                $stmtRecursosAsignados->execute();

                switch ($statusActividad) {
                  case '-1':  // -1     N/A

                      break;
                  case '1':   // 1      Completado

                      if ($stmtUsuariosAsignados->rowCount()) {
                          echo "pendienteAprobacion";
                          return;
                      } else if (!$stmtRecursosAsignados->rowCount()) {
                          echo "noRecursos";
                          return;
                      }
                      break;
                  case '2':   // 2      Aprobado

                      break;
                  case '3':   // 3      Rechazado

                      break;
                  default:
                      break;
                }

                echo "success";
            } catch (\Exception $e) {
                echo "error" . $e;
            }
      } elseif ($_POST['accion'] == 'aprobarEtapaStatus') {
            try {
                $idUsuario = $_SESSION['idUsuario'];
                $id = cleanInput($_POST['idProyectoAprobador']);
                $status = cleanInput($_POST['status']);
                $motivo = cleanInput($_POST['motivo']);

                if (empty($motivo)) {
                    $stmt = $dbh->prepare("UPDATE proyecto_aprobador_etapa SET approved = $status, fechaAprobacion = now()
                                            WHERE idProyectoAprobador = $id");
                } else {
                    $stmt = $dbh->prepare("UPDATE proyecto_aprobador_etapa SET approved = $status, razon = '$motivo', fechaAprobacion = now()
                                            WHERE idProyectoAprobador = $id");
                }

                if ($stmt->execute()) {
                  $stmt2 = $dbh->prepare("UPDATE proyecto_etapa SET status = -1
                                           WHERE idProyectoEtapa = (SELECT idProyectoEtapa
                                                                    FROM proyecto_aprobador_etapa
                                                                    WHERE idProyectoAprobador = $id)");
                   if($stmt2->execute()) {
                      echo $status;
                   } else {
                      echo "Error UPDATE";
                   }
                } else {
                    echo "failed";
                }
            } catch (\Exception $e) {
                echo "error" . $e;
            }
      } elseif ($_POST['accion'] == 'editarActUbicacion') {
            try {
                $id = cleanInput($_POST['idActividades_proyecto']);
                $fechaRequerida = cleanInput($_POST['fechaRequerida']);
                $notas = cleanInput($_POST['notas']);
                $ubicacion = cleanInput(str_replace("\\","\\\\",$_POST["ubicacion"]));

                $stmt = $dbh->prepare("UPDATE actividades_proyecto SET fechaRequerida = '$fechaRequerida', ubicacion = '$ubicacion', notas = '$notas'
                                        WHERE idActividades_proyecto = $id");
                $stmt->execute();
                $stmt2 = $dbh-> prepare("SELECT idActividades_proyecto, DATE(fechaRequerida) AS fechaRequerida, ubicacion, notas
                                         FROM actividades_proyecto
                                         WHERE idActividades_proyecto = $id");
                $stmt2->execute();
                $data['result'] = $stmt2->fetch();
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarRecursoAdicional') {
            try {
                $id = cleanInput($_POST['idRecursosAdicionales']);
                $fechaInicio = cleanInput($_POST['fechaInicio']);
                $fechaRequerida = cleanInput($_POST['fechaRequerida']);
                $comentarios = cleanInput($_POST['comentarios']);
                $ubicacion = cleanInput(str_replace("\\","\\\\",$_POST["ubicacion"]));

                $stmt = $dbh->prepare("UPDATE actividad_recursos_adicionales SET fechaInicio = '$fechaInicio', fechaRequerida = '$fechaRequerida', ubicacion = '$ubicacion', comentarios = '$comentarios'
                                        WHERE idRecursosAdicionales = $id");
                $stmt->execute();
                $stmt2 = $dbh-> prepare("SELECT idRecursosAdicionales, DATE(fechaInicio) AS fechaInicio, DATE(fechaRequerida) AS fechaRequerida, ubicacion, comentarios
                                         FROM actividad_recursos_adicionales
                                         WHERE idRecursosAdicionales = $id");
                $stmt2->execute();
                $data['result'] = $stmt2->fetch();
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarSoporteAdicional') {
        try {
            $id = cleanInput($_POST['idSoporteAdicional']);
            $horas = cleanInput($_POST['horas']);
            $fechaSoporte = cleanInput($_POST['fechaSoporte']);
            $comentarios = cleanInput($_POST['comentarios']);

            $stmt = $dbh->prepare("UPDATE proyecto_soporte_adicional SET horas = '$horas', fechaSoporte = '$fechaSoporte', comentarios = '$comentarios'
                                    WHERE idSoporteAdicional  = $id");
            $stmt->execute();
            $stmt2 = $dbh-> prepare("SELECT DATE(fechaSoporte) AS fechaSoporte, horas, comentarios
                                     FROM proyecto_soporte_adicional
                                     WHERE idSoporteAdicional = $id");
            $stmt2->execute();
            $data['result'] = $stmt2->fetch();
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            $data['result'] = "error";
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            alert($e);
        }
  }  elseif ($_POST['accion'] == 'editarRecurso') {
            try {
                $id = cleanInput($_POST['idRecurso']);
                $fechaInicio = cleanInput($_POST['fechaInicio']);
                $horas = cleanInput($_POST['horas']);

                if (isRealDate($fechaInicio) == true) {
                    $stmt = $dbh->prepare("UPDATE recursos_asignados SET fechaInicio = '$fechaInicio', horas = '$horas'
                                            WHERE idRecurso = $id");
                    $stmt->execute();
                    $stmt2 = $dbh-> prepare("SELECT DATE(fechaInicio) AS fechaInicio, horas
                                             FROM recursos_asignados
                                             WHERE idRecurso = $id");
                    $stmt2->execute();
                    $data['result'] = $stmt2->fetch();
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                } else {
                    $data['result'] = "fechaIncorrecta";
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                alert($e);
            }
      } elseif ($_POST['accion'] == 'editarCotizacionEnsamble') {
            try {
                $id = cleanInput($_POST['idCotizacionEnsamble']);
                $numParte = cleanInput($_POST['numParte']);
                $descripcion = cleanInput($_POST['descripcion']);
                $eau = cleanInput($_POST['eau']);
                $selling_price = cleanInput($_POST['selling_price']);
                $notas = cleanInput($_POST['notas']);

                $stmt = $dbh->prepare("UPDATE cotizacion_ensambles SET numParte = '$numParte', descripcion = '$descripcion', eau = $eau,
                                                                        selling_price = $selling_price, notas = '$notas'
                                        WHERE idCotizacionEnsamble = $id");
                $stmt->execute();
                $stmt2 = $dbh-> prepare("SELECT numParte, descripcion, eau, selling_price, notas
                                         FROM cotizacion_ensambles
                                         WHERE idCotizacionEnsamble = $id");
                $stmt2->execute();
                $data['result'] = $stmt2->fetch();
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }
      } elseif ($_POST['accion'] == 'checkCompletedActivities') {
          try {
              $id = $_POST['idProyecto'];

              $stmtCompleted = $dbh-> prepare("SELECT FORMAT((SELECT COUNT(idActividades_proyecto)
                                                               FROM actividades_proyecto
                                                               WHERE idProyecto = $id AND completado <> 0) /
                                                                    (SELECT COUNT(idActividades_proyecto)
                                               FROM actividades_proyecto
                                               WHERE idProyecto = $id AND completado <> -1) * 100, 0) AS completed");
              if ($stmtCompleted->execute()) {
                  $result = $stmtCompleted->fetch();
                  if ($result->completed==100) {
                      echo "completed";
                  } else {
                      echo "pending";
                  }
              }
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'existStageReport') {
          try {
              $id = $_POST['idProyecto'];
              $idEtapa = $_POST['idEtapa'];

              $stmtCompleted = $dbh-> prepare("SELECT * FROM proyecto_etapa
                                                WHERE idProyecto = $id AND idEtapa = $idEtapa");
              if ($stmtCompleted->execute()) {
                  if ($stmtCompleted->rowCount() > 0) {
                      echo "yes";
                  } else {
                      echo "no";
                  }
              }
          } catch (\Exception $e) {
              echo "ERROR";
          }
      } elseif ($_POST['accion'] == 'checkCompletionStageActivities') {
          try {
              $id = $_POST['idProyecto'];
              $idEtapa = $_POST['idEtapa'];

              $stmtCompleted = $dbh-> prepare("SELECT COUNT(ap.idActividades_proyecto) AS total, (SELECT COUNT(ap.idActividades_proyecto) AS completed
                                                   FROM actividades_proyecto AS ap
                                                   INNER JOIN actividad AS a
                                                   ON ap.idActividad = a.idActividad
                                                   INNER JOIN etapa AS e
                                                   ON a.idEtapa = e.idEtapa
                                                   WHERE ap.idProyecto = $id AND e.idEtapa = $idEtapa AND (ap.completado = -1 OR ap.completado = 1 OR ap.completado = 2)) AS completed
                                                FROM actividades_proyecto AS ap
                                                INNER JOIN actividad AS a
                                                ON ap.idActividad = a.idActividad
                                                INNER JOIN etapa AS e
                                                ON a.idEtapa = e.idEtapa
                                                WHERE ap.idProyecto = $id AND e.idEtapa = $idEtapa");
              if ($stmtCompleted->execute()) {
                  $result = $stmtCompleted->fetch();

                  if ($result->total == 0) {
                      echo "noStageActivities";
                  } else {
                      if ($result->total - $result->completed == 0) {
                          echo "allStageActivitiesCompleted";
                      } else {
                          echo "pendingStageActivities";
                      }
                  }
              }
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'checkStageApprovers') {
          try {
              $id = $_POST['idProyecto'];

              $stmtCompleted = $dbh-> prepare("SELECT idLiderProyecto, idGerenteProyecto, idCoordinadorProyecto, idIngenieroQA
                                                FROM proyecto WHERE idProyecto = $id");
              if ($stmtCompleted->execute()) {
                  $resultado = $stmtCompleted->fetch();

                  if (empty($resultado->idLiderProyecto)) {
                      echo "missingStageApprover";
                      exit;
                  } elseif (empty($resultado->idGerenteProyecto)) {
                      echo "missingStageApprover";
                      exit;
                  } elseif (empty($resultado->idCoordinadorProyecto)) {
                      echo "missingStageApprover";
                      exit;
                  } elseif (empty($resultado->idIngenieroQA)) {
                      echo "missingStageApprover";
                      exit;
                  } else {
                      echo "allStageApproversFound";
                  }
              }
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'createStageReport') {
          try {
              $id = $_POST['idProyecto'];
              $idEtapa = $_POST['idEtapa'];

              $stmtCompleted = $dbh-> prepare("INSERT INTO proyecto_etapa SET  idProyecto = $id, idEtapa = $idEtapa");
              // Ejecutar la consulta preparada
              $result = $stmtCompleted->execute();

              if (!$result) {
                  echo "noStageInserted";
              } else {
                  $idProyectoEtapa = $dbh->lastInsertId();
                  $stmtApprovers = $dbh->prepare("SELECT idLiderProyecto, idGerenteProyecto, idCoordinadorProyecto, idIngenieroQA
                                                    FROM proyecto WHERE idProyecto = $id");
                  if ($stmtApprovers->execute()) {
                      $approvers = $stmtApprovers->fetch();

                      $stmtAssignLiderProyecto = $dbh->prepare("INSERT INTO proyecto_aprobador_etapa (idProyectoEtapa, idUsuario, idRol)
                                                                VALUES ($idProyectoEtapa, $approvers->idLiderProyecto, 1)");
                      $stmtAssignLiderProyecto->execute();
                      $stmtAssignGerenteProyecto = $dbh->prepare("INSERT INTO proyecto_aprobador_etapa (idProyectoEtapa, idUsuario, idRol)
                                                                VALUES ($idProyectoEtapa, $approvers->idGerenteProyecto, 2)");
                      $stmtAssignGerenteProyecto->execute();
                      $stmtAssignCoordinadorProyecto = $dbh->prepare("INSERT INTO proyecto_aprobador_etapa (idProyectoEtapa, idUsuario, idRol)
                                                                    VALUES ($idProyectoEtapa, $approvers->idCoordinadorProyecto, 3)");
                      $stmtAssignCoordinadorProyecto->execute();
                      $stmtAssignLiderProyecto = $dbh->prepare("INSERT INTO proyecto_aprobador_etapa (idProyectoEtapa, idUsuario, idRol)
                                                                VALUES ($idProyectoEtapa, $approvers->idIngenieroQA, 4)");
                      $stmtAssignLiderProyecto->execute();
                      echo "reportCreated";
                  }
              }
          } catch (\Exception $e) {
              alert($e);
          }
      } elseif ($_POST['accion'] == 'awardedProject') {
          try {
              $id = $_POST['idProyecto'];

              $stmtAwarded = $dbh->prepare("UPDATE proyecto SET awarded = 1, dateAwarded = now()
              WHERE idProyecto = $id");
              $stmtAwarded->execute();

              $stmtDiseno = $dbh->prepare("SELECT empleado.correo
                                            FROM `proyecto`
                                            INNER JOIN empleado
                                            ON proyecto.idRespDiseno = empleado.idEmpleado
                                            WHERE idProyecto = $id");
              $stmtDiseno->execute();
              $stmtManu = $dbh->prepare("SELECT empleado.correo
                                            FROM `proyecto`
                                            INNER JOIN empleado
                                            ON proyecto.idRespManu = empleado.idEmpleado
                                            WHERE idProyecto = $id");
              $stmtManu->execute();
              $stmtProject = $dbh->prepare("SELECT projectID, nombre
                                            FROM `proyecto`
                                            WHERE idProyecto = $id");
              $stmtProject->execute();
              $resultado = $stmtProject->fetch();

              $diseno = $stmtDiseno->fetchColumn();
              $manu = $stmtManu->fetchColumn();

              $to_email = $diseno . ";" . $manu;
              $subject = 'Project Awarded';
              $message = 'Project ' . $resultado->projectID . " - " . $resultado->nombre . ' Has been awarded<br>Please complete any pending activity as soon as posible.<br>Please follow Next Link http://172.20.3.169/perfil.php';
              $headers = 'From: noreply@nai-group.com';
              // mail($to_email,$subject,$message,$headers);

              echo "success";
          } catch (\Exception $e) {
              echo $e;
          }
      } elseif ($_POST['accion'] == 'cancelAwardedProject') {
          try {
              $id = $_POST['idProyecto'];

              $stmtAwarded = $dbh->prepare("UPDATE proyecto SET awarded = 0
              WHERE idProyecto = $id");

              if ($stmtAwarded->execute()) {
                  echo "success";
              }else {
                  echo "error";
              }
          } catch (\Exception $e) {
              echo $e;
          }
      } elseif ($_POST['accion'] == 'cancelAwardedQuote') {
          try {
              $id = $_POST['idCotizacion'];

              $stmtAwarded = $dbh->prepare("UPDATE cotizacion SET awarded = 0
              WHERE idCotizacion = $id");

              if ($stmtAwarded->execute()) {
                  echo "success";
              }else {
                  echo "error";
              }
          } catch (\Exception $e) {
              echo $e;
          }
      } elseif ($_POST['accion'] == 'awardedQuote') {
          try {
              $id = $_POST['idCotizacion'];

              $stmtAwarded = $dbh->prepare("UPDATE cotizacion SET awarded = 1, dateAwarded = now()
              WHERE idCotizacion = $id");
              $stmtAwarded->execute();

              $stmtResponsable = $dbh->prepare("SELECT empleado.correo
                                                FROM `cotizacion`
                                                INNER JOIN empleado
                                                ON cotizacion.idResponsable = empleado.idEmpleado
                                                WHERE idCotizacion = $id");
              $stmtResponsable->execute();
              $stmtQuote = $dbh->prepare("SELECT quoteId, nombre
                                            FROM `cotizacion`
                                            WHERE idCotizacion = $id");
              $stmtQuote->execute();
              $resultado = $stmtQuote->fetch();

              $responsable = $stmtResponsable->fetchColumn();

              $to_email = $responsable;
              $subject = 'Quote Awarded';
              $message = 'Quote ' . $resultado->quoteId . " - " . $resultado->nombre . ' Has been awarded<br>Please complete any pending activity as soon as posible.<br>Please follow Next Link http://172.20.3.169/perfil.php';
              $headers = 'From: noreply@nai-group.com';
              // mail($to_email,$subject,$message,$headers);

              echo "success";
          } catch (\Exception $e) {
              echo $e;
          }
      } elseif ($_POST['accion'] == 'validateForAwarded') {
          try {
              $id = $_POST['idCotizacion'];

              $stmt = $dbh->prepare("SELECT idCotizacion
                                    FROM cotizacion
                                    WHERE fechaLanzamiento IS NOT NULL AND idCotizacion = $id");
              $stmt->execute();

              if ($stmt->rowCount() > 0) {
                  echo 'success';
              } else {
                  echo 'emptyData';
              }
          } catch (\Exception $e) {
              echo $e;
          }
      }  elseif ($_POST['accion'] == 'cambiarPrioridad') {
          try {
              $id = $_POST['idProyecto'];
              $prioridad = cleanInput($_POST['idPrioridad']);

                $stmt = $dbh->prepare("UPDATE proyecto SET prioridad = $prioridad
                                              WHERE idProyecto = $id");
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $data['result'] = "success";
                    $data['priority'] = $prioridad;
                }else {
                    $data['result'] = "error";
                }
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
          } catch (\Exception $e) {
              alert($e);
          }
      }  elseif ($_POST['accion'] == 'eliminarFile') {
          $idCotizacion = cleanInput($_POST['idCotizacion']);
          $filename = $_POST['filename'];

          if (unlink($_SERVER['DOCUMENT_ROOT'] . "/images/quotes/" . $idCotizacion . "/" . $filename)) {
              $data = array();
              try {
                  $id = cleanInput($_POST['id']);

                  // Funcion para llenar Selector de puesto de formulario
                  $stmt = $dbh-> prepare("DELETE FROM cotizacion_archivo
                                           WHERE idCotizacionArchivo = $id");
                  $stmt->execute();

                  if ($stmt->rowCount() > 0) {
                      $data['result'] = "deleted";
                  }else {
                      $data['result'] = "error";
                  }
                  echo json_encode($data, JSON_UNESCAPED_UNICODE);
              } catch (\Exception $e) {
                  $data['result'] = "error";
                  echo json_encode($data, JSON_UNESCAPED_UNICODE);
              }
          } else {
              $data['result'] = "error";
              echo json_encode($data, JSON_UNESCAPED_UNICODE);
          }
      } elseif ($_POST['accion'] == 'eliminarRecurso') {
            $id = cleanInput($_POST['idRecursosAdicionales']);

            $data = array();
            try {
                // Funcion para llenar Selector de puesto de formulario
                $stmt = $dbh-> prepare("DELETE FROM actividad_recursos_adicionales
                WHERE idRecursosAdicionales = $id");
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $data['result'] = "deleted";
                }else {
                    $data['result'] = "error";
                }
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }
      } elseif ($_POST['accion'] == 'eliminarSoporte') {
            $id = cleanInput($_POST['idSoporteAdicional']);

            $data = array();
            try {
                // Funcion para llenar Selector de puesto de formulario
                $stmt = $dbh-> prepare("DELETE FROM proyecto_soporte_adicional
                WHERE idSoporteAdicional = $id");
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $data['result'] = "deleted";
                }else {
                    $data['result'] = "error";
                }
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }
        }
  }

  exit;


  // $conexion=mysqli_connect('localhost','root','','prueba');
  // $continente=$_POST['continente'];
  //
  // 	$sql="SELECT id,
  // 			 id_continente,
  // 			 pais
  // 		from t_mundo
  // 		where id_continente='$continente'";
  //
  // 	$result=mysqli_query($conexion,$sql);
  //
  // 	$cadena="<label>SELECT 2 (paises)</label>
  // 			<select id='lista2' name='lista2'>";
  //
  // 	while ($ver=mysqli_fetch_row($result)) {
  // 		$cadena=$cadena.'<option value='.$ver[0].'>'.utf8_encode($ver[2]).'</option>';
  // 	}
  //
  // 	echo  $cadena."</select>";

  // print_r($_POST);
  // exit;
