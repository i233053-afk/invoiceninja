<?php

namespace Tests\Unit\Transformers;

use Tests\TestCase;
use App\Models\ClientContact;
use App\Transformers\ClientContactLoginTransformer;

class ClientContactLoginTransformerTest extends TestCase
{
    public function testClientContactLoginTransformer()
    {
        // Fake model instance
        $contact = new ClientContact();
        $contact->id = 1;
        $contact->first_name = "John";
        $contact->last_name = "Doe";
        $contact->email = "john@example.com";
        $contact->created_at = now()->timestamp;
        $contact->updated_at = now()->timestamp;
        $contact->deleted_at = null;
        $contact->is_primary = true;
        $contact->is_locked = false;
        $contact->phone = "1234567890";
        $contact->custom_value1 = "cv1";
        $contact->custom_value2 = "cv2";
        $contact->custom_value3 = "cv3";
        $contact->custom_value4 = "cv4";
        $contact->token = "XYZ123TOKEN";

        $transformer = new ClientContactLoginTransformer();
        $transformed = $transformer->transform($contact);

        // Assertions
        $this->assertArrayHasKey('id', $transformed);
        $this->assertEquals($transformer->encodePrimaryKey(1), $transformed['id']);

        $this->assertEquals("John", $transformed['first_name']);
        $this->assertEquals("Doe", $transformed['last_name']);
        $this->assertEquals("john@example.com", $transformed['email']);

        $this->assertIsInt($transformed['created_at']);
        $this->assertIsInt($transformed['updated_at']);
        $this->assertEquals(0, $transformed['archived_at']);

        $this->assertTrue($transformed['is_primary']);
        $this->assertFalse($transformed['is_locked']);
        $this->assertEquals("1234567890", $transformed['phone']);

        $this->assertEquals("cv1", $transformed['custom_value1']);
        $this->assertEquals("cv2", $transformed['custom_value2']);
        $this->assertEquals("cv3", $transformed['custom_value3']);
        $this->assertEquals("cv4", $transformed['custom_value4']);

        $this->assertEquals("XYZ123TOKEN", $transformed['token']);
    }
}

