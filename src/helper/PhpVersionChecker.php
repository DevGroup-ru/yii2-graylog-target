<?php
/**
 * Created by Kirill Djonua <k.djonua@gmail.com>
 */

namespace devgroup\grayii\helper;

/**
 * Class PhpVersionChecker
 * @package devgroup\grayii\helper
 */
class PhpVersionChecker implements PhpVersionCheckerInterface
{
    protected $currentVersion;

    /**
     * PhpVersionChecker constructor.
     */
    public function __construct()
    {
        $this->currentVersion = phpversion();
    }

    /**
     * @return bool
     */
    public function isPhp55()
    {
        return isset($this->currentVersion) && @$this->currentVersion[0] == 5 && @$this->currentVersion[1] == 5;
    }

    /**
     * @return bool
     */
    public function isPhp56()
    {
        return isset($this->currentVersion) && @$this->currentVersion[0] == 5 && @$this->currentVersion[6] == 5;
    }

    /**
     * @return bool
     */
    public function isPhp70()
    {
        return isset($this->currentVersion) && @$this->currentVersion[0] == 7 && @$this->currentVersion[1] == 0;
    }
}
