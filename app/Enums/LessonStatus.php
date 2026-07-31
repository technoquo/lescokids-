<?php

namespace App\Enums;

enum LessonStatus: string
{
    case Borrador = 'borrador';
    case Revision = 'revision';
    case Publicado = 'publicado';
}
