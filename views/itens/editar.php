// ...existing code...

<div class="form-group">
    <label for="codigoBarras">Código de Barras</label>
    <div class="input-group">
        <input type="text" 
               class="form-control" 
               id="codigoBarras" 
               name="codigoBarras" 
               value="<?php echo isset($item['CodigoBarras']) ? $item['CodigoBarras'] : ''; ?>"
               placeholder="Digite ou escaneie o código de barras"
               pattern="\d*"
               maxlength="13"
               title="Digite apenas números">
        <span class="input-group-btn">
            <button type="button" class="btn btn-default" onclick="lerCodigoBarras()">
                <i class="fa fa-barcode"></i>
            </button>
        </span>
    </div>
    <small class="text-muted">Use um leitor de código de barras ou digite manualmente</small>
</div>

<script>
function lerCodigoBarras() {
    document.getElementById('codigoBarras').focus();
}

// Validação do campo de código de barras
document.getElementById('codigoBarras').addEventListener('input', function(e) {
    // Remove caracteres não numéricos
    this.value = this.value.replace(/[^\d]/g, '');
    
    // Limita a 13 dígitos (padrão EAN-13)
    if (this.value.length > 13) {
        this.value = this.value.slice(0, 13);
    }
});
</script>

// ...existing code...
