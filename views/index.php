<?php
require_once '../App/auth.php';
require_once '../layout/script.php';
require_once '../App/Models/relatorios.class.php';
require_once '../App/Models/calculadora.class.php';

echo $head;
echo $header;
echo $aside;
?>

<div class="content-wrapper">
    <section class="content">
        <!-- Mensagem de Boas-vindas -->
        <div class="welcome-section">
            <h1>Olá <b><?php echo $username; ?></b></h1>
            <p>Seja bem-vindo ao seu Controle de Estoque em PHP!</p>
        </div>

        <div class="row">
            <!-- Coluna do Carrossel -->
            <div class="col-md-8">
                <!-- Carrossel existente -->
                <div class="carousel-container">
                    <!-- Controles do Carrossel -->
                    <div class="carousel-controls">
                        <button class="carousel-btn prev" onclick="prevImage()">
                            <i class="fa fa-chevron-left"></i>
                        </button>
                        
                        <div class="carousel-image">
                            <img id="mainImage" src="<?php echo $url; ?>dist/img/controledeestoque.png" 
                                 alt="Controle de Estoque">
                        </div>

                        <button class="carousel-btn next" onclick="nextImage()">
                            <i class="fa fa-chevron-right"></i>
                        </button>
                    </div>

                    <!-- Barra de Ferramentas -->
                    <div class="toolbar">
                        <button class="btn" onclick="toggleFullscreen()">
                            <i class="fa fa-expand"></i>
                        </button>
                        <button class="btn" onclick="addImage()">
                            <i class="fa fa-plus"></i> Adicionar
                        </button>
                        <button class="btn btn-danger" onclick="deleteCurrentImage()">
                            <i class="fa fa-trash"></i> Excluir
                        </button>
                    </div>

                    <!-- Preview de Imagens -->
                    <div class="preview-container">
                        <div id="imagePreview" class="image-preview"></div>
                        <div id="imageCounter" class="image-counter"></div>
                    </div>
                </div>
            </div>

            <!-- Nova coluna para a calculadora -->
            <div class="col-md-4">
                <?php
                // Instancia e renderiza a calculadora
                $calculadora = new CalculadoraProduto();
                echo $calculadora->renderCalculadora();
                ?>
            </div>
        </div>
    </section>
</div>

<style>
/* Estilos Base */


.content-wrapper {
    padding: 20px;
    background: #f4f6f9;
}

/* Seção de Boas-vindas */
.welcome-section {
    text-align: center;
    margin-bottom: 30px;
}

.welcome-section h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 10px;
}

.welcome-section p {
    font-size: 1.2em;
    color: #7f8c8d;
}

/* Container do Carrossel */
.carousel-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Controles do Carrossel */
.carousel-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
}

.carousel-btn {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    background: #3498db;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.carousel-btn:hover {
    background: #2980b9;
    transform: scale(1.1);
}

/* Imagem Principal */
.carousel-image {
    position: relative;
    width: 600px;
    height: 400px;
    overflow: hidden;
    border-radius: 10px;
}

.carousel-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

/* Barra de Ferramentas */
.toolbar {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 20px 0;
}

.toolbar .btn {
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    background: #3498db;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.toolbar .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.toolbar .btn-danger {
    background: #e74c3c;
}

/* Preview de Imagens */
.preview-container {
    margin-top: 20px;
}

.image-preview {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding: 10px;
    background: rgba(0,0,0,0.05);
    border-radius: 10px;
}

.image-preview img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.image-preview img:hover {
    transform: scale(1.1);
}

.image-preview img.active {
    border: 2px solid #3498db;
}

/* Responsividade */
@media (max-width: 768px) {
    .carousel-image {
        width: 100%;
        height: 300px;
    }

    .toolbar {
        flex-wrap: wrap;
    }
}

/* Animações */
.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Adicione estilos */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 400px; /* Mesma altura do carousel-image */
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 40px;
    text-align: center;
    border: 2px dashed #dee2e6;
}

.empty-state i {
    font-size: 80px;
    background: linear-gradient(45deg, #3498db, #2ecc71);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 25px;
    animation: float 3s ease-in-out infinite;
}

.empty-state h3 {
    color: #2c3e50;
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 15px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.empty-state p {
    color: #7f8c8d;
    font-size: 18px;
    max-width: 300px;
    line-height: 1.5;
    margin: 0 auto;
}

/* Animação de flutuação */
@keyframes float {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-5px);
    }
    100% {
        transform: translateY(0px);
    }
}

/* Adicione um efeito de hover no estado vazio */
.empty-state:hover {
    border-color: #3498db;
    transform: scale(1.02);
    transition: all 0.3s ease;
    cursor: pointer;
}

/* Adicione um efeito de brilho */
.empty-state::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        to right,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,0.3) 50%,
        rgba(255,255,255,0) 100%
    );
    transform: rotate(30deg);
    animation: shine 6s infinite;
}

@keyframes shine {
    from {
        transform: translateX(-100%) rotate(30deg);
    }
    to {
        transform: translateX(100%) rotate(30deg);
    }
}

/* Estilos para a nova seção da calculadora */
.calculator-box {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.calculator-box h5 {
    color: #3498db;
    margin-bottom: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.calculator-box h5 i {
    font-size: 20px;
}

.calculator-input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.calculator-input:focus {
    border-color: #3498db;
    box-shadow: 0 0 8px rgba(52, 152, 219, 0.2);
    outline: none;
}

.calculator-result, .margin-result {
    margin-top: 15px;
    padding: 15px;
    border-radius: 10px;
    animation: fadeIn 0.3s ease;
}

.calculator-result {
    background: #e8f5e9;
    color: #2e7d32;
}

.margin-result {
    background: #e3f2fd;
    color: #1565c0;
}

.margin-result.margin-positive {
    background: #e8f5e9;
    color: #2e7d32;
}

.margin-result.margin-negative {
    background: #ffebee;
    color: #c62828;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsividade */
@media (max-width: 768px) {
    .calculator-box {
        margin-top: 30px;
    }
}
</style>

<script>
let currentIndex = 0;
let isFullscreen = false;
let defaultImages = [
    '<?php echo $url; ?>dist/img/controledeestoque.png',
    '<?php echo $url; ?>dist/img/controle-de-estoque2.png',
    '<?php echo $url; ?>dist/img/gráfico.png'
];
let images = [...defaultImages];

// Carrega imagens salvas e mantém registro das apagadas
function loadSavedImages() {
    const savedImages = localStorage.getItem('customImages');
    const deletedImages = JSON.parse(localStorage.getItem('deletedImages') || '[]');
    
    if (savedImages) {
        const customImages = JSON.parse(savedImages);
        // Filtra as imagens padrão removidas
        images = defaultImages.filter(img => !deletedImages.includes(img));
        // Adiciona as imagens customizadas
        images = [...images, ...customImages];
    }
}

// Atualiza a exibição
function updateDisplay() {
    const mainImage = document.getElementById('mainImage');
    const carouselImage = document.querySelector('.carousel-image');
    
    if (images.length === 0) {
        showEmptyState();
        return;
    }
    
    // Remove o estado vazio se existir
    const emptyState = carouselImage.querySelector('.empty-state');
    if (emptyState) {
        emptyState.remove();
    }
    
    // Verifica se a imagem principal já existe
    if (!carouselImage.contains(mainImage)) {
        const newMainImage = document.createElement('img');
        newMainImage.id = 'mainImage';
        newMainImage.alt = 'Controle de Estoque';
        carouselImage.appendChild(newMainImage);
    }
    
    // Atualiza a imagem
    mainImage.src = images[currentIndex];
    mainImage.style.display = 'block';
    
    // Atualiza o resto da interface
    updatePreview();
    updateCounter();
    
    // Habilita os botões de navegação
    document.querySelector('.carousel-btn.prev').disabled = false;
    document.querySelector('.carousel-btn.next').disabled = false;
}

// Atualiza o preview
function updatePreview() {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    images.forEach((src, index) => {
        const img = document.createElement('img');
        img.src = src;
        img.draggable = true;
        img.onclick = () => {
            currentIndex = index;
            updateDisplay();
        };
        
        if (index === currentIndex) {
            img.classList.add('active');
        }
        
        preview.appendChild(img);
    });
    
    setupDragAndDrop();
}

// Atualiza o contador
function updateCounter() {
    const counter = document.getElementById('imageCounter');
    counter.textContent = `${currentIndex + 1}/${images.length}`;
}

// Navegação
function nextImage() {
    currentIndex = (currentIndex + 1) % images.length;
    updateDisplay();
}

function prevImage() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    updateDisplay();
}

// Adicionar imagem
function addImage() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = handleImageUpload;
    input.click();
}

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        images.push(e.target.result);
        // Salva apenas as imagens customizadas
        const customImages = images.filter(img => !defaultImages.includes(img));
        localStorage.setItem('customImages', JSON.stringify(customImages));
        currentIndex = images.length - 1;
        
        // Garante que o estado vazio seja removido
        const carouselImage = document.querySelector('.carousel-image');
        const emptyState = carouselImage.querySelector('.empty-state');
        if (emptyState) {
            emptyState.remove();
        }
        
        updateDisplay();
    };
    reader.readAsDataURL(file);
}

// Excluir imagem
function deleteCurrentImage() {
    const deletedImage = images[currentIndex];
    
    // Se for uma imagem padrão, adiciona à lista de imagens excluídas
    if (defaultImages.includes(deletedImage)) {
        const deletedImages = JSON.parse(localStorage.getItem('deletedImages') || '[]');
        deletedImages.push(deletedImage);
        localStorage.setItem('deletedImages', JSON.stringify(deletedImages));
    }
    
    // Remove a imagem do array atual
    images.splice(currentIndex, 1);
    if (currentIndex >= images.length) {
        currentIndex = images.length - 1;
    }
    
    // Salva o estado atual das imagens customizadas
    const customImages = images.filter(img => !defaultImages.includes(img));
    localStorage.setItem('customImages', JSON.stringify(customImages));
    
    if (images.length === 0) {
        showEmptyState();
    } else {
        updateDisplay();
    }
}

// Adicione a função showEmptyState
function showEmptyState() {
    const carouselImage = document.querySelector('.carousel-image');
    carouselImage.innerHTML = `
        <div class="empty-state" onclick="addImage()">
            <i class="fa fa-images"></i>
            <h3>Nenhuma imagem disponível</h3>
            <p>Clique aqui ou no botão "Adicionar" para incluir novas imagens</p>
            <div class="empty-state-action">
                <i class="fa fa-plus-circle"></i>
            </div>
        </div>
    `;
    
    // Atualiza o contador
    const counter = document.getElementById('imageCounter');
    counter.textContent = '0/0';
    
    // Limpa o preview
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    // Desabilita os botões de navegação
    document.querySelector('.carousel-btn.prev').disabled = true;
    document.querySelector('.carousel-btn.next').disabled = true;
}

// Modifique a função toggleFullscreen
function toggleFullscreen() {
    const mainImage = document.getElementById('mainImage');
    const container = document.querySelector('.carousel-image');
    
    if (!isFullscreen) {
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        }
        
        // Ajusta o container para cobrir toda a tela
        container.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
            background-color: rgba(0, 0, 0, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            margin: 0;
        `;
        
        // Ajusta a imagem para cobrir a tela mantendo proporções
        mainImage.style.cssText = `
            width: 100vw;
            height: 100vh;
            object-fit: contain;
            border-radius: 0;
            padding: 0;
            margin: 0;
            transition: none;
        `;
        
        isFullscreen = true;
        document.addEventListener('fullscreenchange', exitHandler);
        
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
        resetImageStyle();
        isFullscreen = false;
    }
}

// Função para resetar o estilo da imagem
function resetImageStyle() {
    const mainImage = document.getElementById('mainImage');
    const container = document.querySelector('.carousel-image');
    
    container.style.cssText = `
        position: relative;
        width: 600px;
        height: 400px;
        overflow: hidden;
        border-radius: 10px;
    `;
    
    mainImage.style.cssText = `
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    `;
}

// Handler para sair do modo tela cheia
function exitHandler() {
    if (!document.fullscreenElement) {
        resetImageStyle();
        isFullscreen = false;
        document.removeEventListener('fullscreenchange', exitHandler);
    }
}

// Event Listeners
document.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') prevImage();
    if (event.key === 'ArrowRight') nextImage();
    if (event.key === 'f') toggleFullscreen();
});

// Inicialização
loadSavedImages();
updateDisplay();

// Adicione estas funções para o drag and drop
function setupDragAndDrop() {
    const preview = document.getElementById('imagePreview');
    const previewImages = preview.getElementsByTagName('img');

    Array.from(previewImages).forEach(img => {
        img.draggable = true;
        img.addEventListener('dragstart', handleDragStart);
        img.addEventListener('dragend', handleDragEnd);
        img.addEventListener('dragover', handleDragOver);
        img.addEventListener('drop', handleDrop);
    });
}

function handleDragStart(e) {
    this.classList.add('dragging');
    e.dataTransfer.setData('text/plain', Array.from(this.parentNode.children).indexOf(this));
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
}

function handleDragOver(e) {
    e.preventDefault();
    return false;
}

function handleDrop(e) {
    e.preventDefault();
    
    const fromIndex = parseInt(e.dataTransfer.getData('text/plain'));
    const toIndex = Array.from(this.parentNode.children).indexOf(this);
    
    // Reordena o array de imagens
    const [movedImage] = images.splice(fromIndex, 1);
    images.splice(toIndex, 0, movedImage);
    
    // Ajusta o índice atual se necessário
    if (currentIndex === fromIndex) {
        currentIndex = toIndex;
    } else if (currentIndex > fromIndex && currentIndex <= toIndex) {
        currentIndex--;
    } else if (currentIndex < fromIndex && currentIndex >= toIndex) {
        currentIndex++;
    }
    
    // Salva a nova ordem
    saveImageOrder();
    
    // Atualiza a interface
    updateDisplay();
}

// Função para salvar a ordem das imagens
function saveImageOrder() {
    const customImages = images.filter(img => !defaultImages.includes(img));
    localStorage.setItem('customImages', JSON.stringify(customImages));
    localStorage.setItem('imagesOrder', JSON.stringify(images));
}
</script>

<?php echo $javascript; ?>


