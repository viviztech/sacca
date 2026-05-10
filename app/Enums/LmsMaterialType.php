<?php

namespace App\Enums;

enum LmsMaterialType: string
{
    case Note = 'note';
    case Video = 'video';
    case Link = 'link';
    case Assignment = 'assignment';
    case Quiz = 'quiz';

    public function label(): string
    {
        return match ($this) {
            self::Note => 'Note / Document',
            self::Video => 'Video',
            self::Link => 'External Link',
            self::Assignment => 'Assignment',
            self::Quiz => 'Quiz',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Note => '📄',
            self::Video => '🎥',
            self::Link => '🔗',
            self::Assignment => '📝',
            self::Quiz => '❓',
        };
    }
}
