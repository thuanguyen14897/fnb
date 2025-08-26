<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
    <meta name="author" content="Coderthemes">

    <link rel="shortcut icon" href="{{get_option('favicon')}}">

    <title>Login - Admin</title>
    <base href="{{ asset('') }}">
    <link href="admin/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/core.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/components.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/icons.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/pages.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/login.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/responsive.css" rel="stylesheet" type="text/css"/>

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script src="admin/assets/js/modernizr.min.js"></script>

</head>
<body>

<div id="particles-js">
    <div class="login-container">
        <div class="login-content">
            <div class="wrap-login">
                <form class="form-horizontal m-t-20" action="admin/login" method="post">
                      <span class="img-logo-foso">
                        <img src="admin/assets/images/logo_login.png" width="150"></span>
                    <div class="title_login">Đăng nhập</div>
                    <div class="sub_title_login">Đăng nhập vào trang quản trị FNB</div>
                    <?php $cookieLogin = !empty($_COOKIE['remember_login']) ? json_decode($_COOKIE['remember_login']) : NULL?>
                    {{ csrf_field() }}
                    @if(Session::has('message'))
                        <div class="alert alert-danger" style="font-size: 15px">{{ Session::get('message') }}</div>
                    @endif
                        <div class="input input-login">
                            <span class="forcus-input">Email</span>
                            <input class="input_login_v2 input-login-text" id="input_login_email" name="email" type="text" autocomplete="off"
                                   placeholder="Nhập email" value="{{!empty($cookieLogin) ? $cookieLogin->email : ''}}" required/>
                        </div>
                    @if($errors->has('email'))
                        <div class="alert alert-danger">{{ $errors->first('email') }}</div>
                    @endif

                        <div class="input input-login input-icons">
                            <span class="forcus-input">Password</span>
                            <input class="input-login-text" name="password" type="password" autocomplete="off" placeholder="Nhập password" value="{{!empty($cookieLogin) ? decrypt($cookieLogin->password) : ''}}" required/>
                            <i class="icon-show-password fa fa-eye-slash" onclick="showPassword('password'); return false;"></i>
                        </div>
                    @if($errors->has('password'))
                        <div class="alert alert-danger">{{ $errors->first('password') }}</div>
                    @endif

                    <div class="form-group ">
                        <div class="col-xs-12">
                            <div class="checkbox checkbox-primary">
                                <input id="checkbox-signup" type="checkbox" name="remember" value="1" {{!empty($cookieLogin) ? 'checked' : ''}}>
                                <label for="checkbox-signup" style="color: #585F71;font-size: 14px;font-weight: 400">
                                    Ghi nhớ đăng nhập
                                </label>
                            </div>

                        </div>
                    </div>

                    <div class="container-login-form">
                        <div class="wrap-login-form-btn">
                            <div class="login-form-bgbtn"></div>
                            <button type="submit"
                                    class="login-form-btn">Đăng nhập</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    var resizefunc = [];
</script>

<!-- jQuery  -->
<script src="admin/assets/js/jquery.min.js"></script>
<script src="admin/assets/js/bootstrap.min.js"></script>
<script src="admin/assets/js/detect.js"></script>
<script src="admin/assets/js/fastclick.js"></script>
<script src="admin/assets/js/jquery.slimscroll.js"></script>
<script src="admin/assets/js/jquery.blockUI.js"></script>
<script src="admin/assets/js/waves.js"></script>
<script src="admin/assets/js/wow.min.js"></script>
<script src="admin/assets/js/jquery.nicescroll.js"></script>
<script src="admin/assets/js/jquery.scrollTo.min.js"></script>


<script src="admin/assets/js/jquery.core.js"></script>
<script src="admin/assets/js/jquery.app.js"></script>
<script>
    function showPassword(name) {
        var target = $('input[name="' + name + '"]');
        if ($(target).attr('type') == 'password' && $(target).val() !== '') {
            $(target)
                .queue(function() {
                    $(target).attr('type', 'text').dequeue();
                });
            $(".icon-show-password").addClass('fa-eye');
            $(".icon-show-password").removeClass('fa-eye-slash');
        } else {
            $(target).queue(function() {
                $(target).attr('type', 'password').dequeue();
            });
            $(".icon-show-password").removeClass('fa-eye');
            $(".icon-show-password").addClass('fa-eye-slash');
        }
    }
</script>
</body>
</html>
