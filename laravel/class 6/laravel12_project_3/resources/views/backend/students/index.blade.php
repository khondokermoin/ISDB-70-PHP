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
                    <span class="page-icon"><i class="bi bi-table" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Data</p>
                        <h1 class="h3 mb-1">Tables</h1>
                        <p class="text-muted mb-0">Use responsive, searchable tables for operational records.</p>
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

                <!-- Pushed to the right with an icon -->
                <div class="d-flex justify-content-end mb-3">
                    <a class="btn btn-outline-secondary" href="{{ url('/students/create') }}">
                        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> New Student
                    </a>
                </div>
                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Advanced
                                Table</span></h2>
                        <p class="text-muted mb-0">Searchable responsive table for orders and customer data.</p>
                    </div><input class="form-control form-control-sm table-search" type="search"
                        placeholder="Search orders" data-table-search="ordersTable" aria-label="Search orders">
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="ordersTable" data-searchable-table>
                        <thead>
                            <tr>
                                <th class="text-center">Id</th>
                                <th class="text-center">Name</th>
                                <th class="text-center">Photo</th>
                                <th class="text-center">Gender</th>
                                <th class="text-center">Phone</th>
                                <th class="text-center">Email</th>
                                <th class="text-center">Subject</th>
                                <th class="text-center">District</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                <tr>
                                    <td class="text-center">{{ $student->id }}</td>
                                    <td class="text-center">{{ $student->name }}</td>
                                    <td class="text-center">
                                        <div class="table-center">
                                            <img class="product-thumb" src="{{ $student->photo }}"
                                                alt="{{ $student->name }}" />
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $student->gender }}</td>
                                    <td class="text-center">{{ $student->phone }}</td>
                                    <td class="text-center">{{ $student->email }}</td>
                                    <td class="text-center">{{ $student->subjects }}</td>
                                    <td class="text-center">{{ $student->district }}</td>
                                    <td class="text-center">
                                        <!-- View Button: নির্দিষ্ট স্টুডেন্টের বিস্তারিত দেখার জন্য -->
                                        <a href="{{ route('students.show', $student->id) }}"
                                            class="btn btn-outline-info btn-sm">
                                            View
                                        </a>

                                        <!-- Edit Button: এডিট পেজে যাওয়ার জন্য -->
                                        <a href="{{ route('students.edit', $student->id) }}"
                                            class="btn btn-outline-primary btn-sm">
                                            Edit
                                        </a>

                                        <!-- Delete Button: ফর্ম প্রোটেকশন সহ -->
                                        <form action="{{ route('students.destroy', $student->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf

                                            <button class="btn btn-outline-danger btn-sm" type="submit"
                                                onclick="return confirm('Are you sure you want to delete this student?');">
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
