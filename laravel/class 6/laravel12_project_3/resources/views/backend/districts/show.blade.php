@extends('backend.master')

{{-- style css urls --}}
@push('styles')
@endpush

{{-- scripts url --}}
@push('scripts')
@endpush

{{-- content --}}
@section('content')
<main class="dashboard-content">
    <div class="container-fluid px-3 px-lg-4 py-4">

        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Location Management</p>
                    <h1 class="h3 mb-1">District Profile</h1>
                    <p class="text-muted mb-0">Detailed view of the district's operational settings.</p>
                </div>
            </div>
            <div>
                <a href="{{ route('districts.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <section class="row g-3">
            <div class="col-12 col-xl-4">
                <div class="panel h-100 text-center profile-card p-4">
                    <div class="my-3">
                        <i class="bi bi-geo-fill text-primary" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="h5 mt-3 mb-1">{{ $district->name }}</h2>
                    <p class="text-muted mb-3">ID: #{{ $district->id }}</p>

                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active Location</span>
                    </div>

                    {{-- Dark mode friendly border adjustment --}}
                    <div class="info-list mt-4 text-start border-top border-secondary-subtle pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Created At:</span>
                            <strong class="text-inherit">{{ $district->created_at->format('d M, Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="panel h-100 p-4">
                    <div class="panel-header border-bottom border-secondary-subtle pb-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-geo" aria-hidden="true"></i><span>Information Details</span></h2>
                                <p class="text-muted mb-0">System configuration and modification logs.</p>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('districts.edit', $district->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil-square"></i> Edit District
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted d-block mb-1">District Name</label>
                            <div class="p-2 border border-secondary-subtle rounded text-inherit">
                                <strong>{{ $district->name }}</strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted d-block mb-1">System Record ID</label>
                            <div class="p-2 border border-secondary-subtle rounded text-inherit">
                                <strong>#{{ $district->id }}</strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted d-block mb-1">Last Updated</label>
                            <div class="p-2 border border-secondary-subtle rounded text-inherit">
                                <strong>{{ $district->updated_at->format('d M, Y - h:i A') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
@endsection