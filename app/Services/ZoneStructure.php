<?php

namespace App\Services;

class ZoneStructure
{
    public const TIMED = [3, 4, 5];
    public const QUIZ = [3, 4];

    public static function timed(int $sequence): bool
    {
        return in_array($sequence, self::TIMED, true);
    }

    public static function normalize(array $manifest): array
    {
        // Apply current structure to older releases without rewriting source snapshots.
        foreach ($manifest['zones'] as &$zone) {
            $sequence = (int) $zone['sequence'];
            $zone['has_timer'] = self::timed($sequence);
            $zone['has_countdown'] = in_array($sequence,[3,4],true);
            $zone['allows_quiz'] = in_array($sequence, self::QUIZ, true);
            if (in_array($sequence, [1, 2], true)) {
                $zone['activities'] = [];
            }
            if ($sequence === 1) {
                $zone['notes'] = [];
            }
            if ($sequence === 5) {
                foreach ($zone['activities'] as &$activity) {
                    $activity['kind'] = 'activity';
                }
                unset($activity);
            }
        }
        unset($zone);
        return $manifest;
    }
}
