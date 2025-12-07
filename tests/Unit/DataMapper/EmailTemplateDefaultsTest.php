<?php

namespace Tests\Unit\DataMapper;

use App\DataMapper\EmailTemplateDefaults;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class EmailTemplateDefaultsTest extends TestCase
{
    /** @test */
    public function it_contains_expected_templates_array()
    {
        $defaults = new EmailTemplateDefaults();

        $this->assertIsArray($defaults->templates);
        $this->assertContains('email_template_invoice', $defaults->templates);
        $this->assertContains('email_template_quote', $defaults->templates);
        $this->assertContains('email_template_payment_failed', $defaults->templates);
    }

    /** @test */
    public function it_returns_invoice_template()
    {
        $result = EmailTemplateDefaults::getDefaultTemplate('email_template_invoice', 'en');

        $this->assertStringContainsString('<p>$client', $result);
    }

    /** @test */
    public function it_returns_quote_template()
    {
        $result = EmailTemplateDefaults::getDefaultTemplate('email_template_quote', 'en');

        $this->assertStringContainsString('<p>$client', $result);
    }

    /** @test */
    public function it_returns_payment_failed_template_and_subject()
    {
        $body = EmailTemplateDefaults::getDefaultTemplate('email_template_payment_failed', 'en');
        $subject = EmailTemplateDefaults::getDefaultTemplate('email_subject_payment_failed', 'en');

        $this->assertStringContainsString('<p>$client', $body);
        $this->assertStringContainsString('notification_invoice_payment_failed_subject', $subject);
    }

    /** @test */
    public function it_returns_vendor_notification_templates()
    {
        $subject = EmailTemplateDefaults::getDefaultTemplate('email_vendor_notification_subject', 'en');
        $body = EmailTemplateDefaults::getDefaultTemplate('email_vendor_notification_body', 'en');

        $this->assertStringContainsString('vendor_notification_subject', $subject);
        $this->assertStringContainsString('vendor_notification_body', $body);
    }

    /** @test */
    public function it_falls_back_to_invoice_template_for_unknown_template()
    {
        $result = EmailTemplateDefaults::getDefaultTemplate('unknown_template', 'en');

        $this->assertStringContainsString('<p>$client', $result);
    }

    /** @test */
    public function it_sets_locale_when_getting_template()
    {
        App::shouldReceive('setLocale')->once()->with('fr');

        EmailTemplateDefaults::getDefaultTemplate('email_template_invoice', 'fr');
    }
    /** @test */
    public function it_returns_credit_template()
    {
        $result = EmailTemplateDefaults::getDefaultTemplate('email_template_credit', 'en');
        $this->assertStringContainsString('<p>$client', $result);
    }

    /** @test */
    public function it_returns_payment_template()
    {
        $result = EmailTemplateDefaults::getDefaultTemplate('email_template_payment', 'en');
        $this->assertStringContainsString('<p>$client', $result);
    }

    /** @test */
    public function it_returns_payment_partial_template()
    {
        $result = EmailTemplateDefaults::getDefaultTemplate('email_template_payment_partial', 'en');
        $this->assertStringContainsString('<p>$client', $result);
    }

    /** @test */
    public function it_returns_statement_template()
    {
        $result = EmailTemplateDefaults::getDefaultTemplate('email_template_statement', 'en');
        $this->assertStringContainsString('<p>$client', $result);
    }

    /** @test */
    public function it_returns_reminder_templates()
    {
        $reminders = [
            'email_template_reminder1',
            'email_template_reminder2',
            'email_template_reminder3',
            'email_template_reminder_endless',
        ];

        foreach ($reminders as $template) {
            $result = EmailTemplateDefaults::getDefaultTemplate($template, 'en');
            $this->assertStringContainsString('<p>$client', $result);
        }
    }

    /** @test */
    public function it_returns_custom_templates()
    {
        $customs = [
            'email_template_custom1',
            'email_template_custom2',
            'email_template_custom3',
        ];

        foreach ($customs as $template) {
            $result = EmailTemplateDefaults::getDefaultTemplate($template, 'en');
            $this->assertStringContainsString('<p>$client', $result);
        }
    }

    /** @test */
    public function it_returns_purchase_order_template()
    {
        $result = EmailTemplateDefaults::getDefaultTemplate('email_template_purchase_order', 'en');
        $this->assertStringContainsString('<p>$vendor', $result);
    }

    /** @test */
    public function it_returns_statement_subject()
    {
        $result = EmailTemplateDefaults::getDefaultTemplate('email_subject_statement', 'en');
        $this->assertIsString($result);
$this->assertNotEmpty($result);
    }
    
    /** @test */
public function it_returns_quote_reminder1_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailQuoteReminder1Subject();

    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}

/** @test */
public function it_returns_quote_reminder1_body()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailQuoteReminder1Body();

    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}

/** @test */
public function it_returns_invoice_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailInvoiceSubject();

    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}

/** @test */
public function it_returns_credit_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailCreditSubject();

    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}
/** @test */
public function it_returns_quote_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailQuoteSubject();

    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}

/** @test */
public function it_returns_payment_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailPaymentSubject();

    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}

/** @test */
public function it_returns_purchase_order_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailPurchaseOrderSubject();

    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}
/** @test */
public function it_returns_payment_partial_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailPaymentPartialSubject();
    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}

/** @test */
public function it_returns_reminder1_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailReminder1Subject();
    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}

/** @test */
public function it_returns_reminder2_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailReminder2Subject();
    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}

/** @test */
public function it_returns_reminder3_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailReminder3Subject();
    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}

/** @test */
public function it_returns_reminder_endless_subject()
{
    $result = \App\DataMapper\EmailTemplateDefaults::emailReminderEndlessSubject();
    $this->assertIsString($result);
    $this->assertNotEmpty($result);
}
}
