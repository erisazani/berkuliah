<?php
/* @var $this DashboardController */
/* @var $downloadsDataProvider CArrayDataProvider */
?>

<h4>Sejarah Pengunduhan</h4>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$downloadsDataProvider,
	'itemView'=>'_downloads',
)); ?>
