<?php

namespace App\Mail;

use App\Models\Player;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $player;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Player $player, User $user)
    {
        $this->player = $player;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('emails.registration-success')
            ->subject('Registrasi Berhasil - Selamat Bergabung dengan SSB PUTRA MUDA BALARAJA Kami!')
            ->with([
                'player' => $this->player,
                'user' => $this->user,
            ]);
    }
}
