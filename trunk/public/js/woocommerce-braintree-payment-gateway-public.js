/* global Braintree_params */
(function ( $ ) {

	// Check if Checkout form has id attribute set
	var checkoutForm = $( 'form.checkout' );
	var checkoutFormId = checkoutForm.attr( 'id' );
	if( !checkoutFormId ) {
		// Set id attribute to "checkout"
		checkoutFormId = 'checkout';
    	checkoutForm.attr( 'id', checkoutFormId );
	}
	// Braintree form data encryption
    var braintree = Braintree.create( Braintree_params.cse_key );
    braintree.onSubmitEncryptForm( checkoutFormId );

}( jQuery ) );
