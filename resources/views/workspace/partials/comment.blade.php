<article class="task-comment" data-comment-id="{{ $comment->id }}" tabindex="-1">
    <div class="task-comment-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($comment->user->name, 0, 1)) }}</div>
    <div class="min-w-0 flex-1">
        <div class="flex items-baseline gap-2"><strong class="truncate text-sm text-slate-800">{{ $comment->user->name }}</strong><time class="shrink-0 text-xs text-slate-400" datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time></div>
        @if($comment->body)<p class="mt-1 whitespace-pre-wrap break-words text-sm leading-6 text-slate-600">{{ $comment->body }}</p>@endif
        @if($comment->attachments->isNotEmpty())
            <div class="task-comment-images">@foreach($comment->attachments as $attachment)<a href="{{ route('comment-attachments.show', $attachment) }}" target="_blank" rel="noopener"><img src="{{ route('comment-attachments.show', $attachment) }}" alt="{{ $attachment->name }}"></a>@endforeach</div>
        @endif
        <button type="button" class="task-comment-reply-button" data-reply-to="{{ $comment->id }}"><i data-lucide="reply"></i> Balas</button>
        <div data-reply-composer></div>
        <div class="task-comment-children" data-comment-children>
            @foreach($commentTree->get($comment->id, collect()) as $child)
                @include('workspace.partials.comment', ['comment' => $child, 'commentTree' => $commentTree])
            @endforeach
        </div>
    </div>
</article>
