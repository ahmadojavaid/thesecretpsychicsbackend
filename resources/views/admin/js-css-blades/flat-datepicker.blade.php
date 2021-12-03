@section('css')
@parent 
	<link href="{{asset('admin/assets/libs/flatpickr/flatpickr.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('script')
@parent
	<script src="{{asset('admin/assets/libs/flatpickr/flatpickr.min.js')}}"></script>
@endsection