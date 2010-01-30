<?php
/**
 * This file implements the UI controller for file upload.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2009 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * }}
 *
 * @package admin
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @global File
 */
global $edited_File;

global $blog;

$Form = new Form( NULL, 'fm_properties_checkchanges' );

$Form->global_icon( T_('Close properties!'), 'close', regenerate_url() );

$Form->begin_form( 'fform', T_('File properties') );

	$Form->add_crumb( 'file' );
	$Form->hidden_ctrl();
	$Form->hidden( 'action', 'update_properties' );
	$Form->hiddens_by_key( get_memorized() );

	$Form->begin_fieldset( T_('Properties') );
		if( $current_User->check_perm( 'files', 'edit', false, $blog ? $blog : NULL ) )
		{ // User can edit: 
			$Form->text( 'name', $edited_File->dget('name'), 32, T_('Filename'), T_('This is the name of the file on the server hard drive.'), 128 );
		}
		else
		{ // User can view only:
			$Form->info( T_('Filename'), $edited_File->dget('name'), T_('This is the name of the file on the server hard drive.') );	
		}
		$Form->info( T_('Type'), $edited_File->get_icon().' '.$edited_File->get_type() );
	$Form->end_fieldset();

	$Form->begin_fieldset( T_('Meta data') );
		if( $current_User->check_perm( 'files', 'edit', false, $blog ? $blog : NULL ) )
		{ // User can edit:
			$Form->text( 'title', $edited_File->title, 50, T_('Long title'), T_('This is a longer descriptive title'), 255 );
			$Form->text( 'alt', $edited_File->alt, 50, T_('Alternative text'), T_('This is useful for images'), 255 );
			$Form->textarea( 'desc', $edited_File->desc, 10, T_('Caption/Description') );
		}
		else
		{ // User can view only:
			$Form->info( T_('Long title'), $edited_File->dget('title'), T_('This is a longer descriptive title') );
			$Form->info( T_('Alternative text'), $edited_File->dget('alt'), T_('This is useful for images') );
			$Form->info( T_('Caption/Description'), $edited_File->dget('desc') );
		}
	$Form->end_fieldset();

if( $current_User->check_perm( 'files', 'edit', false, $blog ? $blog : NULL ) )
{ // User can edit:
	$Form->end_form( array( array( 'submit', '', T_('Update'), 'SaveButton' ),
													array( 'reset', '', T_('Reset'), 'ResetButton' ) ) );
}
else
{ // User can view only:
	$Form->end_form();
}

/*
 * $Log$
 * Revision 1.9  2010/01/30 18:55:27  blueyed
 * Fix "Assigning the return value of new by reference is deprecated" (PHP 5.3)
 *
 * Revision 1.8  2010/01/22 20:20:21  efy-asimo
 * Remove File manager rename file
 *
 * Revision 1.7  2010/01/03 13:45:36  fplanque
 * set some crumbs (needs checking)
 *
 * Revision 1.6  2009/08/29 12:23:56  tblue246
 * - SECURITY:
 * 	- Implemented checking of previously (mostly) ignored blog_media_(browse|upload|change) permissions.
 * 	- files.ctrl.php: Removed redundant calls to User::check_perm().
 * 	- XML-RPC APIs: Added missing permission checks.
 * 	- items.ctrl.php: Check permission to edit item with current status (also checks user levels) for update actions.
 * - XML-RPC client: Re-added check for zlib support (removed by update).
 * - XML-RPC APIs: Corrected method signatures (return type).
 * - Localization:
 * 	- Fixed wrong permission description in blog user/group permissions screen.
 * 	- Removed wrong TRANS comment
 * 	- de-DE: Fixed bad translation strings (double quotes + HTML attribute = mess).
 * - File upload:
 * 	- Suppress warnings generated by move_uploaded_file().
 * 	- File browser: Hide link to upload screen if no upload permission.
 * - Further code optimizations.
 *
 * Revision 1.5  2009/03/08 23:57:43  fplanque
 * 2009
 *
 * Revision 1.4  2008/10/11 22:20:48  blueyed
 * Fix edit and properties view in file browser. (edit_File has been renamed to edited_File)
 *
 * Revision 1.3  2008/01/21 09:35:29  fplanque
 * (c) 2008
 *
 * Revision 1.2  2007/09/26 21:53:23  fplanque
 * file manager / file linking enhancements
 *
 * Revision 1.1  2007/06/25 11:00:05  fplanque
 * MODULES (refactored MVC)
 *
 * Revision 1.9  2007/04/26 00:11:10  fplanque
 * (c) 2007
 *
 * Revision 1.8  2007/01/24 03:45:29  fplanque
 * decrap / removed a lot of bloat...
 *
 * Revision 1.7  2007/01/24 02:35:42  fplanque
 * refactoring
 *
 * Revision 1.6  2006/12/23 22:53:10  fplanque
 * extra security
 *
 * Revision 1.5  2006/11/24 18:27:25  blueyed
 * Fixed link to b2evo CVS browsing interface in file docblocks
 */
?>