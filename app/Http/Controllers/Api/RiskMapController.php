<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RiskMapController extends Controller
{
    public function index()
    {
        return DB::table('barangay_risk_summary')->get();
    }
}