@extends('layout')
@section('title')
    <title>My Communities</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style=" background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }}) "></div>
        <div class="container"><div class="col-lg-12"><div class="inner-banner-df"><h1 class="inner-banner-taitel">My Communities</h1></div></div></div>
    </section>

    <section class="dashboard">
        <div class="container">
            <div class="row">
                @include('profile.sidebar')
                <div class="col-lg-9">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="mb-3">
                        <a href="{{ route('user.communities.create') }}" class="btn btn-primary btn-sm">Create Community</a>
                    </div>
                    <div class="row g-4">
                        @forelse($communities as $community)
                            <div class="col-md-6">
                                <div class="dealer-item position-relative">
                                    <a href="{{ route('community.show', $community->slug) }}" class="stretched-link" aria-label="{{ $community->name }}"></a>
                                    <div class="dealer-img">
                                        <img src="{{ getImageOrPlaceholder($community->cover_image ?: $community->image, '320x217') }}" alt="{{ $community->name }}">
                                    </div>
                                    <div class="dealer-content p-3">
                                        <h5>{{ $community->name }}</h5>
                                        <p class="mb-1">{{ \Illuminate\Support\Str::limit(strip_tags($community->description), 90) }}</p>
                                        <p class="mb-0">
                                            <strong>Members:</strong> {{ $community->members_count }}
                                            | <strong>Posts:</strong> {{ $community->posts_count }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><div class="alert alert-info mb-0">You have not joined any community yet.</div></div>
                        @endforelse
                    </div>
                    <div class="mt-3">{{ $communities->links() }}</div>
                </div>
            </div>
        </div>
    </section>
    @include('profile.logout')
</main>
@endsection

