<?php
/**
 * This file implements the email campaign class.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}.
*
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 *
 * @package evocore
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( '_core/model/dataobjects/_dataobject.class.php', 'DataObject' );


/**
 * Email Campaign Class
 *
 * @package evocore
 */
class EmailCampaign extends DataObject
{
	var $date_ts;

	var $name;

	var $email_title;

	var $email_html;

	var $email_text;

	var $email_plaintext;

	var $sent_ts;

	/**
	 * @var array|NULL User IDs which assigned for this email campaign
	 *   'all'    - All users which assigned to this campaign
	 *   'accept' - Users which already receive email newsletter
	 *   'wait'   - Users which still didn't receive email by some reason (Probably their newsletter limit was full)
	 */
	var $users = NULL;

	/**
	 * @var string
	 */
	var $renderers;

	/**
	 * Constructor
	 *
	 * @param object table Database row
	 */
	function EmailCampaign( $db_row = NULL )
	{
		// Call parent constructor:
		parent::DataObject( 'T_email__campaign', 'ecmp_', 'ecmp_ID', 'date_ts' );

		if( $db_row == NULL )
		{
			$this->set_renderers( array( 'default' ) );
		}
		else
		{
			$this->ID = $db_row->ecmp_ID;
			$this->date_ts = $db_row->ecmp_date_ts;
			$this->name = $db_row->ecmp_name;
			$this->email_title = $db_row->ecmp_email_title;
			$this->email_html = $db_row->ecmp_email_html;
			$this->email_text = $db_row->ecmp_email_text;
			$this->email_plaintext = $db_row->ecmp_email_plaintext;
			$this->sent_ts = $db_row->ecmp_sent_ts;
			$this->renderers = $db_row->ecmp_renderers;
		}
	}


	/**
	 * Get delete cascade settings
	 *
	 * @return array
	 */
	static function get_delete_cascades()
	{
		return array(
				array( 'table'=>'T_email__campaign_send', 'fk'=>'csnd_camp_ID', 'msg'=>T_('%d links with users') ),
			);
	}


	/**
	 * Add users for this campaign in DB
	 */
	function add_users()
	{
		global $DB;

		// Get user IDs from current filterset of users list
		$new_users_IDs = get_filterset_user_IDs();

		if( count( $new_users_IDs ) )
		{ // Users are found in the filterset

			// Get all active users which accept newsletter email
			$new_users_SQL = get_newsletter_users_sql( $new_users_IDs );
			$new_users = $DB->get_col( $new_users_SQL->get() );

			// Remove the users which didn't accept email before
			$DB->query( 'DELETE FROM T_email__campaign_send
				WHERE csnd_camp_ID = '.$DB->quote( $this->ID ).'
				 AND csnd_emlog_ID IS NULL' );

			// Get users which already accept newsletter email
			$old_users = $this->get_users( 'accept' );

			// Exclude old users from new users (To store value of csnd_emlog_ID)
			$new_users = array_diff( $new_users, $old_users );

			if( count( $new_users ) )
			{ // Insert new users for this campaign
				$insert_SQL = 'INSERT INTO T_email__campaign_send ( csnd_camp_ID, csnd_user_ID ) VALUES';
				foreach( $new_users as $user_ID )
				{
					$insert_SQL .= "\n".'( '.$DB->quote( $this->ID ).', '.$DB->quote( $user_ID ).' ),';
				}
				$DB->query( substr( $insert_SQL, 0, -1 ) );
			}
		}
	}


	/**
	 * Get user IDs of this campaign
	 *
	 * @param string Type of users:
	 *   'all'    - All users which assigned to this campaign
	 *   'accept' - Users which already receive email newsletter
	 *   'wait'   - Users which still didn't receive email by some reason (Probably their newsletter limit was full)
	 * @return array user IDs
	 */
	function get_users( $type = 'all' )
	{
		global $DB;

		if( !is_null( $this->users ) )
		{ // Get users from cache
			return $this->users[ $type ];
		}

		// Get users from DB
		$users_SQL = new SQL();
		$users_SQL->SELECT( 'csnd_user_ID, csnd_emlog_ID' );
		$users_SQL->FROM( 'T_email__campaign_send' );
		$users_SQL->WHERE( 'csnd_camp_ID = '.$DB->quote( $this->ID ) );
		$users = $DB->get_assoc( $users_SQL->get() );

		$this->users['all'] = array();
		$this->users['accept'] = array();
		$this->users['wait'] = array();

		foreach( $users as $user_ID => $emlog_ID )
		{
			$this->users['all'][] = $user_ID;
			if( $emlog_ID > 0 )
			{ // This user already accepted newsletter email
				$this->users['accept'][] = $user_ID;
			}
			else
			{ // This user didn't still accept email
				$this->users['wait'][] = $user_ID;
			}
		}

		return $this->users[ $type ];
	}


	/**
	 * Get the users number of this campaign
	 *
	 * @param string Type of users:
	 *   'all'    - All users which assigned to this campaign
	 *   'accept' - Users which already receive email newsletter
	 *   'wait'   - Users which still didn't receive email by some reason (Probably their newsletter limit was full)
	 * @return integer Number of users
	 */
	function get_users_count( $type = 'all' )
	{
		return count( $this->get_users( $type ) );
	}


	/**
	 * Load data from Request form fields.
	 *
	 * @return boolean true if loaded data seems valid.
	 */
	function load_from_Request()
	{
		global $Plugins;

		if( param( 'ecmp_name', 'string', NULL ) !== NULL )
		{ // Name
			param_string_not_empty( 'ecmp_name', T_('Please enter a campaign name.') );
			$this->set_from_Request( 'name' );
		}

		if( param( 'ecmp_email_title', 'string', NULL ) !== NULL )
		{ // Email title
			param_string_not_empty( 'ecmp_email_title', T_('Please enter an email title.') );
			$this->set_from_Request( 'email_title' );
		}

		if( param( 'ecmp_email_html', 'html', NULL ) !== NULL )
		{ // Email HTML message
			param_check_html( 'ecmp_email_html', T_('Please enter an HTML message.') );
			$this->set_from_Request( 'email_html' );
		}

		// Renderers:
		if( param( 'renderers_displayed', 'integer', 0 ) )
		{	// use "renderers" value only if it has been displayed (may be empty):
			$renderers = $Plugins->validate_renderer_list( param( 'renderers', 'array:string', array() ), array( 'EmailCampaign' => & $this ) );
			$this->set_renderers( $renderers );
		}

		if( param( 'ecmp_email_text', 'html', NULL ) !== NULL )
		{	// Email message:
			// Save original message:
			$this->set_from_Request( 'email_text' );

			// Save prerendered message:
			$data = $this->get( 'email_text' );
			$Plugins->trigger_event( 'FilterEmailContent', array( 'data' => & $data, 'EmailCampaign' => $this ) );
			$this->set( 'email_html', format_to_output( $data ) );

			// Save plain-text message:
			$email_plaintext = preg_replace( '/<a[^>]+href="([^"]+)"[^>]*>[^<]*<\/a>/i', ' [ $1 ] ', $this->get( 'email_html' ) );
			$email_plaintext = str_replace(
				array( "\n", "\r", '</p><p>', '<p>',  '</p>', '<br>', '<br />', '<br/>' ),
				array( '',   '',   "\n\n",    "\n\n", "\n\n", "\n",   "\n",     "\n" ),
				$email_plaintext );
			$this->set( 'email_plaintext', strip_tags( $email_plaintext ) );
		}

		return ! param_errors_detected();
	}


	/**
	 * Check if campaign are ready to send emails
	 *
	 * @param boolean TRUE to display messages about empty fields
	 * @param string Mode: 'test' - used to don't check some fields
	 * @return boolean TRUE if all fields are filled
	 */
	function check( $display_messages = true, $mode = '' )
	{
		if( $display_messages )
		{ // Display message
			global $Messages;
		}

		$result = true;

		if( empty( $this->email_title ) )
		{ // Email title is empty
			if( $display_messages )
			{
				$Messages->add( T_('Please enter an email title.'), 'error' );
			}
			$result = false;
		}

		if( empty( $this->email_text ) )
		{	// Email message is empty:
			if( $display_messages )
			{
				$Messages->add( T_('Please enter a text message.'), 'error' );
			}
			$result = false;
		}

		if( $mode != 'test' && count( $this->get_users( 'wait' ) ) == 0 )
		{ // No users found which wait this newsletter
			if( $display_messages )
			{
				$Messages->add( T_('No recipients found for this campaign.'), 'error' );
			}
			$result = false;
		}

		return $result;
	}


	/**
	 * Send one email
	 *
	 * @param integer User ID
	 * @param string Email address
	 * @param string Mode: 'test' - to send test email newsletter
	 * @return boolean TRUE on success
	 */
	function send_email( $user_ID, $email_address = '', $mode = '' )
	{
		$newsletter_params = array(
				'include_greeting' => false,
				'message_html'     => $this->get( 'email_html' ),
				'message_text'     => $this->get( 'email_plaintext' ),
			);

		if( $mode == 'test' )
		{ // Send a test newsletter
			global $current_User;

			$newsletter_params['boundary'] = 'b2evo-'.md5( rand() );
			$headers = array( 'Content-Type' => 'multipart/mixed; boundary="'.$newsletter_params['boundary'].'"' );

			$UserCache = & get_UserCache();
			if( $test_User = & $UserCache->get_by_ID( $user_ID, false, false ) )
			{ // Send a test email only when test user exists
				$message = mail_template( 'newsletter', 'auto', $newsletter_params, $test_User );
				return send_mail( $email_address, NULL, $this->get( 'email_title' ), $message, NULL, NULL, $headers );
			}
			else
			{ // No test user found
				return false;
			}
		}
		else
		{ // Send a newsletter to real user
			return send_mail_to_User( $user_ID, $this->get( 'email_title' ), 'newsletter', $newsletter_params, false, array(), $email_address );
		}
	}


	/**
	 * Send email newsletter for all users of this campaign
	 *
	 * @param boolean
	 */
	function send_all_emails( $display_messages = true )
	{
		global $DB, $localtimenow, $mail_log_insert_ID;

		// Send emails only for users which still don't accept emails
		$user_IDs = $this->get_users( 'wait' );

		if( empty( $user_IDs ) )
		{ // No users, Exit here
			return;
		}

		$DB->begin();

		// Update date of sending
		$this->set( 'sent_ts', date( 'Y-m-d H:i:s', $localtimenow ) );
		$this->dbupdate();

		if( $display_messages )
		{ // We need in this cache when display the messages
			$UserCache = & get_UserCache();
		}

		foreach( $user_IDs as $user_ID )
		{
			$result = $this->send_email( $user_ID );

			if( $result )
			{ // Email newsletter was sent for user successfully
				if( !empty( $mail_log_insert_ID ) )
				{ // ID of last inserted mail log is defined in function mail_log()
					$DB->query( 'UPDATE T_email__campaign_send
							SET csnd_emlog_ID = '.$DB->quote( $mail_log_insert_ID ).'
						WHERE csnd_camp_ID = '.$DB->quote( $this->ID ).'
							AND csnd_user_ID = '.$DB->quote( $user_ID ) );

					// Update arrays where we store which users accepted email and who waiting it now
					$this->users['accept'][] = $user_ID;
					if( ( $wait_user_ID_key = array_search( $user_ID, $this->users['wait'] ) ) !== false )
					{
						unset( $this->users['wait'][ $wait_user_ID_key ] );
					}
				}
			}

			if( $display_messages )
			{ // Print the messages
				$User = & $UserCache->get_by_ID( $user_ID, false, false );

				if( $result === true )
				{ // Success
					echo sprintf( T_('Email was sent to user: %s'), $User->get_identity_link() ).'<br />';
				}
				else
				{ // Failed, Email was NOT sent
					if( ! check_allow_new_email( 'newsletter_limit', 'last_newsletter', $user_ID ) )
					{ // Newsletter email is limited today for this user
						echo '<span class="orange">'.sprintf( T_('User %s has already received max # of newsletters today.'), $User->get_identity_link() ).'</span><br />';
					}
					else
					{ // Another error
						echo '<span class="red">'.sprintf( T_('Email was not sent to user: %s'), $User->get_identity_link() ).'</span><br />';
					}
				}

				evo_flush();
			}
		}

		$DB->commit();
	}


	/**
	 * Get the list of validated renderers for this EmailCampaign. This includes stealth plugins etc.
	 * @return array List of validated renderer codes
	 */
	function get_renderers_validated()
	{
		if( ! isset( $this->renderers_validated ) )
		{
			global $Plugins;
			$this->renderers_validated = $Plugins->validate_renderer_list( $this->get_renderers(), array( 'EmailCampaign' => & $this ) );
		}
		return $this->renderers_validated;
	}


	/**
	 * Get the list of renderers for this Message.
	 * @return array
	 */
	function get_renderers()
	{
		return explode( '.', $this->renderers );
	}


	/**
	 * Set the renderers of the Message.
	 *
	 * @param array List of renderer codes.
	 * @return boolean true, if it has been set; false if it has not changed
	 */
	function set_renderers( $renderers )
	{
		return $this->set_param( 'renderers', 'string', implode( '.', $renderers ) );
	}
}

?>