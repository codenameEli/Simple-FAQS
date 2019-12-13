<?php

namespace YDOP;

class FAQ
{
    function __construct($post)
    {
        $this->id = $post->post_id;
        $this->question = $post->post_title;
        $this->answer = apply_filters('the_content', $post->post_content);
    }

    function template()
    {
        $markup = ob_start();

        ?>
        <div class="faqs__item faq">
            <div class="faq__question question">
                <?= $this->question ?>
            </div>
            <i class="faq__icon icon--action"></i>
            <div class="faq__answer"><?= $this->answer ?></div>
        </div>
        <?php

        return ob_get_clean();
    }

    function schema() {
        return '{
            "@type": "Question",
            "name": "' . $this->question . '",' .
            '"acceptedAnswer": {
                "@type": "Answer",
                "text": "' . strip_tags($this->answer)  . '"' .'
            }
        }';
    }
}

class FAQS
{
    static function init()
    {
        add_action('init', array('YDOP\FAQS', 'registerPostTypes'));
        add_action('wp_enqueue_scripts', array('YDOP\FAQS', 'loadScripts'));
        add_action('edit_post', array('YDOP\FAQS', 'clearCache'), 10, 2);
        add_shortcode( 'faqs', array('YDOP\FAQS', 'shortcode') );
    }

    static function shortcode($atts)
    {
        return FAQS::render();
    }

    static function loadScripts()
    {
        // // //Fancybox
        wp_enqueue_style('fancybox-styles', '//cdnjs.cloudflare.com/ajax/libs/fancybox/3.0.47/jquery.fancybox.min.css', false, '3.0', false);
        wp_enqueue_script('fancybox-js', '//cdnjs.cloudflare.com/ajax/libs/fancybox/3.0.47/jquery.fancybox.min.js', array('jquery'), '3.0', true);

        wp_register_script('ydop-faqs-js', get_stylesheet_directory_uri() . '/lib/FAQ/FAQ.js', array('fancybox-js'), '1.0', true);
        wp_register_style('ydop-faqs-css', get_stylesheet_directory_uri() . '/lib/FAQ/FAQ.css', array('fancybox-styles'), '1.0');

        wp_enqueue_script('ydop-faqs-js');
        wp_enqueue_style('ydop-faqs-css');
    }

    static function query()
    {
        $args = array(
            'post_type' => 'ydop_faq',
            'posts_per_page' => -1,
            'orderby' => 'menu_order'
        );
        $query = new \WP_Query($args);

        return $query->posts;
    }

    static function cached()
    {
        return get_transient('ydop_faqs_markup');
    }

    static function setCache($value)
    {
        set_transient('ydop_faqs_markup', $value, 12 * HOUR_IN_SECONDS);
    }

    static function clearCache($post_id)
    {
        if (get_post_type($post_id) !== 'ydop_faq') return;

        delete_transient('ydop_faqs_markup');
    }

    static function get()
    {
        $final = self::cached();

        if (empty($final)) {
            $posts = self::query();
            $final = self::template($posts);

            self::setCache($final);
        }

        return $final;
    }

    static function template($posts)
    {
        $markup = ob_start();
        $faqs = array();

        ?>
        <div class="faqs">
            <?php
            foreach($posts as $index => $post) {
                $faq = new FAQ($post);

                array_push($faqs, $faq);

                echo $faq->template();
            }
            ?>
        </div>
        <?php

        self::schema($faqs);

        return ob_get_clean();
    }

    static function schema($items)
    {
        $mainEntity = '';

        foreach($items as $index => $item) {
            $mainEntity .= $item->schema();

            if ($index < count($items) - 1) {
                $mainEntity .= ',';
            }
        }
        ?>
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [ <?= $mainEntity ?> ]
        }
        </script>
        <?php
    }

    static function render()
    {
        echo self::get();
    }

    static function registerPostTypes()
    {
        $labels = array(
            'name'                  => _x( 'FAQs', 'Post Type General Name', 'text_domain' ),
            'singular_name'         => _x( 'FAQ', 'Post Type Singular Name', 'text_domain' ),
            'menu_name'             => __( 'FAQs', 'text_domain' ),
            'name_admin_bar'        => __( 'FAQ', 'text_domain' ),
            'archives'              => __( 'Item Archives', 'text_domain' ),
            'attributes'            => __( 'Item Attributes', 'text_domain' ),
            'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
            'all_items'             => __( 'All Items', 'text_domain' ),
            'add_new_item'          => __( 'Add New Item', 'text_domain' ),
            'add_new'               => __( 'Add New', 'text_domain' ),
            'new_item'              => __( 'New Item', 'text_domain' ),
            'edit_item'             => __( 'Edit Item', 'text_domain' ),
            'update_item'           => __( 'Update Item', 'text_domain' ),
            'view_item'             => __( 'View Item', 'text_domain' ),
            'view_items'            => __( 'View Items', 'text_domain' ),
            'search_items'          => __( 'Search Item', 'text_domain' ),
            'not_found'             => __( 'Not found', 'text_domain' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
            'featured_image'        => __( 'Featured Image', 'text_domain' ),
            'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
            'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
            'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
            'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
            'items_list'            => __( 'Items list', 'text_domain' ),
            'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
            'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
        );
        $rewrite = array(
            'slug'                  => 'faq',
            'with_front'            => true,
            'pages'                 => true,
            'feeds'                 => true
        );
        $args = array(
            'label'                 => __( 'FAQ', 'text_domain' ),
            'description'           => __( 'FAQ Description', 'text_domain' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-format-chat',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'rewrite'               => $rewrite,
            'capability_type'       => 'page',
        );
        register_post_type( 'ydop_faq', $args );
    }
}