<?php

namespace App\Utils;

class ColorUtils
{
    public static function randomGradient()
    {
        $gradients = file_get_contents(base_path('storage/gradients.json'));
        $gradients = json_decode($gradients);

        $gradientOptions = [];
        foreach ($gradients as $gradient) {            
            $gradientOptions[] = join(', ', $gradient->colors);
        }

        $random = rand(0, count($gradientOptions) - 1);
        
        return $gradientOptions[$random];
    }

    public static function randomBackgroundImage()
    {
        $backgrounds = file_get_contents(base_path('storage/backgrounds.json'));
        $backgrounds = json_decode($backgrounds);

        $random = rand(0, count($backgrounds) - 1);
        
        return $backgrounds[$random]->name;
    }
}
