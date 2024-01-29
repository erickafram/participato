// scripts.js


//COMPARTILHAMENTO PARA REDES SOCIAIS
document.addEventListener("DOMContentLoaded", function() {
    // Mais código já existente aqui

    // Botão de compartilhamento do Facebook
    document.getElementById("shareFacebook").addEventListener("click", function() {
        const url = window.location.href;
        const title = document.querySelector('.petition-title').textContent;
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(title)}`, "_blank");
    });

    // Botão de compartilhamento do Twitter
    document.getElementById("shareTwitter").addEventListener("click", function() {
        const url = window.location.href;
        const title = document.querySelector('.petition-title').textContent;
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`, "_blank");
    });

    // Botão de compartilhamento do WhatsApp
    document.getElementById("shareWhatsApp").addEventListener("click", function() {
        const url = window.location.href;
        const title = document.querySelector('.petition-title').textContent;
        window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(title + " " + url)}`, "_blank");
    });
});
