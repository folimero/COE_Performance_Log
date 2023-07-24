<?php
  include "../../inc/header.php";
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
  function cleanInput($value)
  {
      $value = preg_replace("/[\'\")(;|`,<>]/", "", $value);
      return $value;
  }
  // Campos obtenidos en GET
  $URL = "/index.php";
  $id;
  $actividad;
  $fechaInicio;
  $fechaEntrega;

  if (isset($_GET['id'])) {
      $id = cleanInput($_GET['id']);
      include "../../inc/conexion.php";
  } elseif (isset($_POST['btnAsignarActividades'])) {
      $id = cleanInput($_POST['id']);
      $actividad = cleanInput($_POST['actividad']);
      $fechaInicio = cleanInput($_POST['fechaInicio']);
      $fechaEntrega = cleanInput($_POST['fechaEntrega']);

      include "../../inc/conexion.php";
      // Se prueba la conexion
        if ($dbh!=null) {  //Se logró la conexión con la BD
            // Valida que ningun campo este vacio
            if (empty($actividad)) {
                $message = "Incomplete data. Please look for empty fields.";
                echo "<script>
                        alert('$message');
                    </script>";
            } else { //               ----------------     REGISTRO     -----------------------
                $stmt = $dbh-> prepare("INSERT INTO actividades_proyecto (idProyecto, idActividad, fechaInicio, fechaEntrega)
                                      VALUES (?, ?, ?, ?)");
                // Valida los campos de fecha para que sean compatibles con la BD
                if (empty($fechaInicio)) {
                    $fechaInicio = null;
                }
                if (empty($fechaEntrega)) {
                    $fechaEntrega = null;
                }
                // Se asignan los valores a la consulta preparada
                $stmt->bindParam(1, $id);
                $stmt->bindParam(2, $actividad);
                $stmt->bindParam(3, $fechaInicio);
                $stmt->bindParam(4, $fechaEntrega);

                // Ejecutar la consulta preparada
                $stmt->execute();
            }
        } else {
            $message = "DataBase Connection Error. Please try again later.";
            echo "<script>
                    alert('$message');
                </script>";
        }
  } else {
      header('Location: '.$URL);
      die();
  }
      $stmt = $dbh->prepare("SELECT idProyecto, nombre, descripcion
                            FROM proyecto
                            WHERE idProyecto = $id");
      $stmt->execute();
      // Funcion para llenar Selector de actividades
      $stmt3 = $dbh->prepare("SELECT idActividad, nombre
                            FROM   actividad
                            WHERE  idActividad NOT IN (SELECT idActividad FROM actividades_proyecto WHERE idProyecto = $id)");
      $stmt3->execute();
 ?>
      <!DOCTYPE html>
      <div class="flex-container">
        <h1>Project</h1>
        <a href='../proyecto_detalle/proyecto_detalle_coe.php?id=<?php echo $id  ?>'>
            <div class='icon-container' style="margin: 20px 0px;">
                <div class='back-icon-green'></div>
            </div>
        </a>

<?php
          while ($resultado = $stmt->fetch()) {
              ?>
              <div class="" style="width: 500px;">
                <div class="input-field">
                  <label for="idProyecto">Project ID</label>
                  <input name="idProyecto" id="idProyecto" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->idProyecto; ?>" disabled>
                </div>
                <div class="input-field">
                  <label for="nombre">Name</label>
                  <input name="nombre" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->nombre; ?>"disabled>
                </div>
                <div class="input-field">
                  <label for="descripcion">Description</label>
                  <input name="descripcion" type="text" style="text-align:center; font-weight:bold; background-color: AliceBlue;" value="<?php echo $resultado->descripcion; ?>"disabled>
                </div>
              </div>
<?php
          } ?>
          <hr style="width:30%;margin: 30px 0px; text-align:center;margin-left:0">
          <h1>Activities</h1>



              <div class="container" style="width:100%; display: flex; margin-top: 30px;">
                  <div class="card maincol" id='columna1' style="width:25%; ">
                      <div class="containter actcol" id='tittle1'></div>

                      <hr class="actcol">
                      <div class="containter actcol" id='columna1-input' style="height: 500px;"></div>
                      <div class="containter actcol" id='columna1-output'></div>

                      <div class="containter actcol" id='conditional' style="margin-top: auto; text-align: center;">
                          <hr style="margin-top:20px;">
                          <p>Gate -Stage I</p>
                          <p>GO</p>
                          <p>KILL</p>
                          <p>HOLD</p>
                          <p>RECYCLE</p>
                          <p>CONDITIONAL GO</p>
                      </div>
                  </div>

                  <div class="card maincol" id='columna2' style="width:25%; ">
                      <div class="containter actcol" id='tittle2'></div>

                      <hr class="actcol">
                      <div class="containter actcol" id='columna2-input' style="height: 500px;"></div>
                      <div class="containter actcol" id='columna2-output'></div>

                      <div class="containter actcol" id='conditional' style="margin-top: auto; text-align: center;">
                          <hr style="margin-top:20px;">
                          <p>Gate -Stage II</p>
                          <p>GO</p>
                          <p>KILL</p>
                          <p>HOLD</p>
                          <p>RECYCLE</p>
                          <p>CONDITIONAL GO</p>
                      </div>
                  </div>

                  <div class="card maincol" id='columna3' style="width:25%; ">
                      <div class="containter actcol" id='tittle3'></div>

                      <hr class="actcol">
                      <div class="containter actcol" id='columna3-input' style="height: 500px;"></div>
                      <div class="containter actcol" id='columna3-output'></div>

                      <div class="containter actcol" id='conditional' style="margin-top: auto; text-align: center;">
                          <hr style="margin-top:20px;">
                          <p>Gate -Stage III</p>
                          <p>GO</p>
                          <p>KILL</p>
                          <p>HOLD</p>
                          <p>RECYCLE</p>
                          <p>CONDITIONAL GO</p>
                      </div>
                  </div>
                  <div class="card maincol" id='columna4' style="width:25%; ">
                      <div class="containter actcol" id='tittle4'></div>

                      <hr class="actcol">
                      <div class="containter actcol" id='columna4-input' style="height: 500px;"></div>
                      <div class="containter actcol" id='columna4-output'></div>

                      <div class="containter actcol" id='conditional' style="margin-top: auto; text-align: center;">
                          <hr style="margin-top:20px;">
                          <p>Gate -Stage IV</p>
                          <p>GO</p>
                          <p>KILL</p>
                          <p>HOLD</p>
                          <p>RECYCLE</p>
                          <p>CONDITIONAL GO</p>
                      </div>
                  </div>

              </div>

              <?php if (in_array(7, $_SESSION["permisos"])) { ?>
                          <div class="container" style="width:100%;">
                              <input name="btnAsignarActividades" type="button" value="Save" onclick="asignarActividades(event)">
                          </div>
              <?php } ?>
        </div>

        <!-- VENTANAS MODALES -->
        <span class="alerta ocultar">
            <span class="msg">This is a warning</span>
                <span class='icon-container'>
                    <div id="cerrar_alerta" class='cross-icon'></div>
                </span>
        </span>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#cerrar_alerta').click(function(){
                  $('.alerta').removeClass('mostrar');
                  $('.alerta').addClass('ocultar');
                });

                $.ajax({
                  url: '../../js/ajax.php',
                  type: 'POST',
                  async: true,
                  data: {
                    accion: 'cargarActividades',
                  },
                  success: function(response) {
                    // console.log(response);
                    /////////////////////////////////////////////// LLENADO DE CAMPOS BASADOS EN PDP /////////////////////////////////////////////////
                    if (!response != "error") {
                      // console.log(response);
                      var info = JSON.parse(response);
                      // console.log(info);

                      var stg1 = 0;
                      var stg2 = 0;
                      var stg3 = 0;
                      var stg4 = 0;
                      var input1 = 0;
                      var input2 = 0;
                      var input3 = 0;
                      var input4 = 0;
                      var insert = 0;

                      info.result.forEach((item, i) => {
                          // console.log(item.aNombre);
                          switch (item.idEtapa) {
                            case "1":
                                if (stg1 == 0) {
                                    $('#tittle1').append("<h1 style='color: darkorchid; text-align: center;'>"+item.eNombre+" <h3 style='color: darkorchid; text-align: center;'>(PLANNING)</h3></h1>");
                                    stg1 = 1;
                                }
                                if (input1 == 0) {
                                    $('#columna1-input').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                    input1=1;
                                } else if (input1 == 1) {
                                      if (item.tipo == "OUTPUT") {
                                          $('#columna1-output').append("<hr>");
                                          $('#columna1-output').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                          input1=2;
                                      }
                                }
                                if (item.tipo == "INPUT") {
                                    $('#columna1-input').append("<div class='inline-flex' style='margin: 2px 0px; display: flex;'>" +
                                                                    "<input type='checkbox' name='act"+item.idActividad+"' id='act"+item.idActividad+"' value='"+item.idActividad+"'>" +
                                                                    "<label for='act"+item.idActividad+"' style='margin-left:5px;'>"+item.aNombre+"</label>" +

                                                                        "<label style='margin-left: auto;'>"+item.resp+"</label>" +

                                                                "</div>");
                                }else {
                                    $('#columna1-output').append("<div class='inline-flex' style='margin: 2px 0px; display: flex;'>" +
                                                                    "<input type='checkbox' name='act"+item.idActividad+"' id='act"+item.idActividad+"' value='"+item.idActividad+"'>" +
                                                                    "<label for='act"+item.idActividad+"' style='margin-left:5px;'>"+item.aNombre+"</label>" +

                                                                        "<label style='margin-left: auto;'>"+item.resp+"</label>" +

                                                                "</div>");
                                }
                                break;
                            case "2":
                                if (stg2 == 0) {
                                    $('#tittle2').append("<h1 style='color: darkorchid; text-align: center;'>"+item.eNombre+" <h3 style='color: darkorchid; text-align: center;'>(DESIGN AND DEVELOPMENT)</h3></h1>");
                                    stg2 = 1;
                                }
                                if (input2 == 0) {
                                    if (item.tipo == "INPUT") {
                                        $('#columna2-input').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                        $('#columna2-input').append("<p style='margin-left: 18px'>Project Plan</p>");
                                        $('#columna2-input').append("<p style='margin-left: 18px'>BOM/ AML/AVL/ Raw material requirements</p>");
                                    }else {
                                        $('#columna2-input').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>INPUT</h3>");
                                        $('#columna2-input').append("<p style='margin-left: 18px'>Project Plan</p>");
                                        $('#columna2-input').append("<p style='margin-left: 18px'>BOM/ AML/AVL/ Raw material requirements</p>");
                                        $('#columna2-output').append("<hr>");
                                        $('#columna2-output').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                        input2=2;
                                    }
                                    if (input2 == 0) {
                                        input2=1;
                                    }
                                } else if (input2 == 1 && item.tipo == "OUTPUT") {
                                    $('#columna2-output').append("<hr>");
                                    $('#columna2-output').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                    input2=2;
                                }
                                if (item.tipo == "INPUT") {
                                    $('#columna2-input').append("<div class='inline-flex' style='margin: 2px 0px; display: flex;'>" +
                                                                    "<input type='checkbox' name='act"+item.idActividad+"' id='act"+item.idActividad+"' value='"+item.idActividad+"'>" +
                                                                    "<label for='act"+item.idActividad+"' style='margin-left:5px;'>"+item.aNombre+"</label>" +

                                                                        "<label style='margin-left: auto;'>"+item.resp+"</label>" +

                                                                "</div>");
                                }else {
                                    $('#columna2-output').append("<div class='inline-flex' style='margin: 2px 0px; display: flex;'>" +
                                                                    "<input type='checkbox' name='act"+item.idActividad+"' id='act"+item.idActividad+"' value='"+item.idActividad+"'>" +
                                                                    "<label for='act"+item.idActividad+"' style='margin-left:5px;'>"+item.aNombre+"</label>" +

                                                                        "<label style='margin-left: auto;'>"+item.resp+"</label>" +

                                                                "</div>");
                                }
                                break;
                            case "3":
                                if (stg3 == 0) {
                                    $('#tittle3').append("<h1 style='color: darkorchid; text-align: center;'>"+item.eNombre+" <h3 style='color: darkorchid; text-align: center;'>(VALIDATION)</h3></h1>");
                                    stg3 = 1;
                                }
                                if (input3 == 0) {
                                    if (item.tipo == "INPUT") {
                                        $('#columna3-input').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Prints</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Product design specifications (PDS)</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>BOM</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Preliminary sample</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Preliminary work instructions</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Gauges and templates for new terminations</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>ECN</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>First Article Report (FAR)</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>DFMEA</p>");
                                    }else {
                                        $('#columna3-input').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>INPUT</h3>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Prints</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Product design specifications (PDS)</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>BOM</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Preliminary sample</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Preliminary work instructions</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>Gauges and templates for new terminations</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>ECN</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>First Article Report (FAR)</p>");
                                        $('#columna3-input').append("<p style='margin-left: 18px'>DFMEA</p>");
                                        $('#columna3-output').append("<hr>");
                                        $('#columna3-output').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                        input3=2;
                                    }
                                    if (input3 == 0) {
                                        input3=1;
                                    }
                                } else if (input3 == 1 && item.tipo == "OUTPUT") {
                                    $('#columna3-output').append("<hr>");
                                    $('#columna3-output').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                    input3=2;
                                }
                                if (item.tipo == "INPUT") {
                                    $('#columna3-input').append("<div class='inline-flex' style='margin: 2px 0px; display: flex;'>" +
                                                                    "<input type='checkbox' name='act"+item.idActividad+"' id='act"+item.idActividad+"' value='"+item.idActividad+"'>" +
                                                                    "<label for='act"+item.idActividad+"' style='margin-left:5px;'>"+item.aNombre+"</label>" +

                                                                        "<label style='margin-left: auto;'>"+item.resp+"</label>" +

                                                                "</div>");
                                }else {
                                    $('#columna3-output').append("<div class='inline-flex' style='margin: 2px 0px; display: flex;'>" +
                                                                    "<input type='checkbox' name='act"+item.idActividad+"' id='act"+item.idActividad+"' value='"+item.idActividad+"'>" +
                                                                    "<label for='act"+item.idActividad+"' style='margin-left:5px;'>"+item.aNombre+"</label>" +

                                                                        "<label style='margin-left: auto;'>"+item.resp+"</label>" +

                                                                "</div>");
                                }
                                break;
                            case "4":
                                if (stg4 == 0) {
                                    $('#tittle4').append("<h1 style='color: darkorchid; text-align: center;'>"+item.eNombre+" <h3 style='color: darkorchid; text-align: center;'>(LAUNCH)</h3></h1>");
                                    stg4 = 1;
                                }
                                if (input4 == 0) {
                                    if (item.tipo == "INPUT") {
                                        $('#columna4-input').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>Quoted EAU</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>Prints</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>FA approval from customer</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>Project Book</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>BOM’s loaded in visual w/vendors info</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>Lessons learned (all areas)</p>");
                                    }else {
                                        $('#columna4-input').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>INPUT</h3>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>Quoted EAU</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>Prints</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>FA approval from customer</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>Project Book</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>BOM’s loaded in visual w/vendors info</p>");
                                        $('#columna4-input').append("<p style='margin-left: 18px'>Lessons learned (all areas)</p>");
                                        $('#columna4-output').append("<hr>");
                                        $('#columna4-output').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                        input4=2;
                                    }
                                    if (input4 == 0) {
                                        input4=1;
                                    }
                                } else if (input4 == 1 && item.tipo == "OUTPUT") {
                                    $('#columna4-output').append("<hr>");
                                    $('#columna4-output').append("<h3 style='margin: 20px 0px 10px 0px; color: teal;'>"+item.tipo+"</h3>");
                                    input4=2;
                                }
                                if (item.tipo == "INPUT") {
                                    $('#columna4-input').append("<div class='inline-flex' style='margin: 2px 0px; display: flex;'>" +
                                                                    "<input type='checkbox' name='act"+item.idActividad+"' id='act"+item.idActividad+"' value='"+item.idActividad+"'>" +
                                                                    "<label for='act"+item.idActividad+"' style='margin-left:5px;'>"+item.aNombre+"</label>" +

                                                                        "<label style='margin-left: auto;'>"+item.resp+"</label>" +

                                                                "</div>");
                                }else {
                                    $('#columna4-output').append("<div class='inline-flex' style='margin: 2px 0px; display: flex;'>" +
                                                                    "<input type='checkbox' name='act"+item.idActividad+"' id='act"+item.idActividad+"' value='"+item.idActividad+"'>" +
                                                                    "<label for='act"+item.idActividad+"' style='margin-left:5px;'>"+item.aNombre+"</label>" +

                                                                        "<label style='margin-left: auto;'>"+item.resp+"</label>" +

                                                                "</div>");
                                }
                            default:
                          }
                      });
                      loadCheckedActivities();
                    }
                  },
                  error: function(error) {
                    console.log(error);
                  }
                });
            });

            function asignarActividades(e) {
              e.preventDefault();
              var actividades = new Array();
              var idProyecto = $('#idProyecto').val();

              $('input[type=checkbox]').each(function () {
                  if (this.checked == true) {
                      actividades.push($(this).val());
                  }
              });
              // alert(actividades);
              // actividades.forEach((item, i) => {
              //   console.log(item);
              // });
              // actividades = actividades.toString();

              $.ajax({
                  url: '../../js/ajax.php',
                  type: 'POST',
                  async: true,
                  data: {
                      accion: 'asignarActividades',
                      idProyecto: idProyecto,
                      actividades:actividades
                  },
                  success: function(response) {
                      console.log(response);
                      // if (response != "error") {
                          // console.log(response);
                          mostrarAlerta('success','data saved successfully.');
                          // var info = JSON.parse(response);
                          // console.log(info.TODELETE);
                      //     var tabAct = $('#tablaActividades');
                      //
                      //     info.result.forEach((item, i) => {
                      //         tabAct.append('<tr>' +
                      //                             '<td>'+item.idActividades_proyecto+'</td>' +
                      //                             '<td>'+item.nombre+'</td>' +
                      //                             '<td>'+item.fechaInicio+'</td>' +
                      //                             '<td>'+item.fechaEntrega+'</td>' +
                      //                             '<td>' +
                      //                                 '<a href="#" onclick="eliminarActividad('+item.idActividades_proyecto+')">' +
                      //                                     '<div class="icon-container">' +
                      //                                         '<div class="cross-icon"></div>' +
                      //                                     '</div>' +
                      //                                 '</a>' +
                      //                             '</td>' +
                      //                         '</tr>');
                      //     });
                      //     return false;
                      // } else {
                      //     console.log("ERROR");
                      // }
                  },
                  error: function(error) {
                      mostrarAlerta('danger','Data not saved, please try again later.');
                      console.log(error);
                  }
              });
            }

            function loadCheckedActivities(){
                var idProyecto = $('#idProyecto').val();
                $.ajax({
                    url: '../../js/ajax.php',
                    type: 'POST',
                    async: true,
                    data: {
                        accion: 'loadCheckedActivities',
                        idProyecto: idProyecto,
                    },
                    success: function(response) {
                        // console.log(response);
                        if (response != "error") {
                            // console.log(response);
                            var info = JSON.parse(response);
                            // console.log(info);

                            info.result.forEach((item, i) => {
                                $("#act" + item.idActividad).attr('checked', 'checked');
                                // console.log(item);
                            });
                            return false;
                        } else {
                            console.log("ERROR");
                        }
                    },
                    error: function(error) {
                      console.log(errorCODE);
                    }
                });
            }

            function eliminarActividad(id){
                event.preventDefault();
                alert(id);
            }
            // function mostrarAlerta(tipo, mensaje){
            //     $('.msg').html(mensaje);
            //     $('.alerta').addClass(tipo);
            //     $('.alerta').addClass('mostrar');
            //     $('.alerta').addClass('mostrarAlerta');
            //     $('.alerta').removeClass('ocultar');
            //     setTimeout(function(){
            //         $('.alerta').removeClass('mostrar');
            //         // $('.alerta').removeClass('mostrarAlerta');
            //         $('.alerta').addClass('ocultar');
            //     },5000);
            // }
        </script>
        <script src="../../js/funciones.js"></script>
      <?php include "../../inc/footer.html"; ?>
