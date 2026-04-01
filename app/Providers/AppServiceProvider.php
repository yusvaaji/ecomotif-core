<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\GeneralSetting\Entities\Setting;
use Modules\Language\Entities\Language;
use Modules\Page\Entities\HomePage;
use Modules\Page\Entities\CustomPage;
use Modules\GeneralSetting\Entities\GoogleRecaptcha;
use Modules\GeneralSetting\Entities\GoogleAnalytic;
use Modules\GeneralSetting\Entities\FacebookPixel;
use Modules\GeneralSetting\Entities\TawkChat;
use Modules\GeneralSetting\Entities\CookieConsent;
use Modules\GeneralSetting\Entities\SocialLoginInfo;
use Modules\Currency\app\Models\MultiCurrency;
use Modules\Blog\Entities\Blog;
use View;
use Session;
use Illuminate\Support\Facades\Schema;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Session::put('admin_lang', 'en');

        View::composer('*', function($view){
            try {
                $setting = Setting::first();
                $language_list = Language::where('status', 1)->get();
                $currency_list = MultiCurrency::where('status', 'active')->get();
                
                // Check if tables exist before querying
                $google_recaptcha = null;
                if (\Schema::hasTable('google_recaptchas')) {
                    $google_recaptcha = GoogleRecaptcha::first();
                }
                
                $custom_pages = CustomPage::where('status', 1)->get();
                
                $google_analytic = null;
                if (\Schema::hasTable('google_analytics')) {
                    $google_analytic = GoogleAnalytic::first();
                }
                
                $facebook_pixel = null;
                if (\Schema::hasTable('facebook_pixels')) {
                    $facebook_pixel = FacebookPixel::first();
                }
                
                $tawk_chat = null;
                if (\Schema::hasTable('tawk_chats')) {
                    $tawk_chat = TawkChat::first();
                }
                
                $cookie_consent = null;
                if (\Schema::hasTable('cookie_consents')) {
                    $cookie_consent = CookieConsent::first();
                }
                
                $footer_blogs = collect();
                if (\Schema::hasTable('blogs')) {
                    $footer_blogs = Blog::where('status', 1)->orderBy('id','desc')->get()->take(2);
                }
                
                $social_login = null;
                if (\Schema::hasTable('social_login_infos')) {
                    $social_login = SocialLoginInfo::first();
                }

                $view->with('breadcrumb', $setting ? $setting->breadcrumb_image : null);
                $view->with('setting', $setting);
                $view->with('language_list', $language_list ?? collect());
                $view->with('currency_list', $currency_list ?? collect());
                $view->with('google_recaptcha', $google_recaptcha);
                $view->with('custom_pages', $custom_pages ?? collect());
                $view->with('google_analytic', $google_analytic);
                $view->with('facebook_pixel', $facebook_pixel);
                $view->with('tawk_chat', $tawk_chat);
                $view->with('cookie_consent', $cookie_consent);
                $view->with('footer_blogs', $footer_blogs);
                $view->with('social_login', $social_login);
            } catch (\Exception $e) {
                // Fallback values if database tables don't exist
                $view->with('setting', null);
                $view->with('language_list', collect());
                $view->with('currency_list', collect());
                $view->with('google_recaptcha', null);
                $view->with('custom_pages', collect());
                $view->with('google_analytic', null);
                $view->with('facebook_pixel', null);
                $view->with('tawk_chat', null);
                $view->with('cookie_consent', null);
                $view->with('footer_blogs', collect());
                $view->with('breadcrumb', null);
                $view->with('social_login', null);
            }
        });
    }
}
