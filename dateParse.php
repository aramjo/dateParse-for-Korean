<?
/**
* 한글 문자열로부터 DATE TIME 파싱
*/
class DateParse {
	var $set, $setStr, $matchVal, $aUnit_Index, $aWeek_Day, $aParsing_Tokens, $aLoc;

	public function __construct() {
		$this->set = array();
		$this->setStr = array();
		$this->matchVal = array();
		$this->aUnit_Index = array(1=>'second', 2=>'minute', 3=>'hour', 4=>'day', 5=>'week', 6=>'month', 7=>'year');
		$this->aWeek_Day = array(0=>'sunday', 1=>'monday', 2=>'tuesday', 3=>'wednesday', 4=>'thursday', 5=>'friday', 6=>'saturday');

		$this->aParsing_Tokens = array(
			'yyyy'=>array('param'=>'year', 'src'=>'\\d{4}'),
			'MM'=>array('param'=>'month', 'src'=>'[01]?\\d'),
			'dd'=>array('param'=>'date', 'src'=>'[0123]?\\d'),
			'hh'=>array('param'=>'hour', 'src'=>'[0-2]?\\d'),
			'mm'=>array('param'=>'minute', 'src'=>'[0-5]\\d'),
			'ss'=>array('param'=>'second', 'src'=>'[0-5]\\d(?:[,.]\\d+)?'),
			'yy'=>array('param'=>'year', 'src'=>'\\d{2}'),
			'y'=>array('param'=>'year', 'src'=>'\\d'),
			'yearSign'=>array('src'=>'[+-]', 'sign'=>true),
			'timestamp'=>array('src'=>'\\d+')
		);

		$chPT_Shift = "지지난|재작|작|지난|이번|올|다음|내|후내|내후|다다음";
		$chPT_ShiftUnit = "주|달|년|해";
		$chPT_Unit = "밀리초|초|분|시간|일|주|달|개월|년|해";
		$chPT_Weekday = "일요일|월요일|화요일|수요일|목요일|금요일|토요일|일|월|화|수|목|금|토";
		$chPT_Day = "그저께|그제|어제|오늘|내일모레|내일|모레";
		$chPT_Numeral = "공|영|일|이|삼|사|오|육|유|칠|팔|구|십|시|백|천";
		$chPT_Ordinal = "한|첫|두|둘|세|셋|네|넷|다섯|여섯|일곱|여덟|아홉|열";
		$chPT_HourNumeral = str_replace("시", "", $chPT_Numeral)."|".$chPT_Ordinal;
		$chPT_Midday = "정오|자정";
		$chPT_Ampm = "오전|오후|아침|저녁";
		$chPT_Sign = "전|앞|후|뒤";

		$this->aLoc = array(
			"compiledFormats"=>array(
				array(
					"reg"=>"~ *(".$chPT_Shift.") ?(".$chPT_ShiftUnit.") ?(?:([01]?\d|(?:".$chPT_Numeral.")+)월)? ?(?:([1-6]\d|(?:".$chPT_Ordinal.")+)째주) ?(".$chPT_Weekday.")? *~iu",
					"to"=>array("shift", "unit", "month", "week", "weekday")
				),
				array(
					"reg"=>"~ *(".$chPT_Shift.") ?(".$chPT_ShiftUnit.") ?(?:([01]?\d|(?:".$chPT_Numeral.")+)월)? ?(?:([0123]?\d|(?:".$chPT_Numeral.")+)일)? ?(".$chPT_Weekday.")? ?(".$chPT_Midday.")? ?(".$chPT_Ampm.")? ?(?:([0-2]?\d|(?:".$chPT_HourNumeral.")+)시)? ?(반)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)분)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)초)? *~iu",
					"to"=>array("shift", "unit", "month", "date", "weekday", "midday", "ampm", "hour", "half", "minute", "second")
				),
				array(
					"reg"=>"~ *(".$chPT_Day.") ?(".$chPT_Midday.")? ?(".$chPT_Ampm.")? ?(?:([0-2]?\d|(?:".$chPT_HourNumeral.")+)시)? ?(반)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)분)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)초)? *~iu",
					"to"=>array("day", "midday", "ampm", "hour", "half", "minute", "second")
				),
				array(
					"reg"=>"~ *(".$chPT_Shift.") ?(주) ?(".$chPT_Weekday.") ?(".$chPT_Midday.")? ?(".$chPT_Ampm.")? ?(?:([0-2]?\d|(?:".$chPT_HourNumeral.")+)시)? ?(반)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)분)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)초)? *~iu",
					"to"=>array("shift", "unit", "weekday", "midday", "ampm", "hour", "half", "minute", "second")
				),
				array(
					"reg"=>"~ *(\d+|(?:".$chPT_Numeral."|".$chPT_Ordinal.")+)(".$chPT_Unit.") ?(".$chPT_Sign.") ?(?:([01]?\d|(?:".$chPT_Numeral.")+)월)? ?(?:([0123]?\d|(?:".$chPT_Numeral.")+)일)? ?(".$chPT_Weekday.")? ?(".$chPT_Midday.")? ?(".$chPT_Ampm.")? ?(?:([0-2]?\d|(?:".$chPT_HourNumeral.")+)시)? ?(반)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)분)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)초)? *~iu",
					"to"=>array("num", "unit", "sign", "month", "date", "weekday", "midday", "ampm", "hour", "half", "minute", "second")
				),
				array(
					"reg"=>"~ *(\d+|(?:".$chPT_Numeral."|".$chPT_Ordinal.")+)(".$chPT_Unit.") ?(".$chPT_Sign.") *~iu",
					"to"=>array("num", "unit", "sign")
				),
				array(
					"reg"=>"~ *(?:(\d{4}|(?:".$chPT_Numeral."|".$chPT_Ordinal.")+)년)? ?(?:([01]?\d|(?:".$chPT_Numeral.")+)월)? ?(?:([1-6]\d|(?:".$chPT_Ordinal.")+)째주)? ?(?:([0123]?\d|(?:".$chPT_Numeral.")+)일) ?(".$chPT_Weekday.")? ?(".$chPT_Midday.")? ?(".$chPT_Ampm.")? ?(?:([0-2]?\d|(?:".$chPT_HourNumeral.")+)시)? ?(반)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)분)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)초)? *~iu",
					"to"=>array("year", "month", "week", "date", "weekday", "midday", "ampm", "hour", "half", "minute", "second")
				),
				array(
					"reg"=>"~ *(?:(\d{4}|(?:".$chPT_Numeral."|".$chPT_Ordinal.")+)년) ?(?:([01]?\d|(?:".$chPT_Numeral.")+)월) ?(?:([1-6]\d|(?:".$chPT_Ordinal.")+)째주)? *~iu",
					"to"=>array("year", "month", "week")
				),
				array(
					"reg"=>"~ *(?:(\d{4}|(?:".$chPT_Numeral."|".$chPT_Ordinal.")+)년) ?(?:([01]?\d|(?:".$chPT_Numeral.")+)월) *~iu",
					"to"=>array("year", "month")
				),
				array(
					"reg"=>"~ *(?:(\d{4}|(?:".$chPT_Numeral."|".$chPT_Ordinal.")+)년) *~iu",
					"to"=>array("year")
				),
				array(
					"reg"=>"~ *(\d{4})[-.\/]([01]?\d)(?:[-.\/]([0123]?\d)) ?(".$chPT_Ampm.")? ?(?:([0-2]?\d|(?:".$chPT_HourNumeral.")+)시)? ?(반)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)분)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)초)? *~iu",
					"to"=>array("yyyy", "MM", "dd", "ampm", "hour", "half", "minute", "second")
				),
				array(
					"reg"=>"~ *(".$chPT_Ampm.")? ?([0-2]?\d|(?:".$chPT_HourNumeral.")+)(?::|시) ?(반)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)분)? ?(?:([0-5]\d|(?:".$chPT_Numeral.")+)초)? *~iu",
					"to"=>array("ampm", "hour", "half", "minute", "second")
				)
			),
			"ampmMap"=>array("오전"=>0, "아침"=>0, "오후"=>1, "저녁"=>1),
			"dayMap"=>array("그저께"=>-2, "그제"=>-2, "어제"=>-1, "오늘"=>0, "내일"=>1, "모레"=>2, "내일모레"=>2),
			"halfMap"=>array("반"=>0.5),
			"middayMap"=>array("정오"=>12, "자정"=>24),
			"monthMap"=>array(),
			"numeralMap"=>array("공"=>0, "영"=>0, "제로"=>0, "일"=>1, "한"=>1, "이"=>2, "두"=>2, "둘"=>2, "삼"=>3, "세"=>3, "셋"=>3, "사"=>4, "네"=>4, "넷"=>4, "오"=>5, "다섯"=>5, "육"=>6, "유"=>6, "여섯"=>6, "칠"=>7, "일곱"=>7, "팔"=>8, "여덟"=>8, "구"=>9, "아홉"=>9, "십"=>10, "시"=>10, "열"=>10, "백"=>100, "천"=>1000),
			"shiftMap"=>array("지지난"=>-2, "재작"=>-2, "작"=>-1, "지난"=>-1, "이번"=>0, "올"=>0, "다음"=>1, "내"=>1, "후내"=>2, "내후"=>2, "다다음"=>2),
			"signMap"=>array("전"=>-1, "앞"=>-1, "후"=>1, "뒤"=>1),
			"unitMap"=>array("밀리초"=>0, "초"=>1, "분"=>2, "시간"=>3, "일"=>4, "주"=>5, "달"=>6, "개월"=>6, "년"=>7, "해"=>7),
			"weekdayMap"=>array("일요일"=>0, "일"=>0, "월요일"=>1, "월"=>1, "화요일"=>2, "화"=>2, "수요일"=>3, "수"=>3, "목요일"=>4, "목"=>4, "금요일"=>5, "금"=>5, "토요일"=>6, "토"=>6),
			"weekMap"=>array("첫"=>1, "둘"=>2, "두"=>2, "세"=>3, "셋"=>3, "네"=>4, "넷"=>4, "다섯"=>5, "여섯"=>6)
		);
	}

	public function getDateParse($chStr="") {
		if (!$chStr || !is_string($chStr)) return "";
		$chStr = strtolower($chStr);

		$dDateTime = date("Y-m-d H:i:s");
		$aDate = explode(" ", $dDateTime);
		$dDate = $aDate[0];
		$dTime = $aDate[1];
		$nDateTimeStamp = (strtotime('sunday', strtotime($dDate)) > strtotime($dDate)) ? strtotime('last sunday', strtotime($dDate)) : strtotime('sunday', strtotime($dDate));
		$dEndDateTime = "";

		$bMatch = false;
		$this->set = array();
		$this->setStr = array();
		$this->matchVal = array();

		for ($i=0, $nCnt=count($this->aLoc['compiledFormats']); $i<$nCnt; $i++) {
			$dif = $this->aLoc['compiledFormats'][$i];
			preg_match($dif['reg'], $chStr, $aMatch);
			if ($aMatch) {
				$bMatch = true;
				$nRegIdx = $i;

				// 확인 (매칭단어/매칭요소)
				echo "nRegIdx: ".$nRegIdx."<br>";
				print_r($aMatch);
				echo "<br>";
				print_r($dif['to']);

				$this->setCacheFormat($dif, $i);

				$set = $this->getFormatParams($aMatch, $dif, $dDate);
				$this->set = $set['set'];
				$this->setStr = $set['str'];

				if ($this->set['midday']) {
					$this->getHandleMidday($this->set['midday']);
				}
				if ($this->set['ampm']) {
					$this->getHandleAmpm($this->set['ampm']);
				}

				// 확인 (매칭된 요소/매칭된 요소 단어)
				echo "<br>";
				print_r($this->set);
				echo "<br>";
				print_r($this->setStr);
				echo "<br>";

				if (isset($this->set['shift']) && $this->set['unit']) {
					if ($this->set['shift'] === 0) {
						if ($this->set['unit'] == 6) $dDate = date('Y-m')."-01";
						if ($this->set['unit'] == 7) $dDate = date('Y')."-01-01";
						$dTime = '00:00:00';
					} else {
						$chUnit = $this->aUnit_Index[$this->set['unit']];
						if ($this->set['shift'] === -1 && $chUnit == 'month') {
							// strtotime -1 month 문제 회피
							$dDate = date('Y-m-d', strtotime('first day of '.$this->set['shift'].' '.$chUnit));
						} else {
							$dDate = date('Y-m-d', strtotime($this->set['shift'].' '.$chUnit));
						}
						$dTime = '00:00:00';
					}
					if (isset($this->set['weekday'])) {
						$nDateTimeStamp = (strtotime('sunday', strtotime($dDate)) > strtotime($dDate)) ? strtotime('last sunday', strtotime($dDate)) : strtotime('sunday', strtotime($dDate));
						$dDate = date('Y-m-d', strtotime($this->aWeek_Day[$this->set['weekday']], $nDateTimeStamp));
					}

					//-------------------------------------------------------------------
					$this->matchVal['year'] = $this->setStr['shift'].$this->setStr['unit'];
					if ($this->set['unit'] == 5 || isset($this->set['weekday'])) {
						$this->matchVal['month'] = $this->setStr['shift'].$this->setStr['unit'];
						$this->matchVal['day'] = $this->setStr['weekday'];
						$this->matchVal['weekday'] = $this->setStr['weekday'];
					}
					if ($this->set['unit'] == 6) $this->matchVal['month'] = $this->matchVal['day'] = $this->setStr['shift'].$this->setStr['unit'];
					//-------------------------------------------------------------------

				} else if ($this->set['sign'] && $this->set['unit'] && isset($this->set['num'])) {
					$plus = $this->set['sign'] > 0 ? '+' : '-';
					$chUnit = $this->aUnit_Index[$this->set['unit']];

					if ($this->set['unit'] > 3) {
						$dDate = date('Y-m-d', strtotime($plus.$this->set['num'].' '.$chUnit));
						$dTime = '00:00:00';
					} else {
						$dDateTime = date('Y-m-d H:i:s', strtotime($plus.$this->set['num'].' '.$chUnit));
						$aDate = explode(" ", $dDateTime);
						$dDate = $aDate[0];
						$dTime = $aDate[1];
					}

					//-------------------------------------------------------------------
					$this->matchVal['year'] = $this->setStr['num'].$this->setStr['unit'].$this->setStr['sign'];
					if ($this->set['unit'] == 4) {
						$this->matchVal['month'] = $this->setStr['num'].$this->setStr['unit'].$this->setStr['sign'];
						$this->matchVal['day'] = $this->setStr['num'].$this->setStr['unit'].$this->setStr['sign'];
					}
					if ($this->set['unit'] == 5) {
						$this->matchVal['month'] = $this->setStr['num'].$this->setStr['unit'].$this->setStr['sign'];
						$this->matchVal['day'] = $this->setStr['num'].$this->setStr['unit'].$this->setStr['sign'];
					}
					if ($this->set['unit'] == 6) $this->matchVal['month'] = $this->matchVal['day'] = $this->setStr['num'].$this->setStr['unit'].$this->setStr['sign'];
					//-------------------------------------------------------------------

				} else {
					if (isset($this->set['weekday'])) {
						$nDateTimeStamp = (strtotime('sunday', strtotime($dDate)) > strtotime($dDate)) ? strtotime('last sunday', strtotime($dDate)) : strtotime('sunday', strtotime($dDate));
						$dDate = date('Y-m-d', strtotime($this->aWeek_Day[$this->set['weekday']], $nDateTimeStamp));
						$dTime = '00:00:00';
					} else {
						if (isset($this->set['day'])) {
							if ($this->set['day'] === 0) {
								$dDate = date('Y-m-d', strtotime($this->set['day']. 'day'));
								$dTime = '00:00:00';
							} else {
								$dDate = date('Y-m-d', strtotime($this->set['day']. 'day'));
							}
						}
					}
					//-------------------------------------------------------------------
					if (isset($this->set['day'])) {
						$this->matchVal['year'] = $this->matchVal['month'] = $this->matchVal['day'] = $this->setStr['day'];
					}
					if (isset($this->set['weekday'])) {
						$this->matchVal['weekday'] = $this->setStr['weekday'];
					}
					//-------------------------------------------------------------------
				}

				if ($this->set['year']) {
					$dDate = $this->set['year'].'-01-01';
					$dTime = '00:00:00';
					$this->matchVal['year'] = $this->setStr['year'].'년';
				}
				if ($this->set['month']) {
					$dDate = substr($dDate,0,4).'-'.sprintf('%02d', $this->set['month']).'-01';
					$dTime = '00:00:00';
					$this->matchVal['month'] = $this->setStr['month'].'월';
				}
				if ($this->set['date']) {
					$dDate = substr($dDate,0,7).'-'.sprintf('%02d', $this->set['date']);
					$dTime = '00:00:00';
					$this->matchVal['day'] = $this->setStr['date'].'일';
				}
				if (isset($this->set['week'])) {
					$dDate = $this->getWeekNumDateInMonth($dDate, $this->set['week']);
					$this->matchVal['day'] = $this->setStr['week'].'째주';
					if (isset($this->set['weekday'])) {
						$nDateTimeStamp = (strtotime('sunday', strtotime($dDate)) > strtotime($dDate)) ? strtotime('last sunday', strtotime($dDate)) : strtotime('sunday', strtotime($dDate));
						$dDate = date('Y-m-d', strtotime($this->aWeek_Day[$this->set['weekday']], $nDateTimeStamp));
						$this->matchVal['day'] = $this->setStr['week'].'째주 '.$this->setStr['weekday'];
						$this->matchVal['weekday'] = $this->setStr['weekday'];
					}
				}
				if ($this->set['hour']) {
					$dTime = sprintf('%02d', $this->set['hour']).':00:00';
					$this->matchVal['hour'] = isset($this->set['ampm']) ? $this->setStr['ampm'].' '.$this->setStr['hour'].'시' : $this->setStr['hour'].'시';
				}
				if (isset($this->set['half'])) {
					$dTime = sprintf('%02d', $this->set['hour']).':30:00';
					$this->matchVal['minute'] = $this->setStr['half'];
				} else {
					if ($this->set['minute']) {
						$dTime = sprintf('%02d', $this->set['hour']).':'.sprintf('%02d', $this->set['minute']).':00';
						$this->matchVal['minute'] = $this->setStr['minute'].'분';
					}
				}
				if ($this->set['second']) {
					$dTime = sprintf('%02d', $this->set['hour']).':'.sprintf('%02d', $this->set['minute']).':'.sprintf('%02d', $this->set['second']);
					$this->matchVal['second'] = $this->setStr['second'].'초';
				}

				// 최종 출력
				$dDateTime = $dDate.' '.$dTime;
				break;
			}
		}

		return $bMatch ? $this->getDateSplit($dDateTime) : "";
	}

	private function setCacheFormat($dif, $i) {
		array_splice($this->aLoc['compiledFormats'], $i, 1);
		array_unshift($this->aLoc['compiledFormats'], $dif);
	}

	private function getFormatParams($aMatch, $dif, $dDate="") {
		$set = array();
		$set['set'] = array();
		$set['str'] = array();
		for ($i=0, $nCnt=count($dif['to']); $i<$nCnt; $i++) {
			$field = $dif['to'][$i];

			$str = $aMatch[$i+1];
			if ($str) {
				if ($field === 'yy' || $field === 'y') {
					$field = 'year';
					$val = $this->getYearFromAbbreviation($str, $dDate);
				} else if ($this->aParsing_Tokens[$field]) {
					$token = $this->aParsing_Tokens[$field];
					$field = $token['param'];
					$val = $this->getParsingTokenValue($token, $str);
				} else {
					$val = $this->getTokenValue($field, $str);
				}
				$set['set'][$field] = $val;
				$set['str'][$field] = $str;
			}
		}
		return $set;
	}

	private function getTokenValue($field, $str) {
		$val = '';
		if ($this->aLoc[$field.'Map']) {
			$map = $this->aLoc[$field.'Map'];
			if ($map) {
				$val = $map[$str];
			}
		}
		if ($val === '') {
			$val = $this->getNumber($str);
		}
		return $val;
      }

	private function getNumber($str) {
		$num = $this->aLoc['numeralMap'][$str];
		if ($num) return $num;

		$num = preg_replace("/,/i", ".", $str);
		if (is_numeric($num)) return $num;

		$num = $this->getNumeralValue($str);
		if ($num) {
			$this->aLoc['numeralMap'][$str] = $num;
			return $num;
		}
		return $num;
	}

	private function mb_str_split($str) {
		return preg_split('/(?<!^)(?!$)/u', $str);
	}

	private function getNumeralValue($str) {
		$place = 1; $num = 0; $lastWasPlace; $isPlace; $numeral; $digit; $arr;

		$arr = $this->mb_str_split($str);
		for ($i=(count($arr)-1); $i>=0; $i--) {
			$numeral = $arr[$i];
			$digit = $this->aLoc['numeralMap'][$numeral];

			if (!$digit) $digit = 0;
			$isPlace = $digit > 0 && $digit % 10 === 0;
			if ($isPlace) {
				if ($lastWasPlace) $num += $place;
				if ($i) {
					$place = $digit;
				} else {
					$num += $digit;
				}
			} else {
				$num += $digit * $place;
				$place *= 10;
			}
			$lastWasPlace = $isPlace;
		}
		return $num;
	}

	private function getParsingTokenValue($token, $str) {
		$val = "";
		if ($token['val']) {
			$val = $token['val'];
		} else if ($token['sign']) {
			$val = $str === '+' ? 1 : -1;
		} else if ($token['bool']) {
			$val = !!$val;
		} else {
			$val = preg_replace("/,/i", ".", $str);
		}
		return $val;
	}

	private function getYearFromAbbreviation($str, $d, $prefer=0) {
		$val = +($str);
		$val += $val < 50 ? 2000 : 1900;
		if ($prefer) {
			$delta = $val - substr($d,0,4);
			if ($delta / abs($delta) !== $prefer) $val += $prefer * 100;
		}
		return $val;
	}

	private function getHandleAmpm($ampm) {
		if ($ampm === 1 && $this->set['hour'] < 12) {
			$this->set['hour'] += 12;
		} else if ($ampm === 0 && $this->set['hour'] === 12) {
			$this->set['hour'] = 0;
		}
	}
	private function getHandleMidday($midday) {
		if ($midday === 12) {
			$this->set['hour'] = 12;
			$this->set['minute'] = $this->set['second'] = 0;
		} else {
			$this->set['hour'] = $this->set['minute'] = $this->set['second'] = 0;
		}
	}
	private function getWeekNumDateInMonth($dDate, $nNumber=0) {
		$nTime = strtotime($dDate);
		$dFirstMondayDate = date("Y-m-d", strtotime(date("Y-m-01", $nTime)));
		$nFirstWeekday = date('N', strtotime($dFirstMondayDate));

		$chThisNext = ($nFirstWeekday > 4) ? "next" : "this";
		$dFirstWeekDate = date("Y-m-d", strtotime($chThisNext." week", strtotime($dFirstMondayDate)));
		if ($nNumber > 1) {
			$dFirstWeekDate = date("Y-m-d", strtotime("+".($nNumber-1)." week", strtotime($dFirstWeekDate)));
		}
		return $dFirstWeekDate;
	}
	private function getDateSplit($dDateTime, $dEndDateTime="") {
		$nTime = strtotime($dDateTime);
		$aTemp = explode(" ", $dDateTime);
		$aDate = explode("-", $aTemp[0]);
		$aTime = explode(":", $aTemp[1]);
		$aWeek = array("일", "월", "화", "수", "목", "금", "토");

		$aVal = array();
		$aVal['match'] = $this->matchVal;
		$aVal['year'] = $aDate[0];
		$aVal['month'] = $aDate[1];
		$aVal['day'] = $aDate[2];
		$aVal['ampm'] = (int)$aTime[0] >= 12 ? "pm" : "am";
		$aVal['hour'] = $aTime[0];
		$aVal['minute'] = $aTime[1];
		$aVal['second'] = $aTime[2];
		$aVal['weekday'] = $aWeek[date("w", $nTime)];
		return $aVal;
	}
}


//------ 예문 적용 --//
	$aKoDate = array(
		"이번 주 일요일", "내일 오전 아홉시", "이주후 오후 여섯시", "이년뒤 5월 5일", "내년 오월 세째주 금요일", "다음달 세째주 수요일", "내년 5월 셋째주", "이천십이년 유월 넷째주", "다음주 월요일 오후 4시 반 ", "3개월후",
		"다음달 십오일 정오", "다다음달 팔일 오전 열한시", "다다음주 토요일 오후 5시반", "다음주 토요일 오후 5시 반",
		"지난주 월요일","지난주 금요일",  "지지난주 월요일 오후 여섯시", "이번주 월요일", "이번주 월요일 오후 여섯시",
		"재작년 5월 오일", "후내년 시월 칠일 오후 다섯시 반", "올해 5월팔일", "이번달 5일", "모레 오후 5시 반", "2018.12.5 다섯시 반",
		"오늘", "내일", "작년", "작년 십이월 4일", "내년 십일월 5일", "내년 시월 5일 오후 6시 반", "오늘 오후 다섯시 오십오분",
		"지난달 이십오일", "오일전", "팔일후", "이년후 5월 삼일", "삼십분뒤", "내일 아침 7시"
	);

	$oDate = new DateParse();
	for ($i=0, $nCnt=count($aKoDate); $i<$nCnt; $i++) {
		$chStr = "예약정보는 ".$aKoDate[$i]."입니다";
		$aResult = $oDate->getDateParse($chStr);

		echo "----------------------------------------------<br>";
		print_r($aResult['match']);
		echo "<br>";

		$chResult = "";
		foreach($aResult as $key=>$val) {
			if ($key != 'match') {
				$chResult .=$key."=>".$val." ";
			}
		}
		echo $chStr."<br><strong>".$chResult."</strong><br>----------------------------------------------<br>";

	}
?>
