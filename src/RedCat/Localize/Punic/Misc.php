<?php
namespace RedCat\Localize\Punic;

/**
 * Various helper stuff
 */
class Misc
{
    /**
     * Concatenates a list of items returning a localized string (for instance: array(1, 2, 3) will result in '1, 2, and 3' for English or '1, 2 e 3' for Italian)
     * @param array $list The list to concatenate
     * @param string $locale = '' The locale to use. If empty we'll use the default locale set in \RedCat\Localize\Punic\Data
     * @return string Returns an empty string if $list is not an array of it it's empty, the joined items otherwise.
     */
    public static function join($list, $locale = '')
    {
        return static::joinInternal($list, null, $locale);
    }

    /**
     * Concatenates a list of unit items returning a localized string (for instance: array('3 ft', '2 in') will result in '3 ft, 2 in'
     * @param array $list The list to concatenate
     * @param string $width = '' The preferred width ('' for default, or 'short' or 'narrow')
     * @param string $locale = '' The locale to use. If empty we'll use the default locale set in \RedCat\Localize\Punic\Data
     * @return string Returns an empty string if $list is not an array of it it's empty, the joined items otherwise.
     */
    public static function joinUnits($list, $width = '', $locale = '')
    {
        $keys = array();
        if (!empty($width)) {
            switch ($width) {
                case 'narrow':
                    $keys = array('unit-narrow', 'unit-short');
                    break;
                case 'short':
                    $keys = array('unit-short', 'unit-narrow');
                    break;
                default:
                    throw new \RedCat\Localize\Punic\Exception\ValueNotInList($width, array('', 'short', 'narrow'));
            }
        }
        $keys[] = 'unit';

        return static::joinInternal($list, $keys, $locale);
    }

    protected static function joinInternal($list, $keys, $locale)
    {
        $result = '';
        if (is_array($list)) {
            $list = array_values($list);
            $n = count($list);
            switch ($n) {
                case 0:
                    break;
                case 1:
                    $result = strval($list[0]);
                    break;
                default:
                    $allData = \RedCat\Localize\Punic\Data::get('listPatterns', $locale);
                    $data = null;
                    if (!empty($keys)) {
                        foreach ($keys as $key) {
                            if (array_key_exists($key, $allData)) {
                                $data = $allData[$key];
                                break;
                            }
                        }
                    }
                    if (is_null($data)) {
                        $data = $allData['standard'];
                    }
                    if (array_key_exists($n, $data)) {
                        $result = vsprintf($data[$n], $list);
                    } else {
                        $result = sprintf($data['end'], $list[$n - 2], $list[$n - 1]);
                        if ($n > 2) {
                            for ($index = $n - 3; $index > 0; $index --) {
                                $result = sprintf($data['middle'], $list[$index], $result);
                            }
                            $result = sprintf($data['start'], $list[0], $result);
                        }
                    }
                    break;
            }
        }

        return $result;
    }

    /**
     * Fix the case of a string.
     * @param string $string The string whose case needs to be fixed
     * @param string $case How to fix case. Allowed values:<ul>
     *   <li><code>'titlecase-words'</code> all words in the phrase should be title case</li>
     *   <li><code>'titlecase-firstword'</code> the first word should be title case</li>
     *   <li><code>'lowercase-words'</code> all words in the phrase should be lower case</li>
     * </ul>
     * @return string
     * @link http://cldr.unicode.org/development/development-process/design-proposals/consistent-casing
     */
    public static function fixCase($string, $case)
    {
        $result = $string;
        if (is_string($string) && is_string($case) && (strlen($string) > 0)) {
            switch ($case) {
                case 'titlecase-words':
                    if (function_exists('mb_strtoupper') && (@preg_match('/\pL/u', 'a'))) {
                        $result = preg_replace_callback('/\\pL+/u', function ($m) {
                            $s = $m[0];
                            $l = mb_strlen($s, 'UTF-8');
                            if ($l === 1) {
                                $s = mb_strtoupper($s, 'UTF-8');
                            } else {
                                $s = mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($s, 1, $l - 1, 'UTF-8');
                            }

                            return $s;
                        }, $string);
                    }
                    break;
                case 'titlecase-firstword':
                    if (function_exists('mb_strlen')) {
                        $l = mb_strlen($string, 'UTF-8');
                        if ($l === 1) {
                            $result = mb_strtoupper($string, 'UTF-8');
                        } elseif ($l > 1) {
                            $result = mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($string, 1, $l - 1, 'UTF-8');
                        }
                    }
                    break;
                case 'lowercase-words':
                    if (function_exists('mb_strtolower')) {
                        $result = mb_strtolower($string, 'UTF-8');
                    }
                    break;
            }
        }

        return $result;
    }

    /**
     * Parse the browser HTTP_ACCEPT_LANGUAGE header and return the found locales
     * @param boolean $ignoreCache set to true if you want to ignore the cache
     * @return array
     */
    public static function getBrowserLocales($ignoreCache = false)
    {
        static $result;
        if (!isset($result) || $ignoreCache) {
            $httpAcceptLanguages = @getenv('HTTP_ACCEPT_LANGUAGE');
            if ((!is_string($httpAcceptLanguages)) || (strlen($httpAcceptLanguages) === 0)) {
                if (isset($_SERVER) && is_array($_SERVER) && array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
                    $httpAcceptLanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
                }
            }
            $result = self::parseHttpAcceptLanguage($httpAcceptLanguages);
        }

        return $result;
    }

    /**
     * Parse the value of an HTTP_ACCEPT_LANGUAGE header and return the found locales
     * @param string $httpAcceptLanguages
     * @return array
     */
    public static function parseHttpAcceptLanguage($httpAcceptLanguages)
    {
        $result = array();
        if (is_string($httpAcceptLanguages) && (strlen($httpAcceptLanguages) > 0)) {
            foreach (explode(',', $httpAcceptLanguages) as $httpAcceptLanguage) {
                if (preg_match('/^([a-z]{2,3}(?:[_\\-][a-z]+)*)(?:\\s*;(?:\\s*q(?:\\s*=(?:\\s*([\\d.]+))?)?)?)?$/', strtolower(trim($httpAcceptLanguage, " \t")), $m)) {
                    if (count($m) > 2) {
                        if (strpos($m[2], '.') === 0) {
                            $m[2] = '0' . $m[2];
                        }
                        if (preg_match('/^[01](\\.\\d*)?$/', $m[2])) {
                            $quality = round(@floatval($m[2]), 4);
                        } else {
                            $quality = -1;
                        }
                    } else {
                        $quality = 1;
                    }
                    if (($quality >= 0) && ($quality <= 1)) {
                        $found = array();
                        $chunks = explode('-', str_replace('_', '-', $m[1]));
                        $numChunks = count($chunks);
                        if ($numChunks === 1) {
                            $found[] = $m[1];
                        } else {
                            $base = $chunks[0];
                            for ($i = 1; $i < $numChunks; $i++) {
                                if (strlen($chunks[$i]) === 4) {
                                    $base .= '-' . ucfirst($chunks[$i]);
                                    break;
                                }
                            }
                            for ($i = 1; $i < $numChunks; $i++) {
                                if (preg_match('/^[a-z][a-z]$/', $chunks[$i])) {
                                    $found[] = $base . '-' . strtoupper($chunks[$i]);
                                } elseif (preg_match('/^\\d{3}$/', $chunks[$i])) {
                                    $found[] = $base . '-' . $chunks[$i];
                                }
                            }
                            if (empty($found)) {
                                $found[] = $base;
                            }
                        }
                        foreach ($found as $f) {
                            if (array_key_exists($f, $result)) {
                                if ($result[$f] < $quality) {
                                    $result[$f] = $quality;
                                }
                            } else {
                                $result[$f] = $quality;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }
}
