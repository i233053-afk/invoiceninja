<?php

namespace App\Services\Bank {
    // Override nlog() inside Bank namespace
    function nlog($msg) {
        \Tests\Unit\Services\Bank\BankMatchingServiceTest::$nlog_output = $msg;
    }
}

namespace Tests\Unit\Services\Bank {

    use Tests\TestCase;
    use Mockery;
    use App\Services\Bank\BankMatchingService;
    use App\Services\Bank\BankService;
    use App\Models\BankTransaction;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Collection;

    class BankMatchingServiceTest extends TestCase
    {
        /** capture output from nlog() */
        public static string $nlog_output = '';

        public function tearDown(): void
        {
            Mockery::close();
            parent::tearDown();
        }

        /** @test */
        public function testMiddlewareReturnsWithoutOverlapping()
        {
            $service = new BankMatchingService(1, 'db1');
            $middlewares = $service->middleware();

            $this->assertCount(1, $middlewares);
            $this->assertStringContainsString('db1_1', $middlewares[0]->key);
        }

        /** @test */
        public function testHandleProcessesOnlyUnmatchedTransactions()
        {
            // Mock MultiDB::setDb
            $multiDbMock = Mockery::mock('alias:App\Libraries\MultiDB');
            $multiDbMock->shouldReceive('setDb')
                ->once()
                ->with('test_db');

            // Fake one BankTransaction
            $bt = new BankTransaction();

            // Mock BankService::processRules()
            $bankServiceMock = Mockery::mock('overload:' . BankService::class);
            $bankServiceMock->shouldReceive('processRules')->once();

            // Mock query builder chain via DB::table
            $queryMock = Mockery::mock();
            DB::shouldReceive('table')->with('bank_transactions')->andReturn($queryMock);
            $queryMock->shouldReceive('where')->with('company_id', 100)->andReturnSelf();
            $queryMock->shouldReceive('where')->with('status_id', BankTransaction::STATUS_UNMATCHED)->andReturnSelf();
            $queryMock->shouldReceive('cursor')->andReturn(collect([$bt]));

            // Execute handle
            $service = new BankMatchingService(100, 'test_db');
            $service->handle();

            $this->assertTrue(true); // Test passes if no exception
        }

        /** @test */
        public function testFailedMethodLogsExceptionAndDisablesFailedQueueDriver()
        {
            // Reset captured log
            self::$nlog_output = '';

            $exception = new \Exception("Test failure");

            $service = new BankMatchingService(1, 'db');
            $service->failed($exception);

            $this->assertStringContainsString(
                "BANKMATCHINGSERVICE:: Test failure",
                self::$nlog_output
            );

            $this->assertNull(config('queue.failed.driver'));
        }
    }
}

