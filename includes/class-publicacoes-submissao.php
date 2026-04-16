<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once PUBLICACOES_SUBMISSAO_PATH . 'includes/admin.php';
require_once PUBLICACOES_SUBMISSAO_PATH . 'includes/frontend.php';

class Publicacoes_Submissao {
    private static $instance = null;
    private $admin;
    private $frontend;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        $this->admin = new Publicacoes_Submissao_Admin();
        $this->frontend = new Publicacoes_Submissao_Frontend();

        add_action( 'init', array( $this, 'register_cpt' ) );
        add_action( 'init', array( $this->frontend, 'register_shortcodes' ) );
        add_action( 'wp_enqueue_scripts', array( $this->frontend, 'register_assets' ) );
        add_action( 'wp_ajax_publicacoes_like', array( $this->frontend, 'ajax_like' ) );
        add_action( 'wp_ajax_nopriv_publicacoes_like', array( $this->frontend, 'ajax_like' ) );
        add_action( 'wp_ajax_publicacoes_comment', array( $this->frontend, 'ajax_comment' ) );
        add_action( 'wp_ajax_nopriv_publicacoes_comment', array( $this->frontend, 'ajax_comment' ) );
    }

    public static function activate() {
        self::instance()->register_cpt();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    public function register_cpt() {
        $labels = array(
            'name'               => __( 'Publicações', 'publicacoes-submissao' ),
            'singular_name'      => __( 'Publicação', 'publicacoes-submissao' ),
            'menu_name'          => __( 'Publicações', 'publicacoes-submissao' ),
            'name_admin_bar'     => __( 'Publicação', 'publicacoes-submissao' ),
            'add_new'            => __( 'Adicionar Nova', 'publicacoes-submissao' ),
            'add_new_item'       => __( 'Adicionar nova publicação', 'publicacoes-submissao' ),
            'new_item'           => __( 'Nova publicação', 'publicacoes-submissao' ),
            'edit_item'          => __( 'Editar publicação', 'publicacoes-submissao' ),
            'view_item'          => __( 'Ver publicação', 'publicacoes-submissao' ),
            'all_items'          => __( 'Todas as publicações', 'publicacoes-submissao' ),
            'search_items'       => __( 'Buscar publicações', 'publicacoes-submissao' ),
            'not_found'          => __( 'Nenhuma publicação encontrada.', 'publicacoes-submissao' ),
            'not_found_in_trash' => __( 'Nenhuma publicação na lixeira.', 'publicacoes-submissao' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => false,
            'show_in_menu'       => true,
            'taxonomies'         => array(),
            'menu_icon'          => 'dashicons-format-image',
            'capability_type'    => 'post',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'comments' ),
            'show_in_rest'       => false,
        );

        register_post_type( 'publicacoes', $args );
    }
}
