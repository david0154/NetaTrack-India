<?php
class EnvValidator
{
    public static function required(array $keys): array
    {
        $missing = [];
        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value === false || $value === '') {
                $missing[] = $key;
            }
        }
        return $missing;
    }
}
