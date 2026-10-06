<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskPushNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task, TaskPushNotifier $pushNotifier)
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:images'],
            'parent_id' => ['nullable', 'integer'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);
        $data['body'] = trim($data['body'] ?? '');
        if ($data['body'] === '' && ! $request->hasFile('images')) {
            throw ValidationException::withMessages(['body' => 'Komentar atau gambar wajib diisi.']);
        }

        if (isset($data['parent_id'])) {
            $task->comments()->whereKey($data['parent_id'])->firstOrFail();
        }

        $paths = [];
        try {
            $comment = DB::transaction(function () use ($task, $data, $request, &$paths) {
                $comment = $task->comments()->create($data + ['user_id' => $request->user()->id]);
                foreach ($request->file('images', []) as $image) {
                    $path = $image->store('comments/'.$comment->id, 'local');
                    $paths[] = $path;
                    $comment->attachments()->create(['user_id' => $request->user()->id, 'path' => $path, 'name' => $image->getClientOriginalName(), 'mime_type' => $image->getMimeType(), 'size' => $image->getSize()]);
                }

                return $comment;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }
        $comment->load(['user', 'attachments']);
        $pushNotifier->send($task, $request->user(), 'commented', $comment);

        return response()->json([
            'ok' => true,
            'parent_id' => $comment->parent_id,
            'html' => view('workspace.partials.comment', ['comment' => $comment, 'commentTree' => collect()])->render(),
        ], 201);
    }
}
