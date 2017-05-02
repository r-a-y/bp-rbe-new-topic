<?php

namespace BP_RBE_New_Topic;

use BP_RBE_New_Topic\Init;
use BP_RBE_New_Topic\Get;
use BP_Groups_Group;

/**
 * RBE parser integration.
 *
 * @since 0.1
 */
class Parser extends Init {
	/**
	 * Hooks.
	 *
	 * @since 0.1
	 */
	protected function hooks() {
		add_filter( 'bp_rbe_is_new_item',          array( $this, 'alter_new_item_check' ), 10, 2 );
		add_filter( 'bp_rbe_get_querystring',      array( $this, 'parse_group_topic_address' ), 10, 2 );
		add_filter( 'bp_rbe_new_item_querystring', array( $this, 'set_new_topic_querystring' ) );
	}

	/**
	 * Force RBE's new item check to true when custom group topic email is in use.
	 *
	 * @since 0.1
	 *
	 * @param  bool   $retval Current boolean.
	 * @param  string $qs     Current querystring.
	 * @return bool
	 */
	public function alter_new_item_check( $retval, $qs ) {
		$prefix = Get::mailbox_prefix();
		if ( ! empty( $prefix ) && 0 === strpos( $qs, Get::mailbox_prefix() ) ) {
			$retval = true;
		}

		// Using an empty prefix so check if group slug exists.
		if ( empty( $prefix ) ) {
			// Group exists, so set new itme check to true.
			if ( $this->set_new_topic_querystring( $qs ) !== $qs ) {
				$retval = true;
			}
		}

		return $retval;
	}

	/**
	 * In IMAP mode, force querystring to use custom group topic email mailbox.
	 *
	 * @since 0.1
	 *
	 * @param  string|false $qs      Current querystring.
	 * @param  string       $address Full 'to' email address. 
	 * @return string|false
	 */
	public function parse_group_topic_address( $retval, $address ) {
		if ( bp_rbe_is_inbound() ) {
			return $retval;
		}

		if ( false !== $retval ) {
			return $retval;
		}

		$prefix = Get::mailbox_prefix();

		// Prefix exists, so check if querystring has it. If not, bail.
		if ( ! empty( $prefix ) && 0 !== strpos( $address, Get::mailbox_prefix() ) ) {
			return $retval;

		// No prefix, so verify group slug. If group slug doesn't exist, bail.
		} elseif ( empty( $prefix ) ) {
			$slug = substr( $address, 0, strpos( $address, '@' ) );
			if ( $this->set_new_topic_querystring( $slug ) === $slug ) {
				return $retval;
			}
		}

		return substr( $address, 0, strpos( $address, '@' ) );
	}

	/**
	 * Sets new topic querystring for our custom group topic email address.
	 *
	 * Since our querystring will look like this 'group-my-group', we need to
	 * transform this into RBE's bbPress' new topic querystring, which resembles
	 * this: 'bbpg=0&bbpf=0'. 'bbpg' is the group ID and 'bbpf' is the bbPress
	 * forum ID.
	 *
	 * @since 0.1
	 *
	 * @param  string $qs Current querystring.
	 * @return string
	 */
	public function set_new_topic_querystring( $qs ) {
		$prefix = Get::mailbox_prefix();
		if ( ! empty( $prefix ) && 0 !== strpos( $qs, $prefix ) ) {
			return $qs;
		}

		$mailbox  = ! empty( $prefix ) ? str_replace( $prefix, '', $qs ) : $qs;
		$group_id = $forum_id = 0;

		// Check if a group has a customized mailbox.
		$custom_mailbox = BP_Groups_Group::get( array(
			'update_meta_cache' => false,
			'populate_extras' => false,
			'show_hidden' => true,
			'meta_query' => array( array(
				'key'   => 'bp_rbe_new_topic_mailbox',
				'value' => $mailbox
			) )
		) );
	
		// We have a custom mailbox!
		if ( $custom_mailbox['total'] > 0 ) {
			$group_id = $custom_mailbox['groups'][0]->id;

		// Fallback to group slug check.
		} else {
			$group_id = BP_Groups_Group::group_exists( $mailbox );
	
			if ( is_null( $group_id ) ) {
				return $qs;
			}
		}

		// No group, so bail.
		if ( empty( $group_id ) ) {
			return $qs;
		}

		// Get bbPress forum ID associated with the group.
		$forum_ids = bbp_get_group_forum_ids( $group_id );
		if ( ! empty( $forum_ids ) ) {
			$forum_id = (int) ( is_array( $forum_ids ) ? $forum_ids[0] : $forum_ids );
		}

		// No forum attached to group, so bail.
		if ( empty( $forum_id ) ) {
			return $qs;
		}

		// Set up our parameters.
		$params = array(
			'bbpg' => $group_id,
			'bbpf' => $forum_id
		);

		return http_build_query( $params );
	}
}