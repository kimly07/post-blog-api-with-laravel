<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posts;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use PHPUnit\Logging\OpenTestReporting\Status;

class PostsController extends Controller
{
    public function index()
    {
        $posts = Posts::all();
        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
        return view('posts.index', ['posts' => $posts]);
    }

    public function show($id)
    {
        $post = Posts::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $post
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'body' => 'required|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $data['image_url'] = $path;
        }
        $post = Posts::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $post
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $post = Posts::findOrFail($id);

        $request->validate(
            [
                'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            ]
        );

        $data = $request->all();

        if ($request->hasFile('image')) {

            if ($post->image_url) {
                Storage::disk('public')->delete($post->image_url);
            } elseif (!$post->image_url) {
                return response()->json([
                    'success' => false,
                    'status' => $post->status->code
                ]);
            }

            $path = $request->file('image')->store('image', 'public');
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
        $post = Posts::findOrFail($id);

        if ($post->image_url) {
            Storage::disk('public')->delete($post->image_url);
        }

        $post->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Post deleted successfully'
        ]);
    }
}
