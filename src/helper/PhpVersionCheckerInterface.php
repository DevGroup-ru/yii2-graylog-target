<?php
/**
 * Created by Kirill Djonua <k.djonua@gmail.com>
 */

namespace kdjonua\grayii\helper;

/**
 * Interface PhpVersionCheckerInterface
 * @package kdjonua\grayii\helper
 */
interface PhpVersionCheckerInterface
{
    public function isPhp55();
    public function isPhp56();
    public function isPhp70();
}