<!-- resources/views/emails/training-notification.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: Arial, sans-serif; color: #333;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%"
        style="max-width: 600px; margin: auto;">
        <tr>
            <td align="center" style="padding: 30px;">
                <!-- CARD -->
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="
          background-color: #ffffff;
          border-radius: 12px;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
          overflow: hidden;
        ">
                    <tr>
                        <td align="center" style="padding: 30px 20px 10px;">
                            <!-- Logo -->
                            <img src="{{ $message->embed(public_path('images/logo/LOGOSSB.png')) }}" alt="Logo SSB"
                                style="width: 100px;">

                            <!-- Garis -->
                            <hr
                                style="border: none; height: 1px; background-color: #ddd; width: 80%; margin: 15px auto;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 20px 20px 20px; color: #333;">
                            @if ($notificationType == 'h_minus_7')
                                <h2 style="color: #FF9500; text-align: center; margin: 0 0 20px 0;">
                                    Pengingat Latihan - 7 Hari Lagi
                                </h2>
                                <p
                                    style="color: #FF9500; text-align: center; font-weight: bold; background-color: #FFF3E0; padding: 10px; border-radius: 6px; margin: 15px 0;">
                                    Bersiaplah! Latihan akan dimulai dalam 7 hari
                                </p>
                            @elseif($notificationType == 'h_minus_1')
                                <h2 style="color: #FF5722; text-align: center; margin: 0 0 20px 0;">
                                    Pengingat Latihan - Besok
                                </h2>
                                <p
                                    style="color: #FF5722; text-align: center; font-weight: bold; background-color: #FFEBEE; padding: 10px; border-radius: 6px; margin: 15px 0;">
                                    Jangan lupa! Latihan besok ya
                                </p>
                            @else
                                <h2 style="color: #4CAF50; text-align: center; margin: 0 0 20px 0;">
                                    Latihan Hari Ini
                                </h2>
                                <p
                                    style="color: #4CAF50; text-align: center; font-weight: bold; background-color: #E8F5E8; padding: 10px; border-radius: 6px; margin: 15px 0;">
                                    Hari ini adalah hari latihan!
                                </p>
                            @endif

                            <p>Yth <strong>{{ $player->name }}</strong>,</p>

                            @if ($notificationType == 'h_minus_7')
                                <p>Kami ingin mengingatkan bahwa akan ada sesi latihan dalam 7 hari ke depan.
                                    Gunakan waktu ini untuk mempersiapkan diri dengan baik.</p>
                            @elseif($notificationType == 'h_minus_1')
                                <p>Besok adalah hari latihan! Pastikan Anda sudah menyiapkan segala keperluan
                                    dan istirahat yang cukup agar performa optimal.</p>
                            @else
                                <p>Hari ini adalah hari latihan! Bersiaplah dan jangan sampai terlambat.
                                    Semoga latihan hari ini berjalan lancar dan bermanfaat.</p>
                            @endif

                            <!-- Detail Jadwal Latihan -->
                            <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;">
                                <h3 style="color: #333; margin-top: 0;">📅 Detail Jadwal Latihan:</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>📍
                                                Lokasi:</strong></td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                            {{ $scheduleTraining->stadium->name ?? 'Stadium' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>📅
                                                Tanggal:</strong></td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                            {{ \Carbon\Carbon::parse($scheduleTraining->schedule_date)->format('d F Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>⏰
                                                Waktu:</strong></td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                            {{ \Carbon\Carbon::parse($scheduleTraining->schedule_start_at)->format('H:i') }}
                                            WIB
                                        </td>
                                    </tr>
                                    @if ($scheduleTraining->stadium && $scheduleTraining->stadium->address)
                                        <tr>
                                            <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>🏢
                                                    Alamat:</strong></td>
                                            <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                                {{ $scheduleTraining->stadium->address }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>

                            <!-- Pesan khusus berdasarkan timing -->
                            @if ($notificationType == 'h_minus_7')
                                <div
                                    style="background-color: #E3F2FD; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <p style="margin: 0; color: #1976D2;"><strong>💡 Tips Persiapan:</strong></p>
                                    <ul style="color: #1976D2; line-height: 1.6;">
                                        <li>Mulai mengatur pola tidur dan istirahat</li>
                                        <li>Persiapkan peralatan latihan</li>
                                        <li>Jaga kondisi fisik dan kesehatan</li>
                                    </ul>
                                </div>
                            @elseif($notificationType == 'h_minus_1')
                                <div
                                    style="background-color: #FFF3E0; border-left: 4px solid #FF9800; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <p style="margin: 0; color: #F57C00;"><strong>✅ Checklist Besok:</strong></p>
                                    <ul style="color: #F57C00; line-height: 1.6;">
                                        <li>Sepatu dan perlengkapan olahraga</li>
                                        <li>Botol minum yang cukup</li>
                                        <li>Berangkat lebih awal untuk menghindari terlambat</li>
                                    </ul>
                                </div>
                            @else
                                <div
                                    style="background-color: #E8F5E8; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <p style="margin: 0; color: #2E7D32;"><strong>🔥 Semangat Hari Ini:</strong></p>
                                    <ul style="color: #2E7D32; line-height: 1.6;">
                                        <li>Berikan yang terbaik dalam latihan</li>
                                        <li>Jangan lupa pemanasan sebelum latihan</li>
                                        <li>Tetap semangat dan nikmati prosesnya!</li>
                                    </ul>
                                </div>
                            @endif

                            <p style="color: #555; font-size: 14px;">Jika ada pertanyaan atau kendala,
                                jangan ragu untuk menghubungi pelatih atau koordinator SSB PUTRA MUDA BALARAJA.</p>

                            <p>Tetap semangat dan keep fighting!</p>

                            <p style="margin-top: 30px;">
                                Salam Olahraga,<br>
                                <strong>SSB PUTRA MUDA BALARAJA</strong>
                            </p>

                            <hr style="border: none; height: 1px; background-color: #eee; margin: 30px 0;">

                            <p style="font-size: 12px; color: #999; text-align: center;">
                                Email ini dikirim secara otomatis, mohon tidak membalas email ini.<br>
                                ©{{ date('Y') }} SSB PUTRA MUDA BALARAJA Management System. All Rights Reserved
                            </p>
                        </td>
                    </tr>
                </table>
                <!-- END CARD -->
            </td>
        </tr>
    </table>
</body>

</html>
