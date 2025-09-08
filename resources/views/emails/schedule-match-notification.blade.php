<!-- resources/views/emails/schedule-match-notification.blade.php -->
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
                                    Pengingat Pertandingan - 7 Hari Lagi
                                </h2>
                                <p
                                    style="color: #FF9500; text-align: center; font-weight: bold; background-color: #FFF3E0; padding: 10px; border-radius: 6px; margin: 15px 0;">
                                    Bersiaplah! Pertandingan akan dimulai dalam 7 hari
                                </p>
                            @elseif($notificationType == 'h_minus_1')
                                <h2 style="color: #FF5722; text-align: center; margin: 0 0 20px 0;">
                                    Pengingat Pertandingan - Besok
                                </h2>
                                <p
                                    style="color: #FF5722; text-align: center; font-weight: bold; background-color: #FFEBEE; padding: 10px; border-radius: 6px; margin: 15px 0;">
                                    Jangan lupa! Pertandingan besok ya
                                </p>
                            @else
                                <h2 style="color: #4CAF50; text-align: center; margin: 0 0 20px 0;">
                                    Pertandingan Hari Ini
                                </h2>
                                <p
                                    style="color: #4CAF50; text-align: center; font-weight: bold; background-color: #E8F5E8; padding: 10px; border-radius: 6px; margin: 15px 0;">
                                    Hari ini adalah hari Pertandingan!
                                </p>
                            @endif

                            <p>Yth <strong>{{ $player->name }}</strong>,</p>

                            @if ($notificationType == 'h_minus_7')
                                <p>Kami ingin mengingatkan bahwa akan ada sesi pertandingan dalam 7 hari ke depan.
                                    Gunakan waktu ini untuk mempersiapkan diri dengan baik.</p>
                            @elseif($notificationType == 'h_minus_1')
                                <p>Besok adalah hari pertandingan! Pastikan Anda sudah menyiapkan segala keperluan
                                    dan istirahat yang cukup agar performa optimal.</p>
                            @else
                                <p>Hari ini adalah hari pertandingan! Bersiaplah dan jangan sampai terlambat.
                                    Semoga pertandingan hari ini berjalan lancar dan bermanfaat.</p>
                            @endif

                            <!-- Match Info Box - New Design Like Sports App -->
                            <div
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; margin: 25px 0; color: white; text-align: center;">
                                <h3 style="color: white; margin: 0 0 15px 0; font-size: 18px;">PERTANDINGAN</h3>

                                <!-- Teams Display -->
                                <div
                                    style="display: flex; align-items: center; justify-content: space-between; margin: 20px 0; background-color: rgba(255,255,255,0.1); padding: 20px; border-radius: 8px;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="40%" style="text-align: center; vertical-align: middle;">
                                                <div style="font-weight: bold; font-size: 16px; margin-bottom: 5px;">
                                                    {{ $scheduleMatch->firstClub->name ?? 'TIM 1' }}
                                                </div>
                                            </td>
                                            <td width="20%" style="text-align: center; vertical-align: middle;">
                                                <div style="font-size: 24px; font-weight: bold; margin: 10px 0;">
                                                    VS
                                                </div>
                                            </td>
                                            <td width="40%" style="text-align: center; vertical-align: middle;">
                                                <div style="font-weight: bold; font-size: 16px; margin-bottom: 5px;">
                                                    {{ $scheduleMatch->secoundClub->name ?? 'TIM 2' }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <!-- Match Date & Time -->
                                <div style="font-size: 14px; margin-top: 15px; opacity: 0.9;">
                                    🏟️ {{ $scheduleMatch->stadium->name ?? 'Stadium' }}
                                    <br>
                                    📅 {{ \Carbon\Carbon::parse($scheduleMatch->schedule_date)->format('l, d F Y') }}
                                    <br>
                                    ⏰ {{ \Carbon\Carbon::parse($scheduleMatch->schedule_start_at)->format('H:i') }} WIB
                                </div>
                            </div>

                            <!-- Pesan khusus berdasarkan timing -->
                            @if ($notificationType == 'h_minus_7')
                                <div
                                    style="background-color: #E3F2FD; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <p style="margin: 0; color: #1976D2;"><strong>💡 Tips Persiapan 7 Hari:</strong></p>
                                    <ul style="color: #1976D2; line-height: 1.6;">
                                        <li>Mulai mengatur pola tidur dan istirahat</li>
                                        <li>Pelajari formasi dan strategi tim lawan</li>
                                        <li>Jaga kondisi fisik dan kesehatan</li>
                                    </ul>
                                </div>
                            @elseif($notificationType == 'h_minus_1')
                                <div
                                    style="background-color: #FFF3E0; border-left: 4px solid #FF9800; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <p style="margin: 0; color: #F57C00;"><strong>✅ Checklist H-1:</strong></p>
                                    <ul style="color: #F57C00; line-height: 1.6;">
                                        <li>Perlengkapan olahraga</li>
                                        <li>Jersey tim</li>
                                        <li>Berangkat 30 menit lebih awal dari waktu berkumpul</li>
                                        <li>Pastikan tidur yang cukup malam ini</li>
                                    </ul>
                                </div>
                            @else
                                <div
                                    style="background-color: #E8F5E8; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <p style="margin: 0; color: #2E7D32;"><strong>🔥 Hari Pertandingan:</strong></p>
                                    <ul style="color: #2E7D32; line-height: 1.6;">
                                        <li>Sarapan dengan menu yang ringan dan bergizi</li>
                                        <li>Datang lebih awal untuk pemanasan</li>
                                        <li>Jaga sportivitas dan fair play</li>
                                        <li>Berikan yang terbaik untuk tim!</li>
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
