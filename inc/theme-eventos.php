<?php
/**
 * Theme Options Panel - Eventos Culturais
 * Submenu to manage cultural events
 */

// Handle Form Submission explicitly to save multidimensional arrays
add_action( 'admin_init', 'cdn_eventos_save_settings' );
function cdn_eventos_save_settings() {
    if ( isset( $_POST['cdn_eventos_submit'] ) && current_user_can( 'manage_options' ) ) {
        check_admin_referer( 'cdn_eventos_nonce_action', 'cdn_eventos_nonce' );

        $eventos = isset( $_POST['cdn_eventos'] ) ? $_POST['cdn_eventos'] : [];

        // Simple sanitization: loop through array and sanitize inputs
        $clean_eventos = cdn_sanitize_eventos_array( $eventos );

        update_option( 'cdn_eventos_lista', $clean_eventos );

        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Agenda de <strong>Eventos Culturais</strong> salva com sucesso!</p></div>';
        });
    }
}

// Helper: Sanitize multi-dimensional array recursively
function cdn_sanitize_eventos_array( $data ) {
    if ( ! is_array( $data ) ) {
        return sanitize_text_field( wp_unslash( $data ) );
    }
    
    $clean = [];
    $is_numeric = ( array_keys( $data ) === range( 0, count( $data ) - 1 ) );

    foreach ( $data as $k => $v ) {
        if ( is_array( $v ) ) {
            $cleaned_v = cdn_sanitize_eventos_array( $v );
            
            // Se for uma "linha" (array interno), verifica se tem pelo menos um valor essencial (título)
            if ( ! empty( $cleaned_v['titulo'] ) ) {
                $clean[$k] = $cleaned_v;
            }
        } else {
            // Handle boolean checkbox for 'destaque'
            if ( $k === 'destaque' ) {
                $clean[$k] = (bool) $v;
            } else {
                $clean[$k] = sanitize_text_field( wp_unslash( $v ) );
            }
        }
    }
    
    return $is_numeric ? array_values( $clean ) : $clean;
}


// Exibir o painel (Aba dentro do Painel CDN)
function cdn_render_eventos_tab() {
    // Get existing options to prefill the form
    $eventos = get_option( 'cdn_eventos_lista', [] );

    // Ensure we have at least 10 slots for the UI loop
    $slots = max( 10, count( $eventos ) + 2 );
    ?>
    <style>
        .cdn-admin-header { background: #fff; padding: 15px 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,.05); display: flex; align-items: center; gap: 15px; margin-bottom: 20px;}
        .cdn-admin-header h1 { margin: 0; padding: 0; font-size: 20px;}
        .eventos-section-box { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;}
        .eventos-section-box h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #f50000; font-size: 1.2rem;}
        
        .cdntable { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        .cdntable th { background: #f9f9f9; padding: 10px; border: 1px solid #ddd; font-weight: 600; font-size: 13px; }
        .cdntable td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }
        .cdntable input[type="text"], .cdntable textarea { width: 100%; box-sizing: border-box; }
        .cdntable textarea { height: 60px; resize: vertical; }
        .cdntable .check-col { width: 40px; text-align: center; }
        .cdntable .num-col { width: 40px; text-align: center; color: #999; }
    </style>
    <div class="wrap">
        <div class="cdn-admin-header">
            <span class="dashicons dashicons-calendar-alt" style="font-size: 28px; width: 28px; height: 28px; color: #f50000;"></span>
            <h1>Painel CDN — Agenda Cultural 🎭</h1>
        </div>

        <p>Gerencie os eventos que aparecem na sidebar e na página de Eventos Culturais. Preencha pelo menos o <strong>Título</strong> para salvar o evento.</p>

        <form method="post" action="?page=painel-cdn&tab=eventos">
            <?php wp_nonce_field( 'cdn_eventos_nonce_action', 'cdn_eventos_nonce' ); ?>

            <div class="eventos-section-box">
                <h3>📅 Lista de Eventos</h3>
                <table class="cdntable">
                    <thead>
                        <tr>
                            <th class="num-col">#</th>
                            <th class="check-col" title="Destaque">⭐</th>
                            <th>Data / Hora / Dia</th>
                            <th>Evento / Local</th>
                            <th>Descrição Curta</th>
                            <th>Link / Categoria</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ( $i = 0; $i < $slots; $i++ ) : 
                            $e = isset( $eventos[$i] ) ? $eventos[$i] : [];
                        ?>
                        <tr>
                            <td class="num-col"><?php echo $i+1; ?></td>
                            <td class="check-col">
                                <input type="checkbox" name="cdn_eventos[<?php echo $i; ?>][destaque]" value="1" <?php checked( !empty($e['destaque']) ); ?>>
                            </td>
                            <td>
                                <input type="text" name="cdn_eventos[<?php echo $i; ?>][data]" value="<?php echo esc_attr($e['data'] ?? ''); ?>" placeholder="Ex: 08/03/2026" style="margin-bottom:4px;">
                                <input type="text" name="cdn_eventos[<?php echo $i; ?>][hora]" value="<?php echo esc_attr($e['hora'] ?? ''); ?>" placeholder="Ex: 19:00" style="margin-bottom:4px;">
                                <input type="text" name="cdn_eventos[<?php echo $i; ?>][dia_semana]" value="<?php echo esc_attr($e['dia_semana'] ?? ''); ?>" placeholder="Ex: Domingo">
                            </td>
                            <td>
                                <input type="text" name="cdn_eventos[<?php echo $i; ?>][titulo]" value="<?php echo esc_attr($e['titulo'] ?? ''); ?>" placeholder="Título do Evento" style="font-weight:bold; margin-bottom:4px;">
                                <input type="text" name="cdn_eventos[<?php echo $i; ?>][local]" value="<?php echo esc_attr($e['local'] ?? ''); ?>" placeholder="Local do Evento">
                            </td>
                            <td>
                                <textarea name="cdn_eventos[<?php echo $i; ?>][desc]" placeholder="Breve descrição do evento..."><?php echo esc_textarea($e['desc'] ?? ''); ?></textarea>
                            </td>
                            <td>
                                <input type="text" name="cdn_eventos[<?php echo $i; ?>][link]" value="<?php echo esc_attr($e['link'] ?? ''); ?>" placeholder="Link (URL)" style="margin-bottom:4px;">
                                <input type="text" name="cdn_eventos[<?php echo $i; ?>][categ]" value="<?php echo esc_attr($e['categ'] ?? ''); ?>" placeholder="Categoria (Ex: Música)">
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <p class="submit">
                <input type="submit" name="cdn_eventos_submit" class="button button-primary" style="background:#f50000; border-color:#d00000; box-shadow:none; padding:5px 30px;" value="Salvar Agenda Cultural">
            </p>
        </form>
    </div>
    <?php
}
