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

class RejectPlayerMail extends Mailable
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
        return $this->view('emails.reject-player')
            ->subject('Aktivasi Gagal - Kamu Belum Dapat Menjadi Pemain Aktif')
            ->with([
                'player' => $this->player,
                'user' => $this->user,
            ]);
    }
}
