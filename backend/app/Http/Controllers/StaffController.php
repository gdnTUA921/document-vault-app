<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Welcome Staff!']);
    }

    // List users in same department (with optional search + department info)
    public function departmentUsers(Request $request)
    {
        $u = Auth::guard('api')->user();
        if (!$u->department_id) {
            return response()->json(['data' => [], 'message' => 'No department assigned'], 200);
        }

        $search = trim((string) $request->query('search', ''));

        $q = User::query()
            ->with('department:id,name')
            ->where('department_id', $u->department_id)
            ->when($search !== '', function ($qq) use ($search) {
                $qq->where(function ($w) use ($search) {
                    $w->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc');

        return response()->json($q->paginate(15));
    }

    // List files in same department (with owner + department info + optional search)
    public function departmentFiles(Request $request)
    {
        $u = Auth::guard('api')->user();
        if (!$u->department_id) {
            return response()->json(['data'=>[], 'message'=>'No department assigned'], 200);
        }

        $search = trim((string) $request->query('search', ''));

        $q = File::query()
            ->with([
                'user:id,first_name,last_name,email,department_id',
                'user.department:id,name'
            ])
            ->where('department_id', $u->department_id)
            ->when($search !== '', function ($qq) use ($search) {
                $qq->where(function ($w) use ($search) {
                    $w->where('title', 'like', "%{$search}%")
                      ->orWhere('original_name', 'like', "%{$search}%")
                      ->orWhere('mime_type', 'like', "%{$search}%")
                      ->orWhere('ocr_text', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at','desc');

        return response()->json($q->paginate(15));
    }

    // Update staff password
    public function updatePassword(Request $request, User $user)
    {
        $auth = Auth::guard('api')->user();
        if ($auth->id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'password' => 'required|string|min:8'
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password updated']);
    }
}
