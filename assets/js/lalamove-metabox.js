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
            " has been pushed. You’ll now be redirected to the Lalamove menu."
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
});
