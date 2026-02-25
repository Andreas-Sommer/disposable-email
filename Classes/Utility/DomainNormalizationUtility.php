<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Utility;

class DomainNormalizationUtility
{
    public static function normalizeDomain(string $domain): string
    {
        $normalizedDomain = trim($domain);
        $normalizedDomain = rtrim($normalizedDomain, '.');
        if ($normalizedDomain === '')
        {
            return '';
        }

        $normalizedDomain = self::toLowerCase($normalizedDomain);
        if (preg_match('/[^\x20-\x7E]/', $normalizedDomain) === 1)
        {
            if (function_exists('idn_to_ascii') === false)
            {
                return '';
            }

            $asciiDomain = self::convertIdnToAscii($normalizedDomain);
            if ($asciiDomain === '')
            {
                return '';
            }
            $normalizedDomain = self::toLowerCase($asciiDomain);
        }

        if (filter_var($normalizedDomain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false)
        {
            return '';
        }

        return $normalizedDomain;
    }

    private static function toLowerCase(string $value): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    private static function convertIdnToAscii(string $domain): string
    {
        $flags = defined('IDNA_DEFAULT') ? IDNA_DEFAULT : 0;
        if (defined('INTL_IDNA_VARIANT_UTS46'))
        {
            $asciiDomain = idn_to_ascii($domain, $flags, INTL_IDNA_VARIANT_UTS46);
        }
        else
        {
            $asciiDomain = idn_to_ascii($domain, $flags);
        }

        return is_string($asciiDomain) ? $asciiDomain : '';
    }
}
