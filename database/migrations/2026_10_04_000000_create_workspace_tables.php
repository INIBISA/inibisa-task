<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('member')->after('email');
            $table->string('position')->nullable()->after('role');
            $table->string('avatar')->nullable()->after('position');
        });
        Schema::create('audiences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audience_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('problem')->nullable();
            $table->text('solution')->nullable();
            $table->string('target_user')->nullable();
            $table->string('business_model')->nullable();
            $table->string('price_idea')->nullable();
            $table->string('stage')->default('Idea');
            $table->string('priority')->default('normal');
            $table->string('status')->default('active');
            $table->date('target_launch')->nullable();
            $table->timestamps();
        });
        Schema::create('product_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['product_id', 'user_id']);
        });
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('planned');
            $table->timestamps();
        });
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('Backlog')->index();
            $table->string('priority')->default('normal')->index();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_blocked')->default(false)->index();
            $table->text('blocked_reason')->nullable();
            $table->unsignedInteger('week_number')->nullable();
            $table->timestamps();
        });
        Schema::create('task_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['task_id', 'user_id']);
        });
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#6b7280');
            $table->timestamps();
        });
        Schema::create('task_labels', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->primary(['task_id', 'label_id']);
        });
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audience_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('problem')->nullable();
            $table->text('proposed_solution')->nullable();
            $table->string('target_user')->nullable();
            $table->string('monetization_idea')->nullable();
            $table->string('status')->default('New');
            $table->timestamps();
        });
        Schema::create('idea_votes', function (Blueprint $table) {
            $table->foreignId('idea_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['idea_id', 'user_id']);
        });
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('Monthly');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
        Schema::create('goal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('commentable');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('attachable');
            $table->string('path');
            $table->string('name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->nullableMorphs('subject');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('goal_items');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('idea_votes');
        Schema::dropIfExists('ideas');
        Schema::dropIfExists('task_labels');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('subtasks');
        Schema::dropIfExists('task_assignees');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('product_members');
        Schema::dropIfExists('products');
        Schema::dropIfExists('audiences');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'position', 'avatar']));
    }
};
