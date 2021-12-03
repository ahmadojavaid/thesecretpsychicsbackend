@section('css')
@parent 
	<link href="{{asset('admin/assets/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
  
@endsection
@section('script')
@parent
	<script type="text/javascript" src="{{asset('admin/assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
 
@endsection