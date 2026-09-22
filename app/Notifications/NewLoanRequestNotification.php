<?php

namespace App\Notifications;

use App\Models\LoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLoanRequestNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public LoanRequest $loanRequest)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan Peminjaman Baru')
            ->line("Tiket {$this->loanRequest->ticket_number} menunggu verifikasi Anda.")
            ->line("Peminjam: {$this->loanRequest->user?->name}")
            ->line("Laboratorium: {$this->loanRequest->laboratory?->name}");
    }

    /**
     * Get the array representation of the notification for the database channel.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_loan_request',
            'loan_request_id' => $this->loanRequest->id,
            'ticket_number' => $this->loanRequest->ticket_number,
            'title' => 'Pengajuan Peminjaman Baru',
            'message' => sprintf(
                '%s mengajukan peminjaman alat di %s.',
                $this->loanRequest->user?->name ?? 'Peminjam',
                $this->loanRequest->laboratory?->name ?? 'laboratorium',
            ),
            'status' => $this->loanRequest->status,
            'url' => route('admin.loans.review', $this->loanRequest),
        ];
    }
}
