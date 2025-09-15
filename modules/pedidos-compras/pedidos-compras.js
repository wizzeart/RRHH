var libgrid = "/plugins/dhtmlxGrid/codebase";
var spd, cmd_params; //datos del cliente seleccionado
var rowId; //almacena temporal el id de la línea para poder tratarla
var mygrid;
var imprimir = 0;
var style_row_cancel = "color: red; text-decoration: line-through;";
//mygrid.setRowAttribute(rowId, 'medida', $('#msl-lote option:selected').data('medida-id'));
//mygrid.getRowAttribute(id, 'medida')

$(document).ready(function () {
  Dropzone.autoDiscover = false;
  //checkPermisoAlmacen();
  $("#tbl-documentos").on("click", ".view-docs", function () {
    var pedido = $("#f-pedido").val();
    var tipo = $(this).parent().parent().data("tipo");

    var cmd =
      "module=pedidos-compras&method=view-docs&pedido=" +
      pedido +
      "&tipo=" +
      tipo;
    $.ajax({
      url: "api-app.php",
      type: "GET",
      data: cmd,
      dataType: "json",
      success: function (d) {
        if (d.status == 1) {
          $("#img-content").show();
          $("#list-images").empty();
          $.each(d.items, function (i, v) {
            var e = $(tpl_item_image);
            $(e).attr("href", "docs/" + v);
            $(e)
              .find("img")
              .attr("src", "docs/" + v);
            $("#list-images").append(e);
          });
        }
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        alert(XMLHttpRequest.responseText);
      },
    });
  });
  $("#btn-save-doc-multiple").click(function () {
    var img = [],
      status = 1,
      msg = "";
    $(
      "#modalUploadMultiple .gallery-container-multiple div.dz-success.dz-complete"
    ).each(function (i, v) {
      //alert($(v).find('[data-dz-name]').html());
      img.push($(v).find("[data-dz-name]").html());
    });

    if ($("#f-pedido").val() == "") {
      status = 0;
      msg +=
        "<div>Debe crear un pedido y guardarlo correctamente. Una vez asignado Nº de pedido ya podrá asociar documentación.</div>";
    }

    if (img.length == 0) {
      status = 0;
      msg += "<div>No ha seleccionado ninguna imagen.</div>";
    }

    if (status == 1) {
      var cmd =
        "module=pedidos-compras&method=save-file-doc-multiple&files=" +
        img.join("|") +
        "&type=" +
        $("#dst-doc-multiple").val() +
        "&desc=" +
        $("#mu-desc-multiple").val() +
        "&pedido=" +
        $("#f-pedido").val();
      $.ajax({
        url: "api-app.php",
        type: "GET",
        data: cmd,
        dataType: "json",
        success: function (d) {
          if (d.status == 1) {
            $.niftyNoty({
              type: "success",
              title: "Asociar Documentos",
              message: d.msg,
              container: "floating",
              timer: 6000,
            });
            $("#modalUploadMultiple")
              .find(".gallery-container-multiple")
              .empty();
            $("#mu-desc-multiple,#mu-filename-multiple,#dst-doc-multiple").val(
              ""
            );
            $("#modalUploadMultiple").modal("hide");
            load_docs();
          } else {
            $.niftyNoty({
              type: "danger",
              title: "Asociar Documentos",
              message: dmsg,
              container: "floating",
              timer: 6000,
            });
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          alert(XMLHttpRequest.responseText);
        },
      });
    } else {
      $.niftyNoty({
        type: "danger",
        title: "Asociar Documentos",
        message: msg,
        container: "floating",
        timer: 6000,
      });
    }
  });
  $("#tbl-documentos").on("click", ".dl-doc", function () {
    var pedido = $("#f-pedido").val();
    var tipo = $(this).parent().parent().data("tipo");
    location.href =
      "api-app.php?module=pedidos-compras&method=dl-doc&pedido=" +
      pedido +
      "&tipo=" +
      tipo;
  });
  $("#btn-save-doc").click(function () {
    var status = 1,
      msg = "";

    if ($("#f-pedido").val() == "") {
      status = 0;
      msg +=
        "<div>Debe crear un pedido y guardarlo correctamente. Una vez asignado Nº de pedido ya podrá asociar documentación.</div>";
    }

    if ($("#mu-filename").val() == "") {
      status = 0;
      msg += "<div>Selecciona un fichero a subir</div>";
    }

    if (status == 1) {
      var cmd =
        "module=pedidos-compras&method=save-file-doc&file=" +
        $("#mu-filename").val() +
        "&type=" +
        $("#dst-doc").val() +
        "&desc=" +
        $("#mu-desc").val() +
        "&pedido=" +
        $("#f-pedido").val();
      $.ajax({
        url: "api-app.php",
        type: "GET",
        data: cmd,
        dataType: "json",
        success: function (d) {
          if (d.status == 1) {
            $.niftyNoty({
              type: "success",
              title: "Asociar Documento",
              message: d.msg,
              container: "floating",
              timer: 6000,
            });
            $("#modalUpload").find(".gallery-container").empty();
            $("#mu-desc,#mu-filename,#dst-doc").val("");
            $("#modalUpload").modal("hide");
            load_docs();
          } else {
            $.niftyNoty({
              type: "danger",
              title: "Asociar Documento",
              message: dmsg,
              container: "floating",
              timer: 6000,
            });
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          alert(XMLHttpRequest.responseText);
        },
      });
    } else {
      $.niftyNoty({
        type: "danger",
        title: "Asociar Documento",
        message: msg,
        container: "floating",
        timer: 6000,
      });
    }
  });
  $("#btn-select-tipo").click(function () {
    var status = 1,
      msg = "";

    if ($("#mt-tipo").val() == "") {
      status = 0;
      msg = "<div>Selecciona un tipo de documento</div>";
    }

    if (status == 1) {
      if ($("#mt-tipo").val() !== "inspeccion") {
        $("#dst-doc").val($("#mt-tipo").val());
        $("#mu-desc").val($("#mt-tipo option:selected").text());
        $("#modalTipo").modal("hide");
        $("#modalUpload").find(".gallery-container").empty();
        $("#modalUpload").modal("show");
      } else {
        $("#dst-doc-multiple").val($("#mt-tipo").val());
        $("#mu-desc-multiple").val($("#mt-tipo option:selected").text());
        $("#modalTipo").modal("hide");
        $("#modalUploadMultiple").find(".gallery-container-multiple").empty();
        $("#modalUploadMultiple").modal("show");
      }
    } else {
      $.niftyNoty({
        type: "danger",
        title: "Asociar Documento",
        message: msg,
        container: "floating",
        timer: 6000,
      });
    }
  });
  $("#btn-add-documento").click(function () {
    $("#modalTipo").modal("show");
  });
  $("#btn-close").click(function () {
    // const urlParams = new URLSearchParams(window.location.search);
    // if (urlParams.has("id")) {
    //   window.close();
    // }
    location.href = "index.php?module=list-pedidos-compras";
    if (window.opener) {
      window.opener.close();
    }
  });
  $("#btn-his").click(function () {
    var status = 1,
      msg = "";

    if ($("#f-pedido").val() == "") {
      status = 0;
    }

    if (status == 1) {
      var cmd = "module=pedidos&method=get-his&doc=" + $("#f-pedido").val();
      $.ajax({
        url: "api-app.php",
        type: "GET",
        data: cmd,
        dataType: "json",
        success: function (d) {
          $("#tbl-his tbody").empty();
          $.each(d, function (i, v) {
            var e = $(tpl_lin_his);

            $(e).find("td:eq(0)").html(v.xfecha_format);
            $(e)
              .find("td:eq(1)")
              .html(v.xestado + "-" + v.xestado_desc);
            $(e).find("td:eq(2)").html(v.xusuario);
            $(e).find("td:eq(3)").html(v.xobs);

            $("#tbl-his tbody").append(e);
          });
          $("#modalHis").modal("show");
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          alert(XMLHttpRequest.responseText);
        },
      });
    }
  });
  $("#btn-movs").click(function () {
    var status = 1,
      msg = "";

    if ($("#f-pedido").val() == "") {
      status = 0;
    }

    if (status == 1) {
      var cmd = "module=almacenes&method=get-movs&doc=" + $("#f-pedido").val();
      $.ajax({
        url: "api-app.php",
        type: "GET",
        data: cmd,
        dataType: "json",
        success: function (d) {
          $("#tbl-movs tbody").empty();
          $.each(d, function (i, v) {
            var e = $(tpl_lin_mov);

            $(e).find("td:eq(0)").html(v.xfecha_format);
            $(e).find("td:eq(1)").html(v.xdoc_id);
            $(e)
              .find("td:eq(2)")
              .html(v.xarticulo_id + "-" + v.xarticulo);
            $(e)
              .find("td:eq(3)")
              .html(v.xcomponente_id + "-" + v.xcomponente);
            $(e).find("td:eq(4)").html(v.xcantidad);
            $(e)
              .find("td:eq(5)")
              .html(v.xalmacen_id + "-" + v.xalmacen);
            $(e).find("td:eq(6)").html(v.xconcepto);
            $(e).find("td:eq(7)").html(v.xstock_before);
            $(e).find("td:eq(8)").html(v.xstock_after);

            $("#tbl-movs tbody").append(e);
          });
          $("#modalMovs").modal("show");
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          alert(XMLHttpRequest.responseText);
        },
      });
    }
  });
  $("#msp-art").chosen({
    no_results_text: "!Oops, no hay coincidencias!",
    width: "80%",
  });

  $("#btn-copy").click(function () {
    var status = 1,
      msg = "";

    if ($("#f-pedido").val() == "") {
      status = 0;
      msg = "<div>No puede duplicar un pedido no creado.</div>";
    }

    if (status == 1) {
      if (
        confirm(
          "¿Deseas duplicar este pedido?? Pedido: " + $("#f-pedido").val()
        )
      ) {
        var cmd =
          "module=pedidos&method=copy-order&ord=" + $("#f-pedido").val();
        $.ajax({
          url: "api-app.php",
          type: "POST",
          data: cmd,
          dataType: "json",
          success: function (d) {
            if (d.status == 1) {
              $.niftyNoty({
                type: "success",
                title: "Duplicar Pedido",
                message: d.msg,
                container: "floating",
                timer: 3000,
              });
            }
          },
          error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
          },
        });
      }
    } else {
      $.niftyNoty({
        type: "danger",
        title: "Duplicar Pedido",
        message: msg,
        container: "floating",
        timer: 3000,
      });
    }
  });

  $("#btn-chg-estado").click(function () {
    var status = 1,
      msg = "";
    $("#btn-chg-estado").attr("disabled", true);
    $("#img-loading-chg-estado").removeClass("hidden");

    if (status == 1) {
      var cmd =
        "module=pedidos-compras&method=chg-estado&ord=" +
        $("#f-pedido").val() +
        "&id=" +
        $("#mdl-ce-estado-id").val() +
        "&obs=" +
        $("#mdl-ce-obs").val();
      cmd_params = cmd;
      $.ajax({
        url: "api-app.php",
        type: "POST",
        data: cmd,
        dataType: "json",
        success: function (d) {
          $("#btn-chg-estado").attr("disabled", false);
          $("#img-loading-chg-estado").addClass("hidden");
          if (d.status == 1) {
            $("#mdlChgEstado").modal("hide");
            $("#f-estado").val($("#mdl-ce-estado-new").val());
            ped_estado = d.estado;
            checkPermisoAlmacen();
            $.niftyNoty({
              type: "success",
              title: "Cambiar Estado",
              message: d.msg,
              container: "floating",
              timer: 3000,
            });
          } else {
            $("#mdlChgEstado").modal("hide");
            $.niftyNoty({
              type: "danger",
              title: "Cambiar Estado",
              message: d.msg,
              container: "floating",
              timer: 6000,
            });
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          //alert(XMLHttpRequest.responseText);

          $("#mdlChgEstado").modal("hide");
          $("#btn-chg-estado").attr("disabled", false);
          $("#img-loading-chg-estado").addClass("hidden");
          $.niftyNoty({
            type: "danger",
            title: "Cambiar Estado",
            message: XMLHttpRequest.responseText,
            container: "page",
          });

          var cmd =
            "module=tools&method=log-error&ref=" +
            encodeURIComponent("ERROR PANEL CHG-ESTADO PEDIDOS") +
            "&data=" +
            encodeURIComponent(XMLHttpRequest.responseText) +
            "&params=" +
            encodeURIComponent(cmd_params);
          $.ajax({
            url: "api-app.php",
            type: "POST",
            data: cmd,
            dataType: "json",
            success: function (d) {},
            error: function (XMLHttpRequest, textStatus, errorThrown) {
              alert(XMLHttpRequest.responseText);
            },
          });
        },
      });
    } else {
      $("#btn-chg-estado").attr("disabled", false);
      $.niftyNoty({
        type: "danger",
        title: "Cambiar Estado",
        message: msg,
        container: "floating",
        timer: 3000,
      });
    }
  });
  $(".chg-estado").click(function () {
    var status = 1,
      msg = "";

    if ($("#f-pedido").val() == "") {
      status = 0;
      msg = "Debe guardar o crear un pedido antes.";
    }

    if (status == 1) {
      var id = $(this).data("estado");
      var estado_new = $(this).text();
      var estado_old = $("#f-estado").val();
      //alert('modal ' + id + ' ' + estado_new + ' ' + estado_old);
      $("#mdl-ce-estado-old").val(estado_old);
      $("#mdl-ce-estado-new").val(estado_new);
      $("#mdl-ce-estado-id").val(id);
      $("#mdlChgEstado").modal("show");
    } else {
      $.niftyNoty({
        type: "danger",
        title: "Cambiar Estado",
        message: msg,
        container: "floating",
        timer: 3000,
      });
    }
  });
  $("#btn-print").click(function () {
    if (ped_estado == "P") {
      imprimir = 1;
      $("#btn-save").click();
    } else {
      imprimir_pedido();
    }
  });
  $("#btn-new").click(function () {
    location.href = "?module=pedidos-compras";
  });
  //$('#f-proveedor').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

  $(
    "#f-fecha-salida .input-group.date,#f-eta .input-group.date,#f-eta-real .input-group.date,#f-fecha-pedido .input-group.date"
  ).datepicker({
    format: "dd/mm/yyyy",
    todayBtn: "linked",
    autoclose: true,
    todayHighlight: true,
    language: "es",
  });
  $("#btn-add-product-search").click(function () {
    var status = 1,
      msg = "";

    if ($("#msp-art").val() == "") status = 0;

    if (status == 1) {
      var precio = $("#msp-art option:checked").data("precio"),
        precio_format = "";
      precio_format = $.number(precio, 2, ",", "");
      //var fecha = new Date(), fecha_format = '';
      //fecha_format = fecha.getFullYear() + '-' + (fecha.getMonth() + 1) + '-' + fecha.getDate() + ' ' + fecha.getHours() + ':' + fecha.getMinutes() + ':' + fecha.getSeconds();
      //console.debug(fecha_format);

      mygrid.cellById(rowId, 0).setValue("");
      mygrid.cellById(rowId, 1).setValue($("#msp-art").val());
      mygrid.cellById(rowId, 2).setValue($("#msp-art option:checked").text());
      mygrid.cellById(rowId, 3).setValue("1");
      mygrid.cellById(rowId, 4).setValue(precio_format);
      mygrid.cellById(rowId, 5).setValue(precio_format);
      //mygrid.setRowAttribute(rowId, 'xhash', $('#msp-art option:checked').data('hash'));

      if (mygrid.getRowIndex(rowId) + 1 == mygrid.getRowsNum()) {
        add_row();
      }
      window.setTimeout(function () {
        //mygrid.selectCell(mygrid.getRowIndex(rowId), 4, false, false, true, true);
        calc_importe_total();
      }, 1);
      $("#modalSearchProduct").modal("hide");
    } else {
      $.niftyNoty({
        type: "success",
        title: "Asignación de Producto",
        message: "Tiene que seleccionar un producto.",
        container: "floating",
        timer: 3000,
      });
    }
  });
  $("#btn-back").click(function () {
    location.href = "index.php?module=list-pedidos-compras";
  });
  $("#btn-save").click(function () {
    var status = 1,
      n = 0,
      i = 0,
      c = 0;
    var msg = "",
      lin = [],
      tmp = [],
      iva = [];

    if ($("#f-proveedor").val() == "") {
      status = 0;
      msg += "Debe seleccionar un cliente.<br>";
    }

    //chequeamos la empresa que sea obligatoria
    if ($("#f-empresa").val() == "") {
      status = 0;
      msg += "Debe seleccionar una empresa.<br>";
    }
    /*
         if ($('#f-fecha input').val() == '') {
         status = 0;
         msg += 'Debe introducir la fecha del pedido.<br>';
         }
         * 
         */

    i = 0;
    c = 0; //contador de lineas válidas
    //tmp = [];
    mygrid.forEachRow(function (id) {
      i++;
      if (
        mygrid.cellById(id, 1).getValue() != "" &&
        mygrid.cellById(id, 2).getValue() != ""
      ) {
        c++;
        if (mygrid.cellById(id, 2).getValue() == "") {
          status = 0;
          msg +=
            "Error de datos línea nº " +
            i +
            " código: " +
            mygrid.cellById(id, 1).getValue() +
            " sin descripción.<br>";
        }
        n = mygrid.cellById(id, 3).getValue().replace(/,/gi, ".");
        if (!$.isNumeric(n)) {
          status = 0;
          msg +=
            "Error de datos línea nº " +
            i +
            " código: " +
            mygrid.cellById(id, 1).getValue() +
            " valor incorrecto: <strong>" +
            n +
            "</strong>.<br>";
        }
        n = mygrid.cellById(id, 4).getValue().replace(/,/gi, ".");
        if (!$.isNumeric(n)) {
          status = 0;
          msg +=
            "Error de datos línea nº " +
            i +
            " código: " +
            mygrid.cellById(id, 1).getValue() +
            " valor incorrecto: <strong>" +
            n +
            "</strong>.<br>";
        }
      }
    });
    if (c == 0) {
      status = 0;
      msg +=
        "No existen líneas válidas. Debe de contener al menos una línea.<br>";
    }

    if (status == 1) {
      $("#img-loading").removeClass("hidden");
      $("#btn-save").attr("disabled", true);

      mygrid.forEachRow(function (id) {
        if (
          mygrid.cellById(id, 1).getValue() != "" &&
          mygrid.cellById(id, 2).getValue() != ""
        ) {
          var d = [];
          d.push(mygrid.cellById(id, 1).getValue());
          d.push(encodeURIComponent(mygrid.cellById(id, 2).getValue()));
          d.push(mygrid.cellById(id, 3).getValue().replace(/,/gi, "."));
          d.push(mygrid.cellById(id, 4).getValue().replace(/,/gi, "."));
          d.push(mygrid.cellById(id, 5).getValue().replace(/,/gi, "."));
          lin.push(d.join("|"));
        }
      });

      var cmd =
        "module=pedidos-compras&method=save&" +
        $.param(
          $("input[name^=x],select[name^=x],textarea[name^=x]").serializeArray()
        );
      cmd += "&action=" + action;
      cmd += "&ximporte=" + $("#f-importe-total").val().replace(/,/gi, ".");
      cmd += "&xproveedor_id=" + $("#f-proveedor").data("id");
      cmd += "&xfecha=" + $("#f-fecha").val();
      if (action == "update") {
        cmd += "&xpedido_id=" + $("#f-pedido").val();
      }
      cmd += "&lin=" + lin.join(";");
      //console.debug(cmd);
      $.ajax({
        url: "api-app.php",
        type: "POST",
        data: cmd,
        dataType: "json",
        success: function (d) {
          $("#img-loading").addClass("hidden");
          $("#btn-save").attr("disabled", false);
          if (d.status == 1) {
            action = "update";
            $("#f-pedido").val(d.id);
            $("#f-hash").val(d.hash);
            $.niftyNoty({
              type: "success",
              title: "Guardar datos",
              message: d.msg,
              container: "floating",
              timer: 3000,
            });
            if (imprimir == 1) {
              imprimir = 0;
              imprimir_pedido();
            }
          } else {
            $.niftyNoty({
              type: "danger",
              title: "Guardar datos",
              message: d.msg,
              container: "floating",
              timer: 3000,
            });
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          //alert(textStatus);
          var e = $(alert_widget_tpl);
          $(e).find("#alert-title").html("Error");
          $(e).find("#alert-text").html(XMLHttpRequest.responseText);
          $("#alert-widget").append(e);
          //$('body').scrollTo('#alert-widget');
        },
      });
    } else {
      $.niftyNoty({
        type: "danger",
        title: "Guardar datos",
        message: msg,
        container: "floating",
        timer: 5000,
      });
    }
  });

  init_dropzone();
  init_dropzone_multiple();
  init_grid();
  autocomplete();

  if (action == "update" && ped_lin.length > 0) {
    load_lines(ped_lin);
    add_row();
  } else {
    add_row();
  }

  if ($("#f-pedido").val() != "") {
    load_docs($("#f-pedido").val());
  }

  /*
     if ($.inArray(rol, [11]) >= 0) {
     $('#btn-movs').remove();
     }
     * 
     */
});

function imprimir_pedido() {
  var ped = [],
    cmd = "",
    web = $("#f-web").data("web");

  //ped.push($('#f-pedido').val());
  //cmd = 'api-app.php?module=pedidos&method=dl-doc&peds=' + ped.join(';');
  if (web == 1)
    cmd =
      "api-app.php?module=pedidos&method=dl-doc-ms&id=" + $("#f-pedido").val();
  if (web == 4)
    cmd =
      "api-app.php?module=pedidos&method=dl-doc-an&id=" + $("#f-pedido").val();
  console.debug(cmd);
  window.open(cmd);
}

function calc_importe_lin(rId) {
  var cant = 0,
    precio = 0,
    imp = 0;

  cant = parseFloat(mygrid.cellById(rId, 3).getValue().replace(/,/gi, "."));
  precio = parseFloat(mygrid.cellById(rId, 4).getValue().replace(/,/gi, "."));
  imp = cant * precio;

  mygrid.cellById(rId, 5).setValue($.number(imp, 2, ",", ""));
}

function calc_importe_total() {
  var imp = 0;

  mygrid.forEachRow(function (id) {
    if (
      mygrid.cellById(id, 1).getValue() != "" &&
      mygrid.cellById(id, 2).getValue() != ""
    ) {
      imp += parseFloat(mygrid.cellById(id, 5).getValue().replace(/,/gi, "."));
    }
  });

  $("#f-importe-total").val($.number(imp, 2, ",", ""));
}

function add_row() {
  var id = new Date().valueOf() + "" + Math.round(Math.random() * 1000, 0);
  mygrid.addRow(id, "");
  mygrid
    .cellById(id, 0)
    .setValue(
      "img/grid-icons/search.png^Buscar/Añadir Producto^javascript:search_product(" +
        id +
        ");^_self"
    );
  mygrid.cellById(id, 3).setValue("1");
  mygrid.cellById(id, 4).setValue("0,00");
  mygrid.cellById(id, 5).setValue("0,00");
  mygrid
    .cellById(id, mygrid.getColumnsNum() - 1)
    .setValue(
      "img/grid-icons/remove.png^Eliminar Línea^javascript:delete_row(" +
        id +
        ");^_self"
    );
  mygrid.selectRowById(id, false, true, false);
  return id;
}

function search_product(id) {
  var status = 1,
    msg = "";
  mygrid.editStop();
  //mygrid.selectCell(mygrid.getRowIndex(rowId), 5, false, false, false, false);

  if ($("#f-proveedor").val() == "") {
    status = 0;
    msg += "Debe seleccionar un proveedor antes de introducir líneas.<br>";
  }

  if (status == 1) {
    rowId = id;
    $("#txt-product").val("");
    $("#msp-lote").empty().append("<option>Selecciona Dato</option>");
    $("#modalSearchProduct").modal("show");
    spd = {}; //inicializamos el
    window.setTimeout(function () {
      $("#txt-product").focus();
    }, 600);
  } else {
    $.niftyNoty({
      type: "danger",
      title: "Añadir Producto",
      message: msg,
      container: "floating",
      timer: 3000,
    });
  }
}
function delete_row(id) {
  var status = 1,
    msg = "";

  if (status == 1) {
    if (mygrid.cellById(id, 2).getValue() != "") {
      if (
        confirm(
          "¿Deseas eliminar esta línea: " +
            mygrid.cellById(id, 1).getValue() +
            "/" +
            mygrid.cellById(id, 2).getValue() +
            "?"
        )
      ) {
        mygrid.deleteRow(id);
        calc_importe_total();
        $.niftyNoty({
          type: "success",
          title: "Eliminar Línea",
          message: "Línea eliminada correctamente",
          container: "floating",
          timer: 3000,
        });
      }
    }
  } else {
    $.niftyNoty({
      type: "danger",
      title: "Eliminar Línea",
      message: msg,
      container: "floating",
      timer: 3000,
    });
  }
}

function init_dropzone() {
  Dropzone.options.imageGalleryDropzone = false; // Prevent Dropzone from auto discovering this element
  var dropZoneTemplate = $.get(
    "tpl/gallery-dropzone-template.html",
    function (template) {
      $("#imageGalleryDropzone").dropzone({
        paramName: "file", // The name that will be used to transfer the file
        maxFilesize: 10, // MB
        //acceptedFiles: '.jpg,.jpeg,.png,.gif',
        acceptedFiles: ".jpg,.jpeg,.png,.doc,.docx,.odt,.xls,.xlsx,.pdf",
        maxFiles: 1,
        uploadMultiple: false,
        previewsContainer: ".gallery-container",
        previewTemplate: template,
        init: function () {
          this.on("maxfilesexceeded", function (file) {
            this.removeAllFiles(); // borra el anterior
            this.addFile(file); // añade el nuevo
          });
          this.on("addedfile", function (d) {
            //$('#modalUpload').find('.gallery-container').empty();
            //alert("Added file.");
            //console.debug(d);
            //console.debug(d.status);
            /*if (d.status == 'added') {
                     console.debug('fichero añadido');
                     try {
                     var res = JSON.parse(d.xhr.response);
                     console.debug(res);
                     switch (res.module) {
                     case 'mascota':
                     alert('ok');
                     break;
                     }
                     } catch (err) {
                     console.debug(err + ' response xhr:' + d.xhr.response);
                     }
                     }*/
          });
          this.on("success", function (d) {
            //alert("Added file.");
            //console.debug(d.status);
            if (d.status == "success") {
              try {
                var res = JSON.parse(d.xhr.response);
                console.debug(res);
                $("#mu-filename").val(res.filename);

                /*
                             if (res.dst_image == 'mandasaldo') {
                             $('#img-upload').attr('src', res.url + '?' + (new Date()).valueOf());
                             $('#f-img').val(res.filename);
                             }
                             if (res.dst_image == 'revendedores') {
                             $('#img-upload-rev').attr('src', res.url);
                             $('#f-img-rev').val(res.filename);
                             }
                             if (res.dst_image == 'mercarapid') {
                             $('#img-upload-mr').attr('src', res.url);
                             $('#f-img-mr').val(res.filename);
                             }
                             if (res.dst_image == 'allnovu') {
                             $('#img-upload-an').attr('src', res.url);
                             $('#f-img-an').val(res.filename);
                             }
                             * 
                             */
                //$('#modalUpload').modal('hide');
              } catch (err) {
                console.debug(err + " response xhr:" + d.xhr.response);
              }
            }
          });
        },
      });
    }
  ).fail(function () {
    alert("Image Gallery Error: could not load gallery html template");
  });
}

function init_dropzone_multiple() {
  Dropzone.options.imageGalleryDropzone = false; // Prevent Dropzone from auto discovering this element
  var dropZoneTemplate = $.get(
    "tpl/gallery-dropzone-template.html",
    function (template) {
      $("#imageGalleryDropzoneMultiple").dropzone({
        paramName: "file", // The name that will be used to transfer the file
        maxFilesize: 10, // MB
        //acceptedFiles: '.jpg,.jpeg,.png,.gif',
        acceptedFiles: ".jpg,.jpeg",
        maxFiles: 30,
        parallelUploads: 30,
        uploadMultiple: true,
        previewsContainer: ".gallery-container-multiple",
        previewTemplate: template,
        init: function () {
          /*
                 this.on('maxfilesexceeded', function (file) {
                 this.removeAllFiles(); // borra el anterior
                 this.addFile(file);    // añade el nuevo
                 });
                 */
          this.on("addedfile", function (d) {
            //$('#modalUpload').find('.gallery-container').empty();
            //alert("Added file.");
            //console.debug(d);
            //console.debug(d.status);
            /*if (d.status == 'added') {
                     console.debug('fichero añadido');
                     try {
                     var res = JSON.parse(d.xhr.response);
                     console.debug(res);
                     switch (res.module) {
                     case 'mascota':
                     alert('ok');
                     break;
                     }
                     } catch (err) {
                     console.debug(err + ' response xhr:' + d.xhr.response);
                     }
                     }*/
          });
          this.on("success", function (d) {
            //alert("Added file.");
            //console.debug(d.status);
            /*
                     if (d.status == 'success') {
                     try {
                     var res = JSON.parse(d.xhr.response);
                     console.debug(res);
                     $('#mu-filename').val(res.filename);
                     
                     //$('#modalUpload').modal('hide');
                     } catch (err) {
                     console.debug(err + ' response xhr:' + d.xhr.response);
                     }
                     }
                     */
          });
          this.on("queuecomplete", function () {
            console.debug("todos subidos");
          });
        },
      });
    }
  ).fail(function () {
    alert("Image Gallery Error: could not load gallery html template");
  });
}

function init_grid() {
  var field = "ed";
  /*
     * if (action == 'update') {
     field = 'ro';
     }
     * 
     */
  mygrid = new dhtmlXGridObject("gridbox");
  mygrid.setImagePath(libgrid + "/imgs/");
  mygrid.setHeader(",Código,Descripción,Cantidad,Precio,Importe,"); //14
  mygrid.setInitWidths("40,80,500,80,80,80,40");
  mygrid.setColAlign("center,right,left,right,right,right,center");
  mygrid.setColTypes("img,ro,ro," + field + ",ed,ro,img");
  //mygrid.setColTypes("img,ro,ro,ro,ro,ro,ro,img");

  dhtmlXCalendarObject.prototype.langData["es"] = {
    dateformat: "%d/%m/%Y",
    monthesFNames: [
      "Enero",
      "Febrero",
      "Marzo",
      "Abril",
      "Mayo",
      "Junio",
      "Julio",
      "Agosto",
      "Septiembre",
      "Octubre",
      "Noviembre",
      "Diciembre",
    ],
    monthesSNames: [
      "Ene",
      "Feb",
      "Mar",
      "Abr",
      "May",
      "Jun",
      "Jul",
      "Ago",
      "Sep",
      "Oct",
      "Nov",
      "Dic",
    ],
    daysFNames: [
      "Domingo",
      "Lunes",
      "Martes",
      "Miércoles",
      "Jueves",
      "Viernes",
      "Sábado",
      "Domingo",
    ],
    daysSNames: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
    weekstart: 1,
    weekname: "w",
    today: "Hoy",
    clear: "Borrar",
  };
  dhtmlXCalendarObject.prototype.lang = "es";
  mygrid.init();
  mygrid.attachEvent("onEditCell", function (stage, rId, cInd, nValue, oValue) {
    //console.debug('stage: ' + stage);
    var status, msg;
    if (
      stage == 1 &&
      $.inArray(cInd, [3, 4]) >= 0 &&
      this.editor &&
      this.editor.obj
    ) {
      $(this.editor.obj).attr("style", "text-align:right");
      $(this.editor.obj).select();
    }
    if (stage == 2) {
      //colocamos el codigo cuando se modifica una celda
      rowId = rId;
      var mresult = true;
      switch (cInd) {
        case 3: //cantidad
          var n, c;
          n = nValue.replace(/,/gi, ".");
          if ($.isNumeric(n)) {
            nValue = $.number(parseFloat(n), 0, ",", "");
          }
          //c = $.number((n * parseFloat(mygrid.cellById(rId, 6).getValue().replace(/,/gi, '.'))) / 100, 4, ',', '');
          window.setTimeout(function () {
            mygrid.cellById(rId, cInd).setValue(nValue);
            //mygrid.cellById(rId, 6).setValue(c);//asginamos coste calculado
            calc_importe_lin(rId);
            calc_importe_total();
            //calc_importe_iva();
            //mygrid.selectCell(mygrid.getRowIndex(rId), 8, false, false, true, true);
          }, 100);
          break;
        case 4: //precio
          var n, c;
          n = nValue.replace(/,/gi, ".");
          if ($.isNumeric(n)) {
            nValue = $.number(parseFloat(n), 0, ",", "");
          }
          //c = $.number((n * parseFloat(mygrid.cellById(rId, 6).getValue().replace(/,/gi, '.'))) / 100, 4, ',', '');
          window.setTimeout(function () {
            mygrid.cellById(rId, cInd).setValue(nValue);
            //mygrid.cellById(rId, 6).setValue(c);//asginamos coste calculado
            calc_importe_lin(rId);
            calc_importe_total();
            //calc_importe_iva();
            //mygrid.selectCell(mygrid.getRowIndex(rId), 8, false, false, true, true);
          }, 100);
          break;
      }

      return mresult;
    }
  });
}

function load_lines(items) {
  $.each(items, function (i, v) {
    var id = add_row();
    mygrid.cellById(id, 0).setValue("");
    mygrid.cellById(id, 1).setValue(v.xcomponente_id);
    mygrid.cellById(id, 2).setValue(v.xcomponente);
    mygrid.cellById(id, 3).setValue($.number(v.xcantidad, 0, ",", ""));
    mygrid.cellById(id, 4).setValue($.number(v.xprecio, 2, ",", ""));
    mygrid.cellById(id, 5).setValue($.number(v.ximporte, 2, ",", ""));
  });

  return true;
}

function load_docs() {
  var cmd =
    "module=pedidos-compras&method=list-docs&pedido=" + $("#f-pedido").val();
  $.ajax({
    url: "api-app.php",
    type: "GET",
    data: cmd,
    dataType: "json",
    success: function (d) {
      //console.debug(d);
      if (d.status == 1) {
        $("#tbl-documentos tbody").empty();
        $.each(d.items, function (i, v) {
          var e = $(tpl_lin_doc);
          //alert(v.xtipo);
          $(e).attr("data-tipo", v.xtipo_id);
          $(e).find("td:eq(0)").html(v.xname);
          $(e).find("td:eq(1)").html(v.xdescripcion);
          $(e).find("td:eq(2)").html(v.xdate_upload);
          $(e).find("td:eq(3)").html(v.xdate_download);
          if (v.xtipo_id == "inspeccion") {
            $(e)
              .find("td:eq(4)")
              .html(
                '<a class="fa fa-eye view-docs" href="javascript:void(0);" title="Ver documentos"></a>'
              );
          }
          $("#tbl-documentos tbody").append(e);
        });
      }
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      alert(textStatus);
    },
  });

  return true;
}

function autocomplete() {
  $("#f-proveedor").autocomplete({
    source: function (request, response) {
      var cmd =
        "module=pedidos-compras&method=search-proveedor&t=" + request.term;
      $.ajax({
        url: "api-app.php",
        type: "GET",
        data: cmd,
        dataType: "json",
        success: function (d) {
          //console.debug(d);
          if (d.data != null) {
            response(
              $.map(d.data, function (item) {
                var label = item.xproveedor_id + " - " + item.xproveedor;
                var p = {
                  label: label,
                  value: label,
                  pro: item.xproveedor,
                  pro_id: item.xproveedor_id,
                };
                return p;
              })
            );
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          alert(textStatus);
        },
      });
    },
    minLength: 3,
    select: function (event, ui) {
      spd = ui.item; //asignamos el producto seleccionado a una variable glogal
      //console.debug(ui.item);
      $("#f-proveedor").data("id", spd.pro_id);
      $("#f-proveedor").data("pro", spd.pro);
    },
    open: function () {
      $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
    },
    close: function () {
      $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
    },
  });
  $("#proveedor-clear-autocomplete").click(function () {
    $("#f-proveedor").val("").data("id", "").data("pro", "").focus();
  });

  $("#txt-product").autocomplete({
    source: function (request, response) {
      var cmd = "module=pedidos&method=search-product&t=" + request.term;
      $.ajax({
        url: "api-app.php",
        type: "GET",
        data: cmd,
        dataType: "json",
        success: function (d) {
          //console.debug(d);
          if (d.data != null) {
            response(
              $.map(d.data, function (item) {
                var label = item.xcomponente;
                var p = {
                  label: label,
                  value: item.xcomponente,
                  art: item.xcomponente,
                  art_id: item.xcomponente_id,
                };
                return p;
              })
            );
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          alert(textStatus);
        },
      });
    },
    minLength: 3,
    select: function (event, ui) {
      spd = ui.item; //asignamos el producto seleccionado a una variable glogal
      //console.debug(ui.item);
      $("#txt-product").data("id", spd.com_id);
    },
    open: function () {
      $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
    },
    close: function () {
      $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
    },
  });
}

var alert_tpl =
  '\
    <div class="alert alert-danger fade in">\
        <button class="close" data-dismiss="alert"><span>&times;</span></button>\
        <div class="alert-content">\
            <div><strong>Oh snap!</strong> Change a few things up and try submitting again.</div>\
            <div><strong>Oh snap!</strong> Change a few things up and try submitting again.</div>\
        </div>\
    </div>\
';

var alert_widget_tpl =
  '\n\
    <div class="alert alert-danger fade in">\n\
        <button class="close" data-dismiss="alert"><span>&times;</span></button>\n\
        <strong><span id="alert-title">Oh snap!</span></strong> <span id="alert-text">Change a few things up and try submitting again.</span>\n\
    </div>\n\
';

var tpl_img_tracking =
  '\n\
    <div class="row">\n\
        <div class="col-md-12">\n\
            <img class="img-responsive" src="/img/envios/0032-3438-0012-015-01.jpg"/>\n\
            <p>Tracking CU000349888RT Referencia 0032-3438-0012-015-01</p>\n\
        </div>\n\
    </div>\n\
';

var tpl_lin_mov =
  "\
    <tr>\
        <td>Fecha</td>\
        <td>Doc.</td>\
        <td>Artículo</td>\
        <td>Componente</td>\
        <td>Cantidad</td>\
        <td>Almacén</td>\
        <td>Concepto</td>\
        <td>Stock Antes</td>\
        <td>Stock Después</td>\
    </tr>";

var tpl_lin_his =
  "\
    <tr>\
        <td>Fecha</td>\
        <td>Estado</td>\
        <td>Usuario</td>\
        <td>Descripción</td>\
    </tr>";

var tpl_lin_doc =
  '\
    <tr>\
        <td>Tipo</td>\
        <td>Desc</td>\
        <td>Fecha</td>\
        <td>Fecha</td>\
        <td class="text-center">\n\
            <a class="fa fa-cloud-download dl-doc" href="javascript:void(0);" title="Descargar documento"></a>\n\
        </td>\
    </tr>';

var tpl_item_image =
  '\n\
    <a href="docs/5_inspeccion_1.jpg" data-lightbox="galeria">\n\
        <div class="col-md-2">\n\
            <img class="img-thumbnail" src="docs/5_inspeccion_1.jpg"/>\n\
        </div>\n\
    </a>\n\
';

function checkPermisoAlmacen() {
  if ($.inArray(parseInt(ped_estado), [12, 17, 6, 7, 23, 24]) >= 0) {
    $("#f-almacen").attr("disabled", true);
  } else {
    $("#f-almacen").attr("disabled", false);
  }
}
