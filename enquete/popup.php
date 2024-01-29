<!-- Popup -->
<div id="popup" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compartilhe e nos Ajude-nos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    <i class="bi bi-megaphone" style="color: #0275d8;"></i> <strong>Chamada à Ação:</strong>
                    Estamos diante de um momento crucial e sua ajuda é fundamental.
                </p>
                <p>
                    <i class="bi bi-people-fill" style="color: #5cb85c;"></i> <strong>Unidos por uma Causa:</strong>
                    Junte-se a nós na petição para a <a href="https://participato.com.br/sistema/peticao/petition_details.php?id=6" target="_blank" style="color: #6a0000; font-weight: bold; text-decoration: underline;">"Exoneração Imediata do Secretário Executivo Milton Neris por Assédio Moral e Sexual"</a>.
                    Cada voz conta e a sua pode fazer a diferença.
                </p>
                <p>
                    <i class="bi bi-arrow-up-right-square" style="color: #f0ad4e;"></i> <strong>Amplie Nosso Alcance:</strong>
                    Ao compartilhar esta mensagem, você amplifica nosso apelo por justiça e pressiona os governantes a tomar conhecimento e agir.
                </p>
                <p>
                    <i class="bi bi-hand-thumbs-up" style="color: #d9534f;"></i> <strong>Sua Ação Importa:</strong>
                    É um passo simples, mas poderoso, em direção a um futuro mais justo e seguro para todos. Compartilhe agora e faça parte desta mudança vital.
                </p>

                <!-- Botões de compartilhamento -->
                <center>
                    <div class="btn-group">
                    <!-- Botão de compartilhamento do WhatsApp -->
                    <a href="https://api.whatsapp.com/send?text=https://participato.com.br/sistema/peticao/petition_details.php?id=6" target="_blank" class="btn btn-success btn-sm"><i class="bi bi-whatsapp"></i> WhatsApp</a>

                    <!-- Botão de compartilhamento do Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://participato.com.br/sistema/peticao/petition_details.php?id=6" target="_blank" class="btn btn-primary btn-sm"><i class="bi bi-facebook"></i> Facebook</a>

                    <!-- Botão de compartilhamento do Twitter -->
                    <a href="https://twitter.com/intent/tweet?url=https://participato.com.br/sistema/peticao/petition_details.php?id=6" target="_blank" class="btn btn-info btn-sm"><i class="bi bi-twitter"></i> Twitter</a>
                    </div>
                </center>
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
    .center-buttons {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    /* Aqui você pode adicionar mais estilos para melhorar o visual */
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Define um temporizador para abrir o modal após 5 segundos (5000 milissegundos)
        setTimeout(function() {
            $('#popup').modal('show');
        }, 2000);
    });
</script>
