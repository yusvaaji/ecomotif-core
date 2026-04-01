@extends('layout')
@section('title')
    <title>{{ $community->name }} — Community</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style="background-image: url({{ getImageOrPlaceholder($community->cover_image ?: $breadcrumb,'1905x300') }})"></div>
        <div class="container">
            <div class="col-lg-12">
                <div class="inner-banner-df">
                    <h1 class="inner-banner-taitel">{{ $community->name }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('communities') }}">Communities</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($community->name, 40) }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="inventory feature-two py-5">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="dealer-item shadow-sm border-0">
                        <div class="dealer-img">
                            <img src="{{ getImageOrPlaceholder($community->image ?: $community->cover_image, '400x300') }}" alt="{{ $community->name }}">
                        </div>
                        <div class="dealer-content p-3">
                            <p class="mb-2">{{ $community->description ? strip_tags($community->description) : '' }}</p>
                            <p class="mb-1"><strong>Location:</strong> {{ $community->location ?: '-' }}</p>
                            <p class="mb-1"><strong>Privacy:</strong> {{ $community->privacy }}</p>
                            <p class="mb-1"><strong>Members:</strong> {{ $community->members_count }}</p>
                            <p class="mb-1"><strong>Posts:</strong> {{ $community->posts_count }}</p>
                            <p class="mb-0"><strong>Owner:</strong> {{ optional($community->owner)->name }}</p>
                        </div>
                    </div>

                    <h6 class="mt-4 mb-2">Recent members</h6>
                    <ul class="list-unstyled small">
                        @forelse($latestMembers as $lm)
                            <li class="mb-1">{{ optional($lm->user)->name ?? 'Member' }}</li>
                        @empty
                            <li class="text-muted">No members yet.</li>
                        @endforelse
                    </ul>

                    @auth('web')
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @if($isMember)
                                <form method="post" action="{{ route('user.communities.leave', $community->slug) }}" class="d-inline" onsubmit="return confirm('Leave this community?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Leave</button>
                                </form>
                            @else
                                <form method="post" action="{{ route('user.communities.join', $community->slug) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Join community</button>
                                </form>
                            @endif
                            <a href="{{ route('user.communities') }}" class="btn btn-outline-secondary btn-sm">My communities</a>
                            <a href="{{ route('user.communities.create') }}" class="btn btn-outline-primary btn-sm">Create community</a>
                        </div>
                    @else
                        <p class="text-muted small mt-3 mb-0"><a href="{{ route('login') }}">Log in</a> to join or post.</p>
                    @endauth
                </div>

                <div class="col-lg-8">
                    @auth('web')
                        @if($isMember)
                            <h5 class="mb-3">New post</h5>
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form method="post" action="{{ route('user.communities.posts.store', $community->slug) }}" enctype="multipart/form-data" class="mb-5">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="content" class="form-control" rows="4" placeholder="Write something…" required>{{ old('content') }}</textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Image (optional)</label>
                                    <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Publish</button>
                            </form>
                        @endif
                    @endauth

                    <h5 class="mb-3">Feed</h5>
                    @forelse($posts as $post)
                        <div class="card mb-3 shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <strong>{{ optional($post->author)->name }}</strong>
                                    <span class="text-muted small ms-2">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mb-2">{!! nl2br(e($post->content)) !!}</p>
                                @if($post->image)
                                    <img src="{{ getImageOrPlaceholder($post->image, '800x400') }}" alt="" class="img-fluid rounded" style="max-height: 280px;">
                                @endif
                                <p class="small text-muted mb-0 mt-2">{{ $post->comments_count }} comments</p>
                                @if($post->comments->isNotEmpty())
                                    <div class="mt-2 small">
                                        @foreach($post->comments as $c)
                                            <div class="border-top pt-2 mt-2">
                                                <strong>{{ optional($c->author)->name }}:</strong> {{ $c->content }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @auth('web')
                                    @if($isMember)
                                        <form method="POST" action="{{ route('user.communities.comments.store', $post->id) }}" class="mt-2">
                                            @csrf
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="content" class="form-control" placeholder="Write a comment..." required>
                                                <button type="submit" class="btn btn-outline-primary">Comment</button>
                                            </div>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info mb-0">No posts yet.</div>
                    @endforelse

                    <div class="mt-3">{{ $posts->links() }}</div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
