@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('c_title_ares')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/ares/list">{{lang('c_title_ares')}}</a></li>
                <li class="active">{{$title}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form action="admin/ares/updateSetup" method="post" id="formClient" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <input type="hidden" name="id" value="{{$id ?? 0}}">
                <div class="tab-pane active" id="profile1">
                    <div class="card-box">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Thành phố/Tỉnh</th>
                                            <th>Xã phường</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="item">
                                            <td>
                                                <div class="form-group">
                                                    <label for="province_id-0">{{lang('dt_province')}}</label>
                                                    <select class="province_id-0 select2" id="province_id-0"
                                                            data-placeholder="Chọn ..." name="province_id[0]"
                                                            onchange="changeProvince(this)">
                                                        <option></option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label for="ward_id-0">{{lang('dt_ward')}}</label>
                                                    <select class="ward_id-0 select2" id="ward_id-0"
                                                            data-placeholder="Chọn ..." name="ward_id[0]"
                                                            onchange="changeProvince(this)">
                                                        <option></option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group text-right m-b-0">
                    <button class="btn btn-primary waves-effect waves-light" type="submit">
                        {{lang('dt_save')}}
                    </button>
                </div>
            </div>
        </form>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script>
        $(document).ready(function(){
            searchAjaxSelect2('.province_id-0','api/category/getListProvince',0,{
                'select2':true
            })
        })


        function changeProvince() {

        }


        $(document).ready(function(){
            searchAjaxSelect2('.province_id-0','api/category/getListProvince',0,{
                'select2':true
            })
            //searchAjaxSelect2('#wards_id','api/category/getListWard',0,{
                //'select2':true,
               // province_id: {{!empty($dtData['province_id']) ? $dtData['province_id'] : -1}},
           // })
            $(".form-group a").click(function(){
                var $this=$(this);
                if(!$this.hasClass('active')){
                    $this.parents(".form-group").find('input').attr('type','text')
                    $this.addClass('active');
                }else{
                    $this.parents(".form-group").find('input').attr('type','password')
                    $this.removeClass('active')
                }
            });

            $("#formClient").submit(function(e){
                if($('.form-group.error-or').length > 0) {
                    e.preventDefault();
                }
            });
        });

        $(".delete_image").click(function () {
            $(".show_image").addClass('hide');
            $(".image_old").val('');
        });

        $("#formClient").validate({
            rules: {
                phone: {
                    required: true,
                },
                fullname: {
                    required: true,
                }
            },
            messages: {
                phone: {
                    required: "{{lang('dt_required')}}",
                },
                fullname: {
                    required: "{{lang('dt_required')}}",
                }
            },
            submitHandler: function (form) {
                var url = form.action;
                var form = $(form),
                    formData = new FormData(),
                    formParams = form.serializeArray();

                $.each(form.find('input[type="file"]'), function (i, tag) {
                    $.each($(tag)[0].files, function (i, file) {
                        formData.append(tag.name, file);
                    });
                });
                $.each(formParams, function (i, val) {
                    formData.append(val.name, val.value);
                });

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                })
                    .done(function (data) {
                        if (data.result) {
                            alert_float('success',data.message);
                            window.location.href='admin/clients/list';
                        } else {
                            alert_float('error',data.message);
                        }
                    })
                    .fail(function (err) {
                        htmlError = '';
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                        alert_float('error',htmlError);
                    });
                return false;
            }
        });
    </script>
@endsection
