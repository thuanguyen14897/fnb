<table id="tb-synthetic-kpi" class="table table-bordered dataTable tb-synthetic-kpi-new" style="width: 100%;">
    <thead>
    <?= $tHead ?>
    </thead>
    <tbody>
    <?= $html ?>
    </tbody>
    <tfoot>
    </tfoot>
</table>
<script>
    var dt_weight_tagert_kpi = {!! json_encode($dt_weight_tagert_kpi) !!};
    var dt_rating_kpi = {!! json_encode($dt_rating_kpi) !!};
    $(document).ready(function () {
         $('#tb-synthetic-kpi').DataTable({
            "language": lang.datatables,
            "pageLength": options.tables_pagination_limit,
            fixedColumns: {
                leftColumns: 3,
                rightColumns: 0
            },
            scrollY: '430px',
            scrollX: true,
            'searching': false,
            'ordering': false,
            'paging': false,
            "info": false,
            "drawCallback": function (aoData, settings) {
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {

            },
            "initComplete": function (settings, json) {
                var t = this;
                t.parents('.table-loading').removeClass('table-loading');
                t.removeClass('dt-table-loading');
            },
            "footerCallback": function (nRow, aaData, start, end, display) {

            }
        });
    });
    $(document).on('change', '.point_payment, .point_service, .point_member, .point_violation', function(event) {
        totalKpi();
    });
    function totalKpi() {
        tb = '#tb-synthetic-kpi tbody tr.tr_total';
        var n = $(tb).length;
        count_error = 0;

        for (ii = 0; ii < n; ii++) {
            element = $(tb)[ii];

            point_payment = intVal($(element).find('.point_payment').val());
            point_service = intVal($(element).find('.point_service').val());
            point_member = intVal($(element).find('.point_member').val());
            point_violation = intVal($(element).find('.point_violation').val());

            point_kpi = 0;
            $.each(dt_weight_tagert_kpi, function(key, value) {
                if (value.type == 'payment') {
                    point_kpi += (point_payment * value.weight) / 100;
                } else if (value.type == 'service') {
                    point_kpi += (point_service * value.weight) / 100;
                } else if (value.type == 'member') {
                    point_kpi += (point_member * value.weight) / 100;
                } else if (value.type == 'violate') {
                    point_kpi += (point_violation * value.weight) / 100;
                }
            });
            $(element).find('.point_kpi').val(point_kpi);
            name_kpi = '';
            $.each(dt_rating_kpi, function(key, value) {
                if (point_kpi >= value.point_start_kpi && point_kpi < value.point_end_kpi) {
                    name_kpi = value.name;
                    return false;
                }
            });
            $(element).find('.result_name_kpi').html(name_kpi);

        }
    }
</script>
