jQuery(document).ready(function($) {
  $(document).off( 'click', '#affiliate' );
  $(document).on( 'click', '#affiliate', function() {
  //Code Here
	var prodid = $("#prodid").text();
	alert( prodid );
	var odata = {
		'action': 'pending_order',
		security: wp_ajax_eaco.ajaxnonce,
		prod_id: prodid
	};
	
	$.post( 
        wp_ajax_eaco.ajaxurl, 
        odata, function(response) {
		alert('Got this from the server: ' + response);
				});
  });
});
