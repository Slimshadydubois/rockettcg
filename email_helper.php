<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

function enviar_email_pedido($pdo, $pedido_id, $tipo_email) {
    // Buscar o pedido
    $stmt = $pdo->prepare("SELECT p.*, u.nome, u.email FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id = ?");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) return false;

    $para = $pedido['email'];
    $nome = $pedido['nome'];
    $status = $pedido['status'];
    $id_formatado = str_pad($pedido['id'], 5, '0', STR_PAD_LEFT);
    
    if ($tipo_email === 'novo_pedido') {
        $assunto = "Pedido #$id_formatado recebido com sucesso! - RocketTCG";
        $mensagem = "
        <html>
        <head><title>Pedido Recebido</title></head>
        <body style='font-family: sans-serif; line-height: 1.6; color: #333;'>
          <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
              <h2 style='color: #ff5252; border-bottom: 2px solid #ff5252; padding-bottom: 10px;'>Olá, $nome!</h2>
              <p>Recebemos o seu pedido <strong>#$id_formatado</strong> e o pagamento foi aprovado!</p>
              <p>Status atual: <strong>$status</strong>.</p>
              <p>Estamos muito felizes por ter escolhido a RocketTCG. Nossa equipe já está separando as suas cartas com muito cuidado. Você será notificado assim que ele for enviado para entrega.</p>
              <br>
              <p>Atenciosamente,<br><strong>Equipe RocketTCG</strong></p>
          </div>
        </body>
        </html>
        ";
    } else if ($tipo_email === 'atualizacao_status') {
        $assunto = "Atualizacao no seu Pedido #$id_formatado: $status - RocketTCG";
        $mensagem = "
        <html>
        <head><title>Atualização de Pedido</title></head>
        <body style='font-family: sans-serif; line-height: 1.6; color: #333;'>
          <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
              <h2 style='color: #ff5252; border-bottom: 2px solid #ff5252; padding-bottom: 10px;'>Olá, $nome!</h2>
              <p>O seu pedido <strong>#$id_formatado</strong> teve uma atualização!</p>
              <p style='font-size: 1.1em; background: #f9f9f9; padding: 15px; border-left: 4px solid #ff5252;'>
                  Novo status: <strong>$status</strong>
              </p>";
              
        if ($status === 'Enviado') {
            $mensagem .= "<p>Seu pedido já foi despachado aos correios/entregador e está a caminho!</p>";
        } else if ($status === 'Entregue') {
            $mensagem .= "<p>Seu pedido foi marcado como Entregue. Esperamos que você tire ótimas cartas nas suas batalhas!</p>";
        }

        $mensagem .= "
              <p>Você pode conferir o histórico completo na aba 'Minhas Compras' no nosso site.</p>
              <br>
              <p>Atenciosamente,<br><strong>Equipe RocketTCG</strong></p>
          </div>
        </body>
        </html>
        ";
    } else {
        return false;
    }

    $mail = new PHPMailer(true);
    
    try {
        // Se houver variáveis de ambiente configuradas no Railway, ele usa o SMTP.
        // Caso contrário, ele usa a função mail() normal do PHP.
        $smtp_host = getenv('SMTP_HOST');
        if ($smtp_host) {
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USER');
            $mail->Password   = getenv('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = getenv('SMTP_PORT') ?: 587;
        }

        $mail->setFrom('contato@rockettcg.com', 'RocketTCG');
        $mail->addAddress($para, $nome);
        $mail->CharSet = 'UTF-8';

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Ignora erro para não quebrar a página de sucesso/admin
        error_log(\"Message could not be sent. Mailer Error: {$mail->ErrorInfo}\");
        return false;
    }
}
?>
