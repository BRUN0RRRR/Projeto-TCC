document.addEventListener('DOMContentLoaded', function() {
    // Referências aos elementos do DOM
    const btnGerarPosicoes = document.getElementById('btnGerarPosicoes');
    const modalGerador = new bootstrap.Modal(document.getElementById('modalGerador'));
    const btnConfirmarGeracao = document.getElementById('btnConfirmarGeracao');
    const formGerador = document.getElementById('formGerador');
    const totalCalculado = document.getElementById('totalCalculado');
    
    // Campos de entrada para os intervalos
    const buildingInicio = document.getElementById('buildingInicio');
    const buildingFim = document.getElementById('buildingFim');
    const floorInicio = document.getElementById('floorInicio');
    const floorFim = document.getElementById('floorFim');
    const corridorInicio = document.getElementById('corridorInicio');
    const corridorFim = document.getElementById('corridorFim');
    const shelfInicio = document.getElementById('shelfInicio');
    const shelfFim = document.getElementById('shelfFim');
    const compartmentInicio = document.getElementById('compartmentInicio');
    const compartmentFim = document.getElementById('compartmentFim');
    
    // Array de todos os campos de entrada
    const camposEntrada = [
        buildingInicio, buildingFim,
        floorInicio, floorFim,
        corridorInicio, corridorFim,
        shelfInicio, shelfFim,
        compartmentInicio, compartmentFim
    ];
    
    // Função para extrair letra e número de um código no formato "LetraNúmero"
    function extrairLetraNumero(codigo) {
        const match = /([A-Za-z]+)(\d+)/.exec(codigo);
        if (match && match.length >= 3) {
            return {
                letra: match[1].toUpperCase(),
                numero: parseInt(match[2])
            };
        }
        return null;
    }
    
    // Função para calcular o número de itens em um intervalo
    function calcularTamanhoIntervalo(inicio, fim) {
        const inicioInfo = extrairLetraNumero(inicio);
        const fimInfo = extrairLetraNumero(fim);
        
        if (!inicioInfo || !fimInfo) {
            return 0;
        }
        
        const letraInicio = inicioInfo.letra;
        const numeroInicio = inicioInfo.numero;
        const letraFim = fimInfo.letra;
        const numeroFim = fimInfo.numero;
        
        // Se as letras são iguais, apenas calcule a diferença entre os números
        if (letraInicio === letraFim) {
            return numeroFim - numeroInicio + 1;
        } else {
            // Caso contrário, calcule todas as combinações possíveis
            let total = 0;
            const letraInicioAscii = letraInicio.charCodeAt(0);
            const letraFimAscii = letraFim.charCodeAt(0);
            
            for (let l = letraInicioAscii; l <= letraFimAscii; l++) {
                const letra = String.fromCharCode(l);
                
                // Para a primeira letra, comece do número inicial
                const numInicio = (letra === letraInicio) ? numeroInicio : 1;
                
                // Para a última letra, termine no número final
                const numFim = (letra === letraFim) ? numeroFim : 99;
                
                total += (numFim - numInicio + 1);
            }
            
            return total;
        }
    }
    
    // Função para calcular o total de posições
    function calcularTotalPosicoes() {
        const totalBuilding = calcularTamanhoIntervalo(buildingInicio.value, buildingFim.value);
        const totalFloor = calcularTamanhoIntervalo(floorInicio.value, floorFim.value);
        const totalCorridor = calcularTamanhoIntervalo(corridorInicio.value, corridorFim.value);
        const totalShelf = calcularTamanhoIntervalo(shelfInicio.value, shelfFim.value);
        const totalCompartment = calcularTamanhoIntervalo(compartmentInicio.value, compartmentFim.value);
        
        // Multiplicar todos os totais para obter o número total de combinações
        const total = totalBuilding * totalFloor * totalCorridor * totalShelf * totalCompartment;
        
        return total;
    }
    
    // Função para atualizar o contador de total de posições
    function atualizarTotalPosicoes() {
        const total = calcularTotalPosicoes();
        totalCalculado.textContent = total;
        
        // Destacar visualmente se o total for muito grande
        if (total > 5000) {
            totalCalculado.classList.add('warning');
        } else {
            totalCalculado.classList.remove('warning');
        }
    }
    
    // Função para validar o formato dos campos
    function validarCampo(campo) {
        const valor = campo.value.trim();
        const regex = /^[A-Za-z]+\d+$/;
        
        if (!regex.test(valor)) {
            campo.classList.add('is-invalid');
            return false;
        } else {
            campo.classList.remove('is-invalid');
            campo.classList.add('is-valid');
            return true;
        }
    }
    
    // Adicionar event listeners para os campos de entrada
    camposEntrada.forEach(campo => {
        campo.addEventListener('input', function() {
            validarCampo(this);
            atualizarTotalPosicoes();
        });
        
        campo.addEventListener('blur', function() {
            validarCampo(this);
        });
    });
    
    // Event listener para o botão de abrir o modal
    btnGerarPosicoes.addEventListener('click', function() {
        modalGerador.show();
        // Calcular o total inicial
        atualizarTotalPosicoes();
    });
    
    // Event listener para o botão de confirmar geração
    btnConfirmarGeracao.addEventListener('click', function() {
        let formValido = true;
        
        // Validar todos os campos antes de enviar
        camposEntrada.forEach(campo => {
            if (!validarCampo(campo)) {
                formValido = false;
            }
        });
        
        if (formValido) {
            // Verificar se o total não é excessivo
            const total = calcularTotalPosicoes();
            if (total > 10000) {
                if (!confirm(`Atenção: Você está prestes a gerar ${total} posições. Isso pode levar algum tempo e consumir muitos recursos. Deseja continuar?`)) {
                    return;
                }
            }
            
            // Enviar o formulário
            formGerador.submit();
        } else {
            alert('Por favor, corrija os campos destacados antes de continuar.');
        }
    });
    
    // Verificar se há mensagens de sessão para exibir
    if (typeof sessionMessages !== 'undefined' && sessionMessages) {
        if (sessionMessages.success) {
            showNotification(sessionMessages.success, 'success');
        }
        if (sessionMessages.error) {
            showNotification(sessionMessages.error, 'error');
        }
    }
    
    // Função para mostrar notificações
    function showNotification(message, type) {
        // Verificar se já existe uma notificação
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Criar elemento de notificação
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close"><i class="fas fa-times"></i></button>
        `;
        
        // Adicionar ao DOM
        document.body.appendChild(notification);
        
        // Adicionar event listener para fechar
        notification.querySelector('.notification-close').addEventListener('click', function() {
            notification.remove();
        });
        
        // Auto-fechar após 5 segundos
        setTimeout(() => {
            if (document.body.contains(notification)) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Adicionar estilos CSS para notificações
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 350px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideIn 0.3s ease-out;
        }
        
        .notification-content {
            padding: 15px;
            display: flex;
            align-items: center;
        }
        
        .notification i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .notification.success {
            border-left: 4px solid var(--success-color);
        }
        
        .notification.success i {
            color: var(--success-color);
        }
        
        .notification.error {
            border-left: 4px solid var(--danger-color);
        }
        
        .notification.error i {
            color: var(--danger-color);
        }
        
        .notification.info {
            border-left: 4px solid var(--primary-color);
        }
        
        .notification.info i {
            color: var(--primary-color);
        }
        
        .notification-close {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 15px;
            transition: color 0.3s;
        }
        
        .notification-close:hover {
            color: #333;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .highlight-total.warning {
            background-color: var(--warning-color);
            color: #333;
        }
        
        .is-invalid {
            border-color: var(--danger-color) !important;
        }
        
        .is-valid {
            border-color: var(--success-color) !important;
        }
    `;
    document.head.appendChild(style);
});
