<?php
/**
 * Syndication_Notifier implements a unified notification mechanism for the syndication plugin.
 *
 * @since 2.1
 * @package Syndication
 */
class Syndication_Notifier {
	/**
	 * Construct
	 *
	 * Syndication_Notifier constructor.
	 *
	 * @since 2.1
	 */
	public function __construct() {
		add_action( 'syn_post_pull_new_post', array( $this, 'notify_new' ), 10, 5 );
		add_action( 'syn_post_pull_edit_post', array( $this, 'notify_update' ), 10, 5 );
		add_action( 'syn_post_push_delete_post', array( $this, 'notify_delete' ), 10, 6 );
		add_action( 'syn_post_push_new_post', array( $this, 'notify_new' ), 10, 5 );
		add_action( 'syn_post_push_edit_post', array( $this, 'notify_update' ), 10, 5 );
	}

	/**
	 * Notify New
	 *
	 * Notify about a new post creation event usually implemented via action hook
	 *
	 * `do_action( 'syn_post_pull_new_post', $result, $post, $site, $transport_type, $client );`
	 * `do_action( 'syn_post_push_new_post', $result, $post_ID, $site, $transport_type, $client, $info );`
	 *
	 * @since 2.1
	 * @param mixed  $result         Result object of previous wp_insert_post action
	 * @param mixed  $post           Post object or post_id
	 * @param object $site           Post object for the site doing the syndication
	 * @param string $transport_type Post meta syn_transport_type for site
	 * @param object $client         Syndication_Client class
	 */
	public function notify_new( $result, $post, $site, $transport_type, $client ) {
		$this->notify_post_event( 'new', $result, $post, $site, $transport_type, $client );
	}

	/**
	 * Notify about a post update event
	 * usually implemented via action hook
	 * do_action( 'syn_post_pull_edit_post', $result, $post, $site, $transport_type, $client );
	 * do_action( 'syn_post_push_edit_post', $result, $post_ID, $site, $transport_type, $client, $info );
	 *
	 * @param  mixed  $result         Result object of previous wp_insert_post action
	 * @param  mixed  $post           Post object or post_id
	 * @param  object $site           Post object for the site doing the syndication
	 * @param  string $transport_type Post meta syn_transport_type for site
	 * @param  object $client         Syndication_Client class
	 */
	public function notify_update( $result, $post, $site, $transport_type, $client ) {
		$this->notify_post_event( 'update', $result, $post, $site, $transport_type, $client );
	}

	/**
	 * Notify about a post delete event
	 * usually implemented via action hook
	 * do_action( 'syn_post_push_delete_post', $result, $ext_ID, $post_ID, $site_ID, $transport_type, $client );
	 *
	 * @param  mixed  $result         Result object of previous wp_insert_post action
	 * @param  mixed  $external_id    External post post_id
	 * @param  mixed  $post           Post object or post_id
	 * @param  object $site           Post object for the site doing the syndication
	 * @param  string $transport_type Post meta syn_transport_type for site
	 * @param  object $client         Syndication_Client class
	 */
	public function notify_delete( $result, $external_id, $post, $site, $transport_type, $client ) {
		$this->notify_post_event( 'delete', $result, $post, $site, $transport_type, $client );
	}

	/**
	 * Prepares data for the post level notify events
	 *
	 * @param  string $event          Type of event new/update/delete
	 * @param  mixed  $result         Result object of previous wp_insert_post action
	 * @param  mixed  $post           Post object or post_id
	 * @param  object $site           Post object for the site doing the syndication
	 * @param  string $transport_type Post meta syn_transport_type for site
	 * @param  object $client         Syndication_Client class
	 */
	private function notify_post_event( $event, $result, $post, $site, $transport_type, $client ) {
		if ( is_int( $post ) ) {
			$post = get_post( $post, ARRAY_A );
		}

		if ( isset( $post['postmeta'] ) && isset( $post['postmeta']['is_update'] ) ) {
			$log_time = $post['postmeta']['is_update'];
		} else {
			$log_time = null;
		}

		$extra = array(
			'post'           => $post,
			'result'         => $result,
			'transpost_type' => $transport_type,
			'client'         => $client
		);

		if ( false == $result || is_wp_error( $result ) ) {
			if ( is_wp_error( $result ) ) {
				$message = $result->get_error_message();
			} else {
				$message = 'fail';
			}
			$this->send_notification( $site->ID, $status = __( esc_attr( $event ), 'push-syndication' ), $message, $log_time, $extra );
		} else {
			$message = sprintf( '%s,%d', sanitize_text_field( $post['post_guid'] ), intval( $result ) );
			$this->send_notification( $site->ID, $status = __( esc_attr( $event ), 'push-syndication' ), $message, $log_time, $extra );
		}
	}

	/**
	 * Send notification
	 *
	 * @param  int    $site_id  site_id the notification is about
	 * @param  string $status   status entry
	 * @param  string $message  log message
	 * @param  string $log_time time of event
	 * @param  array  $extra    additional data
	 *
	 * @return mixed true or WP_Error
	 */
	private function send_notification( $site_id, $status, $message, $log_time, $extra ) {
		return true;
	}
}
