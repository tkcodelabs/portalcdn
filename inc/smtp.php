<?php
/**
 * smtp.php — Configuração SMTP nativa para o tema Correio do Norte
 * Não requer plugin externo. Configura PHPMailer via hook 'phpmailer_init'.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ===========================================================
// 1. REGISTRAR SETTINGS DO SMTP
// ===========================================================
add_action( 'admin_init', 'cdn_smtp_settings_init' );
function cdn_smtp_settings_init() {
    $fields = [
        'cdn_smtp_host',
        'cdn_smtp_port',
        'cdn_smtp_encryption',
        'cdn_smtp_user',
        'cdn_smtp_pass',
        'cdn_smtp_from_email',
        'cdn_smtp_from_name',
        'cdn_smtp_enabled',
    ];
    foreach ( $fields as $f ) {
        register_setting( 'cdn_smtp', $f );
    }
}

/**
 * Forçar o e-mail de remetente globalmente para evitar o erro "wordpress@localhost"
 */
add_filter( 'wp_mail_from', function( $email ) {
    if ( get_option( 'cdn_smtp_enabled' ) === '1' ) {
        $from = get_option( 'cdn_smtp_from_email' );
        if ( ! empty( $from ) && is_email( $from ) ) return $from;
    }
    return $email;
}, 20 );

add_filter( 'wp_mail_from_name', function( $name ) {
    if ( get_option( 'cdn_smtp_enabled' ) === '1' ) {
        $from_name = get_option( 'cdn_smtp_from_name' );
        if ( ! empty( $from_name ) ) return $from_name;
    }
    return $name;
}, 20 );

// ===========================================================
// 2. CONFIGURAR PHPMAILER VIA HOOK
// ===========================================================
add_action( 'phpmailer_init', 'cdn_configure_phpmailer' );
function cdn_configure_phpmailer( $phpmailer ) {
    if ( get_option( 'cdn_smtp_enabled' ) !== '1' ) return;

    $host       = get_option( 'cdn_smtp_host', '' );
    $port       = (int) get_option( 'cdn_smtp_port', 587 );
    $user       = get_option( 'cdn_smtp_user', '' );
    $pass       = get_option( 'cdn_smtp_pass', '' );
    $enc        = get_option( 'cdn_smtp_encryption', 'tls' );
    $from_email = get_option( 'cdn_smtp_from_email', get_option( 'admin_email' ) );
    $from_name  = get_option( 'cdn_smtp_from_name', get_bloginfo( 'name' ) );

    if ( ! $host || ! $user || ! $pass ) return;

    $phpmailer->isSMTP();
    $phpmailer->Host       = $host;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = $port;
    $phpmailer->Username   = $user;
    $phpmailer->Password   = $pass;
    $phpmailer->SMTPSecure = $enc === 'none' ? '' : $enc;
    
    // Configurar Remetente (From)
    $phpmailer->setFrom( $from_email, $from_name );
    $phpmailer->CharSet = 'UTF-8';
}

// ===========================================================
// 3. AJAX: ENVIAR E-MAIL DE TESTE
// ===========================================================
add_action( 'wp_ajax_cdn_smtp_test', 'cdn_ajax_smtp_test' );
function cdn_ajax_smtp_test() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( [ 'message' => 'Sem permissão.' ] );
    check_ajax_referer( 'cdn_smtp_test_nonce', 'nonce' );

    $to      = sanitize_email( $_POST['to'] ?? get_option( 'admin_email' ) );
    $subject = '[Teste] Newsletter - ' . get_bloginfo( 'name' );
    $body    = '
<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;padding:30px;background:#f5f5f5;">
<div style="max-width:500px;margin:0 auto;background:#fff;padding:36px;border-radius:10px;text-align:center;">
  <h2 style="color:#1a1a2e;">✅ SMTP Funcionando!</h2>
  <p style="color:#555;">Parabéns! O sistema de e-mail do <strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong> está configurado corretamente.</p>
  <p style="color:#888;font-size:13px;">Este é um e-mail de teste enviado do seu Painel CDN.</p>
</div>
</body></html>';

    $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
    $sent = wp_mail( $to, $subject, $body, $headers );

    if ( $sent ) {
        wp_send_json_success( [ 'message' => "✅ E-mail de teste enviado com sucesso para <strong>{$to}</strong>!" ] );
    } else {
        global $phpmailer;
        $error = isset( $phpmailer->ErrorInfo ) ? $phpmailer->ErrorInfo : 'Verifique as configurações SMTP.';
        wp_send_json_error( [ 'message' => '❌ Falha ao enviar: ' . esc_html( $error ) ] );
    }
}

// ===========================================================
// 4. RENDERIZAR ABA SMTP NO PAINEL CDN
// ===========================================================
function cdn_render_smtp_tab() {
    $enabled    = get_option( 'cdn_smtp_enabled', '0' );
    $host       = get_option( 'cdn_smtp_host', '' );
    $port       = get_option( 'cdn_smtp_port', '587' );
    $enc        = get_option( 'cdn_smtp_encryption', 'tls' );
    $user       = get_option( 'cdn_smtp_user', '' );
    $from_email = get_option( 'cdn_smtp_from_email', get_option( 'admin_email' ) );
    $from_name  = get_option( 'cdn_smtp_from_name', get_bloginfo( 'name' ) );

    // Presets de provedores
    $presets = [
        'gmail'     => [ 'smtp.gmail.com',          '587', 'tls',  'Gmail' ],
        'outlook'   => [ 'smtp.office365.com',       '587', 'tls',  'Outlook / Office 365' ],
        'hotmail'   => [ 'smtp.live.com',            '587', 'tls',  'Hotmail / Live' ],
        'yahoo'     => [ 'smtp.mail.yahoo.com',      '465', 'ssl',  'Yahoo Mail' ],
        'sendgrid'  => [ 'smtp.sendgrid.net',        '587', 'tls',  'SendGrid (Recomendado para alto volume)' ],
        'brevo'     => [ 'smtp-relay.brevo.com',     '587', 'tls',  'Brevo / Sendinblue (Plano gratuito: 300/dia)' ],
        'mailgun'   => [ 'smtp.mailgun.org',         '587', 'tls',  'Mailgun' ],
        'hostgator' => [ 'mail.hostgator.com',       '587', 'tls',  'HostGator' ],
        'locaweb'   => [ 'email-ssl.com.br',         '587', 'tls',  'Locaweb' ],
        'kinghost'  => [ 'smtp.kinghost.net',        '587', 'tls',  'KingHost' ],
    ];
    ?>
    <div style="max-width:780px;">

      <!-- Alerta de status -->
      <div style="background:<?php echo $enabled === '1' ? '#e8f5e9' : '#fff8e1'; ?>;border:1px solid <?php echo $enabled === '1' ? '#a5d6a7' : '#ffe082'; ?>;border-radius:8px;padding:14px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;">
        <span style="font-size:22px;"><?php echo $enabled === '1' ? '✅' : '⚠️'; ?></span>
        <div>
          <strong><?php echo $enabled === '1' ? 'SMTP Ativado' : 'SMTP Desativado'; ?></strong>
          <p style="margin:2px 0 0;font-size:13px;color:#555;"><?php echo $enabled === '1' ? 'O WordPress está usando seu servidor SMTP para enviar e-mails.' : 'O WordPress está usando o PHP mail() padrão, que pode não funcionar no localhost.'; ?></p>
        </div>
      </div>

      <!-- Presets de Provedor -->
      <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:22px;margin-bottom:24px;">
        <h3 style="margin:0 0 12px;">⚡ Configuração Rápida por Provedor</h3>
        <p style="color:#666;font-size:13px;margin:0 0 14px;">Clique no seu provedor de e-mail para preencher automaticamente os campos de servidor e porta:</p>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
          <?php foreach ( $presets as $key => [$h, $p, $e, $label] ) : ?>
            <button type="button" class="button cdn-smtp-preset" 
              data-host="<?php echo esc_attr($h); ?>" 
              data-port="<?php echo esc_attr($p); ?>" 
              data-enc="<?php echo esc_attr($e); ?>"
              style="font-size:12px;"><?php echo esc_html($label); ?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Formulário SMTP -->
      <form method="post" action="options.php" id="cdn-smtp-form">
        <?php settings_fields( 'cdn_smtp' ); ?>
        <div style="background:#fff;border:1px solid #ddd;border-radius:8px;overflow:hidden;margin-bottom:24px;">
          <div style="padding:18px 22px;border-bottom:1px solid #eee;">
            <h3 style="margin:0;">⚙️ Configurações SMTP</h3>
          </div>
          <table class="form-table" style="margin:0;">
            <tr>
              <th><label for="cdn_smtp_enabled">Ativar SMTP</label></th>
              <td>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                  <input type="checkbox" id="cdn_smtp_enabled" name="cdn_smtp_enabled" value="1" <?php checked( $enabled, '1' ); ?> style="width:18px;height:18px;">
                  <span>Usar servidor SMTP personalizado ao invés do PHP mail()</span>
                </label>
              </td>
            </tr>
            <tr>
              <th><label for="cdn_smtp_host">Servidor SMTP</label></th>
              <td>
                <input type="text" id="cdn_smtp_host" name="cdn_smtp_host" value="<?php echo esc_attr($host); ?>" class="regular-text" placeholder="smtp.gmail.com">
              </td>
            </tr>
            <tr>
              <th><label for="cdn_smtp_port">Porta</label></th>
              <td>
                <input type="number" id="cdn_smtp_port" name="cdn_smtp_port" value="<?php echo esc_attr($port); ?>" style="width:100px;" placeholder="587">
                <p class="description">587 (TLS/STARTTLS) ou 465 (SSL) — recomendamos 587.</p>
              </td>
            </tr>
            <tr>
              <th><label for="cdn_smtp_encryption">Criptografia</label></th>
              <td>
                <select id="cdn_smtp_encryption" name="cdn_smtp_encryption">
                  <option value="tls"  <?php selected($enc,'tls'); ?>>TLS / STARTTLS (Recomendado)</option>
                  <option value="ssl"  <?php selected($enc,'ssl'); ?>>SSL</option>
                  <option value="none" <?php selected($enc,'none'); ?>>Nenhuma</option>
                </select>
              </td>
            </tr>
            <tr>
              <th><label for="cdn_smtp_user">Usuário (E-mail)</label></th>
              <td>
                <input type="email" id="cdn_smtp_user" name="cdn_smtp_user" value="<?php echo esc_attr($user); ?>" class="regular-text" placeholder="seuemail@gmail.com">
              </td>
            </tr>
            <tr>
              <th><label for="cdn_smtp_pass">Senha / App Password</label></th>
              <td>
                <div style="display:flex;gap:8px;align-items:center;">
                  <input type="password" id="cdn_smtp_pass" name="cdn_smtp_pass" value="<?php echo esc_attr( get_option('cdn_smtp_pass','') ); ?>" class="regular-text" placeholder="••••••••••••••••" autocomplete="new-password">
                  <button type="button" id="cdn-toggle-pass" class="button" style="flex-shrink:0;">👁 Mostrar</button>
                </div>
                <p class="description">
                  Para Gmail: use uma <strong>Senha de App</strong> (não sua senha normal). 
                  <a href="https://myaccount.google.com/apppasswords" target="_blank">Criar Senha de App no Google →</a>
                </p>
              </td>
            </tr>
            <tr>
              <th><label for="cdn_smtp_from_email">E-mail do Remetente</label></th>
              <td>
                <input type="email" id="cdn_smtp_from_email" name="cdn_smtp_from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>">
              </td>
            </tr>
            <tr>
              <th><label for="cdn_smtp_from_name">Nome do Remetente</label></th>
              <td>
                <input type="text" id="cdn_smtp_from_name" name="cdn_smtp_from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text" placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>">
              </td>
            </tr>
          </table>
        </div>

        <?php submit_button( 'Salvar Configurações SMTP', 'primary', 'submit', false, ['style' => 'background:#f50000;border-color:#d00000;'] ); ?>
      </form>

      <!-- Teste de E-mail -->
      <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:22px;margin-top:24px;">
        <h3 style="margin:0 0 10px;">🧪 Testar Configurações</h3>
        <p style="color:#666;font-size:13px;margin:0 0 14px;">Após salvar, clique abaixo para enviar um e-mail de teste e confirmar que está funcionando:</p>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <input type="email" id="cdn-test-email" value="<?php echo esc_attr(get_option('admin_email')); ?>" style="flex:1;min-width:220px;padding:8px 12px;border:1px solid #ccc;border-radius:4px;" placeholder="seu@email.com">
          <button type="button" id="cdn-smtp-test-btn" class="button button-primary" style="background:#1a1a2e;border-color:#1a1a2e;">🚀 Enviar Teste</button>
        </div>
        <div id="cdn-smtp-test-result" style="margin-top:12px;font-size:13px;display:none;padding:10px 14px;border-radius:6px;"></div>
      </div>

    </div>

    <script>
    (function(){
      // Presets
      document.querySelectorAll('.cdn-smtp-preset').forEach(function(btn){
        btn.addEventListener('click', function(){
          document.getElementById('cdn_smtp_host').value = this.dataset.host;
          document.getElementById('cdn_smtp_port').value = this.dataset.port;
          document.getElementById('cdn_smtp_encryption').value = this.dataset.enc;
        });
      });

      // Toggle senha
      var toggleBtn = document.getElementById('cdn-toggle-pass');
      var passField = document.getElementById('cdn_smtp_pass');
      if(toggleBtn && passField){
        toggleBtn.addEventListener('click', function(){
          passField.type = passField.type === 'password' ? 'text' : 'password';
          this.textContent = passField.type === 'password' ? '👁 Mostrar' : '🙈 Ocultar';
        });
      }

      // Teste SMTP
      var testBtn = document.getElementById('cdn-smtp-test-btn');
      var resultDiv = document.getElementById('cdn-smtp-test-result');
      if(testBtn){
        testBtn.addEventListener('click', function(){
          var to = document.getElementById('cdn-test-email').value;
          if(!to){ alert('Informe um e-mail para teste.'); return; }
          testBtn.textContent = '⏳ Enviando...';
          testBtn.disabled = true;

          var fd = new FormData();
          fd.append('action', 'cdn_smtp_test');
          fd.append('nonce', '<?php echo wp_create_nonce("cdn_smtp_test_nonce"); ?>');
          fd.append('to', to);

          fetch('<?php echo esc_url(admin_url("admin-ajax.php")); ?>', { method:'POST', body:fd })
            .then(function(r){ return r.json(); })
            .then(function(res){
              resultDiv.style.display = 'block';
              if(res.success){
                resultDiv.style.background = '#e8f5e9';
                resultDiv.style.border = '1px solid #a5d6a7';
                resultDiv.style.color = '#2e7d32';
              } else {
                resultDiv.style.background = '#fce4ec';
                resultDiv.style.border = '1px solid #f48fb1';
                resultDiv.style.color = '#c62828';
              }
              resultDiv.innerHTML = res.data.message;
              testBtn.textContent = '🚀 Enviar Teste';
              testBtn.disabled = false;
            })
            .catch(function(){
              resultDiv.style.display = 'block';
              resultDiv.textContent = 'Erro de comunicação com o servidor.';
              testBtn.textContent = '🚀 Enviar Teste';
              testBtn.disabled = false;
            });
        });
      }
    })();
    </script>
    <?php
}
