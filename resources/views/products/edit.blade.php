@extends('dashboard.body.main')

@section('specificpagestyles')
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
<link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('container')
@include('products._form', ['formAction' => route('products.update', $product), 'formMethod' => 'PUT', 'submitLabel' => 'Update'])
    

@include('components.preview-img-form')
@endsection