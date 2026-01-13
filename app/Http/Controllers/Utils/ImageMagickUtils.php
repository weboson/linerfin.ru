<?php

namespace App\Http\Controllers\Utils;


/**
 * Required ImageMagick module on your host
 */
class ImageMagickUtils
{

    /*
     * input: filename and color + path to save
     * output: path to saved image
     */

    protected $pathToFile;


    // Set path to file
    public static function image(string $pathToFile){
        $self = new static();
        return $self->setImage($pathToFile);
    }

    public function setImage(string $pathToFile){
        if(!file_exists($pathToFile))
            throw new \Exception('File '.$pathToFile.' not exists');

        $this->pathToFile = $pathToFile;
        return $this;
    }



    // Add transparency
    public function addTransparency(int $red, int $green, int $blue, string $pathToSave, int $fuzz = 9){
        if($red < 0 || $red > 255)
            throw new \Exception('Param $red was between 0 and 255');
        if($green < 0 || $green > 255)
            throw new \Exception('Param $green was between 0 and 255');
        if($blue < 0 || $blue > 255)
            throw new \Exception('Param $blue was between 0 and 255');

        $shellResult = shell_exec('convert '.$this->pathToFile.' -fuzz '.$fuzz.'% -transparent "rgb('.$red.','.$green.','.$blue.')" '.$pathToSave.'');

        if(!file_exists($pathToSave))
            throw new \Exception('Failed to create file '.$pathToSave, compact('shellResult'));

        return $this;
    }


}
