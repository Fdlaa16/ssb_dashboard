<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Biodata Pemain - {{ $player->name }}</title>
    <style>
        /* Reset dan Base Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            /* Ukuran font diperbesar dari 12px */
            color: #333;
            line-height: 1.4;
            margin: 30px 50px;
            /* Margin diperbesar dari 20px 40px */
            background: #fff;
            min-height: 100vh;
        }

        /* Header Section */
        .header {
            border-bottom: 3px solid #2c3e50;
            /* Border lebih tebal dan warna lebih modern */
            padding-bottom: 20px;
            /* Padding diperbesar dari 10px */
            margin-bottom: 35px;
            /* Margin diperbesar dari 20px */
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo {
            width: 100px;
            /* Diperbesar dari 80px */
            text-align: center;
            vertical-align: middle;
        }

        .header-logo img {
            width: 70px;
            /* Diperbesar dari 50px */
            height: auto;
        }

        .header-content {
            text-align: center;
            vertical-align: middle;
            padding-left: 20px;
        }

        .header-content h1 {
            font-size: 22px;
            /* Diperbesar dari 16px */
            font-weight: bold;
            text-transform: uppercase;
            color: #2c3e50;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .header-content p {
            font-size: 13px;
            /* Diperbesar dari 11px */
            color: #555;
            margin: 4px 0;
            /* Diperbesar dari 2px */
            line-height: 1.3;
        }

        /* Profile Section */
        .profile-container {
            margin-top: 25px;
            /* Diperbesar dari 10px */
        }

        .profile-table {
            width: 100%;
            border-collapse: collapse;
        }

        .avatar-section {
            width: 180px;
            /* Diperbesar dari 160px */
            text-align: center;
            vertical-align: top;
            padding-right: 25px;
            /* Padding diperbesar */
        }

        .avatar-img {
            width: 150px;
            /* Diperbesar dari 130px */
            height: 190px;
            /* Diperbesar dari 170px */
            border: 2px solid #2c3e50;
            /* Border lebih tebal */
            object-fit: cover;
            border-radius: 8px;
            /* Tambahan border radius */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            /* Tambahan shadow */
        }

        .avatar-placeholder {
            width: 150px;
            height: 190px;
            border: 2px dashed #bdc3c7;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7f8c8d;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .data-section {
            vertical-align: top;
            padding-left: 25px;
            /* Diperbesar dari 15px */
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            /* Diperbesar dari 12px */
        }

        .data-table tr {
            height: 35px;
            /* Tinggi row diperbesar untuk spacing lebih baik */
        }

        .data-table td {
            padding: 8px 0;
            /* Padding vertikal diperbesar dari 4px */
            vertical-align: middle;
        }

        .data-label {
            width: 140px;
            /* Diperbesar dari 130px */
            font-weight: bold;
            color: #2c3e50;
        }

        .data-colon {
            width: 15px;
            /* Diperbesar dari 10px */
            text-align: center;
            font-weight: bold;
        }

        .data-value {
            color: #34495e;
        }

        /* Status Badges */
        .status-badge {
            font-weight: bold;
            padding: 4px 12px;
            /* Padding diperbesar */
            border-radius: 15px;
            /* Border radius diperbesar */
            font-size: 11px;
            display: inline-block;
            margin-left: 8px;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .captain-badge {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        /* Responsive adjustments */
        @media print {
            body {
                margin: 20mm 15mm;
                /* Margin untuk print */
            }
        }

        /* Additional spacing utilities */
        .mt-1 {
            margin-top: 8px;
        }

        .mt-2 {
            margin-top: 16px;
        }

        .mb-1 {
            margin-bottom: 8px;
        }

        .mb-2 {
            margin-bottom: 16px;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    <img src="{{ $logo }}" alt="Logo">
                </td>
                <td class="header-content">
                    <h1>PUTRA MUDA BALARAJA</h1>
                    <p>Alamat: Jl. Cipete No. 06, Cipete Selatan, Cilandak, Jakarta Selatan - DKI Jakarta - 12410</p>
                    <p>Telp: 081234567890 | Email: putramuda.balaraja@gmail.com</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Profile Section -->
    <div class="profile-container">
        <table class="profile-table">
            <tr>
                <!-- Avatar Section -->
                <td class="avatar-section">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="Avatar" class="avatar-img">
                    @else
                        <div class="avatar-placeholder">
                            Foto Tidak Tersedia
                        </div>
                    @endif
                </td>

                <!-- Data Section -->
                <td class="data-section">
                    <table class="data-table">
                        <tr>
                            <td class="data-label">Nama Lengkap</td>
                            <td class="data-colon">:</td>
                            <td class="data-value">{{ $player->name }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">NISN</td>
                            <td class="data-colon">:</td>
                            <td class="data-value">{{ $player->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Email</td>
                            <td class="data-colon">:</td>
                            <td class="data-value">{{ $player->user->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">No. Telepon</td>
                            <td class="data-colon">:</td>
                            <td class="data-value">{{ $player->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Tinggi Badan</td>
                            <td class="data-colon">:</td>
                            <td class="data-value">{{ $player->height ? $player->height . ' cm' : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Berat Badan</td>
                            <td class="data-colon">:</td>
                            <td class="data-value">{{ $player->weight ? $player->weight . ' kg' : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Posisi</td>
                            <td class="data-colon">:</td>
                            <td class="data-value">{{ $positionText ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Kategori</td>
                            <td class="data-colon">:</td>
                            <td class="data-value">{{ $ageText ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Status</td>
                            <td class="data-colon">:</td>
                            <td class="data-value">
                                <span
                                    class="status-badge {{ $player->status == 1 ? 'status-active' : 'status-inactive' }}">
                                    {{ $player->status == 1 ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
