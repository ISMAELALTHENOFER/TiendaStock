<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardReactActivityCopyTest extends TestCase
{
    public function test_dashboard_localizes_activity_and_retains_the_hidden_stock_panel(): void
    {
        $dashboard = file_get_contents(base_path('resources/js/react/dashboard.jsx'));
        $copy = file_get_contents(base_path('resources/js/react/lib/dashboardActivityCopy.js'));

        $this->assertStringContainsString("import { localizeActivity } from './lib/dashboardActivityCopy.js';", $dashboard);
        $this->assertStringContainsString('const copy = localizeActivity(event)', $dashboard);
        $this->assertStringContainsString('const SHOW_LOW_STOCK_SECTION = false;', $dashboard);
        $this->assertStringContainsString('{SHOW_LOW_STOCK_SECTION && <Card>', $dashboard);
        $this->assertStringContainsString('Stock que requiere atención', $dashboard);
        $this->assertStringContainsString("'sale.created': 'Venta registrada'", $copy);
        $this->assertStringContainsString("'product.created': 'Producto creado'", $copy);
    }
}
