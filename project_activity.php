<?php
    $id = $_GET['id'];
    $archivoController = 'projectActivity/projectActivityController.php';
    require_once $archivoController;
    $controller = new ProjectActivity();
    $controller->render($id);
    // return false;
?>
