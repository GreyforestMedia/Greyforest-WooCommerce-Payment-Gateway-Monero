	

			jQuery( function( $ ) {

				$(document).ready( function() {
					var loadedoption = $('#woocommerce_monero_feeordiscount_charge option:selected').val();

							if ( loadedoption == "None" ) { 
								$('#woocommerce_monero_feeordiscount_percentage').prop('disabled',true);
								$('#woocommerce_monero_feeordiscount_percentage').val('0');
							}
							else {
								$('#woocommerce_monero_feeordiscount_percentage').prop('disabled',false);
							}
				});
				
				$('#woocommerce_monero_feeordiscount_charge').on('click keyup keydown change', function() {
				var currentoption = $('#woocommerce_monero_feeordiscount_charge option:selected').val();

						if ( currentoption == "None" ) { 
							$('#woocommerce_monero_feeordiscount_percentage').prop('disabled',true);
							$('#woocommerce_monero_feeordiscount_percentage').val('0');
						}
						else {
							$('#woocommerce_monero_feeordiscount_percentage').prop('disabled',false);
						}									
				});

			});
