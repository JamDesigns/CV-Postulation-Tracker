<?php

namespace App\Filament\Actions;

use App\Services\WebmailProvider;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;

class OpenWebmailAction
{
    public static function make(Closure $emailResolver): Action
    {
        $webmail = app(WebmailProvider::class);
        $options = $webmail->options();

        $action = Action::make('openWebmail')
            ->modalHeading(__('webmail.heading'))
            ->modalDescription(__('webmail.description'))
            ->modalCancelActionLabel(__('webmail.close'))
            ->modalSubmitAction(false);

        if ($options === []) {
            return $action->disabled();
        }

        if (count($options) === 1) {
            $provider = array_key_first($options);

            return $action
                ->url(
                    fn (Model $record): ?string => self::composeUrl(
                        $webmail,
                        $provider,
                        $emailResolver,
                        $record,
                    ),
                )
                ->openUrlInNewTab();
        }

        if (count($options) <= 3) {
            $providerActions = [];

            foreach ($options as $provider => $label) {
                $providerActions[] = Action::make("openWebmailProvider_{$provider}")
                    ->label($label)
                    ->url(
                        fn (Model $record): ?string => self::composeUrl(
                            $webmail,
                            $provider,
                            $emailResolver,
                            $record,
                        ),
                    )
                    ->openUrlInNewTab();
            }

            return $action
                ->schema([
                    Actions::make($providerActions)
                        ->fullWidth(),
                ]);
        }

        return $action
            ->schema([
                Select::make('provider')
                    ->label(__('webmail.provider'))
                    ->options($options)
                    ->live()
                    ->searchable()
                    ->native(false)
                    ->required(),

                Actions::make([
                    Action::make('openSelectedWebmailProvider')
                        ->label(__('webmail.send'))
                        ->url(
                            function (Model $record, Get $schemaGet) use ($webmail, $emailResolver): ?string {
                                $provider = $schemaGet('provider');

                                if (! is_string($provider) || blank($provider)) {
                                    return null;
                                }

                                return self::composeUrl(
                                    $webmail,
                                    $provider,
                                    $emailResolver,
                                    $record,
                                );
                            },
                        )
                        ->openUrlInNewTab()
                        ->disabled(
                            fn (Get $schemaGet): bool => blank($schemaGet('provider')),
                        ),
                ])->fullWidth(),
            ]);
    }

    private static function composeUrl(
        WebmailProvider $webmail,
        string $provider,
        Closure $emailResolver,
        Model $record,
    ): ?string {
        $email = $emailResolver($record);

        if (! is_string($email) || blank($email)) {
            return null;
        }

        return $webmail->composeUrl($provider, $email);
    }
}
