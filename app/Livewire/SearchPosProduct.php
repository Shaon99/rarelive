<?php

namespace App\Livewire;

use App\Models\Combo;
use App\Models\Product;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class SearchPosProduct extends Component
{
    use WithPagination;

    protected $listeners = [
        'refreshSearchPosProduct' => '$refresh',
        'loadMoreProducts',
    ];

    public $search = '';

    public $perPage = 12;

    public $showComboProducts = false;

    public $warehouse;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleComboProducts()
    {
        $this->showComboProducts = ! $this->showComboProducts;
    }

    public function loadMoreProducts()
    {
        $this->perPage += 12;
    }

    public function mount()
    {
        // Set default warehouse (admin's warehouse or first warehouse)
        $this->warehouse = auth()->guard('admin')->check() && auth()->guard('admin')->user()->warehouse_id
            ? auth()->guard('admin')->user()->warehouse_id
            : Warehouse::orderBy('id')->value('id');
    }

    public function updatedWarehouse()
    {
        // This will trigger when warehouse selection changes
        $this->resetPage();
    }

    public function render()
    {
        $warehouses = Warehouse::all();

        if ($this->showComboProducts) {
            $combos = Combo::with('products')
                ->when($this->search, function ($query) {
                    $query->where('name', 'LIKE', '%'.trim(strtolower($this->search)).'%');
                })
                ->orderBy('name')
                ->paginate($this->perPage);

            return view('livewire.search-pos-product', [
                'combos' => $combos,
                'warehouses' => $warehouses,
            ]);
        } else {
            $products = Product::select('id', 'code', 'name', 'quantity', 'image', 'sale_price', 'discount', 'discount_type', 'discount_date_range')
                ->whereHas('warehouses', function ($query) {
                    $query->where('warehouse_id', $this->warehouse)
                        ->where('quantity', '>', 0);
                })
                ->with(['warehouses' => function ($query) {
                    $query->where('warehouse_id', $this->warehouse);
                }])
                ->when($this->search, function ($query) {
                    $query->where('name', 'LIKE', '%'.trim(strtolower($this->search)).'%')
                        ->orWhere('code', 'LIKE', '%'.trim(strtolower($this->search)).'%');
                })
                ->latest()
                ->paginate($this->perPage);

            // Ensure the 'image' attribute is accessible and properly set in the Product model.
            // Check if the accessor or mutator for 'image' is correctly implemented in the Product model.

            return view('livewire.search-pos-product', [
                'products' => $products,
                'warehouses' => $warehouses,
            ]);
        }
    }
}
