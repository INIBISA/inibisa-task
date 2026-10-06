<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskPushNotifier;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Minishlink\WebPush\VAPID;
use Tests\TestCase;

class PushNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscriptions_require_login_and_follow_the_current_account(): void
    {
        config()->set('push.public_key', 'configured');
        config()->set('push.private_key', 'configured');
        $first = User::factory()->create();
        $second = User::factory()->create();
        $endpoint = 'https://push.example.test/device-one';
        $subscription = ['endpoint' => $endpoint, 'keys' => ['p256dh' => VAPID::createVapidKeys()['publicKey'], 'auth' => rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=')]];

        $this->postJson(route('push-subscriptions.store'), $subscription)->assertUnauthorized();
        $this->actingAs($first)->postJson(route('push-subscriptions.store'), $subscription)->assertOk();
        $this->assertDatabaseHas('push_subscriptions', ['user_id' => $first->id, 'endpoint_hash' => hash('sha256', $endpoint)]);

        $this->actingAs($second)->postJson(route('push-subscriptions.store'), $subscription)->assertOk();
        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->assertDatabaseHas('push_subscriptions', ['user_id' => $second->id, 'endpoint_hash' => hash('sha256', $endpoint)]);
        $this->actingAs($first)->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $endpoint])->assertOk();
        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->actingAs($second)->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $endpoint])->assertOk();
        $this->assertDatabaseCount('push_subscriptions', 0);
    }

    public function test_task_changes_push_to_every_active_member_except_actor(): void
    {
        $keys = VAPID::createVapidKeys();
        config()->set('push.public_key', $keys['publicKey']);
        config()->set('push.private_key', $keys['privateKey']);
        config()->set('push.subject', 'https://task.example.test');

        $history = [];
        $handler = HandlerStack::create(new MockHandler(array_fill(0, 9, new Response(201))));
        $handler->push(Middleware::history($history));
        $this->app->instance(TaskPushNotifier::class, new TaskPushNotifier(new Client(['handler' => $handler])));

        $actor = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $inactive = User::factory()->create(['is_active' => false]);
        $unrelated = User::factory()->create();
        foreach ([$actor, $creator, $assignee, $inactive, $unrelated] as $user) {
            PushSubscription::create([
                'user_id' => $user->id,
                'endpoint' => 'https://push.example.test/device-'.$user->id,
                'endpoint_hash' => hash('sha256', 'https://push.example.test/device-'.$user->id),
                'p256dh' => $keys['publicKey'],
                'auth' => rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '='),
            ]);
        }

        $this->actingAs($actor)->post(route('tasks.store'), [
            'title' => 'Tugas baru', 'priority' => 'normal', 'status' => 'Backlog', 'assignees' => [$assignee->id, $inactive->id],
        ])->assertRedirect();
        $this->assertCount(3, $history);
        $this->assertSame([
            'https://push.example.test/device-'.$creator->id,
            'https://push.example.test/device-'.$assignee->id,
            'https://push.example.test/device-'.$unrelated->id,
        ], collect($history)->map(fn ($entry) => (string) $entry['request']->getUri())->sort()->values()->all());

        $task = Task::create(['title' => 'Tugas lama', 'created_by' => $creator->id]);
        $task->assignees()->attach([$actor->id, $assignee->id, $inactive->id]);
        $this->actingAs($actor)->put(route('tasks.update', $task), ['status' => 'Done'])->assertRedirect();
        $this->assertCount(6, $history);

        $sentTo = collect(array_slice($history, 3))->map(fn ($entry) => (string) $entry['request']->getUri())->sort()->values()->all();
        $this->assertSame([
            'https://push.example.test/device-'.$creator->id,
            'https://push.example.test/device-'.$assignee->id,
            'https://push.example.test/device-'.$unrelated->id,
        ], $sentTo);

        $this->actingAs($actor)->put(route('tasks.update', $task), ['status' => 'Done'])->assertRedirect();
        $this->assertCount(6, $history);

        $this->actingAs($actor)->post(route('tasks.store'), [
            'title' => 'Tugas berikutnya', 'priority' => 'normal', 'status' => 'Backlog', 'assignees' => [$assignee->id],
        ])->assertRedirect();
        $this->assertCount(9, $history);
    }

    public function test_push_test_requires_the_current_user_to_have_a_subscription(): void
    {
        config()->set('push.public_key', 'configured');
        config()->set('push.private_key', 'configured');
        $user = User::factory()->create();

        $this->postJson(route('push-subscriptions.test'))->assertUnauthorized();
        $this->actingAs($user)->postJson(route('push-subscriptions.test'))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Aktifkan notifikasi di perangkat ini terlebih dahulu.');
    }

    public function test_comment_pushes_to_other_active_members(): void
    {
        $keys = VAPID::createVapidKeys();
        config()->set('push.public_key', $keys['publicKey']);
        config()->set('push.private_key', $keys['privateKey']);
        config()->set('push.subject', 'https://task.example.test');
        $author = User::factory()->create();
        $recipient = User::factory()->create();
        $history = [];
        $handler = HandlerStack::create(new MockHandler([new Response(201)]));
        $handler->push(Middleware::history($history));
        $this->app->instance(TaskPushNotifier::class, new TaskPushNotifier(new Client(['handler' => $handler])));

        foreach ([$author, $recipient] as $user) {
            PushSubscription::create(['user_id' => $user->id, 'endpoint' => 'https://push.example.test/comment-'.$user->id, 'endpoint_hash' => hash('sha256', 'https://push.example.test/comment-'.$user->id), 'p256dh' => $keys['publicKey'], 'auth' => rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=')]);
        }
        $task = Task::create(['title' => 'Diskusi', 'created_by' => $author->id]);

        $this->actingAs($author)->postJson(route('tasks.comments.store', $task), ['body' => 'Ada kabar'])->assertCreated();

        $this->assertCount(1, $history);
        $this->assertSame('https://push.example.test/comment-'.$recipient->id, (string) $history[0]['request']->getUri());
    }

    public function test_push_test_sends_only_to_the_current_users_devices(): void
    {
        $keys = VAPID::createVapidKeys();
        config()->set('push.public_key', $keys['publicKey']);
        config()->set('push.private_key', $keys['privateKey']);
        config()->set('push.subject', 'https://task.example.test');
        $user = User::factory()->create();
        $other = User::factory()->create();
        $history = [];
        $handler = HandlerStack::create(new MockHandler([new Response(201)]));
        $handler->push(Middleware::history($history));
        $this->app->instance(TaskPushNotifier::class, new TaskPushNotifier(new Client(['handler' => $handler])));

        foreach ([$user, $other] as $subscriber) {
            PushSubscription::create([
                'user_id' => $subscriber->id,
                'endpoint' => 'https://push.example.test/device-'.$subscriber->id,
                'endpoint_hash' => hash('sha256', 'https://push.example.test/device-'.$subscriber->id),
                'p256dh' => $keys['publicKey'],
                'auth' => rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '='),
            ]);
        }

        $this->actingAs($user)->postJson(route('push-subscriptions.test'))->assertOk();

        $this->assertCount(1, $history);
        $this->assertSame('https://push.example.test/device-'.$user->id, (string) $history[0]['request']->getUri());
    }
}
