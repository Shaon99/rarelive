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
            <div class="card">
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        @if (auth()->guard('admin')->user()->can('transfer_fund'))
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ session('activeTab') == 'add_fund' || session('activeTab') == 'withdraw_fund' ? '' : 'active' }}"
                                    id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home"
                                    aria-selected="{{ session('activeTab') == 'add_fund' ? 'false' : 'true' }}">
                                    Account Transfer
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('add_fund'))
                            <!-- Add Funds Tab -->
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ session('activeTab') == 'add_fund' ? 'active' : '' }}"
                                    id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                                    aria-controls="profile"
                                    aria-selected="{{ session('activeTab') == 'add_fund' ? 'true' : 'false' }}">
                                    Add Funds
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('withdraw_fund'))
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ session('activeTab') == 'withdraw_fund' ? 'active' : '' }}"
                                    id="withdraw-tab" data-toggle="tab" href="#withdraw" role="tab"
                                    aria-controls="withdraw"
                                    aria-selected="{{ session('activeTab') == 'withdraw_fund' ? 'true' : 'false' }}">
                                    Withdraw Funds
                                </a>
                            </li>
                        @endif
                        {{-- @if (auth()->guard('admin')->user()->can('owner_capital'))
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                                    aria-controls="contact" aria-selected="false">Owner Capital</a>
                            </li>
                        @endif --}}
                    </ul>

                    <!-- Tab content -->
                    <div class="tab-content pt-3" id="myTabContent">
                        @if (auth()->guard('admin')->user()->can('transfer_fund'))
                            <!-- Transfer Tab -->
                            <div class="tab-pane fade {{ session('activeTab') == 'add_fund' || session('activeTab') == 'withdraw_fund' ? '' : 'show active' }}"
                                id="home" role="tabpanel" aria-labelledby="home-tab">
                                <form action="{{ route('admin.transferFunds') }}" method="POST" class="needs-validation"
                                    novalidate="">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="accountSelect" class="form-label">Transfer From</label>
                                            <select class="form-control select2 accountSelectForm" name="from_account_id"
                                                required="">
                                                <option value="" selected disabled>Select Account</option>
                                                @forelse ($accounts as $item)
                                                    <option value="{{ $item->id }}"
                                                        data-balance="{{ $item->current_balance }}">
                                                        {{ $item->name }}
                                                        @if ($item->account_number)
                                                            (AC: {{ $item->account_number }})
                                                        @endif
                                                    </option>
                                                @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-feedback"> {{ __('Select an account for transfer') }}
                                            </div>
                                        </div>

                                        <!-- Account Balance Display -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label class="form-label">Account Balance
                                                ({{ $general->site_currency }})</label>
                                            <input type="text" class="form-control" name="balance_was"
                                                id="accountBalance" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="accountSelect" class="form-label">Transfer To</label>
                                            <select class="form-control select2" name="to_account_id" required="">
                                                <option value="" selected disabled>Select Account</option>
                                                @forelse ($accounts as $item)
                                                    <option value="{{ $item->id }}"
                                                        data-balance="{{ $item->current_balance }}">
                                                        {{ $item->name }}
                                                        @if ($item->account_number)
                                                            (AC: {{ $item->account_number }})
                                                        @endif
                                                    </option>
                                                @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-feedback"> {{ __('Select an account for transfer') }}
                                            </div>
                                        </div>

                                        <!-- Withdraw Amount -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label class="form-label">Enter Withdraw Amount
                                                ({{ $general->site_currency }})</label>
                                            <input type="number" class="form-control" name="amount" id="withdrawAmount"
                                                value="{{ old('amount') }}" placeholder="Enter amount" required="">
                                            <div class="invalid-feedback"> {{ __('Amount cannot be empty') }} </div>
                                            <small class="error-c text-danger"></small>
                                        </div>

                                        <!-- Transaction Date -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label class="form-label">Transaction Date</label>
                                            <input type="text" name="transaction_date" class="form-control datepicker"
                                                placeholder="Select Date" required=""
                                                value="{{ old('transaction_date') }}">
                                            <div class="invalid-feedback"> {{ __('Transaction date cannot be empty') }}
                                            </div>
                                        </div>

                                        <!-- Note -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="note" class="form-label">Note</label>
                                            <textarea name="note" class="form-control" cols="1" rows="1" placeholder="Type here..."></textarea>
                                        </div>
                                    </div>
                                    <!-- Submit Button -->
                                    <div class="d-flex justify-content-end mt-2">
                                        <button class="btn btn-primary"
                                            type="submit">{{ __('Transfer Amount') }}</button>
                                    </div>
                                </form>
                                <div class="mt-0 mb-4">
                                    <div class="card-header px-0">
                                        <h4 class="mb-0">Transfer History</h4>
                                    </div>
                                    <div class="card-body px-0">
                                        <div class="table-responsive" id="transactionsTable">
                                            @include('backend.accounts.transactions_table')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (auth()->guard('admin')->user()->can('add_fund'))
                            <!-- Deposit Tab -->
                            <div class="tab-pane fade {{ session('activeTab') == 'add_fund' ? 'show active' : '' }}"
                                id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <form action="{{ route('admin.addFund') }}" method="POST" class="needs-validation"
                                    novalidate="">
                                    @csrf
                                    <div class="row">
                                        <!-- Account Selection -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="accountSelect" class="form-label">Select
                                                Account</label>
                                            <select class="form-control select2" name="account_to_add" required="">
                                                <option value="" selected disabled>Select Account</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        data-balance="{{ $account->current_balance }}">
                                                        {{ $account->name }}
                                                        @if ($account->account_number)
                                                            (AC: {{ $account->account_number }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback"> {{ __('Select an account for transfer') }}
                                            </div>
                                        </div>
                                        <!-- Add Amount -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="addAmount" class="form-label">Enter Fund Amount
                                                ({{ $general->site_currency }})</label>
                                            <input type="number" class="form-control" name="amount"
                                                value="{{ old('amount') }}" placeholder="Enter amount" required="">
                                            <div class="invalid-feedback"> {{ __('Amount cannot be empty') }} </div>
                                        </div>

                                        <!-- Transaction Date -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label class="form-label">Transaction Date</label>
                                            <input type="text" name="transaction_date" class="form-control datepicker"
                                                placeholder="Select Date">
                                        </div>

                                        <!-- Note -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="note" class="form-label">Note</label>
                                            <textarea name="note" class="form-control" cols="1" rows="1" placeholder="Type here..."></textarea>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-flex justify-content-end mt-3">
                                        <button class="btn btn-primary" type="submit">Add Fund</button>
                                    </div>
                                </form>
                                <div class="mt-0 mb-4">
                                    <div class="card-header px-0">
                                        <h4 class="mb-0">Add Fund History</h4>
                                    </div>
                                    <div class="card-body px-0">
                                        <div class="table-responsive" id="add_fund_transactionsTable">
                                            @include('backend.accounts.add_fund_transactions_table')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (auth()->guard('admin')->user()->can('withdraw_fund'))
                            <!-- withdraw Tab -->
                            <div class="tab-pane fade {{ session('activeTab') == 'withdraw_fund' ? 'show active' : '' }}"
                                id="withdraw" role="tabpanel" aria-labelledby="withdraw-tab">
                                <form action="{{ route('admin.withdrawFund') }}" method="POST" class="needs-validation"
                                    novalidate="">
                                    @csrf
                                    <div class="row">
                                        <!-- Account Selection -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="accountSelect" class="form-label">Select
                                                Account</label>
                                            <select class="form-control select2 account_select_for_withdraw"
                                                name="account_to_add" required="">
                                                <option value="" selected disabled>Select Account</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        data-balance="{{ $account->current_balance }}">
                                                        {{ $account->name }}
                                                        @if ($account->account_number)
                                                            (AC: {{ $account->account_number }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback"> {{ __('Select an account for transfer') }}
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="currentBalance" class="form-label">Current Balance
                                                ({{ $general->site_currency }})</label>
                                            <input type="number" class="form-control" placeholder="0.00"
                                                name="current_balance" id="currentBalance_account" readonly>
                                        </div>

                                        <!-- Enter Withdraw Amount -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="withdrawAmount" class="form-label">Enter Withdraw Amount
                                                ({{ $general->site_currency }})</label>
                                            <input type="number" class="form-control" name="amount"
                                                id="withdrawFundAmount" value="{{ old('amount') }}"
                                                placeholder="Enter amount" required="">
                                            <div id="withdrawError" class="invalid-feedback d-none">Withdrawal amount
                                                cannot
                                                exceed current balance.</div>
                                        </div>

                                        <!-- Transaction Date -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label class="form-label">Transaction Date</label>
                                            <input type="text" name="transaction_date" class="form-control datepicker"
                                                placeholder="Select Date">
                                        </div>

                                        <!-- Note -->
                                        <div class="col-md-3 col-6 mb-3">
                                            <label for="note" class="form-label">Note</label>
                                            <textarea name="note" class="form-control" cols="1" rows="1" placeholder="Type here..."></textarea>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-flex justify-content-end mt-3">
                                        <button class="btn btn-primary" type="submit">Withdraw Fund</button>
                                    </div>
                                </form>
                                <div class="mt-0 mb-4">
                                    <div class="card-header px-0">
                                        <h4 class="mb-0">Withdraw Fund History</h4>
                                    </div>
                                    <div class="card-body px-0">
                                        <div class="table-responsive" id="withdraw_fund_transactionsTable">
                                            @include('backend.accounts.withdraw_fund_transactions_table')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{-- @if (auth()->guard('admin')->user()->can('owner_capital'))
                            <!-- Owner Capital Tab -->
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                <div class="row">
                                    <!-- Owner's Capital Input and Button Group -->
                                    <div class="col-md-4 col-4 mb-3 form-group">
                                        <label class="form-label">Owner Capital</label>
                                        <!-- Input field -->
                                        <input type="number" name="opening_balance" id="opening_balance"
                                            placeholder="0.00" class="form-control mb-2 rounded-pill"
                                            value="{{ @$general->opening_balance }}"
                                            {{ @$general->opening_balance ? 'readonly' : '' }}>

                                        <!-- Reset or Update Button -->
                                        <div id="capital-button-group">
                                            <button type="button" id="capitalActionButton" data-mode="reset"
                                                class="btn btn-primary w-100 rounded-pill">
                                                <i class="fas fa-undo-alt mr-1"></i> Reset to Update Again
                                            </button>
                                        </div>
                                    </div>


                                    <div class="col-md-4 col-4 mb-3 form-group">
                                        <label class="form-label">SteadFast Account</label>

                                        <div class="px-0">
                                            <!-- Balance Check Button -->
                                            <button id="checkBalanceBtn"
                                                class="btn btn-primary balance-btn bg-white w-100 text-primary  rounded-pill">
                                                <i class="fas fa-hand-point-up icon-size"></i>
                                                Check SteadFast Balance
                                            </button>
                                            <!-- Balance Display (Hidden by Default) -->
                                            <div id="balanceDisplay" class="balance-display">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-wallet mr-2 icon-size"></i>
                                                        <span class="balance-amount" id="current_balance"></span>
                                                    </div>
                                                    <button id="hideBalanceBtn"
                                                        class="btn btn-sm text-secondary border-0 p-0">
                                                        <i class="fas fa-times icon-size"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif --}}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            let selectedBalance = 0;

            // Withdraw amount input validation
            $('#withdrawAmount').on('input', function() {
                let amount = parseFloat($(this).val());
                if (!isNaN(amount) && selectedBalance !== 0 && amount > selectedBalance) {
                    showToast('Withdraw amount cannot be greater than account balance!', 'error');
                    $('.error-c').text('Withdraw amount cannot be greater than account balance!');
                    $(this).val('');
                } else {
                    $('.error-c').text('');
                }
            });

            // Account selection logic
            $('.accountSelectForm').on('change', function() {
                let selectedValue = $(this).val();

                if (selectedValue === 'steadfast') {
                    // Fetch balance from API
                    $.ajax({
                        url: '{{ route('admin.steadfast.current.balance') }}',
                        method: 'GET',
                        beforeSend: function() {
                            $('#accountBalance').val('Loading...');
                        },
                        success: function(res) {
                            selectedBalance = parseFloat(res.current_balance) || 0;
                            $('#accountBalance').val(selectedBalance.toFixed(2));
                        },
                        error: function() {
                            $('#accountBalance').val('Error fetching balance');
                            selectedBalance = 0;
                        }
                    });
                } else {
                    // Get balance from selected option's data
                    let balance = parseFloat($(this).find(':selected').data('balance')) || 0;
                    selectedBalance = balance;
                    $('#accountBalance').val(balance.toFixed(2));
                }
            });

            $('.accountSelectForm').on('change', function() {
                var selectedFromAccount = $(this).val();
                $('select[name="to_account_id"] option').prop('disabled', false);
                if (selectedFromAccount) {
                    $('select[name="to_account_id"] option[value="' + selectedFromAccount + '"]').prop(
                        'disabled', true);
                }

                $('select[name="to_account_id"]').select2();
            });
        });

        // $(document).ready(function() {
        //     const checkBalanceBtn = $('#checkBalanceBtn');
        //     const balanceDisplay = $('#balanceDisplay');
        //     const hideBalanceBtn = $('#hideBalanceBtn');
        //     let balanceCache = null;
        //     let cacheTimestamp = null;

        //     checkBalanceBtn.on('click', function() {
        //         const now = new Date().getTime();

        //         if (balanceCache && cacheTimestamp && (now - cacheTimestamp < 2 * 60 * 1000)) {
        //             // Use cached balance if it's within 2 minutes
        //             balanceDisplay.find('.balance-amount').text(balanceCache.toFixed(2) +
        //                 ' {{ $general->site_currency }}');
        //         } else {
        //             // Fetch balance from API
        //             $.ajax({
        //                 url: '{{ route('admin.steadfast.current.balance') }}', // Replace with your actual route
        //                 method: 'GET',
        //                 beforeSend: function() {
        //                     balanceDisplay.find('.balance-amount').text('Loading...');
        //                 },
        //                 success: function(response) {
        //                     balanceCache = response.current_balance ?? 0;
        //                     cacheTimestamp = now;
        //                     balanceDisplay.find('.balance-amount').text(balanceCache.toFixed(
        //                         2) + ' {{ $general->site_currency }}');
        //                 },
        //                 error: function() {
        //                     balanceDisplay.find('.balance-amount').text(
        //                         'Error fetching balance');
        //                 }
        //             });
        //         }

        //         checkBalanceBtn.hide();
        //         balanceDisplay.show();
        //     });

        //     hideBalanceBtn.on('click', function() {
        //         balanceDisplay.hide();
        //         checkBalanceBtn.show();
        //     });
        // });


        // $(document).on('click', '#capitalActionButton', function(e) {
        //     e.preventDefault();

        //     const $btn = $(this);
        //     const mode = $btn.data('mode');

        //     if (mode === 'reset') {
        //         $('#opening_balance').prop('readonly', false).focus();
        //         $btn
        //             .data('mode', 'update')
        //             .removeClass('btn-primary')
        //             .addClass('btn-success')
        //             .html('<i class="fas fa-check-circle mr-1"></i> Owner Capital Update');

        //     } else if (mode === 'update') {
        //         const openingBalance = $('#opening_balance').val();
        //         if (openingBalance === '') {
        //             showToast('Please enter a valid value.', 'error');
        //             return;
        //         }

        //         $('#opening_balance').prop('disabled', true);
        //         $btn.prop('disabled', true).text('Loading...');

        //         $.ajax({
        //             url: '{{ route('admin.update.ownerCapital') }}',
        //             type: 'POST',
        //             data: {
        //                 opening_balance: openingBalance,
        //                 _token: '{{ csrf_token() }}'
        //             },
        //             success: function(response) {
        //                 showToast('Owner Capital updated successfully', 'success');
        //                 $('#opening_balance')
        //                     .val(response.updated_balance)
        //                     .prop('readonly', true);

        //                 $btn
        //                     .data('mode', 'reset')
        //                     .removeClass('btn-success')
        //                     .addClass('btn-primary')
        //                     .html('<i class="fas fa-undo-alt mr-1"></i> Reset to Update Again');

        //                 $('#updateNote').fadeIn();
        //             },
        //             error: function(xhr, status, error) {
        //                 showToast('Error: ' + error, 'error');
        //             },
        //             complete: function() {
        //                 $('#opening_balance').prop('disabled', false);
        //                 $btn.prop('disabled', false);
        //             }
        //         });
        //     }
        // });

        $(document).ready(function() {
            $('.account_select_for_withdraw').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const balance = parseFloat(selectedOption.data('balance') ?? 0);
                $('#currentBalance_account').val(balance.toFixed(2));
            });
            $('#withdrawFundAmount').on('input', function() {
                const currentBalance = parseFloat($('#currentBalance_account').val() ?? 0);
                const withdrawAmount = parseFloat($(this).val() ?? 0);

                if (withdrawAmount > currentBalance) {
                    $(this).addClass('is-invalid');
                    $('#withdrawError').removeClass('d-none');
                } else {
                    $(this).removeClass('is-invalid');
                    $('#withdrawError').addClass('d-none');
                }
            });

        });
    </script>
@endpush
