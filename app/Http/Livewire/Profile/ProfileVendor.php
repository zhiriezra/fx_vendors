<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ProfileVendor extends Component
{

    public $vendor;
    public $productCount;
    
    public function mount()
    {
        $user = Auth::user();
        $this->productCount = $user->vendor ? $user->vendor->products()->count() : 0;
        $this->vendor = Auth::user()->vendor;

    }

    public function render()
    {
        return view('livewire.profile.profile-vendor');
    }
}
