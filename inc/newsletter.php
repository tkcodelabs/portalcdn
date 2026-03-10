<?php
/**
 * newsletter.php — Sistema completo de Newsletter por E-mail
 * Funcionalidades:
 *  - Tabela personalizada de assinantes
 *  - Inscrição via AJAX (formulário do rodapé)
 *  - Descadastro via link de token único
 *  - Disparo automático ao publicar um post
 *  - Aba de gestão no Painel CDN
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ===========================================================
// 1. CRIAÇÃO DA TABELA NO BANCO DE DADOS (ao ativar o tema)
// ===========================================================
function cdn_newsletter_create_table() {
    global $wpdb;
    $table   = $wpdb->prefix . 'cdn_newsletter';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        email      VARCHAR(200)    NOT NULL,
        name       VARCHAR(100)    DEFAULT '',
        status     ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
        token      VARCHAR(64)     NOT NULL,
        created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email),
        KEY status (status)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
add_action( 'after_switch_theme', 'cdn_newsletter_create_table' );
// Também executa na inicialização para garantir (idempotente via dbDelta)
add_action( 'init', 'cdn_newsletter_create_table' );


// ===========================================================
// 2. AJAX: INSCRIÇÃO
// ===========================================================
add_action( 'wp_ajax_cdn_newsletter_subscribe',        'cdn_ajax_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_cdn_newsletter_subscribe', 'cdn_ajax_newsletter_subscribe' );

function cdn_ajax_newsletter_subscribe() {
    // Verificar nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cdn_nonce' ) ) {
        wp_send_json_error( [ 'message' => 'Requisição inválida.' ] );
    }

    // Honeypot anti-spam
    if ( ! empty( $_POST['website'] ) ) {
        wp_send_json_success( [ 'message' => 'Inscrição realizada com sucesso!' ] ); // Silencia o bot
    }

    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $name  = isset( $_POST['name'] )  ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => 'Por favor, informe um e-mail válido.' ] );
    }

    global $wpdb;
    $table    = $wpdb->prefix . 'cdn_newsletter';
    $existing = $wpdb->get_row( $wpdb->prepare( "SELECT id, status FROM $table WHERE email = %s", $email ) );

    if ( $existing ) {
        if ( $existing->status === 'active' ) {
            wp_send_json_error( [ 'message' => 'Este e-mail já está cadastrado na nossa newsletter!' ] );
        }
        // Re-ativar descadastrado
        $wpdb->update( $table, [ 'status' => 'active', 'name' => $name ], [ 'id' => $existing->id ] );
        wp_send_json_success( [ 'message' => '🎉 Bem-vindo de volta! Sua inscrição foi reativada.' ] );
    }

    $token = wp_generate_password( 40, false );
    $wpdb->insert( $table, [
        'email'  => $email,
        'name'   => $name,
        'status' => 'active',
        'token'  => $token,
    ] );

    // E-mail de confirmação ao assinante
    cdn_send_confirmation_email( $email, $name, $token );

    wp_send_json_success( [ 'message' => '🎉 Inscrição realizada! Verifique seu e-mail para confirmar.' ] );
}


// ===========================================================
// 3. DESCADASTRO via TOKEN (link no e-mail)
// ===========================================================
add_action( 'init', 'cdn_handle_unsubscribe' );
function cdn_handle_unsubscribe() {
    if ( ! isset( $_GET['cdn_unsub'] ) || empty( $_GET['cdn_unsub'] ) ) return;

    $token = sanitize_text_field( wp_unslash( $_GET['cdn_unsub'] ) );

    global $wpdb;
    $table = $wpdb->prefix . 'cdn_newsletter';
    $row   = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $table WHERE token = %s", $token ) );

    if ( $row ) {
        $wpdb->update( $table, [ 'status' => 'unsubscribed' ], [ 'id' => $row->id ] );
        wp_die(
            '<div style="font-family:sans-serif;text-align:center;padding:60px 20px;"><h2>✅ Descadastro realizado</h2><p>Você foi removido da nossa lista de newsletter com sucesso.</p><a href="' . esc_url( home_url() ) . '" style="color:#c00;">Voltar ao site</a></div>',
            'Descadastro - ' . get_bloginfo( 'name' ),
            [ 'response' => 200 ]
        );
    } else {
        wp_die( '<p>Link inválido ou já processado.</p><a href="' . esc_url( home_url() ) . '">Voltar</a>', 'Erro', [ 'response' => 404 ] );
    }
}


// ===========================================================
// 4. DISPARO AUTOMÁTICO AO PUBLICAR UM POST
// ===========================================================
add_action( 'transition_post_status', 'cdn_send_newsletter_on_publish', 10, 3 );
function cdn_send_newsletter_on_publish( $new_status, $old_status, $post ) {
    // Só posts normais publicados pela primeira vez
    if ( $new_status !== 'publish' || $old_status === 'publish' ) return;
    if ( $post->post_type !== 'post' ) return;
    // Não disparar em atualizações agendadas automáticas de rascunhos antigos
    if ( wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) return;

    // Agenda para 30 segundos depois (para o post estar totalmente salvo)
    wp_schedule_single_event( time() + 30, 'cdn_dispatch_newsletter', [ $post->ID ] );
}

add_action( 'cdn_dispatch_newsletter', 'cdn_do_dispatch_newsletter' );
function cdn_do_dispatch_newsletter( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post || $post->post_status !== 'publish' ) return;

    global $wpdb;
    $table       = $wpdb->prefix . 'cdn_newsletter';
    $subscribers = $wpdb->get_results( "SELECT email, name, token FROM $table WHERE status = 'active'" );

    if ( empty( $subscribers ) ) return;

    $thumb_url   = get_the_post_thumbnail_url( $post_id, 'large' ) ?: '';

    // Remover imagem se a URL for de localhost (clientes de e-mail não conseguem acessar)
    if ( $thumb_url ) {
        $host = parse_url( $thumb_url, PHP_URL_HOST );
        if ( in_array( $host, [ 'localhost', '127.0.0.1', '::1' ], true ) || str_ends_with( $host ?? '', '.local' ) ) {
            $thumb_url = ''; // Omite a imagem no e-mail para URLs locais
        }
    }

    $post_url    = get_permalink( $post_id );
    $post_title  = get_the_title( $post_id );
    $post_excerpt = has_excerpt( $post_id )
        ? get_the_excerpt( $post_id )
        : wp_trim_words( strip_shortcodes( wp_strip_all_tags( $post->post_content ) ), 30, '...' );

    $cats    = get_the_category( $post_id );
    $cat_name = $cats ? $cats[0]->name : '';
    $author  = get_the_author_meta( 'display_name', $post->post_author );
    $date    = get_the_date( 'd/m/Y', $post );
    $site    = get_bloginfo( 'name' );
    $site_url = home_url();
    $logo_url = get_option( 'cdn_logo' ) ?: '';

    // Remover logo se também for localhost
    if ( $logo_url ) {
        $logo_host = parse_url( $logo_url, PHP_URL_HOST );
        if ( in_array( $logo_host, [ 'localhost', '127.0.0.1', '::1' ], true ) || str_ends_with( $logo_host ?? '', '.local' ) ) {
            $logo_url = '';
        }
    }

    foreach ( $subscribers as $sub ) {
        $unsub_url = add_query_arg( 'cdn_unsub', $sub->token, home_url( '/' ) );
        $greeting  = $sub->name ? ', ' . esc_html( $sub->name ) : '';
        $subject   = '📰 ' . $post_title . ' — ' . $site;

        $html = cdn_newsletter_email_template( [
            'site'       => $site,
            'site_url'   => $site_url,
            'logo_url'   => $logo_url,
            'greeting'   => $greeting,
            'cat_name'   => $cat_name,
            'post_title' => $post_title,
            'post_url'   => $post_url,
            'thumb_url'  => $thumb_url,
            'excerpt'    => $post_excerpt,
            'author'     => $author,
            'date'       => $date,
            'unsub_url'  => $unsub_url,
        ] );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        wp_mail( $sub->email, $subject, $html, $headers );
    }

    // Salva o log do último disparo
    update_option( 'cdn_newsletter_last_dispatch', [
        'post_id'    => $post_id,
        'post_title' => $post_title,
        'sent'       => count( $subscribers ),
        'date'       => current_time( 'mysql' ),
    ] );
}


// ===========================================================
// 5. TEMPLATE DE E-MAIL HTML
// ===========================================================
function cdn_newsletter_email_template( $d ) {
    $thumb_block = '';
    if ( $d['thumb_url'] ) {
        $thumb_block = '<a href="' . esc_url( $d['post_url'] ) . '"><img src="' . esc_url( $d['thumb_url'] ) . '" alt="" style="width:100%;max-height:360px;object-fit:cover;display:block;border-radius:8px 8px 0 0;"></a>';
    }

    $logo_block = '';
    if ( $d['logo_url'] ) {
        $logo_block = '<img src="' . esc_url( $d['logo_url'] ) . '" alt="' . esc_attr( $d['site'] ) . '" style="max-height:40px;width:auto;filter:brightness(0) invert(1);">';
    } else {
        $logo_block = '<span style="font-size:22px;font-weight:900;letter-spacing:-1px;color:#fff;">' . esc_html( strtoupper( $d['site'] ) ) . '</span>';
    }

    $cat_block = $d['cat_name'] ? '<span style="background:#c0392b;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:3px;text-transform:uppercase;letter-spacing:1px;">' . esc_html( $d['cat_name'] ) . '</span><br><br>' : '';

    return '<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . esc_html( $d['site'] ) . '</title></head>
<body style="margin:0;padding:0;background:#f4f4f8;font-family:Georgia,\'Times New Roman\',serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f8;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">

  <!-- Header -->
  <tr><td style="background:#1a1a2e;padding:28px 36px;text-align:left;">' . $logo_block . '</td></tr>

  <!-- Hero Imagem -->
  <tr><td>' . $thumb_block . '</td></tr>

  <!-- Conteúdo -->
  <tr><td style="padding:36px 40px 24px;">
    ' . $cat_block . '
    <h1 style="margin:0 0 16px;font-size:26px;font-weight:900;color:#1a1a2e;line-height:1.25;">' . esc_html( $d['post_title'] ) . '</h1>
    <p style="margin:0 0 8px;font-size:13px;color:#888;">Por <strong>' . esc_html( $d['author'] ) . '</strong> · ' . esc_html( $d['date'] ) . '</p>
    <p style="margin:0 0 28px;font-size:16px;line-height:1.75;color:#444;">' . esc_html( $d['excerpt'] ) . '</p>
    <a href="' . esc_url( $d['post_url'] ) . '" style="background:#c0392b;color:#ffffff;text-decoration:none;font-family:Arial,sans-serif;font-size:14px;font-weight:700;padding:14px 32px;border-radius:6px;display:inline-block;letter-spacing:.5px;">LER MATÉRIA COMPLETA →</a>
  </td></tr>

  <!-- Divisor -->
  <tr><td style="padding:0 40px;"><hr style="border:none;border-top:1px solid #eee;"></td></tr>

  <!-- Rodapé -->
  <tr><td style="padding:24px 40px;text-align:center;">
    <p style="font-family:Arial,sans-serif;font-size:13px;color:#888;margin:0 0 8px;">Você está recebendo este e-mail porque se inscreveu na newsletter do <strong>' . esc_html( $d['site'] ) . '</strong>.</p>
    <p style="font-family:Arial,sans-serif;font-size:12px;color:#aaa;margin:0;">
      <a href="' . esc_url( $d['site_url'] ) . '" style="color:#c0392b;text-decoration:none;">Visitar o site</a>
      &nbsp;·&nbsp;
      <a href="' . esc_url( $d['unsub_url'] ) . '" style="color:#aaa;text-decoration:underline;">Cancelar inscrição</a>
    </p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>';
}


// ===========================================================
// 6. E-MAIL DE CONFIRMAÇÃO PARA O NOVO ASSINANTE
// ===========================================================
function cdn_send_confirmation_email( $email, $name, $token ) {
    $site      = get_bloginfo( 'name' );
    $site_url  = home_url();
    $unsub_url = add_query_arg( 'cdn_unsub', $token, home_url( '/' ) );
    $greeting  = $name ? 'Olá, ' . esc_html( $name ) . '!' : 'Olá!';
    $subject   = '✅ Bem-vindo à newsletter do ' . $site;

    $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f4f4f8;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f8;padding:40px 20px;"><tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
  <tr><td style="background:#1a1a2e;padding:28px 36px;"><span style="font-size:22px;font-weight:900;color:#fff;letter-spacing:-1px;">' . esc_html( strtoupper( $site ) ) . '</span></td></tr>
  <tr><td style="padding:40px;">
    <h2 style="margin:0 0 16px;color:#1a1a2e;">' . $greeting . '</h2>
    <p style="color:#444;line-height:1.7;margin:0 0 24px;">Sua inscrição na newsletter do <strong>' . esc_html( $site ) . '</strong> foi confirmada com sucesso! 🎉<br>A partir de agora, você receberá nossas principais matérias diretamente no seu e-mail.</p>
    <a href="' . esc_url( $site_url ) . '" style="background:#c0392b;color:#fff;text-decoration:none;font-size:14px;font-weight:700;padding:12px 28px;border-radius:6px;display:inline-block;">IR PARA O PORTAL</a>
  </td></tr>
  <tr><td style="padding:20px 40px;text-align:center;border-top:1px solid #eee;">
    <p style="font-size:12px;color:#aaa;margin:0;">Não se inscreveu? <a href="' . esc_url( $unsub_url ) . '" style="color:#aaa;">Cancele aqui</a>.</p>
  </td></tr>
</table></td></tr></table></body></html>';

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site . ' <' . get_option( 'admin_email' ) . '>',
    ];

    wp_mail( $email, $subject, $html, $headers );
}


// ===========================================================
// 7. ABA "NEWSLETTER" NO PAINEL CDN
// ===========================================================

// AJAX: Disparo manual
add_action( 'wp_ajax_cdn_manual_dispatch', 'cdn_ajax_manual_dispatch' );
function cdn_ajax_manual_dispatch() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( [ 'message' => 'Sem permissão.' ] );
    check_ajax_referer( 'cdn_manual_dispatch_nonce', 'nonce' );

    $post_id = intval( $_POST['post_id'] ?? 0 );
    if ( $post_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Selecione uma matéria válida.' ] );
    }

    $post = get_post( $post_id );
    if ( ! $post ) {
        wp_send_json_error( [ 'message' => 'Matéria não encontrada.' ] );
    }

    // Executar o disparo direto (sem agendar cron, pois é manual)
    cdn_do_dispatch_newsletter( $post_id );

    global $wpdb;
    $table = $wpdb->prefix . 'cdn_newsletter';
    $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'active'" );
    $last  = get_option( 'cdn_newsletter_last_dispatch' );

    wp_send_json_success( [
        'message' => '&#10003; Newsletter disparada para <strong>' . $count . '</strong> assinante(s)!',
        'last'    => $last,
        'count'   => $count,
    ] );
}

function cdn_render_newsletter_tab() {
    global $wpdb;
    $table = $wpdb->prefix . 'cdn_newsletter';

    // Garante que a tabela existe
    cdn_newsletter_create_table();

    $total_active = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'active'" );
    $total_unsub  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'unsubscribed'" );
    $last         = get_option( 'cdn_newsletter_last_dispatch' );
    $subscribers  = $wpdb->get_results( "SELECT id, email, name, status, created_at FROM $table ORDER BY created_at DESC LIMIT 100" );

    // Processar exclusão individual via form normal
    if ( isset( $_POST['cdn_del_subscriber'] ) && check_admin_referer( 'cdn_del_sub' ) ) {
        $del_id = intval( $_POST['cdn_del_subscriber'] );
        $wpdb->delete( $table, [ 'id' => $del_id ] );
        echo '<div class="notice notice-success is-dismissible"><p>Assinante removido.</p></div>';
        $subscribers  = $wpdb->get_results( "SELECT id, email, name, status, created_at FROM $table ORDER BY created_at DESC LIMIT 100" );
        $total_active = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'active'" );
    }

    $posts = get_posts( [ 'numberposts' => 30, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] );
    ?>
    <div style="max-width:960px;">

    <div style="max-width:960px;">

      <!-- Modal de Confirmação Customizado -->
      <div id="cdn-nl-confirm-modal" style="display:none;position:fixed;inset:0;background:rgba(26,26,46,0.8);backdrop-filter:blur(4px);z-index:999999;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;width:100%;max-width:440px;border-radius:16px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,0.3);animation:cdn-modal-in 0.3s ease-out;">
          <div style="background:#1a1a2e;padding:24px;text-align:center;">
            <div style="font-size:40px;margin-bottom:10px;">✉️</div>
            <h3 style="margin:0;color:#fff;font-size:20px;font-weight:700;">Confirmar Envio?</h3>
          </div>
          <div style="padding:30px;text-align:center;">
            <p style="margin:0;color:#1a1a2e;font-size:15px;line-height:1.5;">Você está prestes a disparar a newsletter para todos os <strong id="cdn-confirm-count"><?php echo $total_active; ?></strong> assinantes ativos.</p>
            <div style="background:#f4f4f8;padding:12px;border-radius:8px;margin-top:16px;text-align:left;border-left:4px solid #c0392b;">
                <span style="font-size:11px;text-transform:uppercase;color:#888;font-weight:700;display:block;margin-bottom:4px;">Matéria Selecionada:</span>
                <strong id="cdn-confirm-post-title" style="color:#333;font-size:13px;word-break:break-word;">—</strong>
            </div>
          </div>
          <div style="padding:0 30px 30px;display:flex;gap:12px;">
            <button type="button" id="cdn-confirm-cancel" style="flex:1;padding:12px;border:1px solid #ddd;background:#fff;border-radius:8px;font-weight:600;color:#666;cursor:pointer;transition:all .2s;">Voltar</button>
            <button type="button" id="cdn-confirm-execute" style="flex:1.5;padding:12px;border:none;background:#c0392b;color:#fff;border-radius:8px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(192,57,43,0.3);transition:all .2s;">Sim, Disparar Agora</button>
          </div>
        </div>
      </div>

      <!-- Loading Overlay (disparo manual) -->
      <div id="cdn-nl-overlay" style="display:none;position:fixed;inset:0;background:rgba(26,26,46,0.9);backdrop-filter:blur(8px);z-index:999999;align-items:center;justify-content:center;flex-direction:column;gap:16px;">
        <div style="text-align:center;">
          <div style="width:60px;height:60px;border:5px solid rgba(255,255,255,0.1);border-top-color:#c0392b;border-radius:50%;animation:cdn-spin 0.8s linear infinite;margin:0 auto 24px;"></div>
          <h2 style="margin:0;font-size:22px;font-weight:900;color:#fff;letter-spacing:-0.5px;">Enviando Newsletter...</h2>
          <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.6);">Não feche esta janela enquanto o processo não for concluído.</p>
        </div>
      </div>

      <style>
        @keyframes cdn-spin{to{transform:rotate(360deg)}}
        @keyframes cdn-modal-in{from{opacity:0;transform:translateY(20px) scale(0.95)}to{opacity:1;transform:translateY(0) scale(1)}}
        #cdn-confirm-cancel:hover{background:#f8f8f8;color:#333;}
        #cdn-confirm-execute:hover{background:#a22d21;transform:translateY(-2px);box-shadow:0 6px 15px rgba(192,57,43,0.4);}
      </style>

      <!-- Mensagem de resultado do disparo -->
      <div id="cdn-nl-result" style="display:none;padding:16px 20px;border-radius:10px;margin-bottom:24px;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.05);border:1px solid transparent;"></div>

      <!-- Stats -->
      <div id="cdn-nl-stats" style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:28px;">
        <div style="background:#f0f9f0;border:1px solid #b3d9b3;border-radius:12px;padding:24px 30px;flex:1;min-width:160px;box-shadow:subtle;">
          <div id="cdn-stat-active" style="font-size:36px;font-weight:900;color:#2e7d32;"><?php echo $total_active; ?></div>
          <div style="color:#555;font-size:14px;margin-top:6px;font-weight:500;">Assinantes Ativos</div>
        </div>
        <div style="background:#fff8f0;border:1px solid #ffe0b2;border-radius:12px;padding:24px 30px;flex:1;min-width:160px;">
          <div style="font-size:36px;font-weight:900;color:#e65100;"><?php echo $total_unsub; ?></div>
          <div style="color:#555;font-size:14px;margin-top:6px;font-weight:500;">Descadastrados</div>
        </div>
        <div id="cdn-last-dispatch-card" style="background:#f8f8ff;border:1px solid #c5cae9;border-radius:12px;padding:24px 30px;flex:2;min-width:220px;<?php echo $last ? '' : 'display:none;'; ?>">
          <div style="font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:6px;display:flex;align-items:center;gap:6px;"><span>📡</span> Último Disparo</div>
          <div id="cdn-last-title" style="font-size:14px;color:#333;font-weight:600;"><?php echo $last ? esc_html( $last['post_title'] ) : ''; ?></div>
          <div id="cdn-last-meta" style="font-size:12px;color:#777;margin-top:6px;"><?php echo $last ? esc_html( $last['date'] ) . ' · ' . $last['sent'] . ' envios' : ''; ?></div>
        </div>
      </div>

      <!-- Disparo Manual (AJAX) -->
      <div style="background:#fff;border:1px solid #ddd;border-radius:12px;padding:28px;margin-bottom:28px;box-shadow:0 4px 15px rgba(0,0,0,0.03);">
        <h3 style="margin:0 0 14px;font-size:18px;">✉️ Enviar Newsletter Manualmente</h3>
        <p style="color:#666;font-size:14px;margin:0 0 20px;">Selecione a matéria e utilize o disparo manual se precisar reenviar ou disparar uma matéria antiga.</p>
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
          <select id="cdn-dispatch-post-id" style="flex:1;min-width:260px;padding:10px;border-radius:8px;border:1px solid #ccc;font-size:14px;">
            <option value="">— Selecione uma matéria —</option>
            <?php foreach ( $posts as $p ) : ?>
              <option value="<?php echo $p->ID; ?>" data-title="<?php echo esc_attr($p->post_title); ?>"><?php echo esc_html( $p->post_title ); ?> (<?php echo get_the_date( 'd/m/Y', $p ); ?>)</option>
            <?php endforeach; ?>
          </select>
          <button type="button" id="cdn-dispatch-btn" class="button button-primary" style="padding:8px 24px;border-radius:8px;height:42px;background:#1a1a2e;border:none;">🚀 Preparar Disparo</button>
        </div>
      </div>

      <script>
      (function(){
        // Elementos
        var btn           = document.getElementById('cdn-dispatch-btn');
        var sel           = document.getElementById('cdn-dispatch-post-id');
        var overlay       = document.getElementById('cdn-nl-overlay');
        var result        = document.getElementById('cdn-nl-result');
        var modal         = document.getElementById('cdn-nl-confirm-modal');
        var modalCancel   = document.getElementById('cdn-confirm-cancel');
        var modalExecute  = document.getElementById('cdn-confirm-execute');
        var confirmTitle  = document.getElementById('cdn-confirm-post-title');
        var confirmCount  = document.getElementById('cdn-confirm-count');

        if (!btn) return;

        // Abrir Modal de Confirmação
        btn.addEventListener('click', function(){
          var postId = sel.value;
          if (!postId) { alert('Por favor, selecione uma matéria.'); return; }
          
          var postTitle = sel.options[sel.selectedIndex].getAttribute('data-title');
          confirmTitle.textContent = postTitle;
          
          modal.style.display = 'flex';
          result.style.display = 'none';
        });

        // Fechar modal ao cancelar
        modalCancel.addEventListener('click', function(){
           modal.style.display = 'none';
        });

        // Fechar ao clicar fora (no escuro)
        modal.addEventListener('click', function(e){
            if(e.target === modal) modal.style.display = 'none';
        });

        // Executar Envio Real
        modalExecute.addEventListener('click', function(){
          var postId = sel.value;
          
          modal.style.display = 'none';
          overlay.style.display = 'flex';

          var fd = new FormData();
          fd.append('action', 'cdn_manual_dispatch');
          fd.append('nonce', '<?php echo wp_create_nonce( 'cdn_manual_dispatch_nonce' ); ?>');
          fd.append('post_id', postId);

          fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(res){
              overlay.style.display = 'none';
              result.style.display = 'block';

              if (res.success) {
                result.style.background = '#e8f5e9';
                result.style.border     = '1px solid #a5d6a7';
                result.style.color      = '#2e7d32';
                result.innerHTML        = res.data.message;

                if (res.data.last) {
                  var card = document.getElementById('cdn-last-dispatch-card');
                  card.style.display = 'block';
                  document.getElementById('cdn-last-title').textContent = res.data.last.post_title;
                  document.getElementById('cdn-last-meta').textContent  = res.data.last.date + ' · ' + res.data.last.sent + ' envios';
                }
                if (res.data.count !== undefined) {
                  document.getElementById('cdn-stat-active').textContent = res.data.count;
                  confirmCount.textContent = res.data.count;
                }
              } else {
                result.style.background = '#fce4ec';
                result.style.border     = '1px solid #f48fb1';
                result.style.color      = '#c62828';
                result.innerHTML        = '❌ ' + (res.data ? res.data.message : 'Erro ao processar envio.');
              }
            })
            .catch(function(){
              overlay.style.display = 'none';
              result.style.display  = 'block';
              result.style.background = '#fce4ec';
              result.style.border     = '1px solid #f48fb1';
              result.style.color      = '#c62828';
              result.textContent      = '❌ Erro crítico de conexão.';
            });
        });
      })();
      </script>

      <!-- Lista de Assinantes -->

      <div style="background:#fff;border:1px solid #ddd;border-radius:8px;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between;">
          <h3 style="margin:0;">📋 Assinantes (últimos 100)</h3>
          <a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=cdn_export_newsletter&nonce=' . wp_create_nonce( 'cdn_export' ) ) ); ?>" class="button">⬇ Exportar CSV</a>
        </div>
        <?php if ( $subscribers ) : ?>
        <table class="widefat striped" style="border:none;">
          <thead>
            <tr>
              <th>#</th>
              <th>E-mail</th>
              <th>Nome</th>
              <th>Status</th>
              <th>Data</th>
              <th>Ação</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ( $subscribers as $i => $sub ) : ?>
            <tr>
              <td><?php echo $sub->id; ?></td>
              <td><?php echo esc_html( $sub->email ); ?></td>
              <td><?php echo esc_html( $sub->name ?: '—' ); ?></td>
              <td>
                <?php if ( $sub->status === 'active' ) : ?>
                  <span style="background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">Ativo</span>
                <?php else : ?>
                  <span style="background:#fce4ec;color:#c62828;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">Descadastrado</span>
                <?php endif; ?>
              </td>
              <td style="font-size:12px;color:#888;"><?php echo date_i18n( 'd/m/Y H:i', strtotime( $sub->created_at ) ); ?></td>
              <td>
                <form method="post" style="display:inline;" onsubmit="return confirm('Remover permanentemente?');">
                  <?php wp_nonce_field( 'cdn_del_sub' ); ?>
                  <input type="hidden" name="cdn_del_subscriber" value="<?php echo $sub->id; ?>">
                  <button type="submit" class="button button-small" style="color:#c00;">Remover</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else : ?>
          <p style="padding:24px;color:#888;text-align:center;">Nenhum assinante ainda. O formulário do rodapé está pronto para receber inscrições!</p>
        <?php endif; ?>
      </div>

    </div>
    <?php
}


// ===========================================================
// 8. EXPORTAR CSV DE ASSINANTES
// ===========================================================
add_action( 'wp_ajax_cdn_export_newsletter', 'cdn_export_newsletter_csv' );
function cdn_export_newsletter_csv() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sem permissão.' );
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ?? '' ) ), 'cdn_export' ) ) wp_die( 'Nonce inválido.' );

    global $wpdb;
    $table = $wpdb->prefix . 'cdn_newsletter';
    $rows  = $wpdb->get_results( "SELECT email, name, status, created_at FROM $table ORDER BY created_at DESC", ARRAY_A );

    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=newsletter-assinantes-' . date( 'Y-m-d' ) . '.csv' );

    $out = fopen( 'php://output', 'w' );
    fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM para Excel
    fputcsv( $out, [ 'E-mail', 'Nome', 'Status', 'Data de Inscrição' ] );
    foreach ( $rows as $row ) {
        fputcsv( $out, $row );
    }
    fclose( $out );
    exit;
}
