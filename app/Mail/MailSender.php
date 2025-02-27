<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

use Barryvdh\DomPDF\PDF;

class MailSender extends Mailable
{
    use Queueable, SerializesModels;

    public $details;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

        /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /**
         * checking for attachment
         */
        if(isset($this->details["has_attachment"])){
        // if (!empty($this->details["has_attachment"])) {
            Log::info('****************** Sending mail with attachment **************************');
            $pdf = app(PDF::class);
            $pdf->loadHTML($this->details["attachment"]);

            $pdfContent = $pdf->output();
            return $this->markdown('email.mailsender')->with([
                'body' => $this->details["body"]
            ])->subject($this->details["subject"])->attachData($pdfContent, $this->details["attachment_name"]);
        }else{
            return $this->markdown('email.mailsender')->with([
                'body' => $this->details["body"]
            ])->subject($this->details["subject"]);
        }

    }

}
