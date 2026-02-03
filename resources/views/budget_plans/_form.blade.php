@php
  $items = old('items', isset($budgetPlan) ? $budgetPlan->items->toArray() : []);
  if (empty($items)) {
      $items = [[
          'item_name' => '',
          'kode' => '',
          'vendor_name' => '',
          'harsat' => '',
          'qty' => '',
          'satuan' => '',
          'jumlah' => '',
      ]];
  }
@endphp

<div class="card">
  <div class="card-header pb-0">
    <h5>Header</h5>
  </div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Tanggal BP</label>
        <input class="form-control" name="tanggal" type="date" value="{{ old('tanggal', isset($budgetPlan->tanggal) ? $budgetPlan->tanggal->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
      </div>
      <div class="col-md-12">
        <label class="form-label">Catatan</label>
        <textarea class="form-control" name="notes" rows="3">{{ old('notes', $budgetPlan->notes ?? '') }}</textarea>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header pb-0 d-flex align-items-center justify-content-between">
    <h5>Detail Item</h5>
    <button class="btn btn-light" type="button" id="add-item-row">Tambah Baris</button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="items-table">
        <thead>
          <tr>
            <th>ITEM</th>
            <th>KODE</th>
            <th>VENDOR</th>
            <th class="text-end">HARSAT</th>
            <th class="text-end">QTY</th>
            <th>SATUAN</th>
            <th class="text-end">JUMLAH</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($items as $index => $item)
            <tr>
              <td><input class="form-control" name="items[{{ $index }}][item_name]" type="text" value="{{ $item['item_name'] ?? '' }}" required></td>
              <td><input class="form-control" name="items[{{ $index }}][kode]" type="text" value="{{ $item['kode'] ?? '' }}" required></td>
              <td><input class="form-control" name="items[{{ $index }}][vendor_name]" type="text" value="{{ $item['vendor_name'] ?? '' }}"></td>
              <td><input class="form-control text-end harsat-input" name="items[{{ $index }}][harsat]" type="number" step="0.01" min="0" value="{{ $item['harsat'] ?? '' }}" required></td>
              <td><input class="form-control text-end qty-input" name="items[{{ $index }}][qty]" type="number" step="0.01" min="0" value="{{ $item['qty'] ?? '' }}" required></td>
              <td><input class="form-control" name="items[{{ $index }}][satuan]" type="text" value="{{ $item['satuan'] ?? '' }}" required></td>
              <td><input class="form-control text-end jumlah-input" name="items[{{ $index }}][jumlah]" type="number" step="0.01" min="0" value="{{ $item['jumlah'] ?? '' }}" readonly></td>
              <td class="text-center">
                <button class="btn btn-sm btn-light remove-item-row" type="button"><i data-feather="trash-2"></i></button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-end">
      <div class="text-end">
        <div class="text-muted">Total</div>
        <h5 class="mb-0">Rp <span id="total-amount">0</span></h5>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  (function () {
    const tableBody = document.querySelector('#items-table tbody');
    const addButton = document.getElementById('add-item-row');
    const totalAmountEl = document.getElementById('total-amount');

    function formatNumber(value) {
      return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
    }

    function recalcRow(row) {
      const harsat = parseFloat(row.querySelector('.harsat-input')?.value || 0);
      const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
      const jumlah = (harsat * qty).toFixed(2);
      const jumlahInput = row.querySelector('.jumlah-input');
      if (jumlahInput) jumlahInput.value = isNaN(jumlah) ? '0.00' : jumlah;
    }

    function recalcTotal() {
      let total = 0;
      tableBody.querySelectorAll('.jumlah-input').forEach((input) => {
        const value = parseFloat(input.value || 0);
        total += isNaN(value) ? 0 : value;
      });
      totalAmountEl.textContent = formatNumber(total);
    }

    function attachRowEvents(row) {
      row.querySelectorAll('.harsat-input, .qty-input').forEach((input) => {
        input.addEventListener('input', () => {
          recalcRow(row);
          recalcTotal();
        });
      });

      const removeBtn = row.querySelector('.remove-item-row');
      if (removeBtn) {
        removeBtn.addEventListener('click', () => {
          if (tableBody.rows.length > 1) {
            row.remove();
            recalcTotal();
          }
        });
      }
    }

    function addRow() {
      const index = tableBody.rows.length;
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><input class="form-control" name="items[${index}][item_name]" type="text" required></td>
        <td><input class="form-control" name="items[${index}][kode]" type="text" required></td>
        <td><input class="form-control" name="items[${index}][vendor_name]" type="text"></td>
        <td><input class="form-control text-end harsat-input" name="items[${index}][harsat]" type="number" step="0.01" min="0" required></td>
        <td><input class="form-control text-end qty-input" name="items[${index}][qty]" type="number" step="0.01" min="0" required></td>
        <td><input class="form-control" name="items[${index}][satuan]" type="text" required></td>
        <td><input class="form-control text-end jumlah-input" name="items[${index}][jumlah]" type="number" step="0.01" min="0" value="0.00" readonly></td>
        <td class="text-center"><button class="btn btn-sm btn-light remove-item-row" type="button"><i data-feather="trash-2"></i></button></td>
      `;
      tableBody.appendChild(row);
      attachRowEvents(row);
      if (window.feather) {
        window.feather.replace();
      }
    }

    tableBody.querySelectorAll('tr').forEach((row) => attachRowEvents(row));
    recalcTotal();

    addButton.addEventListener('click', addRow);
  })();
</script>
@endpush
