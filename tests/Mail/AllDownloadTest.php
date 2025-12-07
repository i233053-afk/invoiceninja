<?php

namespace Tests\Mail;

use Tests\TestCase;
use App\Mail\DownloadBackup;
use App\Models\Company;
use App\Models\Account;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Mail\DownloadCredits;
use App\Mail\DownloadDocuments;
use App\Mail\DownloadInvoices;
use App\Mail\DownloadPurchaseOrders;
use App\Mail\DownloadQuotes;

class AllDownloadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_builds_email_with_all_data_and_sets_locale()
    {
        $account = Account::factory()->create();

        // Convert settings object → array
        $defaultSettings = (array) \App\DataMapper\CompanySettings::defaults();

        // Add company_name
        $defaultSettings['company_name'] = 'Test Company';

        // Create company with modified settings
        $company = Company::factory()
            ->for($account)
            ->create(['settings' => $defaultSettings]);

        $filePath = storage_path('backups/test_backup.zip');

        $mail = new DownloadBackup($filePath, $company);

        $this->assertSame($filePath, $mail->file_path);
        $this->assertSame($company->id, $mail->company->id);

        $builtMail = $mail->build();

        $this->assertInstanceOf(DownloadBackup::class, $builtMail);

        // Locale should match company locale
        $this->assertSame(
            $company->getLocale(),
            App::getLocale()
        );

        // Subject must contain company name
       $this->assertSame(
    'Your company backup is ready for download',
    $builtMail->subject
);


        // View must receive correct data
        $this->assertArrayHasKey('url', $builtMail->viewData);
        $this->assertSame($filePath, $builtMail->viewData['url']);
    }
    
    
      /** @test */
    public function it_builds_email_with_all_data()
    {
        // Create account
        $account = Account::factory()->create([
            'plan' => 'pro',
        ]);

        // Create company linked to account
        $company = Company::factory()->for($account)->create([
            'settings' => (object) array_merge(
                (array) \App\DataMapper\CompanySettings::defaults(),
                ['company_name' => 'Test Company']
            ),
        ]);

        // Fake presenter object
        $fakePresenter = new class {
            public $logo = 'test_logo.png';
            public function name() {
                return 'Test Company';
            }
        };

        // Override the present() method on the company
        $company = $company->fresh();
        $company = new class($company, $fakePresenter) extends Company {
            public $fakePresenter;
            public function __construct($company, $fakePresenter) {
                foreach ($company->getAttributes() as $key => $value) {
                    $this->$key = $value;
                }
                $this->fakePresenter = $fakePresenter;
            }
            public function present() {
                return $this->fakePresenter;
            }
        };

        $filePath = storage_path('backups/test_credits.zip');

        $mail = new DownloadCredits($filePath, $company);
        $builtMail = $mail->build();

        // Assertions
        $this->assertSame($company->getLocale(), App::getLocale());
        $this->assertSame(ctrans('texts.download_files'), $builtMail->subject);
        $this->assertSame($filePath, $builtMail->viewData['url']);
        $this->assertSame('test_logo.png', $builtMail->viewData['logo']);
        $this->assertTrue($builtMail->viewData['whitelabel']);
        $this->assertEquals($company->settings, $builtMail->viewData['settings']);
        $this->assertSame('Test Company', $builtMail->viewData['greeting']);
    }
    /** @test */
public function Document_builds_email_with_all_data()
    {
        // Create a paid account
        $account = Account::factory()->create([
            'plan' => 'pro',
        ]);

        // Create a company linked to the account
        $company = Company::factory()->for($account)->create([
            'settings' => (object) array_merge(
                (array) \App\DataMapper\CompanySettings::defaults(),
                ['company_name' => 'Test Company']
            ),
        ]);

        // Fake presenter object for logo and name
        $fakePresenter = new class {
            public $logo = 'test_logo.png';
            public function name() {
                return 'Test Company';
            }
        };

        // Override present() method in a company subclass
        $company = $company->fresh();
        $company = new class($company, $fakePresenter) extends Company {
            public $fakePresenter;

            public function __construct($company, $fakePresenter)
            {
                foreach ($company->getAttributes() as $key => $value) {
                    $this->$key = $value;
                }
                $this->fakePresenter = $fakePresenter;
            }

            public function present()
            {
                return $this->fakePresenter;
            }
        };

        $filePath = storage_path('backups/test_documents.zip');

        // Instantiate the mailable
        $mail = new DownloadDocuments($filePath, $company);
        $builtMail = $mail->build();

        // Assertions
        $this->assertSame($company->getLocale(), App::getLocale());
        $this->assertSame(ctrans('texts.download_files'), $builtMail->subject);
        $this->assertSame($filePath, $builtMail->viewData['url']);
        $this->assertSame('test_logo.png', $builtMail->viewData['logo']);
        $this->assertTrue($builtMail->viewData['whitelabel']);
        $this->assertEquals($company->settings, $builtMail->viewData['settings']);
        $this->assertSame('Test Company', $builtMail->viewData['greeting']);
    }
/** @test */
    public function invoice_builds_email_with_all_data()
    {
        // Create a paid account
        $account = Account::factory()->create([
            'plan' => 'pro',
        ]);

        // Create a company linked to the account
        $company = Company::factory()->for($account)->create([
            'settings' => (object) array_merge(
                (array) \App\DataMapper\CompanySettings::defaults(),
                ['company_name' => 'Test Company']
            ),
        ]);

        // Create a fake presenter for logo and name
        $fakePresenter = new class {
            public $logo = 'test_logo.png';
            public function name() {
                return 'Test Company';
            }
        };

        // Override present() method in a company subclass
        $company = $company->fresh();
        $company = new class($company, $fakePresenter) extends Company {
            public $fakePresenter;

            public function __construct($company, $fakePresenter)
            {
                foreach ($company->getAttributes() as $key => $value) {
                    $this->$key = $value;
                }
                $this->fakePresenter = $fakePresenter;
            }

            public function present()
            {
                return $this->fakePresenter;
            }
        };

        $filePath = storage_path('backups/test_invoices.zip');

        // Instantiate the mailable
        $mail = new DownloadInvoices($filePath, $company);
        $builtMail = $mail->build();

        // Assertions
        $this->assertSame($company->getLocale(), App::getLocale());
        $this->assertSame(ctrans('texts.download_files'), $builtMail->subject);
        $this->assertSame($filePath, $builtMail->viewData['url']);
        $this->assertSame('test_logo.png', $builtMail->viewData['logo']);
        $this->assertTrue($builtMail->viewData['whitelabel']);
        $this->assertEquals($company->settings, $builtMail->viewData['settings']);
        $this->assertSame('Test Company', $builtMail->viewData['greeting']);
    }
 /** @test */
    public function Purchase_builds_email_with_all_data()
    {
        // Create a paid account
        $account = Account::factory()->create([
            'plan' => 'pro',
        ]);

        // Create a company linked to the account
        $company = Company::factory()->for($account)->create([
            'settings' => (object) array_merge(
                (array) \App\DataMapper\CompanySettings::defaults(),
                ['company_name' => 'Test Company']
            ),
        ]);

        // Fake presenter object for logo and name
        $fakePresenter = new class {
            public $logo = 'test_logo.png';
            public function name() {
                return 'Test Company';
            }
        };

        // Override present() method in a company subclass
        $company = $company->fresh();
        $company = new class($company, $fakePresenter) extends Company {
            public $fakePresenter;

            public function __construct($company, $fakePresenter)
            {
                foreach ($company->getAttributes() as $key => $value) {
                    $this->$key = $value;
                }
                $this->fakePresenter = $fakePresenter;
            }

            public function present()
            {
                return $this->fakePresenter;
            }
        };

        $filePath = storage_path('backups/test_purchase_orders.zip');

        // Instantiate the mailable
        $mail = new DownloadPurchaseOrders($filePath, $company);
        $builtMail = $mail->build();

        // Assertions
        $this->assertSame($company->getLocale(), App::getLocale());
        $this->assertSame(ctrans('texts.download_files'), $builtMail->subject);
        $this->assertSame($filePath, $builtMail->viewData['url']);
        $this->assertSame('test_logo.png', $builtMail->viewData['logo']);
        $this->assertTrue($builtMail->viewData['whitelabel']);
        $this->assertEquals($company->settings, $builtMail->viewData['settings']);
        $this->assertSame('Test Company', $builtMail->viewData['greeting']);
    }
 /** @test */
    public function Quotes_builds_email_with_all_data()
    {
        // Create a paid account
        $account = Account::factory()->create([
            'plan' => 'pro',
        ]);

        // Create a company linked to the account
        $company = Company::factory()->for($account)->create([
            'settings' => (object) array_merge(
                (array) \App\DataMapper\CompanySettings::defaults(),
                ['company_name' => 'Test Company']
            ),
        ]);

        // Fake presenter object for logo and name
        $fakePresenter = new class {
            public $logo = 'test_logo.png';
            public function name() {
                return 'Test Company';
            }
        };

        // Override present() method in a company subclass
        $company = $company->fresh();
        $company = new class($company, $fakePresenter) extends Company {
            public $fakePresenter;

            public function __construct($company, $fakePresenter)
            {
                foreach ($company->getAttributes() as $key => $value) {
                    $this->$key = $value;
                }
                $this->fakePresenter = $fakePresenter;
            }

            public function present()
            {
                return $this->fakePresenter;
            }
        };

        $filePath = storage_path('backups/test_quotes.zip');

        // Instantiate the mailable
        $mail = new DownloadQuotes($filePath, $company);
        $builtMail = $mail->build();

        // Assertions
        $this->assertSame($company->getLocale(), App::getLocale());
        $this->assertSame(ctrans('texts.download_files'), $builtMail->subject);
        $this->assertSame($filePath, $builtMail->viewData['url']);
        $this->assertSame('test_logo.png', $builtMail->viewData['logo']);
        $this->assertTrue($builtMail->viewData['whitelabel']);
        $this->assertEquals($company->settings, $builtMail->viewData['settings']);
        $this->assertSame('Test Company', $builtMail->viewData['greeting']);
    }

}

