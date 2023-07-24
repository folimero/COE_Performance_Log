$("#form_empleados").submit(function(e) {
    e.preventDefault(); // avoid to execute the actual submit of the form.
    var form = $(this);
    var url = form.attr('action');
    $.ajax({
        type: "POST",
        url: url,
        data: form.serialize(), // serializes the form's elements.
        success: function(response) {
            console.log(response);
            switch (response) {
                case "errorVacio":
                    mostrarAlerta('warning','Incomplete data. Please look for empty fields.');
                    // console.log(response);
                    break;
                case "success":
                    location.replace("/log.php")
                    // console.log(response);
                    break;
                case "successEdit":
                    mostrarAlerta('success','Record Updated.');
                    // console.log(response);
                    // mostrarAlerta('success','Bienvenido');
                    break;
                case "duplicatedName":
                    mostrarAlerta('warning','Record NOT completed, Selected NAME is already in Data Base.');
                    $('#nombre').focus();
                    break;
                case "duplicatedID":
                    mostrarAlerta('warning','Record NOT completed, Selected ID is already in Data Base.');
                    $('#projectID').focus();
                    break;
                case "errorDB":
                    mostrarAlerta('danger','DataBase Connection Error. Please try again later.');
                    break;
                default:
            }
        }
    });
});

function recargarCategoriaProyecto(){
  $.ajax({
    type:"POST",
    url:"../../js/ajax.php",
    async: true,
    data: {
      accion: 'actualizarCategoriaProyecto',
      cliente: $('#idTipoProyecto').val()
    },
    success:function(result){
      $('#idCategoria').attr('value', result);
    }
  });
}
function recargarComplejidadProyecto(){
  $.ajax({
    type:"POST",
    url:"../../js/ajax.php",
    async: true,
    data: {
      accion: 'actualizarComplejidadProyecto',
      cliente: $('#idTipoProyecto').val()
    },
    success:function(result){
      $('#idComplejidad').attr('value', result);
    }
  });
}
function recargarTipoProyecto(){
  $.ajax({
    type:"POST",
    url:"../../js/ajax.php",
    async: true,
    data: {
      accion: 'actualizarTipoProyectoServicio',
      categoria: $('#idCategoria').val(),
      servicio: $('#idServicio').val(),
      complejidad: $('#idComplejidad').val()
    },
    success:function(result){
      $('#idTipoProyecto').attr('value', result);
      recargarHorasProyecto();
    }
  });
}
function recargarServicios(){
  $.ajax({
    type:"POST",
    url:"../../js/ajax.php",
    async: false,
    data: {
      accion: 'cargarServicios',
      categoria: $('#idCategoria').val(),
    },
    success:function(result){
      $('#idServicio').html(result);
      $('#idServicio').prop("disabled", false);
      $('#idServicio').css("background-color", "white");
      recargarHorasProyecto();
    }
  });
}
function recargarHorasProyecto(){
  $.ajax({
    type:"POST",
    url:"../../js/ajax.php",
    async: true,
    data: {
      accion: 'actualizarHorasProyecto',
      cliente: $('#idTipoProyecto').val()
    },
    success:function(result){
      $('#idHrs').attr('value', result);
    }
  });
}
function editarProyecto(id){
  $.ajax({
    type:"POST",
    url:"../../js/ajax.php",
    async: true,
    data: {
      accion: 'editarProyecto',
      idProyecto: id
    },
    success:function(response) {
      if (!response != "error") {
        var info = JSON.parse(response);
        // console.log(info);
          $('#tittle').html('Edit Project');
          $('#projectID').val(info.result.projectID);
          $("#cliente option[value=" + info.result.idCliente + "]").attr('selected', 'selected');
          $('#nombre').val(info.result.nombre);
          $('#descripcion').val(info.result.descripcion);
          $('#po').val(info.result.PO);
          $("#idCategoria option[value=" + info.result.idProyectoCategoria + "]").attr('selected', 'selected');
          // $("#idServicio").attr('disabled', 'false');
          $("#idComplejidad option[value=" + info.result.idComplejidad + "]").attr('selected', 'selected');
          $('#idTipoProyecto').val(info.result.idTipoProyecto);
          // $("#idServicio option[value=" + info.result.idProyectoServicio + "]").attr('selected', 'selected');
          $('#idProyectoRequester').val(info.result.idProyectoRequester);
          recargarServicios();
          $('#idServicio').val(info.result.idProyectoServicio);
          $("#respDiseno option[value=" + info.result.idRespDiseno + "]").attr('selected', 'selected');

          $("#status option[value=" + info.result.idStatus + "]").attr('selected', 'selected');
          $("#etapa option[value=" + info.result.currentStage + "]").attr('selected', 'selected');
          // APPROVERS
          $("#idProjectLeader option[value=" + info.result.idLiderProyecto + "]").attr('selected', 'selected');

          $("#backIcon").attr("href", "../../pages/proyecto_detalle/proyecto_detalle_application.php?id=" + id);
          $("#clienteIcon").attr("href", "/cliente.php?id=" + id);
          $("#categoriaIcon").attr("href", "/tipo_proyecto.php?id=" + id);
          $("#categoriaIcon").attr("href", "/tipo_proyecto.php?id=" + id);
          $("#complejidadIcon").attr("href", "/tipo_proyecto.php?id=" + id);

          var fechaReqCliente;
          var fechaPromesa;
          var fechaEmbarque;
          var fechaInicio;
          var fechaTermino
          if (info.result.fechaReqCliente) {
            fechaReqCliente = new Date(info.result.fechaReqCliente).toISOString().slice(0,10);
          }
          if (info.result.fechaPromesa) {
            fechaPromesa = new Date(info.result.fechaPromesa).toISOString().slice(0,10);
          }
          if (info.result.fechaEmbarque) {
            fechaEmbarque = new Date(info.result.fechaEmbarque).toISOString().slice(0,10);
          }
          if (info.result.fechaInicio) {
            fechaInicio = new Date(info.result.fechaInicio).toISOString().slice(0,10);
          }
          if (info.result.fechaTermino) {
            fechaTermino = new Date(info.result.fechaTermino).toISOString().slice(0,10);
          }
          $('#fechaReqCliente').val(fechaReqCliente);
          $('#fechaPromesa').val(fechaPromesa);
          $('#fechaEmbarque').val(fechaEmbarque);
          $('#fechaInicio').val(fechaInicio);
          $('#fechaTermino').val(fechaTermino);
          $('#qto').val(info.result.qtoNumber);
          $('#notas').val(info.result.notas);

          // recargarHorasProyecto();
      }
    },
    error: function(error) {
      console.log(error);
    }
  });
}

$("input[data-type='currency']").on({
  keyup: function() {
    formatCurrency($(this));
  },
  blur: function() {
    formatCurrency($(this), "blur");
  }
});


function formatNumber(n) {
// format number 1000000 to 1,234,567
return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}


function formatCurrency(input, blur) {
// appends $ to value, validates decimal side
// and puts cursor back in right position.

// get input value
var input_val = input.val();

// don't validate empty input
if (input_val === "") { return; }

// original length
var original_len = input_val.length;

// initial caret position
var caret_pos = input.prop("selectionStart");

// check for decimal
if (input_val.indexOf(".") >= 0) {

  // get position of first decimal
  // this prevents multiple decimals from
  // being entered
  var decimal_pos = input_val.indexOf(".");

  // split number by decimal point
  var left_side = input_val.substring(0, decimal_pos);
  var right_side = input_val.substring(decimal_pos);

  // add commas to left side of number
  left_side = formatNumber(left_side);

  // validate right side
  right_side = formatNumber(right_side);

  // On blur make sure 2 numbers after decimal
  if (blur === "blur") {
    right_side += "00";
  }

  // Limit decimal to only 2 digits
  right_side = right_side.substring(0, 2);

  // join number by .
  input_val = "$" + left_side + "." + right_side;

} else {
  // no decimal entered
  // add commas to number
  // remove all non-digits
  input_val = formatNumber(input_val);
  input_val = "$" + input_val;

  // final formatting
  if (blur === "blur") {
    input_val += ".00";
  }
}

// send updated string to input
input.val(input_val);

// put caret back in the right position
var updated_len = input_val.length;
caret_pos = updated_len - original_len + caret_pos;
input[0].setSelectionRange(caret_pos, caret_pos);
}
