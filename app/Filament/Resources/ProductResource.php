<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\ManufacturerProduct;
use App\Models\Unit;
use App\Models\SubCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\TextEntry;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Manage Products';

    protected static ?string $navigationGroup = 'Products';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('vendor_id', auth()->user()?->vendor?->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('manufacturer_id')
                    ->label('Manufacturer')
                    ->options(\App\Models\Manufacturer::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('manufacturer_product_id', null)),

                Forms\Components\Select::make('manufacturer_product_id')
                    ->label('Manufacturer Product')
                    ->options(function (Forms\Get $get, ?Product $record) {
                        $manufacturerId = $get('manufacturer_id');
                
                        // If editing and manufacturer_id not yet set, fallback to current product's manufacturer
                        if (! $manufacturerId && $record?->manufacturer_product) {
                            $manufacturerId = $record->manufacturer_product->manufacturer_id;
                        }
                
                        return $manufacturerId
                            ? \App\Models\ManufacturerProduct::where('manufacturer_id', $manufacturerId)->pluck('name', 'id')
                            : [];
                    })
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state) {
                            $manufacturerProduct = ManufacturerProduct::find($state);
                            if ($manufacturerProduct) {
                                $subCategory = SubCategory::find($manufacturerProduct->sub_category_id);
                                $set('sub_category_id', $manufacturerProduct->sub_category_id);
                                $set('category_id', $subCategory ? $subCategory->category_id : null);
                            }
                        }
                    })
                    ->default(fn (?Product $record) => $record?->manufacturer_product_id),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                    
                Forms\Components\Select::make('unit_id')
                    ->label('Unit')
                    ->options(Unit::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                    
                Forms\Components\TextInput::make('unit_price')
                    ->label('Unit Price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                    
                Forms\Components\TextInput::make('agent_price')
                    ->label('Agent Price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                    
                Forms\Components\DatePicker::make('stock_date')
                    ->label('Stock Date')
                    ->required()
                    ->default(now()),
                    
                Forms\Components\Hidden::make('vendor_id')
                    ->default(fn () => auth()->user()?->vendor?->id)
                    ->required()
                    ->rule('exists:vendors,id'),
                    
                Forms\Components\Hidden::make('category_id'),
                    
                Forms\Components\Hidden::make('sub_category_id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('manufacturer_product.name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Unit')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money(fn () => auth()->user()->country?->currency ?? 'NGN')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('agent_price')
                    ->label('Agent Price')
                    ->money(fn () => auth()->user()->country?->currency ?? 'NGN')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('stock_date')
                    ->label('Stock Date')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('batch_number')
                    ->label('Batch Number')
                    ->searchable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Product Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('manufacturer_product.image')
                            ->label('Product Image')
                            ->url(fn ($record) => 'https://staging.farmex.extensionafrica.com/storage/manufacturer_product/' . $record->manufacturer_product->image)
                            ->height(200) // optional
                            ->width(200) // optional
                            ->extraAttributes(['class' => 'rounded-lg shadow']),
                            
                        Infolists\Components\TextEntry::make('manufacturer_product.manufacturer.name')
                            ->label('Manufacturer'),
                            
                        Infolists\Components\TextEntry::make('manufacturer_product.name')
                            ->label('Product Name'),
                            
                        Infolists\Components\TextEntry::make('quantity')
                            ->label('Quantity'),
                            
                        Infolists\Components\TextEntry::make('unit.name')
                            ->label('Unit'),
                    ])
                    ->columns(2),
                    
                Infolists\Components\Section::make('Pricing Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('unit_price')
                            ->label('Unit Price')
                            ->money(fn () => auth()->user()->country?->currency ?? 'NGN'),
                            
                        Infolists\Components\TextEntry::make('agent_price')
                            ->label('Agent Price')
                            ->money(fn () => auth()->user()->country?->currency ?? 'NGN'),
                    ])
                    ->columns(2),
                    
                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('stock_date')
                            ->label('Stock Date')
                            ->date(),
                            
                        Infolists\Components\TextEntry::make('batch_number')
                            ->label('Batch Number'),
                            
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Date Created')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
