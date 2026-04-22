@props(['messages' => null, 'for' => null])

{{-- Soporte para sintaxis de Livewire / Jetstream (for="campo") --}}
@if($for)
    @error($for)
        <p {{ $attributes->merge(['class' => 'text-sm text-red-600 font-medium mt-1']) }}>{{ $message }}</p>
    @enderror
    
{{-- Soporte original para sintaxis de Laravel Breeze (:messages="$errors->get('campo')") --}}
@elseif ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 font-medium space-y-1 mt-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif