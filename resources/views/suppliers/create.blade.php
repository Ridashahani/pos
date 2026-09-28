@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Add Supplier</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('suppliers.form')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
