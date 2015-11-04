<?php
/**
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2009-2015 by Francois Planque - {@link http://fplanque.com/}
 * Parts of this file are copyright (c)2009 by The Evo Factory - {@link http://www.evofactory.com/}.
 *
 * @package evocore
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

// Load Organization class:
load_class( 'users/model/_organization.class.php', 'Organization' );

/**
 * @var User
 */
global $current_User;

// Check minimum permission:
$current_User->check_perm( 'orgs', 'create', true );

// Set options path:
$AdminUI->set_path( 'users', 'organizations' );

// Get action parameter from request:
param_action( '', true );

if( param( 'org_ID', 'integer', '', true ) )
{ // Load organization from cache:
	$OrganizationCache = & get_OrganizationCache();
	if( ( $edited_Organization = & $OrganizationCache->get_by_ID( $org_ID, false ) ) === false )
	{ // We could not find the organization to edit:
		unset( $edited_Organization );
		forget_param( 'org_ID' );
		$Messages->add( sprintf( T_('Requested &laquo;%s&raquo; object does not exist any longer.'), T_('Organization') ), 'error' );
		$action = 'nil';
	}
}


switch( $action )
{
	case 'new':
		// Check permission:
		$current_User->check_perm( 'orgs', 'create', true );

		if( ! isset( $edited_Organization ) )
		{ // We don't have a model to use, start with blank object:
			$edited_Organization = new Organization();
		}
		else
		{ // Duplicate object in order no to mess with the cache:
			$edited_Organization = duplicate( $edited_Organization ); // PHP4/5 abstraction
			$edited_Organization->ID = 0;
			$edited_Organization->set( 'owner_user_ID', $current_User->ID );
		}
		break;

	case 'edit':
		// Check permission:
		$current_User->check_perm( 'orgs', 'view', true, $edited_Organization );

		// Make sure we got an org_ID:
		param( 'org_ID', 'integer', true );
		break;

	case 'create': // Record new Organization
	case 'create_new': // Record Organization and create new
	case 'create_copy': // Record Organization and create similar
		// Insert new organization...:
		$edited_Organization = new Organization();

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'organization' );

		// Check permission:
		$current_User->check_perm( 'orgs', 'create', true );

		// load data from request
		if( $edited_Organization->load_from_Request() )
		{ // We could load data from form without errors:

			// While inserting into DB, ID property of Organization object will be set to autogenerated ID
			// So far as we set ID manualy, we need to preserve this value
			// When assignment of wrong value will be fixed, we can skip this
			$entered_organization_id = $edited_Organization->ID;

			$DB->begin();

			$duplicated_organization_ID = $edited_Organization->dbexists( 'org_name', $edited_Organization->get( 'name' ) );
			if( $duplicated_organization_ID )
			{ // We have a duplicate entry:
				param_error( 'org_name',
					sprintf( T_('This organization name already exists. Do you want to <a %s>edit the existing organization</a>?'),
						'href="?ctrl=organizations&amp;action=edit&amp;org_ID='.$duplicated_organization_ID.'"' ) );
			}
			else
			{ // Insert in DB:
				$edited_Organization->dbinsert();
				$Messages->add( T_('New organization created.'), 'success' );
			}

			$DB->commit();

			if( ! param_errors_detected() )
			{ // No errors
				switch( $action )
				{ // What next?
					case 'create_copy':
						// Redirect so that a reload doesn't write to the DB twice:
						header_redirect( '?ctrl=organizations&action=new&org_ID='.$edited_Organization->ID, 303 ); // Will EXIT
						// We have EXITed already at this point!!
						break;
					case 'create_new':
						// Redirect so that a reload doesn't write to the DB twice:
						header_redirect( '?ctrl=organizations&action=new', 303 ); // Will EXIT
						// We have EXITed already at this point!!
						break;
					case 'create':
						// Redirect so that a reload doesn't write to the DB twice:
						header_redirect( '?ctrl=organizations', 303 ); // Will EXIT
						// We have EXITed already at this point!!
						break;
				}
			}
		}
		break;

	case 'update':
		// Edit organization form...:

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'organization' );

		// Check permission:
		$current_User->check_perm( 'orgs', 'edit', true, $edited_Organization );

		// Make sure we got an org_ID:
		param( 'org_ID', 'integer', true );

		// load data from request
		if( $edited_Organization->load_from_Request() )
		{ // We could load data from form without errors:

			$DB->begin();

			$duplicated_organization_ID = $edited_Organization->dbexists( 'org_name', $edited_Organization->get( 'name' ) );
			if( $duplicated_organization_ID && $duplicated_organization_ID != $edited_Organization->ID )
			{ // We have a duplicate entry:
				param_error( 'org_name',
					sprintf( T_('This organization name already exists. Do you want to <a %s>edit the existing organization</a>?'),
						'href="?ctrl=organizations&amp;action=edit&amp;org_ID='.$duplicated_organization_ID.'"' ) );
			}
			else
			{ // Update in DB:
				$edited_Organization->dbupdate();
				$Messages->add( T_('Organization updated.'), 'success' );
			}

			$DB->commit();

			if( ! param_errors_detected() )
			{ // No errors
				header_redirect( '?ctrl=organizations', 303 ); // Will EXIT
				// We have EXITed already at this point!!
			}
		}
		break;

	case 'delete':
		// Delete organization:

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'organization' );

		// Check permission:
		$current_User->check_perm( 'orgs', 'edit', true, $edited_Organization );

		// Make sure we got an org_ID:
		param( 'org_ID', 'integer', true );

		if( param( 'confirm', 'integer', 0 ) )
		{ // confirmed, Delete from DB:
			$msg = sprintf( T_('Organization &laquo;%s&raquo; deleted.'), $edited_Organization->dget( 'name' ) );
			$edited_Organization->dbdelete();
			unset( $edited_Organization );
			forget_param( 'org_ID' );
			$Messages->add( $msg, 'success' );
			// Redirect so that a reload doesn't write to the DB twice:
			header_redirect( '?ctrl=organizations', 303 ); // Will EXIT
			// We have EXITed already at this point!!

		}
		else
		{	// not confirmed, Check for restrictions:
			if( ! $edited_Organization->check_delete( sprintf( T_('Cannot delete organization &laquo;%s&raquo;'), $edited_Organization->dget( 'name' ) ) ) )
			{	// There are restrictions:
				$action = 'view';
			}
		}
		break;
}

$AdminUI->breadcrumbpath_init( false );  // fp> I'm playing with the idea of keeping the current blog in the path here...
$AdminUI->breadcrumbpath_add( T_('Users'), '?ctrl=users' );
$AdminUI->breadcrumbpath_add( T_('Settings'), '?ctrl=usersettings' );
$AdminUI->breadcrumbpath_add( T_('Organizations'), '?ctrl=organizations' );

if( $action == 'new' || $action == 'edit' )
{
	// Set an url for manual page:
	$AdminUI->set_page_manual_link( 'organization-form' );
	// Init JS to autcomplete the user logins
	init_autocomplete_login_js( 'rsc_url', $AdminUI->get_template( 'autocomplete_plugin' ) );
}
else
{	// Set an url for manual page:
	$AdminUI->set_page_manual_link( 'organizations' );
}

// Display <html><head>...</head> section! (Note: should be done early if actions do not redirect)
$AdminUI->disp_html_head();

// Display title, menu, messages, etc. (Note: messages MUST be displayed AFTER the actions)
$AdminUI->disp_body_top();

$AdminUI->disp_payload_begin();

/**
 * Display payload:
 */
switch( $action )
{
	case 'nil':
		// Do nothing
		break;


	case 'delete':
		// We need to ask for confirmation:
		$edited_Organization->confirm_delete(
				sprintf( T_('Delete organization &laquo;%s&raquo;?'), $edited_Organization->dget( 'name' ) ),
				'organization', $action, get_memorized( 'action' ) );
		/* no break */
	case 'new':
	case 'create':
	case 'create_new':
	case 'create_copy':
	case 'edit':
	case 'update':	// we return in this state after a validation error
		$AdminUI->disp_view( 'users/views/_organization.form.php' );
		break;


	default:
		// No specific request, list all organizations:
		// Cleanup context:
		forget_param( 'org_ID' );
		// Display organizations list:
		$AdminUI->disp_view( 'users/views/_organization.view.php' );
		break;

}

$AdminUI->disp_payload_end();

// Display body bottom, debug info and close </html>:
$AdminUI->disp_global_footer();

?>