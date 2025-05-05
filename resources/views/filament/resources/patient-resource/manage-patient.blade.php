<x-filament::page>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-100 h-screen p-6 shadow-md">
            <div class="text-lg font-semibold mb-6">
                Sidebar Menu
            </div>
            <ul class="space-y-4">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-black">Dashboard</a>
                </li>
                <li>
                    <a href="{{ route('users') }}" class="text-gray-600 hover:text-black">Users</a>
                </li>
                <li>
                    <a href="{{ route('settings') }}" class="text-gray-600 hover:text-black">Settings</a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <div class="space-y-6">
                <h1 class="text-2xl font-bold">{{ $this->getTitle() }}</h1>

                <!-- Toggle for Forms -->
                <div class="mb-6">
                    {{ $this->form }}
                </div>
            </div>

            <!-- Modal Container -->
            <x-filament::modal>
                {{ $this->modal }}
            </x-filament::modal>
        </div>
    </div>
</x-filament::page>