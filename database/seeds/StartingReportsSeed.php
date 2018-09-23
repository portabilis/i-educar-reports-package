<?php

use Phinx\Seed\AbstractSeed;

class StartingReportsSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $sql = file_get_contents(__DIR__ . '/reports.sql');
        $this->execute($sql);
    }
}
