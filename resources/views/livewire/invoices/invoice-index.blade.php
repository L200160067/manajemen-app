<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Invoice;
use App\Enums\InvoiceStatus;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $status = '';

    public function with(): array
    {
        $query = Invoice::query()->with(['client', 'items']);

        if ($this->search) {
            $query->whereHas('client', function ($q) {
                $q->search($this->search);
            })->orWhere('invoice_number', 'like', '%' . $this->search . '%');
        }

        if ($this->status) {
            $query->byStatus(InvoiceStatus::tryFrom($this->status));
        }

        return [
            'invoices' => $query->latest()->paginate(10),
        ];
    }
};
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Invoices
        </h2>
        <a href="{{ route('invoices.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Create Invoice
        </a>
    </div>

    <div class="flex justify-between gap-4 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search invoice number or client..."
            class="w-full max-w-md px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

        <select wire:model.live="status"
            class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Statuses</option>
            @foreach (App\Enums\InvoiceStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Number</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Client</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due
                            Date</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $invoice->client->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $invoice->issue_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    {{ $invoice->due_date->format('d M Y') }}
                                    @if($invoice->is_overdue)
                                        <span class="text-red-500 text-xs font-bold">(Overdue)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $invoice->formatted_total_amount }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($invoice->status === App\Enums\InvoiceStatus::Paid) bg-green-100 text-green-800 
                                        @elseif($invoice->status === App\Enums\InvoiceStatus::Sent) bg-blue-100 text-blue-800 
                                        @elseif($invoice->status === App\Enums\InvoiceStatus::Canceled) bg-gray-100 text-gray-800 
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $invoice->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('invoices.show', $invoice) }}"
                                    class="text-blue-600 hover:text-blue-900 mr-4">View</a>
                                <a href="{{ route('invoices.edit', $invoice) }}"
                                    class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</a>
                                <button wire:click="delete({{ $invoice->id }})"
                                    wire:confirm="Are you sure you want to delete this invoice?"
                                    class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $invoices->links() }}
        </div>
    </div>
</div>