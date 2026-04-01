@extends('layout')
@section('title')
    <title>{{ $garage->name }} — Garage</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style="background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }})"></div>
        <div class="container">
            <div class="col-lg-12">
                <div class="inner-banner-df">
                    <h1 class="inner-banner-taitel">{{ $garage->name }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('garages') }}">Garages</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($garage->name, 40) }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="inventory feature-two py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="dealer-item shadow-sm border-0">
                        <div class="dealer-img">
                            <img src="{{ getImageOrPlaceholder($garage->image, '400x300') }}" alt="{{ $garage->name }}">
                        </div>
                        <div class="dealer-content p-3">
                            <p class="mb-1"><strong>Specialization:</strong> {{ $garage->specialization ?: '-' }}</p>
                            <p class="mb-1"><strong>Address:</strong> {{ $garage->address ?: '-' }}</p>
                            @if($garage->phone)
                                <p class="mb-1"><strong>Phone:</strong> {{ $garage->phone }}</p>
                            @endif
                            @if($garage->email)
                                <p class="mb-0"><strong>Email:</strong> {{ $garage->email }}</p>
                            @endif
                            @if($garage->reviews_avg_rating)
                                <p class="mb-0 mt-2"><strong>Avg. rating:</strong> {{ number_format((float) $garage->reviews_avg_rating, 1) }} ({{ $garage->reviews_count }} {{ $garage->reviews_count === 1 ? 'review' : 'reviews' }})</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <h5 class="mb-3">Services</h5>
                    <div class="table-responsive">
                        <table class="table two">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Price</th>
                                    <th>Duration
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($garage->garageServices as $svc)
                                    <tr>
                                        <td>
                                            <strong>{{ $svc->name }}</strong>
                                            @if($svc->description)
                                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($svc->description), 120) }}</div>
                                            @endif
                                        </td>
                                        <td>{{ currency($svc->price) }}</td>
                                        <td>{{ $svc->duration ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No active services listed yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @auth('web')
                        @if($garage->garageServices->isNotEmpty())
                            <h5 class="mb-3 mt-4">Book a service</h5>
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form method="post" action="{{ route('user.service-bookings.store') }}" class="row g-3">
                                @csrf
                                <input type="hidden" name="garage_id" value="{{ $garage->id }}">

                                <div class="col-md-6">
                                    <label class="form-label">Service</label>
                                    <select name="garage_service_id" class="form-select" required>
                                        <option value="">Select…</option>
                                        @foreach($garage->garageServices as $svc)
                                            <option value="{{ $svc->id }}" @selected(old('garage_service_id') == $svc->id)>{{ $svc->name }} — {{ currency($svc->price) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Service type</label>
                                    <select name="service_type" class="form-select" required>
                                        <option value="walk_in" @selected(old('service_type') === 'walk_in')>Walk-in</option>
                                        <option value="home_service" @selected(old('service_type') === 'home_service')>Home service</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="booking_date" class="form-control" value="{{ old('booking_date') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Time</label>
                                    <input type="time" name="booking_time" class="form-control" value="{{ old('booking_time') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Your name</label>
                                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', auth()->user()->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address (required for home service)</label>
                                    <input type="text" name="customer_address" class="form-control" value="{{ old('customer_address') }}" placeholder="Use full address for home service">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Vehicle brand</label>
                                    <input type="text" name="vehicle_brand" class="form-control" value="{{ old('vehicle_brand') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Model</label>
                                    <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Plate</label>
                                    <input type="text" name="vehicle_plate" class="form-control" value="{{ old('vehicle_plate') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Submit booking</button>
                                    <a href="{{ route('user.service-bookings') }}" class="btn btn-outline-secondary ms-2">My bookings</a>
                                </div>
                            </form>
                        @endif
                    @else
                        <div class="alert alert-info mt-3 mb-0">
                            <a href="{{ route('login') }}">Log in</a> to book a service online.
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
