@extends('admin.layout.index')
@section('content')
 
	 

	<div class="row">
        <div class="col-12">
         
            <!-- <div class="card-box"> -->
                <div class="row">
                    <div class="col-lg-8">
                         
                    </div>
                    <div class="col-lg-4">
                       {{--  <div class="text-lg-right mt-3 mt-lg-0"> 
                            <a href="" class="btn btn-danger waves-effect waves-light"><i class="mdi mdi-plus-circle mr-1"></i> Add Coupon</a>
                        </div> --}}
                    </div><!-- end col-->
                </div> <!-- end row -->
            <!-- </div> end card-box -->
        </div> <!-- end col-->
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
                        	<th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>NI Number</th>
                            <th>SIA Number</th>
                            <th>SIA Expire</th>
                            {{-- <th>Action</th> --}}
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div> <!-- end table-responsive-->

            </div> <!-- end card-box -->
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
	ajax: "{{route('admin.agent.get_agents')}}",
    "aaSorting": [],
	columns: [
        {data: 'first_name',name:'first_name'},
    	{data: 'last_name',name:'last_name',},
    	{data: 'email',name:'email'},
        {data: 'phone',name:'phone'},
        {data: 'ni_number',name:'ni_number'},
        {data: 'sia_number',name:'sia_number'},
        {data: 'sia_expire_on',name:'sia_expire_on'},
        // {data: 'action',"orderable": false,"searchable": false},
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