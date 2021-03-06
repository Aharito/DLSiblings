# DLSiblings

### Вывод соседних ресурсов с шаблонизацией (множественная кольцевая перелинковка)

*Подробнее о кольцевой схеме перелинковки под НЧ-запросы [читайте в моей статье >>](https://aharito.ru/seo-prodvizhenie/shema-perelinkovki-stranic-sajta-pod-nch)*

*Больше информации о параметрах [читайте на моем сайте >>](https://aharito.ru/modx-evolution/dlsiblings-podnimaem-sajt-po-nch-zaprosam)*

## Параметры сниппета:
- **&renderSnippet** ( DocLister | sgController ) сниппет, используемый для вывода, default DocLister
- **&prevQty** Кол-во соседей-предшественников, default 2
- **&nextQty** Кол-во соседей-последователей, default 2
- **все** остальные параметры и шаблоны **как в DocLister/sgController**

Можно использовать унаследованные от DocLister (такие же, как у него): условия выборки `&addWhereList` и  `&filters`, условия сортировки `&orderBy`, глубину выборки `&depth`, prepare-сниппеты и многие другие параметры и **все шаблоны**.

Если в качестве сниппета вывода указан sgController, то соответственно можно использовать и все условия, параметры и шаблоны sgController.

## Шаблоны сниппета
Все шаблоны - точно такие же, **как у DocLister**. Плейсхолдеры в шаблонах - тоже точно такие же, **как в DocLister**. Если в качестве сниппета вывода указан sgController, то шаблоны и плейсхолдеры - точно такие же, как в sgController.


## ПРИМЕР

Простой вызов сниппета.

	[[DLSiblings?
		&idType=`parents`
		&parents=`[*parent*]`
		&tpl=`@CODE: <a href="[+url+]">[+tv.h1+]</a><br>`
		&prevQty=`2`
		&nextQty=`2`
		&tvList=`h1`
	]]


*Больше примеров с кодом и пояснениями в моей статье [Примеры DLSiblings >>](https://aharito.ru/modx-evolution/dlsiblings-primery-perelinkovki)*

### Результат работы

Результат работы более сложного вызова сниппета с выводом превьюшек, даты и заголовка может выглядеть примерно так:
![siblings_demo_1](https://user-images.githubusercontent.com/6253807/50377091-b58ff300-0649-11e9-8880-f2672927e4af.png)
