@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('dt_user')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/user/list">{{lang('dt_user')}}</a></li>
                <li class="active">{{!empty($user) ? lang('dt_edit') : lang('dt_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form action="admin/user/submit/{{$id}}" method="post" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#profile1" data-toggle="tab" aria-expanded="false">
                            <span class="visible-xs"><i class="fa fa-home"></i></span>
                            <span class="hidden-xs">Thông tin</span>
                        </a>
                    </li>
                    <li>
                        <a href="#permission" data-toggle="tab" aria-expanded="true">
                            <span class="visible-xs"><i class="fa fa-user"></i></span>
                            <span class="hidden-xs">Quyền hạn</span>
                        </a>
                    </li>

                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="profile1">
                        <div class="card-box">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="image">Avatar</label>
                                                <input type="file" name="image" id="image" class="filestyle image"
                                                       data-buttonbefore="true">
                                                @if(!empty($user) && $user->image != null)
                                                    <input type="hidden" name="image_old" id="image_old"
                                                           class="image_old"
                                                           data-buttonbefore="true" value="{{!empty($user) ? $user->image : ''}}">
                                                    <div style="display: flex;justify-content:center;margin-top: 5px"
                                                         class="show_image">
                                                        <img src="{{asset('storage/'.$user->image)}}" alt="image"
                                                             class="img-responsive img-circle"
                                                             style="width: 150px;height: 150px">
                                                    </div>
                                                @else
                                                    <div style="display: flex;justify-content:center;margin-top: 5px"
                                                         class="show_image">
                                                        <img src="admin/assets/images/users/avatar-1.jpg" alt="image"
                                                             class="img-responsive img-circle"
                                                             style="width: 150px;height: 150px">

                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="department">{{lang('dt_department')}}</label>
                                                <select multiple class="department select2 select2-multiple" id="department" data-placeholder="Chọn ..." name="department[]">
                                                    @foreach($department as $department)
                                                        <option
                                                            @if(!empty($user))
                                                                @foreach($user->department as $user_department)
                                                                    @if($user_department->id == $department->id)
                                                                        {{'selected'}}
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                            value="{{$department->id}}">{{$department->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <div>
                                                    <label for="admin">{{lang('dt_admin')}}</label>
                                                    <br>
                                                    <input type="checkbox" name="admin" {{!empty($user) ? ($user->admin == 1 ? 'checked' : '') : ''}} class="admin"
                                                           data-plugin="switchery" data-color="#5fbeaa"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="code">{{lang('dt_code_user')}}</label>
                                                <input type="text" name="code" parsley-trigger="change" required autocomplete="off"
                                                       value="{{!empty($user) ? $user->code : ''}}" class="form-control code">
                                            </div>
                                            <div class="form-group">
                                                <label for="name">{{lang('dt_name_user')}}</label>
                                                <input type="text" name="name" parsley-trigger="change" required autocomplete="off"
                                                       value="{{!empty($user) ? $user->name : ''}}" class="form-control name">
                                            </div>
                                            <div class="form-group">
                                                <label for="email">{{lang('dt_email_user')}}</label>
                                                <input type="email" name="email" parsley-trigger="change" required autocomplete="off"
                                                       value="{{!empty($user) ? $user->email : ''}}" class="form-control email">
                                            </div>
                                            <div class="form-group">
                                                <label for="phone">Số điện thoại</label>
                                                <input type="text" name="phone" parsley-trigger="change" autocomplete="off"
                                                       value="{{!empty($user) ? $user->phone : ''}}"
                                                       class="form-control phone">
                                            </div>
                                            <div class="form-group" style="position: relative;">
                                                <label for="password">{{lang('dt_password_user')}}</label>
                                                <input type="password" class="form-control password" id="password" name="password">
                                                <a style="position: absolute; top:54%;right: 25px" href="javascript:;void(0)" ><i class="fa fa-eye"></i></a>
                                            </div>
                                            <div class="form-group">
                                                <label for="list_ares">{{lang('c_ares')}}</label>
                                                <select multiple class="list_ares select2 select2-multiple" id="list_ares" data-placeholder="Chọn ..." name="list_ares[]">
                                                    @foreach($ares as $detailAres)
                                                        <option value="{{$detailAres->id ?? ''}}"
                                                                @php
                                                                    $selected = '';
                                                                        if(!empty($user->ares)) {
                                                                            foreach($user->ares as $k => $v) {
                                                                                if($v['id_ares'] == $detailAres->id) {
                                                                                    $selected = 'selected';
                                                                                    break;
                                                                                }
                                                                            }
                                                                        }
                                                                @endphp
                                                        {{$selected}}>{{$detailAres->name ?? ''}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="">{{lang('dt_active_user')}}</label>
                                                <div class="radio radio-custom radio-inline">
                                                    <input type="radio"
                                                           @if(!empty($user) && $user->active==1 )
                                                               checked
                                                           @else
                                                               checked
                                                           @endif
                                                           id="active1"
                                                           value="1" name="active">
                                                    <label for="active1">Hoạt Động</label>
                                                </div>
                                                <div class="radio radio-custom radio-inline">
                                                    <input type="radio"
                                                           @if(!empty($user) && $user->active==0)
                                                               checked
                                                           @endif
                                                           id="active2"
                                                           value="0" name="active">
                                                    <label for="active2"> Khoá </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="tab-pane " id="permission">
                        <div class="form-group">
                            <label for="role">{{lang('dt_role')}}</label>
                            <select multiple class="role select2 select2-multiple" id="role" data-placeholder="Chọn ..." name="role[]">
                                @foreach($role as $role)
                                    <option
                                        @if(!empty($user))
                                            @foreach($user->role as $user_role)
                                                @if($user_role->id == $role->id)
                                                    {{'selected'}}
                                                @endif
                                            @endforeach
                                        @endif
                                        value="{{$role->id}}">{{$role->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-top: 5px;margin-bottom: 5px">
                            <div class="row permission_role">

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

        $(document).ready(function(){
            $(".role").trigger('change');
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
        });

        $(".delete_image").click(function () {
            $(".show_image").addClass('hide');
            $(".image_old").val('');
        });


        $(".role").change(function () {
            var role = $(this).val();
            var user_id = "{{$id}}";
            $.ajax({
                url: 'admin/user/getPermissonByRole',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    role: role,
                    user_id: user_id,
                },
            })
                .done(function (data) {
                    var html = '';
                    var permission = data.permission != undefined ? data.permission : [];
                    if (data.roles != undefined) {
                        $.each(data.roles, function (k, v) {
                            var html_child = '';
                            if (v.permission.length > 0) {
                                $.each(v.permission, function (k_child, v_child) {
                                    html_child += `
                                     <div class="col-md-3">
                                     <div class="checkbox checkbox-pink">
                                        <input id="checkbox_${v_child.id}" ${permission.includes(v_child.id) == false ? '' : 'checked'} type="checkbox"
                                                                       name="permission[${v.id}][]"
                                                                       class="permission_${v.id}"
                                                                       value="${v_child.id}"
                                                                       data-parsley-multiple="groups"
                                                                       data-parsley-mincheck="2">
                                            <label
                                                for="checkbox_${v_child.id}"> ${v_child.name}</label>
                                        </div>
                                     </div>
                                    `;
                                });
                                html += `
                                <div class="col-md-6" style="margin-top: 5px">
                                    <div style="border: 1px solid #eee;padding: 10px">
                                        <h4 class="page-title">${v.name}</h4>
                                        <input type="hidden" name="group_permission[]" value="${v.id}">
                                        <div class="row">
                                            ${html_child}
                                             <div class="col-md-12 row">
                                                <div class="col-md-3 pull-left">
                                                    <a class="btn btn-default pull-left"
                                                       onclick="checkAll(this,${v.id})">
                                                       Chọn tất cả</a>
                                                </div>
                                                <div class="col-md-3 pull-left">
                                                    <a class="btn btn-danger pull-left"
                                                       onclick="cancelAll(this,${v.id})"> Huỷ chọn tất cả</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                            }
                        });
                    }
                    $(".permission_role").html(html);
                })
                .fail(function () {

                });
            return false;
        });

        function checkAll(_this, group_permission_id) {
            $(".permission_" + group_permission_id).prop('checked', true);
        }

        function cancelAll(_this, group_permission_id) {
            $(".permission_" + group_permission_id).prop('checked', false);
        }
    </script>
@endsection
