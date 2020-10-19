<?php
/**
 * The public-facing functionality of the plugin.
 */

namespace Quick_And_Easy_FAQs\Frontend;

use Quick_And_Easy_FAQs\Includes\FAQs_Query;
use Quick_And_Easy_FAQs\Includes\Utilities;

class Shortcode extends Utilities {

	/**
	 * The ID of this plugin.
	 *
	 * @var string $plugin_name
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string $version
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @var string $plugin_name
	 * @var string $version
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name          = $plugin_name;
		$this->version              = $version;
		$this->shortcode_being_used = false;
	}

	/**
	 * Check if old short code is used.
	 *
	 * @param  array $attr short code attr values.
	 *
	 * @return array Modified array is user using old short codes.
	 */
	public function old_shortcode_fallback( $attr ) {

		if ( isset( $attr['style'] ) && 'filterable-toggle' === $attr['style'] ) {
			$attr['style']  = 'toggle';
			$attr['filter'] = true;
		}

		if ( isset( $attr['style'] ) && 'toggle' === $attr['style'] && isset( $attr['grouped'] ) && 'yes' === $attr['grouped'] ) {
			$attr['style'] = 'toggle-grouped';
			unset( $attr['grouped'] );
		}

		if ( isset( $attr['grouped'] ) && 'yes' === $attr['grouped'] ) {
			$attr['style'] = 'grouped';
			unset( $attr['grouped'] );
		}

		if ( isset( $attr['filter'] ) && 'group-slug' === $attr['filter'] ) {
			$attr['filter'] = true;
		}

		return $attr;
	}

	/**
	 * Display faqs in a list
	 *
	 * @param  array $attributes short code attr values.
	 *
	 * @return bool true on success or false on failure.
	 */
	public function display_faqs_list( $attributes ) {

		$attributes = $this->old_shortcode_fallback( $attributes );

		extract(
			shortcode_atts(
				array(
					'style'     => '',    // Possible Values: toggle, accordion, toggle-grouped, accordion-grouped
					'filter'    => false,
					'orderby'   => 'date',
					'order'     => 'DESC',
					'linksonly' => false
				),
				$attributes,
				'faqs'
			)
		);

		// faq groups filter.
		if ( isset( $filter ) && ! empty( $filter ) && 'true' != $filter ) {
			$filter = explode( ',', $filter );
		}

		ob_start();

		$faqs_query = new FAQs_Query( $style, $filter, $orderby, $order, $linksonly );
		$faqs_query->render();

		wp_localize_script(
			$this->plugin_name,
			'qaef_object',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'style'   => $style,
			)
		);
		wp_enqueue_script( $this->plugin_name );

		return ob_get_clean();

	}

	/**
	 * Register FAQs short codes
	 */
	public function register_faqs_shortcodes() {
		add_shortcode( 'faqs', array( $this, 'display_faqs_list' ) );
	}

	/**
	 * Integrate shortcode with Visual Composer
	 */
	public function integrate_shortcode_with_vc() {

		vc_map(
			array(
				'name'        => __( 'Quick and Easy FAQs', 'quick-and-easy-faqs' ),
				'description' => __( 'Quick and Easy FAQs Plugin', 'quick-and-easy-faqs' ),
				'base'        => 'faqs',
				'category'    => __( 'Content', 'quick-and-easy-faqs' ),
				'params'      => array(
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Display Style', 'quick-and-easy-faqs' ),
						'param_name'  => 'style',
						'value'       => array(
							'Default'           => '',
							'Toggle'            => 'toggle',
							'Accordion'         => 'accordion',
							'Grouped'           => 'grouped',
							'Toggle Grouped'    => 'toggle-grouped',
							'Accordion Grouped' => 'accordion-grouped',
						),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'FAQs Filters', 'quick-and-easy-faqs' ),
						'param_name'  => 'filter',
						'value'       => array(
							'Default' => '',
							'Yes'     => 'true',
							'No'      => 'false',
						),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'FAQs Order', 'quick-and-easy-faqs' ),
						'param_name'  => 'order',
						'value'       => array(
							'Default'    => '',
							'Ascending'  => 'ASC',
							'Descending' => 'DESC',
						),
						'admin_label' => true,
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'FAQs Order By', 'quick-and-easy-faqs' ),
						'param_name'  => 'orderby',
						'value'       => array(
							'Default'  => '',
							'ID'       => 'ID',
							'Author'   => 'author',
							'Title'    => 'title',
							'Name'     => 'name',
							'Type'     => 'type',
							'Date'     => 'date',
							'Modified' => 'modified',
							'Parent'   => 'parent',
							'Random'   => 'rand',
						),
						'admin_label' => true,
					),
				),
			)
		);

	}
}
