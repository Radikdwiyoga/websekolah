<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Backup Absensi</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; color: #111827; font-size: 10px; }
        .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 14px; }
        .school { font-size: 15px; font-weight: bold; color: #1e3a8a; }
        .school-meta { font-size: 9px; color: #4b5563; margin-top: 2px; }
        .title { font-size: 13px; font-weight: bold; margin-top: 10px; }
        .subtitle { font-size: 10px; color: #374151; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta td { padding: 2px 0; font-size: 9px; vertical-align: top; }
        .meta .label { color: #6b7280; width: 90px; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary td { width: 12.5%; border: 1px solid #d1d5db; padding: 6px 4px; text-align: center; background: #f9fafb; }
        .summary .value { font-size: 14px; font-weight: bold; color: #111827; }
        .summary .label { font-size: 8px; color: #6b7280; text-transform: uppercase; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.data th { background: #1e3a8a; color: #ffffff; font-size: 8px; text-align: left; padding: 5px 4px; border: 1px solid #1e3a8a; }
        table.data td { font-size: 8.5px; padding: 4px; border: 1px solid #e5e7eb; }
        table.data tr:nth-child(even) td { background: #f9fafb; }
        .section-title { font-size: 11px; font-weight: bold; margin: 0 0 6px; color: #1e3a8a; }
        .muted { color: #9ca3af; font-style: italic; }
        .footer { margin-top: 16px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school">{{ $school['name'] }}</div>
        @if ($school['address'] || $school['city'])
            <div class="school-meta">
                {{ collect([$school['address'], $school['city'], $school['province']])->filter()->implode(', ') }}
            </div>
        @endif
        <div class="title">Laporan Backup Absensi {{ $backup['periodLabel'] }}</div>
        <div class="subtitle">Izin &amp; Sakit tercakup dalam laporan ini</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Periode</td>
            <td>{{ $backup['periodLabel'] }} ({{ $backup['rangeLabel'] }})</td>
            <td class="label">Kelas</td>
            <td>{{ $backup['className'] }}</td>
        </tr>
        <tr>
            <td class="label">Dicetak</td>
            <td>{{ $generatedAt->translatedFormat('d F Y, H.i') }}</td>
            <td class="label">Jumlah Siswa</td>
            <td>{{ $backup['summary']['siswa'] }} siswa</td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td><div class="value">{{ $backup['summary']['hadir'] }}</div><div class="label">Hadir</div></td>
            <td><div class="value">{{ $backup['summary']['terlambat'] }}</div><div class="label">Terlambat</div></td>
            <td><div class="value">{{ $backup['summary']['izin'] }}</div><div class="label">Izin</div></td>
            <td><div class="value">{{ $backup['summary']['sakit'] }}</div><div class="label">Sakit</div></td>
            <td><div class="value">{{ $backup['summary']['alfa'] }}</div><div class="label">Alfa</div></td>
            <td><div class="value">{{ $backup['summary']['total'] }}</div><div class="label">Total</div></td>
            <td><div class="value">{{ $backup['summary']['permits_izin'] }}</div><div class="label">Surat Izin</div></td>
            <td><div class="value">{{ $backup['summary']['permits_sakit'] }}</div><div class="label">Surat Sakit</div></td>
        </tr>
    </table>

    <p class="section-title">Rekap Kehadiran per Siswa</p>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Nama Siswa</th>
                <th style="width: 90px;">Kelas</th>
                <th style="width: 40px;">Hadir</th>
                <th style="width: 60px;">Terlambat</th>
                <th style="width: 40px;">Izin</th>
                <th style="width: 40px;">Sakit</th>
                <th style="width: 40px;">Alfa</th>
                <th style="width: 45px;">Total</th>
                <th style="width: 55px;">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($backup['rows'] as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['class'] }}</td>
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['terlambat'] }}</td>
                    <td>{{ $row['izin'] }}</td>
                    <td>{{ $row['sakit'] }}</td>
                    <td>{{ $row['alfa'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['percentage'] !== null ? $row['percentage'].'%' : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="muted">Tidak ada data siswa pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="section-title">Izin &amp; Sakit</p>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th style="width: 70px;">Tanggal</th>
                <th style="width: 80px;">Kelas</th>
                <th>Nama Siswa</th>
                <th style="width: 45px;">Jenis</th>
                <th>Alasan</th>
                <th style="width: 60px;">Status</th>
                <th style="width: 80px;">Ditinjau Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($backup['permits'] as $index => $permit)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $permit['date'] }}</td>
                    <td>{{ $permit['class'] }}</td>
                    <td>{{ $permit['student'] }}</td>
                    <td>{{ $permit['type'] }}</td>
                    <td>{{ $permit['reason'] ?: '—' }}</td>
                    <td>{{ $permit['status'] }}</td>
                    <td>{{ $permit['reviewer'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">Tidak ada pengajuan izin atau sakit pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh sistem {{ config('app.name') }} pada {{ $generatedAt->translatedFormat('d F Y, H.i') }}.
    </div>
</body>
</html>
