<?php

namespace App\Enums;

enum AttachmentDirection: string
{
    case Received = 'received';
    case Sent = 'sent';
    case Internal = 'internal';
    case NotSpecified = 'not_specified';

    public function label(): string
    {
        return __('enums.attachment_direction.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $direction) => [$direction->value => $direction->label()])
            ->all();
    }
}
