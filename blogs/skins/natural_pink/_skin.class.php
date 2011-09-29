<?php
/**
 * This file implements a class derived of the generic Skin class in order to provide custom code for
 * the skin in this folder.
 *
 * This file is part of the b2evolution project - {@link http://b2evolution.net/}
 *
 * @package skins
 * @subpackage custom
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Specific code for this skin.
 *
 * ATTENTION: if you make a new skin you have to change the class name below accordingly
 */
class natural_pink_Skin extends Skin
{
  /**
	 * Get default name for the skin.
	 * Note: the admin can customize it.
	 */
	function get_default_name()
	{
		return 'Natural Pink';
	}


  /**
	 * Get default type for the skin.
	 */
	function get_default_type()
	{
		return 'normal';
	}


	/**
	 * Get definitions for editable params
	 *
	 * @see Plugin::GetDefaultSettings()
	 * @param local params like 'for_editing' => true
	 */
	function get_param_definitions( $params )
	{
		$r = array_merge( array(
				'colorbox' => array(
					'label' => T_('Colorbox Image Zoom'),
					'note' => T_('Check to enable javascript zooming on images (using the colorbox script)'),
					'defaultvalue' => 1,
					'type' => 'checkbox',
				),
				'bubbletip' => array(
					'label' => T_('Username bubble tips'),
					'note' => T_('Check to enable bubble tips on usernames'),
					'defaultvalue' => 0,
					'type' => 'checkbox',
				),
			), parent::get_param_definitions( $params )	);

		return $r;
	}


  /**
	 * Get ready for displaying the skin.
	 *
	 * This may register some CSS or JS...
	 */
	function display_init()
	{
		// call parent:
		parent::display_init();


		// Colorbox (a lightweight Lightbox alternative) allows to zoom on images and do slideshows with groups of images:
		if($this->get_setting("colorbox")) 
		{
			require_js_helper( 'colorbox', 'blog' );
		}
	}
}

/*
 * $Log$
 * Revision 1.7  2011/09/29 12:22:24  efy-yurybakh
 * skin param for bubbletip
 *
 * Revision 1.6  2011/09/17 02:31:59  fplanque
 * Unless I screwed up with merges, this update is for making all included files in a blog use the same domain as that blog.
 *
 * Revision 1.5  2011/09/14 20:19:49  fplanque
 * cleanup
 *
 * Revision 1.4  2011/09/12 16:43:36  lxndral
 * skins colorbox fix
 *
 * Revision 1.3  2011/09/09 23:26:48  lxndral
 * Add _skins.class.php to all skins  (Easy task)
 *
 * Revision 1.2  2011/09/08 13:42:37  lxndral
 * Add _skins.class.php to all skins  (Easy task)
 *
 * Revision 1.1  2009/05/23 20:20:17  fplanque
 * Skins can now have a _skin.class.php file to override default Skin behaviour. Currently only the default name but can/will be extended.
 *
 */
?>
