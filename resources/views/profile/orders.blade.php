@extends('layout')
@section('title')
    <title>Orders</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style=" background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }})"></div>
        <div class="container">
            <div class="col-lg-12">
                <div class="inner-banner-df">
                    <h1 class="inner-banner-taitel">Orders</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard">
        <div class="container">
            <div class="row">
                @include('profile.sidebar')
                <div class="col-lg-9">
                    <div class="manage-car two p-4 shadow-sm border-0">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a href="{{ route('user.orders', ['tab' => 'showroom']) }}" class="btn btn-sm {{ $tab === 'showroom' ? 'btn-primary' : 'btn-outline-primary' }}">Showroom Orders</a>
                            <a href="{{ route('user.orders', ['tab' => 'bengkel']) }}" class="btn btn-sm {{ $tab === 'bengkel' ? 'btn-primary' : 'btn-outline-primary' }}">Bengkel Orders</a>
                            <a href="{{ route('user.orders', ['tab' => 'subscription']) }}" class="btn btn-sm {{ $tab === 'subscription' ? 'btn-primary' : 'btn-outline-primary' }}">Subscription</a>
                        </div>

                        @if($tab === 'showroom')
                            <h6 class="mb-3">Showroom Orders</h6>
                            <div class="table-responsive">
                                <table class="table two">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Car</th>
                                            <th>Showroom</th>
                                            <th>Leasing Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($showroom_orders as $o)
                                            <tr>
                                                <td>{{ $o->order_id }}</td>
                                                <td>{{ optional($o->car)->title ?: '-' }}</td>
                                                <td>{{ optional($o->showroom)->name ?: optional($o->seller)->name ?: '-' }}</td>
                                                <td>
                                                    <span class="badge {{ ($o->leasing_status === 'approved') ? 'bg-success' : (($o->leasing_status === 'rejected') ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                        {{ strtoupper($o->leasing_status ?: 'pending') }}
                                                    </span>
                                                </td>
                                                <td>{{ currency($o->down_payment ?: $o->total_price ?: 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center">No showroom orders found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div>{{ $showroom_orders->appends(['tab' => 'showroom'])->links() }}</div>
                        @elseif($tab === 'bengkel')
                            <h6 class="mb-3">Bengkel Orders</h6>
                            <div class="table-responsive">
                                <table class="table two">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Garage</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($service_orders as $o)
                                            <tr>
                                                <td><a href="{{ route('user.service-bookings.show', $o->id) }}">{{ $o->order_id }}</a></td>
                                                <td>{{ optional($o->garage)->name }}</td>
                                                <td>{{ optional($o->service)->name }}</td>
                                                <td>{{ $o->booking_date?->format('d M Y') }} {{ $o->booking_time }}</td>
                                                <td>
                                                    <span class="badge {{ in_array($o->status, ['completed']) ? 'bg-success' : (in_array($o->status, ['cancelled']) ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                        {{ strtoupper($o->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ currency($o->total_price) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center">No bengkel orders found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div>{{ $service_orders->appends(['tab' => 'bengkel'])->links() }}</div>
                        @else
                            <h6 class="mb-3">Subscription History</h6>
                            <div class="table-responsive">
                                <table class="table two">
                                    <thead>
                                        <tr>
                                            <th>Package</th>
                                            <th>Expiration</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($histories as $history)
                                            <tr>
                                                <td>{{ $history->plan_name }}</td>
                                                <td>{{ $history->expiration }}</td>
                                                <td>
                                                    <span class="badge {{ $history->status === 'active' ? 'bg-success' : ($history->status === 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                        {{ strtoupper($history->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ currency($history->plan_price) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center">No subscription history found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('profile.logout')
</main>
@endsection
