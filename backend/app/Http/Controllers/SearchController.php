<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $u   = Auth::guard('api')->user();
        $q   = trim((string) $request->query('q', ''));

        $res = File::query()
            ->where(function ($scoped) use ($u) {
                $scoped->where('user_id', $u->id)
                    ->orWhere(function ($q2) use ($u) {
                        if ($u->department_id) {
                            $q2->where('department_id', $u->department_id);
                        }
                    })
                    ->orWhereIn('id', function ($sub) use ($u) {
                        $sub->from('file_shares')
                            ->select('file_id')
                            ->where('shared_with', $u->id);
                    });
            })
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('original_name', 'like', "%{$q}%")
                      ->orWhere('mime_type', 'like', "%{$q}%")
                      ->orWhere('ocr_text', 'like', "%{$q}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($res);
    }
}
