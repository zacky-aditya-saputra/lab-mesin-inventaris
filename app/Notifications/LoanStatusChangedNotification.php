<?php

namespace App\Notifications;

use App\Models\LoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanStatusChangedNotification extends Notification
{
    use Queueable;

    /**
     * Indonesian labels for each loan ticket status.
     */
    private const STATUS_LABELS = [
        'PENDING' => 'Menunggu Verifikasi',
        'APPROVED' => 'Disetujui',
        'PARTIALLY_APPROVED' => 'Disetujui Sebagian',
        'REJECTED' => 'Ditolak',
        'ON_LOAN' => 'Sedang Dipinjam',
        'OVERDUE' => 'Terlambat',
        'RETURNED' => 'Selesai (Dikembalikan)',
    ];

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
            ->subject('Status Peminjaman Diperbarui')
            ->line("Tiket {$this->loanRequest->ticket_number} kini berstatus {$this->statusLabel()}.")
            ->action('Lihat Riwayat Peminjaman', route('loans.history'));
    }

    /**
     * Get the array representation of the notification for the database channel.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'loan_status_changed',
            'loan_request_id' => $this->loanRequest->id,
            'ticket_number' => $this->loanRequest->ticket_number,
            'title' => 'Status Peminjaman Diperbarui',
            'message' => sprintf(
                'Tiket %s kini berstatus %s.',
                $this->loanRequest->ticket_number,
                $this->statusLabel(),
            ),
            'status' => $this->loanRequest->status,
            'url' => route('loans.history'),
        ];
    }

    /**
     * Get the Indonesian label of the current loan status.
     */
    private function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->loanRequest->status] ?? $this->loanRequest->status;
    }
}
