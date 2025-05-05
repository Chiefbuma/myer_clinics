<div x-show="suggestions.length > 0" class="absolute z-10 mt-2 w-full bg-white border border-gray-300 rounded-lg shadow-lg">
    @if (!empty($suggestions))
    @foreach ($suggestions as $suggestion)
    <div
        x-on:click="
                    $wire.set('ICD_10', '{{ $suggestion['code'] }}');
                    $wire.set('diagnosis_name', '{{ $suggestion['name'] }}');
                    suggestions = [];
                "
        class="p-2 hover:bg-gray-100 cursor-pointer">
        <span>{{ $suggestion['code'] }}</span> - <span>{{ $suggestion['name'] }}</span>
    </div>
    @endforeach
    @else
    <div class="p-2 text-gray-500">
        No suggestions found.
    </div>
    @endif
</div>