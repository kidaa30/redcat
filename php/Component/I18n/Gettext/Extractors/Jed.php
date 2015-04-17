<?php
namespace Surikat\Component\I18n\Gettext\Extractors;

use Surikat\Component\I18n\Gettext\Translations;

/**
 * Class to get gettext strings from json files
 */
class Jed extends PhpArray implements ExtractorInterface
{
    /**
     * {@inheritDoc}
     */
    public static function fromString($string, Translations $translations = null, $file = '')
    {
        if ($translations === null) {
            $translations = new Translations();
        }

        $content = json_decode($string);

        return PhpArray::handleArray($content, $translations);
    }
}