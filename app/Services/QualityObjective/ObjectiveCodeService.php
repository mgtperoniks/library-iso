<?php

namespace App\Services\QualityObjective;

use App\Models\Department;
use App\Models\QualityObjective;
use InvalidArgumentException;

class ObjectiveCodeService
{
    /**
     * Generate a unique code for a Quality Objective.
     * Format: SM-{DEPT_CODE}-{YEAR}-{SEQUENCE} (e.g., SM-QA-2026-001)
     *
     * @param int $departmentId
     * @param int $periodYear
     * @return string
     * @throws InvalidArgumentException
     */
    public function generateCode(int $departmentId, int $periodYear): string
    {
        $dept = Department::find($departmentId);
        if (!$dept) {
            throw new InvalidArgumentException("Department with ID {$departmentId} not found.");
        }

        $deptCode = strtoupper(trim($dept->code));
        $prefix = "SM-{$deptCode}-{$periodYear}-";

        // Find the last generated sequence number
        $lastCode = QualityObjective::where('code', 'like', "{$prefix}%")
            ->orderByDesc('code')
            ->value('code');

        $sequence = 1;
        if ($lastCode && preg_match('/-(\d{3})$/', $lastCode, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        $sequenceStr = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$sequenceStr}";
    }
}
