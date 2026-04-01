@extends('layout')
@section('title')
    <title>Service Bookings</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style=" background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }}) "></div>
        <div class="container"><div class="col-lg-12"><div class="inner-banner-df"><h1 class="inner-banner-taitel">Service Bookings</h1></div></div></div>
    </section>

    <section class="dashboard">
        <div class="container">
            <div class="row">
                @include('profile.sidebar')
                <div class="col-lg-9">
                    <div class="manage-car two">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        <h5 class="mb-3">My Bengkel Bookings</h5>
                        <div class="car_list_table">
                            <table class="table two">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Garage</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bookings as $booking)
                                        <tr>
                                            <td>{{ $booking->order_id }}</td>
                                            <td>{{ optional($booking->garage)->name }}</td>
                                            <td>{{ optional($booking->service)->name }}</td>
                                            <td>{{ $booking->booking_date?->format('d M Y') }} {{ $booking->booking_time }}</td>
                                            <td>{{ strtoupper($booking->status) }}</td>
                                            <td>{{ currency($booking->total_price) }}</td>
                                            <td>
                                                <a href="{{ route('user.service-bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                                @if(in_array($booking->status, ['pending', 'confirmed']))
                                                    <form action="{{ route('user.service-bookings.cancel', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this booking?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center">No service bookings found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $bookings->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('profile.logout')
</main>
@endsection

