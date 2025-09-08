<?php

namespace App\Mail;

use App\Models\ScheduleMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduleMatchNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $scheduleMatch;
    public $player;
    public $notificationType;

    public function __construct(ScheduleMatch $scheduleMatch, $player, $notificationType)
    {
        $this->scheduleMatch = $scheduleMatch;
        $this->player = $player;
        $this->notificationType = $notificationType;
    }

    public function build()
    {
        $subject = $this->getSubject();

        // Load relationships untuk memastikan data lengkap
        $this->scheduleMatch->load([
            'firstClub',
            'secoundClub',
            'stadium'
        ]);

        return $this->subject($subject)
            ->view('emails.schedule-match-notification')
            ->with([
                'scheduleMatch' => $this->scheduleMatch,
                'player' => $this->player,
                'notificationType' => $this->notificationType,
                'subject' => $subject,
                'matchDetails' => $this->getMatchDetails(),
            ]);
    }

    private function getSubject()
    {
        $firstClub = $this->scheduleMatch->firstClub->name ?? 'TIM 1';
        $secondClub = $this->scheduleMatch->secoundClub->name ?? 'TIM 2';

        switch ($this->notificationType) {
            case 'h_minus_7':
                return "Pengingat Pertandingan - 7 Hari Lagi: {$firstClub} vs {$secondClub} | SSB PUTRA MUDA BALARAJA";
            case 'h_minus_1':
                return "Pengingat Pertandingan - Besok: {$firstClub} vs {$secondClub} | SSB PUTRA MUDA BALARAJA";
            case 'day_h':
                return "Pertandingan Hari Ini: {$firstClub} vs {$secondClub} | SSB PUTRA MUDA BALARAJA";
            default:
                return "Pengingat Pertandingan: {$firstClub} vs {$secondClub} | SSB PUTRA MUDA BALARAJA";
        }
    }

    private function getMatchDetails()
    {
        return [
            'first_club' => [
                'name' => $this->scheduleMatch->firstClub->name ?? 'TIM 1',
                'code' => $this->scheduleMatch->firstClub->code ?? '',
                'score' => $this->scheduleMatch->first_club_score,
            ],
            'second_club' => [
                'name' => $this->scheduleMatch->secoundClub->name ?? 'TIM 2',
                'code' => $this->scheduleMatch->secoundClub->code ?? '',
                'score' => $this->scheduleMatch->secound_club_score,
            ],
            'stadium' => [
                'name' => $this->scheduleMatch->stadium->name ?? 'Stadium',
                'address' => $this->scheduleMatch->stadium->address ?? null,
            ],
            'schedule' => [
                'date' => $this->scheduleMatch->schedule_date,
                'start_time' => $this->scheduleMatch->schedule_start_at,
                'end_time' => $this->scheduleMatch->schedule_end_at,
            ],
            'status' => $this->scheduleMatch->status,
            'match_day_message' => $this->getMatchDayMessage(),
        ];
    }

    private function getMatchDayMessage()
    {
        $scheduleDate = \Carbon\Carbon::parse($this->scheduleMatch->schedule_date);
        $today = \Carbon\Carbon::today();
        $diffInDays = $today->diffInDays($scheduleDate, false);

        if ($diffInDays > 0) {
            return "Pertandingan akan dimulai dalam {$diffInDays} hari";
        } elseif ($diffInDays == 0) {
            return "Pertandingan hari ini!";
        } else {
            return "Pertandingan telah selesai";
        }
    }
}
