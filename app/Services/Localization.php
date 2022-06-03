<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class Localization
{
    public const DEFAULT = 'ua';

    public function default() {
        return static::DEFAULT;
    }

    /**
     * Get prefix language in url.
     * @return string
     */
    public function getLangPrefix()
    {
        $language = request()->segment(1, '');
        if ($language && in_array($language, config('app.locales')))
            return $language;
        return ''; // default language - empty string in url
    }

    /**
     * Get language abbreviation.
     * @return string
     */
    public function getLangText($lang = null)
    {
        if (!isset($lang))
            $lang = $this->getLangPrefix();
        return ($lang === '' || $lang === self::DEFAULT) ? mb_strtoupper(self::DEFAULT) : mb_strtoupper($lang);
    }

    /**
     * Get info about all languages except current language.
     * @return array
     */
    public function getOtherLanguages()
    {
        $languages = [];
        $current = $this->getLangPrefix();
        foreach (config('app.locales') as $lang)
        {
            if ($current === $lang || ($current === '' && $lang === self::DEFAULT))
                continue;

            // starts with en
            $replacementPattern = '#^'.preg_quote(ltrim(Route::getCurrentRoute()->getPrefix(), '/')).'#';
            // remove en
            $routeWithoutPrefix = preg_replace($replacementPattern, '', Route::getCurrentRoute()->uri());
            // remove left /
            $routeWithoutPrefix = ltrim($routeWithoutPrefix, '/');
            // prefix starts with / or /en or /en/
            if ($lang === self::DEFAULT)
                $prefix = '/';
            else if (empty($routeWithoutPrefix))
                $prefix = '/'.$lang;
            else
                $prefix = '/'.$lang.'/';

            $languages[] = [
                'route' => $prefix . $routeWithoutPrefix,
                'text' => $this->getLangText($lang)
            ];
        }
        return $languages;
    }
}
