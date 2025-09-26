<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Registrasi Berhasil - SSB PUTRA MUDA BALARAJA</title>
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
                            <!-- Logo Text -->
                            <img src="{{ $message->embed(public_path('images/logo/LOGOSSB.png')) }}" alt="Logo SSB"
                                style="width: 100px;">

                            <!-- Garis -->
                            <hr
                                style="border: none; height: 1px; background-color: #ddd; width: 80%; margin: 15px auto;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 20px 20px 20px; color: #333;">
                            <h2 style="color: #6C63FF; text-align: center; margin: 0 0 20px 0;">Selamat! Registrasi
                                Berhasil</h2>

                            <p>Yth <strong>{{ $player->name }}</strong>,</p>

                            <p>Selamat! Registrasi Anda untuk menjadi pemain SSB PUTRA MUDA BALARAJA telah berhasil
                                diproses. Kami sangat
                                senang menyambut Anda sebagai bagian dari keluarga besar SSB PUTRA MUDA BALARAJA kami.
                            </p>

                            <!-- Detail Registrasi -->
                            <div style="background-color: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;">
                                <h3 style="color: #333; margin-top: 0;">Detail Registrasi Anda:</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Nama
                                                Lengkap:</strong></td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $player->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                                            <strong>Email:</strong>
                                        </td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $user->email }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Tinggi
                                                Badan:</strong></td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $player->height }}
                                            cm</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Berat
                                                Badan:</strong></td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $player->weight }}
                                            kg</td>
                                    </tr>
                                </table>
                            </div>

                            <p><strong>Langkah Selanjutnya:</strong></p>
                            <ul style="color: #555; line-height: 1.6;">
                                <li>Silakan klik tombol di bawah ini untuk masuk ke akun Anda.</li>
                                <li>Pastikan Anda selalu mengingat email dan password yang digunakan saat registrasi.
                                </li>
                            </ul>

                            <!-- Tombol Login -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ url('/authentication/login') }}"
                                    style="display:inline-block;
                                    padding:12px 24px;
                                    background-color:#6C63FF;
                                    color:#ffffff;
                                    text-decoration:none;
                                    border-radius:6px;
                                    font-weight:bold;">
                                    Login ke Akun Anda
                                </a>
                            </div>

                            <p style="color: #555; font-size: 14px;">Jika Anda memiliki pertanyaan, jangan ragu untuk
                                menghubungi kami melalui email ini atau nomor telepon yang tersedia di website kami.</p>

                            <p style="color: #555; font-size: 14px;">
                                Berikut adalah detail biaya masuk yang perlu Anda selesaikan:
                            </p>

                            <div style="background-color:#f8f9fa; border-radius:8px; padding:15px; margin:15px 0;">
                                <p style="margin:0 0 12px; color:#333; font-weight:bold;">Rincian Biaya Pendaftaran</p>
                                <table style="width:100%; border-collapse:collapse; font-size:14px; color:#555;">
                                    <tr>
                                        <td style="padding:6px 0; width:40%;"><strong>Jumlah Biaya Masuk</strong></td>
                                        <td style="padding:6px 0;">Rp 250.000,-</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px 0;"><strong>Nomor Rekening</strong></td>
                                        <td style="padding:6px 0;">408601032591532<br>(Bank BRI a.n. M FIRMAN F)</td>
                                    </tr>
                                </table>
                                <p style="margin-top:14px; color:#d9534f; font-size:13px; line-height:1.5;">
                                    ⚠️ Silakan lakukan transfer sesuai dengan jumlah di atas, lalu unggah bukti
                                    pendaftaran melalui
                                    <strong>halaman profil</strong> di website SSB PUTRA MUDA BALARAJA.
                                </p>
                            </div>

                            <p>Terima kasih telah memilih bergabung dengan SSB PUTRA MUDA BALARAJA kami. Semoga
                                perjalanan sepak bola Anda
                                bersama kami menjadi pengalaman yang berharga!</p>

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
