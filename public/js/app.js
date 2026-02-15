/* ============================================================
 * APPLICATION SCRIPTS
 * Organized by modules for easy maintenance
 * ============================================================ */

/* ===== USERS FORM MODULE ===== */
const UsersFormModule = {
    init() {
        const roleSelect = document.getElementById('role');
        const atasanGroup = document.getElementById('atasan_group');
        const atasanSelect = document.getElementById('atasan_id');
        const deptSelect = document.getElementById('departemen_id');
        
        if (!roleSelect) return;

        const atasanRequired = document.getElementById('atasan_required');
        const atasanOptional = document.getElementById('atasan_optional');
        const pegawaiRequired = document.getElementById('pegawai_required');
        const atasanHelp = document.getElementById('atasan_help');

        roleSelect.addEventListener('change', () => this.updateForm(
            roleSelect, atasanGroup, atasanSelect, deptSelect,
            atasanRequired, atasanOptional, pegawaiRequired, atasanHelp
        ));
        
        deptSelect.addEventListener('change', () => {
            if (roleSelect.value === 'pegawai') {
                this.filterAtasanByDept(atasanSelect, deptSelect.value);
            }
        });

        this.updateForm(roleSelect, atasanGroup, atasanSelect, deptSelect,
            atasanRequired, atasanOptional, pegawaiRequired, atasanHelp);
    },

    updateForm(roleSelect, atasanGroup, atasanSelect, deptSelect, atasanRequired, atasanOptional, pegawaiRequired, atasanHelp) {
        const selectedRole = roleSelect.value;
        const selectedDept = deptSelect.value;

        if (selectedRole === 'pegawai') {
            atasanGroup.style.display = 'block';
            atasanRequired.style.display = 'inline';
            atasanOptional.style.display = 'none';
            pegawaiRequired.style.display = 'block';
            atasanSelect.required = true;
            atasanHelp.style.display = 'block';
            this.filterAtasanByDept(atasanSelect, selectedDept);
        } else if (selectedRole === 'atasan') {
            atasanGroup.style.display = 'block';
            atasanRequired.style.display = 'none';
            atasanOptional.style.display = 'inline';
            pegawaiRequired.style.display = 'none';
            atasanSelect.required = false;
            atasanHelp.style.display = 'block';
            this.showAllAtasan(atasanSelect);
        } else {
            atasanGroup.style.display = 'none';
            atasanSelect.required = false;
        }
    },

    filterAtasanByDept(atasanSelect, deptId) {
        const options = atasanSelect.querySelectorAll('option');
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'none';
            } else {
                const optionDept = option.getAttribute('data-dept');
                option.style.display = (optionDept === deptId) ? 'block' : 'none';
            }
        });
        atasanSelect.value = '';
    },

    showAllAtasan(atasanSelect) {
        const options = atasanSelect.querySelectorAll('option');
        options.forEach(option => {
            option.style.display = 'block';
        });
    }
};

/* ===== INITIALIZE ALL MODULES ===== */
document.addEventListener('DOMContentLoaded', function() {
    UsersFormModule.init();
});
