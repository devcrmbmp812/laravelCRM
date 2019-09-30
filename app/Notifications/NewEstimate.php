<?php

namespace App\Notifications;

use App\Estimate;
use App\Http\Controllers\Admin\ManageEstimatesController;
use Illuminate\Bus\Queueable;
use App\Traits\SmtpSettings;
use App\User;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewEstimate extends Notification implements ShouldQueue
{
    use Queueable, SmtpSettings;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $estimate;
    private $user;
    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate;
        $this->user = User::findOrFail($estimate->client_id);
        $this->setMailConfigs();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('/');

        // For Sending pdf to email
        $invoiceController = new ManageEstimatesController();
        $pdfOption = $invoiceController->domPdfObjectForDownload($this->estimate->id);
        $pdf = $pdfOption['pdf'];
        $filename = $pdfOption['fileName'];

        return (new MailMessage)
            ->subject(__('email.estimate.subject').' - '.config('app.name').'!')
            ->greeting(__('email.hello').' '.ucwords($this->user->name).'!')
            ->line(__('email.estimate.text'))
            ->action(__('email.loginDashboard'), $url)
            ->line(__('email.thankyouNote'))
            ->attachData($pdf->output(), $filename.'.pdf');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->estimate->toArray();
    }
}
