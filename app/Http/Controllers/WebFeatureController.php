<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityComment;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\GarageService;
use App\Models\Notification;
use App\Models\ServiceBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebFeatureController extends Controller
{
    public function garages(Request $request)
    {
        $query = User::where([
            'status' => 'enable',
            'is_banned' => 'no',
            'is_garage' => 1,
        ])->whereNotNull('email_verified_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('specialization', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%');
            });
        }

        $garages = $query->withCount('garageServices')->latest()->paginate(12);

        return view('features.garages', compact('garages'));
    }

    public function communities(Request $request)
    {
        $query = Community::where('status', 'active')
            ->with('owner:id,name,image')
            ->withCount(['members', 'posts']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        $communities = $query->latest()->paginate(12);

        return view('features.communities', compact('communities'));
    }

    public function garageShow($id)
    {
        $garage = User::where(['status' => 'enable', 'is_banned' => 'no', 'is_garage' => 1])
            ->whereNotNull('email_verified_at')
            ->with(['garageServices' => function ($q) {
                $q->where('status', 'active')->orderBy('id');
            }])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->find($id);

        if (!$garage) {
            abort(404);
        }

        return view('features.garage_show', compact('garage'));
    }

    public function communityShow($slug)
    {
        $community = Community::where('slug', $slug)
            ->where('status', 'active')
            ->with('owner:id,name,image')
            ->withCount(['members', 'posts'])
            ->firstOrFail();

        $latestMembers = CommunityMember::where('community_id', $community->id)
            ->with('user:id,name,image')
            ->orderByDesc('id')
            ->take(12)
            ->get();

        $posts = CommunityPost::where('community_id', $community->id)
            ->with([
                'author:id,name,image',
                'comments' => function ($q) {
                    $q->with('author:id,name,image')->latest()->take(3);
                },
            ])
            ->withCount('comments')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $isMember = false;
        $memberRole = null;
        if (Auth::guard('web')->check()) {
            $m = CommunityMember::where('community_id', $community->id)
                ->where('user_id', Auth::guard('web')->id())
                ->first();
            $isMember = (bool) $m;
            $memberRole = $m?->role;
        }

        return view('features.community_show', compact(
            'community',
            'latestMembers',
            'posts',
            'isMember',
            'memberRole'
        ));
    }

    public function storeServiceBooking(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'garage_id' => 'required|integer',
            'garage_service_id' => 'required|integer|exists:garage_services,id',
            'service_type' => 'required|in:walk_in,home_service',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_address' => 'required_if:service_type,home_service|nullable|string',
            'vehicle_brand' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|string|max:10',
            'vehicle_plate' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $service = GarageService::where('id', $validated['garage_service_id'])
            ->where('garage_id', $validated['garage_id'])
            ->where('status', 'active')
            ->firstOrFail();

        ServiceBooking::create([
            'order_id' => 'SB-' . strtoupper(substr(uniqid(), -8)),
            'user_id' => $user->id,
            'garage_id' => $validated['garage_id'],
            'garage_service_id' => $service->id,
            'service_type' => $validated['service_type'],
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'] ?? null,
            'vehicle_brand' => $validated['vehicle_brand'] ?? null,
            'vehicle_model' => $validated['vehicle_model'] ?? null,
            'vehicle_year' => $validated['vehicle_year'] ?? null,
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'total_price' => $service->price,
            'status' => ServiceBooking::STATUS_PENDING,
        ]);

        return redirect()
            ->route('user.service-bookings')
            ->with('success', trans('translate.Booking created successfully'));
    }

    public function joinCommunity($slug)
    {
        $user = Auth::guard('web')->user();
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $existing = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('error', trans('translate.Already a member'));
        }

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        return back()->with('success', trans('translate.Joined community'));
    }

    public function leaveCommunity($slug)
    {
        $user = Auth::guard('web')->user();
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$member) {
            return back()->with('error', trans('translate.Not a member'));
        }

        if ($member->role === 'owner') {
            return back()->with('error', trans('translate.Owner cannot leave'));
        }

        $member->delete();

        return back()->with('success', trans('translate.Left community'));
    }

    public function storeCommunityPost(Request $request, $slug)
    {
        $user = Auth::guard('web')->user();
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $isMember = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return back()->with('error', trans('translate.Must be a member to post'));
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'image' => 'nullable|image|max:4096',
        ]);

        $data = [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
        ];

        if ($request->hasFile('image')) {
            $data['image'] = uploadFile($request->file('image'), 'uploads/community-posts');
        }

        CommunityPost::create($data);

        return back()->with('success', trans('translate.Post created'));
    }

    public function storeCommunityComment(Request $request, $postId)
    {
        $user = Auth::guard('web')->user();
        $post = CommunityPost::with('community')->findOrFail($postId);

        $isMember = CommunityMember::where('community_id', $post->community_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return back()->with('error', trans('translate.Must be a member to comment'));
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
        ]);

        return back()->with('success', trans('translate.Comment added'));
    }

    public function createCommunity()
    {
        return view('profile.community_create');
    }

    public function storeCommunity(Request $request)
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'location' => 'nullable|string|max:255',
            'privacy' => 'nullable|in:public,private',
            'image' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:4096',
        ]);

        $slug = Str::slug($validated['name']) . '-' . Str::lower(Str::random(6));
        $data = [
            'owner_id' => $user->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'privacy' => $validated['privacy'] ?? 'public',
            'status' => 'active',
        ];

        if ($request->hasFile('image')) {
            $data['image'] = uploadFile($request->file('image'), 'uploads/communities');
        }
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = uploadFile($request->file('cover_image'), 'uploads/communities');
        }

        $community = Community::create($data);

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        return redirect()->route('community.show', $community->slug)
            ->with('success', trans('translate.Community created successfully'));
    }

    public function userServiceBookings()
    {
        $user = Auth::guard('web')->user();
        $bookings = ServiceBooking::with(['service', 'garage'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('profile.service_bookings', compact('bookings'));
    }

    public function userServiceBookingShow($id)
    {
        $user = Auth::guard('web')->user();
        $booking = ServiceBooking::with(['service', 'garage'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return view('profile.service_booking_show', compact('booking'));
    }

    public function cancelServiceBooking($id)
    {
        $user = Auth::guard('web')->user();
        $booking = ServiceBooking::where('user_id', $user->id)->findOrFail($id);

        if (!in_array($booking->status, [ServiceBooking::STATUS_PENDING, ServiceBooking::STATUS_CONFIRMED])) {
            return back()->with('error', trans('translate.Booking cannot be cancelled'));
        }

        $booking->status = ServiceBooking::STATUS_CANCELLED;
        $booking->save();

        return back()->with('success', trans('translate.Booking cancelled'));
    }

    public function userWallet()
    {
        $user = Auth::guard('web')->user();
        $wallet = $user->wallet;
        $transactions = $wallet ? $wallet->transactions()->latest()->paginate(15) : collect([]);

        return view('profile.wallet', compact('wallet', 'transactions'));
    }

    public function userNotifications()
    {
        $user = Auth::guard('web')->user();
        $notifications = Notification::where('user_id', $user->id)->latest()->paginate(20);

        return view('profile.notifications', compact('notifications'));
    }

    public function readNotification($id)
    {
        $user = Auth::guard('web')->user();
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return back()->with('success', 'Notification marked as read.');
    }

    public function readAllNotifications()
    {
        $user = Auth::guard('web')->user();
        Notification::where('user_id', $user->id)->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function userCommunities()
    {
        $user = Auth::guard('web')->user();
        $communityIds = CommunityMember::where('user_id', $user->id)->pluck('community_id');
        $communities = Community::whereIn('id', $communityIds)
            ->withCount(['members', 'posts'])
            ->latest()
            ->paginate(15);

        return view('profile.communities', compact('communities'));
    }

    public function garageDashboard()
    {
        $user = Auth::guard('web')->user();
        abort_if($user->is_garage != 1, 403);

        $totalServices = GarageService::where('garage_id', $user->id)->count();
        $totalBookings = ServiceBooking::where('garage_id', $user->id)->count();
        $pendingBookings = ServiceBooking::where('garage_id', $user->id)->where('status', ServiceBooking::STATUS_PENDING)->count();
        $completedBookings = ServiceBooking::where('garage_id', $user->id)->where('status', ServiceBooking::STATUS_COMPLETED)->count();

        $services = GarageService::where('garage_id', $user->id)->latest()->paginate(10);
        $bookings = ServiceBooking::with('service', 'customer')
            ->where('garage_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('profile.garage_dashboard', compact(
            'totalServices',
            'totalBookings',
            'pendingBookings',
            'completedBookings',
            'services',
            'bookings'
        ));
    }

    public function storeGarageService(Request $request)
    {
        $user = Auth::guard('web')->user();
        abort_if($user->is_garage != 1, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $validated;
        $data['garage_id'] = $user->id;
        $data['status'] = 'active';

        if ($request->hasFile('image')) {
            $data['image'] = uploadFile($request->file('image'), 'uploads/garage-services');
        }

        GarageService::create($data);

        return back()->with('success', trans('translate.Service created successfully'));
    }

    public function updateGarageService(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        abort_if($user->is_garage != 1, 403);

        $service = GarageService::where('garage_id', $user->id)->findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $service->status = $validated['status'];
        $service->save();

        return back()->with('success', trans('translate.Service updated'));
    }

    public function deleteGarageService($id)
    {
        $user = Auth::guard('web')->user();
        abort_if($user->is_garage != 1, 403);

        $service = GarageService::where('garage_id', $user->id)->findOrFail($id);
        $service->delete();

        return back()->with('success', trans('translate.Service deleted'));
    }

    public function updateGarageBookingStatus(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        abort_if($user->is_garage != 1, 403);

        $booking = ServiceBooking::where('garage_id', $user->id)->findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:confirmed,in_progress,completed,cancelled',
            'garage_notes' => 'nullable|string|max:2000',
        ]);

        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed'],
        ];
        $allowed = $allowedTransitions[$booking->status] ?? [];

        if (!in_array($validated['status'], $allowed)) {
            return back()->with('error', trans('translate.Invalid status transition'));
        }

        $booking->status = $validated['status'];
        $booking->garage_notes = $validated['garage_notes'] ?? $booking->garage_notes;
        $booking->save();

        return back()->with('success', trans('translate.Booking status updated'));
    }
}

