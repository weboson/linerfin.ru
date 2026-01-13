<?php


namespace App\Http\Traits;


trait ConsoleMsgTrait
{
    protected function consoleMsg(string $msg){
        if(app()->runningInConsole())
            echo "$msg\n";

        return null;
    }
}
