<?php

namespace App\Entity;

enum Statut: string
{
    case ENCOURS = 'EnCours';
    case ACCEPTE = 'Accepte';
    case ANNULE = 'Annule';

    public function label(): string
    {
        return match($this) {
            self::ANNULE => 'Annulé',
            self::ENCOURS => 'En cours',
            self::ACCEPTE => 'Accepté',
        };
    }
}

?>