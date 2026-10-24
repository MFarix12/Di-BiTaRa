<?php
namespace App\Services;

class SentenceMeaning
{
    /** Only proverbs require a meaning popup; published source snapshots remain unchanged. */
    public static function for(array $snapshot): ?array
    {
        if(($snapshot['type']??null)!=='sentence_order')return null;
        $answer=$snapshot['answer'];
        $sentence=$answer['sentence']??implode(' ',array_map(fn($key)=>$snapshot['config']['items'][$key],$answer['value']));
        $known=['Ukur baju di badan sendiri.','Bagai dihiris sembilu.'];
        $isProverb=$snapshot['config']['is_proverb'] ?? in_array($sentence,$known,true);
        if (!$isProverb) return null;
        $meaning=trim($answer['meaning']??'');
        return $meaning?['sentence'=>$sentence,'meaning'=>$meaning]:null;
    }
}
