<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use File;
use Hash;
use Illuminate\Http\Request;
use Modules\Car\Entities\Car;
use Modules\Kyc\Entities\KycInformation;
use Modules\Subscription\Entities\SubscriptionHistory;
use Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function user_list(Request $request)
    {

        $query = User::query();

        // Filter by status
        if ($request->status) {
            if ($request->status == 'enable') {
                $query->where('status', 'enable');
            } elseif ($request->status == 'disable') {
                $query->where('status', 'disable');
            }
        } else {
            $query->where('status', 'enable');
        }

        // Filter by user type
        if ($request->user_type) {
            if ($request->user_type == 'dealer') {
                $query->where('is_dealer', 1);
            } elseif ($request->user_type == 'garage') {
                $query->where('is_garage', 1);
            } elseif ($request->user_type == 'sales') {
                $query->where('is_sales', 1);
            } elseif ($request->user_type == 'mediator') {
                $query->where('is_mediator', 1);
            } elseif ($request->user_type == 'user') {
                $query->where('is_dealer', 0)
                    ->where('is_mediator', 0)
                    ->where('is_garage', 0)
                    ->where('is_sales', 0);
            }
        }

        // Filter by banned status
        if ($request->is_banned) {
            $query->where('is_banned', $request->is_banned);
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }

        $users = $query->latest()->get();

        $title = trans('translate.User List');

        return view('admin.user.user_list', compact('users', 'title'));
    }

    public function pending_user()
    {

        $users = User::where('status', 'disable')->latest()->get();

        $title = trans('translate.Pending User');

        return view('admin.user.user_list', compact('users', 'title'));
    }

    public function user_show($id)
    {

        $user = User::findOrFail($id);

        $total_listing = Car::where('agent_id', $user->id)->count();

        $active_listing = Car::with('dealer', 'brand')->where(function ($query) {
            $query->where('expired_date', null)
                ->orWhere('expired_date', '>=', date('Y-m-d'));
        })->where(['status' => 'enable', 'approved_by_admin' => 'approved', 'agent_id' => $id])->count();

        $total_purchase = SubscriptionHistory::where('user_id', $id)->sum('plan_price');

        $total_review = Review::where('agent_id', $id)->count();

        $cars = Car::with('translate', 'brand')->where('agent_id', $user->id)->latest()->get();

        $editUserType = 'user';
        if ((int) $user->is_sales === 1) {
            $editUserType = 'sales';
        } elseif ((int) $user->is_mediator === 1) {
            $editUserType = 'mediator';
        } elseif ((int) $user->is_dealer === 1) {
            $editUserType = 'dealer';
        } elseif ((int) $user->is_garage === 1) {
            $editUserType = 'garage';
        }

        $showroomsForEdit = User::where('is_dealer', 1)->where('status', 'enable')->orderBy('name')->get();
        $garagesForEdit = User::where('is_garage', 1)->where('status', 'enable')->orderBy('name')->get();

        return view('admin.user.user_show', [
            'user' => $user,
            'total_listing' => $total_listing,
            'total_purchase' => $total_purchase,
            'total_review' => $total_review,
            'active_listing' => $active_listing,
            'cars' => $cars,
            'editUserType' => $editUserType,
            'showroomsForEdit' => $showroomsForEdit,
            'garagesForEdit' => $garagesForEdit,
        ]);
    }

    public function update(Request $request, $id)
    {

        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required|max:220',
            'user_type' => 'required|in:user,dealer,garage,mediator,sales',
            'showroom_id' => 'nullable|integer|exists:users,id',
            'partner_id' => 'nullable|integer|exists:users,id',
            'sales_partner_type' => 'nullable|in:dealer,garage',
        ];
        $customMessages = [
            'name.required' => trans('translate.Name is required'),
            'phone.required' => trans('translate.Phone is required'),
            'address.required' => trans('translate.Address is required'),
            'user_type.required' => trans('translate.User type is required'),
            'showroom_id.exists' => trans('translate.Showroom not found'),
        ];
        $this->validate($request, $rules, $customMessages);

        if ($response = $this->validateMitraRoleInputs($request)) {
            return $response;
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->designation = $request->designation;
        $user->about_me = $request->about_me;
        $user->status = $request->status ? 'enable' : 'disable';
        $this->assignUserRole($user, $request);
        $user->save();

        $notification = trans('translate.User updated successful');
        $notification = ['messege' => $notification, 'alert-type' => 'success'];

        return redirect()->back()->with($notification);

    }

    public function user_destroy($id)
    {

        $total_car = Car::where('agent_id', $id)->count();
        if ($total_car > 0) {
            $notification = trans('translate.You can not delete this user, multiple listing available under this user');
            $notification = ['messege' => $notification, 'alert-type' => 'error'];

            return redirect()->route('admin.user-list')->with($notification);
        }

        $user = User::find($id);
        $user_image = $user->image;

        if ($user_image) {
            if (File::exists(public_path().'/'.$user_image)) {
                unlink(public_path().'/'.$user_image);
            }
        }

        Review::where('user_id', $id)->delete();
        SubscriptionHistory::where('user_id', $id)->delete();
        Wishlist::where('user_id', $id)->delete();
        KycInformation::where('user_id', $id)->delete();

        $user->delete();

        $notification = trans('translate.Delete Successfully');
        $notification = ['messege' => $notification, 'alert-type' => 'success'];

        return redirect()->route('admin.user-list')->with($notification);

    }

    public function user_status($id)
    {
        $user = User::findOrFail($id);
        if ($user->status == 'enable') {
            $user->status = 'disable';
            $user->save();
            $message = trans('translate.Status Changed Successfully');
        } else {
            $user->status = 'enable';
            $user->save();
            $message = trans('translate.Status Changed Successfully');
        }

        return response()->json($message);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        $title = trans('translate.Create User');
        $showrooms = User::where('is_dealer', 1)->where('status', 'enable')->get();
        $garages = User::where('is_garage', 1)->where('status', 'enable')->get();

        return view('admin.user.user_create', compact('title', 'showrooms', 'garages'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|max:100|confirmed',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string',
            'designation' => 'nullable|string|max:255',
            'user_type' => 'required|in:user,dealer,garage,mediator,sales',
            'showroom_id' => 'nullable|integer|exists:users,id',
            'partner_id' => 'nullable|integer|exists:users,id',
            'sales_partner_type' => 'nullable|in:dealer,garage',
            'status' => 'nullable|in:enable,disable',
            'is_banned' => 'nullable|in:yes,no',
        ];

        $customMessages = [
            'name.required' => trans('translate.Name is required'),
            'email.required' => trans('translate.Email is required'),
            'email.unique' => trans('translate.Email already exist'),
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
            'user_type.required' => trans('translate.User type is required'),
            'showroom_id.exists' => trans('translate.Showroom not found'),
        ];

        $this->validate($request, $rules, $customMessages);

        if ($response = $this->validateMitraRoleInputs($request)) {
            return $response;
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = Str::slug($request->name).'-'.date('Ymdhis');
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->country = $request->country;
        $user->designation = $request->designation;
        $user->password = Hash::make($request->password);
        $user->status = $request->status ?? 'enable';
        $user->is_banned = $request->is_banned ?? 'no';
        $user->email_verified_at = now(); // Auto verify when created by admin

        $this->assignUserRole($user, $request);

        $user->save();

        $notification = trans('translate.User created successfully');
        $notification = ['messege' => $notification, 'alert-type' => 'success'];

        return redirect()->route('admin.user-list')->with($notification);
    }

    /**
     * Reset user password
     */
    public function reset_password(Request $request, $id)
    {
        $rules = [
            'password' => 'required|string|min:4|max:100|confirmed',
        ];

        $customMessages = [
            'password.required' => trans('translate.Password is required'),
            'password.confirmed' => trans('translate.Confirm password does not match'),
            'password.min' => trans('translate.You have to provide minimum 4 character password'),
        ];

        $this->validate($request, $rules, $customMessages);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        $notification = trans('translate.Password reset successfully');
        $notification = ['messege' => $notification, 'alert-type' => 'success'];

        return redirect()->back()->with($notification);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function validateMitraRoleInputs(Request $request)
    {
        if ($request->user_type == 'mediator' && $request->filled('showroom_id')) {
            $showroom = User::where('id', $request->showroom_id)
                ->where('is_dealer', 1)
                ->where('status', 'enable')
                ->first();

            if (! $showroom) {
                $notification = trans('translate.Invalid showroom');
                $notification = ['messege' => $notification, 'alert-type' => 'error'];

                return redirect()->back()->withInput()->with($notification);
            }
        }

        if ($request->user_type == 'sales') {
            if (! $request->partner_id || ! $request->sales_partner_type) {
                $notification = trans('translate.Invalid showroom');
                $notification = ['messege' => $notification, 'alert-type' => 'error'];

                return redirect()->back()->withInput()->with($notification);
            }

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
                $notification = trans('translate.Invalid showroom');
                $notification = ['messege' => $notification, 'alert-type' => 'error'];

                return redirect()->back()->withInput()->with($notification);
            }
        }

        return null;
    }

    protected function assignUserRole(User $user, Request $request): void
    {
        $user->is_dealer = 0;
        $user->is_garage = 0;
        $user->is_mediator = 0;
        $user->is_sales = 0;
        $user->showroom_id = null;
        $user->partner_id = null;
        $user->sales_partner_type = null;

        if ($request->user_type == 'dealer') {
            $user->is_dealer = 1;
        } elseif ($request->user_type == 'mediator') {
            $user->is_mediator = 1;
            $user->showroom_id = $request->showroom_id;
        } elseif ($request->user_type == 'garage') {
            $user->is_garage = 1;
        } elseif ($request->user_type == 'sales') {
            $user->is_sales = 1;
            $user->sales_partner_type = $request->sales_partner_type;
            $user->partner_id = $request->partner_id;
            $user->showroom_id = $request->sales_partner_type === 'dealer' ? $request->partner_id : null;
        }
    }
}
