<?php

namespace Tests\Unit\Repositories;

use App\Models\Document;
use App\Repositories\DocumentRepository;
use Mockery;
use Tests\TestCase;

class DocumentRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_delete_removes_file_and_forces_delete()
    {
        $document = Mockery::mock(Document::class)->makePartial();
        $documentable = Mockery::mock();
        $document->documentable = $documentable;

        $document->shouldReceive('deleteFile')->once();
        $document->shouldReceive('forceDelete')->once();
        $documentable->shouldReceive('touch')->once();

        $repo = new DocumentRepository();
        $repo->delete($document);
    }

    public function test_delete_handles_null_documentable()
    {
        $document = Mockery::mock(Document::class)->makePartial();
        $document->documentable = null;

        $document->shouldReceive('deleteFile')->once();
        $document->shouldReceive('forceDelete')->once();

        $repo = new DocumentRepository();
        $repo->delete($document);
    }
 public function test_delete_calls_deleteFile_forceDelete_and_touches_documentable()
    {
        // Create a mock for the documentable model
        $documentable = Mockery::mock();
        $documentable->shouldReceive('touch')->once(); // must be called

        // Create a mock for the document
        $document = Mockery::mock(Document::class);
        $document->documentable = $documentable;
        $document->shouldReceive('deleteFile')->once();   // must be called
        $document->shouldReceive('forceDelete')->once();  // must be called

        // Call the repository
        $repo = new DocumentRepository();
        $repo->delete($document);
    }

    public function test_delete_skips_touch_if_no_documentable()
    {
        $document = Mockery::mock(Document::class);
        $document->documentable = null;
        $document->shouldReceive('deleteFile')->once();
        $document->shouldReceive('forceDelete')->once();

        $repo = new DocumentRepository();
        $repo->delete($document);
    }
    public function test_restore_does_nothing_for_now()
    {
        // Since restore is empty/commented, we just check no exceptions occur
        $document = Mockery::mock(Document::class);

        $repo = new DocumentRepository();
        $repo->restore($document);

        $this->assertTrue(true); // just to mark test as passed
    }

    public function test_archive_does_nothing_for_now()
    {
        // Since archive is empty, just ensure no exceptions
        $document = Mockery::mock(Document::class);

        $repo = new DocumentRepository();
        $repo->archive($document);

        $this->assertTrue(true);
    }
}

