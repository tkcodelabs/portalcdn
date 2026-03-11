<?php
/**
 * Template Name: Edição Digital (Flipbook)
 */
get_header();
?>

<main id="main-content" class="digital-edition-page" style="background: #f8f9fa; min-height: calc(100vh - 80px); padding: 3rem 0;">
    <div class="container">
        
        <div class="digital-header">
            <div>
                <h1 class="inst-hero-title" style="font-size: 2rem; margin-bottom: 0.5rem; color: #111;">Edição Digital — Correio do Norte</h1>
                <p style="margin: 0; color: #666; font-size: 1.1rem;">Navegue pela edição impressa completa de hoje diretamente na sua tela.</p>
            </div>
            <div class="digital-actions" style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button class="btn btn-outline btn-fullscreen" style="border: 2px solid var(--color-primary); color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem; background: transparent; font-weight: 700; transition: all 0.3s ease; border-radius: 8px; padding: 10px 20px" onmouseover="this.style.background='var(--color-primary)'; this.style.color='#fff'" onmouseout="this.style.background='transparent'; this.style.color='var(--color-primary)'" onclick="toggleFullscreen()"><span class="material-symbols-outlined">fullscreen</span> <span id="fs-text">Ativar Tela Cheia</span></button>
                <a href="#" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;" onclick="alert('Download da edição PDF iniciado demonstrativamente.')"><span class="material-symbols-outlined">download</span> Baixar PDF</a>
            </div>
        </div>

        <div id="flipbook-wrapper" style="background: #e2e8f0; padding: 3rem 2rem; border-radius: 16px; margin-top: 2rem; display: flex; justify-content: center; position: relative; box-shadow: inset 0 2px 10px rgba(0,0,0,0.05); min-height: 600px; align-items:center;">
            
            <?php $pdf_url = get_option('cdn_digital_pdf'); if ( ! $pdf_url ) : ?>
                <div style="text-align:center; padding: 3rem; background: white; border-radius: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 3rem; color: #ccc;">picture_as_pdf</span>
                    <h3 style="margin: 1rem 0 0.5rem;">Nenhuma edição disponível</h3>
                    <p style="color: #666; margin-bottom: 0;">O administrador ainda não enviou o PDF da edição digital de hoje no Painel CDN.</p>
                </div>
            <?php else: ?>
                <!-- Loader inicial -->
                <div id="pdf-loader" style="display:flex; flex-direction:column; align-items:center; gap: 1rem;">
                    <div style="width: 40px; height: 40px; border: 4px solid var(--primary-color); border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="font-weight:bold; color:#444;">Renderizando Edição Digital...</p>
                </div>

                <div id="flipbook" class="flipbook" style="display: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                    <!-- As páginas do PDF serão injetadas aqui pelo PDF.js -->
                </div>
                
                <!-- Controles do Flipbook -->
                <div id="flipbook-controls" class="flipbook-controls" style="display:none; position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.85); backdrop-filter: blur(10px); padding: 10px 25px; border-radius: 40px; color: #fff; gap: 20px; align-items: center; z-index: 10; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);">
                    <button id="btn-zoom-out" style="background:none; border:none; color:white; cursor:pointer; display:flex; align-items:center; opacity: 0.7; transition: all 0.2s; padding:0" onmouseover="this.style.opacity=1; this.style.color='var(--color-primary)'" onmouseout="this.style.opacity=0.7; this.style.color='white'" title="Diminuir Zoom"><span class="material-symbols-outlined" style="font-size:1.5rem">zoom_out</span></button>
                    <div style="width: 1px; height: 20px; background: rgba(255,255,255,0.2)"></div>
                    
                    <button id="btn-prev" style="background:none; border:none; color:white; cursor:pointer; display:flex; align-items:center; opacity: 0.8; transition: opacity 0.2s; padding:0" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8" title="Página Anterior"><span class="material-symbols-outlined" style="font-size: 1.8rem">arrow_back_ios_new</span></button>
                    <span id="page-info" style="font-size: 1.1rem; font-weight: 700; min-width: 60px; text-align: center; font-variant-numeric: tabular-nums; letter-spacing: 1px">- / -</span>
                    <button id="btn-next" style="background:none; border:none; color:white; cursor:pointer; display:flex; align-items:center; opacity: 0.8; transition: opacity 0.2s; padding:0" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8" title="Próxima Página"><span class="material-symbols-outlined" style="font-size: 1.8rem">arrow_forward_ios</span></button>
                    
                    <div style="width: 1px; height: 20px; background: rgba(255,255,255,0.2)"></div>
                    <button id="btn-zoom-in" style="background:none; border:none; color:white; cursor:pointer; display:flex; align-items:center; opacity: 0.7; transition: all 0.2s; padding:0" onmouseover="this.style.opacity=1; this.style.color='var(--color-primary)'" onmouseout="this.style.opacity=0.7; this.style.color='white'" title="Aumentar Zoom"><span class="material-symbols-outlined" style="font-size:1.5rem">zoom_in</span></button>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
.digital-header {
    display: flex; 
    justify-content: space-between; 
    align-items: flex-end; 
    border-bottom: 1px solid #ddd;
    padding-bottom: 1.5rem;
}
@media (max-width: 768px) {
    .digital-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
.fb-page {
    background-color: white !important;
    overflow: hidden;
}
.fb-page canvas {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
</style>

<?php if ( $pdf_url ) : ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Atualiza o link do botão de Download PDF
    const downloadBtn = document.querySelector('a[onclick="alert(\'Download da edição PDF iniciado demonstrativamente.\')"]');
    if (downloadBtn) {
        downloadBtn.removeAttribute('onclick');
        downloadBtn.setAttribute('href', '<?php echo esc_url( $pdf_url ); ?>');
        downloadBtn.setAttribute('download', '');
        downloadBtn.setAttribute('target', '_blank');
    }

    const url = '<?php echo esc_url( wp_make_link_relative( $pdf_url ) ); ?>';
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    const flipbookEl = document.getElementById('flipbook');
    const loader = document.getElementById('pdf-loader');
    const controls = document.getElementById('flipbook-controls');

    let pdfDoc = null;

    pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        const numPages = pdfDoc.numPages;
        let pagesRendered = 0;
        
        // Criar containers e renderizar páginas
        for(let num = 1; num <= numPages; num++) {
            const div = document.createElement('div');
            div.className = 'fb-page';
            // Hard data-density for cover and backcover
            if (num === 1 || num === numPages) {
                div.setAttribute('data-density', 'hard');
            }
            
            const canvas = document.createElement('canvas');
            div.appendChild(canvas);
            flipbookEl.appendChild(div);

            pdfDoc.getPage(num).then(function(page) {
                // Aumentar a escala para melhor resolução no canvas
                const viewport = page.getViewport({scale: 1.5});
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                const renderContext = {
                    canvasContext: canvas.getContext('2d'),
                    viewport: viewport
                };
                
                page.render(renderContext).promise.then(function() {
                    pagesRendered++;
                    // Quando todas as páginas do PDF terminarem de renderizar no Canvas...
                    if (pagesRendered === numPages) {
                        initFlipbook();
                    }
                });
            });
        }
    }).catch(function(error) {
        loader.innerHTML = '<p style="color:red">Erro ao carregar o PDF. O arquivo pode estar corrompido ou ter problemas de CORS (se for externo).</p>';
        console.error(error);
    });

    function initFlipbook() {
        loader.style.display = 'none';
        flipbookEl.style.display = 'block';
        controls.style.display = 'flex';
        
        const pageFlip = new St.PageFlip(flipbookEl, {
            width: 450, 
            height: 600, 
            size: "stretch", 
            minWidth: 300,
            maxWidth: 600,
            minHeight: 420,
            maxHeight: 850,
            maxShadowOpacity: 0.4,
            showCover: true,
            mobileScrollSupport: false,
            usePortrait: true
        });

        pageFlip.loadFromHTML(document.querySelectorAll('.fb-page'));

        document.getElementById('btn-prev').addEventListener('click', () => { pageFlip.flipPrev(); });
        document.getElementById('btn-next').addEventListener('click', () => { pageFlip.flipNext(); });

        const updatePageInfo = (e) => {
            const total = pageFlip.getPageCount();
            let current = (e && e.data !== undefined) ? e.data + 1 : 1;
            
            if (pageFlip.getOrientation() === 'landscape' && current > 1 && current < total) {
                document.getElementById('page-info').textContent = `${current}-${current+1} / ${total}`;
            } else {
                document.getElementById('page-info').textContent = `${current} / ${total}`;
            }
        };

        pageFlip.on('flip', updatePageInfo);
        
        // Atualização inicial forçada
        setTimeout(updatePageInfo, 200);
    }
});

function toggleFullscreen() {
    const elem = document.getElementById("flipbook-wrapper");
    const text = document.getElementById("fs-text");
    if (!document.fullscreenElement) {
        elem.requestFullscreen().catch(err => {
            console.log(`Erro ao ativar tela cheia: ${err.message}`);
        });
        elem.style.borderRadius = "0";
        elem.style.margin = "0";
        text.innerText = "Sair";
    } else {
        document.exitFullscreen();
        elem.style.borderRadius = "16px";
        elem.style.marginTop = "2rem";
        text.innerText = "Ativar Tela Cheia";
        
        // Reset zoom on exit full screen to avoid bugs
        currentZoom = 1;
        document.getElementById('flipbook').style.transform = `scale(${currentZoom})`;
    }
}

document.addEventListener("fullscreenchange", function() {
    const elem = document.getElementById("flipbook-wrapper");
    const text = document.getElementById("fs-text");
    if (!document.fullscreenElement) {
        elem.style.borderRadius = "16px";
        elem.style.marginTop = "2rem";
        text.innerText = "Ativar Tela Cheia";
        currentZoom = 1;
        document.getElementById('flipbook').style.transform = `scale(${currentZoom})`;
    } else {
        // Enforce padding top when in fullscreen to not clash with controls
        elem.style.paddingTop = '60px';
    }
});

// Zoom Logic
let currentZoom = 1;
document.addEventListener('DOMContentLoaded', function() {
    const btnZoomIn = document.getElementById('btn-zoom-in');
    const btnZoomOut = document.getElementById('btn-zoom-out');
    const flipbook = document.getElementById('flipbook');
    
    if(btnZoomIn && btnZoomOut && flipbook) {
        flipbook.style.transition = 'transform 0.3s ease';
        
        btnZoomIn.addEventListener('click', () => {
            currentZoom += 0.2;
            if(currentZoom > 2.5) currentZoom = 2.5;
            flipbook.style.transform = `scale(${currentZoom})`;
            flipbook.style.transformOrigin = `center top`;
        });
        
        btnZoomOut.addEventListener('click', () => {
            currentZoom -= 0.2;
            if(currentZoom < 0.8) currentZoom = 0.8;
            flipbook.style.transform = `scale(${currentZoom})`;
            flipbook.style.transformOrigin = `center top`;
        });
        
        // Pannable logic when zoomed in? The page-wrapper CSS handles hidden overflow, 
        // to actually pan we might just let the user drag or scroll. The easiest is CSS overflow auto on the wrapper.
        document.getElementById('flipbook-wrapper').style.overflow = 'auto';
    }
});
</script>
<?php endif; ?>

<?php get_footer(); ?>
