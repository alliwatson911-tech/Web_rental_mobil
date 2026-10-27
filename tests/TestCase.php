<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test tidak butuh aset CSS/JS hasil build.
        $this->withoutVite();
    }

    /** Tanggal relatif dari hari ini dalam format Y-m-d. */
    protected function day(int $offset): string
    {
        return now()->startOfDay()->addDays($offset)->toDateString();
    }
}
