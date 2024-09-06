<?php
// Conexão com o banco de dados
$host = 'localhost';
$dbname = 'nome_do_banco';
$user = 'usuario';
$pass = 'senha';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
    exit();
}

// Função para salvar feedback
function salvarFeedback($pdo, $feedback) {
    $stmt = $pdo->prepare("INSERT INTO feedback (feedback) VALUES (:feedback)");
    $stmt->bindParam(':feedback', $feedback);
    return $stmt->execute();
}

// Função para salvar resposta de enquete
function salvarRespostaEnquete($pdo, $enquete_id, $resposta) {
    $stmt = $pdo->prepare("INSERT INTO respostas_enquete (enquete_id, resposta) VALUES (:enquete_id, :resposta)");
    $stmt->bindParam(':enquete_id', $enquete_id);
    $stmt->bindParam(':resposta', $resposta);
    return $stmt->execute();
}

// Função para criar uma nova enquete
function criarEnquete($pdo, $pergunta) {
    $stmt = $pdo->prepare("INSERT INTO enquetes (pergunta) VALUES (:pergunta)");
    $stmt->bindParam(':pergunta', $pergunta);
    return $stmt->execute();
}

// Verifica o envio do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tipo']) && $_POST['tipo'] == 'feedback') {
        $feedback = $_POST['feedback'];
        if (salvarFeedback($pdo, $feedback)) {
            echo "Feedback enviado com sucesso!";
        } else {
            echo "Erro ao enviar feedback.";
        }
    } elseif (isset($_POST['tipo']) && $_POST['tipo'] == 'enquete') {
        $pergunta = $_POST['pergunta'];
        if (criarEnquete($pdo, $pergunta)) {
            echo "Enquete criada com sucesso!";
        } else {
            echo "Erro ao criar enquete.";
        }
    } elseif (isset($_POST['resposta']) && isset($_POST['enquete_id'])) {
        $enquete_id = $_POST['enquete_id'];
        $resposta = $_POST['resposta'];
        if (salvarRespostaEnquete($pdo, $enquete_id, $resposta)) {
            echo "Resposta enviada com sucesso!";
        } else {
            echo "Erro ao enviar resposta.";
        }
    }
}

// Exibir todas as enquetes e permitir respostas
function exibirEnquetes($pdo) {
    $stmt = $pdo->query("SELECT * FROM enquetes");
    $enquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($enquetes as $enquete) {
        echo "<h3>" . $enquete['pergunta'] . "</h3>";
        echo "<form action='' method='POST'>
                <input type='hidden' name='enquete_id' value='" . $enquete['id'] . "'>
                <input type='text' name='resposta' placeholder='Sua resposta'>
                <button type='submit'>Enviar Resposta</button>
              </form>";

        // Mostrar respostas da enquete
        $stmtRespostas = $pdo->prepare("SELECT resposta FROM respostas_enquete WHERE enquete_id = :enquete_id");
        $stmtRespostas->bindParam(':enquete_id', $enquete['id']);
        $stmtRespostas->execute();
        $respostas = $stmtRespostas->fetchAll(PDO::FETCH_ASSOC);

        if ($respostas) {
            echo "<ul>";
            foreach ($respostas as $resposta) {
                echo "<li>" . $resposta['resposta'] . "</li>";
            }
            echo "</ul>";
        }
    }
}

// Exibir feedbacks enviados
function exibirFeedbacks($pdo) {
    $stmt = $pdo->query("SELECT * FROM feedback");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h2>Feedbacks Recebidos</h2>";
    echo "<ul>";
    foreach ($feedbacks as $feedback) {
        echo "<li>" . $feedback['feedback'] . " - Enviado em: " . $feedback['data_envio'] . "</li>";
    }
    echo "</ul>";
}
?>
