<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(AuditLogResource::collection($logs)->response()->getData(true));
    }
}
