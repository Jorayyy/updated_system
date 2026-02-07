<?php

namespace App\Mail;

use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipReleased extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Payroll $payroll;
    public bool $attachPdf;

    /**
     * Create a new message instance.
     */
    public function __construct(Payroll $payroll, bool $attachPdf = true)
    {
        $this->payroll = $payroll;
        $this->attachPdf = $attachPdf;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $periodName = $this->payroll->payrollPeriod->start_date->format('M d') . ' - ' . 
                      $this->payroll->payrollPeriod->end_date->format('M d, Y');

        return new Envelope(
            subject: "Your Payslip for {$periodName} is Ready",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payslip-released',
            with: [
                'payroll' => $this->payroll,
                'employee' => $this->payroll->user,
                'period' => $this->payroll->payrollPeriod,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (!$this->attachPdf) {
            return [];
        }

        $this->payroll->load(['user', 'payrollPeriod']);
        
        $pdf = Pdf::loadView('payroll.payslip-pdf', [
            'payroll' => $this->payroll,
            'settings' => \App\Models\CompanySetting::getAllSettings(),
        ]);

        $filename = sprintf(
            'Payslip_%s_%s.pdf',
            $this->payroll->user->employee_id,
            $this->payroll->payrollPeriod->start_date->format('Y-m-d')
        );

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
