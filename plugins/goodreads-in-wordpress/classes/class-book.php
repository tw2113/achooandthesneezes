<?php
/**
 * Book renderer.
 *
 * @package Goodreads Widget
 */

namespace tw2113;

/**
 * Class Book
 */
class Book {

	/**
	 * Book title.
	 *
	 * @var mixed|string
	 * @since 1.0.0
	 */
	protected $title = '';

	/**
	 * Book image.
	 *
	 * @var mixed|string
	 * @since 1.0.0
	 */
	protected $image = '';

	/**
	 * Book URL.
	 *
	 * @var mixed|string
	 * @since 1.0.0
	 */
	protected $link = '';

	/**
	 * Book constructor.
	 *
	 * @param array $args Array of arguments for the book.
	 */
	public function __construct( $args = [] ) {
		$this->title = $args['title'];
		$this->image = $args['image'];
		$this->link  = $args['link'];
	}

	/**
	 * Formats a user badge URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_book_linked_title() {
		$tmpl = '<p><a href="%s">%s</a></p>';

		return sprintf(
			$tmpl,
			esc_url( $this->link ),
			esc_html( $this->title )
		);
	}

	/**
	 * Return full markup for a given book.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|string
	 */
	public function get_book_markup() {
		$markup  = $this->get_book_img();
		$markup .= $this->get_book_linked_title();

		return $markup;
	}

	/**
	 * Returns a given user badge image URL.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_book_img() {
		$tmpl = '<img src="%s" alt="%s" />';

		return sprintf(
			$tmpl,
			esc_url( $this->image ),
			sprintf(
				/* Translators: placeholder will be the book name. */
				esc_attr__( 'Cover for %s', 'mb_goodreads' ),
				$this->title
			)
		);
	}
}
