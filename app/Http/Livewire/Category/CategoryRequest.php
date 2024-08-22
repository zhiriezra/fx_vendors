<?php

namespace App\Http\Livewire\Category;

use Livewire\Component;
use App\Models\Category;

class CategoryRequest extends Component
{
    public $name;

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function saveCategory()
    {
        $this->validate();

        Category::create([
            'name' => $this->name,
        ]);
        // Optional flash message
        session()->flash('message', 'Category Request sent to admin successfully!');

        // Reset the form after save
        $this->reset(['name']);

        // Optional flash message
        session()->flash('message', 'Category Request sent to admin successfully!');
    }

    public function render()
    {
        return view('livewire.category.category-request');
    }
}
