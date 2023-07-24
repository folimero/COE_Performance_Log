<?php
    session_start();
    session_destroy();

    $message = "Sesion finalizada correctamente.";
    echo "<script>
              alert('$message');
              window.location.href='index.php';
          </script>";
    die();
?>
