<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Category;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StockStatusTable extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Monitor';

    public function table(Table $table): Table
    {
        $user = Filament::auth()->user();
        $vendorId = $user?->vendor?->id;

        return $table
            ->query(
                Product::query()
                    ->where('vendor_id', $vendorId)
                    ->where(function ($q) {
                        $q->where('quantity', '<=', 5) //  low stock threshold
                          ->orWhere('quantity', '=', 0);
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('manufacturer_product.name')
                    ->label('Product Name')
                    ->sortable(),
                   // ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Available Quantity')
                    ->sortable()
                    ->color(fn ($state) => $state == 0 ? 'danger' : 'warning'),

               // Tables\Columns\TextColumn::make('category.name')
                 //   ->label('Category'),
                  //  ->toggleable(),

                Tables\Columns\TextColumn::make('stock_date')
                    ->label('Stock Date')
                    ->dateTime('M d, Y'),
            ])
            ->defaultSort('quantity', 'asc')
            ->paginated(false);
    }
}
