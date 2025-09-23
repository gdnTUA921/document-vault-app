<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\File;
use App\Models\FileShare;
use App\Models\AuditLog;
use Carbon\Carbon;

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
        $staff = null;

        if ($u->role === 'admin') {
            $admin = [
                'users_total'      => User::count(),
                'files_total'      => File::count(),
                'audit_logs_total' => AuditLog::count(),
                'shares_total'     => FileShare::count(),
            ];

            // ✅ Recently uploaded files (last 7 days) - GLOBAL
            $recentFiles = File::with('user:id,first_name,last_name,email')
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['id','title','user_id','mime_type','size_bytes','created_at']);
        }
        else if ($u->role === 'staff') {
            $deptId = $u->department_id;

            $staff = [
                'users_total'  => User::where('department_id', $deptId)->count(),
                'files_total'  => File::where('department_id', $deptId)->count(),
                'shares_total' => FileShare::whereHas('file', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                })->count(),
            ];

            // ✅ Recently uploaded files (last 7 days) - DEPARTMENT ONLY
            $recentFiles = File::with('user:id,first_name,last_name,email')
                ->where('department_id', $deptId)
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['id','title','user_id','mime_type','size_bytes','created_at']);
        }
        else {
            // ✅ Recently uploaded files (last 7 days) - SPECIFIC USER ONLY
            $recentFiles = File::with('user:id,first_name,last_name,email')
                ->where('user_id', $u->id)
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['id','title','user_id','mime_type','size_bytes','created_at']);
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
            'recent_files'    => $recentFiles, // 👈 Add this for frontend
            'admin'           => $admin,
            'staff'           => $staff,
        ]);
    }
}
