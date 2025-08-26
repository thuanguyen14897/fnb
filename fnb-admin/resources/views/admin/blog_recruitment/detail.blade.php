@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('dt_blog_recruitment')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/blog_recruitment/list">{{lang('dt_blog_recruitment')}}</a></li>
                <li class="active">{{!empty($blog) ? lang('dt_edit') : lang('dt_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form action="admin/blog_recruitment/submit/{{$id}}" method="post" id="blogRecruitmenrForm" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="card-box">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{lang('dt_title')}}</label>
                                    <input type="text" name="title" autocomplete="off"
                                           value="{{!empty($blog) ? $blog->title : ''}}" class="form-control title">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="salary">{{lang('dt_salary')}}</label>
                                            <input type="text" name="salary" autocomplete="off"
                                                   value="{{!empty($blog) ? $blog->salary : ''}}" class="form-control salary">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="experience">{{lang('dt_experience')}}</label>
                                            <input type="text" name="experience" autocomplete="off"
                                                   value="{{!empty($blog) ? $blog->experience : ''}}" class="form-control experience">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="working_form">{{lang('dt_working_form')}}</label>
                                            <input type="text" name="working_form" autocomplete="off"
                                                   value="{{!empty($blog) ? $blog->working_form : ''}}" class="form-control working_form">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="degree">{{lang('dt_degree')}}</label>
                                            <input type="text" name="degree" autocomplete="off"
                                                   value="{{!empty($blog) ? $blog->degree : ''}}" class="form-control degree">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gender">{{lang('dt_gender')}}</label>
                                            <input type="text" name="gender" autocomplete="off"
                                                   value="{{!empty($blog) ? $blog->gender : ''}}" class="form-control gender">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quantity">{{lang('dt_quantity')}}</label>
                                            <input type="text" name="quantity" autocomplete="off"
                                                   value="{{!empty($blog) ? $blog->quantity : ''}}" class="form-control quantity">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="address_recruitment">{{lang('dt_address_recruitment')}}</label>
                                    <input type="text" name="address_recruitment" autocomplete="off"
                                           value="{{!empty($blog) ? $blog->address : ''}}" class="form-control address_recruitment">
                                </div>
                                <div class="form-group">
                                    <label for="name">{{lang('dt_descption_blog_recruitment')}}</label>
                                    <textarea type="text" name="descption" autocomplete="off"
                                              cols="2" rows="3" class="form-control descption editor">{{!empty($blog) ? $blog->descption : ''}}
                                    </textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="content">{{lang('dt_content_blog_recruitment')}}</label>
                                    <textarea type="text" name="content" autocomplete="off"
                                              cols="2" rows="5" class="form-control content editor">{{!empty($blog) ? $blog->content : ''}}
                                    </textarea>
                                </div>
                                <div class="form-group">
                                    <label for="job_requirement">{{lang('dt_job_requirement')}}</label>
                                    <textarea type="text" name="job_requirement" autocomplete="off"
                                              cols="2" rows="5" class="form-control job_requirement editor">{{!empty($blog) ? $blog->job_requirement : ''}}
                                    </textarea>
                                </div>
                                <div class="form-group">
                                    <label for="your_benefit">{{lang('dt_your_benefit')}}</label>
                                    <textarea type="text" name="your_benefit" autocomplete="off"
                                              cols="2" rows="5" class="form-control your_benefit editor">{{!empty($blog) ? $blog->your_benefit : ''}}
                                    </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right m-b-0">
                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                            {{lang('dt_save')}}
                        </button>
                        <button type="reset" class="btn btn-default waves-effect waves-light m-l-5">
                            {{lang('dt_cancel')}}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script>
        $("#blogRecruitmenrForm").validate({
            rules: {
                title: {
                    required: true,
                },
                salary: {
                    required: true,
                },
                experience: {
                    required: true,
                },
                working_form: {
                    required: true,
                },
                degree: {
                    required: true,
                },
                quantity: {
                    required: true,
                },
                address_recruitment: {
                    required: true,
                },
            },
            messages: {
                title: {
                    required: "{{lang('dt_required')}}",
                },
                salary: {
                    required: "{{lang('dt_required')}}",
                },
                experience: {
                    required: "{{lang('dt_required')}}",
                },
                working_form: {
                    required: "{{lang('dt_required')}}",
                },
                degree: {
                    required: "{{lang('dt_required')}}",
                },
                quantity: {
                    required: "{{lang('dt_required')}}",
                },
                address_recruitment: {
                    required: "{{lang('dt_required')}}",
                },

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
                            window.location.href='admin/blog_recruitment/list';
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
