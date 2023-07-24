<?php session_start();
include_once '../inc/conexion.php';
// Check if the form was submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Check if file was uploaded without errors
    if(isset($_FILES["anyfile"]) && $_FILES["anyfile"]["error"] == 0){
        $allowed = array("pdf", "xls", "xlsx");
        // $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["anyfile"]["name"];
        $filetype = $_FILES["anyfile"]["type"];
        $filesize = $_FILES["anyfile"]["size"];

        // Validate file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!in_array($ext, $allowed)){
          $data['result'] = "format";
          echo json_encode($data, JSON_UNESCAPED_UNICODE);
          exit;
        }

        // Validate file size - 10MB maximum
        $maxsize = 10 * 1024 * 1024;
        if($filesize > $maxsize){
            $data['result'] = "maxSize";
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit;
        }

            $idCotizacion = $_POST['idCotizacion'];
            $dir = "../images/quotes/" . $idCotizacion;

            if( is_dir($dir) === false ) {
                mkdir($dir);
            }

            if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/images/quotes/" . $idCotizacion . "/" . $filename)){
                $data['result'] = "error";
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                exit;
            } else{
                if(move_uploaded_file($_FILES["anyfile"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $dir . "/" . $filename)){
                    //
                    // $sql="INSERT INTO images(file,type,size) VALUES('$filename','$filetype','$filesize')";
                    //
                    // mysqli_query($conn,$sql);

                    try {
                        $tipo = $_POST['tipo'];
                        $idUsuario = $_SESSION['idUsuario'];

                        $stmt = $dbh-> prepare("INSERT INTO cotizacion_archivo (nombre, tamano, tipo, subidoPor, idCotizacion)
                                                VALUES (?, ?, ?, ?, ?)");
                        // Se asignan los valores a la consulta preparada
                        $stmt->bindParam(1, $filename);
                        $stmt->bindParam(2, $filesize);
                        $stmt->bindParam(3, $tipo);
                        $stmt->bindParam(4, $idUsuario);
                        $stmt->bindParam(5, $idCotizacion);

                        if ($stmt->execute()) {
                            $stmtResult = $dbh-> prepare("SELECT * FROM cotizacion_archivo
                                                          WHERE idCotizacion = ?");
                            $stmtResult->bindParam(1, $idCotizacion);
                            $stmtResult->execute();
                            $results = $stmtResult->fetchAll(PDO::FETCH_ASSOC);
                            $json = json_encode($results);
                            echo $json;
                        }else {
                            $data['result'] = "error";
                            echo json_encode($data, JSON_UNESCAPED_UNICODE);
                        }
                    } catch (\Exception $e) {
                        alert($e);
                    }
                }else{
                    $data['result'] = "error";
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                }

            }

    } else{
        echo "Error: " . $_FILES["anyfile"]["error"];
    }
}
?>
