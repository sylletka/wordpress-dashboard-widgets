<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'dashboardWidgets' ) ) {

    class dashboardWidgets
    {
        const POST_TYPE_SLUG = 'dashboard-widget';
        private static $instance = null;
        private $post_type;
        private $basedir;
        private $plugindir;

        public static function init()
        {
            $this->basedir   = dirname( dirname( __FILE__ ) );
            $this->plugindir = basename( $this->basedir );
            load_plugin_textdomain(
                'dashboard-widgets',
                false,
                $this->plugindir . '/languages/'
            );
            $this->register_post_type();
            add_action( 'add_meta_boxes',        array( $this, 'add_meta_boxes_action' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_action' ) );
            add_action( 'admin_menu',            array( $this, 'admin_menu_action' ) );
            add_action( 'save_post',             array( $this, 'save_post_action' ) );
            add_action( 'wp_dashboard_setup',    array( $this, 'wp_dashboard_setup_action' ) );
            add_filter( 'parent_file',           array( $this, 'parent_file_filter' ) );
            add_filter( 'submenu_file',          array( $this, 'submenu_file_filter' ) );
        }

        public static function get_instance()
        {
            NULL === self::$instance && self::$instance = new self;
            return self::$instance;
        }

        private function register_post_type()
        {
            $labels = array(
                'name'                => __( 'Dashboard widgets', 'dashboard-widgets' ),
                'singular_name'       => __( 'Dashboard widget', 'dashboard-widgets' ),
                'add_new'             => __( 'Add new', 'dashboard-widgets' ),
                'add_new_item'        => __( 'Add new widget in dashboard', 'dashboard-widgets' ),
                'edit_item'           => __( 'Edit widget', 'dashboard-widgets' ),
                'search_items'        => __( 'Search dashboard widgets', 'dashboard-widgets' ),
                'not_found'           => __( 'No dashboard widgets found.', 'dashboard-widgets' ),
                'not_found_in_trash'  => __( 'No dashboard widgets found in Trash.', 'dashboard-widgets' )
            );

            $capabilities =  array(
                'create_posts'        => 'update_core',
                'publish_posts'       => 'update_core',
                'edit_others_posts'   => 'update_core',
                'delete_posts'        => 'update_core',
                'delete_others_posts' => 'update_core',
                'read_private_posts'  => 'update_core',
                'edit_post'           => 'update_core',
                'delete_post'         => 'update_core',
                'read_post'           => 'read_post',
            );
            $args = array(
                'label'               => 'Dashboard widgets',
                'labels'              => $labels,
                'description'         => '',
                'public'              => false,
                'publicly_queryable'  => false,
                'show_ui'             => true,
                'show_in_rest'        => false,
                'rest_base'           => '',
                'has_archive'         => false,
                'show_in_menu'        => false,
                'exclude_from_search' => false,
                'capability_type'     => 'post',
                'capabilities'        => $capabilities,
                'map_meta_cap'        => true,
                'hierarchical'        => false,
                'rewrite'             => false,
                'query_var'           => false,
                'supports'            => array( 'title', 'editor' ),
            );
            $this->post_type = register_post_type(
                self::POST_TYPE_SLUG,
                $args
            );
        }

        public static function roles_meta_box_callback( $post )
        {
            global $wp_roles;
            $all_roles          = $wp_roles->roles;
            $roles              = apply_filters('editable_roles', $all_roles );
            $roles_keys         = array_keys( $roles );
            $meta               = get_post_meta( $post->ID );
            $enable_roles_limit =
                ( array_key_exists( 'enable_roles_limit', $meta ) ) ?
                $meta[ 'enable_roles_limit' ][ 0 ]:
                0;
            $limit_checked      = $enable_roles_limit ? ' checked' : '';
            $disabled           = !$enable_roles_limit ? ' disabled' : '';
            $enabled_roles      =
                ( array_key_exists( 'enabled_roles', $meta ) ) ?
                unserialize( $meta[ 'enabled_roles' ][ 0 ] ) :
                array_fill_keys( $roles_keys, 0);
            wp_nonce_field(
                self::POST_TYPE_SLUG . '-roles-metabox',
                self::POST_TYPE_SLUG . '-roles-metabox-nonce'
            );
            ob_start();
            require_once( $this->basedir . '/classes/views/rolesMetabox.php' );
            $view = ob_get_clean();
            echo $view;
        }

        public static function dashboard_widget_callback( $var, $args )
        {
            $query_args = array(
                'post_type' => self::POST_TYPE_SLUG,
                'p'         => $args[ 'args' ]
            );
            $query = new WP_Query( $query_args );
            if ( $query->have_posts() ){
                while ( $query->have_posts() ){
                    $query->the_post();
                    the_content();
                }
                wp_reset_postdata();
            }
        }

        public static function add_meta_boxes_action()
        {
            add_meta_box(
                self::POST_TYPE_SLUG . '-roles-metabox',
                __( 'Roles to display to', 'dashboard-widgets' ),
                array( $this, 'roles_meta_box_callback' ),
                self::POST_TYPE_SLUG
            );
        }

        public static function save_post_action( $post_id )
        {
            if ( ! isset( $_POST[ self::POST_TYPE_SLUG . '-roles-metabox-nonce' ] ) ) {
                return $post_id;
            }
            $nonce = $_POST[ self::POST_TYPE_SLUG . '-roles-metabox-nonce' ];
            if ( ! wp_verify_nonce( $nonce, self::POST_TYPE_SLUG . '-roles-metabox' ) ) {
                return $post_id;
            }
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return $post_id;
            }
            if ( 'page' == $_POST[ 'post_type' ] ) {
                if ( ! current_user_can( 'edit_page', $post_id ) ) {
                    return $post_id;
                }
            } else {
                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                    return $post_id;
                }
            }
            update_post_meta(
                $post_id,
                'enable_roles_limit',
                sanitize_text_field( $_POST[ 'enable_roles_limit' ] )
            );
            update_post_meta(
                $post_id,
                'enabled_roles',
                $_POST[ 'enabled_roles' ]
            );
        }

        public static function wp_dashboard_setup_action()
        {
            if ( is_user_logged_in() ){
                $query_args = array(
                    'post_type'        => self::POST_TYPE_SLUG,
                    'posts_per_page'   => -1,
                    'suppress_filters' => false,
                );
                $query = new WP_Query( $query_args );
                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $meta = get_post_meta( get_the_ID() );
                        if (
                            array_key_exists( 'enable_roles_limit', $meta ) &&
                            array_key_exists( 'enabled_roles', $meta ) &&
                            $meta[ 'enable_roles_limit' ][ 0 ]
                        ){
                            $enabled_roles = array_keys( array_filter( unserialize( $meta[ 'enabled_roles' ][ 0 ] ) ) );
                            $current_user = wp_get_current_user();
                            if( !array_intersect( $current_user->roles, $enabled_roles ) ){
                                continue;
                            }
                        }
                        $id = $query->post->ID;
                        wp_add_dashboard_widget(
                            self::POST_TYPE_SLUG . $id,
                            $query->post->post_title,
                            array( $this, 'dashboard_widget_callback' ),
                            '',
                            $id
                        );
                    }
                    wp_reset_postdata();
                }
            }
        }

        public static function admin_menu_action()
        {
            if ( !current_user_can( 'administrator' ) ){
                remove_menu_page( 'edit.php?post_type=' . self::POST_TYPE_SLUG );
            } else {
                add_submenu_page(
                    'index.php',
                    'Dashboard widget', // ignored
                    $this->post_type->labels->name,
                    $this->post_type->cap->edit_posts,
                    'edit.php?post_type=' . self::POST_TYPE_SLUG
                );
            }
        }

        public static function submenu_file_filter( $submenu_file )
        {
            if ( $this->is_widget_edit_page() ) {
                $submenu_file = 'edit.php?post_type=' . self::POST_TYPE_SLUG;
            }
            return $submenu_file;
        }

        public static function parent_file_filter( $parent_file )
        {
            if ( $this->is_widget_edit_page() ) {
                $parent_file = 'index.php';
            }
            return $parent_file;
        }

        public static function admin_enqueue_scripts_action( $hook )
        {
            if ( $this->is_widget_edit_page() ) {
                wp_enqueue_script(
                    'dashboard-widgets',
                    plugins_url( $this->plugindir ) . '/assets/js/dashboard-widgets.js'
                );
            }
        }

        private function is_widget_edit_page()
        {
            global $current_screen;
            if (
                $current_screen
                && in_array( $current_screen->base, array( 'post', 'edit' ) )
                && self::POST_TYPE_SLUG == $current_screen->post_type
            ){
                return true;
            }
            return false;
        }
    }
}

