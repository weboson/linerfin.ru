<?php
// для утилиты ImageMagick (удаление фона с печатей и подписей)
return [
    'background_removal' => [
        'default_color' => [255, 255, 255],
        'fuzz_percentage' => 25,
        'use_multiple_colors' => true,
        'supported_extensions' => ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'webp'],
    ],
];