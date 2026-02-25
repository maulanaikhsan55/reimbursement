<!-- Proof Modal -->
<div id="proofModal" class="proof-modal" data-proof-modal-root="1" style="display: none;">
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

@push('scripts')
<script>
    (function () {
    window.__proofModalState = window.__proofModalState || {
        zoom: 1,
        fileUrl: '',
        fileName: 'bukti-transaksi',
        previousBodyOverflow: '',
    };

    const proofModalState = window.__proofModalState;

    const sanitizeProofFileName = (value) => {
        const normalized = String(value || '')
            .trim()
            .replace(/[^a-zA-Z0-9._-]+/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_+|_+$/g, '');

        if (!normalized) {
            return '';
        }

        return normalized.slice(0, 120);
    };

    const extractExtensionFromPath = (path) => {
        if (!path) {
            return '';
        }

        const match = path.match(/\.([a-zA-Z0-9]{2,8})$/);
        if (!match) {
            return '';
        }

        return String(match[1]).toLowerCase();
    };

    const resolveProofExtension = (fileUrl, isPdf) => {
        if (isPdf) {
            return 'pdf';
        }

        try {
            const parsed = new URL(fileUrl, window.location.origin);
            const extFromPath = extractExtensionFromPath(parsed.pathname);
            if (extFromPath) {
                return extFromPath;
            }
        } catch (error) {
            const extFromRaw = extractExtensionFromPath(String(fileUrl || ''));
            if (extFromRaw) {
                return extFromRaw;
            }
        }

        return 'jpg';
    };

    const ensureExtension = (fileName, extension) => {
        if (!fileName) {
            return '';
        }

        if (!extension) {
            return fileName;
        }

        const lowerFileName = fileName.toLowerCase();
        const suffix = `.${extension}`;
        if (lowerFileName.endsWith(suffix)) {
            return fileName;
        }

        return `${fileName}${suffix}`;
    };

    const formatTimestamp = () => {
        const now = new Date();
        const YYYY = now.getFullYear();
        const MM = String(now.getMonth() + 1).padStart(2, '0');
        const DD = String(now.getDate()).padStart(2, '0');
        const HH = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const ss = String(now.getSeconds()).padStart(2, '0');
        return `${YYYY}${MM}${DD}_${HH}${mm}${ss}`;
    };

    const resolveProofId = (fileUrl) => {
        const fallbackId = 'unknown';

        try {
            const parsed = new URL(fileUrl, window.location.origin);
            const segments = parsed.pathname.split('/').filter(Boolean);
            const proofIndex = segments.lastIndexOf('proof');
            const idSegment = proofIndex >= 0 ? segments[proofIndex + 1] : segments[segments.length - 1];
            const safeId = sanitizeProofFileName(idSegment || fallbackId);
            return safeId || fallbackId;
        } catch (error) {
            const match = String(fileUrl || '').match(/\/proof\/([^/?#]+)/i);
            const safeId = sanitizeProofFileName(match ? match[1] : fallbackId);
            return safeId || fallbackId;
        }
    };

    const buildAutoProofFileName = (fileUrl, isPdf) => {
        const extension = resolveProofExtension(fileUrl, isPdf);
        const proofId = resolveProofId(fileUrl);
        const timestamp = formatTimestamp();
        return `bukti-transaksi_${proofId}_${timestamp}.${extension}`;
    };

    const resolveProofFileName = (fileUrl, isPdf, requestedFileName) => {
        const sanitizedRequested = sanitizeProofFileName(requestedFileName);
        const normalizedRequested = sanitizedRequested.toLowerCase();
        const shouldAutoGenerate = !sanitizedRequested ||
            normalizedRequested === 'bukti-transaksi' ||
            normalizedRequested === 'bukti_transaksi';

        if (shouldAutoGenerate) {
            return buildAutoProofFileName(fileUrl, isPdf);
        }

        const extension = resolveProofExtension(fileUrl, isPdf);
        return ensureExtension(sanitizedRequested, extension);
    };

    const buildDownloadUrl = (fileUrl, fileName) => {
        try {
            const url = new URL(fileUrl, window.location.origin);
            url.searchParams.set('download', '1');
            if (fileName) {
                url.searchParams.set('filename', fileName);
            }
            return url.toString();
        } catch (error) {
            return fileUrl;
        }
    };

    const ensureProofModalMounted = () => {
        const modalNodes = Array.from(document.querySelectorAll('#proofModal'));
        if (!modalNodes.length) {
            return null;
        }

        const activeModal = modalNodes[modalNodes.length - 1];
        modalNodes.slice(0, -1).forEach((node) => node.remove());

        if (activeModal.parentElement !== document.body) {
            document.body.appendChild(activeModal);
        }

        return activeModal;
    };

    if (!window.__proofModalMountListenerBound) {
        window.__proofModalMountListenerBound = true;
        document.addEventListener('livewire:navigated', () => setTimeout(ensureProofModalMounted, 0));
        document.addEventListener('DOMContentLoaded', ensureProofModalMounted);
    }
    ensureProofModalMounted();

    if (typeof window.openProofModal !== 'function') {
        window.openProofModal = function(fileUrl, isPdf = false, fileName = '') {
            const modal = ensureProofModalMounted();
            const modalBody = document.getElementById('proofModalBody');
            const downloadBtn = document.getElementById('downloadBtn');
            const zoomControls = document.getElementById('imageZoomControls');

            if (!modal || !modalBody || !downloadBtn || !zoomControls) return;

            proofModalState.fileUrl = fileUrl;
            proofModalState.fileName = resolveProofFileName(fileUrl, isPdf, fileName);
            proofModalState.zoom = 1;

            modalBody.innerHTML = '<div class="clip-loader" style="border-color: #3b82f6; border-bottom-color: transparent; margin: 2rem auto;"></div>';
            modal.style.display = 'flex';

            proofModalState.previousBodyOverflow = document.body.style.overflow || '';
            document.body.style.overflow = 'hidden';

            downloadBtn.onclick = () => {
                const link = document.createElement('a');
                link.href = buildDownloadUrl(proofModalState.fileUrl, proofModalState.fileName);
                link.download = proofModalState.fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            if (isPdf) {
                zoomControls.style.display = 'none';
                modalBody.innerHTML = '';
                modalBody.style.alignItems = 'stretch';

                const iframe = document.createElement('iframe');
                iframe.src = fileUrl;
                iframe.title = 'Bukti Transaksi';
                iframe.style.width = '100%';
                iframe.style.height = '75vh';
                iframe.style.border = 'none';
                iframe.style.borderRadius = '0.5rem';
                modalBody.appendChild(iframe);
                return;
            }

            zoomControls.style.display = 'flex';
            modalBody.style.alignItems = 'center';

            const image = new Image();
            image.id = 'proofImage';
            image.alt = 'Bukti Transaksi';
            image.style.maxWidth = '100%';
            image.style.height = 'auto';
            image.style.transition = 'transform 0.2s ease';
            image.style.borderRadius = '0.5rem';
            image.style.transformOrigin = 'top center';

            image.onload = () => {
                modalBody.innerHTML = '';
                modalBody.appendChild(image);
            };
            image.onerror = () => {
                modalBody.innerHTML = '<div style="padding: 2rem; text-align: center;"><p style="color: #dc2626;">Gagal memuat gambar bukti.</p></div>';
            };
            image.src = fileUrl;
        };

        window.zoomProof = function(delta) {
            const img = document.getElementById('proofImage');
            if (!img) return;

            proofModalState.zoom += delta;
            proofModalState.zoom = Math.max(0.1, Math.min(3, proofModalState.zoom));
            img.style.transform = `scale(${proofModalState.zoom})`;

            const modalBody = document.getElementById('proofModalBody');
            if (modalBody) {
                modalBody.style.alignItems = proofModalState.zoom > 1 ? 'flex-start' : 'center';
            }
        };

        window.resetZoom = function() {
            const img = document.getElementById('proofImage');
            proofModalState.zoom = 1;

            if (img) {
                img.style.transform = 'scale(1)';
            }

            const modalBody = document.getElementById('proofModalBody');
            if (modalBody) {
                modalBody.style.alignItems = 'center';
            }
        };

        window.closeProofModal = function() {
            const modal = document.getElementById('proofModal');
            const modalBody = document.getElementById('proofModalBody');
            const zoomControls = document.getElementById('imageZoomControls');

            if (!modal) return;

            modal.style.display = 'none';
            if (modalBody) {
                modalBody.innerHTML = '';
                modalBody.style.alignItems = 'center';
            }
            if (zoomControls) {
                zoomControls.style.display = 'none';
            }

            document.body.style.overflow = proofModalState.previousBodyOverflow || '';
            proofModalState.previousBodyOverflow = '';
            resetZoom();
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeProofModal();
        });
    }
    })();
</script>
@endpush
