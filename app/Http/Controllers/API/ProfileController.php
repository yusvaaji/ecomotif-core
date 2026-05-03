<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\InvitationCode;
use App\Models\MerchantProfile;
use App\Models\Review;
use App\Models\Wishlist;
use App\Rules\Captcha;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Car\Entities\Car;
use Modules\Subscription\Entities\SubscriptionHistory;
use App\Models\UserVehicle;

class ProfileController extends Controller
{
    public function dashboard(Request $request)
    {

        $user = Auth::guard('api')->user()->load(['merchantProfile.subscriptionPlan']);

        $cars = Car::with('brand')->where('agent_id', $user->id)->get()->take(10);

        $total_car = Car::where('agent_id', $user->id)->count();

        $total_featured_car = Car::where('agent_id', $user->id)->where('is_featured', 'enable')->count();

        $total_wishlist = Wishlist::where('user_id', $user->id)->count();

        $wallet = $user->wallet;
        $wallet_balance = $wallet ? $wallet->balance : 0;

        $total_communities = $user->communities()->count();

        $total_applications = \App\Models\Booking::where('user_id', $user->id)->count();
        $total_service_bookings = \App\Models\ServiceBooking::where('user_id', $user->id)->count();

        $unread_notifications = \App\Models\Notification::where('user_id', $user->id)->where('is_read', false)->count();

        return response()->json([
            'user' => $user,
            'cars' => $cars,
            'total_car' => $total_car,
            'total_featured_car' => $total_featured_car,
            'total_wishlist' => $total_wishlist,
            'wallet_balance' => $wallet_balance,
            'total_communities' => $total_communities,
            'total_applications' => $total_applications,
            'total_service_bookings' => $total_service_bookings,
            'unread_notifications' => $unread_notifications,
        ]);
    }

    public function edit(Request $request)
    {
        $user = Auth::guard('api')->user()->load(['merchantProfile.subscriptionPlan']);

        return response()->json([
            'user' => $user,
        ]);
    }

    public function profile(Request $request)
    {
        $user = Auth::guard('api')->user()->load(['merchantProfile.subscriptionPlan']);

        $communities_count = $user->communities()->count();

        return response()->json([
            'user' => $user,
            'communities_count' => $communities_count,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'address' => 'required|max:220',
        ];
        $customMessages = [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'phone.required' => trans('translate.Phone is required'),
            'address.required' => trans('translate.Address is required'),
        ];
        $this->validate($request, $rules, $customMessages);

        $user = Auth::guard('api')->user();

        if ($request->file('image')) {
            $image_path = uploadFile($request->file('image'), 'uploads/custom-images', $user->image);
            $user->image = $image_path;
            $user->save();
        }

        if ($request->file('banner_image')) {
            $image_path = uploadFile($request->file('banner_image'), 'uploads/custom-images', $user->banner_image);
            $user->banner_image = $image_path;
            $user->save();
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->designation = $request->designation;

        if ($request->filled('date_of_birth')) {
            $user->date_of_birth = $request->date_of_birth;
        }
        if ($request->filled('gender')) {
            $user->gender = $request->gender;
        }
        if ($request->filled('latitude')) {
            $user->latitude = $request->latitude;
        }
        if ($request->filled('longitude')) {
            $user->longitude = $request->longitude;
        }
        if ($request->filled('instagram')) {
            $user->instagram = $request->instagram;
        }
        if ($request->filled('facebook')) {
            $user->facebook = $request->facebook;
        }
        if ($request->filled('twitter')) {
            $user->twitter = $request->twitter;
        }
        if ($request->filled('linkedin')) {
            $user->linkedin = $request->linkedin;
        }

        $user->save();

        $notification = trans('translate.Your profile updated successfully');

        return response()->json([
            'message' => $notification,
        ]);
    }

    /**
     * Update onboarding data for dealer/showroom or garage (merchant_profiles + overlapping user fields).
     * Use multipart/form-data when uploading files. Sales and other roles receive 403.
     */
    public function updateMerchantProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ((int) $user->is_dealer !== 1 && (int) $user->is_garage !== 1) {
            return response()->json([
                'message' => trans('translate.Merchant profile update is only for showroom or garage accounts'),
            ], 403);
        }

        $isGarage = (int) $user->is_garage === 1;
        $businessType = $isGarage ? MerchantProfile::BUSINESS_GARAGE : MerchantProfile::BUSINESS_SHOWROOM;

        $invitationRule = [
            'nullable', 'string', 'max:64',
            function ($attribute, $value, $fail) use ($user) {
                if ($value === null || $value === '' || ! Schema::hasTable('invitation_codes')) {
                    return;
                }
                $profile = $user->merchantProfile;
                if ($profile && (string) $profile->invitation_code === (string) $value) {
                    return;
                }
                $row = InvitationCode::where('code', $value)->first();
                if (! $row || ! $row->isUsable()) {
                    $fail(trans('translate.Invalid or expired invitation code'));
                }
            },
        ];

        $rules = [
            'name'              => ['nullable', 'string', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:60'],
            'address'           => ['nullable', 'string', 'max:500'],
            'latitude'          => ['nullable', 'numeric'],
            'longitude'         => ['nullable', 'numeric'],
            'showroom_category' => ['nullable', 'string', 'max:120'],
            'showroom_type'     => ['nullable', 'string', 'max:120'],
            'garage_category'   => ['nullable', 'string', 'max:120'],
            'garage_services'   => ['nullable', 'string', 'max:5000'],
            'served_brands'     => ['nullable', 'string', 'max:5000'],
            'specialization'    => ['nullable', 'string', 'max:255'],
            'pic_name'          => ['nullable', 'string', 'max:255'],
            'pic_email'         => ['nullable', 'email', 'max:255'],
            'pic_phone'         => ['nullable', 'string', 'max:30'],
            'invitation_code'   => $invitationRule,
            // Jam operasional (format HH:MM, null = buka 24 jam)
            'opening_hour'         => ['nullable', 'date_format:H:i'],
            'closing_hour'         => ['nullable', 'date_format:H:i'],
            // Biaya perjalanan per tier jarak
            'travel_fee_0_1km'     => ['nullable', 'integer', 'min:0'],
            'travel_fee_1_5km'     => ['nullable', 'integer', 'min:0'],
            'travel_fee_5_10km'    => ['nullable', 'integer', 'min:0'],
            'travel_fee_10km_plus' => ['nullable', 'integer', 'min:0'],
            'subscription_plan_id' => [
                'nullable', 'integer',
                Rule::exists('subscription_plans', 'id')->where(function ($q) {
                    $q->where('status', 'active');
                }),
            ],
            'payment_proof'  => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:8192'],
            'business_photo' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ];

        $this->validate($request, $rules);

        $profile = $user->merchantProfile;

        if (! $profile) {
            $profile = new MerchantProfile([
                'user_id' => $user->id,
                'business_type' => $businessType,
            ]);
        }

        $previousInvitationCode = $profile->exists ? (string) ($profile->invitation_code ?? '') : '';

        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('address')) {
            $user->address = $request->address;
        }
        if ($request->has('latitude')) {
            $user->latitude = $request->latitude;
        }
        if ($request->has('longitude')) {
            $user->longitude = $request->longitude;
        }

        if ($isGarage) {
            if ($request->has('garage_category')) {
                $profile->business_category = $request->garage_category;
            }
            if ($request->has('garage_services')) {
                $services = $request->garage_services;
                $profile->garage_services_description = $services;
                if ($services !== null && $services !== '') {
                    $user->specialization = Str::limit((string) $services, 255);
                }
            } elseif ($request->has('specialization')) {
                $user->specialization = $request->specialization;
                $profile->garage_services_description = $request->specialization;
            }
            if ($request->has('served_brands')) {
                $profile->served_brands = $request->served_brands;
            }
            // ── Jam operasional ───────────────────────────────────────────
            if ($request->exists('opening_hour')) {
                $profile->opening_hour = $request->input('opening_hour'); // null = 24 jam
            }
            if ($request->exists('closing_hour')) {
                $profile->closing_hour = $request->input('closing_hour');
            }
            // ── Biaya perjalanan per tier ─────────────────────────────────
            foreach (['travel_fee_0_1km', 'travel_fee_1_5km', 'travel_fee_5_10km', 'travel_fee_10km_plus'] as $feeField) {
                if ($request->exists($feeField)) {
                    $profile->{$feeField} = (int) ($request->input($feeField) ?? 0);
                }
            }
            // ─────────────────────────────────────────────────────────────
            $profile->showroom_type = null;
        } else {
            if ($request->has('showroom_category')) {
                $profile->business_category = $request->showroom_category;
            }
            if ($request->has('showroom_type')) {
                $profile->showroom_type = $request->showroom_type;
            }
            $profile->garage_services_description = null;
        }

        foreach (['pic_name', 'pic_email', 'pic_phone'] as $field) {
            if ($request->exists($field)) {
                $profile->{$field} = $request->input($field);
            }
        }

        if ($request->filled('subscription_plan_id')) {
            $profile->subscription_plan_id = (int) $request->subscription_plan_id;
        }

        if ($request->has('terms_accepted') && $request->boolean('terms_accepted')) {
            $profile->terms_accepted_at = now();
        }

        if ($request->exists('invitation_code')) {
            $incoming = $request->input('invitation_code');
            $profile->invitation_code = ($incoming === null || $incoming === '') ? null : (string) $incoming;
        }

        if ($request->hasFile('payment_proof')) {
            $newProofPath = uploadFile($request->file('payment_proof'), 'uploads/merchant-onboarding', $profile->payment_proof_path);
            $profile->payment_proof_path = $newProofPath;
        }

        if ($request->hasFile('business_photo')) {
            $newPhotoPath = uploadFile($request->file('business_photo'), 'uploads/merchant-onboarding', $profile->business_photo_path);
            $profile->business_photo_path = $newPhotoPath;
            $user->image = $newPhotoPath;
        }

        $user->save();
        $profile->business_type = $businessType;
        $profile->user_id = $user->id;
        $profile->save();

        $newInvitationCode = (string) ($profile->fresh()->invitation_code ?? '');
        if ($newInvitationCode !== '' && $newInvitationCode !== $previousInvitationCode && Schema::hasTable('invitation_codes')) {
            InvitationCode::where('code', $newInvitationCode)->increment('uses_count');
        }

        $user->load(['merchantProfile.subscriptionPlan']);

        return response()->json([
            'message' => trans('translate.Your profile updated successfully'),
            'user' => $user,
        ]);
    }

    public function update_password(Request $request)
    {
        $rules = [
            'current_password' => 'required',
            'password' => 'required|min:4|confirmed',
        ];
        $customMessages = [
            'current_password.required' => trans('translate.Current password is required'),
            'password.required' => trans('translate.Password is required'),
            'password.min' => trans('translate.Password minimum 4 character'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
        ];
        $this->validate($request, $rules, $customMessages);

        $user = Auth::guard('api')->user();
        if (Hash::check($request->current_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();

            $notification = trans('translate.Password change successfully');

            return response()->json([
                'message' => $notification,
            ]);

        } else {
            $notification = trans('translate.Current password does not match');

            return response()->json([
                'message' => $notification,
            ], 403);
        }
    }

    public function transactions()
    {
        $user = Auth::guard('api')->user();

        $histories = SubscriptionHistory::where('user_id', $user->id)->latest()->get();

        return response()->json(['histories' => $histories]);
    }

    public function bookingHistory()
    {
        $user = Auth::guard('api')->user();
        $histories = Booking::with('car', 'seller', 'review_by_user', 'review_by_dealer')->where('user_id', $user->id)->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'histories' => $histories,
        ]);
    }

    public function bookingDetails($id)
    {

        $user = Auth::guard('api')->user();

        $history = Booking::with('car', 'seller')->where('id', $id)->where('user_id', $user->id)->first();

        if (! $history) {
            $message = trans('Not found');

            return response()->json([
                'message' => $message,
            ], 403);
        }

        return response()->json([
            'history' => $history,
        ]);

    }

    public function bookingCancelByUser(Request $request, $id)
    {

        $user = Auth::guard('api')->user();

        $history = Booking::where('id', $id)->where('user_id', $user->id)->first();

        if (! $history) {
            $message = trans('Not found');

            return response()->json([
                'message' => $message,
            ], 403);
        }

        if ($history->status == 0 || $history->status == 1) {
            $history->status = 3;
            $history->save();

            $message = trans('translate.Booking cancel succesful');

            return response()->json([
                'message' => $message,
            ]);

        } else {
            $message = trans('translate.Unable to cancel the booking');

            return response()->json([
                'message' => $message,
            ], 403);
        }

    }

    public function rideStartByUser(Request $request, $id)
    {

        $user = Auth::guard('api')->user();

        $history = Booking::where('id', $id)->where('user_id', $user->id)->first();

        if (! $history) {
            $message = trans('Not found');

            return response()->json([
                'message' => $message,
            ], 403);
        }

        if ($history->status == 1) {

            $history->status = 6;
            $history->save();

            $message = trans('translate.Ride started succesful');

            return response()->json([
                'message' => $message,
            ]);

        } else {
            $message = trans('translate.Unable to start the ride');

            return response()->json([
                'message' => $message,
            ], 403);
        }

    }

    public function bookingRequest()
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer == 0) {
            $message = trans('Need dealer account to access this route');

            return response()->json([
                'message' => $message,
            ], 403);
        }

        $histories = Booking::with('car', 'seller', 'review_by_user', 'review_by_dealer')->where('supplier_id', $user->id)->orderBy('id', 'desc')->paginate(10);

        return response()->json(['histories' => $histories]);
    }

    public function bookingRequestDetails($id)
    {

        $user = Auth::guard('api')->user();

        $history = Booking::with('car', 'seller', 'review_by_user', 'review_by_dealer')->where('id', $id)->where('supplier_id', $user->id)->first();

        if (! $history) {
            $message = trans('Not found');

            return response()->json([
                'message' => $message,
            ], 403);
        }

        return response()->json(['history' => $history]);
    }

    public function bookingRequestAccept(Request $request, $id)
    {

        $user = Auth::guard('api')->user();

        $history = Booking::where('id', $id)->where('supplier_id', $user->id)->first();

        if (! $history) {
            $message = trans('Not found');

            return response()->json([
                'message' => $message,
            ], 403);
        }

        if ($history->status == 0) {
            $history->status = 1;
            $history->save();

            $notification = trans('translate.Booking approved succesful');

            return response()->json([
                'message' => $notification,
            ]);

        } else {
            $notification = trans('translate.Unable to approved the booking');

            return response()->json([
                'message' => $notification,
            ], 403);
        }

    }

    public function bookingCancelByDealer(Request $request, $id)
    {

        $user = Auth::guard('api')->user();

        $history = Booking::where('id', $id)->where('supplier_id', $user->id)->first();

        if (! $history) {
            $message = trans('Not found');

            return response()->json([
                'message' => $message,
            ], 403);
        }

        if ($history->status == 0 || $history->status == 1) {
            $history->status = 4;
            $history->save();

            $notification = trans('translate.Booking cancel succesful');

            return response()->json([
                'message' => $notification,
            ]);

        } else {
            $notification = trans('translate.Unable to cancel the booking');

            return response()->json([
                'message' => $notification,
            ], 403);
        }

    }

    public function bookingCompleteByDealer(Request $request, $id)
    {

        $user = Auth::guard('api')->user();

        $history = Booking::where('id', $id)->where('supplier_id', $user->id)->first();

        if (! $history) {
            $message = trans('Not found');

            return response()->json([
                'message' => $message,
            ], 403);
        }

        if ($history->status == 6) {
            $history->status = 2;
            $history->save();

            $notification = trans('translate.Booking completed succesful');

            return response()->json([
                'message' => $notification,
            ]);

        } else {
            $notification = trans('translate.Unable to complete the booking');

            return response()->json([
                'message' => $notification,
            ], 403);
        }

    }

    public function reviews()
    {

        $user = Auth::guard('api')->user();

        $reviews = Review::with('car')->latest()->where('user_id', $user->id)->get();

        return response()->json(['reviews' => $reviews]);
    }

    public function store_review(Request $request)
    {

        $rules = [
            'rating' => 'required',
            'comment' => 'required',
            'car_id' => 'required',
            'g-recaptcha-response' => new Captcha(),
        ];

        $customMessages = [
            'rating.required' => trans('translate.Rating is required'),
            'comment.required' => trans('translate.Review is required'),
            'car_id.required' => trans('Car is required'),
        ];

        $this->validate($request, $rules, $customMessages);

        $user = Auth::guard('api')->user();

        $car = Car::where('id', $request->car_id)->first();

        $is_exist = Review::where(['user_id' => $user->id, 'car_id' => $car->id])->first();

        if (! $is_exist) {

            $review = new Review();
            $review->user_id = $user->id;
            $review->rating = $request->rating;
            $review->comment = $request->comment;
            $review->agent_id = $car->agent_id;
            $review->car_id = $request->car_id;
            $review->save();

            $message = trans('translate.Review submited successfully');

            return response()->json([
                'message' => $message,
            ]);

        } else {

            $notification = trans('translate.Review already submited');

            return response()->json([
                'message' => $notification,
            ], 403);
        }

    }

    public function add_to_wishlist($id)
    {

        $car = Car::where('id', $id)->first();

        if (! $car) {
            $message = trans('translate.Listing Not Found!');

            return response()->json([
                'message' => $message,
            ], 403);
        }

        $user = Auth::guard('api')->user();

        $is_exist = Wishlist::where(['user_id' => $user->id, 'car_id' => $id])->count();

        if ($is_exist == 0) {

            $wishlist = new Wishlist();
            $wishlist->car_id = $id;
            $wishlist->user_id = $user->id;
            $wishlist->save();

            $notification = trans('translate.Item added to favourite list');

            return response()->json([
                'message' => $notification,
            ]);

        } else {
            $notification = trans('translate.Already added to favourite list');

            return response()->json([
                'message' => $notification,
            ], 403);
        }

    }

    public function wishlists()
    {

        $user = Auth::guard('api')->user();
        $wishlists = Wishlist::where(['user_id' => $user->id])->get();
        $wishlist_arr = [];
        foreach ($wishlists as $wishlist) {
            $wishlist_arr[] = $wishlist->car_id;
        }

        $cars = Car::with('brand')->where(function ($query) {
            $query->where('expired_date', null)
                ->orWhere('expired_date', '>=', date('Y-m-d'));
        })->where(['status' => 'enable', 'approved_by_admin' => 'approved'])->whereIn('id', $wishlist_arr)->select('id', 'slug', 'brand_id', 'expired_date', 'regular_price', 'offer_price', 'thumb_image', 'purpose', 'condition', 'is_featured', 'status', 'approved_by_admin')->get();

        return response()->json(['cars' => $cars]);

    }

    public function remove_wishlist($id)
    {

        $user = Auth::guard('api')->user();
        Wishlist::where(['user_id' => $user->id, 'car_id' => $id])->delete();

        $notification = trans('translate.Item remove to favourite list');

        return response()->json([
            'message' => $notification,
        ]);
    }

    // ──────────────────────────────────────────────
    // WALLET
    // ──────────────────────────────────────────────

    public function wallet()
    {
        $user = Auth::guard('api')->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            $wallet = \App\Models\Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);
        }

        return response()->json([
            'wallet' => $wallet,
        ]);
    }

    public function walletTransactions(Request $request)
    {
        $user = Auth::guard('api')->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            return response()->json(['transactions' => []]);
        }

        $query = $wallet->transactions()->orderBy('id', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json([
            'transactions' => $query->paginate(15),
        ]);
    }

    // ──────────────────────────────────────────────
    // NOTIFICATIONS
    // ──────────────────────────────────────────────

    public function notifications(Request $request)
    {
        $user = Auth::guard('api')->user();

        $query = \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('id', 'desc');

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->is_read);
        }

        return response()->json([
            'notifications' => $query->paginate(15),
        ]);
    }

    public function markNotificationRead($id)
    {
        $user = Auth::guard('api')->user();

        $notification = \App\Models\Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return response()->json(['message' => trans('translate.Notification marked as read')]);
    }

    // ──────────────────────────────────────────────
    // USER VEHICLES
    // ──────────────────────────────────────────────

    public function getVehicles()
    {
        $user = Auth::guard('api')->user();
        $vehicles = UserVehicle::with('brand')->where('user_id', $user->id)->get();

        return response()->json([
            'vehicles' => $vehicles,
        ]);
    }

    public function storeVehicle(Request $request)
    {
        $user = Auth::guard('api')->user();

        $rules = [
            'vehicle_type' => 'required|in:car,motorcycle',
            'brand_id' => 'nullable|integer',
            'vehicle_model' => 'nullable|string|max:255',
        ];
        $this->validate($request, $rules);

        $vehicle = UserVehicle::create([
            'user_id' => $user->id,
            'vehicle_type' => $request->vehicle_type,
            'brand_id' => $request->brand_id,
            'vehicle_model' => $request->vehicle_model,
        ]);

        return response()->json([
            'message' => 'Vehicle added successfully',
            'vehicle' => $vehicle->load('brand'),
        ]);
    }

    public function updateVehicle(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $rules = [
            'vehicle_type' => 'required|in:car,motorcycle',
            'brand_id' => 'nullable|integer',
            'vehicle_model' => 'nullable|string|max:255',
        ];
        $this->validate($request, $rules);

        $vehicle = UserVehicle::where('user_id', $user->id)->findOrFail($id);
        
        $vehicle->update([
            'vehicle_type' => $request->vehicle_type,
            'brand_id' => $request->brand_id,
            'vehicle_model' => $request->vehicle_model,
        ]);

        return response()->json([
            'message' => 'Vehicle updated successfully',
            'vehicle' => $vehicle->load('brand'),
        ]);
    }

    public function destroyVehicle($id)
    {
        $user = Auth::guard('api')->user();

        $vehicle = UserVehicle::where('user_id', $user->id)->findOrFail($id);
        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully',
        ]);
    }

    public function markAllNotificationsRead()
    {
        $user = Auth::guard('api')->user();

        \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => trans('translate.All notifications marked as read')]);
    }
}
