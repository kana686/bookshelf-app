<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\View\View;

class RankingController extends Controller
{
    protected RankingService $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    public function index(): View
    {
        $rankedBooks = $this->rankingService->getRanking();

        return view('ranking.index', compact('rankedBooks'));
    }
}
