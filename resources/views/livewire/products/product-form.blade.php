<?php

use Livewire\Volt\Component;
use App\Models\Product;

new class extends Component {
    public Product $product;

    public $name = '';
    public $description = '';
    public $current_price = '';

    public function mount(Product $product = null)
    {
        $this->product = $product ?? new Product();

        if ($this->product->exists) {
            $this->name = $this->product->name;
            $this->description = $this->product->description;
            $this->current_price = $this->product->current_price;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'current_price' => ['required', 'numeric', 'min:0'],
        ]);

        $this->product->fill($validated);
        $this->product->save();

        return redirect()->route('products.index');
    }
};
?>

<div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
    <form wire:submit="save" class="space-y-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input wire:model="name" type="text" id="name"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="current_price" class="block text-sm font-medium text-gray-700">Price (IDR)</label>
            <input wire:model="current_price" type="number" id="current_price" step="0.01"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('current_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea wire:model="description" id="description" rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('products.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
        </div>
    </form>
</div>