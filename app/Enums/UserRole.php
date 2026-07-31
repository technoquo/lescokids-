<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Tutor = 'tutor';
    case Docente = 'docente';
    case Nino = 'nino';
}
