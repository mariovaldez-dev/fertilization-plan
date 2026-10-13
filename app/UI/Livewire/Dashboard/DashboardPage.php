<?php

declare(strict_types=1);

namespace App\UI\Livewire\Dashboard;

use Livewire\Component;

final class DashboardPage extends Component
{
    public function render()
    {
        return view('livewire.dashboard.dashboard-page')
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
