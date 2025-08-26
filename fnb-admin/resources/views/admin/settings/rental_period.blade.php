<div class="title-statistic title_left">
    <span class="mleft5 mright5">
        Thiết lập thời gian thuê lễ, tết
    </span>
</div>
<div class="col-md-12">
    <label>Thiết lập thời gian thuê lễ, tết</label>
    <table class="table-bordered table" id="table_rental_period">
        <thead>
        <tr>
            <th style="width: 50px;text-align: center">
                <button type="button" class="btn btn-xs btn-info btn-icon" onclick="addRow()"><i class="fa fa-plus"
                                                                                                 aria-hidden="true"></i>
                </button>
            </th>
            <th class="text-center" style="width: 200px">Tên</th>
            <th class="text-center" style="width: 300px">Thời gian</th>
            <th class="text-center" style="width: 150px">Số ngày thuê tối thiểu</th>
            <th class="text-center" style="width: 80px"></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
@section('script')
    <script>
        var counter = 0;

        var dtHolidayList = <?= !empty($dtHolidayList) ? json_encode($dtHolidayList) : [] ?>;

        function getOptionHoliday() {
            var option = `<option></option>`;
            if (dtHolidayList.length > 0) {
                $.each(dtHolidayList, function (k, v) {
                    option += `<option value="${v.id}">${v.name}</option>`;
                });
            }
            return option;
        }

        function addRow() {
            var tr = $('<tr></tr>');
            var td_delete = $('<td class="text-center"></td>');
            var td_stt = $('<td></td>');
            var td_name = $(`<td></td>`);
            var td_hour = $('<td></td>');
            var td_number_day = $('<td></td>');
            td_delete.append('<div class="text-center"><i class="fa fa-remove btn btn-danger remove-row"></i></div>');
            td_stt.append('<div class="text-center stt"></div><input type="hidden" name="counter_holiday[]" value="' + counter + '">');
            td_hour.append('<input type="text" name="hour[' + counter + ']" value="" required style="width: 100%;" class="date_search form-control">');
            td_name.append(`<select class="holiday_list_id select2 form-control" required name="holiday_list_id[${counter}]">
                ${getOptionHoliday()}
            </select>`);
            td_number_day.append('<input type="text" name="number_day[' + counter + ']" onchange="formatNumBerKeyChange(this)" value="2" style="width: 100%;" class="number_day form-control">');


            tr.append(td_stt);
            tr.append(td_name);
            tr.append(td_hour);
            tr.append(td_number_day);
            tr.append(td_delete);
            $('#table_rental_period tbody').append(tr);
            search_daterangetimepicker('date_search');
            $(".holiday_list_id").select2();
            counter++;
            totalSetup();
        }

        var search_daterangetimepicker = (element) => {
            $(`.${element}`).daterangepicker({
                buttonClasses: ['btn', 'btn-sm'],
                applyClass: 'btn-default',
                cancelClass: 'btn-white',
                autoUpdateInput: false,
                isInvalidDate: false,
                timePicker: false,
            }, function (start, end, label) {
            });
            $(`.${element}`).on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                $(`.${element}`).trigger("change");
            });
            $(`.${element}`).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                $(`.${element}`).trigger("change");
            });
        }

        $(document).on('click', '.remove-row', function (event) {
            event.preventDefault();
            tr = $(this).closest('tr');
            tr.remove();
            totalSetup();
        })

        function totalSetup() {
            tb = '#table_rental_period tbody tr';
            var n = $(tb).length;
            var stt = 0;
            for (ii = 0; ii < n; ii++) {
                stt++;
                element = $(tb)[ii];
                $(element).find('.stt').html(stt);
            }
        }

        let loadDataSetupHoliday = function () {
            $.ajax({
                url: 'admin/settings/loadDataSetupHoliday',
                type: 'POST',
                dataType: 'json',
                cache: false,
            })
                .done(function (data) {
                    $("#table_rental_period").find('tbody').html(data.html);
                    $(".holiday_list_id").select2();
                    search_daterangetimepicker('date_search');
                    counter = data.counter;
                })
                .fail(function () {

                });
            return false;
        }
        loadDataSetupHoliday();
    </script>
@endsection
