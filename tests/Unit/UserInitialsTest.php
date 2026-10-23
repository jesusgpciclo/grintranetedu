<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserInitialsTest extends TestCase
{
    /** @test */
    public function initials_handle_accented_characters_without_encoding_corruption(): void
    {
        // Caso del usuario: Apellidos "Ávila Ruiz", Nombre "Iván"
        $user = new User([
            'name' => 'Iván',
            'last_name' => 'Ávila Ruiz',
        ]);
        $this->assertEquals('ÁI', $user->initials);

        // Caso con minúsculas y tilde en nombre y apellido: "écija", "ángel"
        $user2 = new User([
            'name' => 'ángel',
            'last_name' => 'écija',
        ]);
        $this->assertEquals('ÉÁ', $user2->initials);

        // Caso con Ñ: "Ñíguez", "Óscar"
        $user3 = new User([
            'name' => 'Óscar',
            'last_name' => 'Ñíguez',
        ]);
        $this->assertEquals('ÑÓ', $user3->initials);

        // Caso con diéresis: "Güell", "Úrsula"
        $user4 = new User([
            'name' => 'Úrsula',
            'last_name' => 'Güell',
        ]);
        $this->assertEquals('GÚ', $user4->initials);

        // Caso solo con nombre con tilde
        $user5 = new User([
            'name' => 'Álvaro',
            'last_name' => null,
        ]);
        $this->assertEquals('Á', $user5->initials);
    }
}
