<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $complaints = Complaint::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'complaints' => [
                'pending' => $complaints->get('pending', 0),
                'reviewing' => $complaints->get('reviewing', 0),
                'resolved' => $complaints->get('resolved', 0),
                'rejected' => $complaints->get('rejected', 0),
            ],
        ], 200);
    }
}
