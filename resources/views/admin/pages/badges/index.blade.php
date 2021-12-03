@extends('admin.layout.index')
@section('content')
 
	 

	<div class="row">
        <div class="col-12">
                <div class="row">
                    <div class="col-lg-8">
                    </div>
                    <div class="col-lg-4">
                    	<div class="text-lg-right mt-3 mt-lg-0"> 
                            <a href="{{route('admin.security_type.add')}}" class="btn btn-danger waves-effect waves-light"><i class="mdi mdi-plus-circle mr-1"></i> Add Security Type</a>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <div class="row m-t-20">
    	<div class="col-lg-12">
            <div class="card-box">
                <h4 class="header-title"></h4>
                <p class="sub-header">
                    
                </p>

                <div class="table-responsive ">
                    <table class="table datatables table-hover mb-0">
                        <thead>
                        <tr>
                        	<th>Name</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Charges</th>
                            <th> Action </th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    @include('admin.js-css-blades.datatables')
	@include('admin.js-css-blades.sweetalert')
@endsection

@section('script')
@parent
<script type="text/javascript">
	$(document).ready(function(){
	 
	var datatbl = $('.datatables').DataTable({
	processing: true,
	serverSide: true,
	ajax: "{{route('admin.security_type.get_badges')}}",
    "aaSorting": [],
	columns: [
        {data: 'name',name:'name'},
    	{data: 'description',name:'description',},
    	{data: 'selected_img',name:'selected_img',"orderable": false,"searchable": false},
        {data: 'charges',name:'charges'},
        // {data: 'ni_number',name:'ni_number'},
        // {data: 'sia_number',name:'sia_number'},
        // {data: 'sia_expire_on',name:'sia_expire_on'},
        {data: 'action',"orderable": false,"searchable": false},
	]
	});
     $(document).on('click','#sa-title',function () {
       // return false;
       var url = $(this).data('href');
          Swal.fire({
            title: 'Are you sure?',
            text: '',
            type: 'warning',
            showCancelButton: !0,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            confirmButtonClass: 'btn btn-success mt-2',
            cancelButtonClass: 'btn btn-danger ml-2 mt-2',
            buttonsStyling: !1
          }).then(function (t) {
            t.value ? window.location = url  : t.dismiss === Swal.DismissReason.cancel && Swal.fire({
              title: 'Cancelled',
              text: 'Your Data is safe :)',
              type: 'error'
            })
      })
    })
});
	 
</script>
@endsection