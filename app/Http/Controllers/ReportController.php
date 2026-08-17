<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = $this->reportService->getReportData($user);

        return view('reports.index', compact('stats'));
    }
}
