<?php

namespace App\Http\Controllers;

use App\Models\PostReaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function toggleReact(Request $request, $postId)
    {
        $userId = auth()->id();

        $reactoin = PostReaction::where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();

        if ($reactoin) {
            $reactoin->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Reaction removed'
            ]);
        } else {
            PostReaction::create([
                'post_id' => $postId,
                'user_id' => $userId,
                'type' => 'like'
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Reaction added'
            ]);
        }
    }
}
