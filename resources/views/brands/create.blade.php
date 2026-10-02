@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">

                   <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Add New Brand</h4>

               <a href="{{ route('brands.index') }}"
                class="btn btn-secondary d-flex align-items-center text-nowrap">
               <x-heroicon-o-arrow-left class="w-5 h-5 mr-2" />
                 Back to Brands
                    </a>
                    </div>
    
                    <div class="card-body">
                        <form action="{{ route('brands.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" maxlength="255" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary mr-2">Add Brand</button>
                            <a class="btn btn-cancel" href="{{ route('brands.index') }}">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection