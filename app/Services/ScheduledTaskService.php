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
    }

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
            ->take(10)  // Limit the result to 10 records
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
}
