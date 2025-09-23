<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Models\AuditLog;
use App\Models\File;
use App\Models\FileKey;
use App\Models\FileShare;
use App\Models\UserKey;
use App\Services\CryptoService;
use App\Services\RsaKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::guard('api')->user();
        $scope = $request->query('scope', 'my'); // my|dept|shared

        $q = File::query();
        if ($scope === 'my') {
            $q->where('user_id', $user->id);
        } elseif ($scope === 'dept' && $user->department_id) {
            $q->where('department_id', $user->department_id);
        } elseif ($scope === 'shared') {
            $q->whereIn('id', function ($sub) use ($user) {
                $sub->from('file_shares')->select('file_id')->where('shared_with', $user->id);
            });
        } else {
            $q->where('user_id', $user->id);
        }

        if ($title = $request->query('title')) {
            $q->where('title', 'like', "%{$title}%");
        }

        return response()->json($q->orderBy('created_at','desc')->paginate(10));
    }

    public function store(StoreFileRequest $request, CryptoService $crypto, RsaKeyService $rsa)
    {
        $user = Auth::guard('api')->user();

        // Ensure uploader has RSA keypair
        if (!UserKey::find($user->id)) {
            return response()->json(['message' => 'Generate your RSA keypair first'], 409);
        }

        $upload = $request->file('file');

        // ✅ Backend validation for file type & size
        $allowedTypes = [
            'text/plain',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/png',
            'image/jpeg',
            'image/gif',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        $maxSize = 10 * 1024 * 1024; // 10 MB

        if (!in_array($upload->getMimeType(), $allowedTypes)) {
            return response()->json([
                'message' => 'Invalid file type. Allowed: txt, pdf, docx, png, jpeg, gif, xlsx, pptx'
            ], 422);
        }

        if ($upload->getSize() > $maxSize) {
            return response()->json([
                'message' => 'File too large. Max size is 10MB.'
            ], 422);
        }

        $raw    = file_get_contents($upload->getRealPath());

        // Per-file AES encryption; IV is prepended to the file
        $aesKey   = $crypto->makeAesKey();
        $iv       = $crypto->makeIv();
        $ivCipher = $crypto->encryptRaw($raw, $aesKey, $iv);
        $hash     = $crypto->sha256($raw);

        // Save encrypted blob to filesystem, store path in DB
        $disk = 'local'; // storage/app
        $path = 'vault/'.date('Y/m/').Str::uuid().'.bin';
        Storage::disk($disk)->put($path, $ivCipher);

        $file = File::create([
            'user_id'       => $user->id,
            'department_id' => $request->input('department_id', $user->department_id),
            'title'         => $request->input('title'),
            'original_name' => $upload->getClientOriginalName(),
            'file_path'     => $path,
            'mime_type'     => $upload->getClientMimeType(),
            'size_bytes'    => $upload->getSize(),
            'hash'          => $hash,
        ]);

        // Wrap AES key for the owner and save FileKey
        $wrapped = $rsa->encryptForUser($user, $aesKey);
        $ownerUk = UserKey::find($user->id);
        FileKey::create([
            'file_id'             => $file->id,
            'recipient_user_id'   => $user->id,
            'encrypted_aes_key'   => $wrapped,
            'key_encryption_algo' => 'RSA-OAEP-2048',
            'key_fingerprint'     => $rsa->fingerprint($ownerUk->public_key),
        ]);

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'upload',
            'file_id'    => $file->id,
            'ip_address' => $request->ip(),
            'details'    => ['mime'=>$file->mime_type, 'size'=>$file->size_bytes],
        ]);

        return response()->json($file, 201);
    }


    public function show(Request $request, File $file)
    {
        Gate::authorize('view', $file);

        $file->load([
            'user:id,first_name,last_name,email',
            'department:id,name',
            'shares.sharedWithUser:id,first_name,last_name,email',
        ]);

        return response()->json($file);
    }

    public function download(Request $request, File $file, CryptoService $crypto, RsaKeyService $rsa)
    {
        Gate::authorize('download', $file);
        $user = Auth::guard('api')->user();

        $fk = FileKey::where('file_id', $file->id)
            ->where('recipient_user_id', $user->id)
            ->first();

        $aesKey = null;

        if ($fk) {
            // Normal user path
            $aesKey = $rsa->decryptForUser($user, $fk->encrypted_aes_key);
        } elseif (in_array($user->role, ['admin','staff'])) {
            // Admin/staff: pick the owner's FileKey
            $ownerFk = FileKey::where('file_id', $file->id)
                ->where('recipient_user_id', $file->user_id)
                ->first();

            if ($ownerFk) {
                // ✅ Use the owner's key
                $aesKey = $rsa->decryptForUser($file->user, $ownerFk->encrypted_aes_key);
            }
        }

        if (!$aesKey) {
            return response()->json(['message' => 'No decryption key for this user'], 403);
        }

        $ivCipher = Storage::disk('local')->get($file->file_path);
        $plain    = $crypto->decryptRaw($ivCipher, $aesKey);

        if ($plain === false) {
            return response()->json(['message' => 'Decryption failed'], 500);
        }
        if (hash('sha256', $plain) !== $file->hash) {
            return response()->json(['message' => 'Integrity check failed'], 409);
        }

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'download',
            'file_id'    => $file->id,
            'ip_address' => $request->ip(),
            'details'    => ['ua' => ($request->userAgent() ?? '')],
        ]);

        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_',
            $file->original_name ?: ($file->title . '.bin')
        );

        return response($plain, 200, [
            'Content-Type'        => $file->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'X-Content-Hash'      => $file->hash,
            'X-File-Name'         => $file->original_name,
        ]);
    }

    public function update(Request $request, File $file)
    {
        Gate::authorize('update', $file);

        $data = $request->validate([
            'title'         => 'sometimes|string|max:255',
            'department_id' => 'sometimes|nullable|integer|exists:departments,id',
        ]);

        $file->update($data);

        AuditLog::create([
            'user_id'    => $request->user('api')->id,
            'action'     => 'file_update',
            'file_id'    => $file->id,
            'ip_address' => $request->ip(),
            'details'    => $data,
        ]);

        return response()->json($file);
    }

    public function destroy(Request $request, File $file)
    {
        $this->authorize('delete', $file);

        // capture info you may want in the audit trail
        $path = $file->file_path;
        $snapshot = [
            'id'           => $file->id,
            'title'        => $file->title,
            'original_name'=> $file->original_name,
            'mime_type'    => $file->mime_type,
            'size_bytes'   => $file->size_bytes,
        ];

        DB::transaction(function () use ($request, $file, $snapshot) {
            // ✅ log BEFORE delete; FK is valid at this point
            AuditLog::create([
                'user_id'    => auth('api')->id(),
                'action'     => 'delete',
                'file_id'    => $file->id,                 // will be auto-SET NULL after parent delete
                'ip_address' => $request->ip(),
                'details'    => [
                    'ua' => $request->userAgent(),
                    'deleted_file_id' => $file->id,        // preserve the id in details
                    'snapshot' => $snapshot,               // preserve metadata for later
                ],
            ]);

            // delete DB row (this will SET NULL file_id on existing logs per FK)
            $file->delete();
        });

        // finally remove the encrypted blob from storage (best-effort)
        if ($path) {
            Storage::disk('local')->delete($path);
        }

        return response()->json(['message' => 'File deleted']);
    }
}
