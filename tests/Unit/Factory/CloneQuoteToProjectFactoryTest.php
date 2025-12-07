<?php

namespace Tests\Unit\Factory;

use App\Factory\CloneQuoteToProjectFactory;
use App\Models\Quote;
use App\Models\Project;
use Tests\TestCase;

class CloneQuoteToProjectFactoryTest extends TestCase
{
    public function test_create_clones_quote_to_project_properly()
    {
        $userId = 123;

        // Create a mock Quote
        $quote = new Quote();
        $quote->company_id = 1;
        $quote->client_id = 10;
        $quote->number = 'Q-001';
        $quote->public_notes = 'Public note';
        $quote->private_notes = 'Private note';

        // Call the factory
        $project = CloneQuoteToProjectFactory::create($quote, $userId);

        // Assertions
        $this->assertInstanceOf(Project::class, $project);
        $this->assertSame($quote->company_id, $project->company_id);
        $this->assertSame($userId, $project->user_id);
        $this->assertSame($quote->client_id, $project->client_id);
        $this->assertSame($quote->public_notes, $project->public_notes);
        $this->assertSame($quote->private_notes, $project->private_notes);
        $this->assertSame('0', (string)$project->budgeted_hours);
        $this->assertSame('0', (string)$project->task_rate);
        $this->assertSame('texts.quote_number_short Q-001', $project->name); // ctrans returns key if not loaded
        $this->assertSame('', $project->custom_value1);
        $this->assertSame('', $project->custom_value2);
        $this->assertSame('', $project->custom_value3);
        $this->assertSame('', $project->custom_value4);
        $this->assertFalse($project->is_deleted);
    }
}

