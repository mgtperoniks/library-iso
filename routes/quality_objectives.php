<?php

use App\Http\Controllers\QualityObjective\ActionPlanController;
use App\Http\Controllers\QualityObjective\MonitoringController;
use App\Http\Controllers\QualityObjective\QoDashboardController;
use App\Http\Controllers\QualityObjective\ObjectiveController;
use App\Http\Controllers\QualityObjective\PeriodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Quality Objectives Dashboard (MR/Management Dashboard)
    Route::get('quality-objectives/dashboard', [QoDashboardController::class, 'index'])
        ->name('quality-objectives.dashboard');

    // Quality Objective Periods (FR/MR/19 Period)
    Route::resource('quality-objectives/periods', PeriodController::class)->names([
        'index' => 'quality-objectives.periods.index',
        'create' => 'quality-objectives.periods.create',
        'store' => 'quality-objectives.periods.store',
        'edit' => 'quality-objectives.periods.edit',
        'update' => 'quality-objectives.periods.update',
        'destroy' => 'quality-objectives.periods.destroy',
    ]);

    // Quality Objectives (FR/MR/19)
    Route::resource('quality-objectives/objectives', ObjectiveController::class)->names([
        'index' => 'quality-objectives.objectives.index',
        'create' => 'quality-objectives.objectives.create',
        'store' => 'quality-objectives.objectives.store',
        'show' => 'quality-objectives.objectives.show',
        'edit' => 'quality-objectives.objectives.edit',
        'update' => 'quality-objectives.objectives.update',
        'destroy' => 'quality-objectives.objectives.destroy',
    ]);

    // Workflow Actions
    Route::post('quality-objectives/objectives/{objective}/submit', [ObjectiveController::class, 'submit'])
        ->name('quality-objectives.objectives.submit');

    Route::get('quality-objectives/objectives/{objective}/renew', [ObjectiveController::class, 'renewForm'])
        ->name('quality-objectives.objectives.renew-form');

    Route::post('quality-objectives/objectives/{objective}/renew', [ObjectiveController::class, 'renew'])
        ->name('quality-objectives.objectives.renew');

    // Action Plans (FR/MR/20)
    Route::resource('quality-objectives/action-plans', ActionPlanController::class)->names([
        'index' => 'quality-objectives.action-plans.index',
        'create' => 'quality-objectives.action-plans.create',
        'store' => 'quality-objectives.action-plans.store',
        'edit' => 'quality-objectives.action-plans.edit',
        'update' => 'quality-objectives.action-plans.update',
        'destroy' => 'quality-objectives.action-plans.destroy',
    ]);

    // Monitorings (FR/MR/25)
    Route::resource('quality-objectives/monitorings', MonitoringController::class)->names([
        'index' => 'quality-objectives.monitorings.index',
        'create' => 'quality-objectives.monitorings.create',
        'store' => 'quality-objectives.monitorings.store',
        'edit' => 'quality-objectives.monitorings.edit',
        'update' => 'quality-objectives.monitorings.update',
        'destroy' => 'quality-objectives.monitorings.destroy',
    ]);
    Route::post('quality-objectives/monitorings/{monitoring}/lock', [MonitoringController::class, 'lock'])
        ->name('quality-objectives.monitorings.lock');
});
