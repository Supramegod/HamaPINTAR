@props(['node'])

<x-badge>
    <span class="size-1.5 rounded-full" style="background-color: {{ config('hamapintar.nodes')[$node] ?? '#64748b' }}"></span>
    {{ $node }}
</x-badge>
