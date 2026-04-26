"use strict";
$(".customer-create").on("click", function () {
  $(".errorphone").text("");
  $(".errorname").text("");
  $(".erroraddress").text("");
  const modal = $("#customer-create");
  modal.modal("show");
});

//append--func
function newDataAppend(data, type, branchQuantity) {
  var tableBody = $("table.order-list tbody");
  var rowCount = tableBody.find("tr").length + 1;

  var newRow = $("<tr>");
  var cols = "";
  cols +=
    '<td class="product-title"><span class="product-name text-capitalize">' +
    rowCount + '. ' + data.name +
    "</span></td>";

  cols +=
    '<td><input class="product-price" name="sale_price[]" value="' +
    parseFloat((type == "combo" ? data.price : data.sale_price)).toFixed(2) +
    '" /></td>';
  cols +=
    '<td><input class="discount-price" name="discount_price[]" value="' +
    (data.discount ? parseFloat(data.discount).toFixed(2) : '0.00') +
    '" /><span class="discount-type-indicator">' +
    (data.discount_type === 'percentage' ? ' %' : ' Fixed') +
    '</span></td>';
  cols +=
    '<td><span><button type="button" class="minus"><span><i class="fas fa-minus-circle text-primary"></i></span></button></span>' +
    '<input type="text" name="qty[]" class="qty numkey input-number" value="1" min="1" step="any" required>' +
    '<span><button type="button" data-id="' +
    data.id +
    '" class="plus"><span><i class="fas fa-plus-circle text-primary"></i></span></button></span></td>';

  cols +=
    '<td class="sub-total">' +
    parseFloat((type == "combo" ? data.price :
      data.discount_type === 'percentage' ?
        data.sale_price - (data.sale_price * (data.discount / 100)) :
        data.sale_price - data.discount
    )).toFixed(2) +
    "</td>";

  cols +=
    '<td class="text-center"><a type="button" class="ibtnDel text-center"><i class="fas fa-xmark-circle text-danger text-end"></i></a></td>';

  cols +=
    '<input type="hidden" class="product-id" name="product_id[]" value="' +
    data.id +
    '"/>';

  cols +=
    '<input type="hidden" class="net_unit_price" name="net_unit_price[]" value="' +
    (type == "combo" ? data.price : data.sale_price) +
    '" />';

  cols +=
    '<td><input class="combo" type="hidden" name="type[]" value="' +
    type +
    '" /></td>';

  cols +=
    '<td><input type="hidden" class="productqty" value="' +
    branchQuantity +
    '" /></td>';
  cols +=
    '<td><input type="hidden" class="discount_type" name="discount_type[]" value="' +
    data.discount_type +
    '" /></td>';

  newRow.append(cols);
  tableBody.append(newRow);

  // Play sound if enabled
  if (isSoundOn) {
    sound.play().catch((error) => console.error("Sound playback failed:", error));
  }

  // Update serial numbers after adding a new row
  updateSerialNumbers();
}

// Function to update serial numbers dynamically
function updateSerialNumbers() {
  $("table.order-list tbody tr").each(function (index) {
    $(this).find(".serial-number").text(index + 1);
  });
}

//Remove--button
$("table.order-list tbody").on("click", ".ibtnDel", function (event) {
  $(this)
    .closest("tr")
    .fadeOut(300, function () {
      $(this).closest("tr").remove();
      calculateTotal();
    });
  if (isSoundOn) {
    sound.play().catch(error => console.error("Sound playback failed:", error));
  }
});

// onkeyup
$("#sale").on("keyup", ".product-price", function () {
  var rowindex = $(this).closest("tr").index();
  var qty = parseFloat(
    $("table.order-list tbody tr:nth-child(" + (rowindex + 1) + ") .qty").val()
  );
  var price = parseFloat($(this).val());
  var discountType = $(
    "table.order-list tbody tr:nth-child(" + (rowindex + 1) + ") .discount_type"
  ).val();
  var discountValue = parseFloat(
    $("table.order-list tbody tr:nth-child(" + (rowindex + 1) + ") .discount-price").val()
  ) || 0;

  var discount = discountType === 'percentage' ? (price * (discountValue / 100)) : discountValue;
  var total = (price - discount) * qty;

  if (qty > 0) {
    $("table.order-list tbody tr:nth-child(" + (rowindex + 1) + ") .qty").val(qty);
    $("table.order-list tbody tr:nth-child(" + (rowindex + 1) + ") .sub-total").text(total.toFixed(2));
  }

  calculateTotal();
});

$("#sale").on("keyup", ".discount-price", function () {
  var rowindex = $(this).closest("tr").index();
  var price = parseFloat(
    $("table.order-list tbody tr:nth-child(" + (rowindex + 1) + ") .product-price").val()
  );
  var discountType = $(
    "table.order-list tbody tr:nth-child(" + (rowindex + 1) + ") .discount_type"
  ).val();
  var discountValue = parseFloat($(this).val()) || 0;
  var qty = parseFloat(
    $("table.order-list tbody tr:nth-child(" + (rowindex + 1) + ") .qty").val()
  );

  var rowTotal = price * qty;

  var discount = 0;
  if (discountType === 'percentage') {
    discount = (rowTotal * discountValue) / 100;
  } else {
    discount = discountValue;
  }

  var total = rowTotal - discount;

  $("table.order-list tbody tr:nth-child(" + (rowindex + 1) + ") .sub-total").text(total.toFixed(2));
  calculateTotal();
});

function calculateRowTotal(rowindex) {
  const row = $("table.order-list tbody tr").eq(rowindex);
  const price = parseFloat(row.find(".product-price").val()) || 0;
  const qty = parseFloat(row.find(".qty").val()) || 1;
  const discountType = row.find(".discount_type").val();
  const discountValue = parseFloat(row.find(".discount-price").val()) || 0;

  const rowTotal = price * qty;
  const discount = (discountType === 'percentage')
    ? (rowTotal * discountValue) / 100
    : discountValue;

  const total = rowTotal - discount;
  row.find(".sub-total").text(total.toFixed(2));
}

$("#sale").on("click", ".plus", function () {
  if (isSoundOn) sound.play().catch(error => console.error("Sound playback failed:", error));
  const rowindex = $(this).closest("tr").index();
  const row = $("table.order-list tbody tr").eq(rowindex);
  const currentQty = parseFloat(row.find(".qty").val()) || 0;
  const stockQty = parseFloat(row.find(".productqty").val()) || 0;

  if (currentQty + 1 > stockQty) {
    alert("Product quantity is over available quantity: " + stockQty);
    return;
  }

  row.find(".qty").val(currentQty + 1);
  calculateRowTotal(rowindex);
  calculateTotal();
});

$("#sale").on("click", ".minus", function () {
  if (isSoundOn) sound.play().catch(error => console.error("Sound playback failed:", error));
  const rowindex = $(this).closest("tr").index();
  const row = $("table.order-list tbody tr").eq(rowindex);
  let currentQty = parseFloat(row.find(".qty").val()) || 1;

  if (currentQty > 1) {
    row.find(".qty").val(currentQty - 1);
  }

  calculateRowTotal(rowindex);
  calculateTotal();
});

$("#sale").on("keyup", ".qty", function () {
  const rowindex = $(this).closest("tr").index();
  const row = $("table.order-list tbody tr").eq(rowindex);
  let qty = parseFloat($(this).val()) || 1;
  const stockQty = parseFloat(row.find(".productqty").val()) || 0;
  const productTitle = row.find(".product-name").text();

  if (qty > stockQty) {
    alert(`${productTitle} - Product quantity is over available quantity: ${stockQty}`);
    qty = 1;
    $(this).val(1);
  }

  row.find(".qty").val(qty);
  calculateRowTotal(rowindex);
  calculateTotal();
});


function calculateQuantity() {
  var total_items = $("table.order-list tbody tr").length;
  $('input[name="item"]').val(total_items);
  $("#item").text(total_items);
}

function calculateSubtotal() {
  var total = 0;
  $(".sub-total").each(function () {
    total += parseFloat($(this).text());
  });
  $('input[name="subtotal"]').val(total);

  $("#subtotal").html(total.toFixed(2));
}

$(document).on("keyup", "#discount", function () {
  calculateTotal();
});

$(document).on("keyup", "#shipping_cost", function () {
  calculateTotal();
});

function calculateDiscount() {
  var discount = $("#discount").val();
  if (discount) {
    $("#total_discount").html(discount);
  } else {
    $("#total_discount").html(0);
  }
}

function calculateShipping() {
  var shipping = $("#shipping_cost").val();
  if (shipping) {
    $("#shipping").html(shipping);
  } else {
    $("#shipping").html(0);
  }
}

function calculateGrandTotal() {
  var grandtotal =
    parseFloat($("#subtotal").html()) -
    parseFloat($("#total_discount").html()) +
    parseFloat($("#shipping").html());
  $("#grand_totals").html(grandtotal.toFixed(2));
  $('input[name="grand_total"]').val(grandtotal.toFixed(2));
  $("#duepayment").val(grandtotal);

}

$(document).on("keyup", "#payment", function () {
  const grand_total = parseFloat($('input[name="grand_total"]').val()) || 0;
  const payment = parseFloat($(this).val()) || 0;

  if (payment > grand_total) {
    alert("Paying amount cannot be bigger than grand total");
    $(this).val("");
    $("#duepayment").val(grand_total.toFixed(2));
    return;
  }

  const duepayment = grand_total - payment;
  $("#duepayment").val(duepayment.toFixed(2));
});

function calculateTotal() {
  calculateQuantity();
  calculateSubtotal();
  calculateShipping();
  calculateDiscount();
  calculateGrandTotal();
}
