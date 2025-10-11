<x-filament::page>
    <div class="space-y-6">
        <!-- Personal Information Form -->
        <x-filament::section>
            <form wire:submit.prevent="save" class="space-y-4">
                <x-slot name="heading">Personal Information</x-slot>
                
                <div class="space-y-4">
                    {{ $this->form }}
                    <x-filament::button type="submit" class="mt-4">
                        Update Password
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <!-- Two Factor Authentication -->
        <div>
            @foreach($this->getRegisteredMyProfileComponents() as $key => $component)
                @livewire($component, ['key' => $key])
            @endforeach
        </div>
    </div>
</x-filament::page>