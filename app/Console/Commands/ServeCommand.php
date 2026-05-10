<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

class ServeCommand extends BaseServeCommand
{
    /**
     * Get the port for the command.
     *
     * @return int
     */
    protected function port()
    {
        $port = $this->input->getOption('port');

        if (is_null($port) || $port === '') {
            $port = env('PORT', 8080);
        }

        return is_numeric($port) ? (int) $port : 8080;
    }
}