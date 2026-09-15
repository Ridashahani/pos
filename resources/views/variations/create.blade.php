@extends('dashboard.body.main')
@section('container')
    @include('variations._form', ['variation' => null, 'formAction' => route('variations.store'), 'formMethod' => 'POST', 'submitLabel' => 'Save'])
@endsection