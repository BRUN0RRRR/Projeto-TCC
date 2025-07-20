<?php
ob_start();
// Database configuration
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../databases/conexao.php";
include "../../../includes/sidebar.php";
include "../../../api/alerta/alert_erro.php";

$moduloNecessario = "baixa_produtos";
verificarAcessoPagina($moduloNecessario);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    include "../../../../databases/conexao.php";
    try {
        $sql2 = "SELECT id_custo FROM custo ORDER BY id_custo DESC LIMIT 1";
        $stmt2 = $pdo->query($sql2);
        $ultimoCodigo = $stmt2->fetchColumn();

        $proximoCodigo = $ultimoCodigo
            ? str_pad((int) $ultimoCodigo + 1, 6, '000000', STR_PAD_LEFT)
            : '000001';

        header('Content-Type: application/json');
        echo json_encode(['proximoCodigo' => $proximoCodigo]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao gerar código: ' . $e->getMessage()]);
        exit;
    }
}

try {
    // Busca todos os itens com estoque disponível e preço médio
    $sql = "SELECT * FROM custo;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $custo = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $sql = "SELECT COUNT(*) AS total FROM custo;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $total_custo = $stmt->fetch(PDO::FETCH_ASSOC);



} catch (PDOException $e) {
    die("Erro ao executar a consulta: " . $e->getMessage());
}


ob_end_flush();

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Infostock - Gestão categoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/pagina_centro_custo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

</head>

<body>

    <div class="container">
        <div class="header">
            <div class="header-actions">
                <h4>Gerenciamento de Centro de Custo</h4>
            </div>
            <button class="btn btn-primary " id="btn_cad" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="fas fa-plus me-1"></i> Nova Custo
            </button>

        </div>


        <div class="card shadow-sm">

            <div class="card-body">
                <div class="mb-3">
                    <span><i class="fas fa-tag me-1"></i> Total de Custo: <?= $total_custo['total']; ?></span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>CÓDIGO</th>
                                <th>NOME</th>
                                <th>DESCRIÇÃO</th>
                                <th>STATUS</th>
                                <th>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($custo as $custos): ?>
                                <tr data-produto-id="<?= $custos['id_custo']; ?>">
                                    <td><?= $custos['codigo']; ?></td>
                                    <td><?= $custos['nome']; ?></td>
                                    <td><?= $custos['descricao']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($custos['status']) ?>">
                                            <?= htmlspecialchars($custos['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-edit" data-bs-toggle="modal"
                                            data-bs-target="#editCategoryModal" data-id="<?= $custos['id_custo']; ?>"
                                            data-nome="<?= htmlspecialchars($custos['nome'], ENT_QUOTES); ?>"
                                            data-codigo="<?= htmlspecialchars($custos['codigo'], ENT_QUOTES); ?>"
                                            data-descricao="<?= htmlspecialchars($custos['descricao'], ENT_QUOTES); ?>"
                                            data-status="<?= $custos['status']; ?>">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </button>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Category Modal -->
        <div class="modal fade" id="categoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar Centro de Custo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="createCategoryForm" action="config/config_criar_custo.php" method="POST">
                        <!-- <input type="hidden" id="edit_id" name="id_categoria">-->
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="code" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="name" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="description" name="descricao" rows="3"></textarea>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div class="modal fade" id="editCategoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Centro de Custo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>
                    <form id="editCategoryForm" action="config/config_edite_custo.php" method="POST">
                        <input type="hidden" id="edit_id" name="id_custo">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_code" class="form-label">Código</label>
                                <input type="text" class="form-control" id="edit_code" name="codigo" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="edit_name" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="edit_description" name="descricao"
                                    rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="form-label"></i>Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Atualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Category Modal 
        <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir esta categoria?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="confirmDelete" class="btn btn-danger">Excluir</button>
                    </div>
                </div>
            </div>
        </div>-->

    </div>

    <script>
        document.getElementById('btn_cad').addEventListener('click', carregarProximoCodigo);

        // Edit category button click
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nome = this.getAttribute('data-nome');
                const codigo = this.getAttribute('data-codigo');
                const descricao = this.getAttribute('data-descricao');
                const status = this.getAttribute('data-status');

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_code').value = codigo;
                document.getElementById('edit_name').value = nome;
                document.getElementById('edit_description').value = descricao;
                document.getElementById('status').value = status;
            });
        });
        carregarProximoCodigo();
        async function carregarProximoCodigo() {
            try {
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();

                if (data.error) {
                    console.error(data.error);
                    return;
                }

                document.getElementById('codigo').value = data.proximoCodigo;
                document.getElementById('btn_cad').addEventListener('click', carregarProximoCodigo);


            } catch (error) {
                console.error('Erro ao carregar código:', error);
            }
        }

        // Delete category
        let categoryIdToDelete;

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function () {
                categoryIdToDelete = this.getAttribute('data-id');
            });
        });

        document.getElementById('confirmDelete').addEventListener('click', function () {
            // Make AJAX request to delete category
            fetch(`api/category/delete.php?id=${categoryIdToDelete}`, {
                method: 'DELETE'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.message === 'Category was deleted.') {
                        // Reload page or remove row from table
                        location.reload();
                    }
                });

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCategoryModal'));
            modal.hide();
        });
    </script>
</body>

</html>