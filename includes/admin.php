<?php

if (! defined('ABSPATH')) {
    exit;
}

class Publicacoes_Submissao_Admin
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_approval_meta_box'));
        add_action('add_meta_boxes', array($this, 'add_author_info_meta_box'));
        add_action('save_post_publicacoes', array($this, 'save_approval_meta_box'), 10, 2);
        add_filter('manage_publicacoes_posts_columns', array($this, 'register_columns'));
        add_action('manage_publicacoes_posts_custom_column', array($this, 'render_columns'), 10, 2);
    }

    public function add_approval_meta_box()
    {
        add_meta_box(
            'publicacoes_approval',
            __('Aprovado', 'publicacoes-submissao'),
            array($this, 'render_approval_meta_box'),
            'publicacoes',
            'side',
            'high'
        );
    }

    public function add_author_info_meta_box()
    {
        add_meta_box(
            'publicacoes_author_info',
            __('Informações do Autor', 'publicacoes-submissao'),
            array($this, 'render_author_info_meta_box'),
            'publicacoes',
            'normal',
            'high'
        );
    }

    public function render_approval_meta_box($post)
    {
        wp_nonce_field('publicacoes_save_approval', 'publicacoes_approval_nonce');
        $approved = get_post_meta($post->ID, '_publicacoes_aprovado', true);
?>
        <label for="publicacoes_aprovado">
            <input type="checkbox" name="publicacoes_aprovado" id="publicacoes_aprovado" value="1" <?php checked($approved, '1'); ?> />
            <?php esc_html_e('Marcar como aprovado', 'publicacoes-submissao'); ?>
        </label>
    <?php
    }

    public function render_author_info_meta_box($post)
    {
        $nome = get_post_meta($post->ID, '_publicacoes_nome', true);
        $email = get_post_meta($post->ID, '_publicacoes_email', true);
    ?>
        <div style="margin-bottom: 16px;">
            <label for="publicacoes_nome_display">
                <strong><?php esc_html_e('Nome do Autor', 'publicacoes-submissao'); ?></strong>
                <input type="text" id="publicacoes_nome_display" value="<?php echo esc_attr($nome); ?>" readonly style="width: 100%; padding: 8px; margin-top: 6px; border: 1px solid #ddd; border-radius: 4px; background-color: #f5f5f5;" />
            </label>
        </div>
        <div>
            <label for="publicacoes_email_display">
                <strong><?php esc_html_e('Email do Autor', 'publicacoes-submissao'); ?></strong>
                <input type="email" id="publicacoes_email_display" value="<?php echo esc_attr($email); ?>" readonly style="width: 100%; padding: 8px; margin-top: 6px; border: 1px solid #ddd; border-radius: 4px; background-color: #f5f5f5;" />
            </label>
        </div>
<?php
    }

    public function save_approval_meta_box($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($post_id)) {
            return;
        }

        if (! isset($_POST['publicacoes_approval_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['publicacoes_approval_nonce'])), 'publicacoes_save_approval')) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $approved = isset($_POST['publicacoes_aprovado']) ? '1' : '0';
        update_post_meta($post_id, '_publicacoes_aprovado', $approved);

        if ('1' === $approved && 'publish' !== $post->post_status) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_status' => 'publish',
            ));
            wp_mail(get_post_meta($post->ID, '_publicacoes_email', true), "Sua publicação foi aprovada!", '<h1>Olá, ' . ${esc_html(get_post_meta($post->ID, '_publicacoes_nome', true))} . '!</h1><p>Seu depoimento foi validado e já está disponível na lista de publicações.</p>');
        }

        if ('0' === $approved && 'publish' === $post->post_status) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_status' => 'pending',
            ));
        }
    }

    public function register_columns($columns)
    {
        $new_columns = array();

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;

            if ('title' === $key) {
                $new_columns['publicacoes_aprovado'] = __('Aprovado', 'publicacoes-submissao');
            }
        }

        return $new_columns;
    }

    public function render_columns($column, $post_id)
    {
        if ('publicacoes_aprovado' !== $column) {
            return;
        }

        $approved = get_post_meta($post_id, '_publicacoes_aprovado', true);

        if ('1' === $approved) {
            esc_html_e('Sim', 'publicacoes-submissao');
        } else {
            esc_html_e('Não', 'publicacoes-submissao');
        }
    }
}
