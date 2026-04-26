<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerTable extends Component
{
    use WithPagination;

    public $search = '';

    public $sortBy = 'created_at';

    public $sortDir = 'ASC';

    public $district = '';

    public $perPage = 25;

    public array $districts = [];

    public $thanas = [];

    public $selectedThana = '';

    public function updatedDistrict($value)
    {
        $this->selectedThana = '';
        $this->fetchThanas($value);
    }

    public function fetchThanas($cityName)
    {
        $json = file_get_contents(public_path('assets/address.json'));
        $data = json_decode($json, true);

        $district = collect($data['district'])->firstWhere('name', $cityName);

        $this->thanas = $district['thana'] ?? [];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function mount()
    {
        $json = file_get_contents(public_path('assets/address.json'));
        $city = json_decode($json, true);

        $this->districts = collect($city['district'])->map(function ($district) {
            return [
                'name' => $district['name'],
                'bn_name' => $district['bn_name'] ?? null,
            ];
        })->all();
    }

    public function setSortBy($sortByField): void
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == 'ASC') ? 'DESC' : 'ASC';

            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public $customerIdToDelete;

    public function confirmDelete($customerId, $name)
    {
        $this->customerIdToDelete = $customerId;

        $this->dispatch('show-delete-modal', name: $name);
    }

    public function destroy()
    {
        Customer::find($this->customerIdToDelete)->delete();

        $this->dispatch('close-delete-modal');
    }

    public function render()
    {
        $query = Customer::with(['addressBooks' => function ($query) {
            $query->latest()->limit(1);
        }])
            ->search($this->search)
            ->orderBy($this->sortBy, $this->sortDir);

        if ($this->district) {
            $query->whereHas('addressBooks', function ($q) {
                $q->where('city', $this->district);
            });
        }

        if ($this->selectedThana) {
            $query->whereHas('addressBooks', function ($q) {
                $q->where('thana', $this->selectedThana);
            });
        }

        return view('livewire.customer-table', [
            'customers' => $query->paginate($this->perPage),
        ]);
    }
}
