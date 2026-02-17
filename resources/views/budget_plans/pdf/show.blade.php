<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <title>Budget Plan {{ $budgetPlan->bp_number }}</title>
    <style>
      body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
      h2 { margin-bottom: 4px; }
      .muted { color: #6b7280; }
      table { width: 100%; border-collapse: collapse; margin-top: 12px; }
      th, td { border: 1px solid #e5e7eb; padding: 6px 8px; }
      th { background: #f3f4f6; text-align: left; }
      .text-end { text-align: right; }
    </style>
  </head>
  <body>
    <h2>Budget Plan {{ $budgetPlan->bp_number }}</h2>
    <div class="muted">Tanggal Pengajuan: {{ $budgetPlan->submission_date?->format('d/m/Y') ?? '-' }}</div>
    @php
      $submissionDate = $budgetPlan->submission_date;
      $weekOfMonthDisplay = $budgetPlan->week_of_month;
      if ($weekOfMonthDisplay === null && $submissionDate) {
          $firstOfMonth = $submissionDate->copy()->startOfMonth();
          $offset = $firstOfMonth->dayOfWeekIso - 1;
          $weekOfMonthDisplay = intdiv($offset + ($submissionDate->day - 1), 7) + 1;
      }
      $projectCountDisplay = $budgetPlan->project_count ?? $budgetPlan->items->pluck('project_id')->filter()->unique()->count();
    @endphp

    <table>
      <tbody>
        <tr>
          <th>Perusahaan</th>
          <td>{{ $budgetPlan->company->name ?? '-' }}</td>
          <th>Pemohon</th>
          <td>{{ $budgetPlan->requester->name ?? '-' }}</td>
        </tr>
        <tr>
          <th>Total</th>
          <td>Rp {{ number_format($budgetPlan->total_amount, 2, ',', '.') }}</td>
          <th>Status</th>
          <td>{{ $budgetPlan->status }}</td>
        </tr>
        <tr>
          <th>Tanggal Pengajuan</th>
          <td>{{ $submissionDate?->format('d/m/Y') ?? '-' }}</td>
          <th>Minggu ke-</th>
          <td>{{ $weekOfMonthDisplay ?? '-' }}</td>
        </tr>
        <tr>
          <th>Jumlah Project</th>
          <td>{{ $projectCountDisplay ?? '-' }}</td>
          <th>Kategori</th>
          <td>{{ $budgetPlan->category ?? '-' }}</td>
        </tr>
        <tr>
          <th>Catatan</th>
          <td colspan="3">{{ $budgetPlan->notes ?: '-' }}</td>
        </tr>
      </tbody>
    </table>

    <table>
      <thead>
        <tr>
          <th>Project</th>
          <th>Akun</th>
          <th>Rekening</th>
          <th>Kategori</th>
          <th>Item</th>
          <th>Vendor</th>
          <th class="text-end">Harsat</th>
          <th class="text-end">Qty</th>
          <th>Satuan</th>
          <th class="text-end">Jumlah</th>
          <th class="text-end">Realisasi</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($budgetPlan->items as $item)
          <tr>
            <td>{{ $item->project->name ?? '-' }}</td>
            <td>{{ $item->chartAccount ? $item->chartAccount->code . ' - ' . $item->chartAccount->name : '-' }}</td>
            <td>
              @if ($item->bankAccount)
                {{ $item->bankAccount->bank_name }} - {{ $item->bankAccount->account_number }}
              @else
                -
              @endif
            </td>
            <td>{{ $item->category ?? '-' }}</td>
            <td>{{ $item->item_name }}</td>
            <td>{{ $item->vendor_name ?? '-' }}</td>
            <td class="text-end">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
            <td class="text-end">{{ number_format($item->quantity, 2, ',', '.') }}</td>
            <td>{{ $item->unit }}</td>
            <td class="text-end">{{ number_format($item->line_total, 2, ',', '.') }}</td>
            <td class="text-end">{{ number_format($item->real_amount, 2, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </body>
</html>
