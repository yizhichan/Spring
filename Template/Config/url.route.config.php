<?
/**
 * 重新定义url规则
 */

$rules[] = array(
	'#/list/#',
	array('mod' => 'article', 'action' => 'index'),
	array(
		'cateId' => '#/(\d+)/#',
		'page' => '#/page-(\d+)\.html#',
		),
);

$rules[] = array(
	'#/data/#',
	array('mod' => 'article', 'action' => 'detail'),
	array(
		'id' => '#(\d+).html#',
		),
);

$rules[] = array(
	'#/article/change/#',
	array('mod' => 'article', 'action' => 'edit'),
	array(
		'id' => '#/articleId/(\d+)/#',
		'cateId' => '#/categoryId/(\d+)/#',
		),
);

?>