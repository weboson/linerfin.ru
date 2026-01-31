<?php

namespace App\Http\Controllers\Utils;

/**
 * Утилиты для обработки изображений с использованием ImageMagick
 */
class ImageMagickUtils
{
    protected $pathToFile;

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

    /**
     * Основной метод для удаления фона
     */
    public function removeBackground(
        string $outputPath,
        ?array $backgroundColor = null,
        ?int $fuzz = 25,
        ?bool $useMultipleColors = true
    ){
        $color = $backgroundColor ?: [255, 255, 255];
        
        $inputExtension = strtolower(pathinfo($this->pathToFile, PATHINFO_EXTENSION));
        $outputExtension = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION));
        
        // Если выходной файл не PNG, меняем его на PNG
        if ($outputExtension !== 'png') {
            $outputPath = preg_replace('/\.[^\.]+$/', '.png', $outputPath);
        }
        
        // Для JPG/JPEG/BMP конвертируем в PNG
        $inputFile = $this->pathToFile;
        $isTempFile = false;
        
        if (in_array($inputExtension, ['jpg', 'jpeg', 'bmp'])) {
            $tempFile = sys_get_temp_dir() . '/temp_' . uniqid() . '.png';
            shell_exec("convert '{$inputFile}' '{$tempFile}'");
            
            if (!file_exists($tempFile)) {
                throw new \Exception('Failed to convert image to PNG');
            }
            
            $inputFile = $tempFile;
            $isTempFile = true;
        }
        
        try {
            // Основная команда удаления фона
            $command = "convert '{$inputFile}' -fuzz {$fuzz}% ";
            
            if ($useMultipleColors) {
                // Удаляем несколько оттенков
                $colors = $this->getColorVariations($color);
                foreach ($colors as $c) {
                    $command .= "-transparent \"rgb({$c[0]},{$c[1]},{$c[2]})\" ";
                }
            } else {
                $command .= "-transparent \"rgb({$color[0]},{$color[1]},{$color[2]})\" ";
            }
            
            $command .= "'{$outputPath}'";
            
            shell_exec($command);
            
            if (!file_exists($outputPath)) {
                throw new \Exception('Failed to create output file');
            }
            
        } finally {
            if ($isTempFile && file_exists($inputFile)) {
                unlink($inputFile);
            }
        }
        
        return $this;
    }
    
    private function getColorVariations(array $baseColor): array
    {
        $colors = [$baseColor];
        
        // Добавляем похожие цвета (оттенки белого/серого)
        $variations = [5, 10, 15, 20];
        foreach ($variations as $v) {
            $colors[] = [
                min(255, $baseColor[0] + $v),
                min(255, $baseColor[1] + $v),
                min(255, $baseColor[2] + $v)
            ];
            $colors[] = [
                max(0, $baseColor[0] - $v),
                max(0, $baseColor[1] - $v),
                max(0, $baseColor[2] - $v)
            ];
        }
        
        return array_unique($colors, SORT_REGULAR);
    }
    
    // Для обратной совместимости
    public function addTransparency(int $red, int $green, int $blue, string $pathToSave, int $fuzz = 9){
        return $this->removeBackground($pathToSave, [$red, $green, $blue], $fuzz, false);
    }
}