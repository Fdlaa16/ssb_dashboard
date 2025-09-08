<?php

namespace App\Console\Commands;

use App\Mail\ScheduleTrainingNotificationMail;
use App\Models\Player;
use App\Models\ScheduleTrainingNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendScheduleTrainingNotifications extends Command
{
    protected $signature = 'schedule-training:send-notifications';
    protected $description = 'Send training notifications based on schedule';

    public function handle()
    {
        $this->info('Checking for notifications to send...');

        // Ambil notifikasi yang perlu dikirim hari ini dan belum terkirim
        $notifications = ScheduleTrainingNotification::with('scheduleTraining.stadium')
            ->where('is_sent', false)
            ->whereDate('scheduled_at', Carbon::today())
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('No notifications to send today.');
            return;
        }

        $sentCount = 0;

        foreach ($notifications as $notification) {
            try {
                // Ambil semua pemain aktif
                $players = Player::with('user')
                    ->whereIn('status', [1, 2])
                    ->whereHas('user', function ($query) {
                        $query->whereNotNull('email');
                    })
                    ->get();

                foreach ($players as $player) {
                    if ($player->user && $player->user->email) {
                        Mail::to($player->user->email)->send(
                            new ScheduleTrainingNotificationMail(
                                $notification->scheduleTraining,
                                $player,
                                $notification->type
                            )
                        );
                    }
                }

                // Update status notifikasi
                $notification->update([
                    'is_sent' => true,
                    'sent_at' => now(),
                ]);

                $sentCount++;
                $this->info("Sent {$notification->type} notification for training on {$notification->scheduleTraining->schedule_date}");
            } catch (\Exception $e) {
                $this->error("Failed to send notification: " . $e->getMessage());
            }
        }

        $this->info("Total notifications sent: {$sentCount}");
    }
}
