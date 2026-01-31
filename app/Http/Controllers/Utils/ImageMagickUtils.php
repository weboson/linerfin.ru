<?php

namespace App\Http\Controllers\Utils;

/**
 * Утилиты для обработки изображений
 */
class ImageMagickUtils
{
    protected $pathToFile;
    
    /**
     * Проверяем доступность ImageMagick
     */
    public static function isAvailable(): bool
    {
        static $available = null;
        
        if ($available === null) {
            $available = false;
            
            // Проверяем существование реального бинарника
            $realPath = self::getRealConvertPath();
            if ($realPath && is_executable($realPath)) {
                // Проверяем может ли текущий пользователь выполнить
                $testCmd = escapeshellarg($realPath) . ' -version 2>&1';
                $result = @shell_exec($testCmd);
                
                if ($result && strpos($result, 'ImageMagick') !== false) {
                    $available = true;
                }
            }
        }
        
        return $available;
    }
    
    /**
     * Получаем реальный путь к convert
     */
    private static function getRealConvertPath(): string
    {
        // Следуем по цепочке симлинков
        $path = '/usr/bin/convert';
        
        while (is_link($path)) {
            $link = readlink($path);
            if ($link[0] !== '/') {
                $path = dirname($path) . '/' . $link;
            } else {
                $path = $link;
            }
        }
        
        return $path;
    }

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
        ?int $fuzz = 25
    ){
        $realConvertPath = self::getRealConvertPath();
        
        if (!is_executable($realConvertPath)) {
            throw new \Exception('ImageMagick не доступен для выполнения');
        }
        
        // Всегда сохраняем как PNG
        if (!preg_match('/\.png$/i', $outputPath)) {
            $outputPath = preg_replace('/\.[^\.]+$/', '.png', $outputPath);
        }
        
        // Создаем директорию
        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            @mkdir($outputDir, 0755, true);
        }
        
        // Проверяем права на запись
        if (!is_writable($outputDir)) {
            // Пробуем изменить права
            @chmod($outputDir, 0755);
        }
        
        // Команда с реальным путем
        $cmd = sprintf(
            '%s %s -fuzz %d%% -transparent white %s 2>&1',
            escapeshellarg($realConvertPath),
            escapeshellarg($this->pathToFile),
            $fuzz,
            escapeshellarg($outputPath)
        );
        
        $result = @shell_exec($cmd);
        
        if (!file_exists($outputPath)) {
            // Пробуем альтернативный подход - через временный файл
            $this->tryWithTempFile($realConvertPath, $outputPath, $fuzz);
        }
        
        if (!file_exists($outputPath)) {
            throw new \Exception('Не удалось обработать изображение. Возможно нет прав.');
        }
        
        return $this;
    }
    
    /**
     * Альтернативный метод через временный файл
     */
    private function tryWithTempFile($convertPath, $outputPath, $fuzz)
    {
        // Создаем временный файл в /tmp (обычно доступен всем)
        $tempOutput = '/tmp/' . basename($outputPath) . '_' . uniqid() . '.png';
        
        $cmd = sprintf(
            '%s %s -fuzz %d%% -transparent white %s 2>&1',
            escapeshellarg($convertPath),
            escapeshellarg($this->pathToFile),
            $fuzz,
            escapeshellarg($tempOutput)
        );
        
        @shell_exec($cmd);
        
        if (file_exists($tempOutput)) {
            // Копируем в нужное место
            rename($tempOutput, $outputPath);
        }
    }
}