<?php

namespace App\Enums;

enum DocumentOrigin: string
{
    case Manual = 'manual';
    case Imported = 'imported';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Workshop',
            self::Imported => 'Imported',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Manual => 'bg-[#12241C] text-[#6BC79A]',
            self::Imported => 'bg-[#1A222E] text-[#7C93B3]',
        };
    }
}
