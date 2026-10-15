<!-- begin: Edit Profile -->
<div class="card-header d-flex justify-content-between">
    <div class="iq-header-title">
        <h4 class="card-title">Edit Profile</h4>
    </div>
</div>
<div class="card-body">
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('put')
        <!-- begin: Input Image -->
        <div class="form-group row align-items-center">
            <div class="col-md-12">
                <div class="profile-img-edit">
                    <div class="crm-profile-img-edit">
                        <img class="crm-profile-pic rounded-circle avatar-100" id="image-preview" src="{{ $user->photo ? asset('storage/profile/'.$user->photo) : asset('assets/images/user/1.png') }}" alt="profile-pic">
                    </div>
                </div>
            </div>
        </div>
        <div class="input-group mb-4">
            <div class="custom-file">
                <input type="file" class="custom-file-input @error('photo') is-invalid @enderror" id="image" name="photo" accept="image/*" onchange="previewImage();">
                <label class="custom-file-label" for="photo">Choose file</label>
            </div>
            @error('photo')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
        <!-- end: Input Image -->
        <!-- begin: Input Data -->
        <div class=" row align-items-center">
            <div class="form-group col-md-12">
                <label for="name">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="username">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}" required>
                @error('username')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="job_description">Job Description</label>
                <input type="text" class="form-control @error('job_description') is-invalid @enderror" id="job_description" name="job_description" value="{{ old('job_description', $user->job_description) }}">
                @error('job_description')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            @if (!$user->isAdmin())
            <div class="form-group col-md-6">
                <label for="active_branch_id">Branch <span class="text-danger">*</span></label>
                <select class="form-control @error('active_branch_id') is-invalid @enderror" id="active_branch_id" name="active_branch_id" required>
                    <option value="" disabled {{ !$user->active_branch_id ? 'selected' : '' }}>Select Branch</option>
                    @foreach ($user->branches()->where(function($q) { $q->where('status', 'Active')->orWhere('status', 1)->orWhere('status', true); })->orderBy('name')->get() as $branch)
                        <option value="{{ $branch->id }}" {{ old('active_branch_id', $user->active_branch_id) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('active_branch_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            @endif
        </div>
        <!-- end: Input Data -->
        <div class="mt-2">
            <button type="submit" class="btn btn-save mr-2">
                <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Update
            </button>
            <a class="btn btn-cancel" href="{{ route('profile') }}">
                <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
            </a>
        </div>
    </form>
</div>
<!-- end: Edit Profile -->