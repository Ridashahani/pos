@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 mt-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Create Setting</h4>
                    </div>
                    <!-- <a href="{{ route('settings.index') }}" class="btn btn-light btn-sm">
                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-1 inline" /> Back
                    </a> -->
                </div>

                <div class="card-body">
                    <form action="{{ route('settings.store') }}" method="POST">
                        @csrf
                        @include('settings.form')
                        <div class="mb-5 ml-3">
                            <button type="submit" class="btn btn-primary mr-2">
                                <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save
                            </button>
                            <a class="btn btn-orange" href="{{ route('settings.index') }}">
                                <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection