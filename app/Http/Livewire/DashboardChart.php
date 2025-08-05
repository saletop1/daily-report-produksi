<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class DashboardChart extends Component
{
    public $labels = [];
    public $gr_data = [];

    public function mount()
    {
        for ($i = 6; $i >= 0; $i--) {
            $this->labels[] = Carbon::now()->subDays($i)->format('Y-m-d');
            $this->gr_data[] = rand(10, 100);
        }
    }

    public function render()
    {
        return view('livewire.dashboard-chart');
    }
}
