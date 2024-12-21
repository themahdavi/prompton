<x-filament-panels::page>
    <div class="h-auto">
        <div class="max-h-[50vh] overflow-y-auto py-6 pl-6">
            @if (count($this->getAllMessages()) == 0)
                <div>هروقت خواستی میتونی باهام چت کنی!</div>
            @else
                @foreach ($this->getAllMessages() as $conversation)
                    <div class="mb-12">
                        <div class="text-green-600 font-bold text-xl">{!! $conversation['prompt'] !!}</div>
                        <div class="bg-green-50 border p-3 rounded-lg mt-2">{!! Str::markdown($conversation['answer']) !!}</div>
                    </div>
                @endforeach
            @endif
        </div>
        <form wire:submit="submit">
            <div class="flex items-center mt-6">
                <div class="flex-1">
                    {{ $this->form }}
                </div>

                <div class="mt-2">
                    <x-filament::button color="primary" type="submit">
                        ارسال
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>


    <x-filament-actions::modals />
</x-filament-panels::page>
