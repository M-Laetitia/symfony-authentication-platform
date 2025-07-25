<?php
namespace App\Event;

use App\Entity\User;
// Importe la classe Event de Symfony
use Symfony\Contracts\EventDispatcher\Event;

class UserRegisteredEvent extends Event
{
    // Définit une constante nommée NAME qui contient le nom unique de cet événement.
    // Ce nom est utilisé pour identifier cet événement dans le système de gestion d'événements.
    public const NAME = 'user.registered';

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
