<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Pages\MyProfilePage as BaseProfile;
use Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication;
use Illuminate\Support\Facades\Auth;

class Profile extends BaseProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    
    protected static string $view = 'filament.pages.profile';
    

    public $data = [];
    
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    // ✅ Fill form fields with user data on mount
    public function mount()
    {
        $user = Auth::user();

        $this->form->fill([
            'firstname' => $user->firstname,
            'middlename' => $user->middlename,
            'lastname' => $user->lastname,
            'email' => $user->email, 
            'phone' => $user->phone,
        ]);
    }    

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('firstname')
                    ->label('First Name')
                    ->disabled(),
                Forms\Components\TextInput::make('middlename')
                    ->label('Middle Name')
                    ->disabled(),
                Forms\Components\TextInput::make('lastname')
                    ->label('Last Name')
                    ->disabled(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->disabled(), 
                Forms\Components\TextInput::make('phone')
                    ->label('Phone')
                    ->disabled(),

                Forms\Components\TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->nullable()
                    ->revealable()
                    ->minLength(8),

                Forms\Components\TextInput::make('password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->revealable()
                    ->nullable()
                    ->same('password'),
            ])
            ->columns(2)
            ->statePath('data');
    }

public function save(): void
    {
        $user = Auth::user();
        $data = $this->form->getState();

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        Notification::make()
        ->title('Password updated successfully.')
        ->success()
        ->send();
        
            //Hiding the profile on navbar
        }
        
        protected function getFormActions(): array
        {
            return [];
        }
        
    }