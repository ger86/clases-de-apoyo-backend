<?php

namespace App\Model;

/** What a student can mark as done from the app. */
enum ProgressKind: string
{
    case Chapter = 'chapter';
    case Exam = 'exam';
}
