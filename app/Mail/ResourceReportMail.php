<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResourceReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public array $stats;
    public string $aiAnalysis;
    public ?string $pdfBinary;
    public string $pdfFilename;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $userName,
        array $stats,
        string $aiAnalysis,
        ?string $pdfBinary = null,
        string $pdfFilename = 'laporan-resource-simox.pdf'
    ) {
        $this->userName = $userName;
        $this->stats = $stats;
        $this->aiAnalysis = $aiAnalysis;
        $this->pdfBinary = $pdfBinary;
        $this->pdfFilename = $pdfFilename;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $timeStr = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB';
        return new Envelope(
            subject: "[SIMOX AI] Laporan Pemakaian Resource Proxmox VE - {$timeStr}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.resource-report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfBinary) {
            return [
                Attachment::fromData(fn () => $this->pdfBinary, $this->pdfFilename)
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
