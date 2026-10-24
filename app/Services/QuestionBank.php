<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestionBank
{
    public const TYPES = ['objective' => 'Pilihan Jawapan', 'true_false' => 'Betul / Salah', 'drag_drop' => 'Seret dan Lepas', 'sentence_order' => 'Susun Ayat', 'fill_blank' => 'Isi Tempat Kosong', 'dictation' => 'Imlak'];

    public function snapshot(object $q): array
    {
        $config = json_decode($q->public_config, true) ?: [];
        $options = DB::table('answer_options')->where('question_id', $q->id)->orderBy('sort_order')->get();
        $solution = DB::table('question_solutions')->where('question_id', $q->id)->first();

        return SentencePunctuation::normalize(['id' => $q->id, 'prompt' => $q->prompt, 'type' => $q->type, 'max_marks' => $q->max_marks, 'config' => $config,
            'options' => $options->map(fn ($o) => ['key' => $o->option_key, 'text' => $o->text])->all(),
            'answer' => $solution ? json_decode($solution->answer_key, true) : ['value' => $options->firstWhere('is_correct', 1)?->option_key],
            'feedback_correct' => $q->feedback_correct, 'feedback_incorrect' => $q->feedback_incorrect,
            'audio_id' => DB::table('question_audio')->where('question_id', $q->id)->value('media_file_id')]);
    }

    public function validate(array $q, int $zone): void
    {
        $allowed = match ($zone) {
            3 => ['true_false', 'drag_drop'],4 => ['sentence_order', 'fill_blank'],5 => ['dictation'],default => []
        };
        $fail = fn ($message) => throw ValidationException::withMessages(['question' => $message]);
        if (! in_array($q['type'], $allowed, true)) {
            $fail('Jenis soalan tidak dibenarkan untuk zon ini.');
        }
        if (! trim($q['prompt']) || $q['max_marks'] < 1) {
            $fail('Soalan dan markah positif diperlukan.');
        }
        $answer = $q['answer']['value'] ?? null;
        $config = $q['config'];
        if ($q['type']==='fill_blank' && !($config['require_copy'] ?? false)) {
            $fail('Isi Tempat Kosong memerlukan pilihan ejaan dan salinan ayat lengkap.');
        }
        if (in_array($q['type'], ['objective', 'true_false'])) {
            $keys = array_column($q['options'], 'key');
            if (count($keys) < 2 || count($keys) !== count(array_unique($keys)) || ! in_array($answer, $keys, true)) {
                $fail('Sekurang-kurangnya dua pilihan unik dan satu jawapan sah diperlukan.');
            }
        } elseif ($q['type'] === 'drag_drop') {
            $items = $config['items'] ?? [];
            $targets = $config['targets'] ?? [];
            if (! $items || ! $targets || ! is_array($answer) || count($answer) !== count($items)) {
                $fail('Semua item mesti mempunyai sasaran jawapan.');
            }
            foreach ($items as $key => $label) {
                if (! isset($answer[$key]) || ! isset($targets[$answer[$key]])) {
                    $fail('Pemetaan item dan sasaran tidak sah.');
                }
            }
        } elseif ($q['type'] === 'sentence_order') {
            $items = $config['items'] ?? [];
            if (! $items || ! is_array($answer) || count($answer) !== count($items) || count(array_unique($answer)) !== count($items) || array_diff($answer, array_keys($items))) {
                $fail('Susunan jawapan mesti menggunakan setiap blok sekali.');
            }
        } elseif ($q['type'] === 'fill_blank' && ($config['require_copy'] ?? false)) {
            if (!is_array($answer) || !is_string($answer['blank'] ?? null) || !is_string($answer['sentence'] ?? null) || !trim($answer['blank']) || !trim($answer['sentence']) || !in_array($answer['blank'],$config['choices'] ?? [],true)) {
                $fail('Pilihan ejaan dan jawapan ayat lengkap diperlukan.');
            }
        } else {
            if (! is_string($answer) || ! trim($answer)) {
                $fail('Jawapan teks diperlukan.');
            }
        }
        if ($q['type'] === 'dictation' && empty($q['audio_id'])) {
            $fail('Soalan imlak memerlukan audio sedia sebelum diterbitkan.');
        }
    }

    public function grade(array $q, mixed $response): bool
    {
        $answer = $q['answer']['value'] ?? null;
        if ($q['type'] === 'drag_drop') {
            if (! is_array($response)) {
                return false;
            } ksort($response);
            ksort($answer);

            return $response === $answer;
        }
        if ($q['type'] === 'sentence_order') {
            return is_array($response) && array_values($response) === array_values($answer);
        }
        if (in_array($q['type'], ['objective', 'true_false'])) {
            return is_string($response) && $response === $answer;
        }

        if ($q['type'] === 'fill_blank' && ($q['config']['require_copy'] ?? false)) {
            return is_array($response) && is_string($response['blank'] ?? null) && is_string($response['sentence'] ?? null)
                && $this->normalize($response['blank']) === $this->normalize($answer['blank'])
                && $this->normalize($response['sentence']) === $this->normalize($answer['sentence']);
        }

        return is_string($response) && $this->normalize($response) === $this->normalize((string) $answer);
    }

    private function normalize(string $s): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', preg_replace('/[.!?]+$/u', '', trim($s)))));
    }

    public function publicQuestion(array $q): array
    {
        unset($q['answer'],$q['feedback_correct'],$q['feedback_incorrect']);

        return $q;
    }
}
