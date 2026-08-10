<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\LikeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    protected LikeService $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    public function store(Request $request, Review $review): RedirectResponse
    {
        $this->authorize('like', $review);

        $this->likeService->toggleLike($review, $request->user());

        return back();
    }
}
