<?php
/**
 * DLSiblings
 * вывод соседних ресурсов с шаблонизацией (множественная кольцевая перелинковка)
 * @category snippet
 *
 * @version   0.1
 * CMS version MODx Evo 7.1.6
 * @lastupdate 25/03/2017
 * 
 * @author Aharito http://aharito.ru на основе DLPrevNext @author Agel_Nash <Agel_Nash@xaker.ru>
 *
 * @param int &Qty Кол-во соседей с каждой стороны, default &Qty=`2`
 * @param string &ownerTPL Шаблон-обертка, должен содержать плейсхолдер [+wrap+], default &ownerTPL=`@CODE:<div>[+wrap+]</div>`
 * @NOTE остальные параметры - как у DocLister
 * @example
 *       [[DLSiblings? &idType=`parents` &parents=`[*parent*]` &tpl=`@CODE: <a href="[+url+]">[+tv_h1+]</a><br>` &Qty=`2` &tvList=`h1` ]]
**/
	
	
if ( ! defined('MODX_BASE_PATH')) {
	die('HACK???');
}

$ownerTPL = isset($ownerTPL) ? $ownerTPL : '@CODE:[+wrap+]'; //Дефолтное значение &ownerTPL
$Qty = isset($Qty) ? $Qty : 2; //Дефолтное значение &Qty

$out = $prevOut = $nextOut = "";
$next = $prev = array();

$ID = $modx->documentIdentifier;
$params = is_array($modx->Event->params) ? $modx->Event->params : array();
$params = array_merge( $params, array('api' => '1', 'debug' => '0', 'display' => 'all') );

$json = $modx->runSnippet("DocLister", $params);
$children = jsonHelper::jsonDecode($json, array('assoc' => true));
$children = is_array($children) ? $children : array(); // Тут проверка, что вернулся массив

$ids = array_keys($children); //Индексный массив ID в выборке (потом избавиться от него через prev-next)

$curIndex = array_search($ID, $ids); //Текущий индекс (индекс текущего ID)

$count = count($ids); // Длина массива
$lastIndex = $count - 1; // Последний индекс

$TPL = DLTemplate::getInstance($modx);

if($count-1 > 0) { // Если длина выборки (за исключением текущего элемента) больше 0

	if(($count - 1) <= $Qty*2) { // Если длина выборки (за исключением текущего элемента) меньше нужного кол-ва
		// То просто выводим все элементы выборки
		for($i=0; $i<=$lastIndex; $i++) {
			$out .= ($curIndex == $i) ? "" : $TPL->parseChunk($tpl, $children[$ids[$i]]);
		}

	} else {

		// Иначе ищем соседей
		for($i=1; $i<=$Qty; $i++) {
			$next[$i-1] = ($curIndex + $i <= $lastIndex) ? $ids[$curIndex + $i] : $ids[$i - ($lastIndex - $curIndex) - 1];
			$prev[$i-1] = ($curIndex - $i >= 0) ? $ids[$curIndex - $i] : $ids[$count + $curIndex - $i];
		}

		for($i=1; $i<=$Qty; $i++) {
			$prevOut .= $TPL->parseChunk($tpl, $children[$prev[$i-1]]);
			$nextOut .= $TPL->parseChunk($tpl, $children[$next[$i-1]]);
		}

		$out = $prevOut.$nextOut;

	}
	return $TPL->parseChunk( $ownerTPL, array('wrap' => $out) );
	
} else { // Если длина выборки (за исключением текущего элемента) <= 0 (нет элементов, кроме текущего, или вообще нет)
	
	// Если задан noneTPL, парсим его без параметров
	if(isset($params['noneTPL'])) $out = $TPL->parseChunk( $noneTPL, array() );
	
	// Если noneWrapOuter не задано или задано как 1, и ownerTPL не пустой
	if( (!isset($params['noneWrapOuter']) || $params['noneWrapOuter'] == 1) && !empty($params['ownerTPL']) )
		// то "нулевой" результат оборачиваем в ownerTPL (копируем поведение ДокЛистер)
		$out = $TPL->parseChunk( $ownerTPL, array('wrap' => $out) );
	
	return $out;
}
