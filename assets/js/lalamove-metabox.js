jQuery(function($) {
  console 
  $(document).on('click', '.transfer-to-lalamove-btn', function(e) {
    e.preventDefault();

    var $btn    = $(this),
        orderId = $btn.data('order_id'),
        nonce   = $btn.data('nonce');

    $btn.prop('disabled', true).text('Transferring…');


    jQuery.ajax({
    url: wooLalamoveMetabox.ajax_url,
    method: "POST",
    data: {
        action   : "push_send_to_lalamove",
        order_id : orderId,
        nonce    : nonce
    },
    success: function(response) {
        console.log("RESPONSE", response);
        if (response.success) {
        alert(
            "✅ Order #" + orderId +
            " has been pushed. You'll now be redirected to the Lalamove menu."
        );

        //Redirect to lalamove orders
        window.location.href = window.location.origin + "/wp-admin/admin.php?page=woo-lalamove#/orders";
        
        } else {
        console.error("ERROR", response.data);
        alert(
            "❌ Transfer failed: " + response.data + "\n" +
            "Please try again."
        );
        $btn.prop("disabled", false).text("Transfer to Lalamove");
        }
    },
    error: function(response, error, xhr) {
        console.log("RESPONSE", response);
        console.error("MAY MALI!!!", error);
        console.error("XHR Response Text:", xhr.responseText);
        alert(
        "🚨 Unable to reach the server.\n" +
        "Check your network and try again."
        );
        $btn.prop("disabled", false).text("Transfer to Lalamove");
    }
    });

  });

  // Handle admin payment marking
  $(document).on('click', '.mark-admin-paid-btn', function(e) {
    e.preventDefault();

    var $btn = $(this),
        orderId = $btn.data('order_id'),
        nonce = $btn.data('nonce'),
        shippingCost = parseFloat($('#admin_shipping_cost').val()),
        paymentMethod = $('#admin_payment_method').val();

    // Validation
    if (!shippingCost || shippingCost <= 0) {
      alert('Please enter a valid shipping cost.');
      return;
    }

    if (confirm('Mark this shipping as paid by admin for $' + shippingCost.toFixed(2) + '?')) {
      $btn.prop('disabled', true).text('Saving...');

      $.ajax({
        url: wooLalamoveMetabox.ajax_url,
        method: 'POST',
        data: {
          action: wooLalamoveMetabox.mark_admin_paid_action,
          order_id: orderId,
          shipping_cost: shippingCost,
          payment_method: paymentMethod,
          nonce: nonce
        },
        success: function(response) {
          if (response.success) {
            alert('✅ ' + response.data);
            location.reload(); // Refresh to show updated status
          } else {
            alert('❌ Error: ' + response.data);
            $btn.prop('disabled', false).text('Mark as Paid');
          }
        },
        error: function(xhr, status, error) {
          alert('🚨 Network error. Please try again.');
          $btn.prop('disabled', false).text('Mark as Paid');
        }
      });
    }
  });
});
