document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const atasanGroup = document.getElementById('atasan_group');
    const atasanSelect = document.getElementById('atasan_id');
    const deptSelect = document.getElementById('departemen_id');
    const atasanRequired = document.getElementById('atasan_required');
    const atasanOptional = document.getElementById('atasan_optional');
    const pegawaiRequired = document.getElementById('pegawai_required');
    const atasanHelp = document.getElementById('atasan_help');

    function updateForm() {
        const selectedRole = roleSelect.value;
        const selectedDept = deptSelect.value;

        if (selectedRole === 'pegawai') {
            atasanGroup.style.display = 'block';
            atasanRequired.style.display = 'inline';
            atasanOptional.style.display = 'none';
            pegawaiRequired.style.display = 'block';
            atasanSelect.required = true;
            atasanHelp.style.display = 'block';
            
            filterAtasanByDept(selectedDept);
        } else if (selectedRole === 'atasan') {
            atasanGroup.style.display = 'block';
            atasanRequired.style.display = 'none';
            atasanOptional.style.display = 'inline';
            pegawaiRequired.style.display = 'none';
            atasanSelect.required = false;
            atasanHelp.style.display = 'block';
            
            showAllAtasan();
        } else {
            atasanGroup.style.display = 'none';
            atasanSelect.required = false;
        }
    }

    function filterAtasanByDept(deptId) {
        const options = atasanSelect.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'none';
            } else {
                const optionDept = option.getAttribute('data-dept');
                if (optionDept === deptId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            }
        });

        atasanSelect.value = '';
    }

    function showAllAtasan() {
        const options = atasanSelect.querySelectorAll('option');
        options.forEach(option => {
            option.style.display = 'block';
        });
    }

    roleSelect.addEventListener('change', updateForm);
    deptSelect.addEventListener('change', function() {
        if (roleSelect.value === 'pegawai') {
            filterAtasanByDept(this.value);
        }
    });

    updateForm();
});
