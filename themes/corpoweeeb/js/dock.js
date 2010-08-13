$( function() {
	$( '#dock li' ).hover( function() {
		$( '.latest' ).fadeOut( 'fast' );
		$( this ).addClass( 'dock-active' );
		$( this ).children( '.display' ).fadeIn( 200 );
	}).bind( "mouseleave", function() {
		$( this ).removeClass( 'dock-active' );
		$( this ).children( '.display' ).fadeOut( 200 );
	} );

	$( '#dock' ).bind( "mouseleave", function() {
		$( '.latest' ).fadeIn( 1000 );
	} );
} );
