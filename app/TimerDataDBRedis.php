<?php

namespace App;

interface TimerDataDBRedis
{
    const lifetimeId = time()+3600; // 1 hour
    const lifetimeMessage = time()+3600; // 1 hour
    const lifetimeInvitations = time()+300; // 5 min
}