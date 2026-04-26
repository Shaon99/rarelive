"use strict";
function newDataAppend(data) {
    // Check if product already exists in table
    let productExists = false;
    $('.product-id').each(function() {
        if ($(this).val() == data.id) {
            productExists = true;
            showToast('Product already added');
            return false; // Break the loop
        }
    });

    if (productExists) {
        return; // Exit function if product already exists
    }

    var newRow = $("<tr>");
    var cols = '';
    cols += '<td class="col-sm-2 product-title"><span >' + data.name +
        '</span></td>';
    cols +=
        '<td class="col-sm-3"><div class="input-group"><span class="input-group-btn"><button type="button" class="btn btn-default btn-xs minus"><span><i class="fas fa-minus-circle"></i></span></button></span><input type="text"  name="qty[]" class="text-center form-control qty numkey input-number rounded" id="qty-val" value="1" min="1" step="any" required><span class="input-group-btn"><button type="button" data-id="' +
        data.id +
        '" class="btn btn-default btn-xs plus"><span><i class="fas fa-plus-circle"></i></span></button></span></div></td>';
    var purchasePrice = data.purchase_price ? data.purchase_price : 0;
    var salePrice = data.sale_price ? data.sale_price : 0;

    cols += '<td class="col-sm-2 "><input class="form-control product-price" name="current_net_unit_price[]" value="' + purchasePrice + '"></td>';
    cols += '<td class="col-sm-2 "><input class="form-control sale-price" name="current_sales_unit_price[]" value="' + salePrice + '"></td>';

    cols += '<td class="col-sm-2 sub-total">' + purchasePrice + '</td>';

    cols +=
        '<td class="col-sm-1"><button type="button" class="ibtnDel btn btn-danger btn-xs"><span><i class="fas fa-times"></i></span></button></td>';
    cols += '<input type="hidden" class="product-id" name="product_id[]" value="' +
        data.id + '"/>';
    cols += '<input type="hidden" class="net_unit_price" name="previous_net_unit_price[]" value="' + purchasePrice + '" />';
    cols += '<input type="hidden" class="net_unit_price" name="previous_sales_unit_price[]" value="' + salePrice + '" />';

    cols += '<input type="hidden" class="productqty" value="' + data.quantity + '" />';
    newRow.append(cols);
    $("table.order-list tbody").append(newRow);
    $('#product').val('').trigger('change');

    if (isSoundOn) {
        sound.play().catch(error => console.error("Sound playback failed:", error));
    }
}

$("table.order-list tbody").on("click", ".ibtnDel", function (event) {
    $(this).closest("tr").remove();
    $('#product').val('').trigger('change');
    $('#discount').val(0);
    $('#shipping').val(0);
    $('#payment').val(0);

    if (isSoundOn) {
        sound.play().catch(error => console.error("Sound playback failed:", error));
    }

    calculateTotal();
});


$("#invoice").on('click', '.plus', function () {
    var rowindex = $(this).closest('tr').index();
    var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) + 1;

    var total = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-price')
        .val() * qty);
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .sub-total').text(total);

    if (isSoundOn) {
        sound.play().catch(error => console.error("Sound playback failed:", error));
    }

    calculateTotal();
});


$("#invoice").on('keyup', '#qty-val', function () {
    var rowindex = $(this).closest('tr').index();
    var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val());
    var total = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-price')
        .val() * qty);
    if (qty > 0) {
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .sub-total').text(total);
    }
    calculateTotal();
});
$("#invoice").on('keyup', '.product-price', function () {
    var rowindex = $(this).closest('tr').index();
    var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val());
    var total = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-price')
        .val() * qty);

    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .sub-total').text(total);


    calculateTotal();

});

$("#invoice").on('click', '.minus', function () {
    var rowindex = $(this).closest('tr').index();
    var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) - 1;
    var price = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-price')
        .val());
    var total = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .sub-total')
        .text());

    if (qty > 0) {
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .sub-total').text(total - price);
    }
    calculateTotal();

    if (isSoundOn) {
        sound.play().catch(error => console.error("Sound playback failed:", error));
    }

});


function calculateQuantity() {
    var total_ordered = 0;
    $(".qty").each(function () {

        var value = parseFloat($(this).val());

        total_ordered = total_ordered + value;
    });
    $('#total_item').html(total_ordered);
    $('#total_items').html(total_ordered);

    $('#total_qty').val(total_ordered);

};

function calculateSubtotal() {
    var total = 0;
    $(".sub-total").each(function () {
        total += parseFloat($(this).text());
    });
    $('#total_sub').text(total.toFixed());
    $('#total_value').text(total.toFixed());

    $('#sub_totals').val(total);
};



// Call discount calculation when typing in discount input
$(document).on('keyup change', '#discount', function () {
    calculateTotal();
});

// Call discount calculation when changing the discount type
$(document).on('change', '#discount_type', function () {
    calculateTotal();
});

$(document).on('keyup', '#shipping', function () {
    calculateTotal();
});

$(document).on('keyup', '#payment', function () {
    if ($(this).val() > parseFloat($('#grand_total').text())) {
        alert('Paying amount cannot be bigger than grand total');
        $(this).val('');
    }
    var duepayment = $('.grand_total').val() - $('#payment').val();

    $('#duepayment').val(duepayment);
    $('#dueamount').html(duepayment);
});


function calculateDiscount() {
    var discount = parseFloat($('#discount').val()) || 0;
    var discountType = $('#discount_type').val();
    var subtotal = parseFloat($('#total_sub').html()) || 0;
    var totalDiscount = 0;

    if (discountType === 'percentage') {
        totalDiscount = (subtotal * discount) / 100;
    } else {
        totalDiscount = discount;
    }

    $('#total_discount').html(totalDiscount.toFixed(2));

    return totalDiscount;
}


function calculateShipping() {
    var shipping = $('#shipping').val();
    $('#total_shipping').html(shipping);
}

function calculateGrandTotal() {
    var grandtotal = parseFloat($('#total_value').html()) - parseFloat($('#total_discount').html()) + parseFloat($('#total_shipping').html());
    $('#grand_total').html(grandtotal);
    $('.grand_total').val(grandtotal);
}

function calculateDue() {
    var duepayment = $('.grand_total').val() - $('#payment').val();

    $('#duepayment').val(duepayment);
    $('#dueamount').html(duepayment);
}

function calculateTotal() {
    calculateQuantity();
    calculateSubtotal();
    calculateDiscount();
    calculateShipping();
    calculateGrandTotal();
    calculateDue();
}