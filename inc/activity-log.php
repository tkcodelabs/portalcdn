<?php
/**
 * Activity Log — Correio do Norte
 * Monitora eventos WordPress + integridade de arquivos via WP-Cron
 */

// ============================================================
// 1. BANCO DE DADOS — Criar tabela na ativação do tema
// ============================================================
function cdn_log_create_table() {
    global $wpdb;
    $table   = $wpdb->prefix . 'cdn_activity_log';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        event_type  VARCHAR(60)  NOT NULL,
        description TEXT         NOT NULL,
        user_login  VARCHAR(60)  DEFAULT '',
        ip_address  VARCHAR(45)  DEFAULT '',
        created_at  DATETIME     NOT NULL,
        PRIMARY KEY (id),
        KEY event_type (event_type),
        KEY created_at (created_at)
    ) {$charset};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
add_action( 'after_switch_theme', 'cdn_log_create_table' );
add_action( 'init', function() {
    // Garante a tabela mesmo em temas atualizados sem reativação
    static $checked = false;
    if ( $checked ) return;
    $checked = true;
    global $wpdb;
    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}cdn_activity_log'" ) !== $wpdb->prefix . 'cdn_activity_log' ) {
        cdn_log_create_table();
    }
} );

// ============================================================
// 2. FUNÇÃO CENTRAL: Registrar evento + enviar e-mail
// ============================================================
function cdn_log_event( string $type, string $description ) {
    global $wpdb;

    $user      = wp_get_current_user();
    $user_login= $user && $user->ID ? $user->user_login : '(sistema)';
    $ip        = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '—' );

    $wpdb->insert(
        $wpdb->prefix . 'cdn_activity_log',
        [
            'event_type'  => sanitize_text_field( $type ),
            'description' => sanitize_text_field( $description ),
            'user_login'  => $user_login,
            'ip_address'  => $ip,
            'created_at'  => current_time( 'mysql' ),
        ],
        [ '%s', '%s', '%s', '%s', '%s' ]
    );

    // Notificação imediata (modo padrão)
    $mode = get_option( 'cdn_log_notify', 'immediate' );
    if ( $mode === 'immediate' ) {
        cdn_log_send_email( $type, $description, $user_login, $ip );
    }
}

// ============================================================
// 3. ENVIO DE E-MAIL
// ============================================================
function cdn_log_send_email( string $type, string $description, string $user_login, string $ip ) {
    $dest    = get_option( 'cdn_log_email' ) ?: get_option( 'admin_email' );
    $site    = get_bloginfo( 'name' );
    $now     = current_time( 'd/m/Y H:i:s' );
    $subject = "[{$site}] Alteração detectada: {$type}";

    $icon_map = [
        'post'          => '📝',
        'page'          => '📄',
        'media'         => '🖼️',
        'user'          => '👤',
        'plugin'        => '🔌',
        'theme'         => '🎨',
        'settings'      => '⚙️',
        'file_integrity'=> '🔒',
        'painel_cdn'    => '🛠️',
    ];
    $icon = $icon_map[ $type ] ?? '🔔';

    $color_map = [
        'post'           => '#22609e',
        'page'           => '#22609e',
        'media'          => '#ec7153',
        'user'           => '#9dcc52',
        'plugin'         => '#f59e0b',
        'theme'          => '#8b5cf6',
        'settings'       => '#64748b',
        'file_integrity' => '#e72127',
        'painel_cdn'     => '#22609e',
    ];
    $color = $color_map[ $type ] ?? '#22609e';

    $body = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f8;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 16px;">
<tr><td align="center">
<table width="540" style="max-width:540px;width:100%;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
  <tr><td style="background:' . esc_attr( $color ) . ';padding:20px 28px;">
    <h2 style="margin:0;color:#fff;font-size:17px;">' . esc_html( $icon . ' Alteração Detectada — ' . $site ) . '</h2>
  </td></tr>
  <tr><td style="padding:28px;">
    <table width="100%" style="border-collapse:collapse;">
      <tr><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:13px;color:#888;width:120px;">Evento</td><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:14px;font-weight:700;color:#1a1a2e;">' . esc_html( $type ) . '</td></tr>
      <tr><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:13px;color:#888;">Detalhe</td><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:14px;color:#333;">' . esc_html( $description ) . '</td></tr>
      <tr><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:13px;color:#888;">Usuário</td><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:14px;">' . esc_html( $user_login ) . '</td></tr>
      <tr><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:13px;color:#888;">IP</td><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:13px;color:#64748b;">' . esc_html( $ip ) . '</td></tr>
    </table>
  </td></tr>
  <tr><td style="padding:14px 28px;background:#f8f8f8;text-align:center;">
    <p style="margin:0;font-size:11px;color:#aaa;">' . esc_html( $now ) . ' — <a href="' . esc_url( admin_url('themes.php?page=painel-cdn&tab=activity_log') ) . '" style="color:#22609e;">Ver Log Completo</a></p>
  </td></tr>
</table>
</td></tr>
</table></body></html>';

    wp_mail( $dest, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
}

// ============================================================
// 4. DIGEST DIÁRIO — WP-Cron
// ============================================================
add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['cdn_daily_8am'] = [
        'interval' => DAY_IN_SECONDS,
        'display'  => 'Diário (07:00)',
    ];
    return $schedules;
} );

function cdn_log_schedule_digest() {
    if ( ! wp_next_scheduled( 'cdn_log_daily_digest' ) ) {
        wp_schedule_event( strtotime( 'tomorrow 07:00:00' ), 'cdn_daily_8am', 'cdn_log_daily_digest' );
    }
}
add_action( 'init', 'cdn_log_schedule_digest' );

add_action( 'cdn_log_daily_digest', function() {
    if ( get_option( 'cdn_log_notify', 'immediate' ) !== 'digest' ) return;
    global $wpdb;
    $table  = $wpdb->prefix . 'cdn_activity_log';
    $events = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE created_at >= %s ORDER BY created_at DESC LIMIT 500",
            gmdate( 'Y-m-d 00:00:00', strtotime( 'yesterday' ) )
        )
    );
    if ( ! $events ) return;

    $site = get_bloginfo( 'name' );
    $dest = get_option( 'cdn_log_email' ) ?: get_option( 'admin_email' );
    $rows = '';
    foreach ( $events as $e ) {
        $rows .= '<tr>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;font-size:12px;color:#888;">' . esc_html( date_i18n( 'd/m H:i', strtotime( $e->created_at ) ) ) . '</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;font-size:13px;font-weight:600;">' . esc_html( $e->event_type ) . '</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;font-size:13px;">' . esc_html( $e->description ) . '</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;font-size:12px;color:#64748b;">' . esc_html( $e->user_login ) . '</td>
        </tr>';
    }

    $body = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f8;padding:24px 16px;">
<table width="620" style="max-width:620px;width:100%;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;">
  <tr><td style="background:#1a1a2e;padding:20px 28px;"><h2 style="color:#fff;margin:0;font-size:17px;">📋 Resumo Diário de Atividades — ' . esc_html($site) . '</h2></td></tr>
  <tr><td style="padding:24px;">
    <table width="100%" style="border-collapse:collapse;">
      <thead><tr>
        <th style="text-align:left;padding:8px 10px;background:#f8f8ff;font-size:12px;color:#888;">DATA</th>
        <th style="text-align:left;padding:8px 10px;background:#f8f8ff;font-size:12px;color:#888;">EVENTO</th>
        <th style="text-align:left;padding:8px 10px;background:#f8f8ff;font-size:12px;color:#888;">DETALHE</th>
        <th style="text-align:left;padding:8px 10px;background:#f8f8ff;font-size:12px;color:#888;">USUÁRIO</th>
      </tr></thead>
      <tbody>' . $rows . '</tbody>
    </table>
  </td></tr>
  <tr><td style="padding:14px 28px;background:#f8f8f8;text-align:center;">
    <a href="' . esc_url( admin_url('themes.php?page=painel-cdn&tab=activity_log') ) . '" style="color:#22609e;font-size:12px;">Ver log completo no Painel CDN</a>
  </td></tr>
</table></body></html>';

    wp_mail( $dest, "[{$site}] Resumo Diário de Atividades — " . date_i18n('d/m/Y'), $body, ['Content-Type: text/html; charset=UTF-8'] );
} );

// ============================================================
// 5. HOOKS WORDPRESS — Monitorar ações no admin
// ============================================================

// Posts & Páginas
add_action( 'transition_post_status', function( $new, $old, $post ) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post ) ) return;
    $type = $post->post_type === 'page' ? 'page' : 'post';

    if ( $new === 'publish' && $old !== 'publish' ) {
        cdn_log_event( $type, "Publicado: \"{$post->post_title}\" (ID {$post->ID})" );
    } elseif ( $new === 'publish' && $old === 'publish' ) {
        cdn_log_event( $type, "Atualizado: \"{$post->post_title}\" (ID {$post->ID})" );
    } elseif ( $new === 'trash' ) {
        cdn_log_event( $type, "Movido para lixeira: \"{$post->post_title}\" (ID {$post->ID})" );
    }
}, 10, 3 );

// Upload de mídia
add_action( 'add_attachment', function( $attach_id ) {
    cdn_log_event( 'media', 'Arquivo enviado: ' . get_the_title( $attach_id ) . ' (ID ' . $attach_id . ')' );
} );

// Login de usuário
add_action( 'wp_login', function( $user_login, $user ) {
    // Logar apenas admins
    if ( ! user_can( $user, 'manage_options' ) ) return;
    cdn_log_event( 'user', "Login de administrador: {$user_login}" );
}, 10, 2 );

// Novo usuário
add_action( 'user_register', function( $user_id ) {
    $user = get_userdata( $user_id );
    cdn_log_event( 'user', "Novo usuário cadastrado: {$user->user_login} ({$user->user_email})" );
} );

// Plugins
add_action( 'activated_plugin', function( $plugin ) {
    cdn_log_event( 'plugin', "Plugin ativado: {$plugin}" );
} );
add_action( 'deactivated_plugin', function( $plugin ) {
    cdn_log_event( 'plugin', "Plugin desativado: {$plugin}" );
} );

// Troca de tema
add_action( 'switch_theme', function( $new_name ) {
    cdn_log_event( 'theme', "Tema ativo alterado para: {$new_name}" );
} );

// Configurações gerais (filtramos opções internas do WP para evitar spam)
add_action( 'updated_option', function( $option_name ) {
    // Lista de prefixos/opções internas para ignorar
    $ignore = [ '_transient', '_site_transient', 'auth_key', 'secure_auth_key',
                'logged_in_key', 'nonce_key', 'cron', 'rewrite_rules',
                'siteurl_migrated', 'cdn_log_file_baseline' ];
    foreach ( $ignore as $skip ) {
        if ( str_contains( $option_name, $skip ) ) return;
    }
    // Focar apenas em opções relevantes ou do Painel CDN
    $relevant_prefixes = [ 'cdn_', 'blogname', 'blogdescription', 'admin_email',
                           'users_can_register', 'default_role', 'permalink_structure',
                           'siteurl', 'home' ];
    $log = false;
    foreach ( $relevant_prefixes as $prefix ) {
        if ( str_starts_with( $option_name, $prefix ) ) { $log = true; break; }
    }
    if ( ! $log ) return;
    cdn_log_event( 'settings', "Opção atualizada: {$option_name}" );
}, 10, 1 );

// ============================================================
// 6. VERIFICAÇÃO DE INTEGRIDADE DE ARQUIVOS — WP-Cron (1h)
// ============================================================
add_filter( 'cron_schedules', function( $schedules ) {
    if ( ! isset( $schedules['cdn_hourly'] ) ) {
        $schedules['cdn_hourly'] = [
            'interval' => HOUR_IN_SECONDS,
            'display'  => 'A cada hora',
        ];
    }
    return $schedules;
} );

function cdn_log_schedule_file_check() {
    if ( ! wp_next_scheduled( 'cdn_log_file_integrity_check' ) ) {
        wp_schedule_event( time(), 'cdn_hourly', 'cdn_log_file_integrity_check' );
    }
}
add_action( 'init', 'cdn_log_schedule_file_check' );

add_action( 'cdn_log_file_integrity_check', 'cdn_run_file_integrity_check' );

function cdn_get_monitored_files(): array {
    $root = ABSPATH;
    return [
        // Arquivos críticos na raiz
        $root . '.htaccess',
        $root . 'wp-config.php',
        $root . 'index.php',
        // Tema ativo
        get_template_directory() . '/functions.php',
        get_template_directory() . '/style.css',
        get_template_directory() . '/header.php',
        get_template_directory() . '/footer.php',
        get_template_directory() . '/index.php',
        get_template_directory() . '/single.php',
        get_template_directory() . '/inc/newsletter.php',
        get_template_directory() . '/inc/smtp.php',
        get_template_directory() . '/inc/theme-options.php',
        get_template_directory() . '/inc/activity-log.php',
    ];
}

function cdn_run_file_integrity_check() {
    $files    = cdn_get_monitored_files();
    $baseline = get_option( 'cdn_log_file_baseline', [] );
    $current  = [];
    $alerts   = [];

    foreach ( $files as $path ) {
        if ( ! file_exists( $path ) ) {
            $current[ $path ] = 'MISSING';
            if ( isset( $baseline[ $path ] ) && $baseline[ $path ] !== 'MISSING' ) {
                $alerts[] = "Arquivo removido: " . str_replace( ABSPATH, '', $path );
            }
            continue;
        }
        $hash = sha1_file( $path );
        $current[ $path ] = $hash;

        if ( ! isset( $baseline[ $path ] ) ) {
            // Novo arquivo detectado (primeiro scan)
            $alerts[] = "Novo arquivo na baseline: " . str_replace( ABSPATH, '', $path );
        } elseif ( $baseline[ $path ] !== $hash ) {
            $alerts[] = "Arquivo modificado: " . str_replace( ABSPATH, '', $path );
        }
    }

    // Verificar arquivos que sumiram do disco mas estavam no baseline
    foreach ( $baseline as $path => $hash ) {
        if ( ! isset( $current[ $path ] ) && $hash !== 'MISSING' ) {
            $alerts[] = "Arquivo removido do monitoramento: " . str_replace( ABSPATH, '', $path );
        }
    }

    // Atualizar baseline
    update_option( 'cdn_log_file_baseline', $current );

    // Se há alertas E a baseline já existia (não é o primeiro scan), registrar
    if ( $alerts && $baseline ) {
        foreach ( $alerts as $alert ) {
            cdn_log_event( 'file_integrity', $alert );
        }
    }
}

// Botão manual para recriar baseline
add_action( 'admin_post_cdn_reset_baseline', function() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Acesso negado.' );
    check_admin_referer( 'cdn_reset_baseline' );
    delete_option( 'cdn_log_file_baseline' );
    cdn_run_file_integrity_check(); // cria nova baseline imediatamente
    wp_redirect( admin_url( 'themes.php?page=painel-cdn&tab=activity_log&baseline_reset=1' ) );
    exit;
} );

// Botão para limpar logs
add_action( 'admin_post_cdn_clear_logs', function() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Acesso negado.' );
    check_admin_referer( 'cdn_clear_logs' );
    global $wpdb;
    $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}cdn_activity_log" );
    wp_redirect( admin_url( 'themes.php?page=painel-cdn&tab=activity_log&cleared=1' ) );
    exit;
} );

// Exportar CSV
add_action( 'admin_post_cdn_export_log_csv', function() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Acesso negado.' );
    check_admin_referer( 'cdn_export_csv' );
    global $wpdb;
    $table  = $wpdb->prefix . 'cdn_activity_log';
    $events = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A );

    header( 'Content-Type: text/csv; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename="activity-log-' . date('Y-m-d') . '.csv"' );
    header( 'Pragma: no-cache' );
    echo "\xEF\xBB\xBF"; // BOM UTF-8 para Excel
    $out = fopen( 'php://output', 'w' );
    fputcsv( $out, [ 'ID', 'Evento', 'Descrição', 'Usuário', 'IP', 'Data/Hora' ] );
    foreach ( $events as $row ) {
        fputcsv( $out, [ $row['id'], $row['event_type'], $row['description'], $row['user_login'], $row['ip_address'], $row['created_at'] ] );
    }
    fclose( $out );
    exit;
} );

// ============================================================
// 7. ABA "ACTIVITY LOG" NO PAINEL CDN
// ============================================================
function cdn_render_activity_log_tab() {
    global $wpdb;
    $table  = $wpdb->prefix . 'cdn_activity_log';
    $events = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 200" );
    $total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

    $baseline_count = count( get_option( 'cdn_log_file_baseline', [] ) );

    // Ícones por tipo
    $icons = [
        'post'           => '📝',
        'page'           => '📄',
        'media'          => '🖼️',
        'user'           => '👤',
        'plugin'         => '🔌',
        'theme'          => '🎨',
        'settings'       => '⚙️',
        'file_integrity' => '🔒',
        'painel_cdn'     => '🛠️',
    ];
    $colors = [
        'post'           => '#22609e',
        'page'           => '#22609e',
        'media'          => '#ec7153',
        'user'           => '#16a34a',
        'plugin'         => '#f59e0b',
        'theme'          => '#8b5cf6',
        'settings'       => '#64748b',
        'file_integrity' => '#e72127',
        'painel_cdn'     => '#22609e',
    ];

    if ( isset($_GET['cleared']) ) echo '<div class="notice notice-success is-dismissible"><p>Log limpo com sucesso.</p></div>';
    if ( isset($_GET['baseline_reset']) ) echo '<div class="notice notice-success is-dismissible"><p>Baseline de arquivos recriada. O próximo scan comparará a partir de agora.</p></div>';
    ?>

    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:24px;">
        <!-- Stat: Total -->
        <div style="flex:1;min-width:160px;background:#fff;border-radius:10px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);border-top:4px solid #22609e;">
            <p style="margin:0;font-size:13px;color:#888;">Total de eventos</p>
            <h2 style="margin:4px 0 0;font-size:28px;color:#22609e;"><?php echo number_format_i18n( $total ); ?></h2>
        </div>
        <!-- Stat: Arquivos monitorados -->
        <div style="flex:1;min-width:160px;background:#fff;border-radius:10px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);border-top:4px solid #16a34a;">
            <p style="margin:0;font-size:13px;color:#888;">Arquivos monitorados</p>
            <h2 style="margin:4px 0 0;font-size:28px;color:#16a34a;"><?php echo $baseline_count ?: count( cdn_get_monitored_files() ); ?></h2>
        </div>
        <!-- Stat: Último scan -->
        <div style="flex:1;min-width:160px;background:#fff;border-radius:10px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);border-top:4px solid #f59e0b;">
            <p style="margin:0;font-size:13px;color:#888;">Próximo scan de arquivos</p>
            <h2 style="margin:4px 0 0;font-size:14px;color:#f59e0b;">
                <?php
                $next = wp_next_scheduled( 'cdn_log_file_integrity_check' );
                echo $next ? date_i18n( 'd/m/Y H:i', $next ) : 'Não agendado';
                ?>
            </h2>
        </div>
    </div>

    <!-- Configurações -->
    <div style="background:#fff;border-radius:10px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:24px;">
        <h3 style="margin:0 0 16px;font-size:15px;">⚙️ Configurações de Notificação</h3>
        <form method="post" action="options.php">
            <?php settings_fields( 'cdn_activity_log' ); ?>
            <table class="form-table" style="margin:0;">
                <tr>
                    <th style="width:200px;"><label for="cdn_log_email">E-mail para notificações</label></th>
                    <td>
                        <input type="email" id="cdn_log_email" name="cdn_log_email"
                               value="<?php echo esc_attr( get_option('cdn_log_email', get_option('admin_email')) ); ?>"
                               class="regular-text">
                        <p class="description">Deixe em branco para usar o e-mail de admin do WordPress.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="cdn_log_notify">Modo de notificação</label></th>
                    <td>
                        <select id="cdn_log_notify" name="cdn_log_notify">
                            <option value="immediate" <?php selected( get_option('cdn_log_notify','immediate'), 'immediate' ); ?>>Imediato (1 e-mail por evento)</option>
                            <option value="digest"    <?php selected( get_option('cdn_log_notify','immediate'), 'digest' ); ?>>Digest diário (resumo às 07h)</option>
                            <option value="none"      <?php selected( get_option('cdn_log_notify','immediate'), 'none' ); ?>>Sem notificações (apenas log)</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Salvar Configurações', 'primary', 'submit', false ); ?>
        </form>
    </div>

    <!-- Ações -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;align-items:center;">
        <strong style="color:#444;">Ações:</strong>

        <!-- Limpar logs -->
        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;"
              onsubmit="return confirm('Tem certeza? Todos os logs serão excluídos permanentemente.');">
            <?php wp_nonce_field('cdn_clear_logs'); ?>
            <input type="hidden" name="action" value="cdn_clear_logs">
            <button type="submit" class="button button-secondary" style="color:#c62828;">🗑️ Limpar Todos os Logs</button>
        </form>

        <!-- Exportar CSV -->
        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;">
            <?php wp_nonce_field('cdn_export_csv'); ?>
            <input type="hidden" name="action" value="cdn_export_log_csv">
            <button type="submit" class="button button-secondary">📥 Exportar CSV</button>
        </form>

        <!-- Recriar baseline -->
        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;"
              onsubmit="return confirm('Isso vai recriar a baseline de arquivos. Use após fazer mudanças intencionais nos arquivos.');">
            <?php wp_nonce_field('cdn_reset_baseline'); ?>
            <input type="hidden" name="action" value="cdn_reset_baseline">
            <button type="submit" class="button button-secondary">🔄 Recriar Baseline de Arquivos</button>
        </form>

        <span style="color:#888;font-size:13px;margin-left:auto;">Exibindo os últimos 200 de <?php echo number_format_i18n($total); ?> eventos</span>
    </div>

    <!-- Tabela de logs -->
    <?php if ( ! $events ) : ?>
        <div style="background:#fff;border-radius:10px;padding:40px;text-align:center;color:#888;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            ✅ Nenhum evento registrado ainda. Os eventos aparecerão aqui conforme o site for utilizado.
        </div>
    <?php else : ?>
    <table class="widefat striped" style="border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.07);">
        <thead>
            <tr>
                <th style="width:140px;">Data / Hora</th>
                <th style="width:140px;">Evento</th>
                <th>Descrição</th>
                <th style="width:120px;">Usuário</th>
                <th style="width:120px;">IP</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $events as $e ) :
            $icon  = $icons[ $e->event_type ]  ?? '🔔';
            $color = $colors[ $e->event_type ] ?? '#64748b';
            $date  = date_i18n( 'd/m/Y H:i:s', strtotime( $e->created_at ) );
        ?>
            <tr>
                <td style="font-size:12px;color:#888;"><?php echo esc_html( $date ); ?></td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:5px;background:<?php echo esc_attr($color); ?>18;color:<?php echo esc_attr($color); ?>;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:700;">
                        <?php echo $icon; ?> <?php echo esc_html( $e->event_type ); ?>
                    </span>
                </td>
                <td style="font-size:13px;"><?php echo esc_html( $e->description ); ?></td>
                <td style="font-size:12px;color:#555;"><?php echo esc_html( $e->user_login ); ?></td>
                <td style="font-size:12px;color:#94a3b8;font-family:monospace;"><?php echo esc_html( $e->ip_address ); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <?php
}
