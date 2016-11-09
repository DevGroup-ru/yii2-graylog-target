<?php
/**
 * Created by Kirill Djonua <k.djonua@gmail.com>
 */

namespace devgroup\grayii\helper;

/**
 * Interface PhpVersionCheckerInterface
 * @package devgroup\grayii\helper
 */
interface PhpVersionCheckerInterface
{
    public function isPhp55();
    public function isPhp56();
    public function isPhp70();
}