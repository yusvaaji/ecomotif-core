@extends('layout')
@section('title')
    <title>Booking Detail</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style=" background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }}) "></div>
        <div class="container"><div class="col-lg-12"><div class="inner-banner-df"><h1 class="inner-banner-taitel">Booking Detail</h1></div></div></div>
    </section>

    <section class="dashboard">
        <div class="container">
            <div class="row">
                @include('profile.sidebar')
                <div class="col-lg-9">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="manage-car two p-4 shadow-sm border-0 booking-detail-card">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="mb-0">Order {{ $booking->order_id }}</h5>
                            <span class="badge {{ in_array($booking->status, ['completed']) ? 'bg-success' : (in_array($booking->status, ['cancelled']) ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ strtoupper($booking->status) }}
                            </span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><p class="mb-1"><strong>Garage:</strong> {{ optional($booking->garage)->name }}</p></div>
                            <div class="col-md-6"><p class="mb-1"><strong>Service:</strong> {{ optional($booking->service)->name }}</p></div>
                            <div class="col-md-6"><p class="mb-1"><strong>Date:</strong> {{ $booking->booking_date?->format('d M Y') }} {{ $booking->booking_time }}</p></div>
                            <div class="col-md-6"><p class="mb-1"><strong>Total:</strong> {{ currency($booking->total_price) }}</p></div>
                            <div class="col-md-6"><p class="mb-1"><strong>Customer:</strong> {{ $booking->customer_name }} ({{ $booking->customer_phone }})</p></div>
                            <div class="col-md-6"><p class="mb-1"><strong>Vehicle:</strong> {{ trim(($booking->vehicle_brand ?: '') . ' ' . ($booking->vehicle_model ?: '')) ?: '-' }}</p></div>
                            <div class="col-12"><p class="mb-1"><strong>Address:</strong> {{ $booking->customer_address ?: '-' }}</p></div>
                            <div class="col-12"><p class="mb-1"><strong>Notes:</strong> {{ $booking->notes ?: '-' }}</p></div>
                        </div>

                        @if($booking->garage_notes)
                            <div class="alert alert-secondary mb-3">
                                <strong>Garage note:</strong> {{ $booking->garage_notes }}
                            </div>
                        @endif

                        <a href="{{ route('user.service-bookings') }}" class="btn btn-outline-secondary mt-2">Back</a>
                        @if(in_array($booking->status, ['pending', 'confirmed']))
                            <form action="{{ route('user.service-bookings.cancel', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this booking?');">
                                @csrf
                                <button type="submit" class="btn btn-danger">Cancel booking</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('profile.logout')
</main>
@endsection
