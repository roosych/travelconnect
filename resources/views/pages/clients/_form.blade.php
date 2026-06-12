<div class="row g-5">
    <div class="col-12">
        <label class="form-label required fw-semibold">Агентство</label>
        <select name="agency_id" class="form-select form-select-solid" id="client-agency-select" required>
            <option value="">— выберите агентство —</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label required fw-semibold">Полное имя</label>
        <input type="text" name="name" class="form-control form-control-solid"
               placeholder="Напр. Иван Петров" required />
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control form-control-solid"
               placeholder="client@email.com" />
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Телефон</label>
        <input type="text" name="phone" class="form-control form-control-solid js-phone"
               placeholder="+7 900 123 4567" />
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Гражданство</label>
        <input type="text" name="nationality" class="form-control form-control-solid"
               placeholder="RU" maxlength="2" style="text-transform:uppercase" />
        <div class="form-text">2-буквенный код страны ISO</div>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Дата рождения</label>
        <input type="date" name="date_of_birth" class="form-control form-control-solid" />
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Паспорт / Номер документа</label>
        <input type="text" name="passport_number" class="form-control form-control-solid"
               placeholder="Напр. 700 123456" />
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Примечания</label>
        <textarea name="notes" class="form-control form-control-solid" rows="2"
                  placeholder="Диетические требования, потребности в доступности и т.д."></textarea>
    </div>
</div>

@push('scripts')
<script>
    // Populate agency selects in modals from already loaded allAgencies list
    document.addEventListener('DOMContentLoaded', () => {
        function fillAgencySelect(selId) {
            const sel = document.getElementById(selId);
            if (!sel || !window.allAgencies) return;
            const current = sel.value;
            sel.innerHTML = '<option value="">— выберите агентство —</option>';
            window.allAgencies.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = a.company_name || a.name;
                if (String(a.id) === current) opt.selected = true;
                sel.appendChild(opt);
            });
        }

        // Fill when modal opens
        document.getElementById('modal-create-client')?.addEventListener('shown.bs.modal',
            () => fillAgencySelect('client-agency-select'));
        document.getElementById('modal-edit-client')?.addEventListener('shown.bs.modal',
            () => fillAgencySelect('client-agency-select'));
    });
</script>
@endpush
