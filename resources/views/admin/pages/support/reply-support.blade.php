@extends('admin.layout.index')
@section('content')
    <br>
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid">
            <!-- Right Sidebar -->
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">Reply to support message</h4>
                            <div class="mt-4">
                                <form action="{{ url('admin/send-message') }}" method="POST" class="needs-validation validateForm" novalidate>
                                    @csrf
                                    <input type="hidden" name="supportID" value="{{ $supportID}}">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" name="email" class="form-control" placeholder="To" value="{{ $email }}" required readonly>
                                    </div>
                                    <br>
                                    <div class="form-group">
                                        <label for="message">Reply Message</label>
                                        <textarea name="message" class="summernote" required></textarea>
                                    </div>

                                    <div class="form-group m-b-0">
                                        <div class="text-right">
                                            <button class="btn btn-success waves-effect waves-light"> <span>Send</span> <i class="mdi mdi-send ml-2"></i> </button>
                                        </div>
                                    </div>

                                </form>
                            </div> <!-- end card-->

                        </div>
                        <!-- end inbox-rightbar-->

                        <div class="clearfix"></div>
                    </div>

                </div> <!-- end Col -->

            </div><!-- End row -->

        </div> <!-- container -->

    </div> <!-- content -->

@endsection
@section('cssheader')
    <!-- third party css -->
    <!-- Summernote css -->
    <link href="{{ asset('admin/assets/libs/summernote/summernote-bs4.css') }}" rel="stylesheet"/>

    <!-- third party css end -->
@endsection
@section('jsfooter')
    <!--Summernote js-->
    <script src="{{asset('admin/assets/libs/summernote/summernote-bs4.min.js')}}"></script>
    <script>
        jQuery(document).ready(function(){
            $('.summernote').summernote({
                height: 230,                 // set editor height
                minHeight: null,             // set minimum height of editor
                maxHeight: null,             // set maximum height of editor
                focus: false                 // set focus to editable area after initializing summernote
            });
        });
    </script>
    <!-- Plugin js-->
    <script src="{{ asset('admin/assets/libs/parsleyjs/parsley.min.js') }}"></script>

    <!-- Validation init js-->
    <script src="{{ asset('admin/assets/js/pages/form-validation.init.js') }}"></script>
@endsection
