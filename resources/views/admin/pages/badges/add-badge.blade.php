@extends('admin.layout.index')
@section('content')
<div class="card">
   <div class="card-body">
      <form method="post" action="{{route('admin.security_type.save')}}" id="form1"  enctype="multipart/form-data">
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
                  <input type="text"  value="{{old('name')}}" id="name" required =""  name="name" class="form-control">
               </div>
            </div>            
         </div>
         <div class="row">
         	<div class="col-lg-6 error_p">
               <div class="form-group">
                  <label for="exampleInputPassword1">Charges</label>
                  <input type="number" name="charges" required ="" class="form-control" />
               </div>
            </div>
         </div>
         <div class="row">
         	<div class="col-lg-6 error_p">
               <div class="form-group">
                  <label for="exampleInputPassword1">Description</label>
                  <textarea class="form-control" required ="" name="description"></textarea>
               </div>
            </div>
         </div>
         <div class="row">
         	<div class="col-lg-6 error_p">
               <div class="form-group">
                  <label for="exampleInputPassword1">Upload Image</label>
                  <input type="file"  name="file"  required ="" class="form-control">
               </div>
            </div>
         </div>
         <div class="m-t-20">
            <button type="submit" id="submit-all" class="btn btn-primary waves-effect waves-light">Submit</button>
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
		   var pristine = new Pristine(form,defaultConfig);
		   form.addEventListener('submit', function (e) {
		      e.preventDefault();
		      var valid = pristine.validate();
		      if(valid){
		      	form.submit();
		      }
		      // alert('Form is valid: ' + valid);
		   });
		};
	</script>
 
@endsection

