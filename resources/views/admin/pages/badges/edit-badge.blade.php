@extends('admin.layout.index')
@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{route('admin.security_type.update')}}" id="form1"
                  enctype="multipart/form-data">
                <h4 class="mb-3 header-title"></h4>
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @csrf
                <div class="alert alert-danger  hide error_msgs" role="alert">
                </div>
                <div class="alert alert-success hide success_msgs" role="alert">
                </div>
                <div class="row">
                    <div class="col-lg-6 error_p">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Name</label>
                            <input type="text" value="{{$badge->name}}" id="name" required="" name="name"
                                   class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 error_p">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Charges</label>
                            <input type="number" name="charges" value="{{$badge->charges}}" required=""
                                   class="form-control"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 error_p">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Description</label>
                            <textarea class="form-control" required=""
                                      name="description">{{$badge->description}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 error_p">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Upload Image</label>
                            {{-- <input type="file"  name="file"  required ="" class="form-control"> --}}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="mt-3">
                            <input type="file" class="dropify" accept="image/*" name="image"/>
                            <input type="hidden" name="id" value="{{request()->id}}">
                        </div>
                        <input type="hidden" name="img_del" class="img_delete" value="0">

                        <div class="">
                            <div class="card-box product-box">
                                <div class="product-action">
                                    <a href="javascript: void(0);"
                                       class="btn btn-danger btn-xs waves-effect waves-light del_img"><i
                                            class="mdi mdi-close"></i></a>
                                </div>
                                <div>
                                    <img src="{{asset($badge->selected_img)}}" alt="product-pic"
                                         class="img-fluid set_width"/>
                                </div>
                            </div> <!-- end card-box-->
                        </div> <!-- end col-->

                    </div>
                </div>
                <div class="m-t-20">
                    <button type="submit" id="submit-all" class="btn btn-primary waves-effect waves-light">Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    @parent
    <script src="{{asset('js/pristine.min.js')}}"></script>
    <script type="text/javascript">

        window.onload = function () {
            var form = document.getElementById("form1");
            let defaultConfig = {
                classTo: 'error_p',
                errorClass: 'has-danger',
                successClass: 'has-success',
                errorTextParent: 'error_p',
                errorTextTag: 'div',
                errorTextClass: 'text-help'
            };
            var pristine = new Pristine(form, defaultConfig);
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var valid = pristine.validate();
                if (valid) {
                    form.submit();
                }
                // alert('Form is valid: ' + valid);
            });
        };
        $('.img_delete').val(0);
        $('.del_img').click(function () {
            $('.img_delete').val(1);
            $(this).parent().parent().remove();
        });
    </script>

@endsection

