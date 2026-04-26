$(document).ready(function () {
    $('#citySelect').on('change', function () {
        var cityName = $(this).val();

        if (cityName) {
            $.ajax({
                url: "/get-thana",
                type: "GET",
                data: {
                    cityName: cityName
                },
                success: function (response) {
                    // Clear and enable the thana select dropdown
                    $('#thanaSelect')
                        .prop('disabled', false)
                        .html('<option value="">Select Thana</option>');

                    if (response && response.length > 0) {
                        // Build options HTML string for better performance
                        const optionsHtml = response.map(item =>
                            `<option value="${item.name}">${item.name} ${item.bn_name ? '( ' + item.bn_name + ' )' : ''}</option>`
                        ).join('');

                        // Add all options at once
                        $('#thanaSelect').append(optionsHtml);
                    } else {
                        // If no thanas found, disable the select and show message
                        $('#thanaSelect')
                            .html('<option value="">No Thana Found</option>')
                            .prop('disabled', true);
                    }
                },
                beforeSend: function () {
                    $('#thanaSelect').prop('disabled', true).html(
                        '<option value="">Loading...</option>');
                },
                error: function () {
                    $('#thanaSelect').html(
                        '<option value="">Error loading data</option>').prop(
                            'disabled', true);
                }

            });
        } else {
            $('#thanaSelect').html('<option value="">Select Thana</option>');
        }
    });

    $('input[name="social_type"]').on('change', function () {
        const selected = $(this).val();

        // Clear the previous value in the input field
        let inputValue = $('#socialIdInput input').val();

        // Show the input field
        $('#socialIdInput').removeClass('d-none');

        // Change label and placeholder based on the selected social type
        if (selected === 'whatsapp') {
            $('#socialIdLabel').text('WhatsApp Number');
            if (inputValue == '') {
                $('#socialIdInput input').attr('placeholder', 'Enter WhatsApp Number');
                $('#socialIdInput input').attr('type', 'number');
            }
        } else if (selected === 'facebook') {
            $('#socialIdLabel').text('Facebook ID');
            if (inputValue == '') {
                $('#socialIdInput input').attr('placeholder', 'Enter Facebook Short Url');
                $('#socialIdInput input').attr('type', 'text'); // Change to text type for Facebook
            }
        }
    });
});