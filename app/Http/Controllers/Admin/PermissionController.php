<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function data(Request $request)
    {
        $this->authorize('manage permissions');

        $permissions = Permission::orderBy('name')->get();

        return response()->json([
            'permissions' => $permissions
        ]);
    }
}
