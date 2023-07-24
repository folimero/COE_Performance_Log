function obtenerCuentas(str) {
  if (str == "") {
    document.getElementById("cliente").VALUES() = "";
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("txtHint").innerHTML = this.responseText;
      }
    };
    xmlhttp.open("GET", "getuser.php?q=" + str, true);
    xmlhttp.send();
  }
}

function isJson(item) {
  item = typeof item !== "string" ?
    JSON.stringify(item) :
    item;

  try {
    item = JSON.parse(item);
  } catch (e) {
    return false;
  }

  if (typeof item === "object" && item !== null) {
    return true;
  }

  return false;
}

function mostrarAlerta(tipo, mensaje) {
  switch (tipo) {
    case 'success':
      $('.alerta').removeClass('warning');
      $('.alerta').removeClass('danger');
      break;
    case 'warning':
      $('.alerta').removeClass('success');
      $('.alerta').removeClass('danger');
      break;
    case 'danger':
      $('.alerta').removeClass('success');
      $('.alerta').removeClass('warning');
      break;
    default:
      break;
  }

  $('.msg').html(mensaje);
  $('.alerta').addClass(tipo);
  $('.alerta').addClass('mostrar');
  $('.alerta').addClass('mostrarAlerta');
  $('.alerta').removeClass('ocultar');
  setTimeout(function() {
    $('.alerta').removeClass('mostrar');
    // $('.alerta').removeClass('mostrarAlerta');
    $('.alerta').addClass('ocultar');
  }, 5000);
}

// document.getElementById('').addeventlistener('click',
//   function(){
//
//
// });

// document.querySelector('.btn-cerrar').addEventListener('click',
//   function() {
//   document.querySelector('.back-modal').style.display = 'none';
// });

function abrirModal() {
  document.querySelector('.back-modal').style.display = 'flex';

  // sessionStorage.setItem('actId', actId);
  // alert(sessionStorage.getItem('actId'));

  // var off = window.scrollY;
  //
  // document.querySelector('.contenido-modal').style.position = 'absolute';
  // document.querySelector('.contenido-modal').style.transform = "translate(0," + (off - 200) + "px)";
}

function cerrarModal() {
  var modal = document.querySelector('.back-modal');
  modal.style.display = 'none';
  $('#usuario').val('');
  $('#nota').val('');
  $('#idProyectoNota').val('0');
  $('#idCotizacionNota').val('0');
  $('#contrasena').val('');
  $('#usuario').prop("disabled", false);
  $('#idUsuario').val('');
  $('#idActividad').val('');
  $('#idEmpleado').val('');
  $('#nombre').val('');
  $('#nombre').prop('disabled', false);
  $('#fechaInicio').val('');
  $('#horas').val('');
  $('#numEmpleado').val('');
  $('#numEmpleado').prop('disabled', false);
  $('#celular').val('');
  $('#correo').val('');
  $('#activo').val('');
  $('#activo').prop('checked', false);
  $('#asignable').prop('checked', false);
  $('[name=empleado]').prop("disabled", false);
  var select1 = document.getElementById("recurso");
  if (select1 != null) {
    var length = select1.options.length;
    for (i = length - 1; i >= 0; i--) {
      select1.options[i] = null;
    }
  }
  var select2 = document.getElementById("departamento");
  if (select2 != null) {
    var length = select2.options.length;
    for (i = length - 1; i >= 0; i--) {
      select2.options[i] = null;
    }
  }
  var select3 = document.getElementById("puesto");
  if (select3 != null) {
    var length = select3.options.length;
    for (i = length - 1; i >= 0; i--) {
      select3.options[i] = null;
    }
  }
  var select4 = document.getElementById("empleado");
  if (select4 != null) {
    var length = select4.options.length;
    for (i = length - 1; i >= 0; i--) {
      select4.options[i] = null;
    }
  }
}

function cerrarModal2() {
  var modal = document.querySelector('.contenido-modal');
  $('.contenido-modal').html("");
  cerrarModal();
}

function registrarRecurso(e) {
  e.preventDefault();
  var actId = document.getElementById("idActividad").value;
  var recId = document.getElementById("recurso").value;
  var fechaInicio = document.getElementById("fechaInicio").value;
  var horas = document.getElementById("horas").value;
  // alert(recId);

  if (horas <= 0) {
      mostrarAlerta("danger","Hours cant be Zero");
      return;
  }

  $.ajax({
    url: 'js/ajax.php',
    type: 'POST',
    async: true,
    data: {
      accion: 'registrarRecurso',
      actividad: actId,
      recurso: recId,
      fechaInicio: fechaInicio,
      horas: horas
    },
    success: function(response) {
      // console.log(response);
      if (!response != "error") {
        console.log(response);
        var nombre = $('.op' + recId).attr('nombre');
        var fechaInicio = $('#fechaInicio').val();
        var horas = $('#horas').val();
        $('.column' + actId).append(
            "<div class='col text-center p-2'><h5><a href='#' onclick='abrirModalAsinacionHoras(" + actId + "," + recId + ")'>" + nombre + "</a></h5></div>"
        );
        cerrarModal();
      }
    },
    error: function(error) {
      console.log(error);
    }
  });
}

// AJAX
$(document).ready(function() {
  var pathname = window.location.pathname;
  // $('#myC').addClass("active");
  // alert(pathname);
  $("a[href='" + pathname + "']").addClass('active');
  // $('.a').addClass('active');

  $('.add-resource').click(function(e) {
    e.preventDefault();
    var actividad = $(this).attr('actividad');
    var act, resources;
    $.ajax({
      url: 'js/ajax.php',
      type: 'POST',
      async: true,
      data: {
        accion: 'mostrarRecursos',
        actividad: actividad
      },
      success: function(response) {
        // console.log(response);
        if (!response != "error") {
          // console.log(response);
          var info = JSON.parse(response);
          // console.log(info);
          $('#idActividad').val(info.result1.idActividades_proyecto);
          $('#nombre').val(info.result1.nombre);
          var mySelect = document.getElementById("recurso");

          var index = 0;
          info.result2.forEach((item, i) => {
            // console.log(item.idEmpleado);
            var myOption = document.createElement("option");
            myOption.value = item.idEmpleado;
            myOption.className = "op" + item.idEmpleado;
            myOption.setAttribute("nombre", item.enombre);
            myOption.innerHTML = item.enombre + " - " + item.pnombre; // whatever property it has

            // then append it to the select element
            mySelect.appendChild(myOption);
            index++;
          });
        }
      },
      error: function(error) {
        console.log(error);
      }
    });
  });

  $('.edit-employee').click(function(e) {
    e.preventDefault();
    var idEmpleado = $(this).attr('idEmpleado');
    $.ajax({
      url: 'js/ajax.php',
      type: 'POST',
      async: true,
      data: {
        accion: 'mostrarEmpleado',
        idEmpleado: idEmpleado
      },
      success: function(response) {
        // console.log(response);
        if (!response != "error") {
          // console.log(response);
          var info = JSON.parse(response);
          // console.log(info);
          $('#numEmpleado').val(info.result.numEmpleado);
          $('#numEmpleado').prop("disabled", true);
          $('#nombre').val(info.result.nombre);
          $('#nombre').prop("disabled", true);
          $('[name=departamento]').val(info.result.idDepartamento);
          $('[name=puesto]').val(info.result.idPuesto);
          $('#correo').val(info.result.correo);
          $('#celular').val(info.result.celular);
          $('#idEmpleado').val(info.result.idEmpleado);
          if (info.result.activo == 1) {
            $('#activo').prop('checked', true);
          } else {
            $('#activo').prop('checked', false);
          }
          if (info.result.asignableAct == 1) {
            $('#asignable').prop('checked', true);
          } else {
            $('#asignable').prop('checked', false);
          }
          if (info.result.asignableAsResp == 1) {
            $('#asignambleAsProjectLeader').prop('checked', true);
          } else {
            $('#asignambleAsProjectLeader').prop('checked', false);
          }
        }
      },
      error: function(error) {
        console.log(error);
      }
    });
  });

  $('.edit-user').click(function(e) {
    e.preventDefault();
    $('#tittle').html('Edit User');
    $('#btnModal').val('Edit');
    var idUsuario = $(this).attr('idUsuario');
    $.ajax({
      url: 'js/ajax.php',
      type: 'POST',
      async: true,
      data: {
        accion: 'mostrarUsuario',
        idUsuario: idUsuario
      },
      success: function(response) {
        // console.log(response);
        if (!response != "error") {
          // console.log(response);
          var info = JSON.parse(response);
          // console.log(info);
          $('#idUsuario').val(info.result.idUsuario);
          $('#usuario').val(info.result.usuarioNombre);
          $('#usuario').prop("disabled", true);
          $('[name=empleado]').prop("disabled", true);
          if (info.result.act == 1) {
            $('#activo').prop('checked', true);
          } else {
            $('#activo').prop('checked', false);
          }

          var optionText = info.result.numEmp + " - " + info.result.emp;
          var optionValue = 'dummy';

          $('[name=empleado]').append(new Option(optionText, optionValue));
          $('[name=empleado]').val('dummy');
        }
      },
      error: function(error) {
        console.log(error);
      }
    });
  });

  // $('.edit-note').click(function(e) {
  //   e.preventDefault();
  //   var idNota = $(this).attr('idNota');
  //
  //   $.ajax({
  //     url: 'js/ajax.php',
  //     type: 'POST',
  //     async: true,
  //     data: {
  //       accion: 'mostrarNota',
  //       idNota: idNota
  //     },
  //     success: function(response) {
  //       // console.log(response);
  //       if (!response != "error") {
  //         // console.log(response);
  //         var info = JSON.parse(response);
  //         // console.log(info);
  //         $('#idNota').val(info.result.id);
  //         $('#nota').val(info.result.nota);
  //         $('#idProyectoNota').val('0');
  //       }
  //     },
  //     error: function(error) {
  //       console.log(error);
  //     }
  //   });
  // });

});
