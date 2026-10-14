<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Backup Tugas &amp; Penilaian</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #111827; }
        .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 14px; }
        .school { font-size: 15px; font-weight: bold; color: #1e3a8a; }
        .school-meta { font-size: 9px; color: #4b5563; margin-top: 2px; }
        .title { font-size: 13px; font-weight: bold; margin-top: 10px; }
        .subtitle { font-size: 10px; color: #374151; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta td { padding: 2px 0; font-size: 9px; vertical-align: top; }
        .meta .label { color: #6b7280; width: 100px; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary td { width: 25%; border: 1px solid #d1d5db; padding: 6px 4px; text-align: center; background: #f9fafb; }
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
        <div class="title">Ringkasan Tugas &amp; Penilaian</div>
        <div class="subtitle">Backup ringkasan tugas dan nilai siswa</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Kelas</td>
            <td>{{ $backup['className'] }}</td>
            <td class="label">Mata Pelajaran</td>
            <td>{{ $backup['subjectName'] }}</td>
        </tr>
        <tr>
            <td class="label">Rentang</td>
            <td>{{ $backup['rangeLabel'] }}</td>
            <td class="label">Dicetak</td>
            <td>{{ $generatedAt->translatedFormat('d F Y, H.i') }}</td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td><div class="value">{{ $backup['summary']['assignments'] }}</div><div class="label">Tugas</div></td>
            <td><div class="value">{{ $backup['summary']['submissions'] }}</div><div class="label">Dikumpulkan</div></td>
            <td><div class="value">{{ $backup['summary']['graded'] }}</div><div class="label">Dinilai</div></td>
            <td><div class="value">{{ $backup['summary']['average'] !== null ? number_format($backup['summary']['average'], 2, ',', '.') : '—' }}</div><div class="label">Rata-rata</div></td>
        </tr>
    </table>

    <p class="section-title">Ringkasan Tugas</p>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Judul Tugas</th>
                <th style="width: 80px;">Kelas</th>
                <th>Mata Pelajaran</th>
                <th>Guru</th>
                <th style="width: 55px;">Status</th>
                <th style="width: 45px;">Siswa</th>
                <th style="width: 65px;">Dikumpulkan</th>
                <th style="width: 45px;">Dinilai</th>
                <th style="width: 55px;">Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($backup['assignmentRows'] as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['title'] }}</td>
                    <td>{{ $row['class'] }}</td>
                    <td>{{ $row['subject'] }}</td>
                    <td>{{ $row['teacher'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['students'] }}</td>
                    <td>{{ $row['submitted'] }}</td>
                    <td>{{ $row['graded'] }}</td>
                    <td>{{ $row['average'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="muted">Belum ada tugas pada kriteria ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="section-title">Rekap Nilai per Siswa</p>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Nama Siswa</th>
                <th style="width: 90px;">Kelas</th>
                <th style="width: 70px;">Jumlah Tugas</th>
                <th style="width: 70px;">Dikumpulkan</th>
                <th style="width: 55px;">Dinilai</th>
                <th style="width: 70px;">Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($backup['studentRows'] as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['class'] }}</td>
                    <td>{{ $row['assignments'] }}</td>
                    <td>{{ $row['submitted'] }}</td>
                    <td>{{ $row['graded'] }}</td>
                    <td>{{ $row['average'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">Tidak ada data siswa pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak {{ $generatedAt->translatedFormat('d F Y, H.i') }} oleh sistem {{ $school['name'] }}.
    </div>
</body>
</html>
