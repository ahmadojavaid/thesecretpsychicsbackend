@extends('admin.layout.index')
@section('content')
    <br>
    <div class="content">
        @if(Session::has('message'))
            <div class="alert">
                <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
                <script>
                    setTimeout(function(){
                        $('div.alert').toggle(1000);
                    },3500);
                </script>
            </div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">User Support Messages</h4>
                        <br>
                        <table id="alternative-page-datatable" class="table dt-responsive nowrap">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Message</th>
                                <th>Action(s)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($support as $detail)
                                <tr>
                                    <td>{{ $detail->userDetails->first_name.' '.$detail->userDetails->last_name }}</td>
                                    <td>{{ $detail->userDetails->email }}</td>
                                    <td>{{ $detail->userDetails->phone }}</td>
                                    <td>{{ $detail->message }}</td>
                                    <td>
                                        <a href="{{ url('admin/reply-support?support_id='.$detail->id.'&support_type=1') }}" class="action-icon"
                                           data-toggle="tooltip" data-placement="top" title=""
                                           data-original-title="Reply"> <i class="mdi mdi-reply text-black-50"></i></a>
                                        <a href="{{ url('') }}" class="action-icon" data-toggle="tooltip"
                                           data-placement="top" title="" data-original-title="Delete">
                                            <i class="mdi mdi-delete"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div> <!-- end card body-->
                </div> <!-- end card -->
            </div><!-- end col-->
        </div>
        <!-- end row-->
    </div>
@endsection
@section('cssheader')
    <!-- third party css -->
    <link href="{{ asset('admin/assets/libs/datatables/dataTables.bootstrap4.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ asset('admin/assets/libs/datatables/responsive.bootstrap4.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ asset('admin/assets/libs/datatables/buttons.bootstrap4.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('admin/assets/libs/datatables/select.bootstrap4.css') }}" rel="stylesheet" type="text/css"/>
    <!-- third party css end -->
@endsection
@section('jsfooter')
    <!-- third party js -->
    <script src="{{ asset('admin/assets/libs/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/buttons.print.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/datatables/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/pages/datatables.init.js') }}"></script>
@endsection
