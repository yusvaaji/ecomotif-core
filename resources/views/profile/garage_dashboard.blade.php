@extends('layout')
@section('title')
    <title>Garage Dashboard</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style=" background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }}) "></div>
        <div class="container"><div class="col-lg-12"><div class="inner-banner-df"><h1 class="inner-banner-taitel">Garage Dashboard</h1></div></div></div>
    </section>

    <section class="dashboard">
        <div class="container">
            <div class="row">
                @include('profile.sidebar')
                <div class="col-lg-9 garage-dashboard">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row g-3 mb-4 garage-kpi">
                        <div class="col-md-3"><div class="p-3 border rounded shadow-sm"><small>Total Services</small><h4 class="mb-0">{{ $totalServices }}</h4></div></div>
                        <div class="col-md-3"><div class="p-3 border rounded shadow-sm"><small>Total Bookings</small><h4 class="mb-0">{{ $totalBookings }}</h4></div></div>
                        <div class="col-md-3"><div class="p-3 border rounded shadow-sm"><small>Pending</small><h4 class="mb-0">{{ $pendingBookings }}</h4></div></div>
                        <div class="col-md-3"><div class="p-3 border rounded shadow-sm"><small>Completed</small><h4 class="mb-0">{{ $completedBookings }}</h4></div></div>
                    </div>

                    <div class="manage-car two p-4 mb-4">
                        <h5 class="mb-3">Add Service</h5>
                        <form method="POST" action="{{ route('user.garage.services.store') }}" enctype="multipart/form-data" class="row g-2">
                            @csrf
                            <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Service name" required></div>
                            <div class="col-md-2"><input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="Price" required></div>
                            <div class="col-md-3"><input type="text" name="duration" class="form-control" placeholder="Duration"></div>
                            <div class="col-md-3"><input type="file" name="image" class="form-control" accept="image/*"></div>
                            <div class="col-12"><textarea name="description" rows="2" class="form-control" placeholder="Description"></textarea></div>
                            <div class="col-12"><button class="btn btn-primary btn-sm" type="submit">Save service</button></div>
                        </form>
                    </div>

                    <div class="manage-car two p-4 mb-4">
                        <h5 class="mb-3">My Services</h5>
                        <div class="table-responsive">
                            <table class="table two">
                                <thead><tr><th>Name</th><th>Price</th><th>Duration</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody>
                                @forelse($services as $s)
                                    <tr>
                                        <td>{{ $s->name }}</td>
                                        <td>{{ currency($s->price) }}</td>
                                        <td>{{ $s->duration ?: '-' }}</td>
                                        <td>{{ strtoupper($s->status) }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('user.garage.services.status', $s->id) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $s->status === 'active' ? 'inactive' : 'active' }}">
                                                <button class="btn btn-sm btn-outline-secondary" type="submit">
                                                    {{ $s->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('user.garage.services.delete', $s->id) }}" class="d-inline" onsubmit="return confirm('Delete this service?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">No services yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div>{{ $services->links() }}</div>
                    </div>

                    <div class="manage-car two p-4">
                        <h5 class="mb-3">Booking Requests</h5>
                        <div class="table-responsive">
                            <table class="table two">
                                <thead><tr><th>Order</th><th>Customer</th><th>Service</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody>
                                @forelse($bookings as $b)
                                    <tr>
                                        <td>{{ $b->order_id }}</td>
                                        <td>{{ optional($b->customer)->name ?: $b->customer_name }}</td>
                                        <td>{{ optional($b->service)->name }}</td>
                                        <td>{{ strtoupper($b->status) }}</td>
                                        <td>
                                            @if(in_array($b->status, ['pending', 'confirmed', 'in_progress']))
                                                <form method="POST" action="{{ route('user.garage.bookings.status', $b->id) }}" class="d-flex gap-1 flex-wrap">
                                                    @csrf
                                                    <select name="status" class="form-select form-select-sm">
                                                        @if($b->status === 'pending')
                                                            <option value="confirmed">Confirm</option>
                                                            <option value="cancelled">Cancel</option>
                                                        @elseif($b->status === 'confirmed')
                                                            <option value="in_progress">In Progress</option>
                                                            <option value="cancelled">Cancel</option>
                                                        @elseif($b->status === 'in_progress')
                                                            <option value="completed">Complete</option>
                                                        @endif
                                                    </select>
                                                    <input type="text" name="garage_notes" class="form-control form-control-sm" placeholder="Optional note">
                                                    <button class="btn btn-sm btn-outline-primary" type="submit">Update</button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">No bookings yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div>{{ $bookings->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('profile.logout')
</main>
@endsection
