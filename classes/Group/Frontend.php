<?php

namespace BP_RBE_New_Topic\Group;

use BP_RBE_New_Topic\Get;
use BP_RBE_New_Topic\Init;

/**
 * Frontend group intergration.
 *
 * @since 0.1
 */
class Frontend extends Init {
	/**
	 * Hooks
	 *
	 * @since 0.1
	 */
	protected function hooks() {
		/**
		 * Filter to display the new group email address on the frontend.
		 *
		 * @since 0.1
		 *
		 * @param  bool $retval Defaults to true.
		 * @return bool
		 */
		$enable = apply_filters( 'bp_rbe_enable_frontend_group_email_address', true );
		if ( true !== $enable ) {
			return;
		}

		add_filter( 'bp_rbe_encode_group_querystring', array( $this, 'use_group_mailbox' ), 30, 3 );
		add_filter( 'bp_rbe_inject_qs_in_email',       array( $this, 'alter_email_address' ) );

		add_action( 'wp_head',  array( $this, 'inline_css' ) );
	}

	/**
	 * Use our custom group topic mailbox for our new topic email address.
	 *
	 * @since 0.1
	 *
	 * @param  string $retval   Current email address.
	 * @param  int    $user_id  User ID. Not used in this filter.
	 * @param  int    $group_id Group ID.
	 * @return string
	 */
	public function use_group_mailbox( $retval, $user_id, $group_id ) {
		// Sanity check!
		if ( ! did_action( 'wp_head' ) ) {
			return $retval;
		}

		return Get::mailbox_prefix() . Get::mailbox( $group_id );
	}

	/**
	 * Alters the new topic email address to use our custom mailbox.
	 *
	 * In Inbound mode, we remove the '-new' string.  In IMAP mode, we
	 * completely change the email address to use inbound mode's email address.
	 *
	 * @since 0.1
	 *
	 * @param  string $retval Current querystring to inject into email address.
	 * @return string
	 */
	public function alter_email_address( $retval ) {
		// Sanity check!
		if ( ! did_action( 'wp_head' ) ) {
			return $retval;
		}

		if ( bp_rbe_is_inbound() ) {
			$retval = str_replace( '-new', '', $retval );
		} else {
			$retval = Get::mailbox_prefix() . Get::mailbox() . '@' . bp_rbe_get_setting( 'inbound-domain' );
		}

		/**
		 * Filters the new topic email address for the displayed group.
		 *
		 * Handy if you wanted to use a separate inbound domain for new group topics.
		 *
		 * @param  string $retval Current new topic email address.
		 * @return string
		 */
		return apply_filters( 'bp_rbe_group_email_address', $retval );
	}

	public function inline_css() {
		$css = <<<EOD
#rbe-message p:last-child {display:none;}
EOD;

		printf( '<style type="text/css">%s</style>', $css );
	}
}