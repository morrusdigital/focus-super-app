<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\TaxMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxMasterFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_company_can_create_tax_master(): void
    {
        $company = Company::create([
            'name' => 'Company A',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $response = $this->actingAs($admin)->post(route('tax-masters.store'), [
            'tax_type' => TaxMaster::TYPE_PPH,
            'name' => 'PPH Final',
            'percentage' => 2.5,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('tax-masters.index'));
        $this->assertDatabaseHas('tax_masters', [
            'tax_type' => TaxMaster::TYPE_PPH,
            'name' => 'PPH Final',
            'percentage' => 2.50,
            'is_active' => true,
        ]);
    }

    public function test_non_admin_company_cannot_manage_tax_master(): void
    {
        $holding = Company::create([
            'name' => 'Holding',
            'type' => 'holding',
        ]);

        $finance = User::factory()->create([
            'company_id' => $holding->id,
            'role' => 'finance_holding',
        ]);

        $response = $this->actingAs($finance)->get(route('tax-masters.index'));
        $response->assertForbidden();
    }

    public function test_tax_master_validation_rejects_invalid_type_and_percentage(): void
    {
        $company = Company::create([
            'name' => 'Company B',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $response = $this->actingAs($admin)->post(route('tax-masters.store'), [
            'tax_type' => 'other',
            'name' => 'Tarif Invalid',
            'percentage' => 120,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors(['tax_type', 'percentage']);
    }

    public function test_tax_master_validation_rejects_duplicate_name_per_type(): void
    {
        $company = Company::create([
            'name' => 'Company C',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        TaxMaster::create([
            'tax_type' => TaxMaster::TYPE_PPN,
            'name' => 'PPN Umum',
            'percentage' => 11,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('tax-masters.store'), [
            'tax_type' => TaxMaster::TYPE_PPN,
            'name' => 'PPN Umum',
            'percentage' => 12,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors(['name']);
    }
}
