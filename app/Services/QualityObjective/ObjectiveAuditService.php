<?php

namespace App\Services\QualityObjective;

use App\Models\AuditLog;

class ObjectiveAuditService
{
    /**
     * Log a Quality Objective event to the existing audit_logs table.
     *
     * @param string $event
     * @param int|null $userId
     * @param int|null $objectiveId
     * @param string|null $ipAddress
     * @param array $details
     * @return void
     */
    public function log(string $event, ?int $userId, ?int $objectiveId, ?string $ipAddress = null, array $details = []): void
    {
        $payload = array_merge([
            'objective_id' => $objectiveId
        ], $details);

        AuditLog::create([
            'event' => $event,
            'user_id' => $userId,
            'document_id' => null, // not linked to main document
            'document_version_id' => null, // not linked to document version
            'detail' => $payload, // casted as array automatically in AuditLog model
            'ip' => $ipAddress,
        ]);
    }
}
