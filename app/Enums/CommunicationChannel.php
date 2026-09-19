<?php

namespace App\Enums;

enum CommunicationChannel: string
{
    case Email = 'email';
    case Linkedin = 'linkedin';
    case Phone = 'phone';
    case Whatsapp = 'whatsapp';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.communication_channel.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $channel): array => [$channel->value => $channel->label()])
            ->all();
    }
}
