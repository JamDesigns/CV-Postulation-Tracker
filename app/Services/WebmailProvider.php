<?php

namespace App\Services;

class WebmailProvider
{
    public function providers(): array
    {
        return config('webmail.providers', []);
    }

    public function composeUrl(string $provider, string $email): ?string
    {
        $providers = $this->providers();
        $template = $providers[$provider]['compose_url'] ?? null;

        if (! is_string($template) || blank($template)) {
            return null;
        }

        return str_replace(
            '{email}',
            rawurlencode($email),
            $template,
        );
    }

    public function options(): array
    {
        $options = [];

        foreach ($this->providers() as $key => $provider) {
            $label = $provider['label'] ?? null;

            if (! is_string($label) || blank($label)) {
                continue;
            }

            $options[$key] = $label;
        }

        return $options;
    }
}
