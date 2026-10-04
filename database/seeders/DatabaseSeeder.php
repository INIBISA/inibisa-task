<?php

namespace Database\Seeders;

use App\Models\Audience;
use App\Models\Idea;
use App\Models\Product;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $hael = User::factory()->create(['name' => 'Hael', 'email' => 'hael@inibisa.test', 'role' => 'admin', 'position' => 'Product Lead']);
        $nara = User::factory()->create(['name' => 'Nara', 'email' => 'nara@inibisa.test', 'position' => 'Designer']);
        $bima = User::factory()->create(['name' => 'Bima', 'email' => 'bima@inibisa.test', 'position' => 'Developer']);
        $audience = Audience::create(['name' => 'Persiapan Menikah', 'slug' => 'persiapan-menikah', 'description' => 'Pasangan yang menyiapkan hari pernikahan.', 'icon' => '💍']);
        Audience::create(['name' => 'Pacaran', 'slug' => 'pacaran', 'description' => 'Pasangan yang merayakan hubungan.', 'icon' => '♥']);
        $product = Product::create(['name' => 'IniBisa Invitation', 'slug' => 'inibisa-invitation', 'audience_id' => $audience->id, 'owner_id' => $hael->id, 'description' => 'Undangan digital yang personal dan mudah dibagikan.', 'problem' => 'Membuat undangan masih rumit.', 'solution' => 'Editor undangan sederhana.', 'stage' => 'Development', 'priority' => 'urgent', 'target_launch' => now()->addMonth()]);
        $tasks = collect([
            ['title' => 'Selesaikan Wedding Editor', 'status' => 'In Progress', 'priority' => 'urgent', 'due_date' => now()->addDays(3)],
            ['title' => 'Rancang template Romantic', 'status' => 'Review', 'priority' => 'high', 'due_date' => now()->addDay()],
            ['title' => 'Integrasi payment gateway', 'status' => 'Planned', 'priority' => 'high', 'due_date' => now()->addDays(7)],
            ['title' => 'Tulis landing page', 'status' => 'Backlog', 'priority' => 'normal', 'due_date' => now()->addDays(10)],
        ])->map(fn ($data) => Task::create($data + ['product_id' => $product->id, 'created_by' => $hael->id]));
        $tasks[0]->assignees()->attach($bima);
        $tasks[1]->assignees()->attach($nara);
        $tasks[2]->assignees()->attach($hael);
        $tasks[0]->subtasks()->createMany([['title' => 'Input nama pasangan', 'is_completed' => true], ['title' => 'Upload foto', 'is_completed' => true], ['title' => 'Preview dan publish']]);
        Idea::create(['title' => 'Couple Question Generator', 'audience_id' => $audience->id, 'submitted_by' => $nara->id, 'description' => 'Generator pertanyaan untuk pasangan.', 'status' => 'Discuss']);
    }
}
