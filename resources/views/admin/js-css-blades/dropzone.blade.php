@section('css')
@parent 
		<link href="{{asset('admin/assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('admin/assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('script')
@parent
		<script src="{{asset('admin/assets/libs/dropzone/dropzone.min.js')}}"></script>
		<script src="{{asset('admin/assets/libs/dropify/dropify.min.js')}}"></script>
@endsection