<?php include "inc/headerBoostrap.php"; ?>

<div class="flex-container">
  <h1>Index Page</h1>
</div>

<!-- VENTANAS MODALES -->
<span class="alerta ocultar">
    <span class="msg">This is a warning</span>
        <span class='icon-container'>
            <div id="cerrar_alerta" class='cross-icon'></div>
        </span>
</span>

<script src="js/funciones.js"></script>
<?php

  // Suppose your "public_html" folder is .
  $file = './../files/img2.jpg';
  $userCanDownloadThisFile = true; // apply your logic here

  if (file_exists($file) && $userCanDownloadThisFile) {
      // header('Content-Description: File Transfer');
      // header('Content-Type: application/octet-stream');
      // header('Content-Disposition: attachment; filename=filename.gif');
      // header('Content-Transfer-Encoding: binary');
      // header('Expires: 0');
      // header('Cache-Control: must-revalidate');
      // header('Pragma: public');
      // header('Content-Length: ' . filesize($file));
      // ob_clean();
      // flush();
      // readfile($file);
  }
?>

<?php include "inc/footer.html"; ?>
