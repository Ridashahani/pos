@extends('dashboard.body.main')

@section('container')
<div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card"><div class="card-header"><h4 class="card-title">Create Subcategory</h4></div><div class="card-body">@include('subcategories._form')</div></div></div></div></div>
@endsection
