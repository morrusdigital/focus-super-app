<?php

namespace Tests\Feature;

use App\Events\KanbanCardMoved;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Project $project;
    protected Board $board;
    protected Column $column;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'staff',
        ]);

        $this->project = Project::factory()->create([
            'company_id' => $this->company->id,
            'manager_id' => $this->user->id,
        ]);

        $this->board = $this->project->board;
        $this->column = $this->board->columns->first();
    }

    public function test_can_create_card(): void
    {
        $response = $this->actingAs($this->user)->postJson("/api/projects/{$this->project->id}/boards/{$this->board->id}/cards", [
            'column_id' => $this->column->id,
            'title' => 'Test Card',
            'description' => 'Test Description',
            'priority' => 'high',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'priority',
                'position',
            ],
        ]);

        $this->assertDatabaseHas('cards', [
            'column_id' => $this->column->id,
            'title' => 'Test Card',
        ]);
    }

    public function test_can_update_card(): void
    {
        $card = Card::factory()->create([
            'column_id' => $this->column->id,
            'assignee_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/projects/{$this->project->id}/boards/{$this->board->id}/cards/{$card->id}", [
            'title' => 'Updated Card',
            'priority' => 'urgent',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'title' => 'Updated Card',
            'priority' => 'urgent',
        ]);
    }

    public function test_can_move_card_within_same_column(): void
    {
        Event::fake([KanbanCardMoved::class]);

        // Create 3 cards in the same column
        $card1 = Card::factory()->create(['column_id' => $this->column->id, 'position' => 0]);
        $card2 = Card::factory()->create(['column_id' => $this->column->id, 'position' => 1]);
        $card3 = Card::factory()->create(['column_id' => $this->column->id, 'position' => 2]);

        // Move card1 from position 0 to position 2
        $response = $this->actingAs($this->user)->patchJson(
            "/api/projects/{$this->project->id}/boards/{$this->board->id}/cards/{$card1->id}/move",
            [
                'target_column_id' => $this->column->id,
                'target_position' => 2,
            ]
        );

        $response->assertStatus(200);

        // Check that positions were updated correctly
        $this->assertEquals(2, $card1->fresh()->position);
        $this->assertEquals(0, $card2->fresh()->position);
        $this->assertEquals(1, $card3->fresh()->position);

        Event::assertDispatched(KanbanCardMoved::class);
    }

    public function test_can_move_card_to_different_column(): void
    {
        Event::fake([KanbanCardMoved::class]);

        $targetColumn = $this->board->columns->skip(1)->first();

        // Create cards
        $card1 = Card::factory()->create(['column_id' => $this->column->id, 'position' => 0]);
        $card2 = Card::factory()->create(['column_id' => $this->column->id, 'position' => 1]);

        // Move card1 to different column at position 0
        $response = $this->actingAs($this->user)->patchJson(
            "/api/projects/{$this->project->id}/boards/{$this->board->id}/cards/{$card1->id}/move",
            [
                'target_column_id' => $targetColumn->id,
                'target_position' => 0,
            ]
        );

        $response->assertStatus(200);

        // Check that card moved to new column
        $this->assertEquals($targetColumn->id, $card1->fresh()->column_id);
        $this->assertEquals(0, $card1->fresh()->position);

        // Check that card2 position was updated in source column
        $this->assertEquals(0, $card2->fresh()->position);

        Event::assertDispatched(KanbanCardMoved::class);
    }

    public function test_can_delete_card(): void
    {
        $card = Card::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(
            "/api/projects/{$this->project->id}/boards/{$this->board->id}/cards/{$card->id}"
        );

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cards', [
            'id' => $card->id,
        ]);
    }
}
