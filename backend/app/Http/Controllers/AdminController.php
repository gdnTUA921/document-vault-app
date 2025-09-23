<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // --- Dashboard ping (sanity check) ---
    public function index()
    {
        return response()->json(['message' => 'Welcome Admin!']);
    }

    // ========================
    // 👥 USER MANAGEMENT
    // ========================
    public function listUsers(Request $request)
    {
        $q = User::query()
            ->with('department:id,name')
            ->when($request->query('role'), fn($qq, $role) => $qq->where('role', $role))
            ->when($request->query('dept'), fn($qq, $dept) => $qq->where('department_id', $dept))
            ->when($request->query('search'), function ($qq, $search) {
                $qq->where(function ($w) use ($search) {
                    $w->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'asc');

        return response()->json($q->paginate(15));
    }

    public function createUser(Request $request)
    {
        $data = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8',
            'role'          => ['required', Rule::in(['admin','staff','user'])],
            'department_id' => 'nullable|integer|exists:departments,id',
        ]);

        $user = User::create([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'role'          => $data['role'],
            'department_id' => $data['department_id'] ?? null,
        ]);

        // Log admin action
        AuditLog::create([
            'user_id'   => $request->user('api')->id,
            'action'    => 'create_user',
            'file_id'   => null,
            'ip_address'=> $request->ip(),
        ]);

        return response()->json($user, 201);
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name'    => 'sometimes|string|max:100',
            'last_name'     => 'sometimes|string|max:100',
            'email'         => ['sometimes','email', Rule::unique('users','email')->ignore($user->id)],
            'password'      => 'sometimes|nullable|string|min:8',
            'role'          => ['sometimes', Rule::in(['admin','staff','user'])],
            'department_id' => 'sometimes|nullable|integer|exists:departments,id',
        ]);

        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        AuditLog::create([
            'user_id'   => $request->user('api')->id,
            'action'    => 'update_user',
            'file_id'   => null,
            'ip_address'=> $request->ip(),
        ]);

        return response()->json($user);
    }

    public function deleteUser(Request $request, User $user)
    {
        if ($request->user('api')->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account'], 422);
        }

        $user->delete();

        AuditLog::create([
            'user_id'   => $request->user('api')->id,
            'action'    => 'delete_user',
            'file_id'   => null,
            'ip_address'=> $request->ip(),
        ]);

        return response()->json(['message' => 'User deleted']);
    }

    // Change password only
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8'
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        AuditLog::create([
            'user_id'   => $request->user('api')->id,
            'action'    => 'update_password',
            'file_id'   => null,
            'ip_address'=> $request->ip(),
        ]);

        return response()->json(['message' => 'Password updated']);
    }

    // ========================
    // 📝 AUDIT LOGS
    // ========================
    public function auditLogs(Request $request)
    {
        $perPage = $request->query('per_page', 20); // default 20 per page
        $logs = AuditLog::query()
            ->with(['user:id,first_name,last_name,email', 'file:id,title'])
            ->when($request->query('user_id'), fn($q, $id) => $q->where('user_id', $id))
            ->when($request->query('action'), fn($q, $a) => $q->where('action', $a))
            ->when($request->query('file_id'), fn($q, $f) => $q->where('file_id', $f))
            ->orderBy('created_at','desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
            ]
        ]);
    }

    // ========================
    // 🏢 DEPARTMENTS
    // ========================
    public function departments()
    {
        return response()->json(
            Department::orderBy('name')->get(['id','name'])
        );
    }

    // ========================
    // List all files (for admin overview)
    // ========================
    public function listFiles()
    {
        $files = \App\Models\File::with(['user:id,first_name,last_name,email','department:id,name'])
            ->orderBy('created_at','desc')
            ->paginate(20);

        return response()->json([
            'data' => $files->items(),
            'meta' => [
                'current_page' => $files->currentPage(),
                'last_page'    => $files->lastPage(),
                'per_page'     => $files->perPage(),
                'total'        => $files->total(),
            ]
        ]);
    }
}
