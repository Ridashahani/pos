@extends('dashboard.body.main')
@section('container')
    @include('variations._form', ['formAction' => route('variations.update', $variation), 'formMethod' => 'PUT', 'submitLabel' => 'Update'])
@endsection