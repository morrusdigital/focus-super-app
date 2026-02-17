<?php

namespace Tests\Unit;

use App\Models\Project;
use Tests\TestCase;

class ProjectCalculationTest extends TestCase
{
    public function test_pph_amount_and_net_contract_with_pph_enabled(): void
    {
        $project = new Project([
            'contract_value' => 100000000,
            'use_pph' => true,
            'pph_rate' => 2.5,
        ]);

        $this->assertSame(2500000.0, $project->pph_amount);
        $this->assertSame(97500000.0, $project->net_contract_value);
    }

    public function test_pph_amount_and_net_contract_with_pph_disabled(): void
    {
        $project = new Project([
            'contract_value' => 100000000,
            'use_pph' => false,
            'pph_rate' => 2.5,
        ]);

        $this->assertSame(0.0, $project->pph_amount);
        $this->assertSame(100000000.0, $project->net_contract_value);
    }

    public function test_pph_amount_and_net_contract_are_null_when_contract_value_is_null(): void
    {
        $project = new Project([
            'contract_value' => null,
            'use_pph' => true,
            'pph_rate' => 2.5,
        ]);

        $this->assertNull($project->pph_amount);
        $this->assertNull($project->net_contract_value);
    }

    public function test_ppn_amount_and_contract_value_with_ppn_when_ppn_enabled(): void
    {
        $project = new Project([
            'contract_value' => 100000000,
            'use_ppn' => true,
            'ppn_rate' => 11,
        ]);

        $this->assertSame(11000000.0, $project->ppn_amount);
        $this->assertSame(111000000.0, $project->contract_value_with_ppn);
    }

    public function test_ppn_amount_and_contract_value_with_ppn_when_ppn_disabled(): void
    {
        $project = new Project([
            'contract_value' => 100000000,
            'use_ppn' => false,
            'ppn_rate' => 11,
        ]);

        $this->assertSame(0.0, $project->ppn_amount);
        $this->assertSame(100000000.0, $project->contract_value_with_ppn);
    }

    public function test_ppn_amount_and_contract_value_with_ppn_are_null_when_contract_value_is_null(): void
    {
        $project = new Project([
            'contract_value' => null,
            'use_ppn' => true,
            'ppn_rate' => 11,
        ]);

        $this->assertNull($project->ppn_amount);
        $this->assertNull($project->contract_value_with_ppn);
    }
}
