document.addEventListener("DOMContentLoaded", function () {
    // Seleciona todos os botões de edição
    const botoesEditar = document.querySelectorAll('.btn-edit');

    botoesEditar.forEach(botao => {
        botao.addEventListener('click', function () {
            // Obtém os dados da linha da tabela
            const linha = this.closest('tr');
            const dados = {
                id: linha.querySelector('td:nth-child(1)').textContent, // ID do usuário (hidden)
                nome: linha.querySelector('td:nth-child(2)').textContent, // Nome Completo
                email: linha.querySelector('td:nth-child(3)').textContent, // Email
                setor: linha.querySelector('td:nth-child(4)').textContent, // Setor
                grupo: linha.querySelector('td:nth-child(5)').textContent, // Grupo
                perfil: linha.querySelector('td:nth-child(6)').textContent, // Perfil
                status: linha.querySelector('td:nth-child(7)').textContent // Status
            };

            // Preenche o formulário do modal com os dados
            document.getElementById('idUsuario').value = dados.id; // Campo hidden
            document.getElementById('nome_completo').value = dados.nome;
            document.getElementById('email').value = dados.email;
            document.getElementById('setor').value = dados.setor;

            // Preenche os campos select (Grupo, Perfil, Status)
            const grupoSelect = document.getElementById('grupo');
            const perfilSelect = document.getElementById('perfil');
            const statusSelect = document.getElementById('status');

            // Define o valor do campo Grupo
            for (let i = 0; i < grupoSelect.options.length; i++) {
                if (grupoSelect.options[i].text === dados.grupo) {
                    grupoSelect.selectedIndex = i;
                    break;
                }
            }

            // Define o valor do campo Perfil
            for (let i = 0; i < perfilSelect.options.length; i++) {
                if (perfilSelect.options[i].text === dados.perfil) {
                    perfilSelect.selectedIndex = i;
                    break;
                }
            }

            // Define o valor do campo Status
            for (let i = 0; i < statusSelect.options.length; i++) {
                if (statusSelect.options[i].text === dados.status) {
                    statusSelect.selectedIndex = i;
                    break;
                }
            }

            // Abre o modal
            const modal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
            modal.show();
        });
    });
});