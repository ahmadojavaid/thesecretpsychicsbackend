@section('css')
@parent 
	<link href="{{asset('datepicker/css/daterangepicker.min.css')}}" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" type="text/css" href="{{asset('datepicker/css/daterangepicker.css')}}" />
@endsection
@section('script')
@parent
	{{-- <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script> --}}
<script type="text/javascript" src="{{asset('datepicker/js/moment.min.js')}}"></script>
<script type="text/javascript" src="{{asset('datepicker/js/daterangepicker.min.js')}}"></script>

	{{-- <script src="{{asset('datepicker/js/jquery.daterangepicker.js')}}"></script> --}}
 
@endsection