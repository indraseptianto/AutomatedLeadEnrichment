<?php

use App\Http\Controllers\LeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public endpoint — n8n calls this without auth (secured via n8n automation token header)
Route::apiResource('leads', LeadController::class)->except(['create', 'edit']);

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'app' => 'Automated Lead Enrichment']);
});
