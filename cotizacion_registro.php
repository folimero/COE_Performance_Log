<?php
  include "inc/conexion.php";
  session_start();

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
  $quoteID=cleanInput($_POST['quoteID']);
  if (isset($_POST['cliente'])) {
      $cliente=cleanInput($_POST['cliente']);
  }
  $nombre=cleanInput($_POST['nombre']);
  if (isset($_POST['descripcion'])){$descripcion=cleanInput($_POST['descripcion']);}
  $tipoCotizacion=cleanInput($_POST['tipoCotizacion']);
  $ventasPotenciales=cleanInput($_POST['ventasPotenciales']);
  $responCotizaciones=cleanInput($_POST['responCotizaciones']);
  $repreVentas=cleanInput($_POST['repreVentas']);
  $overallComplet=cleanInput($_POST['overallComplet']);
  $status=cleanInput($_POST['status']);
  $uniqueFG=cleanInput($_POST['uniqueFG']);
  $notas=cleanInput($_POST['notas']);
  $fechaInicio=cleanInput($_POST['fechaInicio']);
  $fechaLanzamiento=cleanInput($_POST['fechaLanzamiento']);
  $fechaReqCliente=cleanInput($_POST['fechaReqCliente']);
  $sourcMatStartDate=cleanInput($_POST['sourcMatStartDate']);
  $sourcMatEndDate=cleanInput($_POST['sourcMatEndDate']);
  $dateBDM=cleanInput($_POST['dateBDM']);


  // Valida los campos de fecha para que sean compatibles con la BD
  if (empty($fechaInicio)) {
      $fechaInicio = null;
  }
  if (empty($fechaLanzamiento)) {
      $fechaLanzamiento = null;
  }
  if (empty($fechaReqCliente)) {
      $fechaReqCliente = null;
  }
  if (empty($sourcMatStartDate)) {
      $sourcMatStartDate = null;
  }
  if (empty($sourcMatEndDate)) {
      $sourcMatEndDate = null;
  }
  if (empty($dateBDM)) {
      $dateBDM = null;
  }
  // Validacion de campos
  if (isset($_POST['awarded'])) {
    $awarded = cleanInput($_POST['awarded']);
  }else {
    $awarded = 0;
  }
  if (isset($_POST['clienteContacto'])) {
    $clienteContacto = cleanInput($_POST['clienteContacto']);
  }else {
    $clienteContacto = null;
  }
  if (isset($_POST['repreVentas'])) {
    $repreVentas = cleanInput($_POST['repreVentas']);
  }else {
    $repreVentas = null;
  }
  if (isset($_POST['BOMType'])) {
    $BOMType = cleanInput($_POST['BOMType']);
  }else {
    $BOMType = null;
  }
  if (isset($_POST['lineItems']) && $_POST['lineItems'] <> "") {
    $lineItems= cleanInput($_POST['lineItems']);
  }else {
    $lineItems = null;
  }
  if (isset($_POST['uniqueFG']) && $_POST['uniqueFG'] <> "") {
    $uniqueFG= cleanInput($_POST['uniqueFG']);
  }else {
    $lineItems = null;
  }
  if (isset($_POST['consolidatedEAU'])) {
    $consolidatedEAU= cleanInput($_POST['consolidatedEAU']);
  }else {
    $consolidatedEAU = null;
  }
  if (isset($_POST['consOTC'])) {
    $consOTC= cleanInput($_POST['consOTC']);
  }else {
    $consOTC = null;
  }

  if ($dbh!=null) {  //Se logró la conexión con la BD
      if (empty($quoteID) || empty($nombre) || empty($tipoCotizacion) ||
              empty($responCotizaciones) || empty($status)) {
                  echo "errorVacio";
                  die();
      }
      // En caso de que haya pasado todas las validaciones, se procede a insetar el registro en la base de datos
      else {
          if ($_POST['edicion'] == 1) {
              if(isset($_POST['idCotizacion'])){$idCotizacion = cleanInput($_POST['idCotizacion']);}
                  // EDICION
                  $stmt = $dbh-> prepare("UPDATE cotizacion SET idCliente=:idCliente, quoteID=:quoteID, nombre=:nombre, descripcion=:descripcion, idTipoCotizacion=:idTipoCotizacion,
                  ventasPotenciales=:ventasPotenciales, idClienteContacto=:idClienteContacto, idResponsable=:idResponsable, idRepreVentas=:idRepreVentas, uniqueFG=:uniqueFG, lineItems=:lineItems,
                  BOMType=:BOMType, overallComplet=:overallComplet, idStatus=:idStatus, notas=:notas, fechaInicio=:fechaInicio, fechaLanzamiento=:fechaLanzamiento,
                  fechaReqCliente=:fechaReqCliente, consolidatedEAU=:consolidatedEAU, consOTC=:consOTC, sourcMatStartDate=:sourcMatStartDate, sourcMatEndDate=:sourcMatEndDate, dateBDM=:dateBDM
                  WHERE idCotizacion = $idCotizacion");
                  // Valida los campos de fecha para que sean compatibles con la BD
                  if (empty($fechaInicio)) {
                      $fechaInicio = null;
                  }
                  if (empty($fechaLanzamiento)) {
                      $fechaLanzamiento = null;
                  }
                  if (empty($fechaReqCliente)) {
                      $fechaReqCliente = null;
                  }
                  if (empty($sourcMatStartDate)) {
                      $sourcMatStartDate = null;
                  }
                  if (empty($sourcMatEndDate)) {
                      $sourcMatEndDate = null;
                  }
                  if (empty($dateBDM)) {
                      $dateBDM = null;
                  }
                  if (empty($appTrackID)) {
                      $appTrackID = null;
                  }
                  $stmt->bindParam(':quoteID', $quoteID);
                  $stmt->bindParam(':idCliente', $cliente);
                  $stmt->bindParam(':nombre', $nombre);
                  $stmt->bindParam(':descripcion', $descripcion);
                  $stmt->bindParam(':idTipoCotizacion', $tipoCotizacion);
                  $stmt->bindParam(':ventasPotenciales', $ventasPotenciales);
                  $stmt->bindParam(':idClienteContacto', $clienteContacto);
                  $stmt->bindParam(':idResponsable', $responCotizaciones);
                  $stmt->bindParam(':idRepreVentas', $repreVentas);
                  $stmt->bindParam(':uniqueFG', $uniqueFG);
                  $stmt->bindParam(':lineItems', $lineItems);
                  $stmt->bindParam(':BOMType', $BOMType);
                  $stmt->bindParam(':overallComplet', $overallComplet);
                  $stmt->bindParam(':idStatus', $status);
                  $stmt->bindParam(':notas', $notas);
                  $stmt->bindParam(':fechaInicio', $fechaInicio);
                  $stmt->bindParam(':fechaLanzamiento', $fechaLanzamiento);
                  $stmt->bindParam(':fechaReqCliente', $fechaReqCliente);
                  $stmt->bindParam(':consolidatedEAU', $consolidatedEAU);
                  $stmt->bindParam(':sourcMatStartDate', $sourcMatStartDate);
                  $stmt->bindParam(':sourcMatEndDate', $sourcMatEndDate);
                  $stmt->bindParam(':dateBDM', $dateBDM);
                  $stmt->bindParam(':consOTC', $consOTC);
                  // Ejecutar la consulta preparada
                  $result = $stmt->execute();

                  if (!$result) {
                    $res = $stmt->errorInfo();
                    print_r($res);
                  } else {
                      echo "successEdit";
                  }
          } else { // NEW RECORD
              $stmt2 = $dbh-> prepare("SELECT * FROM cotizacion WHERE quoteID=:quoteID");
              $stmt2->bindParam(':quoteID', $quoteID);
              $stmt2->execute();
              $result = $stmt2->fetchAll();
              // En caso de ya existir se muestra un error al usuario
              if ($result!=null) {
                  echo "duplicatedID";
                  die();
              }
              if (empty($cliente)) {
                  echo "errorVacio";
                  die();
              }

            // Se realiza una consulta preparada
            $stmt = $dbh-> prepare("INSERT INTO cotizacion (quoteID, idCliente, nombre, descripcion, idTipoCotizacion , ventasPotenciales, idClienteContacto,
            idResponsable, idRepreVentas, uniqueFG , lineItems, BOMType, overallComplet, idStatus, notas, fechaInicio, fechaLanzamiento, fechaReqCliente,
            consolidatedEAU, sourcMatStartDate, sourcMatEndDate, dateBDM) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // Se asignan los valores a la consulta preparada
            $stmt->bindParam(1, $quoteID);
            $stmt->bindParam(2, $cliente);
            $stmt->bindParam(3, $nombre);
            $stmt->bindParam(4, $descripcion);
            $stmt->bindParam(5, $tipoCotizacion);
            $stmt->bindParam(6, $ventasPotenciales);
            $stmt->bindParam(7, $clienteContacto);
            $stmt->bindParam(8, $responCotizaciones);
            $stmt->bindParam(9, $repreVentas);
            $stmt->bindParam(10, $uniqueFG);
            $stmt->bindParam(11, $lineItems);
            $stmt->bindParam(12, $BOMType);
            $stmt->bindParam(13, $overallComplet);
            $stmt->bindParam(14, $status);
            $stmt->bindParam(15, $notas);
            $stmt->bindParam(16, $fechaInicio);
            $stmt->bindParam(17, $fechaLanzamiento);
            $stmt->bindParam(18, $fechaReqCliente);
            $stmt->bindParam(19, $consolidatedEAU);
            $stmt->bindParam(20, $sourcMatStartDate);
            $stmt->bindParam(21, $sourcMatEndDate);
            $stmt->bindParam(22, $dateBDM);
            // Ejecutar la consulta preparada
            $result = $stmt->execute();

            if (!$result) {
              $res = $stmt->errorInfo();
              $message = $res;
              print_r($res);
            } else {
                echo "success";
            }
          }
      }
      //Cierra conexión
      $dbh=null;
  } else {
      echo "errorDB";
      die();
  }
