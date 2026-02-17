<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <title>Rekap Budget Plan</title>
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
    <h2>Rekap Budget Plan</h2>
    <div class="muted">Tanggal: {{ now()->format('d/m/Y H:i') }}</div>

    <table>
      <thead>
        <tr>
          <th>No. BP</th>
          <th>Perusahaan</th>
          <th>Pemohon</th>
          <th class="text-end">Total</th>
          <th>Status</th>
          <th>Tanggal Pengajuan</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($budgetPlans as $bp)
          <tr>
            <td>{{ $bp->bp_number }}</td>
            <td>{{ $bp->company->name ?? '-' }}</td>
            <td>{{ $bp->requester->name ?? '-' }}</td>
            <td class="text-end">Rp {{ number_format($bp->total_amount, 2, ',', '.') }}</td>
            <td>{{ $bp->status }}</td>
            <td>{{ $bp->submission_date?->format('d/m/Y') ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </body>
</html>
