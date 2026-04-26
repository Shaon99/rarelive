<div>
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Try Again!</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div x-data="{ showFilters: false }">
        <button @click="showFilters = !showFilters" class="btn btn-success btn-sm mb-2">
            <i class="fa fa-filter"></i> Filters
            <i :class="'fa fa-chevron-' + (showFilters ? 'up' : 'down') + ' ml-1'"></i>
        </button>

        <!-- Filters section -->
        <div x-show="showFilters" x-cloak class="mt-2">
            <div class="row">
                <div class="col-md-2 col-sm-6 col-6 mb-2">
                    <select class="form-control" wire:model="created_by">
                        <option value="" selected disabled>Created by</option>
                        @forelse ($adminUser as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
                @if ($general->pos_lead_on_off == 1)
                    <div class="col-md-2 col-sm-6 col-6 mb-2">
                        <select class="form-control" wire:model="lead_by">
                            <option value="" selected disabled>Lead by</option>
                            @forelse ($leads as $item)
                                <option value="{{ $item->id }}">{{ $item->employee_name }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                @endif
                <div class="col-md-2 col-sm-6 col-6 mb-2">
                    <input type="text" class="form-control" id="dateRangePicker" placeholder="Select Date Range"
                        autocomplete="off" />
                </div>
                <div class="col-md-2 col-sm-6 col-6 mb-2">
                    <select class="form-control" wire:model="system_status">
                        <option value="" selected disabled>Status</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Shipping</option>
                        <option value="completed">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 col-6 mb-2">
                    <select class="form-control" wire:model="courier_status">
                        <option value="" selected disabled>Shipping status</option>
                        <option value="in_review">In-Review</option>
                        <option value="pending">Pending</option>
                        <option value="delivered">Delivered</option>
                        <option value="partial_delivered">Partial-Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 col-6 mb-2">
                    <select class="form-control" wire:model="payment_status">
                        <option value="" selected disabled>Payment status</option>
                        <option value="2">Due</option>
                        <option value="1">Paid</option>
                    </select>
                </div>
                @if ($general->pos_platform_on_off == 1)
                    <div class="col-md-2 col-sm-6 col-6 mb-2">
                        <select class="form-control" wire:model="platform">
                            <option value="" selected disabled>Platform</option>
                            <option value="facebook">Facebook</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                @endif
                <div class="col-md-8 col-sm-6 col-6 mb-2">
                    <div class="d-flex">
                        <button wire:click="applyFilter" wire:loading.attr="disabled" wire:target="applyFilter"
                            class="btn btn-primary">
                            <span wire:loading.remove wire:target="applyFilter">Apply</span>
                            <span wire:loading wire:target="applyFilter">
                                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                            </span>
                        </button>
                        <button wire:click="resetFilter" title="Everything will be reset" wire:loading.attr="disabled"
                            wire:target="resetFilter" class="btn btn-danger ml-2">
                            <span wire:loading.remove wire:target="resetFilter">Reset</span>
                            <span wire:loading wire:target="resetFilter">
                                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="col-md-2 mb-2 mt-4">
                    <div class="d-flex justify-content-end">
                        @if (!$exporting && !$exportFinished)
                            <button wire:click="downloadCSV" class="btn btn-success btn-sm" wire:loading.attr="disabled"
                                wire:target="downloadCSV">
                                <span wire:loading.remove wire:target="downloadCSV">
                                    <i class="fa fa-download mr-1" aria-hidden="true"></i> Download CSV
                                </span>
                                <span wire:loading wire:target="downloadCSV">
                                    <i class="fa fa-spinner fa-spin mr-1" aria-hidden="true"></i> Exporting...
                                </span>
                            </button>
                        @endif
                        @if ($exporting && !$exportFinished)
                            <button class="btn btn-success btn-sm" wire:poll="updateExportProgress" disabled>
                                <span>
                                    <i class="fa fa-spinner fa-spin mr-1" aria-hidden="true"></i>Downloading...
                                </span>
                            </button>
                        @endif
                        @if ($exportFinished)
                            <button class="btn btn-success btn-sm" wire:click="downloadExport"
                                wire:loading.attr="disabled" wire:target="downloadExport">
                                <span>
                                    <i class="fa fa-download mr-1" aria-hidden="true"></i> Done. Download Now
                                </span>
                            </button>
                        @endif
                    </div>
                    @if ($exporting || $exportFinished)
                        <div class="progress mt-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: {{ intval($progress) }}%;" aria-valuenow="{{ intval($progress) }}"
                                aria-valuemin="0" aria-valuemax="100">
                                {{ intval($progress) }}%
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <div class="d-flex align-items-center">
                <select class="custom-select-box" wire:model.live="perPage">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="25">25</option>
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
    </div>

    <div class="table-wrapper position-relative">
        {{-- multiple sale delete --}}
        @if (auth()->guard('admin')->user()->can('order_delete'))
            <button type="button" class="btn btn-danger btn-sm d-none" id="deleteSelectedSale" disabled
                data-toggle="modal" data-target="#selectedConfirmDeleteModal">
                <i class="fas fa-trash"></i> Delete Selected
            </button>
        @endif
        <div class="d-flex justify-content-end py-2 mb-2">
            <button type="button" class="btn btn-info btn-sm" id="print-invoices-btn">
                <i class="fas fa-print"></i>
                <span class="d-none d-md-inline">Select & Print</span>
                <span class="d-inline d-md-none">Print</span>
            </button>
            <button type="button" class="btn btn-secondary btn-sm ml-2" id="print-level-labels-btn">
                <i class="fas fa-tag"></i>
                <span class="d-none d-md-inline">Print Delivery Labels</span>
                <span class="d-inline d-md-none">Labels</span>
            </button>
            @if ($general->enable_online_deliver == 1)
                @if (!request('system_status'))
                    <button type="button" class="btn btn-primary btn-sm ml-2" id="trackBtn">
                        <i class="fas fa-truck-fast"></i>
                        <span class="d-none d-md-inline">Check SteadFast Status</span>
                        <span class="d-inline d-md-none">Check</span>
                    </button>
                @endif
                <button type="button" class="btn btn-success btn-sm ml-2" id="sendToSteadFAst">
                    <i class="fas fa-truck-fast"></i>
                    <span class="d-none d-md-inline">Select & Send to Steadfast</span>
                    <span class="d-inline d-md-none">Steadfast</span>
                </button>
            @endif

            @if ($general->enable_carrybee == 1)
                @if (count($carrybeeAccountsForSend ?? []) > 0)
                    <label for="carrybeeAccountSelect" class="sr-only">Carrybee account</label>
                    <select id="carrybeeAccountSelect" name="carrybee_account_key"
                        class="form-control form-control-sm d-inline-block align-middle ml-2"
                        style="max-width: 12rem; width: auto;" title="Carrybee account">
                        @foreach ($carrybeeAccountsForSend as $cbAccount)
                            <option value="{{ $cbAccount['key'] }}"
                                @if (($cbAccount['store_id'] ?? '') !== '') title="{{ __('Store ID') }}: {{ $cbAccount['store_id'] }}" @endif>
                                {{ $cbAccount['title'] }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-success btn-sm ml-1" id="sendToCarrybee">
                        <i class="fas fa-truck-fast"></i>
                        <span class="d-none d-md-inline">Send with account</span>
                        <span class="d-inline d-md-none">Send</span>
                    </button>
                @else
                    <span class="text-muted small ml-2 align-middle"
                        title="Add accounts under Settings → Integration">
                        Carrybee: no accounts configured
                    </span>
                @endif
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllSale" title="Select All"></th>
                        <th>#</th>
                        <th>Actions</th>
                        @include('livewire.sortable-table-th', [
                            'name' => 'created_at',
                            'displayName' => 'Date',
                        ])
                        <th>Created By</th>
                        @if ($general->pos_lead_on_off == 1)
                            <th>Lead By</th>
                        @endif
                        @include('livewire.sortable-table-th', [
                            'name' => 'customer_id',
                            'displayName' => 'Name',
                        ])
                        <th>Phone</th>
                        <th>Invoice No</th>
                        <th>Stock Return</th>
                        <th>Status</th>
                        @if ($general->enable_online_deliver == 1)
                            <th title="Courier Name">Courier Name</th>
                            <th title="Consignment Id">Consignments ID</th>
                            <th title="Courier Delivery Status">Delivery Status</th>
                            <th title="Courier COD Charge">COD Charge</th>
                            <th title="Courier Delivery Cost">Delivery Cost</th>
                        @endif
                        <th>Discount</th>
                        <th title="Delivery Charge">Delivery Charge</th>
                        <th title="Cash On Delivery">Due</th>
                        <th>Paid</th>
                        <th>Grand Total</th>
                        @if ($general->enable_online_deliver == 1)
                            <th>Courier Cost</th>
                        @endif
                        <th title="Final">Final Amount</th>
                        @if ($general->pos_platform_on_off == 1)
                            <th>Platform</th>
                        @endif
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Branch</th>
                    </tr>
                </thead>
                <tbody>
                    <tr wire:loading wire:target="applyFilter, resetFilter, search, perPage">
                        <td colspan="100%" class="loading-overlay"></td>
                    </tr>
                    @forelse ($sales as $index => $sale)
                        <tr wire:key="{{ $sale->id }}" class="border-b">
                            <td>
                                <input type="checkbox" name="sales[]" value="{{ $sale->id }}"
                                    class="saleCheckbox">
                            </td>
                            <td>
                                <div class="d-flex justify-content-between">
                                    {{ $sales->firstItem() + $index }}
                                </div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 text-primary" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        ••• <i class="fas fa-chevron-down ml-1"></i>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        @if ($sale->system_status != 'draft')
                                            <a href="{{ route('admin.sales.show', $sale->id) }}"
                                                class="dropdown-item">
                                                <i class="fas fa-eye text-success mr-2"></i> View Details
                                            </a>
                                            <a href="{{ route('admin.invoice', $sale->id) }}" class="dropdown-item">
                                                <i class="fas fa-file-invoice text-warning mr-2"></i> Invoice
                                            </a>
                                            @if ($sale->due_amount && $sale->system_status != 'cancelled')
                                                <button type="button" class="dropdown-item due"
                                                    data-href="{{ route('admin.sales.duepayment', $sale->id) }}"
                                                    data-due="{{ $sale->due_amount }}"
                                                    data-name="{{ $sale->invoice_no . ' - Due Payment' }}">
                                                    <i class="fas fa-credit-card text-info mr-2"></i> Due Payment
                                                </button>
                                            @endif
                                        @endif
                                        @if ($sale->system_status === 'draft')
                                            @if (auth()->guard('admin')->user()->can('order_edit'))
                                                <a href="{{ route('admin.sales.edit', $sale->id) }}"
                                                    class="dropdown-item">
                                                    <i class="fas fa-pencil-alt text-primary mr-2"></i>Edit Draft
                                                </a>
                                            @endif
                                            @if (auth()->guard('admin')->user()->can('order_delete'))
                                                <button type="button" class="dropdown-item delete"
                                                    data-href="{{ route('admin.sales.destroy', $sale->id) }}">
                                                    <i class="fas fa-trash text-danger mr-2"></i> Delete
                                                </button>
                                            @endif
                                        @endif
                                        @if (
                                            (!empty($sale->consignment_id) && $sale->system_status == 'cancelled' && $sale->return_status != 'returned') ||
                                                empty($sale->consignment_id))
                                            @if (auth()->guard('admin')->user()->can('Sale_return_adjustment'))
                                                <a href="{{ route('admin.saleReturnAdjustment', $sale->id) }}"
                                                    class="dropdown-item">
                                                    <i class="fas fa-undo text-info mr-2"></i> Return
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $sale->created_at->format('d M, y h:i A') }}</td>
                            <td>{{ $sale->user->name ?? 'N/A' }}</td>
                            @if ($general->pos_lead_on_off == 1)
                                <td>{{ $sale->lead->employee_name ?? 'N/A' }}</td>
                            @endif
                            <td>
                                @if ($sale->customer)
                                    <a href="{{ route('admin.customer.customerLedger', $sale->customer->id) }}"
                                        target="_blank">
                                        {{ $sale->customer->name }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $sale->customer->phone ?? 'N/A' }}</td>
                            <td>
                                <span class="copyable selectable"
                                    onclick="copyToClipboard(this, '{{ $sale->invoice_no }}')">
                                    <a href="{{ route('admin.invoice', $sale->id) }}"> {{ $sale->invoice_no }} </a>
                                    @if ($sale->invoice_no != 'N/A')
                                        <i class="fas fa-copy copy-icon text-success ml-2"></i>
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if ($sale->return_status === 'pending')
                                    <span class="badge badge-warning">{{ __('Pending') }}</span>
                                @elseif($sale->return_status === 'returned')
                                    <span class="badge badge-success">{{ __('Returned') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('NO Need') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($sale->system_status === 'pending')
                                    <span class="badge badge-warning">{{ __('Shipping') }}</span>
                                @elseif($sale->system_status === 'completed')
                                    <span class="badge badge-success">{{ __('Delivered') }}</span>
                                @elseif($sale->system_status === 'partial_delivered')
                                    <span class="badge badge-info">{{ __('Partial Delivered') }}</span>
                                @elseif($sale->system_status === 'cancelled')
                                    <span class="badge badge-danger">{{ __('Cancelled') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            @if ($general->enable_online_deliver == 1)
                                <td>
                                    @if ($sale->courier_name === 'steadfast')
                                        <span class="badge badge-success">{{ $sale->courier_name }}</span>
                                    @elseif ($sale->courier_name === 'pathao')
                                        <span class="badge badge-danger">{{ $sale->courier_name }}</span>
                                    @elseif ($sale->courier_name === 'carrybee')
                                        <span class="badge badge-warning">{{ $sale->courier_name }}</span>
                                    @else
                                        <span class="badge badge-primary">{{ $sale->courier_name ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php $consignmentId = $sale->consignment_id ?? 'N/A'; @endphp
                                    <span class="copyable selectable"
                                        onclick="copyToClipboard(this, '{{ $consignmentId }}')">
                                        {{ $consignmentId }}
                                        @if ($consignmentId != 'N/A')
                                            <i class="fas fa-copy copy-icon text-success ml-2"></i>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if ($sale->status === 'pending' || $sale->status === 'in_review')
                                        <span
                                            class="badge badge-warning">{{ Str::headline($sale->status ?? 'N/A') }}</span>
                                    @elseif($sale->status === 'delivered')
                                        <span
                                            class="badge badge-success">{{ Str::headline($sale->status ?? 'N/A') }}</span>
                                    @elseif($sale->status === 'cancelled' || $sale->status === 'unknown')
                                        <span
                                            class="badge badge-danger">{{ Str::headline($sale->status ?? 'N/A') }}</span>
                                    @elseif($sale->status === 'partial_delivered')
                                        <span
                                            class="badge badge-info">{{ Str::headline($sale->status ?? 'N/A') }}</span>
                                    @else
                                        <span
                                            class="badge badge-primary">{{ Str::headline($sale->status ?? 'N/A') }}</span>
                                    @endif
                                </td>
                                <td>{{ currency_format($sale->cod_charge) ?? '0.00' }}</td>
                                <td>{{ currency_format($sale->shipping_cost) ?? '0.00' }}</td>
                            @endif
                            <td>{{ currency_format($sale->discount) }}</td>
                            <td>{{ currency_format($sale->system_delivery_charge) }}</td>
                            <td>{{ currency_format($sale->due_amount) }}</td>
                            <td>{{ currency_format($sale->paid_amount) }}</td>
                            <td>{{ currency_format($sale->grand_total) }}</td>
                            @if ($general->enable_online_deliver == 1)
                                <td>{{ currency_format($sale->cod_charge + $sale->shipping_cost) }}</td>
                            @endif
                            <td>{{ currency_format($sale->grand_total - ($sale->cod_charge + $sale->shipping_cost)) }}
                            </td>
                            @if ($general->pos_platform_on_off == 1)
                                <td>
                                    @if ($sale->platform === 'facebook')
                                        <span class="badge badge-primary">{{ __('Facebook') }}</span>
                                    @elseif($sale->platform === 'whatsapp')
                                        <span class="badge badge-success">{{ __('Whatsapp') }}</span>
                                    @elseif($sale->platform === 'others')
                                        <span class="badge badge-info">{{ __('Others') }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ __('N/A') }}</span>
                                    @endif
                                </td>
                            @endif
                            <td class="text-center">
                                @php $type = $sale->paymentMethod->type ?? ''; @endphp
                                @switch($type)
                                    @case('CASH')
                                        <span class="badge badge-success">{{ $sale->paymentMethod->name ?? 'N/A' }}</span>
                                    @break

                                    @case('STEADFAST')
                                        <span class="badge badge-warning">{{ $sale->paymentMethod->name ?? 'N/A' }}
                                            (COD)
                                        </span>
                                    @break

                                    @case('CARRYBEE')
                                        <span class="badge badge-warning">{{ $sale->paymentMethod->name ?? 'N/A' }}
                                            (COD)
                                        </span>
                                    @break

                                    @case('BANK')
                                        <span class="badge badge-info">{{ $sale->paymentMethod->name ?? 'N/A' }}</span>
                                    @break

                                    @case('MFS')
                                        <span class="badge badge-primary">{{ $sale->paymentMethod->name ?? 'N/A' }}</span>
                                    @break

                                    @default
                                        <span class="badge badge-dark">{{ $sale->paymentMethod->name ?? 'COD' }}</span>
                                @endswitch
                            </td>
                            <td class="text-center">
                                @if ($sale->payment_status == 2)
                                    <span class="badge badge-danger">{{ __('Due') }}</span>
                                @elseif($sale->payment_status == 1)
                                    <span class="badge badge-success">{{ __('Paid') }}</span>
                                @elseif($sale->payment_status == 3)
                                    <span class="badge badge-info">{{ __('Partial') }}</span>
                                @endif
                            </td>
                            <td>{{ $sale->warehouse->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center text-secondary">{{ __('No record found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            @if ($general->enable_online_deliver == 1)
                                @if ($general->pos_platform_on_off == 1 && $general->pos_lead_on_off == 1)
                                    <td colspan="14" class="text-right"></td>
                                @else
                                    <td colspan="13" class="text-right"></td>
                                @endif
                            @else
                                @if ($general->pos_platform_on_off == 1 && $general->pos_lead_on_off == 1)
                                    <td colspan="12" class="text-right"></td>
                                @else
                                    <td colspan="10" class="text-right"></td>
                                @endif
                            @endif
                            @if ($general->enable_online_deliver == 1)
                                <td><strong>{{ currency_format($sales->sum('cod_charge')) }}</strong></td>
                                <td><strong>{{ currency_format($sales->sum('shipping_cost')) }}</strong></td>
                            @endif
                            <td><strong>{{ currency_format($sales->sum('discount')) }}</strong></td>
                            <td><strong>{{ currency_format($sales->sum('system_delivery_charge')) }}</strong></td>
                            <td><strong>{{ currency_format($sales->sum('due_amount')) }}</strong></td>
                            <td><strong>{{ currency_format($sales->sum('paid_amount')) }}</strong></td>
                            <td><strong>{{ currency_format($sales->sum('grand_total')) }}</strong></td>
                            @if ($general->enable_online_deliver == 1)
                                <td><strong>{{ currency_format($sales->sum('cod_charge') + $sales->sum('shipping_cost')) }}</strong>
                                </td>
                            @endif
                            <td><strong>{{ currency_format($sales->sum('grand_total') - ($sales->sum('cod_charge') + $sales->sum('shipping_cost'))) }}</strong>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- Pagination Controls -->
            {{ $sales->links('livewire.pagination-table') }}
        </div>
    </div>

    @push('script')
        <script>
            function copyToClipboard(element, text) {
                try {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text).then(() => {
                            updateCopyIcon(element);
                        }).catch(() => {
                            fallbackCopy(text, element);
                        });
                    } else {
                        fallbackCopy(text, element);
                    }
                } catch (err) {
                    console.error("Copy failed:", err);
                }
            }

            function fallbackCopy(text, element) {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    updateCopyIcon(element);
                } catch (err) {
                    console.error("Fallback copy failed:", err);
                }
                document.body.removeChild(textArea);
            }

            function updateCopyIcon(element) {
                const icon = element.querySelector(".copy-icon");
                if (!icon) return;
                icon.classList.remove("fa-copy");
                icon.classList.add("fa-check");
                setTimeout(() => {
                    icon.classList.remove("fa-check");
                    icon.classList.add("fa-copy");
                }, 2000);
            }

            document.addEventListener('livewire:init', () => {
                let startDate = null;
                let endDate = null;

                $('#dateRangePicker').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        format: 'DD-MM-YYYY',
                        separator: ' to ',
                    },
                });

                $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                    $('#dateRangePicker').val(picker.startDate.format('DD-MM-YYYY') + ' to ' + picker.endDate
                        .format('DD-MM-YYYY'));
                    startDate = picker.startDate.format('YYYY-MM-DD');
                    endDate = picker.endDate.format('YYYY-MM-DD');
                });

                $('button[wire\\:click="applyFilter"]').on('click', function() {
                    if (startDate && endDate) {
                        @this.set('startDate', startDate);
                        @this.set('endDate', endDate);
                    }
                });

                $('button[wire\\:click="resetFilter"]').on('click', function() {
                    $('#dateRangePicker').val('');
                    startDate = '';
                    endDate = '';
                });
            });
        </script>
    @endpush
