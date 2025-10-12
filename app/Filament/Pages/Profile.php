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
use App\Models\Vendor;

class Profile extends BaseProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    
    protected static string $view = 'filament.pages.profile';
    

    public $data = [];
    
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

        // disable filament-breezy default sections completely
        public function getRegisteredMyProfileComponents(): array
    {
        return []; 
    }

    //  Fill form fields with user data on mount
    public function mount()
    {
        $user = Auth::user();
        $vendor = $user->vendor;

        $this->form->fill([
            'firstname' => $user->firstname,
            'middlename' => $user->middlename,
            'lastname' => $user->lastname,
            'email' => $user->email, 
            'phone' => $user->phone,
            'business_name' => $vendor?->business_name,
            'current_location' => $vendor?->current_location,
            'business_email' => $vendor?->business_email,
            'business_type' => $vendor?->business_type,
            'registration_no' => $vendor?->registration_no,
            'tin' => $vendor?->tin,
            'bank' => $vendor?->bank,
            'account_name' => $vendor?->account_name,
            'account_no' => $vendor?->account_no,
        ]);
    }    

    public function form(Form $form): Form
    {
         return $form
        ->schema([
            Forms\Components\Section::make('Personal Information')
                ->schema([
                    Forms\Components\TextInput::make('firstname')->label('First Name')->disabled(),
                    Forms\Components\TextInput::make('middlename')->label('Middle Name')->disabled(),
                    Forms\Components\TextInput::make('lastname')->label('Last Name')->disabled(),
                    Forms\Components\TextInput::make('email')->label('Email')->disabled(),
                    Forms\Components\TextInput::make('phone')->label('Phone')->disabled(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Business Information')
                ->schema([
                    Forms\Components\TextInput::make('business_name')->label('Business Name'),
                    Forms\Components\TextInput::make('current_location')->label('Business Address'),
                    Forms\Components\TextInput::make('business_email')->label('Business Email'),
                    Forms\Components\TextInput::make('business_type')->label('Business Type'),
                    Forms\Components\TextInput::make('registration_no')->label('CAC Reg. Number'),
                    Forms\Components\TextInput::make('tin')->label('TIN Number'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Address Information')
                ->schema(function () {
                    $user = auth()->user(); // ensure it's re-evaluated at runtime
                    $vendor = $user?->vendor;

                    if (!$user || !$vendor) {
                        return [];
                    }

                    if ((int) $user->country_id === 1) {
                        return [
                            Forms\Components\TextInput::make('state')
                                ->label('State')
                                //->disabled()
                                ->formatStateUsing(fn () => $vendor?->state?->name ?? 'N/A'),

                            Forms\Components\TextInput::make('lga')
                                ->label('LGA')
                                //->disabled()
                                ->formatStateUsing(fn () => $vendor?->lga?->name ?? 'N/A'),
                        ];
                    }

                    if ((int) $user->country_id === 2) {
                        return [
                            Forms\Components\TextInput::make('county')
                                ->label('County')
                                //->disabled()
                                ->formatStateUsing(fn () => $vendor?->state?->name ?? 'N/A'),

                            Forms\Components\TextInput::make('constituency')
                                ->label('Constituency')
                                //->disabled()
                                ->formatStateUsing(fn () => $vendor?->lga?->name ?? 'N/A'),

                            Forms\Components\TextInput::make('ward')
                                ->label('Ward')
                                //->disabled()
                                ->formatStateUsing(fn () => $vendor?->ward?->name ?? 'N/A'),
                        ];
                    }

                    return [];
                })
                ->columns(2),

            Forms\Components\Section::make('Bank Information')
                ->schema([
                    Forms\Components\TextInput::make('bank')->label('Bank Name'),
                    Forms\Components\TextInput::make('account_name')->label('Account Name'),
                    Forms\Components\TextInput::make('account_no')->label('Account Number'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Change Password')
                ->schema([
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
                ->columns(2),
        ])
        ->statePath('data');

    }

        public function save(): void
        {
            $user = Auth::user();
            $vendor = $user->vendor;
            $data = $this->form->getState();

            // Save vendor fields
            if ($vendor) {
                $vendor->fill([
                    'business_name' => $data['business_name'] ?? $vendor->business_name,
                    'current_location' => $data['current_location'] ?? $vendor->current_location,
                    'business_email' => $data['business_email'] ?? $vendor->business_email,
                    'business_type' => $data['business_type'] ?? $vendor->business_type,
                    'registration_no' => $data['registration_no'] ?? $vendor->registration_no,
                    'tin' => $data['tin'] ?? $vendor->tin,
                    'bank' => $data['bank'] ?? $vendor->bank,
                    'account_name' => $data['account_name'] ?? $vendor->account_name,
                    'account_no' => $data['account_no'] ?? $vendor->account_no,
                ]);
                $vendor->save();
            }

            // Update password if provided
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
                $user->save();
            }

            Notification::make()
                ->title('Profile updated successfully.')
                ->success()
                ->send();
    }
        
            protected function getFormActions(): array
        {
            return [];
        }

            protected function getProfileFormSchema(): array
        {
            return [];
        }

        protected function getPasswordFormSchema(): array
        {
            return [];
        }
          

        
    }