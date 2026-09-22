{{-- The avatar of a user: their uploaded image, or their initials. --}}
@props([
    'user' => null,
    'name' => null,
    'size' => 32,
])

@php
    $displayName = $name ?? $user?->getFullName() ?? '?';
@endphp

@if($user?->hasAvatar())
    <img
        src="{{ $user->avatarUrl($size) }}"
        srcset="{{ $user->avatarSrcset($size) }}"
        width="{{ $size }}"
        height="{{ $size }}"
        alt="{{ $displayName }}"
        title="{{ $displayName }}"
        {{ $attributes->class('shrink-0 rounded-full object-cover') }}
    >
@else
    <x-avatar-initials :name="$displayName" {{ $attributes }} />
@endif
