<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\TaskPushNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless(config('push.public_key') && config('push.private_key'), 503, 'Notifikasi belum dikonfigurasi.');

        $data = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:2048'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'min:80', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
            'keys.auth' => ['required', 'string', 'min:16', 'max:32', 'regex:/^[A-Za-z0-9_-]+$/'],
            'contentEncoding' => ['nullable', 'in:aes128gcm,aesgcm'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $data['endpoint'])],
            [
                'user_id' => $request->user()->id,
                'endpoint' => $data['endpoint'],
                'p256dh' => $data['keys']['p256dh'],
                'auth' => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? null,
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['endpoint' => ['required', 'url:https', 'max:2048']]);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->delete();

        return response()->json(['ok' => true]);
    }

    public function test(Request $request, TaskPushNotifier $pushNotifier): JsonResponse
    {
        abort_unless(config('push.public_key') && config('push.private_key'), 503, 'Notifikasi belum dikonfigurasi.');
        abort_unless($pushNotifier->sendTest($request->user()), 422, 'Aktifkan notifikasi di perangkat ini terlebih dahulu.');

        return response()->json(['ok' => true]);
    }
}
