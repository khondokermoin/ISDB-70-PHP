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
            
            <!-- Page Header -->
            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Student Management</p>
                        <h1 class="h3 mb-1">Student Profile</h1>
                        <p class="text-muted mb-0">Detailed view of the student's academic and contact records.</p>
                    </div>
                </div>
                <!-- Back Button to List -->
                <div>
                    <a href="{{ url('/students') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <!-- Profile Details Section -->
            <section class="row g-3">
                <!-- Left Sidebar: Basic Info Card -->
                <div class="col-12 col-xl-4">
                    <div class="panel h-100 text-center profile-card shadow-sm p-4 bg-white rounded">
                        <!-- Student Default Avatar Icon -->
                        <div class="my-3">
                            <i class="bi bi-person-circle text-primary" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="h5 mt-3 mb-1">{{ $student->name }}</h2>
                        <p class="text-muted mb-3">ID: #{{ $student->id }}</p>
                        
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge text-bg-primary">{{ ucfirst($student->gender) }}</span>
                            <span class="badge text-bg-success">Active Student</span>
                        </div>
                        
                        <div class="info-list mt-4 text-start border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Registered At:</span>
                                <strong>{{ $student->created_at->format('d M, Y') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Detailed Academic & Contact Info -->
                <div class="col-12 col-xl-8">
                    <div class="panel h-100 shadow-sm p-4 bg-white rounded">
                        
                        <!-- Header with Title and Edit Button on the Right Side -->
                        <div class="panel-header border-bottom pb-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div>
                                    <h2 class="h5 mb-1 section-title">
                                        <i class="bi bi-person-gear" aria-hidden="true"></i>
                                        <span>Information Details</span>
                                    </h2>
                                    <p class="text-muted mb-0">Verified contact information and selected subjects.</p>
                                </div>
                                
                                <!-- Edit Button explicitly aligned to the Right Side -->
                                <div class="text-end">
                                    <a href="{{ route('students.edit', $student->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-pencil-square"></i> Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Data Display Grid -->
                        <div class="row g-4">
                            <!-- Full Name -->
                            <div class="col-md-6">
                                <label class="form-label text-muted d-block mb-1">Full Name</label>
                                <div class="p-2 bg-light border rounded"><strong>{{ $student->name }}</strong></div>
                            </div>

                            <!-- Email Address -->
                            <div class="col-md-6">
                                <label class="form-label text-muted d-block mb-1">Email Address</label>
                                <div class="p-2 bg-light border rounded"><strong>{{ $student->email }}</strong></div>
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-6">
                                <label class="form-label text-muted d-block mb-1">Phone Number</label>
                                <div class="p-2 bg-light border rounded"><strong>{{ $student->phone ?? 'N/A' }}</strong></div>
                            </div>

                            <!-- Gender -->
                            <div class="col-md-6">
                                <label class="form-label text-muted d-block mb-1">Gender</label>
                                <div class="p-2 bg-light border rounded"><strong>{{ ucfirst($student->gender) }}</strong></div>
                            </div>

                            <!-- District -->
                            <div class="col-md-6">
                                <label class="form-label text-muted d-block mb-1">District</label>
                                <div class="p-2 bg-light border rounded">
                                    <strong>
                                        @if($student->district == '1') Dhaka 
                                        @elseif($student->district == '2') Barishal 
                                        @elseif($student->district == '3') Khulna 
                                        @elseif($student->district == '4') Chittagong 
                                        @else {{ $student->district }} @endif
                                    </strong>
                                </div>
                            </div>

                            <!-- Enrolled Subjects -->
                            <div class="col-md-6">
                                <label class="form-label text-muted d-block mb-1">Enrolled Subjects</label>
                                <div class="p-2 bg-light border rounded">
                                    <strong>{{ $student->subjects ?? 'No subject selected' }}</strong>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection()
