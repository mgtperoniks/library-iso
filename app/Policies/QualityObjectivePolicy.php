<?php

namespace App\Policies;

use App\Models\QualityObjective;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QualityObjectivePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any quality objectives.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'mr', 'director', 'kabag']);
    }

    /**
     * Determine whether the user can view the quality objective.
     */
    public function view(User $user, QualityObjective $objective): bool
    {
        if ($user->hasAnyRole(['admin', 'mr', 'director'])) {
            return true;
        }

        // KABAG can only view objectives belonging to their department
        return $user->hasAnyRole(['kabag']) && $user->department_id === $objective->department_id;
    }

    /**
     * Determine whether the user can create quality objectives.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'kabag']);
    }

    /**
     * Determine whether the user can update the quality objective.
     */
    public function update(User $user, QualityObjective $objective): bool
    {
        if ($user->hasAnyRole(['admin'])) {
            return true;
        }

        // KABAG can update only if it belongs to their department and is in 'draft' or 'revision' status
        if ($user->hasAnyRole(['kabag']) && $user->department_id === $objective->department_id) {
            return in_array($objective->status, ['draft', 'revision']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the quality objective.
     */
    public function delete(User $user, QualityObjective $objective): bool
    {
        if ($user->hasAnyRole(['admin'])) {
            return true;
        }

        // KABAG can delete only if it belongs to their department and is in 'draft' status
        if ($user->hasAnyRole(['kabag']) && $user->department_id === $objective->department_id) {
            return $objective->status === 'draft';
        }

        return false;
    }

    /**
     * Determine whether the user can submit the quality objective.
     */
    public function submit(User $user, QualityObjective $objective): bool
    {
        // KABAG can submit their department's objective if it is draft/revision
        return $user->hasAnyRole(['admin']) || 
            ($user->hasAnyRole(['kabag']) && $user->department_id === $objective->department_id && in_array($objective->status, ['draft', 'revision']));
    }

    /**
     * Determine whether the user can review/approve at MR stage.
     */
    public function reviewMr(User $user, QualityObjective $objective): bool
    {
        return $user->hasAnyRole(['admin', 'mr']) && $objective->status === 'submitted';
    }

    /**
     * Determine whether the user can approve/reject at Director stage.
     */
    public function approveDirector(User $user, QualityObjective $objective): bool
    {
        return $user->hasAnyRole(['admin', 'director']) && $objective->status === 'pending_director';
    }

    /**
     * Determine whether the user can manage monitoring inputs (FR/MR/25).
     */
    public function manageMonitoring(User $user, QualityObjective $objective): bool
    {
        if ($user->hasAnyRole(['admin'])) {
            return true;
        }

        // KABAG can only log monitoring if it is active and belongs to their department
        return $user->hasAnyRole(['kabag']) && 
            $user->department_id === $objective->department_id && 
            $objective->status === 'active';
    }

    /**
     * Determine whether the user can manage action plans (FR/MR/20).
     */
    public function manageActionPlans(User $user, QualityObjective $objective): bool
    {
        if ($user->hasAnyRole(['admin', 'mr'])) {
            return true;
        }

        // KABAG can manage action plans of their department's active/draft objectives
        return $user->hasAnyRole(['kabag']) && 
            $user->department_id === $objective->department_id && 
            in_array($objective->status, ['draft', 'revision', 'active']);
    }

    /**
     * Determine whether the user can evaluate the quality objective.
     */
    public function evaluate(User $user, QualityObjective $objective): bool
    {
        // MR conducts annual evaluations
        return $user->hasAnyRole(['admin', 'mr']) && $objective->status === 'active';
    }
}
