function _table_jump_to_page(table, oSettings) {

    var paginationData = table.DataTable().page.info();
    var previousDtPageJump = $("body").find('#dt-page-jump-' + oSettings.sTableId);

    if (previousDtPageJump.length) {
        previousDtPageJump.remove();
    }

    if (paginationData.pages > 1) {

        var jumpToPageSelect = $("<select></select>", {
            "data-id": oSettings.sTableId,
            "class": "dt-page-jump-select form-control",
            'id': 'dt-page-jump-' + oSettings.sTableId
        });

        var paginationHtml = '';

        for (var i = 1; i <= paginationData.pages; i++) {
            var selectedCurrentPage = ((paginationData.page + 1) === i) ? 'selected' : '';
            paginationHtml += "<option value='" + i + "'" + selectedCurrentPage + ">" + i + "</option>";
        }

        if (paginationHtml != '') {
            jumpToPageSelect.append(paginationHtml);
        }

        $("#" + oSettings.sTableId + "_wrapper .dt-page-jump").append(jumpToPageSelect);
    }
}

function get_datatable_buttons_new(e) {
    var n = {
            body: function (e, t, a, n) {
                var i = $("<div></div>", e);
                if (
                    (i.append(e),
                    i.find("[data-note-edit-textarea]").length > 0 &&
                    (i.find("[data-note-edit-textarea]").remove(),
                        (e = i.html().trim())),
                    i.find(".row-options").length > 0 &&
                    (i.find(".row-options").remove(),
                        (e = i.html().trim())),
                    i.find(".table-export-exclude").length > 0 &&
                    (i.find(".table-export-exclude").remove(),
                        (e = i.html().trim())),
                        e)
                ) {
                    var o = new RegExp(
                        "([0-9]{1,3})(,)([0-9]{" +
                        app.options.decimal_places +
                        "," +
                        app.options.decimal_places +
                        "})",
                        "gm"
                    );
                    e.matchAll(o) && (e = e.replace(o, "$1.$3"));
                }
                var r = document.createElement("div");
                return (
                    (r.innerHTML = e),
                        (r.textContent || r.innerText || "").trim()
                );
            },
        },
        o = [];

    var r = $("body").find(".table-btn");
    return (
        $.each(r, function () {
            var t = $(this);
            t.length &&
            t.attr("data-table") &&
            $(e).is(t.attr("data-table")) &&
            o.push({
                text: t.text().trim(),
                className: "btn btn-default-dt-options",
                action: function (e, a, n, i) {
                    t.click();
                },
            });
        }),
        $(e).hasClass("dt-inline") ||
        o.push({
            text: '<i class="fa fa-refresh"></i>',
            className: "btn btn-default-dt-options btn-dt-reload",
            action: function (e, t, a, n) {
                t.ajax.reload();
            },
        }),
            o
    );
}

function InitDataTable(selector, url, initParams, notsearchable = [0], notsortable = [0], fnserverparams = [], defaultorder = [0], fixedColumns = {leftColumns: 0, rightColumns: 0}) {
    // var table = typeof (selector) == 'string' ? $("body").find('table' + selector) : selector;
    table = $(selector);
    if (table.length === 0) {
        return false;
    }


    fnserverparams = (fnserverparams == 'undefined' || typeof (fnserverparams) == 'undefined') ? [] : fnserverparams;

    // If not order is passed order by the first column
    if (typeof (defaultorder) == 'undefined') {
        defaultorder = [
            [0, 'asc']
        ];
    } else {
        if (defaultorder.length === 1) {
            defaultorder = [defaultorder];
        }
    }

    var user_table_default_order = table.attr('data-default-order');
    if ((user_table_default_order) != undefined) {
        var tmp_new_default_order = JSON.parse(user_table_default_order);
        var new_defaultorder = [];
        for (var i in tmp_new_default_order) {
            // If the order index do not exists will throw errors
            if (table.find('thead th:eq(' + tmp_new_default_order[i][0] + ')').length > 0) {
                new_defaultorder.push(tmp_new_default_order[i]);
            }
        }
        if (new_defaultorder.length > 0) {
            defaultorder = new_defaultorder;
        }
    }

    var length_options = [10, 25, 50, 100];
    var length_options_names = [10, 25, 50, 100];

    options.tables_pagination_limit = parseFloat(options.tables_pagination_limit);

    if ($.inArray(options.tables_pagination_limit, length_options) == -1) {
        length_options.push(options.tables_pagination_limit);
        length_options_names.push(options.tables_pagination_limit);
    }

    length_options.sort(function (a, b) {
        return a - b;
    });
    length_options_names.sort(function (a, b) {
        return a - b;
    });

    length_options.push(-1);
    length_options_names.push(lang.dt_length_menu_all);
    var width_document = $(document).width();
    if (Number(width_document) <= 768) {
        fixedColumns.leftColumns = 0;
        fixedColumns.rightColumns = 0;
    }
    var dtSettings = {
        "language": lang.datatables,
        "processing": true,
        "retrieve": true,
        "serverSide": true,
        'paginate': true,
        'searchDelay': 750,
        "bDeferRender": true,
        "autoWidth": false,
        dom: "<'row'><'row'<'col-md-7'lB><'col-md-5'f>>rt<'row pull-left'<'col-md-4'i>><'row pull-right'<'#colvis'><'.dt-page-jump'>p>",
        "pageLength": options.tables_pagination_limit,
        "lengthMenu": [length_options, length_options_names],
        "columnDefs": [{
            "searchable": false,
            "targets": notsearchable,
        }, {
            "sortable": false,
            "targets": notsortable
        }],
        "fnDrawCallback": function (oSettings) {
            // _table_jump_to_page(this, oSettings);
            if (oSettings.aoData.length === 0) {
                $(oSettings.nTableWrapper).addClass('app_dt_empty');
            } else {
                $(oSettings.nTableWrapper).removeClass('app_dt_empty');
            }
        },
        "fnCreatedRow": function (nRow, aData, iDataIndex) {
            // If tooltips found
            $(nRow).attr('data-title', aData.Data_Title);
            $(nRow).attr('data-toggle', aData.Data_Toggle);
        },
        "initComplete": function (settings, json) {
            var t = this;
            t.parents('.table-loading').removeClass('table-loading');
            t.removeClass('dt-table-loading');
        },
        "order": defaultorder,
        "ajax": {
            "url": url,
            "type": "POST",
            "data": function (d) {
                for (var key in fnserverparams) {
                    d[key] = $(fnserverparams[key]).val();
                }
                if (table.attr('data-last-order-identifier')) {
                    d['last_order_identifier'] = table.attr('data-last-order-identifier');
                }
            }
        },
        buttons: get_datatable_buttons_new(table),
    };

    if (table.hasClass('scroll-responsive') || options.scroll_responsive_tables == 1) {
        dtSettings.responsive = false;
    }

    if (initParams) {
        if (typeof initParams.order !== 'undefined') {
            dtSettings.order = initParams.order;
        }
        if (typeof initParams.ajax !== 'undefined') {
            dtSettings.ajax = initParams.ajax;
        }

        if (typeof initParams.sAjaxSource !== 'undefined') {
            dtSettings.sAjaxSource = initParams.sAjaxSource;
        }

        if (typeof initParams.fnServerData !== 'undefined') {
            dtSettings.fnServerData = initParams.fnServerData;
        }

        if (typeof initParams.columnDefs !== 'undefined') {
            dtSettings.columns = initParams.columnDefs;
        }

        if (typeof initParams.fixedHeader !== 'undefined') {
            dtSettings.fixedHeader = initParams.fixedHeader;
        }

        if (typeof initParams.responsive !== 'undefined') {
            dtSettings.responsive = initParams.responsive;
        }

        if (typeof initParams.searching !== 'undefined') {
            dtSettings.searching = initParams.searching;
        }

        if (typeof initParams.ordering !== 'undefined') {
            dtSettings.ordering = initParams.ordering;
        }

        if (typeof initParams.fixedColumns !== 'undefined') {
            dtSettings.fixedColumns = initParams.fixedColumns;
        }

        if (typeof initParams.scrollY !== 'undefined') {
            dtSettings.scrollY = initParams.scrollY;
        }

        if (typeof initParams.scrollX !== 'undefined') {
            dtSettings.scrollX = initParams.scrollX;
        }

        if (typeof initParams.createdRow !== 'undefined') {
            dtSettings.fnCreatedRow = initParams.createdRow;
        }

        if (typeof initParams.dom !== 'undefined') {
            dtSettings.dom = initParams.dom;
        }

        if (typeof initParams.paging !== 'undefined') {
            dtSettings.paging = initParams.paging;
        }

        if (typeof initParams.rowGroup !== 'undefined') {
            dtSettings.rowGroup = initParams.rowGroup;
        }

        if (typeof initParams.info !== 'undefined') {
            dtSettings.info = initParams.info;
        }

        if (typeof initParams.fnRowCallback !== 'undefined') {
            dtSettings.fnRowCallback = initParams.fnRowCallback;
        }
        if (typeof initParams.footerCallback !== 'undefined') {
            dtSettings.footerCallback = initParams.footerCallback;
        }
    }
    table = table.dataTable(dtSettings);
    var tableApi = table.DataTable();

    var hiddenHeadings = table.find('th.not_visible');
    var hiddenIndexes = [];

    $.each(hiddenHeadings, function () {
        hiddenIndexes.push(this.cellIndex);
    });

    setTimeout(function () {
        for (var i in hiddenIndexes) {
            tableApi.columns(hiddenIndexes[i]).visible(false, false).columns.adjust();
        }
    }, 10);

    if (table.hasClass('customizable-table')) {

        var tableToggleAbleHeadings = table.find('th.toggleable');
        var invisible = $('#hidden-columns-' + table.attr('id'));
        try {
            invisible = JSON.parse(invisible.text());
        } catch (err) {
            invisible = [];
        }

        $.each(tableToggleAbleHeadings, function () {
            var cID = $(this).attr('id');
            if ($.inArray(cID, invisible) > -1) {
                tableApi.column('#' + cID).visible(false);
            }
        });
    }

    // Fix for hidden tables colspan not correct if the table is empty
    if (table.is(':hidden')) {
        table.find('.dataTables_empty').attr('colspan', table.find('thead th').length);
    }

    table.on('preXhr.dt', function (e, settings, data) {
        if (settings.jqXHR) settings.jqXHR.abort();
    });
    return tableApi;
}

function get_datatable_buttons(table) {
    var formatExport = {
        body: function(data, row, column, node) {

            // Fix for notes inline datatables
            // Causing issues because of the hidden textarea for edit and the content is duplicating
            // This logic may be extended in future for other similar fixes
            var newTmpRow = $('<div></div>', data);
            newTmpRow.append(data);

            if (newTmpRow.find('[data-note-edit-textarea]').length > 0) {
                newTmpRow.find('[data-note-edit-textarea]').remove();
                data = newTmpRow.html().trim();
            }

            if (newTmpRow.find('.row-options').length > 0) {
                newTmpRow.find('.row-options').remove();
                data = newTmpRow.html().trim();
            }

            if (newTmpRow.find('.table-export-exclude').length > 0) {
                newTmpRow.find('.table-export-exclude').remove();
                data = newTmpRow.html().trim();
            }

            if (data) {
                // 300,00 becomes 300.00 because excel does not support decimal as coma
                var regexFixExcelExport = new RegExp("([0-9]{1,3})(,)([0-9]{" + options.decimal_places + ',' + options.decimal_places + "})", "gm");
                var found = data.matchAll(regexFixExcelExport);
                if (found) {
                    data = data.replace(regexFixExcelExport, "$1.$3");
                }
            }

            // Datatables use the same implementation to strip the html.
            var div = document.createElement("div");
            div.innerHTML = data;
            var text = div.textContent || div.innerText || "";

            return text.trim();
        }
    };
    var table_buttons_options = [];

    if (typeof(table_export_button_is_hidden) != 'function' || !table_export_button_is_hidden()) {
        table_buttons_options.push({
            extend: 'collection',
            text: lang.dt_button_export,
            className: 'btn btn-default-dt-options',
            buttons: [{
                extend: 'excel',
                text: lang.dt_button_excel,
                footer: true,
                exportOptions: {
                    columns: [':not(.not-export)'],
                    rows: function(index) {
                        return _dt_maybe_export_only_selected_rows(index, table);
                    },
                    format: formatExport,
                },
            }, {
                extend: 'csvHtml5',
                text: lang.dt_button_csv,
                footer: true,
                exportOptions: {
                    columns: [':not(.not-export)'],
                    rows: function(index) {
                        return _dt_maybe_export_only_selected_rows(index, table);
                    },
                    format: formatExport,
                }
            }, {
                extend: 'pdfHtml5',
                text: lang.dt_button_pdf,
                footer: true,
                exportOptions: {
                    columns: [':not(.not-export)'],
                    rows: function(index) {
                        return _dt_maybe_export_only_selected_rows(index, table);
                    },
                    format: formatExport,
                },
                orientation: 'landscape',
                customize: function(doc) {
                    // Fix for column widths
                    var table_api = $(table).DataTable();
                    var columns = table_api.columns().visible();
                    var columns_total = columns.length;
                    var pdf_widths = [];
                    var total_visible_columns = 0;
                    for (i = 0; i < columns_total; i++) {
                        // Is only visible column
                        if (columns[i] == true) {
                            total_visible_columns++;
                        }
                    }
                    setTimeout(function() {
                        if (total_visible_columns <= 5) {
                            for (i = 0; i < total_visible_columns; i++) {
                                pdf_widths.push((735 / total_visible_columns));
                            }
                            doc.content[1].table.widths = pdf_widths;
                        }
                    }, 10);

                    //  doc.defaultStyle.font = 'test';
                    doc.styles.tableHeader.alignment = 'left';
                    doc.styles.tableHeader.margin = [5, 5, 5, 5];
                    doc.pageMargins = [12, 12, 12, 12];
                }
            }, {
                extend: 'print',
                text: lang.dt_button_print,
                footer: true,
                exportOptions: {
                    columns: [':not(.not-export)'],
                    rows: function(index) {
                        return _dt_maybe_export_only_selected_rows(index, table);
                    },
                    format: formatExport,
                }
            }],
        });
    }
    var tableButtons = $("body").find('.table-btn');

    $.each(tableButtons, function() {
        var b = $(this);
        if (b.length && b.attr('data-table')) {
            if ($(table).is(b.attr('data-table'))) {
                table_buttons_options.push({
                    text: b.text().trim(),
                    className: 'btn btn-default-dt-options',
                    action: function(e, dt, node, config) {
                        b.click();
                    }
                });
            }
        }
    });

    if (!$(table).hasClass('dt-inline')) {
        table_buttons_options.push({
            text: '<i class="fa fa-refresh"></i>',
            className: 'btn btn-default-dt-options btn-dt-reload',
            action: function(e, dt, node, config) {
                dt.ajax.reload();
            }
        });
    }

    return table_buttons_options;
}

function _dt_maybe_export_only_selected_rows(index, table) {
    table = $(table);
    index = index.toString();
    var bulkActionsCheckbox = table.find('thead th input[type="checkbox"]').eq(0);
    if (bulkActionsCheckbox && bulkActionsCheckbox.length > 0) {
        var rows = table.find('tbody tr');
        var anyChecked = false;
        $.each(rows, function() {
            if ($(this).find('td:first input[type="checkbox"]:checked').length) {
                anyChecked = true;
            }
        });

        if (anyChecked) {
            if (table.find('tbody tr:eq(' + (index) + ') td:first input[type="checkbox"]:checked').length > 0) {
                return index;
            } else {
                return null;
            }
        } else {
            return index;
        }
    }
    return index;
}

function dtDatatable(selector, initParams) {
    var e = selector;
    var n = "undefined";
    var s = [];
    var tables_pagination_limit = initParams.pageLength ? initParams.pageLength : 20;
    var o = "string" == typeof e ? $("body").find("table" + e) : e;
    // if (0 === o.length) return !1;
    (n = "undefined" == n || void 0 === n ? [] : n),
        void 0 === s ? (s = [
            [0, "asc"]
        ]) : 1 === s.length && (s = [s]);
    var l = o.attr("data-default-order");
    if (l != undefined) {
        var d = JSON.parse(l),
            r = [];
        for (var c in d)
            o.find("thead th:eq(" + d[c][0] + ")").length > 0 && r.push(d[c]);
        r.length > 0 && (s = r);
    }

    initParams.cache = false;
    initParams.pageLength = 15;

    var length_options = [10, 25, 50, 100];
    var length_options_names = [10, 25, 50, 100];

    tables_pagination_limit = parseFloat(
        tables_pagination_limit
    );

    if ($.inArray(tables_pagination_limit, length_options) == -1) {
        length_options.push(tables_pagination_limit);
        length_options_names.push(tables_pagination_limit);
    }

    length_options.sort(function (a, b) {
        return a - b;
    });
    length_options_names.sort(function (a, b) {
        return a - b;
    });

    length_options.push(-1);
    length_options_names.push('Tất cả');
    initParams.lengthMenu = [length_options, length_options_names];

    initParams.dom =
        "<'row'><'row'<'col-md-7'lB><'col-md-5'f>>rt<'row'<'col-md-4'i><'#colvis'><'.dt-page-jump'>p>";

    if (!initParams.buttons) {
        initParams.buttons = get_datatable_buttons_new(o);
    } else {
        initParams.buttons.unshift({
            text: "<span class='fa fa-refresh'></span>",
            action: function (e, dt, node, config) {
                dt.ajax.reload(null, false);
            }
        });
    }
    // initParams.buttons = get_tnh_datatable_buttons(o);

    oTableCustom = $(selector).DataTable(initParams);
    // reLoadDatatable();

    // setTimeout(function(){ oTableCustom.draw(); }, 1000);
    return oTableCustom;
}

function initDatepicker(){
    $('.datepicker').datepicker({
        timePicker: true,
        autoclose: true,
        timePickerIncrement: 30,
        format: "dd/mm/yyyy",
    });
    $('.datepicker_modal').datepicker({
        dateFormat: "dd/mm/yy",
    });
    $('.datetimepicker').datetimepicker({
        timePicker: true,
        autoclose: true,
        timePickerIncrement: 30,
        format:'d/m/Y H:i',
    });
}

initDatepicker();
var search_daterangepicker = (element) => {
    $(`input[name="${element}"]`).daterangepicker({
        buttonClasses: ['btn', 'btn-sm'],
        applyClass: 'btn-default',
        cancelClass: 'btn-white',
        autoUpdateInput: false,
        isInvalidDate: false,
    }, function(start, end, label) {});
    $(`input[name="${element}"]`).on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        $(`#${element}`).trigger("change");
    });
    $(`input[name="${element}"]`).on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $(`#${element}`).trigger("change");
    });
};

var search_daterangetimepicker = (element) => {
    $(`input[name="${element}"]`).daterangepicker({
        buttonClasses: ['btn', 'btn-sm'],
        applyClass: 'btn-default',
        cancelClass: 'btn-white',
        autoUpdateInput: false,
        isInvalidDate: false,
        timePicker: true,
    }, function(start, end, label) {});
    $(`input[name="${element}"]`).on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY h:mm A') + ' - ' + picker.endDate.format('DD/MM/YYYY h:mm A'));
        $(`#${element}`).trigger("change");
    });
    $(`input[name="${element}"]`).on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $(`#${element}`).trigger("change");
    });
};
$(document).on('show.bs.modal', '.modal', function () {
    $("body").addClass('modal-open');
});
$("body").on("hidden.bs.modal", '.modal', function(event) {
    $('.modal:visible').length && $(document.body).addClass('modal-open');
    $(this).data('bs.modal', null);
});
$(document).on('click', '.dt-modal', function (event) {
    event.preventDefault();
    link = this.href;
    type = this.type;
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // let tokenVoice = localStorage.getItem('tokenVoice');
    // tokenVoice = $.parseJSON(tokenVoice);
    // userId = tokenVoice != null ? tokenVoice.id : 0;
    userId = 0;
    $.ajax({
        url: link,
        type: 'GET',
        dataType: 'html',
        data: type == 1 ? {
            'userId': userId
        } : {},
    })
        .done(function (data) {
            $('#dtModal').html(data);
            addTitleRequired();
            initDatepicker();
            tinymce.init(editor_config);
        })
        .fail(function () {
            console.log("error");
        });
    $('#dtModal').modal({backdrop: 'static', keyboard: true});
});
$(document).on('click', '.dt-modal2', function (event) {
    event.preventDefault();
    link = this.href;
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: link,
        type: 'GET',
        dataType: 'html',
        data: {},
    })
        .done(function (data) {
            $('#dtModal2').html(data);
            addTitleRequired();
            initDatepicker();
        })
        .fail(function () {
            console.log("error");
        });
    $('#dtModal2').modal({backdrop: 'static', keyboard: true});
});

row_popover = '';
$(document).on('click', '.po', function() {
    row_popover = $(this).closest('div');
});

$(document).on('click', '.po-delete', function() {
    $(this).popover('show');
});
$(document).on('click', '.po-close', function() {
    $('.po-delete').popover('hide');
    return false;
});
$(document).on('click', '.po-close', function(e) {
    $('.popover').popover('hide');
});


$(document).on('click', '.dt-delete', function(e) {
    var row = $(this).closest('tr');
    e.preventDefault();
    $('.po-delete').popover('hide');
    var link = $(this).attr('href');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: link,
        type: 'GET',
        dataType: 'JSON',
        data: {},
    })
        .done(function(data) {
            if (data)
            {
                if (data.result) {
                    if (typeof oTable != 'undefined') {
                        oTable.draw('page');
                    }
                    alert_float('success',data.message);
                } else {
                    alert_float('error',data.message);
                }
            }
        })
        .fail(function() {
        })
    return false;
});

$(document).on('change', '.dt-active', function(e) {
    e.preventDefault();
    var link = $(this).attr('data-href');
    var status = $(this).attr('data-status');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: link,
        type: 'GET',
        dataType: 'JSON',
        data: {
            status:status
        },
    })
        .done(function(data) {
            if (data)
            {
                if (data.result) {
                    alert_float('success',data.message);
                } else {
                    alert_float('error',data.message);
                }
                if (typeof oTable != 'undefined') {
                    oTable.draw('page');
                }
                if (typeof loadDataSetup != 'undefined'){
                    loadDataSetup();
                }
            }
        })
        .fail(function() {
        })
    return false;
});

$(document).on('click', '.dt-update', function(e) {
    var row = $(this).closest('tr');
    e.preventDefault();
    var link = $(this).attr('href');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: link,
        type: 'GET',
        dataType: 'JSON',
        data: {},
    })
        .done(function(data) {
            if (data)
            {
                if (data.result) {
                    if (typeof oTable != 'undefined') {
                        oTable.draw('page');
                    }
                    alert_float('success',data.message);
                } else {
                    alert_float('error',data.message);
                }
            }
        })
        .fail(function() {
        })
    return false;
});

function alert_float(type = 'success',message = ''){
    $("#toast-container-new").show();
    var html = `<div class="toast toast-${type}" style="">
        <div class="toast-message">${message}</div>
    </div>`;
    $("#toast-container-new").html(html);
    setTimeout(function () {
        $("#toast-container-new").hide();
    }, 4000);
    // $.Notification.autoHideNotify(type, 'top right', message)
}


$("#toast-container-new").click(function (){
    $(this).hide();
})

function searchAjaxSelect2(element,url = '',id = 0,paramsCus = {},allowClear = true){
    $(element).select2({
        allowClear:allowClear,
        dropdownParent: $(element).parent(),
        ajax: {
            url: url + '/' + id,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    paramsCus:paramsCus,
                    term: params.term,
                };
            },
            processResults: function (data, params) {
                return {
                    results: data.items,
                };
            },
            cache: true
        },
        placeholder: 'Chọn ...',
        templateResult: formatRepo,
        templateSelection: formatRepoSelection,
    });
}

function formatRepo (repo) {
    return repo.text;
}
function formatRepoSelection (repo) {
    return repo.full_name || repo.text;
}

function mainWrapperHeightFix() {
    // Get and set current height
    var headerH = $("#topnav").height();
    $(".table-responsive").css("min-height", $(window).height() - 200 - headerH + 'px');
}

$(document).ajaxStart(function() {
    $('#loading').addClass('loading');
    $(".loading_img").removeClass('hide');
    // $(".loading_img").attr('src','admin/assets/images/loading.gif');
    // $('#loading-content').addClass('loading-content');
});

$(document).ajaxStop(function() {
    $('#loading').removeClass('loading');
    $(".loading_img").addClass('hide');
    // $(".loading_img").removeAttr('src');
    // $('#loading-content').removeClass('loading-content');
    loadInitFileStyle();
    initDatepicker();
});
$(".delete_image").click(function () {
    $(this).closest("div.show_image").remove();
});
function initRangeSlider(ele,min,max,value = 0,step = 0){
    $(`${ele}`).ionRangeSlider({
        min: min,
        max: max,
        from: value,
        step: step,
    })
}
function formatMoney(x, d = 0) {
    if(!d) { d = options.decimals_money; }
    x = x * 1;
    if (x % 1 == 0) {
        d = 0;
    }
    return accounting.formatNumber(x, d, options.thousands_sep == 0 ? ' ' : options.thousands_sep, options.decimals_sep);
}
function formatNumber(x, d = null) {
    // if(!d) { d = site.decimals_number; }
    if(d == null) { d = options.decimals_number; }
    x = x * 1;
    if (x % 1 == 0) {
        d = 0;
    }
    return accounting.formatNumber(x, d, options.thousands_sep == 0 ? ' ' : options.thousands_sep, options.decimals_sep);
}
// hàm format number
function formatNumberOld(nStr, decSeperate = ".", groupSeperate = ",") {
    // if ($.isNumeric(nStr)) {
    //     nStr = Math.round(nStr * 1000) / 1000;
    // }
    // nStr += '';
    // x = nStr.split(decSeperate);
    // x1 = x[0];
    // x2 = x.length > 1 ? x[1] : '';
    // if (x2.length > 0) {
    //     tosum = 0;
    //     if (x2.length > 2 && parseFloat(x2.substring(2, 3)) > 5) {
    //         tosum = tosum + 1;
    //     }
    //     tam = (x2[0] == 0 ? '0' : '');
    //     tam += (x2[1] == 0 ? '0' : '');
    //     x2 = tam + (parseFloat(x2.substring(0, 3)) + parseFloat(tosum));
    //     if (parseFloat(x2) > 0) {
    //         x2 = '.' + x2;
    //     } else {
    //         x2 = '';
    //     }
    // }
    // var rgx = /(\d+)(\d{3})/;
    // while (rgx.test(x1)) {
    //     x1 = x1.replace(rgx, '$1' + groupSeperate + '$2');
    // }
    //
    // return x1 + x2;
    nStr += '';
    nStr = nStr.toString(nStr);
    x = nStr.split(decSeperate);
    x1 = x[0];
    x2 = x.length > 1 ? (decSeperate + x[1]) : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + groupSeperate + '$2');
    }
    // console.log(x1);
    x1 = x1.split('.');
    if(x1[1]) {
        x1[1] = x1[1].replace(/[\$,]/g, '');
        x1 = x1[0] + '.' + x1[1];
    }
    else {
        x1 = x1[0];
    }
    return x1 + x2;

}
// hàm formatnumber key Change gắn zô sự kiện input
function formatNumBerKeyChange(id_input) {
    key = "";
    money = $(id_input).val().replace(/[^\-\d\.]/g, '');
    a = money.split(" ");
    $.each(a, function (index, value) {
        key = key + value;
    });
    $(id_input).val(formatNumber(key, null, ','));
}


var intVal = function (i) {
    return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
};

function addTitleRequired() {
    var input_required = $('input[required],select[required]');
    $.each(input_required, function(index, value) {
        var idInput = $(value).attr('id');
        if(idInput && $(`label[for="${idInput}"]`).find('i').length == 0) {
            $(`label[for="${$(value).attr('id')}"]`).append(` <i class="text-danger required">*</i>`);
        }
    })
}
$(document).ready(function() {
    addTitleRequired();
})

$(document).on('click', '.slick-slide', function() {
    $(this).parents().find('.slick-slide').removeClass('active-menu');
    $(this).addClass('active-menu');
})
$(document).on('click.bs.dropdown.data-api', '.dropdown.keep-inside-clicks-open', function (e) {
    e.stopPropagation();
});

var $ddlBtn = $(".clickNoti");
$ddlBtn.on("click", function(){
    var expanded = /true/i.test($ddlBtn.attr("aria-expanded"));
    $ddlBtn
        .attr("aria-expanded", !expanded)
        .siblings(".dropdown-menu").toggleClass("show")
        .parent().toggleClass("show");
    $ddlBtn
        .attr("aria-expanded", !expanded)
        .parent().toggleClass("open");
});

$(".has-submenu").click(function (){
    if ($(window).width() <= 768) {
        if ($(this).find('ul.submenu')) {
            $(this).find('ul.submenu').addClass('show');
            $(this).find('ul.submenu').css({
                "padding-left": "120px"
            });
        }
    }
})
