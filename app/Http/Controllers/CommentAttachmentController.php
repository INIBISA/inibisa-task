<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;

class CommentAttachmentController extends Controller
{
    public function show(Attachment $attachment)
    {
        abort_unless($attachment->attachable instanceof Comment, 404);

        return Storage::disk('local')->response($attachment->path, $attachment->name, ['Content-Type' => $attachment->mime_type ?: 'application/octet-stream', 'Content-Disposition' => 'inline']);
    }
}
