<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShareRequest;
use App\Models\AuditLog;
use App\Models\File;
use App\Models\FileKey;
use App\Models\FileShare;
use App\Models\User;
use App\Models\UserKey;
use App\Services\RsaKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FileShareController extends Controller
{
    public function index(Request $request, File $file)
    {
        Gate::authorize('view', $file);
        $shares = $file->shares()->with('sharedWithUser:id,first_name,last_name,email')->get();
        return response()->json($shares);
    }

    public function store(ShareRequest $request, File $file, RsaKeyService $rsa)
    {
        Gate::authorize('manageShare', $file);

        $ownerKey = FileKey::where('file_id', $file->id)
            ->where('recipient_user_id', $file->user_id)
            ->first();

        if (!$ownerKey) {
            return response()->json(['message' => 'Owner file key missing'], 500);
        }

        $recipient = User::findOrFail($request->input('shared_with'));
        $recipientUk = UserKey::find($recipient->id);
        if (!$recipientUk) {
            return response()->json(['message' => 'Recipient has no RSA keypair'], 409);
        }

        // unwrap owner AES key then wrap for recipient
        $aesKey = $rsa->decryptForUser($file->user, $ownerKey->encrypted_aes_key);
        $wrappedForRecipient = $rsa->encryptForUser($recipient, $aesKey);

        // persist share + recipient file key
        $share = FileShare::updateOrCreate(
            ['file_id' => $file->id, 'shared_with' => $recipient->id],
            ['permission' => $request->input('permission')]
        );

        FileKey::updateOrCreate(
            ['file_id' => $file->id, 'recipient_user_id' => $recipient->id],
            [
                'encrypted_aes_key'   => $wrappedForRecipient,
                'key_encryption_algo' => 'RSA-OAEP-2048',
                'key_fingerprint'     => $rsa->fingerprint($recipientUk->public_key),
            ]
        );

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'share',
            'file_id'    => $file->id,
            'ip_address' => $request->ip(),
            'details'    => [
                'shared_with' => $recipient->id,
                'permission'  => $request->input('permission')
            ],
        ]);

        return response()->json($share, 201);
    }

    public function destroy(Request $request, File $file, FileShare $share)
    {
        Gate::authorize('manageShare', $file);

        abort_if($share->file_id !== $file->id, 404);

        FileKey::where('file_id', $file->id)
            ->where('recipient_user_id', $share->shared_with)
            ->delete();

        $share->delete();

        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'unshare',
            'file_id'    => $file->id,
            'ip_address' => $request->ip(),
            'details'    => ['shared_with' => $share->shared_with],
        ]);

        return response()->json(['message' => 'Share removed']);
    }
}
