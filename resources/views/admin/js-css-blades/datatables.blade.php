@section('css')
@parent 
	<link href="{{asset('admin/assets/libs/datatables/dataTables.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/libs/datatables/responsive.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/libs/datatables/buttons.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/libs/datatables/select.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
@parent

	<script src="{{asset('admin/assets/libs/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/datatables/dataTables.bootstrap4.js')}}"></script>
    <script src="{{asset('admin/assets/libs/datatables/dataTables.responsive.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/datatables/responsive.bootstrap4.min.js')}}"></script>
   <!--  <script src="{{asset('admin/assets/libs/datatables/dataTables.buttons.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/datatables/buttons.bootstrap4.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/datatables/buttons.html5.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/datatables/buttons.flash.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/datatables/buttons.print.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/datatables/dataTables.keyTable.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/datatables/dataTables.select.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/pdfmake/pdfmake.min.js')}}"></script>
    <script src="{{asset('admin/assets/libs/pdfmake/vfs_fonts.js')}}"></script> -->
@endsection