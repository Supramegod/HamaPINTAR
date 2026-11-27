@props(['title', 'chart', 'description' => null, 'icon' => 'chart', 'height' => 'h-72'])

<x-card :title="$title" :description="$description" :icon="$icon" {{ $attributes }}>
    <div class="relative {{ $height }}">
        <canvas data-chart="{{ $chart }}" role="img" aria-label="{{ $title }}"></canvas>
    </div>
</x-card>
