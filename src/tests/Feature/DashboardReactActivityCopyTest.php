<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardReactActivityCopyTest extends TestCase
{
    public function test_dashboard_omits_activity_and_retains_the_hidden_stock_panel(): void
    {
        $dashboard = file_get_contents(base_path('resources/js/react/dashboard.jsx'));
        $this->assertStringNotContainsString('/dashboard/activity', $dashboard);
        $this->assertStringNotContainsString('ActivityCard', $dashboard);
        $this->assertStringNotContainsString('Actividad reciente', $dashboard);
        $this->assertStringContainsString('/dashboard/analytics', $dashboard);
        $this->assertStringContainsString('Acciones rápidas', $dashboard);
        $this->assertStringContainsString('const SHOW_LOW_STOCK_SECTION = false;', $dashboard);
        $this->assertStringContainsString('{SHOW_LOW_STOCK_SECTION && <Card>', $dashboard);
        $this->assertStringContainsString('Stock que requiere atención', $dashboard);
    }
}
