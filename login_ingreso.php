<?php
    if (!session_id()) {
        session_start();
    }
    include "inc/conexion.php";

    $usuario=$_POST['usuario'];    $contrasena=$_POST['contrasena'];
    $URL = "index.php";
    if ($dbh!=null) {  //Se logró conectar
        // Se prepara una consulta con los parametros ingresados en el formulario
        $stmt = $dbh->prepare("SELECT idUsuario, empleado.idEmpleado, usuarioNombre, empleado.nombre, idDepartamento
                              FROM usuario
                              INNER JOIN empleado
                              ON usuario.idEmpleado = empleado.idEmpleado
                              WHERE usuarioNombre=:usuario AND contrasena=:contrasena");
        $stmt->bindParam(':usuario', $usuario);
        $cifrada=md5($contrasena);
        $stmt->bindParam(':contrasena', $cifrada);
        // Ejemplo de FetchMode por asociación (Podemos dejar el ya especificado)
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        // Ejecutar la consulta
        $stmt->execute();
        $datos = $stmt->fetch();
        if (is_array($datos)) {    //Si obtuvo un registro
            // Se asignan en variables de sesion, los datos del usuario, recuperados de la base de datos
            $_SESSION["usuarioNombre"]=$datos["usuarioNombre"];
            $_SESSION['idUsuario']=$datos['idUsuario'];
            $_SESSION['idEmpleado']=$datos['idEmpleado'];
            $_SESSION['nombre']=$datos['nombre'];
            $_SESSION['idDepartamento']=$datos['idDepartamento'];

            $stmt2 = $dbh->prepare("SELECT idPermiso
                                    FROM privilegios
                                    WHERE idUsuario = " . $datos['idUsuario']);
            $stmt2->execute();
            $_SESSION['permisos'] = array();
            // $msg2 = "";
            while ($resultado = $stmt2->fetch()) {
                array_push($_SESSION['permisos'], $resultado->idPermiso);
                // $msg2 = $msg2 . " - " . $resultado->idPermiso;
            }
            $_SESSION['first'] = true;
            $message = "Loggeo Exitoso. Bienvenido " . $_SESSION["usuarioNombre"] . "!";
            echo ("success");
            die();
        } else {   //No se obtuvo registro
            // $message = "Loggeo Erroneo. Identificador de usuario o contraseña incorrecta.";
            echo ("errorLoggeo");
            die();
        }
        $dbh=null;  //Termina conexión
    } else {     //No se logró conexión
            echo ("errorDB");
        die();
    }
