<?php

namespace Ebox\Enterprise\Services\Audit;

use Ebox\Enterprise\Models\MessageAuditLog;
use Illuminate\Support\Facades\DB;

/**
 * Activity tracking service for e-Box audit
 */
class ActivityTracker
{
    /**
     * Get recent activities
     */
    public function getRecentActivities(int $limit = 50): array
    {
        return MessageAuditLog::with('message')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'message_id' => $log->ebox_message_id,
                    'action' => $log->action,
                    'actor' => $log->actor_identifier,
                    'timestamp' => $log->created_at->toISOString(),
                    'details' => $log->details,
                ];
            })
            ->toArray();
    }
    
    /**
     * Activity statistics by period
     */
    public function getActivityStats(string $startDate, string $endDate): array
    {
        return MessageAuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->get()
            ->pluck('count', 'action')
            ->toArray();
    }
}

