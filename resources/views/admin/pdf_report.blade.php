<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman UPT Studio & Lab</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            color: #0f766e;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 11px;
            margin: 3px 0 0 0;
            color: #4b5563;
            font-weight: normal;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 9px;
            color: #9ca3af;
        }
        .meta-info {
            margin-bottom: 15px;
        }
        .meta-info table {
            width: 100%;
        }
        .meta-info td {
            font-size: 10px;
            padding: 2px 0;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .report-table th {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: bold;
            text-align: left;
            padding: 8px 6px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
            text-transform: uppercase;
        }
        .report-table td {
            padding: 8px 6px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .status-pill {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        .status-pending { color: #b45309; }
        .status-disetujui { color: #047857; }
        .status-ditolak { color: #b91c1c; }
        .status-selesai { color: #4b5563; }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 10px;
        }
        .footer .signature {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Unit Pelaksana Teknis (UPT) Studio & Laboratorium</h1>
        <h2>Sekolah Tinggi Agama Islam Mulia Astuti Wonogiri (STAIMAS)</h2>
        <p>Alamat: Wonogiri, Jawa Tengah | Email: info@staimaswonogiri.ac.id | Telp: +62 822-2320-4552</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td style="font-weight: bold; font-size: 12px; color: #0f766e;">LAPORAN DATA PEMINJAMAN</td>
                <td style="text-align: right;">Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</td>
            </tr>
            <tr>
                <td>Kategori Item: <strong style="text-transform: uppercase;">{{ $filterTipe ?: 'Semua' }}</strong></td>
                <td style="text-align: right;">Periode Laporan: <strong>{{ $filterPeriode }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Peminjam</th>
                <th style="width: 25%;">Item Dipinjam</th>
                <th style="width: 25%;">Tanggal / Jam Pinjam</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 10%;">PJ Bertugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $index => $booking)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $booking->nama_peminjam }}</strong><br>
                        <span style="color: #6b7280; font-size: 9px;">{{ $booking->instansi_peminjam }}</span><br>
                        <span style="color: #047857; font-size: 9px;">WA: {{ $booking->no_wa ?: '-' }}</span>
                    </td>
                    <td>
                        <strong>{{ $booking->item->nama }}</strong><br>
                        <span style="color: #6b7280; font-size: 9px; text-transform: uppercase;">{{ $booking->item->kategori }} &bull; {{ $booking->item->tipe }}</span>
                    </td>
                    <td>
                        {{ $booking->tanggal_peminjaman->format('d M Y') }}
                        @if($booking->jam_mulai)
                            <br><span style="color: #0f766e; font-weight: bold;">{{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i') }} WIB</span>
                        @else
                            <br><span style="color: #6b7280; font-size: 9px;">s/d {{ $booking->tanggal_pengembalian->format('d M Y') }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-pill status-{{ $booking->status }}">
                            {{ $booking->status }}
                        </span>
                    </td>
                    <td>
                        {{ $booking->penanggungJawab?->name ?: '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #9ca3af;">Tidak ada data peminjaman dalam periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Wonogiri, {{ now()->translatedFormat('d F Y') }}</p>
        <p>Mengetahui,</p>
        <p style="margin-top: 5px; color: #4b5563;">Kepala UPT Studio & Lab</p>
        <p class="signature">Muhammad Umar Khadafi, M.Sos</p>
    </div>

</body>
</html>
