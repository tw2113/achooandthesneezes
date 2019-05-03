<?php

// DO NOT CALL THIS CLASS DIRECTLY. CALL VIA: pb_backupbuddy_destination in bootstrap.php.

class pb_backupbuddy_destination_stash3 { // Change class name end to match destination name.

	const MINIMUM_CHUNK_SIZE = 5; // Minimum size, in MB to allow chunks to be. Anything less will not be chunked even if requested.

	public static $destination_info = array(
		'name'			=>		'BackupBuddy Stash (v3)',
		'description'	=>		'<b>The easiest of all destinations</b> for PHP v5.5+; just enter your iThemes login and Stash away! Store your backups in the BackupBuddy cloud safely with high redundancy and encrypted storage.  Supports multipart uploads for larger file support with both bursting and chunking. Active BackupBuddy customers receive <b>free</b> storage! Additional storage upgrades optionally available. <a href="http://ithemes.com/backupbuddy-stash/" target="_blank">Learn more here.</a>',
		'category'		=>		'normal', // best, normal, legacy
	);

	// Default settings. Should be public static for auto-merging.
	public static $default_settings = array(
		'data_version'				=>		'1',
		'type'						=>		'stash3',	// MUST MATCH your destination slug. Required destination field.
		'title'						=>		'',			// Required destination field.

		'itxapi_username'			=>		'',			// Username to connect to iThemes API.
		'itxapi_token'				=>		'',			// Site token for iThemes API.

		'ssl'						=>		'1',		// Whether or not to use SSL encryption for connecting.
		'server_encryption'			=>		'AES256',	// Encryption (if any) to have the destination enact. Empty string for none.
		'max_time'					=>		'',			// Default max time in seconds to allow a send to run for. Set to 0 for no time limit. Aka no chunking.
		'max_burst'					=>		'10',		// Max size in mb of each burst within the same page load.
		'use_packaged_cert'			=>		'0',		// When 1, use the packaged cacert.pem file included with the AWS SDK.

		'db_archive_limit'			=>		'0',		// Maximum number of db backups for this site in this directory for this account. No limit if zero 0.
		'full_archive_limit' 		=>		'0',		// Maximum number of full backups for this site in this directory for this account. No limit if zero 0.
		'themes_archive_limit' 		=>		'0',
		'plugins_archive_limit' 		=>		'0',
		'media_archive_limit' 		=>		'0',
		'files_archive_limit' 		=>		'0',

		'manage_all_files'			=>		'0',		// Allow user to manage all files in Stash? If enabled then user can view all files after entering their password. If disabled the link to view all is hidden.
		'disable_file_management'	=>		'0',		// When 1, _manage.php will not load which renders remote file management DISABLED.
		'disabled'					=>		'0',		// When 1, disable this destination.
		'skip_bucket_prepare'		=>		'1',		// Always skip bucket prepare for Stash.
		'stash_mode'				=>		'1',		// Master destination is Stash.

		'_multipart_id'				=>		'',			// Instance var. Internal use only for continuing a chunked upload.
	);



	public static function _init( $settings ) {

		require_once( pb_backupbuddy::plugin_path() . '/destinations/s33/init.php' );

		$settings = self::_formatSettings( $settings );
		return $settings;

	} // End _init().



	/*	send()
	 *
	 *	Send one or more files.
	 *
	 *	@param		array			$files			Array of one or more files to send.
	 *	@return		boolean|array					True on success, false on failure, array if a multipart chunked send so there is no status yet.
	 */
	public static function send( $settings = array(), $file, $send_id = '', $delete_after = false, $clear_uploads = false ) {
		require_once( pb_backupbuddy::plugin_path() . '/lib/stash/stash-api.php' );

		pb_backupbuddy::status( 'details', 'Starting stash3 send().' );
		$settings = self::_init( $settings );

		if ( '1' == $settings['disabled'] ) {
			self::_error( __( 'Error #48933: This destination is currently disabled. Enable it under this destination\'s Advanced Settings.', 'it-l10n-backupbuddy' ) );
			return false;
		}
		if ( is_array( $file ) ) {
			$file = $file[0];
		}

		if ( '' == $settings['_multipart_id'] ) { // New transfer. Populate initial Stash settings.
			$result = BackupBuddy_Stash_API::send_file( $settings['itxapi_username'], $settings['itxapi_token'], $file );

			if ( true === $result ) {
				return true;
			}


			$file_size = filesize( $file );
			$remote_path = self::get_remote_path(); // Has leading and trailng slashes.
			$backup_type = backupbuddy_core::getBackupTypeFromFile( $file, $quiet = false, $skip_fileoptions = false );
			if ( '' == $backup_type ) { // unknown backup type
				$backup_type_path = '';
			} else { // known backup type. store in subdir.
				$backup_type_path = $backup_type . '/';
			}

			$additionalParams =array(
				'filename' => $remote_path . $backup_type_path . basename( $file ),
				'size'     => $file_size,
				'timezone' => get_option('timezone_string')
			);
			$stashAction = 'upload';
			$response = self::stashAPI( $settings, $stashAction, $additionalParams );
			if ( ! is_array( $response ) ) {
				$error = 'Error #82333232973: Unable to initiate Stash (v3) upload. Details: `' . $response . '`.';
				self::_error( $error );
				return false;
			}

			if ( pb_backupbuddy::$options['log_level'] == '3' ) { // Full logging enabled.
				pb_backupbuddy::status( 'details', 'Stash API upload action response due to logging level: `' . print_r( $response, true ) . '`. Call params: `' . print_r( $additionalParams, true ) . ' `.' );
			}
			$settings['stash_mode'] = '1'; // Stash is calling the s33 destination.
			$settings['bucket'] = $response['bucket'];
			$settings['credentials'] = $response['credentials'];

			if ( isset( $response['object'] ) ) {
				$settings['_stash_object'] = $response['object'];
			}
			if ( isset( $response['upload_id'] ) ) {
				$settings['_stash_upload_id'] = $response['upload_id'];
			}
		}
		//error_log( print_r( $settings, true ) );

		// Send file.
		$result = pb_backupbuddy_destination_s33::send( $settings, $file, $send_id, $delete_after, $clear_uploads );

		if ( false === $result ) { // Notify Stash if failure.
			self::_uploadFailed( $settings );
		} elseif ( is_array( $result ) ) { // Chunking. Notify Stash API to kick cron.
			self::cron_kick_api( $settings, $blocking = false );
		} elseif ( true === $result ) {

		}

		return $result;

	} // End send().



	public static function test( $settings ) {
		$settings = self::_init( $settings );

		error_log( 'Debug #8393283:' );
		error_log( print_r( $settings, true ) );

		return pb_backupbuddy_destination_s33::test( $settings );

	} // End test().



	/* stashAPI()
	 *
	 * Communicate with the Stash API.
	 *
	 * @param	array 			$settings			Destination settings array.
	 * @param	string			$action				API verb/action to call.
	 * @param	bool			$passthru_errors	When false, we will handle parsing errors here, returning a string error message. When true, pass back the entire array from the server.
	 * @return	array|string						Array with response data on success. String with error message if something went wrong. Auto-logs all errors to status log.
	 */
	public static function stashAPI( $settings, $action, $additionalParams = array(), $blocking = true, $passthru_errors = false ) {
		require_once( pb_backupbuddy::plugin_path() . '/lib/stash/stash-api.php' );

		return BackupBuddy_Stash_API::request( $action, $settings, $additionalParams, $blocking, $passthru_errors );
	} // End stashAPI().



	/* get_quota()
	 *
	 * Get Stash quota.
	 *
	 */
	public static function get_quota( $settings, $bypass_cache = false ) {

		$settings = self::_init( $settings );

		$cache_time = 60*5; // 5 minutes.
		$bypass_cache = true;

		if ( false === $bypass_cache ) {
			$transient = get_transient( 'pb_backupbuddy_stash3quota_' . $settings['itxapi_username'] );
			if ( $transient !== false ) {
				pb_backupbuddy::status( 'details', 'Stash quota information CACHED. Returning cached version.' );
				return $transient;
			}
		} else {
			pb_backupbuddy::status( 'details', 'Stash bypassing cached quota information. Getting new values.' );
		}

		// Contact API.
		$quota_data = self::stashAPI( $settings, 'quota' );

		/*
		echo "QUOTARESULTS:";
		echo '<pre>';
		print_r( $quota_data );
		echo '</pre>';`
		*/

		if ( ! is_array( $quota_data ) ) {
			return false;
		} else {
			set_transient( 'pb_backupbuddy_stash3quota_' . $settings['itxapi_username'], $quota_data, $cache_time );
			return $quota_data;
		}

	} // End get_quota().



	/*	get_remote_path()
	 *
	 *	Returns the site-specific remote path to store into.
	 *	Slashes (caused by subdirectories in url) are replaced with underscores.
	 *	Always has a leading and trailing slash.
	 *
	 *	@return		string			Ex: /dustinbolton.com_blog/
	 */
	public static function get_remote_path( $directory = '' ) {

		$remote_path = str_replace( 'www.', '', site_url() );
		$remote_path = str_ireplace( 'http://', '', $remote_path );
		$remote_path = str_ireplace( 'https://', '', $remote_path );

		//$remote_path = preg_replace('/[^\da-z]/i', '_', $remote_path );

		$remote_path = str_ireplace( '/', '_', $remote_path );
		$remote_path = str_ireplace( '~', '_', $remote_path );
		$remote_path = str_ireplace( ':', '_', $remote_path );

		$remote_path = '/' . trim( $remote_path, '/\\' ) . '/';

		$directory = trim( $directory, '/\\' );
		if ( $directory != '' ) {
			$remote_path .= $directory . '/';
		}

		return $remote_path;

	} // End get_remote_path().



	/*	get_quota_bar()
	 *
	 *	Returns the progress quota bar showing usage.
	 *
	 *	@param		array 			Array of account info from API call.
	 *	@param		string			HTML to append below bar, eg for more options.
	 *	@return		string			HTML for the quota bar.
	 */
	public static function get_quota_bar( $account_info, $settings = array(), $additionalOptions = false ) {

		$settings = self::_init( $settings );
		//echo '<pre>' . print_r( $account_info, true ) . '</pre>';

		$return = '<div class="backupbuddy-stash3-quotawrap">';
		$return .= '
		<style>
			.outer_progress {
				-moz-border-radius: 4px;
				-webkit-border-radius: 4px;
				-khtml-border-radius: 4px;
				border-radius: 4px;

				border: 1px solid #DDD;
				background: #EEE;

				max-width: 700px;

				margin-left: auto;
				margin-right: auto;

				height: 30px;
			}

			.inner_progress {
				border-right: 1px solid #85bb3c;
				background: #8cc63f url("' . pb_backupbuddy::plugin_url() . '/destinations/stash3/progress.png") 50% 50% repeat-x;

				height: 100%;
			}

			.progress_table {
				color: #5E7078;
				font-family: "Open Sans", Arial, Helvetica, Sans-Serif;
				font-size: 14px;
				line-height: 20px;
				text-align: center;

				margin-left: auto;
				margin-right: auto;
				margin-bottom: 20px;
				max-width: 700px;
			}
		</style>';

		if ( isset( $account_info['quota_warning'] ) && ( $account_info['quota_warning'] != '' ) ) {
			//echo '<div style="color: red; max-width: 700px; margin-left: auto; margin-right: auto;"><b>Warning</b>: ' . $account_info['quota_warning'] . '</div><br>';
		}

		$return .= '
		<div class="outer_progress">
			<div class="inner_progress" style="width: ' . $account_info['quota_used_percent'] . '%"></div>
		</div>

		<table align="center" class="progress_table">
			<tbody><tr align="center">
			    <td style="width: 10%; font-weight: bold; text-align: center">Free Tier</td>
			    <td style="width: 10%; font-weight: bold; text-align: center">Paid Tier</td>
			    <td style="width: 10%"></td>
			    <td style="width: 10%; font-weight: bold; text-align: center">Total</td>
			    <td style="width: 10%; font-weight: bold; text-align: center">Used</td>
			    <td style="width: 10%; font-weight: bold; text-align: center">Available</td>
			</tr>

			<tr align="center">
			    <td style="text-align: center">' . $account_info['quota_free_nice'] . '</td>
			    <td style="text-align: center">';
			    if ( $account_info['quota_paid'] == '0' ) {
			    	$return .= 'none';
			    } else {
			    	$return .= $account_info['quota_paid_nice'];
			    }
			    $return .= '</td>
			    <td></td>
			    <td style="text-align: center">' . $account_info['quota_total_nice'] . '</td>
			    <td style="text-align: center">' . $account_info['quota_used_nice'] . ' (' . $account_info['quota_used_percent'] . '%)</td>
			    <td style="text-align: center">' . $account_info['quota_available_nice'] . '</td>
			</tr>
			';
		$return .= '
		</tbody></table>';

		$return .= '<div style="text-align: center;">';
		$return .= '
		<b>' . __( 'Upgrade storage', 'it-l10n-backupbuddy' ) . ':</b> &nbsp;
		<a href="https://ithemes.com/member/cart.php?action=add&id=290" target="_blank" style="text-decoration: none;">+ 5GB</a>, &nbsp;
		<a href="https://ithemes.com/member/cart.php?action=add&id=291" target="_blank" style="text-decoration: none;">+ 10GB</a>, &nbsp;
		<a href="https://ithemes.com/member/cart.php?action=add&id=292" target="_blank" style="text-decoration: none;">+ 25GB</a>

		&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="https://sync.ithemes.com/stash/" target="_blank" style="text-decoration: none;"><b>Manage Stash & Stash Live Files</b></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="https://sync.ithemes.com/stash/" target="_blank" style="text-decoration: none;"><b>Manage Account</b></a>';;

		// Welcome text.
		$up_path = '/';

		$return .= '<br><br></div>';
		$return .= '</div>';

		return $return;

	} // End get_quota_bar().



	/* _formatSettings()
	 *
	 * Called by _formatSettings().
	 *
	 */
	public static function _formatSettings( $settings ) {

		$settings['skip_bucket_prepare'] = '1';
		$settings['stash_mode'] = '1';
		return pb_backupbuddy_destination_s33::_formatSettings( $settings );

	} // End _formatSettings().



	/* listFiles()
	 *
	 * Get list of files with optional prefix. Returns stashAPI response.
	 *
	 */
	public static function listFiles( $settings, $prefix = '', $site_only = false ) {
		require_once( pb_backupbuddy::plugin_path() . '/lib/stash/stash-api.php' );

		$settings = self::_init( $settings );

		if ( $site_only ) {
			$files = BackupBuddy_Stash_API::list_site_files( $settings['itxapi_username'], $settings['itxapi_token'] );
		} else {
			$files = BackupBuddy_Stash_API::list_files( $settings['itxapi_username'], $settings['itxapi_token'] );
		}

		return $files;
	} // End listFiles().



	/* deleteFile()
	 *
	 * Alias to deleteFiles().
	 *
	 */
	public static function deleteFile( $settings, $file ) {
		if ( ! is_array( $file ) ) {
			$file = array( $file );
		}
		return self::deleteFiles( $settings, $file );
	} // End deleteFile().



	/* deleteFiles()
	 *
	 * Delete files.
	 *	@param		array		$settings	Destination settings.
	 *	@return		bool|string				True if success, else string error message.
	 */
	public static function deleteFiles( $settings, $files = array() ) {
		require_once( pb_backupbuddy::plugin_path() . '/lib/stash/stash-api.php' );

		$settings = self::_init( $settings );

		if ( ! is_array( $files ) ) {
			$file = array( $files );
		}

		if ( true === BackupBuddy_Stash_API::delete_files( $settings['itxapi_username'], $settings['itxapi_token'], $files ) ) {
			return true;
		}


		$remote_path = self::get_remote_path(); // Has leading and trailng slashes.
		$additionalParams =array();
		$stashAction = 'manage';
		$manage_data = self::stashAPI( $settings, $stashAction, $additionalParams );
		if ( ! is_array( $manage_data ) ) {
			$error = 'Error #47349723: Unable to initiate file deletion for file(s) `' . implode( ', ', $files ) . '`. Details: `' . print_r( $manage_data, true ) . '`.';
			self::_error( $error );
			return $error;
		}
		$settings['bucket'] = $manage_data['bucket'];
		$settings['credentials'] = $manage_data['credentials'];

		$settings['directory'] = $manage_data['subkey'];
		foreach( $files as &$file ) {
			$file = $remote_path . $file;
		}

		return pb_backupbuddy_destination_s33::deleteFiles( $settings, $files );

	} // End deleteFiles().



	// Called from s33 init.php.
	public static function archiveLimit( $settings, $backup_type ) {
		pb_backupbuddy::status( 'details', 'Starting remote archive limiting. Limiting to `' . $settings['db_archive_limit'] . '` database and `' . $settings['full_archive_limit'] . '` full archives based on destination settings.' );

		$settings = self::_init( $settings );

		$additionalParams = array(
			'types' => array(
						'db' => $settings['db_archive_limit'],
						'full' => $settings['full_archive_limit'],
						'themes' => $settings['themes_archive_limit'],
						'plugins' => $settings['plugins_archive_limit'],
						'media' => $settings['media_archive_limit'],
						'files' => $settings['files_archive_limit'],
						),
			'delete' => true,
		);

		if ( pb_backupbuddy::$options['log_level'] == '3' ) { // Full logging enabled.
			pb_backupbuddy::status( 'details', 'Trim params based on settings: `' . print_r( $additionalParams, true ) . '`.' );
		}

		$response = self::stashAPI( $settings, $stashAction = 'trim', $additionalParams );
		if ( ! is_array( $response ) ) {
			$error = 'Error #8329545445573: Unable to trim Stash (v3) upload. Details: `' . print_r( $response, true ) . '`.';
			self::_error( $error );
			return false;
		} else {
			pb_backupbuddy::status( 'details', 'Trimmed remote archives. Results: `' . print_r( $response, true ) . '`.' );
		}

		return true;

	} // End archiveLimit();



	// find Xth occurance position from the write.
	public static function strrpos_count($haystack, $needle, $count) {

		if ($count <= 0)
			return false;

		$len = strlen($haystack);
		$pos = $len;

		for ($i = 0; $i < $count && $pos; $i++)
			$pos = strrpos($haystack, $needle, $pos - $len - 1);

		return $pos;

	} // End _strrpost_count().



	/* _error()
	 *
	 * Log error into status logger and destination error global. Returns false.
	 *
	 */
	private static function _error( $message ) {

		global $pb_backupbuddy_destination_errors;
		$pb_backupbuddy_destination_errors[] = $message;
		pb_backupbuddy::status( 'error', 'Error #3892343283: ' . $message );
		return false;

	}



	/* _uploadFailed()
	 *
	 * Reports to the Stash API that the upload failed.
	 * @return	bool		True if reporting failure succeeded, else false.
	 *
	 */
	public static function _uploadFailed( $settings ) {

		pb_backupbuddy::status( 'details', 'Notifying Stash of upload failure.' );
		$additionalParams =array(
			'upload_id' => $settings['_stash_upload_id'],
		);
		$failResult = self::stashAPI( $settings, 'upload-fail', $additionalParams );
		if ( ( ! isset( $failResult['success'] ) || ( true !== $failResult['success'] ) ) ) {
			pb_backupbuddy::status( 'error', 'Warning #32736326: Error notifying Stash of upload failure. Details: `' . print_r( $failResult, true ) . '`.' );
			return false;
		} else {
			pb_backupbuddy::status( 'details', 'Stash notified of upload fail.' );
			return true;
		}

	} // End _uploadFailed().



	/* cron_kick_api()
	 *
	 * Instructs Stash API to kick our cron at this URL. Note: If $blocking === false then the HTTP request is non-blocking and we will get no response logged.
	 *
	 */
	public static function cron_kick_api( $settings, $blocking = true ) {
		$activity_time_file = backupbuddy_core::getLogDirectory() . 'live/cron_kick-' . pb_backupbuddy::$options['log_serial'] . '.txt';

		if ( file_exists( $activity_time_file ) ) {
			if ( false !== ( $mtime = @filemtime( $activity_time_file ) ) ) {
				$ago = ( time() - $mtime );
				if ( $ago < backupbuddy_constants::MINIMUM_CRON_KICK_INTERVAL ) { // Trying to kick too soon.
					pb_backupbuddy::status( 'details', 'cron-kick API call too soon (' . pb_backupbuddy::$format->time_ago( $mtime ) . ' ago). Skipping for now.' );
					return true;
				}
			}
		}

		@touch( $activity_time_file );

		$response = pb_backupbuddy_destination_stash3::stashAPI( $settings, 'cron-kick', array(), $blocking );
		if ( false === $blocking ) {
			return true;
		}
		if ( ! is_array( $response ) ) { // Error message.
			pb_backupbuddy::status( 'error', 'Error #3279723: Unexpected server response. Detailed response: `' . print_r( $response, true ) .'`.' );
		} else { // Errors.
			if ( isset( $response['error'] ) ) {
				pb_backupbuddy::status( 'error', $response['error']['message'] );
			} else { // No error?
				if ( ! isset( $response['success'] ) || ( '1' != $response['success'] ) ) {
					pb_backupbuddy::status( 'error', 'Error #3289327932: Something went wrong. Success was not reported. Detailed response: `' . print_r( $response, true ) .'`.' );
				} else {
					pb_backupbuddy::status ('details', 'Successfully inititated cron kicker with API.' );
					return true;
				}
			}
		}
		return false;
	}



} // End class.


