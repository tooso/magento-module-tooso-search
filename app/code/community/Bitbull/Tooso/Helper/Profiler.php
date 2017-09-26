<?php
/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
class Bitbull_Tooso_Helper_Profiler implements Bitbull_Tooso_Log_ProfilerInterface
{

    /**
     * Start timing
     *
     * @param string $label
     */
    public function start($label){
        Varien_Profiler::start($label);
    }

    /**
     * Stop timing
     *
     * @param string $label
     */
    public function stop($label){
        Varien_Profiler::start($label);
    }

}