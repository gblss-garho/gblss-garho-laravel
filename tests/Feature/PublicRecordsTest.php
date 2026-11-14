<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_render(): void
    {
        $this->get('/admit-card')->assertOk()->assertSee('Check Your Admit Card');
        $this->get('/leaving-certificate')->assertOk()->assertSee('Find Leaving Certificate');
        $this->get('/alumni')->assertOk()->assertSee('Find a Former Student');
    }

    public function test_wrong_dob_is_rejected(): void
    {
        Student::forceCreate(['id' => 's1', 'gr_number' => '77', 'name' => 'Ali', 'father_name' => 'Aslam', 'dob' => '2012-01-02', 'class' => 'VII']);
        $this->post('/admit-card', ['gr_number' => '77', 'dob' => '2010-01-01'])->assertOk()->assertSee('نه ملي');
        $this->post('/leaving-certificate', ['gr_number' => '77', 'dob' => '2010-01-01'])->assertOk()->assertSee('نه ملي');
    }

    public function test_lc_missing_and_alumni_search_hides_dob(): void
    {
        Student::forceCreate(['id' => 's2', 'gr_number' => '88', 'name' => 'Bilal', 'father_name' => 'Bashir', 'dob' => '2011-03-04', 'class' => 'VIII', 'passed_out' => true, 'pass_out_year' => '2024-2025']);
        $this->post('/leaving-certificate', ['gr_number' => '88', 'dob' => '2011-03-04'])->assertOk()->assertSee('جاري نه ٿيو');
        $this->get('/alumni?q=Bilal')->assertOk()->assertSee('Bilal')->assertSee('2024-2025')->assertDontSee('2011');
        $this->get('/alumni?q=Zzz')->assertOk()->assertSee('نه مليو');
    }
}
