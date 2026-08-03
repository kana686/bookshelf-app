<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\LikeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store(Request $request, Review $review, LikeService $likeService): RedirectResponse
    {
        $likeService->toggleLike($review, $request->user());

        return back();
    }
}
