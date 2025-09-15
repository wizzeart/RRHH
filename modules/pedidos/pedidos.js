var libgrid = "/plugins/dhtmlxGrid/codebase";
var spd, cmd_params; //datos del cliente seleccionado
var rowId; //almacena temporal el id de la línea para poder tratarla
var mygrid;
var imprimir = 0;
var style_row_cancel = "color: red; text-decoration: line-through;";
//mygrid.setRowAttribute(rowId, 'medida', $('#msl-lote option:selected').data('medida-id'));
//mygrid.getRowAttribute(id, 'medida')

$(document).ready(function () {
  checkPermisoAlmacen();
  $("#btn-close").click(function () {
    window.close();
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

  $("#aceptar-seguro").click(function () {
    var seguro_imp = 0,
      seguro_coste = $("#f-seguro").data("seguro"),
      tipo = "",
      cantidad = 0,
      seguro_lin = 0;

    //console.debug(seguro_imp);

    mygrid.forEachRow(function (id) {
      if (
        mygrid.cellById(id, 1).getValue() != "" &&
        mygrid.cellById(id, 2).getValue() != ""
      ) {
        cantidad = mygrid.cellById(id, 4).getValue();
        tipo = mygrid.getRowAttribute(id, "xtipo");
        seguro_lin = mygrid.getRowAttribute(id, "xseguro");
        //console.debug(tipo);
        if (tipo == "A") {
          seguro_imp += seguro_lin * cantidad;
        }
      }
    });
    if (seguro_imp == 0) seguro_imp = seguro_coste;
    $("#f-seguro").val($.number(seguro_imp, 2, ",", ""));
    calc_importe_total();
  });
  $("#rechazar-seguro").click(function () {
    $("#f-seguro").val("0,00");
    calc_importe_total();
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
  $("#btn-cancel-lin-accept").click(function () {
    var status = 1,
      msg = "";

    if (ped_estado != 12 && ped_estado != 4 && ped_estado != 5) {
      status = 0;
      msg =
        "<div>No puede cancelar líneas si el pedido no está verificado/enviado.</div>";
    }

    if (status == 1) {
      var cmd =
        "module=pedidos&method=cancel-line&lin=" +
        $("#mcl-lin").val() +
        "&rev=" +
        revendedor;
      $.ajax({
        url: "api-app.php",
        type: "POST",
        data: cmd,
        dataType: "json",
        success: function (d) {
          if (d.status == 1) {
            mygrid.forEachRow(function (id) {
              if (mygrid.getRowAttribute(id, "xlin_id") == d.lin_id) {
                mygrid.setRowTextStyle(id, style_row_cancel);
              }
            });

            $("#mdlCancelLin").modal("hide");
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          alert(XMLHttpRequest.responseText);
        },
      });
    } else {
      $.niftyNoty({
        type: "danger",
        title: "Cancelar Pedido",
        message: msg,
        container: "floating",
        timer: 3000,
      });
    }
  });
  $("#btn-cancel-lin").click(function () {
    var status = 1,
      msg = "";

    if ($("#f-pedido").val() == "") {
      status = 0;
      msg +=
        "<div>No puede cancelar líneas si el pedido está sin guardar.</div>";
    }

    if (ped_estado != 12 && ped_estado != 4 && ped_estado != 5) {
      status = 0;
      msg +=
        "<div>No puede cancelar líneas si el pedido no está verificado/enviado.</div>";
    }

    if (status == 1) {
      var cmd =
        "module=pedidos&method=load-lines&ord=" +
        $("#f-pedido").val() +
        "&rev=" +
        revendedor;
      $.ajax({
        url: "api-app.php",
        type: "POST",
        data: cmd,
        dataType: "json",
        success: function (d) {
          if (d.status == 1) {
            $("#mcl-lin").empty();
            $.each(d.items, function (i, v) {
              $("#mcl-lin").append(
                '<option value="' +
                  v.xlin_id +
                  '">' +
                  v.xarticulo_id +
                  " " +
                  v.xarticulo +
                  " (lin:" +
                  v.xlin_id +
                  ")</option>"
              );
            });
            if (d.rev_id > 0) {
              $("#mcl-rev").removeClass("hidden");
              $("#mcl-rev").find("div").html(d.rev);
            }

            $("#mdlCancelLin").modal("show");
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          alert(XMLHttpRequest.responseText);
        },
      });
    } else {
      $.niftyNoty({
        type: "danger",
        title: "Cancelar Pedido",
        message: msg,
        container: "floating",
        timer: 3000,
      });
    }
  });
  $("#btn-cancel-all").click(function () {
    if (
      confirm(
        "¿Deseas cancelar este pedido con todos sus trackings y envíos?? Pedido: " +
          $("#f-pedido").val()
      )
    ) {
      var cmd =
        "module=pedidos&method=cancel-order&ord=" + $("#f-pedido").val();
      $.ajax({
        url: "api-app.php",
        type: "POST",
        data: cmd,
        dataType: "json",
        success: function (d) {
          if (d.status == 1) {
            $("#btn-cancel-all").addClass("hidden");
            $.niftyNoty({
              type: "success",
              title: "Cancelar Pedido",
              message: d.msg,
              container: "floating",
              timer: 3000,
            });
          } else {
            $.niftyNoty({
              type: "danger",
              title: "Cancelar Pedido",
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
  });

  $("#view-tracking").click(function () {
    var status = 1,
      msg = "",
      extra = 0;
    $("#view-tracking").attr("disabled", true);

    if ($("#f-tracking").val() == "") {
      if (
        confirm(
          "No tiene asignado tracking, ¿Desea ver si existe alguna imagen asociada en este pedido?"
        )
      ) {
        $("#f-tracking").val("extra");
      } else {
        status = 0;
        msg = "<div>Campo vacío</div>";
      }
    }

    if (status == 1) {
      var cmd =
        "module=pedidos&method=view-tracking&tracking=" +
        $("#f-tracking").val() +
        "&pedido=" +
        $("#f-pedido").val();
      /*if ($('#f-tracking').val() == 'extra') {
             cmd += '&pedido=' + $('#f-pedido').val()
             }*/
      $.ajax({
        url: "api-app.php",
        type: "POST",
        data: cmd,
        dataType: "json",
        success: function (d) {
          $("#view-tracking").attr("disabled", false);
          if (d.status == 1) {
            $("#mvt-title").html(
              "Ver Tracking del Pedido " + $("#f-pedido").val()
            );
            $("#modalViewTracking .modal-body").empty();
            $.each(d.items, function (i, v) {
              var e = $(tpl_img_tracking);
              $(e)
                .find("p")
                .html(
                  "Tracking <strong>" +
                    v.tracking +
                    "</strong> Referencia <strong>" +
                    v.referencia +
                    "</strong>"
                );
              $(e)
                .find("img")
                .attr("src", "/img/envios/" + v.image);
              if ("image2" in v)
                $(e)
                  .find("img")
                  .after(
                    '<br><img class="img-responsive" src="/img/envios/' +
                      v.image2 +
                      '"/>'
                  );
              $("#modalViewTracking .modal-body").append(e);
            });
            $("#modalViewTracking").modal("show");
            $.niftyNoty({
              type: "success",
              title: "Ver Tracking",
              message: d.msg,
              container: "floating",
              timer: 3000,
            });
          } else {
            $.niftyNoty({
              type: "danger",
              title: "Ver Tracking",
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
    } else {
      $("#view-tracking").attr("disabled", false);
      $.niftyNoty({
        type: "danger",
        title: "Ver Tracking",
        message: msg,
        container: "floating",
        timer: 3000,
      });
    }
  });
  $("#f-provincia").change(function () {
    $("#f-city")
      .empty()
      .append('<option value="">Selecciona Municipio</option>');
    $.each(municipios, function (i, v) {
      if (v.xprovincia == $("#f-provincia").val())
        $("#f-city").append(
          '<option value="' +
            v.xmunicipio +
            '">' +
            v.xmunicipio.replace(/_/gi, " ") +
            "</option>"
        );
    });
  });

  $("#btn-chg-estado").click(function () {
    var status = 1,
      msg = "";
    $("#btn-chg-estado").attr("disabled", true);
    $("#img-loading-chg-estado").removeClass("hidden");

    if (status == 1) {
      var cmd =
        "module=pedidos&method=chg-estado&ord=" +
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
            if ("emergency" in d && d.emergency == 1) {
              $("#btn-cancel-all").removeClass("hidden");
            }
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
    location.href = "?module=pedidos";
  });
  //$('#f-cliente').chosen({search_contains: true, no_results_text: "!Oops, no hay coincidencias!", width: '90%'});

  $("#f-fecha .input-group.date").datepicker({
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
      var fecha = new Date(),
        fecha_format = "";
      fecha_format =
        fecha.getFullYear() +
        "-" +
        (fecha.getMonth() + 1) +
        "-" +
        fecha.getDate() +
        " " +
        fecha.getHours() +
        ":" +
        fecha.getMinutes() +
        ":" +
        fecha.getSeconds();
      console.debug(fecha_format);

      mygrid.cellById(rowId, 1).setValue($("#msp-art").val());
      mygrid.cellById(rowId, 2).setValue($("#msp-art option:checked").text());
      mygrid.cellById(rowId, 3).setValue("");
      mygrid.cellById(rowId, 4).setValue("1");
      mygrid.cellById(rowId, 5).setValue(precio_format);
      mygrid.cellById(rowId, 6).setValue(precio_format);
      mygrid.setRowAttribute(
        rowId,
        "xhash",
        $("#msp-art option:checked").data("hash")
      );
      mygrid.setRowAttribute(rowId, "xdate", fecha_format);
      mygrid.setRowAttribute(
        rowId,
        "xtipo",
        $("#msp-art option:checked").data("tipo")
      );
      mygrid.setRowAttribute(rowId, "xcanjeado", "N");
      mygrid.setRowAttribute(
        rowId,
        "xseguro",
        $("#msp-art option:checked").data("seguro")
      );

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
    location.href = "index.php?module=list-pedidos";
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

    if ($("#f-fpago").val() == "") {
      status = 0;
      msg +=
        "La forma de pago no puede estar vacío, indique forma de pago según *Valores.<br>";
    }
    if ($("#f-cliente").val() == "") {
      status = 0;
      msg += "Debe seleccionar un cliente.<br>";
    }
    if ($("#f-fecha input").val() == "") {
      status = 0;
      msg += "Debe introducir la fecha del pedido.<br>";
    }
    if (
      ($("#f-provincia").val() == "" || $("#f-provincia").val() == null) &&
      $("#f-tipo-pedido").val() == "E"
    ) {
      status = 0;
      msg += "Debe introducir la provincia del pedido.<br>";
    }
    if (
      ($("#f-city").val() == "" || $("#f-city").val() == null) &&
      $("#f-tipo-pedido").val() == "E"
    ) {
      status = 0;
      msg += "Debe introducir la municipio del pedido.<br>";
    }
    if ($("#f-destino").val() == "") {
      $("#f-destino").val("1");
      //status = 0;
      //msg += 'Debe introducir el medio de transporte del pedido.<br>';
    }

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
        n = mygrid.cellById(id, 5).getValue().replace(/,/gi, ".");
        if (!$.isNumeric(n)) {
          status = 0;
          msg +=
            "Error de datos línea nº " +
            i +
            " código: " +
            mygrid.cellById(id, 1).getValue() +
            " orden incorrecto: <strong>" +
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
          d.push(mygrid.cellById(id, 3).getValue());
          d.push(mygrid.cellById(id, 4).getValue().replace(/,/gi, "."));
          d.push(mygrid.cellById(id, 5).getValue().replace(/,/gi, "."));
          d.push(mygrid.cellById(id, 6).getValue().replace(/,/gi, "."));
          d.push(mygrid.getRowAttribute(id, "xhash"));
          d.push(mygrid.getRowAttribute(id, "xdate"));
          d.push(mygrid.getRowAttribute(id, "xtipo"));
          d.push(mygrid.getRowAttribute(id, "xcanjeado"));
          lin.push(d.join("|"));
        }
      });

      if ($("#f-seguro").val() != "0,00") $("#aceptar-seguro").click();

      var cmd =
        "module=pedidos&method=save&" +
        $.param(
          $("input[name^=x],select[name^=x],textarea[name^=x]").serializeArray()
        );
      cmd += "&action=" + action;
      cmd += "&ximporte=" + $("#f-importe-total").val().replace(/,/gi, ".");
      cmd += "&xseguro=" + $("#f-seguro").val().replace(/,/gi, ".");
      cmd += "&xtransporte=" + $("#f-transporte").val().replace(/,/gi, ".");
      cmd += "&xcliente_id=" + $("#f-cliente").data("id");
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

  init_grid();
  autocomplete();

  if (action == "update" && ped_lin.length > 0) {
    if (municipio_value != "") {
      $("#f-provincia").change();
      $("#f-city").val(municipio_value);
    }

    load_lines(ped_lin);
    add_row();
  } else {
    add_row();
  }

  if ($.inArray(rol, [11]) >= 0) {
    $("#btn-movs").remove();
  }
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

  cant = parseFloat(mygrid.cellById(rId, 4).getValue().replace(/,/gi, "."));
  precio = parseFloat(mygrid.cellById(rId, 5).getValue().replace(/,/gi, "."));
  imp = cant * precio;

  mygrid.cellById(rId, 6).setValue($.number(imp, 2, ",", ""));
}

function calc_importe_total() {
  var imp = 0,
    seguro = 0,
    portes = 0;

  mygrid.forEachRow(function (id) {
    if (
      mygrid.cellById(id, 1).getValue() != "" &&
      mygrid.cellById(id, 2).getValue() != ""
    ) {
      imp += parseFloat(mygrid.cellById(id, 6).getValue().replace(/,/gi, "."));
    }
  });
  portes = transporte;
  if (imp > transporte_free) portes = 0;

  seguro = parseFloat($("#f-seguro").val().replace(/,/gi, "."));
  imp += seguro;

  imp += portes;
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
  mygrid.cellById(id, 4).setValue("1");
  mygrid.cellById(id, 5).setValue("0,00");
  mygrid.cellById(id, 6).setValue("0,00");
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

  if ($("#f-cliente").val() == "") {
    status = 0;
    msg += "Debe seleccionar un cliente antes de introducir líneas.<br>";
  }
  if (revendedor > 0) {
    status = 0;
    msg +=
      "<div>La líneas de un pedido de revendedor no pueden ser modificadas.</div>";
  }

  if (status == 1) {
    rowId = id;
    $("#txt-product").val("");
    $("#msp-lote")
      .empty()
      .append('<option data-lote="-1">Selecciona Lote/Caducidad</option>');
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

  if (revendedor > 0) {
    status = 0;
    msg +=
      "<div>La líneas de un pedido de revendedor no pueden ser modificadas.</div>";
  }

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

function init_grid() {
  var field = "ed";
  if (action == "update") {
    field = "ro";
  }
  mygrid = new dhtmlXGridObject("gridbox");
  mygrid.setImagePath(libgrid + "/imgs/");
  mygrid.setHeader(
    ",Código,Descripción,Teléfono/Cuenta,Cantidad,Precio,Importe,"
  ); //14
  mygrid.setInitWidths("40,80,300,300,80,80,80,40");
  mygrid.setColAlign("center,right,left,left,right,right,right,center");
  mygrid.setColTypes("img,ro,ro,ed," + field + ",ro,ro,img");
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
      $.inArray(cInd, [1, 7, 8]) >= 0 &&
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
        case 1: //codigo
          status = 1;
          msg = "";

          if ($("#f-cliente").val() == "") {
            status = 0;
            msg +=
              "Debe seleccionar un cliente antes de introducir líneas.<br>";
          }

          if (status == 1) {
            if (nValue != "") {
              var cmd =
                "module=pedidos&method=search-product-codigo&t=" + nValue;
              //console.log(cmd);
              $.ajax({
                url: "api-app.php",
                type: "GET",
                data: cmd,
                dataType: "json",
                success: function (d) {
                  if (d.status == 1) {
                    var r = d.item;

                    var iva_lin = $.number(r.iva.xvalue, 2, ",", "");
                    if ($("#f-iva").prop("checked") == false) iva_lin = "0,00";

                    mygrid.cellById(rowId, 1).setValue(r.xarticulo_id);
                    mygrid.cellById(rowId, 2).setValue(r.xarticulo);
                    mygrid.cellById(rowId, 4).setValue("");
                    mygrid.cellById(rowId, 5).setValue("");
                    mygrid.cellById(rowId, 6).setValue("");
                    mygrid.cellById(rowId, 7).setValue("0,000");
                    mygrid.cellById(rowId, 8).setValue("0,00");
                    mygrid.cellById(rowId, 9).setValue("0,00");
                    mygrid.cellById(rowId, 10).setValue(iva_lin);
                    mygrid.cellById(rowId, 11).setValue("0,00");
                    mygrid.cellById(rowId, 12).setValue("0,00");

                    if (mygrid.getRowIndex(rowId) + 1 == mygrid.getRowsNum()) {
                      add_row();
                    }
                    $("#modalSearchProduct").modal("hide");
                    window.setTimeout(function () {
                      mygrid.selectCell(
                        mygrid.getRowIndex(rowId),
                        4,
                        false,
                        false,
                        true,
                        true
                      );
                    }, 1);
                  } else {
                    $.niftyNoty({
                      type: "danger",
                      title: "Producto no encontrado",
                      message: d.msg,
                      container: "floating",
                      timer: 3000,
                    });
                  }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                  alert(textStatus);
                },
              });
            }
          } else {
            $.niftyNoty({
              type: "danger",
              title: "Añadir Producto",
              message: msg,
              container: "floating",
              timer: 3000,
            });
            mresult = false;
          }
          break;
        case 4: //cantidad
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
        case 8: //precio
          var n;
          n = nValue.replace(/,/gi, ".");
          if ($.isNumeric(n)) {
            nValue = $.number(parseFloat(n), 2, ",", "");
          }
          window.setTimeout(function () {
            mygrid.cellById(rId, cInd).setValue(nValue);
            calc_importe_lin(rId);
            calc_importe_total();
            calc_importe_iva();
            //mygrid.selectCell(mygrid.getRowIndex(rId), 4, false, false, true, true);
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
    mygrid.cellById(id, 1).setValue(v.xarticulo_id);
    mygrid.cellById(id, 2).setValue(v.xarticulo);
    mygrid.cellById(id, 3).setValue(v.xphone);
    mygrid.cellById(id, 4).setValue($.number(v.xcantidad, 0, ",", ""));
    mygrid.cellById(id, 5).setValue($.number(v.xprecio, 2, ",", ""));
    mygrid.cellById(id, 6).setValue($.number(v.ximporte, 2, ",", ""));
    mygrid.setRowAttribute(id, "xhash", v.xhash);
    mygrid.setRowAttribute(id, "xdate", v.xdate);
    mygrid.setRowAttribute(id, "xtipo", v.xtipo);
    mygrid.setRowAttribute(id, "xcanjeado", v.xcanjeado);
    mygrid.setRowAttribute(id, "xestado", v.xestado);
    mygrid.setRowAttribute(id, "xlin_id", v.xlin_id);
    mygrid.setRowAttribute(id, "xseguro", v.xseguro);
    if (v.xestado == 3) {
      mygrid.setRowTextStyle(id, style_row_cancel);
    }
  });

  return true;
}

function autocomplete() {
  $("#f-cliente").autocomplete({
    source: function (request, response) {
      var cmd = "module=pedidos&method=search-client&t=" + request.term;
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
                var label = item.xcliente_id + " - " + item.xcliente;
                var p = {
                  label: label,
                  value: label,
                  cli: item.xcliente,
                  cli_id: item.xcliente_id,
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
      $("#f-cliente").data("id", spd.cli_id);
      $("#f-cliente").data("cli", spd.cli);
      limpiarCamposEnvio();
      cargarDatosDireccionEnvio(spd.cli_id);
    },
    open: function () {
      $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
    },
    close: function () {
      $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
    },
  });
  $("#cliente-clear-autocomplete").click(function () {
    $("#f-cliente").val("").data("id", "").data("cli", "").focus();
    limpiarCamposEnvio();
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
                var label = item.xarticulo;
                var p = {
                  label: label,
                  value: item.xarticulo,
                  art: item.xarticulo,
                  art_id: item.xarticulo_id,
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
      $("#txt-product").data("id", spd.art_id);
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

function checkPermisoAlmacen() {
  if ($.inArray(parseInt(ped_estado), [12, 17, 6, 7, 23, 24]) >= 0) {
    $("#f-almacen").attr("disabled", true);
  } else {
    $("#f-almacen").attr("disabled", false);
  }
}

/**
 * Carga los datos de la dirección de envío del cliente.
 * @param {number} cliente_id - El ID del cliente.
 */
const cargarDatosDireccionEnvio = (cliente_id) => {
  var cmd = {
    module: "clientes",
    method: "get_cliente",
    id: cliente_id,
  };
  if (cliente_id) {
    // Cargar información del cliente
    $.ajax({
      url: "api-app.php", // Ruta al archivo PHP que maneja la solicitud
      type: "GET", // Método de solicitud
      data: cmd,
      dataType: "json",
    })
      .done(function (data) {
        // Verificar si se recibieron datos
        if (!data || data.length === 0) {
          const msg = `No se encontraron datos del envio del cliente: ${$("#f-cliente").val()}`;
          $.niftyNoty({
            type: "warning",
            title: "Datos de envio",
            message: msg,
            container: "floating",
            timer: 3000,
          });
          return;
        }
        // Mapeo de campos del cliente de la dirección de envío
        const campoMapeo = {
          "f-name": "xname",
          "f-name2": "xname2",
          "f-apellido1": "xapellido1",
          "f-apellido2": "xapellido2",
          "f-dir1": "xdir1",
          "f-entre_calle1": "xentre_calle1",
          "f-entre_calle2": "xentre_calle2",
          "f-numero": "xnumero",
          "f-apartamento": "xapartamento",
          "f-piso": "xpiso",
          "f-provincia": "xprovincia",
          "f-city": "xcity",
          "f-zipcode": "xzipcode",
          "f-reparto": "xreparto",
          "f-phone": "xphone",
          "f-ci": "xci",
        };

        const cliente = data[0];
        // Asignar valores a los campos del formulario de envío
        for (const [inputId, jsonKey] of Object.entries(campoMapeo)) {
          const valor = cliente[jsonKey] ?? "";
          $(`#${inputId}`).val(valor);
        }
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        // Manejar errores
        console.log(
          "Error al cargar datos del cliente:",
          textStatus,
          errorThrown
        );
      });
  }
};

/**
 * Limpia los campos de envío del cliente.
 */
const limpiarCamposEnvio = () => {
  //Limpio los campos de envío
  $("#envio").find("input, select").val("");
};
