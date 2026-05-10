<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = NotificationLog::where('recipient_user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'channel' => $n->channel,
                'template' => $n->template_name,
                'status' => $n->status,
                'sent_at' => $n->sent_at?->toISOString(),
            ]),
            'meta' => ['page' => $notifications->currentPage(), 'total' => $notifications->total()],
        ]);
    }
}
