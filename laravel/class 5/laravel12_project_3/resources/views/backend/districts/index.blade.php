@extends('backend.master')

{{-- style css urls --}}
@push('styles')
@endpush()

{{-- scripts url --}}
@push('scripts')
@endpush()

{{-- content --}}
@section('content')
<main class="dashboard-content">
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Data</p>
                    <h1 class="h3 mb-1">Districts</h1>
                    <p class="text-muted mb-0">Use responsive, searchable tables for district records.</p>
                </div>
            </div>
        </div>

        <section class="panel">
            @session('success')
            <div class="alert alert-success" role="alert">
                {{ $value }}
            </div>
            @endsession
            @session('destroy')
            <div class="alert alert-danger " role="alert">
                {{ $value }}
            </div>
            @endsession

            <div class="d-flex justify-content-end mb-3">
                <a class="btn btn-outline-secondary" href="{{ route('districts.create') }}">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> New District
                </a>
            </div>

            <div class="panel-header">
                <div>
                    <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>District Table</span></h2>
                    <p class="text-muted mb-0">Searchable responsive table for district data.</p>
                </div>
                <input class="form-control form-control-sm table-search" type="search"
                    placeholder="Search districts" data-table-search="districtsTable" aria-label="Search districts">
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="districtsTable" data-searchable-table>
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 15%;">Id</th>
                            <th class="text-center" style="width: 55%;">District Name</th>
                            <th class="text-center" style="width: 30%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($districts as $district)
                        <tr>
                            <td class="text-center">{{ $district->id }}</td>
                            <td class="text-center">{{ $district->name }}</td>
                            <td class="text-center">
                                <a href="{{ route('districts.show', $district->id) }}"
                                    class="btn btn-outline-info btn-sm">
                                    View
                                </a>

                                <a href="{{ route('districts.edit', $district->id) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    Edit
                                </a>

                                <form action="{{ route('districts.destroy', $district->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-danger btn-sm" type="submit"
                                        onclick="return confirm('Are you sure you want to delete this district?');">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
@endsection()