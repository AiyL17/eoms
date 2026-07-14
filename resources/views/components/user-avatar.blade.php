@props([
    'user',        // User model instance
    'size' => 8,   // Tailwind size number: 7, 8, 10, 16 …
    'rounded' => 'rounded-lg',
])

@php
    $initials  = strtoupper(substr($user->name ?? '?', 0, 2));
    $textSize  = match(true) {
        $size <= 7  => 'text-[10px]',
        $size <= 8  => 'text-xs',
        $size <= 10 => 'text-sm',
        default     => 'text-xl',
    };
@endphp

<div {{ $attributes->merge(['class' => "w-{$size} h-{$size} {$rounded} shrink-0 overflow-hidden flex items-center justify-center text-white {$textSize} font-bold"]) }}
     style="background: linear-gradient(135deg, #6d28d9, #7c3aed);">
    @if($user->avatar)
        <img src="{{ asset('storage/' . $user->avatar) }}"
             alt="{{ $user->name }}"
             class="w-full h-full object-cover">
    @else
        {{ $initials }}
    @endif
</div>
