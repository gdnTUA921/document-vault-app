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
    // Simple “you are in” ping
    public function index()
    {
        return response()->json(['message' => 'Welcome Admin!']);
    }

    // ---- Users Management ----
    public function listUsers(Request $request)
    {
        $q = User::query()
            ->with('department:id,name')
            ->when($request->query('role'), fn($qq, $role) => $qq->where('role', $role))
            ->when($request->query('dept'), fn($qq, $dept) => $qq->where('department_id', $dept))
            ->orderBy('created_at', 'desc');

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
        return response()->json($user);
    }

    public function deleteUser(Request $request, User $user)
    {
        // prevent self-delete (optional safety)
        if ($request->user('api')->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account'], 422);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    // ---- View Audit Logs (system-wide) ----
    public function auditLogs(Request $request)
    {
        $logs = AuditLog::query()
            ->with(['user:id,first_name,last_name,email', 'file:id,title'])
            ->when($request->query('user_id'), fn($q, $id) => $q->where('user_id', $id))
            ->when($request->query('action'), fn($q, $a) => $q->where('action', $a))
            ->when($request->query('file_id'), fn($q, $f) => $q->where('file_id', $f))
            ->orderBy('created_at','desc')
            ->paginate(20);

        return response()->json($logs);
    }

    // (Optional) departments list for admin UIs
    public function departments()
    {
        return response()->json(Department::orderBy('name')->get(['id','name']));
    }
}
