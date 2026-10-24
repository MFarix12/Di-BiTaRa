<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LearningMetrics
{
    public function for(int $journeyId): array
    {
        $attempts=DB::table('attempts')->where('student_journey_id',$journeyId)->pluck('id');
        $questions=DB::table('attempt_questions')->whereIn('attempt_id',$attempts)->orderBy('id')->get()->unique('question_id');
        $correct=0; $marks=0; $answered=0;
        foreach ($questions as $q) {
            $first=DB::table('answer_submissions')->where('attempt_question_id',$q->id)->orderBy('id')->first();
            if ($first) { $answered++; if ($first->is_correct) { $correct++; $marks+=$q->max_marks; } }
        }
        $allQuestionIds=DB::table('attempt_questions')->whereIn('attempt_id',$attempts)->pluck('id');
        return ['first_correct'=>$correct,'answered'=>$answered,'first_marks'=>$marks,'submissions'=>DB::table('answer_submissions')->whereIn('attempt_question_id',$allQuestionIds)->count()];
    }
}
