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
                        <div id="loading-overlay" class="loading-overlay" style="display: none;">
                            <div class="loading-overlay-text text-center">please wait...</div>
                        </div>
                        <form action="{{ route('admin.transfer.post') }}" method="POST" class="needs-validation"
                            novalidate="">
                            @csrf
                            <div class="row p-3">
                                <div class="col-md-3">
                                    <label for="">Products</label>
                                    <select name="product" class="form-control select2" id="product-select" required="">
                                        <option value="" selected disabled>Select product</option>
                                        @forelse ($product as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-feedback">
                                        {{ __('product should be selected') }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="">From Branch</label>
                                    <select name="from_warehouse" class="form-control select2" id="from-warehouse"
                                        required="" disabled>
                                        <option value="" selected disabled>Select branch</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        {{ __('warehouse should be selected') }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="">Amount Transfer Quantity</label>
                                    <input type="text" class="form-control" name="transfer_quantity"
                                        placeholder="Amount of quantity transfer" required="">
                                    <div class="invalid-feedback">
                                        {{ __('product quantity required') }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="">To Branch</label>
                                    <select name="to_warehouse" class="form-control select2" id="to-warehouse"
                                        required="" disabled>
                                        <option value="" selected disabled>Select branch</option>
                                        @foreach ($warehouse as $warehouseItem)
                                            <option value="{{ $warehouseItem->id }}">{{ $warehouseItem->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        {{ __('warehouse should be selected') }}
                                    </div>
                                </div>
                            </div>

                            <div class="float-right p-3">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table_1" class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('SL') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Product Name') }}</th>
                                            <th>{{ __('Transfer Quantity') }}</th>
                                            <th>{{ __('Transfer From Branch') }}</th>
                                            <th>{{ __('Transfer To Branch') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transferProduct as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->created_at->format('d M, Y H:i a') }}</td>
                                                <td>{{ @$item->product->name }}</td>
                                                <td>{{ @$item->quantity }}</td>
                                                <td>{{ @$item->warehouseFrom->name }}</td>
                                                <td>{{ @$item->warehouseTo->name }}</td>
                                                <td>
                                                    @if (auth()->guard('admin')->user()->can('product_transfer_delete'))
                                                        <button class="btn btn-danger delete btn-sm"
                                                            data-href="{{ route('admin.productTransfer.destroy', $item->id) }}"
                                                            data-toggle="tooltip" title="Delete" type="button">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('#product-select').change(function() {
                var productId = $(this).val();
                if (productId) {
                    var url = "{{ route('admin.getWarehousesByProduct', ':productId') }}";
                    url = url.replace(':productId', productId);
                    $.ajax({
                        url: url,
                        method: 'GET',
                        "beforeSend": function() {
                            $('#loading-overlay').show();
                        },
                        "complete": function() {
                            $('#loading-overlay').hide();
                        },
                        success: function(response) {
                            $('#from-warehouse').empty();
                            $('#from-warehouse').append(
                                '<option value="" selected disabled>Select Warehouse</option>'
                            );

                            $('#from-warehouse').prop('disabled', false);

                            $.each(response, function(index, warehouse) {
                                $('#from-warehouse').append('<option value="' +
                                    warehouse.warehouse.id + '">' + warehouse
                                    .warehouse.name + ' (QTY: ' + warehouse
                                    .quantity + ')</option>');
                            });

                            $('#from-warehouse').select2();
                        },
                        error: function() {
                            alert('Error fetching warehouses');
                        }
                    });
                } else {
                    $('#from-warehouse').prop('disabled', true).empty().append(
                        '<option value="" selected disabled>Select Warehouse</option>');
                }
            });

            $('#from-warehouse').change(function() {
                $('#to-warehouse').prop('disabled', false);
                var fromWarehouseId = $(this).val();
                $('#to-warehouse option').each(function() {
                    if ($(this).val() == fromWarehouseId) {
                        $(this).prop('disabled', true);
                    }
                });

                $('#to-warehouse').select2();
            });
        });
    </script>
@endpush
