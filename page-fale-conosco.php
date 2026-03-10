<?php
/**
 * Template Name: Fale Conosco
 * Template Post Type: page
 */
get_header();
?>
<main id="main-content" class="inst-page contato-page">

    <!-- HERO -->
    <section class="inst-hero contato-hero">
        <div class="inst-hero-bg" aria-hidden="true">
            <div class="hero-orb hero-orb-1" style="background:rgba(60,180,90,.3)"></div>
            <div class="hero-orb hero-orb-2" style="background:rgba(26,107,181,.25)"></div>
        </div>
        <div class="container inst-hero-inner">
            <div class="inst-hero-badge">💬 Fale com a gente</div>
            <h1 class="inst-hero-title">Sua voz é nossa<br><span>melhor pauta</span></h1>
            <p class="inst-hero-sub">Sugestões, denúncias, pautas ou elogios — nosso time está pronto para ouvir você.</p>
        </div>
    </section>

    <section class="inst-section">
        <div class="container contato-layout">

            <!-- CANAIS -->
            <div class="contato-canais">
                <h2 class="contato-section-title">Nossos canais</h2>

                <div class="canal-card canal-whatsapp">
                    <div class="canal-icon">💬</div>
                    <div class="canal-info">
                        <strong>WhatsApp do Plantão</strong>
                        <span>Emergências jornalísticas 24h</span>
                        <a href="https://wa.me/558699220001" target="_blank" class="canal-link">(86) 99922-0001</a>
                    </div>
                </div>

                <div class="canal-card canal-redacao">
                    <div class="canal-icon">✏️</div>
                    <div class="canal-info">
                        <strong>Redação Editorial</strong>
                        <span>Para pautas e sugestões de matéria</span>
                        <a href="mailto:redacao@correiodonorte.com.br" class="canal-link">redacao@correiodonorte.com.br</a>
                    </div>
                </div>

                <div class="canal-card canal-comercial">
                    <div class="canal-icon">📢</div>
                    <div class="canal-info">
                        <strong>Comercial / Publicidade</strong>
                        <span>Anúncios, patrocínios e veiculação</span>
                        <a href="mailto:comercial@correiodonorte.com.br" class="canal-link">comercial@correiodonorte.com.br</a>
                    </div>
                </div>

                <div class="canal-card canal-denuncia">
                    <div class="canal-icon">🔒</div>
                    <div class="canal-info">
                        <strong>Canal de Denúncias Anônimas</strong>
                        <span>Anonimato 100% protegido</span>
                        <a href="mailto:denuncia@correiodonorte.com.br" class="canal-link">denuncia@correiodonorte.com.br</a>
                    </div>
                </div>

                <div class="canal-card canal-errata">
                    <div class="canal-icon">📋</div>
                    <div class="canal-info">
                        <strong>Errata e Correções</strong>
                        <span>Encontrou um erro? Nos avise</span>
                        <a href="mailto:errata@correiodonorte.com.br" class="canal-link">errata@correiodonorte.com.br</a>
                    </div>
                </div>

                <div class="contato-endereco">
                    <h3>📍 Onde estamos</h3>
                    <p>Rua Cel. Leônidas Melo, 215 — Centro<br>
                    Parnaíba — PI | CEP 64.200-080</p>
                    <p class="contato-horario">⏰ Seg–Sex, das <strong>8h às 18h</strong></p>
                </div>
            </div>

            <!-- FORMULÁRIO -->
            <div class="contato-form-wrap">
                <h2 class="contato-section-title">Envie uma mensagem</h2>
                <div id="contato-result" style="display:none;padding:14px 18px;border-radius:8px;margin-bottom:20px;font-size:14px;font-weight:500;"></div>
                <form class="contato-form" id="contato-form" novalidate>
                    <?php wp_nonce_field( 'cdn_nonce', 'cdn_contact_nonce' ); ?>
                    <!-- Honeypot anti-spam (oculto) -->
                    <div style="display:none;" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Seu nome *</label>
                            <input type="text" id="nome" name="nome" placeholder="João Silva" required>
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail *</label>
                            <input type="email" id="email" name="email" placeholder="joao@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="assunto">Assunto *</label>
                        <select id="assunto" name="assunto" required>
                            <option value="">Selecione o assunto</option>
                            <option value="Sugestão de pauta">Sugestão de pauta</option>
                            <option value="Denúncia">Denúncia</option>
                            <option value="Correção de matéria">Correção de matéria</option>
                            <option value="Publicidade">Publicidade</option>
                            <option value="Elogio">Elogio</option>
                            <option value="Crítica">Crítica</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mensagem">Mensagem *</label>
                        <textarea id="mensagem" name="mensagem" rows="6" placeholder="Escreva aqui sua mensagem..." required></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="lgpd" required>
                        <label for="lgpd">Concordo com a <a href="<?php echo esc_url( home_url('/politica-de-privacidade/') ); ?>">Política de Privacidade</a> do Correio do Norte.</label>
                    </div>
                    <button type="submit" class="form-submit" id="contato-submit">
                        Enviar mensagem <span>→</span>
                    </button>
                    <p class="form-aviso">⏱️ Respondemos em até <strong>2 dias úteis</strong>.</p>
                </form>
                <script>
                (function(){
                    var form    = document.getElementById('contato-form');
                    var result  = document.getElementById('contato-result');
                    var btn     = document.getElementById('contato-submit');
                    if (!form) return;

                    form.addEventListener('submit', function(e){
                        e.preventDefault();

                        var lgpd = document.getElementById('lgpd');
                        if (!lgpd.checked) {
                            showResult('Por favor, aceite a Política de Privacidade.', false);
                            return;
                        }

                        var originalText = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '⏳ Enviando...';
                        result.style.display = 'none';

                        var fd = new FormData(form);
                        fd.append('action', 'cdn_contact_form');
                        fd.append('nonce', document.getElementById('cdn_contact_nonce') ? document.getElementById('cdn_contact_nonce').value : '');

                        fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
                            method: 'POST',
                            body: fd
                        })
                        .then(function(r){ return r.json(); })
                        .then(function(res){
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            if (res.success) {
                                showResult('✅ ' + res.data.message, true);
                                form.reset();
                            } else {
                                showResult('❌ ' + (res.data ? res.data.message : 'Erro ao enviar.'), false);
                            }
                        })
                        .catch(function(){
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            showResult('❌ Erro de conexão. Tente novamente.', false);
                        });
                    });

                    function showResult(msg, success) {
                        result.style.display = 'block';
                        result.style.background  = success ? '#e8f5e9' : '#fce4ec';
                        result.style.border      = '1px solid ' + (success ? '#a5d6a7' : '#f48fb1');
                        result.style.color       = success ? '#2e7d32' : '#c62828';
                        result.innerHTML = msg;
                        result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                })();
                </script>
        </div>
    </section>

</main>
<?php get_footer(); ?>

