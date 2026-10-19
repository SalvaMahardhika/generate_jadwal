<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Endpoint API untuk statistik dashboard.
     */
    public function stats(): JsonResponse
    {
        $stats = $this->dashboardService->getStats();

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }
}