<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Constant;
use App\Http\Controllers\MainController;
use App\Http\Resources\DefaultResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends MainController
{
    public function index(Request $request)
    {
        $totalUsers = User::whereRole(Constant::USER)->count();

        $currentYearUser = request("user_by_year", now()->year);
        // Prepare an array to hold the results
        $monthsCountUsers = array_fill(0, 12, 0); // Initialize an array with 12 zeros for each month

        // Loop through each month from January to the current month
        for ($month = 1; $month <= 12; $month++) {
            // Calculate the start and end dates for the current month
            $startOfMonth = Carbon::create($currentYearUser, $month)->startOfMonth();
            $endOfMonth = Carbon::create($currentYearUser, $month)->endOfMonth();

            // Count the number of users created in this month
            $count = User::whereRole(Constant::USER)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            // Store the count in the corresponding month index (month - 1 because array index starts at 0)
            $monthsCountUsers[$month - 1] = $count;
        }

        $data = [
            'total_users' => $totalUsers,
            'monthly_users' => $monthsCountUsers,
        ];

        return $this->response->success(new DefaultResource($data));
    }
}
