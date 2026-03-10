<?php
/**
 * Template Name: Lançamento de Livro
 * Description: Landing page focada em alta conversão para lançamento de livros.
 */
get_header();

$book_img   = get_option('cdn_book_image');
$book_badge = get_option('cdn_book_badge', 'LANÇAMENTO');
$book_title = get_option('cdn_book_title', 'Título do Livro');
$book_desc  = get_option('cdn_book_desc', 'Descrição impactante e irresistível da obra para prender a atenção do leitor logo nas primeiras linhas.');
$btn1_text  = get_option('cdn_book_btn1_text', 'Saiba Mais');
$btn1_url   = get_option('cdn_book_btn1_url', '#');
$btn2_text  = get_option('cdn_book_btn2_text', 'Comprar Agora');
$btn2_url   = get_option('cdn_book_btn2_url', '#');

// Fallback image in case none was uploaded
if ( ! $book_img ) {
    $book_img = content_url( 'uploads/2026/03/placeholder-book.jpg' ); 
}
?>

<main id="main-content" class="book-landing-page" style="background: var(--color-bg); overflow: hidden;">
    
    <!-- Hero Section -->
    <section class="book-hero" style="position: relative; min-height: 85vh; display: flex; align-items: center; padding: 4rem 1rem;">
        
        <!-- Animated Background Orbs (Glassmorphism effect) -->
        <div class="hero-bg-orbs" aria-hidden="true" style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:0; overflow:hidden;">
            <div style="position:absolute; top:-10%; right:-5%; width:50vw; height:50vw; background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%); opacity:0.15; border-radius:50%; filter:blur(60px); animation: floatOrb 10s ease-in-out infinite alternate;"></div>
            <div style="position:absolute; bottom:-20%; left:-10%; width:60vw; height:60vw; background: radial-gradient(circle, var(--color-accent-blue) 0%, transparent 70%); opacity:0.1; border-radius:50%; filter:blur(80px); animation: floatOrb 15s ease-in-out infinite alternate-reverse;"></div>
        </div>

        <div class="container" style="position: relative; z-index: 2; max-width: 1200px; margin: 0 auto;">
            <div class="book-hero-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
                
                <!-- Left: Typography & CTA -->
                <div class="book-content" style="animation: fadeInUp 1s ease forwards;">
                    
                    <?php if ( $book_badge ) : ?>
                    <div class="book-badge" style="display: inline-block; padding: 0.5rem 1rem; border-radius: 50px; background: rgba(var(--color-primary-rgb, 220, 38, 38), 0.1); border: 1px solid rgba(var(--color-primary-rgb, 220, 38, 38), 0.3); color: var(--color-primary); font-weight: 800; font-size: 0.85rem; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(var(--color-primary-rgb, 220, 38, 38), 0.15);">
                        <span class="material-symbols-outlined" style="font-size: 1.1rem; vertical-align: middle; margin-right: 0.3rem;">stars</span>
                        <?php echo esc_html($book_badge); ?>
                    </div>
                    <?php endif; ?>

                    <h1 style="font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 900; line-height: 1.1; margin-bottom: 1.5rem; color: var(--color-text-heading); letter-spacing: -1px;">
                        <?php echo esc_html($book_title); ?>
                    </h1>
                    
                    <p style="font-size: 1.25rem; line-height: 1.6; color: var(--color-text); margin-bottom: 2.5rem; font-weight: 400; max-width: 600px;">
                        <?php echo nl2br(esc_html($book_desc)); ?>
                    </p>

                    <div class="book-actions" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <?php if ( $btn2_text && $btn2_url ) : ?>
                        <a href="<?php echo esc_url($btn2_url); ?>" class="btn-book-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; background: var(--color-primary); color: #ffffff; padding: 1.2rem 2.5rem; border-radius: 8px; font-weight: 800; font-size: 1.1rem; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(var(--color-primary-rgb, 220, 38, 38), 0.4);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(var(--color-primary-rgb, 220, 38, 38), 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(var(--color-primary-rgb, 220, 38, 38), 0.4)'">
                            <span class="material-symbols-outlined">shopping_cart</span>
                            <?php echo esc_html($btn2_text); ?>
                        </a>
                        <?php endif; ?>

                        <?php if ( $btn1_text && $btn1_url ) : ?>
                        <a href="<?php echo esc_url($btn1_url); ?>" class="btn-book-secondary" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; background: transparent; color: var(--color-text-heading); border: 2px solid var(--color-border); padding: 1.1rem 2rem; border-radius: 8px; font-weight: 700; font-size: 1.1rem; text-decoration: none; transition: all 0.3s ease;" onmouseover="this.style.background='var(--color-surface)'; this.style.borderColor='var(--color-text-heading)';" onmouseout="this.style.background='transparent'; this.style.borderColor='var(--color-border)';">
                            <span class="material-symbols-outlined">menu_book</span>
                            <?php echo esc_html($btn1_text); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 1rem; margin-top: 3rem; opacity: 0.8;">
                        <span class="material-symbols-outlined" style="color: #FFD700;">star star star star star</span>
                        <span style="font-size: 0.9rem; font-weight: 600; color: var(--color-text);">"Leitura obrigatória e impactante."</span>
                    </div>
                </div>

                <!-- Right: 3D Book Cover -->
                <div class="book-cover-wrapper" style="position: relative; perspective: 1000px; animation: floatBook 6s ease-in-out infinite; display:flex; justify-content:center;">
                    
                    <!-- Glow behind book -->
                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:80%; height:90%; background:var(--color-primary); filter:blur(80px); opacity:0.3; z-index:0; border-radius:20px;"></div>

                    <img src="<?php echo esc_url($book_img); ?>" alt="Capa do Livro" style="position:relative; z-index:2; width: 100%; max-width: 450px; height: auto; border-radius: 4px 12px 12px 4px; box-shadow: -15px 25px 50px rgba(0,0,0,0.5), inset 4px 0 10px rgba(255,255,255,0.2); transform: rotateY(-15deg) rotateX(5deg); transform-style: preserve-3d; transition: transform 0.5s ease;" onmouseover="this.style.transform='rotateY(-5deg) rotateX(2deg) scale(1.02)'" onmouseout="this.style.transform='rotateY(-15deg) rotateX(5deg) scale(1)'">
                    
                    <!-- Book spine illusion -->
                    <div style="position:absolute; top:0; left:12%; width:5%; height:100%; background: linear-gradient(to right, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0) 100%); z-index:3; transform: rotateY(-15deg) rotateX(5deg); pointer-events:none; border-radius: 4px 0 0 4px;"></div>
                </div>

            </div>
        </div>
    </section>

    <!-- HTML structure ends, adding keyframes directly inside page stringency -->
    <style>
        @keyframes floatOrb {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(50px, -50px) scale(1.1); }
        }
        @keyframes floatBook {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 900px) {
            .book-hero-grid {
                grid-template-columns: 1fr !important;
                text-align: center;
                gap: 2rem !important;
            }
            .book-actions {
                justify-content: center;
            }
            .book-cover-wrapper img {
                transform: rotateY(0deg) rotateX(0deg) !important;
            }
            .book-cover-wrapper {
                animation: none !important;
            }
        }
    </style>

</main>

<?php get_footer(); ?>
