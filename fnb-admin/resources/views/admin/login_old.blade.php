<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
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
    <link href="admin/assets/css/responsive.css" rel="stylesheet" type="text/css"/>

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script src="admin/assets/js/modernizr.min.js"></script>

</head>
<body>

<div class="account-pages"></div>
<div class="clearfix"></div>
<div class="wrapper-page">
    <div class=" card-box">
        <div class="panel-heading">
            <h3 class="text-center"> Sign In <img src="admin/assets/images/logo_login.png" style="width: 150px"></h3>
        </div>

        <div class="panel-body">
            <form class="form-horizontal m-t-20" action="admin/login" method="post">
                <?php $cookieLogin = !empty($_COOKIE['remember_login']) ? json_decode($_COOKIE['remember_login']) : NULL?>
                {{ csrf_field() }}
                @if(Session::has('message'))
                    <div class="alert alert-danger">{{ Session::get('message') }}</div>
                @endif
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input class="form-control" name="email" type="text" required="" placeholder="Email" value="{{!empty($cookieLogin) ? $cookieLogin->email : ''}}">
                    </div>
                </div>
                @if($errors->has('email'))
                    <div class="alert alert-danger">{{ $errors->first('email') }}</div>
                @endif

                <div class="form-group">
                    <div class="col-xs-12">
                        <input class="form-control" name="password" type="password" required="" placeholder="Password" value="{{!empty($cookieLogin) ? decrypt($cookieLogin->password) : ''}}">
                    </div>
                </div>
                @if($errors->has('password'))
                    <div class="alert alert-danger">{{ $errors->first('password') }}</div>
                @endif

                <div class="form-group ">
                    <div class="col-xs-12">
                        <div class="checkbox checkbox-primary">
                            <input id="checkbox-signup" type="checkbox" name="remember" value="1" {{!empty($cookieLogin) ? 'checked' : ''}}>
                            <label for="checkbox-signup">
                                Remember me
                            </label>
                        </div>

                    </div>
                </div>

                <div class="form-group text-center m-t-40">
                    <div class="col-xs-12">
                        <button class="btn btn-pink btn-block text-uppercase waves-effect waves-light" type="submit">Log
                            In
                        </button>
                    </div>
                </div>
            </form>

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

</body>
</html>
