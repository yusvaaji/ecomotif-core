<?php

use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\CalculatorController;
use App\Http\Controllers\API\CommunityController;
use App\Http\Controllers\API\GarageController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\KataKataController;
use App\Http\Controllers\API\MarketingController;
use App\Http\Controllers\API\MediatorController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\QuoteController;
use App\Http\Controllers\API\ShowroomController;
use Illuminate\Support\Facades\Route;
use Modules\Car\Http\Controllers\API\CarController;

Route::group(['middleware' => ['HtmlSpecialchars', 'CurrencyLangaugeForAPI']], function () {

    Route::controller(HomeController::class)->group(function () {

        Route::get('/website-setup', 'website_setup')->name('website-setup');
        Route::get('/', 'index')->name('home');
        Route::get('/all-brands', 'all_brands')->name('all-brands');

        Route::get('/listings-filter-option', 'listings_filter_option')->name('listings-filter-option');
        Route::get('/listings', 'listings')->name('api.listings');
        Route::get('/listing/{id}', 'listing')->name('api.listing');

        Route::get('/terms-conditions', 'terms_conditions')->name('terms-conditions');
        Route::get('/privacy-policy', 'privacy_policy')->name('privacy-policy');

        Route::get('/dealers', 'dealers')->name('api.dealers');
        Route::get('/dealers-filter-option', 'dealers_filter_option')->name('api.dealers-filter-option');
        Route::get('/dealer/{slug}', 'dealer')->name('api.dealer');
        Route::post('/send-message-to-dealer/{id}', 'send_message_to_dealer')->name('api.send-message-to-dealer');

        // Showrooms (enhanced)
        Route::get('/showrooms', [ApplicationController::class, 'selectShowroom'])->name('showrooms');

        // Calculator
        Route::post('/calculate-installment', [ApplicationController::class, 'selectDPAndInstallment'])->name('calculate-installment');
        Route::post('/calculate-payment-capability', [ApplicationController::class, 'calculatePaymentCapability'])->name('calculate-payment-capability');
        Route::post('/calculator/payment-capability', [CalculatorController::class, 'paymentCapability'])->name('calculator-payment-capability');

        Route::post('/quotes/rental', [QuoteController::class, 'rental'])->middleware('throttle:30,1')->name('quotes-rental');
        Route::post('/quotes/leasing', [QuoteController::class, 'leasing'])->middleware('throttle:30,1')->name('quotes-leasing');
        Route::post('/quotes/garage', [QuoteController::class, 'garage'])->middleware('throttle:30,1')->name('quotes-garage');

        // Kutipan / kata-kata (teks), bukan estimasi harga — bedakan dari /quotes/rental|leasing|garage
        Route::get('/kata-kata/random', [KataKataController::class, 'random'])->middleware('throttle:60,1')->name('kata-kata-random');
        Route::get('/kata-kata', [KataKataController::class, 'index'])->middleware('throttle:60,1')->name('kata-kata-index');

        Route::post('/guest/service-bookings', [GarageController::class, 'storeGuestBooking'])->middleware('throttle:20,1')->name('guest-service-bookings');

        // Scan barcode (public)
        Route::post('/scan-showroom/{code}', [ShowroomController::class, 'scanBarcode'])->name('scan-showroom');

        Route::get('/join-as-dealer', 'join_as_dealer')->name('api.join-as-dealer');
        Route::get('/subscription-plan', [\Modules\Subscription\Http\Controllers\SubscriptionPlanController::class, 'subscription_plan'])->name('api.subscription-plan');

        Route::get('/language-switcher', 'language_switcher')->name('api.language-switcher');
        Route::get('/currency-switcher', 'currency_switcher')->name('api.currency-switcher');

        Route::get('/cities-by-country/{id}', 'cities_by_country')->name('api.cities-by-country');

    });

    // Public garage endpoints
    Route::controller(GarageController::class)->group(function () {
        Route::get('/garages', 'index')->name('api.garages');
        Route::get('/garages/{id}', 'show')->name('garage-detail');
    });

    // Public community endpoints
    Route::controller(CommunityController::class)->group(function () {
        Route::get('/communities', 'index')->name('api.communities');
        Route::get('/communities/{slug}', 'show')->name('community-detail');
    });

    // Login route
    Route::post('/store-login', [LoginController::class, 'store_login'])->name('store-login');
    Route::post('/store-login-mobile', [LoginController::class, 'store_login_mobile'])->name('store-login-mobile');

    Route::post('/store-register', [RegisterController::class, 'store_register'])->name('store-register');
    Route::post('/seller/store-register', [RegisterController::class, 'seller_store_register'])->name('seller-store-register');
    Route::post('/garage/store-register', [RegisterController::class, 'garage_store_register'])->name('garage-store-register');
    Route::post('/mediator/store-register', [RegisterController::class, 'mediator_store_register'])->name('mediator-store-register');
    Route::post('/sales/store-register', [RegisterController::class, 'sales_store_register'])->name('sales-store-register');
    Route::post('/resend-register', [RegisterController::class, 'resend_register_code'])->name('resend-register');
    Route::post('/user-verification', [RegisterController::class, 'register_verification'])->name('user-verification');

    Route::post('/send-forget-password', [LoginController::class, 'send_custom_forget_pass'])->name('send-forget-password');
    Route::post('/verify-forget-password-otp', [LoginController::class, 'verify_custom_reset_password'])->name('verify-forget-password-otp');
    Route::post('/store-reset-password', [LoginController::class, 'store_reset_password'])->name('store-reset-password');

    Route::get('/user-logout', [LoginController::class, 'userLogout'])->name('user.logout');

    // Login route end

    Route::group(['as' => 'user.', 'prefix' => 'user', 'middleware' => ['auth:api']], function () {

        Route::controller(PaymentController::class)->group(function () {

            Route::post('/payment', 'payment')->name('payment');

            Route::post('/pay-via-stripe', 'pay_via_stripe')->name('pay-via-stripe');
            Route::post('/pay-via-bank', 'pay_via_bank')->name('pay-via-bank');
            Route::post('/pay-via-razorpay', 'pay_via_razorpay')->name('pay-via-razorpay');
            Route::post('/pay-via-flutterwave', 'pay_via_flutterwave')->name('pay-via-flutterwave');
            Route::get('/pay-via-paystack', 'pay_via_payStack')->name('pay-via-paystack');
            Route::get('/pay-via-mollie', 'pay_via_mollie')->name('pay-via-mollie');
            Route::get('/mollie-payment-success', 'mollie_payment_success')->name('mollie-payment-success');
            Route::get('/pay-via-instamojo', 'pay_via_instamojo')->name('pay-via-instamojo');
            Route::get('/response-instamojo', 'instamojo_response')->name('response-instamojo');

        });

        Route::controller(ProfileController::class)->group(function () {

            Route::get('/dashboard', 'dashboard')->name('dashboard');

            Route::get('/profile', 'profile')->name('profile');
            Route::get('/edit-profile', 'edit')->name('edit-profile');
            Route::put('/update-profile', 'update')->name('update-profile');
            Route::put('/update-merchant-profile', 'updateMerchantProfile')->name('update-merchant-profile');

            Route::get('/change-password', 'change_password')->name('change-password');
            Route::post('/update-password', 'update_password')->name('update-password');
            Route::post('/upload-user-avatar', 'upload_user_avatar')->name('upload-user-avatar');

            Route::get('/transactions', 'transactions')->name('transactions');

            Route::get('/wishlists', 'wishlists')->name('wishlists');
            Route::get('/add-to-wishlist/{id}', 'add_to_wishlist')->name('add-to-wishlist');
            Route::delete('/remove-wishlist/{id}', 'remove_wishlist')->name('remove-wishlist');

            Route::get('/reviews', 'reviews')->name('reviews');
            Route::post('/store-review', 'store_review')->name('store-review');

            Route::get('/wallet', 'wallet')->name('wallet');
            Route::get('/wallet/transactions', 'walletTransactions')->name('wallet-transactions');

            Route::get('/notifications', 'notifications')->name('notifications');
            Route::post('/notifications/{id}/read', 'markNotificationRead')->name('mark-notification-read');
            Route::post('/notifications/read-all', 'markAllNotificationsRead')->name('mark-all-notifications-read');
        });

        // Mediator routes
        Route::group(['prefix' => 'mediator', 'middleware' => ['mediator']], function () {
            Route::controller(MediatorController::class)->group(function () {
                Route::get('/dashboard', 'dashboard')->name('mediator-dashboard');
                Route::get('/applications', 'applications')->name('mediator-applications');
                Route::post('/applications', 'createApplication')->name('mediator-create-application');
                Route::get('/applications/{id}', 'applicationDetails')->name('mediator-application-details');
                Route::put('/applications/{id}', 'updateApplication')->name('mediator-update-application');
            });
        });

        // Showroom/Dealer routes
        Route::group(['prefix' => 'showroom', 'middleware' => ['showroom']], function () {
            Route::controller(ShowroomController::class)->group(function () {
                Route::post('/generate-barcode', 'generateBarcode')->name('showroom-generate-barcode');
                Route::get('/barcode', 'getBarcode')->name('showroom-get-barcode');
                Route::get('/applications', 'applications')->name('showroom-applications');
                Route::get('/applications/{id}', 'applicationDetails')->name('showroom-application-details');
                Route::post('/applications/{id}/claim', 'claimApplication')->name('showroom-claim-application');
                Route::post('/applications/{id}/review', 'reviewApplication')->name('showroom-review-application');
                Route::post('/applications/{id}/pool-to-leasing', 'poolToLeasing')->name('showroom-pool-to-leasing');
                Route::get('/applications/{id}/leasing-result', 'receiveLeasingResult')->name('showroom-leasing-result');
                Route::post('/applications/{id}/appeal', 'appealToLeasing')->name('showroom-appeal');
                Route::post('/applications/{id}/handle-dp', 'handleDP')->name('showroom-handle-dp');
                Route::post('/applications/{id}/reject', 'rejectApplication')->name('showroom-reject-application');
                Route::post('/applications/{id}/update-status', 'updateApplicationStatus')->name('showroom-update-application-status');

                Route::get('/marketing', 'getMarketingUsers')->name('showroom-marketing-list');
                Route::post('/marketing', 'addMarketingUser')->name('showroom-marketing-add');
                Route::delete('/marketing/{id}', 'removeMarketingUser')->name('showroom-marketing-remove');
                Route::put('/marketing/{id}/status', 'updateMarketingUserStatus')->name('showroom-marketing-update-status');
                // Performance API
                Route::get('/performance', 'performance')->name('showroom-performance');
            });
        });

        // Marketing routes
        Route::group(['prefix' => 'marketing', 'middleware' => ['marketing']], function () {
            Route::controller(MarketingController::class)->group(function () {
                Route::get('/dashboard', 'dashboard')->name('marketing-dashboard');
                Route::get('/applications', 'applications')->name('marketing-applications');
                Route::post('/applications', 'createApplication')->name('marketing-create-application');
                
                // New dedicated APIs for sales orders
                Route::get('/orders', 'orders')->name('marketing-orders');
                Route::get('/orders/{id}', 'orderDetails')->name('marketing-order-details');
                Route::post('/orders/{id}/claim', 'claimOrder')->name('marketing-claim-order');
                Route::post('/orders/{id}/status', 'updateStatus')->name('marketing-update-order-status');
            });
        });

        // Application routes (for consumers)
        Route::controller(ApplicationController::class)->group(function () {
            Route::get('/applications', 'myApplications')->name('my-applications');
            Route::post('/applications', 'submitApplication')->name('submit-application');
            Route::post('/applications/{id}/documents', 'uploadDocuments')->name('upload-documents');
            Route::get('/applications/{id}', 'applicationStatus')->name('application-status');
            Route::post('/applications/{id}/pay-dp', 'payDP')->name('pay-dp');
            Route::post('/applications/{id}/cancel', 'cancelApplication')->name('user-cancel-application');
        });

        // Service booking routes (for consumers)
        Route::controller(GarageController::class)->group(function () {
            Route::get('/service-bookings', 'myBookings')->name('my-service-bookings');
            Route::post('/service-bookings', 'storeBooking')->name('store-service-booking');
            Route::get('/service-bookings/{id}', 'showBooking')->name('show-service-booking');
            Route::post('/service-bookings/{id}/cancel', 'cancelBooking')->name('cancel-service-booking');
        });

        // Garage owner routes
        Route::group(['prefix' => 'garage', 'middleware' => ['garage']], function () {
            Route::controller(GarageController::class)->group(function () {
                Route::get('/dashboard', 'dashboard')->name('garage-dashboard');
                Route::get('/services', 'listServices')->name('garage-services');
                Route::post('/services', 'storeService')->name('garage-store-service');
                Route::put('/services/{id}', 'updateService')->name('garage-update-service');
                Route::delete('/services/{id}', 'deleteService')->name('garage-delete-service');
                Route::get('/bookings', 'garageBookings')->name('garage-bookings');
                Route::get('/bookings/{id}', 'garageBookingDetail')->name('garage-booking-detail');
                Route::put('/bookings/{id}/status', 'updateBookingStatus')->name('garage-update-booking-status');

                Route::get('/mechanics', 'getMechanics')->name('garage-mechanics-list');
                Route::post('/mechanics', 'addMechanic')->name('garage-mechanics-add');
                Route::delete('/mechanics/{id}', 'removeMechanic')->name('garage-mechanics-remove');
                Route::put('/mechanics/{id}/status', 'updateMechanicStatus')->name('garage-mechanics-update-status');
                
                Route::get('/performance', 'performance')->name('garage-performance');
            });
        });

        // Mechanic routes
        Route::group(['prefix' => 'mechanic'], function () {
            Route::controller(GarageController::class)->group(function () {
                Route::get('/dashboard', 'mechanicDashboard')->name('mechanic-dashboard');
                Route::get('/bookings', 'mechanicBookings')->name('mechanic-bookings');
                Route::put('/bookings/{id}/status', 'updateBookingStatus')->name('mechanic-update-booking-status');
            });
        });

        // Car routes (for dealers/showrooms)
        Route::resource('car', CarController::class);
        Route::get('select-car-purpose', [CarController::class, 'select_car_purpose'])->name('select-car-purpose');
        Route::post('car-key-feature/{id}', [CarController::class, 'car_key_feature'])->name('car-key-feature');
        Route::post('car-feature/{id}', [CarController::class, 'car_feature'])->name('car-feature');
        Route::post('car-address/{id}', [CarController::class, 'car_address'])->name('car-address');
        Route::post('video-images/{id}', [CarController::class, 'video_images'])->name('video-images');
        Route::delete('image-delete/{id}', [CarController::class, 'image_delete'])->name('image-delete');
        Route::post('request-to-publish/{id}', [CarController::class, 'request_to_publish'])->name('request-to-publish');
        Route::get('car-gallery/{id}', [CarController::class, 'car_gallery'])->name('car-gallery');
        Route::post('upload-gallery/{id}', [CarController::class, 'upload_car_gallery'])->name('upload-gallery');
        Route::delete('delete-gallery/{id}', [CarController::class, 'delete_car_gallery'])->name('delete-gallery');

        // Community routes (authenticated)
        Route::controller(CommunityController::class)->group(function () {
            Route::get('/my-communities', 'myCommunities')->name('my-communities');
            Route::get('/my-likes', 'getUserLikes')->name('my-likes');
            Route::post('/communities', 'store')->name('store-community');
            Route::post('/communities/{slug}/join', 'join')->name('join-community');
            Route::post('/communities/{slug}/leave', 'leave')->name('leave-community');
            Route::get('/communities/{slug}/members', 'members')->name('community-members');
            Route::put('/communities/{slug}/members/{userId}/role', 'updateMemberRole')->name('community-members-update-role');
            Route::delete('/communities/{slug}/members/{userId}', 'kickMember')->name('community-members-kick');
            Route::get('/communities/{slug}/posts', 'posts')->name('community-posts');
            Route::post('/communities/{slug}/posts', 'storePost')->name('store-community-post');
            Route::post('/communities/{slug}/posts/{postId}/like', 'toggleLike')->name('toggle-community-post-like');
            Route::delete('/communities/{slug}/posts/{postId}/like', 'removeLike')->name('remove-community-post-like');
            Route::get('/communities/{slug}/posts/{postId}/comments', 'getComments')->name('get-community-comments');
            Route::post('/community-posts/{postId}/comments', 'storeComment')->name('store-community-comment');
            Route::delete('/communities/{slug}/posts/{postId}', 'deletePost')->name('delete-community-post');
            Route::delete('/community-posts/{postId}/comments/{commentId}', 'deleteComment')->name('delete-community-comment');
        });

        // Admin routes
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard-stats', [\App\Http\Controllers\API\AdminController::class, 'dashboard_stats'])->name('admin.dashboard-stats');
            Route::get('/pending-cars', [\App\Http\Controllers\API\AdminController::class, 'pending_cars'])->name('admin.pending-cars');
            Route::post('/verify-car/{id}', [\App\Http\Controllers\API\AdminController::class, 'verify_car'])->name('admin.verify-car');
            Route::get('/mitra', [\App\Http\Controllers\API\AdminController::class, 'mitra_list'])->name('admin.mitra');
            Route::post('/verify-mitra/{id}', [\App\Http\Controllers\API\AdminController::class, 'verify_mitra'])->name('admin.verify-mitra');
            
            // Subscription Plans Management
            Route::get('/subscription-plans', [\App\Http\Controllers\API\AdminController::class, 'subscription_plans'])->name('admin.subscription-plans.list');
            Route::post('/subscription-plans', [\App\Http\Controllers\API\AdminController::class, 'store_subscription_plan'])->name('admin.subscription-plans.store');
            Route::put('/subscription-plans/{id}', [\App\Http\Controllers\API\AdminController::class, 'update_subscription_plan'])->name('admin.subscription-plans.update');
            Route::delete('/subscription-plans/{id}', [\App\Http\Controllers\API\AdminController::class, 'destroy_subscription_plan'])->name('admin.subscription-plans.destroy');

            // Ads Banner Management
            Route::get('/ads-banners', [\App\Http\Controllers\API\AdminController::class, 'ads_banners_list'])->name('admin.ads-banners.list');
            Route::post('/ads-banners', [\App\Http\Controllers\API\AdminController::class, 'store_ads_banner'])->name('admin.ads-banners.store');
            Route::delete('/ads-banners/{id}', [\App\Http\Controllers\API\AdminController::class, 'destroy_ads_banner'])->name('admin.ads-banners.destroy');
        });

    });

    // Public API
    Route::get('/public/ads-banners', [\App\Http\Controllers\API\AdminController::class, 'get_active_banners'])->name('public.ads-banners');
    
    // ... Any other public routes below ...
});
