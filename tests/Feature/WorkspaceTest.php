<?php

namespace Tests\Feature;

use App\Models\Audience;
use App\Models\Product;
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
        $idea = \App\Models\Idea::create(['title' => 'Love Letter', 'audience_id' => $audience->id, 'submitted_by' => $user->id]);

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
}
