<?php
  include "inc/conexion.php";

  $nombre=$_POST['nombre'];  $descripcion=$_POST['descripcion'];  $modelo=$_POST['modelo'];
  $talla=$_POST['talla']; $color=$_POST['color'];  $descontinuado=$_POST['descontinuado'];
  $precioCompra=$_POST['precioCompra']; $precioVenta=$_POST['precioVenta'];

  if ($dbh!=null) {  //Se logró la conexión con la BD
      // Se realiza una consulta preparada
      $stmt = $dbh-> prepare("INSERT INTO productos (nombre, descripcion, modelo, talla, color, descontinuado, precioCompra, precioVenta)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      // Se asignan los valores a la consulta preparada
      $stmt->bindParam(1, $nombre);
      $stmt->bindParam(2, $descripcion);
      $stmt->bindParam(3, $modelo);
      $stmt->bindParam(4, $talla);
      $stmt->bindParam(5, $color);
      $stmt->bindParam(6, $descontinuado);
      $stmt->bindParam(7, $precioCompra);
      $stmt->bindParam(8, $precioVenta);
      // Ejecutar la consulta preparada
      $stmt->execute();
      //Cierra conexión
      $dbh=null;
  } else {
      echo "DataBase Connection Error. Please try again later.";
      die();
  }
