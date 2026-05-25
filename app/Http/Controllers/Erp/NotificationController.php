<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Student;
use App\Services\NotificationService;
use App\Support\BranchScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
        $this->middleware('finance');
    }

    public function index(): View
    {
        $logs = NotificationLog::query()
            ->with(['student', 'sentBy'])
            ->latest()
            ->limit(100)
            ->get();

        $overdue = $this->notifications->overdueInvoicesForReminders();

        return view('erp.notifications.index', compact('logs', 'overdue'));
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'channel' => 'required|in:email,sms,whatsapp',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
            'recipient' => 'nullable|string|max:255',
        ]);

        $student = Student::query()->findOrFail($data['student_id']);
        BranchScope::assertStudentAccess($student);

        $sentBy = auth()->id();

        if ($data['channel'] === 'email') {
            $to = $data['recipient'] ?: $student->parent_contact;
            if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return back()->with('error', 'Valid email required for this student.');
            }
            $ok = $this->notifications->sendEmail(
                $to,
                $data['subject'] ?: 'Message from Barefoot Martial Arts',
                $data['body'],
                $student,
                $sentBy
            );

            return back()->with($ok ? 'success' : 'error', $ok ? 'Email sent.' : 'Email failed — check mail config.');
        }

        $phone = $data['recipient'] ?: ($student->phone ?: $student->parent_contact);
        if (! $phone) {
            return back()->with('error', 'Phone number required.');
        }

        $this->notifications->logSms($phone, $data['body'], $student, $sentBy);

        if ($data['channel'] === 'whatsapp') {
            return back()->with('success', 'SMS logged. Open WhatsApp from fee reminders or student profile.');
        }

        return back()->with('success', 'SMS logged (configure SMS gateway separately).');
    }

    public function sendInvoiceReminder(Invoice $invoice): RedirectResponse
    {
        BranchScope::assertStudentAccess($invoice->student);
        $body = $this->notifications->feeReminderMessage($invoice);
        $student = $invoice->student;
        $email = filter_var($student->parent_contact, FILTER_VALIDATE_EMAIL) ? $student->parent_contact : null;

        if ($email) {
            $this->notifications->sendEmail($email, 'Fee reminder — '.$invoice->invoice_number, $body, $student, auth()->id());
        } else {
            $phone = $student->phone ?: $student->parent_contact;
            if ($phone) {
                $this->notifications->logSms($phone, $body, $student, auth()->id());
            }
        }

        return back()->with('success', 'Reminder queued/logged.');
    }
}
