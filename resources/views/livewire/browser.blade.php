<div class="py-6 space-y-8">
    <div class="border rounded-lg p-6">
        <div class="flex items-center justify-between space-x-4">
            <div class="flex-1">
                <input type="text" class="px-3 py-2 border border-gray-300 w-full rounded-lg" wire:model="url">
            </div>

            <button class="px-4 py-2 border rounded-lg bg-purple-700 hover:bg-purple-800 text-white"
                wire:click="handleLoad">
                Get Data
            </button>
        </div>

    </div>

    <div wire:loading>
        Loading...
    </div>

    @if($content)
    <div class="border rounded-lg p-6 bg-gray-700 text-white max-h-96 overflow-y-auto">
        <pre class="whitespace-pre-line text-sm">
            {{ $content}}
        </pre>
    </div>
    @endif
</div>
