<?php

namespace Ebox\Enterprise\Services\Audit;

use Ebox\Enterprise\Models\MessageAuditLog;
use Illuminate\Support\Facades\DB;

/**
 * Service de suivi d'activité pour l'audit e-Box
 */
class ActivityTracker
{
    /**
     * Récupération des activités récentes
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
     * Statistiques d'activité par période
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

