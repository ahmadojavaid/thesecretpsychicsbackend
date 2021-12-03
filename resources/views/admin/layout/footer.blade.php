<!-- begin:: Footer -->
	<div class="kt-footer kt-grid__item kt-grid kt-grid--desktop kt-grid--ver-desktop" id="kt_footer">
		<div class="kt-footer__copyright">
			2019&nbsp;&copy;&nbsp;<a href="http://keenthemes.com/metronic" target="_blank" class="kt-link">Keenthemes</a>
		</div>
		<div class="kt-footer__menu">
			<a href="http://keenthemes.com/metronic" target="_blank" class="kt-footer__menu-link kt-link">About</a>
			<a href="http://keenthemes.com/metronic" target="_blank" class="kt-footer__menu-link kt-link">Team</a>
			<a href="http://keenthemes.com/metronic" target="_blank" class="kt-footer__menu-link kt-link">Contact</a>
		</div>
	</div>

 

		<!--begin:: Global Mandatory Vendors -->
		<script src="{{asset('admin/assets/vendors/general/jquery/dist/jquery.js')}}" type="text/javascript"></script>
		<script src="{{asset('admin/assets/vendors/general/popper.js/dist/umd/popper.js')}}" type="text/javascript"></script>
		<script src="{{asset('admin/assets/vendors/general/bootstrap/dist/js/bootstrap.min.js')}}" type="text/javascript"></script>
		<script src="{{asset('admin/assets/vendors/general/js-cookie/src/js.cookie.js')}}" type="text/javascript"></script>
		<script src="{{asset('admin/assets/vendors/general/moment/min/moment.min.js')}}" type="text/javascript"></script>
		<script src="{{asset('admin/assets/vendors/general/tooltip.js/dist/umd/tooltip.min.js')}}" type="text/javascript"></script>
		<script src="{{asset('admin/assets/vendors/general/perfect-scrollbar/dist/perfect-scrollbar.js')}}" type="text/javascript"></script>
		<script src="{{asset('admin/assets/vendors/general/sticky-js/dist/sticky.min.js')}}" type="text/javascript"></script>
		<script src="{{asset('admin/assets/vendors/general/wnumb/wNumb.js')}}" type="text/javascript"></script>

		<!--end:: Global Mandatory Vendors -->

		<!--begin:: Global Optional Vendors -->
		
		<script src="{{asset('admin/assets/vendors/general/chart.js/dist/Chart.bundle.js')}}" type="text/javascript"></script>

		<!--end:: Global Optional Vendors -->

		<!--begin::Global Theme Bundle(used by all pages) -->
		<script src="{{asset('admin/assets/js/demo1/scripts.bundle.js')}}" type="text/javascript"></script> 
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