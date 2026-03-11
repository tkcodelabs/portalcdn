<?php
/**
 * 404.php
 */
get_header();
?>
<main id="main-content" class="container">
    <div class="error-page">
        <div class="error-code" aria-hidden="true">404</div>
        <h1 class="error-title">Página não encontrada</h1>
        <p class="error-desc">A página que você está procurando não existe ou foi movida.</p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary" style="padding:.875rem 2rem">
            <span class="material-symbols-outlined" aria-hidden="true">home</span> Voltar para tela inicial
        </a>
        <div style="margin-top:3rem;max-width:480px;margin-left:auto;margin-right:auto">
            <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:flex;gap:.5rem">
                <input type="search" name="s" placeholder="Pesquisar no site..." style="flex:1;padding:.75rem 1rem;border:1px solid var(--color-border);border-radius:var(--radius-md);font-family:inherit;outline:none" autocomplete="off">
                <button type="submit" class="btn-primary" style="padding:.75rem 1.25rem">
                    <span class="material-symbols-outlined" aria-hidden="true">search</span>
                </button>
            </form>
        </div>
    </div>
</main>
<?php get_footer(); ?>
