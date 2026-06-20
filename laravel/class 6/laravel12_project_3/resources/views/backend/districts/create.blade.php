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
                    <p class="eyebrow mb-1">Management</p>
                    <h1 class="h3 mb-1">Add District</h1>
                    <p class="text-muted mb-0">Create a new district record for location settings.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('districts.index') }}">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Districts
                </a>
            </div>
        </div>

        <section class="row g-3">
            <div class="col-12 col-xl-8">
                {{-- Top Validation Errors Block --}}
                @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form class="panel needs-validation" method="POST" novalidate action="{{ route('districts.store') }}">
                    @csrf
                    <div class="panel-header">
                        <div>
                            <h2 class="h5 mb-1 section-title">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i><span>District Information</span>
                            </h2>
                            <p class="text-muted mb-0">Create a district with validated fields.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label" for="name">District Name</label>
                            <input class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                placeholder="Enter district name (e.g., Dhaka)"
                                value="{{ old('name') }}"
                                type="text"
                                required>
                            <div class="invalid-feedback">District name is required.</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="{{ route('districts.index') }}">Cancel</a>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-geo" aria-hidden="true"></i> Create District
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>
@endsection()