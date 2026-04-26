<script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/proper.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/nicescroll.min.js') }}"></script>
<script src="{{ asset('assets/admin/modules/jquery-selectric/jquery.selectric.min.js') }}"></script>
<script src="{{ asset('assets/admin/modules/upload-preview/assets/js/jquery.uploadPreview.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/scripts.js') }}"></script>
<script src="{{ asset('assets/admin/js/datatables.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/daterangepicker.js') }}"></script>
<script src="{{ asset('assets/admin/js/cloudinary.js') }}"></script>


@if (Session::has('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast("{{ session('success') }}", 'success');
        });
    </script>
@endif


@if (Session::has('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast("{{ session('error') }}", 'error');
        });
    </script>
@endif

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast("{{ $error }}", 'error');
            });
        </script>
    @endforeach
@endif

<script>
    "use strict";

    $(document).ready(function() {
        //create
        var Curl = null;

        $('.create').on('click', function() {
            const modal = $('#create');
            const name = $(this).data('name');
            $("#name").val('');
            Curl = $(this).data('href');
            $('.name').text(name);
            $('#error-container').empty();
            modal.modal('show');
        });

        $(document).on('submit', '#createFrom', function(e) {
            e.preventDefault();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: 'POST',
                url: Curl,
                data: new FormData(this),
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".btnLoad").addClass('btn-progress');
                },
                success: function(data) {
                    clearFormFields();
                    closeCreateModal();
                    showToast('Created successfully', 'success');
                    if (shouldReloadPage()) {
                        location.reload();
                    }
                    appendOptionIfDataExists(data.warehouse, 'warehouse');
                    appendOptionIfDataExists(data.unit, 'unit');
                    appendOptionIfDataExists(data.category, 'category');
                    appendOptionIfDataExists(data.brand, 'brand');
                    appendOptionIfDataExists(data.expenseCategory, 'expenseCategories');
                },
                complete: function() {
                    $(".btnLoad").removeClass('btn-progress');
                },
                error: function(xhr) {
                    handleAjaxErrors(xhr);
                }
            });
        });

        function clearFormFields() {
            $('#name').val('');
        }

        function closeCreateModal() {
            $('.closeButton').click();
        }

        function shouldReloadPage() {
            const url = window.location.href;
            return url.includes("/warehouse") || url.includes("/brand") || url.includes("/unit") || url
                .includes("/category") || url.includes("/expense-category");
        }

        function appendOptionIfDataExists(data, selectName) {
            if (data) {
                const selectElement = $(`select[name="${selectName}"]`);
                selectElement.append(
                    `<option value="${data.id}" selected>${data.name}</option>`
                );
            }
        }

        function handleAjaxErrors(xhr) {
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;
                $('#error-container').empty();
                $.each(errors, function(key, value) {
                    $('#error-container').text(value[0]);
                });
            } else {
                console.error('Error:', xhr);
            }
        }

        //create end
        $('.edit').on('click', function() {
            const modal = $('#edit');
            const name = $(this).data('name');
            const item = $(this).data('item');
            modal.find('form').attr('action', $(this).data('href'));
            modal.find('input[name=name]').val(item);
            $('.name').text(name);
            modal.modal('show');
        });

        $('.due').on('click', function() {
            const modal = $('#due');
            const name = $(this).data('name');
            const due = $(this).data('due');

            modal.find('form').attr('action', $(this).data('href'));
            modal.find('input[name=due_amount]').val(due);
            $('.name').text(name);
            modal.modal('show');
        });

        $(document).on("click", ".full-paid-btn", function() {
            let dueAmount = $('.due_amouunt').val();
            $(".payment_amount").val(dueAmount);
        });

        $('.payment_amount').on('keyup', function() {
            let due = parseInt($('.due_amouunt').val());
            if (due < parseInt($(this).val())) {
                $('.error-due').text('payment can not be bigger than due payment');
                $(this).val('')
                return false
            } else {
                $('.error-due').text('');
            }
        });

        $(document).on('click', '.deleteforever', function(e) {

            const modal = $('#deleteforever');

            modal.find('form').attr('action', $(this).data('href'));

            modal.modal('show');
        });

        $(document).on('click', '#trackBtn', function(e) {
            const modal = $('#trackingModal');
            modal.modal('show');
        });

        $(document).on('click', '.delete', function(e) {

            const modal = $('#delete');

            modal.find('form').attr('action', $(this).data('href'));

            modal.modal('show');
        });

        $(document).on('click', '.daily-report', function(e) {
            const modal = $('#daily-report');
            // Get the current date in Dhaka time zone
            const today = new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Dhaka',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
            }).format(new Date());

            fetchDailyReport(today, today);

            // Fetch cash flow with today's date as both start and end date
            fetchDailyReport();

            modal.modal('show');
        });

        function fetchDailyReport() {
            $.ajax({
                url: '{{ route('admin.daily.report') }}',
                type: 'GET',
                beforeSend: function() {
                    $('#load').show();
                },
                success: function(response) {
                    if (response.error) {
                        $('#daily_report').html(`<p class="text-danger">${response.error}</p>`);
                        return;
                    }

                    $('#title-daily-report').text(
                        `{{ $general->sitename }} Daily Report Summary (${response.date})`
                    );

                    let reportHtml = `
                    <table class="table table-bordered">                    
                        <tr>
                            <th>Today's Sales</th>
                            <td>${response.today_sales}</td>
                        </tr>
                        <tr>
                            <th>Today's Expense</th>
                            <td>${response.today_expense}</td>
                        </tr>
                        <tr>
                            <th>Today's Balance</th>
                            <td>${response.today_balance}</td>
                        </tr>
                        <tr>
                            <th>Previous Balance</th>
                            <td>${response.previous_balance}</td>
                        </tr>
                        <tr>
                            <th>Cash in Hand</th>
                            <td><strong>${response.balance}</strong></td>
                        </tr>
                    </table>`;

                    $('#daily_report').html(reportHtml);
                },
                complete: function() {
                    $('#load').hide();
                },
                error: function(xhr) {
                    $('#daily_report').html(
                        '<p class="text-danger">Failed to load data. Please try again.</p>');
                    console.error('Error fetching daily report:', xhr.responseText);
                }
            });
        }

        $(document).on('click', '.recent-order', function(e) {
            const modal = $('#recent-order');
            fetchRecentOrder();
            modal.modal('show');
        });


        function fetchRecentOrder() {
            $.ajax({
                url: '{{ route('admin.recentSales') }}',
                type: 'GET',
                beforeSend: function() {
                    $('#load-order').show();
                },
                success: function(response) {
                    if (response.error) {
                        $('#view-recent-sales').html(
                            `<p class="text-danger">${response.error}</p>`);
                        return;
                    }
                    $('#view-recent-sales').html(response.view);
                },
                complete: function() {
                    $('#load-order').hide();
                },
                error: function(xhr) {
                    $('#view-recent-sales').html(
                        '<p class="text-danger">Failed to load data. Please try again.</p>');
                    console.error('Error fetching daily report:', xhr.responseText);
                }
            });
        }


        $(".language-dropdown").on("click", function(e) {
            e.preventDefault();
            var selectedLang = $(this).data("lang");
            $("#languageDropdown").text(selectedLang.toUpperCase());
            $(".language-dropdown").removeClass("active");
            $(this).addClass("active");
            var url = "{{ route('admin.changeLang') }}";
            window.location.href = url + "?lang=" +
                selectedLang;
        });

        $('#table_1').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            pagingType: "numbers",
            language: {
                lengthMenu: "_MENU_",
                search: "",
                searchPlaceholder: "Search records ...",
            },
        });

        // transfer_quantity
        $('.transfer').on('click', function() {
            const modal = $('#transfer');
            const name = $(this).data('name');
            const current_quantity = $(this).data('quantity');
            const current_price = $(this).data('current-price');
            const stock_quantity = $(this).data('stock-quantity');
            const stock_id = $(this).data('stock-id');
            const cp = $(this).data('current-purchases');
            modal.find('form').attr('action', $(this).data('href'));
            modal.find('input[name=sales_quantity]').val(current_quantity);
            modal.find('input[name=stock_quantity]').val(stock_quantity);
            modal.find('input[name=current_saleprice]').val(current_price);
            modal.find('input[name=current_purchases]').val(cp);
            modal.find('input[name=stock_id]').val(stock_id);
            $('.name').text(name);
            modal.modal('show');
        });

        $('.transfer_quantity').on('keyup', function() {
            const available = parseInt($('.stock_quantity').val());
            if (available < parseInt($(this).val())) {
                $('.error-quantity').text('transfer quantity can not be bigger than stock quantity');
                $(this).val('')
                return false
            } else {
                $('.error-quantity').text('');
            }
        });

        //search function   
        document.getElementById("searchBtn").addEventListener("click", function() {
            $("#searchModal").modal("show");
        });

        // Open modal when pressing "Ctrl + K" or "Command + K"
        document.addEventListener("keydown", function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
                event.preventDefault();
                $('#searchModal').modal('show');
            }
        });

        // Filter suggestions based on search input
        const searchInput = document.getElementById("searchInput");
        const suggestions = document.querySelectorAll(".search-suggestion-item");

        searchInput.addEventListener("input", function() {
            const query = searchInput.value.toLowerCase();
            suggestions.forEach((item) => {
                const text = item.innerText.toLowerCase();
                item.style.display = text.includes(query) ? "flex" : "none";
            });
        });

        window.addEventListener('show-delete-modal', function(event) {
            const name = event.detail.name;
            $('#deleteLiveWire').modal('show');
            $('#headingLiveWire').text(name ?? '');
        });

        window.addEventListener('close-delete-modal', function() {
            $('#deleteLiveWire').modal('hide');
        });

        const $selectAllCheckbox = $('#selectAll');
        const $fileCheckboxes = $('.fileCheckbox');
        const $deleteSelectedButton = $('#deleteSelected');
        const $deleteMultipleForm = $('#deleteMultipleForm');
        const $confirmModal = $('#confirmDeleteMultipleModal');
        const $confirmDeleteButton = $('#confirmMultipleDelete');

        // Select/Deselect All
        $selectAllCheckbox.on('change', function() {
            const isChecked = $(this).is(':checked');
            $fileCheckboxes.prop('checked', isChecked);
            toggleDeleteButton();
        });

        // Enable/Disable Delete Button
        $fileCheckboxes.on('change', function() {
            toggleDeleteButton();
        });

        // Show Confirmation Modal
        $deleteSelectedButton.on('click', function(e) {
            e.preventDefault();
            if ($fileCheckboxes.is(':checked')) {
                $confirmModal.modal('show');
            }
        });

        // Confirm Deletion
        $confirmDeleteButton.on('click', function() {
            $deleteMultipleForm.submit();
        });

        // Toggle the delete button state based on checkbox selection
        function toggleDeleteButton() {
            const anyChecked = $fileCheckboxes.is(':checked');
            $deleteSelectedButton.prop('disabled', !anyChecked);
            if (anyChecked) {
                $deleteSelectedButton.removeClass('d-none');
            } else {
                $deleteSelectedButton.addClass('d-none');
            }
        }

        // After AJAX filter or content reload, rebind the checkboxes functionality
        function rebindCheckboxes() {
            $selectAllCheckbox.off('change').on('change', function() {
                const isChecked = $(this).is(':checked');
                $fileCheckboxes.prop('checked', isChecked);
                toggleDeleteButton();
            });

            // Rebind the individual checkbox functionality
            $fileCheckboxes.off('change').on('change', function() {
                toggleDeleteButton();
            });

            // Re-enable the delete button state check
            toggleDeleteButton();
        }
    });

    function showToast(message, type = 'success', duration = 5000) {
        // Create icon based on type
        let icon = type === 'success' ?
            `<x-heroicon-o-check-badge class="h-20 w-20 text-success" />` :
            `<x-heroicon-o-shield-exclamation class="h-20 w-20 text-danger" />`;
        // Create toast element
        let toast = $(`
            <div class="custom-toast">
            <div class="toast-content">
                <div class="toast-icon">
                ${icon}
                </div>
                <p class="toast-message">${message}</p>
            </div>
            <div class="shadow-slide"></div>
            </div>
        `);

        // Append toast to container
        $('#toastContainer').append(toast);

        // Fade in and slide up effect
        setTimeout(() => {
            toast.css({
                'opacity': '1',
                'transform': 'translateY(0)'
            });
        }, 100);

        // Customize shadow animation duration to match toast duration
        const shadowElement = toast.find('.shadow-slide');
        shadowElement.css('animation-duration', `${duration - 500}ms`);

        // Set timeout for fade out and removal
        let removeTimeout = setTimeout(() => {
            fadeOutAndRemoveToast(toast);
        }, duration);

        // Pause removal on hover
        toast.hover(
            () => clearTimeout(removeTimeout),
            () => {
                removeTimeout = setTimeout(() => {
                    fadeOutAndRemoveToast(toast);
                }, 1000); // Delay before removal resumes
            }
        );
    }

    function fadeOutAndRemoveToast(toast) {
        toast.css({
            'opacity': '0',
            'transform': 'translateY(10px)'
        });
        setTimeout(() => toast.remove(), 300);
    }

    $(document).on('click', '.editAddress', function(e) {

        const modal = $('#editAddressModal');


        modal.find('form').attr('action', $(this).data('href'));

        modal.modal('show');
    });

    $(document).ready(function() {
        $('#editAddressModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var city = button.data('city');
            var thana = button.data('thana');
            var address = button.data('address');
            var zone = button.data('zone');
            var cityUrl = "/assets/address.json";

            var modal = $(this);
            modal.find('#editCity').val(city);
            modal.find('#editThana').val(thana);
            modal.find('#editZone').val(zone);
            modal.find('#editAddress').val(address);

            fetchCities(cityUrl, city, thana);

            $('#citySelectModal').select2({
                dropdownParent: $('#editAddressModal')
            });
            $('#thanaSelectModal').select2({
                dropdownParent: $('#editAddressModal')
            });
        });

        // Fetch and Populate Cities
        function fetchCities(url, selectedCity, selectedThana) {
            $.ajax({
                url: url,
                method: "GET",
                dataType: "json",
                success: function(response) {
                    var citySelect = $('#citySelectModal');
                    var thanaSelect = $('#thanaSelectModal');

                    citySelect.empty();
                    thanaSelect.empty().prop('disabled', true);

                    citySelect.append('<option value="" disabled>Select City</option>');

                    if (response.district || response.districts) {
                        const districts = response.district || response.districts;

                        $.each(districts, function(index, district) {
                            var option = $('<option/>', {
                                value: district.name,
                                text: district.name + (district.bn_name ? ' (' +
                                    district.bn_name + ')' : '')
                            });
                            citySelect.append(option);
                        });

                        if (selectedCity) {
                            citySelect.val(selectedCity).trigger('change');
                            fetchThanas(selectedCity, selectedThana);
                        }
                    } else {
                        console.error('Invalid JSON structure:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching cities:', error);
                }
            });
        }

        // Fetch and Populate Thanas
        function fetchThanas(city, selectedThana) {
            $.ajax({
                url: '/get-thana',
                type: 'GET',
                data: {
                    cityName: city
                },
                success: function(response) {
                    var thanaSelect = $('#thanaSelectModal');
                    thanaSelect.prop('disabled', false).html(
                        '<option value="">Select Thana</option>');

                    if (response && response.length > 0) {
                        const optionsHtml = response.map(item =>
                            `<option value="${item.name}" ${selectedThana === item.name ? 'selected' : ''}>
                            ${item.name} ${item.bn_name ? '( ' + item.bn_name + ' )' : ''}
                        </option>`
                        ).join('');

                        thanaSelect.append(optionsHtml).trigger('change');
                    } else {
                        thanaSelect.html('<option value="">No Thana Found</option>').prop(
                            'disabled', true);
                    }
                },
                beforeSend: function() {
                    $('#thanaSelectModal').prop('disabled', true).html(
                        '<option value="">Loading...</option>');
                },
                error: function() {
                    $('#thanaSelectModal').html('<option value="">Error loading data</option>')
                        .prop('disabled', true);
                }
            });
        }

        // Optional: If you want to load thanas when the city changes manually
        $('#citySelectModal').on('change', function() {
            var selectedCity = $(this).val();
            fetchThanas(selectedCity, null);
        });
    });

    $(document).ready(function() {
        $(document).on('click', '#checkStatusBtn', function(e) {
            var consignmentId = $('#cid').val().trim();

            if (consignmentId === '') {
                $('.error-show').text("Please enter a valid Consignment ID.");
                return;
            } else {
                $('.error-show').text('');
            }

            var $button = $(this);
            $button.prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.sales.checkSteadfastStatus') }}",
                type: "POST",
                data: {
                    consignment_id: consignmentId,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    $('#trackingResult').html('<p>Checking...</p>');
                },
                success: function(response) {
                    if (response.status === 'success') {
                        var formattedStatus = formatStatus(response.tracking_status);
                        $('#trackingResult').html(
                            `<p><strong>Status:</strong> <span class="badge badge-success">${formattedStatus}</span></p>`
                        );
                    } else {
                        $('#trackingResult').html('<p class="text-danger">' + response
                            .message + '</p>');
                    }
                },
                error: function(xhr) {
                    $('#trackingResult').html('<p class="text-danger">' + xhr.responseJSON
                        .message + '</p>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });

        // Clear modal input and error message when modal is closed
        $('#trackingModal').on('hidden.bs.modal', function() {
            // Clear the input field
            $('#cid').val('');

            // Clear the error message
            $('.error-show').text('');

            // Clear the result
            $('#trackingResult').html('');
        });


        function formatStatus(status) {
            return status.replace(/_/g, ' ')
                .toUpperCase();
        }
    });

    document.querySelectorAll('.toggle-password').forEach(item => {
        item.addEventListener('click', function() {
            let target = this.getAttribute('data-target');
            let passwordField = document.querySelector(`input[name="${target}"]`);
            let icon = this.querySelector('i');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn();
        } else {
            $('.back-to-top').fadeOut();
        }
    });

    // Scroll to top on click
    $('.back-to-top').click(function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, 600);
        return false;
    });

    $('#generate-ai-btn').on('click', function() {
        const $prompt = $('#ai_prompt');
        const $errorText = $('.error-text');
        const $btn = $(this);

        // Validate prompt
        if (!$prompt.val().trim()) {
            $errorText.text('Please enter a prompt');
            return;
        }
        $errorText.text('');

        // Show loading state
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Thinking...').prop('disabled', true);
        $errorText.text('Optimo is processing your request...').removeClass('text-danger').addClass(
            'text-primary');

        // Create form data
        const formData = new FormData();
        formData.append('prompt', $prompt.val().trim());

        $.ajax({
            url: '/generate-description-ai',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response && response.description) {
                    // Set the generated description content
                    tinymce.get('description').setContent(response.description);

                    // Clear any error messages and prompt
                    $errorText.text('');
                    $prompt.val('');
                } else {
                    // Handle empty or invalid response
                    tinymce.get('description').setContent('<p>No description returned.</p>');
                    $errorText.text('Invalid response received').removeClass('text-info').addClass(
                        'text-danger');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                $errorText.text('Failed to generate description. Please try again.').removeClass(
                    'text-info').addClass('text-danger');
                $prompt.val('')
            },
            complete: function() {
                $btn.html('<i class="fas fa-robot me-2"></i> Generate with Optimo').prop('disabled',
                    false);
                $prompt.val('')
            }
        });
    });

    $(document).ready(function() {
        $('#mark-all-read').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('admin.notifications.markAllAsRead') }}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Remove badge on success
                    $('.navbar-badge').text('0').hide();

                    // Optionally: update UI to remove unread highlighting
                    $('.dropdown-item-unread').removeClass('dropdown-item-unread');
                },
                error: function() {
                    alert('Failed to mark notifications as read.');
                }
            });
        });
    });
</script>
