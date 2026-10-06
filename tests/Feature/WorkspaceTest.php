<?php

namespace Tests\Feature;

use App\Models\Audience;
use App\Models\Idea;
use App\Models\Product;
use App\Models\SocialMediaAccount;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_create_a_task(): void
    {
        $user = User::factory()->create();
        $product = Product::create(['name' => 'Test', 'slug' => 'test', 'stage' => 'Idea']);

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Ship MVP', 'product_id' => $product->id, 'priority' => 'urgent', 'status' => 'Planned',
        ])->assertRedirect();

        $this->assertDatabaseHas('tasks', ['title' => 'Ship MVP', 'created_by' => $user->id]);
    }

    public function test_idea_converts_to_product(): void
    {
        $user = User::factory()->create();
        $audience = Audience::create(['name' => 'Pacaran', 'slug' => 'pacaran']);
        $idea = Idea::create(['title' => 'Love Letter', 'audience_id' => $audience->id, 'submitted_by' => $user->id]);

        $this->actingAs($user)->post(route('ideas.convert', $idea))->assertRedirect(route('products'));

        $this->assertDatabaseHas('products', ['name' => 'Love Letter']);
        $this->assertDatabaseHas('ideas', ['id' => $idea->id, 'status' => 'Planned']);
    }

    public function test_admin_can_create_and_deactivate_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('team.store'), [
            'name' => 'Nara', 'email' => 'nara@example.test', 'role' => 'member',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect();

        $member = User::where('email', 'nara@example.test')->firstOrFail();
        $this->actingAs($admin)->patch(route('team.deactivate', $member))->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $member->id, 'is_active' => false]);
    }

    public function test_member_cannot_manage_team(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $this->actingAs($member)->post(route('team.store'), [])->assertForbidden();
    }

    public function test_member_sees_assigned_tasks_but_cannot_edit_another_members_task(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::create(['title' => 'Buat logo', 'created_by' => $creator->id]);
        $task->assignees()->attach($assignee);

        $this->actingAs($assignee)->get(route('tasks'))->assertSee('Buat logo');
        $this->actingAs($other)->get(route('tasks'))->assertDontSee('Buat logo');
        $this->actingAs($assignee)->put(route('tasks.update', $task), ['status' => 'Done'])->assertForbidden();
    }

    public function test_dashboard_shows_each_members_tasks_to_admin_and_only_own_tasks_to_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['name' => 'Nara']);
        $other = User::factory()->create(['name' => 'Dimas']);
        $naraTask = Task::create(['title' => 'Tugas Nara', 'created_by' => $admin->id, 'status' => 'Done']);
        $dimasTask = Task::create(['title' => 'Tugas Dimas', 'created_by' => $admin->id, 'status' => 'In Progress']);
        $naraTask->assignees()->attach($member);
        $dimasTask->assignees()->attach($other);

        $this->actingAs($admin)->get(route('dashboard'))->assertSee(['Nara', 'Dimas', 'Tugas Nara', 'Tugas Dimas', '100%', '0%']);
        $this->actingAs($member)->get(route('dashboard'))->assertSee(['Nara', 'Tugas Nara', '100%'])->assertDontSee(['Dimas', 'Tugas Dimas']);
    }

    public function test_member_can_manage_social_media_accounts_with_an_encrypted_password(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->post(route('social-media-accounts.store'), [
            'platform' => 'TikTok', 'name' => '@inibisa',
            'login_email' => 'social@example.test', 'password' => 'secret-password',
        ])->assertRedirect();

        $account = SocialMediaAccount::firstOrFail();
        $this->assertSame('inibisa', $account->name);
        $this->assertSame('https://www.tiktok.com/@inibisa', $account->url);
        $this->assertSame('secret-password', $account->password);
        $this->assertDatabaseMissing('social_media_accounts', ['id' => $account->id, 'password' => 'secret-password']);
        $this->actingAs($member)->get(route('social-media-accounts'))->assertSee(['TikTok', '@inibisa'])->assertDontSee('secret-password');
        $this->actingAs($member)->delete(route('social-media-accounts.destroy', $account))->assertRedirect();
    }

    public function test_product_idea_and_audience_can_be_updated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $audience = Audience::create(['name' => 'Pacaran', 'slug' => 'pacaran']);
        $product = Product::create(['name' => 'Love', 'slug' => 'love', 'stage' => 'Idea']);
        $idea = Idea::create(['title' => 'Letters', 'submitted_by' => $admin->id]);

        $this->actingAs($admin)->put(route('audiences.update', $audience), ['name' => 'Keluarga'])->assertRedirect();
        $this->actingAs($admin)->put(route('products.update', $product), ['name' => 'Love 2', 'stage' => 'Design', 'priority' => 'normal'])->assertRedirect();
        $this->actingAs($admin)->put(route('ideas.update', $idea), ['title' => 'Letters 2', 'status' => 'Discuss'])->assertRedirect();

        $this->assertDatabaseHas('audiences', ['id' => $audience->id, 'name' => 'Keluarga']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Love 2']);
        $this->assertDatabaseHas('ideas', ['id' => $idea->id, 'title' => 'Letters 2']);
    }

    public function test_task_assignees_can_be_cleared_and_only_admin_can_delete_task(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::create(['title' => 'Rancang halaman', 'created_by' => $creator->id]);
        $task->assignees()->attach($assignee);

        $this->actingAs($creator)->put(route('tasks.update', $task), ['assignees_present' => '1'])->assertRedirect();
        $this->assertDatabaseMissing('task_assignees', ['task_id' => $task->id]);
        $this->actingAs($creator)->delete(route('tasks.destroy', $task))->assertForbidden();
        $this->actingAs($admin)->delete(route('tasks.destroy', $task))->assertRedirect();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_admin_can_delete_product_and_audience_without_deleting_related_tasks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $audience = Audience::create(['name' => 'Pasangan', 'slug' => 'pasangan']);
        $product = Product::create(['name' => 'Kartu', 'slug' => 'kartu', 'audience_id' => $audience->id]);
        $task = Task::create(['title' => 'Rilis', 'created_by' => $admin->id, 'product_id' => $product->id]);

        $this->actingAs($admin)->delete(route('audiences.destroy', $audience))->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'audience_id' => null]);
        $this->actingAs($admin)->delete(route('products.destroy', $product))->assertRedirect();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'product_id' => null]);
    }

    public function test_admin_can_delete_planned_idea_but_member_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $idea = Idea::create(['title' => 'Undangan', 'submitted_by' => $member->id, 'status' => 'Planned']);

        $this->actingAs($member)->delete(route('ideas.destroy', $idea))->assertForbidden();
        $this->actingAs($admin)->delete(route('ideas.destroy', $idea))->assertRedirect();
        $this->assertDatabaseMissing('ideas', ['id' => $idea->id]);
    }

    public function test_admin_cannot_delete_member_with_authored_content_or_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        Task::create(['title' => 'Tetap tersimpan', 'created_by' => $member->id]);

        $this->actingAs($admin)->delete(route('team.destroy', $admin))->assertForbidden();
        $this->actingAs($admin)->delete(route('team.destroy', $member))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $member->id]);

        $emptyMember = User::factory()->create();
        $this->actingAs($member)->delete(route('team.destroy', $emptyMember))->assertForbidden();
        $this->actingAs($admin)->delete(route('team.destroy', $emptyMember))->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $emptyMember->id]);
    }

    public function test_workspace_management_pages_render_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create(['name' => 'Kartu', 'slug' => 'kartu']);
        Audience::create(['name' => 'Pasangan', 'slug' => 'pasangan']);
        Idea::create(['title' => 'Undangan', 'submitted_by' => $admin->id]);
        Task::create(['title' => 'Rilis', 'created_by' => $admin->id, 'product_id' => $product->id]);

        foreach (['tasks', 'products', 'ideas', 'audiences', 'team'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }
}
