<?php

if (! defined('ABSPATH')) {
    exit;
}

class Publicacoes_Submissao_Frontend
{
    public function __construct()
    {
        // empty constructor - hooks are attached from main class
    }

    public function register_assets()
    {
        wp_register_style(
            'publicacoes-submissao-style',
            PUBLICACOES_SUBMISSAO_URL . 'assets/css/publicacoes-submissao.css',
            array(),
            PUBLICACOES_SUBMISSAO_VERSION
        );

        wp_register_script(
            'publicacoes-submissao-script',
            PUBLICACOES_SUBMISSAO_URL . 'assets/js/publicacoes-submissao.js',
            array(),
            PUBLICACOES_SUBMISSAO_VERSION,
            true
        );

        wp_localize_script('publicacoes-submissao-script', 'PublicacoesSubmissao', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('publicacoes_action'),
            'texts'    => array(
                'likeSuccess'    => __('Curtida registrada!', 'publicacoes-submissao'),
                'commentSuccess' => __('Comentário enviado!', 'publicacoes-submissao'),
            ),
        ));
    }

    public function register_shortcodes()
    {
        add_shortcode('publicacao_form', array($this, 'render_submission_form'));
        add_shortcode('publicacoes_list', array($this, 'render_publicacoes_list'));
    }

    public function render_submission_form()
    {
        wp_enqueue_style('publicacoes-submissao-style');
        wp_enqueue_script('publicacoes-submissao-script');

        $message = '';
        $message_class = '';

        if (isset($_POST['publicacoes_submission_nonce'])) {
            $result = $this->process_submission();

            if (is_wp_error($result)) {
                $message = esc_html($result->get_error_message());
                $message_class = 'publicacoes-error';
            } else {
                $message = esc_html__('Sua publicação foi enviada com sucesso e aguarda aprovação.', 'publicacoes-submissao');
                $message_class = 'publicacoes-success';
            }
        }

        ob_start();
?>
        <div class="publicacoes-form-wrapper">
            <?php if ($message) : ?>
                <div class="publicacoes-form-message <?php echo esc_attr($message_class); ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="publicacoes-submission-form">
                <?php wp_nonce_field('publicacoes_submission', 'publicacoes_submission_nonce'); ?>
                <p>
                    <label for="publicacoes_nome"><?php esc_html_e('Nome completo', 'publicacoes-submissao'); ?>*</label>
                    <input type="text" id="publicacoes_nome" name="publicacoes_nome" required maxlength="120" />
                </p>
                <p>
                    <label for="publicacoes_email"><?php esc_html_e('Email', 'publicacoes-submissao'); ?>*</label>
                    <input type="email" id="publicacoes_email" name="publicacoes_email" required />
                </p>
                <p>
                    <label for="publicacoes_legenda">O que é ser mãe para você? *</label>
                    <textarea id="publicacoes_legenda" name="publicacoes_legenda" rows="3" maxlength="250" required></textarea>
                    <small><?php esc_html_e('Máximo de 250 caracteres.', 'publicacoes-submissao'); ?></small>
                </p>
                <p>
                    <label>
                        <input type="file" id="publicacoes_foto" name="publicacoes_foto" accept="image/*" required style="display: none;" />
                        <span class="publicacoes-submit-button file">Insira sua foto aqui</span>
                        <small class="publicacoes-file-name"></small>
                    </label>
                    <p>Use Apenas fotos verticais (Formato Celular em pé)</p>
                </p>
                <p>
                    <button type="submit" class="publicacoes-submit-button"><?php esc_html_e('Enviar Agora', 'publicacoes-submissao'); ?></button>
                </p>
            </form>
        </div>
    <?php
        return ob_get_clean();
    }

    private function process_submission()
    {
        if (! isset($_POST['publicacoes_submission_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['publicacoes_submission_nonce'])), 'publicacoes_submission')) {
            return new WP_Error('nonce_failed', __('Falha de validação de segurança.', 'publicacoes-submissao'));
        }

        $name = isset($_POST['publicacoes_nome']) ? sanitize_text_field(wp_unslash($_POST['publicacoes_nome'])) : '';
        $email = isset($_POST['publicacoes_email']) ? sanitize_email(wp_unslash($_POST['publicacoes_email'])) : '';
        $legend = isset($_POST['publicacoes_legenda']) ? sanitize_textarea_field(wp_unslash($_POST['publicacoes_legenda'])) : '';

        if (empty($name) || empty($email) || empty($legend)) {
            return new WP_Error('missing_fields', __('Todos os campos são obrigatórios.', 'publicacoes-submissao'));
        }

        if (strlen($legend) > 250) {
            return new WP_Error('legend_too_long', __('A legenda deve ter no máximo 250 caracteres.', 'publicacoes-submissao'));
        }

        if (! is_email($email)) {
            return new WP_Error('invalid_email', __('Email inválido.', 'publicacoes-submissao'));
        }

        if (empty($_FILES['publicacoes_foto']) || ! isset($_FILES['publicacoes_foto']['name'])) {
            return new WP_Error('missing_image', __('É necessário enviar uma imagem.', 'publicacoes-submissao'));
        }

        $attachment_id = $this->handle_image_upload($_FILES['publicacoes_foto']);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        $post_id = wp_insert_post(array(
            'post_type'    => 'publicacoes',
            'post_status'  => 'pending',
            'post_title'   => sprintf('%s - %s', $name, wp_trim_words($legend, 5, '...')),
            'post_content' => $legend,
        ));

        if (is_wp_error($post_id) || ! $post_id) {
            return new WP_Error('post_error', __('Erro ao salvar a publicação.', 'publicacoes-submissao'));
        }

        update_post_meta($post_id, '_publicacoes_nome', $name);
        update_post_meta($post_id, '_publicacoes_email', $email);
        update_post_meta($post_id, '_publicacoes_aprovado', '0');

        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
        }
        $mensagem = "Olá, " . $name . "! O seu depoimento foi recebido com sucesso. Assim que for aprovado, aparecerá na lista de publicações.";

        wp_mail($email, "Seu depoimento foi recebido com sucesso!", $mensagem);
        return $post_id;
    }

    private function handle_image_upload($file)
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $allowed_mimes = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'gif'          => 'image/gif',
            'webp'         => 'image/webp',
        );

        $overrides = array(
            'test_form' => false,
            'mimes'     => $allowed_mimes,
        );

        $upload = wp_handle_upload($file, $overrides);

        if (isset($upload['error'])) {
            return new WP_Error('upload_error', esc_html($upload['error']));
        }

        $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name(basename($upload['file'])),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attachment_id = wp_insert_attachment($attachment, $upload['file']);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        return $attachment_id;
    }

    public function render_publicacoes_list()
    {
        wp_enqueue_style('publicacoes-submissao-style');
        wp_enqueue_script('publicacoes-submissao-script');
        $paged = get_query_var('paged') ?: (get_query_var('page') ?: 1);
        $args = array(
            'post_type'      => 'publicacoes',
            'post_status'    => 'publish',
            'posts_per_page' => 6, // quantidade por página
            'paged'          => $paged,
            'meta_query'     => array(
                array(
                    'key'     => '_publicacoes_aprovado',
                    'value'   => '1',
                    'compare' => '=',
                ),
            ),
        );

        $posts = new WP_Query($args);

        if (is_wp_error($posts)) {
            return $posts->get_error_message();
        }

        ob_start();
    ?>
        <div class="publicacoes-list-wrapper">
            <?php if ($posts->have_posts()) : ?>
                <?php while ($posts->have_posts()) : $posts->the_post(); ?>
                    <?php $this->render_publicacao_card(get_post()); ?>
                <?php endwhile; ?>
            <?php else : ?>
                <p>Nenhuma publicação encontrada.</p>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>
        </div>
        <div class="paginate">
            <?php
            echo paginate_links(array(
                'total'   => $posts->max_num_pages,
                'current' => $paged,
            ));
            ?>
        </div>
    <?php
        return ob_get_clean();
    }

    private function render_publicacao_card($post)
    {
        $post_id   = $post->ID;
        $name      = get_post_meta($post_id, '_publicacoes_nome', true);
        $content   = apply_filters('the_content', $post->post_content);
        $likes     = absint(get_post_meta($post_id, '_publicacoes_likes_count', true));
        $comments  = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
        $thumbnail = get_the_post_thumbnail($post_id, 'full', array('class' => 'publicacoes-card-image'));
    ?>
        <?php
        $caption_text = wp_strip_all_tags($post->post_content);
        $caption_limit = 45;
        $caption_short = $caption_text;
        $caption_more = '';

        if (mb_strlen($caption_text) > $caption_limit) {
            // Trunca por caracteres
            $truncated = mb_substr($caption_text, 0, $caption_limit);

            // Encontra o último espaço para não quebrar palavra
            $last_space = mb_strrpos($truncated, ' ');
            if ($last_space !== false && $last_space > 0) {
                $caption_short = mb_substr($truncated, 0, $last_space);
            } else {
                $caption_short = $truncated;
            }

            // O restante do texto começa onde o short termina
            $caption_more = mb_substr($caption_text, mb_strlen($caption_short) . '');
            $caption_more = ltrim($caption_more); // Remove espaço inicial
        }
        ?>
        <article class="publicacoes-card" data-post-id="<?php echo esc_attr($post_id); ?>">
            <div class="publicacoes-card-media"><?php echo $thumbnail ? $thumbnail : '<div class="publicacoes-card-noimage">' . esc_html__('Sem imagem', 'publicacoes-submissao') . '</div>'; ?></div>
            <div class="publicacoes-card-body">
                <h3 class="publicacoes-card-author"><?php echo esc_html($name); ?></h3>
                <div class="publicacoes-icon"></div>
                <div class="publicacoes-card-actions">
                    <form class="publicacoes-like-form" data-post-id="<?php echo esc_attr($post_id); ?>">
                        <button type="submit" class="publicacoes-like-button">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16px" height="14px" viewBox="0 0 16 14" version="1.1">
                                    <g id="surface1">
                                        <path style="fill:none;stroke-width:0.264583;stroke-linecap:butt;stroke-linejoin:miter;stroke:rgb(74.901962%,33.333334%,38.431373%);stroke-opacity:1;stroke-miterlimit:4;" d="M 1.247469 0.132292 C 0.516764 0.132292 0.10542 0.753442 0.133325 1.450041 C 0.416512 2.340942 1.138949 3.101619 2.137337 3.641121 C 3.091284 3.077848 3.781681 2.388485 4.016292 1.617472 C 4.020427 1.60507 4.024561 1.593701 4.027661 1.581299 C 4.03903 1.543058 4.054533 1.504818 4.062801 1.466577 L 4.059701 1.466577 C 4.075204 1.393197 4.082438 1.318783 4.082438 1.244368 C 4.082438 0.637687 3.590479 0.144694 2.983797 0.144694 C 2.646867 0.144694 2.329574 0.299723 2.120801 0.563273 C 1.913062 0.291455 1.589567 0.132292 1.247469 0.132292 Z M 1.247469 0.132292 " transform="matrix(3.779527,0,0,3.779527,0,0.000000307798)" />
                                    </g>
                                </svg>
                            </div>
                            <?php echo esc_html($likes); ?>
                        </button>
                    </form>
                    <button type="button" class="publicacoes-toggle-comments" aria-expanded="false">
                        <div class="publicacoes-action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16px" height="14px" viewBox="0 0 16 14" version="1.1">
                                <g id="surface1">
                                    <path style="fill:none;stroke-width:0.231296;stroke-linecap:butt;stroke-linejoin:miter;stroke:rgb(74.901962%,33.333334%,38.431373%);stroke-opacity:1;stroke-miterlimit:4;" d="M 2.189014 0.120923 C 1.266073 0.120923 0.516764 0.880566 0.516764 1.817977 C 0.516764 2.078426 0.576709 2.335775 0.690397 2.569352 L 0.572575 3.01687 L 0.412378 3.623551 L 1.018026 3.458187 L 1.451074 3.340365 C 1.680518 3.455086 1.932699 3.513997 2.189014 3.513997 C 3.111955 3.513997 3.861263 2.754354 3.861263 1.817977 C 3.861263 0.880566 3.111955 0.120923 2.189014 0.120923 Z M 2.189014 0.120923 " transform="matrix(3.779527,0,0,3.779527,0,0.000000307798)" />
                                </g>
                            </svg>
                        </div>
                        <?php echo esc_html(count($comments)); ?>
                    </button>
                </div>
                <div class="publicacoes-card-text">
                    <p class="publicacoes-caption-short"><?php echo esc_html($caption_short); ?><?php echo $caption_more ? '...' : ''; ?></p>
                    <?php if ($caption_more) : ?>
                        <p class="publicacoes-caption-more" style="display:none;"><?php echo esc_html($caption_text); ?></p>
                        <button type="button" class="publicacoes-read-more"><?php esc_html_e('Leia mais', 'publicacoes-submissao'); ?></button>
                    <?php endif; ?>
                </div>
                <div class="publicacoes-card-comments">
                    <div class="publicacoes-comments-list" style="display:none;">
                        <?php if (! empty($comments)) : ?>
                            <?php foreach ($comments as $comment) : ?>
                                <div class="publicacoes-comment-item">
                                    <strong><?php echo esc_html($comment->comment_author); ?></strong>
                                    <p><?php echo esc_html($comment->comment_content); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form class="publicacoes-comment-form" data-post-id="<?php echo esc_attr($post_id); ?>" style="display:none;">
                        <label>
                            <span><?php esc_html_e('Nome', 'publicacoes-submissao'); ?>*</span>
                            <input type="text" name="author" required />
                        </label>
                        <label>
                            <span><?php esc_html_e('Email', 'publicacoes-submissao'); ?>*</span>
                            <input type="email" name="email" required />
                        </label>
                        <label>
                            <span><?php esc_html_e('Comentário', 'publicacoes-submissao'); ?>*</span>
                            <textarea name="comment" rows="2" required></textarea>
                        </label>
                        <button type="submit"><?php esc_html_e('Enviar comentário', 'publicacoes-submissao'); ?></button>
                        <div class="publicacoes-comment-message" aria-live="polite"></div>
                    </form>
                </div>
            </div>
        </article>
<?php
    }

    public function ajax_like()
    {
        check_ajax_referer('publicacoes_action', 'nonce');

        $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;

        if (empty($post_id) || ! get_post_type($post_id) || 'publicacoes' !== get_post_type($post_id)) {
            wp_send_json_error(array('message' => __('Publicação inválida.', 'publicacoes-submissao')));
        }

        $likes = absint(get_post_meta($post_id, '_publicacoes_likes_count', true));
        $likes++;
        update_post_meta($post_id, '_publicacoes_likes_count', $likes);

        wp_send_json_success(array('likes' => $likes, 'message' => __('Curtida registrada!', 'publicacoes-submissao')));
    }

    public function ajax_comment()
    {
        check_ajax_referer('publicacoes_action', 'nonce');

        $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
        $author  = isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '';
        $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $comment = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';

        if (empty($post_id) || ! get_post_type($post_id) || 'publicacoes' !== get_post_type($post_id)) {
            wp_send_json_error(array('message' => __('Publicação inválida.', 'publicacoes-submissao')));
        }

        if (empty($author) || empty($email) || empty($comment)) {
            wp_send_json_error(array('message' => __('Todos os campos são obrigatórios.', 'publicacoes-submissao')));
        }

        if (! is_email($email)) {
            wp_send_json_error(array('message' => __('Email inválido.', 'publicacoes-submissao')));
        }

        $comment_data = array(
            'comment_post_ID'      => $post_id,
            'comment_author'       => $author,
            'comment_author_email' => $email,
            'comment_content'      => $comment,
            'comment_type'         => '',
            'comment_approved'     => 1,
        );

        $comment_id = wp_insert_comment($comment_data);

        if (! $comment_id) {
            wp_send_json_error(array('message' => __('Erro ao enviar comentário.', 'publicacoes-submissao')));
        }

        $comment_obj = get_comment($comment_id);
        $comment_html = sprintf(
            '<div class="publicacoes-comment-item"><strong>%s</strong><p>%s</p></div>',
            esc_html($comment_obj->comment_author),
            esc_html($comment_obj->comment_content)
        );

        wp_send_json_success(array('message' => __('Comentário enviado!', 'publicacoes-submissao'), 'html' => $comment_html));
    }
}
