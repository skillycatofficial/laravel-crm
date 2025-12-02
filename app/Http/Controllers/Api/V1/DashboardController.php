<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Webkul\Admin\Helpers\Dashboard;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  Dashboard  $dashboardHelper
     * @return void
     */
    public function __construct(protected Dashboard $dashboardHelper)
    {
    }

    /**
     * Get dashboard statistics.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Cache dashboard stats for 5 minutes for mobile performance
        $cacheKey = 'mobile_dashboard_' . auth()->id();
        
        $data = cache()->remember($cacheKey, 300, function() use ($request) {
            $type = $request->input('type', 'overview');

            return match($type) {
                'overview' => $this->getOverview(),
                'revenue' => $this->getRevenueStats(),
                'leads' => $this->getTotalLeadsStats(),
                'revenue-by-sources' => $this->getLeadsStatsBySources(),
                'revenue-by-types' => $this->getLeadsStatsByTypes(),
                'top-products' => $this->getTopSellingProducts(),
                'top-persons' => $this->getTopPersons(),
                'open-leads-by-states' => $this->getOpenLeadsByStates(),
                default => $this->getOverview(),
            };
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'date_range' => $this->dashboardHelper->getDateRange(),
        ], 200);
    }

    /**
     * Get overview statistics.
     *
     * @return array
     */
    protected function getOverview()
    {
        return [
            'revenue' => $this->dashboardHelper->getRevenueStats(),
            'overall' => $this->dashboardHelper->getOverAllStats(),
        ];
    }

    /**
     * Get revenue statistics.
     *
     * @return array
     */
    protected function getRevenueStats()
    {
        return $this->dashboardHelper->getRevenueStats();
    }

    /**
     * Get total leads statistics.
     *
     * @return array
     */
    protected function getTotalLeadsStats()
    {
        return $this->dashboardHelper->getTotalLeadsStats();
    }

    /**
     * Get leads statistics by sources.
     *
     * @return mixed
     */
    protected function getLeadsStatsBySources()
    {
        return $this->dashboardHelper->getLeadsStatsBySources();
    }

    /**
     * Get leads statistics by types.
     *
     * @return mixed
     */
    protected function getLeadsStatsByTypes()
    {
        return $this->dashboardHelper->getLeadsStatsByTypes();
    }

    /**
     * Get top selling products.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getTopSellingProducts()
    {
        return $this->dashboardHelper->getTopSellingProducts();
    }

    /**
     * Get top persons.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getTopPersons()
    {
        return $this->dashboardHelper->getTopPersons();
    }

    /**
     * Get open leads by states.
     *
     * @return mixed
     */
    protected function getOpenLeadsByStates()
    {
        return $this->dashboardHelper->getOpenLeadsByStates();
    }
}

