<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2015 silbersaiten
 * @version   1.1.0
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

class FormMakerCaptcha
{
    private $background;
    private $foreground;
    
    public $noise_level = 13;
    public $scale = 2;
    public $width = 304;
    public $height = 50;
    public $background_color = array(255, 255, 255);
    public $line_color = array(200, 208, 206);
    public $noise_color = array(200, 208, 206);
    public $text_colors = array(
        array(27, 78, 181),
        array(22, 163, 35),
        array(214, 36, 7),
    );
    public $max_rotation = 8;
    public $min_length = 5;
    public $max_length = 12;
    
    public $x_period    = 15;
    public $x_amplitude = 5;
    public $y_period    = 15;
    public $y_amplitude = 5;
    
    public $fonts = array(
        'Antykwa'  => array('spacing' => 0, 'minSize' => 27, 'maxSize' => 30, 'font' => 'AntykwaBold.ttf'),
        'DingDong' => array('spacing' => 0, 'minSize' => 24, 'maxSize' => 30, 'font' => 'Ding-DongDaddyO.ttf'),
        'Duality'  => array('spacing' => 0, 'minSize' => 30, 'maxSize' => 38, 'font' => 'Duality.ttf'),
        'Jura'     => array('spacing' => 0, 'minSize' => 28, 'maxSize' => 32, 'font' => 'Jura.ttf'),
        'StayPuft' => array('spacing' => 0,'minSize' => 28, 'maxSize' => 32, 'font' => 'StayPuft.ttf'),
        'Times'    => array('spacing' => 0, 'minSize' => 28, 'maxSize' => 34, 'font' => 'TimesNewRomanBold.ttf'),
        'VeraSans' => array('spacing' => 0, 'minSize' => 20, 'maxSize' => 28, 'font' => 'VeraSansBold.ttf'),
    );
    
    public function getCaptcha($text)
    {
        error_reporting(-1);
        header("Content-type: image/png");
        $this->createBackground();
        $this->createForeground();
        $this->drawLines();
        $this->createNoise();
        $this->drawText(false, $text);
        $this->addWaves();
        
        $this->mergeBackgroundWithForeground();

        imagepng($this->background);
        imagedestroy($this->background);
        imagedestroy($this->foreground);
    }
    
    private function createBackground()
    {
        $this->background = imagecreatetruecolor($this->width * $this->scale, $this->height * $this->scale);
        $background = imagecolorallocate(
            $this->background,
            $this->background_color[0],
            $this->background_color[1],
            $this->background_color[2]
        );
        
        imagefilledrectangle(
            $this->background,
            0,
            0,
            $this->width * $this->scale,
            $this->height * $this->scale,
            $background
        );
    }
    
    private function createForeground()
    {
        $this->foreground = imagecreatetruecolor($this->width * $this->scale, $this->height * $this->scale);
        
        imagealphablending($this->foreground, false);
        $transparency = imagecolorallocatealpha($this->foreground, 0, 0, 0, 127);
        imagefill($this->foreground, 0, 0, $transparency);
        imagealphablending($this->foreground, false);
        imagesavealpha($this->foreground, true);
    }
    
    private function mergeBackgroundWithForeground()
    {
        $foreground = imagecreatetruecolor($this->width, $this->height);
        $background = imagecreatetruecolor($this->width, $this->height);
        $transparency = imagecolorallocatealpha($this->foreground, 0, 0, 0, 127);
        imagefill($this->foreground, 0, 0, $transparency);
        imagealphablending($foreground, false);
        imagesavealpha($foreground, true);
        
        imagecopyresampled(
            $foreground,
            $this->foreground,
            0,
            0,
            0,
            0,
            $this->width,
            $this->height,
            $this->width * $this->scale,
            $this->height * $this->scale
        );
        
        imagecopyresampled(
            $background,
            $this->background,
            0,
            0,
            0,
            0,
            $this->width,
            $this->height,
            $this->width * $this->scale,
            $this->height * $this->scale
        );
        
        imagedestroy($this->background);
        imagedestroy($this->foreground);
        
        $this->background = $background;
        $this->foreground = $foreground;
        
        imagecopyresampled(
            $this->background,
            $this->foreground,
            0,
            0,
            0,
            0,
            $this->width,
            $this->height,
            $this->width,
            $this->height
        );
    }
    
    private function drawText($font = false, $text = false)
    {
        if (!$font) {
            $font = array_rand($this->fonts);
        }
        
        if (!array_key_exists($font, $this->fonts)) {
            return false;
        }
                
        $text = $text ? $text : Tools::passwdGen(
            Configuration::getGlobalValue('FM_CAPTCHA_NUMBER_CHAR') ? Configuration::getGlobalValue('FM_CAPTCHA_NUMBER_CHAR') : 8,
            Configuration::getGlobalValue('FM_CAPTCHA_TYPE') ? Configuration::getGlobalValue('FM_CAPTCHA_TYPE') : 'ALPHANUMERIC'
        );
        $text_color = $this->text_colors[array_rand($this->text_colors)];
        $text_color = imagecolorallocate($this->foreground, $text_color[0], $text_color[1], $text_color[2]);
        $l = Tools::strlen($text);
        
        if ($l > $this->max_length || $l < $this->min_length) {
            return false;
        }
        
        $f = 1 + (($this->max_length - $l) * 0.09);
        
        $path = dirname(__FILE__).'/../views/fonts/'.$this->fonts[$font]['font'];
        
        $x = 20 * $this->scale;
        $y = Tools::ps_round(($this->height * 27 / 35) * $this->scale);
        
        for ($i = 0; $i < $l; $i++) {
            $degree   = rand($this->max_rotation*-1, $this->max_rotation);
            $fontsize = rand($this->fonts[$font]['minSize'], $this->fonts[$font]['maxSize']) * $this->scale * $f;
            $letter   = Tools::substr($text, $i, 1);

            $coords = imagettftext($this->foreground, $fontsize, $degree, $x, $y, $text_color, $path, $letter);
            $x += ($coords[2] - $x) + ($this->fonts[$font]['spacing'] * $this->scale);
        }
    }
    
    private function drawLines()
    {
        $line_color = imagecolorallocate(
            $this->background,
            $this->line_color[0],
            $this->line_color[1],
            $this->line_color[2]
        );
        
        for ($i = 0; $i < 10; $i++) {
            imageline(
                $this->background,
                0,
                rand() % ($this->height * $this->scale),
                $this->width * $this->scale,
                rand() % ($this->height * $this->scale),
                $line_color
            );
        }
    }
    
    private function createNoise()
    {
        if (!$this->noise_level || $this->noise_level < 0) {
            return ;
        }
        
        $noise_color = imagecolorallocate(
            $this->background,
            $this->noise_color[0],
            $this->noise_color[1],
            $this->noise_color[2]
        );
        
        $total_pixels = $this->width * $this->height * $this->scale;
        $total_grains = $total_pixels * (1 + ($this->noise_level / 100)) - $total_pixels;

        for ($i = 0; $i < $total_grains; $i++) {
            imagesetpixel(
                $this->background,
                rand() % ($this->width * $this->scale),
                rand() % ($this->height * $this->scale),
                $noise_color
            );
        }
    }
    
    protected function addWaves()
    {
        // X-axis wave generation
        $xp = $this->scale * $this->x_period * rand(1, 3);
        $k = rand(0, 100);
        for ($i = 0; $i < ($this->width * $this->scale); $i++) {
            imagecopy(
                $this->foreground,
                $this->foreground,
                $i - 1,
                sin($k + $i / $xp) * ($this->scale * $this->x_amplitude),
                $i,
                0,
                1,
                $this->height * $this->scale
            );
        }

        // Y-axis wave generation
        $k = rand(0, 100);
        $yp = $this->scale * $this->y_period * rand(1, 2);
        for ($i = 0; $i < ($this->height * $this->scale); $i++) {
            imagecopy(
                $this->foreground,
                $this->foreground,
                sin($k + $i / $yp) * ($this->scale * $this->y_amplitude),
                $i - 1,
                0,
                $i,
                $this->width * $this->scale,
                1
            );
        }
    }
}
