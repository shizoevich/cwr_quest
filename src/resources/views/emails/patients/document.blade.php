@component('mail::message')
# Hello

Change Within Reach, Inc has sent you a document via secure email system. You have 14 days to download this document.

<p style="text-align:center;font-weight:bold;font-size:18px;">
    <a href="{{$documentUrl}}">
        DOWNLOAD
    </a>
</p>

**Please note: In order to download this document, you will need a password.**<br>
If you do not know the password, please reach out to us to get assistance via email or phone listed below:

Phone: (213) 908-1234<br>
Email: admin@cwr.care.

Thank you,<br>
Team @ Change Within Reach, Inc.

**Personal and Confidential Information**<br>
This message is intended only for the use of the individual or entity to which it is addressed and may contain information that is privileged, confidential, and exempt from disclosure.  If the reader of this message is not the intended recipient or an employee or agent responsible for delivering the message to the intended recipient, you are hereby notified that any dissemination, distribution, or copying of this communication is strictly prohibited.
@include('emails.partials.patient_email_unsubscribe_warning')
@endcomponent
