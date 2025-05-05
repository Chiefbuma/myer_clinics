<x-filament-panels::page>
    <div>
        <form wire:submit="save">
            <div class="space-y-6">


                <!-- Display Patient ID -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold">Patient Details</h3>
                    <div class="mt-4 space-y-4">
                        <p><strong>Patient ID:</strong> {{ $record->patient_id }}</p>
                    </div>
                </div>

                <!-- Chronic Care Form -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold">Nutrition Care</h3>
                    <div class="mt-4 space-y-4">
                        {{ $this->form }}
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="mt-6">
                {{ $this->saveAction }}
            </div>
        </form>
    </div>
</x-filament-panels::page>