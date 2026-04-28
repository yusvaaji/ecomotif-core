<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\CommunityComment;
use App\Models\CommunityPostLike;
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
            $otherMembersCount = CommunityMember::where('community_id', $community->id)
                ->where('user_id', '!=', $user->id)
                ->count();

            if ($otherMembersCount > 0) {
                return response()->json([
                    'message' => 'Owner tidak bisa keluar dari komunitas. Silakan alihkan kepemilikan (role owner) ke member lain terlebih dahulu.'
                ], 403);
            }
            
            // Jika tidak ada member lain, owner boleh keluar dan komunitas dihapus
            $member->delete();
            $community->delete();
            return response()->json(['message' => trans('translate.Left community and community deleted as there were no other members')]);
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

    /**
     * PUT /api/user/communities/{slug}/members/{userId}/role
     */
    public function updateMemberRole(Request $request, $slug, $userId)
    {
        $user = Auth::guard('api')->user();
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $currentUserMember = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$currentUserMember || !in_array($currentUserMember->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Anda tidak memiliki akses (Unauthorized)'], 403);
        }

        $request->validate([
            'role' => 'required|in:owner,admin,member',
        ]);

        $targetMember = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Hanya owner yang bisa transfer ownership
        if ($request->role === 'owner' && $currentUserMember->role !== 'owner') {
             return response()->json(['message' => 'Hanya owner yang dapat mentransfer kepemilikan'], 403);
        }

        // Admin tidak bisa mengubah role owner
        if ($targetMember->role === 'owner' && $currentUserMember->role !== 'owner') {
            return response()->json(['message' => 'Admin tidak dapat mengubah role milik owner'], 403);
        }

        if ($request->role === 'owner') {
            // Transfer ownership: Owner lama jadi admin
            $currentUserMember->update(['role' => 'admin']);
            $community->update(['owner_id' => $targetMember->user_id]);
        }

        $targetMember->update(['role' => $request->role]);

        return response()->json([
            'message' => 'Role berhasil diperbarui',
            'member' => $targetMember
        ]);
    }

    /**
     * DELETE /api/user/communities/{slug}/members/{userId}
     */
    public function kickMember($slug, $userId)
    {
        $user = Auth::guard('api')->user();
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $currentUserMember = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$currentUserMember || !in_array($currentUserMember->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Anda tidak memiliki akses (Unauthorized)'], 403);
        }

        $targetMember = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Tidak bisa kick owner
        if ($targetMember->role === 'owner') {
            return response()->json(['message' => 'Owner tidak bisa dikeluarkan dari komunitas'], 403);
        }

        // Admin tidak bisa kick sesama admin (kecuali owner yang kick)
        if ($targetMember->role === 'admin' && $currentUserMember->role !== 'owner') {
            return response()->json(['message' => 'Admin tidak dapat mengeluarkan sesama admin'], 403);
        }

        $targetMember->delete();

        return response()->json(['message' => 'Member berhasil dikeluarkan']);
    }

    // ──────────────────────────────────────────────
    // POSTS & COMMENTS
    // ──────────────────────────────────────────────

    /**
     * GET /api/user/communities/{slug}/posts
     */
    public function posts(Request $request, $slug)
    {
        $user = Auth::guard('api')->user();
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $posts = CommunityPost::where('community_id', $community->id)
            ->with(['author:id,name,image', 'comments' => function ($q) {
                $q->with('author:id,name,image')->orderBy('id', 'desc')->take(3);
            }])
            ->withCount('comments')
            ->withCount('likes')
            ->orderBy('id', 'desc')
            ->paginate(12);

        // Add is_liked_by_user for each post
        $posts->getCollection()->transform(function ($post) use ($user) {
            $post->is_liked_by_user = $post->isLikedBy($user->id);
            return $post;
        });

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
     * GET /api/user/communities/{slug}/posts/{postId}/comments
     */
    public function getComments($slug, $postId)
    {
        $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $post = CommunityPost::where('id', $postId)->where('community_id', $community->id)->firstOrFail();

        $comments = CommunityComment::where('post_id', $post->id)
            ->with('author:id,name,image')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json(['comments' => $comments]);
    }

    /**
     * POST /api/user/communities/{slug}/posts/{postId}/like
     */
    public function toggleLike($slug, $postId)
    {
        try {
            $user = Auth::guard('api')->user();
            $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

            $post = CommunityPost::where('id', $postId)->where('community_id', $community->id)->firstOrFail();

            $isMember = CommunityMember::where('community_id', $community->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isMember) {
                return response()->json(['message' => 'Must be a member to like this post'], 403);
            }

            $existingLike = CommunityPostLike::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingLike) {
                // Unlike
                $existingLike->delete();
                $liked = false;
                $message = 'Post unliked';
            } else {
                // Like
                CommunityPostLike::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ]);
                $liked = true;
                $message = 'Post liked';
            }

            $likesCount = CommunityPostLike::where('post_id', $post->id)->count();

            return response()->json([
                'message' => $message,
                'liked' => $liked,
                'likes_count' => $likesCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error toggling like',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/user/communities/{slug}/posts/{postId}/like
     */
    public function removeLike($slug, $postId)
    {
        try {
            $user = Auth::guard('api')->user();
            $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

            $post = CommunityPost::where('id', $postId)->where('community_id', $community->id)->firstOrFail();

            $like = CommunityPostLike::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$like) {
                return response()->json(['message' => 'You have not liked this post'], 404);
            }

            $like->delete();
            $likesCount = CommunityPostLike::where('post_id', $post->id)->count();

            return response()->json([
                'message' => 'Like removed',
                'likes_count' => $likesCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error removing like',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/user/my-likes
     */
    public function getUserLikes(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $likedPosts = CommunityPostLike::where('user_id', $user->id)
                ->with(['post' => function ($q) {
                    $q->with(['community:id,name,slug', 'author:id,name,image']);
                }])
                ->orderBy('created_at', 'desc')
                ->paginate(12);

            // Transform data untuk response yang lebih bersih
            $posts = $likedPosts->getCollection()->map(function ($like) use ($user) {
                $post = $like->post;
                if ($post) {
                    // Hitung comments dan likes secara manual untuk menghindari error
                    $post->comments_count = CommunityComment::where('post_id', $post->id)->count();
                    $post->likes_count = CommunityPostLike::where('post_id', $post->id)->count();
                    $post->is_liked_by_user = true; // Sudah pasti true karena ini dari likes user
                    $post->liked_at = $like->created_at;
                    return $post;
                }
                return null;
            })->filter()->values();

            $likedPosts->setCollection($posts);

            return response()->json(['liked_posts' => $likedPosts]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching liked posts',
                'error' => $e->getMessage()
            ], 500);
        }
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

    // ──────────────────────────────────────────────
    // DELETE POST & COMMENT
    // ──────────────────────────────────────────────

    /**
     * DELETE /api/user/communities/{slug}/posts/{postId}
     * Hanya pemilik postingan yang dapat menghapus.
     */
    public function deletePost($slug, $postId)
    {
        try {
            $user = Auth::guard('api')->user();
            $community = Community::where('slug', $slug)->where('status', 'active')->firstOrFail();

            $post = CommunityPost::where('id', $postId)
                ->where('community_id', $community->id)
                ->firstOrFail();

            // Hanya pemilik postingan atau owner komunitas yang bisa hapus
            $isOwner = $post->user_id === $user->id;
            $isCommunityOwner = CommunityMember::where('community_id', $community->id)
                ->where('user_id', $user->id)
                ->where('role', 'owner')
                ->exists();

            if (!$isOwner && !$isCommunityOwner) {
                return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus postingan ini'], 403);
            }

            // Hapus likes dan komentar terkait terlebih dahulu
            CommunityPostLike::where('post_id', $post->id)->delete();
            CommunityComment::where('post_id', $post->id)->delete();

            // Hapus gambar jika ada
            if ($post->image && file_exists(public_path($post->image))) {
                unlink(public_path($post->image));
            }

            $post->delete();

            return response()->json(['message' => 'Postingan berhasil dihapus']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Postingan tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus postingan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/user/community-posts/{postId}/comments/{commentId}
     * Hanya pemilik komentar yang dapat menghapus.
     */
    public function deleteComment($postId, $commentId)
    {
        try {
            $user = Auth::guard('api')->user();
            $post = CommunityPost::findOrFail($postId);

            $comment = CommunityComment::where('id', $commentId)
                ->where('post_id', $post->id)
                ->firstOrFail();

            // Hanya pemilik komentar, pemilik post, atau owner komunitas yang bisa hapus
            $isCommentOwner = $comment->user_id === $user->id;
            $isPostOwner = $post->user_id === $user->id;
            $isCommunityOwner = CommunityMember::where('community_id', $post->community_id)
                ->where('user_id', $user->id)
                ->where('role', 'owner')
                ->exists();

            if (!$isCommentOwner && !$isPostOwner && !$isCommunityOwner) {
                return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus komentar ini'], 403);
            }

            $comment->delete();

            return response()->json(['message' => 'Komentar berhasil dihapus']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Komentar tidak ditemukan'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus komentar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
