<?php
/**
 * footer.php — Rodapé do tema Correio do Norte
 */
?>

<?php
// ===== DADOS DO LANÇAMENTO DE LIVRO =====
$book_img   = get_option('cdn_book_image') ?: content_url( 'uploads/2026/03/book-cover.jpg' );
$book_badge = get_option('cdn_book_badge') ?: 'Lançamento Especial';
$book_title = get_option('cdn_book_title') ?: 'O Despertar da Amazônia';
$book_desc  = get_option('cdn_book_desc')  ?: 'Uma obra épica que mergulha nas raízes profundas da floresta e de seu povo. Conheça a história real por trás dos mitos e a luta pela preservação de um dos maiores tesouros do planeta.';
$btn1_text  = get_option('cdn_book_btn1_text') ?: 'SAIBA MAIS';
$btn1_url   = get_option('cdn_book_btn1_url')  ?: home_url( '/o-despertar-da-amazonia/' );
$btn2_text  = get_option('cdn_book_btn2_text') ?: 'COMPRAR AGORA';
$btn2_url   = get_option('cdn_book_btn2_url')  ?: home_url( '/loja/' );
?>
<!-- ====== PROMO SECTION (Book / Special) ====== -->
<section class="promo-section">
    <div class="container promo-inner">
        <div class="promo-book-cover" style="aspect-ratio: auto !important;">
            <img src="<?php echo esc_url( $book_img ); ?>"
                 alt="<?php echo esc_attr( $book_title ); ?>"
                 loading="lazy"
                 style="width: 100%; height: auto; object-fit: contain !important;">
        </div>
        <div class="promo-content">
            <?php if ( $book_badge ) : ?><span class="promo-badge"><?php echo esc_html( $book_badge ); ?></span><?php endif; ?>
            <h2 class="promo-title"><?php echo esc_html( $book_title ); ?></h2>
            <p class="promo-desc"><?php echo esc_html( $book_desc ); ?></p>
            <div class="promo-actions">
                <?php if ( $btn1_text ) : ?><a href="<?php echo esc_url( $btn1_url ); ?>" class="btn-light"><?php echo esc_html( $btn1_text ); ?></a><?php endif; ?>
                <?php if ( $btn2_text ) : ?><a href="<?php echo esc_url( $btn2_url ); ?>" class="btn-outline-white"><?php echo esc_html( $btn2_text ); ?></a><?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ====== SITE FOOTER ====== -->
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-grid">

            <!-- Brand -->
            <div class="footer-brand footer-col">
                <div class="footer-logo">
                    <?php $f_logo = get_option('cdn_footer_logo'); if ( $f_logo ) : ?>
                        <img src="<?php echo esc_url( $f_logo ); ?>" alt="<?php bloginfo( 'name' ); ?>" style="max-height:50px; width:auto; filter:brightness(0) invert(1);">
                    <?php else : ?>
                        <div class="logo-icon" aria-hidden="true">
                            <span class="material-symbols-outlined">newspaper</span>
                        </div>
                        <span class="logo-text">CORREIO<span>DO</span>NORTE</span>
                    <?php endif; ?>
                </div>
                <p class="footer-tagline"><?php echo esc_html( get_option('cdn_footer_desc') ?: 'Informação de qualidade com o compromisso da verdade para todo o Norte do Brasil desde 1974.' ); ?></p>
                <div class="social-links">
                    <?php
                    $socials = [
                        'facebook'  => [ (get_option('cdn_social_facebook') ?: get_theme_mod( 'cdn_social_facebook', '#' )), 'Facebook', 'public' ],
                        'instagram' => [ (get_option('cdn_social_instagram') ?: get_theme_mod( 'cdn_social_instagram', '#' )), 'Instagram', 'share' ],
                        'twitter'   => [ (get_option('cdn_social_twitter') ?: get_theme_mod( 'cdn_social_twitter', '#' )), 'Twitter/X', 'tag' ],
                        'youtube'   => [ (get_option('cdn_social_youtube') ?: get_theme_mod( 'cdn_social_youtube', '#' )), 'YouTube', 'play_circle' ],
                        'email'     => [ 'mailto:' . antispambot( get_theme_mod( 'cdn_commercial_email', 'contato@correiodonorte.com.br' ) ), 'E-mail', 'mail' ],
                    ];
                    foreach ( $socials as $key => [ $url, $label, $icon ] ) :
                        if ( $url && $url !== '#' ) : ?>
                        <a href="<?php echo esc_url( $url ); ?>" class="social-link" aria-label="<?php echo esc_attr( $label ); ?>" rel="noopener noreferrer" <?php echo $key !== 'email' ? 'target="_blank"' : ''; ?>>
                            <span class="material-symbols-outlined" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
                        </a>
                    <?php endif; endforeach; ?>
                </div>
            </div>

            <!-- Categorias -->
            <div class="footer-col footer-col-categorias">
                <h5><?php _e( 'Categorias', 'correiodonorte' ); ?></h5>
                <ul>
                    <?php
                    $footer_cats = get_categories( [ 'orderby' => 'name', 'order' => 'ASC', 'number' => 8 ] );
                    foreach ( $footer_cats as $cat ) : ?>
                        <li><a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Institucional -->
            <div class="footer-col footer-col-institucional">
                <h5><?php _e( 'Institucional', 'correiodonorte' ); ?></h5>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/sobre-nos/' ) ); ?>">Sobre Nós</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/expediente/' ) ); ?>">Expediente</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/etica-e-compliance/' ) ); ?>">Ética e Compliance</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/fale-conosco/' ) ); ?>">Fale Conosco</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/anuncie/' ) ); ?>">Anuncie</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="footer-col">
                <h5><?php _e( 'Newsletter', 'correiodonorte' ); ?></h5>
                <p class="newsletter-desc">Receba os destaques do dia no seu e-mail.</p>
                <form class="newsletter-form" id="newsletter-form" novalidate>
                    <?php wp_nonce_field( 'cdn_nonce', '_cdn_nonce' ); ?>
                    <!-- Honeypot -->
                    <div style="display:none" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <label for="newsletter-email" class="sr-only">Seu e-mail</label>
                    <input type="email" id="newsletter-email" name="email" placeholder="Seu melhor e-mail" required autocomplete="email">
                    <button type="submit" class="btn-primary">INSCREVER</button>
                    <p class="newsletter-msg" id="newsletter-msg" style="display:none;font-size:.8rem;color:var(--color-primary)"></p>
                </form>
            </div>

        </div><!-- /.footer-grid -->

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <?php $copyright = get_option('cdn_footer_copyright') ?: ('&copy; ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. Todos os direitos reservados.'); ?>
            <p><?php echo wp_kses_post( $copyright ); ?></p>
            <nav class="footer-legal" aria-label="Links legais">
                <a href="<?php echo esc_url( home_url( '/termos-de-uso/' ) ); ?>">Termos de Uso</a>
                <a href="<?php echo esc_url( home_url( '/politica-de-privacidade/' ) ); ?>">Política de Privacidade</a>
                <a href="<?php echo esc_url( home_url( '/politica-de-cookies/' ) ); ?>">Cookies</a>
            </nav>
        </div>

    </div><!-- /.container -->
</footer>

<!-- Back to Top -->
<button class="back-to-top" id="back-to-top" aria-label="Voltar ao topo">
    <span class="material-symbols-outlined" aria-hidden="true">arrow_upward</span>
</button>

<!-- Page Loading Animation -->
<div id="cdn-page-loader" class="cdn-loader" aria-hidden="true">
    <div class="cdn-loader-inner">
        <div class="cdn-loader-icon">
            <span class="material-symbols-outlined">newspaper</span>
        </div>
        <div class="cdn-loader-brand">CORREIO<span>DO NORTE</span></div>
        <div class="cdn-loader-dots">
            <span></span><span></span><span></span>
        </div>
    </div>
</div>

<script>
(function() {
    var loader = document.getElementById('cdn-page-loader');
    // Hide as soon as DOM is ready to avoid stalling on slow image loading
    document.addEventListener('DOMContentLoaded', function() {
        loader.classList.add('cdn-loader--hidden');
    });
    // Fallback if DOMContentLoaded already fired before script execution
    if(document.readyState === 'interactive' || document.readyState === 'complete') {
        loader.classList.add('cdn-loader--hidden');
    }

    // Show on internal link click
    document.addEventListener('click', function(e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return;
        if (a.target === '_blank') return;
        try {
            var url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return;
        } catch(e) { return; }
        loader.classList.remove('cdn-loader--hidden');
        
        // Failsafe tightly bound to click: hide after 2.5s if navigation didn't happen
        setTimeout(function() { loader.classList.add('cdn-loader--hidden'); }, 2500);
    });
})();
</script>

<!-- Developer Signature -->
<a href="#" class="dev-badge-float" aria-label="Desenvolvido por Bruno" title="Desenvolvido por Bruno">
    <div class="dev-badge-inner">BM</div>
</a>

<?php wp_footer(); ?>
</body>
</html>
