<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\Task;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Psr\Http\Client\ClientInterface;

class TaskPushNotifier
{
    public function __construct(private ?ClientInterface $client = null) {}

    public function send(Task $task, int $actorId, string $action): void
    {
        if (! config('push.public_key') || ! config('push.private_key')) {
            return;
        }

        $recipientIds = $task->assignees()->pluck('users.id')->push($task->created_by)->unique()->reject(fn ($id) => (int) $id === $actorId);
        if ($recipientIds->isEmpty()) {
            return;
        }

        $subscriptions = PushSubscription::query()
            ->join('users', 'users.id', '=', 'push_subscriptions.user_id')
            ->whereIn('push_subscriptions.user_id', $recipientIds)
            ->where('users.is_active', true)
            ->select('push_subscriptions.*')
            ->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $sender = new WebPush(['VAPID' => [
                'subject' => config('push.subject'),
                'publicKey' => config('push.public_key'),
                'privateKey' => config('push.private_key'),
            ]], ['TTL' => 86400, 'urgency' => 'normal', 'contentType' => 'application/json'], $this->client ?? new Client(['timeout' => 5, 'connect_timeout' => 3, 'allow_redirects' => false]));
            $sender->setReuseVAPIDHeaders(true);

            $payload = json_encode([
                'title' => $action === 'created' ? 'Tugas baru' : 'Tugas diperbarui',
                'body' => $task->title,
                'url' => route('tasks', absolute: false).'?q='.rawurlencode($task->title),
                'tag' => 'task-'.$task->id,
            ], JSON_THROW_ON_ERROR);

            foreach ($subscriptions as $subscription) {
                try {
                    $sender->queueNotification(Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'keys' => ['p256dh' => $subscription->p256dh, 'auth' => $subscription->auth],
                        'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
                    ]), $payload);
                } catch (\Throwable $exception) {
                    Log::warning('Invalid push subscription', ['subscription_id' => $subscription->id, 'exception' => $exception]);
                }
            }

            foreach ($sender->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint_hash', hash('sha256', $report->getEndpoint()))->delete();
                } elseif (! $report->isSuccess()) {
                    Log::warning('Push notification failed', ['reason' => $report->getReason()]);
                }
            }
        } catch (\Throwable $exception) {
            Log::warning('Push notification could not be sent', ['exception' => $exception]);
        }
    }

    public function sendTest(User $user): bool
    {
        if (! config('push.public_key') || ! config('push.private_key')) {
            return false;
        }

        $subscriptions = PushSubscription::query()
            ->where('user_id', $user->id)
            ->get();
        if ($subscriptions->isEmpty()) {
            return false;
        }

        try {
            $sender = new WebPush(['VAPID' => [
                'subject' => config('push.subject'),
                'publicKey' => config('push.public_key'),
                'privateKey' => config('push.private_key'),
            ]], ['TTL' => 60, 'urgency' => 'normal', 'contentType' => 'application/json'], $this->client ?? new Client(['timeout' => 5, 'connect_timeout' => 3, 'allow_redirects' => false]));
            $sender->setReuseVAPIDHeaders(true);
            $payload = json_encode(['title' => 'Notifikasi tes', 'body' => 'Notifikasi IniBisa berfungsi di perangkat ini.', 'url' => route('profile.edit', absolute: false), 'tag' => 'push-test'], JSON_THROW_ON_ERROR);

            foreach ($subscriptions as $subscription) {
                $sender->queueNotification(Subscription::create(['endpoint' => $subscription->endpoint, 'keys' => ['p256dh' => $subscription->p256dh, 'auth' => $subscription->auth], 'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm']), $payload);
            }
            foreach ($sender->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint_hash', hash('sha256', $report->getEndpoint()))->delete();
                }
            }

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Test push notification could not be sent', ['exception' => $exception]);

            return false;
        }
    }
}
