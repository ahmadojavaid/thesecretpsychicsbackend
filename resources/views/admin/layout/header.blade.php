	<!--begin::Base Path (base relative path for assets of this page) -->
	<base href="../">

	<!--end::Base Path -->
	<meta charset="utf-8" />
	<title>Metronic | Dashboard</title>
	<meta name="description" content="Updates and statistics">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="_token" content="{{ csrf_token() }}">
	<!--begin::Fonts -->
	<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
	<script>
		WebFont.load({
			google: {
				"families": ["Poppins:300,400,500,600,700", "Roboto:300,400,500,600,700"]
			},
			active: function() {
				sessionStorage.fonts = true;
			}
		});
	</script>
	<link href="{{asset('admin/assets/vendors/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{asset('admin/assets/vendors/general/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{asset('admin/assets/vendors/general/socicon/css/socicon.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{asset('admin/assets/vendors/custom/vendors/line-awesome/css/line-awesome.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{asset('admin/assets/css/demo1/style.bundle.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{asset('admin/assets/css/demo1/skins/header/base/light.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{asset('admin/assets/css/demo1/skins/header/menu/light.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{asset('admin/assets/css/demo1/skins/brand/dark.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{asset('admin/assets/css/demo1/skins/aside/dark.css')}}" rel="stylesheet" type="text/css" />
	<link rel="shortcut icon" href="{{asset('admin/assets/media/logos/favicon.ico')}}" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" integrity="sha256-ENFZrbVzylNbgnXx0n3I1g//2WeO47XxoPe0vkp3NC8=" crossorigin="anonymous" />
	@section('css')
	@show


