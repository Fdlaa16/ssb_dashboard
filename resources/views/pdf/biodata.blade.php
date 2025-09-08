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
            color: #333;
            line-height: 1.4;
            margin: 30px 50px;
            background: #fff;
            min-height: 100vh;
        }

        /* Header Section */
        .header {
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 35px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo {
            width: 100px;
            text-align: center;
            vertical-align: middle;
        }

        .header-logo img {
            width: 70px;
            height: auto;
        }

        .header-content {
            text-align: center;
            vertical-align: middle;
            padding-left: 20px;
        }

        .header-content h1 {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            color: #2c3e50;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .header-content p {
            font-size: 13px;
            color: #555;
            margin: 4px 0;
            line-height: 1.3;
        }

        /* Profile Section */
        .profile-container {
            margin-top: 25px;
        }

        .profile-table {
            width: 100%;
            border-collapse: collapse;
        }

        .avatar-section {
            width: 180px;
            text-align: center;
            vertical-align: top;
            padding-right: 25px;
        }

        .avatar-img {
            width: 150px;
            height: 190px;
            border: 2px solid #2c3e50;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table tr {
            height: 35px;
        }

        .data-table td {
            padding: 8px 0;
            vertical-align: middle;
        }

        .data-label {
            width: 140px;
            font-weight: bold;
            color: #2c3e50;
        }

        .data-colon {
            width: 15px;
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
            border-radius: 15px;
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

        /* Page Break */
        .page-break {
            page-break-before: always;
        }

        /* Attachments Section */
        .attachments-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .attachments-header h2 {
            font-size: 20px;
            color: #2c3e50;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .attachments-container {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .attachment-item {
            text-align: center;
            page-break-inside: avoid;
            margin-bottom: 25px;
        }

        .attachment-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .attachment-image {
            width: 100%;
            max-width: 500px;
            height: auto;
            max-height: 350px;
            border: 2px solid #2c3e50;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            object-fit: contain;
            background: #f8f9fa;
        }

        .attachment-placeholder {
            width: 100%;
            max-width: 500px;
            height: 350px;
            border: 2px dashed #bdc3c7;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7f8c8d;
            font-size: 14px;
            background: #f8f9fa;
            margin: 0 auto;
        }

        /* Three items per page layout */
        .attachments-page {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 700px;
        }

        /* Responsive adjustments */
        @media print {
            body {
                margin: 20mm 15mm;
            }

            .page-break {
                page-break-before: always;
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

    <!-- Attachments Page -->
    @if (count($attachments) > 0)
        <div class="page-break">
            <div class="attachments-header">
                <h2>Lampiran Dokumen</h2>
            </div>

            <div class="attachments-page">
                @foreach ($attachments as $key => $attachment)
                    <div class="attachment-item">
                        <div class="attachment-title">{{ $attachment['title'] }}</div>
                        @if (isset($attachment['data']))
                            <img src="{{ $attachment['data'] }}" alt="{{ $attachment['title'] }}"
                                class="attachment-image">
                        @else
                            <div class="attachment-placeholder">
                                Dokumen {{ $attachment['title'] }} Tidak Tersedia
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</body>

</html>
