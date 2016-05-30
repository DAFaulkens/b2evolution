/**
 * This file implements links specific Javascript functions.
 * (Used only in back-office)
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2006 by Francois PLANQUE - {@link http://fplanque.com/}
 *
 * @package admin
 */

function evo_display_position_onchange( selectInput, url, crumb )
{
	var oThis = selectInput;
	var new_position = selectInput.value;
	jQuery.get( url + 'async.php?action=set_object_link_position&link_ID=' + selectInput.id.substr(17) + '&link_position=' + new_position + '&crumb_link=' + crumb, {
	}, function(r, status) {
		r = ajax_debug_clear( r );
		if( r == "OK" ) {
			evoFadeSuccess( jQuery(oThis).closest('tr') );
			jQuery(oThis).closest('td').removeClass('error');
			if( new_position == 'cover' )
			{ // Position "Cover" can be used only by one link
				jQuery( 'select[name=link_position][id!=' + selectInput.id + '] option[value=cover]:selected' ).each( function()
				{ // Replace previous position with "Inline"
					jQuery( this ).parent().val( 'aftermore' );
					evoFadeSuccess( jQuery( this ).closest('tr') );
				} );
			}
		} else {
			jQuery(oThis).val(r);
			evoFadeFailure( jQuery(oThis).closest('tr') );
			jQuery(oThis.form).closest('td').addClass('error');
		}
	} );
	return false;
}


/**
 * Insert inline tag into the post ( example: [image:123:caption text] | [file:123:caption text] )
 *
 * @param string Type: 'image', 'file', 'video'
 * @param integer File ID
 * @param string Caption text
 */
function insert_inline_link( type, link_ID, caption )
{
	var b2evoCanvas = window.document.getElementById('itemform_post_content');
	if( b2evoCanvas != null )
	{ // Canvas exists
		var insert_tag = '[' + type + ':' + link_ID;

		if( caption.length )
		{
			console.log( caption.length );
			insert_tag += ':' + caption;
		}

		insert_tag += ']';

		// Insert an image tag
		textarea_wrap_selection( b2evoCanvas, insert_tag, '', 0, window.document );

		var $position_selector = jQuery( '#display_position_' + link_ID );
		if( $position_selector.length != 0 )
		{ // Change the position to 'Inline'
			if( $position_selector.val() != 'inline' )
			{
				$position_selector.val( 'inline' ).change();
			}
		}
	}
}


/**
 * Unlink an attachment from Item or Comment
 *
 * @param object Event object
 * @param string Type: 'item', 'comment'
 * @param integer Link ID
 * @param string Action: 'unlink', 'delete'
 */
function evo_unlink_attachment( event_object, type, link_ID, action )
{
	if( type == 'item' )
	{	// Replace the inline image placeholders when file is unlinked from Item:
		var b2evoCanvas = window.document.getElementById( 'itemform_post_content' );
		if( b2evoCanvas != null )
		{ // Canvas exists
			var regexp = new RegExp( '\\\[(image|file|inline):' + link_ID + ':?[^\\\]]*\\\]', 'ig' );
			textarea_str_replace( b2evoCanvas, regexp, '', window.document );
		}
	}

	// Call REST API request to unlink/delete the attachment:
	evo_rest_api_request( 'links/' + link_ID,
	{
		'action': action
	},
	function( data )
	{
		// Remove attachment row from table:
		jQuery( event_object ).closest( 'tr' ).remove();
	},
	'DELETE' );


	return false;
}