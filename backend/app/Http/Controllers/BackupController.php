<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    public function manual(): JsonResponse
    {
        try {
            $process = new Process(['sh', '/backup.sh']);

            $process->setTimeout(300); // 5 minutes
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // ✅ Log this action in audit_logs
            AuditLog::create([
                'user_id'   => Auth::id(),
                'action'    => 'manual_backup',
                'ip_address'=> request()->ip(),
                'user_agent'=> request()->userAgent(),
                'meta'      => ['output' => $process->getOutput()],
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Backup executed successfully!',
                'output'  => $process->getOutput(),
            ]);
        } catch (\Exception $e) {
            // Log failed backup attempt as well
            AuditLog::create([
                'user_id'   => Auth::id(),
                'action'    => 'manual_backup_failed',
                'ip_address'=> request()->ip(),
                'user_agent'=> request()->userAgent(),
                'meta'      => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Backup failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
