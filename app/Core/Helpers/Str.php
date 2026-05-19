<?php

namespace Flex\Core\Helpers;

class Str 
{
    public static function slug(string $string): string
    {
        $cyrillic = [
            'а','б','в','г','д','е','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ь','ю','я',
            'А','Б','В','Г','Д','Е','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ь','Ю','Я'
        ];
        $latin = [
            'a','b','v','g','d','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','ts','ch','sh','sht','a','y','yu','ya',
            'a','b','v','g','d','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','ts','ch','sh','sht','a','y','yu','ya'
        ];
        
        $string = str_replace($cyrillic, $latin, $string);
        $string = mb_strtolower($string, 'UTF-8');
        $string = preg_replace('/[^a-z0-9]+/u', '-', $string);
        
        return trim($string, '-');
    }
}