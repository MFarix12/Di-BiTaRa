<?php
namespace App\Services;

class SentencePunctuation
{
    /** Presentation correction only: preserve item keys, grading order and historical results. */
    public static function normalize(array $q): array
    {
        if (($q['type'] ?? '') !== 'sentence_order') return $q;
        $order=$q['answer']['value'] ?? [];
        $last=$order ? end($order) : null;
        if ($last && isset($q['config']['items'][$last])) {
            $q['config']['items'][$last]=rtrim(trim($q['config']['items'][$last]),'.!?').'.';
        }
        return $q;
    }
}
