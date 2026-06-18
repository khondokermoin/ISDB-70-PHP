@extends('backend.master')

{{-- style css urls --}}
@push('styles')
@endpush()

{{-- scripts url --}}
@push('scripts')
@endpush()

{{-- scontent --}}
@section('content')
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Management</p>
                        <h1 class="h3 mb-1">Add User</h1>
                        <p class="text-muted mb-0">Create a new user account with role and team assignments.</p>
                    </div>
                </div>
                <div class="heading-actions"><a class="btn btn-outline-secondary btn-sm" href="{{ url('/students') }}"><i
                            class="bi bi-arrow-left" aria-hidden="true"></i> Back to Users</a></div>
            </div>

            <section class="row g-3">
                <div class="col-12 col-xl-8">
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
                    <form class="panel needs-validation" method="POST" novalidate action="{{ route('students.store') }}">
                        @csrf
                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-person-plus"
                                        aria-hidden="true"></i><span>User Information</span></h2>
                                <p class="text-muted mb-0">Create a user account with validated fields.</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <!-- First Name -->
                            <div class="col-md-6">
                                <label class="form-label" for="fullName">Name</label>
                                <input class="form-control" id="fullName" name="fullName" value="{{ old('fullName') }}"
                                    type="text" required>
                                <div class="invalid-feedback">First name is required.</div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" id="email" name="email" value="{{ old('email') }}"
                                    type="email" required>
                                <div class="invalid-feedback">Enter a valid email.</div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone</label>
                                <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}"
                                    type="tel" required>
                                <div class="invalid-feedback">Phone number is required.</div>
                            </div>

                            <!-- Gender -->
                            <div class="col-md-6">
                                <label class="form-label d-block">Gender</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender"
                                        {{ old('gender') == 'male' ? 'checked' : '' }} id="genderMale" value="male"
                                        required>
                                    <label class="form-check-label" for="genderMale">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender"
                                        {{ old('gender') == 'female' ? 'checked' : '' }} id="genderFemale" value="female"
                                        required>
                                    <label class="form-check-label" for="genderFemale">Female</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender"
                                        {{ old('gender') == 'other' ? 'checked' : '' }} id="genderOther" value="other"
                                        required>
                                    <label class="form-check-label" for="genderOther">Other</label>
                                </div>
                                <div class="invalid-feedback">Please select a gender.</div>
                            </div>

                            <!-- District -->
                            <div class="col-md-6">
                                <label class="form-label" for="district">District</label>
                                <select class="form-select" id="district" name="district" required>
                                    <option value="">Choose district</option>
                                    <option value="1" {{ old('district') == '1' ? 'selected' : '' }}>Dhaka</option>
                                    <option value="2" {{ old('district') == '2' ? 'selected' : '' }}>Barishal</option>
                                    <option value="3" {{ old('district') == '3' ? 'selected' : '' }}>Khulna</option>
                                    <option value="4" {{ old('district') == '4' ? 'selected' : '' }}>Chittagong
                                    </option>
                                </select>
                                <div class="invalid-feedback">Choose a district.</div>
                            </div>


                            <!-- Subject (Updated to requested checkbox structure) -->
                            <div class="col-md-6">
                                <label class="form-label d-block">Subject</label>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="subjects[]"
                                         id="subjectPhp" value="php" />
                                    <label class="form-check-label" for="subjectPhp">PHP</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="subjects[]"
                                         id="subjectJava"
                                        value="java" />
                                    <label class="form-check-label" for="subjectJava">Java</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="subjects[]"
                                         id="subjectLaravel"
                                        value="laravel" />
                                    <label class="form-check-label" for="subjectLaravel">Laravel</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="subjects[]"
                                         id="subjectKotlin"
                                        value="kotlin" />
                                    <label class="form-check-label" for="subjectKotlin">Kotlin</label>
                                </div>

                                <div class="invalid-feedback">Please select at least one subject.</div>
                            </div>


                        </div>

                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                            <a class="btn btn-outline-secondary" href="users.html">Cancel</a>
                            <button class="btn btn-primary" type="submit"><i class="bi bi-person-check"
                                    aria-hidden="true"></i> Create User</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
@endsection()
