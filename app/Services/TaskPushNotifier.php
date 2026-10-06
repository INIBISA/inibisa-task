<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\Comment;
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

    public function send(Task $task, User $actor, string $action, ?Comment $comment = null, ?string $detail = null): void
    {
        if (! config('push.public_key') || ! config('push.private_key')) {
            return;
        }

        try {
            $subscriptions = PushSubscription::query()
                ->join('users', 'users.id', '=', 'push_subscriptions.user_id')
                ->where('users.is_active', true)
                ->where('push_subscriptions.user_id', '!=', $actor->id)
                ->select('push_subscriptions.*')
                ->get();
            if ($subscriptions->isEmpty()) {
                return;
            }

            $sender = new WebPush(['VAPID' => [
                'subject' => config('push.subject'),
                'publicKey' => config('push.public_key'),
                'privateKey' => config('push.private_key'),
            ]], ['TTL' => 86400, 'urgency' => 'normal', 'contentType' => 'application/json'], $this->client ?? new Client(['timeout' => 5, 'connect_timeout' => 3, 'allow_redirects' => false]));
            $sender->setReuseVAPIDHeaders(true);

            $commentText = $comment ? $this->excerpt($comment->body) : null;
            $imageCount = $comment?->attachments()->count() ?? 0;
            $payload = json_encode([
                'title' => match ($action) {
                    'created' => 'Tugas baru',
                    'commented' => $comment?->parent_id ? 'Balasan baru' : 'Komentar baru',
                    'subtask_created' => 'Subtugas baru',
                    'subtask_completed' => 'Subtugas selesai',
                    default => 'Tugas diperbarui',
                },
                'body' => match ($action) {
                    'created' => "{$actor->name} membuat tugas: {$task->title}",
                    'commented' => $commentText ? "{$actor->name}: {$commentText}" : "{$actor->name} mengirim {$imageCount} gambar di {$task->title}",
                    'subtask_created' => "{$actor->name} menambahkan subtugas: {$detail}",
                    'subtask_completed' => "{$actor->name} menyelesaikan subtugas: {$detail}",
                    default => "{$actor->name} memperbarui {$task->title}".($detail ? ": {$detail}" : ''),
                },
                'url' => route('tasks', absolute: false).'?task='.$task->id.($comment ? '&comment='.$comment->id : ''),
                'tag' => $comment ? 'task-'.$task->id.'-comment-'.$comment->id : 'task-'.$task->id,
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

    private function excerpt(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)));

        return mb_strimwidth($text, 0, 140, '...');
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
