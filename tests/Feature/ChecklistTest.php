<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Column;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Project $project;
    protected Card $card;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->project = Project::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $column = $this->project->board->columns->first();
        $this->card = Card::factory()->create([
            'column_id' => $column->id,
        ]);
    }

    public function test_can_create_checklist(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/checklists', [
            'card_id' => $this->card->id,
            'title' => 'Test Checklist',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('checklists', [
            'card_id' => $this->card->id,
            'title' => 'Test Checklist',
        ]);
    }

    public function test_can_create_checklist_item(): void
    {
        $checklist = Checklist::factory()->create([
            'card_id' => $this->card->id,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/checklist-items', [
            'checklist_id' => $checklist->id,
            'title' => 'Test Item',
            'is_completed' => false,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('checklist_items', [
            'checklist_id' => $checklist->id,
            'title' => 'Test Item',
        ]);
    }

    public function test_can_toggle_checklist_item(): void
    {
        $checklist = Checklist::factory()->create([
            'card_id' => $this->card->id,
        ]);

        $item = ChecklistItem::factory()->create([
            'checklist_id' => $checklist->id,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/checklist-items/{$item->id}/toggle");

        $response->assertStatus(200);
        $this->assertTrue($item->fresh()->is_completed);

        // Toggle again
        $response = $this->actingAs($this->user)->postJson("/api/checklist-items/{$item->id}/toggle");

        $response->assertStatus(200);
        $this->assertFalse($item->fresh()->is_completed);
    }

    public function test_card_progress_updates_when_checklist_item_completed(): void
    {
        $checklist = Checklist::factory()->create([
            'card_id' => $this->card->id,
        ]);

        // Create 4 items
        $item1 = ChecklistItem::factory()->create(['checklist_id' => $checklist->id, 'is_completed' => false]);
        $item2 = ChecklistItem::factory()->create(['checklist_id' => $checklist->id, 'is_completed' => false]);
        $item3 = ChecklistItem::factory()->create(['checklist_id' => $checklist->id, 'is_completed' => false]);
        $item4 = ChecklistItem::factory()->create(['checklist_id' => $checklist->id, 'is_completed' => false]);

        // Initially progress should be 0
        $this->assertEquals(0, $this->card->fresh()->progress);

        // Complete 2 items (50%)
        $item1->update(['is_completed' => true]);
        $item2->update(['is_completed' => true]);

        $this->assertEquals(50, $this->card->fresh()->progress);

        // Complete all items (100%)
        $item3->update(['is_completed' => true]);
        $item4->update(['is_completed' => true]);

        $this->assertEquals(100, $this->card->fresh()->progress);
    }

    public function test_can_delete_checklist(): void
    {
        $checklist = Checklist::factory()->create([
            'card_id' => $this->card->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/checklists/{$checklist->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('checklists', [
            'id' => $checklist->id,
        ]);
    }
}
