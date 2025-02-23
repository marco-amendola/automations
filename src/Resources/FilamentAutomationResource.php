<?php

namespace Automations\FilamentAutomations\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Automations\FilamentAutomations\Concerns\CanSetupAutomations;
use Automations\FilamentAutomations\Models\Automation;
use Automations\FilamentAutomations\Resources\FilamentAutomationResource\Pages;

class FilamentAutomationResource extends Resource
{
    use CanSetupAutomations;

    protected static ?string $model = Automation::class;

    protected static ?string $navigationIcon = 'heroicon-s-bolt';

    public static function getModelLabel(): string
    {
        return __('filament-automations::automations.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-automations::automations.plural_title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-automations::automations.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-automations::automations.navigation_group');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make()->schema([
                    Forms\Components\Tabs\Tab::make('Basic')->label('Generale')->schema([
                        Forms\Components\Select::make('model_type')->label('Modello')->searchable()->reactive()->options(self::getModelTypeOptions())->required(),
                        Forms\Components\Select::make('model_id')->label('Specifica un id')->helperText('Specificando un ID, il automation verrà applicato solo a quel record.')
                            ->searchable()->getSearchResultsUsing(fn (Forms\Get $get, ?string $search) => $get('model_type') ? self::getModelOptions(app($get('model_type')), $search) : [])->nullable(),
                        Forms\Components\TextInput::make('title')->label('Titolo')->autofocus()->required(),
                        Forms\Components\Toggle::make('enabled')->label('Abilitato')->inline(false)->default(true)->required(),
                        Forms\Components\Textarea::make('description')->label('Descrizione')->nullable(),
                    ]),
                    Forms\Components\Tabs\Tab::make('Trigger')->schema([
                        Forms\Components\Repeater::make('trigger')->schema(fn (Forms\Get $get) => [
                            Forms\Components\Select::make('event')->label('Evento')->options([
                                'created' => 'Creato',
                                'updated' => 'Aggiornato',
                                'deleted' => 'Eliminato',
                                'restored' => 'Ripristinato',
                                'forceDeleted' => 'Eliminato definitivamente',
                            ])->reactive()->required(),
                            Forms\Components\Group::make()->schema([
                                Forms\Components\Repeater::make('triggers')
                                    ->helperText('Quando tutte le condizioni sono soddisfatte, l\'azione verrà eseguita.')
                                    ->schema([
                                        Forms\Components\Fieldset::make('Trigger Definition')->schema([
                                            Forms\Components\Select::make('field')->reactive()->label('Il campo')->options(fn () => $get('model_type') ? self::getModelFields(app($get('model_type'))) : [])->nullable(),
                                            Forms\Components\Select::make('operator')->label('è')->options([
                                                '===' => '===',
                                                '==' => '==',
                                                '!=' => '!=',
                                                '!==' => '!==',
                                                '>' => '>',
                                                '<' => '<',
                                                '>=' => '>=',
                                                '<=' => '<=',
                                            ])->nullable(),
                                            Forms\Components\TextInput::make('value')->label('Valore')->nullable(),
                                        ])->columns(3),
                                    ]),
                            ])->visible(fn (Forms\Get $get) => $get('event') !== 'deleted'),
                        ])->maxItems(1)->minItems(1)->columns(1),
                    ]),
                    Forms\Components\Tabs\Tab::make('Actions')->label('Azioni')->schema([
                        // Forms\Components\Placeholder::make('placeholder')->content('Le azioni vengono eseguite nell\'ordine in cui sono elencate.')->columns(1),
                        //voglio mostrare tutte la colonne come smart tag per fare in modo che l'utente possa scegliere l'azione da eseguire
                        Forms\Components\Placeholder::make('placeholder')->label('Variabili disponibili')->content(function (Forms\Get $get) {
                            //uso la funzione getModelFields per ottenere i campi del modello
                            $modelFields = $get('model_type') ? self::getModelFields(app($get('model_type'))) : [];
                            //devo ritornare come stringa, per ora è un array
                            $modelFieldsString = '';
                            foreach ($modelFields as $key => $value) {
                                $modelFieldsString .= '{{' . $key . '}} ';
                            }
                            return $modelFieldsString;
                        })->columns(1),
                        Forms\Components\Repeater::make('actions')->schema([
                            Forms\Components\Select::make('action_class')->live()
                                ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => $set('action_class', $get('action_class')))
                                ->label('Azioni da eseguire')->options(self::getActionOptions())->required(),
                            //dati aggiuntivi per l'azione
                            Forms\Components\Group::make()->schema(function (Forms\Get $get) {
                                return self::getActionFormByClass($get('action_class'));
                            })->visible(fn ($get) => $get('action_class') !== ''),

                             //delay per l'azione numero e unita. es. una input numero e una select unita
                             Forms\Components\Section::make([
                             Forms\Components\Toggle::make('delay_enabled')->label('Esegui dopo...')->inline(false)->default(false)->live(),
                             Forms\Components\Group::make()->schema([
                                 Forms\Components\TextInput::make('delay_number')->label('Ritardo')->required()->default(0)->minValue(0),
                                 Forms\Components\Select::make('delay_unit')->label('Unità')->required()
                                 ->options([
                                     'Seconds' => 'Secondi',
                                     'Minutes' => 'Minuti',
                                     'Hours' => 'Ore',
                                     'Days' => 'Giorni',
                                 ])->default('seconds'),
                             ])->columns(2)->columnSpan(3)->hidden(fn ($get) => !$get('delay_enabled')),
                             ])->columns(4),


                        ])->columns(1),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('enabled')->label('Abilitato')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('title')->label('Titolo')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('model_type')->label('Tipo')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('model_type')->label('Tipo')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('trigger')->label('Quando')->toggleable()->getStateUsing(function ($record) {
                    return $record->trigger[0]['event'];
                }),

                Tables\Columns\TextColumn::make('description')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListFilamentAutomations::route('/'),
            'create' => Pages\CreateFilamentAutomation::route('/create'),
            'edit' => Pages\EditFilamentAutomation::route('/{record}/edit'),
        ];
    }
}
