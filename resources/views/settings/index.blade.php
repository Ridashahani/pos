@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
                <div class="alert text-white bg-success" role="alert">
                    <div class="iq-alert-text">{{ session('success') }}</div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
            @endif
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Settings</h4>
                </div>
                <div>
                    <a href="{{ route('settings.create') }}" class="btn btn-primary add-list">
                        <x-heroicon-o-plus class="w-5 h-5 mr-3" />Create Setting
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="table-responsive rounded mb-3">
                <table class="table mb-0 text-center">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>Key</th>
                            <th>Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="ligth-body">
                    @forelse ($settings as $setting)
                        <tr>
                            <td>{{ $setting->key }}</td>
                            <td class="text-break">{{ $setting->value }}</td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center list-action">
                                    <a class="btn btn-primary mr-2" data-toggle="tooltip" data-placement="top" title="" href="{{ route('settings.edit', $setting) }}">
                                        <x-heroicon-o-pencil class="w-5 h-5 mr-0" />
                                    </a>
                                    <form action="{{ route('settings.destroy', $setting) }}" method="POST" style="display:inline;">
                                        @method('delete')
                                        @csrf
                                        <button type="submit" class="btn btn-danger border-0" onclick="return confirm('Are you sure you want to delete this setting?')" data-toggle="tooltip" data-placement="top" title="">
                                            <x-heroicon-o-trash class="w-5 h-5 mr-0" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">
                                <div class="alert" role="alert">
                                    <div class="iq-alert-text">No settings found. Click "Create Setting" to add one.</div>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <x-heroicon-o-x-mark class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $settings->links() }}
        </div>
    </div>
</div>
@endsection