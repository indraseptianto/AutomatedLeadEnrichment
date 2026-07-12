<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Lead;

class LeadApiTest extends TestCase
{
    public function test_health_check(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200)
                 ->assertJson(['status' => 'ok', 'app' => 'Automated Lead Enrichment']);
    }

    public function test_can_create_lead(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+6281234567890',
            'company' => 'Test Corp',
            'source' => 'n8n',
            'industry' => 'Technology',
            'location' => 'Jakarta',
        ];

        $response = $this->postJson('/api/leads', $payload);
        $response->assertStatus(201)
                 ->assertJson(['ok' => true])
                 ->assertJsonPath('lead.name', 'Test User')
                 ->assertJsonPath('lead.email', 'test@example.com');
    }

    public function test_create_lead_requires_name(): void
    {
        $response = $this->postJson('/api/leads', []);
        $response->assertStatus(422);
    }

    public function test_can_list_leads(): void
    {
        Lead::factory()->count(3)->create();
        $response = $this->getJson('/api/leads');
        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_can_view_lead(): void
    {
        $lead = Lead::factory()->create(['name' => 'Specific Lead']);
        $response = $this->getJson("/api/leads/{$lead->id}");
        $response->assertStatus(200)
                 ->assertJsonPath('name', 'Specific Lead');
    }

    public function test_can_update_lead(): void
    {
        $lead = Lead::factory()->create();
        $response = $this->putJson("/api/leads/{$lead->id}", ['status' => 'contacted']);
        $response->assertStatus(200)
                 ->assertJsonPath('lead.status', 'contacted');
    }

    public function test_can_delete_lead(): void
    {
        $lead = Lead::factory()->create();
        $response = $this->deleteJson("/api/leads/{$lead->id}");
        $response->assertStatus(200)
                 ->assertJson(['ok' => true]);
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }
}
