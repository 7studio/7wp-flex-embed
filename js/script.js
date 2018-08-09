window._wpLoadGutenbergEditor.then( function() {
	wp.blocks.getBlockTypes().forEach( function( blockType ) {
	    if ( blockType.name.indexOf( 'core-embed/' ) !== -1 && SWP_FE.allowedProviders.indexOf( blockType.title ) === -1 ) {
            wp.blocks.unregisterBlockType( blockType.name );
	    }
	} );
} );
