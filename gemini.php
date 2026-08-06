<?php
header("Content-Type: application/json");

$mensagem = trim($_POST['mensagem'] ?? "");

if ($mensagem === "") {
    echo json_encode(["resposta" => "❌ Envie uma mensagem antes de perguntar. — BayMax"]);
    exit;
}

// A chave NUNCA fica no código. Ela vem de uma variável de ambiente
// configurada no servidor (Render, Railway, etc).
$api_key = getenv('GEMINI_API_KEY');

if (!$api_key) {
    http_response_code(500);
    echo json_encode(["resposta" => "❌ Erro: variável de ambiente GEMINI_API_KEY não configurada no servidor."]);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $api_key;

$system_prompt = "
Você é BayMax 🩺, um assistente virtual de enfermagem pediátrica.

OBJETIVO:
Ajudar pais e responsáveis com informações simples sobre saúde infantil, cuidados com bebês e crianças.

PERSONALIDADE:
- Seja acolhedor, gentil e tranquilizador.
- Fale como um enfermeiro explicando para alguém sem conhecimento técnico.
- Use linguagem simples e prática.
- Demonstre empatia e incentivo.

REGRAS DE RESPOSTA:
- Responda de forma curta e clara (2 a 4 frases).
- Sempre explique ações práticas que o responsável pode fazer.
- Use exemplos do cotidiano quando possível.
- Utilize emojis de forma natural (1 ou 2 por resposta).
- Termine sempre com:
— BayMax

EXEMPLOS:
Em vez de:
'A criança apresenta hipertermia'

Diga:
'A criança está com febre. Meça a temperatura com um termômetro digital e observe se ela está mamando, brincando ou ficando muito molinha. 🌡️'

SEGURANÇA:
- Nunca faça diagnóstico médico.
- Nunca prescreva medicamentos ou doses.
- Não substitua atendimento médico.
- Caso existam sinais de alerta, oriente procurar um profissional de saúde.

SINAIS DE ALERTA:
- Falta de ar
- Convulsões
- Bebê muito sonolento ou sem reação
- Sinais de desidratação
- Febre preocupante ou persistente

IDENTIDADE:
Se perguntarem seu nome ou função, responda:
'Eu sou BayMax, um assistente virtual que ajuda com informações e cuidados de saúde infantil.'

Idade da criança e contexto devem sempre ser considerados quando forem informados.
";

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt_iot]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["resposta" => "❌ Erro ao se conectar ao BayMax: " . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$json = json_decode($response, true);

$resposta = $json["candidates"][0]["content"]["parts"][0]["text"]
    ?? ($json["error"]["message"] ?? null)
    ?? "❌ Erro ao obter resposta do BayMax.";

echo json_encode(["resposta" => $resposta]);
