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
    /**
     * Methot checks if current PHP version is 5.5 compatible
     * @return mixed
     */
    public function isPhp55();
    /**
     * Methot checks if current PHP version is 5.6 compatible
     * @return mixed
     */
    public function isPhp56();
    /**
     * Methot checks if current PHP version is 7.0 compatible
     * @return mixed
     */
    public function isPhp70();
}
