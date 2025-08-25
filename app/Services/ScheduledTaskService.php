<?php

namespace App\Services;

use App\Models\OutGoingMail;
use Illuminate\Support\Facades\Log;
use App\Services\MailSenderService;
use Carbon\Carbon;

class ScheduledTaskService
{

    protected $mailSenderService;

    public function __construct(MailSenderService $mailSenderService)
    {
        $this->mailSenderService = $mailSenderService; // Correctly assigning the parameter to the property
    }

    public function __invoke()
    {
        $this->sendPendingMails(); // Single point of execution



                // Daily alert at 08:00
        if (now()->format('H:i') === '00:00') {
            // $this->sendDailyAlerts();
            // $this->markRan('daily');
        }

        // Weekly alert - Monday 08:00
        if (now()->isMonday() && now()->format('H:i') === '00:00') {
            // $this->sendWeeklyAlerts();
            // $this->markRan('weekly');
        }

        // Monthly alert - 1st of month at 08:00
        if (now()->day === 1 && now()->format('H:i') === '00:00') {
            // $this->sendMonthlyAlerts();
            // $this->markRan('monthly');
        }

        // Quarterly alert - 1st of Jan/Apr/Jul/Oct at 08:00
        if (in_array(now()->month, [1,4,7,10]) && now()->day === 1 && now()->format('H:i') === '00:00') {
            // $this->sendQuarterlyAlerts();
            // $this->markRan('quarterly');
        }

                // Yearly alert - 1st January at 08:00
        if (now()->month === 1 && now()->day === 1 && now()->format('H:i') === '08:00') {
            // $this->sendYearlyAlerts();
            // $this->markRan('yearly', now()->endOfYear());
        }
    }

    /**
     * Summary of sendPendingMails
     * @return void
     */
    public function sendPendingMails(): void
    {
        Log::info('Start sending mail');

        $pendingMails = OutGoingMail::where('status', 'Active')
            ->where('sent', false)
            ->where(function ($query) {
                $query->where('retry_after', '<=', now())
                    ->orWhereNull('retry_after');
            })
            ->where('failure_count', '<', 6)  // Ensure failure count is less than 5
            // ->orderBy('priority', 'desc')
            ->orderByDesc('priority')  // High priority comes first
            ->orderBy('id', 'asc')     // Then order by id ascending
            ->take(100)  // Limit the result to 10 records
            ->get();
        // Loop through each pending mail and send
        foreach ($pendingMails as $mail) {
            // Log::info("Sending mail to: " . implode(', ', $mail->to)); // Log mail recipient(s)
            if (is_array($mail->to)) {
                Log::info("Sending mail to: " . implode(', ', $mail->to));
            } else {
                Log::info("Sending mail to: " . $mail->to);
            }
            try {
                // Attempt to send the mail
                $status = $this->mailSenderService->sendMail($mail);

                if (isset($status) && $status['status'] == 'success') {
                    // Mark as sent on success
                    $mail->update([
                        'sent' => true,
                        'sent_at' => Carbon::now(),
                        'status'=>'Sent',
                    ]);
                    Log::info("Mail sent successfully to: " . implode(', ', (array)$mail->to));
                } else {
                    // If failed, schedule for retry later
                    $mail->update([
                        'failed_at' => Carbon::now(),
                        'retry_after' => ($mail->failure_count + 1 >= 3) ? Carbon::now()->addMinutes(30):$mail->retry_after,
                        'failure_reason' => $status['error'], // Add specific failure reason if available
                        'failure_count' => $mail->failure_count + 1,
                        // 'status' => ($mail->failure_count + 1 >= 5) ? 'Failed' : $mail->status, // Set status to 'Failed' if failure_count is 5 or more
                    ]);
                    Log::error("Mail failed to send to: " . implode(', ', (array)$mail->to));
                }
            } catch (\Exception $e) {
                // Catch any exception that occurs during the send operation
                $mail->update([
                    'failed_at' => Carbon::now(),
                    'retry_after' => Carbon::now()->addMinutes(10),
                    'failure_reason' => $e->getMessage(),
                ]);
                Log::error("Exception while sending mail to: " . implode(', ', (array)$mail->to) . " - " . $e->getMessage());
            }
        }
        Log::info('Finished sending mail');
    }


    /**
     * Summary of sendDailyAlerts
     * includes the sales, purchases, and other daily alerts
     * like low stock, upcoming expires, sales purchases, and other alerts
     * @return void
     */
    public function sendDailyAlerts()
    {
        // Logic for sending daily alerts
        Log::info('Sending daily alerts');
    }
}
