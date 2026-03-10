<?php
/**
 * Theme Options Panel - Painel CDN
 */

// Adicionar menu sob "Aparência"
add_action( 'admin_menu', 'cdn_add_admin_menu' );
function cdn_add_admin_menu() {
    add_theme_page(
        'Painel CDN',
        'Painel CDN',
        'manage_options',
        'painel-cdn',
        'cdn_options_page'
    );
}

// Processador Oculto: Injetor de Visualizações
if ( isset($_POST['cdn_add_views']) && isset($_POST['cdn_post_id']) && isset($_POST['cdn_view_count']) ) {
    if ( current_user_can('manage_options') && check_admin_referer('cdn_inject_views_nonce') ) {
        $p_id = intval($_POST['cdn_post_id']);
        $v_add = intval($_POST['cdn_view_count']);
        if ( $p_id > 0 && $v_add > 0 ) {
            $current_views = (int) get_post_meta($p_id, 'post_views_count', true);
            update_post_meta($p_id, 'post_views_count', $current_views + $v_add);
            add_action('admin_notices', function() use ($v_add, $p_id) {
                echo '<div class="notice notice-success is-dismissible"><p>Sucesso! <strong>+'.$v_add.' visualizações</strong> foram injetadas no Post ID <strong>'.$p_id.'</strong>.</p></div>';
            });
        }
    }
}


// Registrar configurações
add_action( 'admin_init', 'cdn_settings_init' );
function cdn_settings_init() {

    // === ACTIVITY LOG ===
    register_setting( 'cdn_activity_log', 'cdn_log_email',  [ 'sanitize_callback' => 'sanitize_email' ] );
    register_setting( 'cdn_activity_log', 'cdn_log_notify', [ 'sanitize_callback' => 'sanitize_text_field' ] );

    // === PERSONALIZAÇÃO ===

    register_setting( 'cdn_personalizacao', 'cdn_urgente_text' );
    register_setting( 'cdn_personalizacao', 'cdn_urgente_color' );
    register_setting( 'cdn_personalizacao', 'cdn_topbar_links' );
    register_setting( 'cdn_personalizacao', 'cdn_logo' );
    register_setting( 'cdn_personalizacao', 'cdn_home_layout' );

    add_settings_section( 'cdn_secao_appbar', 'Appbar Urgente e Links', '', 'cdn_personalizacao' );
    add_settings_field( 'cdn_urgente_text', 'Texto Urgente', 'cdn_render_text_field', 'cdn_personalizacao', 'cdn_secao_appbar', ['label_for' => 'cdn_urgente_text'] );
    add_settings_field( 'cdn_urgente_color', 'Cor de Fundo Urgente', 'cdn_render_color_field', 'cdn_personalizacao', 'cdn_secao_appbar', ['label_for' => 'cdn_urgente_color'] );
    add_settings_field( 'cdn_topbar_links', 'Links do Topbar (Texto e URL)', 'cdn_render_textarea_field', 'cdn_personalizacao', 'cdn_secao_appbar', ['label_for' => 'cdn_topbar_links', 'desc' => 'Um por linha no formato: Texto|URL (ex: Fale Conosco|/fale-conosco/)'] );

    add_settings_section( 'cdn_secao_header', 'Logo do Cabeçalho', '', 'cdn_personalizacao' );
    add_settings_field( 'cdn_logo', 'Logo Imagem', 'cdn_render_media_field', 'cdn_personalizacao', 'cdn_secao_header', ['label_for' => 'cdn_logo'] );

    add_settings_section( 'cdn_secao_layout', 'Layout da Página Principal', '', 'cdn_personalizacao' );
    add_settings_field( 'cdn_home_layout', 'Modo de Exibição', 'cdn_render_layout_field', 'cdn_personalizacao', 'cdn_secao_layout', ['label_for' => 'cdn_home_layout'] );

    // === JORNAL DIGITAL ===
    register_setting( 'cdn_digital', 'cdn_digital_pdf' );
    register_setting( 'cdn_digital', 'cdn_digital_cover' );
    register_setting( 'cdn_digital', 'cdn_digital_btn_text' );
    register_setting( 'cdn_digital', 'cdn_digital_btn_number' );

    add_settings_section( 'cdn_secao_digital', 'Edição Digital', '', 'cdn_digital' );
    add_settings_field( 'cdn_digital_btn_text', 'Nome do Botão do Cabeçalho', 'cdn_render_text_field', 'cdn_digital', 'cdn_secao_digital', ['label_for' => 'cdn_digital_btn_text', 'desc' => 'Ex: Edição de Abril, Edição do Mês...'] );
    add_settings_field( 'cdn_digital_btn_number', 'Número da Edição (Destaque)', 'cdn_render_text_field', 'cdn_digital', 'cdn_secao_digital', ['label_for' => 'cdn_digital_btn_number', 'desc' => 'Ex: Nº 267'] );
    add_settings_field( 'cdn_digital_pdf', 'PDF da Edição (Envio)', 'cdn_render_media_field', 'cdn_digital', 'cdn_secao_digital', ['label_for' => 'cdn_digital_pdf', 'desc' => 'Tamanho máximo permitido pelo seu servidor: ' . size_format( wp_max_upload_size() )] );
    add_settings_field( 'cdn_digital_cover', 'Capa (Imagem)', 'cdn_render_media_field', 'cdn_digital', 'cdn_secao_digital', ['label_for' => 'cdn_digital_cover'] );

    // === ANÚNCIOS ===
    register_setting( 'cdn_anuncios', 'cdn_ad_home_url' );
    register_setting( 'cdn_anuncios', 'cdn_ad_home_link' );
    register_setting( 'cdn_anuncios', 'cdn_sidebar_ad1_img' );
    register_setting( 'cdn_anuncios', 'cdn_sidebar_ad1_link' );
    register_setting( 'cdn_anuncios', 'cdn_sidebar_ad2_img' );
    register_setting( 'cdn_anuncios', 'cdn_sidebar_ad2_link' );
    register_setting( 'cdn_anuncios', 'cdn_sidebar_ad3_img' );
    register_setting( 'cdn_anuncios', 'cdn_sidebar_ad3_link' );

    add_settings_section( 'cdn_secao_anuncios', 'Anúncio Principal (Home)', '', 'cdn_anuncios' );
    add_settings_field( 'cdn_ad_home_url', 'Imagem do Anúncio (ex: 970x90)', 'cdn_render_media_field', 'cdn_anuncios', 'cdn_secao_anuncios', ['label_for' => 'cdn_ad_home_url'] );
    add_settings_field( 'cdn_ad_home_link', 'Link de Destino', 'cdn_render_text_field', 'cdn_anuncios', 'cdn_secao_anuncios', ['label_for' => 'cdn_ad_home_link'] );
    
    add_settings_section( 'cdn_secao_sidebar_ads', 'Anúncios da Sidebar', '', 'cdn_anuncios' );
    add_settings_field( 'cdn_sidebar_ad1_img', 'Sidebar Ad #1 - Imagem', 'cdn_render_media_field', 'cdn_anuncios', 'cdn_secao_sidebar_ads', ['label_for' => 'cdn_sidebar_ad1_img', 'desc' => 'Tamanho recomendado: 360px de largura. A altura é automática (ex: 360x250 ou 360x600)'] );
    add_settings_field( 'cdn_sidebar_ad1_link', 'Sidebar Ad #1 - Link', 'cdn_render_text_field', 'cdn_anuncios', 'cdn_secao_sidebar_ads', ['label_for' => 'cdn_sidebar_ad1_link'] );
    add_settings_field( 'cdn_sidebar_ad2_img', 'Sidebar Ad #2 - Imagem', 'cdn_render_media_field', 'cdn_anuncios', 'cdn_secao_sidebar_ads', ['label_for' => 'cdn_sidebar_ad2_img'] );
    add_settings_field( 'cdn_sidebar_ad2_link', 'Sidebar Ad #2 - Link', 'cdn_render_text_field', 'cdn_anuncios', 'cdn_secao_sidebar_ads', ['label_for' => 'cdn_sidebar_ad2_link'] );
    add_settings_field( 'cdn_sidebar_ad3_img', 'Sidebar Ad #3 - Imagem', 'cdn_render_media_field', 'cdn_anuncios', 'cdn_secao_sidebar_ads', ['label_for' => 'cdn_sidebar_ad3_img'] );
    add_settings_field( 'cdn_sidebar_ad3_link', 'Sidebar Ad #3 - Link', 'cdn_render_text_field', 'cdn_anuncios', 'cdn_secao_sidebar_ads', ['label_for' => 'cdn_sidebar_ad3_link'] );

    register_setting( 'cdn_anuncios', 'cdn_single_ad_img' );
    register_setting( 'cdn_anuncios', 'cdn_single_ad_link' );
    register_setting( 'cdn_anuncios', 'cdn_single_sidebar_ad_img' );
    register_setting( 'cdn_anuncios', 'cdn_single_sidebar_ad_link' );

    add_settings_section( 'cdn_secao_single_ads', 'Anúncios Internos (Dentro da Matéria)', '', 'cdn_anuncios' );
    add_settings_field( 'cdn_single_ad_img', 'Anúncio Meio da Matéria (728x90)', 'cdn_render_media_field', 'cdn_anuncios', 'cdn_secao_single_ads', ['label_for' => 'cdn_single_ad_img'] );
    add_settings_field( 'cdn_single_ad_link', 'Link Destino', 'cdn_render_text_field', 'cdn_anuncios', 'cdn_secao_single_ads', ['label_for' => 'cdn_single_ad_link'] );
    add_settings_field( 'cdn_single_sidebar_ad_img', 'Anúncio Sidebar Matéria (300x250)', 'cdn_render_media_field', 'cdn_anuncios', 'cdn_secao_single_ads', ['label_for' => 'cdn_single_sidebar_ad_img'] );
    add_settings_field( 'cdn_single_sidebar_ad_link', 'Link Destino', 'cdn_render_text_field', 'cdn_anuncios', 'cdn_secao_single_ads', ['label_for' => 'cdn_single_sidebar_ad_link'] );

    // === RODAPÉ (FOOTER) ===
    register_setting( 'cdn_footer', 'cdn_footer_logo' );
    register_setting( 'cdn_footer', 'cdn_footer_desc' );
    register_setting( 'cdn_footer', 'cdn_footer_copyright' );
    register_setting( 'cdn_footer', 'cdn_social_facebook' );
    register_setting( 'cdn_footer', 'cdn_social_instagram' );
    register_setting( 'cdn_footer', 'cdn_social_youtube' );
    register_setting( 'cdn_footer', 'cdn_social_twitter' );

    add_settings_section( 'cdn_secao_footer_info', 'Informações do Rodapé', '', 'cdn_footer' );
    add_settings_field( 'cdn_footer_logo', 'Logo Branca (Rodapé)', 'cdn_render_media_field', 'cdn_footer', 'cdn_secao_footer_info', ['label_for' => 'cdn_footer_logo'] );
    add_settings_field( 'cdn_footer_desc', 'Descrição', 'cdn_render_textarea_field', 'cdn_footer', 'cdn_secao_footer_info', ['label_for' => 'cdn_footer_desc', 'desc' => 'Breve descrição do portal no rodapé.'] );
    add_settings_field( 'cdn_footer_copyright', 'Copyright', 'cdn_render_text_field', 'cdn_footer', 'cdn_secao_footer_info', ['label_for' => 'cdn_footer_copyright'] );
    
    add_settings_section( 'cdn_secao_footer_social', 'Redes Sociais', '', 'cdn_footer' );
    add_settings_field( 'cdn_social_facebook', 'Facebook URL', 'cdn_render_text_field', 'cdn_footer', 'cdn_secao_footer_social', ['label_for' => 'cdn_social_facebook'] );
    add_settings_field( 'cdn_social_instagram', 'Instagram URL', 'cdn_render_text_field', 'cdn_footer', 'cdn_secao_footer_social', ['label_for' => 'cdn_social_instagram'] );
    add_settings_field( 'cdn_social_youtube', 'YouTube URL', 'cdn_render_text_field', 'cdn_footer', 'cdn_secao_footer_social', ['label_for' => 'cdn_social_youtube'] );
    add_settings_field( 'cdn_social_twitter', 'Twitter (X) URL', 'cdn_render_text_field', 'cdn_footer', 'cdn_secao_footer_social', ['label_for' => 'cdn_social_twitter'] );

    // === LANÇAMENTO DE LIVRO ===
    register_setting( 'cdn_livro', 'cdn_book_image' );
    register_setting( 'cdn_livro', 'cdn_book_badge' );
    register_setting( 'cdn_livro', 'cdn_book_title' );
    register_setting( 'cdn_livro', 'cdn_book_desc' );
    register_setting( 'cdn_livro', 'cdn_book_btn1_text' );
    register_setting( 'cdn_livro', 'cdn_book_btn1_url' );
    register_setting( 'cdn_livro', 'cdn_book_btn2_text' );
    register_setting( 'cdn_livro', 'cdn_book_btn2_url' );

    add_settings_section( 'cdn_secao_livro', 'Banner de Lançamento de Livro (Home e Sidebar)', '', 'cdn_livro' );
    add_settings_field( 'cdn_book_image', 'Foto do Livro (Capa)', 'cdn_render_media_field', 'cdn_livro', 'cdn_secao_livro', ['label_for' => 'cdn_book_image'] );
    add_settings_field( 'cdn_book_badge', 'Badge Ex: "LANÇAMENTO"', 'cdn_render_text_field', 'cdn_livro', 'cdn_secao_livro', ['label_for' => 'cdn_book_badge'] );
    add_settings_field( 'cdn_book_title', 'Título do Livro', 'cdn_render_text_field', 'cdn_livro', 'cdn_secao_livro', ['label_for' => 'cdn_book_title'] );
    add_settings_field( 'cdn_book_desc', 'Descrição curta', 'cdn_render_textarea_field', 'cdn_livro', 'cdn_secao_livro', ['label_for' => 'cdn_book_desc'] );
    
    add_settings_section( 'cdn_secao_livro_botoes', 'Botões de Ação do Livro', '', 'cdn_livro' );
    add_settings_field( 'cdn_book_btn1_text', 'Botão 1 (Ex: Saiba Mais)', 'cdn_render_text_field', 'cdn_livro', 'cdn_secao_livro_botoes', ['label_for' => 'cdn_book_btn1_text'] );
    add_settings_field( 'cdn_book_btn1_url', 'Botão 1 URL', 'cdn_render_text_field', 'cdn_livro', 'cdn_secao_livro_botoes', ['label_for' => 'cdn_book_btn1_url'] );
    add_settings_field( 'cdn_book_btn2_text', 'Botão 2 (Ex: Comprar)', 'cdn_render_text_field', 'cdn_livro', 'cdn_secao_livro_botoes', ['label_for' => 'cdn_book_btn2_text'] );
    add_settings_field( 'cdn_book_btn2_url', 'Botão 2 URL', 'cdn_render_text_field', 'cdn_livro', 'cdn_secao_livro_botoes', ['label_for' => 'cdn_book_btn2_url'] );
}

// Renderizar campos
function cdn_render_text_field( $args ) {
    $val = get_option( $args['label_for'] );
    echo '<input type="text" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['label_for'] ) . '" value="' . esc_attr( $val ) . '" class="regular-text">';
}

function cdn_render_textarea_field( $args ) {
    $val = get_option( $args['label_for'] );
    echo '<textarea id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['label_for'] ) . '" class="large-text" rows="5">' . esc_textarea( $val ) . '</textarea>';
    if ( isset( $args['desc'] ) ) echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
}

function cdn_render_color_field( $args ) {
    $val = get_option( $args['label_for'], '#f50000' );
    echo '<input type="color" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['label_for'] ) . '" value="' . esc_attr( $val ) . '">';
}

function cdn_render_media_field( $args ) {
    $val = get_option( $args['label_for'] );
    echo '<div style="display:flex; gap:10px; align-items:center;">';
    echo '<input type="text" id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $args['label_for'] ) . '" value="' . esc_attr( $val ) . '" class="regular-text">';
    echo '<button type="button" class="button cdn-upload-btn" data-target="' . esc_attr( $args['label_for'] ) . '">Selecionar Arquivo</button>';
    echo '</div>';
    if ( isset( $args['desc'] ) ) echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
}

function cdn_render_layout_field( $args ) {
    $val = get_option( $args['label_for'], 'grid' );
    ?>
    <div style="display: flex; gap: 20px; margin-top: 5px;">
        <label style="cursor:pointer;">
            <input type="radio" name="cdn_home_layout" value="grid" <?php checked( $val, 'grid' ); ?> style="margin-right:6px;">
            <span style="font-weight:600;">&#9707; Portal Grid</span>
            <p style="color:#777; font-size:12px; margin:4px 0 0 20px;">Duas matérias por linha com imagem e resumo. Leitor clica para abrir.</p>
        </label>
        <label style="cursor:pointer;">
            <input type="radio" name="cdn_home_layout" value="lista" <?php checked( $val, 'lista' ); ?> style="margin-right:6px;">
            <span style="font-weight:600;">&#9776; Portal Lista</span>
            <p style="color:#777; font-size:12px; margin:4px 0 0 20px;">Uma matéria por linha com conteúdo completo já exibido. Estilo blog/revista.</p>
        </label>
    </div>
    <?php
}

// Enqueue scripts pro Media Uploader e correção do BUG
add_action( 'admin_enqueue_scripts', 'cdn_admin_scripts' );
function cdn_admin_scripts( $hook ) {
    // Verifica se estamos na página correta do tema para incluir a Media API
    if ( strpos( $hook, 'painel-cdn' ) === false ) {
        return;
    }
    wp_enqueue_media();
}

// Página do Menu Frontend
function cdn_options_page() {
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'personalizacao';
    ?>
    <style>
        .cdn-admin-header { background: #fff; padding: 15px 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,.05); display: flex; align-items: center; gap: 15px; margin-bottom: 20px;}
        .cdn-admin-header h1 { margin: 0; padding: 0; font-size: 20px;}
        .nav-tab-wrapper { margin-bottom: 20px; }
    </style>
    <div class="wrap">
        <div class="cdn-admin-header">
            <span class="dashicons dashicons-admin-customizer" style="font-size: 28px; width: 28px; height: 28px; color: #f50000;"></span>
            <h1>Painel CDN — Opções do Portal</h1>
        </div>
        
        <h2 class="nav-tab-wrapper">
            <a href="?page=painel-cdn&tab=personalizacao" class="nav-tab <?php echo $active_tab == 'personalizacao' ? 'nav-tab-active' : ''; ?>">Appbar & Header</a>
            <a href="?page=painel-cdn&tab=footer" class="nav-tab <?php echo $active_tab == 'footer' ? 'nav-tab-active' : ''; ?>">Rodapé</a>
            <a href="?page=painel-cdn&tab=digital" class="nav-tab <?php echo $active_tab == 'digital' ? 'nav-tab-active' : ''; ?>">Jornal Digital</a>
            <a href="?page=painel-cdn&tab=anuncios" class="nav-tab <?php echo $active_tab == 'anuncios' ? 'nav-tab-active' : ''; ?>">Anúncios Gerais</a>
            <a href="?page=painel-cdn&tab=livro" class="nav-tab <?php echo $active_tab == 'livro' ? 'nav-tab-active' : ''; ?>">Lançamento de Livro</a>
            <a href="?page=painel-cdn&tab=newsletter" class="nav-tab <?php echo $active_tab == 'newsletter' ? 'nav-tab-active' : ''; ?>">✉️ Newsletter</a>
            <a href="?page=painel-cdn&tab=smtp" class="nav-tab <?php echo $active_tab == 'smtp' ? 'nav-tab-active' : ''; ?>">&#128231; E-mail / SMTP</a>
            <a href="?page=painel-cdn&tab=activity_log" class="nav-tab <?php echo $active_tab == 'activity_log' ? 'nav-tab-active' : ''; ?>" style="<?php echo $active_tab == 'activity_log' ? '' : 'background:#fff3cd;color:#856404;'; ?>">🔔 Activity Log</a>
            <a href="?page=painel-cdn&tab=ferramentas" class="nav-tab <?php echo $active_tab == 'ferramentas' ? 'nav-tab-active' : ''; ?>"><span class="dashicons dashicons-admin-tools" style="margin-top:2px"></span> Ferramentas</a>
        </h2>

        <?php if ( $active_tab === 'activity_log' ) :
            cdn_render_activity_log_tab();
        elseif ( $active_tab !== 'ferramentas' && $active_tab !== 'newsletter' && $active_tab !== 'smtp' ) : ?>
        <form method="post" action="options.php" style="background:#fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <?php
            if ( $active_tab == 'personalizacao' ) {
                settings_fields( 'cdn_personalizacao' ); do_settings_sections( 'cdn_personalizacao' );
            } elseif ( $active_tab == 'footer' ) {
                settings_fields( 'cdn_footer' ); do_settings_sections( 'cdn_footer' );
            } elseif ( $active_tab == 'digital' ) {
                settings_fields( 'cdn_digital' ); do_settings_sections( 'cdn_digital' );
            } elseif ( $active_tab == 'anuncios' ) {
                settings_fields( 'cdn_anuncios' ); do_settings_sections( 'cdn_anuncios' );
            } elseif ( $active_tab == 'livro' ) {
                settings_fields( 'cdn_livro' ); do_settings_sections( 'cdn_livro' );
            }
            submit_button( 'Salvar Configurações', 'primary', 'submit', true, ['style' => 'background:#f50000; border-color:#d00000; box-shadow:none;'] );
            ?>
        </form>
        <?php elseif ( $active_tab === 'newsletter' ) : ?>
        <!-- === ABA NEWSLETTER === -->
        <div style="background:#fff; padding: 30px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <?php cdn_render_newsletter_tab(); ?>
        </div>
        <?php elseif ( $active_tab === 'smtp' ) : ?>
        <!-- === ABA SMTP === -->
        <div style="padding: 10px 0;">
            <?php cdn_render_smtp_tab(); ?>
        </div>
        <?php else : ?>
        <!-- === ABA DE FERRAMENTAS === -->
        <div style="background:#fff; padding: 30px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); max-width:800px;">
            <div style="display:flex; gap:20px; align-items:flex-start;">
                <div style="flex:1;">
                    <h2 style="margin-top:0; color:#f50000; font-size:1.5em; border-bottom:1px solid #eee; padding-bottom:10px;">Impulsionador de Matérias (Views)</h2>
                    <p style="font-size:14px; color:#555;">Deseja bombar os números de uma matéria específica? Preencha os campos abaixo para injetar um pacote de visualizações fantasmas instantaneamente no banco de dados.</p>
                    <form method="post" action="">
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="cdn_post_id">Matéria</label></th>
                                    <td>
                                        <select name="cdn_post_id" id="cdn_post_id" required style="max-width:350px;">
                                            <option value="">-- Selecione uma matéria --</option>
                                            <?php
                                            $posts = get_posts(array(
                                                'numberposts' => -1,
                                                'post_status' => 'publish',
                                                'post_type'   => 'post',
                                                'orderby'     => 'date',
                                                'order'       => 'DESC'
                                            ));
                                            foreach($posts as $p) {
                                                echo '<option value="' . esc_attr($p->ID) . '">' . esc_html($p->post_title) . ' (ID: ' . esc_html($p->ID) . ')</option>';
                                            }
                                            ?>
                                        </select>
                                        <p class="description">Escolha a postagem que receberá as novas visualizações.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="cdn_view_count">Quantidade a Injetar</label></th>
                                    <td>
                                        <input name="cdn_view_count" type="number" id="cdn_view_count" class="regular-text" required placeholder="Ex: 500" style="max-width:150px;">
                                        <p class="description">Quantas views serão somadas de uma só vez? Recomenda-se entre 100 e 5000.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <button type="submit" name="cdn_add_views" class="button button-primary" style="background:#f50000; border-color:#d00000; box-shadow:none; padding:5px 30px;"><span class="dashicons dashicons-visibility" style="margin-top:4px;"></span> Injetar Acessos Agora</button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($){
        var custom_uploader;
        $('.cdn-upload-btn').click(function(e) {
            e.preventDefault();
            var button = $(this);
            var target = button.data('target');
            
            if (custom_uploader) {
                // Atualiza o listener para o alvo atual
                custom_uploader.off('select');
                custom_uploader.on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $('#' + target).val(attachment.url);
                });
                custom_uploader.open();
                return;
            }
            
            // Instancia o WP Media Frame pela primeira vez
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Escolha a Imagem/Arquivo',
                button: { text: 'Usar este arquivo' },
                multiple: false
            });

            custom_uploader.on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $('#' + target).val(attachment.url);
            });

            custom_uploader.open();
        });
    });
    </script>
    <?php
}
