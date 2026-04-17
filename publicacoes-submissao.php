<?php
/*
Plugin Name: Publicações por Visitantes
Description: Permite envio de publicações por usuários não logados, moderação no admin, curtidas e comentários via AJAX.
Version: 1.0.6
Author: Marcos Cordeiro
Text Domain: publicacoes-submissao
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PUBLICACOES_SUBMISSAO_VERSION', '1.0.6' );
define( 'PUBLICACOES_SUBMISSAO_FILE', __FILE__ );
define( 'PUBLICACOES_SUBMISSAO_PATH', plugin_dir_path( __FILE__ ) );
define( 'PUBLICACOES_SUBMISSAO_URL', plugin_dir_url( __FILE__ ) );

require_once PUBLICACOES_SUBMISSAO_PATH . 'includes/class-publicacoes-submissao.php';

function publicacoes_submissao_init() {
    Publicacoes_Submissao::instance();
}

add_action( 'plugins_loaded', 'publicacoes_submissao_init' );
register_activation_hook( __FILE__, array( 'Publicacoes_Submissao', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Publicacoes_Submissao', 'deactivate' ) );
