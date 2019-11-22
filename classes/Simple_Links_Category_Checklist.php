<?php

/**
 * Simple Links Category Checklist
 *
 * Custom category walker to use the term names as values instead of term ids
 *
 * @uses    Walker_Category_Checklist
 * @uses    Walker
 *
 * @class   Simple_Links_Category_Checklist
 * @package Simple Links
 *
 */
class Simple_Links_Category_Checklist extends Walker_Category_Checklist {

	/**
	 * Widget
	 *
	 * Name to use with the widget inputs
	 *
	 * @var string
	 */
	private $widget;

	/**
	 * Selected
	 *
	 * Selected categories by name
	 *
	 * @var array
	 */
	private $selected = array();


	/**
	 * Constructor
	 *
	 * @param string [$widget] - widget input name
	 * @param array  [$selected] - selected categories
	 */
	function __construct( $widget = null, $selected = array() ) {
		$this->widget   = $widget;
		$this->selected = $selected;

	}


	/**
	 * Start the element output.
	 *
	 * @param array $args wp_terms_checklist args with an additional $widget to allow for sending a name
	 *
	 * @see wp_terms_checklist()
	 *
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		extract( $args );

		if ( ! empty( $this->widget ) ) {
			$name = $this->widget . '[' . $category->term_id . ']';
		} else {
			$name = '';
		}

		$output .= '<li>
						<label class="selectit">
							<input class="cat" value="' . $category->term_id . '" type="checkbox" name="' . $name . '"' . checked( in_array( $category->term_id, $this->selected ), 1, 0 ) . ' /> ' . $category->name . '</label>
					</li>';

	}
}

