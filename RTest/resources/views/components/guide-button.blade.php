@props(['href', 'label' => 'Ver guía'])
<a href="{{ $href }}" target="_blank" rel="noopener noreferrer" {{ $attributes->class(['app-guide-button']) }} title="{{ $label }} (abre otra pestaña)">
    <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
    <span>{{ $label }}</span>
    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
    <span class="visually-hidden"> (abre otra pestaña)</span>
</a>
