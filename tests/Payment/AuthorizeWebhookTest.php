public function test_void_webhook_changes_payment_status_and_dispatches_mail()
{
    Mail::fake();
    Queue::fake();

    // Create company, gateway with signatureKey, client, payment
    $company = Company::factory()->create();
    $companyGateway = CompanyGateway::factory()->create(['company_id' => $company->id]);
    $companyGateway->setConfigField('signatureKey', '0123456789abcdef0123456789abcdef'); // hex
    $companyGateway->company_id = $company->id;
    $companyGateway->save();

    $client = Client::factory()->create(['company_id' => $company->id]);
    $payment = Payment::factory()->create([
        'company_id' => $company->id,
        'transaction_reference' => '80040995616',
        'status_id' => Payment::STATUS_COMPLETED,
        'client_id' => $client->id,
        'amount' => 100,
    ]);

    $payload = [
        'eventType' => 'net.authorize.payment.void.created',
        'payload' => ['id' => '80040995616'],
    ];

    $raw = json_encode($payload);
    $sigKey = '0123456789abcdef0123456789abcdef';
    $signature = strtoupper(hash_hmac('sha512', $raw, $sigKey));
    $headers = ['X-ANET-SIGNATURE' => 'sha512=' . $signature];

    $this->postJson('/webhooks/authorize', $payload, $headers)->assertNoContent();

    $payment->refresh();
    $this->assertEquals(Payment::STATUS_FAILED, $payment->status_id);

    // PaymentFailedMailer dispatched
    Queue::assertPushed(\App\Jobs\Mail\PaymentFailedMailer::class);
}
