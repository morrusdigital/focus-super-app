<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\TaxMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_company_can_create_project_without_tax(): void
    {
        $company = Company::create([
            'name' => 'Company A',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'name' => 'Project Tanpa Pajak',
            'address' => 'Jl. Project 1',
            'contract_value' => 150000000,
            'use_pph' => 0,
            'use_ppn' => 0,
        ]);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', [
            'company_id' => $company->id,
            'name' => 'Project Tanpa Pajak',
            'use_pph' => false,
            'pph_tax_master_id' => null,
            'pph_rate' => null,
            'use_ppn' => false,
            'ppn_tax_master_id' => null,
            'ppn_rate' => null,
        ]);
    }

    public function test_admin_company_can_create_project_with_tax_and_store_snapshot_rate(): void
    {
        $company = Company::create([
            'name' => 'Company B',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $pph = TaxMaster::create([
            'tax_type' => TaxMaster::TYPE_PPH,
            'name' => 'PPH Final 2%',
            'percentage' => 2,
            'is_active' => true,
        ]);
        $ppn = TaxMaster::create([
            'tax_type' => TaxMaster::TYPE_PPN,
            'name' => 'PPN Umum 11%',
            'percentage' => 11,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'name' => 'Project Pajak',
            'address' => 'Jl. Project Pajak',
            'contract_value' => 200000000,
            'use_pph' => 1,
            'pph_tax_master_id' => $pph->id,
            'use_ppn' => 1,
            'ppn_tax_master_id' => $ppn->id,
        ]);

        $response->assertRedirect(route('projects.index'));

        $project = Project::query()->where('name', 'Project Pajak')->firstOrFail();
        $this->assertSame('2.00', (string) $project->pph_rate);
        $this->assertSame('11.00', (string) $project->ppn_rate);

        $pph->update(['percentage' => 3]);
        $ppn->update(['percentage' => 12]);

        $project->refresh();
        $this->assertSame('2.00', (string) $project->pph_rate);
        $this->assertSame('11.00', (string) $project->ppn_rate);
    }

    public function test_project_validation_requires_tax_master_when_tax_is_yes(): void
    {
        $company = Company::create([
            'name' => 'Company C',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'name' => 'Project Invalid',
            'address' => 'Jl. Invalid',
            'contract_value' => 300000000,
            'use_pph' => 1,
            'use_ppn' => 0,
        ]);

        $response->assertSessionHasErrors(['pph_tax_master_id']);
    }

    public function test_project_validation_rejects_wrong_tax_type_reference(): void
    {
        $company = Company::create([
            'name' => 'Company D',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $ppn = TaxMaster::create([
            'tax_type' => TaxMaster::TYPE_PPN,
            'name' => 'PPN Salah',
            'percentage' => 11,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'name' => 'Project Wrong Tax',
            'address' => 'Jl. Wrong Tax',
            'contract_value' => 250000000,
            'use_pph' => 1,
            'pph_tax_master_id' => $ppn->id,
            'use_ppn' => 0,
        ]);

        $response->assertSessionHasErrors(['pph_tax_master_id']);
    }

    public function test_legacy_project_is_marked_as_incomplete_in_index_and_show(): void
    {
        $company = Company::create([
            'name' => 'Company E',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Lama',
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('projects.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Belum Lengkap');

        $showResponse = $this->actingAs($admin)->get(route('projects.show', $project));
        $showResponse->assertOk();
        $showResponse->assertSee('Data project belum lengkap');
        $showResponse->assertSee('Total Kontrak Net');
        $showResponse->assertSee('-');
    }

    public function test_project_show_displays_total_net_contract_and_pph_nominal(): void
    {
        $company = Company::create([
            'name' => 'Company F',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $pph = TaxMaster::create([
            'tax_type' => TaxMaster::TYPE_PPH,
            'name' => 'PPH 2.5%',
            'percentage' => 2.5,
            'is_active' => true,
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Net',
            'address' => 'Jl. Net',
            'contract_value' => 100000000,
            'use_pph' => true,
            'pph_tax_master_id' => $pph->id,
            'pph_rate' => 2.5,
            'use_ppn' => false,
            'ppn_tax_master_id' => null,
            'ppn_rate' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertOk();
        $response->assertSee('Total Kontrak Net');
        $response->assertSee('Rp 97.500.000,00');
        $response->assertSee('Nominal PPH');
        $response->assertSee('Rp 2.500.000,00');
    }

    public function test_project_show_displays_start_work_date_and_elapsed_days(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 17, 10, 0, 0));

        $company = Company::create([
            'name' => 'Company G',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Start',
            'address' => 'Jl. Start',
            'start_work_date' => '2026-02-01',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertOk();
        $response->assertSee('Tanggal Start Kerja');
        $response->assertSee('01/02/2026');
        $response->assertSee('16 hari');

        Carbon::setTestNow();
    }
}
