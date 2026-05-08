<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posts;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use PHPUnit\Logging\OpenTestReporting\Status;

class PostsController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user() ? $request->user()->id : null;

        $posts = Posts::with(['user', 'reactions'])->latest()->get()->map(function ($post) use ($userId) {
            $post->is_liked = $post->reactions->contains('user_id', $userId);

            return $post;
        });

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function show($id)
    {
        $post = Posts::with('user')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $post
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $validated['image_url'] = $path;
        }


        $post = $request->user()->posts()->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Post created successfully!',
            'data' => $post->load('user')
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Posts::findOrFail($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate(
            [
                'title' => 'sometimes|required|string|max:255',
                'body' => 'sometimes|required|string',
                'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            ]
        );

        $data = $request->except('image');

        if ($request->hasFile('image')) {

            if ($post->image_url) {
                Storage::disk('public')->delete($post->image_url);
            } elseif (!$post->image_url) {
                return response()->json([
                    'success' => false,
                    'status' => $post->status->code
                ]);
            }

            $path = $request->file('image')->store('images', 'public');
            $data['image_url'] = $path;
        }

        $post->update($data);

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    public function delete($id)
    {
        $post = Posts::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found or unauthorized'
            ], 404);
        }

        $post->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Post deleted successfully'
        ]);
    }
}
