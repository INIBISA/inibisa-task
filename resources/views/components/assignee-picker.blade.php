@props(['members', 'selected' => []])

<div class="grid gap-2 sm:grid-cols-2">
@foreach($members as $member)
    <label class="assignee-option">
        <input class="peer sr-only" type="checkbox" name="assignees[]" value="{{ $member->id }}" @checked(collect($selected)->contains($member->id))>
        <span class="grid h-8 w-8 place-items-center rounded-full bg-cyan-100 text-xs font-black text-blue-800 peer-checked:bg-blue-700 peer-checked:text-white">{{ str($member->name)->substr(0, 1) }}</span>
        <span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold">{{ $member->name }}</span><span class="block truncate text-xs text-slate-400">{{ $member->position ?: ($member->role === 'admin' ? 'Admin' : 'Anggota') }}</span></span>
        <span class="assignee-check">✓</span>
    </label>
@endforeach
</div>
