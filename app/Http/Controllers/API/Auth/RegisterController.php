<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Mail\UserRegistration;
use App\Models\InvitationCode;
use App\Models\MerchantProfile;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Mail;
use Modules\GeneralSetting\Entities\EmailTemplate;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:api')->except([
            'seller_store_register', 
            'garage_store_register'
        ]);
    }

    public function store_register(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:4', 'max:100'],

        ], [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'email.unique' => trans('translate.Email already exist'),
            'phone.required' => trans('translate.Phone is required'),
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => Str::slug($request->name).'-'.date('Ymdhis'),
            'status' => 'enable',
            'is_banned' => 'no',
            'password' => Hash::make($request->password),
            'verification_otp' => random_int(100000, 999999),
        ]);

        $emailConfig = \Modules\GeneralSetting\Entities\EmailConfiguration::first();
        if ($emailConfig) {
            MailHelper::setMailConfig();

            try {
                $template = EmailTemplate::where('id', 12)->first();
                if ($template) {
                    $subject = $template->subject;
                    $message = $template->description;
                    $message = str_replace('{{user_name}}', $request->name, $message);
                    $message = str_replace('{{varification_otp}}', $user->verification_otp, $message);

                    Mail::to($user->email)->send(new UserRegistration($message, $subject, $user));
                }

            } catch (Exception $ex) {
                Log::info($ex->getMessage());
            }
        }

        $notify_message = trans('translate.Account created successful, a verification OTP has been send to your mail, please verify it');

        return response()->json([
            'message' => $notify_message,
            'otp' => $user->verification_otp,
            'phone' => $user->phone,
        ]);

    }

    public function resend_register_code(Request $request)
    {

        $rules = [
            'email' => 'required',
        ];
        $customMessages = [
            'email.required' => trans('translate.Email is required'),
        ];
        $this->validate($request, $rules, $customMessages);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            if ($user->email_verified_at == null) {
                try {
                    MailHelper::setMailConfig();

                    $template = EmailTemplate::where('id', 12)->first();
                    if ($template) {
                        $subject = $template->subject;
                        $message = $template->description;
                        $message = str_replace('{{user_name}}', $user->name, $message);
                        $message = str_replace('{{varification_otp}}', $user->verification_otp, $message);

                        Mail::to($user->email)->send(new UserRegistration($message, $subject, $user));
                    }

                } catch (Exception $ex) {
                    Log::info($ex->getMessage());
                }

                $notification = trans('translate.OTP resend successfully');

                return response()->json(['message' => $notification]);
            } else {
                $notification = trans('translate.Email already verified');

                return response()->json(['message' => $notification], 403);
            }

        } else {
            $notification = trans('translate.Email does not exist');

            return response()->json(['message' => $notification], 403);
        }
    }

    public function register_verification(Request $request)
    {

        $rules = [
            'email' => 'required',
            'otp' => 'required',
            'phone' => 'required',
        ];
        $customMessages = [
            'email.required' => trans('translate.Email is required'),
            'otp.required' => trans('translate.OTP is required'),
            'phone.required' => trans('translate.Phone is required'),
        ];
        $this->validate($request, $rules, $customMessages);

        $user = User::where('verification_otp', $request->otp)->where('email', $request->email)->where('phone', $request->phone)->first();
        if ($user) {

            if ($user->email_verified_at != null) {
                $notify_message = trans('translate.Email already verified');

                return response()->json(['message' => $notify_message], 403);
            }

            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_otp = null;
            $user->save();

            $notify_message = trans('translate.Verification Successfully');

            return response()->json(['message' => $notify_message]);
        } else {

            $notify_message = trans('translate.Invalid token or email');

            return response()->json(['message' => $notify_message], 403);
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    /**
     * Seller registration (dealer/showroom)
     * POST /api/seller/store-register
     */
    public function seller_store_register(Request $request)
    {
        $user = auth('api')->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'terms_accepted' => ['required', 'accepted'],
            'subscription_plan_id' => [
                'required',
                'integer',
                Rule::exists('subscription_plans', 'id')->where(function ($q) {
                    $q->where('status', 'active');
                }),
            ],
            'showroom_category' => ['nullable', 'string', 'max:120'],
            'showroom_type' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_email' => ['nullable', 'email', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:30'],
            'invitation_code' => [
                'nullable', 'string', 'max:64',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '' || ! Schema::hasTable('invitation_codes')) {
                        return;
                    }
                    $row = InvitationCode::where('code', $value)->first();
                    if (! $row || ! $row->isUsable()) {
                        $fail(trans('translate.Invalid or expired invitation code'));
                    }
                },
            ],
            'payment_proof' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:8192'],
            'business_photo' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ];

        if (!$user) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:'.User::class];
            $rules['password'] = ['required', 'confirmed', 'min:4', 'max:100'];
        }

        $request->validate($rules, [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'email.unique' => trans('translate.Email already exist'),
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
            'phone.required' => trans('translate.Phone is required'),
            'address.required' => trans('translate.Address is required'),
            'terms_accepted.accepted' => trans('translate.You must accept the terms and conditions'),
            'subscription_plan_id.required' => trans('translate.Subscription plan is required'),
        ]);

        $paymentProofPath = $request->hasFile('payment_proof')
            ? uploadFile($request->file('payment_proof'), 'uploads/merchant-onboarding')
            : null;
        $businessPhotoPath = $request->hasFile('business_photo')
            ? uploadFile($request->file('business_photo'), 'uploads/merchant-onboarding')
            : null;

        // Do NOT overwrite $user with null here, it will break the upgrade flow!
        // $user = null;

        DB::transaction(function () use ($request, $paymentProofPath, $businessPhotoPath, &$user) {
            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'username' => Str::slug($request->name).'-'.date('Ymdhis'),
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'status' => 'enable',
                    'is_banned' => 'no',
                    'is_dealer' => 1,
                    'is_garage' => 0,
                    'password' => Hash::make($request->password),
                    'verification_otp' => random_int(100000, 999999),
                    'image' => $businessPhotoPath,
                ]);
            } else {
                $user->name = $request->name;
                $user->email = $request->email ?? $request->pic_email ?? $user->email;
                $user->phone = $request->phone;
                $user->address = $request->address;
                $user->latitude = $request->latitude;
                $user->longitude = $request->longitude;
                $user->is_dealer = 1;
                $user->is_garage = 0;
                if ($businessPhotoPath) {
                    $user->image = $businessPhotoPath;
                }
                $user->save();
            }

            $merchant = MerchantProfile::firstOrNew(['user_id' => $user->id]);
            $merchant->fill([
                'business_type' => MerchantProfile::BUSINESS_SHOWROOM,
                'subscription_plan_id' => (int) $request->subscription_plan_id,
                'business_category' => $request->showroom_category,
                'showroom_type' => $request->showroom_type,
                'garage_services_description' => null,
                'pic_name' => $request->pic_name,
                'pic_email' => $request->pic_email,
                'pic_phone' => $request->pic_phone,
                'invitation_code' => $request->invitation_code,
                'payment_proof_path' => $paymentProofPath,
                'business_photo_path' => $businessPhotoPath,
                'terms_accepted_at' => now(),
            ]);
            $merchant->save();

            if ($request->filled('invitation_code') && Schema::hasTable('invitation_codes')) {
                InvitationCode::where('code', $request->invitation_code)->increment('uses_count');
            }
        });

        if ($request->has('password') && $request->filled('password')) {
            MailHelper::setMailConfig();

            try {
                $template = EmailTemplate::where('id', 12)->first();
                if ($template) {
                    $subject = $template->subject;
                    $message = $template->description;
                    $message = str_replace('{{user_name}}', $request->name, $message);
                    $message = str_replace('{{varification_otp}}', $user->verification_otp, $message);

                    Mail::to($user->email)->send(new UserRegistration($message, $subject, $user));
                }
            } catch (Exception $ex) {
                Log::info($ex->getMessage());
            }
        }

        $notify_message = trans('translate.Seller account created successful, a verification OTP has been send to your mail, please verify it');

        return response()->json([
            'message' => $notify_message,
        ]);
    }

    /**
     * Garage/Bengkel registration
     * POST /api/garage/store-register
     */
    public function garage_store_register(Request $request)
    {
        $user = auth('api')->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'terms_accepted' => ['required', 'accepted'],
            'subscription_plan_id' => [
                'required',
                'integer',
                Rule::exists('subscription_plans', 'id')->where(function ($q) {
                    $q->where('status', 'active');
                }),
            ],
            'garage_services' => ['nullable', 'string', 'max:5000'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'garage_category' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_email' => ['nullable', 'email', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:30'],
            'invitation_code' => [
                'nullable', 'string', 'max:64',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '' || ! Schema::hasTable('invitation_codes')) {
                        return;
                    }
                    $row = InvitationCode::where('code', $value)->first();
                    if (! $row || ! $row->isUsable()) {
                        $fail(trans('translate.Invalid or expired invitation code'));
                    }
                },
            ],
            'payment_proof' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:8192'],
            'business_photo' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ];

        if (!$user) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:'.User::class];
            $rules['password'] = ['required', 'confirmed', 'min:4', 'max:100'];
        }

        $request->validate($rules, [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'email.unique' => trans('translate.Email already exist'),
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
            'phone.required' => trans('translate.Phone is required'),
            'address.required' => trans('translate.Address is required'),
            'terms_accepted.accepted' => trans('translate.You must accept the terms and conditions'),
            'subscription_plan_id.required' => trans('translate.Subscription plan is required'),
        ]);

        $paymentProofPath = $request->hasFile('payment_proof')
            ? uploadFile($request->file('payment_proof'), 'uploads/merchant-onboarding')
            : null;
        $businessPhotoPath = $request->hasFile('business_photo')
            ? uploadFile($request->file('business_photo'), 'uploads/merchant-onboarding')
            : null;

        $servicesDescription = $request->input('garage_services') ?: $request->input('specialization');
        $specializationShort = $servicesDescription ? Str::limit($servicesDescription, 255) : $request->specialization;

        // Do NOT overwrite $user with null here, it will break the upgrade flow!
        // $user = null;

        DB::transaction(function () use ($request, $paymentProofPath, $businessPhotoPath, $servicesDescription, $specializationShort, &$user) {
            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'username' => Str::slug($request->name).'-'.date('Ymdhis'),
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'specialization' => $specializationShort,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'status' => 'enable',
                    'is_banned' => 'no',
                    'is_garage' => 1,
                    'is_dealer' => 0,
                    'password' => Hash::make($request->password),
                    'verification_otp' => random_int(100000, 999999),
                    'image' => $businessPhotoPath,
                ]);
            } else {
                $user->name = $request->name;
                $user->email = $request->email ?? $request->pic_email ?? $user->email;
                $user->phone = $request->phone;
                $user->address = $request->address;
                $user->specialization = $specializationShort;
                $user->latitude = $request->latitude;
                $user->longitude = $request->longitude;
                $user->is_garage = 1;
                $user->is_dealer = 0;
                if ($businessPhotoPath) {
                    $user->image = $businessPhotoPath;
                }
                $user->save();
            }

            $merchant = MerchantProfile::firstOrNew(['user_id' => $user->id]);
            $merchant->fill([
                'business_type' => MerchantProfile::BUSINESS_GARAGE,
                'subscription_plan_id' => (int) $request->subscription_plan_id,
                'business_category' => $request->garage_category,
                'showroom_type' => null,
                'garage_services_description' => $servicesDescription,
                'pic_name' => $request->pic_name,
                'pic_email' => $request->pic_email,
                'pic_phone' => $request->pic_phone,
                'invitation_code' => $request->invitation_code,
                'payment_proof_path' => $paymentProofPath,
                'business_photo_path' => $businessPhotoPath,
                'terms_accepted_at' => now(),
            ]);
            $merchant->save();

            if ($request->filled('invitation_code') && Schema::hasTable('invitation_codes')) {
                InvitationCode::where('code', $request->invitation_code)->increment('uses_count');
            }
        });

        if ($request->has('password') && $request->filled('password')) {
            MailHelper::setMailConfig();

            try {
                $template = EmailTemplate::where('id', 12)->first();
                if ($template) {
                    $subject = $template->subject;
                    $message = $template->description;
                    $message = str_replace('{{user_name}}', $request->name, $message);
                    $message = str_replace('{{varification_otp}}', $user->verification_otp, $message);

                    Mail::to($user->email)->send(new UserRegistration($message, $subject, $user));
                }
            } catch (Exception $ex) {
                Log::info($ex->getMessage());
            }
        }

        $notify_message = trans('translate.Garage account created successful, a verification OTP has been send to your mail, please verify it');

        return response()->json([
            'message' => $notify_message,
        ]);
    }

    /**
     * Mediator registration
     * POST /api/mediator/store-register
     */
    public function mediator_store_register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', 'min:4', 'max:100'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'showroom_id' => ['nullable', 'integer', 'exists:users,id'],
        ], [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'email.unique' => trans('translate.Email already exist'),
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
            'phone.required' => trans('translate.Phone is required'),
            'address.required' => trans('translate.Address is required'),
            'showroom_id.exists' => trans('translate.Showroom not found'),
        ]);

        // Verify showroom if provided
        if ($request->showroom_id) {
            $showroom = User::where('id', $request->showroom_id)
                ->where('is_dealer', 1)
                ->where('status', 'enable')
                ->first();

            if (! $showroom) {
                return response()->json([
                    'message' => trans('translate.Invalid showroom'),
                ], 403);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => Str::slug($request->name).'-'.date('Ymdhis'),
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => 'enable',
            'is_banned' => 'no',
            'is_mediator' => 1, // Set as mediator
            'showroom_id' => $request->showroom_id,
            'password' => Hash::make($request->password),
            'verification_otp' => random_int(100000, 999999),
        ]);

        MailHelper::setMailConfig();

        try {
            $template = EmailTemplate::where('id', 12)->first();
            if ($template) {
                $subject = $template->subject;
                $message = $template->description;
                $message = str_replace('{{user_name}}', $request->name, $message);
                $message = str_replace('{{varification_otp}}', $user->verification_otp, $message);

                Mail::to($user->email)->send(new UserRegistration($message, $subject, $user));
            }
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
        }

        $notify_message = trans('translate.Mediator account created successful, a verification OTP has been send to your mail, please verify it');

        return response()->json([
            'message' => $notify_message,
        ]);
    }

    /**
     * Sales registration (dealer sales / garage sales)
     * POST /api/sales/store-register
     */
    public function sales_store_register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', 'min:4', 'max:100'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'sales_partner_type' => ['required', Rule::in(['dealer', 'garage'])],
            'partner_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'email.unique' => trans('translate.Email already exist'),
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
            'phone.required' => trans('translate.Phone is required'),
            'address.required' => trans('translate.Address is required'),
        ]);

        $partner = User::where('id', $request->partner_id)
            ->where('status', 'enable')
            ->where(function ($q) use ($request) {
                if ($request->sales_partner_type === 'dealer') {
                    $q->where('is_dealer', 1);
                } else {
                    $q->where('is_garage', 1);
                }
            })
            ->first();

        if (! $partner) {
            return response()->json([
                'message' => trans('translate.Invalid showroom'),
            ], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => Str::slug($request->name).'-'.date('Ymdhis'),
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => 'enable',
            'is_banned' => 'no',
            'is_sales' => 1,
            'sales_partner_type' => $request->sales_partner_type,
            'partner_id' => $request->partner_id,
            // Backward compatibility for existing dealer-marketing flow.
            'showroom_id' => $request->sales_partner_type === 'dealer' ? $request->partner_id : null,
            'password' => Hash::make($request->password),
            'verification_otp' => random_int(100000, 999999),
        ]);

        MailHelper::setMailConfig();

        try {
            $template = EmailTemplate::where('id', 12)->first();
            if ($template) {
                $subject = $template->subject;
                $message = $template->description;
                $message = str_replace('{{user_name}}', $request->name, $message);
                $message = str_replace('{{varification_otp}}', $user->verification_otp, $message);

                Mail::to($user->email)->send(new UserRegistration($message, $subject, $user));
            }
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
        }

        return response()->json([
            'message' => trans('translate.Account created successful, a verification OTP has been send to your mail, please verify it'),
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
