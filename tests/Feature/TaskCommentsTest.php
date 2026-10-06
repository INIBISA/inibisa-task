<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_member_can_comment_and_reply_without_cross_task_parents(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $task = Task::create(['title' => 'Diskusi', 'created_by' => $creator->id]);
        $otherTask = Task::create(['title' => 'Lain', 'created_by' => $creator->id]);

        $root = $this->actingAs($member)->postJson(route('tasks.comments.store', $task), ['body' => '<b>Halo</b>'])
            ->assertCreated()->json('html');

        $comment = Comment::where('commentable_id', $task->id)->firstOrFail();
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'user_id' => $member->id, 'body' => '<b>Halo</b>', 'parent_id' => null]);
        $this->assertStringContainsString('&lt;b&gt;Halo&lt;/b&gt;', $root);

        $this->actingAs($member)->postJson(route('tasks.comments.store', $task), ['body' => 'Balasan', 'parent_id' => $comment->id])
            ->assertCreated();
        $this->assertDatabaseHas('comments', ['commentable_id' => $task->id, 'parent_id' => $comment->id, 'body' => 'Balasan']);

        $reply = Comment::where('parent_id', $comment->id)->firstOrFail();
        $this->actingAs($member)->postJson(route('tasks.comments.store', $task), ['body' => 'Balasan tingkat tiga', 'parent_id' => $reply->id])
            ->assertCreated();
        $this->assertDatabaseHas('comments', ['commentable_id' => $task->id, 'parent_id' => $reply->id, 'body' => 'Balasan tingkat tiga']);

        $foreign = $otherTask->comments()->create(['user_id' => $creator->id, 'body' => 'Rahasia']);
        $this->actingAs($member)->postJson(route('tasks.comments.store', $task), ['body' => 'Tidak boleh', 'parent_id' => $foreign->id])
            ->assertNotFound();
    }

    public function test_comment_requires_an_authenticated_user_and_a_body(): void
    {
        $user = User::factory()->create();
        $task = Task::create(['title' => 'Validasi', 'created_by' => $user->id]);

        $this->postJson(route('tasks.comments.store', $task), ['body' => 'Halo'])->assertUnauthorized();
        $this->actingAs($user)->postJson(route('tasks.comments.store', $task), ['body' => ''])->assertUnprocessable()->assertJsonValidationErrors('body');
    }

    public function test_comment_can_include_a_private_image(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $task = Task::create(['title' => 'Gambar', 'created_by' => $user->id]);

        $this->actingAs($user)->post(route('tasks.comments.store', $task), [
            'body' => '',
            'images' => [UploadedFile::fake()->image('bukti.png')],
        ], ['Accept' => 'application/json'])->assertCreated();

        $attachment = Comment::firstOrFail()->attachments()->firstOrFail();
        Storage::disk('local')->assertExists($attachment->path);
        $this->get(route('comment-attachments.show', $attachment))->assertOk();
        $this->post(route('comment-attachments.show', $attachment))->assertMethodNotAllowed();
    }
}
