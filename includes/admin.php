<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Publicacoes_Submissao_Admin {
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_approval_meta_box' ) );
        add_action( 'save_post_publicacoes', array( $this, 'save_approval_meta_box' ), 10, 2 );
        add_filter( 'manage_publicacoes_posts_columns', array( $this, 'register_columns' ) );
        add_action( 'manage_publicacoes_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
    }

    public function add_approval_meta_box() {
        add_meta_box(
            'publicacoes_approval',
            __( 'Aprovado', 'publicacoes-submissao' ),
            array( $this, 'render_approval_meta_box' ),
            'publicacoes',
            'side',
            'high'
        );
    }

    public function render_approval_meta_box( $post ) {
        wp_nonce_field( 'publicacoes_save_approval', 'publicacoes_approval_nonce' );
        $approved = get_post_meta( $post->ID, '_publicacoes_aprovado', true );
        ?>
        <label for="publicacoes_aprovado">
            <input type="checkbox" name="publicacoes_aprovado" id="publicacoes_aprovado" value="1" <?php checked( $approved, '1' ); ?> />
            <?php esc_html_e( 'Marcar como aprovado', 'publicacoes-submissao' ); ?>
        </label>
        <?php
    }

    public function save_approval_meta_box( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['publicacoes_approval_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['publicacoes_approval_nonce'] ) ), 'publicacoes_save_approval' ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $approved = isset( $_POST['publicacoes_aprovado'] ) ? '1' : '0';
        update_post_meta( $post_id, '_publicacoes_aprovado', $approved );

        if ( '1' === $approved && 'publish' !== $post->post_status ) {
            wp_update_post( array(
                'ID' => $post_id,
                'post_status' => 'publish',
            ) );
        }

        if ( '0' === $approved && 'publish' === $post->post_status ) {
            wp_update_post( array(
                'ID' => $post_id,
                'post_status' => 'pending',
            ) );
        }
    }

    public function register_columns( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;

            if ( 'title' === $key ) {
                $new_columns['publicacoes_aprovado'] = __( 'Aprovado', 'publicacoes-submissao' );
            }
        }

        return $new_columns;
    }

    public function render_columns( $column, $post_id ) {
        if ( 'publicacoes_aprovado' !== $column ) {
            return;
        }

        $approved = get_post_meta( $post_id, '_publicacoes_aprovado', true );

        if ( '1' === $approved ) {
            esc_html_e( 'Sim', 'publicacoes-submissao' );
        } else {
            esc_html_e( 'Não', 'publicacoes-submissao' );
        }
    }
}
