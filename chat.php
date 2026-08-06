<?php
header("Content-Type: application/json");

$mensagem = trim($_POST['mensagem'] ?? "");

if ($mensagem === "") {
    echo json_encode(["resposta" => "❌ Envie uma mensagem antes de perguntar. — BayMax"]);
    exit;
}

// A chave NUNCA fica no código. Ela vem de uma variável de ambiente
// configurada no servidor (Render, Railway, etc).
$api_key = getenv('GROQ_API_KEY');

if (!$api_key) {
    http_response_code(500);
    echo json_encode(["resposta" => "❌ Erro: variável de ambiente GROQ_API_KEY não configurada no servidor."]);
    exit;
}

$url = "https://api.groq.com/openai/v1/chat/completions";

$prompt_sistema = "
Você é BayMax, um assistente virtual de enfermagem pediátrica.
Explique conceitos de saúde infantil de forma simples, clara e prática, usando palavras fáceis, sem jargões, e termos técnicos apenas quando necessário.
Responda em até 2 frases, completas mas diretas, incluindo pelo menos 2 emojis por resposta.
Dê exemplos práticos de cuidados com crianças e bebês, mostrando como aplicar no dia a dia (banho, medir febre, organizar rotina alimentar, etc.).
Conecte o conceito a ações concretas, como “troque a fralda sempre que estiver molhada” ou “registre a febre no termômetro digital”.
Use um tom amigável, acolhedor e encorajador, com pitadas de humor e empatia, para que o leitor se sinta seguro cuidando da criança.
Adapte os exemplos conforme a idade da criança ou a situação apresentada pelo usuário.
Finalize todas as respostas com “— BayMax”, exceto quando perguntarem seu nome ou função, nesse caso apenas diga seu nome e para que serve em 1 frase.
";

$data = [
    "model" => "llama-3.3-70b-versatile",
    "messages" => [
        ["role" => "system", "content" => $prompt_sistema],
        ["role" => "user", "content" => $mensagem]
    ],
    "temperature" => 0.7,
    "max_tokens" => 300
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $api_key
]);
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

$resposta = $json["choices"][0]["message"]["content"]
    ?? ($json["error"]["message"] ?? null)
    ?? "❌ Erro ao obter resposta do BayMax.";

echo json_encode(["resposta" => $resposta]);
