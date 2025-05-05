<x-filament::page>
    <form wire:submit.prevent="saveNutrition">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $patient->patient_id }}">
        {{ $this->form }}
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Nutrition</button>
    </form>

    <form wire:submit.prevent="saveChronic" class="mt-6">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $patient->patient_id }}">
        {{ $this->form }}
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Save Chronic</button>
    </form>
</x-filament::page>