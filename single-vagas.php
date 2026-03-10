<?php
/**
 * single-vagas.php — Template individual para Vagas de Emprego
 */
get_header();
while ( have_posts() ) :
    the_post();
    $empresa = get_post_meta(get_the_ID(), 'cdn_vaga_empresa', true);
    $salario = get_post_meta(get_the_ID(), 'cdn_vaga_salario', true);
    $local   = get_post_meta(get_the_ID(), 'cdn_vaga_local', true);
    $link    = get_post_meta(get_the_ID(), 'cdn_vaga_link', true);
?>
<main id="main-content" class="container" style="padding: 2rem 1rem;">
    <div class="single-layout">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <nav aria-label="Caminho de navegação" style="margin-bottom: 1rem;font-size:0.85rem;color:var(--color-text-muted)">
                <a href="<?php echo esc_url( home_url() ); ?>" style="color:var(--color-primary)">Home</a>
                &rsaquo; <a href="<?php echo esc_url( home_url('/vagas/') ); ?>" style="color:var(--color-primary)">Vagas de Emprego</a>
                &rsaquo; <span><?php echo esc_html( wp_trim_words( get_the_title(), 8 ) ); ?></span>
            </nav>

            <!-- HEADER DA VAGA -->
            <header class="post-header" style="margin-bottom: 2rem; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <div style="display:flex; align-items:center; gap: 1rem; margin-bottom:1rem;">
                            <span class="post-category" style="display:inline-block; font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--color-primary); background:rgba(var(--color-primary-rgb),0.1); padding:4px 10px; border-radius:30px; letter-spacing:1px; margin-bottom:0;">Oportunidade de Emprego</span>
                            <span style="font-size:0.85rem; color:var(--color-text-muted); display:flex; align-items:center; gap:4px;"><span class="material-symbols-outlined" style="font-size:16px;">calendar_today</span> Publicado em <?php echo get_the_date(); ?></span>
                        </div>
                        <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 900; line-height: 1.1; margin-bottom: 0.5rem; color: var(--color-text-heading);"><?php the_title(); ?></h1>
                        <p style="font-size: 1.1rem; color: var(--color-text-muted); display:flex; align-items:center; gap:0.5rem;">
                            <span class="material-symbols-outlined" style="font-size:1.2rem">apartment</span> <?php echo esc_html($empresa ?: 'Empresa Confidencial'); ?>
                        </p>
                    </div>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div style="width:80px; height:80px; border-radius:var(--radius-md); overflow:hidden; border:1px solid var(--color-border); flex-shrink: 0;">
                            <?php the_post_thumbnail('thumbnail', ['style' => 'width:100%; height:100%; object-fit:cover;']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAGS DE RESUMO -->
                <div style="display:flex; flex-wrap: wrap; gap: 1rem; border-top: 1px solid var(--color-border); padding-top: 1.5rem;">
                    <div style="display:flex; align-items:center; gap:0.75rem; background:var(--color-background); padding:0.75rem 1.25rem; border-radius:var(--radius-md);">
                        <span class="material-symbols-outlined" style="color:var(--color-primary);">location_on</span>
                        <div>
                            <span style="display:block; font-size:0.75rem; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Local / Região</span>
                            <strong style="color:var(--color-text-heading);"><?php echo esc_html($local ?: 'Não especificado'); ?></strong>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem; background:var(--color-background); padding:0.75rem 1.25rem; border-radius:var(--radius-md);">
                        <span class="material-symbols-outlined" style="color:var(--color-primary);">payments</span>
                        <div>
                            <span style="display:block; font-size:0.75rem; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Faixa Salarial</span>
                            <strong style="color:var(--color-text-heading);"><?php echo esc_html($salario ?: 'A combinar'); ?></strong>
                        </div>
                    </div>
                </div>

                <?php 
                if ($link) : 
                    $is_mailto = strpos($link, 'mailto:') === 0;
                    $target_attr = $is_mailto ? '' : 'target="_blank" rel="noopener noreferrer"';
                    $btn_icon = $is_mailto ? 'mail' : 'send';
                    $btn_text = $is_mailto ? 'Enviar Currículo por E-mail' : 'Cadastre-se na Vaga';
                ?>
                <div style="margin-top: 2rem;">
                    <a href="<?php echo esc_url($link); ?>" <?php echo $target_attr; ?> class="btn-primary btn-pulse-anim" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 1rem 2rem; border-radius: 50px; font-weight: 800; font-size: 1.1rem; width: 100%; max-width: 400px; text-decoration:none;">
                        <span class="material-symbols-outlined"><?php echo $btn_icon; ?></span> <?php echo $btn_text; ?>
                    </a>
                </div>
                <?php endif; ?>
            </header>

            <div class="post-content" style="font-size: 1.1rem; line-height: 1.7; color: var(--color-text);">
                <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; color: var(--color-text-heading);">Descrição da Vaga</h2>
                <?php the_content(); ?>
            </div>

        </article>
        
        <?php get_sidebar(); ?>
    </div>
</main>
<?php endwhile; get_footer(); ?>
