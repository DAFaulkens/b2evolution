<?php
/**
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2009-2016 by Francois Planque - {@link http://fplanque.com/}
 * Parts of this file are copyright (c)2009 by The Evo Factory - {@link http://www.evofactory.com/}.
 *
 * @package evocore
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * @var Site
 */
global $edited_Site;

// Determine if we are creating or updating...
global $action;

$creating = is_create_action( $action );

$Form = new Form( NULL, 'site_checkchanges', 'post', 'compact' );

// Get permission to edit the site:
$perm_site_edit = $current_User->check_perm( 'site', 'edit', false, $edited_Site->ID );

if( ! $creating && $perm_site_edit && $edited_Site->ID != 1 )
{	// Display a link to delete the site only if Current user has no permission to edit it:
	$Form->global_icon( T_('Delete this site!'), 'delete', regenerate_url( 'action', 'action=delete_site&amp;'.url_crumb( 'site' ) ) );
}
$Form->global_icon( T_('Cancel editing!'), 'close', '?ctrl=dashboard' );

$Form->begin_form( 'fform', $creating ?  T_('New site') : T_('Site') );

	$Form->add_crumb( 'site' );

	$Form->hiddens_by_key( get_memorized( 'action'.( $creating ? ',site_ID' : '' ) ) ); // (this allows to come back to the right list order & page)

	$Form->hidden( 'site_ID', $edited_Site->ID );

	// Name:
	if( $perm_site_edit && $edited_Site->ID != 1 )
	{
		$Form->text_input( 'site_name', $edited_Site->get( 'name' ), 50, T_('Name'), '', array( 'maxlength' => 255, 'required' => true ) );
	}
	else
	{
		$Form->info( T_('Name'), $edited_Site->get( 'name' ) );
	}

	// Owner:
	$owner_User = & $edited_Site->get_owner_User();
	if( $perm_site_edit )
	{
		$Form->username( 'site_owner_login', $owner_User, T_('Owner'), T_('Login of this site\'s owner.'), '', array( 'required' => true ) );
	}
	else
	{
		$Form->info( T_('Owner'), $owner_User->get_identity_link() );
	}

	// Order:
	if( $perm_site_edit )
	{
		$Form->text_input( 'site_order', $edited_Site->get( 'order' ), 5, T_('Order number'), '', array( 'maxlength' => 11, 'required' => true ) );
	}
	else
	{
		$Form->info( T_('Order number'), $edited_Site->get( 'order' ) );
	}

if( ! $perm_site_edit )
{	// Don't display a submit button if Current user has no permission to edit this site:
	$Form->end_form();
}
elseif( $creating )
{	// Display a button to create new site:
	$Form->end_form( array( array( 'submit', 'actionArray[create_site]', T_('Record'), 'SaveButton' ) ) );
}
else
{	// Display a button to update the site:
	$Form->end_form( array( array( 'submit', 'actionArray[update_site]', T_('Save Changes!'), 'SaveButton' ) ) );
}

?>