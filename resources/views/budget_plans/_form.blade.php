@php
    $budgetPlan = $budgetPlan ?? null;
    $items = old('items', $budgetPlan ? $budgetPlan->items->toArray() : []);
    $items = is_array($items) ? $items : [];

    $projects = $projects ?? collect();
    $bankAccounts = $bankAccounts ?? collect();
    $chartAccounts = $chartAccounts ?? collect();
    $categories = $categories ?? collect();
@endphp

<div class="card">
    <div class="card-header pb-0">
        <h5>Header</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal Pengajuan</label>
                <input class="form-control" name="submission_date" type="date"
                    value="{{ old('submission_date', $budgetPlan?->submission_date ? $budgetPlan->submission_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Minggu ke-</label>
                <input class="form-control" id="week-of-month" name="week_of_month" type="number"
                    value="{{ old('week_of_month', $budgetPlan?->week_of_month ?? '') }}" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jumlah Project</label>
                <input class="form-control" id="project-count" name="project_count" type="number"
                    value="{{ old('project_count', $budgetPlan?->project_count ?? '') }}" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kategori</label>
                <select class="form-select" name="category" required>
                    <option value="">-- Pilih --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->name }}" @selected(old('category', $budgetPlan?->category) === $category->name)>{{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @if ($categories->isEmpty())
                    <small class="text-muted d-block mt-1">Belum ada kategori. Tambahkan di menu Master.</small>
                @endif
            </div>
            <div class="col-md-12">
                <label class="form-label">Catatan</label>
                <textarea class="form-control" name="notes" rows="3">{{ old('notes', $budgetPlan?->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>Detail Item</h5>
        <button class="btn btn-light" type="button" id="add-item-row">Tambah Item</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="items-table">
                <thead>
                    <tr>
                        <th class="text-center">NO</th>
                        <th>PROJECT</th>
                        <th>AKUN</th>
                        <th>REKENING</th>
                        <th>ITEM</th>
                        <th class="text-end">QTY</th>
                        <th class="text-end">HARSAT</th>
                        <th class="text-end">JUMLAH</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                        @php
                            $projectName = $projects->firstWhere('id', $item['project_id'] ?? null)?->name ?? '-';
                            $account = $chartAccounts->firstWhere('id', $item['chart_account_id'] ?? null);
                            $bankAccount = $bankAccounts->firstWhere('id', $item['bank_account_id'] ?? null);
                            $accountLabel = $account ? trim($account->code . ' - ' . $account->name) : '';
                            $bankAccountLabel = $bankAccount
                                ? trim($bankAccount->bank_name . ' - ' . $bankAccount->account_number)
                                : '';
                            $quantityValue = (float) ($item['quantity'] ?? 0);
                            $unitPriceValue = (float) ($item['unit_price'] ?? 0);
                            $lineTotalValue = (float) ($item['line_total'] ?? 0);
                        @endphp
                        <tr>
                            <td class="text-center index-cell">{{ $index + 1 }}</td>
                            <td class="project-cell">{{ $projectName }}</td>
                            <td class="account-cell">{{ $accountLabel ?: '-' }}</td>
                            <td class="bank-cell">{{ $bankAccountLabel ?: '-' }}</td>
                            <td class="item-cell">{{ $item['item_name'] ?? '-' }}</td>
                            <td class="text-end quantity-cell">{{ number_format($quantityValue, 2, ',', '.') }}</td>
                            <td class="text-end unit-price-cell">{{ number_format($unitPriceValue, 2, ',', '.') }}</td>
                            <td class="text-end line-total-cell">{{ number_format($lineTotalValue, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary edit-item-row" type="button">Edit</button>
                                <button class="btn btn-sm btn-light remove-item-row" type="button">
                                    <i data-feather="trash-2"></i> Hapus
                                </button>
                            </td>
                            <td class="d-none">
                                <input data-field="id" name="items[{{ $index }}][id]" type="hidden"
                                    value="{{ $item['id'] ?? '' }}">
                                <input data-field="project_id" name="items[{{ $index }}][project_id]"
                                    type="hidden" value="{{ $item['project_id'] ?? '' }}">
                                <input data-field="bank_account_id" name="items[{{ $index }}][bank_account_id]"
                                    type="hidden" value="{{ $item['bank_account_id'] ?? '' }}">
                                <input data-field="chart_account_id"
                                    name="items[{{ $index }}][chart_account_id]" type="hidden"
                                    value="{{ $item['chart_account_id'] ?? '' }}">
                                <input data-field="category" name="items[{{ $index }}][category]" type="hidden"
                                    value="{{ $item['category'] ?? '' }}">
                                <input data-field="item_name" name="items[{{ $index }}][item_name]"
                                    type="hidden" value="{{ $item['item_name'] ?? '' }}">
                                <input data-field="vendor_name" name="items[{{ $index }}][vendor_name]"
                                    type="hidden" value="{{ $item['vendor_name'] ?? '' }}">
                                <input data-field="unit_price" name="items[{{ $index }}][unit_price]"
                                    type="hidden" value="{{ $item['unit_price'] ?? '' }}">
                                <input data-field="quantity" name="items[{{ $index }}][quantity]" type="hidden"
                                    value="{{ $item['quantity'] ?? '' }}">
                                <input data-field="unit" name="items[{{ $index }}][unit]" type="hidden"
                                    value="{{ $item['unit'] ?? '' }}">
                                <input data-field="line_total" name="items[{{ $index }}][line_total]"
                                    type="hidden" value="{{ $item['line_total'] ?? '' }}">
                                <input data-field="real_amount" name="items[{{ $index }}][real_amount]"
                                    type="hidden" value="{{ $item['real_amount'] ?? 0 }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-center text-muted py-2 {{ count($items) > 0 ? 'd-none' : '' }}" id="items-empty-state">
            Belum ada item.
        </div>
        <div class="d-flex justify-content-end">
            <div class="text-end">
                <div class="text-muted">Total</div>
                <h5 class="mb-0">Rp <span id="total-amount">0</span></h5>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Detail Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="item-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Project</label>
                            <select class="form-select" id="item-project" required>
                                <option value="">-- Pilih Project --</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Akun</label>
                            <select class="form-select" id="item-chart-account" required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach ($chartAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($chartAccounts->isEmpty())
                                <small class="text-muted d-block mt-1">Belum ada akun. Tambahkan di menu
                                    Master.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rekening</label>
                            <select class="form-select" id="item-bank-account" required>
                                <option value="">-- Pilih Rekening --</option>
                                @foreach ($bankAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->bank_name }} -
                                        {{ $account->account_number }} ({{ $account->account_name }})</option>
                                @endforeach
                            </select>
                            @if ($bankAccounts->isEmpty())
                                <small class="text-muted d-block mt-1">Belum ada rekening. Anda bisa menambahkannya di
                                    menu Rekening.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Item</label>
                            <input class="form-control" id="item-name" type="text" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga Satuan</label>
                            <input class="form-control text-end" id="item-unit-price" type="text"
                                inputmode="decimal" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Qty</label>
                            <input class="form-control text-end" id="item-quantity" type="number" step="0.01"
                                min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jumlah</label>
                            <input class="form-control text-end" id="item-line-total" type="text" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="save-item">Simpan</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            const tableBody = document.querySelector('#items-table tbody');
            const addButton = document.getElementById('add-item-row');
            const totalAmountEl = document.getElementById('total-amount');
            const emptyStateEl = document.getElementById('items-empty-state');
            const modalEl = document.getElementById('itemModal');
            const saveButton = document.getElementById('save-item');
            const form = document.getElementById('item-form');
            const submissionDateField = document.querySelector('input[name="submission_date"]');
            const weekOfMonthField = document.getElementById('week-of-month');
            const projectCountField = document.getElementById('project-count');

            const bootstrapLib = window.bootstrap || undefined;

            if (!tableBody || !addButton || !totalAmountEl || !modalEl || !saveButton || !form) {
                return;
            }

            if (!document.body.contains(modalEl) || modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }

            const modal = bootstrapLib?.Modal ? new bootstrapLib.Modal(modalEl) : null;
            const modalCloseButtons = modalEl.querySelectorAll('[data-bs-dismiss="modal"]');

            const fieldProject = document.getElementById('item-project');
            const chartAccountField = document.getElementById('item-chart-account');
            const bankAccountField = document.getElementById('item-bank-account');
            const fieldName = document.getElementById('item-name');
            const unitPriceField = document.getElementById('item-unit-price');
            const quantityField = document.getElementById('item-quantity');
            const lineTotalField = document.getElementById('item-line-total');

            let currentEditRow = null;

            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(value);
            }

            function formatRupiah(value) {
                return `Rp ${formatNumber(value)}`;
            }

            function parseNumber(value) {
                if (typeof value === 'number') {
                    return value;
                }
                const raw = String(value ?? '').trim();
                if (!raw) {
                    return 0;
                }
                let cleaned = raw.replace(/[^\d,.-]/g, '');
                const lastComma = cleaned.lastIndexOf(',');
                const lastDot = cleaned.lastIndexOf('.');
                if (lastComma > lastDot) {
                    cleaned = cleaned.replace(/\./g, '');
                    cleaned = cleaned.replace(',', '.');
                } else {
                    cleaned = cleaned.replace(/,/g, '');
                }
                const parsed = parseFloat(cleaned);
                return Number.isNaN(parsed) ? 0 : parsed;
            }

            function formatRupiahInput(field, value) {
                const hasValue = value !== null && value !== undefined && String(value).trim() !== '';
                if (!hasValue) {
                    field.value = '';
                    return;
                }
                field.value = formatRupiah(parseNumber(value));
            }

            function recalcTotal() {
                let total = 0;
                tableBody.querySelectorAll('input[data-field="line_total"]').forEach((input) => {
                    total += parseNumber(input.value || 0);
                });
                totalAmountEl.textContent = formatNumber(total);
            }

            function updateEmptyState() {
                if (!emptyStateEl) {
                    return;
                }
                const hasRows = tableBody.querySelectorAll('tr').length > 0;
                emptyStateEl.classList.toggle('d-none', hasRows);
            }

            function updateProjectCount() {
                if (!projectCountField) {
                    return;
                }
                const projectIds = new Set();
                tableBody.querySelectorAll('input[data-field="project_id"]').forEach((input) => {
                    if (input.value) {
                        projectIds.add(input.value);
                    }
                });
                projectCountField.value = projectIds.size;
            }

            function calculateWeekOfMonth(dateString) {
                if (!dateString) {
                    return '';
                }
                const parts = dateString.split('-').map((value) => parseInt(value, 10));
                if (parts.length !== 3 || parts.some((value) => Number.isNaN(value))) {
                    return '';
                }
                const [year, month, day] = parts;
                const date = new Date(year, month - 1, day);
                if (Number.isNaN(date.getTime())) {
                    return '';
                }
                const firstOfMonth = new Date(year, month - 1, 1);
                const firstDow = (firstOfMonth.getDay() + 6) % 7; // Monday = 0
                return Math.floor((firstDow + (day - 1)) / 7) + 1;
            }

            function updateWeekOfMonth() {
                if (!weekOfMonthField || !submissionDateField) {
                    return;
                }
                weekOfMonthField.value = calculateWeekOfMonth(submissionDateField.value) || '';
            }

            function updateModalLineTotal() {
                const lineTotal = parseNumber(unitPriceField.value) * parseNumber(quantityField.value);
                lineTotalField.value = formatNumber(lineTotal);
            }

            function resetModal() {
                form.querySelectorAll('input, select, textarea').forEach((field) => {
                    if (field === lineTotalField) {
                        return;
                    }
                    if (field.tagName === 'SELECT') {
                        field.selectedIndex = 0;
                        return;
                    }
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        field.checked = false;
                        return;
                    }
                    field.value = '';
                });
                lineTotalField.value = formatNumber(0);
                unitPriceField.value = '';
            }

            function isModalValid() {
                const fields = form.querySelectorAll('input, select, textarea');
                for (const field of fields) {
                    if (!field.checkValidity()) {
                        field.reportValidity();
                        return false;
                    }
                }
                return true;
            }

            function ensureBackdrop() {
                if (document.querySelector('.modal-backdrop')) {
                    return;
                }
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }

            function removeBackdrop() {
                document.querySelectorAll('.modal-backdrop').forEach((node) => node.remove());
            }

            function showModal() {
                if (modal) {
                    modal.show();
                    return;
                }
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                modalEl.removeAttribute('aria-hidden');
                modalEl.setAttribute('aria-modal', 'true');
                document.body.classList.add('modal-open');
                ensureBackdrop();
            }

            function hideModal() {
                if (modal) {
                    modal.hide();
                    return;
                }
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                modalEl.setAttribute('aria-hidden', 'true');
                modalEl.removeAttribute('aria-modal');
                document.body.classList.remove('modal-open');
                removeBackdrop();
            }

            function getRowData(row) {
                const getValue = (field) => row.querySelector(`input[data-field="${field}"]`)?.value || '';
                return {
                    id: getValue('id'),
                    project_id: getValue('project_id'),
                    bank_account_id: getValue('bank_account_id'),
                    chart_account_id: getValue('chart_account_id'),
                    category: getValue('category'),
                    item_name: getValue('item_name'),
                    vendor_name: getValue('vendor_name'),
                    unit_price: getValue('unit_price'),
                    quantity: getValue('quantity'),
                    unit: getValue('unit'),
                    line_total: getValue('line_total'),
                    real_amount: getValue('real_amount') || '0',
                };
            }

            function setRowData(row, data) {
                row.querySelector('input[data-field="id"]').value = data.id || '';
                row.querySelector('input[data-field="project_id"]').value = data.project_id || '';
                row.querySelector('input[data-field="bank_account_id"]').value = data.bank_account_id || '';
                row.querySelector('input[data-field="chart_account_id"]').value = data.chart_account_id || '';
                row.querySelector('input[data-field="category"]').value = data.category || '';
                row.querySelector('input[data-field="item_name"]').value = data.item_name || '';
                row.querySelector('input[data-field="vendor_name"]').value = data.vendor_name || '';
                row.querySelector('input[data-field="unit_price"]').value = data.unit_price || 0;
                row.querySelector('input[data-field="quantity"]').value = data.quantity || 0;
                row.querySelector('input[data-field="unit"]').value = data.unit || '';
                row.querySelector('input[data-field="line_total"]').value = data.line_total || 0;
                row.querySelector('input[data-field="real_amount"]').value = data.real_amount || 0;

                row.querySelector('.project-cell').textContent = data.project_name || '-';
                row.querySelector('.account-cell').textContent = data.chart_account_label || '-';
                row.querySelector('.bank-cell').textContent = data.bank_account_label || '-';
                row.querySelector('.item-cell').textContent = data.item_name || '-';
                row.querySelector('.quantity-cell').textContent = formatNumber(parseNumber(data.quantity));
                row.querySelector('.unit-price-cell').textContent = formatNumber(parseNumber(data.unit_price));
                row.querySelector('.line-total-cell').textContent = formatNumber(parseNumber(data.line_total));
            }

            function buildRow(data) {
                const row = document.createElement('tr');
                row.innerHTML = `
        <td class="text-center index-cell"></td>
        <td class="project-cell"></td>
        <td class="account-cell"></td>
        <td class="bank-cell"></td>
        <td class="item-cell"></td>
        <td class="text-end quantity-cell"></td>
        <td class="text-end unit-price-cell"></td>
        <td class="text-end line-total-cell"></td>
        <td class="text-center">
          <button class="btn btn-sm btn-primary edit-item-row" type="button">Edit</button>
          <button class="btn btn-sm btn-light remove-item-row" type="button"><i data-feather="trash-2"></i> Hapus</button>
        </td>
        <td class="d-none">
          <input data-field="id" type="hidden" value="">
          <input data-field="project_id" type="hidden" value="">
          <input data-field="bank_account_id" type="hidden" value="">
          <input data-field="chart_account_id" type="hidden" value="">
          <input data-field="category" type="hidden" value="">
          <input data-field="item_name" type="hidden" value="">
          <input data-field="vendor_name" type="hidden" value="">
          <input data-field="unit_price" type="hidden" value="">
          <input data-field="quantity" type="hidden" value="">
          <input data-field="unit" type="hidden" value="">
          <input data-field="line_total" type="hidden" value="">
          <input data-field="real_amount" type="hidden" value="0">
        </td>
      `;
                setRowData(row, data);
                return row;
            }

            function reindexRows() {
                tableBody.querySelectorAll('tr').forEach((row, index) => {
                    const indexCell = row.querySelector('.index-cell');
                    if (indexCell) {
                        indexCell.textContent = index + 1;
                    }
                    row.querySelectorAll('input[data-field]').forEach((input) => {
                        const field = input.getAttribute('data-field');
                        input.setAttribute('name', `items[${index}][${field}]`);
                    });
                });
                updateProjectCount();
                updateEmptyState();
            }

            function openModalForAdd() {
                currentEditRow = null;
                resetModal();
                showModal();
            }

            function openModalForEdit(row) {
                const data = getRowData(row);
                currentEditRow = row;

                fieldProject.value = data.project_id || '';
                chartAccountField.value = data.chart_account_id || '';
                bankAccountField.value = data.bank_account_id || '';
                fieldName.value = data.item_name || '';
                formatRupiahInput(unitPriceField, data.unit_price || '');
                quantityField.value = data.quantity || '';
                updateModalLineTotal();
                showModal();
            }

            function handleSave() {
                if (!isModalValid()) {
                    return;
                }

                const projectOption = fieldProject.options[fieldProject.selectedIndex];
                const chartAccountOption = chartAccountField.options[chartAccountField.selectedIndex];
                const bankAccountOption = bankAccountField.options[bankAccountField.selectedIndex];
                const existingData = currentEditRow ? getRowData(currentEditRow) : null;
                const data = {
                    id: existingData?.id || '',
                    real_amount: existingData?.real_amount || '0',
                    project_id: fieldProject.value,
                    project_name: projectOption ? projectOption.textContent.trim() : '-',
                    chart_account_id: chartAccountField.value,
                    chart_account_label: chartAccountOption ? chartAccountOption.textContent.trim() : '-',
                    bank_account_id: bankAccountField.value,
                    bank_account_label: bankAccountOption ? bankAccountOption.textContent.trim() : '-',
                    category: existingData?.category || '',
                    item_name: fieldName.value.trim(),
                    vendor_name: existingData?.vendor_name || '',
                    unit_price: parseNumber(unitPriceField.value).toFixed(2),
                    quantity: quantityField.value,
                    unit: existingData?.unit || 'unit',
                    line_total: (parseNumber(unitPriceField.value) * parseNumber(quantityField.value)).toFixed(2),
                };

                if (currentEditRow) {
                    setRowData(currentEditRow, data);
                } else {
                    const row = buildRow(data);
                    tableBody.appendChild(row);
                }

                reindexRows();
                recalcTotal();
                updateWeekOfMonth();
                if (window.feather) {
                    window.feather.replace();
                }
                hideModal();
            }

            function handleRowAction(event) {
                const editButton = event.target.closest('.edit-item-row');
                const removeButton = event.target.closest('.remove-item-row');
                const row = event.target.closest('tr');

                if (!row) return;

                if (editButton) {
                    openModalForEdit(row);
                    return;
                }

                if (removeButton) {
                    row.remove();
                    reindexRows();
                    recalcTotal();
                    updateWeekOfMonth();
                }
            }

            unitPriceField.addEventListener('input', updateModalLineTotal);
            unitPriceField.addEventListener('blur', () => {
                formatRupiahInput(unitPriceField, unitPriceField.value);
                updateModalLineTotal();
            });
            unitPriceField.addEventListener('focus', () => {
                const value = parseNumber(unitPriceField.value);
                unitPriceField.value = value ? String(value) : '';
            });
            quantityField.addEventListener('input', updateModalLineTotal);
            saveButton.addEventListener('click', handleSave);
            addButton.addEventListener('click', openModalForAdd);
            tableBody.addEventListener('click', handleRowAction);
            if (submissionDateField) {
                submissionDateField.addEventListener('input', updateWeekOfMonth);
            }
            modalEl.addEventListener('click', (event) => {
                if (event.target === modalEl) {
                    hideModal();
                }
            });
            modalCloseButtons.forEach((button) => {
                button.addEventListener('click', hideModal);
            });

            reindexRows();
            recalcTotal();
            updateWeekOfMonth();

        })();
    </script>
@endpush
