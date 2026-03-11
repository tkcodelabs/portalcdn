<?php
/**
 * Theme Options Panel - Futebol
 * Submenu to manage the Football Table, Next Matches, and Results
 */

// Handle Form Submission explicitly to save multidimensional arrays
add_action( 'admin_init', 'cdn_futebol_save_settings' );
function cdn_futebol_save_settings() {
    if ( isset( $_POST['cdn_futebol_submit'] ) && current_user_can( 'manage_options' ) ) {
        check_admin_referer( 'cdn_futebol_nonce_action', 'cdn_futebol_nonce' );

        $classificacao = isset( $_POST['cdn_classificacao'] ) ? $_POST['cdn_classificacao'] : [];
        $proximos = isset( $_POST['cdn_proximos'] ) ? $_POST['cdn_proximos'] : [];
        $resultados = isset( $_POST['cdn_resultados'] ) ? $_POST['cdn_resultados'] : [];

        // Simple sanitization: loop through array and sanitize inputs
        $clean_classificacao = cdn_sanitize_football_array( $classificacao );
        $clean_proximos = cdn_sanitize_football_array( $proximos );
        $clean_resultados = cdn_sanitize_football_array( $resultados );

        update_option( 'cdn_futebol_classificacao', $clean_classificacao );
        update_option( 'cdn_futebol_proximos', $clean_proximos );
        update_option( 'cdn_futebol_resultados', $clean_resultados );

        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Dados da <strong>Tabela de Futebol</strong> salvos com sucesso!</p></div>';
        });
    }
}

// Helper: Sanitize multi-dimensional array recursively
function cdn_sanitize_football_array( $data ) {
    if ( ! is_array( $data ) ) {
        return sanitize_text_field( wp_unslash( $data ) );
    }
    
    $clean = [];
    $is_numeric = ( array_keys( $data ) === range( 0, count( $data ) - 1 ) );

    foreach ( $data as $k => $v ) {
        if ( is_array( $v ) ) {
            $cleaned_v = cdn_sanitize_football_array( $v );
            
            // Se for uma "linha" (array interno), verifica se está totalmente vazia
            $has_value = false;
            foreach ( $cleaned_v as $val ) {
                if ( $val !== '' ) {
                    $has_value = true;
                    break;
                }
            }
            
            if ( $has_value ) {
                $clean[$k] = $cleaned_v;
            }
        } else {
            $clean[$k] = sanitize_text_field( wp_unslash( $v ) );
        }
    }
    
    // Só reindexar se for uma lista numérica (ex: a lista de resultados), 
    // mas preservar chaves se for um objeto associativo (ex: a linha com 'time', 'pontos', etc)
    return $is_numeric ? array_values( $clean ) : $clean;
}


// Exibir o painel (Aba dentro do Painel CDN)
function cdn_render_futebol_tab() {
    // Get existing options to prefill the form
    $val_classi = get_option( 'cdn_futebol_classificacao', [] );
    $val_prox   = get_option( 'cdn_futebol_proximos', [] );
    $val_result = get_option( 'cdn_futebol_resultados', [] );

    // Ensure we have at least 8 empty slots for the UI loop
    $slots = 8;
    ?>
    <style>
        .cdn-admin-header { background: #fff; padding: 15px 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,.05); display: flex; align-items: center; gap: 15px; margin-bottom: 20px;}
        .cdn-admin-header h1 { margin: 0; padding: 0; font-size: 20px;}
        .futebol-section-box { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px;}
        .futebol-section-box h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #f50000; font-size: 1.2rem;}
        
        .cdntable { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left; }
        .cdntable th { background: #f9f9f9; padding: 10px; border: 1px solid #ddd; font-weight: 600; font-size: 13px; }
        .cdntable td { padding: 6px; border: 1px solid #ddd; }
        .cdntable input[type="text"], .cdntable input[type="number"] { width: 100%; box-sizing: border-box; }
        .cdntable .num-col { width: 60px; }
    </style>
    <div class="wrap">
        <div class="cdn-admin-header">
            <span class="dashicons dashicons-money-alt" style="font-size: 28px; width: 28px; height: 28px; color: #f50000;"></span> <!-- Ícone provisório -->
            <h1>Painel CDN — Gestão de Futebol ⚽</h1>
        </div>

        <p>Preencha apenas as linhas que deseja exibir no site. Se uma linha inteira for deixada em branco, ela será ignorada. Para o "Parnahyba SC" ficar em destaque nos cards, digite o nome exato do time como: <strong>Parnahyba SC</strong>.</p>

        <form method="post" action="?page=painel-cdn&tab=futebol">
            <?php wp_nonce_field( 'cdn_futebol_nonce_action', 'cdn_futebol_nonce' ); ?>

            <!-- PRÓXIMOS JOGOS (Usado no Widget da Home e na página de Tabela) -->
            <div class="futebol-section-box">
                <h3>📅 Próximos Jogos</h3>
                <p class="description">Estes jogos preenchem o widget da barra lateral e a aba "Próximos Jogos" na tela de futebol.</p>
                <table class="cdntable">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Data (Ex: 05/04)</th>
                            <th>Hora (Ex: 16h)</th>
                            <th>Mandante (Casa)</th>
                            <th>Visitante (Fora)</th>
                            <th>Estádio</th>
                            <th>Competição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ( $i = 0; $i < $slots; $i++ ) : 
                            $r = isset( $val_prox[$i] ) ? $val_prox[$i] : [];
                        ?>
                        <tr>
                            <td style="text-align:center; color:#999;"><?php echo $i+1; ?></td>
                            <td><input type="text" name="cdn_proximos[<?php echo $i; ?>][data]" value="<?php echo esc_attr($r['data'] ?? ''); ?>"></td>
                            <td><input type="text" name="cdn_proximos[<?php echo $i; ?>][hora]" value="<?php echo esc_attr($r['hora'] ?? ''); ?>"></td>
                            <td><input type="text" name="cdn_proximos[<?php echo $i; ?>][mandante]" value="<?php echo esc_attr($r['mandante'] ?? ''); ?>"></td>
                            <td><input type="text" name="cdn_proximos[<?php echo $i; ?>][visitante]" value="<?php echo esc_attr($r['visitante'] ?? ''); ?>"></td>
                            <td><input type="text" name="cdn_proximos[<?php echo $i; ?>][estadio]" value="<?php echo esc_attr($r['estadio'] ?? ''); ?>"></td>
                            <td><input type="text" name="cdn_proximos[<?php echo $i; ?>][comp]" value="<?php echo esc_attr($r['comp'] ?? ''); ?>"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <!-- RESULTADOS -->
            <div class="futebol-section-box">
                <h3>📊 Resultados Anteriores</h3>
                <p class="description">Aparição exclusiva na tela de Tabela de Futebol (Aba: Resultados).</p>
                <table class="cdntable">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Data</th>
                            <th>Mandante (Casa)</th>
                            <th class="num-col">G. Casa</th>
                            <th class="num-col">G. Fora</th>
                            <th>Visitante (Fora)</th>
                            <th>Competição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ( $i = 0; $i < $slots; $i++ ) : 
                            $r = isset( $val_result[$i] ) ? $val_result[$i] : [];
                        ?>
                        <tr>
                            <td style="text-align:center; color:#999;"><?php echo $i+1; ?></td>
                            <td><input type="text" name="cdn_resultados[<?php echo $i; ?>][data]" value="<?php echo esc_attr($r['data'] ?? ''); ?>"></td>
                            <td><input type="text" name="cdn_resultados[<?php echo $i; ?>][mandante]" value="<?php echo esc_attr($r['mandante'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_resultados[<?php echo $i; ?>][gm]" value="<?php echo esc_attr($r['gm'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_resultados[<?php echo $i; ?>][gv]" value="<?php echo esc_attr($r['gv'] ?? ''); ?>"></td>
                            <td><input type="text" name="cdn_resultados[<?php echo $i; ?>][visitante]" value="<?php echo esc_attr($r['visitante'] ?? ''); ?>"></td>
                            <td><input type="text" name="cdn_resultados[<?php echo $i; ?>][comp]" value="<?php echo esc_attr($r['comp'] ?? ''); ?>"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <!-- CLASSIFICAÇÃO -->
            <div class="futebol-section-box">
                <h3>🏆 Classificação (Tabela)</h3>
                <p class="description">Aba principal da tela de Tabela. Preencha na ordem correta, as posições de 1 a 8 serão aplicadas visualmente com as tags de Libertadores/Rebaixamento.</p>
                <table class="cdntable">
                    <thead>
                        <tr>
                            <th style="width:50px;">#Pos</th>
                            <th>Nome do Time</th>
                            <th class="num-col" title="Pontos">P</th>
                            <th class="num-col" title="Jogos">J</th>
                            <th class="num-col" title="Vitórias">V</th>
                            <th class="num-col" title="Empates">E</th>
                            <th class="num-col" title="Derrotas">D</th>
                            <th class="num-col" title="Gols Pró">GP</th>
                            <th class="num-col" title="Gols Contra">GC</th>
                            <th class="num-col" title="Saldo">SG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ( $i = 0; $i < $slots; $i++ ) : 
                            $r = isset( $val_classi[$i] ) ? $val_classi[$i] : [];
                        ?>
                        <tr>
                            <td style="text-align:center;"><input type="text" name="cdn_classificacao[<?php echo $i; ?>][pos]" value="<?php echo esc_attr($r['pos'] ?? ($i+1)); ?>" style="text-align:center;"></td>
                            <td><input type="text" name="cdn_classificacao[<?php echo $i; ?>][time]" value="<?php echo esc_attr($r['time'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_classificacao[<?php echo $i; ?>][p]" value="<?php echo esc_attr($r['p'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_classificacao[<?php echo $i; ?>][j]" value="<?php echo esc_attr($r['j'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_classificacao[<?php echo $i; ?>][v]" value="<?php echo esc_attr($r['v'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_classificacao[<?php echo $i; ?>][e]" value="<?php echo esc_attr($r['e'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_classificacao[<?php echo $i; ?>][d]" value="<?php echo esc_attr($r['d'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_classificacao[<?php echo $i; ?>][gp]" value="<?php echo esc_attr($r['gp'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_classificacao[<?php echo $i; ?>][gc]" value="<?php echo esc_attr($r['gc'] ?? ''); ?>"></td>
                            <td><input type="number" name="cdn_classificacao[<?php echo $i; ?>][sg]" value="<?php echo esc_attr($r['sg'] ?? ''); ?>"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <p class="submit">
                <input type="submit" name="cdn_futebol_submit" class="button button-primary" style="background:#f50000; border-color:#d00000; box-shadow:none; padding:5px 30px;" value="Salvar Tabelas de Futebol">
            </p>
        </form>
    </div>
    <?php
}
