@extends('backend.layout.master')
@push('style')
<style>
    @media print {
        #print {
            display: none;
        }
    }

    @media print {
        body {
            -webkit-print-color-adjust: exact;
        }
    }

    address h5,h6{
        font-size: 17px
    }
    th,.table td {
    text-align: inherit;
    font-size: 20px!important;
    color: #000!important;
    font-weight: 600
}
</style>
@endpush
@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>{{ __(@$pageTitle) }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item">{{ __(@$pageTitle) }}</div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.sales.index') }}">{{ __('Sales') }}</a>

                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="invoice">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-center mb-5 text-dark text-uppercase">
                                {{ @$receipt->customer->name }}    {{ __('Due Payment Receipt') }}
                                
                            </h5>
                        </div>
                    </div>
                    <div class="invoice-print " id="print_setion">
                        <div class="row">
                            <div class="col-lg-12 text-dark">
                                <div class="invoice-title d-flex justify-content-between">
                                    <img src="{{ getFile('logo', @$general->logo) }}" alt="img" width="180px"
                                        height="180px" class="img-fluid rounded">
                                    <h2 class="mt-5 text-dark">ID #{{ @$receipt->id }}
                                    </h2>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6 col-lg-6 col-sm-6 col-6">
                                        <h4>Receipt Form</h4>
                                        <h6>{{ @$general->sitename }}</h6>
                                        <h6>{{ @$general->site_phone }}</h6>
                                        <h6>{{ @$general->site_email }}</h6>
                                        <h6>{{ @$general->site_address }}</h6>
                                    </div>
                                    <div class="col-md-6 col-lg-6 col-sm-6 col-6 text-md-right">
                                        <h4>Receipt To</h4>
                                        <h6>{{ @$receipt->customer->name }}</h6>
                                        <h6>{{ @$receipt->customer->phone }}</h6>
                                        <h6>{{ @$receipt->customer->email }}</h6>
                                        <h6>{{ @$receipt->customer->address }}</h6>
                                    </div>
                                </div>                                
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4 class="text-dark">Receipt Summary</h4>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-md">

                                        <tr>
                                            <th>Previous Due</th>
                                            <th>Receive Amount</th>
                                            <th>Current Due</th>
                                            <th>Date</th>
                                        </tr>

                                        <tr>
                                            <td>{{ $receipt->previous_due. ' ' . @$general->site_currency  }}</td>
                                            <td>{{ $receipt->receive_amount. ' ' . @$general->site_currency  }}</td>
                                            <td>{{ $receipt->current_due. ' ' . @$general->site_currency  }}</td>
                                            <td>
                                                {{ $receipt->created_at->format('d/m/y') }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>                               
                            </div>                            
                        </div>
                        <div class="py-5">
                            <div class="float-lg-right mb-lg-0 mb-3">
                                <button class="btn btn-success btn-icon icon-left" id="print"><i class="fas fa-print"></i>
                                    Print </button>
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
    "use Strict";

        $(document).ready(function() {
            $(document).on('click', '#print', function() {
                printDivSection();
            });
        });

        function printDivSection() {
            var Contents_Section = document.getElementById('print_setion').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = Contents_Section;

            window.print();

            document.body.innerHTML = originalContents;
        }
</script>
@endpush