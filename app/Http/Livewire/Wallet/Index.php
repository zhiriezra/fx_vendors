<?php

namespace App\Http\Livewire\Wallet;

use Livewire\Component;

class Index extends Component
{
    public $balance;

    public function render()
    {u
        return view('livewire.wallet.index');
    }

    public function mount(){
        $user = auth()->user();
        $this->balance = $user->balance;
    }
}
