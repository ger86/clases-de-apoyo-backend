<?php

namespace App\Enum;

class ExamType
{

    private static $types = [
        'modelo' => self::MODELO,
        'Modelo' => self::MODELO,
        'Junio' => self::JUNIO,
        'Septiembre' => self::SEPTIEMBRE,
        'Junio - F.G.' => self::JUNIOFG,
        'Junio F.G.' => self::JUNIOFG,
        'Junio. F.G.' => self::JUNIOFG,
        'Septiembre - F.M.' => self::SEPTFM,
        'Septiembre - F.G.' => self::SEPTFG,
        'Septiembre F.M.' => self::SEPTFM,
        'Septiembre F.G.' => self::SEPTFG,
        'Junio - F.M.' => self::JUNIOFM,
        'Junio F.M.' => self::JUNIOFM,
        'Junio. F.M.' => self::JUNIOFM,
        'Junio Ordinaria' => self::JUNIOORD,
        'Julio Extraordinaria' => self::JULIOEXTRA,
        'Otros' => self::OTROS
    ];

    const MODELO = 'modelo';
    const JUNIO = 'junio';
    const SEPTIEMBRE = 'septiembre';
    const JUNIOFG = 'junio-fg';
    const JUNIOFM = 'junio-fm';
    const SEPTFM = 'sept-fm';
    const SEPTFG = 'sept-fg';
    const JULIOEXTRA = 'julioextr';
    const JUNIOORD = 'junioord';
    const OTROS = 'otros';

    public static function getTypes()
    {
        return self::$types;
    }

    public static function toString($value)
    {
        return array_search($value, self::$types, true);
    }

    public static function fromString($text)
    {
        if (isset(self::$types[$text])) {
            return self::$types[$text];
        } else {
            return self::OTROS;
        }
    }

    private function __construct()
    {
    }
}
