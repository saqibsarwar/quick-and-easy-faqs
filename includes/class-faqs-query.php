<?php

namespace Quick_And_Easy_FAQs\Includes;

use Quick_And_Easy_FAQs\Includes\Utilities;

/**
 * This class holds all the faqs query related members and methods.
 */
class FAQs_Query extends Utilities
{

    /**
     * Holds the query of faqs.
     *
     * @var array $faqs_query
     */
    protected $faqs_query;

    /**
     * Holds the value of display type of faqs.
     *
     * @var string $display
     */
    protected $display;

    /**
     * The filters of this plugin.
     *
     * @var bool | array $filters
     */
    protected $filters;

    /**
     * The faqs order by.
     *
     * @var string $orderby
     */
    protected $orderby;

    /**
     * The faqs order.
     *
     * @var string $order
     */
    protected $order;

    /**
     * Initialize the class and set its properties.
     *
     * @param string       $display Display type of plugin.
     * @param bool | array $filters The filters of this plugin.
     * @param string       $orderby FAQs posts order by.
     * @param string       $order FAQs posts order.
     * @param bool         $linksonly Should only links be shown.
     */
    public function __construct($display = '', $filters = false || array(), $orderby = 'date', $order = 'DESC', $linksonly = false)
    {
        $this->display   = $display;
        $this->filters   = $filters;
        $this->orderby   = $orderby;
        $this->order     = $order;
        $this->linksonly = $linksonly;

        if ($this->filters && ! is_array($this->filters)) {
            $terms = get_terms(
                array(
                    'taxonomy' => 'faq-group',
                    'fields'   => 'slugs',
                )
            );

            $this->filters = $terms;
        }

        $this->faqs_query = $this->query_build();
    }

    /**
     * Build the basic query for faqs.
     */
    protected function query_build()
    {
        $query = array(
            'post_type'      => 'faq',
            'posts_per_page' => - 1,
            'orderby'        => $this->orderby,
            'order'          => $this->order,
        );

        if ($this->filters) {
            $query['tax_query'] = array(
                array(
                    'taxonomy' => 'faq-group',
                    'field'    => 'slug',
                    'terms'    => $this->filters,
                ),
            );
        }

        return $query;
    }

    /**
     * Helper function to build faq terms slugs array
     *
     * @param int $id Holds the faq post ID.
     *
     * @return array terms array.
     */
    protected function get_terms_slugs($id)
    {
        $terms_slugs = array();
        $terms       = get_the_terms($id, 'faq-group');

        if ($terms && ! is_wp_error($terms)) {
            foreach ($terms as $term) {
                $terms_slugs[] = $term->slug;
            }
        }

        return $terms_slugs;
    }

    /**
     * Helper function to build term faqs ids array
     *
     * @param array $faqs_posts_ids Holds the faq post IDs.
     *
     * @return array terms array.
     */
    protected function get_term_faqs_ids($faqs_posts_ids)
    {
        $faq_terms_posts = array();

        foreach ($faqs_posts_ids as $id) {
            $terms = get_the_terms($id, 'faq-group');

            if ($terms && ! is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $faq_terms_posts[ $term->slug ][] = $id;
                }
            }
        }

        return $faq_terms_posts;
    }

    /**
     * Build and render the faqs
     *
     * @param array $faq_terms_posts Holds the faq terms as slug and faqs ids as associative array.
     */
    protected function build_titles_structure($faq_terms_posts)
    {
        echo '<div id="qe-faqs-index" class="qe-faqs-index">';

        foreach ($faq_terms_posts as $slug => $faq_ids) {
            if (is_array($faq_ids)) {
                $term_object = get_term_by('slug', $slug, 'faq-group');

                if ($term_object) {
                    echo '<h4 class="qe-faqs-group-title">' . esc_html($term_object->name) . '</h4>';
                }

                echo '<ol class="qe-faqs-index-list">';

                foreach ($faq_ids as $id) {
                    $terms_slugs = $this->get_terms_slugs($id); ?>
					<li class="<?php echo esc_attr(implode(' ', $terms_slugs)); ?>">
						<?php if ($this->linksonly): ?>
						<a href="<?php echo get_the_permalink($id); ?>"><?php echo esc_html(get_the_title($id)); ?></a>
						<?php else: ?>
							<a href="#qaef-<?php echo esc_attr($id); ?>"><?php echo esc_html(get_the_title($id)); ?></a>
						<?php endif; ?>
					</li>
					<?php
                }

                echo '</ol>';
            } else {
                $terms_slugs = $this->get_terms_slugs($faq_ids); ?>
				
					<li class="<?php echo esc_attr(implode(' ', $terms_slugs)); ?>">
						<?php if ($this->linksonly): ?>
						<a href="<?php echo get_the_permalink($faq_ids); ?>"><?php echo esc_html(get_the_title($faq_ids)); ?></a>
						<?php else: ?>
							<a href="#qaef-<?php echo esc_attr($faq_ids); ?>"><?php echo esc_html(get_the_title($faq_ids)); ?></a>
						<?php endif; ?>
					</li>
				
				<?php
            }
        }
        echo '</div>';
    }

    /**
     * Render the faqs title
     *
     * @param array $faqs_posts_ids FAQs posts ids.
     */
    protected function render_faqs_title($faqs_posts_ids)
    {
        if ('grouped' === $this->display) {
            $faq_terms_posts = $this->get_term_faqs_ids($faqs_posts_ids);

            $this->build_titles_structure($faq_terms_posts);
        } elseif ('' === $this->display) {
            echo '<ol class="qe-faqs-index-list">';
            $this->build_titles_structure($faqs_posts_ids);
            echo '</ol>';
        }
    }

    /**
     * Build and render the faqs filters
     */
    protected function build_faqs_filter_structure()
    {
        if ($this->filters) {
            ?>
			<ul class="qe-faqs-filters-container">
				<li><a class="qe-faqs-filter" href="#" data-filter="*"><?php esc_html_e('All', 'quick-and-easy-faqs'); ?></a></li>
				<?php
                foreach ($this->filters as $term) {
                    $term_object = get_term_by('slug', $term, 'faq-group');
                    if ($term_object) {
                        echo '<li><a class="qe-faqs-filter" href="#' . esc_attr($term) . '" data-filter=".' . esc_attr($term) . '">' . esc_html($term_object->name) . '</a></li>';
                    }
                } ?>
			</ul>
			<?php
        }
    }

    /**
     * Get and render the faqs icon
     *
     * @return string HTML.
     */
    protected function get_the_icon()
    {
        if (empty($this->display) || 'grouped' === $this->display) {
            $class = 'fa fa-question-circle';
        } else {
            $class = 'fa fa-minus-circle';
        }

        return '<i class="' . esc_attr($class) . '"></i> ';
    }

    /**
     * Build and render the faqs
     *
     * @param int $id Holds the faq post id.
     */
    protected function build_faqs_structure($id)
    {
        if (empty($this->display) || 'grouped' === $this->display) {
            $class = 'list';
        } else {
            $class = 'toggle';
        }

        $terms_slugs = $this->get_terms_slugs($id);

        $back_to_index = $this->get_option('faqs_hide_back_index', 'qaef_basics'); ?>
		<div id="qaef-<?php echo esc_attr($id); ?>" class="qe-faq-<?php echo esc_attr($class) . ' ' . esc_attr(implode(' ', $terms_slugs)); ?>">
			<div class="qe-<?php echo esc_attr($class); ?>-title">
				<h4>
					<?php
                    echo wp_kses($this->get_the_icon(), array( 'i' => array( 'class' => array() ) ));
        echo esc_html(get_the_title($id)); ?>
				</h4>
			</div>
			<div class="qe-<?php echo esc_attr($class); ?>-content">
				<?php
                $content_post = get_post($id);
        $content      = $content_post->post_content;
        $content      = apply_filters('the_content', $content);
        $content      = str_replace(']]>', ']]&gt;', $content);
        echo wp_kses_post($content);
        if ((empty($this->display) || 'grouped' === $this->display) && 'on' !== $back_to_index) {
            echo '<br /><a class="qe-faq-top" href="#qe-faqs-index"><i class="fa fa-angle-up"></i> ' . esc_html__('Back to Index', 'quick-and-easy-faqs') . '</a>';
        } ?>
			</div>
		</div>
		<?php
    }

    /**
     * Build and render the faqs
     *
     * @param int | array $ids Html Post IDs.
     */
    protected function render_faqs($ids)
    {
        if (is_array($ids)) {
            $faq_terms_posts = $this->get_term_faqs_ids($ids);

            foreach ($faq_terms_posts as $slug => $faq_ids) {
                $term_object = get_term_by('slug', $slug, 'faq-group');
                echo '<h3 class="qe-faqs-group-title">' . esc_html($term_object->name) . '</h3>';
                foreach ($faq_ids as $id) {
                    $this->build_faqs_structure($id);
                }
            }
        } else {
            $this->build_faqs_structure($ids);
        }
    }

    /**
     * Render the faqs
     */
    public function render()
    {
        $faq_posts = new \WP_Query($this->faqs_query);

        $class = 'toggle';
        if (empty($this->display) || 'grouped' === $this->display) {
            $class = 'list';
        }

        if ($faq_posts->have_posts()) : ?>
            <div class="qae-faqs-container qae-faqs-<?php echo esc_attr($class); ?>-container">
				<?php
                $faqs_array     = $faq_posts->posts;
        $faqs_posts_ids = wp_list_pluck($faqs_array, 'ID');

        $this->build_faqs_filter_structure();

        $this->render_faqs_title($faqs_posts_ids);
        if (! $this->linksonly) {
            if ('accordion-grouped' === $this->display || 'toggle-grouped' === $this->display) {
                $this->render_faqs($faqs_posts_ids);
            } else {
                while ($faq_posts->have_posts()) : $faq_posts->the_post();
                $this->render_faqs(get_the_ID());
                endwhile;
            }
        } ?>
            </div>
		<?php
        endif;

        // All the custom loops ends here so reset the query.
        wp_reset_postdata();
    }
}
