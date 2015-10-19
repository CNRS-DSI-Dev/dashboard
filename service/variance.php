<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Service;

/**
 * Variance
 * Implements http://en.wikipedia.org/wiki/Algorithms_for_calculating_variance#Computing_shifted_data algorithm
 */

class Variance
{
    protected $K;
    protected $n;
    protected $Ex;
    protected $Ex2;

    public function __construct()
    {
        $this->K = 0;
        $this->n = 0;
        $this->Ex = 0;
        $this->Ex2 = 0;
    }

    public function addValue($value)
    {
        if (!is_numeric($value)) {
            $value = (float)$value;
        }

        if ($this->n == 0) {
            $this->K = $value;
        }

        $this->n ++;
        $this->Ex += $value - $this->K;
        $this->Ex2 += ($value - $this->K) * ($value - $this->K);
    }

    public function getMean()
    {
        return $this->K + $this->Ex / $this->n;
    }

    public function getVariance()
    {
        return ($this->Ex2 - ($this->Ex * $this->Ex) / $this->n) / ($this->n);
    }

    public function getStandardDeviation()
    {
        return sqrt($this->getVariance());
    }
}
