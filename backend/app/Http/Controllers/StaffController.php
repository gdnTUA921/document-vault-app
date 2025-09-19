<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'Welcome Staff!']);
    }

    // List files in MY department (staff or admin)
    public function departmentFiles(Request $request)
    {
        $u = Auth::guard('api')->user();
        if (!$u->department_id) {
            return response()->json(['data'=>[], 'message'=>'No department assigned'], 200);
        }

        $q = File::query()
            ->where('department_id', $u->department_id)
            ->when($request->query('title'), fn($qq, $t) => $qq->where('title','like',"%{$t}%"))
            ->orderBy('created_at','desc');

        return response()->json($q->paginate(15));
    }

    // Who currently has access to a department file (for quick staff checks)
    public function fileAccessList(Request $request, File $file)
    {
        $u = Auth::guard('api')->user();

        if ($u->role !== 'admin' && $u->department_id !== $file->department_id) {
            return response()->json(['message'=>'Forbidden'], 403);
        }

        $shares = $file->shares()->with('sharedWithUser:id,first_name,last_name,email')->get();
        return response()->json([
            'file'   => ['id'=>$file->id, 'title'=>$file->title],
            'shares' => $shares,
        ]);
    }
}
