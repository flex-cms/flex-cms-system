<?php

namespace Flex\Core\Traits;

trait RequestHelper
{
    public function normalizeCheckboxes(array|null $data = null): array
    {
        $data = $data ?? $_POST;

        foreach ($data as $key => $value) {
            if ($this->isCheckboxField($key)) {
                $data[$key] = ($value == 1) ? 1 : 0;
            }
        }

        return $data;
    }

    protected function isCheckboxField(string $key): bool
    {
        return str_starts_with($key, 'is_')
            || str_contains($key, 'enabled')
            || str_contains($key, 'active')
            || str_contains($key, 'visible');
    }
}