<?php

namespace App\Services;


use App\Models\OutGoingMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\MailSender;
use Exception;

class MailSenderService
{
    public function sendMail($postData): array
    {
        try {
            Mail::to($postData['to'])
                ->cc($postData['cc'])
                ->bcc($postData['bcc'])
                ->send(mailable: new MailSender($postData));

            // If no exception is thrown, the mail was sent successfully
            return [
                'status' => 'success',
                'message' => 'Mail sent successfully',
            ];
        } catch (Exception $e) {
            // Log the error for debugging purposes
            Log::error('Mail sending failed: ' . $e->getMessage());

            // Return a failure response
            return [
                'status' => 'failure',
                'message' => 'Mail sending failed',
                'error' => $e->getMessage(),
            ];
        }
    }


    public function setOutGoingMails($postData)
    {
        try {
            DB::beginTransaction();
            $outGoingMails = OutGoingMail::create($postData);
            DB::commit();
            return  $outGoingMails;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
