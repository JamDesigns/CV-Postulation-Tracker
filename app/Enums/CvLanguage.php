<?php

namespace App\Enums;

enum CvLanguage: string
{
    case Spanish = 'spanish';
    case English = 'english';

    public function label(): string
    {
        return __('enums.cv_language.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $language) => [$language->value => $language->label()])
            ->all();
    }
}
