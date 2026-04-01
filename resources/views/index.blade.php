@extends('layout')
@section('title')
    <title>{{ $seo_setting ? $seo_setting->seo_title : 'ECOMOTIF - Teman Bisnis Showroom Mobil' }}</title>
    <meta name="title" content="{{ $seo_setting ? $seo_setting->seo_title : 'ECOMOTIF' }}">
    <meta name="description" content="{{ $seo_setting ? strip_tags(clean($seo_setting->seo_description)) : 'ECOMOTIF - Platform terpercaya untuk bisnis showroom mobil' }}">
@endsection

@section('body-content')
    <main>

        <!-- HERO SECTION - Broom Style with Video Background -->
        <section class="ecomotif-hero-video">
            <!-- Video Background -->
            <div class="hero-video-wrapper">
                @php
                    $heroVideo = $homepage->hero_video ?? $homepage->video_id ?? null;
                    $isVideoUrl = $heroVideo && (strpos($heroVideo, 'http') === 0 || strpos($heroVideo, '//') === 0);
                    $isVideoFile = $heroVideo && !$isVideoUrl && file_exists(public_path($heroVideo));
                @endphp
                
                @if($heroVideo && ($isVideoFile || $isVideoUrl))
                    <div class="hero-video-container" id="heroVideoContainer">
                        @if($isVideoFile)
                            <video id="heroVideo" class="hero-video" autoplay muted loop playsinline>
                                <source src="{{ asset($heroVideo) }}" type="video/mp4">
                            </video>
                        @else
                            {{-- YouTube/Vimeo Embed --}}
                            @php
                                $videoId = '';
                                if(strpos($heroVideo, 'youtube.com/watch') !== false || strpos($heroVideo, 'youtu.be/') !== false) {
                                    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $heroVideo, $matches);
                                    $videoId = $matches[1] ?? '';
                                    $embedUrl = 'https://www.youtube.com/embed/' . $videoId . '?autoplay=1&mute=1&loop=1&playlist=' . $videoId . '&controls=0&showinfo=0&rel=0&iv_load_policy=3';
                                } elseif(strpos($heroVideo, 'vimeo.com/') !== false) {
                                    preg_match('/vimeo\.com\/(\d+)/', $heroVideo, $matches);
                                    $videoId = $matches[1] ?? '';
                                    $embedUrl = 'https://player.vimeo.com/video/' . $videoId . '?autoplay=1&muted=1&loop=1&background=1';
                                } else {
                                    $embedUrl = $heroVideo;
                                }
                            @endphp
                            @if($videoId)
                                <iframe id="heroVideo" class="hero-video" src="{{ $embedUrl }}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen style="width: 100%; height: 100%; object-fit: cover;"></iframe>
                            @else
                                <video id="heroVideo" class="hero-video" autoplay muted loop playsinline>
                                    <source src="{{ $heroVideo }}" type="video/mp4">
                                </video>
                            @endif
                        @endif
                        <div class="hero-video-overlay"></div>
                    </div>
                @else
                    <div class="hero-video-placeholder">
                        <img src="{{ getImageOrPlaceholder($homepage->home1_intro_image ?? $homepage->video_bg_image ?? '', '1920x1080') }}" 
                             alt="ECOMOTIF Hero" class="hero-placeholder-image">
                        <div class="hero-video-overlay"></div>
                    </div>
                @endif
                
                <!-- Play Button - Only show for video files, not YouTube/Vimeo -->
                @if($heroVideo && $isVideoFile)
                <div class="hero-play-button" id="heroPlayButton">
                    <div class="play-icon-wrapper">
                        <i class="fas fa-play"></i>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Text Overlay - Centered like Broom.id -->
            <div class="hero-text-overlay">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-10 text-center">
                            <div class="hero-overlay-content" data-aos="fade-up" data-aos-delay="200">
                                <span class="hero-overlay-badge">{{ $homepage->home1_intro_short_title ?? 'Teman Bisnis Showroom' }}</span>
                                <h1 class="hero-overlay-title">{{ $homepage->home1_intro_title ?? 'LAYANAN UNTUK SHOWROOM' }}</h1>
                                <p class="hero-overlay-description">
                                    {{ $homepage->home1_intro_description ?? 'ECOMOTIF bantu wujudkan mimpi sukses bisnis showroom' }}
                                </p>
                                <div class="hero-overlay-cta mt-4">
                                    <a href="{{ route('listings') }}" class="btn-ecomotif-hero">
                                        <i class="fas fa-search me-2"></i> {{ __('translate.Browse Vehicle') }}
                                    </a>
                                    <a href="{{ route('dealers') }}" class="btn-ecomotif-hero-outline">
                                        <i class="fas fa-store me-2"></i> {{ __('translate.Find Showroom') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                
                <!-- Search Bar - HIDDEN -->
                <div class="row justify-content-center mt-5" style="display: none;">
                    <div class="col-xxl-10 col-xl-12 col-lg-12">
                        <div class="banner-search-bar" data-aos="fade-up" data-aos-delay="300">
                            <ul class="nav nav-pills" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pills-home-tab1" data-bs-toggle="pill"
                                        data-bs-target="#pills-home1" type="button" role="tab"
                                        aria-controls="pills-home1"
                                        aria-selected="true">{{ __('translate.All Car') }}</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-profile-tab1" data-bs-toggle="pill"
                                        data-bs-target="#pills-profile1" type="button" role="tab"
                                        aria-controls="pills-profile1"
                                        aria-selected="false">{{ __('translate.Used Car') }}</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-contact-tab1" data-bs-toggle="pill"
                                        data-bs-target="#pills-contact1" type="button" role="tab"
                                        aria-controls="pills-contact1"
                                        aria-selected="false">{{ __('translate.New Car') }}</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="pills-tabContent1">
                                <div class="tab-pane fade show active" id="pills-home1" role="tabpanel"
                                    aria-labelledby="pills-home-tab1">
                                    <div class="banner-sarchber-box">
                                        <form class="banner-sarchber-box-item" action="{{ route('listings') }}">
                                            <div class="banner-sarchber-box-inner">
                                                <span class="icon">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                                <input type="text" class="form-control"
                                                    placeholder="{{ __('translate.Type here') }}" name="search">
                                            </div>

                                            <div class="banner-sarchber-box-inner">
                                                <span class="icon">
                                                    <i class="fas fa-car"></i>
                                                </span>
                                                <select class="form-select form-select-lg" name="brands[]">
                                                    <option selected value="">{{ __('translate.Select Brand') }}</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="banner-sarchber-box-inner">
                                                <span class="icon">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>
                                                <select class="form-select form-select-lg" name="country">
                                                    <option value="">{{ __('translate.Country') }}</option>
                                                    @foreach ($countries as $country)
                                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="banner-sarchber-box-inner two">
                                                <button type="submit" class="sarchber-box-btn">
                                                    <i class="fas fa-search"></i>
                                                    <span class="search-text1">{{ __('translate.Search') }}</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Used Car Tab -->
                                <div class="tab-pane fade" id="pills-profile1" role="tabpanel" aria-labelledby="pills-profile-tab1">
                                    <div class="banner-sarchber-box">
                                        <form class="banner-sarchber-box-item" action="{{ route('listings') }}">
                                            <input type="hidden" name="condition" value="used">
                                            <div class="banner-sarchber-box-inner">
                                                <span class="icon"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" placeholder="{{ __('translate.Type here') }}" name="search">
                                            </div>
                                            <div class="banner-sarchber-box-inner">
                                                <span class="icon"><i class="fas fa-car"></i></span>
                                                <select class="form-select form-select-lg" name="brands[]">
                                                    <option selected value="">{{ __('translate.Select Brand') }}</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="banner-sarchber-box-inner two">
                                                <button type="submit" class="sarchber-box-btn">
                                                    <i class="fas fa-search"></i>
                                                    <span class="search-text1">{{ __('translate.Search') }}</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- New Car Tab -->
                                <div class="tab-pane fade" id="pills-contact1" role="tabpanel" aria-labelledby="pills-contact-tab1">
                                    <div class="banner-sarchber-box">
                                        <form class="banner-sarchber-box-item" action="{{ route('listings') }}">
                                            <input type="hidden" name="condition" value="new">
                                            <div class="banner-sarchber-box-inner">
                                                <span class="icon"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" placeholder="{{ __('translate.Type here') }}" name="search">
                                            </div>
                                            <div class="banner-sarchber-box-inner">
                                                <span class="icon"><i class="fas fa-car"></i></span>
                                                <select class="form-select form-select-lg" name="brands[]">
                                                    <option selected value="">{{ __('translate.Select Brand') }}</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="banner-sarchber-box-inner two">
                                                <button type="submit" class="sarchber-box-btn">
                                                    <i class="fas fa-search"></i>
                                                    <span class="search-text1">{{ __('translate.Search') }}</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- DEALER SEARCH SECTION -->
        <section class="ecomotif-dealer-search py-5" style="background: var(--bg-light);">
            <div class="container">
                <div class="section-title text-center mb-4" data-aos="fade-up">
                    <span>{{ __('translate.Find Showroom') }}</span>
                    <h2>{{ __('translate.Search for Dealers') }}</h2>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="banner-search-bar" data-aos="fade-up" data-aos-delay="100">
                            <div class="banner-sarchber-box">
                                <form class="banner-sarchber-box-item" action="{{ route('dealers') }}">
                                    <div class="banner-sarchber-box-inner">
                                        <span class="icon">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" class="form-control"
                                            placeholder="{{ __('translate.Search Dealer Name') }}" name="search">
                                    </div>
                                    <div class="banner-sarchber-box-inner">
                                        <span class="icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                        <select class="form-select form-select-lg" name="country">
                                            <option value="">{{ __('translate.Select Country') }}</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}" {{ ($country->is_default ?? 0) ? 'selected' : '' }}>{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="banner-sarchber-box-inner">
                                        <span class="icon">
                                            <i class="fas fa-motorcycle"></i>
                                        </span>
                                        <select class="form-select form-select-lg" name="vehicle_type">
                                            <option value="">{{ __('translate.Select Vehicle Type') }}</option>
                                            <option value="car">{{ __('translate.Car') }}</option>
                                            <option value="motorcycle">{{ __('translate.Motorcycle') }}</option>
                                        </select>
                                    </div>
                                    <div class="banner-sarchber-box-inner two">
                                        <button type="submit" class="sarchber-box-btn">
                                            <i class="fas fa-search"></i>
                                            <span class="search-text1">{{ __('translate.Search') }}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SERVICES SECTION - Broom Style Cards -->
        <section class="ecomotif-services">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <span>LAYANAN UNTUK SHOWROOM</span>
                    <h2>{{ __('translate.Our Services') }}</h2>
                </div>
                
                <div class="row g-4">
                    <!-- Leasing Service -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="service-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <h3>Leasing Channeling</h3>
                            <p class="subtitle">Proses pengajuan kredit tidak selalu mulus</p>
                            <p>Hubungkan beragam pembiayaan leasing dari satu pintu. Proses cepat dan mudah untuk membantu pelanggan Anda mendapatkan kredit mobil.</p>
                            <a href="{{ route('listings') }}" class="btn-link">
                                {{ __('translate.Learn More') }} <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Mediator Service -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="service-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h3>Layanan Mediator</h3>
                            <p class="subtitle">Perputaran stok mobil lambat</p>
                            <p>Akses jual sementara mobil untuk putar modal belanja showroom. Dengan mediator profesional yang siap membantu transaksi Anda.</p>
                            <a href="{{ route('register') }}" class="btn-link">
                                {{ __('translate.Register Now') }} <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Showroom Partnership -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="service-card">
                            <div class="icon-wrapper">
                                <i class="fas fa-store"></i>
                            </div>
                            <h3>Kemitraan Showroom</h3>
                            <p class="subtitle">Sulit meningkatkan penjualan</p>
                            <p>Bergabung dengan jaringan showroom ECOMOTIF untuk meningkatkan exposure dan penjualan. Dapatkan akses ke ribuan pembeli potensial.</p>
                            <a href="{{ route('dealers') }}" class="btn-link">
                                {{ __('translate.Join Now') }} <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- STATISTICS SECTION -->
        <section class="ecomotif-stats">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                        <div class="stat-item">
                            <div class="stat-number">{{ $homepage->home1_counter_one_value ?? '500+' }}</div>
                            <div class="stat-label">{{ $homepage->home1_counter_one_title ?? 'Showroom Partner' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                        <div class="stat-item">
                            <div class="stat-number">{{ $homepage->home1_counter_two_value ?? '92%' }}</div>
                            <div class="stat-label">{{ $homepage->home1_counter_two_title ?? 'Pelanggan Setia' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                        <div class="stat-item">
                            <div class="stat-number">{{ $homepage->home1_counter_three_value ?? '5x' }}</div>
                            <div class="stat-label">{{ $homepage->home1_counter_three_title ?? 'Peningkatan Transaksi' }}</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                        <div class="stat-item">
                            <div class="stat-number">{{ $homepage->home1_counter_four_value ?? '1000+' }}</div>
                            <div class="stat-label">{{ $homepage->home1_counter_four_title ?? 'Mobil Terdaftar' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURED CARS SECTION -->
        <section class="trending py-120px">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="taitel two text-center" data-aos="fade-up">
                            <span>{{ __('translate.Featured Cars') }}</span>
                            <h2>{{ $homepage->home1_feature_car_title ?? __('translate.Explore Our Vehicles') }}</h2>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-56px">
                    @foreach ($new_cars->take(8) as $car)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}">
                        <div class="trending-card">
                            <div class="trending-card-img">
                                <a href="{{ route('listing.detail', $car->slug) }}">
                                    <img src="{{ getImageOrPlaceholder($car->thumbnail_image, '380x240') }}" alt="{{ $car->title }}">
                                </a>
                                @auth('web')
                                    <span class="heart-icon add_to_wishlist" data-car="{{ $car->id }}">
                                        <i class="fa-regular fa-heart"></i>
                                    </span>
                                @else
                                    <span class="heart-icon before_auth_wishlist">
                                        <i class="fa-regular fa-heart"></i>
                                    </span>
                                @endauth
                                @if($car->condition)
                                    <span class="badge-condition">{{ ucfirst($car->condition) }}</span>
                                @endif
                            </div>
                            <div class="trending-card-inner">
                                <div class="card-info">
                                    <h4>
                                        <a href="{{ route('listing.detail', $car->slug) }}">
                                            {{ Str::limit($car->title, 40) }}
                                        </a>
                                    </h4>
                                    <div class="price">
                                        {{ Session::get('currency_icon') }}{{ number_format(currencyConvert($car->regular_price)) }}
                                    </div>
                                </div>
                                <div class="card-features">
                                    <span><i class="fas fa-tachometer-alt"></i> {{ $car->mileage ?? 'N/A' }}</span>
                                    <span><i class="fas fa-gas-pump"></i> {{ $car->fuel_type ?? 'N/A' }}</span>
                                    <span><i class="fas fa-cog"></i> {{ $car->transmission ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="text-center mt-5" data-aos="fade-up">
                    <a href="{{ route('listings') }}" class="btn-ecomotif">
                        {{ __('translate.View All Cars') }} <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- TESTIMONIALS SECTION -->
        <section class="ecomotif-testimonials">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <span>TESTIMONI</span>
                    <h2>{{ __('translate.What Our Clients Say') }}</h2>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="testimonial-card">
                            <p class="quote">
                                "Alhamdulillah terima kasih ECOMOTIF. Sungguh sangat membantu usaha showroom saya. 
                                Jualan jadi cepat laku dengan harga menarik."
                            </p>
                            <div class="author">
                                <div class="author-avatar">DR</div>
                                <div class="author-info">
                                    <h4>Daris Motor</h4>
                                    <span>Showroom Partner</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="testimonial-card">
                            <p class="quote">
                                "Pakai layanan leasing ECOMOTIF bisa cair duluan, gak pusing cari leasing yang mau nerima. 
                                Semua udah dibantu dari tim ECOMOTIF!"
                            </p>
                            <div class="author">
                                <div class="author-avatar">MM</div>
                                <div class="author-info">
                                    <h4>Mekarsari Mobilindo</h4>
                                    <span>Dealer Partner</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="testimonial-card">
                            <p class="quote">
                                "Saya merasa terbantu dengan ECOMOTIF, lokasi yang strategis dan dibantu jualan 
                                membuat saya berhasil menjual banyak unit mobil."
                            </p>
                            <div class="author">
                                <div class="author-avatar">TI</div>
                                <div class="author-info">
                                    <h4>Tio Auto Gallery</h4>
                                    <span>Showroom Partner</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ SECTION -->
        <section class="ecomotif-faq">
            <div class="container">
                <div class="section-title" data-aos="fade-up">
                    <span>FAQ</span>
                    <h2>{{ __('translate.Frequently Asked Questions') }}</h2>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="faq-item active" data-aos="fade-up" data-aos-delay="100">
                            <div class="faq-question">
                                <h4>Bagaimana cara mendaftarkan showroom saya di ECOMOTIF?</h4>
                                <div class="icon">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="faq-answer">
                                <p>Pendaftaran showroom dapat dilakukan melalui aplikasi ECOMOTIF yang dapat di download di Play Store dan App Store, atau melalui website dengan menu Register dan pilih tipe akun Dealer/Showroom.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-aos="fade-up" data-aos-delay="150">
                            <div class="faq-question">
                                <h4>Apakah ECOMOTIF berlaku untuk semua jenis showroom mobil bekas?</h4>
                                <div class="icon">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="faq-answer">
                                <p>ECOMOTIF dapat dimanfaatkan oleh seluruh jenis showroom mobil bekas, baik showroom rintisan atau rumahan. Kami menyediakan layanan untuk semua skala bisnis.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-aos="fade-up" data-aos-delay="200">
                            <div class="faq-question">
                                <h4>Apa keuntungan menjadi Mediator di ECOMOTIF?</h4>
                                <div class="icon">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="faq-answer">
                                <p>Sebagai Mediator, Anda dapat membantu konsumen mendapatkan mobil impian mereka sambil mendapatkan komisi dari setiap transaksi yang berhasil. Anda juga akan mendapatkan akses ke database showroom partner kami.</p>
                            </div>
                        </div>
                        
                        <div class="faq-item" data-aos="fade-up" data-aos-delay="250">
                            <div class="faq-question">
                                <h4>Bagaimana proses leasing melalui ECOMOTIF?</h4>
                                <div class="icon">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="faq-answer">
                                <p>Proses leasing sangat mudah. Pilih mobil, hitung kemampuan bayar dengan kalkulator kami, ajukan leasing, dan tunggu approval. Tim kami akan membantu proses dari awal hingga akhir.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- DEALERS/SHOWROOM SECTION -->
        @if(isset($dealers) && count($dealers) > 0)
        <section class="dealer py-120px">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="taitel two text-center" data-aos="fade-up">
                            <span>{{ __('translate.Our Partners') }}</span>
                            <h2>{{ __('translate.Trusted Showroom Partners') }}</h2>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-56px">
                    @foreach ($dealers->take(6) as $dealer)
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}">
                        <div class="dealer-item" style="display: flex !important; flex-direction: row !important; align-items: flex-start !important; justify-content: flex-start !important; position: relative !important;">
                            <div class="dealer-img" style="flex-shrink: 0 !important; width: 100px !important; height: 100px !important; margin-right: 20px !important; float: left !important; display: block !important; position: static !important; left: auto !important; right: auto !important; top: auto !important; bottom: auto !important;">
                                <a href="{{ route('dealer', $dealer->username) }}" style="display: block !important; width: 100px !important; height: 100px !important; position: static !important;">
                                    <img src="{{ getImageOrPlaceholder($dealer->image, '100x100') }}" alt="{{ $dealer->name }}" style="width: 100px !important; height: 100px !important; border-radius: 50% !important; object-fit: cover !important; display: block !important; margin: 0 !important; padding: 0 !important; position: static !important; left: auto !important; right: auto !important; top: auto !important; bottom: auto !important;">
                                </a>
                            </div>
                            <div class="dealer-info" style="flex: 1 !important; display: flex !important; flex-direction: column !important; text-align: left !important; overflow: hidden !important; min-width: 0 !important;">
                                <h4 style="text-align: left !important; margin-bottom: 6px !important;"><a href="{{ route('dealer', $dealer->username) }}">{{ $dealer->name }}</a></h4>
                                <p style="text-align: left !important; margin-bottom: 10px !important;">{{ $dealer->designation ?? __('translate.Showroom') }}</p>
                                @if(isset($dealer->average_rating) && $dealer->average_rating > 0)
                                <div class="dealer-rating" style="text-align: left !important; margin-bottom: 10px !important;">
                                    <span class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= round($dealer->average_rating) ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                    </span>
                                    <span class="rating-value">{{ number_format($dealer->average_rating, 1) }}</span>
                                    <span class="rating-count">({{ $dealer->total_reviews ?? 0 }})</span>
                                </div>
                                @endif
                                <span class="car-count" style="text-align: left !important; display: inline-block !important;">{{ $dealer->total_car ?? 0 }} {{ __('translate.Cars') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="text-center mt-5" data-aos="fade-up">
                    <a href="{{ route('dealers') }}" class="btn-ecomotif">
                        {{ __('translate.View All Dealers') }} <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>
        @endif

        <!-- DOWNLOAD APP CTA -->
        <section class="ecomotif-download-cta">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-7" data-aos="fade-right">
                        <h2>Rasakan Kemudahan Akses ECOMOTIF dari Genggaman Tangan</h2>
                        <p>Download ECOMOTIF sekarang dan nikmati kemudahan jual beli mobil kapan saja, di mana saja.</p>
                        <div class="download-buttons">
                            @if($homepage->app_store_link ?? false)
                            <a href="{{ $homepage->app_store_link }}" class="download-btn" target="_blank">
                                <i class="fab fa-apple fa-2x"></i>
                                <div>
                                    <small>Download on</small>
                                    <strong>App Store</strong>
                                </div>
                            </a>
                            @endif
                            @if($homepage->play_store_link ?? false)
                            <a href="{{ $homepage->play_store_link }}" class="download-btn" target="_blank">
                                <i class="fab fa-google-play fa-2x"></i>
                                <div>
                                    <small>Get it on</small>
                                    <strong>Google Play</strong>
                                </div>
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-5 text-center" data-aos="fade-left">
                        @if($homepage->mobile_app_image ?? false)
                        <img src="{{ getImageOrPlaceholder($homepage->mobile_app_image, '300x500') }}" 
                             alt="ECOMOTIF App" class="img-fluid" style="max-height: 400px;">
                        @else
                        <div class="p-5">
                            <i class="fas fa-mobile-alt fa-5x text-white opacity-50"></i>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

    </main>
@endsection

@push('js_section')
<script>
    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const faqItem = question.parentElement;
            faqItem.classList.toggle('active');
        });
    });
    
    // Hero Video Play/Pause - Only for video files, not iframes
    const heroPlayButton = document.getElementById('heroPlayButton');
    const heroVideo = document.getElementById('heroVideo');
    
    if (heroPlayButton && heroVideo && heroVideo.tagName === 'VIDEO') {
        heroPlayButton.addEventListener('click', () => {
            if (heroVideo.paused) {
                heroVideo.play();
                heroPlayButton.classList.add('playing');
            } else {
                heroVideo.pause();
                heroPlayButton.classList.remove('playing');
            }
        });
        
        // Auto-hide play button when video is playing
        heroVideo.addEventListener('play', () => {
            heroPlayButton.classList.add('playing');
        });
        
        heroVideo.addEventListener('pause', () => {
            heroPlayButton.classList.remove('playing');
        });
    }
</script>
@endpush
