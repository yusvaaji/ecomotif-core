@extends('layout')
@section('title')
    <title>Create Community</title>
@endsection
@section('body-content')
<main>
    <section class="inner-banner">
        <div class="inner-banner-img" style=" background-image: url({{ getImageOrPlaceholder($breadcrumb,'1905x300') }}) "></div>
        <div class="container"><div class="col-lg-12"><div class="inner-banner-df"><h1 class="inner-banner-taitel">Create Community</h1></div></div></div>
    </section>

    <section class="dashboard">
        <div class="container">
            <div class="row">
                @include('profile.sidebar')
                <div class="col-lg-9">
                    <div class="manage-car two p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('user.communities.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Privacy</label>
                                <select name="privacy" class="form-select">
                                    <option value="public" @selected(old('privacy') === 'public')>Public</option>
                                    <option value="private" @selected(old('privacy') === 'private')>Private</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Community image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Cover image</label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Create</button>
                                <a href="{{ route('user.communities') }}" class="btn btn-outline-secondary">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('profile.logout')
</main>
@endsection
