<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Leads';

    protected static ?string $navigationGroup = 'Lead Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Contact Information')->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')->email()->maxLength(255),
                TextInput::make('phone')->maxLength(50)->tel(),
                TextInput::make('website')->url()->maxLength(500),
                TextInput::make('linkedin_url')->url()->maxLength(500)->label('LinkedIn URL'),
            ])->columns(2),

            Section::make('Company Details')->schema([
                TextInput::make('company')->maxLength(255),
                TextInput::make('company_size')->maxLength(50)->label('Company Size'),
                TextInput::make('industry')->maxLength(255),
                TextInput::make('location')->maxLength(255),
                Select::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'qualified' => 'Qualified',
                        'converted' => 'Converted',
                        'lost' => 'Lost',
                    ])
                    ->default('new'),
            ])->columns(2),

            Section::make('Additional')->schema([
                TextInput::make('source')->maxLength(100),
                Textarea::make('notes')->rows(3),
                KeyValue::make('enrichment_data')
                    ->label('Enrichment Data (JSON)')
                    ->keyLabel('Field')
                    ->valueLabel('Value'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('phone')->toggleable(),
                TextColumn::make('company')->searchable()->toggleable(),
                TextColumn::make('source')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'gray',
                        'contacted' => 'warning',
                        'qualified' => 'info',
                        'converted' => 'success',
                        'lost' => 'danger',
                    }),
                TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'qualified' => 'Qualified',
                        'converted' => 'Converted',
                        'lost' => 'Lost',
                    ]),
                SelectFilter::make('source')
                    ->options(fn() => Lead::distinct()->pluck('source', 'source')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLeads::route('/'),
        ];
    }
}
