<?php
/**
 * Goodreads Current Reading Widget.
 *
 * @package Goodreads
 * @subpackage Widgets
 * @since   1.0.0
 */

namespace tw2113;

/**
 * Extend our class and create our new widget.
 *
 * @since 1.0.0
 */
class Goodreads_Current_Reading_Widget extends Goodreads_Base_Widget {

	/**
	 * Saved settings.
	 *
	 * @var array|mixed|void
	 */
	protected $goodreads_settings = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = [
			'classname'   => '',
			'description' => esc_html__( 'Display current reading list', 'mb_goodreads' ),
		];
		parent::__construct( 'goodreads_current_reading', esc_html__( 'Goodreads Current Reading', 'mb_goodreads' ), $widget_ops );

		$this->goodreads_settings = get_option( 'mb_goodreads_settings', [] );
	}

	/**
	 * Form method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function form( $instance = [] ) {

		$defaults = [
			'title' => esc_html__( 'My current reading', 'mb_goodreads' ),
			'limit' => '2',
		];
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title    = trim( strip_tags( $instance['title'] ) );
		$limit    = trim( strip_tags( $instance['limit'] ) );

		printf(
			'<p>%s</p>',
			esc_html__( 'Note: order will be most recently added first.', 'mb_goodreads' )
		);

		$this->form_input(
			[
				'label' => esc_html__( 'Title:', 'mb_goodreads' ),
				'name'  => $this->get_field_name( 'title' ),
				'id'    => $this->get_field_id( 'title' ),
				'type'  => 'text',
				'value' => $title,
			]
		);

		$this->form_input(
			[
				'label' => esc_html__( 'Limit:', 'mb_goodreads' ),
				'name'  => $this->get_field_name( 'limit' ),
				'id'    => $this->get_field_id( 'limit' ),
				'type'  => 'number',
				'value' => $limit,
			]
		);
	}

	/**
	 * Update method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New widget instance.
	 * @param array $old_instance Old widget instance.
	 * @return array
	 */
	public function update( $new_instance = [], $old_instance = [] ) {
		$instance          = $old_instance;
		$instance['title'] = trim( strip_tags( $new_instance['title'] ) );
		$instance['limit'] = trim( strip_tags( $new_instance['limit'] ) );

		if ( $new_instance['limit'] !== $old_instance['limit'] ) {
			$this->clearTransient();
		}

		return $instance;
	}

	/**
	 * Widget display method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args = [], $instance = [] ) {

		$title   = trim( strip_tags( $instance['title'] ) );
		$user_id = ( ! empty( $this->goodreads_settings['user_id'] ) ) ? trim( strip_tags( $this->goodreads_settings['user_id'] ) ) : '';
		$api_key = ( ! empty( $this->goodreads_settings['client_id'] ) ) ? trim( strip_tags( $this->goodreads_settings['client_id'] ) ) : '';
		$limit   = ( ! empty( $instance['limit'] ) ) ? $instance['limit'] : '2';

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		$error = $this->maybe_display_errors( $user_id, $api_key );

		if ( false === $error ) {
			$transient  = apply_filters( 'current_reading_filter', 'goodreads_current_reading_' . $user_id );
			$trans_args = [
				'transient_name' => $transient,
				'user_id'        => $user_id,
				'api_key'        => $api_key,
				'limit'          => $limit,
			];
			$books      = $this->getTransient( $trans_args );

			if ( is_wp_error( $books ) ) {
				echo $books->get_error_message();
			} else {
				if ( $books && is_string( $books ) ) {
					/**
					 * Filters the list of classes to apply to our widget output.
					 *
					 * @since 1.0.0
					 *
					 * @param array  $value Array of classes to use.
					 */
					$classes   = implode( ', ', apply_filters( 'goodreads_current_reading_classes', [ 'currently-reading' ] ) );
					$book_xml  = simplexml_load_string( $books );
					$book_data = [
						'books'   => $book_xml->reviews,
						'classes' => $classes,
					];

					/**
					 * Filters the markup to use for the output.
					 *
					 * @since 1.0.0
					 *
					 * @param array $book_data Data for our books.
					 */
					$current_reading_markup = apply_filters( 'current_reading_markup', '', $book_data );

					echo ( '' !== $current_reading_markup ) ? $current_reading_markup : $this->books( $book_data, $trans_args['user_id'] );

				} else {
					echo '<p>' . esc_html__( 'Nothing to display yet', 'mb_goodreads' ) . '</p>';
				}
			}
		}
		echo $args['after_widget'];
	}

	/**
	 * Render our badge.
	 *
	 * @since 1.0.0
	 *
	 * @param array $bookdata Array of data for a badge.
	 * @return string $value Rendered list of brews.
	 */
	public function books( $bookdata ) {
		$mybooks = [];
		if ( is_object( $bookdata['books'] ) && $bookdata['books']->count() > 0 ) {
			foreach ( $bookdata['books']->review as $abook ) {
				$mybooks[ $abook->book->id->__tostring() ] = [
					'link'  => $abook->book->link,
					'title' => $abook->book->title,
				];
			}
		}

		$books_start = '<div><ul>';
		$books_end   = '</ul></div>';
		$book_start  = sprintf(
			'<div class="%s">',
			$bookdata['classes']
		);
		$book_end    = '</div>';
		$books       = '';

		foreach ( $mybooks as $mybook ) {
			$book_obj = new Book( $mybook );
			$books   .= sprintf(
				'<li>%s%s%s</li>',
				$book_start,
				$book_obj->get_book_markup(),
				$book_end
			);
		}
		$books .= sprintf(
			'<p><small>%s</small></p>',
			sprintf(
				/* Translators: placeholder will hold a link to Goodreads.com */
				esc_html__( 'All data provided by %s', 'mb_goodreads' ),
				'<a href="https://www.goodreads.com">Goodreads.com</a>'
			)
		);

		return $books_start . $books . $books_end;
	}

	/**
	 * Retrieve our Goodreads API data, from a transient first, if available.
	 *
	 * @since 1.0.0
	 *
	 * @param array $trans_args Array of transient name, username, Goodreads API credentials, and listing limit.
	 * @return array|\WP_Error Data from Goodreads
	 */
	public function getTransient( $trans_args = [] ) {
		$thebooks = get_transient( $trans_args['transient_name'] );
		if ( false === $thebooks ) {
			$current_reading = new Current_Reading_Shelf_API(
				[
					'api_key' => $trans_args['api_key'],
					'user_id' => $trans_args['user_id'],
					'limit'   => $trans_args['limit'],
				]
			);

			$books = $current_reading->get_books();

			/**
			 * Filters the duration to store our transients.
			 *
			 * @since 1.0.0
			 *
			 * @param int $value Time in seconds.
			 */
			$duration = apply_filters( 'goodreads_transient_duration', 60 * 10 );

			// Save only if we get a good response back.
			if ( 200 === wp_remote_retrieve_response_code( $books ) ) {
				$thebooks = wp_remote_retrieve_body( $books );
				set_transient( $trans_args['transient_name'], $thebooks, $duration );
			} else {
				if ( current_user_can( 'manage_options' ) ) {
					if ( is_array( $books ) && isset( $books['error'] ) ) {
						$message = $books['error'];
					} else {
						$message = $books->get_error_message();
					}

					return new \WP_Error(
						'admin_only_error',
						sprintf(
							/* Translators: placeholder will hold Goodreads API error message. */
							esc_html__( 'Admin-only error: %s', 'mb_goodreads' ),
							esc_html( $message )
						)
					);
				}
			}
		}

		return $thebooks;
	}

	/**
	 * Clear out our transient as needed, like if the widget limit changes.
	 *
	 * @since 1.0.0
	 */
	public function clearTransient() {
		$user_id = ( ! empty( $this->goodreads_settings['user_id'] ) ) ? trim( strip_tags( $this->goodreads_settings['user_id'] ) ) : '';
		if ( ! empty( $user_id ) ) {
			delete_transient( apply_filters( 'current_reading_filter', 'goodreads_current_reading_' . $user_id ) );
		}
	}
}
