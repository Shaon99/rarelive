<div class="row">
    <div class="form-group col-md-8 col-12 mt-4">
        <label for="ai_prompt">Enhance Your Product Description with AI-Powered Assistance</label>
        <textarea type="text" id="ai_prompt" class="form-control" rows="3"
            placeholder="Enter product details (Format: Product Name, Specifications, Features. Example: Premium Cotton T-Shirt, Color: Navy Blue, Size: Large, Features: Embroidered Logo, Breathable Fabric)"
            style="min-height: 50px;" oninput="this.style.height = 'auto'; this.style.height = this.scrollHeight + 'px';"></textarea>
        <small class="text-muted">Leverage with advanced AI technology to generate compelling and professional product
            descriptions</small>
        <br>
        <small class="text-danger error-text"></small>
    </div>
    <div class="form-group col-12">
        <button type="button" class="ai-gradient-btn" id="generate-ai-btn">
            <i class="fas fa-robot me-2"></i>
            Generate with AI Assistance
            <div class="particles">
                <div class="particle particle-1"></div>
                <div class="particle particle-2"></div>
                <div class="particle particle-3"></div>
            </div>
        </button>
    </div>
</div>
