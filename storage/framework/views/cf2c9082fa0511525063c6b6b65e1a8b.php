<!-- Proof Modal -->
<div id="proofModal" class="proof-modal" style="display: none;">
    <div class="proof-modal-overlay" onclick="closeProofModal()"></div>
    <div class="proof-modal-container">
        <div class="proof-modal-header">
            <h3>Bukti Transaksi</h3>
            <div class="proof-modal-actions" style="display: flex; gap: 0.75rem; align-items: center; margin-left: auto; margin-right: 1rem;">
                <!-- Zoom Controls (Images only) -->
                <div id="imageZoomControls" style="display: none; align-items: center; gap: 0.5rem; background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 0.5rem;">
                    <button type="button" class="action-btn" onclick="zoomProof(0.1)" title="Zoom In" style="background: none; border: none; padding: 4px; cursor: pointer; color: #475569; display: flex;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    </button>
                    <button type="button" class="action-btn" onclick="zoomProof(-0.1)" title="Zoom Out" style="background: none; border: none; padding: 4px; cursor: pointer; color: #475569; display: flex;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    </button>
                    <button type="button" class="action-btn" onclick="resetZoom()" title="Reset Zoom" style="background: none; border: none; padding: 4px; cursor: pointer; color: #475569; display: flex;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h6v6"></path><path d="M9 21H3v-6"></path><path d="M21 3l-7 7"></path><path d="M3 21l7-7"></path></svg>
                    </button>
                </div>
                <!-- Download Button -->
                <button type="button" id="downloadBtn" class="action-btn" title="Download File" style="background: #4f46e5; color: white; border: none; padding: 6px 10px; border-radius: 0.5rem; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; font-weight: 600;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Unduh
                </button>
            </div>
            <button type="button" class="proof-modal-close" onclick="closeProofModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="proof-modal-body" id="proofModalBody" style="position: relative; overflow: auto; display: flex; justify-content: center; align-items: flex-start; min-height: 200px; padding: 1rem;">
            <!-- Content will be inserted dynamically -->
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    let currentZoom = 1;
    let currentFileUrl = '';
    let currentFileName = 'bukti-transaksi';

    if (typeof window.openProofModal !== 'function') {
        window.openProofModal = function(fileUrl, isPdf = false, fileName = 'bukti-transaksi') {
            const modal = document.getElementById('proofModal');
            const modalBody = document.getElementById('proofModalBody');
            const downloadBtn = document.getElementById('downloadBtn');
            const zoomControls = document.getElementById('imageZoomControls');
            
            if (!modal || !modalBody) return;
            
            currentFileUrl = fileUrl;
            currentFileName = fileName;
            currentZoom = 1;
            
            modalBody.innerHTML = '<div class="clip-loader" style="border-color: #3b82f6; border-bottom-color: transparent; margin: 2rem auto;"></div>';
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Set download action
            downloadBtn.onclick = () => {
                const link = document.createElement('a');
                link.href = currentFileUrl;
                link.download = currentFileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            if (isPdf) {
                zoomControls.style.display = 'none';
                modalBody.innerHTML = `<iframe src="${fileUrl}" style="width: 100%; height: 75vh; border: none; border-radius: 0.5rem;" title="Bukti Transaksi"></iframe>`;
            } else {
                zoomControls.style.display = 'flex';
                const img = new Image();
                img.onload = function() {
                    modalBody.innerHTML = `<img id="proofImage" src="${fileUrl}" style="max-width: 100%; height: auto; transition: transform 0.2s ease; border-radius: 0.5rem; transform-origin: top center;" alt="Bukti Transaksi">`;
                };
                img.onerror = function() {
                    modalBody.innerHTML = '<div style="padding: 2rem; text-align: center;"><p style="color: #dc2626;">Gagal memuat gambar bukti.</p></div>';
                };
                img.src = fileUrl;
            }
        };

        window.zoomProof = function(delta) {
            const img = document.getElementById('proofImage');
            if (!img) return;
            currentZoom += delta;
            currentZoom = Math.max(0.1, Math.min(3, currentZoom)); // Limit zoom between 10% and 300%
            img.style.transform = `scale(${currentZoom})`;
            
            // Adjust container scroll if zoomed
            if (currentZoom > 1) {
                img.parentElement.style.alignItems = 'flex-start';
            } else {
                img.parentElement.style.alignItems = 'center';
            }
        };

        window.resetZoom = function() {
            const img = document.getElementById('proofImage');
            if (!img) return;
            currentZoom = 1;
            img.style.transform = 'scale(1)';
            img.parentElement.style.alignItems = 'center';
        };

        window.closeProofModal = function() {
            const modal = document.getElementById('proofModal');
            const modalBody = document.getElementById('proofModalBody');
            if (!modal) return;
            modal.style.display = 'none';
            if (modalBody) modalBody.innerHTML = '';
            document.body.style.overflow = 'auto';
            resetZoom();
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeProofModal();
        });
    }
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/components/proof-modal.blade.php ENDPATH**/ ?>