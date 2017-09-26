<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface Bitbull_Tooso_Log_ProfilerInterface
{

    /**
     * Start timing
     *
     * @param string $label
     */
    public function start($label);

    /**
     * Stop timing
     *
     * @param string $label
     */
    public function stop($label);
}