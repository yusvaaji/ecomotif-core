@extends('layout')
@section('title')
    <title>Garages</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style="background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }})"></div>
        <div class="container">
            <div class="col-lg-12">
                <div class="inner-banner-df">
                    <h1 class="inner-banner-taitel">Garages</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Garages</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="inventory feature-two py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0">Discover Garages</h5>
                <span class="badge bg-light text-dark border">{{ $garages->total() }} results</span>
            </div>
            <form method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search garage, specialization, location">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Search</button>
                    </div>
                </div>
            </form>

            <div class="row g-4">
                @forelse($garages as $garage)
                    <div class="col-lg-4 col-md-6">
                        <div class="dealer-item position-relative shadow-sm border-0">
                            <a href="{{ route('garage.show', $garage->id) }}" class="stretched-link" aria-label="{{ $garage->name }}"></a>
                            <div class="dealer-img">
                                <img src="{{ getImageOrPlaceholder($garage->image, '320x217') }}" alt="{{ $garage->name }}">
                            </div>
                            <div class="dealer-content p-3">
                                <h5>{{ $garage->name }}</h5>
                                <p class="mb-1"><strong>Specialization:</strong> {{ $garage->specialization ?: '-' }}</p>
                                <p class="mb-1"><strong>Address:</strong> {{ $garage->address ?: '-' }}</p>
                                <p class="mb-0"><strong>Services:</strong> {{ $garage->garage_services_count }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info mb-0">No garages found.</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $garages->withQueryString()->links() }}
            </div>
        </div>
    </section>
</main>
@endsection

