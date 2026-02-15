if (typeof Icons === 'undefined') {
    var Icons = {
        check: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        cross: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
        warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        ai: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><rect x="3" y="11" width="18" height="10" rx="2"></rect><circle cx="12" cy="5" r="2"></circle><path d="M12 7v4"></path><line x1="8" y1="16" x2="8" y2="16"></line><line x1="16" y1="16" x2="16" y2="16"></line></svg>',
        loading: '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 1rem; height: 1rem; display:inline-block; vertical-align:middle;"></span>',
        info: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
        file: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>'
    };
}

class TesseractOCR {
    constructor() {
        this.worker = null;
        this.isProcessing = false;
        this.initialized = false;
    }

    async initialize() {
        if (this.initialized) return;
        
        try {
            // In v5, createWorker is async and accepts language/OEM directly
            // Using ind+eng for better accuracy with currency symbols and numbers
            this.worker = await Tesseract.createWorker('ind+eng');
            
            // Set parameters separately in v5
            await this.worker.setParameters({
                tessedit_pageseg_mode: 3, // Fully automatic page segmentation, but no OSD
                preserve_interword_spaces: '1',
            });
            
            this.initialized = true;
        } catch (error) {
            console.error('Failed to initialize Tesseract:', error);
            throw error;
        }
    }

    async recognizeImage(imageFile) {
        if (this.isProcessing) {
            throw new Error('Tesseract sedang memproses file lain. Tunggu sebentar.');
        }

        try {
            await this.initialize();
            this.isProcessing = true;

            // Preprocess image for better accuracy
            let processedImage;
            
            if (imageFile.type === 'application/pdf') {
                const summaryEl = document.getElementById('validationSummary');
                if (summaryEl) {
                    summaryEl.innerHTML = `<div class="validation-loading" style="display:flex; align-items:center; gap:0.5rem; color:#667eea;">${Icons.file}<span> Mengkonversi PDF ke gambar...</span></div>`;
                }
                processedImage = await this.convertPdfToImage(imageFile);
            } else {
                processedImage = await this.preprocessImage(imageFile);
            }
            
            // recognize in v5
            const result = await this.worker.recognize(processedImage);
            
            const ocrText = result.data.text;
            const lines = ocrText.trim().split('\n').filter(line => line.trim().length > 0);
            
            
            // Early warning if OCR text seems too short
            if (ocrText.length < 20) {
                console.warn('Warning: OCR text is very short. Image quality might be poor.');
            }

            return {
                success: true,
                rawText: ocrText,
                confidence: Math.round(result.data.confidence),
                blocks: result.data.blocks,
                lineCount: lines.length
            };

        } catch (error) {
            console.error('OCR recognition failed:', error);
            return {
                success: false,
                error: 'Gagal membaca file: ' + error.message
            };
        } finally {
            this.isProcessing = false;
        }
    }

    async convertPdfToImage(pdfFile) {
        if (typeof pdfjsLib === 'undefined') {
            throw new Error('PDF.js library not loaded');
        }

        const arrayBuffer = await pdfFile.arrayBuffer();
        const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        const page = await pdf.getPage(1); // Get first page
        
        const viewport = page.getViewport({ scale: 4.0 }); // Scale 4.0 for high quality (crucial for OCR)
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        
        await page.render({
            canvasContext: context,
            viewport: viewport
        }).promise;
        
        // Preprocess the rendered canvas similar to image
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        
        // Grayscale & Contrast enhancement
        for (let i = 0; i < data.length; i += 4) {
             const r = data[i];
             const g = data[i + 1];
             const b = data[i + 2];
             
             // Grayscale
             const gray = 0.299 * r + 0.587 * g + 0.114 * b;
             
             // Simple contrast
             const contrastFactor = 1.2;
             const newGray = ((gray - 128) * contrastFactor) + 128;
             
             const finalGray = Math.min(255, Math.max(0, newGray));
             
             data[i] = finalGray;
             data[i + 1] = finalGray;
             data[i + 2] = finalGray;
        }
        
        context.putImageData(imageData, 0, 0);
        
        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                resolve(blob);
            }, 'image/jpeg', 0.9);
        });
    }

    async preprocessImage(imageFile) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                let width = img.width;
                let height = img.height;
                const MIN_WIDTH = 2000; // Increased for better accuracy
                const MAX_WIDTH = 3000;
                
                if (width < MIN_WIDTH) {
                    const scale = MIN_WIDTH / width;
                    width = Math.round(width * scale);
                    height = Math.round(height * scale);
                }
                
                if (width > MAX_WIDTH) {
                    height = Math.round(height * (MAX_WIDTH / width));
                    width = MAX_WIDTH;
                }
                
                canvas.width = width;
                canvas.height = height;
                
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(img, 0, 0, width, height);
                
                const imageData = ctx.getImageData(0, 0, width, height);
                const data = imageData.data;
                
                // --- ULTRA SMART PREPROCESSING ---
                
                // 1. Grayscale & Adaptive Contrast Stretching
                let min = 255, max = 0;
                const grayscale = new Uint8ClampedArray(width * height);
                
                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i];
                    const g = data[i+1];
                    const b = data[i+2];
                    
                    // Luma formula - Give more weight to green for clarity
                    const gray = 0.299 * r + 0.587 * g + 0.114 * b;
                    grayscale[i/4] = gray;
                    
                    if (gray < min) min = gray;
                    if (gray > max) max = gray;
                }
                
                // Contrast Stretching
                const range = max - min || 1;
                for (let i = 0; i < grayscale.length; i++) {
                    grayscale[i] = ((grayscale[i] - min) / range) * 255;
                }
                
                // 2. Simple Adaptive Thresholding (Window-based)
                // This helps with uneven lighting/shadows on receipts
                const thresholded = new Uint8ClampedArray(width * height);
                
                // For performance, we'll use a simplified version of adaptive thresholding
                // Divide image into blocks and compute local thresholds
                const blockSize = 60; // Slightly larger for better local contrast in large fonts
                for (let y = 0; y < height; y += blockSize) {
                    for (let x = 0; x < width; x += blockSize) {
                        // Find local min/max and average in block
                        let lMin = 255, lMax = 0, sum = 0, count = 0;
                        for (let by = y; by < Math.min(y + blockSize, height); by++) {
                            for (let bx = x; bx < Math.min(x + blockSize, width); bx++) {
                                const val = grayscale[by * width + bx];
                                if (val < lMin) lMin = val;
                                if (val > lMax) lMax = val;
                                sum += val;
                                count++;
                            }
                        }
                        
                        const avg = sum / count;
                        // ULTRA AGGRESSIVE: Push light grays (watermarks) to white
                        // Using a much higher bias towards white for receipts with backgrounds
                        const lThreshold = (avg * 0.5 + (lMin + lMax) / 2 * 0.5) * 0.92;
                        
                        // Smart Inversion Detection: Check if block is mostly dark
                        let darkPixels = 0;
                        for (let by = y; by < Math.min(y + blockSize, height); by++) {
                            for (let bx = x; bx < Math.min(x + blockSize, width); bx++) {
                                if (grayscale[by * width + bx] < lThreshold) darkPixels++;
                            }
                        }
                        
                        // If more than 60% of pixels are dark, it's likely a dark background (e.g. BRI Header)
                        // We invert it so Tesseract sees black text on white
                        const shouldInvert = (darkPixels / count) > 0.6;

                        // Apply threshold to block
                        for (let by = y; by < Math.min(y + blockSize, height); by++) {
                            for (let bx = x; bx < Math.min(x + blockSize, width); bx++) {
                                const idx = by * width + bx;
                                // Pure binary with high-pass filter
                                let val = grayscale[idx] < lThreshold ? 0 : 255;
                                if (shouldInvert) val = 255 - val;
                                thresholded[idx] = val;
                            }
                        }
                    }
                }
                
                // Write back to imageData - Use Pure Binary for cleaner background removal
                for (let i = 0; i < data.length; i += 4) {
                    const idx = i / 4;
                    const val = thresholded[idx];
                    data[i] = val;
                    data[i + 1] = val;
                    data[i + 2] = val;
                    data[i + 3] = 255;
                }
                
                ctx.putImageData(imageData, 0, 0);
                
                canvas.toBlob((blob) => {
                    resolve(blob);
                }, 'image/jpeg', 0.9);
            };
            img.onerror = reject;
            img.src = URL.createObjectURL(imageFile);
        });
    }



    async terminate() {
        if (this.worker) {
            try {
                await this.worker.terminate();
                this.initialized = false;
                            } catch (error) {
                console.error('Error terminating worker:', error);
            }
        }
    }

    extractOCRData(rawText) {
        const lines = rawText.split('\n').filter(line => line.trim());
        
        return {
            rawText: rawText,
            lines: lines,
            firstLine: lines[0] || '',
            lastLine: lines[lines.length - 1] || ''
        };
    }
}

class OCRValidator {
    constructor(apiEndpoints) {
        this.processBuktiUrl = apiEndpoints.processBukti || '/api/ocr/process-bukti';
        this.validateDataUrl = apiEndpoints.validateData || '/api/ocr/validate-data';
        this.csrfToken = this.getCsrfToken();
    }

    getCsrfToken() {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            return metaToken.getAttribute('content');
        }
        
        const inputToken = document.querySelector('input[name="_token"]');
        if (inputToken) {
            return inputToken.value;
        }
        
        console.warn('CSRF token not found');
        return '';
    }

    async processBukti(file, ocrText = '') {
        try {
            const formData = new FormData();
            formData.append('bukti', file);
            formData.append('ocr_text', ocrText);

            
            const response = await fetch(this.processBuktiUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: formData
            });

            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Backend error response:', errorText);
                try {
                    const errorData = JSON.parse(errorText);
                    return {
                        success: false,
                        error: errorData.message || `HTTP ${response.status}`
                    };
                } catch (e) {
                    return {
                        success: false,
                        error: `HTTP ${response.status}: ${errorText.substring(0, 200)}`
                    };
                }
            }

            const data = await response.json();
                        return data;

        } catch (error) {
            console.error('Error sending to backend:', error);
            return {
                success: false,
                error: 'Gagal mengirim file ke server: ' + error.message
            };
        }
    }

    async validateData(ocrData, inputData) {
        try {
            const response = await fetch(this.validateDataUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    ocr_data: ocrData,
                    input_data: inputData
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                return {
                    success: false,
                    error: errorData.message || `HTTP ${response.status}`
                };
            }

            const data = await response.json();
            return data;

        } catch (error) {
            console.error('Error validating data:', error);
            return {
                success: false,
                error: 'Gagal memvalidasi data: ' + error.message
            };
        }
    }
}

class FileUploadHandler {
    constructor(config) {
        this.fileInput = document.getElementById(config.fileInputId);
        this.uploadZone = document.getElementById(config.uploadZoneId);
        this.filePreview = document.getElementById(config.filePreviewId);
        this.submitBtn = document.getElementById(config.submitBtnId);
        this.validationResults = document.getElementById(config.validationResultsId);
        
        this.ocr = new TesseractOCR();
        this.validator = new OCRValidator({
            processBukti: config.processBuktiUrl,
            validateData: config.validateDataUrl
        });

        this.currentFile = null;
        this.lastOCRData = null;
        this.validationState = false;
        this.setupEventListeners();
        this.setupInputListeners();
    }

    setupEventListeners() {
        this.fileInput.addEventListener('click', (e) => {
            if (!this.validateInputs()) {
                e.preventDefault();
                showNotification('warning', 'Data Belum Lengkap', 'Mohon lengkapi data Tanggal, Vendor, dan Nominal terlebih dahulu sebelum upload bukti.');
            }
        });

        this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        this.uploadZone.addEventListener('dragover', (e) => this.handleDragOver(e));
        this.uploadZone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
        this.uploadZone.addEventListener('drop', (e) => this.handleDrop(e));

        document.getElementById('removeFile')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.removeFile();
        });

        document.getElementById('previewBtn')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.previewFile();
        });
    }

    setupInputListeners() {
        const inputs = ['nama_vendor', 'nominal', 'tanggal_transaksi'];
        inputs.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', () => {
                    if (this.lastOCRData) {
                        this.displayMatchingResults(this.lastOCRData);
                    }
                });
                element.addEventListener('change', () => {
                    if (this.lastOCRData) {
                        this.displayMatchingResults(this.lastOCRData);
                    }
                });
            }
        });
    }

    handleFileSelect(e) {
        if (e.target.files.length > 0) {
            if (this.validateInputs()) {
                this.processFile(e.target.files[0]);
            } else {
                this.fileInput.value = ''; // Reset file input
                showNotification('warning', 'Data Belum Lengkap', 'Mohon lengkapi data Tanggal, Vendor, dan Nominal terlebih dahulu sebelum upload bukti.');
            }
        }
    }

    handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        this.uploadZone.style.backgroundColor = 'rgba(102, 126, 234, 0.05)';
        this.uploadZone.style.borderColor = '#667eea';
    }

    handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        this.uploadZone.style.backgroundColor = '';
        this.uploadZone.style.borderColor = '';
    }

    handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        this.uploadZone.style.backgroundColor = '';
        this.uploadZone.style.borderColor = '';

        if (e.dataTransfer.files.length > 0) {
            if (this.validateInputs()) {
                const file = e.dataTransfer.files[0];
                this.fileInput.files = e.dataTransfer.files;
                this.processFile(file);
            } else {
                showNotification('warning', 'Data Belum Lengkap', 'Mohon lengkapi data Tanggal, Vendor, dan Nominal terlebih dahulu sebelum upload bukti.');
            }
        }
    }

    validateInputs() {
        const tanggal = document.getElementById('tanggal_transaksi').value;
        const vendor = document.getElementById('nama_vendor').value;
        const nominal = document.getElementById('nominal').value;

        return tanggal && vendor && nominal;
    }

    async processFile(file) {
        this.currentFile = file;
        this.displayFileInfo(file);
        
        const fileValidation = this.validateFile(file);
        if (!fileValidation.valid) {
            this.showValidationError(fileValidation.message);
            return;
        }

        await this.validateWithBackend(file);
    }

    validateFile(file) {
        const maxSize = 5 * 1024 * 1024;
        const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

        if (file.size > maxSize) {
            return {
                valid: false,
                message: `Ukuran file terlalu besar. Maksimal 5MB, file Anda ${this.formatFileSize(file.size)}`
            };
        }

        if (!allowedMimes.includes(file.type)) {
            return {
                valid: false,
                message: 'Format file tidak didukung. Gunakan JPG, PNG, WebP, atau PDF'
            };
        }

        return { valid: true };
    }

    displayFileInfo(file) {
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        
        fileName.textContent = file.name;
        fileSize.textContent = this.formatFileSize(file.size);
        
        this.filePreview.style.display = 'block';
    }

    async validateWithBackend(file) {
        this.showValidationLoading();

        try {
                        
            const tesseractOCR = new TesseractOCR();
            const ocrResult = await tesseractOCR.recognizeImage(file);
            
            await tesseractOCR.terminate();

            if (!ocrResult.success) {
                this.showValidationError(ocrResult.error || 'Gagal membaca gambar');
                this.validationState = false;
                this.submitBtn.disabled = true;
                return;
            }

            // Check OCR quality - warn user if text is too short
            const textLength = ocrResult.rawText.length;
            const lineCount = ocrResult.lineCount || 0;
            
            
            if (textLength < 30 || lineCount < 3) {
                console.warn('OCR Text Result:', ocrResult.rawText);
                console.warn('Image might have poor quality. Try:', {
                    'Better lighting': 'Pastikan pencahayaan terang',
                    'Higher resolution': 'Gunakan foto berkualitas tinggi',
                    'Clear focus': 'Fokus pada teks/nomor penting',
                    'Straight angle': 'Foto tegak lurus, tidak miring'
                });
            }

            // Set hidden input for form submission
            const ocrInput = document.getElementById('ocr_text');
            if (ocrInput) {
                ocrInput.value = ocrResult.rawText;
            }

            const backendResult = await this.validator.processBukti(file, ocrResult.rawText);

            if (!backendResult.success) {
                let errorMsg = backendResult.message || 'Validasi gagal';
                if (backendResult.is_duplicate) {
                    errorMsg = '' + errorMsg + ' File sudah pernah diupload!';
                }
                this.showValidationError(errorMsg);
                this.validationState = false;
                this.submitBtn.disabled = true;
                return;
            }

            if (backendResult.is_pdf) {
                this.handlePdfFile(backendResult);
                return;
            }

            // POPULATE OCR_DATA_JSON HIDDEN INPUT
            const ocrDataJsonInput = document.getElementById('ocr_data_json');
            if (ocrDataJsonInput && backendResult.data) {
                ocrDataJsonInput.value = JSON.stringify(backendResult.data);
            }

            this.lastOCRData = backendResult.data;
            this.validationState = backendResult.can_submit || false;
            
            this.displayOCRResults(backendResult.data);
            this.displayMatchingResults(backendResult.data);
            this.displayValidationStatus(backendResult);
            this.updateSubmitButton(this.validationState);
            
            
        } catch (error) {
            console.error('Validation error:', error);
            this.showValidationError('Error: ' + error.message);
            this.validationState = false;
            this.submitBtn.disabled = true;
        }
    }

    handlePdfFile(backendResult) {
        const validationResults = document.getElementById('validationResults');
        const validationStatus = document.getElementById('validationStatus');
        
        if (validationResults) {
            validationResults.style.display = 'block';
        }
        
        if (validationStatus) {
            validationStatus.innerHTML = `<span style="color: #ea580c;">${Icons.file} PDF File</span>`;
        }
        
        const validationLoading = document.getElementById('validationLoading');
        const validationContent = document.getElementById('validationContent');
        
        if (validationLoading) validationLoading.style.display = 'none';
        if (validationContent) validationContent.style.display = 'block';
        
        const ocrResults = document.getElementById('ocrResults');
        if (ocrResults) {
            ocrResults.style.display = 'block';
            ocrResults.innerHTML = `
                <h4>${Icons.file} File PDF</h4>
                <div class="validation-grid">
                    <div class="validation-item">
                        <span class="label">Status:</span>
                        <span class="value" style="color: #ea580c;">${Icons.warning} Silakan isi data secara manual</span>
                    </div>
                </div>
            `;
        }
        
        const matchingResults = document.getElementById('matchingResults');
        if (matchingResults) {
            matchingResults.style.display = 'none';
        }
        
        this.submitBtn.disabled = true;
            }

    displayOCRResults(data) {
        const ocrVendor = document.getElementById('ocrVendor');
        const ocrNominal = document.getElementById('ocrNominal');
        const ocrTanggal = document.getElementById('ocrTanggal');
        const ocrConfidence = document.getElementById('ocrConfidence');

        if (ocrVendor) ocrVendor.textContent = data.vendor || '-';
        if (ocrNominal) ocrNominal.textContent = data.nominal ? this.formatCurrency(data.nominal) : '-';
        if (ocrTanggal) ocrTanggal.textContent = data.tanggal || '-';
        if (ocrConfidence) ocrConfidence.textContent = (data.confidence_score || 0) + '%';

        // Display Raw Text with quality assessment
        const ocrRawText = document.getElementById('ocrRawText');
        const rawText = data.raw_text || 'No raw text available';
        
        if (ocrRawText) {
            ocrRawText.textContent = rawText;
            
            // Assess OCR quality
            const textLines = rawText.trim().split('\n').filter(line => line.trim().length > 0);
            const textLength = rawText.length;
            
            let qualityIcon = Icons.check;
            let qualityColor = '#10b981'; // Green
            let qualityMessage = 'Kualitas OCR Baik';
            
            if (textLength < 30 || textLines.length < 3) {
                qualityIcon = Icons.warning;
                qualityColor = '#f59e0b'; // Amber
                qualityMessage = 'Kualitas OCR Rendah - Coba upload ulang dengan foto lebih jelas';
            } else if (textLength < 100 || textLines.length < 5) {
                qualityIcon = Icons.warning;
                qualityColor = '#f59e0b'; // Amber
                qualityMessage = 'Kualitas OCR Sedang - Verifikasi teks berikut dengan hati-hati';
            }
            
            // Add quality indicator above raw text
            const qualityDiv = document.createElement('div');
            qualityDiv.style.marginBottom = '0.5rem';
            qualityDiv.style.padding = '0.5rem';
            qualityDiv.style.backgroundColor = '#f0f9ff';
            qualityDiv.style.borderLeft = `3px solid ${qualityColor}`;
            qualityDiv.innerHTML = `${qualityIcon} ${qualityMessage} (${textLength} karakter, ${textLines.length} baris)`;
            
            if (ocrRawText.parentNode && ocrRawText.previousElementSibling?.tagName !== 'DIV') {
                ocrRawText.parentNode.insertBefore(qualityDiv, ocrRawText);
            }
        }

        const ocrResults = document.getElementById('ocrResults');
        if (ocrResults) ocrResults.style.display = 'block';

        this.showValidationContent();
    }

    displayValidationStatus(backendResult) {
        const validationStatus = document.getElementById('validationStatus');
        if (validationStatus) {
            if (backendResult.can_submit) {
                validationStatus.innerHTML = `<span style="color: #10b981; font-weight: bold;">${Icons.check} Lolos - Siap Submit</span>`;
            } else {
                validationStatus.innerHTML = `<span style="color: #ef4444; font-weight: bold;">${Icons.cross} Tidak Lolos - Perbaiki Masalah</span>`;
            }
        }

        const issuesDiv = document.getElementById('validationIssues');
        if (issuesDiv && backendResult.issues && backendResult.issues.length > 0) {
            issuesDiv.style.display = 'block';
            const issuesList = document.getElementById('issuesList');
            if (issuesList) {
                issuesList.innerHTML = backendResult.issues.map(issue => `<li>${issue}</li>`).join('');
            }
        } else if (issuesDiv) {
            issuesDiv.style.display = 'none';
        }
    }

    displayMatchingResults(data) {
        const namaVendor = document.getElementById('nama_vendor').value.trim();
        const nominal = parseFloat(document.getElementById('nominal').value) || 0;
        const tanggalTransaksi = document.getElementById('tanggal_transaksi').value;

        
        const vendorMatch = document.getElementById('vendorMatch');
        const vendorMatchPercent = document.getElementById('vendorMatchPercent');
        const nominalMatch = document.getElementById('nominalMatch');
        const nominalMatchStatus = document.getElementById('nominalMatchStatus');
        const tanggalMatch = document.getElementById('tanggalMatch');
        const tanggalMatchStatus = document.getElementById('tanggalMatchStatus');
        const matchingResults = document.getElementById('matchingResults');

        if (!matchingResults) return;

        let hasMatches = false;
        let blockSubmit = false;

        // Vendor Matching: 85% threshold for UI display (warning), but 80% for backend (pass)
        if (vendorMatch && vendorMatchPercent && namaVendor) {
            const vendorSimilarity = this.calculateSimilarity(data.vendor.toLowerCase(), namaVendor.toLowerCase());
            const vendorUIPass = vendorSimilarity >= 85;
            
            vendorMatch.style.display = 'flex';
            vendorMatch.className = 'match-item';
            vendorMatch.classList.remove('pass', 'fail');
            vendorMatch.classList.add(vendorUIPass ? 'pass' : 'fail');
            vendorMatchPercent.textContent = `${vendorSimilarity}% - ${vendorUIPass ? 'Cocok' : 'Rendah'} (OCR: "${data.vendor}" | Input: "${namaVendor}")`;
            
            hasMatches = true;
        }

        // Nominal Matching: MUST be exact match (BLOCKING)
        if (nominalMatch && nominalMatchStatus && nominal > 0) {
            const nominalExactMatch = parseInt(data.nominal) === parseInt(nominal);
            
            nominalMatch.style.display = 'flex';
            nominalMatch.className = 'match-item';
            nominalMatch.classList.remove('pass', 'fail');
            nominalMatch.classList.add(nominalExactMatch ? 'pass' : 'fail');
            nominalMatchStatus.textContent = `${nominalExactMatch ? 'Cocok Persis' : 'TIDAK COCOK'} (OCR: ${this.formatCurrency(data.nominal)} | Input: ${this.formatCurrency(nominal)})`;
            
            if (!nominalExactMatch) blockSubmit = true;
            hasMatches = true;
        }

        // Tanggal Matching: Warning only, NOT blocking (similarity based)
        if (tanggalMatch && tanggalMatchStatus && tanggalTransaksi) {
            const tanggalSimilarity = this.calculateDateSimilarity(data.tanggal, tanggalTransaksi);
            const tanggalPass = tanggalSimilarity >= 90;
            
            tanggalMatch.style.display = 'flex';
            tanggalMatch.className = 'match-item';
            tanggalMatch.classList.remove('pass', 'fail');
            tanggalMatch.classList.add(tanggalPass ? 'pass' : 'fail');
            tanggalMatchStatus.textContent = `${tanggalSimilarity}% - ${tanggalPass ? 'Cocok' : 'Berbeda'} (OCR: "${data.tanggal || '-'}" | Input: "${tanggalTransaksi}")`;
            
            hasMatches = true;
        }

        // Duplicate Check Display
        const duplicateCheck = document.getElementById('duplicateCheck');
        const duplicateStatus = document.getElementById('duplicateStatus');
        
        if (duplicateCheck && duplicateStatus) {
            duplicateCheck.style.display = 'flex';
            duplicateCheck.className = 'match-item pass';
            duplicateStatus.innerHTML = `${Icons.check} File Unik (Belum pernah diupload)`;
            hasMatches = true;
        }

        if (hasMatches) {
            matchingResults.style.display = 'block';
        }

        this.updateSubmitButton(!blockSubmit);
    }

    updateSubmitButton(isValid) {
        if (this.submitBtn) {
            this.submitBtn.disabled = !isValid;
            if (!isValid) {
                this.submitBtn.classList.add('disabled-btn');
            } else {
                this.submitBtn.classList.remove('disabled-btn');
            }
        }
    }

    calculateSimilarity(str1, str2) {
        const longer = str1.length > str2.length ? str1 : str2;
        const shorter = str1.length > str2.length ? str2 : str1;
        
        if (longer.length === 0) return 100;
        
        const editDistance = this.getEditDistance(longer, shorter);
        return Math.round(((longer.length - editDistance) / longer.length) * 100);
    }

    getEditDistance(s1, s2) {
        const costs = [];
        for (let i = 0; i <= s1.length; i++) {
            let lastValue = i;
            for (let j = 0; j <= s2.length; j++) {
                if (i === 0) {
                    costs[j] = j;
                } else if (j > 0) {
                    let newValue = costs[j - 1];
                    if (s1.charAt(i - 1) !== s2.charAt(j - 1)) {
                        newValue = Math.min(Math.min(newValue, lastValue), costs[j]) + 1;
                    }
                    costs[j - 1] = lastValue;
                    lastValue = newValue;
                }
            }
            if (i > 0) costs[s2.length] = lastValue;
        }
        return costs[s2.length];
    }

    calculateDateSimilarity(ocrDate, inputDate) {
        if (!ocrDate || !inputDate) return 0;
        
        try {
            // Parse dates (expect YYYY-MM-DD format)
            const ocr = new Date(ocrDate);
            const input = new Date(inputDate);
            
            if (isNaN(ocr.getTime()) || isNaN(input.getTime())) {
                return 0;
            }
            
            // If exact match
            if (ocrDate === inputDate) {
                return 100;
            }
            
            // Calculate day difference
            const timeDiff = Math.abs(ocr.getTime() - input.getTime());
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            // Similarity: 100% if exact, decreases by 3% per day difference
            // Formula: 100 - (dayDiff * 3), minimum 0%
            const similarity = Math.max(0, 100 - (dayDiff * 3));
            
            return Math.round(similarity);
        } catch (e) {
            console.error('Error calculating date similarity:', e);
            return 0;
        }
    }

    showValidationLoading() {
        this.validationResults.style.display = 'block';
        document.getElementById('validationLoading').style.display = 'flex';
        document.getElementById('validationContent').style.display = 'none';
        this.submitBtn.disabled = true;
    }

    showValidationContent() {
        document.getElementById('validationLoading').style.display = 'none';
        document.getElementById('validationContent').style.display = 'block';
    }

    showValidationError(message) {
        const statusDiv = document.getElementById('validationStatus');
        if (statusDiv) {
            statusDiv.innerHTML = `<span class="status-error">${Icons.cross} Error</span>`;
        }

        this.validationResults.style.display = 'block';
        document.getElementById('validationLoading').style.display = 'none';
        document.getElementById('validationContent').style.display = 'block';
        
        const issuesDiv = document.getElementById('validationIssues');
        if (issuesDiv) {
            issuesDiv.style.display = 'block';
            document.getElementById('issuesList').innerHTML = `<li>${message}</li>`;
        }
    }

    removeFile() {
        this.fileInput.value = '';
        this.filePreview.style.display = 'none';
        this.validationResults.style.display = 'none';
        this.currentFile = null;
        this.lastOCRData = null;
        this.validationState = false;
        this.submitBtn.disabled = true;
    }

    previewFile() {
        if (this.currentFile) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const modal = document.getElementById('fileModal');
                const modalBody = document.getElementById('modalBody');
                const modalTitle = document.getElementById('modalFileName');
                
                modalTitle.textContent = this.currentFile.name;
                modalBody.innerHTML = '';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'file-preview-image';
                img.alt = this.currentFile.name;
                modalBody.appendChild(img);

                modal.classList.add('active');
            };
            reader.readAsDataURL(this.currentFile);
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }

    formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);
    }
}

// Auto-instantiation disabled to avoid conflict with pengajuan.js
// document.addEventListener('DOMContentLoaded', function() { ... });
