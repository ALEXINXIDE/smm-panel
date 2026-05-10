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

        return is_null($port) ? 8000 : (int) $port;
    }
}
