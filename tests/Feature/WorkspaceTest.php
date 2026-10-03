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
            'title' => 'Ship MVP', 'product_id' => $product->id, 'priority' => 'P0', 'status' => 'Planned',
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
}
