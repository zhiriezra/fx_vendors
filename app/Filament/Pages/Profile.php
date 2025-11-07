<?php

namespace App\Filament\Pages;

use Filament\Forms;
use App\Models\Bank;
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
            'kra_pin' => $vendor?->kra_pin,
            'state_id' => $vendor?->state_id,
            'lga_id' => $vendor?->lga_id, 
            'ward_id' => $vendor?->ward_id, 
        ]);
    }    

    public function form(Form $form): Form
    {
         return $form
        ->schema([
            Forms\Components\Section::make('Personal Information')
                ->description('Basic personal details')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Forms\Components\TextInput::make('firstname')->label('First Name')->disabled(),
                    Forms\Components\TextInput::make('middlename')->label('Middle Name')->disabled(),
                    Forms\Components\TextInput::make('lastname')->label('Last Name')->disabled(),
                    Forms\Components\TextInput::make('email')->label('Email')->disabled(),
                    Forms\Components\TextInput::make('phone')->label('Phone')->disabled(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Business Information')
                    ->description('A summary of my business profile')
                    ->icon('heroicon-o-building-storefront')
                    //->collapsible()
                    //->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('business_name')->label('Business Name'),
                    Forms\Components\TextInput::make('current_location')->label('Business Address'),
                    Forms\Components\TextInput::make('business_email')->label('Business Email'),
                    Forms\Components\Select::make('business_type')
                        ->label('Business Type')
                        ->options([
                            'sole proprietorship' => 'Sole Proprietorship',
                            'partnership' => 'Partnership',
                            'limited liability' => 'Limited Liability',
                        ])
                        ->searchable()
                        ->required()
                        ->native(false), 
                                   ])
                ->columns(2),

            Forms\Components\Section::make('Address Information')
                    ->description('Geographical location details')
                    ->icon('heroicon-o-map-pin')
                    ->schema(function () {
                        $user = auth()->user();
                        $vendor = $user?->vendor;

                        if (!$user || !$vendor) {
                            return [];
                        }

                        // Check country
                        $isKenya = (int) $user->country_id === 2;

                        // For 🇳🇬 NIGERIA accounts
                        if (!$isKenya) {
                            return [
                                Forms\Components\Select::make('state_id')
                                    ->label('State')
                                    ->options(
                                        \App\Models\State::where('country_id', 1)
                                            ->pluck('name', 'id')
                                    )
                                    ->default($vendor?->state_id)
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->required(),

                                Forms\Components\Select::make('lga_id')
                                    ->label('LGA')
                                    ->options(function (callable $get) {
                                        $stateId = $get('state_id');
                                        if (!$stateId) return [];
                                        return \App\Models\Lga::where('state_id', $stateId)
                                            ->pluck('name', 'id');
                                    })
                                    ->default($vendor?->lga_id)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ];
                        }

                        // For 🇰🇪 KENYA accounts
                        return [
                            Forms\Components\Select::make('state_id')
                                ->label('County')
                                ->options(
                                    \App\Models\State::whereBetween('id', [38, 84])
                                        ->pluck('name', 'id')
                                )
                                ->default($vendor?->state_id)
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->required(),

                            Forms\Components\Select::make('lga_id')
                                ->label('Sub-county')
                                ->options(function (callable $get) {
                                    $stateId = $get('state_id');
                                    if (!$stateId) return [];
                                    return \App\Models\Lga::where('state_id', $stateId)
                                        ->where('id', '>=', 741)
                                        ->pluck('name', 'id');
                                })
                                ->default($vendor?->lga_id)
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->required(),

                            Forms\Components\Select::make('ward_id')
                                ->label('Ward')
                                ->options(function (callable $get) {
                                    $lgaId = $get('lga_id');
                                    if (!$lgaId) return [];
                                    return \App\Models\Ward::where('lga_id', $lgaId)
                                        ->pluck('name', 'id');
                                })
                                ->default($vendor?->ward_id)
                                ->searchable()
                                ->preload()
                                ->required(),
                        ];
                    })
                    ->columns(2),

            Forms\Components\Section::make('Bank Information')
                    ->description('Banking and tax identification information')
                    ->icon('heroicon-o-banknotes')
                    //->collapsible()
                    //->collapsed()
                ->schema([
                    Forms\Components\Select::make('bank')
                        ->label('Bank Name')
                        ->options(fn () => Bank::query()->pluck('name', 'name'))
                        ->searchable()
                        ->preload()
                        ->required()
                       // ->visible(fn () => auth()->user()?->country_id === 1)
                        ->native(false),
                    Forms\Components\TextInput::make('account_name')->label('Account Name'),
                    Forms\Components\TextInput::make('account_no')->label('Account Number'),
                    Forms\Components\TextInput::make('registration_no')->label('CAC Reg. Number')->visible(fn () => auth()->user()?->country?->name !== 'Kenya'),
                    Forms\Components\TextInput::make('tin')->label('TIN Number')->visible(fn () => auth()->user()?->country?->name !== 'Kenya'),
                    Forms\Components\TextInput::make('kra_pin')->label('KRA PIN')->visible(fn () => auth()->user()?->country?->name === 'Kenya'),

                    
                ])
                ->columns(2)
                ->visible(fn () => auth()->user()?->country?->name !== 'Kenya'),


            Forms\Components\Section::make('Change Password')
                ->description('Secure your account by updating your password here.')
                ->icon('heroicon-o-lock-closed')
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
                    'kra_pin' => $data['kra_pin'] ?? $vendor->kra_pin,
                    'state_id' => $data['state_id'] ?? $vendor->state_id,
                    'lga_id' => $data['lga_id'] ?? $vendor->lga_id,
                    'ward_id' => $data['ward_id'] ?? $vendor->ward_id,
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