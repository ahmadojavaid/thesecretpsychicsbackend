<!DOCTYPE html>
<html lang="en">

  <head>
    <base href="../../../../">
    <meta charset="utf-8" />
    <title>Metronic | Login Page v3</title>
    <meta name="description" content="Login page example">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="_token" content="{{ csrf_token() }}">
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js')}}"></script> --}}
    {{-- <script>
      WebFont.load({
        google: {
          "families": ["Poppins:300,400,500,600,700", "Roboto:300,400,500,600,700"]
        },
        active: function() {
          sessionStorage.fonts = true;
        }
      });
    </script> --}}
    <link href="{{asset('admin/assets/css/demo1/pages/general/login/login-3.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/vendors/general/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/vendors/general/tether/dist/css/tether.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/vendors/general/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/vendors/general/nouislider/distribute/nouislider.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/vendors/general/animate.css/animate.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/css/demo1/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/css/demo1/skins/header/base/light.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/css/demo1/skins/header/menu/light.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/css/demo1/skins/brand/dark.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/css/demo1/skins/aside/dark.css')}}" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{asset('admin/assets/media/logos/favicon.ico')}}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" integrity="sha256-ENFZrbVzylNbgnXx0n3I1g//2WeO47XxoPe0vkp3NC8=" crossorigin="anonymous" />
  </head>
  <body class="kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header-mobile--fixed kt-subheader--fixed kt-subheader--enabled kt-subheader--solid kt-aside--enabled kt-aside--fixed kt-page--loading">
    <div class="kt-grid kt-grid--ver kt-grid--root">
      <div class="kt-grid kt-grid--hor kt-grid--root  kt-login kt-login--v3 kt-login--signin" id="kt_login">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" style="background-image: url({{asset('admin/assets/media//bg/bg-3.jpg')}});">
          <div class="kt-grid__item kt-grid__item--fluid kt-login__wrapper">
            <div class="kt-login__container">
              <div class="kt-login__logo">
                <a href="#">
                  <img src="{{asset('admin/assets/media/logos/logo-5.png')}}">
                </a>
              </div>
              <div class="kt-login__signin">
                <div class="kt-login__head">
                  <h3 class="kt-login__title">Sign In To Admin</h3>
                </div>
                <form class="kt-form" action="{{route('admin_login')}}" method="post">
                  @csrf 
                  @if (count($errors) > 0)
                      <div class="alert alert-solid-danger alert-bold">
                          <ul>
                              @foreach ($errors->all() as $error)
                                  <li>{{ $error }}</li>
                              @endforeach
                          </ul>
                      </div>
                  @endif
                  <div class="input-group">
                    <input class="form-control" type="text" placeholder="Email" name="email" autocomplete="off">
                  </div>
                  <div class="input-group">
                    <input class="form-control" type="password" placeholder="Password" name="password">
                  </div>
                  <div class="row kt-login__extra">
                    <div class="col">
                      <label class="kt-checkbox">
                        <input type="checkbox" name="remember"> Remember me
                        <span></span>
                      </label>
                    </div>
                    <div class="col kt-align-right">
                      <a href="javascript:;" id="kt_login_forgot" class="kt-login__link">Forget Password ?</a>
                    </div>
                  </div>
                  <div class="kt-login__actions">
                    <button id="" class="btn btn-brand btn-elevate kt-login__btn-primary">Sign In</button>
                  </div>
                </form>
              </div>
              <div class="kt-login__signup">
                <div class="kt-login__head">
                  <h3 class="kt-login__title">Sign Up</h3>
                  <div class="kt-login__desc">Enter your details to create your account:</div>
                </div>
                <form class="kt-form" action="">
                  <div class="input-group">
                    <input class="form-control" type="text" placeholder="Fullname" name="fullname">
                  </div>
                  <div class="input-group">
                    <input class="form-control" type="text" placeholder="Email" name="email" autocomplete="off">
                  </div>
                  <div class="input-group">
                    <input class="form-control" type="password" placeholder="Password" name="password">
                  </div>
                  <div class="input-group">
                    <input class="form-control" type="password" placeholder="Confirm Password" name="rpassword">
                  </div>
                  <div class="row kt-login__extra">
                    <div class="col kt-align-left">
                      <label class="kt-checkbox">
                        <input type="checkbox" name="agree">I Agree the <a href="#" class="kt-link kt-login__link kt-font-bold">terms and conditions</a>.
                        <span></span>
                      </label>
                      <span class="form-text text-muted"></span>
                    </div>
                  </div>
                  <div class="kt-login__actions">
                    <button id="kt_login_signup_submit" class="btn btn-brand btn-elevate kt-login__btn-primary">Sign Up</button>&nbsp;&nbsp;
                    <button id="kt_login_signup_cancel" class="btn btn-light btn-elevate kt-login__btn-secondary">Cancel</button>
                  </div>
                </form>
              </div>
              <div class="kt-login__forgot">
                <div class="kt-login__head">
                  <h3 class="kt-login__title">Forgotten Password ?</h3>
                  <div class="kt-login__desc">Enter your email to reset your password:</div>
                </div>
                <form class="kt-form" action="">
                  <div class="input-group">
                    <input class="form-control" type="text" placeholder="Email" name="email" id="kt_email" autocomplete="off">
                  </div>
                  <div class="kt-login__actions">
                    <button id="kt_login_forgot_submit" class="btn btn-brand btn-elevate kt-login__btn-primary">Request</button>&nbsp;&nbsp;
                    <button id="kt_login_forgot_cancel" class="btn btn-light btn-elevate kt-login__btn-secondary">Cancel</button>
                  </div>
                </form>
              </div>
              <div class="kt-login__account">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
      var KTAppOptions = {
        "colors": {
          "state": {
            "brand": "#5d78ff",
            "dark": "#282a3c",
            "light": "#ffffff",
            "primary": "#5867dd",
            "success": "#34bfa3",
            "info": "#36a3f7",
            "warning": "#ffb822",
            "danger": "#fd3995"
          },
          "base": {
            "label": ["#c5cbe3", "#a1a8c3", "#3d4465", "#3e4466"],
            "shape": ["#f0f3ff", "#d9dffa", "#afb4d4", "#646c9a"]
          }
        }
      };
    </script>
   
    <script src="{{asset('admin/assets/vendors/general/jquery/dist/jquery.js')}}" type="text/javascript"></script>
    <script src="{{asset('admin/assets/vendors/general/popper.js/dist/umd/popper.js')}}" type="text/javascript"></script>
    <script src="{{asset('admin/assets/vendors/general/bootstrap/dist/js/bootstrap.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('admin/assets/vendors/general/jquery-form/dist/jquery.form.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('admin/assets/vendors/general/jquery-validation/dist/jquery.validate.js')}}" type="text/javascript"></script>
    <script src="{{asset('admin/assets/vendors/general/jquery-validation/dist/additional-methods.js')}}" type="text/javascript"></script>
    <script src="{{asset('admin/assets/vendors/custom/js/vendors/jquery-validation.init.js')}}" type="text/javascript"></script>
    <script src="{{asset('admin/assets/js/demo1/scripts.bundle.js')}}" type="text/javascript"></script>
    <script src="{{asset('admin/assets/js/demo1/pages/login/login-general.js')}}" type="text/javascript"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha256-3blsJd4Hli/7wCQ+bmgXfOdK7p/ZUMtPXY08jmxSSgk=" crossorigin="anonymous"></script>
    <script type="text/javascript">
      toastr.options = {
          "closeButton": false,
          "debug": false,
          "newestOnTop": false,
          "progressBar": false,
          "positionClass": "toast-bottom-left",
          "preventDuplicates": false,
          "onclick": null,
          "showDuration": "300",
          "hideDuration": "1000",
          "timeOut": "5000",
          "extendedTimeOut": "1000",
          "showEasing": "swing",
          "hideEasing": "linear",
          "showMethod": "fadeIn",
          "hideMethod": "fadeOut"
        }
        @if(request()->session()->has('flash')) 
          toastr.error("{{request()->session()->get('flash')}}", 'Error');
          @php request()->session()->forget('flash')@endphp
        @endif
        @if(request()->session()->has('flash_success')) 
          toastr.success("{{request()->session()->get('flash_success')}}", 'Success');
          @php request()->session()->forget('flash_success')@endphp
        @endif 
      </script>
  </body>
</html>