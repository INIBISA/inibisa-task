<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Audience;
use App\Models\Idea;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\SocialMediaAccount;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskPushNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function dashboard()
    {
        $tasks = Task::with(['product', 'assignees'])->latest();
        $this->visibleTasks($tasks, request());
        $tasks = $tasks->get();

        $members = User::with(['assignedTasks' => fn ($query) => $query->with('product')->latest()])
            ->when(request()->user()->role !== 'admin', fn ($query) => $query->whereKey(request()->user()->id))
            ->orderBy('name')
            ->get();

        return view('workspace.dashboard', [
            'products' => Product::with('tasks')->latest()->get(),
            'stats' => ['active' => Product::where('status', 'active')->count(), 'progress' => $tasks->where('status', 'In Progress')->count(), 'done' => $tasks->where('status', 'Done')->count(), 'blocked' => $tasks->where('is_blocked', true)->count(), 'ideas' => Idea::count()],
            'members' => $members,
        ]);
    }

    public function tasks(Request $request)
    {
        $query = Task::with(['product', 'milestone', 'assignees', 'subtasks', 'comments' => fn ($comments) => $comments->with(['user', 'attachments'])->oldest()])->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->product === 'none') {
            $query->whereNull('product_id');
        } elseif ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->q.'%');
        }

        return view('workspace.tasks', ['tasks' => $query->get(), 'products' => Product::where('status', 'active')->orderBy('name')->get(), 'members' => User::orderBy('name')->get(), 'milestones' => Milestone::orderBy('name')->get()]);
    }

    public function storeTask(Request $request, TaskPushNotifier $pushNotifier)
    {
        $data = $request->validate(['title' => 'required|string|max:255', 'description' => 'nullable|string', 'product_id' => 'nullable|exists:products,id', 'milestone_id' => 'nullable|exists:milestones,id', 'priority' => 'required|in:urgent,high,normal,low', 'status' => 'required|in:Backlog,Planned,In Progress,Review,Done', 'due_date' => 'nullable|date', 'assignees' => 'array', 'assignees.*' => 'exists:users,id']);
        $task = Task::create($data + ['created_by' => $request->user()->id]);
        $task->assignees()->sync($data['assignees'] ?? []);
        $this->activity($request, 'membuat tugas', $task);
        $pushNotifier->send($task, $request->user()->id, 'created');

        return back()->with('success', 'Tugas dibuat.');
    }

    public function updateTask(Request $request, Task $task, TaskPushNotifier $pushNotifier)
    {
        $data = $request->validate(['title' => 'sometimes|required|string|max:255', 'description' => 'nullable|string', 'product_id' => 'nullable|exists:products,id', 'milestone_id' => 'nullable|exists:milestones,id', 'priority' => 'sometimes|required|in:urgent,high,normal,low', 'status' => 'sometimes|required|in:Backlog,Planned,In Progress,Review,Done', 'position' => 'nullable|integer|min:0', 'due_date' => 'nullable|date', 'is_blocked' => 'nullable|boolean', 'blocked_reason' => 'nullable|string', 'assignees' => 'array', 'assignees.*' => 'exists:users,id']);
        $task->update($data);
        $assigneesChanged = false;
        if ($request->has('assignees_present')) {
            $changes = $task->assignees()->sync($data['assignees'] ?? []);
            $assigneesChanged = count($changes['attached']) > 0 || count($changes['detached']) > 0;
        }
        $this->activity($request, 'memperbarui tugas', $task);
        if ($task->wasChanged() || $assigneesChanged) {
            $pushNotifier->send($task, $request->user()->id, 'updated');
        }

        return back()->with('success', 'Tugas diperbarui.');
    }

    public function destroyTask(Request $request, Task $task)
    {
        $this->admin($request);
        $task->delete();

        return back()->with('success', 'Tugas dihapus.');
    }

    public function subtask(Request $request, Task $task, TaskPushNotifier $pushNotifier)
    {
        $data = $request->validate(['title' => 'required|string|max:255']);
        $task->subtasks()->create($data);
        $pushNotifier->send($task, $request->user()->id, 'updated');

        return back();
    }

    public function toggleSubtask(Request $request, Subtask $subtask, TaskPushNotifier $pushNotifier)
    {
        $task = $subtask->task;
        $subtask->update(['is_completed' => ! $subtask->is_completed]);
        $pushNotifier->send($task, $request->user()->id, 'updated');

        return back();
    }

    public function products()
    {
        return view('workspace.products', ['products' => Product::with(['audience', 'owner', 'tasks'])->latest()->get(), 'audiences' => Audience::orderBy('position')->get(), 'members' => User::orderBy('name')->get()]);
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:255', 'audience_id' => 'nullable|exists:audiences,id', 'owner_id' => 'nullable|exists:users,id', 'description' => 'nullable', 'problem' => 'nullable', 'solution' => 'nullable', 'stage' => 'required', 'priority' => 'required|in:urgent,high,normal,low', 'target_launch' => 'nullable|date']);
        $product = Product::create($data + ['slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5))]);
        $this->activity($request, 'membuat produk', $product);

        return back()->with('success', 'Produk dibuat.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate(['name' => 'required|max:255', 'audience_id' => 'nullable|exists:audiences,id', 'owner_id' => 'nullable|exists:users,id', 'description' => 'nullable', 'problem' => 'nullable', 'solution' => 'nullable', 'stage' => 'required', 'priority' => 'required|in:urgent,high,normal,low', 'target_launch' => 'nullable|date']);
        $product->update($data);
        $this->activity($request, 'memperbarui produk', $product);

        return back()->with('success', 'Produk diperbarui.');
    }

    public function archiveProduct(Request $request, Product $product)
    {
        $product->update(['status' => 'archived']);
        $this->activity($request, 'mengarsipkan produk', $product);

        return back()->with('success', 'Produk diarsipkan.');
    }

    public function destroyProduct(Request $request, Product $product)
    {
        $this->admin($request);
        $product->delete();

        return back()->with('success', 'Produk dihapus.');
    }

    public function pipeline()
    {
        return view('workspace.pipeline', ['products' => Product::with('audience')->get(), 'stages' => ['Idea', 'Validation', 'Design', 'Development', 'Content Preparation', 'Ready To Launch', 'Launched', 'Growth']]);
    }

    public function updateProductStage(Request $request, Product $product)
    {
        $data = $request->validate(['stage' => 'required|string']);
        $product->update($data);
        $this->activity($request, 'mengubah tahap produk', $product);

        return back();
    }

    public function ideas()
    {
        return view('workspace.ideas', ['ideas' => Idea::with(['audience', 'submitter'])->latest()->get(), 'audiences' => Audience::all()]);
    }

    public function storeIdea(Request $request)
    {
        $data = $request->validate(['title' => 'required|max:255', 'audience_id' => 'nullable|exists:audiences,id', 'description' => 'nullable', 'problem' => 'nullable', 'proposed_solution' => 'nullable', 'monetization_idea' => 'nullable']);
        $idea = Idea::create($data + ['submitted_by' => $request->user()->id]);
        $this->activity($request, 'mengirim ide', $idea);

        return back()->with('success', 'Ide ditambahkan.');
    }

    public function updateIdea(Request $request, Idea $idea)
    {
        $data = $request->validate(['title' => 'required|max:255', 'audience_id' => 'nullable|exists:audiences,id', 'description' => 'nullable', 'problem' => 'nullable', 'proposed_solution' => 'nullable', 'monetization_idea' => 'nullable', 'status' => 'required|in:New,Discuss,Research,Approved,Rejected,Planned']);
        $idea->update($data);
        $this->activity($request, 'memperbarui ide', $idea);

        return back()->with('success', 'Ide diperbarui.');
    }

    public function destroyIdea(Request $request, Idea $idea)
    {
        $this->admin($request);
        $idea->delete();

        return back()->with('success', 'Ide dihapus.');
    }

    public function convertIdea(Request $request, Idea $idea)
    {
        $product = Product::create(['name' => $idea->title, 'slug' => Str::slug($idea->title).'-'.Str::lower(Str::random(5)), 'audience_id' => $idea->audience_id, 'owner_id' => $request->user()->id, 'description' => $idea->description, 'problem' => $idea->problem, 'solution' => $idea->proposed_solution, 'business_model' => $idea->monetization_idea, 'stage' => 'Idea']);
        $idea->update(['status' => 'Planned']);
        $this->activity($request, 'mengubah ide menjadi produk', $product);

        return redirect()->route('products')->with('success', 'Ide dikonversi menjadi produk.');
    }

    public function audiences()
    {
        return view('workspace.audiences', ['audiences' => Audience::withCount('products')->orderBy('position')->get()]);
    }

    public function storeAudience(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:100', 'description' => 'nullable', 'icon' => 'nullable|max:10']);
        Audience::create($data + ['slug' => Str::slug($data['name']), 'position' => Audience::max('position') + 1]);

        return back()->with('success', 'Audiens ditambahkan.');
    }

    public function updateAudience(Request $request, Audience $audience)
    {
        $data = $request->validate(['name' => 'required|max:100', 'description' => 'nullable', 'icon' => 'nullable|max:10']);
        $audience->update($data + ['slug' => Str::slug($data['name'])]);

        return back()->with('success', 'Audiens diperbarui.');
    }

    public function archiveAudience(Request $request, Audience $audience)
    {
        $this->admin($request);
        $audience->update(['is_archived' => true]);

        return back()->with('success', 'Audiens diarsipkan.');
    }

    public function destroyAudience(Request $request, Audience $audience)
    {
        $this->admin($request);
        $audience->delete();

        return back()->with('success', 'Audiens dihapus.');
    }

    public function team()
    {
        return view('workspace.team', ['members' => User::with(['assignedTasks.product'])->withCount('assignedTasks')->get()]);
    }

    public function storeMember(Request $request)
    {
        $this->admin($request);
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email', 'role' => 'required|in:admin,member', 'position' => 'nullable|string|max:255', 'password' => 'required|string|min:8|confirmed']);
        User::create($data);

        return back()->with('success', 'Anggota ditambahkan.');
    }

    public function updateMember(Request $request, User $user)
    {
        $this->admin($request);
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email,'.$user->id, 'role' => 'required|in:admin,member', 'position' => 'nullable|string|max:255', 'password' => 'nullable|string|min:8|confirmed']);
        if (blank($data['password'])) {
            unset($data['password']);
        } $user->update($data);

        return back()->with('success', 'Anggota diperbarui.');
    }

    public function deactivateMember(Request $request, User $user)
    {
        $this->admin($request);
        abort_if($user->is($request->user()), 422, 'Tidak dapat menonaktifkan akun sendiri.');
        $user->update(['is_active' => false]);

        return back()->with('success', 'Anggota dinonaktifkan.');
    }

    public function destroyMember(Request $request, User $user)
    {
        $this->admin($request);
        abort_if($user->is($request->user()), 403);
        if (Task::where('created_by', $user->id)->exists() || Idea::where('submitted_by', $user->id)->exists()
            || DB::table('comments')->where('user_id', $user->id)->exists()
            || DB::table('attachments')->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anggota masih memiliki tugas, ide, komentar, atau lampiran. Hapus kontennya terlebih dahulu.');
        }
        $user->delete();

        return back()->with('success', 'Anggota dihapus.');
    }

    public function activityIndex()
    {
        return view('workspace.activity', ['activities' => Activity::with('user')->latest()->paginate(30)]);
    }

    public function socialMediaAccounts()
    {
        return view('workspace.social-media-accounts', ['accounts' => SocialMediaAccount::latest()->get()]);
    }

    public function storeSocialMediaAccount(Request $request)
    {
        $data = $this->socialMediaAccountData($request);
        SocialMediaAccount::create($data);

        return back()->with('success', 'Akun sosial media ditambahkan.');
    }

    public function updateSocialMediaAccount(Request $request, SocialMediaAccount $socialMediaAccount)
    {
        $data = $this->socialMediaAccountData($request);
        if (blank($data['password'])) {
            unset($data['password']);
        } $socialMediaAccount->update($data);

        return back()->with('success', 'Akun sosial media diperbarui.');
    }

    public function deactivateSocialMediaAccount(SocialMediaAccount $socialMediaAccount)
    {
        $socialMediaAccount->update(['is_active' => false]);

        return back()->with('success', 'Akun sosial media dinonaktifkan.');
    }

    public function destroySocialMediaAccount(SocialMediaAccount $socialMediaAccount)
    {
        $socialMediaAccount->delete();

        return back()->with('success', 'Akun sosial media dihapus.');
    }

    public function activity(Request $request, string $action, $subject): void
    {
        Activity::create(['user_id' => $request->user()->id, 'action' => $action, 'subject_type' => $subject::class, 'subject_id' => $subject->id]);
    }

    private function socialMediaAccountData(Request $request): array
    {
        $data = $request->validate(['platform' => 'required|in:Instagram,TikTok,YouTube,Facebook,X,LinkedIn', 'name' => 'required|string|max:255', 'login_email' => 'nullable|email|max:255', 'password' => 'nullable|string|max:255']);
        $data['name'] = ltrim(trim($data['name']), '@');
        abort_if($data['name'] === '', 422, 'Username wajib diisi.');
        $prefixes = ['Instagram' => 'https://instagram.com/', 'TikTok' => 'https://www.tiktok.com/@', 'YouTube' => 'https://www.youtube.com/@', 'Facebook' => 'https://www.facebook.com/', 'X' => 'https://x.com/', 'LinkedIn' => 'https://www.linkedin.com/in/'];
        $data['url'] = $prefixes[$data['platform']].rawurlencode($data['name']);

        return $data;
    }

    private function admin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }

    private function visibleTasks($query, Request $request): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }
        $userId = $request->user()->id;
        $query->where(fn ($tasks) => $tasks->where('created_by', $userId)->orWhereHas('assignees', fn ($users) => $users->whereKey($userId)));
    }

}
