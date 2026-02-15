/**
 * Smart Form Loading States
 * Provides visual feedback when forms are submitted
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle all form submissions
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                // Store original button content
                const originalContent = submitBtn.innerHTML;
                const originalDisabled = submitBtn.disabled;
                
                // Add loading state
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = `
                    <svg class="spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 6v6l4 2"></path>
                    </svg>
                    <span>Memproses...</span>
                `;
                
                // Prevent double submission
                form.style.opacity = '0.6';
                form.style.pointerEvents = 'none';
                
                // Restore after timeout (in case request takes too long)
                const timeout = setTimeout(() => {
                    submitBtn.disabled = originalDisabled;
                    submitBtn.classList.remove('loading');
                    submitBtn.innerHTML = originalContent;
                    form.style.opacity = '1';
                    form.style.pointerEvents = 'auto';
                }, 30000); // 30 seconds timeout
                
                // Store timeout ID for cleanup
                form.dataset.loadingTimeout = timeout;
            }
        });
    });
    
    // Restore on page show (browser back button)
    window.addEventListener('pageshow', function(event) {
        forms.forEach(form => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && form.dataset.loadingTimeout) {
                clearTimeout(parseInt(form.dataset.loadingTimeout));
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
                form.style.opacity = '1';
                form.style.pointerEvents = 'auto';
            }
        });
    });
    
    // Handle OCR file upload with progress
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2); // MB
                
                // Update label with file info
                const label = this.closest('.form-group')?.querySelector('label');
                if (label) {
                    const originalLabel = label.textContent;
                    label.innerHTML = `${originalLabel} <small style="color: #64748b;">(${fileName} - ${fileSize}MB)</small>`;
                }
            }
        });
    });
    
    // Add animation keyframes
    if (!document.querySelector('style[data-form-loading]')) {
        const style = document.createElement('style');
        style.setAttribute('data-form-loading', 'true');
        style.textContent = `
            button.loading {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                transition: all 0.2s ease;
            }
            
            button.loading .spinner {
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }
            
            .form-group {
                transition: all 0.2s ease;
            }
            
            .form-group.disabled {
                opacity: 0.6;
                pointer-events: none;
            }
        `;
        document.head.appendChild(style);
    }
});
