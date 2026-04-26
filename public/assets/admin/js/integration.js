$(document).ready(function () {
    $('.copy-btn').on('click', function () {
        const $input = $(this).siblings('input');
        $input.prop('readonly', false).select();
        document.execCommand('copy');
        $input.prop('readonly', true);
        const $iconDefault = $(this).find('.icon-default');
        const $iconSuccess = $(this).find('.icon-success');
        $iconDefault.addClass('d-none');
        $iconSuccess.removeClass('d-none');

        setTimeout(() => {
            $iconSuccess.addClass('d-none');
            $iconDefault.removeClass('d-none');
        }, 1500);
    });

    $('.btn-light').on('click', function () {
        const $input = $(this).closest('.mb-4').find('.api-key-input');
        const newToken = generateRandomToken();
        $input.val(newToken);
    });

    function generateRandomToken() {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let token = 'sk-';
        for (let i = 0; i < 48; i++) {
            token += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return token;
    }
});

$(document).ready(function () {
    $('.toggle-status').on('change', function () {
        const targetId = $(this).data('target');
        $('#' + targetId).text(this.checked ? 'ON' : 'OFF');
    });
});

/**
 * Carrybee integration modal — multiple accounts (clone row + reindex).
 * Handlers are bound on #carrybeeModal so they always match this page’s modal DOM.
 */
function carrybeeNextRowIndex($container) {
    var maxIdx = -1;
    $container.find('.carrybee-account-row').each(function () {
        $(this)
            .find('input[name^="carrybee_accounts"]')
            .each(function () {
                var m = (($(this).attr('name') || '').match(/^carrybee_accounts\[(\d+)\]/) || [])[1];
                if (m !== undefined) {
                    maxIdx = Math.max(maxIdx, parseInt(m, 10));
                }
            });
    });
    return maxIdx + 1;
}

function carrybeeWireNewRow($clone, idx, accountLabel) {
    $clone.find('[name]').each(function () {
        var name = $(this).attr('name');
        if (name && name.indexOf('carrybee_accounts[') === 0) {
            $(this).attr('name', name.replace(/carrybee_accounts\[\d+\]/, 'carrybee_accounts[' + idx + ']'));
        }
    });

    $clone.find('input[name$="[id]"]').val('');
    $clone.find('input[type="text"], input[type="url"]').val('');
    $clone.find('input.carrybee-client-secret-input[name$="[client_secret]"]').val('');

    var label = accountLabel || 'Account';
    $clone.find('h6').first().text(label + ' #' + (idx + 1));
}

jQuery(function ($) {
    var $modal = $('#carrybeeModal');
    if (!$modal.length) {
        return;
    }

    $modal.on('click', '#carrybee-add-row', function (e) {
        e.preventDefault();
        var $container = $modal.find('#carrybee-accounts-container');
        if (!$container.length) {
            return;
        }

        var $cols = $container.find('.carrybee-account-col');
        if (!$cols.length) {
            return;
        }

        var idx = carrybeeNextRowIndex($container);
        var $clone = $cols.first().clone(false, false);
        var accountLabel = $container.attr('data-account-label') || 'Account';

        carrybeeWireNewRow($clone, idx, accountLabel);
        $container.append($clone);
        $container.attr('data-next-index', String(idx + 1));
    });

    $modal.on('click', '.carrybee-remove-row', function (e) {
        e.preventDefault();
        var $container = $modal.find('#carrybee-accounts-container');
        var $cols = $container.find('.carrybee-account-col');

        if ($cols.length <= 1) {
            var $only = $cols.first();
            $only.find('input[name$="[id]"]').val('');
            $only.find('input[type="text"], input[type="url"]').val('');
            $only.find('input.carrybee-client-secret-input[name$="[client_secret]"]').val('');
            return;
        }

        $(this).closest('.carrybee-account-col').remove();
    });

    function carrybeeRandomWebhookSecret() {
        var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var out = '';
        for (var i = 0; i < 64; i++) {
            out += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return out;
    }

    $modal.on('click', '#carrybee-regenerate-webhook', function (e) {
        e.preventDefault();
        var $inp = $modal.find('#carrybee-webhook-secret-input');
        if (!$inp.length) {
            return;
        }
        $inp.prop('readonly', false).val(carrybeeRandomWebhookSecret()).prop('readonly', true);
    });
});