function mostrarMensagem(texto, classe) {
    const chat = document.getElementById('chat');
    const p = document.createElement("p");
    p.className = classe;
    p.textContent = texto;
    chat.appendChild(p);
    chat.scrollTop = chat.scrollHeight;
    return p;
}

function limparChat() {
    document.getElementById("chat").innerHTML = "";
}

function enviarMensagem() {
    const campo = document.getElementById('CampoMensagem');
    const msg = campo.value;

    if (msg.trim() === "") return;

    mostrarMensagem(msg, "mensagem-usuario");
    campo.value = "";

    const carregando = mostrarMensagem("BayMax está digitando...", "mensagem-bot mensagem-carregando");

    fetch("chat.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "mensagem=" + encodeURIComponent(msg)
    })
        .then(res => res.json())
        .then(data => {
            carregando.remove();
            mostrarMensagem(data.resposta, "mensagem-bot");
        })
        .catch(() => {
            carregando.remove();
            mostrarMensagem("BayMax: ❌ Ocorreu um erro ao processar sua mensagem.", "mensagem-bot");
        });
}

document.getElementById('CampoMensagem').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        enviarMensagem();
    }
});

let tamanhoFonte = 16;
function aumentarFonte() {
    tamanhoFonte += 2;
    document.getElementById("chat").style.fontSize = tamanhoFonte + "px";
}

function diminuirFonte() {
    tamanhoFonte -= 2;
    if (tamanhoFonte < 10) tamanhoFonte = 10;
    document.getElementById("chat").style.fontSize = tamanhoFonte + "px";
}
