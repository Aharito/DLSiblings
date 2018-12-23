//<?php
/**
 * DLSiblings
 * Вывод соседних ресурсов с шаблонизацией (множественная кольцевая перелинковка)
 * @category snippet
 *
 * @version   2.0
 * CMS version MODx Evo 1.4.7
 * @lastupdate 21/12/2017
 *
 * @author Aharito https://aharito.ru на основе DLPrevNext @author Agel_Nash <Agel_Nash@xaker.ru>
 * @internal    @installset base, sample 
 *
 * @param int docid Если задан, то соседи выводятся для этого документа, default id текущего документа
 * @param int &prevQty Кол-во соседей-предшественников, default 2
 * @param int &nextQty Кол-во соседей-последователей, default 2
 *
 * @NOTE: Остальные параметры - как у DocLister
 *
 * @example
 * [[DLSiblings? &idType=`parents` &parents=`[*parent*]` &ownerTPL=`@CODE:<ul>[+dl.wrap+]</ul>` &tpl=`@CODE:<li><a href="[+url+]">[+title+]</a></li>` &tvList=`diam,vid` &tvSortType=`UNSIGNED, UNSIGNED` &orderBy=`diam ASC` &addWhereList=`c.template = 7` &filters=`AND(tv:vid:=:стальной)` ]]
 */
  
return require MODX_BASE_PATH.'assets/snippets/DLSiblings/snippet.DLSiblings.php';
