<?php
/**
 * archive-vagas.php — Arquivo de listagem de Vagas de Emprego
 */
get_header();
?>
<main id="main-content" class="container" style="padding: 3rem 1rem;">
    <div class="section-header">
        <div class="accent-bar"></div>
        <h1 style="font-size: 1.8rem; font-weight: 900; letter-spacing: -0.02em;">Painel de Vagas</h1>
    </div>

    <?php if ( have_posts() ) : ?>
        <section class="news-grid" aria-label="Grade de vagas">
            <?php while ( have_posts() ) : the_post(); 
                $empresa = get_post_meta(get_the_ID(), 'cdn_vaga_empresa', true);
                $local = get_post_meta(get_the_ID(), 'cdn_vaga_local', true);
            ?>
                <article class="news-card" style="display:flex; flex-direction:column; background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:2rem; transition: transform 0.2s, box-shadow 0.2s;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem;">
                        <span style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--color-primary); background:rgba(var(--color-primary-rgb),0.1); padding:4px 10px; border-radius:30px; letter-spacing:1px;">Oportunidade</span>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div style="width:40px; height:40px; border-radius:50%; overflow:hidden; border:1px solid var(--color-border);">
                                <?php the_post_thumbnail('thumbnail', ['style' => 'width:100%; height:100%; object-fit:cover;']); ?>
                            </div>
                        <?php else : ?>
                            <div style="width:40px; height:40px; border-radius:50%; background:var(--color-background); display:flex; align-items:center; justify-content:center; color:var(--color-text-muted); border:1px solid var(--color-border);">
                                <span class="material-symbols-outlined" style="font-size:1.2rem">apartment</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h3 style="font-size: 1.3rem; font-weight: 800; line-height: 1.3; margin-bottom: 0.75rem;">
                        <a href="<?php the_permalink(); ?>" style="color:var(--color-text-heading); text-decoration:none;"><?php the_title(); ?></a>
                    </h3>
                    
                    <div style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 2rem; display:flex; flex-direction:column; gap:0.5rem;">
                        <span style="display:flex; align-items:center; gap:6px;"><span class="material-symbols-outlined" style="font-size:1.1rem">apartment</span> <?php echo esc_html($empresa ?: 'Não informada'); ?></span>
                        <span style="display:flex; align-items:center; gap:6px;"><span class="material-symbols-outlined" style="font-size:1.1rem">location_on</span> <?php echo esc_html($local ?: 'Não informado'); ?></span>
                        <span style="display:flex; align-items:center; gap:6px; margin-top: 0.5rem; color: var(--color-text-muted);"><span class="material-symbols-outlined" style="font-size:1.1rem">event</span> Publicado em <?php echo get_the_date(); ?></span>
                    </div>
                    
                    <a href="<?php the_permalink(); ?>" class="btn-primary" style="margin-top:auto; padding:0.75rem 1rem; border-radius:50px; font-weight:700; font-size:0.9rem; width:100%; text-align:center;">Ver Detalhes da Vaga</a>
                </article>
            <?php endwhile; ?>
        </section>

        <!-- Paginação -->
        <div style="margin-top: 3rem; text-align: center;">
            <?php echo paginate_links([
                'prev_text' => '&laquo; Anterior',
                'next_text' => 'Próxima &raquo;',
            ]); ?>
        </div>

    <?php else : ?>
        <div style="background: var(--color-surface); padding: 3rem; text-align: center; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <span class="material-symbols-outlined" style="font-size: 3rem; color: var(--color-text-muted); margin-bottom: 1rem;">work_history</span>
            <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Nada por aqui</h2>
            <p style="color: var(--color-text-muted);">Ainda não há vagas cadastradas ou em aberto no momento.</p>
        </div>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
