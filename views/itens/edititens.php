<?php
require_once '../../App/auth.php';
require_once '../../layout/script.php';
require_once '../../App/Models/produtos.class.php';
require_once '../../App/Models/fabricante.class.php';
require_once '../../App/Models/itens.class.php';

if (!isset($_GET['q'])) {
    header('Location: ./');
    exit;
}

$resp = $itens->editItens($_GET['q']);
$produtoVinculado = $produtos->getProdutoInfo($resp['Itens']['CodRefProduto']);

echo $head;
echo $header;
echo $aside;
?>

<style>
/* Estilos personalizados */
.custom-form-control {
    border-radius: 20px;
    border: 1px solid #ddd;
    padding: 8px 15px;
    transition: all 0.3s ease;
    box-shadow: none;
}

.custom-form-control:focus {
    border-color: #3c8dbc;
    box-shadow: 0 0 8px rgba(60, 141, 188, 0.2);
}

.custom-box {
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.custom-box:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.info-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 20px;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #3c8dbc;
}

.custom-btn {
    border-radius: 20px;
    padding: 8px 20px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    font-weight: bold;
    letter-spacing: 0.5px;
    color: #fff;
}

.custom-btn-success {
    background: #28a745;
    border: none;
    color: #fff;
}

.custom-btn-success:hover {
    background: #218838;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.custom-btn-danger {
    background: #dc3545;
    border: none;
    color: #fff;
}

.custom-btn-danger:hover {
    background: #c82333;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.form-section {
    padding: 20px;
    border-radius: 10px;
    background-color: #fff;
    margin-bottom: 20px;
    overflow: visible !important;
}

.select2-container--default .select2-selection--single {
    border-radius: 20px;
    height: 38px;
    border: 1px solid #ddd;
}

.image-preview {
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 10px;
    text-align: center;
}

.image-preview img {
    max-height: 200px;
    width: 100%;
    object-fit: contain;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 1;
    transform: scale(1);
}

.image-preview.loading img {
    opacity: 0;
    transform: scale(0.95);
}

.floating-label {
    position: relative;
    margin-bottom: 20px;
    z-index: 1;
}

.floating-label label {
    position: absolute;
    top: -10px;
    left: 10px;
    background: white;
    padding: 0 5px;
    font-size: 12px;
    color: #666;
    transition: all 0.3s ease;
}

.form-group input:focus + label {
    color: #3c8dbc;
    font-weight: 600;
}

/* Animação de carregamento para o botão */
.btn-loading {
    position: relative;
    pointer-events: none;
}

.btn-loading:after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-top: -8px;
    margin-left: -8px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Adicione estes estilos na seção de <style> */
.select2-container {
    z-index: 9999;
    width: 100% !important;
}

.select2-container--default .select2-selection--single {
    border-radius: 20px;
    height: 38px;
    border: 1px solid #ddd;
    width: 100%;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 15px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.select2-dropdown {
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.select2-search--dropdown .select2-search__field {
    border: 1px solid #ddd;
    border-radius: 15px;
    padding: 5px 10px;
}

.select2-results__option {
    padding: 8px 15px;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3c8dbc;
}

/* Correção para o problema de corte */
.form-section {
    overflow: visible !important;
}

.box {
    overflow: visible !important;
}

.content-wrapper {
    overflow: visible !important;
}

/* Ajuste para garantir que os dropdowns apareçam sobre outros elementos */
.floating-label {
    position: relative;
    z-index: 1;
}

/* Adicione estes estilos na seção <style> */
.custom-file-input {
    position: relative;
    display: inline-block;
    width: 100%;
}

.custom-file-input input[type="file"] {
    display: none;
}

.custom-file-label {
    display: block;
    padding: 10px 15px;
    background: #f8f9fa;
    border: 2px dashed #ddd;
    border-radius: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.custom-file-label:hover {
    border-color: #3c8dbc;
    background: #fff;
}

.custom-file-label i {
    margin-right: 8px;
    font-size: 18px;
}

.custom-file-label span {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #666;
}

.custom-file-selected {
    margin-top: 8px;
    display: none;
    padding: 5px 10px;
    background: #e8f5e9;
    border-radius: 10px;
    font-size: 12px;
    color: #2e7d32;
}

#preview-image {
    transition: opacity 0.3s ease;
    max-height: 200px;
    object-fit: contain;
    width: 100%;
}

.image-preview {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 10px;
    text-align: center;
}

/* Adicione estes estilos no bloco <style> */
.calculator-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-top: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.calculator-box h5 {
    color: #3c8dbc;
    margin-bottom: 15px;
    font-weight: 600;
}

.calculator-input {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 15px;
    padding: 8px 15px;
    margin-bottom: 10px;
    width: 100%;
    transition: all 0.3s ease;
}

.calculator-input:focus {
    border-color: #3c8dbc;
    box-shadow: 0 0 8px rgba(60, 141, 188, 0.2);
}

.calculator-result {
    background: #e8f5e9;
    padding: 10px 15px;
    border-radius: 10px;
    margin-top: 15px;
    text-align: center;
    font-size: 18px;
    font-weight: 600;
    color: #2e7d32;
    display: none;
}

.margin-result {
    background: #e3f2fd;
    padding: 8px 12px;
    border-radius: 8px;
    margin-top: 10px;
    font-size: 15px;
    color: #1565c0;
    display: none;
}

.margin-positive {
    color: #2e7d32;
    background: #e8f5e9;
}

.margin-negative {
    color: #c62828;
    background: #ffebee;
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Editar Item
            <small>Atualizar informações do item</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="../"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="./"><i class="fa fa-cubes"></i> Itens</a></li>
            <li class="active">Editar Item</li>
        </ol>
    </section>

    <section class="content">
        <?php if (isset($_GET['alert'])): ?>
            <div class="alert alert-<?= ($_GET['alert'] == 4) ? 'warning' : 'info' ?> alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-<?= ($_GET['alert'] == 4) ? 'exclamation-triangle' : 'info' ?>"></i> Atenção!</h4>
                <?php
                switch ($_GET['alert']) {
                    case 4:
                        echo 'O código de barras informado já está em uso por outro produto.';
                        break;
                    default:
                        echo 'Verifique os dados antes de salvar.';
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="box custom-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Dados do Item</h3>
                    </div>

                    <form role="form" id="editItemForm" enctype="multipart/form-data" action="../../App/Database/insertitens.php" method="POST">
                        <div class="box-body">
                            <div class="form-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="floating-label">
                                            <select class="form-control custom-form-control select2" name="codProduto" required>
                                                <?php if ($produtoVinculado): ?>
                                                    <option value="<?= htmlspecialchars($produtoVinculado['CodRefProduto']) ?>" selected>
                                                        <?= htmlspecialchars($produtoVinculado['NomeProduto']) ?>
                                                    </option>
                                                <?php endif; ?>
                                                <?php $produtos->listProdutos($resp['Itens']['CodRefProduto']); ?>
                                            </select>
                                            <label>Produto</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="floating-label">
                                            <select class="form-control custom-form-control select2" name="idFabricante" required>
                                                <?php $fabricante->listfabricante($resp['Itens']['idFabricante']); ?>
                                            </select>
                                            <label>Fabricante</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="QuantItens">Quantidade em Estoque</label>
                                            <input type="number" name="QuantItens" class="form-control custom-form-control" min="0" step="1" 
                                                   value="<?= htmlspecialchars($resp['Itens']['QuantItens']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ValCompItens">Valor de Compra (R$)</label>
                                            <input type="number" name="ValCompItens" class="form-control custom-form-control" min="0" step="0.01" 
                                                   value="<?= htmlspecialchars($resp['Itens']['ValCompItens']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ValVendItens">Valor de Venda (R$)</label>
                                            <input type="number" name="ValVendItens" class="form-control custom-form-control" min="0" step="0.01" 
                                                   value="<?= htmlspecialchars($resp['Itens']['ValVendItens']) ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="DataCompraItens">Data da Compra</label>
                                            <input type="date" name="DataCompraItens" class="form-control custom-form-control" 
                                                   value="<?= htmlspecialchars($resp['Itens']['DataCompraItens']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="DataVenci_Itens">Data de Vencimento</label>
                                            <input type="date" name="DataVenci_Itens" class="form-control custom-form-control" 
                                                   value="<?= htmlspecialchars($resp['Itens']['DataVenci_Itens']) ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="codigoBarras">Código de Barras</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control custom-form-control" id="codigoBarras" name="codigoBarras" 
                                               value="<?= htmlspecialchars($resp['Itens']['CodigoBarras'] ?? '') ?>"
                                               placeholder="Digite ou escaneie o código de barras">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-info" onclick="testarCodigoBarras()">
                                                <i class="fa fa-barcode"></i> Testar Código
                                            </button>
                                        </span>
                                    </div>
                                    <small class="help-block">Deixe em branco se não houver código de barras</small>
                                </div>

                                <div class="form-group">
                                    <label for="arquivo">Imagem do Produto</label>
                                    <div class="row">
                                        <div class="col-md-3 image-preview">
                                            <img src="../<?= htmlspecialchars($resp['Itens']['Image']) ?>" 
                                                 class="img-responsive img-thumbnail" id="preview-image" alt="Imagem atual">
                                        </div>
                                        <div class="col-md-9">
                                            <div class="custom-file-input">
                                                <input type="file" name="arquivo" id="arquivo" class="form-control" accept="image/*" onchange="previewImage(this)">
                                                <label for="arquivo" class="custom-file-label">
                                                    <i class="fa fa-cloud-upload"></i>
                                                    Escolher nova imagem
                                                    <span>Arraste uma imagem ou clique para selecionar</span>
                                                </label>
                                                <div class="custom-file-selected">
                                                    <i class="fa fa-check"></i> <span class="selected-file-name"></span>
                                                </div>
                                            </div>
                                            <small class="help-block">Selecione apenas se desejar alterar a imagem atual</small>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="valor" value="<?= htmlspecialchars($resp['Itens']['Image']) ?>">
                                <input type="hidden" name="iduser" value="<?= htmlspecialchars($idUsuario) ?>">
                                <input type="hidden" name="idItens" value="<?= htmlspecialchars($resp['Itens']['idItens']) ?>">
                            </div>
                        </div>

                        <div class="box-footer text-center">
                            <button type="submit" name="upload" class="btn custom-btn custom-btn-success">
                                <i class="fa fa-save"></i> Salvar Alterações
                            </button>
                            <a class="btn custom-btn custom-btn-danger" href="../../views/prod">
                                <i class="fa fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box custom-box">
                    <h4 class="text-primary"><i class="fa fa-info-circle"></i> Informações do Item</h4>
                    <hr>
                    <div class="stat-group">
                        <label>Último Registro</label>
                        <div class="stat-value">
                            <?= isset($resp['Itens']['DataRegistro']) ? 
                                date('d/m/Y H:i', strtotime($resp['Itens']['DataRegistro'])) : 
                                'Não disponível' ?>
                        </div>
                    </div>
                    <div class="stat-group">
                        <label>Quantidade Vendida</label>
                        <div class="stat-value">
                            <?= number_format($resp['Itens']['QuantItensVend'] ?? 0, 0, ',', '.') ?> unidades
                        </div>
                    </div>
                    <div class="stat-group">
                        <label>Margem de Lucro</label>
                        <div class="stat-value">
                            <?php 
                            if (isset($resp['Itens']['ValVendItens']) && isset($resp['Itens']['ValCompItens']) && $resp['Itens']['ValCompItens'] > 0) {
                                $margem = (($resp['Itens']['ValVendItens'] - $resp['Itens']['ValCompItens']) / $resp['Itens']['ValCompItens']) * 100;
                                echo number_format($margem, 2, ',', '.') . '%';
                            } else {
                                echo 'Não calculável';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="calculator-box">
                        <h5><i class="fa fa-calculator"></i> Calculadora de Valores</h5>
                        <div class="form-group">
                            <label>Peso Total do Produto (g)</label>
                            <input type="number" class="calculator-input" id="pesoTotal" 
                                   value="1000" placeholder="Ex: 1000">
                        </div>
                        <div class="form-group">
                            <label>Valor Total de Compra (R$)</label>
                            <input type="number" class="calculator-input" id="valorTotal" 
                                   value="<?= htmlspecialchars($resp['Itens']['ValCompItens']) ?>" 
                                   step="0.01" placeholder="Ex: 25.00">
                        </div>
                        <div class="form-group">
                            <label>Quantidade Desejada (g)</label>
                            <input type="number" class="calculator-input" id="quantidadeDesejada" 
                                   placeholder="Ex: 250">
                        </div>
                        <div class="form-group">
                            <label>Valor de Venda Desejado (R$)</label>
                            <input type="number" class="calculator-input" id="valorVendaDesejado" 
                                   step="0.01" placeholder="Ex: 35.00">
                        </div>
                        <button type="button" class="btn btn-primary btn-block custom-btn" 
                                onclick="calcularValor()">
                            Calcular Valor
                        </button>
                        <div id="resultadoCalculo" class="calculator-result"></div>
                        <div id="resultadoMargem" class="margin-result"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php 
echo $footer;
echo $javascript;
?>

<script>
$(document).ready(function() {
    // Inicializa Select2
    $('.select2').select2();

    // Validação do formulário
    $('#editItemForm').validate({
        rules: {
            QuantItens: {
                required: true,
                min: 0
            },
            ValCompItens: {
                required: true,
                min: 0
            },
            ValVendItens: {
                required: true,
                min: 0
            },
            DataCompraItens: 'required'
        },
        messages: {
            QuantItens: {
                required: 'Por favor, informe a quantidade',
                min: 'A quantidade deve ser maior ou igual a zero'
            },
            ValCompItens: {
                required: 'Por favor, informe o valor de compra',
                min: 'O valor deve ser maior ou igual a zero'
            },
            ValVendItens: {
                required: 'Por favor, informe o valor de venda',
                min: 'O valor deve ser maior ou igual a zero'
            },
            DataCompraItens: 'Por favor, informe a data da compra'
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('help-block');
            element.closest('.form-group').append(error);
        },
        highlight: function (element) {
            $(element).closest('.form-group').addClass('has-error');
        },
        unhighlight: function (element) {
            $(element).closest('.form-group').removeClass('has-error');
        }
    });
});

function testarCodigoBarras() {
    const codigoBarras = document.getElementById('codigoBarras').value.trim();
    if (!codigoBarras) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Digite um código de barras para testar'
        });
        return;
    }
    
    fetch('../../App/Database/testarCodigoBarras.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `codigo=${codigoBarras}`
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Sucesso!' : 'Atenção',
            text: data.message || 'Código de barras já está em uso em outro item'
        });
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao testar código de barras'
        });
    });
}

// Validação do campo de código de barras
document.getElementById('codigoBarras').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Configuração melhorada do Select2
$('.select2').select2({
    width: '100%',
    dropdownParent: $('body'), // Isso ajuda a evitar problemas de z-index
    placeholder: 'Selecione uma opção',
    allowClear: true
});

// Ajuste da altura do container pai quando o dropdown é aberto
$('.select2').on('select2:open', function() {
    let dropdown = $('.select2-dropdown');
    let parent = $(this).closest('.form-section');
    parent.css('overflow', 'visible');
});

// Manipulação do campo de arquivo
document.getElementById('arquivo').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    const fileSelected = this.closest('.custom-file-input').querySelector('.custom-file-selected');
    const fileNameSpan = fileSelected.querySelector('.selected-file-name');
    
    if (fileName) {
        fileNameSpan.textContent = fileName;
        fileSelected.style.display = 'block';
    } else {
        fileSelected.style.display = 'none';
    }
});

function previewImage(input) {
    const preview = document.getElementById('preview-image');
    const previewContainer = preview.closest('.image-preview');
    const file = input.files[0];
    const reader = new FileReader();

    // Atualiza o nome do arquivo selecionado
    const fileSelected = input.closest('.custom-file-input').querySelector('.custom-file-selected');
    const fileNameSpan = fileSelected.querySelector('.selected-file-name');
    
    if (file) {
        // Adiciona classe de loading antes de começar a carregar
        previewContainer.classList.add('loading');
        
        reader.onload = function(e) {
            const newImage = new Image();
            newImage.src = e.target.result;
            
            newImage.onload = function() {
                preview.src = e.target.result;
                // Remove a classe de loading após um pequeno delay
                setTimeout(() => {
                    previewContainer.classList.remove('loading');
                }, 50);
            }
            
            fileNameSpan.textContent = file.name;
            fileSelected.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    } else {
        previewContainer.classList.add('loading');
        preview.src = '../<?= htmlspecialchars($resp['Itens']['Image']) ?>';
        fileSelected.style.display = 'none';
        
        setTimeout(() => {
            previewContainer.classList.remove('loading');
        }, 50);
    }
}

function calcularValor() {
    const pesoTotal = parseFloat(document.getElementById('pesoTotal').value);
    const valorTotal = parseFloat(document.getElementById('valorTotal').value);
    const quantidadeDesejada = parseFloat(document.getElementById('quantidadeDesejada').value);
    const valorVendaDesejado = parseFloat(document.getElementById('valorVendaDesejado').value);
    const resultado = document.getElementById('resultadoCalculo');
    const resultadoMargem = document.getElementById('resultadoMargem');
    
    if (!pesoTotal || !valorTotal || !quantidadeDesejada || !valorVendaDesejado) {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Por favor, preencha todos os campos!'
        });
        return;
    }
    
    if (pesoTotal <= 0 || valorTotal <= 0 || quantidadeDesejada <= 0 || valorVendaDesejado <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Os valores devem ser maiores que zero!'
        });
        return;
    }
    
    const custoPorGrama = valorTotal / pesoTotal;
    const custoQuantidadeDesejada = custoPorGrama * quantidadeDesejada;
    const margemLucro = ((valorVendaDesejado - custoQuantidadeDesejada) / custoQuantidadeDesejada) * 100;
    
    resultado.style.display = 'block';
    resultado.innerHTML = `
        <div>
            <strong>${quantidadeDesejada}g</strong> custam 
            <strong>R$ ${custoQuantidadeDesejada.toFixed(2)}</strong>
            <br>
            <small>(R$ ${custoPorGrama.toFixed(4)} por grama)</small>
        </div>`;
    
    resultadoMargem.style.display = 'block';
    resultadoMargem.className = 'margin-result ' + (margemLucro >= 0 ? 'margin-positive' : 'margin-negative');
    resultadoMargem.innerHTML = `
        <div>
            <strong>Valor de Venda:</strong> R$ ${valorVendaDesejado.toFixed(2)}
            <br>
            <strong>Margem de Lucro:</strong> ${margemLucro.toFixed(2)}%
            <br>
            <strong>Lucro:</strong> R$ ${(valorVendaDesejado - custoQuantidadeDesejada).toFixed(2)}
        </div>`;
    
    // Adiciona animação de fade
    resultado.style.opacity = '0';
    resultadoMargem.style.opacity = '0';
    setTimeout(() => {
        resultado.style.opacity = '1';
        resultadoMargem.style.opacity = '1';
    }, 50);
}

// Adiciona evento de cálculo ao pressionar Enter em qualquer input
document.querySelectorAll('.calculator-input').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            calcularValor();
        }
    });
});
</script>
