function mostrarMensagem(texto,classe){
    const chat = document.getElementById('chat');

    const p = document.createElement("p");

    p.className = classe;

    p.textContent = texto;

    chat.appendChild(p);

    chat.scrollTop = chat.scrollHeight; 

}

function limparChat(){
        document.getElementById("chat").innerHTML = "";
    }

function enviarMensagem(){
    const msg = document.getElementById('CampoMensagem').value;
    

    if(msg.trim() === "") return;

    mostrarMensagem("" + msg,"mensagem-usuario");

    document.getElementById('CampoMensagem').value = "";

    fetch("gemini.php",{
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "mensagem=" + encodeURIComponent(msg)
    })

    .then(res => res.json())
    .then(data => {
        mostrarMensagem("" + data.resposta,"mensagem-bot");
    })

    .catch(() => {
        mostrarMensagem("BayMax: ❌ Ocorreu um erro ao processar sua mensagem.","mensagem-bot");
    });

}

let tamanhoFonte = 16
function aumentarFonte(){
    tamanhoFonte += 2;
    document.getElementById("chat").style.fontSize = tamanhoFonte + "px";
}

function diminuirFonte(){
    tamanhoFonte -= 2;
    if (tamanhoFonte < 10) tamanhoFonte = 10;
    document.getElementById("chat").style.fontSize = tamanhoFonte + "px";
}

