<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Audience;
use App\Models\Idea;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function dashboard()
    {
        $tasks = Task::with(['product', 'assignees'])->latest()->get();
        return view('workspace.dashboard', [
            'tasks' => $tasks->take(8), 'products' => Product::with('tasks')->latest()->get(),
            'stats' => ['active' => Product::where('status', 'active')->count(), 'progress' => $tasks->where('status', 'In Progress')->count(), 'done' => $tasks->where('status', 'Done')->count(), 'blocked' => $tasks->where('is_blocked', true)->count(), 'ideas' => Idea::count()],
            'activities' => Activity::with('user')->latest()->take(8)->get(), 'members' => User::withCount('assignedTasks')->get(),
        ]);
    }

    public function tasks(Request $request)
    {
        $query = Task::with(['product', 'milestone', 'assignees', 'subtasks'])->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->product === 'none') $query->whereNull('product_id');
        elseif ($request->filled('product')) $query->where('product_id', $request->product);
        if ($request->filled('q')) $query->where('title', 'like', '%'.$request->q.'%');
        return view('workspace.tasks', ['tasks' => $query->get(), 'products' => Product::where('status', 'active')->orderBy('name')->get(), 'members' => User::orderBy('name')->get(), 'milestones' => Milestone::orderBy('name')->get()]);
    }

    public function storeTask(Request $request)
    {
        $data = $request->validate(['title'=>'required|string|max:255','description'=>'nullable|string','product_id'=>'nullable|exists:products,id','milestone_id'=>'nullable|exists:milestones,id','priority'=>'required|in:urgent,high,normal,low','status'=>'required|in:Backlog,Planned,In Progress,Review,Done','due_date'=>'nullable|date','assignees'=>'array','assignees.*'=>'exists:users,id']);
        $task = Task::create($data + ['created_by' => $request->user()->id]); $task->assignees()->sync($data['assignees'] ?? []); $this->activity($request, 'created task', $task); return back()->with('success', 'Task dibuat.');
    }

    public function updateTask(Request $request, Task $task)
    {
        $data = $request->validate(['title'=>'sometimes|required|string|max:255','description'=>'nullable|string','product_id'=>'nullable|exists:products,id','milestone_id'=>'nullable|exists:milestones,id','priority'=>'sometimes|required|in:urgent,high,normal,low','status'=>'sometimes|required|in:Backlog,Planned,In Progress,Review,Done','position'=>'nullable|integer|min:0','due_date'=>'nullable|date','is_blocked'=>'nullable|boolean','blocked_reason'=>'nullable|string','assignees'=>'array','assignees.*'=>'exists:users,id']);
        $task->update($data); if ($request->has('assignees')) $task->assignees()->sync($data['assignees'] ?? []); $this->activity($request, 'updated task', $task); return back()->with('success', 'Task diperbarui.');
    }

    public function subtask(Request $request, Task $task) { $data = $request->validate(['title'=>'required|string|max:255']); $task->subtasks()->create($data); return back(); }
    public function toggleSubtask(Request $request, Subtask $subtask) { $subtask->update(['is_completed' => !$subtask->is_completed]); return back(); }

    public function products() { return view('workspace.products', ['products'=>Product::with(['audience','owner','tasks'])->latest()->get(), 'audiences'=>Audience::orderBy('position')->get(), 'members'=>User::orderBy('name')->get()]); }
    public function storeProduct(Request $request) { $data=$request->validate(['name'=>'required|max:255','audience_id'=>'nullable|exists:audiences,id','owner_id'=>'nullable|exists:users,id','description'=>'nullable','problem'=>'nullable','solution'=>'nullable','stage'=>'required','priority'=>'required|in:urgent,high,normal,low','target_launch'=>'nullable|date']); $product=Product::create($data+['slug'=>Str::slug($data['name']).'-'.Str::lower(Str::random(5))]); $this->activity($request,'created product',$product); return back()->with('success','Produk dibuat.'); }
    public function pipeline() { return view('workspace.pipeline', ['products'=>Product::with('audience')->get(), 'stages'=>['Idea','Validation','Design','Development','Content Preparation','Ready To Launch','Launched','Growth']]); }
    public function updateProductStage(Request $request, Product $product) { $data=$request->validate(['stage'=>'required|string']); $product->update($data); $this->activity($request,'changed product stage',$product); return back(); }

    public function ideas() { return view('workspace.ideas', ['ideas'=>Idea::with(['audience','submitter'])->latest()->get(), 'audiences'=>Audience::all()]); }
    public function storeIdea(Request $request) { $data=$request->validate(['title'=>'required|max:255','audience_id'=>'nullable|exists:audiences,id','description'=>'nullable','problem'=>'nullable','proposed_solution'=>'nullable','monetization_idea'=>'nullable']); $idea=Idea::create($data+['submitted_by'=>$request->user()->id]); $this->activity($request,'submitted idea',$idea); return back()->with('success','Ide ditambahkan.'); }
    public function convertIdea(Request $request, Idea $idea) { $product=Product::create(['name'=>$idea->title,'slug'=>Str::slug($idea->title).'-'.Str::lower(Str::random(5)),'audience_id'=>$idea->audience_id,'owner_id'=>$request->user()->id,'description'=>$idea->description,'problem'=>$idea->problem,'solution'=>$idea->proposed_solution,'business_model'=>$idea->monetization_idea,'stage'=>'Idea']); $idea->update(['status'=>'Planned']); $this->activity($request,'converted idea to product',$product); return redirect()->route('products')->with('success','Ide dikonversi menjadi produk.'); }

    public function audiences() { return view('workspace.audiences', ['audiences'=>Audience::withCount('products')->orderBy('position')->get()]); }
    public function storeAudience(Request $request) { $data=$request->validate(['name'=>'required|max:100','description'=>'nullable','icon'=>'nullable|max:10']); Audience::create($data+['slug'=>Str::slug($data['name']),'position'=>Audience::max('position')+1]); return back()->with('success','Audience ditambahkan.'); }
    public function team() { return view('workspace.team', ['members'=>User::with(['assignedTasks.product'])->withCount('assignedTasks')->get()]); }
    public function storeMember(Request $request) { $this->admin($request); $data = $request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','role'=>'required|in:admin,member','position'=>'nullable|string|max:255','password'=>'required|string|min:8|confirmed']); User::create($data); return back()->with('success', 'Member ditambahkan.'); }
    public function updateMember(Request $request, User $user) { $this->admin($request); $data = $request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email,'.$user->id,'role'=>'required|in:admin,member','position'=>'nullable|string|max:255','password'=>'nullable|string|min:8|confirmed']); if (blank($data['password'])) unset($data['password']); $user->update($data); return back()->with('success', 'Member diperbarui.'); }
    public function deactivateMember(Request $request, User $user) { $this->admin($request); abort_if($user->is($request->user()), 422, 'Tidak dapat menonaktifkan akun sendiri.'); $user->update(['is_active'=>false]); return back()->with('success', 'Member dinonaktifkan.'); }
    public function activityIndex() { return view('workspace.activity', ['activities'=>Activity::with('user')->latest()->paginate(30)]); }
    public function activity(Request $request, string $action, $subject): void { Activity::create(['user_id'=>$request->user()->id,'action'=>$action,'subject_type'=>$subject::class,'subject_id'=>$subject->id]); }
    private function admin(Request $request): void { abort_unless($request->user()->role === 'admin', 403); }
}
