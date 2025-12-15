<?php

namespace Ebox\Enterprise\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Ebox\Enterprise\Models\MessageAuditLog;
use Carbon\Carbon;

class CleanupAuditLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(): void
    {
        $retentionDays = config('ebox.audit.retention_days', 365 * 5);
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        $deleted = MessageAuditLog::where('created_at', '<', $cutoffDate)->delete();
        
        \Log::info("Nettoyage des logs d'audit", [
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoffDate->toDateString(),
        ]);
    }
}

