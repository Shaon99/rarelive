<div>
    <div class="row">
        <div class="col-md-3 form-group mb-4" wire:ignore>
            <label for="">Filter By City</label>
            <select class="form-control select2" wire:model.live="district" id="district">
                <option value="">All</option>
                @foreach ($districts as $item)
                    <option value="{{ $item['name'] }}">{{ $item['name'] }}
                        {{ isset($item['bn_name']) && $item['bn_name'] ? '(' . $item['bn_name'] . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 form-group mb-4">
            <label>Filter By Thana</label>
            <select class="form-control" id="thana" wire:model.live="selectedThana"
                @if (empty($thanas)) disabled @endif>
                <option value="">All</option>
                @foreach ($thanas as $item)
                    <option value="{{ $item['name'] }}">
                        {{ $item['name'] }} {{ $item['bn_name'] ? '(' . $item['bn_name'] . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="d-flex justify-content-between">
        <div class="d-flex align-items-center">
            <select class="custom-select-box" wire:model.live="perPage">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="20">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>

        <div class="position-relative">
            <div class="search-input-container position-relative my-2">
                <i class="fas fa-search search-icon"></i>
                <input wire:model.live.debounce.250ms="search" type="text" class="search-input"
                    placeholder="Search records ..." />
            </div>
        </div>
    </div>

    <div class="table-wrapper position-relative">
        <div class="table-responsive">
                                 <!-- Loading Overlay -->
                    <div wire:loading.delay class="loading-overlay" style="display: none">
                        <div class="loading-overlay-text text-center">please wait...</div>
                    </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Actions</th>
                        @include('livewire.sortable-table-th', [
                            'name' => 'name',
                            'displayName' => 'Name',
                        ])
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Social Platform / Order From</th>
                        <th>Address</th>
                        <th>Due</th>
                        @include('livewire.sortable-table-th', [
                            'name' => 'created_at',
                            'displayName' => 'Date',
                        ])
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $index => $customer)
                        <tr wire:key={{ $customer->id }} class="border-b">
                            <td>
                                {{ $customers->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="actionDropdown{{ $customer->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="actionDropdown{{ $customer->id }}">
                                        <a href="{{ route('admin.customer.customerLedger', $customer->id) }}"
                                            class="dropdown-item" title="Ledger">
                                            <i class="fas fa-book-open mr-1"></i> Ledger
                                        </a>
                                        @if (auth()->guard('admin')->user()->can('customer_list'))
                                            <a href="{{ route('admin.customer.customerOrderHistory', $customer->id) }}"
                                                class="dropdown-item" title="Order history">
                                                <i class="fas fa-credit-card mr-1"></i> Order History
                                            </a>
                                        @endif
                                        @if (auth()->guard('admin')->user()->can('customer_edit'))
                                            <a href="{{ route('admin.customer.edit', $customer->id) }}"
                                                class="dropdown-item" title="Edit">
                                                <i class="fas fa-pencil-alt mr-1"></i> Edit
                                            </a>
                                        @endif
                                        @if (auth()->guard('admin')->user()->can('customer_delete'))
                                            <button class="dropdown-item text-danger"
                                                wire:click="confirmDelete({{ $customer->id }}, '{{ $customer->name }}')"
                                                title="Delete" type="button">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $customer->name ?? 'N/A' }}</td>
                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                            <td>{{ $customer->email ?? 'N/A' }}</td>
                            <td>
                                @if ($customer->social_type === 'facebook')
                                    <a href="{{ $customer->social_id }}" target="_blank">
                                        <i class="fab fa-facebook"></i>
                                        {{ Str::limit($customer->social_id, 20, '...') }}
                                    </a>
                                @elseif ($customer->social_type === 'whatsapp')
                                    <a href="https://wa.me/{{ $customer->social_id }}" class="" target="_blank">
                                        <i class="fab fa-whatsapp text-success mr-1 icon-size-social"></i>
                                        {{ $customer->social_id }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.customer.edit', $customer->id) }}" title="address">
                                    @if ($customer->addressBooks->isNotEmpty())
                                        @php
                                            $address = $customer->addressBooks->first()->address;
                                        @endphp
                                        @if (strlen($address) > 50)
                                            <div>{{ substr($address, 0, 50) }}</div>
                                            <div>{{ substr($address, 50) }}</div>
                                        @else
                                            {{ $address }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </a>
                            </td>
                            <td>{{ currency_format(customerDue($customer->id)) }}</td>
                            <td>{{ $customer->created_at->format('d M, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center text-secondary">{{ __('No record found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-top">
                    <tr>
                        <th colspan="2"><strong>Total: {{ $customers->count() }}</strong></th>
                    </tr>
                </tfoot>
            </table>
            <!-- Pagination Controls -->
            {{ $customers->links('livewire.pagination-table') }}
        </div>
    </div>
</div>
@push('script')
    <script>
        $('#confirmDeleteBtn').click(function() {
            @this.call('destroy');
        });

        $(document).ready(function() {
            $('#district').select2().on('select2:select', function(e) {
                @this.set('district', $('#district').select2("val"));
            });
        });
    </script>
@endpush
