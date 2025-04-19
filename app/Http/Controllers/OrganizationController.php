<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Organization;
use Auth;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        return response()->json(
            [
                'message' => 'Organizations Fetched Successfully!',
                'data' => Organization::all()
            ],
            200
        );
    }

    public function getDriverOrganizations(Request $request)
    {
        return response()->json(
            [
                'message' => 'Organizations Fetched Successfully!',
                'organizations' => Auth::user()->driver->organizations
            ],
            200
        );
    }
}
