<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../../../../databases/conexao.php";

// Captura os dados do formulário
$code_deposito = isset($_POST['code_deposito']) ? $_POST['code_deposito'] : " ";
$predioInicio = isset($_POST['predioInicio']) ? $_POST['predioInicio'] : '';
$predioFim = isset($_POST['predioFim']) ? $_POST['predioFim'] : '';
$andarInicio = isset($_POST['andarInicio']) ? $_POST['andarInicio'] : '';
$andarFim = isset($_POST['andarFim']) ? $_POST['andarFim'] : '';
$corredorInicio = isset($_POST['corredorInicio']) ? $_POST['corredorInicio'] : '';
$corredorFim = isset($_POST['corredorFim']) ? $_POST['corredorFim'] : '';
$prateleiraInicio = isset($_POST['prateleiraInicio']) ? $_POST['prateleiraInicio'] : '';
$prateleiraFim = isset($_POST['prateleiraFim']) ? $_POST['prateleiraFim'] : '';  // Correção aqui
$compartmentoInicio = isset($_POST['compartimentoInicio']) ? $_POST['compartimentoInicio'] : '';
$compartimentoFim = isset($_POST['compartimentoFim']) ? $_POST['compartimentoFim'] : '';  // Correção aqui
$status = "Ativo";
$user_criado = $_SESSION['nome_com'] ?? 'Desconhecido'; // Pegando da sessão ou definindo um valor padrão;




echo "code_deposito: " . $code_deposito . "<br>";
echo "predioInicio: " . $predioInicio . "<br>";
echo "predioFim: " . $predioFim . "<br>";
echo " andarInicio:  " . $andarInicio . "<br>";
echo "andarFim: " . $andarFim . "<br>";
echo "corredorInicio: " . $corredorInicio . "<br>";
echo "corredorFim: " . $corredorFim . "<br>";
echo "prateleiraInicio: " . $prateleiraInicio . "<br>";
echo "nomprateleiraFime: " . $prateleiraFim . "<br>";
echo "compartmentInicio: " . $compartmentoInicio . "<br>";
echo "compartimentoFim: " . $compartimentoFim . "<br>";
echo "user_criado: " . $user_criado . "<br>";
echo "<br>";

// Função para gerar as combinações de posições
function gerarPosicoes($inicio, $fim)
{
    $letraInicio = substr($inicio, 0, 1);
    $numeroInicio = (int) substr($inicio, 1);
    $letraFim = substr($fim, 0, 1);
    $numeroFim = (int) substr($fim, 1);

    // Verificar se o início está depois do fim
    if (
        ord($letraInicio) > ord($letraFim) ||
        ($letraInicio == $letraFim && $numeroInicio > $numeroFim)
    ) {
        die("Erro: O início deve ser menor ou igual ao fim. Você enviou de $inicio para $fim.");

    }

    $posicoes = [];

    if ($letraInicio == $letraFim) {
        for ($i = $numeroInicio; $i <= $numeroFim; $i++) {
            $posicoes[] = $letraInicio . $i;
        }
    } else {
        for ($l = ord($letraInicio); $l <= ord($letraFim); $l++) {
            $letraAtual = chr($l);
            $start = ($l == ord($letraInicio)) ? $numeroInicio : 1;
            $end = ($l == ord($letraFim)) ? $numeroFim : 99; // ou o máximo que quiser
            for ($i = $start; $i <= $end; $i++) {
                $posicoes[] = $letraAtual . $i;
            }
        }
    }

    return $posicoes;
}

// Gerar as posições para cada variável
$predios = gerarPosicoes($predioInicio, $predioFim);
$andares = gerarPosicoes($andarInicio, $andarFim);
$corredores = gerarPosicoes($corredorInicio, $corredorFim);
$prateleiras = gerarPosicoes($prateleiraInicio, $prateleiraFim);
$compartimentos = gerarPosicoes($compartmentoInicio, $compartimentoFim);

$stmt = $pdo->prepare("INSERT INTO localizacoes (
    id_deposito,
    predio,
    andar_setor,
    corredor,
    prateleira,
    compartimento,
    nome,
    status,
    user_criador
) VALUES (
    :id_deposito,
    :predio,
    :andar_setor,
    :corredor,
    :prateleira,
    :compartimento,
    'Disponivel',
    'Ativo',
    :user_criador
)");

// Exibir as combinações de posições
foreach ($predios as $predio) {
    foreach ($andares as $andar) {
        foreach ($corredores as $corredor) {
            foreach ($prateleiras as $prateleira) {
                foreach ($compartimentos as $compartimento) {
                    echo "Depósito: $code_deposito, Predio: $predio, Andar: $andar, Corredor: $corredor, Prateleira: $prateleira, Compartimento: $compartimento<br>";

                    $stmt->bindValue(":id_deposito", $code_deposito, PDO::PARAM_STR);
                    $stmt->bindValue(":predio", $predio, PDO::PARAM_STR);
                    $stmt->bindValue(":andar_setor", $andar, PDO::PARAM_STR);
                    $stmt->bindValue(":corredor", $corredor, PDO::PARAM_STR);
                    $stmt->bindValue(":prateleira", $prateleira, PDO::PARAM_STR);
                    $stmt->bindValue(":compartimento", $compartimento, PDO::PARAM_STR);
                    $stmt->bindValue(":user_criador", $user_criado, PDO::PARAM_STR);

                    $stmt->execute();
                    echo "Inserções concluídas com sucesso!";
                    $_SESSION['aviso'] = [
                        'icon' => "success",
                        'title' => "Localização Criada!",
                        'text' => "A Localização foi criada com sucesso!"
                    ];
                    header("Location: ../cadastro_locatizacao.php");

                }
            }
        }
    }
}


exit;
















/* Consulta para verificar se o login já existe
$stmt = $pdo->prepare("SELECT * FROM custo WHERE codigo = :codigo");
$stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o usuário já existe
if ($result) {
    if ($result['codigo'] === $codigo) {
        //  echo "Usuario já existente!";
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Código já utilizado!",
            'text' => "Já existe uma categoria com esse código. Verifique e tente novamente."
        ];
        header("Location: ../centro_custo.php");
        exit;
    } else {
        //echo "Usuário poderá ser cadastrado.";
    }

} else {
    //echo "Não retornou nada.";
    $stmt = $pdo->prepare("INSERT INTO custo (id_custo, codigo, nome, descricao, status) VALUES (NULL, :codigo, :nome,  :descricao, 'Ativo')");

    $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
    $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
    $stmt->bindParam(":descricao", $descricao, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso!";
        $_SESSION['aviso'] = [
            'icon' => "success",
            'title' => "Categoria Cadastrada",
            'text' => "A categoria foi cadastrada com sucesso!"
        ];

        header("Location: ../centro_custo.php");
    } else {
        echo "Erro ao cadastrar usuário.";
    }
}
*/
?>