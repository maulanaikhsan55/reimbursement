<!-- SweetAlert2 Modal Implementation -->
<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.confirmCallback = null;
    
    // Modern Confirm Modal Configuration (Global/Shared)
    window.swalConfig = window.swalConfig || {
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-gray-100',
            title: 'text-xl font-bold text-gray-800',
            htmlContainer: 'text-gray-600',
            confirmButton: 'btn-modern btn-modern-primary px-6 py-2 rounded-full font-medium shadow-md transition-all duration-300',
            cancelButton: 'btn-modern btn-modern-secondary px-6 py-2 rounded-full font-medium shadow-sm hover:bg-gray-50 transition-all duration-300 mr-2',
            actions: 'gap-3'
        },
        buttonsStyling: false,
        width: '24em',
        padding: '1.5em',
        reverseButtons: true,
        backdrop: `rgba(0,0,0,0.4)`
    };

    window.openConfirmModal = function(actionCallback, title = 'Konfirmasi', message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?') {
        Swal.fire({
            ...window.swalConfig,
            title: title,
            text: message,
            icon: 'question',
            iconColor: '#425d87',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                if (actionCallback) actionCallback();
            }
        });
    }

    // Modern Reject Modal with Textarea
    window.openRejectModal = function(actionUrl, inputName = 'catatan', title = 'Tolak Pengajuan', placeholder = 'Jelaskan alasan penolakan...') {
        Swal.fire({
            ...window.swalConfig,
            customClass: {
                ...window.swalConfig.customClass,
                confirmButton: 'btn-modern btn-modern-danger px-6 py-2 rounded-full font-medium shadow-md transition-all duration-300'
            },
            title: title,
            input: 'textarea',
            inputLabel: 'Alasan Penolakan',
            inputPlaceholder: placeholder,
            inputAttributes: {
                'aria-label': 'Alasan penolakan',
                'name': inputName,
                'required': 'true'
            },
            icon: 'warning',
            iconColor: '#ef4444',
            showCancelButton: true,
            confirmButtonText: 'Tolak Pengajuan',
            cancelButtonText: 'Batal',
            preConfirm: (reason) => {
                if (!reason) {
                    Swal.showValidationMessage('Alasan penolakan wajib diisi!')
                }
                return reason
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                // Create a temporary form to submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = actionUrl;
                
                // Add CSRF Token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                // Add Reason Input
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = inputName;
                reasonInput.value = result.value;
                form.appendChild(reasonInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Modern Disburse Modal with Date Input
    window.openDisburseModal = function(actionUrl, defaultDate = new Date().toISOString().split('T')[0], title = 'Konfirmasi Pencairan', message = 'Pilih tanggal pencairan dana:') {
        Swal.fire({
            ...window.swalConfig,
            title: title,
            html: `
                <div style="text-align: left; margin-top: 1rem;">
                    <p style="margin-bottom: 0.5rem; font-size: 0.9rem; color: #64748b;">${message}</p>
                    <input type="date" id="swal-tanggal-pencairan" class="swal2-input" 
                           value="${defaultDate}" 
                           style="width: 100%; margin: 0; border-radius: 0.75rem; border: 1px solid #cbd5e1; font-family: 'Poppins', sans-serif;">
                </div>
            `,
            icon: 'question',
            iconColor: '#425d87',
            showCancelButton: true,
            confirmButtonText: 'Konfirmasi Cairkan',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const date = document.getElementById('swal-tanggal-pencairan').value;
                if (!date) {
                    Swal.showValidationMessage('Tanggal pencairan wajib diisi!')
                }
                return date;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = actionUrl;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                const dateInput = document.createElement('input');
                dateInput.type = 'hidden';
                dateInput.name = 'tanggal_pencairan';
                dateInput.value = result.value;
                form.appendChild(dateInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Modern Preview Modal for Attachments
    window.openPreviewModal = function(fileUrl, fileName = 'Bukti Transaksi', isPdf = null) {
        if (isPdf === null) {
            isPdf = fileUrl.toLowerCase().endsWith('.pdf') || fileUrl.toLowerCase().includes('.pdf?');
        }
        
        Swal.fire({
            title: fileName,
            html: isPdf 
                ? `<iframe src="${fileUrl}" style="width: 100%; height: 600px; border: none; border-radius: 0.5rem;"></iframe>`
                : `<div style="padding: 10px;"><img src="${fileUrl}" style="max-width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>`,
            width: isPdf ? '80%' : '600px',
            showCloseButton: true,
            showConfirmButton: false,
            focusConfirm: false,
            customClass: {
                popup: 'rounded-2xl shadow-2xl border border-gray-100',
                title: 'text-lg font-bold text-gray-800 border-bottom pb-3',
                closeButton: 'focus:outline-none'
            },
            backdrop: `rgba(0,0,0,0.6)`
        });
    }

    // Keep closeConfirmModal for backward compatibility
    window.closeConfirmModal = function() {
        Swal.close();
    }
</script>

<style>
    /* SweetAlert2 Custom Overrides for Modern "iPhone-like" Clean Look */
    div:where(.swal2-container) div:where(.swal2-popup) {
        border-radius: 1.5rem !important; /* Rounded XL */
        font-family: 'Poppins', sans-serif !important;
        padding: 2rem !important;
    }

    div:where(.swal2-icon) {
        border-color: #e5e7eb !important;
        margin-top: 0 !important;
    }

    div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm {
        background: linear-gradient(135deg, #425d87 0%, #3c5379 100%) !important;
        box-shadow: 0 4px 12px rgba(66, 93, 135, 0.2) !important;
    }

    div:where(.swal2-container) button:where(.swal2-styled).swal2-cancel {
        background-color: #fff !important;
        color: #64748b !important;
        border: 1px solid #e2e8f0 !important;
    }
    
    div:where(.swal2-container) button:where(.swal2-styled).swal2-cancel:hover {
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #475569 !important;
    }

    /* Animation Softening */
    div:where(.swal2-container).swal2-center>.swal2-popup {
        animation-duration: 0.25s !important;
        animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
</style>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/components/confirm-modal.blade.php ENDPATH**/ ?>