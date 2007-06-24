<?php
/**
 * This file implements the Admin UI class.
 * Alternate admin skins should derive from this class.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2007 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 *
 * {@internal Open Source relicensing agreement:
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package admin-skin
 * @subpackage evo
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Includes
 */
require_once dirname(__FILE__).'/../_adminUI_general.class.php';


/**
 * We'll use the default AdminUI templates etc.
 *
 * @package admin-skin
 * @subpackage evo
 */
class AdminUI extends AdminUI_general
{

	/**
	 * Get HTML head lines, links (to CSS files especially).
	 *
	 * @return string Calls parent::get_headlines()
	 */
	function get_headlines()
	{
		global $mode;

		require_css( 'skins_adm/evo/rsc/css/style.css', TRUE, 'Blue' );

		return parent::get_headlines();
	}


	/**
	 * Get the top of the HTML <body>.
	 *
	 * @uses get_page_head()
	 * @return string
	 */
	function get_body_top()
	{
		global $Messages;

		$r = '';

		if( empty($this->mode) )
		{ // We're not running in an special mode (bookmarklet...)

			$r .= $this->get_page_head();

			// Display MAIN menu:
			$r .= $this->get_html_menu().'

			<div class="panelbody">
			';
		}

		$r .= '

		<div id="payload">
		';

		$r .= $this->get_bloglist_buttons( '<div id="TitleArea">', '</div>' );

		// Display info & error messages
		$r .= $Messages->display( NULL, NULL, false, 'all', NULL, NULL, 'action_messages' );

		return $r;
	}


	/**
	 * Close open div(s).
	 *
	 * @return string
	 */
	function get_body_bottom()
	{
		$r = '';

		if( empty($this->mode) )
		{ // We're not running in an special mode (bookmarklet...)
			$r .= "\n\t</div>";
		}

		return $r."</div>\n";
	}


	/**
	 * GLOBAL HEADER - APP TITLE, LOGOUT, ETC.
	 *
	 * @return string
	 */
	function get_page_head()
	{
		global $current_User, $htsrv_url_sensitive, $baseurl, $admin_url, $rsc_url, $Blog;

		$r = '
		<div id="header">
			'.$this->admin_logo.'

			<div id="headinfo">
				<span id="headfunctions">'
				// Note: if we log in with another user, we may not have the perms to come back to the same place any more, thus: redirect to admin home.
				.'<a href="'.$htsrv_url_sensitive.'login.php?action=logout&amp;redirect_to='.rawurlencode(url_rel_to_same_host($admin_url, $htsrv_url_sensitive)).'">'.T_('Logout').'</a>
					&bull;
					<a href="'.( isset($Blog) ? $Blog->get('url') : $baseurl ).'">'.T_('Exit to blogs').'
						<img src="'.$rsc_url.'icons/close.gif" width="14" height="14" border="0" class="top" alt="" title="'
						.T_('Exit to blogs').'" /></a>
				</span>

				'.$this->get_head_info().'
			</div>

			<h1>'.$this->get_title_for_titlearea().'</h1>
		</div>
		';

		return $r;
	}
}

?>