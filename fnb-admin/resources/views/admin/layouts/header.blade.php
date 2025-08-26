<div class="topbar-main">
    <div class="container">
        <!-- Logo container-->
        <div class="logo" style="margin-top: 0px !important;">
            <a href="admin/dashboard" class="logo" style="margin-top: 0px !important;"><img src="{{get_option('logo')}}" style="width: 50px"></a>
        </div>
        <!-- End Logo container-->
        <div class="menu-extras">
            <ul class="nav navbar-nav navbar-right pull-right">
                <li class="dropdown navbar-c-items keep-inside-clicks-open hide">
                    <a href="javascript:void(0);" data-target="#" class="dropdown-toggle waves-effect waves-light"
                       data-toggle="dropdown" aria-expanded="true">
                        <span><img src="admin/assets/images/icon-call.png" style="width: 30px" alt=""></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li style="cursor: pointer"><a id="enable" onclick="csEnableCall()"><i class="ti-power-off text-danger m-r-10"></i> Kích hoạt thoại</a></li>
                        <li style="cursor: pointer"><a id="enable" onclick="changeCallStatus()"><i class="ti-power-off text-danger m-r-10"></i> On/Off trạng thái</a></li>
                        <li style="cursor: pointer">
                            <a>
                                <div style="margin: 10px 0">
                                    <select class="select2" id="select-call-out-id" value="">
                                    </select>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown navbar-c-items keep-inside-clicks-open">
                    <input type="hidden" name="next_noti" class="next_noti" value="">
                    <a href="javascript:void(0);" data-target="#" class="dropdown-toggle waves-effect waves-light clickNoti"
                       data-toggle="dropdown" aria-expanded="true">
                        <i class="icon-bell"></i> <span data-count="{{countNotiNotRead()}}" class="badge badge-xs badge-danger">{{countNotiNotRead()}}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg">
                        <li class="notifi-title" style="display: flex;flex-flow: row-reverse;align-items: center;justify-content: space-between;" ><span style="cursor: pointer" onclick="readAllNoti(this)"><img src="admin/assets/images/noti-check.svg"></span>Thông báo</li>
                        <li class="list-group slimscroll-noti notification-list">
                            <div class=div-data-noti>

                            </div>
                        </li>
                    </ul>
                </li>

                @if(Auth::guard('admin')->check())
                    <li class="dropdown navbar-c-items">
                        <a href="" class="dropdown-toggle waves-effect waves-light profile" data-toggle="dropdown"
                           aria-expanded="true"><img
                                src="{{!empty(Auth::guard('admin')->user()->image) ? asset('storage/'.Auth::guard('admin')->user()->image) : 'admin/assets/images/users/avatar-1.jpg'}}"
                                alt="user-img"
                                class="img-circle"> </a>
                        <ul class="dropdown-menu">
                            <li><a href="javascript:void(0)"><i
                                        class="ti-user text-custom m-r-10"></i> {{Auth::guard('admin')->user()->name}}
                                </a></li>
                            <li id="profile" data-id=""><a href="javascript:void(0)"><i
                                        class="ti-user text-custom m-r-10"></i> Profile</a></li>
                            <li class="divider"></li>
                            <li><a href="admin/logout"><i class="ti-power-off text-danger m-r-10"></i> Logout</a></li>
                        </ul>
                    </li>
                @endif
            </ul>
            <div class="menu-item">
                <!-- Mobile menu toggle-->
                <a class="navbar-toggle">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <!-- End mobile menu toggle-->
            </div>
        </div>

    </div>
</div>

<div class="navbar-custom">
    <div class="container">
        @include('admin.layouts.menu')
    </div> <!-- end container -->
</div> <!-- end navbar-custom -->
