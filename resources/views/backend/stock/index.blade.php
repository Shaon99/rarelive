@extends('backend.layout.master')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('SL') }}</th>
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Quantity') }}</th>
                                            <th>{{ __('Sales Price') }}</th>
                                            <th>{{ __('Last Purchases Price') }}</th>
                                            <th>{{ __('Avg Purchases Price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($stockProducts as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ number_format($item->quantity) }}
                                                </td>
                                                <td>{{ currency_format($item->sale_price) }}
                                                </td>
                                                <td>{{ currency_format($item->purchase_price) }}
                                                </td>
                                                <td>
                                                    {{ currency_format($item->average_unit_price) }}
                                                    <a href="javascript:void(0)" data-history="{{ $item->stockProduct }}"
                                                        data-name="{{ $item->name }}"
                                                        class="ml-2 text-primary purchasesHistory">
                                                        <i class="fa fa-eye"></i> <span>{{ __('History') }}</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">{{ __('Total') }}</th>
                                            <th>{{ number_format($stockProducts->sum('quantity')) }}</th>
                                            <th>{{ currency_format($stockProducts->sum('sale_price')) }}</th>
                                            <th>{{ currency_format($stockProducts->sum('purchase_price')) }}</th>
                                            <th>{{ currency_format($stockProducts->avg('average_unit_price')) }}</th>
                                        </tr>
                                    </tfoot>                                    
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </div>

    <div id="purchasesHistoryModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span id="productName"></span> Purchases History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body purchases-modal-body">

                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        'use strict';
        $(document).ready(function() {
            $('.purchasesHistory').on('click', function() {
                const modal = $('#purchasesHistoryModal');
                const name = $(this).data('name');
                const history = $(this).data('history');
                const modalBody = $('.purchases-modal-body');
                modalBody.html('');
                // Create a table if there is history data
                if (history && history.length > 0) {
                    let tableHTML = `
                        <div class="mb-3">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by Supplier Name">
                        </div>
                        <table class="table" id="historyTable">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Date</th>
                                    <th>Quantity</th>
                                    <th>Purchases Price</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    history.forEach((item, index) => {
                        tableHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${formatDate(item.created_at)}</td>
                                <td>${item.quantity ? item.quantity : 0}</td>
                                <td>${item.current_net_unit_price ? Number(item.current_net_unit_price).toFixed(2) : '0.00'} {{ $general->site_currency }}</td>
                                <td>${item.purchase && item.purchase.supplier ? item.purchase.supplier.name : 'N/A'}</td>
                            </tr>
                        `;
                    });

                    tableHTML += `
                    </tbody>
                </table>
            `;
                    modalBody.append(tableHTML);

                    // Add filtering functionality
                    const searchInput = document.querySelector('#searchInput');
                    const tableRows = document.querySelectorAll('#historyTable tbody tr');

                    searchInput.addEventListener('input', function() {
                        const searchValue = searchInput.value.toLowerCase();

                        tableRows.forEach(row => {
                            const supplierName = row.querySelector('td:nth-child(5)')
                                .textContent.toLowerCase();
                            if (supplierName.includes(searchValue)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                } else {
                    modalBody.append('<p class="text-muted">No history available.</p>');
                }

                $('#productName').text(name);
                modal.modal('show');
            });
        });

        // Utility function to format date
        function formatDate(dateString) {
            const options = {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            };
            const date = new Date(dateString);
            return date.toLocaleString('en-US', options);
        }
    </script>
@endpush
