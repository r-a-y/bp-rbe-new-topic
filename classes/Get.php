<?php

namespace BP_RBE_New_Topic;

/**
 * Getter.
 *
 * @since 0.1
 */
class Get {
	/**
	 * Constructor. Intentionally left blank.
	 *
	 * @since 0.1
	 */
	protected function __construct() {}

	/**
	 * Fetch the mailbox used for our custom group new topic email address.
	 *
	 * Will try and use the custom mailbox as set in the group admin area. If not
	 * set, will fallback to the group slug. For example, in
	 * 'group-my-forum@x.com', 'my-forum' is the mailbox.
	 *
	 * @since 0.1
	 *
	 * @param  int $group_id Group ID.
	 * @return string
	 */
	public static function mailbox( $group_id = 0 ) {
		if ( 0 === $group_id ) {
			$group_id = bp_get_current_group_id();
		}

		$mailbox = groups_get_groupmeta( $group_id, 'bp_rbe_new_topic_mailbox' );

		// No custom mailbox value; fallback to group slug for the mailbox.
		if ( '' === $mailbox ) {
			$mailbox = groups_get_group( array( 'group_id' => $group_id ) )->slug;
		}

		return $mailbox;
	}

	/**
	 * Fetch the mailbox prefix used for our custom group topic email address.
	 *
	 * This prefix is prepended to the mailbox to generate the full group topic
	 * email address.  For example, in 'group-my-forum@x.com', 'group-' is the
	 * mailbox prefix.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public static function mailbox_prefix() {
		/**
		 * Filter the group new topic prefix.
		 *
		 * Custom group new topic email addresses begin with 'group-' by default.
		 *
		 * @since 0.1
		 *
		 * @param string $prefix Mailbox prefix. Default: 'group-'.
		 */
		return (string) apply_filters( 'bp_rbe_new_group_topic_email_address_prefix', 'group-' );
	}
}