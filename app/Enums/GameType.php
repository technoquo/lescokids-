<?php

namespace App\Enums;

enum GameType: string
{
    case Memoria = 'memoria';
    case SeleccionMultiple = 'seleccion_multiple';
    case Asociacion = 'asociacion';
    case ReconocimientoVisual = 'reconocimiento_visual';
}
