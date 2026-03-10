<?php
/**
 * Template Name: Publicar Vaga (Front-End)
 * Description: Página com formulário público para submissão de vagas de emprego.
 */

get_header(); ?>

<main class="site-main container" style="margin-top: 3rem; margin-bottom: 5rem; min-height: 60vh;">
    <div style="max-width: 800px; margin: 0 auto; background: var(--color-surface); padding: 3rem; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
        
        <header style="text-align: center; margin-bottom: 2.5rem;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(var(--color-primary-rgb), 0.1); color: var(--color-primary); border-radius: 50%; margin-bottom: 1rem;">
                <span class="material-symbols-outlined" style="font-size: 32px;">post_add</span>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--color-text-heading); margin-bottom: 0.5rem;">Anuncie sua Vaga</h1>
            <p style="color: var(--color-text-muted); font-size: 1.1rem;">Preencha os detalhes da oportunidade. Sua vaga passará por uma rápida revisão administrativa antes de ser publicada no portal.</p>
        </header>

        <?php if ( isset($_GET['status']) && $_GET['status'] == 'success' ) : ?>
            <div style="background: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid #28a745; border-radius: var(--radius-md); padding: 1.5rem; text-align: center; margin-bottom: 2rem;">
                <span class="material-symbols-outlined" style="font-size: 40px; margin-bottom: 0.5rem; display: block;">check_circle</span>
                <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.25rem;">Vaga Recebida com Sucesso!</h3>
                <p style="margin: 0; font-size: 0.95rem;">Nossa equipe já foi notificada. A vaga estará no ar assim que for aprovada.</p>
                <a href="<?php echo esc_url(home_url('/vagas/')); ?>" class="btn-primary" style="display: inline-block; margin-top: 1rem;">Ver Quadro de Vagas</a>
            </div>
        <?php else : ?>

            <?php if ( isset($_GET['status']) && $_GET['status'] == 'error' ) : ?>
                <div style="background: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid #dc3545; border-radius: var(--radius-md); padding: 1rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-outlined">error</span>
                    <span>Houve um erro no envio. Verifique se preencheu todos os campos obrigatórios e tente novamente.</span>
                </div>
            <?php endif; ?>

            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Ação do Admin Post -->
                <input type="hidden" name="action" value="submit_vaga">
                <?php wp_nonce_field('submit_new_vaga', 'vaga_nonce'); ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label for="vaga_titulo" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-body);">Título da Vaga <span style="color:red">*</span></label>
                        <input type="text" id="vaga_titulo" name="vaga_titulo" required placeholder="Ex: Analista de Marketing Jr." style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg); color: var(--color-text-body); font-family: inherit;">
                    </div>
                    <div>
                        <label for="vaga_empresa" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-body);">Nome da Empresa <span style="color:red">*</span></label>
                        <input type="text" id="vaga_empresa" name="vaga_empresa" required placeholder="Ex: ACME Corporation" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg); color: var(--color-text-body); font-family: inherit;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label for="vaga_salario" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-body);">Faixa Salarial</label>
                        <input type="text" id="vaga_salario" name="vaga_salario" placeholder="Ex: R$ 3.000 ou A Combinar" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg); color: var(--color-text-body); font-family: inherit;">
                    </div>
                    <div>
                        <label for="vaga_local" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-body);">Local / Modelo</label>
                        <input type="text" id="vaga_local" name="vaga_local" placeholder="Ex: Parnaíba / Remoto" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg); color: var(--color-text-body); font-family: inherit;">
                    </div>
                    <div>
                        <label for="vaga_email" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-body);">E-mail p/ Contato <span style="color:red">*</span></label>
                        <input type="email" id="vaga_email" name="vaga_email" required placeholder="rh@empresa.com" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg); color: var(--color-text-body); font-family: inherit;">
                    </div>
                </div>

                <div>
                    <label for="vaga_descricao" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-text-body);">Descrição da Vaga e Requisitos <span style="color:red">*</span></label>
                    <textarea id="vaga_descricao" name="vaga_descricao" rows="8" required placeholder="Descreva as responsabilidades, requisitos, benefícios..." style="width: 100%; padding: 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg); color: var(--color-text-body); font-family: inherit; resize: vertical;"></textarea>
                </div>

                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: var(--radius-md); justify-content: center;">
                        <span class="material-symbols-outlined">send</span> Enviar Vaga para Revisão
                    </button>
                    <p style="text-align: center; font-size: 0.85rem; color: var(--color-text-muted); margin-top: 1rem;">
                        Ao enviar, você concorda que o Correio do Norte avaliará a vaga antes da publicação pública.
                    </p>
                </div>
            </form>

            <style>
                @media (max-width: 768px) {
                    form > div[style*="grid-template-columns"] {
                        grid-template-columns: 1fr !important;
                        gap: 1rem !important;
                    }
                }
            </style>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
