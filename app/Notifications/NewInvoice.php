<?php

namespace App\Notifications;

use App\EmailNotificationSetting;
use App\Http\Controllers\Admin\ManageAllInvoicesController;
use App\Invoice;
use App\Traits\SmtpSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;

class NewInvoice extends Notification implements ShouldQueue
{
    use Queueable, SmtpSettings;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;

        $this->emailSetting = email_notification_setting();

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
        $via = ['database', 'mail'];

        if ($this->emailSetting[10]->send_slack == 'yes') {
            array_push($via, 'slack');
        }

        if ($this->emailSetting[10]->send_push == 'yes') {
            array_push($via, OneSignalChannel::class);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('client.invoices.index');

        if (!is_null($this->invoice->project->client)) {
            // For Sending pdf to email
            $invoiceController = new ManageAllInvoicesController();
            $pdfOption = $invoiceController->domPdfObjectForDownload($this->invoice->id);
            $pdf = $pdfOption['pdf'];
            $filename = $pdfOption['fileName'];

            return (new MailMessage)
                ->subject(__('email.invoice.subject') . ' - ' . config('app.name') . '!')
                ->greeting(__('email.hello') . ' ' . ucwords($this->invoice->project->client->name) . '!')
                ->line(__('email.invoice.text'))
                ->action(__('email.loginDashboard'), $url)
                ->line(__('email.thankyouNote'))
                ->attachData($pdf->output(), $filename.'.pdf');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->invoice->toArray();
    }
}
