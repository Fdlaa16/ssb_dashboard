<?php

namespace App\Console\Commands;

use App\Mail\ScheduleMatchNotificationMail;
use App\Models\Player;
use App\Models\ScheduleMatchNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendScheduleMatchNotifications extends Command
{
    protected $signature = 'schedule-match:send-notifications {--dry-run : Show what would be sent without actually sending}';
    protected $description = 'Send match notifications based on schedule';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No emails will be sent');
        }

        $this->info('Checking for notifications to send...');

        // Ambil notifikasi yang perlu dikirim hari ini dan belum terkirim
        $notifications = ScheduleMatchNotification::with([
            'scheduleMatch.stadium',
            'scheduleMatch.firstClub',
            'scheduleMatch.secoundClub'
        ])
            ->where('is_sent', false)
            ->whereDate('scheduled_at', Carbon::today())
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('No notifications to send today.');
            return Command::SUCCESS;
        }

        $this->info("Found {$notifications->count()} notifications to process");

        $sentCount = 0;
        $errorCount = 0;

        foreach ($notifications as $notification) {
            try {
                $scheduleMatch = $notification->scheduleMatch;

                // Validasi data pertandingan
                if (!$scheduleMatch) {
                    $this->error("Schedule match not found for notification ID: {$notification->id}");
                    continue;
                }

                if (!$scheduleMatch->firstClub || !$scheduleMatch->secoundClub) {
                    $this->error("Missing club information for match ID: {$scheduleMatch->id}");
                    continue;
                }

                $matchInfo = "{$scheduleMatch->firstClub->name} vs {$scheduleMatch->secoundClub->name}";

                $this->info("Processing {$notification->type} notification for: {$matchInfo}");

                // Ambil semua pemain aktif
                $players = Player::with('user')
                    ->where('status', 1)
                    ->whereNull('deleted_at')
                    ->whereHas('user', function ($query) {
                        $query->whereNotNull('email');
                    })
                    ->get();

                if ($players->isEmpty()) {
                    $this->warn("No active players with email found");
                    continue;
                }

                $playersEmailsSent = 0;

                foreach ($players as $player) {
                    if ($player->user && $player->user->email) {
                        try {
                            if (!$dryRun) {
                                Mail::to($player->user->email)->send(
                                    new ScheduleMatchNotificationMail(
                                        $scheduleMatch,
                                        $player,
                                        $notification->type
                                    )
                                );
                            }

                            $playersEmailsSent++;

                            if ($dryRun) {
                                $this->line("  [DRY RUN] Would send to: {$player->user->email} ({$player->name})");
                            }
                        } catch (\Exception $emailError) {
                            $this->error("  Failed to send email to {$player->user->email}: " . $emailError->getMessage());
                            $errorCount++;
                        }
                    }
                }

                // Update status notifikasi hanya jika bukan dry run
                if (!$dryRun) {
                    $notification->update([
                        'is_sent' => true,
                        'sent_at' => now(),
                    ]);
                }

                $sentCount++;
                $this->info("  ✓ {$notification->type} notification processed - {$playersEmailsSent} emails " . ($dryRun ? 'would be sent' : 'sent'));
            } catch (\Exception $e) {
                $this->error("Failed to process notification ID {$notification->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        // Summary
        $this->info("\n=== SUMMARY ===");
        $this->info("Total notifications processed: {$sentCount}");

        if ($dryRun) {
            $this->info("This was a dry run - no emails were actually sent");
        } else {
            $this->info("Total notifications sent successfully: {$sentCount}");
        }

        if ($errorCount > 0) {
            $this->warn("Total errors encountered: {$errorCount}");
        }

        return $errorCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
