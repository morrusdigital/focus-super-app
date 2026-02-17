<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectVendorFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_company_can_crud_project_vendor(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Vendor',
            'address' => 'Alamat',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $this->actingAs($admin)->post(route('projects.vendors.store', $project), [
            'name' => 'Vendor Satu',
        ])->assertRedirect(route('projects.show', $project));

        $vendor = ProjectVendor::firstOrFail();
        $this->assertDatabaseHas('project_vendors', [
            'project_id' => $project->id,
            'name' => 'Vendor Satu',
        ]);

        $this->actingAs($admin)->put(route('projects.vendors.update', [$project, $vendor]), [
            'name' => 'Vendor Update',
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_vendors', [
            'id' => $vendor->id,
            'name' => 'Vendor Update',
        ]);

        $this->actingAs($admin)->delete(route('projects.vendors.destroy', [$project, $vendor]))
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('project_vendors', [
            'id' => $vendor->id,
        ]);
    }

    public function test_vendor_name_is_unique_per_project(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();

        $projectOne = Project::create([
            'company_id' => $company->id,
            'name' => 'Project 1',
            'address' => 'Alamat 1',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);
        $projectTwo = Project::create([
            'company_id' => $company->id,
            'name' => 'Project 2',
            'address' => 'Alamat 2',
            'contract_value' => 200000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $this->actingAs($admin)->post(route('projects.vendors.store', $projectOne), [
            'name' => 'Vendor Sama',
        ])->assertRedirect(route('projects.show', $projectOne));

        $this->actingAs($admin)->post(route('projects.vendors.store', $projectOne), [
            'name' => 'Vendor Sama',
        ])->assertSessionHasErrors(['name']);

        $this->actingAs($admin)->post(route('projects.vendors.store', $projectTwo), [
            'name' => 'Vendor Sama',
        ])->assertRedirect(route('projects.show', $projectTwo));
    }

    public function test_finance_holding_cannot_manage_project_vendor(): void
    {
        $holding = Company::create([
            'name' => 'Holding',
            'type' => 'holding',
        ]);
        $company = Company::create([
            'name' => 'Child Co',
            'type' => 'company',
            'parent_id' => $holding->id,
        ]);

        $finance = User::factory()->create([
            'company_id' => $holding->id,
            'role' => 'finance_holding',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Child',
            'address' => 'Alamat',
            'contract_value' => 150000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $this->actingAs($finance)->post(route('projects.vendors.store', $project), [
            'name' => 'Vendor Finance',
        ])->assertForbidden();
    }

    private function makeCompanyAdmin(): array
    {
        $company = Company::create([
            'name' => 'Company',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        return [$company, $admin];
    }
}
