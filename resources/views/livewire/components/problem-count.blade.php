<div wire:poll.5s>
    @if($count > 0)
        <span class="badge bg-danger ms-auto">{{ $count }}</span>
    @endif
</div>