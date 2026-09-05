<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('applications:send-reminders')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);
