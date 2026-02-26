
if (typeof Icons === 'undefined') {
    var Icons = {
        check: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        cross: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
        warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        ai: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><rect x="3" y="11" width="18" height="10" rx="2"></rect><circle cx="12" cy="5" r="2"></circle><path d="M12 7v4"></path><line x1="8" y1="16" x2="8" y2="16"></line><line x1="16" y1="16" x2="16" y2="16"></line></svg>',
        loading: '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 1rem; height: 1rem; display:inline-block; vertical-align:middle;"></span>',
        info: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
        block: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>',
        eye: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
        file: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>'
    };
} else {
    
    Icons.block = Icons.block || '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>';
    Icons.eye = Icons.eye || '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:text-bottom;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
}

class PengajuanFormManager {
    constructor() {
        this.ocrData = null;
        this.tesseract = new TesseractOCR();
        this.validationTimeout = null;
        this.init();
    }

    init() {
        this.cacheElements();
        this.attachEventListeners();

     
        if (this.nominalInput && this.nominalInput.value) {
            this.handleCurrencyInput();
            this.updateLiveBudget();
        }
    }

    showSummary(type, message) {
        const summaryEl = document.getElementById('validationSummary');
        if (!summaryEl) return;

        summaryEl.className = 'validation-summary-box';

        let icon = Icons.info;
        if (type === 'error') {
            summaryEl.classList.add('validation-summary-error');
            icon = Icons.block;
        } else if (type === 'warning') {
            summaryEl.classList.add('validation-summary-warning');
            icon = Icons.warning;
        } else if (type === 'success') {
            summaryEl.classList.add('validation-summary-success');
            icon = Icons.check;
        } else if (type === 'info') {
            summaryEl.classList.add('validation-summary-info');
            icon = Icons.info;
        }

        summaryEl.innerHTML = `
            <div style="flex-shrink: 0; margin-top: 0.1rem;">${icon}</div>
            <div style="font-weight: 600;">${message}</div>
        `;
        summaryEl.style.display = 'flex';
    }

    cacheElements() {
        this.fileInput = document.getElementById('file_bukti');
        this.vendorInput = document.getElementById('nama_vendor');
        this.nominalInput = document.getElementById('nominal');
        this.dateInput = document.getElementById('tanggal_transaksi');
        this.submitBtn = document.getElementById('submitBtn');
        this.dashboard = document.getElementById('validationResults');
        this.form = document.querySelector('.form-pengajuan') || document.querySelector('form');
    }

    attachEventListeners() {
        if (this.nominalInput) {
            this.nominalInput.addEventListener('input', (e) => {
                this.handleCurrencyInput(e);
                this.updateLiveBudget();
            });
        }
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }
        if (this.fileInput) {
            this.fileInput.addEventListener('click', (e) => {
                const missingFields = this.getMissingFields();
                if (missingFields.length > 0) {
                    e.preventDefault();

                    const message = `Mohon lengkapi <b>${missingFields.join(', ')}</b> terlebih dahulu sebelum mengupload bukti pembayaran.`;

                    this.showNotification('danger', message);

                    
                    this.highlightMissingFields(missingFields);

                  
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
            this.fileInput.addEventListener('change', (e) => this.handleFileChange(e));
        }
        if (this.vendorInput) {
            this.vendorInput.addEventListener('input', () => this.debounceValidation());
        }
        if (this.dateInput) {
            this.dateInput.addEventListener('change', () => this.debounceValidation());
        }

        const kategoriSelect = document.getElementById('kategori_id');
        if (kategoriSelect) {
            kategoriSelect.addEventListener('change', (e) => {
                this.updateCoaDisplay();
            });
            // Initial call for old values
            if (kategoriSelect.value) {
                this.updateCoaDisplay();
            }
        }

        ['vendor', 'nominal', 'date'].forEach(field => {
            const el = document.getElementById(`dash-ocr-${field}`);
            if (el) {
                el.addEventListener('click', () => {
                    if (!this.ocrData) return;

                    let valueToFill = '';
                    let inputToFill = null;

                    if (field === 'vendor') {
                        valueToFill = this.ocrData.vendor;
                        inputToFill = this.vendorInput;
                    } else if (field === 'nominal') {
                        valueToFill = this.ocrData.nominal;
                        inputToFill = this.nominalInput;
                    } else if (field === 'date') {
                        valueToFill = this.ocrData.tanggal;
                        inputToFill = this.dateInput;
                    }

                    if (inputToFill && valueToFill) {
                        inputToFill.value = valueToFill;
                        inputToFill.dispatchEvent(new Event('input'));
                        inputToFill.dispatchEvent(new Event('change'));

                        // Visual feedback
                        inputToFill.style.backgroundColor = '#f0fdf4';
                        inputToFill.style.transition = 'all 0.3s ease';
                        setTimeout(() => inputToFill.style.backgroundColor = '', 1000);

                        this.showNotification('success', `Berhasil menyalin <b>${field}</b> dari hasil OCR + AI.`);
                        this.debounceValidation();
                    }
                });
            }
        });
    }

    updateLiveBudget() {
        const progressBar = document.getElementById('budget-progress-bar');
        const percentageText = document.getElementById('budget-percentage-text');
        const usageAmountValue = document.getElementById('budget-usage-value');
        const warningBox = document.getElementById('budget-warning-box');

        if (!progressBar || !this.nominalInput) return;

        // Get initial data from data attributes or current text
        const limitInfo = document.querySelector('.limit-info .value');
        const usageInfo = document.querySelector('.usage-info .value');

        if (!limitInfo || !usageInfo) return;

        const limit = parseInt(limitInfo.textContent.replace(/\D/g, '')) || 0;

        // Save initial usage if not saved
        if (!usageInfo.getAttribute('data-initial-usage')) {
           
            const currentVal = parseInt(usageInfo.textContent.replace(/\D/g, '')) || 0;
            const inputVal = parseInt(this.nominalInput.value.replace(/\D/g, '')) || 0;
            usageInfo.setAttribute('data-initial-usage', currentVal - inputVal);
        }

        const initialUsage = parseInt(usageInfo.getAttribute('data-initial-usage')) || 0;
        const inputNominal = parseInt(this.nominalInput.value.replace(/\D/g, '')) || 0;
        const newTotal = initialUsage + inputNominal;
        const newPercentage = limit > 0 ? Math.min(Math.round((newTotal / limit) * 100), 1000) : 0; // limit 1000% just in case

        // Update UI
        progressBar.style.width = Math.min(newPercentage, 100) + '%';
        if (percentageText) percentageText.textContent = newPercentage + '%';
        if (usageAmountValue) {
            usageAmountValue.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(newTotal);
        }

        // Update Classes/Colors
        progressBar.classList.remove('progress-success', 'progress-warning', 'progress-danger');
        if (newPercentage <= 80) {
            progressBar.classList.add('progress-success');
        } else if (newPercentage <= 100) {
            progressBar.classList.add('progress-warning');
        } else {
            progressBar.classList.add('progress-danger');
        }

        // Show/Hide Warning
        if (warningBox) {
            if (newPercentage > 100) {
                warningBox.classList.remove('d-none');
            } else {
                warningBox.classList.add('d-none');
            }
        }
    }

    getMissingFields() {
        const fields = [];
        if (!this.dateInput || !this.dateInput.value) fields.push('Tanggal Transaksi');
        if (!this.vendorInput || !this.vendorInput.value.trim()) fields.push('Nama Vendor');
        if (!this.nominalInput || !this.nominalInput.value || this.nominalInput.value === 'Rp 0') fields.push('Nominal');

        const kategori = document.getElementById('kategori_id');
        if (!kategori || !kategori.value) fields.push('Kategori');

        return fields;
    }

    highlightMissingFields(missingFields) {
        // Reset all styles first
        const elements = [
            this.dateInput,
            this.vendorInput,
            this.nominalInput,
            document.getElementById('kategori_id')
        ];

        elements.forEach(el => {
            if (el) {
                el.classList.remove('is-invalid');
                el.style.borderColor = '';
            }
        });

        // Apply invalid class
        if (missingFields.includes('Tanggal Transaksi')) this.setErrorStyle(this.dateInput);
        if (missingFields.includes('Nama Vendor')) this.setErrorStyle(this.vendorInput);
        if (missingFields.includes('Nominal')) this.setErrorStyle(this.nominalInput);
    }

    setErrorStyle(element) {
        if (!element) return;
        element.classList.add('is-invalid');
        element.style.borderColor = '#dc2626';

        // Remove error style on input
        const clearError = () => {
            element.classList.remove('is-invalid');
            element.style.borderColor = '';
            element.removeEventListener('input', clearError);
            element.removeEventListener('change', clearError);
        };

        element.addEventListener('input', clearError);
        element.addEventListener('change', clearError);
    }

    debounceValidation() {
        clearTimeout(this.validationTimeout);
        this.validationTimeout = setTimeout(() => {
            this.validateInputs();
        }, 1000);
    }

    showNotification(type, message) {
        if (window.showNotification) {
            window.showNotification(type, message);
        } else {
            // Fallback if global not loaded
            console.warn('Global showNotification not found');
            alert(message);
        }
    }

    handleCurrencyInput(e = null) {
        let value = this.nominalInput.value.replace(/\D/g, '');
        if (value) {
            this.nominalInput.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        } else {
            this.nominalInput.value = '';
        }
        this.debounceValidation();
    }

    updateCoaDisplay() {
        const coaSection = document.getElementById('recommendedCoaSection');
        const descDiv = document.getElementById('kategori-description');
        const descText = document.getElementById('description-text');

        const kategoriSelect = document.getElementById('kategori_id');
        if (!kategoriSelect || !kategoriSelect.value) {
            if (coaSection) coaSection.style.display = 'none';
            if (descDiv) descDiv.style.display = 'none';
            return;
        }

        const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];

        // Handle Description
        const description = selectedOption.getAttribute('data-description');
        if (descDiv && descText) {
            if (description && description.trim() !== '') {
                descText.textContent = description;
                descDiv.style.display = 'block';
            } else {
                descDiv.style.display = 'none';
            }
        }

        // Handle COA
        if (!coaSection) return;
        const coaCode = selectedOption.getAttribute('data-coa-code');
        const coaName = selectedOption.getAttribute('data-coa-name');

        const codeEl = document.getElementById('recommendedCoaCode');
        const nameEl = document.getElementById('recommendedCoaName');
        const titleEl = document.getElementById('coaSectionTitle');
        const descEl = document.getElementById('coaSectionDesc');
        const iconWrapper = document.getElementById('coaIconWrapper');
        const iconEl = document.getElementById('coaIcon');

        if (coaCode && coaName) {
            if (codeEl) codeEl.textContent = coaCode;
            if (nameEl) nameEl.textContent = coaName;
            coaSection.style.display = 'block';

            // Standard appearance
            if (titleEl) titleEl.textContent = 'Akun Akuntansi (COA)';
            if (descEl) descEl.textContent = 'Berdasarkan kategori yang Anda pilih.';

            coaSection.style.background = '#f8fafc';
            coaSection.style.borderColor = '#e2e8f0';
            if (titleEl) titleEl.style.color = '#64748b';
            if (descEl) descEl.style.color = '#94a3b8';
            if (iconWrapper) {
                iconWrapper.style.background = '#f1f5f9';
                iconWrapper.style.color = '#64748b';
            }
            if (iconEl) {
                iconEl.innerHTML = '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>';
            }
        } else {
            coaSection.style.display = 'none';
        }

        // Trigger animation
        coaSection.style.animation = 'none';
        coaSection.offsetHeight;
        coaSection.style.animation = 'coa-pulse 1s ease-in-out';
    }

    handleFormSubmit(e) {
        let rawValue = this.nominalInput.value.replace(/\D/g, '');
        this.nominalInput.value = rawValue;
    }

    async handleFileChange(e) {
        const file = this.fileInput.files[0];
        if (!file) return;

        this.showPreview(file);

        const debugArea = document.getElementById('debug-raw-ocr');
        if (debugArea) debugArea.value = '';

        if (this.dashboard) this.dashboard.style.display = 'block';

        // Use Global Loader for better UX (More Obvious)
        const globalLoader = document.getElementById('global-loader');
        const globalLoaderText = globalLoader ? globalLoader.querySelector('span') : null;

        if (globalLoader) {
            globalLoader.style.opacity = '1';
            globalLoader.style.pointerEvents = 'auto';
            if (globalLoaderText) globalLoaderText.textContent = 'Tahap 1/2: Ekstraksi OCR dokumen...';
        }

        const summaryEl = document.getElementById('validationSummary');
        if (summaryEl) {
            summaryEl.innerHTML = `<div class="validation-loading" style="display:flex; align-items:center; gap:0.5rem; color:#667eea;">${Icons.eye}<span> Tahap 1/2: OCR membaca teks dokumen...</span></div>`;
        }

        // Disable button and show processing state
        if (this.submitBtn) {
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = `${Icons.loading} Memproses...`;
        }

        let ocrText = '';

        if (file.type.startsWith('image/') || file.type === 'application/pdf') {
            try {
                if (file.type === 'application/pdf') {
                    if (globalLoaderText) globalLoaderText.textContent = 'Tahap 1/2: OCR PDF (konversi + ekstraksi teks)...';
                    if (summaryEl) {
                        summaryEl.innerHTML = `<div class="validation-loading" style="display:flex; align-items:center; gap:0.5rem; color:#667eea;">${Icons.file}<span> Tahap 1/2: OCR PDF sedang berjalan...</span></div>`;
                    }
                } else {
                    if (globalLoaderText) globalLoaderText.textContent = 'Tahap 1/2: OCR gambar (membaca teks)...';
                    if (summaryEl) {
                        summaryEl.innerHTML = `<div class="validation-loading" style="display:flex; align-items:center; gap:0.5rem; color:#667eea;">${Icons.eye}<span> Tahap 1/2: OCR membaca teks gambar...</span></div>`;
                    }
                }

                const ocrResult = await this.tesseract.recognizeImage(file);
                if (ocrResult.success) {
                    ocrText = ocrResult.rawText;
                    const ocrTextInput = document.getElementById('ocr_text');
                    if (ocrTextInput) ocrTextInput.value = ocrText;
                    if (debugArea) debugArea.value = ocrText;

                    // --- CLIENT SIDE QUALITY CHECK ---
                    if (ocrText.trim().length < 20) {
                        this.showNotification('warning', `<b>${Icons.warning} Gambar Kurang Jelas</b><br>Teks yang terbaca sangat sedikit. Mohon pastikan foto struk terlihat jelas dan terang agar tidak ditolak oleh sistem.`);
                    }
                }
            } catch (err) {
                console.error('OCR Client Error:', err);
                // Don't block submission if OCR fails on client side, just log it
            }
        }

        if (globalLoaderText) globalLoaderText.textContent = 'Tahap 2/2: Analisis AI (anomali, fraud, dan validasi data)...';
        if (summaryEl) {
            summaryEl.innerHTML = `<div class="validation-loading" style="display:flex; align-items:center; gap:0.5rem; color:#667eea;">${Icons.ai}<span> Tahap 2/2: AI menganalisis hasil OCR...</span></div>`;
        }

        const formData = new FormData();
        formData.append('file_bukti', file);
        formData.append('ocr_text', ocrText);

        // Add transaction type context if selected
        const tipeTransaksi = document.getElementById('jenis_transaksi')?.value;
        if (tipeTransaksi) {
            formData.append('jenis_transaksi', tipeTransaksi);
        }

        const route = typeof OCR_ROUTES !== 'undefined' ? OCR_ROUTES.process : '/pegawai/validasi-ai/process-file';

        fetch(route, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Server error: ' + response.status);
                    });
                }
                return response.json();
            })
            .then(data => {
                // Hide global loader on finish
                const globalLoader = document.getElementById('global-loader');
                if (globalLoader) {
                    globalLoader.style.opacity = '0';
                    globalLoader.style.pointerEvents = 'none';
                }

                if (data.is_duplicate) {
                    document.getElementById('duplicateAlert').style.display = 'block';
                    document.getElementById('validationSummary').innerHTML = `<div style="color: #dc2626; font-weight: 600;">${Icons.block} File Duplikat: File ini sudah pernah diajukan sebelumnya. Tidak dapat upload ulang.</div>`;
                    if (this.submitBtn) {
                        this.submitBtn.disabled = true;
                        this.submitBtn.innerHTML = 'Ajukan';
                    }
                    return;
                } else {
                    document.getElementById('duplicateAlert').style.display = 'none';
                }

                if (data.success) {
                    this.ocrData = data.ocr_data;

                    // --- QUALITY CHECK NOTIFICATION ---
                    if (this.ocrData.confidence_score < 70 || (this.ocrData.sanity_check_notes && this.ocrData.sanity_check_notes.toLowerCase().includes('buram'))) {
                        this.showNotification('warning', `
                        <b>${Icons.warning} Kualitas Gambar Kurang Optimal</b><br>
                        AI kesulitan membaca struk ini (Keyakinan: ${this.ocrData.confidence_score}%).<br>
                        Saran: Pastikan foto terang, fokus, dan tidak terpotong agar validasi lebih akurat.
                    `);
                    }

                    // Smart Auto-Fill DISABLED for Security (Wajib input manual dulu)
                    /* 
                    if (!this.vendorInput.value.trim() && this.ocrData.vendor) {
                        this.vendorInput.value = this.ocrData.vendor;
                        this.vendorInput.style.backgroundColor = '#dcfce7';
                        setTimeout(() => this.vendorInput.style.backgroundColor = '', 2000);
                    }
    
                    if ((!this.nominalInput.value || this.nominalInput.value === 'Rp 0') && this.ocrData.nominal) {
                        this.nominalInput.value = this.ocrData.nominal;
                        this.nominalInput.dispatchEvent(new Event('input'));
                        this.nominalInput.style.backgroundColor = '#dcfce7';
                        setTimeout(() => this.nominalInput.style.backgroundColor = '', 2000);
                    }
    
                    if (!this.dateInput.value && this.ocrData.tanggal) {
                        this.dateInput.value = this.ocrData.tanggal;
                        this.dateInput.style.backgroundColor = '#dcfce7';
                        setTimeout(() => this.dateInput.style.backgroundColor = '', 2000);
                    }
                    */

                    // Auto-select transaction type based on platform
                    const typeSelect = document.getElementById('jenis_transaksi');
                    if (typeSelect && this.ocrData.platform) {
                        const p = this.ocrData.platform.toLowerCase();
                        if (['tokopedia', 'shopee', 'lazada', 'bukalapak', 'tiktok'].some(m => p.includes(m))) {
                            typeSelect.value = 'marketplace';
                        } else if (['gojek', 'grab'].some(m => p.includes(m))) {
                            typeSelect.value = 'transport';
                        } else if (['qris', 'dana', 'ovo', 'gopay', 'linkaja', 'shopeepay'].some(m => p.includes(m))) {
                            typeSelect.value = 'transfer_direct';
                        }
                    }

                    // Save complete OCR data to hidden input
                    const jsonInput = document.getElementById('ocr_data_json');
                    if (jsonInput) {
                        jsonInput.value = JSON.stringify(data.ocr_data);
                    }

                    let vendorDisplay = this.ocrData.vendor || '-';
                    if (this.ocrData.platform) {
                        vendorDisplay += ' (' + this.ocrData.platform + ')';
                    }

                    document.getElementById('dash-ocr-vendor').textContent = vendorDisplay;
                    document.getElementById('dash-ocr-nominal').textContent = this.ocrData.nominal ? 'Rp ' + new Intl.NumberFormat('id-ID').format(this.ocrData.nominal) : '-';

                    // Date formatting with cleanup
                    let dateDisplay = '-';
                    if (this.ocrData.tanggal) {
                        // Clean up the date string first (remove extra dashes/strips)
                        let cleanedDate = this.ocrData.tanggal;

                        // Remove excessive dashes (more than 2 consecutive dashes)
                        cleanedDate = cleanedDate.replace(/-{3,}/g, '--');

                        // Fix date patterns like "2025--01-14"
                        cleanedDate = cleanedDate.replace(/--/g, '-');

                        // Remove dashes at start or end
                        cleanedDate = cleanedDate.replace(/^[-\s]+|[-\s]+$/g, '');

                        // Fix spaces in date like "2025 01 14"
                        if (cleanedDate.match(/^\d{4}\s+\d{1,2}\s+\d{1,2}$/)) {
                            cleanedDate = cleanedDate.replace(/\s+/g, '-');
                        }

                        // Try to format YYYY-MM-DD to Indonesian format DD MMMM YYYY
                        try {
                            const dateObj = new Date(cleanedDate);
                            if (!isNaN(dateObj)) {
                                dateDisplay = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(dateObj);
                                // Also append raw (YYYY-MM-DD) for clarity
                                dateDisplay += ` (${cleanedDate})`;
                            } else {
                                // If parsing fails, show cleaned raw date
                                dateDisplay = cleanedDate;
                            }
                        } catch (e) {
                            // If error, show cleaned raw date
                            dateDisplay = cleanedDate || this.ocrData.tanggal;
                        }
                    }
                    document.getElementById('dash-ocr-date').textContent = dateDisplay;

                    // --- SMART COA RECOMMENDATION (ULTRA SMART AI) ---
                    this.updateCoaDisplay();

                    // Update Ultra Smart Summary UI
                    this.updateUltraSmartSummary(data.ocr_data);

                    // Smart Category Suggestion
                    if (data.ocr_data.suggested_category) {
                        const kategoriSelect = document.getElementById('kategori_id');
                        if (kategoriSelect) {
                            // Find option that matches suggested category name
                            const suggestion = data.ocr_data.suggested_category.toLowerCase();
                            let found = false;

                            for (let i = 0; i < kategoriSelect.options.length; i++) {
                                const optionText = kategoriSelect.options[i].text.toLowerCase();
                                if (optionText.includes(suggestion) || suggestion.includes(optionText)) {
                                    kategoriSelect.selectedIndex = i;
                                    kategoriSelect.dispatchEvent(new Event('change'));
                                    found = true;
                                    break;
                                }
                            }

                            if (found) {
                                // Visual feedback for auto-selection
                                kategoriSelect.style.backgroundColor = '#dcfce7';
                                kategoriSelect.style.transition = 'background-color 0.5s ease';
                                setTimeout(() => {
                                    kategoriSelect.style.backgroundColor = '';
                                }, 2000);
                            }
                        }
                    }

                    // Handle multi-invoice detection
                    if (data.multi_invoice) {
                        this.handleMultiInvoice(data.multi_invoice);
                    }

                    this.showNotification('success', `<b>Validasi dokumen selesai</b><br>Tahap 1 OCR dan Tahap 2 analisis AI berhasil dijalankan.`);
                    this.validateInputs();
                } else {
                    document.getElementById('validationSummary').innerHTML = `<div style="color: #dc2626; font-weight: 600;">${Icons.block} Validasi AI Gagal: ` + (data.message || 'Silakan periksa file dan coba upload ulang.') + '</div>';
                    if (this.submitBtn) {
                        this.submitBtn.disabled = true;
                        this.submitBtn.innerHTML = 'Ajukan';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);

                // Hide global loader on error
                const globalLoader = document.getElementById('global-loader');
                if (globalLoader) {
                    globalLoader.style.opacity = '0';
                    globalLoader.style.pointerEvents = 'none';
                }

                document.getElementById('validationSummary').innerHTML = `<div style="color: #dc2626; font-weight: 600;">${Icons.block} Validasi Gagal: Terjadi kesalahan pada layanan AI. Silakan muat ulang halaman dan coba lagi.</div>`;
                if (this.submitBtn) {
                    this.submitBtn.disabled = true;
                    this.submitBtn.innerHTML = 'Ajukan';
                }
            });
    }

    updateUltraSmartSummary(ocrData) {
        const summaryContainer = document.getElementById('ultra-smart-summary');
        if (!summaryContainer) return;

        summaryContainer.style.display = 'block';

        // 1. Populate Items List
        const itemsList = document.getElementById('smart-items-list');
        if (itemsList && ocrData.items && ocrData.items.length > 0) {
            let itemsHtml = '<ul style="padding: 0; margin: 0; list-style-type: none;">';
            ocrData.items.forEach(item => {
                const price = item.price ? ' @ Rp' + new Intl.NumberFormat('id-ID').format(item.price) : '';
                const personalBadge = item.is_personal ? ' <span style="font-size: 0.65rem; padding: 1px 4px; background: #fee2e2; color: #991b1b; border-radius: 4px; font-weight: 700;">Personal</span>' : '';
                const categoryBadge = item.category ? ` <span style="font-size: 0.6rem; color: #64748b; font-weight: 500;">[${item.category}]</span>` : '';

                itemsHtml += `<li style="margin-bottom: 0.4rem; list-style: none; display: flex; align-items: flex-start; gap: 0.4rem;">
                    <span style="color: #4f46e5; font-weight: 800; min-width: 20px;">${item.qty || 1}x</span> 
                    <div style="flex: 1;">
                        <span style="font-weight: 600; color: #1e293b;">${item.name}</span>${price}${personalBadge}${categoryBadge}
                    </div>
                </li>`;
            });
            itemsHtml += '</ul>';
            itemsList.innerHTML = itemsHtml;
        } else if (itemsList) {
            itemsList.innerHTML = '<span style="color: #94a3b8; font-style: italic;">Tidak ada rincian item terbaca.</span>';
        }

        // 2. Fraud Risk Score & Note
        const scoreEl = document.getElementById('smart-fraud-score');
        const noteEl = document.getElementById('smart-fraud-note');
        if (scoreEl && noteEl) {
            const score = ocrData.fraud_risk_score || 0;
            scoreEl.innerHTML = `Risk Score: <span style="font-size: 1.2rem;">${score}</span>/100`;
            noteEl.textContent = ocrData.sanity_check_notes || 'Tidak ada anomali terdeteksi.';

            // Color coding based on score
            const parent = scoreEl.parentElement;
            if (score < 30) {
                scoreEl.style.color = '#16a34a'; // Green
                parent.style.background = '#f0fdf4';
                parent.style.borderColor = '#bbf7d0';
            } else if (score < 70) {
                scoreEl.style.color = '#d97706'; // Orange
                parent.style.background = '#fffbeb';
                parent.style.borderColor = '#fef3c7';
            } else {
                scoreEl.style.color = '#dc2626'; // Red
                parent.style.background = '#fef2f2';
                parent.style.borderColor = '#fecdd3';
            }
        }

        // 3. Category Badge
        const badgeEl = document.getElementById('smart-category-badge');
        if (badgeEl) {
            badgeEl.textContent = ocrData.suggested_category || 'Lainnya';
        }

        // 4. Policy Violations
        if (ocrData.policy_violations && ocrData.policy_violations.length > 0) {
            const violationList = ocrData.policy_violations.map(v => {
                if (typeof v === 'object' && v !== null) {
                    return v.item || v.reason || v.description || 'Pelanggaran';
                }
                return v;
            });

            const violationHtml = `<div style="margin-top: 0.75rem; padding: 0.5rem; background: #fff1f2; border: 1px solid #fda4af; border-radius: 0.5rem; color: #be123c; font-size: 0.75rem;">
                <div style="font-weight: 700; margin-bottom: 0.2rem;">⚠️ Pelanggaran Kebijakan:</div>
                <ul style="margin: 0; padding-left: 1.2rem;">
                    ${violationList.map(v => `<li>${v}</li>`).join('')}
                </ul>
            </div>`;
            itemsList.insertAdjacentHTML('beforeend', violationHtml);
        }

        // 5. Accounting Split Visualization
        if (ocrData.accounting_split && ocrData.accounting_split.length > 0) {
            let splitHtml = `<div style="margin-top: 0.75rem; padding: 0.6rem; background: #f1f5f9; border-radius: 0.5rem; border: 1px dashed #cbd5e1;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 0.3rem;">Alokasi Akuntansi (Split):</div>`;

            ocrData.accounting_split.forEach(split => {
                const amount = split.amount ? 'Rp ' + new Intl.NumberFormat('id-ID').format(split.amount) : 'N/A';
                splitHtml += `<div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #1e293b; margin-bottom: 0.1rem;">
                    <span style="font-weight: 600;">${split.category}</span>
                    <span>${amount}</span>
                </div>`;
            });
            splitHtml += `</div>`;
            itemsList.insertAdjacentHTML('beforeend', splitHtml);
        }

        // 6. Personal Items Warning
        const personalItems = ocrData.items ? ocrData.items.filter(item => item.is_personal) : [];
        if (personalItems.length > 0) {
            const totalPersonal = personalItems.reduce((sum, item) => sum + ((item.price || 0) * (item.qty || 1)), 0);
            if (totalPersonal > 0) {
                const personalWarningHtml = `<div style="margin-top: 0.75rem; padding: 0.5rem; background: #fff7ed; border: 1px solid #ffedd5; border-radius: 0.5rem; color: #9a3412; font-size: 0.75rem;">
                    <div style="font-weight: 700; margin-bottom: 0.1rem;">💡 Info Pengeluaran Pribadi:</div>
                    Terdapat total <strong>Rp ${new Intl.NumberFormat('id-ID').format(totalPersonal)}</strong> item personal. Pastikan nominal yang Anda ajukan sudah dikurangi jumlah ini.
                </div>`;
                itemsList.insertAdjacentHTML('beforeend', personalWarningHtml);
            }
        }

        // 7. Confidence Score & Reason
        const confScoreEl = document.getElementById('smart-confidence-score');
        const confReasonEl = document.getElementById('smart-confidence-reason');
        if (confScoreEl && confReasonEl) {
            const conf = ocrData.confidence_score || 0;
            confScoreEl.innerHTML = `Confidence: <span style="font-size: 1.2rem;">${conf}</span>%`;
            confReasonEl.textContent = ocrData.confidence_reason || 'Berdasarkan analisis struktur dokumen.';

            // Color coding for confidence
            if (conf >= 80) {
                confScoreEl.style.color = '#16a34a'; // Green
            } else if (conf >= 50) {
                confScoreEl.style.color = '#d97706'; // Orange
            } else {
                confScoreEl.style.color = '#dc2626'; // Red
            }
        }
    }

    handleMultiInvoice(multiInvoiceData) {
        const container = document.getElementById('multi-invoice-selector');
        const optionsContainer = document.getElementById('multi-invoice-options');

        if (!container || !optionsContainer) return;

        if (!multiInvoiceData || !multiInvoiceData.has_multiple) {
            container.style.display = 'none';
            optionsContainer.innerHTML = '';
            return;
        }

        container.style.display = 'block';
        optionsContainer.innerHTML = '';

        multiInvoiceData.amounts.forEach((item, index) => {
            const wrapper = document.createElement('div');
            wrapper.style.marginBottom = '6px';
            wrapper.style.display = 'flex';
            wrapper.style.alignItems = 'center';

            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'invoice_amount_selection';
            radio.id = `amount_${index}`;
            radio.value = item.value;
            radio.style.marginRight = '8px';
            radio.style.cursor = 'pointer';

            // Select if it's the recommended one or matches current input
            const currentVal = parseInt(this.nominalInput.value.replace(/\D/g, '')) || 0;
            // Pre-select if matches current value or if it's the recommended one (and current is empty/OCR default)
            const isRecommended = multiInvoiceData.recommended && item.value === multiInvoiceData.recommended.value;

            if (item.value === currentVal || (isRecommended && this.ocrData.nominal === item.value)) {
                radio.checked = true;
            }

            // Disable auto-fill from radio buttons to enforce manual input integrity
            /* 
            radio.addEventListener('change', () => {
                if (radio.checked) {
                    this.nominalInput.value = item.value;
                    this.nominalInput.dispatchEvent(new Event('input'));
                    
                    this.nominalInput.style.backgroundColor = '#dcfce7';
                    setTimeout(() => {
                        this.nominalInput.style.backgroundColor = '';
                    }, 500);
                }
            });
            */

            // Add comparison logic instead of auto-fill
            radio.addEventListener('change', () => {
                if (radio.checked) {
                    this.debounceValidation();
                }
            });

            const label = document.createElement('label');
            label.htmlFor = `amount_${index}`;
            label.textContent = item.display;
            label.style.cursor = 'pointer';
            label.style.fontSize = '0.9rem';
            label.style.color = '#334155';

            if (item.priority === 1) {
                const badge = document.createElement('span');
                badge.textContent = 'Rekomendasi';
                badge.style.backgroundColor = '#dcfce7';
                badge.style.color = '#166534';
                badge.style.fontSize = '0.75rem';
                badge.style.padding = '2px 6px';
                badge.style.borderRadius = '4px';
                badge.style.marginLeft = '8px';
                label.appendChild(badge);
            }

            wrapper.appendChild(radio);
            wrapper.appendChild(label);
            optionsContainer.appendChild(wrapper);
        });
    }

    validateInputs() {
        if (!this.ocrData) return;

        const inputVendor = this.vendorInput.value.trim();
        const inputNominal = parseInt(this.nominalInput.value.replace(/\D/g, '')) || 0;
        const inputDate = this.dateInput.value;

        document.getElementById('dash-input-vendor').textContent = inputVendor || '-';
        document.getElementById('dash-input-nominal').textContent = inputNominal ? 'Rp ' + new Intl.NumberFormat('id-ID').format(inputNominal) : '-';
        document.getElementById('dash-input-date').textContent = inputDate || '-';

        // Hanya validasi ke server jika semua input terisi
        if (!inputVendor || !inputNominal || !inputDate) {
            this.updateDashboardToWaiting();
            return;
        }

        const route = typeof OCR_ROUTES !== 'undefined' ? OCR_ROUTES.validate : '/pegawai/validasi-ai/validate-input';

        fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ocr_data: this.ocrData,
                nama_vendor: inputVendor,
                nominal: inputNominal,
                tanggal_transaksi: inputDate
            })
        })
            .then(response => {
                if (!response.ok) {
                    // Handle validation errors (422) or server errors
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                this.updateDashboardStatus(data);
            })
            .catch(error => {
                console.error('Validation error:', error);
            });
    }

    updateDashboardToWaiting() {
        const waitingHtml = '<span style="color: #9ca3af; font-style: italic; font-size: 0.9em;">Menunggu input...</span>';
        document.getElementById('dash-status-vendor').innerHTML = waitingHtml;
        document.getElementById('dash-status-nominal').innerHTML = waitingHtml;
        document.getElementById('dash-status-date').innerHTML = waitingHtml;
        const catStatus = document.getElementById('dash-status-category');
        if (catStatus) catStatus.innerHTML = waitingHtml;
        const catOcr = document.getElementById('dash-ocr-context');
        if (catOcr) catOcr.innerHTML = waitingHtml;

        document.getElementById('validationSummary').innerHTML = `<div style="color: #6b7280; font-size: 0.9em;">${Icons.info} Lengkapi form untuk melihat hasil validasi OCR + AI.</div>`;

        if (this.submitBtn) {
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = 'Ajukan';
        }
    }

    updateDashboardStatus(data) {
        const matches = data.matches;
        const canSubmit = data.can_submit;
        const issues = data.issues;

        // Vendor Status
        const vendorStatus = document.getElementById('dash-status-vendor');
        if (matches.vendor.status === 'pass') {
            vendorStatus.innerHTML = Icons.check + ' Cocok (' + matches.vendor.match_percentage + '%)';
            vendorStatus.style.color = '#059669';
        } else if (matches.vendor.status === 'warning') {
            vendorStatus.innerHTML = Icons.warning + ' Mirip (' + matches.vendor.match_percentage + '%)';
            vendorStatus.style.color = '#d97706';
        } else if (matches.vendor.status === 'fail') {
            vendorStatus.innerHTML = Icons.cross + ' Tidak Cocok (' + matches.vendor.match_percentage + '%)';
            vendorStatus.style.color = '#dc2626';
        }

        // Nominal Status
        const nominalStatus = document.getElementById('dash-status-nominal');
        const nominalOcrDisplay = document.getElementById('dash-ocr-nominal');

        const resetOcrDisplay = () => {
            if (this.ocrData) {
                nominalOcrDisplay.textContent = this.ocrData.nominal ? 'Rp ' + new Intl.NumberFormat('id-ID').format(this.ocrData.nominal) : '-';
            }
        };

        if (matches.nominal.status === 'pass') {
            nominalStatus.style.color = '#059669';

            // Check if it's an alternative match found in raw text
            const isAltMatch = matches.nominal.message && (matches.nominal.message.toLowerCase().includes('ditemukan') || matches.nominal.message.toLowerCase().includes('found'));

            if (isAltMatch) {
                nominalStatus.innerHTML = Icons.warning + ' Cocok (Bukan Total Akhir)';
                nominalStatus.style.color = '#d97706';

                // Update OCR column to show the value we matched against
                const currentInput = document.getElementById('dash-input-nominal').textContent;
                nominalOcrDisplay.innerHTML = currentInput + ' <span style="font-size:0.75em; color:#64748b; font-weight:normal;">(Item di Struk)</span>';
                nominalOcrDisplay.title = matches.nominal.message;
            } else {
                nominalStatus.innerHTML = Icons.check + ' Cocok (Total Akhir)';
                resetOcrDisplay();
            }
        } else if (matches.nominal.status === 'fail') {
            nominalStatus.innerHTML = Icons.cross + ' Tidak Cocok';
            nominalStatus.style.color = '#dc2626';
            resetOcrDisplay();
        }

        // Date Status
        const dateStatus = document.getElementById('dash-status-date');
        if (matches.tanggal.is_too_old) {
            dateStatus.innerHTML = `${Icons.cross} Kadaluwarsa`;
            dateStatus.style.color = '#dc2626';
            dateStatus.title = `Maksimal usia struk adalah ${matches.tanggal.max_age} hari.`;
        } else if (matches.tanggal.status === 'pass') {
            dateStatus.innerHTML = `${Icons.check} Cocok`;
            dateStatus.style.color = '#059669';
        } else if (matches.tanggal.status === 'warning') {
            dateStatus.innerHTML = `${Icons.warning} Beda`;
            dateStatus.style.color = '#d97706';
        } else {
            dateStatus.innerHTML = `${Icons.cross} Tidak Cocok`;
            dateStatus.style.color = '#dc2626';
        }

        // Summary & Button Logic
        const summary = document.getElementById('validationSummary');
        const duplicateAlert = document.getElementById('duplicateAlert');

        // Strict rules: Nominal & Date MUST match exactly, Vendor must NOT be fail
        const nominalMatches = matches.nominal.status === 'pass';
        const dateMatches = matches.tanggal.status === 'pass';
        const vendorMatches = matches.vendor.status !== 'fail'; // Allow pass or warning

        const allowSubmit = nominalMatches && dateMatches && vendorMatches && duplicateAlert.style.display === 'none';

        // --- NEW LOGIC FOR MULTIPLE INVOICE / CANDIDATE ---
        // If nominal is just a warning (e.g. matched one of the candidates but not the primary one), we allow it.
        // Usually the backend returns 'pass' if it matches ANY candidate.
        // But if we want to be explicit:
        if (matches.nominal.status === 'pass') {
            // Logic already handled above. Pass is good.
        }

        if (allowSubmit) {
            let anomalyWarning = '';
            if (matches.anomali && matches.anomali.is_anomaly) {
                anomalyWarning = `<div style="margin-top: 8px; color: #d97706; font-size: 0.9em; border-top: 1px solid #fed7aa; padding-top: 8px;">
                    ${Icons.warning} <b>Catatan Keamanan:</b> ${matches.anomali.reason}
                </div>`;
            }
            summary.innerHTML = `<div style="color: #059669; font-weight: 600;">${Icons.check} Validasi Lolos. Silakan ajukan.</div>` + anomalyWarning;
            this.submitBtn.disabled = false;
            this.submitBtn.innerHTML = 'Ajukan Sekarang';
        } else {
            let errorList = [];

            if (!nominalMatches) {
                errorList.push(`${Icons.cross} <b>Nominal tidak cocok</b>`);
                errorList.push(`<span style="color: #64748b; font-size: 0.85em; margin-left: 24px;">💡 Pastikan nominal yang Anda input sama persis dengan total di struk.</span>`);
                // Show what OCR detected
                if (this.ocrData && this.ocrData.nominal) {
                    errorList.push(`<span style="color: #64748b; font-size: 0.85em; margin-left: 24px;">📄 OCR + AI membaca: <b>Rp ${new Intl.NumberFormat('id-ID').format(this.ocrData.nominal)}</b></span>`);
                }
                // Multi-invoice hint
                if (this.ocrData && this.ocrData.all_detected_totals && this.ocrData.all_detected_totals.length > 1) {
                    errorList.push(`<span style="color: #f59e0b; font-size: 0.85em; margin-left: 24px;">⚠️ Ada beberapa total di struk. Pilih salah satu yang sesuai.</span>`);
                }
            }

            if (!dateMatches) {
                if (matches.tanggal.is_too_old) {
                    errorList.push(`${Icons.cross} <b>STRUK KADALUWARSA</b>`);
                    errorList.push(`<span style="color: #64748b; font-size: 0.85em; margin-left: 24px;">⏱️ Struk terlalu lama (maksimal ${matches.tanggal.max_age} hari).`);
                    errorList.push(`<span style="color: #64748b; font-size: 0.85em; margin-left: 24px;">💡 Pastikan transaksi dilakukan dalam 15 hari terakhir.</span>`);
                } else {
                    errorList.push(`${Icons.cross} <b>Tanggal tidak cocok</b>`);
                    errorList.push(`<span style="color: #64748b; font-size: 0.85em; margin-left: 24px;">💡 Pastikan tanggal yang Anda input sama persis dengan tanggal di struk.</span>`);
                    if (this.ocrData && this.ocrData.tanggal) {
                        errorList.push(`<span style="color: #64748b; font-size: 0.85em; margin-left: 24px;">📄 OCR + AI membaca: <b>${this.ocrData.tanggal}</b></span>`);
                    }
                }
            }

            if (!vendorMatches) {
                const percentage = matches.vendor.match_percentage || 0;
                errorList.push(`${Icons.cross} <b>Nama vendor tidak cocok</b> (Kemiripan: ${percentage}%)`);
                errorList.push(`<span style="color: #64748b; font-size: 0.85em; margin-left: 24px;">💡 Gunakan nama yang persis seperti di struk (minimal 75% mirip).</span>`);

                if (this.ocrData && this.ocrData.vendor) {
                    errorList.push(`<span style="color: #64748b; font-size: 0.85em; margin-left: 24px;">📄 OCR + AI membaca: <b>${this.ocrData.vendor}</b></span>`);
                }
                errorList.push(`<span style="color: #64748b; font-size: 0.85em; margin-left: 24px;">💡 Contoh: "Tokopedia" bukan "Toko Ped", "Gojek" bukan "Go Jek"</span>`);
            }

            // General help
            errorList.push(`<div style="margin-top: 12px; padding: 10px; background: #f8fafc; border-radius: 6px; border-left: 3px solid #3b82f6;">
                <b style="color: #3b82f6;">📌 Tips:</b>
                <ul style="margin: 6px 0 0 0; padding-left: 20px; color: #64748b; font-size: 0.85em;">
                    <li>Pastikan foto struk terang dan tidak buram</li>
                    <li>Struk tidak terpotong dan semua teks terlihat jelas</li>
                    <li>Input data sesuai dengan yang tertera di struk</li>
                </ul>
            </div>`);

            summary.innerHTML = `<div style="color: #dc2626; font-weight: 600; margin-bottom: 8px;">${Icons.block} VALIDASI GAGAL</div>` + errorList.join('<br>');
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = 'Ajukan';
        }
    }

    showPreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const uploadContent = document.getElementById('uploadContent');
            if (uploadContent) uploadContent.style.display = 'none';

            document.getElementById('filePreview').style.display = 'block';
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(2) + ' KB';

            document.getElementById('previewBtn').onclick = () => {
                this.openFileModal(e.target.result, file.name);
            };

            document.getElementById('removeFile').onclick = () => {
                this.fileInput.value = '';
                document.getElementById('filePreview').style.display = 'none';
                if (uploadContent) uploadContent.style.display = 'block';

                if (this.dashboard) this.dashboard.style.display = 'none';
                const smartSummary = document.getElementById('ultra-smart-summary');
                if (smartSummary) smartSummary.style.display = 'none';
                this.ocrData = null;
                if (this.submitBtn) {
                    this.submitBtn.disabled = true;
                    this.submitBtn.innerHTML = 'Ajukan';
                }
            };
        };
        reader.readAsDataURL(file);
    }

    openFileModal(dataUrl, fileName) {
        const modal = document.getElementById('fileModal');
        const modalBody = document.getElementById('modalBody');
        const modalTitle = document.getElementById('modalFileName');

        if (!modal || !modalBody || !modalTitle) {
            console.error('Modal elements not found');
            return;
        }

        modalTitle.textContent = fileName;
        modalBody.innerHTML = '';

        const ext = fileName.split('.').pop().toLowerCase();

        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
            const img = document.createElement('img');
            img.src = dataUrl;
            img.className = 'file-preview-image';
            img.alt = fileName;
            modalBody.appendChild(img);
        } else if (ext === 'pdf') {
            if (typeof pdfjsLib !== 'undefined') {
                this.displayPdfWithPdfJs(dataUrl, modalBody);
            } else {
                const iframe = document.createElement('iframe');
                iframe.src = dataUrl;
                iframe.className = 'file-preview-pdf';
                iframe.style.border = 'none';
                iframe.style.width = '100%';
                iframe.style.height = '600px';
                iframe.title = fileName;
                iframe.setAttribute('sandbox', 'allow-same-origin');
                modalBody.appendChild(iframe);
            }
        } else {
            modalBody.innerHTML = '<p style="color: #667eea; text-align: center;">Format file tidak didukung untuk preview</p>';
        }

        console.log('Adding show class to modal');
        modal.classList.add('show');
        console.log('Modal classes:', modal.className);
    }

    closeFileModal() {
        const modal = document.getElementById('fileModal');
        modal.classList.remove('show');
    }

    async displayPdfWithPdfJs(dataUrl, container) {
        try {
            const pdf = await pdfjsLib.getDocument(dataUrl).promise;
            const page = await pdf.getPage(1);

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            const viewport = page.getViewport({ scale: 2 });

            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;

            canvas.className = 'file-preview-image';
            canvas.style.maxWidth = '100%';
            canvas.style.height = 'auto';
            container.appendChild(canvas);

            if (pdf.numPages > 1) {
                const pageInfo = document.createElement('p');
                pageInfo.style.cssText = 'text-align: center; color: #64748b; font-size: 0.85rem; margin-top: 1rem;';
                pageInfo.textContent = `Halaman 1 dari ${pdf.numPages}`;
                container.appendChild(pageInfo);
            }
        } catch (err) {
            console.error('PDF.js Error:', err);
            const errorMsg = document.createElement('p');
            errorMsg.style.cssText = 'color: #dc2626; text-align: center;';
            errorMsg.textContent = 'Gagal menampilkan PDF. Silakan coba download file secara langsung.';
            container.appendChild(errorMsg);
        }
    }
}

// --- INITIALIZATION ---
let pengajuanManagerInstance = null;

function initPengajuanForm() {
    // Check if we are on a page with the pengajuan form
    const formElement = document.querySelector('.form-pengajuan') || document.getElementById('file_bukti');

    if (formElement) {
        console.log('[Pengajuan] Form detected, initializing manager...');

        // Prevent double initialization if the script runs multiple times
        if (pengajuanManagerInstance) {
            console.log('[Pengajuan] Cleaning up previous instance...');
            // Optional: call a destroy method if you have complex cleanup
        }

        pengajuanManagerInstance = new PengajuanFormManager();
    }
}

// Listen for both standard load and Livewire navigation
document.addEventListener('DOMContentLoaded', initPengajuanForm);
document.addEventListener('livewire:navigated', initPengajuanForm);

// Global UI listeners (Only once)
if (!window.pengajuanListenersAttached) {
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('fileModal');
            if (modal && modal.classList.contains('show')) {
                modal.classList.remove('show');
            }
        }
    });
    window.pengajuanListenersAttached = true;
}

function closeFileModal() {
    const modal = document.getElementById('fileModal');
    if (modal) modal.classList.remove('show');
}

function openFileModal(dataUrl, fileName) {
    const modal = document.getElementById('fileModal');
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalFileName');

    if (!modal || !modalBody || !modalTitle) return;

    modalTitle.textContent = fileName;
    modalBody.innerHTML = '';

    const ext = fileName.split('.').pop().toLowerCase();

    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
        const img = document.createElement('img');
        img.src = dataUrl;
        img.className = 'file-preview-image';
        img.alt = fileName;
        modalBody.appendChild(img);
    } else if (ext === 'pdf') {
        const iframe = document.createElement('iframe');
        iframe.src = dataUrl;
        iframe.className = 'file-preview-pdf';
        iframe.title = fileName;
        modalBody.appendChild(iframe);
    } else {
        modalBody.innerHTML = '<p style="color: #667eea; text-align: center;">Format file tidak didukung untuk preview</p>';
    }

    modal.classList.add('show');
}
