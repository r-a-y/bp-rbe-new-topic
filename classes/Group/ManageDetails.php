<?php

namespace BP_RBE_New_Topic\Group;

use BP_RBE_New_Topic\Init;
use BP_RBE_New_Topic\Get;
use BP_Groups_Group;

/**
 * Group admin integration into "Manage > Details" page.
 *
 * @since 0.2
 */
class ManageDetails extends Init {
	/**
	 * Hooks.
	 *
	 * @since 0.2
	 */
	protected function hooks() {
		add_action( 'wp_head', array( $this, 'inline_css' ) );
		add_action( 'groups_group_details_edited', array( $this, 'validate' ), 0 );
		add_action( 'groups_custom_group_fields_editable', array( $this, 'content' ), 0 );
		add_action( 'template_notices', array( $this, 'notice' ), 20 );
	}

	/**
	 * Inline CSS.
	 *
	 * @since 0.2
	 */
	public function inline_css() {
		$css = <<<EOD
#new-topic {
	margin-bottom: 1em;
}
#new-topic input {
	float: left;
}
#new-topic label {
	line-height: 2.5;
}
#buddypress #new-topic .prefix,
#content #new-topic .prefix {
	width: 3.3em !important;
	opacity: 1;
	border-right: 0;
	border-top-right-radius: 0;
	border-bottom-right-radius: 0;
	padding-right: 0;
}
#buddypress #mailbox,
#content #mailbox {
	width: auto !important;
}
#buddypress .prefix + .mailbox-with-prefix,
#content .prefix + .mailbox-with-prefix {
	border-left: 0;
	border-top-left-radius: 0;
	border-bottom-right-radius: 0;
	padding-left: 0;
}
#buddypress .new-topic-save,
#content .new-topic-save {
	clear: both;
	margin: 1.5em 0 3em;
}
#buddypress #new-topic:after,
#content #new-topic:after {
	clear: both;
	content: "";
	display: table;
}
#buddypress .standard-form p label {
	margin-left: initial;
}
EOD;

		echo "<style type=\"text/css\">{$css}</style>";
	}

	/**
	 * Validate method.
	 *
	 * @since 0.2
	 */
	public function validate() {
		$error = false;

		$mailbox = sanitize_title( $_POST['mailbox'] );

		// Empty submission.
		if ( empty( $mailbox ) ) {
			$error   = true;
			$message = __( 'Custom group email address cannot be empty.', 'bp-rbe-new-topic' );

		// Check if mailbox doesn't match current group slug or any group slug.
		} elseif ( $mailbox !== groups_get_current_group()->slug && BP_Groups_Group::group_exists( $mailbox ) ) {
			$error   = true;
			$message = __( 'Custom group email address cannot be the same as an existing group\'s slug. Please think of another nickname.', 'bp-rbe-new-topic' );

		// Check if mailbox matches any customized mailbox; if so, throw error.
		} else {
			// Check if a group has the same customized mailbox.
			$custom_mailbox = BP_Groups_Group::get( array(
				'update_meta_cache' => false,
				'populate_extras' => false,
				'show_hidden' => true,
				'meta_query' => array( array(
					'key'   => 'bp_rbe_new_topic_mailbox',
					'value' => $mailbox
				) )
			) );

			// Mailbox already in use.
			if ( $custom_mailbox['total'] > 0 && Get::mailbox() !== $mailbox ) {
				$error   = true;
				$message = __( 'Custom group email address is already in use. Please think of another nickname.', 'bp-rbe-new-topic' );
			} else {
				$message = '';
			}
		}

		// All good!
		if ( false === $error ) {
			groups_update_groupmeta( bp_get_current_group_id(), 'bp_rbe_new_topic_mailbox', $mailbox );
		}

		/*
		 * Save template message into groupmeta temporarily.
		 *
		 * We don't want to stamp over the existing bp_core_add_message() block from
		 * groups_screen_group_admin_edit_details().
		 */
		if ( ! empty( $message ) ) {
			groups_update_groupmeta( bp_get_current_group_id(), 'bp_rbe_message', array(
				'error'   => $error,
				'message' => $message
			) );
		}
	}

	/**
	 * Custom notice output.
	 *
	 * @since 0.2
	 */
	public function notice() {
		$notice = groups_get_groupmeta( bp_get_current_group_id(), 'bp_rbe_message' );
		if ( empty( $notice ) ) {
			return;
		}

		groups_delete_groupmeta( bp_get_current_group_id(), 'bp_rbe_message' );

		$type = ! empty( $notice['error'] ) ? 'error' : 'updated';
	?>

		<div id="message" class="bp-template-notice <?php echo esc_attr( $type ); ?>">
			<p><?php echo $notice['message']; ?></p>
		</div>

	<?php
	}

	/**
	 * Content method.
	 *
	 * @since 0.2
	 */
	public function content() {
		$prefix = Get::mailbox_prefix();
		$mailbox_class = ! empty( $prefix ) ? ' class="mailbox-with-prefix"' : '';

		$desc = $this->description();
	?>

		<div id="new-topic">
			<label for="mailbox"><?php esc_html_e( 'New Topic Email Address', 'bp-rbe-new-topic' ); ?></label>

			<?php echo $desc ? $desc : ''; ?>

			<?php if ( ! empty( $prefix ) ) : ?>
				<input class="prefix" name="mailbox-prefix" type="text" value="<?php esc_attr_e( Get::mailbox_prefix() ); ?>" readonly="readonly" onclick="document.getElementById('mailbox').select(); return false;" />
			<?php endif; ?>

			<input id="mailbox" name="mailbox" type="text" value="<?php esc_attr_e( Get::mailbox() ); ?>" placeholder="<?php esc_attr_e( Get::mailbox() ); ?>" onclick="this.select()"<?php echo $mailbox_class; ?>/>
			<label for="mailbox">@<?php echo bp_rbe_get_setting( 'inbound-domain' ); ?></label>
		</div>

	<?php
	}

	/**
	 * Helper method to output description in content() method.
	 *
	 * @since 0.2
	 */
	public function description() {
		/**
		 * Filters the description in the admin area.
		 *
		 * @since 0.1
		 *
		 * @param string $desc Description
		 */
		$desc = apply_filters( 'bp_rbe_new_topic_group_admin_desc', sprintf( '<p>%s</p>', __( 'Customize the new topic email address for your group.', 'bp-rbe-new-topic' ) ) );

		return $desc;
	}
}