<?php

use Livewire\Volt\Component;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Product;
use App\Enums\InvoiceStatus;
use App\Actions\CalculateInvoiceTotalAction;
use Illuminate\Validation\Rule;

new class extends Component {
    public Invoice $invoice;

    // Header Fields
    public $client_id = '';
    public $invoice_number = '';
    public $issue_date = '';
    public $due_date = '';
    public $status = 'draft';
    public $notes = '';

    // Items Repeater
    public $items = []; // Array of ['product_id', 'quantity', 'unit_price', 'subtotal']

    public function mount(Invoice $invoice = null)
    {
        $this->invoice = $invoice ?? new Invoice();

        if ($this->invoice->exists) {
            $this->client_id = $this->invoice->client_id;
            $this->invoice_number = $this->invoice->invoice_number;
            $this->issue_date = $this->invoice->issue_date->format('Y-m-d');
            $this->due_date = $this->invoice->due_date->format('Y-m-d');
            $this->status = $this->invoice->status->value;
            $this->notes = $this->invoice->notes;

            foreach ($this->invoice->items as $item) {
                $this->items[] = [
                    'id' => $item->id, // Track existing ID for updates
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ];
            }
        } else {
            $this->issue_date = date('Y-m-d');
            $this->due_date = date('Y-m-d', strtotime('+14 days'));
            // Start with one empty item row
            $this->addItem();
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'id' => null,
            'product_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'subtotal' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Re-index array
    }

    // Reactively update unit price when product is selected
    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'product_id') {
            $index = $parts[0];
            $productId = $value;

            $product = Product::find($productId);
            if ($product) {
                $this->items[$index]['unit_price'] = $product->current_price;
            }
        }
    }

    public function save(CalculateInvoiceTotalAction $calculateAction)
    {
        $validated = $this->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'invoice_number' => ['required', 'string', 'max:255', Rule::unique('invoices')->ignore($this->invoice)],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $this->invoice->fill([
            'client_id' => $this->client_id,
            'invoice_number' => $this->invoice_number,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        $this->invoice->save();

        // Sync Items
        // 1. Get IDs of existing items in the form
        $currentItemIds = collect($this->items)->pluck('id')->filter()->toArray();

        // 2. Delete items that were removed
        $this->invoice->items()->whereNotIn('id', $currentItemIds)->delete();

        // 3. Update or Create items
        foreach ($this->items as $itemData) {
            $this->invoice->items()->updateOrCreate(
                ['id' => $itemData['id'] ?? null],
                [
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['quantity'] * $itemData['unit_price'], // Calculate subtotal here or use observer
                ]
            );
        }

        // recalculate total
        $calculateAction->execute($this->invoice);

        return redirect()->route('invoices.index');
    }

    public function with(): array
    {
        return [
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ];
    }
};
?>

<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
    <form wire:submit="save" class="space-y-8">
        <!-- Invoice Header -->
        <div class="bg-white p-6 rounded-lg shadow space-y-4">
            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Invoice Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="client_id" class="block text-sm font-medium text-gray-700">Client</label>
                    <select wire:model="client_id" id="client_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->company ?? 'No Company' }})</option>
                        @endforeach
                    </select>
                    @error('client_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-gray-700">Invoice Number</label>
                    <input wire:model="invoice_number" type="text" id="invoice_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="issue_date" class="block text-sm font-medium text-gray-700">Issue Date</label>
                    <input wire:model="issue_date" type="date" id="issue_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('issue_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input wire:model="due_date" type="date" id="due_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                 <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach(App\Enums\InvoiceStatus::cases() as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea wire:model="notes" id="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="bg-white p-6 rounded-lg shadow space-y-4">
            <div class="flex justify-between items-center border-b pb-2">
                <h3 class="text-lg font-medium text-gray-900">Invoice Items</h3>
                <button type="button" wire:click="addItem" class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">Add Item</button>
            </div>

            <div class="space-y-4">
                @foreach($items as $index => $item)
                    <div class="grid grid-cols-12 gap-4 items-end border-b pb-4 last:border-0 last:pb-0">
                        <div class="col-span-5">
                            <label class="block text-xs font-medium text-gray-700">Product</label>
                            <select wire:model.live="items.{{ $index }}.product_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error("items.{$index}.product_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-700">Qty</label>
                            <input wire:model.live="items.{{ $index }}.quantity" type="number" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error("items.{$index}.quantity") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-3">
                            <label class="block text-xs font-medium text-gray-700">Price (IDR)</label>
                            <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error("items.{$index}.unit_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                         <div class="col-span-1 flex flex-col justify-end">
                             <label class="block text-xs font-medium text-gray-700">Subtotal</label>
                             <div class="py-2 text-sm text-gray-900 font-bold">
                                {{ number_format((float) ($items[$index]['quantity'] ?? 0) * (float) ($items[$index]['unit_price'] ?? 0), 0, ',', '.') }}
                             </div>
                        </div>

                        <div class="col-span-1">
                            <button type="button" wire:click="removeItem({{ $index }})" class="mb-1 text-red-600 hover:text-red-900 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
                 @error('items') <span class="text-red-500 text-xs block mt-2">{{ $message }}</span> @enderror
            </div>
            
             <div class="flex justify-end pt-4 border-t mt-4">
                 <div class="text-xl font-bold">
                    Total: Rp {{ number_format(collect($items)->sum(fn($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unit_price'] ?? 0)), 0, ',', '.') }}
                 </div>
             </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Invoice</button>
        </div>
    </form>
</div>