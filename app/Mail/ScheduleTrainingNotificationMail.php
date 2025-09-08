<?php

namespace App\Mail;

use App\Models\ScheduleTraining;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduleTrainingNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $scheduleTraining;
    public $player;
    public $notificationType;

    public function __construct(ScheduleTraining $scheduleTraining, $player, $notificationType)
    {
        $this->scheduleTraining = $scheduleTraining;
        $this->player = $player;
        $this->notificationType = $notificationType;
    }

    public function build()
    {
        $subject = $this->getSubject();

        return $this->subject($subject)
            ->view('emails.schedule-training-notification')
            ->with([
                'scheduleTraining' => $this->scheduleTraining,
                'player' => $this->player,
                'notificationType' => $this->notificationType,
                'subject' => $subject,
            ]);
    }

    private function getSubject()
    {
        switch ($this->notificationType) {
            case 'h_minus_7':
                return 'Pengingat Latihan - 7 Hari Lagi | SSB PUTRA MUDA BALARAJA';
            case 'h_minus_1':
                return 'Pengingat Latihan - Besok | SSB PUTRA MUDA BALARAJA';
            case 'day_h':
                return 'Latihan Hari Ini | SSB PUTRA MUDA BALARAJA';
            default:
                return 'Pengingat Latihan | SSB PUTRA MUDA BALARAJA';
        }
    }
}
