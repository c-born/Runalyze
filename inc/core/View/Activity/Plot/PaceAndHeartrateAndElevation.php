<?php
/**
 * This file contains class::PaceAndHeartrateAndElevation
 * @package Runalyze\View\Activity\Plot
 */

namespace Runalyze\View\Activity\Plot;

use Runalyze\View\Activity;

/**
 * Plot for: Pace and heartrate and elevation
 *
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Plot
 */
class PaceAndHeartrateAndElevation extends ActivityPlot {
	/**
	 * Set key
	 */
	protected function setKey() {
		$this->key = 'pacehrelevation';
	}

	/**
	 * Init data
	 * @param \Runalyze\View\Activity\Context $context
	 */
	protected function initData(Activity\Context $context) {
		$this->addMultipleSeries(array(
			new Series\Elevation($context),
			new Series\Pace($context),
			new Series\Heartrate($context),
			new Series\TimeSeries($context),
			new Series\DistanceSeries($context)
		));
	}
}
