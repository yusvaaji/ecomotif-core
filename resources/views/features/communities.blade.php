@extends('layout')
@section('title')
    <title>Communities</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style="background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }})"></div>
        <div class="container">
            <div class="col-lg-12">
                <div class="inner-banner-df">
                    <h1 class="inner-banner-taitel">Communities</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Communities</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="inventory feature-two py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0">Discover Communities</h5>
                <span class="badge bg-light text-dark border">{{ $communities->total() }} results</span>
            </div>
            <form method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search community, description, location">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Search</button>
                    </div>
                </div>
            </form>

            <div class="row g-4">
                @forelse($communities as $community)
                    <div class="col-lg-4 col-md-6">
                        <div class="dealer-item position-relative shadow-sm border-0">
                            <a href="{{ route('community.show', $community->slug) }}" class="stretched-link" aria-label="{{ $community->name }}"></a>
                            <div class="dealer-img">
                                <img src="{{ getImageOrPlaceholder($community->cover_image ?: $community->image, '320x217') }}" alt="{{ $community->name }}">
                            </div>
                            <div class="dealer-content p-3">
                                <h5>{{ $community->name }}</h5>
                                <p class="mb-1">{{ \Illuminate\Support\Str::limit(strip_tags($community->description), 90) }}</p>
                                <p class="mb-1"><strong>Members:</strong> {{ $community->members_count }}</p>
                                <p class="mb-1"><strong>Posts:</strong> {{ $community->posts_count }}</p>
                                <p class="mb-0"><strong>Owner:</strong> {{ optional($community->owner)->name }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info mb-0">No communities found.</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $communities->withQueryString()->links() }}
            </div>
        </div>
    </section>
</main>
@endsection

