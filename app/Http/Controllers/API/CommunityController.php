<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\CommunityComment;
use Str;

class CommunityController extends Controller
{
    // ──────────────────────────────────────────────
    // PUBLIC
    // ──────────────────────────────────────────────

    /**
     * GET /api/communities
     */
    public function index(Request $request)
    {
        $query = Community::where('status', 'active')
            ->withCount('members')
            ->withCount('posts')
            ->with('owner:id,name,image')
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        return response()->json([
            'communities' => $query->paginate(12),
        ]);
    }

    /**
     * GET /api/communities/{slug}
     */
    public function show($slug)
    {
        $community = Community::where('slug', $slug)
            ->where('status', 'active')
            ->withCount('members')
            ->withCount('posts')
            ->with('owner:id,name,image')
            ->firstOrFail();

        $latestMembers = CommunityMember::where('community_id', $community->id)
            ->with('user:id,name,image')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'community' => $community,
            'latest_members' => $latestMembers,
        ]);
    }

    // ──────────────────────────────────────────────
    // AUTHENTICATED
    // ──────────────────────────────────────────────

    /**
     * POST /api/user/communities
     */
    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:4096',
            'location' => 'nullable|string|max:255',
            'privacy' => 'nullable|in:public,private',
        ]);

        $slug = Str::slug($request->name) . '-' . Str::random(6);

        $data = [
            'owner_id' => $user->id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'location' => $request->location,
            'privacy' => $request->privacy ?? 'public',
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

        return response()->json([
            'message' => trans('translate.Community created successfully'),
            'community' => $community,
        ], 201);
    }

    /**
     * POST /api/user/communities/{slug}/join
     */
    public function join($slug)
    {
        $user = Auth::guard('api')->user();
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $existing = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => trans('translate.Already a member')], 409);
        }

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        return response()->json(['message' => trans('translate.Joined community')]);
    }

    /**
     * POST /api/user/communities/{slug}/leave
     */
    public function leave($slug)
    {
        $user = Auth::guard('api')->user();
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$member) {
            return response()->json(['message' => trans('translate.Not a member')], 404);
        }

        if ($member->role === 'owner') {
            return response()->json(['message' => trans('translate.Owner cannot leave')], 403);
        }

        $member->delete();

        return response()->json(['message' => trans('translate.Left community')]);
    }

    /**
     * GET /api/user/communities/{slug}/members
     */
    public function members(Request $request, $slug)
    {
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $members = CommunityMember::where('community_id', $community->id)
            ->with('user:id,name,image')
            ->orderByRaw("FIELD(role, 'owner', 'admin', 'member')")
            ->paginate(20);

        return response()->json(['members' => $members]);
    }

    // ──────────────────────────────────────────────
    // POSTS & COMMENTS
    // ──────────────────────────────────────────────

    /**
     * GET /api/user/communities/{slug}/posts
     */
    public function posts(Request $request, $slug)
    {
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $posts = CommunityPost::where('community_id', $community->id)
            ->with(['author:id,name,image', 'comments' => function ($q) {
                $q->with('author:id,name,image')->orderBy('id', 'desc')->take(3);
            }])
            ->withCount('comments')
            ->orderBy('id', 'desc')
            ->paginate(12);

        return response()->json(['posts' => $posts]);
    }

    /**
     * POST /api/user/communities/{slug}/posts
     */
    public function storePost(Request $request, $slug)
    {
        $user = Auth::guard('api')->user();
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $isMember = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => trans('translate.Must be a member to post')], 403);
        }

        $request->validate([
            'content' => 'required|string|max:5000',
            'image' => 'nullable|image|max:4096',
        ]);

        $data = [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = uploadFile($request->file('image'), 'uploads/community-posts');
        }

        $post = CommunityPost::create($data);

        return response()->json([
            'message' => trans('translate.Post created'),
            'post' => $post->load('author:id,name,image'),
        ], 201);
    }

    /**
     * POST /api/user/community-posts/{postId}/comments
     */
    public function storeComment(Request $request, $postId)
    {
        $user = Auth::guard('api')->user();
        $post = CommunityPost::findOrFail($postId);

        $isMember = CommunityMember::where('community_id', $post->community_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => trans('translate.Must be a member to comment')], 403);
        }

        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment = CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => trans('translate.Comment added'),
            'comment' => $comment->load('author:id,name,image'),
        ], 201);
    }

    /**
     * GET /api/user/my-communities
     */
    public function myCommunities(Request $request)
    {
        $user = Auth::guard('api')->user();

        $communityIds = CommunityMember::where('user_id', $user->id)->pluck('community_id');

        $communities = Community::whereIn('id', $communityIds)
            ->withCount('members')
            ->withCount('posts')
            ->orderBy('id', 'desc')
            ->paginate(12);

        return response()->json(['communities' => $communities]);
    }
}
