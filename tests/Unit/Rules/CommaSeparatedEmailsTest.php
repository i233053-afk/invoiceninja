<?php

namespace Tests\Unit\Rules;

use App\Rules\CommaSeparatedEmails;
use PHPUnit\Framework\TestCase;

class CommaSeparatedEmailsTest extends TestCase
{
    public function test_validates_single_email()
    {
        $rule = new CommaSeparatedEmails();
        $failed = false;
        
        $rule->validate('emails', 'test@example.com', function() use (&$failed) {
            $failed = true;
        });
        
        $this->assertFalse($failed);
    }
    
    public function test_validates_multiple_emails()
    {
        $rule = new CommaSeparatedEmails();
        $failed = false;
        
        $rule->validate('emails', 'test1@example.com, test2@example.com, test3@example.com', function() use (&$failed) {
            $failed = true;
        });
        
        $this->assertFalse($failed);
    }
    
    public function test_validates_emails_with_whitespace()
    {
        $rule = new CommaSeparatedEmails();
        $failed = false;
        
        $rule->validate('emails', '  test1@example.com  ,  test2@example.com  ', function() use (&$failed) {
            $failed = true;
        });
        
        $this->assertFalse($failed);
    }
    
    public function test_fails_with_invalid_email()
    {
        $rule = new CommaSeparatedEmails();
        $failed = false;
        $errorMessage = '';
        
        $rule->validate('emails', 'invalid-email, test@example.com', function($message) use (&$failed, &$errorMessage) {
            $failed = true;
            $errorMessage = $message;
        });
        
        $this->assertTrue($failed);
        $this->assertStringContainsString('invalid-email', $errorMessage);
    }
    
    public function test_fails_with_empty_emails()
    {
        $rule = new CommaSeparatedEmails();
        $failed = false;
        
        $rule->validate('emails', 'test@example.com, , test2@example.com', function() use (&$failed) {
            $failed = true;
        });
        
        $this->assertFalse($failed); // Empty emails are filtered out
    }
    
    public function test_fails_when_exceeding_max_emails()
    {
        $rule = new CommaSeparatedEmails(2); // Max 2 emails
        $failed = false;
        $errorMessage = '';
        
        $rule->validate('emails', 'test1@example.com, test2@example.com, test3@example.com', function($message) use (&$failed, &$errorMessage) {
            $failed = true;
            $errorMessage = $message;
        });
        
        $this->assertTrue($failed);
        $this->assertStringContainsString('cannot contain more than 2', $errorMessage);
    }
    
    public function test_validates_null_value()
    {
        $rule = new CommaSeparatedEmails();
        $failed = false;
        
        $rule->validate('emails', null, function() use (&$failed) {
            $failed = true;
        });
        
        $this->assertFalse($failed);
    }
    
    public function test_validates_empty_string()
    {
        $rule = new CommaSeparatedEmails();
        $failed = false;
        
        $rule->validate('emails', '', function() use (&$failed) {
            $failed = true;
        });
        
        $this->assertFalse($failed);
    }

public function test_rejects_invalid_separator_usage()
{
    $rule = new CommaSeparatedEmails(10, [';', ',']);
    $failed = false;

    // Using a clearly invalid email that PHP won't accept
    $rule->validate('emails', 'test@example.com; invalid', function() use (&$failed) {
        $failed = true;
    });

    $this->assertTrue($failed);
}


public function test_trailing_comma_is_ignored()
{
    $rule = new CommaSeparatedEmails();
    $failed = false;

    $rule->validate('emails', 'test@example.com,', function() use (&$failed) {
        $failed = true;
    });

    $this->assertFalse($failed);
}

public function test_leading_comma_is_ignored()
{
    $rule = new CommaSeparatedEmails();
    $failed = false;

    $rule->validate('emails', ',test@example.com', function() use (&$failed) {
        $failed = true;
    });

    $this->assertFalse($failed);
}

public function test_only_commas_is_invalid()
{
    $rule = new CommaSeparatedEmails();
    $failed = false;

    $rule->validate('emails', ',,,,', function() use (&$failed) {
        $failed = true;
    });

    $this->assertTrue($failed);
}

public function test_invalid_email_fails_immediately()
{
    $rule = new CommaSeparatedEmails();
    $failed = 0;

    $rule->validate('emails', 'bad-email,also-bad', function() use (&$failed) {
        $failed++;
    });

    // Should fail once & stop
    $this->assertEquals(1, $failed);
}

public function test_uppercase_emails_are_valid()
{
    $rule = new CommaSeparatedEmails();
    $failed = false;

    $rule->validate('emails', 'TEST@EXAMPLE.COM, USER@MAIL.COM', function() use (&$failed) {
        $failed = true;
    });

    $this->assertFalse($failed);
}

public function test_unicode_emails_fail_validation()
{
    $rule = new CommaSeparatedEmails();
    $failed = false;

    $rule->validate('emails', 'tést@exámple.com', function() use (&$failed) {
        $failed = true;
    });

    $this->assertTrue($failed);
}

public function test_static_parse_emails_basic()
{
    $emails = CommaSeparatedEmails::parseEmails('a@a.com, b@b.com , c@c.com');
    $this->assertEquals(['a@a.com','b@b.com','c@c.com'], array_values($emails));
}

public function test_static_parse_emails_empty()
{
    $emails = CommaSeparatedEmails::parseEmails('');
    $this->assertEquals([], $emails);
}

public function test_static_is_valid_email_true()
{
    $this->assertTrue(CommaSeparatedEmails::isValidEmail('valid@example.com'));
}

public function test_static_is_valid_email_false()
{
    $this->assertFalse(CommaSeparatedEmails::isValidEmail('invalid-email'));
}

public function test_maxemails_exact_limit_passes()
{
    $rule = new CommaSeparatedEmails(3);
    $failed = false;

    $rule->validate('emails', 'a@a.com,b@b.com,c@c.com', function() use (&$failed) {
        $failed = true;
    });

    $this->assertFalse($failed);
}

public function test_maxemails_zero_disallows_all()
{
    $rule = new CommaSeparatedEmails(0);
    $failed = false;

    $rule->validate('emails', 'a@a.com', function() use (&$failed) {
        $failed = true;
    });

    $this->assertTrue($failed);
}

}
