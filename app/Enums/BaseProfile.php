<?php

namespace App\Enums;

enum BaseProfile: string
{
    case FullStack = 'full_stack';
    case FrontendAngular = 'frontend_angular';
    case BackendPhp = 'backend_php';
    case BackendNode = 'backend_node';
    case Custom = 'custom';

    public function label(): string
    {
        return __('enums.base_profile.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $baseProfile) => [$baseProfile->value => $baseProfile->label()])
            ->all();
    }
}
