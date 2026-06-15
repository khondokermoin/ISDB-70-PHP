@extends('backend.master')

{{-- style css urls --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('') }}assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('') }}assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('') }}assets/css/style.css">
@endpush()

{{-- scripts url --}}
@push('scripts')
    <script src="{{ asset('') }}assets/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('') }}assets/js/main.js"></script>
@endpush()

{{-- scontent --}}
@section('content')
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Overview</p>
                        <h1 class="h3 mb-1">Dashboard</h1>
                        <p class="text-muted mb-0">Monitor performance, sales, users, and support from one
                            clean workspace.</p>
                    </div>
                </div>
                <div class="heading-actions"><button class="btn btn-outline-secondary btn-sm" type="button"><i
                            class="bi bi-download" aria-hidden="true"></i>
                        Export</button><button class="btn btn-primary btn-sm" type="button"><i
                            class="bi bi-file-earmark-plus" aria-hidden="true"></i> Create Report</button>
                </div>
            </div>

            <section class="row g-3 mt-1" aria-label="Dashboard metrics">
                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-primary">
                        <div class="metric-top">
                            <span class="metric-label">Revenue</span>
                            <span class="metric-icon"><i class="bi bi-currency-dollar" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">$48,240</div>
                        <div class="metric-meta">
                            <span class="text-success">+12.5%</span>
                            <span>from last month</span>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-success">
                        <div class="metric-top">
                            <span class="metric-label">Orders</span>
                            <span class="metric-icon"><i class="bi bi-bag-check" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">1,284</div>
                        <div class="metric-meta">
                            <span class="text-success">+8.2%</span>
                            <span>new orders</span>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-warning">
                        <div class="metric-top">
                            <span class="metric-label">Customers</span>
                            <span class="metric-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">8,742</div>
                        <div class="metric-meta">
                            <span class="text-success">+5.1%</span>
                            <span>active users</span>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <article class="metric-card metric-danger">
                        <div class="metric-top">
                            <span class="metric-label">Tickets</span>
                            <span class="metric-icon"><i class="bi bi-life-preserver" aria-hidden="true"></i></span>
                        </div>
                        <div class="metric-value">36</div>
                        <div class="metric-meta">
                            <span class="text-danger">3 urgent</span>
                            <span>need review</span>
                        </div>
                    </article>
                </div>
            </section>


            <section class="panel mt-3">
                <div class="container">
                    <img class="img-fluid"
                        src="https://img.magnific.com/free-vector/stylish-welcome-lettering-banner-join-with-joy-happiness_1017-57675.jpg?t=st=1781502311~exp=1781505911~hmac=fa76fda1f817cd7220bf1ba360e13570ee7ce93060d647701e52b3c3c7534190&w=740"
                        alt="">
                </div>
            </section>
        </div>
    </main>
@endsection()
