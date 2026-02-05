<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Card;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Column;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class KanbanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a company
        $company = Company::firstOrCreate(
            ['name' => 'Demo Company'],
            ['type' => 'company']
        );

        // Create users
        $manager = User::firstOrCreate(
            ['email' => 'manager@demo.com'],
            [
                'name' => 'Project Manager',
                'password' => bcrypt('password'),
                'company_id' => $company->id,
                'role' => 'manager',
            ]
        );

        $developer1 = User::firstOrCreate(
            ['email' => 'dev1@demo.com'],
            [
                'name' => 'Developer 1',
                'password' => bcrypt('password'),
                'company_id' => $company->id,
                'role' => 'staff',
            ]
        );

        $developer2 = User::firstOrCreate(
            ['email' => 'dev2@demo.com'],
            [
                'name' => 'Developer 2',
                'password' => bcrypt('password'),
                'company_id' => $company->id,
                'role' => 'staff',
            ]
        );

        // Create a project (this will auto-create board, columns, and milestones)
        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'E-Commerce Platform',
            'description' => 'Build a modern e-commerce platform with payment integration',
            'manager_id' => $manager->id,
            'status' => 'active',
            'start_date' => now()->subDays(30),
            'end_date' => now()->addMonths(3),
            'budget' => 150000,
        ]);

        // Get the auto-created board and columns
        $board = $project->board;
        $columns = $board->columns;

        // Create cards in different columns
        $backlogColumn = $columns->where('name', 'Backlog')->first();
        $todoColumn = $columns->where('name', 'To Do')->first();
        $inProgressColumn = $columns->where('name', 'In Progress')->first();
        $reviewColumn = $columns->where('name', 'Review')->first();
        $doneColumn = $columns->where('name', 'Done')->first();

        // Backlog cards
        $this->createCardWithChecklist($backlogColumn, 0, [
            'title' => 'Setup payment gateway integration',
            'description' => 'Integrate Stripe and PayPal payment methods',
            'assignee_id' => null,
            'priority' => 'medium',
            'due_date' => now()->addDays(45),
        ], ['Research payment gateways', 'Choose best option', 'Setup merchant accounts']);

        // To Do cards
        $this->createCardWithChecklist($todoColumn, 0, [
            'title' => 'Design product catalog UI',
            'description' => 'Create responsive product listing and detail pages',
            'assignee_id' => $developer1->id,
            'priority' => 'high',
            'due_date' => now()->addDays(7),
        ], ['Create wireframes', 'Design mockups', 'Get approval']);

        $this->createCardWithChecklist($todoColumn, 1, [
            'title' => 'Implement shopping cart functionality',
            'description' => 'Build cart management system with session handling',
            'assignee_id' => $developer2->id,
            'priority' => 'high',
            'due_date' => now()->addDays(10),
        ], ['Design database schema', 'Create API endpoints', 'Add to cart feature', 'Update cart feature', 'Remove from cart feature']);

        // In Progress cards
        $this->createCardWithChecklist($inProgressColumn, 0, [
            'title' => 'User authentication system',
            'description' => 'Implement login, register, and password reset',
            'assignee_id' => $developer1->id,
            'priority' => 'urgent',
            'due_date' => now()->addDays(3),
        ], ['Setup authentication scaffolding', 'Create login page', 'Create registration page', 'Implement password reset', 'Add email verification'], [true, true, true, false, false]);

        // Review cards
        $this->createCardWithChecklist($reviewColumn, 0, [
            'title' => 'Product search and filtering',
            'description' => 'Add search functionality with filters',
            'assignee_id' => $developer2->id,
            'priority' => 'medium',
            'due_date' => now()->addDays(2),
        ], ['Implement search algorithm', 'Add category filters', 'Add price range filter', 'Add sort options', 'Write tests'], [true, true, true, true, false]);

        // Done cards
        $this->createCardWithChecklist($doneColumn, 0, [
            'title' => 'Setup project repository',
            'description' => 'Initialize Git repository and setup CI/CD',
            'assignee_id' => $manager->id,
            'priority' => 'high',
            'due_date' => now()->subDays(25),
        ], ['Create Git repository', 'Setup GitHub Actions', 'Configure environments'], [true, true, true]);

        $this->createCardWithChecklist($doneColumn, 1, [
            'title' => 'Database schema design',
            'description' => 'Design and create database migrations',
            'assignee_id' => $developer1->id,
            'priority' => 'urgent',
            'due_date' => now()->subDays(20),
        ], ['Design ERD', 'Create migrations', 'Seed test data'], [true, true, true]);
    }

    /**
     * Create a card with checklist items.
     */
    private function createCardWithChecklist(Column $column, int $position, array $cardData, array $checklistItems, array $completedStatuses = []): void
    {
        $card = $column->cards()->create(array_merge($cardData, ['position' => $position]));

        $checklist = $card->checklists()->create([
            'title' => 'Tasks',
            'position' => 0,
        ]);

        foreach ($checklistItems as $index => $itemTitle) {
            $checklist->items()->create([
                'title' => $itemTitle,
                'position' => $index,
                'is_completed' => $completedStatuses[$index] ?? false,
            ]);
        }

        // Update card progress
        $card->updateProgress();
    }
}
