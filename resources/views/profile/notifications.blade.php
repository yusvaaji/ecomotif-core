@extends('layout')
@section('title')
    <title>Notifications</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style=" background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }}) "></div>
        <div class="container"><div class="col-lg-12"><div class="inner-banner-df"><h1 class="inner-banner-taitel">Notifications</h1></div></div></div>
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
                        <h5 class="mb-3">Latest Notifications</h5>
                        <form action="{{ route('user.notifications.read-all') }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Mark all as read</button>
                        </form>
                        <div class="list-group">
                            @forelse($notifications as $n)
                                <div class="list-group-item {{ $n->is_read ? '' : 'bg-light' }}">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $n->title }}</strong>
                                        <small>{{ $n->created_at?->diffForHumans() }}</small>
                                    </div>
                                    <div>{{ $n->message }}</div>
                                    @if(!$n->is_read)
                                        <div class="mt-2">
                                            <span class="badge bg-primary">Unread</span>
                                            <form action="{{ route('user.notifications.read', $n->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-link">Mark as read</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="alert alert-info mb-0">No notifications available.</div>
                            @endforelse
                        </div>
                        <div class="mt-3">{{ $notifications->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('profile.logout')
</main>
@endsection

