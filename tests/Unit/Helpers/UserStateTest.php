<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

class UserStateTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testState()
    {
        $this->assertEquals(state(0), '<span class="badge badge-danger">Inactivo</span>');
        $this->assertEquals(state(1), '<span class="badge badge-success">Activo</span>');
        $this->assertEquals(state(2), '<span class="badge badge-dark">Desconocido</span>');
    }
}
