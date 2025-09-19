<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\File;
use App\Models\FileShare;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $u = Auth::guard('api')->user();

        $myFiles      = File::where('user_id', $u->id)->count();
        $sharedWithMe = FileShare::where('shared_with', $u->id)->count();
        $deptFiles    = $u->department_id
            ? File::where('department_id', $u->department_id)->count()
            : 0;

        $recent = AuditLog::with(['user:id,first_name,last_name,email', 'file:id,title'])
            ->where(function ($q) use ($u) {
                $q->where('user_id', $u->id)
                  ->orWhereIn('file_id', File::where('user_id', $u->id)->pluck('id'));
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $admin = null;
        if ($u->role === 'admin') {
            $admin = [
                'users_total'      => User::count(),
                'files_total'      => File::count(),
                'audit_logs_total' => AuditLog::count(),
            ];
        }

        return response()->json([
            'user' => [
                'id'            => $u->id,
                'first_name'    => $u->first_name,
                'last_name'     => $u->last_name,
                'role'          => $u->role,
                'department_id' => $u->department_id,
            ],
            'counts' => [
                'my_files'         => $myFiles,
                'shared_with_me'   => $sharedWithMe,
                'department_files' => $deptFiles,
            ],
            'recent_activity' => $recent,
            'admin' => $admin,
        ]);
    }
}
