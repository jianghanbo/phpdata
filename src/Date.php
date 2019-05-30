<?php
namespace src;

/**
 * 日期时间 业务模型
 */
class Date {
	protected function __construct() {

	}

	protected static $_instance;
	public static function getInstance(){
		if (!(self::$_instance instanceof Date)){
			self::$_instance = new static();
		}

		return self::$_instance;
	}

	/**
	 * 某年有多少个周
	 *
	 * @param string $year 年份
	 * @return int 一年的周数
	 */
	public function getYearWeeks($year = '') {
		if (empty($year)) {
			return 0;
		}

		return date("W", mktime(0, 0, 0, 12, 28, $year));
	}

	/**
	 * 获取一周的开始时间戳 和 结束时间戳
	 *
	 * @param string $year 年份
	 * @param string $week 第几周
	 * @return array 时间戳
	 */
	public function getWeekBeginEndTimeUTC($year = '', $week = '') {
		if (empty($year) || empty($week)) {
			return array();
		}

		//【处理 跨年的交叉周 情况】
		// 如 2015的最后一周为 53周，但这周的周五为 2016年01月01日
		// 在 2016年01月01日，通过 date('W') 得到当前周为 53，而不是 2016年的第1周
		// 所以需要特殊处理一下：
		$weeks = $this->getYearWeeks($year);
		if ($week > $weeks) {
			$year = $this->getPreYear($year);
		}

		// 注意：一定要为 2位数，否则计算出错
		$week = str_pad($week, 2, '0', STR_PAD_LEFT);
		$beginTime = strtotime($year . 'W' . $week);
		$endTime = $beginTime + 7 * 24 * 60 * 60 - 1;

		return array($beginTime, $endTime);
	}

	/**
	 * 获取某一月的 周 开始时间戳 和 结束时间戳
	 *
	 * @param string $year  年份
	 * @param string $month 第几月
	 * @return array 时间戳
	 */
	public function getMonthWeekBeginEndTimeUTC($year = '', $month = '') {
		if (empty($year) || empty($month)) {
			return array();
		}

		// 本月1日
		$firstDay = strtotime($year . '-' . $month . '-01 00:00:00');
		$firstDayWeekDay = date('N', $firstDay);

		// 定义日历中本月的开始时间
		$beginTime = $firstDay;
		if ($firstDayWeekDay == 1) { // 日历显示6周，当本月第1天为周一时，则放到第二周显示，所以开始时间为7天之前
			$beginTime -= 7 * 24 * 3600;
		} else { // 日历显示6周，当本月第1天不为周一时，则放到第一周显示，所以开始时间为此周的周一
			$beginTime -= ($firstDayWeekDay - 1) * 24 * 3600;
		}

		// 因为日历总是显满6个周时间，所以最后时间应为 当前时间 + 6 * 7 * 24 * 60 * 60 - 1
		$endTime = $beginTime + 6 * 7 * 24 * 60 * 60 - 1;

		return array($beginTime, $endTime);
	}

	/**
	 * 获取某一月的 开始时间戳 和 结束时间戳
	 *
	 * @param string $year  年份
	 * @param string $month 第几月
	 * @return array 时间戳
	 */
	public function getMonthBeginEndTimeUTC($year = '', $month = '') {
		if (empty($year) || empty($month)) {
			return array();
		}

		$beginTime = strtotime($year . '-' . $month . '-01 00:00:00');
		$nextMonth = $this->getNextMonth($year, $month);
		$endTime = strtotime($nextMonth[0] . '-' . $nextMonth[1] . '-01 00:00:00') - 1;

		return array($beginTime, $endTime);
	}

	/**
	 * 获取今天的 开始时间戳 和 结束时间戳
	 */
	public function getTodayBeginEndTimeUTC() {
		$beginTime = strtotime(date('Y-m-d') . ' 00:00:00');
		$endTime = $beginTime + 24 * 60 * 60 - 1;

		return array($beginTime, $endTime);
	}

	/**
	 * 获取某天的 开始时间戳 和 结束时间戳
	 *
	 * @param string $day 某天
	 * @return  array
	 */
	public function getDayBeginEndTimeUTC($day = '') {
		if (empty($day)) {
			return array();
		}

		$beginTime = strtotime(date('Y-m-d', $day) . ' 00:00:00');
		$endTime = $beginTime + 24 * 60 * 60 - 1;

		return array($beginTime, $endTime);
	}

	/**
	 * 获取本周的 开始时间戳 和 结束时间戳
	 */
	public function getThisWeekBeginEndTimeUTC() {
		$year = $this->getThisYear();
		$week = $this->getThisWeek();

		return $this->getWeekBeginEndTimeUTC($year, $week);
	}

	/**
	 * 获取本月的 开始时间戳 和 结束时间戳
	 */
	public function getThisMonthBeginEndTimeUTC() {
		$year = $this->getThisYear();
		$month = $this->getThisMonth();

		return $this->getMonthBeginEndTimeUTC($year, $month);
	}

	/**
	 * 最近7天的 开始时间戳 和 结束时间戳
	 */
	public function getLastSevenDaysBeginEndTimeUTC() {
		$endTime = strtotime(date('Y-m-d') . ' 23:59:59');
		$beginTime = strtotime(date('Y-m-d') . ' 00:00:00') - 6 * 24 * 60 * 60;

		return array($beginTime, $endTime);
	}

	/**
	 * 最近30天的 开始时间戳 和 结束时间戳
	 */
	public function getLastThirtyDaysBeginEndTimeUTC() {
		$endTime = strtotime(date('Y-m-d') . ' 23:59:59');
		$beginTime = strtotime(date('Y-m-d') . ' 00:00:00') - 29 * 24 * 60 * 60;

		return array($beginTime, $endTime);
	}

	/**
	 * 本年
	 */
	public function getThisYear() {
		return date('Y');
	}

	/**
	 * 本月
	 */
	public function getThisMonth() {
		return date('m');
	}

	/**
	 * 本周
	 */
	public function getThisWeek() {
		return date('W');
	}

	/**
	 * 上一年
	 *
	 * @param string $year 年份
	 * @return string
	 */
	public function getPreYear($year = '') {
		if (empty($year)) {
			return '';
		}
		$year = ($year <= 1970) ? 1970 : $year - 1;

		return $year;
	}

	/**
	 * 下一年
	 *
	 * @param string $year 年份
	 * @return string
	 */
	public function getNextYear($year = '') {
		if (empty($year)) {
			return '';
		}
		$year = ($year >= 2038) ? 2038 : intval($year) + 1;

		return $year;
	}

	/**
	 * 上一月
	 *
	 * @param string $year  年份
	 * @param string $month 月份
	 * @return array
	 */
	public function getPreMonth($year = '', $month = '') {
		if (empty($year) || empty($month)) {
			return array();
		}

		if ($month <= 1) {
			$month = 12;
			$year = $this->getPreYear($year);
		} else {
			$month--;
		}

		return array($year, $month);
	}

	/**
	 * 下一月
	 *
	 * @param string $year  年份
	 * @param string $month 月份
	 * @return array
	 */
	public function getNextMonth($year = '', $month = '') {
		if (empty($year) || empty($month)) {
			return array();
		}

		if ($month >= 12) {
			$month = 1;
			$year = $this->getNextYear($year);
		} else {
			$month++;
		}

		return array($year, $month);
	}

	/**
	 * 上一周
	 *
	 * @param string $year 当前年份
	 * @param string $week 当前周数
	 * @return array
	 */
	public function getPreWeek($year = '', $week = '') {
		if (empty($year) || empty($week)) {
			return array();
		}

		if ($week <= 1) {
			$year = $this->getPreYear($year);
			$week = $this->getYearWeeks($year);
		} else {
			$week--;
		}

		return array($year, $week);
	}

	/**
	 * 下一周
	 *
	 * @param string $year 当前年份
	 * @param string $week 当前周数
	 * @return array
	 */
	public function getNextWeek($year = '', $week = '') {
		if (empty($week) || empty($year)) {
			return array();
		}

		$weeks = $this->getYearWeeks($year);
		if ($week >= $weeks) {
			$week = 1;
			$year = $this->getNextYear($year);
		} else {
			$week++;
		}

		return array($year, $week);
	}

	/**
	 * 通过查看类型 获取开始、结束时间戳
	 *
	 * @param string $viewType 1:最近7天，2:最近30天，3: 上一个月，4：当月
	 * @return array
	 */
	public function getBeginEndTimeUTCByViewType($viewType = '1') {
		$viewType = $viewType ?: '1';
		$beginTime = $endTime = 0;

		switch ($viewType) {
			case '1': // 最近 7天
				return $this->getLastSevenDaysBeginEndTimeUTC();
				break;
			case '2': // 最近 30天
				return $this->getLastThirtyDaysBeginEndTimeUTC();
				break;
			case '3': // 上一个月
				$thisYear = $this->getThisYear();
				$thisMonth = $this->getThisMonth();
				$preMonth = $this->getPreMonth($thisYear, $thisMonth);
				return $this->getMonthBeginEndTimeUTC($preMonth[0], $preMonth[1]);
				break;
			case '4': // 当月
				return $this->getThisMonthBeginEndTimeUTC();
				break;
		}

		return array($beginTime, $endTime);
	}

	/**
	 * 获取某时间段内的日期列表
	 *
	 * @param int $beginTime 开始时间戳
	 * @param int $endTime   结束时间戳
	 * @return array
	 */
	public function getDaysByBeginEndTimeUTC($beginTime = 0, $endTime = 0) {
		if (empty($beginTime) || empty($endTime)) {
			return array();
		}

		$beginDayUTC = strtotime(date('Y-m-d', $beginTime) . ' 00:00:00');
		$days = array();
		for ($time = $beginDayUTC; $time <= $endTime; $time += 24 * 60 * 60) {
			$days[date('Y-m-d', $time)] = array(
				date('m-d', $time), $time + 24 * 60 * 60 - 1
			);
		}

		return $days;
	}
}
